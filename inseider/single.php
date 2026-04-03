<?php
/**
 * This file adds the Single Post Template to any Genesis child theme.
 *
 * @author Brad Dalton
 * @link https://wpsites.net/web-design/basic-single-post-template-file-for-genesis-beginners/
 */

add_action('get_header', 'handle_viewing_restrictions');
$imageDir = get_Server_Image_Dir();
$prize_draw_participation_status = array();
$custom = get_post_custom();

add_action('genesis_before_header', 'renew_wp_cookie', 35);

remove_action('genesis_entry_header', 'genesis_do_post_title');
remove_action('genesis_entry_content', 'genesis_do_post_content');

add_filter('body_class', 'sp_body_class');
function sp_body_class($classes)
{
    $media_type = get_post_custom()['media_type'][0];
    if ($media_type === 'prize_draw') {
        $classes[] = $media_type;
    }
    return $classes;
}

add_action('genesis_before_content_sidebar_wrap', 'gallery_fullscreen_view');
function gallery_fullscreen_view()
{
    if (get_post_custom()['media_type'][0] !== 'gallery') {
        return;
    }

    $backButton = "<div class='gallery-top-bar'><p id='gallery-back-Button' onclick='minimizeImage()'>" .
        file_get_contents($GLOBALS['imageDir'] . "small_back_icon.svg") . "zurück</p> </div>";


    echo "<div id=\"gallery-fs-container\" style=\"display:none\">" . $backButton . "
  <button id=\"gallery-previous-button-landscape\"  class=\"custom_hover_button\" onclick=\"showPreviousImage()\">" .
        file_get_contents($GLOBALS['imageDir'] . "small_back_icon.svg") . "</button>
  <figure><img id=\"gallery-fs-img\"></figure>
  <button id=\"gallery-next-button-landscape\" class=\"custom_hover_button\" onclick=\"showNextImage()\">" .
        file_get_contents($GLOBALS['imageDir'] . "small_back_icon.svg") . "</button>
  
  <button id=\"gallery-previous-button-portrait\" class=\"custom_hover_button\" onclick=\"showPreviousImage()\">" .
        file_get_contents($GLOBALS['imageDir'] . "small_back_icon.svg") . " <span>vorheriges</span></button>
  <button id=\"gallery-next-button-portrait\" class=\"custom_hover_button\" onclick=\"showNextImage()\"><span>nächstes</span> "
        . file_get_contents($GLOBALS['imageDir'] . "small_back_icon.svg") . "</button>
</div>";

}


function viewCount()
{
    global $custom;
    $view_count = $custom['view_count'][0] ?? 0;
    if (!session_id()) {
        session_start();
    }
    if (!isset($_SESSION['lastpage']) || $_SESSION['lastpage'] != get_the_ID()) {
        //the last page was not the same as this one -> view will be counted
        __update_post_meta(get_the_ID(), 'view_count', ++$view_count);
    }
    $_SESSION['lastpage'] = get_the_ID();
}

add_action('get_header', 'publish_post_if_submitted');
function publish_post_if_submitted()
{
    global $current_user;
    global $NOTIFICATION_SETTINGS;
    if (can_user_activate_post(get_the_ID(), $current_user->ID) && isset($_POST['publish_this_post'])) {
        $post_later_time = get_post_custom()['post_later_time'][0];
        $media_type = get_post_custom()['media_type'][0];

        $pns = get_notification_settings(get_the_category()[0]->term_id);

        $failed_activation = get_post_custom()['failed_activation'][0];

        $notify = false;
        switch ($pns) {
            case $NOTIFICATION_SETTINGS['NEVER']:
            {
                $notify = false;
                break;
            }
            case $NOTIFICATION_SETTINGS['ALWAYS']:
            {
                $notify = true;
                break;
            }
            case $NOTIFICATION_SETTINGS['MANUAL_DEFAULT_YES']:
            case $NOTIFICATION_SETTINGS['MANUAL_DEFAULT_NO']:
            default:
            {
                $notify = $_POST['notify'] == 'on';
                break;
            }
        }

        if ($notify) {
            $notify = 'true';
        } else {
            $notify = 'false';
        }
        update_post_meta(get_the_ID(), 'notify', $notify);

        if (!$failed_activation && ($media_type == 'prize_draw' || !empty($post_later_time) && $post_later_time != 'canceled')) {
            __update_post_meta(get_the_ID(), 'post_later_activated', 'true');
            wp_safe_redirect(add_query_arg('later_activated', 'true', get_the_permalink()));
            exit;
        }

        delete_post_meta(get_the_ID(), 'failed_activation');

        wp_publish_post(get_post()->ID);

        $time = current_time('mysql');

        wp_update_post(
            array(
                'ID' => get_post()->ID, // ID of the post to update
                'post_date' => $time,
                'post_date_gmt' => get_gmt_from_date($time)
            )
        );

        if ($media_type == 'prize_draw') {

            //start all prize draws that have a starting time in the past
            $changeKey = get_post_custom()['prize_draw_change_key'][0];
            start_prize_draw(get_the_ID(), $changeKey);
        }
        notify_users(get_post()->ID, false, $current_user->user_login);

        wp_safe_redirect(add_query_arg('activated', 'true', get_the_permalink()));
        exit;
    }
}


add_action('get_header', 'handle_favorit_buttons');
function handle_favorit_buttons()
{
    global $current_user;
    if (!has_favorite_button(get_the_category()[0]->term_id)) {
        return false;
    }
    $favorite_posts = get_user_meta(get_current_user_id(), 'favorite_posts', true);
    if (isset($_POST['add_favorite'])) {

        if (empty($favorite_posts)) {
            $favorite_posts = array(get_the_ID());
        } else {
            $favorite_posts[] = get_the_ID();
        }

        update_user_meta(get_current_user_id(), 'favorite_posts', $favorite_posts);

    }
    if (isset($_POST['delete_favorite'])) {

        if (($key = array_search(get_the_ID(), $favorite_posts)) !== false) {
            unset($favorite_posts[$key]);
        }

        update_user_meta(get_current_user_id(), 'favorite_posts', $favorite_posts);
    }
}


add_action('get_header', 'publish_edit_post_if_submitted');
function publish_edit_post_if_submitted()
{
    global $current_user;
    if (can_user_activate_edit(get_the_ID(), $current_user->ID) && isset($_POST['publish_edit_this_post'])) {

        $editpost = array('ID' => get_the_ID());

        $aae_post_content = get_post_meta(get_the_ID(), 'aae_post_content')[0];
        if ($aae_post_content || $aae_post_content === "") {
            $editpost['post_content'] = $aae_post_content;
        }
        delete_post_meta(get_the_ID(), 'aae_post_content');

        $aae_post_excerpt = get_post_meta(get_the_ID(), 'aae_post_excerpt')[0];
        if ($aae_post_excerpt || $aae_post_excerpt === "") {
            $editpost['post_excerpt'] = $aae_post_excerpt;
        }
        delete_post_meta(get_the_ID(), 'aae_post_excerpt');

        $aae_documents_attachment_id = get_post_meta(get_the_ID(), 'aae_documents_attachment_id')[0];
        if ($aae_documents_attachment_id) {
            $editpost['meta_input']['documents_attachment_id'] = $aae_documents_attachment_id;
        }
        delete_post_meta(get_the_ID(), 'aae_documents_attachment_id');

        wp_update_post($editpost);
        wp_safe_redirect(add_query_arg('edited', 'true', get_the_permalink()));
        exit;
    }
}


add_action('get_header', 'request_activation_if_submitted');
function request_activation_if_submitted()
{
    global $current_user;
    if ((!admin_activation_needed(get_the_ID()) && !admin_activation_needed_for_edit(get_the_ID(), true)) ||
        !can_user_request_activate_post(get_the_ID(), $current_user->ID) ||
        !isset($_POST['request_activation_this_post'])) {
        return;
    }


    update_post_meta(get_the_ID(), 'requested_activation', 'true');

    $posting_admins = get_posting_admins();
    foreach ($posting_admins as $receiver) {
        $receiver = get_user_by('login', $receiver);

        if (!$receiver) {
            continue;
        }

        $message = sprintf($GLOBALS['Strings']['request_activation_email_message'], $current_user->user_login, $current_user->first_name, $current_user->last_name, get_the_permalink(), get_the_title());

        spk_sendEmail($receiver->user_email, $message, $GLOBALS['Strings']['request_activation_email_subject']);
    }

    wp_safe_redirect(add_query_arg('requested', 'true', get_the_permalink()));
    exit;
}

add_action('get_header', 'notify_if_submitted');
function notify_if_submitted()
{
    global $current_user;
    if (can_user_notify_manually(get_the_ID(), $current_user->ID) && isset($_POST['notify_post_submit'])) {
        notify_users(get_the_ID(), true, $current_user->user_login);
        wp_safe_redirect(add_query_arg('notified', 'true', get_the_permalink()));
        exit;
    }
}


add_action('get_header', 'cancel_post_later_if_submitted');
function cancel_post_later_if_submitted()
{
    global $current_user;
    if ((in_array('author', $current_user->roles) || in_array('contributor', $current_user->roles) || in_array('administrator', $current_user->roles)) && isset($_POST['cancel_post_later'])) {
        __update_post_meta(get_the_ID(), 'post_later_time', 'canceled');
        __update_post_meta(get_the_ID(), 'post_later_change_key', -1);
        delete_post_meta(get_the_ID(), 'post_later_activated');
        wp_safe_redirect(add_query_arg('post_later_canceled', 'true', get_the_permalink()));
        exit;
    }
}


add_action('genesis_entry_content', 'single_entry_content');
function single_entry_content()
{
    global $current_user;
    if (can_user_delete_post(get_the_ID(), $current_user->ID) && isset($_POST['delete_this_post'])) {
        if (wp_delete_post(get_post()->ID)) {
            wp_reset_postdata();
            echo '<div style="text-align: center">
                <h3>Der Beitrag wurde gelöscht</h3>
                <a href="' . home_url() . '">
                <button class="spk_wide_button">
                zur Startseite</button>
                </a></div>';
            exit;
        } else {
            echo '<div style="text-align: center"><h3>Fehler beim Löschen</h3>
                    <p>Beim Löschen ist ein Fehler aufgetreten. Der Beitrag bleibt weiterhin sichtbar.</p>
                    <a href="' . home_url() . '"><button class="spk_wide_button">zur Startseite</button></a></div>';
        }
    } else {

        // todo form management here is a mess

        $message = null;

        if (isset($_GET['activated'])) {
            $message = 'Der Beitrag wurde freigeben.';
        } else if (isset($_GET['later_activated'])) {
            $message = 'Der Beitrag wurde freigeben und wird terminiert veröffentlicht.';
        } else if (isset($_GET['requested'])) {
            $message = 'Die Freigabe wurde bei den jeweiligen Administratoren beantragt.';
        } else if (isset($_GET['notified'])) {
            $message = 'Die Mitarbeiter wurden benachrichtigt.';
        } else if (isset($_GET['edited'])) {
            $message = 'Die Änderungen im Beitrag wurden freigeben und sind nun öffentlich.';
        } else if (isset($_GET['post_later_canceled'])) {
            $message = 'Die terminierte Veröffentlichung wurde abgebrochen.';
        }


        if (!is_null($message)) {
            print_modal($message, '', 'OK');
        }


        $media_type = get_post_custom()['media_type'][0];

        if ($media_type == 'prize_draw') {
            global $prize_draw_participation_status;
            $prize_draw_participation_status = has_participated_in_prize_draw();
            if ($_POST['participate']) {
                //user wants to participate in prize draw
                participate_in_prize_draw();
            }
        }

        edit_comment_if_submitted();

        viewCount();

        echo '<base href="' . home_url() . '"><script>    
    
        
            function toggleElementVisiblity(handle,show) {
              var scroll = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
             
                if(show){
                    document.getElementById(handle).style.display = \'block\';
                }else{
                    document.getElementById(handle).style.display = \'none\';
                } 
                
                if(scroll){
                   
                    let style = window.getComputedStyle(document.getElementById(handle));
                    let position = style.getPropertyValue(\'position\');
    
                    if(position=="absolute"){
                    document.getElementById(handle).style.top = jQuery("html").scrollTop()+100+"px";
                    }else{
                        document.getElementById(handle).style.removeProperty("top");
                    }
                   
                }
            }
            
            function show_Modal(handle,show){
              var scroll = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
                toggleElementVisiblity(handle,show,scroll);
                toggleElementVisiblity("single_modal_bg",show);
                
            }
            
            </script>
            
            <div id="inseider-post-container" >' . get_post_entry() . get_media_body() . get_custom_post_signature(get_the_ID()) . '</div>';

        render_documents();

        $anchor = "<div id='Kommentare'></div>";
        echo $anchor;

    }
    padStart_js();

    echo no_comment_explanation();
}

function no_comment_explanation()
{

    $post_status = get_post_status(get_the_ID());
    if (post_has_comments(get_the_ID())) {
        return '';
    } else if ($post_status === 'publish') {
        return '<br style="clear: both"><br style="clear: both"><h2 class="discussion_heading">Bei diesem Beitrag ist die Kommentarfunktion deaktiviert.</h2>';
    } else {
        return '<br style="clear: both"><br style="clear: both"><h2 class="discussion_heading">Dieser Beitrag ist nicht öffentlich, es sind keine Kommentare erlaubt.</h2>';
    }
}

/**
 * Edits comment if edit comment form was submitted correctly by a user who is allowed to edit given comment
 * @return bool Whether or not comment was edited successfully
 *
 */
function edit_comment_if_submitted()
{
    if (isset($_POST['comment_id'])) {

        $id = sanitize_text_field($_POST['comment_id']);

        if ($id != null && current_user_can_edit_comment($id)) {
            $commentText = $_POST['comment'];
            $comment = array();
            $comment['comment_ID'] = $id;
            $comment['comment_content'] = $commentText;

            $editedOn = "" . date('d.m.Y');

            $alreadyEdited = get_comment_meta($id, 'editedOn', true);
            if ($alreadyEdited) {
                update_comment_meta($id, 'editedOn', $editedOn);
            } else {
                add_comment_meta($id, 'editedOn', $editedOn);
            }

            return 0 < wp_update_comment($comment);
        }
    }
    return false;
}

// todo this is a mess
function get_post_entry()
{
    if (!is_user_logged_in()) {
        return null;
    }

    global $current_user;
    global $custom;
    $imageDir = get_Server_Image_Dir();

    $media_type = $custom['media_type'][0] ?? null;
    $post_later_time = $custom['post_later_time'][0] ?? null;
    $post_later_activated = $custom['post_later_activated'][0] ?? null;
    $post_later_failed_activation = $custom['failed_activation'][0] ?? null;

    $prize_draw_from = $custom['prize_draw_from'][0] ?? null;


    $backButton = "<div class='post-top-bar'>";

    global $current_user;
    if (!in_array('business_partner', $current_user->roles)) {
        $backButton .= "<p id='post-back-Button'>" . file_get_contents($imageDir . "small_back_icon.svg") .
            "zurück</p>";
    }

    $backurl = "";

    if (isset($_GET['backurl'])) {
        $backurl = $_GET['backurl'];
        $_SESSION['backurl'] = $backurl;
    } else {
        $backurl = $_SESSION['backurl'] ?? "";
    }


    $backButton .= "<div class='post-top-bar'><p id='post-info'>" . get_the_date('d.m.Y') . " | " . get_the_time('H:i') . "</p> </div>";
    $script = "<script>
                
                 let pbB = document.getElementById('post-back-Button');
                 if(pbB){
                     pbB.onclick=function() {
                      history.back()
                    }
                 }
              </script>";

    if (post_has_comments(get_the_ID())) {

        $fab = '<div id="floating-container">
                  <div class="fab-button" id="write_comment_mobile">
                    ' . file_get_contents($imageDir . 'comment-icon.svg') . '
                  </div>
              </div>
              <script>
                    document.getElementById("floating-container").onclick= function() {
                        open_mobile_fs_comment();
                    };
                    
              function close_mobile_fs_comment() {
           
                      if (window.matchMedia(\'(max-width: 960px)\').matches){
                            document.getElementById("floating-container").style.display="block";
                            document.getElementById("respond").style.display="none";
                            document.getElementById("comment").blur();
                            document.getElementById("submit").style.display = \'block\';
                            document.getElementById("submit_change_comment").style.display = \'none\';
                      }
                      
                    document.getElementById("comment").value = " ";
                    document.getElementById("comment").blur();
              }
              
              function open_mobile_fs_comment() {
                    document.getElementById("floating-container").style.display="none";
                    document.getElementById("respond").style.display="block";
                    document.getElementById("comment").focus();
              }
              
            </script>';
    } else {
        $fab = '';
    }

    $customEntry = "<h2>Dieser Beitrag ist beschädigt.</h2>";

    switch ($media_type) {
        case ('video'):
            $customEntry = render_video_entry();
            break;

        case ('article'):
            $customEntry = render_article_entry();
            break;

        case ('audio'):
            $customEntry = render_audio_entry();
            break;

        case ('gallery'):
            $customEntry = render_gallery_entry();
            break;

        case ('prize_draw'):
            $customEntry = render_prize_draw_entry();
            break;
        default:
            $customEntry = render_custom_media_entry($media_type);

    }

    $title = '<h1 class="entry-title" itemprop="headline">' . get_the_title() . '</h1>';
    $linkbar = getLinkbar();

    // if post doesnt have an actual excerpt, get_the_excerpt returns the content
    if (has_excerpt()) {
        $subheading = "<div class=\"post-subheading\">" . get_the_excerpt() . "</div>";
    } else {
        $subheading = "<div class=\"post-subheading\"></div>";
    }

    $aae_post_excerpt = get_post_custom()['aae_post_excerpt'][0] ?? null;
    if ($aae_post_excerpt === "") {
        $aae_post_excerpt = "<i>Der Beschreibungstext wurde entfernt</i>";
    }
    if ($aae_post_excerpt &&
        (can_user_activate_edit(get_the_ID(), get_current_user_id()) ||
            can_user_edit_post(get_the_ID(), get_current_user_id())
        )) {
        $subheading .= "<div class='edit_to_activate'><strong>Zu bestätigende Änderung:</strong><br>$aae_post_excerpt</div>";
    }

    $aae_post_content = get_post_custom()['aae_post_content'][0] ?? null;
    $content = "<div><p>" . wpautop(get_the_content(), true) . "</p></div>";

    if ($aae_post_content === "") {
        $aae_post_content = "<i>Der Inhalt wurde entfernt</i>";
    }

    if ($aae_post_content &&
        (can_user_activate_edit(get_the_ID(), get_current_user_id()) ||
            can_user_edit_post(get_the_ID(), get_current_user_id())
        )) {
        $content .= "<div class='edit_to_activate'><strong>Zu bestätigende Änderung:</strong><br>$aae_post_content</div>";
    }


    $authorForms = '';

    if (can_user_delete_post(get_the_ID(), $current_user->ID)) {
        $authorForms .= '
            <button class="spk_wide_button secondary_button flex_button" onclick="show_Modal(\'shure_you_want_to_delete_Modal\',true,true)">Beitrag löschen</button>';
    }


    $all_ended = true;

    if (metadata_exists('post', get_the_ID(), 'ended')) {
        $ended_array = unserialize(get_post_custom()['ended'][0]) ?? array();
        $active_array = unserialize(get_post_custom()['prize_draw_active'][0]);

        foreach ($active_array as $i => $active) {
            if (is_null($ended_array[$i]) || !$ended_array[$i]) {
                $all_ended = false;
            }
        }
    } else {
        $all_ended = false;
    }

    if (can_user_edit_post(get_the_ID(), $current_user->ID) && !$all_ended) {
        $authorForms .= '
            <a id="edit_this_post" class="button secondary_button spk_wide_button flex_button" href="uploading?edit_post=' . get_the_ID() . '">Beitrag bearbeiten</a>';
    }

    if (can_user_notify_manually(get_the_ID(), $current_user->ID)) {
        $authorForms .= '
            <a id="notify_about_this_post" class="button secondary_button spk_wide_button flex_button" onclick="show_Modal(\'shure_you_want_to_notify_Modal\',true,true)">Benachrichtigungen verschicken</a>
            ';
    }

    if (get_post_status() == 'draft' &&
        ((!empty($post_later_time) && $post_later_time != 'canceled') ||
            $media_type == 'prize_draw' && $post_later_failed_activation != 'true')) {

        if (empty($post_later_time)) {
            $formated_post_later_time = substr_replace($prize_draw_from, ' um', 10, 0);
        } else {
            $formated_post_later_time = gmdate("d.m.Y \u\m H:i", $post_later_time);
            if (can_user_activate_post(get_the_ID(), $current_user->ID)) {
                $authorForms .= '<br><button class="spk_wide_button secondary_button flex_button" onclick="show_Modal(\'shure_you_want_to_cancel_post_later\',true,true)">Terminierung abbrechen</button>';
            }
        }

        if ($post_later_activated == 'true') {
            $post_later_info = 'Dieser Beitrag ist freigegeben und wird terminiert veröffentlicht am: ' . $formated_post_later_time . ' Uhr.';
        } else {
            $post_later_info = 'Dieser Beitrag soll terminiert veröffentlicht werden, ist aber noch nicht freigegeben.<br>
                                Sollte die Freigabe rechtzeitig erfolgen, wird der Beitrag am ' . $formated_post_later_time . ' Uhr terminiert veröffentlicht.';
        }

    } else if ($post_later_failed_activation == 'true' && get_post_status() == 'draft') {
        $post_later_info = 'Dieser Beitrag wurde bis zum festgelegten Startzeitpunkt nicht freigegeben. Wenn der Beitrag jetzt freigegeben wird, wird er sofort veröffentlicht.';
    } else {
        $post_later_info = '';
    }
    if ($post_later_info != '') {
        $post_later_info = '<p class="post_later_info">' . $post_later_info . '</p>';
    }


    if (can_user_activate_post(get_the_ID(), $current_user->ID)) {
        $authorForms .= '
            <button class="spk_wide_button flex_button" onclick="show_Modal(\'shure_you_want_to_publish_Modal\',true,true)">Beitrag freigeben</button>';
    }

    if (can_user_activate_edit(get_the_ID(), $current_user->ID)) {
        $authorForms .= '
            <button class="spk_wide_button flex_button" onclick="show_Modal(\'shure_you_want_to_publish_edit_Modal\',true,true)">Änderungen freigeben</button>';
    }


    if (can_user_request_activate_post(get_the_ID(), $current_user->ID)) {
        $authorForms .= '
            <button class="spk_wide_button flex_button" onclick="show_Modal(\'shure_you_want_to_request_activation_Modal\',true,true)">Freigabe beantragen</button>';
    }

    $infos = '';
    if (admin_activation_needed_for_edit(get_the_ID(), true)) {
        $infos = '<p class="post_later_info">Für Änderungen muss die Freigabe erneut beantragt werden.</p>';
    }

    $notified_time = get_post_custom()['notified_time'][0] ?? 0;
    if ($notified_time && (in_array('author', $current_user->roles) || in_array('contributor', $current_user->roles) || in_array('administrator', $current_user->roles))) {
        $infos .= '<p class="post_later_info">Für diesen Beitrag wurden am ' . date('d.m.Y \u\m H:i', $notified_time + get_timestamp_offset()) . ' Benachrichtigungen verschickt.</p>';
    }

    $authorForms != '' ? $authorForms = '<div class="flex_button_container mobile-not-flex flex-2-3">' . $authorForms . '</div>' : $authorForms = '';

    if (has_favorite_button(get_the_category()[0]->term_id)) {
        if (is_favorite()) {
            $favorite_button = '<form method="post" name="favorit"><input class="spk_wide_button dark_button" name="delete_favorite" type="submit" value="Favorit entfernen"></form>';
        } else {
            $favorite_button = '<form method="post" name="favorit"><input class="spk_wide_button dark_button" name="add_favorite" type="submit" value="Favorit hinzufügen"></form>';
        }
    } else {
        $favorite_button = '';
    }

    return $backButton . $script . $fab . $customEntry . $title . $subheading . $linkbar . $authorForms . $favorite_button . $post_later_info . $infos . $content;
}


function is_favorite()
{
    $favorit_posts = get_user_meta(get_current_user_id(), 'favorite_posts', true);
    if (is_array($favorit_posts)) {
        return in_array(get_the_ID(), get_user_meta(get_current_user_id(), 'favorite_posts', true));
    } else {
        return false;
    }
}


function get_notification_input()
{
    global $NOTIFICATION_SETTINGS;

    $pns = get_notification_settings(get_the_category()[0]->term_id);

    $checked = false;
    $disabled = false;
    switch ($pns) {
        case $NOTIFICATION_SETTINGS['NEVER']:
        {
            $checked = false;
            $disabled = true;
            break;
        }
        case $NOTIFICATION_SETTINGS['ALWAYS']:
        {
            $checked = true;
            $disabled = true;
            break;
        }
        case $NOTIFICATION_SETTINGS['MANUAL_DEFAULT_YES']:
        {
            $checked = true;
            $disabled = false;
            break;
        }
        case $NOTIFICATION_SETTINGS['MANUAL_DEFAULT_NO']:
        default:
        {
            $checked = false;
            $disabled = false;
            break;
        }
    }

    if ($checked) {
        $checked = 'checked';
    } else {
        $checked = '';
    }

    if ($disabled) {
        $disabled = 'disabled';
    } else {
        $disabled = '';
    }


    $input = '<div><input type="checkbox" name="notify" id="notify" ' . $checked . ' ' . $disabled . '>
            <label class="' . $disabled . '" for="notify">Benachrichtigungen per Mail und Push verschicken</label></div><br>';

    return $input;
}

add_action('genesis_after', 'render_modals');

function render_modals()
{
    global $current_user;

    echo '<div style="display: none" id="single_modal_bg"></div>';

    if (can_user_delete_post(get_the_ID(), $current_user->ID)) {
        echo '<div id="shure_you_want_to_delete_Modal" class="single_Modal delete_Modal" style="display: none">
            <h3>Sind Sie sicher, dass Sie den Beitrag: "' . get_the_title() . '" löschen möchten?</h3>
            <form id="delete_post" class="flex_button_container mobile-not-flex" name="delete_post" method="post">
            <input type="submit" class="flex_button spk_wide_button" id="delete_post_submit" name="delete_this_post" value="Beitrag für immer löschen">
            <a class="flex_button button spk_wide_button secondary_button" id="cancel_delete_post" onclick="show_Modal(\'shure_you_want_to_delete_Modal\',false)">abbrechen</a>
            </form></div>';
    }
    if (get_post_status() == 'draft') {

        if (can_user_activate_post(get_the_ID(), $current_user->ID)) {

            echo '<div id="shure_you_want_to_publish_Modal" class="single_Modal publish_Modal" style="display: none">';

            $post_later_time = get_post_custom()['post_later_time'][0];
            $failed_activation = get_post_custom()['failed_activation'][0];
            if ((get_post_custom()['media_type'][0] == 'prize_draw' || !is_null($post_later_time)) && $post_later_time != 'canceled' && $failed_activation != 'true') {
                echo '<h3>Sind Sie sicher, dass Sie den Beitrag: "' . get_the_title() . '" freigeben möchten?<br>
            Der Beitrag wird dann terminiert für alle Nutzer freigeschaltet.</h3>';
                $publish_button_text = 'Beitrag freigeben';
            } else {
                $publish_button_text = 'Beitrag freigeben';
                echo '<h3>Sind Sie sicher, dass Sie den Beitrag: "' . get_the_title() . '" für alle Nutzer freigeben möchten?</h3>';
            }

            echo '<form id="publish_post" name="publish_post" method="post">' . get_notification_input() . '
            <div class="flex_button_container mobile-not-flex" ><input type="submit" class="flex_button spk_wide_button" id="publish_post_submit" name="publish_this_post" value="' . $publish_button_text . '">
            <a class="button flex_button spk_wide_button secondary_button" id="cancel_publish_post" onclick="show_Modal(\'shure_you_want_to_publish_Modal\',false)">abbrechen</a>
            </div></form></div>';
        }

        echo '<div id="shure_you_want_to_cancel_post_later" class="single_Modal request_activitaion_Modal" style="display: none">
            <h3>Sind Sie sicher, dass Sie die terminierte Veröffentlichung für den Beitrag: "' . get_the_title() . '" abbrechen möchten?</h3>
            <form id="cancel_post_later" name="cancel_post_later" class="flex_button_container mobile-not-flex" method="post">
            <input type="submit" class="flex_button spk_wide_button" id="cancel_post_later_submit" name="cancel_post_later" value="Terminierung abbrechen">
            <a class="button flex_button spk_wide_button secondary_button" id="cancel_cancel_post_later" onclick="show_Modal(\'shure_you_want_to_cancel_post_later\',false)">abbrechen</a>
            </form></div>';
    } else if (can_user_activate_edit(get_the_ID(), $current_user->ID)) {

        echo '<div id="shure_you_want_to_publish_edit_Modal" class="single_Modal publish_Modal" style="display: none"><h3>Sind Sie sicher, dass Sie die markierten Änderungen am Beitrag: "' . get_the_title() . '" für alle Nutzer freigeben möchten?</h3><form id="publish_edit_post" name="publish_edit_post" class="flex_button_container mobile-not-flex" method="post">
            <input type="submit" class="flex_button spk_wide_button" id="publish_edit_post_submit" name="publish_edit_this_post" value="Änderungen freigeben">
            <a class="button flex_button spk_wide_button secondary_button" id="cancel_publish_edit_post" onclick="show_Modal(\'shure_you_want_to_publish_edit_Modal\',false)">abbrechen</a>
            </form></div>';
    }


    if (can_user_notify_manually(get_the_ID(), $current_user->ID)) {

        echo '<div id="shure_you_want_to_notify_Modal" class="single_Modal request_activitaion_Modal" style="display: none">
            <h3>Sind Sie sicher, dass Sie an alle Mitarbeiter eine Benachrichtigung per E-Mail und Push über den Beitrag: "' . get_the_title() . '" senden möchten?</h3>
            <form id="notify_post" name="notify_post" class="flex_button_container mobile-not-flex" method="post">
            <input type="submit" class="flex_button spk_wide_button" id="notify_post_submit" name="notify_post_submit" value="Benachrichtigungen versenden">
            <a class="button flex_button spk_wide_button secondary_button" id="cancel_notify_post" onclick="show_Modal(\'shure_you_want_to_notify_Modal\',false)">abbrechen</a>
            </form></div>';
    }

    if (can_user_request_activate_post(get_the_ID(), $current_user->ID)) {
        echo '<div id="shure_you_want_to_request_activation_Modal" class="single_Modal request_activitaion_Modal" style="display: none">
            <h3>Sind Sie sicher, dass Sie die Freigabe für den Beitrag: "' . get_the_title() . '" beantragen möchten?</h3>
            <form id="request_activation_post" name="request_activation_post" class="flex_button_container mobile-not-flex" method="post">
            <input type="submit" class="flex_button spk_wide_button" id="request_activation_submit" name="request_activation_this_post" value="Freigabe beantragen">
            <a class="button flex_button spk_wide_button secondary_button" id="cancel_request_activation_post" onclick="show_Modal(\'shure_you_want_to_request_activation_Modal\',false)">abbrechen</a>
            </form></div>';
    }
}

function getLinkbar()
{
    global $custom;
    if (post_has_comments(get_the_ID())) {
        $commentlink = '<a onclick="goToComments()" class="post-linkbar-discussion"> '
            . file_get_contents(get_Server_Image_Dir() . "comment-icon.svg") . " " .
            get_comment_count(get_the_ID())['approved'] . ' | </a><a onclick="goToComments()"> Zur Diskussion</a>';

    } else {
        $commentlink = '';
    }

    if (array_key_exists('media_info', $custom)) {
        $media_info = $custom['media_info'][0] ?? null;
    } else {
        $media_info = '';
    }

    $author_id = get_post_field('post_author', get_the_ID());

    if ($author_id && show_author_in_post(get_the_ID())) {
        $author = "Autor: " . get_user_by('ID', $author_id)->first_name . ' ' . get_user_by('ID', $author_id)->last_name . "<br>";
    } else {
        $author = "";
    }


    if (has_share_link(get_the_ID())) {
        $share_link = "<a class='post-linkbar-share' onclick='return mail_link()'>" . file_get_contents(get_Server_Image_Dir() . "share_icon.svg") . "teilen</a> | ";
    } else {
        $share_link = "";
    }

    $subheading = "<div class='post-linkbar'><p>" . $author . $media_info .
        "$share_link $commentlink</p></div>";

    $script = "<script>

                function goToComments() {
                  window.location.hash = \"Kommentare\";
                }
                
                function mail_link() {
                    window.location.href = 'mailto:?subject=" . get_the_title() . "&body=" . $GLOBALS['Strings']['share-message'] . get_permalink() . "';
                }
               </script>";

    return $subheading . $script;
}


function featured_image()
{
    if (!is_user_logged_in()) {
        return null;
    }

    $image = genesis_get_image(array( // more options here -> genesis/lib/functions/image.php
        'format' => 'html',
        'size' => 'large',// add in your image size large, medium or thumbnail - for custom see the post
        'context' => '',
        'attr' => array('class' => 'aligncenter'), // set a default WP image class
    ));
    if (!$image) {
        $image = wp_get_attachment_image($GLOBALS['fallback_thumbnail_media_id'], 'large', false, array('class' => 'aligncenter'));
    }
    return '<div class="featured-image-class">' . $image . '</div>'; // wraps the featured image in a div with css class you can control
}


function render_video_entry()
{
    global $custom;

    $videoId = $custom['videoId'][0] ?? null;

    if ($videoId > 0) {

        return '<div class="video-container">
    <iframe src="https://player.vimeo.com/video/' . $videoId . '" frameborder="0" allowfullscreen></iframe>
</div>

';

    }
    return "<h3>Das Video konnte leider nicht geladen werden</h3>";
}

function render_article_entry()
{
    return featured_image();
}


function render_audio_entry()
{
    return featured_image();
}

function render_gallery_entry()
{
    global $custom;

    if (!has_legacy_gallery()) {
        $galleryId = json_decode($custom['gallery_attachment_id'][0] ?? '[]');
        $numItems = count($galleryId);

        if ($numItems == 0) {
            return render_article_entry();
        }

        ob_start();
        ?>

        <!-- Slideshow container -->
        <br>
        <div class="slideshow-container">

            <!-- Full-width images with number and caption text -->

            <?php
            $backButton = "<div class='gallery-top-bar'><p id='gallery-back-Button' onclick='fulscreeenSlide(false)'>" .
                file_get_contents($GLOBALS['imageDir'] . "small_back_icon.svg") . "zurück</p> </div>";

            echo $backButton;

            $arrayString = "";
            $i = 0;
            foreach ($galleryId as $img) {

                $image_src = wp_get_attachment_image_src($img, 'Large');
                $width = $image_src[1];
                $height = $image_src[2];

                if ($height > $width && $height > 800) {
                    $width = round($width / $height * 800);
                    $height = 800;
                }
                echo '<div class="mySlides fade">';
                echo '<img draggable="false"  src=\'' . $image_src[0] . '\'>';

                echo '</div>';
            }
            ?>
            <!-- Next and previous buttons -->
            <a class="prev" <?php if ($numItems < 2) {
                echo 'style="display:none"';
            } ?> onclick="plusSlides(-1)">&#10094;</a>
            <a class="next" <?php if ($numItems < 2) {
                echo 'style="display:none"';
            } ?> onclick="plusSlides(1)">&#10095;</a>
            <a class="fullscreen_toggle"
               onclick="fulscreeenSlide(true)"><?php echo file_get_contents($GLOBALS['imageDir'] . "fullscreen.svg"); ?></a>
            <div class="blurry_bg"></div>
        </div>
        <br>

        <!-- The dots/circles -->
        <div style="text-align:center">
            <?php
            for ($i = 0; $i < $numItems; $i++) {

                echo '<span class="dot" onclick="currentSlide(' . $i . ')"></span>';
            }
            ?>
        </div>

        <script>
            var slideIndex = 1;
            showSlides(slideIndex);

            // Next/previous controls
            function plusSlides(n) {
                showSlides(slideIndex += n);
            }

            // Thumbnail image controls
            function currentSlide(n) {
                showSlides(slideIndex = n);
            }

            function fulscreeenSlide(activate) {
                if (activate) {
                    document.getElementsByClassName('site-inner')[0].classList.add('fullscreen');
                    document.getElementsByClassName('slideshow-container')[0].classList.add('fullscreen');
                } else {
                    document.getElementsByClassName('site-inner')[0].classList.remove('fullscreen');
                    document.getElementsByClassName('slideshow-container')[0].classList.remove('fullscreen');
                }
            }

            function resizeToMax(id) {
                var img = document.getElementById(id);
                myImage = new Image();
                myImage.src = img.src;
                if (window.innerWidth / myImage.width < window.innerHeight / myImage.height) {
                    img.style.width = "100%";
                } else {
                    img.style.height = "100%";
                }
            }

            function showSlides(n) {
                var i;
                var slides = document.getElementsByClassName("mySlides");
                var dots = document.getElementsByClassName("dot");
                if (n > slides.length) {
                    slideIndex = 1
                }
                if (n < 1) {
                    slideIndex = slides.length
                }
                for (i = 0; i < slides.length; i++) {
                    slides[i].style.display = "none";
                }
                for (i = 0; i < dots.length; i++) {
                    dots[i].className = dots[i].className.replace(" active", "");
                }
                slides[slideIndex - 1].style.display = "flex";
                dots[slideIndex - 1].className += " active";
            }
        </script>

        <?php
        return ob_get_clean();
    }

    return featured_image();
}

function render_prize_draw_entry()
{

    return featured_image();
}


function get_media_body()
{
    switch ($media_type = get_post_custom()['media_type'][0]) {

        case ('audio'):
            return render_audio_media();
            break;

        case ('gallery'):
            return render_gallery_media();
            break;
        case ('prize_draw'):
            return render_prize_draw_body();
            break;
        default:
            return render_custom_media_body($media_type);
    }
    return "";
}


function render_audio_media()
{

    global $custom;
    $imageDir = get_Client_Image_Dir();

    $audioId = $custom['audio_attachment_id'][0];

    if ($audioId > 0) {

        // makes 'Dauer 01:05 |'
        $cleandedDuration = substr($custom['media_info'][0], strpos($custom['media_info'][0], ' '));
        $cleandedDuration = substr($cleandedDuration, 0, strlen($cleandedDuration) - 2);

        $html = '
<div id="audio-container">
        <audio id="player" preload="auto">
            <source preload="none" src="' . wp_get_attachment_url($audioId) . '" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>

        <div id="audioplayer">
            <!--	<div id="audio-Button-play-pause" class="play">-->
            <img id="play-button" style="display: block" src="' . $imageDir . "play.png" . '"/>
            <img id="pause-button" style="display: none" src="' . $imageDir . "pause.png" . '"/>

            <!--    </div>-->
            <span id="startTime">00:00</span>
            <div id="audio-timeline">
                <div id="audio-playhead"></div>
            </div>
            <span id="endTime">' . $cleandedDuration . '</span>
        </div>
        </div>
        <br>
        <br>
        <script>
        ' . "
        
                window.addEventListener('load', function () {  
                var music = jQuery('#player')[0]
                music.load();
        jQuery('#player').on('loadeddata', function (event) {
            
     
                var play_button = document.getElementById('play-button'); // play button
                var pause_button = document.getElementById('pause-button'); // play button
                var playhead = document.getElementById('audio-playhead'); // playhead
                var timeline = document.getElementById('audio-timeline'); // timeline

                var starttime = document.getElementById('startTime'); // starttime
                var endtime = document.getElementById('endTime'); // endtime

                                         
                // play button event listenter
                play_button.addEventListener('click', play);
                pause_button.addEventListener('click', play);


                // timeupdate event listener
               music.addEventListener('timeupdate', timeUpdate, false);

               // makes timeline clickable
                timeline.addEventListener('click', function (event) {
                    
                    music.currentTime = parseInt(music.duration * clickPercent(event));
                    moveplayhead(event);
                    
                }, false)

                // returns click as decimal (.77) of the total timelineWidth
                function clickPercent(event) {
                    var isTouch = arguments.length > 0 && arguments[1] !== undefined ? arguments[1] : false;

                    var xPos = 0;
                    if (isTouch) {
                        if(lastTouchEvent.clientX){
                            xPos = lastTouchEvent.clientX;
                        }else if(lastTouchEvent.touches){
                            xPos = lastTouchEvent.touches[0].clientX;
                        }
                    } else {
                        xPos = event.clientX;
                    }
                    return (xPos - getPosition(timeline)) / timeline.clientWidth;
                }

                // makes playhead draggable
                timeline.addEventListener('mousedown', mouseDown, false);
                timeline.addEventListener('touchstart', mouseDown, false);
                window.addEventListener('mouseup', mouseUp, false);
                window.addEventListener('touchcancel', touchUp, true);
                window.addEventListener('touchend', touchUp, true);


                // Boolean value so that audio position is updated only when the playhead is released
                var onplayhead = false;
                var lastTouchEvent = null;

               // mouseDown EventListener
                function mouseDown() {
                    onplayhead = true;
                    window.addEventListener('mousemove', moveplayhead, true);
                    window.addEventListener('touchmove', moveplayhead, true);
                    music.removeEventListener('timeupdate', timeUpdate, false);
                }

                // mouseUp EventListener
                // getting input from all mouse clicks
                function mouseUp(event) {
                    
 
                    if (onplayhead) {
                        moveplayhead(event);
                        window.removeEventListener('mousemove', moveplayhead, true);
                        window.removeEventListener('touchmove', moveplayhead, true);
                        // change current time
                        music.currentTime = parseInt(music.duration * clickPercent(event));
                            
                        music.addEventListener('timeupdate', timeUpdate, false);

                    }
                    onplayhead = false;

                }
                
                // mouseUp EventListener
                // getting input from all mouse clicks
                function touchUp(event) {
                    if (onplayhead) {
                        window.removeEventListener('mousemove', moveplayhead, true);
                        window.removeEventListener('touchmove', moveplayhead, true);
                        // change current time
                        // chrome wants audio to be paused like this

                        music.currentTime = music.duration * clickPercent(event, true);

                        music.addEventListener('timeupdate', timeUpdate, false);

                    }
                    onplayhead = false;

                }
                
                // mousemove EventListener
                // Moves playhead as user drags
                function moveplayhead(event) {
                    lastTouchEvent = event;
                    let xPos = 0;

                      if(lastTouchEvent.clientX){
                            xPos = lastTouchEvent.clientX;
                        }else if(lastTouchEvent.touches){
                            xPos = lastTouchEvent.touches[0].clientX;
                        }

                    var newMargLeft = (xPos - getPosition(timeline));

                    if (newMargLeft >= 0 && newMargLeft <= timeline.clientWidth) {
                        playhead.style.width = newMargLeft + 'px';
                    }
                    if (newMargLeft < 0) {
                        playhead.style.width = '0px';
                    }
                    if (newMargLeft > timeline.clientWidth) {
                        playhead.style.width = timeline.clientWidth + 'px';
                    }
                }
                
                // timeUpdate
                // Synchronizes playhead position with current point in audio
                function timeUpdate() {
                    

                    var RekursionBreak = arguments.length > 0 && arguments[1] !== undefined ? arguments[1] : null;

                    
                    var playPercent = timeline.clientWidth * (music.currentTime / music.duration);
                    
                    if( !isFinite(music.duration)){
                       return; 
                    }
                    
                    
                    playhead.style.width = playPercent + 'px';
                    if (music.currentTime === music.duration) {
                        play_button.style.display = 'block';
                        pause_button.style.display = 'none';
                    }
                    
                    starttime.innerText = formatTime(music.currentTime);
                    endtime.innerText = formatTime(music.duration - music.currentTime);
                }
                

                //Play and Pause
                function play() {
                    // start music
                    if (music.paused) {
                        music.play();
                        // remove play, add pause
                        play_button.style.display = 'none';
                        pause_button.style.display = 'block';
                    } else { // pause music
                        music.pause();
                        // remove pause, add play
                        play_button.style.display = 'block';
                        pause_button.style.display = 'none';
                    }
                }


                // getPosition
                // Returns elements left position relative to top-left of viewport
                function getPosition(el) {
                    return el.getBoundingClientRect().left;

                }

                function formatTime(given_seconds) {
                   
                    var dateObj = new Date(given_seconds * 1000);
                    
                    var hours = dateObj.getUTCHours();
                    var minutes = dateObj.getUTCMinutes();
                    var seconds = dateObj.getSeconds();

                    if (hours){
                        hours= hours.toString().padStart(2, '0') + ':'
                    }else{
                        hours = '';
                    }
                    
                    var timeString = hours+minutes.toString().padStart(2, '0') + ':' +
                        seconds.toString().padStart(2, '0');

                    return timeString;
                }


                if (music.readyState > 1) {
                    timeUpdate();
                } else {
                    music.load();
                }
});
        });
 
</script>
";
        return $html;
    }
    return '<h2>Der Beitrag ist beschädigt.</h2>';
}


function render_gallery_media()
{
    global $custom;

    $html = '';
    if (has_legacy_gallery()) {

        $html = "
<article class='gallery-list'>";
        $galleryId = json_decode($custom['gallery_attachment_id'][0]);
        $arrayString = "";
        $i = 0;
        $numItems = count($galleryId);
        foreach ($galleryId as $img) {

            $html .= '<a class=\'gallery-link\' onclick="enlargeGalleryImage(' . $i . ')">
    <figure class=\'gallery-image\'>
      <img draggable="false" src=\'' . wp_get_attachment_image_src($img, 'medium')[0] . '\'>
    </figure>
  </a>';

            $url = wp_get_attachment_url($img);
            if ($url) {
                $arrayString .= "'" . wp_get_attachment_url($img) . "'";

                if (++$i < $numItems) {
                    $arrayString .= ",";
                }
            }
        }


        $html .= "
</article>
<script>
  var images=[" . $arrayString . "];
  var currentImage =0;

  function enlargeGalleryImage(index){  currentImage = index;
    document.getElementById('gallery-fs-container').style.display='block';
    document.getElementById('gallery-fs-img').src=images[index];
    
   document.getElementsByClassName('content-sidebar-wrap')[0].style.display='none';
   
   document.getElementsByClassName('site-inner')[0].classList.remove('gallery-fs');
    document.getElementsByClassName('site-inner')[0].classList.add('gallery-fs'); 
     
     document.getElementsByClassName('site-header')[0].classList.remove('hidden');
     document.getElementsByClassName('site-header')[0].classList.add('hidden');
     
    }

  function minimizeImage(){
   document.getElementsByClassName('content-sidebar-wrap')[0].style.display='block';
    document.getElementById('gallery-fs-container').style.display='none';
        document.getElementsByClassName('site-inner')[0].classList.remove('gallery-fs');
         document.getElementsByClassName('site-header')[0].classList.remove('hidden');
  }
  function showNextImage(){
    if (currentImage < images.length - 1) {
      ++currentImage;
    } else {
      currentImage = 0
    }
    document.getElementById('gallery-fs-img').src = images[currentImage];

  }
  function showPreviousImage(){
    if (currentImage >= 1) {
      --currentImage;
    } else {
      currentImage = images.length-1;
    }
    document.getElementById('gallery-fs-img').src = images[currentImage];

  }
</script>
";

    }

    return $html;
}

function render_prize_draw_body()
{
    global $custom;

    $tax_free = unserialize($custom['prize_draw_tax_free'][0]);
    $is_event = $custom['prize_draw_is_event'][0] == 'true';
    $prize_draw_from = $custom['prize_draw_from'][0];

    $is_event ? $is_event_class = 'event' : $is_event_class = 'non-event';
    $html = "
    <div class='prize_draw_body $is_event_class'>
            <img id=\"gemeinsam_allem_gewachsen_img\" src='wp-content/themes/inseider/images/gemeinsam_allem_gewachsen.png'>";

    foreach (unserialize($custom['prize_draw_number_of_tickets'][0]) as $i => $number_of_Tickets) {

        if (!$number_of_Tickets) {
            $number_of_Tickets = 1;
        }

        $amount_of_tickets = unserialize($custom['prize_draw_amount_of_tickets'][0])[$i];
        $event_type = unserialize($custom['prize_draw_event_type'][0])[$i];
        $event_name = unserialize($custom['prize_draw_event_name'][0])[$i];
        $location = nl2br(unserialize($custom['prize_draw_event_location'][0])[$i]);
        $start_date = unserialize($custom['prize_draw_event_start_date'][0])[$i];
        $start_time = unserialize($custom['prize_draw_event_start_time'][0])[$i];
        if (!$is_event) {
            $start_time = false;
        }
        $prize_draw_to = unserialize($custom['prize_draw_to'][0])[$i];
        $active = unserialize($custom['prize_draw_active'][0])[$i];

        if ($tax_free) {
            $tax_free = '<p>Der Gewinn ist für den Gewinner steuer- und sozialversicherungsfrei.</p>';
        }

        if ($location) {
            $location = '<p>Veranstaltungsort:<br>' . $location . '</p>';
        }

        if ($start_time) {
            $start_time = '<p>Beginn:<br>' . $start_time . '</p>';
        }

        $html .= "
    <div class='event_container'>
    <div class='prize_draw_containter_left'>
    <p>Im Rahmen unseres Gewinnspiels verlosen wir:</p>";

        if ($is_event) {
            $html .= " <h3>$number_of_Tickets x $amount_of_tickets Freikarten</h3>
    <p>für $event_type</p><h3>$event_name</h3>
    <h3>am $start_date</h3>";
        } else {
            $html .= " <h3>$number_of_Tickets x $amount_of_tickets</h3>
    <h3>$event_name</h3>";
        }

        $html .=
            "
    <br>
    </div>
    
    
    <div class='prize_draw_containter_right'>
    $location
    $start_time
    $tax_free
    <br>
    <br>
    </div>
    <br>

<div>
    <p>Beginn des Gewinnspiels: " . $prize_draw_from . "</p>
    <p>Ende des Gewinnspiels: " . $prize_draw_to . "</p>
    ";

        if ($active == 'true') {
            global $prize_draw_participation_status;


            $html .= '<h3>Das Gewinnspiel ist aktiv</h3>    </div><br/>';

            if ($prize_draw_participation_status[$i] === 'open') {
                /*copying these from excel with a column before and after the data for the tags works well, remove tabs with seearch&replace*/

                $html .= '<div class="participate_in_prize_draw_form_container">
                        <form class="participate_in_prize_draw_form" method="post" >' . wp_nonce_field('wps_participate_prize_draw', '_nonce', true, false) . '
                            <input hidden name="participate" value="participate">
                            <input hidden name="prize_draw_index" value="' . $i . '">' . get_internal_mail_html() . '
                            <button style="width: 100%;" type="submit" value="teilnehmen" aria-label="teilnehmen">teilnehmen</button>
                        </form>
                        </div>';
            }

            if ($prize_draw_participation_status[$i] === 'already_in' || $prize_draw_participation_status[$i] === 'success') {
                $html .= '<div><h3 class="red">Sie nehmen an dem Gewinnspiel teil, viel Glück!</h3></div>';
            }

        } else {
            $html .= '<p>Das Gewinnspiel ist nicht aktiv</p></div>';
        }

        $html .= '</div>';

    }

    $html .= '</div>';
    return $html;

}


/**
 * returns whether or not current users ID is part of the participants-field of the current post
 *
 * @return array $prize_draw_participation_status with the statuses of all prize draws of the current post
 */
function has_participated_in_prize_draw()
{
    global $custom;

    // $prize_draw_participation_status is needed to display the correct status
    // the page is rendered shortly after participating, post meta may not be updated already

    $prize_draw_amount = count(unserialize($custom['prize_draw_number_of_tickets'][0])) ?? 0;

    if (!is_user_logged_in()) {
        ajax_logged_out();
        return array(false);
    }

    if (!array_key_exists('prize_draw_participants', $custom)) {
        $prize_draw_participation_status = array_fill(0, $prize_draw_amount, 'open');
        return $prize_draw_participation_status;
    }
    $prize_draw_participants = json_decode($custom['prize_draw_participants'][0], true) ?? array();
    global $current_user;


    for ($prize_draw_index = 0; $prize_draw_index < $prize_draw_amount; $prize_draw_index++) {


        $participants = $prize_draw_participants [$prize_draw_index];
        if (is_null($participants)) {
            $prize_draw_participation_status[$prize_draw_index] = "open";
            continue;
        }

        $login = $current_user->user_login;
        $prize_draw_participation_status[$prize_draw_index] = "open";
        foreach ($participants as $participant) {
            if ($participant['id'] === $login) {
                $prize_draw_participation_status[$prize_draw_index] = "already_in";
                break;
            }
        }
    }

    return $prize_draw_participation_status;
}

/**
 * If the request is valid will add the users id and given internal mail address to the field of participants stored in current posts meta.
 *
 */
function participate_in_prize_draw()
{


    // this function manages the participants ans saves them as a 2d arrray (json) in the "prize_draw_participants" meta
    // the first dimesnion is the prize draw, the seccond is the participants (id and internal mail)

    $prize_draw_index = sanitize_text_field($_POST['prize_draw_index']);

    if (is_null($prize_draw_index) || has_participated_in_prize_draw()[$prize_draw_index] != 'open' || !is_user_logged_in() || !wp_verify_nonce($_POST['_nonce'], 'wps_participate_prize_draw')) {
        return;
    }

    global $current_user;
    global $custom;
    global $prize_draw_participation_status;


    $data = (object)array(
        'id' => $current_user->user_login
    );

    if ((get_internal_mail_html() !== '')) {
        $data->adress = sanitize_text_field($_POST['internal_mail']);
    }

    if (array_key_exists('prize_draw_participants', $custom)) {
        $participants_raw = $custom['prize_draw_participants'][0];

        $participants = json_decode($participants_raw, true);
    } else {
        $participants = array();
    }
    if (empty($participants[$prize_draw_index])) {
        $participants[$prize_draw_index] = array();
    }

    array_push($participants[$prize_draw_index], $data);

    $str = json_encode($participants);

    __update_post_meta(get_the_ID(), 'prize_draw_participants', $str);
    $prize_draw_participation_status[$prize_draw_index] = "success";
}


// Force content-sidebar layout setting
remove_action('genesis_before_footer', 'genesis_footer_widget_areas');

function render_documents()
{
    global $custom;
    $documentIds = json_decode($custom['documents_attachment_id'][0]) ?? null;

    if ($documentIds != null) {

        foreach ($documentIds as $key => $dcm) {

            if (is_null($dcm) || $dcm <= 0) {
                continue;
            }
            $html = $html ?? '';


            $html .= '<a class=\'document-link\' target="_blank" and rel="noopener" href="' . wp_get_attachment_url($dcm) . '"><img src="' . home_url() . '/' . get_Client_Image_Dir() . 'pdf.png' . '">' . get_the_title($dcm) . '</a>';

            if ($key !== array_key_last($documentIds)) {
                $html .= "<hr>";
            }

        }

        if (empty($html)) {
            return;
        }
        ?>
        <div class="document_container">
            <h3>Anhänge</h3>
            <div id="documents">
                <?php
                echo $html;
                ?>
            </div>
        </div>
        <?php
    }
    $aae_documentIds = json_decode($custom['aae_documents_attachment_id'][0] ?? 'null');

    if ($aae_documentIds !== null &&
        (can_user_activate_edit(get_the_ID(), get_current_user_id()) ||
            can_user_edit_post(get_the_ID(), get_current_user_id())
        )) {

        if (!empty($html)) {
            echo '<br>';
        }
        $html = '';
        if (empty($aae_documentIds)) {

            $html .= '<p>Anhänge wurden gelöscht</p>';
        }
        foreach ($aae_documentIds as $key => $dcm) {

            $html .= '<a class=\'document-link\' target="_blank" and rel="noopener" href="' . wp_get_attachment_url($dcm) . ')"><img src="' . home_url() . '/' . get_Client_Image_Dir() . 'pdf.png' . '">' . get_the_title($dcm) . '</a>';

            if ($key !== array_key_last($aae_documentIds)) {
                $html .= "<hr>";
            }

        }
        ?>
        <div class="document_container dc_edited">
            <h3>Geänderte Anhänge</h3>
            <div id="documents">
                <?php
                echo $html;
                ?>
            </div>
        </div>
        <?php
    }

}

// Run the Genesis loop
genesis();



