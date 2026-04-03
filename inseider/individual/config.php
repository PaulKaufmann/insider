<?php

//
//


//Insider Version 2.3.0
// public Facing version of config file

//region patch notes


/*
 * 2.3.0
 * add can_user_see_hidden_posts
 *
 * 2.2.1
 * add custom_css to account, small text adjustments
 *
 * 2.2
 * added support for php 8.3 and fixed small bugs along the way
 * changed video frame sizing to fix issues with portrait on mobile
 *
 * check if categories are always using single values, not arrays in config
 *   if (is_array($cat)) {
        $cat = $cat[0];
    }
 *
 * 2.1
 * added custom media type, removed fader
 * new function:
 * get_mediatypes
 * custom_uploading_html
 * custom_post_media_changed_js
 * custom_media_save
 * custom_media_postlistLables
 * render_custom_media_entry
 * render_custom_media_body
 * custom_media_edit_post_values
 *
 * 2.0
 * new global Parameter:
 * maxFileSize
 *
 * Feeds array, additional parameters:
 * sort_id+ top_category
 *
 * new functions:
 * can_user_sort_feed
 *
 * renamed getClientImageDir to get_Client_Image_Dir
 *  * renamed getServerImageDir to get_Server_Image_Dir
 * renamed recent_comment_reciever_by_category to recent_comment_receiver_by_category
 *
 * 1.2.6
 * fixed bug where all users could see "admin activate edit" menu
 * 1.2.5
 * changed sick note requirements (no picture but text)
 * 1.2.4
 * ...
 */
/*
// close region

__/\\\\\\\\\\\_        __/\\\\\_____/\\\_        _____/\\\\\\\\\\\___        __/\\\\\\\\\\\_        __/\\\\\\\\\\\\____        __/\\\\\\\\\\\\\\\_        ____/\\\\\\\\\_____
 _\/////\\\///__        _\/\\\\\\___\/\\\_        ___/\\\/////////\\\_        _\/////\\\///__        _\/\\\////////\\\__        _\/\\\///////////__        __/\\\///////\\\___
  _____\/\\\_____        _\/\\\/\\\__\/\\\_        __\//\\\______\///__        _____\/\\\_____        _\/\\\______\//\\\_        _\/\\\_____________        _\/\\\_____\/\\\___
   _____\/\\\_____        _\/\\\//\\\_\/\\\_        ___\////\\\_________        _____\/\\\_____        _\/\\\_______\/\\\_        _\/\\\\\\\\\\\_____        _\/\\\\\\\\\\\/____
    _____\/\\\_____        _\/\\\\//\\\\/\\\_        ______\////\\\______        _____\/\\\_____        _\/\\\_______\/\\\_        _\/\\\///////______        _\/\\\//////\\\____
     _____\/\\\_____        _\/\\\_\//\\\/\\\_        _________\////\\\___        _____\/\\\_____        _\/\\\_______\/\\\_        _\/\\\_____________        _\/\\\____\//\\\___
      _____\/\\\_____        _\/\\\__\//\\\\\\_        __/\\\______\//\\\__        _____\/\\\_____        _\/\\\_______/\\\__        _\/\\\_____________        _\/\\\_____\//\\\__
       __/\\\\\\\\\\\_        _\/\\\___\//\\\\\_        _\///\\\\\\\\\\\/___        __/\\\\\\\\\\\_        _\/\\\\\\\\\\\\/___        _\/\\\\\\\\\\\\\\\_        _\/\\\______\//\\\_
        _\///////////__        _\///_____\/////__        ___\///////////_____        _\///////////__        _\////////////_____        _\///////////////__        _\///________\///__




________/\\\\\\\\\_        _______/\\\\\______        __/\\\\\_____/\\\_        __/\\\\\\\\\\\\\\\_        __/\\\\\\\\\\\_        _____/\\\\\\\\\\\\_
 _____/\\\////////__        _____/\\\///\\\____        _\/\\\\\\___\/\\\_        _\/\\\///////////__        _\/////\\\///__        ___/\\\//////////__
  ___/\\\/___________        ___/\\\/__\///\\\__        _\/\\\/\\\__\/\\\_        _\/\\\_____________        _____\/\\\_____        __/\\\_____________
   __/\\\_____________        __/\\\______\//\\\_        _\/\\\//\\\_\/\\\_        _\/\\\\\\\\\\\_____        _____\/\\\_____        _\/\\\____/\\\\\\\_
    _\/\\\_____________        _\/\\\_______\/\\\_        _\/\\\\//\\\\/\\\_        _\/\\\///////______        _____\/\\\_____        _\/\\\___\/////\\\_
     _\//\\\____________        _\//\\\______/\\\__        _\/\\\_\//\\\/\\\_        _\/\\\_____________        _____\/\\\_____        _\/\\\_______\/\\\_
      __\///\\\__________        __\///\\\__/\\\____        _\/\\\__\//\\\\\\_        _\/\\\_____________        _____\/\\\_____        _\/\\\_______\/\\\_
       ____\////\\\\\\\\\_        ____\///\\\\\/_____        _\/\\\___\//\\\\\_        _\/\\\_____________        __/\\\\\\\\\\\_        _\//\\\\\\\\\\\\/__
        _______\/////////__        ______\/////_______        _\///_____\/////__        _\///______________        _\///////////__        __\////////////____




*/
function is_dev_version()
{
    return false;
}

function can_print_debuggs()
{
    return false;
}

/*

██████╗  █████╗ ███████╗██╗ ██████╗     ██████╗ ██████╗ ███╗   ██╗███████╗██╗ ██████╗ ██╗   ██╗██████╗  █████╗ ████████╗██╗ ██████╗ ███╗   ██╗
██╔══██╗██╔══██╗██╔════╝██║██╔════╝    ██╔════╝██╔═══██╗████╗  ██║██╔════╝██║██╔════╝ ██║   ██║██╔══██╗██╔══██╗╚══██╔══╝██║██╔═══██╗████╗  ██║
██████╔╝███████║███████╗██║██║         ██║     ██║   ██║██╔██╗ ██║█████╗  ██║██║  ███╗██║   ██║██████╔╝███████║   ██║   ██║██║   ██║██╔██╗ ██║
██╔══██╗██╔══██║╚════██║██║██║         ██║     ██║   ██║██║╚██╗██║██╔══╝  ██║██║   ██║██║   ██║██╔══██╗██╔══██║   ██║   ██║██║   ██║██║╚██╗██║
██████╔╝██║  ██║███████║██║╚██████╗    ╚██████╗╚██████╔╝██║ ╚████║██║     ██║╚██████╔╝╚██████╔╝██║  ██║██║  ██║   ██║   ██║╚██████╔╝██║ ╚████║
╚═════╝ ╚═╝  ╚═╝╚══════╝╚═╝ ╚═════╝     ╚═════╝ ╚═════╝ ╚═╝  ╚═══╝╚═╝     ╚═╝ ╚═════╝  ╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝   ╚═╝ ╚═════╝ ╚═╝  ╚═══╝

 */

//region basic configuration
function get_vimeo_access_token()
{
    return '338964740236e1ef099c063909f2654c';
}

add_filter('wp_mail_from', 'get_sender_Email');
add_filter('wp_mail_from_name', 'get_sender_Email');

//sender email of this site
function get_sender_Email()
{
    return 'noreply@muxd.de';
}

function get_domain()
{
    return 'dev.muxd.de';
}

//name of this site
function get_plattform_name()
{
    return get_bloginfo('name');
}

//e-mail address for recieving sick notes
function get_sick_note_Email()
{
    return 'admin@muximum.de';
}

function get_team_Email()
{
    return 'info@muximum.de';
}

//usernames of comment admins (will get emails about new comments every day)
function get_comment_admins()
{
    return array('1', 'benachrichtigungen');
}

//these admins will get notified if a user requests authorisation of a post. Leave empty to turn off "request post activation"
function get_posting_admins()
{
    //return null;
    return array(1);
}


add_action('phpmailer_init', 'send_smtp_email');
function send_smtp_email($phpmailer)
{

    $phpmailer->isSMTP();
    $phpmailer->Host = 'ssl://smtp.strato.de';
    $phpmailer->SMTPAuth = true;
    $phpmailer->Port = 465;
    $phpmailer->Username = 'noreply@muxd.de';
    $phpmailer->Password = 'jz7wn()EFMTY';
    $phpmailer->SMTPSecure = 'tls';
    $phpmailer->From = 'noreply@muxd.de';
    $phpmailer->FromName = 'insider';
    $phpmailer->SMTPDebug = 2;
    $phpmailer->isHtml(true);
    $phpmailer->Debugoutput = function ($str, $level) {
        global $log_email;
        if ($log_email) {
            $filename = get_stylesheet_directory() . '/mail_logs/' . 'mail_' . date('Y_m_d') . '.txt';
            // log all output for mails
            $fp = fopen($filename, 'a');
            fwrite($fp, $level . ': ' . $str);
            fclose($fp);
        }
    };
    // add new line before each mail
    $filename = get_stylesheet_directory() . '/mail_logs/' . 'mail_' . date('Y_m_d') . '.txt';
    $fp = fopen($filename, 'a');
    fwrite($fp, PHP_EOL);
    fclose($fp);
}

function impressum_link()
{
    echo 'https://www.muximum.de/home/Impressum';
}


function datenschutz_link()
{
    echo 'https://www.muximum.de/home/Datenschutz';
}

function get_idea_receiver($slug)
{
    return array('receiver' => get_sick_note_Email(),
        'cc_receiver' => false);
}

function can_ideas_be_submitted()
{
    return true;
}

function can_sick_notes_be_submitted()
{
    return true;
}

$fallback_thumbnail_media_id = 5843;
$maxFileSize = 15;

//endregion

//
/*


 ██████╗ █████╗ ████████╗███████╗ ██████╗  ██████╗ ██████╗ ██╗███████╗███████╗     █████╗ ███╗   ██╗██████╗     ███████╗███████╗███████╗██████╗ ███████╗
██╔════╝██╔══██╗╚══██╔══╝██╔════╝██╔════╝ ██╔═══██╗██╔══██╗██║██╔════╝██╔════╝    ██╔══██╗████╗  ██║██╔══██╗    ██╔════╝██╔════╝██╔════╝██╔══██╗██╔════╝
██║     ███████║   ██║   █████╗  ██║  ███╗██║   ██║██████╔╝██║█████╗  ███████╗    ███████║██╔██╗ ██║██║  ██║    █████╗  █████╗  █████╗  ██║  ██║███████╗
██║     ██╔══██║   ██║   ██╔══╝  ██║   ██║██║   ██║██╔══██╗██║██╔══╝  ╚════██║    ██╔══██║██║╚██╗██║██║  ██║    ██╔══╝  ██╔══╝  ██╔══╝  ██║  ██║╚════██║
╚██████╗██║  ██║   ██║   ███████╗╚██████╔╝╚██████╔╝██║  ██║██║███████╗███████║    ██║  ██║██║ ╚████║██████╔╝    ██║     ███████╗███████╗██████╔╝███████║
 ╚═════╝╚═╝  ╚═╝   ╚═╝   ╚══════╝ ╚═════╝  ╚═════╝ ╚═╝  ╚═╝╚═╝╚══════╝╚══════╝    ╚═╝  ╚═╝╚═╝  ╚═══╝╚═════╝     ╚═╝     ╚══════╝╚══════╝╚═════╝ ╚══════╝


*/


//region Categories and Feeds

$Feeds = array(
    'Home' => 1,
    'Stellen' => 63,
    'SchwarzesBrett' => 69,
    'Gewinnspiele' => 68,
    'Boni' => 70,

);

function get_feeds()
{
    global $Feeds;
    return array(
        array(
            'title' => 'Home',
            'cat' => array($Feeds['Home'], $Feeds['Stellen'], $Feeds['Gewinnspiele']),
            'sort_id' => '6461',
            'top_category' => '77',
            'link' => '/',
        ),
        array(
            'cat' => $Feeds['Stellen'],
            'sort_id' => '6427',
            'link' => '/miteinander'
        ),
        array(
            'cat' => $Feeds['SchwarzesBrett'],
            'sort_id' => '6470',
            'link' => '/schwarzes-brett'
        ),
        array(
            'cat' => $Feeds['Gewinnspiele'],
            'sort_id' => '6503',
            'link' => '/gewinnspiele'
        ),
        array(
            'cat' => $Feeds['Boni'],
            'small' => true,
            'sort_id' => '6459',
            'link' => '/boni'
        )
    );
}


function admin_activation_needed($post_id, $categories = null, $media_type = null): bool
{

    return false;

    if ($post_id != null) {
        $post_later_activated = get_post_custom($post_id)['post_later_activated'][0] ?? null;
        $categories = get_post($post_id)->post_category;
        $media_type = get_post_meta($post_id, 'media_type', true);
        $post_status = get_post_status($post_id);
    } else {
        $post_later_activated = false;
        $post_status = 'draft';
    }

    if ($post_status != 'draft' ||
        $post_later_activated == 'true') {
        return false;
    }

    foreach ($categories as $cat) {
        if (in_array($cat, no_admin_activation_needed_categories())) {
            return false;
        }
    }

    return !empty(get_posting_admins());
}

function admin_activation_needed_for_edit($post_id, $values_set): bool
{
    return false;

    if (get_post_status($post_id) == 'draft') {
        return false;
    }

    $categories_objects = get_post($post_id)->post_category;
    $aanfec = admin_activation_needed_for_edit_catecories();
    $activateableCategory = false;
    foreach ($categories_objects as $cat) {
        if (in_array($cat, $aanfec)) {
            $activateableCategory = true;
        }
    }

    if (!$activateableCategory) {
        return false;
    }

    if (!$values_set) {
        return true;
    }

    $aae_post_content = get_post_meta($post_id, 'aae_post_content')[0];
    $aae_post_excerpt = get_post_meta($post_id, 'aae_post_excerpt')[0];
    $aae_documents_attachment_id = get_post_meta($post_id, 'aae_documents_attachment_id')[0];

    return $aae_post_content || $aae_post_excerpt || $aae_documents_attachment_id;
}

function category_has_drafts($cat): bool
{
    if (is_array($cat)) {
        $cat = $cat[0];
    }

    global $Feeds;
    return $cat != $Feeds['SchwarzesBrett'] ;
}


$NOTIFICATION_SETTINGS = array(
    'NEVER' => -1,
    'ALWAYS' => 1,
    'MANUAL_DEFAULT_NO' => 2,
    'MANUAL_DEFAULT_YES' => 3
);

function get_notification_settings($cat)
{
    if (is_array($cat)) {
        $cat = $cat[0];
    }

    global $Feeds;
    global $NOTIFICATION_SETTINGS;

    $settings = array(
        $Feeds['Home'] => $NOTIFICATION_SETTINGS['MANUAL_DEFAULT_NO'],
        $Feeds['Boni'] => $NOTIFICATION_SETTINGS['MANUAL_DEFAULT_NO'],
        $Feeds['Stellen'] => $NOTIFICATION_SETTINGS['MANUAL_DEFAULT_YES'],
        $Feeds['Gewinnspiele'] => $NOTIFICATION_SETTINGS['MANUAL_DEFAULT_YES'],
        $Feeds['SchwarzesBrett'] => $NOTIFICATION_SETTINGS['NEVER']);

    return $settings[$cat];
}

function get_notification_settings_categories()
{
    global $Feeds;
    //return array($Feeds['Gewinnspiele'], $Feeds['Stellen']);
    return array_values($Feeds);
}

// notifications for these categories can be triggered manually
function get_categories_with_manual_notifications(): array
{
    global $Feeds;
    return array($Feeds['Home'], $Feeds['Boni'], $Feeds['Gewinnspiele'], $Feeds['Stellen'], $Feeds['SchwarzesBrett']);
}

//caution: first element of each array must not be included in category_by_media_type
function get_categories_by_role()
{
    global $Feeds;
    return array(
        "business_partner" => array($Feeds['Boni']),
        "subscriber" => array($Feeds['SchwarzesBrett']),
        "contributor" => array($Feeds['SchwarzesBrett'], $Feeds['Home'], $Feeds['Stellen']),
        "author" => array($Feeds['SchwarzesBrett'], $Feeds['Home'], $Feeds['Stellen'], $Feeds['Gewinnspiele']),
        "administrator" => array_values($Feeds),
        "Developer" => array_values($Feeds),
    );
}


function force_comment_categories()
{
    global $Feeds;
    return array($Feeds['Boni'] => true);
}

$RECENT_COMMENTS_NONE = 0;
$RECENT_COMMENTS_ADMIN = 1;
$RECENT_COMMENTS_AUTHOR = 2;
function recent_comment_receiver_by_category()
{
    global $Feeds;
    global $RECENT_COMMENTS_NONE, $RECENT_COMMENTS_AUTHOR, $RECENT_COMMENTS_ADMIN;
    return array('1' => $RECENT_COMMENTS_ADMIN,
        $Feeds['Boni'] => $RECENT_COMMENTS_AUTHOR,
        $Feeds['Stellen'] => $RECENT_COMMENTS_ADMIN,
        $Feeds['SchwarzesBrett'] => $RECENT_COMMENTS_AUTHOR);
}

function no_admin_activation_needed_categories()
{
    global $Feeds;
    return array($Feeds['SchwarzesBrett']);
}

function admin_activation_needed_for_edit_catecories()
{
    global $Feeds;
    return array($Feeds['Boni']);
}

function hidden_categories()
{
    global $Feeds;
    return array($Feeds['Gewinnspiele']);
}

function get_category_by_media_type($media_type)
{
    global $Feeds;
    switch ($media_type) {
        case ('prize_draw'):
            return $Feeds['Gewinnspiele'];
            break;
        case ('job'):
            return $Feeds['Stellen'];
            break;
        default:
            return null;
    }
}

function category_forces_media($category)
{
    return null;
}


function has_favorite_button($category)
{
    if (is_array($category)) {
        $category = $category[0];
    }

    global $Feeds;
    global $current_user;

    if (in_array('business_partner', $current_user->roles)) {
        return false;
    }

    if (is_null($category)) {
        return false;
    }
    return $Feeds['Boni'] == $category;
}


function has_filter_button($category)
{

    if (is_array($category)) {
        $category = $category[0];
    }

    global $Feeds;
    if (is_null($category)) {
        return false;
    }
    return $Feeds['Boni'] == $category;
}

function get_filter_tags($category)
{

    if (is_array($category)) {
        $category = $category[0];
    }

    global $Feeds;
    $ft = array(
        $Feeds['Boni'] => array(
            72, 73, 74, 75,
        )
    );
    return $ft[$category];
}

function get_mediaSelect_html($user)
{
    $toReturn = '<select id = "mediaSelect" name = "media_type" >';


    if (in_array('subscriber', $user->roles)) {
        $toReturn .= '<option value="none">Bitte wählen</option><option selected value="article">Artikel</option>';
        $toReturn .= '</select>';
        return $toReturn;
    }

    $toReturn .= '<option selected value="none">Bitte wählen</option>
                                <option value="videoEmbed">Video (eingebettet)</option>
                                <option value="article">Artikel</option>
                                <option value="audio">Audio</option>
                                <option value="gallery">Galerie</option>';

    if (can_user_post_prize_draws($user->ID)) {
        $toReturn .= '<option value="prize_draw">Gewinnspiel</option>';
    }

    if (can_user_post_videos($user->ID)) {
        $toReturn .= '<option value="video">Video</option>';
    }
    $toReturn .= '</select>';
    return $toReturn;

}

function get_post_per_page($category)
{
    if (is_array($category)) {
        $category = $category[0];
    }

    global $Feeds;
    return 10;

}


// 2.1 custom posttype


function get_mediatypes()
{
    return array('video', 'audio', 'gallery', 'prize_draw', 'article', 'videoEmbed');
}


function custom_uploading_html()
{
    echo '<div id="videoEmbedCont" style="display: none">
<label for="videoEmbedLink">Einbettungs-Link des Videos</label>
                    <input type="text" placeholder="https://sparkassen-mediacenter.de/mediacenter/mediathek/player/extern?id=0_22sb35dd&blz=73350000&autoplay=false" tabindex="0" size="16" name="videoEmbedLink" id="videoEmbedLink"/>
                </div>';
}

function custom_post_media_changed_js()
{
    ?>
    case "videoEmbed" :
    // thumbnail.style.display = "none";
    document.getElementById("videoEmbedCont").style.display = "block";


    break;
    <?php
}

//will be called with every media edit! check for type
function custom_media_save($mediatype, $edit_postId = null)
{
    global $form_errors;
    if ($mediatype === 'videoEmbed') {

        if ($edit_postId) {
            __update_post_meta($edit_postId, 'video_Embed_Link', sanitize_text_field($_POST['videoEmbedLink']));

            saveDocuments($edit_postId);

        } else {
            $postId = wp_insert_post($GLOBALS['post']);
            if ($postId > 0) {
                __update_post_meta($postId, 'media_type', 'videoEmbed');
                savePostThumbnail($postId);
                __update_post_meta($postId, 'video_Embed_Link', sanitize_text_field($_POST['videoEmbedLink']));

                link_attachmentIds_to_post($postId);
                saveDocuments($postId);

                handle_post_later($postId);

                $GLOBALS['post_url'] = get_permalink($postId);

            } else {
                $form_errors[] = 'Fehler beim Erstellen des Beitrags.';
            }
        }
    }
}

function custom_media_postlistLables($mediatype)
{
    $imageDir = get_Server_Image_Dir();
    return array('<div class="post-list-lable video-lable"><h5>Zum Video</h5>' . file_get_contents($imageDir . "/video-icon.svg") . '</div>', '<div class="post-list-lable video-lable" > ' . file_get_contents($imageDir . "/video-icon.svg") . '<h5 > Video</h5 ></div >');
}

function render_custom_media_entry($mediatype)
{

    if ($mediatype == 'videoEmbed') {
        global $custom;
        $link = $custom['video_Embed_Link'][0];

        return '<div class="video-container-embed">
<iframe src="' . $link . '" frameborder="0" scrolling="no" allowfullscreen></iframe></div>
<style>

.video-container-embed {
    position: relative;
    width: 100%;
max-height: 65vw;
  overflow: hidden;
  height: 90vh;
}

.video-container-embed iframe {
    width: 100%;
    height: 100%;
}

@media only screen and (min-width: 1125px) {
.video-container-embed {
    position: relative;
    width: 100%;
    max-height: 650px;
    overflow: hidden;
    height: 90vh;
}
}

</style>';
    }
}

function render_custom_media_body($mediatype)
{

}

function custom_media_edit_post_values($edit_Post, $edit_Post_meta)
{
    echo "jQuery('#videoEmbedLink').val('" . $edit_Post_meta['video_Embed_Link'][0] . "');";
}

//endregion


/*

███████╗██████╗  ██████╗ ███╗   ██╗████████╗███████╗███╗   ██╗██████╗
██╔════╝██╔══██╗██╔═══██╗████╗  ██║╚══██╔══╝██╔════╝████╗  ██║██╔══██╗
█████╗  ██████╔╝██║   ██║██╔██╗ ██║   ██║   █████╗  ██╔██╗ ██║██║  ██║
██╔══╝  ██╔══██╗██║   ██║██║╚██╗██║   ██║   ██╔══╝  ██║╚██╗██║██║  ██║
██║     ██║  ██║╚██████╔╝██║ ╚████║   ██║   ███████╗██║ ╚████║██████╔╝
╚═╝     ╚═╝  ╚═╝ ╚═════╝ ╚═╝  ╚═══╝   ╚═╝   ╚══════╝╚═╝  ╚═══╝╚═════╝

 */

//region frontend customizations

function tinymce_editor_js()
{
    echo "
    //this is used in the onselected listender to determine if image is beeing selected as a result of beeing freshly uploaded
    var imageBeingUploaded = false;

    // tiny mce initialisation
    tinymce . init({
            selector: '#content',
            convert_fonts_to_spans: true,
            paste_as_text: true,
            height: '440',
            skin: 'insider-tinymceskin',
            skin_url: '/wp-content/themes/inseider/css/insider-tinymceskin',
            content_css: '/wp-content/themes/inseider/css/insider-tinymceskin/content.min.css',
            menubar: '',
            elementpath: false,
            onchange_callback: 'myCustomOnChangeHandler',
            branding: false,
            plugins: 'paste tabfocus image lists link contextmenu',
            contextmenu: false,
            tabfocus_elements: 'subheading,post_tags',
            language: 'de',
            images_upload_url: the_ajax_script . ajaxurl + '?action=editor_image_upload_ajax',
            toolbar: 'undo redo | fontsizeselect | forecolor | bold italic | alignleft aligncenter alignright alignjustify | numlist bullist | outdent indent | link | image ',
            setup: function (ed) {
    ed . on('init', function (e) {
        jQuery('#content_ifr') . attr('tabindex', jQuery('#content') . attr('tabindex'));
        jQuery('#content') . attr('tabindex', null);
    });
    ed . on('ObjectSelected', function (node) {
        if (!imageBeingUploaded) {
            return true;
        }
        imageBeingUploaded = false;
        tinyMCE . activeEditor . selection . select(tinyMCE . activeEditor . getBody(), true);
        tinyMCE . activeEditor . selection . collapse(false);
        tinymce . activeEditor . execCommand('mceInsertContent', false, '<br>');
        tinyMCE . activeEditor . selection . select(tinyMCE . activeEditor . getBody(), true);
        tinyMCE . activeEditor . selection . collapse(false);
    });
},
            images_dataimg_filter: function (img) {
    console . log(img);
    return false;  // blocks the upload of <img> elements with the attribute 'internal-blob'.
},
            images_upload_handler: function (blobInfo, success, failure, progress) {

    var
    xhr, formData;

                xhr = new XMLHttpRequest();
                xhr . withCredentials = false;
                xhr . open('POST', the_ajax_script . ajaxurl + '?action=editor_image_upload');

                xhr . upload . onprogress = function (e) {
                    progress(e . loaded / e . total * 100);
                };

                xhr . onload = function () {
                    var
                    json;

                    if (xhr . status < 200 || xhr . status >= 300) {
                        failure('HTTP Error: ' + xhr . status);
                        return;
                    }

                    if (xhr . responseText == 'false') {
                        location . reload();
                        failure();
                        return;
                    }

                    json = JSON . parse(xhr . responseText);
                    if (!json || typeof json . location != 'string') {
                        failure('Invalid JSON: ' + xhr . responseText);
                        return;
                    }
                    document . getElementById('content_attachment_ids') . value += json . attachment_id + ' ,';
                    imageBeingUploaded = true;
                    success(json . location);
                };

                xhr . onerror = function () {
                    failure('Image upload failed due to a XHR Transport error. Code: ' + xhr . status);
                };

                formData = new FormData();
                formData . append('file', blobInfo . blob(), blobInfo . filename());

                xhr . send(formData);
            }
        });


        tinymce . activeEditor . on('focus', function (e) {
            document . querySelector('.tox-tinymce') . classList . remove('focus');
            document . querySelector('.tox-tinymce') . classList . add('focus');
        });

        tinymce . activeEditor . on('blur', function (e) {
            document . querySelector('.tox-tinymce') . classList . remove('focus');
        });";

}


function get_internal_mail_html()
{
    return '';
}

function get_prize_draw_template_datalists()
{
    return "";
}


function get_prize_draw_templates()
{
    return "";
}

function file_uploader_captions()
{
    return '{
                button: function (e) {
                    return (1 == e.limit ? "Datei" : "Dateien") + " auswählen"
                },
                feedback2: function (e) {
                    return e.length + " " + (1 == e.length ? "Datei" : "Dateien") + " ausgewählt"
                },
                confirm: "Speichern",
                cancel: "Schließen",
                name: "Name",
                type: "Typ",
                size: "Größe",
                dimensions: "Format",
                duration: "Länge",
                crop: "Crop",
                rotate: "Rotieren",
                sort: "Sortieren",
                open: "Öffnen",
                download: "Herunterladen",
                remove: "Löschen",
                drop: "Die Dateien hierher ziehen, um sie hochzuladen",
                paste: \'<div class="fileuploader - pending - loader"></div> Eine Datei wird eingefügt. Klicken Sie hier zum abzubrechen\',
                removeConfirmation: "Möchten Sie diese Datei wirklich löschen ? ",
                errors: {
                    filesLimit: function (e) {
                        return "Nur ${limit} " + (1 == e.limit ? "Datei darf" : "Dateien dürfen") + " hochgeladen werden . "
                    },
                    filesType: "Nur ${extensions} Dateien dürfen hochgeladen werden . ",
                    fileSize: "${name} ist zu groß!Bitte wählen Sie eine Datei bis zu ${fileMaxSize} MB . ",
                    filesSizeAll: "Die ausgewählten Dateien sind zu groß!Bitte wählen Sie Dateien bis zu ${maxSize} MB . ",
                    fileName: "Eine Datei mit demselben Namen ${name} ist bereits ausgewählt . ",
                    remoteFile: "Remote - Dateien sind nicht zulässig . ",
                    folderUpload: "Ordner sind nicht erlaubt . "
                }
            }';
}

//weather or not a specific post has a 'share' link that opens the email client
function has_share_link($id)
{
    return true;
}

function idea_receiver_select_html()
{
    echo '<option value="Building">Vorstandsbüro</option><option value="Building">Logistik und Gebäude</option>';

}

function show_author_in_post($post_id): bool
{
    return true;
}

function customize_uploading_fields($role): array
{
    if ($role == 'business_partner') {

        return array(
            'hidden' => array(
                '#titleCont',
                '#thumbnailCont',
                '#categoryCont',
                '#tagsCont'
            ),
            //disabled values are empty when uploaded!
            'disabled' => array(),
            'disabled_class' => array(
                '#titleCont label'
            ),
            //the explicit key is the param to what the html should be changed
            'change_html' => array(
                'Titel (max. 50 Zeichen)' => '#subheadingLabel'
            )
        );

    } elseif ($role == 'subscriber') {

        return array(
            'disabled_class' => array(
                '#mediaSelect'
            ),
            'hidden' => array(
                '#tagsCont',
                '#thumbnailCont'
            )
        );
    }
    return array();
}

add_filter('genesis_seo_title', 'genesis_sample_header_title', 10, 3);
/**
 * Removes the link from the hidden site title if a custom logo is in use.
 *
 * Without this filter, the site title is hidden with CSS when a custom logo
 * is in use, but the link it contains is still accessible by keyboard.
 *
 * @param string $title The full title.
 * @param string $inside The content inside the title element.
 * @param string $wrap The wrapping element name, such as h1.
 * @return string The site title with anchor removed if a custom logo is active.
 * @since 1.2.0
 *
 */
function genesis_sample_header_title($title, $inside, $wrap)
{

    if (has_custom_logo()) {
        $inside = get_bloginfo('name');
    }

    //todo dev customization
    global $current_user;
    //$inside = $current_user->roles[0];
    return sprintf('<h1 class="site-title">%1$s</h1>', $inside);

}


function get_custom_post_signature($post_id): string
{
    return '';
}

function has_legacy_gallery()
{
    return false;
}


add_action('genesis_before', 'sk_replace_menu_in_primary');
/**
 * Conditionally replace Custom Menu in Primary Navigation.
 *
 * @author Sridhar Katakam
 * @link http://sridharkatakam.com/conditionally-replace-navigation-menu-genesis/
 */
function sk_replace_menu_in_primary()
{
    global $current_user;
    if (in_array('author', $current_user->roles)) {
        add_filter('wp_nav_menu_args', 'show_author_menu');
    }

    if (in_array('contributor', $current_user->roles)) {
        add_filter('wp_nav_menu_args', 'show_author_menu');
    }

    if (in_array('administrator', $current_user->roles)) {
        add_filter('wp_nav_menu_args', 'show_admin_menu');
    }

    if (in_array('business_partner', $current_user->roles)) {
        add_filter('wp_nav_menu_args', 'show_business_partner_menu');
    }


}


function show_business_partner_menu($args)
{
    if ($args['theme_location'] == 'primary') {
        $args['menu'] = 'business_partner_menu';
    }
    return $args;
}


function show_author_menu($args)
{
    if ($args['theme_location'] == 'primary') {
        $args['menu'] = 'author_menu';
    }
    return $args;
}

function show_admin_menu($args)
{
    if ($args['theme_location'] == 'primary') {
        $args['menu'] = 'admin_menu';
    }
    return $args;
}


function can_change_notification_settings()
{
    if (!in_array('business_partner', wp_get_current_user()->roles)) {
        return true;
    }
    return false;
}

//todo make available everywhere
function custom_css($page, $post_id = null)
{
    /* if ($page = 'communication') {
         return '#communication-initial-container,#communication_receiver_cont{display: none}"';
     }
        */
    if ($page = 'account') {
        return '#main_email_options, .notification-form > div:first-child{display: none}"';
    }

    return '';
}

function get_invitation_attachment()
{
    return '';
    //    return get_stylesheet_directory().' / assets / Anleitung MIA . pdf';
}

//todo make available everywhere
function custom_js($page, $id = null)
{
    global $Feeds;
    /* if ($page = 'communication') {
         return 'showCommunicationContainer(2);';
     }*/
    return '';
}

function external_files()
{

    return wp_get_attachment_url(json_decode(get_post_custom(5548)['documents_attachment_id'][0])[0]);

    //return home_url();
}

function get_input_max_length($key)
{

    if (in_array('business_partner', wp_get_current_user()->roles) && $key == 'subheading') {
        return 'maxlength = "50"';
    }
}

//endregion


/*

██████╗  ██████╗ ██╗     ███████╗███████╗
██╔══██╗██╔═══██╗██║     ██╔════╝██╔════╝
██████╔╝██║   ██║██║     █████╗  ███████╗
██╔══██╗██║   ██║██║     ██╔══╝  ╚════██║
██║  ██║╚██████╔╝███████╗███████╗███████║
╚═╝  ╚═╝ ╚═════╝ ╚══════╝╚══════╝╚══════╝

 */

//region roles and capabilites

function can_user_sort_feed($top_category)
{
    if (is_array($top_category)) {
        $top_category = $top_category[0];
    }

    global $Feeds;
    if ($top_category == $Feeds['Boni']) {
        return false;
    }
    if (in_array('administrator', wp_get_current_user()->roles) || current_user_can('Developer')) {
        return true;
    }
}

function can_user_notify_manually($post_id, $user_id)
{

    $user = get_user_by('ID', $user_id);

    if (get_post_status($post_id) != 'publish') {
        return false;
    }

    // users have been notified about this version already
    if (get_post_meta($post_id, 'notified_time', true) > 0 && get_post_meta($post_id, 'post_edited_after_last_notification', true) != 'true') {
        return false;
    }


    if (get_post_meta($post_id, 'disable_manual_notifications', true) == 'true') {
        return false;
    }

    if (!array_intersect(get_post($post_id)->post_category, get_categories_with_manual_notifications())) {
        return false;
    }

    $editor = false;
    foreach (get_post($post_id)->post_category as $cat) {

        if (is_user_category_editor($user->ID, $cat)) {
            $editor = true;
        }
    }
    // permissions are granted for admins and editors always and for authors only if no admin activation is required
    if ((in_array('administrator', $user->roles) || in_array('contributor', $user->roles) ||
        !admin_activation_needed($post_id) && in_array('author', $user->roles))) {
        return true;
    }
    return false;
}

function can_user_edit_post($post_id, $user_id)
{
    $user = get_user_by('ID', $user_id);
    $post = get_post($post_id);

    if ($post->post_author == $user_id) {
        return true;
    }

    $editor = false;
    foreach ($post->post_category as $cat) {
        if (is_user_category_editor($user->ID, $cat)) {
            $editor = true;
        }
    }

    $business_partners_own_post = false;
    if (in_array('business_partner', $user->roles) && $post_id == get_user_meta($user->ID, 'business_parter_post_id', true)) {
        $business_partners_own_post = true;

    }

    if ((in_array('administrator', $user->roles) ||
        $editor ||
        $business_partners_own_post ||
        in_array('author', $user->roles))) {
        return true;
    }
    return false;

}

function can_user_delete_post($post_id, $user_id)
{
    $user = get_user_by('ID', $user_id);


    $editor = false;
    foreach (get_post($post_id)->post_category as $cat) {
        if (is_user_category_editor($user_id, $cat)) {
            $editor = true;
        }
    }

    $is_author = get_post($post_id)->post_author == $user->ID;

    // permissions are granted for admins and editors always and for authors only if no admin activation is required
    if (in_array('administrator', $user->roles) || $is_author || $editor || in_array('author', $user->roles)) {
        return true;
    }

    return false;
}


function can_user_activate_post($post_id, $user_id)
{

    $user = get_user_by('ID', $user_id);

    $post_later_activated = get_post_custom($post_id)['post_later_activated'][0] ?? null;

    // only for posts that are drafts and not prize draws and dont have an active post later trigger
    if (get_post_status($post_id) != 'draft' ||
        $post_later_activated == 'true') {
        return false;
    }

    $editor = false;
    foreach (get_post($post_id)->post_category as $cat) {
        if (is_user_category_editor($user->ID, $cat)) {
            $editor = true;
        }
    }

    // permissions are granted for admins and editors always and for authors only if no admin activation is required
    if (in_array('administrator', $user->roles) || $editor) {
        return true;
    }

    return false;
}


function can_user_activate_edit($post_id, $user_id)
{

    $custom = get_post_custom($post_id);

    if (empty($custom['aae_post_excerpt']) && empty($custom['aae_post_content']) && empty($custom['aae_documents_attachment_id'])) {
        return false;
    }

    $user = get_user_by('ID', $user_id);

    $editor = false;
    foreach (get_post($post_id)->post_category as $cat) {
        if (is_user_category_editor($user->ID, $cat)) {
            $editor = true;
        }
    }

    // permissions are granted for admins, editors and authors
    if (in_array('administrator', $user->roles) || $editor || in_array('author', $user->roles)) {
        return true;
    }
    return false;
}

function can_user_request_activate_post($post_id, $user_id)
{
    if (can_user_activate_post($post_id, $user_id)) {
        return false;
    }

    if (get_post_meta(get_the_ID(), 'requested_activation')) {
        return false;
    }

    $user = get_user_by('ID', $user_id);
    if (admin_activation_needed($post_id)) {

        return get_post($post_id)->post_author == $user->ID &&
            get_post_status($post_id) == 'draft' &&
            get_post_meta($post_id, 'media_type', true) != 'prize_draw';
    }

    if (admin_activation_needed_for_edit($post_id, true)) {

        $business_partners_own_post = false;
        if (in_array('business_partner', $user->roles) && $post_id == get_user_meta($user->ID, 'business_parter_post_id', true)) {
            $business_partners_own_post = true;
        }

        return (get_post($post_id)->post_author == $user->ID ||
                $business_partners_own_post) &&
            get_post_status($post_id) == 'publish';
    }
    return false;

}

function can_user_post_videos($user_id)
{
    $user = get_user_by('ID', $user_id);

    if (in_array('administrator', $user->roles) || in_array('author', $user->roles) || in_array('contributor', $user->roles)) {
        return true;
    }
    return false;
}


function can_user_post_prize_draws($user_id)
{
    $user = get_user_by('ID', $user_id);

    if (in_array('administrator', $user->roles) || in_array('author', $user->roles) || in_array('contributor', $user->roles)) {
        return true;
    }
    return false;
}

function is_user_category_editor($user_id, $cat)
{
    global $Feeds;
    $user = get_user_by('ID', $user_id);
    if ($cat == $Feeds['Stellen'] && in_array('contributor', $user->roles)) {
        return true;
    }
    return false;
}

/**
 * @param $user object user who is trying to acess uploading page
 * @return bool restricted mode
 */
function handle_uploading_restrictions($user)
{


    if ((!in_array('administrator', $user->roles)) && (!in_array('author', $user->roles))) {
        if (in_array('subscriber', $user->roles)) {
            return true;
        } elseif (in_array('contributor', $user->roles)) {
            return false;
        } elseif (in_array('business_partner', $user->roles)) {
            if ($_GET['edit_post'] != get_user_meta($user->ID, 'business_parter_post_id', true)) {
                wp_safe_redirect(home_url());
            }
            return true;
        } else {
            wp_safe_redirect(home_url());
            return true;
        }
    }

    return false;
}

function handle_user_management_restrictions($user)
{
    if (!in_array('administrator', $user->roles)) {
        wp_safe_redirect(home_url());
        exit;
    }

    return false;
}

function user_can_see_viewcounter($roles)
{

    return true;
    //return in_array('administrator',$roles) || in_array('author', $roles)

}

function can_user_see_hidden_posts()
{
    global $current_user;
    return in_array('administrator',$current_user->roles) || in_array('author', $current_user->roles);
}

// Change default role names
add_action('init', 'inseider_change_role_name');
function inseider_change_role_name()
{
    global $wp_roles;
    if (!isset($wp_roles))
        $wp_roles = new WP_Roles();
    $wp_roles->roles['subscriber']['name'] = 'Benutzer';
    $wp_roles->role_names['subscriber'] = 'Benutzer';

    $wp_roles->roles['author']['name'] = 'Autor';
    $wp_roles->role_names['author'] = 'Autor';

    $wp_roles->roles['administrator']['name'] = 'Administrator';
    $wp_roles->role_names['administrator'] = 'Administrator';

    $wp_roles->roles['contributor']['name'] = 'Redaktion';
    $wp_roles->role_names['contributor'] = 'Redaktion';
}

function get_role_create_select_options()
{

    global $current_user;

    $to_return = ' <option value = "subscriber" > Benutzer</option>
                    <option value = "author" > Autor</option >
                    <option value = "contributor" > Redaktion</option >
                    <option value = "administrator" > Administrator</option >
';

    if (in_array('Developer', $current_user->roles)) {
        $to_return .= ' <option value = "business_partner" > Boni - Anbieter</option > ';
    }
    return $to_return;
}


//endregion

/*

███████╗███████╗ ██████╗██╗   ██╗██████╗ ██╗████████╗██╗   ██╗
██╔════╝██╔════╝██╔════╝██║   ██║██╔══██╗██║╚══██╔══╝╚██╗ ██╔╝
███████╗█████╗  ██║     ██║   ██║██████╔╝██║   ██║    ╚████╔╝
╚════██║██╔══╝  ██║     ██║   ██║██╔══██╗██║   ██║     ╚██╔╝
███████║███████╗╚██████╗╚██████╔╝██║  ██║██║   ██║      ██║
╚══════╝╚══════╝ ╚═════╝ ╚═════╝ ╚═╝  ╚═╝╚═╝   ╚═╝      ╚═╝
*/

//region Security option


/* Renew cookie at every page load */
function renew_wp_cookie()
{
    if (is_user_logged_in()) {
        foreach ($_COOKIE as $c_key => $c_value) {
            $needle = "wordpress_logged_in_";
            $len = strlen($needle);
            if (substr($c_key, 0, $len) === $needle) {
                setcookie($c_key, $c_value, time() + get_timestamp_offset() + 5000);
            }

            $needle = "wfwaf-authcookie";
            $len = strlen($needle);
            if (substr($c_key, 0, $len) === $needle) {
                setcookie($c_key, $c_value, time() + get_timestamp_offset() + 5000);
            }

        }
    }
}


add_filter('auth_cookie_expiration', 'inseider_expiration_filter', 99, 3);
function inseider_expiration_filter($seconds, $user_id, $remember)
{
    //30 minutes
    $expiration = 60 * 60 * 5000;

    //http://en.wikipedia.org/wiki/Year_2038_problem
    if (PHP_INT_MAX - time() < $expiration) {
        //Fix to a little bit earlier!
        $expiration = PHP_INT_MAX - time() - 5;
    }

    return $expiration;
}


//blocks non-administrator users from accessing the admin panel
add_action('init', 'blockusers_init');
function blockusers_init()
{
    if (is_admin() && !current_user_can('Developer') &&
        !(defined('DOING_AJAX') && DOING_AJAX)) {
        wp_safe_redirect(home_url());
        exit;
    }
}
//endregion

