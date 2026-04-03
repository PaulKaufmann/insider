<?php

/*
 *
 * Overview of post meta parameters used for notifications:
 *
 * -disable_manual_notification : is used during the time between trigger of a notification and it actually beeing sent.
 *  It prevents manual notifications from beeing triggered multiple times.
 *
 * -notify: used to determine if a post is supposed to send a notificaion once published (should be renamed)
 *
 * -notification_triggered_by: an array of all notifications with user that triggered it, timestamp and if triggered manually used for logging purposes and findiing errors,
 * -post_edited_after_last_notification: true/false
 * -notified time: unix timestamp of last notification
 */

function notify_users($post_id, $manual_mode = false, $user = 'system')
{
    $post = get_post($post_id);

    $nt = get_post_meta($post_id, 'notified_time', true);
    if (!empty($nt) && $nt > 0 && !$manual_mode) {
        return;
    }

    global $NOTIFICATION_SETTINGS;
    $pns = get_notification_settings($post->post_category[0]);
    if ($pns == $NOTIFICATION_SETTINGS['NEVER']) {
        return;
    } elseif ($pns != $NOTIFICATION_SETTINGS['ALWAYS']) {
        if (!$manual_mode && get_post_meta($post_id, 'notify', true) !== 'true') {
            return;
        }
    }

    if ($post != null && $post->post_status === 'publish' && $post->post_type === 'post') {

        $triggerTime = time();
        update_post_meta($post_id, 'disable_manual_notifications', 'true');
        sendPushNotification(wp_generate_uuid4(), get_the_permalink($post_id));
        wp_schedule_single_event(time() + 25, 'hook_for_email_members', array($post_id,$triggerTime));


        $notificatiosn_triggered_by = json_decode(get_post_meta($post->ID, 'notificatiosn_triggered_by', 'true'));

        if (empty($notificatiosn_triggered_by)) {
            $notificatiosn_triggered_by = array();
        }

        $prefix = 'automatic:';
        if ($manual_mode) {
            $prefix = 'manual:';
        }
        $notificatiosn_triggered_by[] = $prefix . ' ' . $triggerTime . ' ' . $user;

        update_post_meta($post->ID, 'notificatiosn_triggered_by', json_encode($notificatiosn_triggered_by));
        delete_post_meta($post->ID, 'post_edited_after_last_notification');
    }
}

$log_email = true;
/**
 * Sends an html e-mail with a default footer via wp_mail.
 *
 * @param string $to The receiver of the e-mail.
 * @param string $message The content of the e-mail.
 * @param string $subject The subject of the e-mail.
 * @param bool|string $cc_to The CC receiver(s) of the e-mail, false if no cc header should be set.
 * @param array $image_Files The attachment files of the e-mail.
 * @return bool Whether the email contents were sent successfully.
 */
function spk_sendEmail($to, $message, $subject, $cc_to = false, $image_Files = array(), $use_footer = true, $log = true)
{
    global $log_email;
    $log_email = $log;
    $headers = 'From: ' . get_plattform_name() . ' <' . get_sender_Email() . '>' . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= 'List-Unsubscribe: ' . get_domain() . "\r\n";


    if ($cc_to) {
        $headers .= "Cc: $cc_to\r\n";
    }

    if ($use_footer) {

        $message .= sprintf($GLOBALS['Strings']['spk_sendmail_signature'], get_team_Email());
    }

    return wp_mail($to, $subject, $message,
        $headers,
        $image_Files);
}


add_action('hook_for_email_members', 'email_members', 10, 2);


/**
 * Sends e-mail notifications to all users who opted in after checking the post is valid and was not notified about before
 *
 * @param int $post_ID The ID of the Post to be notified about
 */
function email_members($post_ID, $triggerTime = null ,$title = null, $excerpt = null)
{
    $sending_emails = get_option('sending_emails');
    if ($sending_emails != 'true') {
        update_option('sending_emails', 'true');
    } else {
        //repeat in 3 minutes
        wp_schedule_single_event(time() + 60 * 3, 'hook_for_email_members', array($post_ID, $triggerTime));
        return;
    }

    $lastTriggertime = get_post_meta($post_ID, 'notified_time', true);
    if ($lastTriggertime == $triggerTime){
        update_option('sending_emails', 'false');
        return;
    }

    global $wpdb;
    $users = $wpdb->get_results("SELECT ID, user_email FROM $wpdb->users;");
    $post = get_post($post_ID);

    if ($post != null && $users != null && $post->post_status === 'publish' && $post->post_type === 'post') {
        update_post_meta($post->ID, 'notified_time', $triggerTime);
        delete_post_meta($post->ID, 'disable_manual_notifications');


        $link = get_post_permalink($post_ID);
        // todo configure text
        $title == null ? $title = $post->post_title : null;
        $excerpt == null ? $excerpt .= $post->post_excerpt : null;

        $subject = html_entity_decode(sprintf($GLOBALS['Strings']['new_post_subject'], $post->post_title));
        $message = sprintf($GLOBALS['Strings']['new_post_message_spk_mail'], $link, $title, $excerpt);

        $censored_subject = $GLOBALS['Strings']['new_post_censored_subject'];
        $censored_message = sprintf($GLOBALS['Strings']['new_post_censored_message_spk_mail'], $link);

        $post_categories = get_the_category($post_ID);
        foreach ($users as $user) {

            if (in_array('business_partner', get_user_by('ID', $user->ID)->roles)) {
                continue;
            }

            $category_matrix = get_user_meta($user->ID, 'notifications_categories');
            if (empty($category_matrix) || !in_array($post_categories[0]->term_id, get_notification_settings_categories())) {
                $send_main = true;
                $send_custom = true;
            } else {
                $category_matrix = $category_matrix[0];
                if (is_null($category_matrix[1]) || is_null($category_matrix[1][$post_categories[0]->term_id])) {
                    $send_main = true;
                } else {
                    $send_main = $category_matrix[1][$post_categories[0]->term_id];
                }

                if (is_null($category_matrix[0]) || is_null($category_matrix[0][$post_categories[0]->term_id])) {
                    $send_custom = true;
                } else {
                    $send_custom = $category_matrix[0][$post_categories[0]->term_id];
                }
            }

            $mail = $user->user_email;

            $notifications_enabled_for_posts = get_user_meta($user->ID, 'notifications_posts', true) != 'false';
            if ($notifications_enabled_for_posts && $send_main) {
                spk_sendEmail($mail, $message, $subject);
            }

            $custom_mail = get_user_meta($user->ID, 'notifications_custom_email', true);
            if ($custom_mail != '' && $send_custom) {
                spk_sendEmail($custom_mail, $censored_message, $censored_subject);
            }
            usleep(100000);
        }
    }
    update_option('sending_emails', 'false');
}


function debugg_mail($text)
{
    return spk_sendEmail('Paulkaufmann@hotmail.de', $text, 'test');
}


add_action('hook_for_repeated_email_recent_comments', 'email_recent_comments');


function email_recent_comments()
{
    $args = array(
        'date_query' => array(
            array(
                'after' => '-1 day',
            ),
        ),
        'orderby' => 'comment_post_ID',
    );

    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);

    if (empty($comments)) {
        return;
    }

    global $RECENT_COMMENTS_NONE, $RECENT_COMMENTS_AUTHOR, $RECENT_COMMENTS_ADMIN;
    $rcr = recent_comment_receiver_by_category();

    $output_admins = '';
    $output_users = array();
    $new_post = null;

    $current_post_id = -1;
    $tmp_output = '';

    foreach ($comments as $comment) {
        if ($comment->comment_post_ID != $current_post_id) {
            $new_post = get_post($comment->comment_post_ID);
            $tmp_output = "<br><h3 style='text-decoration:underline;font-size: x-large;font-weight: bolder;display: inline'>$new_post->post_title</h3> <a href='" . get_permalink($new_post) . "'>zum Beitrag</a><br>";

            $current_post_id = $comment->comment_post_ID;
        }
        $author = get_user_by('login', $comment->comment_author);
        $tmp_output .= '<p class="comment_email_notification"><strong>' . $author->first_name . ' ' . $author->last_name . '</strong><br>' . $comment->comment_content . '</p>';

        $rec = $rcr[($new_post->post_category)[0]];
        if (is_null($rec)) {
            $rec = $RECENT_COMMENTS_ADMIN;
        }

        switch ($rec) {
            case $RECENT_COMMENTS_ADMIN:
            {
                $output_admins .= $tmp_output;
                break;
            }
            case $RECENT_COMMENTS_AUTHOR:
            {
                $output_users[$new_post->post_author] .= $tmp_output;
                break;
            }
            case $RECENT_COMMENTS_NONE:
            {
                break;
            }
        }
        $tmp_output = '';
    }


    $comment_admins = get_comment_admins();

    if (!empty($output_admins)) {
        foreach ($comment_admins as $receiver) {
            $receiver = get_user_by('login', $receiver);

            if (!$receiver) {
                continue;
            }

            $message = sprintf($GLOBALS['Strings']['comment_admin_message'], $receiver->first_name, $receiver->last_name);
            $message .= $output_admins;

            spk_sendEmail($receiver->user_email, $message, $GLOBALS['Strings']['comment_admin_subject']);
        }
    }

    foreach ($output_users as $login => $output) {
        $receiver = get_user_by('ID', $login);
        $message = sprintf($GLOBALS['Strings']['comment_user_message'], $receiver->first_name, $receiver->last_name) . $output;

        spk_sendEmail($receiver->user_email, $message, $GLOBALS['Strings']['comment_user_subject']);
    }


}

//sometimes mail processes can die during sending. then they will not set sending_emails back to false and other mails will not be sent.
// So once every night we set it back to false
add_action('hook_for_unclog_mail_process', 'unclog_mail_process');
function unclog_mail_process()
{
    update_option('sending_emails', 'false');
}

//region onesignal
//REST-API-Key for Onesignal
function get_oneSignal_api_key()
{
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();
    return $onesignal_wp_settings['app_rest_api_key'];
}

//App-ID for Onesignal
function get_oneSignal_appID()
{
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();
    return $onesignal_wp_settings['app_id'];
}

function get_oneSignal_safariWebId()
{
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();
    return $onesignal_wp_settings['safari_web_id'];
}


/**
 * @param string $external_id The UUID of the message
 * @param string $url The url of the push
 * @param string $segment The target segment
 * @param string $message The message of the Notification
 * @return bool true on success or false on failure
 */
function sendPushNotification($external_id, $url, $segment = 'All', $message = null)
{
    if (is_null($message)) {
        $message = $GLOBALS['Strings']['new_post_censored_subject'];
    }

//todo configure
    //$segment = 'test_users';

    $fields = array(
        'external_id' => "$external_id",
        'app_id' => get_oneSignal_appID(),
        'safari_web_id' => get_oneSignal_safariWebId(),
        "headings" => array(
            "en" => get_plattform_name()
        ),
        'included_segments' => array(
            $segment
        ),
        "url" => $url,
        'persistNotification' => true,
        "isAnyWeb" => true,
        'chrome_web_badge' => home_url('pwa/icons/push_monochrome.png'),
        'contents' => array(
            "en" => $message
        )
    );
    $fields = json_encode($fields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ' . get_oneSignal_api_key()
    ));
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;

}

add_filter('onesignal_send_notification', 'onesignal_send_notification_filter', 10, 4);
function onesignal_send_notification_filter($fields, $new_status, $old_status, $post)
{
    //$fields['included_segments'] = null;
    //$fields['include_player_ids'] = array('0959f6e2-cc4a-4ed7-921a-eca26900658a','05fa3e17-1a2d-4e53-9d20-9bc35cff9bfb');
    //$fields['included_segments'] = array('test_users');
    //$fields['filters']=null;
    //$fields['send_after'] = date("Y-m-d, H:i:s T", time() + 30 * 60);
    $fields['chrome_web_badge'] = 'pwa/icons/icon.png';
    return $fields;
}

function onesignal_player_ids_js()
{

    $onesignal_player_ids = unserialize(get_user_meta(get_current_user_id())["onesignal_player_ids"][0]);
    $onesignal_player_ids = json_encode($onesignal_player_ids);
    ?>
    <script>

        var existing_ids = JSON.parse('<?php echo $onesignal_player_ids ?>');


        OneSignal.push(function () {

            OneSignal.isPushNotificationsEnabled(function (isEnabled) {

                if (isEnabled) {
                    OneSignal.getUserId(function (userId) {
                        if (existing_ids !== false && existing_ids.includes(userId)) {
                            return 0;
                        }
                        onesignal_player_id_ajax(userId);

                    });
                }
            });
        });
    </script>
    <?php

}





//endregion