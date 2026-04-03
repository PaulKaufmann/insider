<?php


//Video Posting
function save_Video_Post_Delayed($p1, $p2, $p3)
{
    $args = array($p1, $p2, $p3);
    // action hook which will be executed after a minute
    wp_schedule_single_event(time() + 60, 'hook_for_delayed_video_posting', $args);
}


add_action('hook_for_delayed_video_posting', 'post_Video_With_Thumbnail', 10, 3);
// Post Video after video has fully uploaded and attach thumnail if none is specified in $post from vimeo, if video is not ready yet, wait again
function post_Video_With_Thumbnail($id, $post, $thumbnail_attachment_id)
{
    //vimeo api call
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_URL, 'https://api.vimeo.com/videos/' . $id);
    curl_setopt($handle, CURLOPT_HTTPGET, 1);
    curl_setopt($handle, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . get_vimeo_access_token(),
        'Accept: application/vnd.vimeo.*+json;version=3.4'
    ));

    $response = curl_exec($handle);
    curl_close($handle);

    // Decode the response to get the thumbnailurl
    $responseData = json_decode($response, TRUE);
    $thumbnailPostUrl = $responseData['pictures']['sizes'][3]['link'];

    if ($responseData['transcode']['status'] !== 'complete' || $responseData['pictures']['type'] === 'default') {
        // wait again
        $args = array($id, $post, $thumbnail_attachment_id);
        wp_schedule_single_event(time() + 60, 'hook_for_delayed_video_posting', $args);
        return;
    }

    //if this is called as a result of post-editing, the $post variable has an Id
    if (isset($post['ID'])) {
        $postId = $post['ID'];
    } else {
        $postId = wp_insert_post($post);
    }

    if ($postId > 0) {
        __update_post_meta($postId, 'documents_attachment_id', $post['documents_attachment_ids']);

        link_attachmentIds_to_post($postId, $post['content_attachment_ids']);

        $url_components = parse_url($responseData['player_embed_url']);

        __update_post_meta($postId, 'videoId', $id . '?' . $url_components['query']);
        __update_post_meta($postId, 'media_type', 'video');
        __update_post_meta($postId, 'media_info', "Dauer: " . format_secconds($responseData['duration']) . " |");

        handle_post_later($postId, $post['post_later'], $post['post_later_timeStr']);

        if ($thumbnail_attachment_id == null) {
            if (!isset($post['use_video_thumbnail']) || isset($post['use_video_thumbnail']) && $post['use_video_thumbnail']) {
                $thumbnailUrl = substr($thumbnailPostUrl, 0, strpos($thumbnailPostUrl, "?")) . ".jpg";
                Generate_Featured_Image($thumbnailUrl, $postId);
            }
        } else {
            wp_update_post(array('ID' => $thumbnail_attachment_id, 'post_parent' => $postId));
            set_post_thumbnail($postId, $thumbnail_attachment_id);
        }
    }
}

//Prize_draw_posting

//region prize-draws
/**
 * Schedules hook to start the prize draw with the given ID at the given timestamp
 *
 * @param string $id The ID of the prize draw that is supposed to be started
 * @param int $timestamp The timestamp when the prize draw is supposed to start
 */
function start_prize_draw_delayed($id, $timestamp, $changekey)
{
    // after editing there can be multiple of these calls queued
    // to respond only to the right one, the timestamp of scheduling is passed
    // and compared to the last-edited timestamp of the post when resolving
    $args = array($id, $changekey);
    // action hook which will be executed when the timestamp is reached
    wp_schedule_single_event($timestamp, 'hook_for_start_prize_draw_delayed', $args);
}

add_action('hook_for_start_prize_draw_delayed', 'start_prize_draw', 10, 2);
/**
 * Activates the prize draw with the given ID (it then will be visible
 * on home and search for normal users) and notifies Users via e-mail and push
 *
 * @param string $id The ID of the prize draw that is supposed to be started
 * @param int $change_key The change key of the call which is the timestamp of scheduling
 * @param null $title
 * @param null $excerpt
 */
function start_prize_draw($id, $change_key, $title = null, $excerpt = null)
{

    // ___ termination conditions

    if (get_post_status($id) === 'trash') {
        return;
    }

    if (metadata_exists('post', $id, 'prize_draw_change_key') &&
        get_post_meta($id, 'prize_draw_change_key')[0] != $change_key) {
        // this is an old trigger that is invalid
        // the post has been edited in the meantime (threshold of 2 secs)
        return;
    }

    //todo how do we handle prize_draws
    if (admin_activation_needed($id) && (!metadata_exists('post', $id, 'post_later_activated') ||
            get_post_meta($id, 'post_later_activated')[0] != 'true')) {
        //this prize_draw has not been activated
        __update_post_meta($id, 'failed_activation', "true");
        delete_post_meta($id, 'post_later_time');
        return;
    }

    $custom = get_post_custom($id);
    $ended = unserialize($custom['ended'][0]);
    $active = unserialize($custom['prize_draw_active'][0]);
    $activated_at_least_one = false;

    // ___ actual Activation


    foreach ($active as $index => $a) {
        if ($active[$index] == 'true' || $ended[$index] == 'true') {
            continue;
        }
        $activated_at_least_one = true;
        $active[$index] = 'true';
    }

    if (!$activated_at_least_one) {
        return;
    }

    __update_post_meta($id, 'prize_draw_active', $active);
    wp_update_post(array('ID' => $id, 'post_status' => 'publish'));

    __update_post_meta($id, 'prize_draw_visible', "true");
    notify_users($id);
}

/**
 * Schedules hook to end the prize draw with the given ID at the given timestamp
 *
 * @param string $id The ID of the prize draw that is supposed to be ended
 * @param int $timestamp The timestamp when the prize draw is supposed to end
 */
function end_prize_draw_delayed($id, $index, $timestamp, $change_key)
{
    $args = array($id, $index, $change_key);
    // action hook which will be executed when the timestamp is reached
    wp_schedule_single_event($timestamp, 'hook_for_end_prize_draw_delayed', $args);
}

add_action('hook_for_end_prize_draw_delayed', 'end_prize_draw', 10, 3);
/**
 * Deactivates the prize draw with the given ID (it will not be visible on home and search for normal users anymore)
 * and determines a winner. Winner and organizer get e-mails about the result
 *
 * @param string $id The ID of the prize draw that is supposed to be ended
 * @param int $change_key The change key of the call, meaning the timestamp of scheduling
 */
function end_prize_draw($id, $index, $change_key)
{
    if (metadata_exists('post', $id, 'prize_draw_change_key') &&
        get_post_meta($id, 'prize_draw_change_key')[0] != $change_key) {
        //this is an old trigger that is invalid because the post has been edited in the past (threshold of 2 secs)
        return;
    }

    if (get_post_status($id) !== 'publish') {
        return;
    }

    $custom = get_post_custom($id);
    $ended = unserialize($custom['ended'][0]);
    $active = unserialize($custom['prize_draw_active'][0]);
    if ($ended[$index] == 'true' || $active[$index] == 'false') {
        //this prize_draw has already ended so it is not supposed to end again
        return;
    }


    $ended[$index] = 'true';
    $active[$index] = 'false';
    __update_post_meta($id, 'ended', $ended);
    __update_post_meta($id, 'prize_draw_active', $active);


    $at_least_one_active = false;

    foreach ($active as $i => $a) {
        if ($active[$i] == 'true' && $ended[$i] != 'true') {
            $at_least_one_active = true;
        }
        if (is_null($ended[$i])) {
            $ended[$i] = 'false';
        }
    }

    if (!$at_least_one_active) {
        __update_post_meta($id, 'prize_draw_visible', "false");
    }

    $participants = json_decode($custom['prize_draw_participants'][0], true)[$index];

    $is_event = $custom['prize_draw_is_event'][0] == "true";
    $event_type = unserialize($custom['prize_draw_event_type'][0])[$index];
    $event_name = unserialize($custom['prize_draw_event_name'][0])[$index];

    $event_start_date = unserialize($custom['prize_draw_event_start_date'][0])[$index];
    $event_start_time = unserialize($custom['prize_draw_event_start_time'][0])[$index];
    if ($is_event) {
        $event_time = " am $event_start_date um $event_start_time";
    } else {
        $event_time = "";
    }

    $number_of_tickets = unserialize($custom['prize_draw_number_of_tickets'][0])[$index];
    $number_of_tickets_2 = unserialize($custom['prize_draw_amount_of_tickets'][0])[$index];

    $organizerEmail = unserialize($custom['prize_draw_email'][0])[$index];
    if (empty($organizerEmail) || !filter_var($organizerEmail, FILTER_VALIDATE_EMAIL)) {
        $organizerEmail = get_user_by('ID', get_post($id)->post_author)->user_email;
    }

    $title = get_the_title($id);
    $title = html_entity_decode($title);


    $subj = sprintf($GLOBALS['Strings']['prize_draw_email_subject'], $title);


    $number_of_participants = count($participants);
    if ($number_of_participants === 0) {

        $message = sprintf($GLOBALS['Strings']['prize_draw_organizer_email_message_no_participants'], $title, $event_type, $event_name, $event_time);

        spk_sendEmail($organizerEmail, $message, $subj);
        return;
    }

    if ($number_of_tickets >= $number_of_participants) {
        $winners = $participants;
    } else {
        $winners = array();
        for ($i = $number_of_tickets; $i > 0; $i--) {
            $winnerePos = wp_rand(0, (count($participants) - 1));
            $winners[] = $participants[$winnerePos];
            array_splice($participants, $winnerePos, 1);
        }
    }

    $winnerstring = '';
    foreach ($winners as $winner) {

        $winner_name = $winner['id'];
        //todo adress -> address
        $internal_mail = $winner['adress'];
        $winner = get_user_by("login", $winner_name);
        if ($winner) {
            $userEmail = $winner->user_email;
            $firstname = $winner->first_name;
            $surname = $winner->last_name;

            if ($is_event) {
                $message = sprintf($GLOBALS['Strings']['prize_draw_winner_email_message_event'], $firstname, $surname, $number_of_tickets_2, $event_type, $event_name, $event_time);
            } else {
                $message = sprintf($GLOBALS['Strings']['prize_draw_winner_email_message_no_event'], $firstname, $surname, $number_of_tickets_2, $event_name);
            }

            //used later in the organizer E-Mail
            $winnerstring .= "Personal-Nummer: $winner_name <br>
            Name: $firstname $surname <br>
            E-Mail: $userEmail <br>";

            if ($internal_mail) {
                $winnerstring .= "Hauspost: $internal_mail <br>";
            }
            $winnerstring .= "<br>";
            spk_sendEmail($userEmail, $message, $subj);


            $custom_mail = get_user_meta($winner->ID, 'notifications_custom_email', true);
            if ($custom_mail != '') {
                spk_sendEmail($custom_mail, $message, $subj);
            }

        } else {
            $winnerstring .= "Personal-Nummer: $winner_name <br>
        Unbekannter Nutzer <br><br>";
        }
    }

    if (sizeof($winners) > 1) {
        $winners_are = "die Gewinner sind:<br><br>";
    } else {
        $winners_are = "der Gewinner ist:<br><br>";
    }

    if ($is_event) {
        $message = sprintf($GLOBALS['Strings']['prize_draw_organizer_email_message_event'], $title, $event_type, $event_name, $event_time, $winners_are);
    } else {
        $message = sprintf($GLOBALS['Strings']['prize_draw_organizer_email_message_no_event'], $title, $event_name, $winners_are);
    }


    $message .= $winnerstring;

    $losers = $number_of_participants - sizeof($winners);
    $losers = $losers > 0 ? $losers : 0;

    $message .= "<br>Teilnehmer gesamt: $number_of_participants <br>Es haben folgende " . $losers . " Personen teilgenommen, die nicht gewonnen haben:";

    if ($losers > 0) {
        foreach ($participants as $participant) {

            $tmp_user = get_user_by("login", $participant['id']);
            if ($tmp_user) {
                $firstname = $tmp_user->first_name;
                $surname = $tmp_user->last_name;
            } else {
                $firstname = "Unbekannter Nutzer";
                $surname = "P-NR: " . $participant['id'];
            }
            $message .= "<br> - $firstname $surname (" . $participant['id'] . ")";
        }
    }
    $message .= "<br>";
    spk_sendEmail($organizerEmail, $message, $subj);
}

//endregion

function link_attachmentIds_to_post($postId, $attach_ids = null)
{
    // if called from content editor attach_ids -> string : "1,2,3,"
    // if called from gallery editor attach_ids ->  array : [1,2,3]
    if (!is_array($attach_ids)) {
        $attachment_ids = $attach_ids ? $attach_ids : $_POST['content_attachment_ids'];
        $attachment_ids = rtrim($attachment_ids, ", ");
        $attachment_ids = explode(', ', $attachment_ids);
    } else {
        $attachment_ids = [];
    }

    $args = array(
        'post_parent' => $postId
    );


    foreach ($attachment_ids as $a_id) {
        $args['ID'] = $a_id;
        wp_update_post($args);
    }

}

//sets thumbnail of given Post
function Generate_Featured_Image($image_url, $post_id)
{
    /*$upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    }
    else{
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    file_put_contents($file, $image_data);*/

    $attach_id = media_sideload_image($image_url, $post_id, null, 'id');

    //$attach_data = wp_generate_attachment_metadata($attach_id, $file);
    //wp_update_attachment_metadata($attach_id, $attach_data);
    $res2 = set_post_thumbnail($post_id, $attach_id);
    return $res2;
}


function insider_upload_file($file, $post_id = -1, $set_as_featured = false)
{
    $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));

    $wp_filetype = wp_check_filetype(basename($upload['file']), null);

    $wp_upload_dir = wp_upload_dir();

    $attachment = array(
        'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path($upload['file']),
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id);

    //todo do i need to import image.php again?
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    if ($set_as_featured) {
        update_post_meta($post_id, '_thumbnail_id', $attach_id);
    }

    $attachment['attachment_id'] = $attach_id;
    return $attachment;
}


add_filter('wp_get_attachment_url', function ($url) {
    if (is_ssl())
        $url = str_replace('http://', 'https://', $url);
    return $url;
});


function add_cron_post_later($id, $timestamp, $change_key)
{
    // after editing there can be multiple of these calls queued
    // to respond only to the right one, the timestamp of scheduling is passed
    // and compared to the last-edited timestamp of the post when resolving
    $args = array($id, $change_key);
    // action hook which will be executed when the timestamp is reached

    wp_schedule_single_event($timestamp, 'hook_for_post_later', $args);
}


add_action('hook_for_post_later', 'post_later', 10, 2);

function post_later($id, $change_key)
{
    if (get_post_status($id) === 'trash') {
        return;
    }

    if (metadata_exists('post', $id, 'post_later_change_key') &&
        get_post_meta($id, 'post_later_change_key')[0] != $change_key) {
        // this is an old trigger that is invalid
        // the post has been edited in the meantime
        return;
    }

    if (!metadata_exists('post', $id, 'post_later_activated') ||
        get_post_meta($id, 'post_later_activated')[0] != 'true') {
        // the post has not been activated by an admin
        delete_post_meta($id, 'post_later_time');
        return;
    }

    wp_update_post(array('ID' => $id, 'post_status' => 'publish'));
    notify_users($id);
}


function handle_post_later($postId, $post_later = null, $post_later_timeStr = null)
{
    //the implicit parameters are used in video posting

    if (is_null($post_later)) {
        $post_later = sanitize_text_field($_POST['post_later']);
    }

    if ($post_later == 'on') {

        $timestampOffset = get_timestamp_offset();

        if (is_null($post_later_timeStr)) {
            $post_later_timeStr = sanitize_text_field($_POST['post_later_date']) . " " . sanitize_text_field($_POST['post_later_time']);
        }

        $post_later_timestamp = strtotime($post_later_timeStr) - $timestampOffset;

        //todo this may be problematic with video posting
        if ($post_later_timestamp < time()) {
            global $form_notices;
            $form_notices[] = 'Sie haben einen Zeitpunkt für die terminierte Veröffentlichung gewählt, der in der Vergangenheit liegt.<br>
                    Ihr Beitrag wird nicht terminiert veröffentlicht. Über "Beitrag bearbeiten" können Sie eine neue Terminierung festlegen.';
            return false;
        }

        $change_key = time();
        __update_post_meta($postId, 'post_later_change_key', $change_key);
        __update_post_meta($postId, 'post_later_time', $post_later_timestamp + $timestampOffset);
        add_cron_post_later($postId, $post_later_timestamp, $change_key);
        return true;
    } else {
        return false;
    }
}

// insider 2.0 functionality
add_action('save_post', 'insider_save_post', 10, 1);
function insider_save_post($post_id)
{


    $parent_id = wp_is_post_revision($post_id);

    if ($parent_id) {

        $parent = get_post($parent_id);
        $custom = get_post_custom($parent->ID);
        foreach ($custom as $meta_key => $meta_value) {
            add_metadata('post', $post_id, $meta_key, json_encode($meta_value));
        }
    }
}


add_action('wp_restore_post_revision', 'insider_restore_revision', 10, 2);

function insider_restore_revision($post_id, $revision_id)
{

    $post = get_post($post_id);
    $revision = get_post($revision_id);
    $view_count = get_metadata('post', $revision->ID, 'view_count', true);

    if (false !== $view_count)
        update_post_meta($post_id, 'view_count', $view_count);
    else
        delete_post_meta($post_id, 'view_count');

}

add_filter('_wp_post_revision_fields', 'insider_revision_fields', 10, 2);

function insider_revision_fields($fields, $post)
{
    $custom = get_post_custom($post->ID);
    foreach ($custom as $meta_key => $meta_value) {
        $fields[$meta_key] = $meta_key;
    }
    $fields['aae_documents_attachment_id'] = 'aae_documents_attachment_id';
    $fields['aae_post_excerpt'] = 'aae_post_excerpt';
    $fields['aae_post_content'] = 'aae_post_content';

    return $fields;

}


add_filter('_wp_post_revision_field', 'insider_revision_field', 10, 2);
function insider_revision_field($value, $field)
{

    global $revision;
    return get_metadata('post', $revision->ID, $field, true);

}

