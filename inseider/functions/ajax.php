<?php

function more_post_ajax()
{
    $ppp = (isset($_POST["ppp"])) ? sanitize_text_field($_POST["ppp"]) : 1;
    $page = (isset($_POST['pageNumber'])) ? sanitize_text_field($_POST['pageNumber']) : 1;
    $s = (isset($_POST['search'])) ? sanitize_text_field($_POST['search']) : '';
    $cat = (isset($_POST['cat'])) ? json_decode($_POST['cat']) : '';
    $sort_id = (isset($_POST['sort_id'])) ? json_decode($_POST['sort_id']) : '';
    $show_hidden = (isset($_POST['show_hidden'])) ? json_decode($_POST['show_hidden']) : false;

    $data = more_posts($ppp, $page, $s, $cat, false, '', $sort_id, $show_hidden);
    header("Content-Type: json");
    wp_reset_postdata();
    die(json_encode($data));

}

function ajax_logged_out()
{
    header("Content-Type: text/plain");
    $out = 'false';
    die($out);
}


function edit_user_ajax()
{
    global $wp_roles;

    if (current_user_can('edit_users')) {

        header("Content-Type: json");

        $user_login = sanitize_text_field($_POST["user"]);
        $user = get_user_by('login', $user_login);
        if (!$user) {
            die();
        }
        $data = (object)array();
        $data->first_name = get_user_meta($user->ID, 'first_name', true);
        $data->last_name = get_user_meta($user->ID, 'last_name', true);

        //todo maybe reset everywhere?
        $role = reset($user->roles);

        $data->role = $role;
        $data->role_name = translate_user_role($wp_roles->roles[$role]['name']);
        $data->user_email = $user->user_email;

        die(json_encode($data));
    } else {
        ajax_logged_out();
    }
}


function editor_image_upload_ajax()
{

    reset($_FILES);
    $temp = current($_FILES);

    $name = $temp['name'];
    $extention = '.' . pathinfo($name, PATHINFO_EXTENSION);
    $basename = basename($name, $extention);
    $basename = preg_replace('/[^A-Za-z0-9\_]/', '', $basename);

    $name = $basename . $extention;
    $temp['name'] = $name;

    //spk_sendEmail('paulkaufmann@hotmail.de', json_encode($temp), 'test');

    if (is_uploaded_file($temp['tmp_name'])) {

        /*
          If your script needs to receive cookies, set images_upload_credentials : true in
          the configuration and enable the following two headers.
        */
        /*header('Access-Control-Allow-Credentials: true');
        header('P3P: CP="There is no P3P policy."');*/

        // Sanitize input
        if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
            header("HTTP/1.1 400 Invalid file name.");
            return;
        }

        // Verify extension
        if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))) {
            header("HTTP/1.1 400 Invalid extension.");
            return;
        }


        $attachment = insider_upload_file($temp);

        // Respond to the successful upload with JSON.
        // Use a location key to specify the path to the saved image resource.
        // { location : '/your/uploaded/image/file'}


        die (json_encode(array('location' => wp_get_attachment_url($attachment['attachment_id']), 'attachment_id' => $attachment['attachment_id'])));
    } else {
        // Notify editor that the upload failed
        header("HTTP/1.1 500 Server Error");
        die();
    }
}

function onesignal_player_id_ajax()
{

    $p_id = sanitize_text_field($_POST["id"]);

    if (is_null($p_id)) {
        return;
    }

    $onesignal_player_ids = unserialize(get_user_meta(get_current_user_id())["onesignal_player_ids"][0]);

    if (!is_array($onesignal_player_ids)) {
        $onesignal_player_ids = [];
    }

    if (in_array($p_id, $onesignal_player_ids)) {
        return;
    }

    $onesignal_player_ids[] = $p_id;
    update_user_meta(get_current_user_id(), 'onesignal_player_ids', $onesignal_player_ids);
}

add_action('wp_ajax_nopriv_more_post_ajax', 'ajax_logged_out');
add_action('wp_ajax_nopriv_accounts_ajax', 'ajax_logged_out');
add_action('wp_ajax_nopriv_single_ajax', 'ajax_logged_out');
add_action('wp_ajax_more_post_ajax', 'more_post_ajax');
add_action('wp_ajax_single_ajax', 'single_ajax');
add_action('wp_ajax_accounts_ajax', 'account_ajax');
add_action('wp_ajax_edit_user', 'edit_user_ajax');

add_action('wp_ajax_nopriv_editor_image_upload', 'ajax_logged_out');
add_action('wp_ajax_editor_image_upload', 'editor_image_upload_ajax');

add_action('wp_ajax_nopriv_onesignal_player_id', 'ajax_logged_out');
add_action('wp_ajax_onesignal_player_id', 'onesignal_player_id_ajax');

