<?php

/**
 * Changes password to the one stored in Post
 *
 * @return true|array (true when successful; array of errors if not)
 */
function change_password(): bool|array
{
    if (!isset($_POST['current_password'])) {
        return false;
    }
    $errors = array();
    if (!wp_verify_nonce($_POST['_wpnonce'], 'wps_change_passw')) {
        $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
        return $errors;
    }

    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_new_password = trim($_POST['confirm_new_password']);
    $current_user = wp_get_current_user();

    // Check for errors
    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $errors[] = 'Alle Felder müssen ausgefüllt werden.';
    }

    if (!$current_user || !wp_check_password($current_password, $current_user->data->user_pass, $current_user->ID)) {
        $errors[] = 'Das aktuelle Passwort ist falsch.';
    }

    if ($new_password != $confirm_new_password) {
        $errors[] = 'Die neuen Passwörter simmten nicht überein.';
    }

    if ($new_password === $current_password) {
        $errors[] = 'Neues und altes Passwort sind gleich.';
    }

    $valid = is_valid_password($new_password);
    if ($valid !== true) {
        foreach ($valid as $error) {
            $errors[] = $error;
        }
    }

    if (empty($errors)) {
        //wp_set_password($new_password, $current_user->ID);

        $userdata = array();
        $userdata['ID'] = $current_user->ID; //user ID
        $userdata['user_pass'] = $new_password;
        wp_update_user($userdata);

        Expire_Passwords::save_user_meta(get_current_user_id());

        $loginUrl = add_query_arg('password', 'changed', home_url());
        wp_safe_redirect($loginUrl);

        return true;
    } else {
        return $errors;
    }
}


add_filter('v_forcelogin_bypass', 'spk_forcelogin_bypass');
/**
 * Bypass Force Login to allow for exceptions. These Urls can be accessed by everyone
 *
 * @param bool $bypass Whether to disable Force Login. Default false.
 * @return bool
 */
function spk_forcelogin_bypass($bypass)
{
    // Get visited URL without query string
    $url_path = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);

    $bypass = false;
    //$bypass = true;
    //d($url_path);

    // Allow URL
    if ('/member-password-lost' === $url_path) {
        $bypass = true;
    }

    if ('/impressum' === $url_path) {
        $bypass = true;
    }

    if ('/datenschutz' === $url_path) {
        $bypass = true;
    }

    // Allow filename URL
    if ('/member-password-reset' === $url_path) {
        $bypass = true;
    }

    if ('/external-file' === $url_path) {
        $bypass = true;
    }

    if ('/erklaerung-zur-barrierefreiheit' === $url_path) {
        $bypass = true;
    }

    if (is_user_logged_in() && '/change-pw' === $url_path) {
        $bypass = true;
    }

    /*
        // Allow URL
        if ('/showcase/member-password-lost' === $url_path) {
            $bypass = true;
        }

        // Allow filename URL
        if ('/showcase/member-password-reset' === $url_path) {
            $bypass = true;
        }

        if (is_user_logged_in() && '/showcase/change-pw' === $url_path) {
            $bypass = true;
        }*/

    return $bypass;
}


/**
 * Generates a random password with at least one lowercase letter,
 * one uppercase letter, one number and one special character.
 *
 * @param int $length The length of the password
 * @return string a randomly generated password
 */
function random_password_generate($length)
{
    $lowercaseLetters = "abcdefghijklmnopqrstuvwxyz";
    $uppercaseLetters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $numbers = "0123456789";
    $specialCharacters = "!@#$%^&*()-=+?";


    $pw = "";
    $pw .= $lowercaseLetters[wp_rand(0, (strlen($lowercaseLetters) - 1))];
    $pw .= $uppercaseLetters[wp_rand(0, (strlen($uppercaseLetters) - 1))];
    $pw .= $numbers[wp_rand(0, (strlen($numbers) - 1))];
    $pw .= $specialCharacters[wp_rand(0, (strlen($specialCharacters) - 1))];


    for ($i = 0; $i < $length - 4; $i++) {

        $nextCharCathegory = wp_rand(0, 3);
        switch ($nextCharCathegory) {
            case 0:
                $pw .= $lowercaseLetters[wp_rand(0, (strlen($lowercaseLetters) - 1))];
                break;

            case 1:
                $pw .= $uppercaseLetters[wp_rand(0, (strlen($uppercaseLetters) - 1))];
                break;

            case 2:
                $pw .= $numbers[wp_rand(0, (strlen($numbers) - 1))];
                break;

            case 3:
                $pw .= $specialCharacters[wp_rand(0, (strlen($specialCharacters) - 1))];
                break;
        }
    }

    $pw = str_shuffle($pw);
    $pw = str_shuffle($pw);

    return $pw;
}

/**
 * Checks if given password is valid
 *
 * @return true|array (true when successful; array of errors if not)
 */
function is_valid_password($password)
{
    $errors = array();
    if (strlen($password) < 8) {
        $errors[] = "Das Passwort muss mindestens 8 Zeichen lang sein!";
    }
    if (!preg_match("#[0-9]+#", $password)) {
        $errors[] = "Das Passwort muss mindestens eine Ziffer enthalten!";
    }
    if (!preg_match("#[A-Z]+#", $password)) {
        $errors[] = "Das Passwort muss mindestens einen Großbuchstaben enthalten!";
    }
    if (!preg_match("/\W/", $password)) {
        $errors[] = "Das Passwort muss mindestens ein Sonderzeichen enthalten!";
    }
    if (preg_match("/\s/", $password)) {
        $errors[] = "Das Passwort darf keine Leerzeichen enthalten!";
    }

    if (empty($errors)) {
        return true;
    } else {
        return $errors;
    }

}

add_action('wp_enqueue_scripts', 'enqueue_datepicker');

/**
 * @param $user object user who is trying to acess the page
 */


function handle_viewing_restrictions()
{

    $post_id = get_user_meta(wp_get_current_user()->ID, 'business_parter_post_id', true);
    $target = '/' . $post_id;

    $used_url = strtok($_SERVER['REQUEST_URI'], '?');

    if (in_array('business_partner', wp_get_current_user()->roles)) {

        if ($used_url != $target) {
            ob_clean();
            wp_safe_redirect(home_url($target));
            exit();
        }
    }
}


add_filter('onesignal_initialize_sdk', 'onesignal_initialize_sdk_filter', 10, 1);
function onesignal_initialize_sdk_filter($onesignal_settings)
{
    global $current_user;
    if (is_user_logged_in() && !in_array('business_partner', $current_user->roles)) {
        return true;
    }
    return false;
    /* Returning true allow the SDK to initialize normally on the current page */
    /* Returning false prevents the SDK from initializing automatically on the current page */
}