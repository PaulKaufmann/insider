<?php

/**
 * Template Name: Front End Uploading
 */

$render_form = true;


global $current_user;

//restrictedMode:  prize draws, change-author and post-later are disabled
global $restrictedMode;
$restrictedMode = false;

global $form_errors;
$form_errors = array();

global $form_notices;
$form_notices = array();

global $post_url;

$restrictedMode = handle_uploading_restrictions($current_user);

add_action('genesis_before_content', 'add_back');

function add_back()
{
    ?>
    <div class='post-top-bar'><a class="noHighlight" href="/"><p id='inseider-back-Button'>
                <?php echo file_get_contents(get_Server_Image_Dir() . "small_back_icon.svg"); ?> zurück</p></a>
    </div>
    <?php
}


//import splitted files
$roots_includes = array(
    '/uploading/html.php'
);

foreach ($roots_includes as $file) {
    if (!$filepath = locate_template($file)) {
        trigger_error("Error locating `$file` for inclusion!", E_USER_ERROR);
    }

    require_once $filepath;
}
unset($file, $filepath);


//region handle_form_submit
function handle_form_submit()
{

    if (!isset($_POST['submit'])) {
        return null;
    }

    global $form_errors;
    global $current_user;
    $allowed = false;

    if (current_user_can('Developer')) {
        $allowed = true;
    } else {
        foreach ($current_user->roles as $role) {
            if (in_array($_POST['cat'], get_categories_by_role()[$role])) {
                $allowed = true;
                break;
            }
        }
    }

    if (!$allowed) {
        return wp_safe_redirect(home_url());
    }

    // Check that the nonce was set and valid
    if (!wp_verify_nonce($_POST['wps-frontend-post-nonce'], 'wps-frontend-post')) {
        $form_errors[] = 'Das Übertragung des Formulars ist ein Fehler aufgetreten, bitte versuchen Sie es erneut.';
        return null;
    }

    $durationSecconds = sanitize_text_field($_POST['duration']);
    $durationSecconds != "" ? $duration = format_secconds($durationSecconds) : $duration = "";

    $allowComments = 'false';

    $post_category = array(sanitize_text_field($_POST['cat']));

    $category_by_media = get_category_by_media_type($_POST['media_type']);
    if ($category_by_media) {
        $post_category = array("" . $category_by_media);
    }


    $force_comments = force_post_comments(null, $post_category, $_POST['media_type']);
    if ($force_comments == 0) {
        $_POST['allow_comments'] == 'on' ? $allowComments = 'true' : $allowComments = 'false';
    } else {
        $force_comments > 0 ? $allowComments = 'true' : $allowComments = 'false';
    }

    $post_invisible= $_POST['post_invisible'] == 'on';

    // Add the content of the form to $GLOBALS['post'] as an array
    $GLOBALS['post'] = array(
        'post_title' => sanitize_text_field($_POST['title']),
        'post_content' => $_POST['content'],
        'post_excerpt' => sanitize_text_field($_POST['subheading']),
        'tags_input' => sanitize_text_field($_POST['post_tags']),
        'post_category' => $post_category,
        'post_type' => 'post',
        'duration' => $duration,
        'meta_input' => array("allow_comments" => $allowComments, "post_invisible" => $post_invisible?"true":"false"),
    );


    if (!isset($_POST['edit_post'])) {


        foreach ($current_user->roles as $role) {
            if ($role == 'business_partner') {
                return wp_safe_redirect(home_url());
            }
        }

        if ($_POST['author_me'] != 'on' && !empty($_POST['author_id'])) {
            $author_user = get_user_by('login', sanitize_text_field($_POST['author_id']));
            $GLOBALS['post']['post_author'] = $author_user->ID;
        } else {
            $GLOBALS['post']['post_author'] = wp_get_current_user()->ID;
        }

        if (category_has_drafts($post_category)) {
            $GLOBALS['post']['post_status'] = 'draft';
        } else {
            $GLOBALS['post']['post_status'] = 'publish';
        }

        switch ($_POST['media_type']) {

            case ('video'):
                save_video_post();
                break;

            case ('article'):
                save_article_post();
                break;

            case ('audio'):
                save_audio_post();
                break;

            case ('gallery'):
                save_gallery_post();
                break;
            case ('prize_draw'):
                save_prize_draw_post();
                break;
            default:
                custom_media_save($_POST['media_type']);
                break;

        }

        render_confirm_message($_POST['media_type'], $post_category, false);
    } else {
        //edit post submitted

        $post_id = $_POST['edit_post'];
        $GLOBALS['post']['ID'] = $post_id;

        //this flag marks if values for some properties will only go life after confirmed by an admin
        $admin_activation_edit = false;
        if (admin_activation_needed_for_edit($post_id, false)) {

            $GLOBALS['admin_activation_edit'] = true;

            //there is not a custom excerpt and now there is or there is one and it has changed
            // (wp will return the content as an expcert if there is no custom excerpt)

            if ((!has_excerpt($post_id) && !empty($GLOBALS['post']['post_excerpt']))
                || (has_excerpt($post_id) && strcmp(get_the_excerpt($post_id), $GLOBALS['post']['post_excerpt']) != 0)) {
                $GLOBALS['post']['meta_input']['aae_post_excerpt'] = $GLOBALS['post']['post_excerpt'];
                unset($GLOBALS['post']['post_excerpt']);
            } else {
                delete_post_meta($post_id, 'aae_post_excerpt');
            }

            $old_content = apply_filters('the_content', get_the_content(null, false, $post_id));
            $new_content = apply_filters('the_content', $GLOBALS['post']['post_content']);

            $old_content = str_replace('decoding="async" ', '', $old_content);
            $new_content = str_replace('decoding="async" ', '', $new_content);

            if (strcmp(stripslashes(esc_html($old_content)), stripslashes(esc_html($new_content))) != 0) {
                $GLOBALS['post']['meta_input']['aae_post_content'] = $GLOBALS['post']['post_content'];
                unset($GLOBALS['post']['post_content']);
            } else {
                delete_post_meta($post_id, 'aae_post_content');
            }

        }

        //posts should change status when moved to categories that dont have drafts
        if (!category_has_drafts($post_category)) {
            $GLOBALS['post']['post_status'] = 'publish';
        }

        if ($_POST['author_me'] != 'on' && !empty($_POST['author_id'])) {
            $author_user = get_user_by('login', sanitize_text_field($_POST['author_id']));
            $GLOBALS['post']['post_author'] = $author_user->ID;
        }


        foreach ($current_user->roles as $role) {
            if ($role == 'business_partner' && $post_id != get_user_meta($current_user->ID, 'business_parter_post_id', true)) {
                return wp_safe_redirect(home_url());
            }
        }


        if (isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]['size'] > 0) {
            $GLOBALS['post']['use_video_thumbnail'] = !savePostThumbnail($post_id);
        } else {
            $GLOBALS['post']['use_video_thumbnail'] = true;
        }

        if (get_post_custom($post_id)['media_type'][0] == 'audio') {
            if ($_FILES["audio"]["size"] > 0) {
                save_audio_post($post_id);
            }
            saveDocuments($post_id);
        }

        if (get_post_custom($post_id)['media_type'][0] == 'video') {
            if ($_FILES["videoFile"]["size"] > 0) {
                save_video_post($post_id);
            } else {
                saveDocuments($post_id);
            }
        }


        if (get_post_custom($post_id)['media_type'][0] == 'gallery') {
            save_gallery_post($post_id);
        }

        if (get_post_custom($post_id)['media_type'][0] == 'prize_draw') {
            save_prize_draw_post($post_id);
        } else {
            handle_post_later($post_id);
        }

        if (get_post_custom($post_id)['media_type'][0] == 'article') {
            saveDocuments($post_id);
        }

        custom_media_save(get_post_custom($post_id)['media_type'][0], $post_id);
        link_attachmentIds_to_post($post_id);


        delete_post_meta($post_id, 'requested_activation');

        wp_save_post_revision($post_id);

        if (get_post_meta($post_id, 'notified_time', true) > 0) {
            update_post_meta($post_id, 'post_edited_after_last_notification', 'true');
        }

        $resp = wp_update_post($GLOBALS['post']);
        if ($resp) {
            $GLOBALS['post_url'] = get_permalink($post_id);
            render_confirm_message($_POST['media_type'], $post_category, true);
        } else {
            $form_errors[] = 'Fehler :' . $resp;
        }

    }
    return null;
}

//endregion

function render_confirm_message($media_type, $category, $edit_post)
{
    global $form_errors;
    global $form_notices;
    global $current_user;
    global $NOTIFICATION_SETTINGS;


    if (!empty($form_errors)) {
        echo '<p class="error_notice">';
        foreach ($form_errors as $fe) {
            echo "Fehler: " . "$fe" . '<br>';
        }
        echo '</p>';
        return;
    }

    if (!empty($form_notices)) {
        echo '<p class="form_notice">';
        foreach ($form_notices as $fe) {
            echo "" . "$fe" . '<br>';
        }
        echo '</p>';
    }

    $GLOBALS['render_form'] = false;

    if (is_array($category)) {
        $category = $category[0];
    }
    $message = '';
    $heading = '';

    if ($GLOBALS['confirm_heading'] && $GLOBALS['confirm_message']) {
        $message = $GLOBALS['confirm_message'];
        $heading = $GLOBALS['confirm_heading'];
    } else {
        if ($edit_post) {
            $heading = "Änderungen durchgeführt";
            if (in_array('business_partner', $current_user->roles)) {
                $message = 'Ihre Änderungen am Beitrag wurden erfolgreich hochgeladen, müssen jedoch noch von einem
              Administrator freigegeben werden, um für alle Mitarbeiter sichtbar zu sein.
              Die Freigabe kann über die Schaltfläche im Beitrag beantragt werden. ';
            } else {
                $message = "Der Beitrag wurde editiert. ";
            }
        } else {
            $heading = 'Post erfolgreich erstellt';

            if (category_has_drafts($category)) {

                switch ($media_type) {

                    case ('video'):
                        $message = 'Das Video wird konvertiert. Dies kann je nach Länge des Videos einige Minuten dauern. 
                        Danach wird der Beitrag als Entwurf veröffentlicht und ist nicht öffentlich sichtbar. ';
                        break;

                    case ('prize_draw'):
                    case ('article'):
                    case ('audio'):
                    case ('gallery'):
                    default:
                    {
                        $message = "Ihr Beitrag wurde als Entwurf veröffentlicht und ist nicht öffentlich sichtbar. ";
                    }
                }
                if (admin_activation_needed(null, array($category))) {
                    if ($media_type == 'prize_draw') {
                        $message .= "Das Gewinnspiel muss vor seinem Start freigegeben werden. Nur dann aktiviert es ich selbst. ";
                    } else {
                        $message .= "Der Beitrag muss freigegeben werden, um für alle Mitarbeiter sichtbar zu sein. ";
                    }
                    $message .= "Die Freigabe kann über die Schaltfläche im Beitrag beantragt werden. ";
                } else {

                    if ($media_type == 'prize_draw') {
                        $message .= "Das Gewinnspiel muss vor seinem Start freigegeben werden. Nur dann aktiviert es ich selbst. ";
                    } else {
                        $message .= "Bitte überprüfen Sie diesen Entwurf und geben Sie ihn anschließend über die Schaltfläche im Beitrag frei. ";
                    }
                }


                $pns = get_notification_settings($category);
                if ($pns == $NOTIFICATION_SETTINGS['NEVER']) {
                    $message .= "Bei der Veröffentlichung des Beitrags werden keine Benachrichtigungen verschickt. ";
                } elseif ($pns == $NOTIFICATION_SETTINGS['ALWAYS']) {
                    $message .= "Bei der Veröffentlichung des Beitrags werden automatisch alle Nutzer über den neuen Inhalt benachrichtigt. ";
                } else {
                    $message .= "Bei der Freigabe des Beitrags können dazu berechtige Nutzer entscheiden ob Benachrichtigungen verschickt werden sollen. ";
                }


            } else {
                if ($media_type == 'video') {
                    $message = 'Das Video wird konvertiert. Dies kann je nach Länge des Videos einige Minuten dauern. Danach wird der Beitrag veröffentlicht und ist für alle Mitarbeiter sichtbar. ';
                } else {
                    $message = "Ihr Beitrag ist nun für alle Mitarbeiter sichtbar. ";
                }


                $pns = get_notification_settings($category);
                if ($pns == $NOTIFICATION_SETTINGS['NEVER']) {
                    $message .= "Es werden keine Benachrichtigungen verschickt. ";
                } elseif ($pns == $NOTIFICATION_SETTINGS['ALWAYS']) {
                    $message .= "Alle Nutzer werden in den nächsten Minuten benachrichtigt. ";
                }
            }

        }
    }

    echo "<h2>$heading</h2><p>$message</p>";

    $post_Link = $GLOBALS['post_url'];

    if ($post_Link == "/") {
        $button_text = "zur Startseite";
    } else {
        $button_text = "zum Beitrag";
    }

    if ($post_Link) {
        echo "<a  onclick='redirect_after_faking_histrory(\"$post_Link\")'><button class='spk_wide_button'>$button_text</button></a>
<script>
function redirect_after_faking_histrory(link){

    window.history.pushState('" . home_url() . "', 'Home', '" . home_url() . "');
    window.location.href = link;
}
</script>
";
    }
}


//region save posts types
//todo save_content_attachment_ids
function save_video_post($edit_postId = null)
{
    global $form_errors;

    if (!isset($_FILES["videoFile"]) || $_FILES["videoFile"]['size'] == 0) {
        $form_errors[] = "Bitte laden Sie eine Videodatei hoch.";
        return;
    }

    global $current_user;
    if (!$edit_postId && !can_user_post_videos($current_user->ID)) {
        $form_errors[] = 'Sie haben nicht die richtige Berechtigung für diese Aktion.';
        return;
    }

    //check if there are any error in the uploaded file.
    //Files that are being "drag and dopped" onto the input field are not being filtered out by html-inputs "accept" property

    if ($_FILES["videoFile"]["error"] > 0 || ($_FILES["videoFile"]['type'] != "video/mp4" && $_FILES["videoFile"]['type'] != "video/quicktime")) {
        $form_errors[] = "Die Videodatei ist fehlerhaft. Fehlercode: " . $_FILES["videoFile"]["error"];
        return;
    } else {


        try {
            $curl_vimeo_post_request = curl_init();
            curl_setopt($curl_vimeo_post_request, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_vimeo_post_request, CURLOPT_URL, 'https://api.vimeo.com/me/videos');
            curl_setopt($curl_vimeo_post_request, CURLOPT_POST, 1);
            curl_setopt($curl_vimeo_post_request, CURLOPT_HTTPHEADER, array(
                'Authorization: bearer ' . get_vimeo_access_token(),
                'Content-Type: application/json',
                'Accept: application/vnd.vimeo.*+json;version=3.4'
            ));

            $bodyJson = '{
		      "upload": {"approach": "tus","size": "' . $_FILES['videoFile']['size'] . '"},
		      "name": "WPC-' . time() . '"}';

            curl_setopt($curl_vimeo_post_request, CURLOPT_POSTFIELDS, $bodyJson);

            $response = curl_exec($curl_vimeo_post_request);

            curl_close($curl_vimeo_post_request);

            if ($response === FALSE) {
                $form_errors[] = 'Fehler beim Upload des Videos. Fehlercode: 1';
                return;

            }

        } catch (Exception $e) {
            $form_errors[] = 'Ein unerwarteter Fehler ist aufgetreten. Fehlercode : C- ' .
                $e->getCode() . ' ' . $e->getMessage() . ', ' . E_USER_ERROR;
            return;

        }

        $responseData = json_decode($response, TRUE);
        $upload_url = $responseData['upload']['upload_link'];

        $curl_vimeo_patch_request = curl_init();

        curl_setopt($curl_vimeo_patch_request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_vimeo_patch_request, CURLOPT_URL, $upload_url);
        curl_setopt($curl_vimeo_patch_request, CURLOPT_VERBOSE, 1);
        curl_setopt($curl_vimeo_patch_request, CURLOPT_HEADER, 1);
        curl_setopt($curl_vimeo_patch_request, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl_vimeo_patch_request, CURLOPT_HTTPHEADER, array(
            'Tus-Resumable: 1.0.0',
            'Upload-Offset: 0',
            'Content-Type: application/offset+octet-stream',
            'Accept: application/vnd.vimeo.*+json;version=3.4'
        ));
        //fix parsing for large files
        curl_setopt($curl_vimeo_patch_request, CURLOPT_POSTFIELDS, file_get_contents($_FILES['videoFile']['tmp_name']));

        $patchResponse = curl_exec($curl_vimeo_patch_request);

        curl_close($curl_vimeo_patch_request);

        //the video is uploaded (conversions still to be done by vimeo)
        $id = substr($responseData['uri'], 8);

        //check if there are any error in the uploaded file.
        $thumbnail_attachment_id = null;

        //if this is called during editpost, the thumbnail changes are already processed
        if ($edit_postId == null && isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]['size'] > 0) {
            if ($_FILES["thumbnail"]["error"] > 0 || ($_FILES["thumbnail"]['type'] != "image/png" && $_FILES["thumbnail"]['type'] != "image/jpeg")) {
                $form_errors[] = "Die Datei des Vorschaubildes ist keine Bilddatei oder fehlerhaft. Bitte verwenden Sie ausschließlich .jpg oder .png Dateien. Fehlercode: " . $_FILES["thumbnail"]["error"];
                return;
            } else {
                correctImageOrientation($_FILES['thumbnail']['tmp_name']);
                $thumbnail_attachment_id = upload("thumbnail", 0);
            }
        }

        $documents_attachment_ids = saveDocuments(0);
        //todo sanitize
        $GLOBALS['post']['content_attachment_ids'] = $_POST['content_attachment_ids'];
        $GLOBALS['post']['documents_attachment_ids'] = $documents_attachment_ids;
        $GLOBALS['post']['post_later'] = sanitize_text_field($_POST['post_later']);
        $GLOBALS['post']['post_later_timeStr'] = sanitize_text_field($_POST['post_later_date']) . " " . sanitize_text_field($_POST['post_later_time']);

        //Vimeo is taking some time after the upload for conversions of the Video file
        //the posting of the Wordpress Post can only happen after that and is handled via a wp_cron in functions.php
        save_Video_Post_Delayed($id, $GLOBALS['post'], $thumbnail_attachment_id);

        if ($patchResponse == false) {
            $form_errors[] = 'Fehler beim Videoupload: Der Upload wurde abgelehnt.';
            return;
        }

        $GLOBALS['post_url'] = "/";

        if ($edit_postId) {

            $GLOBALS['confirm_heading'] = 'Video wird konvertiert';
            $GLOBALS['confirm_message'] = 'Das Video wird konvertiert, das kann je nach Länge des Videos einige Minuten dauern.
             Danach wird das Video im Beitrag entsprechend ersetzt.';
        }
    }

}


function save_article_post()
{

    global $form_errors;

    $postId = wp_insert_post($GLOBALS['post']);
    if ($postId > 0) {
        __update_post_meta($postId, 'media_type', 'article');
        savePostThumbnail($postId);

        link_attachmentIds_to_post($postId);
        saveDocuments($postId);

        handle_post_later($postId);

        $GLOBALS['post_url'] = get_permalink($postId);

    } else {
        $form_errors[] = 'Fehler beim Erstellen des Beitrags.';
    }
}


function save_audio_post($edit_postId = null)
{

    global $form_errors;
    //check if there are any error in the uploaded file.
    //Files that are beeing "drag and dopped" onto the input field are not beeing filtered out by html-inputs "accept" property
    if ($_FILES["audio"] == null || $_FILES["audio"]['size'] == 0 || $_FILES["audio"]["error"] > 0 ||
        ($_FILES["audio"]['type'] != "audio/mp3" && $_FILES["audio"]['type'] != "audio/mpeg")) {
        $form_errors[] = "Sie haben keine Audio Datei hochgeladen oder sie ist fehlerhaft. Fehlercode:" . $_FILES["audio"]['size'] . '/' . $_FILES["audio"]["error"] . '/' . $_FILES["audio"]['type'];
    } else {

        if (!$edit_postId) {
            $postId = wp_insert_post($GLOBALS['post']);
        } else {
            $postId = $edit_postId;
        }

        $attachment_id = upload('audio', $postId);

        if (is_wp_error($attachment_id)) {
            // There was an error uploading the audio.
            $form_errors[] = 'Fehler beim Dateiupload.';
        } else {
            if ($postId > 0) {

                __update_post_meta($postId, 'audio_attachment_id', $attachment_id);

                __update_post_meta($postId, 'media_type', 'audio');
                __update_post_meta($postId, 'media_info', "Dauer: " . $GLOBALS['post']['duration'] . " |");

                link_attachmentIds_to_post($postId);

                if ($edit_postId == null) {
                    savePostThumbnail($postId);
                    $GLOBALS['post_url'] = get_permalink($postId);
                }
                handle_post_later($postId);

            } else {
                $form_errors[] = 'Fehler beim Erstellen des Beitrags.';
            }
        }


    }
}


function save_gallery_post($edit_postId = null)
{

    sort_File_upload('gallery');

    global $form_errors;

    //check if there are any error in the uploaded file.
    //Files that are beeing "drag and dopped" onto the input field are not beeing filtered out by html-inputs "accept" property

    //check if files were uploaded
    if ($_FILES["gallery"]['error'][0] != 4) {

        $errorInFiles = false;

        foreach ($_FILES["gallery"]['type'] as $gFile) {
            if ($gFile != 'image/jpeg' && $gFile != 'image/heic' && $gFile != 'image/png' && $gFile != '' && $gFile != 'preload') {
                $errorInFiles = true;
            }
        }

        foreach ($_FILES["gallery"]['error'] as $gFile) {
            if ($gFile > 0 && $gFile != 4) {
                $errorInFiles = true;
            }
        }

        if ($errorInFiles) {
            //todo visual error management
            $form_errors[] = "Die hochgeladenen Dateien der Galerie sind nicht im richtigen Format. Bitte verwenden Sie nur .jpeg, .png oder .heic Dateien.";
            return;
        }

    }

    if (!$edit_postId) {
        $postId = wp_insert_post($GLOBALS['post']);
    } else {
        $postId = $edit_postId;
    }
    link_attachmentIds_to_post($postId);
    saveDocuments($postId);

    $gallery_attachment_ids = array();


    $files = $_FILES["gallery"];
    foreach ($files['name'] as $key => $value) {
        if ($files['type'][$key] == 'preload') {
            $gallery_attachment_ids [] = $files['name'][$key];
            continue;
        }
        if ($files['name'][$key]) {
            $file = array(
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            );
            $_FILES["galleryHelper"] = $file;
            correctImageOrientation($file['tmp_name']);
            $newupload = upload("galleryHelper", $postId);
            if (is_wp_error($newupload)) {
                $form_errors[] = 'Fehler beim Upload, versuchen Sie es erneut.';
                return;
            }

            $gallery_attachment_ids [] = $newupload;
        }
    }

    /*
     *
        if ($edit_postId) {
            foreach ($_POST['gallery-existing-images'] as $field) {
                $gallery_attachment_ids[] = sanitize_text_field($field);
            }
        }*/


    if ($postId > 0) {
        $firstPictureId = $gallery_attachment_ids[0];

        __update_post_meta($postId, 'gallery_attachment_id', json_encode($gallery_attachment_ids));
        __update_post_meta($postId, 'media_type', 'gallery');
        __update_post_meta($postId, 'media_info', "" . count($gallery_attachment_ids) . " Bilder |");
        if ($edit_postId == null) {
            savePostThumbnail($postId, $firstPictureId);
        }

        handle_post_later($postId);

        $GLOBALS['post_url'] = get_permalink($postId);

    } else {
        $form_errors[] = 'Fehler beim Erstellen des Beitrags ' . $postId . '.';
    }

}

function save_prize_draw_post($edit_postId = null)
{
    global $form_errors;
    global $current_user;

    if ($edit_postId) {
        delete_post_meta($edit_postId, 'failed_activation');
    } else if (!can_user_post_prize_draws($current_user->ID)) {
        $form_errors[] = 'Sie haben nicht die richtige Berechtigung für diese Aktion.';
        return;
    }

    $timestampOffset = get_timestamp_offset();

    //will be true if at least one of the prize draws is active right from the start
    $isActive = array();

    $mail = array();
    $event_type = array();
    $event_name = array();
    $event_location = array();
    $event_start_date = array();
    $event_start_time = array();
    $number_of_tickets = array();
    $amount_of_tickets = array();
    $from_timestamp = 0;
    $from_timeStr = '';
    $to_timestamp = array();
    $to_timeStr = array();
    $tax_free = $_POST['tax_free'] == 'on' ? 'true' : 'false';
    $is_event = $_POST['is_event'] == 'on' ? 'true' : 'false';

    $edit_prize_draw_visible = get_post_meta($edit_postId, 'prize_draw_visible', true) == "true";

    $edit_postId == null ? $postId = wp_insert_post($GLOBALS['post']) : $postId = $edit_postId;


    $starting_now = false;
    if ($_POST['from_datepicker'] != null && $_POST['from_datepicker'] === 'Nach Freigabe') {
        $starting_now = true;
        $from_timestamp = time();
        $from_timeStr = date_i18n('d.m.Y H:i');
    } else {
        $from_timeStr = sanitize_text_field($_POST['from_datepicker']) . " " . sanitize_text_field($_POST['from_timepicker']);
        $from_timestamp = strtotime($from_timeStr);

        if ($from_timestamp === false) {
            $form_errors[] = 'Die Zeit- oder Datumsangabe für den Beginn des Gewinnspiels sind ungültig, versuchen Sie es erneut.';
            return;
        }
        $from_timestamp -= $timestampOffset;
        if (($from_timestamp - time()) <= 0) {

            // if editing an existing prize draw that has already started, keep the date wich is already in the post
            if (!($edit_postId && $edit_prize_draw_visible)) {
                $from_timestamp = time();
                $from_timeStr = date_i18n('d.m.Y H:i');
            }
            $starting_now = true;
        }
    }

    $change_key = time();
    __update_post_meta($postId, 'prize_draw_change_key', $change_key);

    foreach ($_POST['number_of_tickets'] as $i => $tickets) {
        $number_of_tickets[$i] = sanitize_text_field($tickets);

        $amount_of_tickets[$i] = sanitize_text_field($_POST['amount_of_tickets'][$i]);
        $mail[$i] = sanitize_text_field($_POST['prize_draw_email'][$i]);
        $event_type[$i] = sanitize_text_field($_POST['event_type'][$i]);
        $event_name[$i] = sanitize_text_field($_POST['event_name'][$i]);
        $event_location[$i] = sanitize_text_field($_POST['event_location'][$i]);
        $event_start_date[$i] = sanitize_text_field($_POST['event_start_datepicker'][$i]);
        $event_start_time[$i] = sanitize_text_field($_POST['event_start_timepicker'][$i]);

        if ($edit_postId) {
            $custom = get_post_custom($postId);
            $old_active = unserialize($custom['prize_draw_active'][0]);
            if ($i >= count($old_active)) {
                if ($edit_prize_draw_visible) {
                    $value = 'true';
                } else {
                    $value = 'false';
                }
            } else {
                $value = $old_active[$i];
            }

            $isActive[$i] = $value;
        } else {
            $isActive[$i] = 'false';
        }


        $to_timeStr[$i] = sanitize_text_field($_POST['to_datepicker'][$i]) . " " . sanitize_text_field($_POST['to_timepicker'][$i]);
        $to_timestamp[$i] = strtotime($to_timeStr[$i]);
        $to_timestamp[$i] -= $timestampOffset;
        if ($to_timestamp[$i] === false) {
            $form_errors[] = 'Die Zeit- bzw. Datumsangabe für das Ende des Gewinnspiels sind ungültig, versuchen Sie es erneut.';
            return;
        }

        if ($to_timestamp[$i] <= time()) {
            if ($edit_postId) {
                $to_timestamp[$i] = time() + 10;
            } else {
                $form_errors[] = 'Die Zeit- bzw. Datumsangabe für das Ende des Gewinnspiels liegen in der Vergangenheit.';
                return;
            }
        }
    }


    if ($postId > 0) {

        link_attachmentIds_to_post($postId);
        saveDocuments($postId);

        __update_post_meta($postId, 'media_type', 'prize_draw');

        __update_post_meta($postId, 'prize_draw_to', $to_timeStr);

        if (!$mail) {
            global $current_user;
            $mail = $current_user->user_email;
        }

        __update_post_meta($postId, 'prize_draw_email', $mail);
        __update_post_meta($postId, 'prize_draw_event_type', $event_type);
        __update_post_meta($postId, 'prize_draw_event_name', $event_name);
        __update_post_meta($postId, 'prize_draw_event_location', $event_location);
        __update_post_meta($postId, 'prize_draw_number_of_tickets', $number_of_tickets);
        __update_post_meta($postId, 'prize_draw_amount_of_tickets', $amount_of_tickets);
        __update_post_meta($postId, 'prize_draw_event_start_date', $event_start_date);
        __update_post_meta($postId, 'prize_draw_event_start_time', $event_start_time);
        __update_post_meta($postId, 'prize_draw_tax_free', $tax_free);
        __update_post_meta($postId, 'prize_draw_is_event', $is_event);
        __update_post_meta($postId, 'prize_draw_active', $isActive);


        $template_thumbnail = sanitize_text_field($_POST["template_thumbnail"]);
        savePostThumbnail($postId, $template_thumbnail, true);

    } else {
        $form_errors[] = 'Fehler beim Erstellen des Beitrags.';
        return;
    }

    $edit_prize_draw_activated = get_post_meta($edit_postId, 'post_later_activated')[0] == 'true';

    // if not active/activated prize draw is not supposed to start now
    if ($starting_now && !$edit_prize_draw_visible && !$edit_prize_draw_activated) {
        __update_post_meta($postId, 'failed_activation', 'true');
    } else {
        start_prize_draw_delayed($postId, $from_timestamp, $change_key);
    }

    foreach ($_POST['number_of_tickets'] as $i => $tickets) {
        end_prize_draw_delayed($postId, $i, $to_timestamp[$i], $change_key);
    }

    $GLOBALS['post_url'] = get_permalink($postId);

    __update_post_meta($postId, 'prize_draw_from', $from_timeStr);

}

//endregion

//region File upload & Thubnails
function upload($file_handler, $post_id)
{
    // check to make sure its a successful upload
    if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');

    $attach_id = media_handle_upload($file_handler, $post_id);
    return $attach_id;
}


function saveDocuments($postId)
{
    global $form_errors;

    $document_attachment_ids = array();
    $document_count = 0;

    sort_File_upload('document');

    $files = $_FILES["document"];

    foreach ($files['name'] as $key => $value) {

        if ($files['type'][$key] == 'preload') {
            $document_attachment_ids [] = $files['name'][$key];
            continue;
        }

        if ($files['name'][$key]) {
            $file = array(
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            );
            $_FILES["documentHelper"] = $file;

            if (!isset($_FILES["documentHelper"]) || $_FILES["documentHelper"]['size'] == 0) {
                $form_errors[] = "Achtung, Leerer Anhang.";
                return false;
            } else if ($_FILES["documentHelper"]["error"] > 0 || $_FILES["documentHelper"]['type'] != "application/pdf") {
                $form_errors[] = "Es ist ein Fehler beim Hochladen des Anhangs aufgetreten. Fehlercode: " . $_FILES["documentHelper"]["error"];
                return false;
            }

            $newupload = upload("documentHelper", $postId);
            if (is_wp_error($newupload) || is_null($newupload)) {
                $form_errors[] = 'Fehler beim Upload der Anhänge, versuchen Sie es erneut.';
                return false;
            }

            $document_attachment_ids [] = $newupload;
            $document_count++;
        }
    }

    foreach ($_POST['existing-documents'] as $field) {
        $document_attachment_ids[] = sanitize_text_field($field);
        $document_count++;
    }

    if ($GLOBALS['admin_activation_edit']) {

        if ($document_attachment_ids != json_decode(get_post_meta($postId, 'documents_attachment_id', true))) {
            $meta_field_name = 'aae_documents_attachment_id';
        } else {
            delete_post_meta($postId, 'aae_documents_attachment_id');
            return false;
        }
    } else {
        $meta_field_name = 'documents_attachment_id';
    }

    if ($postId != 0) {
        __update_post_meta($postId, $meta_field_name, json_encode($document_attachment_ids));
    } else {
        return json_encode($document_attachment_ids);
    }
    return true;
}

function correctImageOrientation($filename)
{
    if (function_exists('exif_read_data')) {
        $exif = exif_read_data($filename);
        //d($exif);
        if ($exif && isset($exif['Orientation'])) {
            $orientation = $exif['Orientation'];
            if ($orientation != 1) {
                $img = imagecreatefromjpeg($filename);
                $deg = 0;
                //d($orientation);
                switch ($orientation) {
                    case 3:
                        $deg = 180;
                        break;
                    case 6:
                        $deg = 270;
                        break;
                    case 8:
                        $deg = 90;
                        break;
                }
                if ($deg) {
                    $img = imagerotate($img, $deg, 0);
                }
                // then rewrite the rotated image back to the disk as $filename
                imagejpeg($img, $filename, 95);
            } // if there is some rotation necessary
        } // if have the exif orientation info
    } // if function exists
}

function savePostThumbnail($postId, $thumbnailId = null, $preventDefault = false)
{
    global $form_errors;


    if (!isset($_FILES["thumbnail"]) || $_FILES["thumbnail"]['size'] == 0) {
        if ($thumbnailId != null) {
            return set_post_thumbnail($postId, $thumbnailId);
        } else if (!$preventDefault) {
            return save_default_Thumbnail($postId);
        }
    } else if ($_FILES["thumbnail"]["error"] > 0 || ($_FILES["thumbnail"]['type'] != "image/png" && $_FILES["thumbnail"]['type'] != "image/jpeg")) {
        $form_errors[] = "Die Datei des Vorschaubildes ist keine Bilddatei oder fehlerhaft. Bitte verwenden Sie ausschließlich .jpg oder .png Dateien. Fehlercode: " . $_FILES["thumbnail"]["error"];
        return false;

    } else {
        correctImageOrientation($_FILES['thumbnail']['tmp_name']);
        $attachment_id = upload("thumbnail", $postId);

        if (is_wp_error($attachment_id)) {
            // There was an error uploading the image.
            $form_errors[] = "Fehler bei Hochladen des Vorschaubildes.";
            return false;
        } else {
            // The image was uploaded successfully!
            return set_post_thumbnail($postId, $attachment_id);
        }
    }
}

function save_default_Thumbnail($postId)
{
    return set_post_thumbnail($postId, $GLOBALS['fallback_thumbnail_media_id']);
}

//endregion


genesis();

