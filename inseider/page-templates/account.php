<?php
/**
 * Template Name: Account
 */

get_header();
add_action('genesis_before_header', 'renew_wp_cookie', 35);

handle_notifications_settings_form_submit();
$notifications_prize_draw = get_user_meta(wp_get_current_user()->ID, 'notifications_prize_draw', true) != 'false';
$notifications_posts = get_user_meta(wp_get_current_user()->ID, 'notifications_posts', true) != 'false';

$customEmail = get_user_meta(wp_get_current_user()->ID, 'notifications_custom_email', true);

echo '<style>' . custom_css('account') . '</style>';

?>

<div class='post-top-bar'><a class="noHighlight" href="/"><p id='inseider-back-Button'>
            <?php echo file_get_contents(get_Server_Image_Dir() . "small_back_icon.svg"); ?> zurück</p></a>
</div>
<div class="account-content">
    <img src="<?php echo get_Client_Image_Dir() . 'person_icon.svg'; ?>">
    <div id="main-content">
        <h2>Account</h2>
        <?php
        echo '<p>' . wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name . '<br>'
            . wp_get_current_user()->user_login . '</p><br>
    <div id="account_logout_button"><a href="' . wp_logout_url() . '"><button class="spk_wide_button">Logout</button></a></div>';

        if (can_change_notification_settings()) {
            echo '<div><button id="account_notification_button" class="spk_wide_button secondary_button" onclick="show_notifications_settings(true)">Benachrichtigungen</button></div>';
        }

        echo '<div id="account_change_pw_button"><a href="' . home_url("/change-pw/?header=true") . '"><button class="spk_wide_button secondary_button">Passwort ändern</button></a></div>';
        ?>
    </div>
    <?php
    if (!in_array('business_partner', wp_get_current_user()->roles)) {

        ?>
        <div id="notifications-settings" style="display: none">
            <h2>Benachrichtigungen</h2>
            <br>
            <br>
            <form method="post">
                <?php wp_nonce_field('wps-notifications-settings', 'wps-notifications-settings-nonce') ?>
                <div>

                    <div class="notification-form">


                        <div>
                            <label><input type="checkbox" <?php if ($notifications_posts) {
                                    echo 'checked';
                                } ?> name="notifications_posts" onchange="main_email_changed(this)">
                                Benachrichtigungen an <?php
                                echo $GLOBALS['current_user']->user_email;
                                ?> senden</label>
                            <br>
                        </div>

                        <div id="main_email_options"<?php if (!$notifications_posts) {
                            echo 'style="display: none"';
                        } ?>>

                            <?php print_notification_sub_form(true); ?>

                        </div>


                        <div>
                            <label><input type="checkbox"
                                          onchange="custom_email_changed(this)" <?php if ($customEmail) {
                                    echo 'checked';
                                } ?> name="use_custom_email">
                                Benachrichtigungen auch an private E-Mail senden</label>
                            <br>
                        </div>

                        <div id="custom_email_options" <?php if (!$customEmail) {
                            echo 'style="display: none"';
                        } ?>>
                            <?php print_notification_sub_form(false); ?>

                            <label for="custom_email">E-Mail Adresse für Benachrichtigungen</label>
                            <input id="custom_email" type="text" required <?php if (!$customEmail) {
                                echo 'disabled';
                            } ?> value="<?php if ($customEmail) {
                                echo $customEmail;
                            } ?>"
                                   name="custom_email">
                            <br>
                        </div>

                        <br>
                        <br>


                    </div>

                </div>
                <br>

                <button class="spk_wide_button" name="submit" value="notifications_settings" type="submit">speichern
                </button>
            </form>
            <div>
                <button class="spk_wide_button secondary_button" onclick="show_notifications_settings(false)">abbrechen
                </button>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<script>

    function custom_email_changed(custom_email) {
        if (custom_email.checked) {
            document.getElementById('custom_email_options').style.display = 'block';
            document.getElementById('custom_email').disabled = false;
        } else {
            document.getElementById('custom_email_options').style.display = 'none';
            document.getElementById('custom_email').disabled = true;
        }
    }

    function main_email_changed(custom_email) {
        if (custom_email.checked) {
            document.getElementById('main_email_options').style.display = 'block';
        } else {
            document.getElementById('main_email_options').style.display = 'none';
        }
    }

    function show_notifications_settings(show) {
        if (show) {
            document.getElementById('notifications-settings').style.display = 'block';
            document.getElementById('main-content').style.display = 'none';
        } else {
            document.getElementById('notifications-settings').style.display = 'none';
            document.getElementById('main-content').style.display = 'block';
        }
    }
</script>

<?php


function print_notification_sub_form($main_email = true)
{

    if ($main_email) {
        $name_pre = '1';
    } else {
        $name_pre = '0';
    }
    $old_values = get_user_meta(wp_get_current_user()->ID, 'notifications_categories', true);
    if (empty($old_values)) {
        $alltrue = true;
    } else {
        $alltrue = false;
        $old_values = $old_values[$name_pre];
    }

    echo '<div class="notification-sub-form">';

    $args = array(
        'hide_empty' => false,
    );
    foreach (get_notification_settings_categories() as $cat) {
        $cat = get_category($cat);
        $name = $cat->name;
        $alltrue || is_null($old_values[$cat->term_id]) || $old_values[$cat->term_id] ? $checked = 'checked' : $checked = '';
        echo ' <label><input ' . $checked . ' name="sub_notification_form[' . $name_pre . '][' . $cat->term_id . ']" type="checkbox">';
        echo ' ' . $name;
        echo '</label><br>';
    }

    echo '</div>';
}


/**
 * Updates users meta values according to the posted values
 * @return array|bool true if successfully changed options, false if not, array of errors if there were any
 */
function handle_notifications_settings_form_submit()
{

    if (!empty($_POST['submit']) && $_POST['submit'] != 'notifications_settings') {
        return false;
    }

    if (!can_change_notification_settings()) {
        return false;
    }

    if (!wp_verify_nonce($_POST['wps-notifications-settings-nonce'], 'wps-notifications-settings')) {
        $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut.';
        return $errors;
    }
    $notifications_posts = sanitize_text_field($_POST['notifications_posts']) == 'on' ? 'true' : 'false';

    $res1 = update_user_meta(wp_get_current_user()->ID, 'notifications_posts', $notifications_posts) == true;

    if (sanitize_text_field($_POST['use_custom_email']) == 'on') {
        $custom_email = sanitize_text_field($_POST['custom_email']);
    } else {
        $custom_email = '';
    }
    $res3 = update_user_meta(wp_get_current_user()->ID, 'notifications_custom_email', $custom_email) == true;

    // this map works like this:
    // the first dimension is the email address 1 is the main address and 0 is the custom one
    // the second dimension is the id of the category
    // the value represents weather the notification should be sent
    $categories_map = array();

    //make an empty array (only true values will be submitted)

    $args = array(
        'hide_empty' => false,
    );
    $changeable_categories = get_notification_settings_categories();


    foreach ($changeable_categories as $cat) {
        $categories_map[0][$cat] = false;
        $categories_map[1][$cat] = false;
    }

    foreach ($_POST['sub_notification_form'] as $a => $address) {
        foreach ($address as $cat => $value) {
            if ($value == 'on' || !in_array($cat, $changeable_categories)) {
                $categories_map[$a][$cat] = true;
            }
        }
    }

    update_user_meta(wp_get_current_user()->ID, 'notifications_categories', $categories_map);

    return $res1 || $res3;
}


get_footer(); ?>

