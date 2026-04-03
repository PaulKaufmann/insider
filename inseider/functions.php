<?php
/**
 * inseider
 *
 * This file adds functions to the inseider Theme.
 *
 * @package inseider
 * @author  muximum design
 * @license GPL-2.0-or-later
 * @link    https://www.studiopress.com/
 */

//region initialisations & imports
define('CHILD_THEME_NAME', 'inseider');
define('CHILD_THEME_URL', 'inseider');
define('CHILD_THEME_Version', '1.0');

//todo configure remove if developing
//error_reporting(E_ALL);
error_reporting(E_ERROR);
//error_reporting(0);

function remove_header()
{

    remove_action('genesis_entry_header', 'genesis_do_post_title');
    remove_action('genesis_header', 'genesis_header_markup_open', 5);
    remove_action('genesis_header', 'genesis_do_header');
    remove_action('genesis_header', 'genesis_do_nav', 50);
    remove_action('genesis_header', 'genesis_header_markup_close', 15);
    remove_action('genesis_before', 'sk_replace_menu_in_primary');
    remove_action('genesis_markup_nav-primary_close', 'insert_search_after_navigation', 10);

    echo '<style>
    #genesis-nav-primary{
    display: none !important;
    }
</style>';
}

//import splitted files
$roots_includes = array(
    '/functions/ajax.php',
    '/functions/comments.php',
    '/functions/frontend.php',
    '/functions/notifications.php',
    '/functions/posting.php',
    '/functions/search.php',
    '/functions/security.php',
    '/functions/user.php',
    '/individual/config.php',
    '/individual/unit-test.php',
    '/individual/strings.php'
);

foreach ($roots_includes as $file) {
    if (!$filepath = locate_template($file)) {
        trigger_error("Error locating `$file` for inclusion!", E_USER_ERROR);
    }

    require_once $filepath;
}
unset($file, $filepath);


// Starts the engine.
require_once get_template_directory() . '/lib/init.php';

// Sets up the Theme.
require_once get_stylesheet_directory() . '/lib/theme-defaults.php';

require_once(ABSPATH . 'wp-admin/includes/user.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
//endregion


//region General Theme customisation such as Layout changes

add_filter('admin_email_check_interval', '__return_false');

add_action('after_setup_theme', 'genesis_sample_localization_setup');

remove_action('genesis_entry_header', 'genesis_post_info', 12);

/**
 * Sets localization (do not remove).
 *
 * @since 1.0.0
 */
function genesis_sample_localization_setup()
{
    load_child_theme_textdomain(genesis_get_theme_handle(), get_stylesheet_directory() . '/languages');
}

// Adds helper functions.
require_once get_stylesheet_directory() . '/lib/helper-functions.php';

// Adds image upload and color select to Customizer.
require_once get_stylesheet_directory() . '/lib/customize.php';

// Includes Customizer CSS.
require_once get_stylesheet_directory() . '/lib/output.php';

// Adds WooCommerce support.
require_once get_stylesheet_directory() . '/lib/woocommerce/woocommerce-setup.php';

// Adds the required WooCommerce styles and Customizer CSS.
require_once get_stylesheet_directory() . '/lib/woocommerce/woocommerce-output.php';

// Adds the Genesis Connect WooCommerce notice.
require_once get_stylesheet_directory() . '/lib/woocommerce/woocommerce-notice.php';

add_action('after_setup_theme', 'genesis_child_gutenberg_support');
/**
 * Adds Gutenberg opt-in features and styling.
 *
 * @since 2.7.0
 */

function genesis_child_gutenberg_support()
{ // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- using same in all child themes to allow action to be unhooked.
    require_once get_stylesheet_directory() . '/lib/gutenberg/init.php';
}

// Registers the responsive menus.
if (function_exists('genesis_register_responsive_menus')) {
    genesis_register_responsive_menus(genesis_get_config('responsive-menus'));
}


add_action('after_setup_theme', 'genesis_sample_theme_support', 9);
/**
 * Add desired theme supports.
 *
 * See config file at `config/theme-supports.php`.
 *
 * @since 3.0.0
 */
function genesis_sample_theme_support()
{

    $theme_supports = genesis_get_config('theme-supports');

    foreach ($theme_supports as $feature => $args) {
        add_theme_support($feature, $args);
    }
}


add_action('delete_user', 'delete_onsignal_user', 10, 1);
function delete_onsignal_user($id)
{
    $onesignal_player_ids = get_user_meta($id)["onesignal_player_ids"];
    $onesignal_player_ids = unserialize($onesignal_player_ids[0]);

    $headers = array(
        "Authorization: Basic " . get_oneSignal_api_key(),
    );

    foreach ($onesignal_player_ids as $player_id) {
        $url = "https://onesignal.com/api/v1/players/$player_id?app_id=" . get_oneSignal_appID();

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
    }

}


add_filter('genesis_author_box_gravatar_size', 'genesis_sample_author_box_gravatar');
/**
 * Modifies size of the Gravatar in the author box.
 *
 * @param int $size Original icon size.
 * @return int Modified icon size.
 * @since 2.2.3
 *
 */
function genesis_sample_author_box_gravatar($size)
{

    return 90;

}


add_filter('genesis_comment_list_args', 'genesis_sample_comments_gravatar');
/**
 * Modifies size of the Gravatar in the entry comments.
 *
 * @param array $args Gravatar settings.
 * @return array Gravatar settings with modified size.
 * @since 2.2.3
 *
 */
function genesis_sample_comments_gravatar($args)
{

    $args['avatar_size'] = 60;
    return $args;

}


remove_action('genesis_entry_header', 'genesis_post_info', 12);
remove_action('genesis_footer', 'genesis_do_footer');
remove_action('genesis_footer', 'genesis_footer_markup_open', 5);
remove_action('genesis_footer', 'genesis_footer_markup_close', 15);
remove_action('genesis_entry_footer', 'genesis_post_meta');
add_filter('genesis_edit_post_link', '__return_false');

// Displays custom logo.
add_action('genesis_site_title', 'the_custom_logo', 0);

// Repositions primary navigation menu.
remove_action('genesis_after_header', 'genesis_do_nav');
add_action('genesis_header', 'genesis_do_nav', 12);

//removes the secondary navigation menu.
remove_action('genesis_after_header', 'genesis_do_subnav');

// Removes secondary sidebar.
unregister_sidebar('sidebar-alt');

//removes sidebar
remove_action('genesis_sidebar', 'genesis_do_sidebar');

// Removes site layouts.
genesis_unregister_layout('content-sidebar-sidebar');
genesis_unregister_layout('sidebar-content-sidebar');
genesis_unregister_layout('sidebar-sidebar-content');

add_filter('genesis_customizer_theme_settings_config', 'genesis_sample_remove_customizer_settings');

//remove admin bar
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar()
{
    if (!is_dev_version()) {
        show_admin_bar(false);
    }
}

/**
 * Removes output of header and front page breadcrumb settings in the Customizer.
 *
 * @param array $config Original Customizer items.
 * @return array Filtered Customizer items.
 * @since 2.6.0
 *
 */
function genesis_sample_remove_customizer_settings($config)
{
    unset($config['genesis']['sections']['genesis_header']);
    unset($config['genesis']['sections']['genesis_breadcrumbs']['controls']['breadcrumb_front_page']);
    return $config;

}


add_filter('the_generator', 'remove_wp_version');
function remove_wp_version()
{
    return '';
}

add_action('genesis_before_header', 'no_chache_Headers');
function no_chache_Headers()
{
    header("Cache-Control: no-store, no-cache, must-revalidate"); //HTTP 1.1
    header("Pragma: no-cache"); //HTTP 1.0
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
}




// remove the genesis_default_list_comments function
remove_action('genesis_list_comments', 'genesis_default_list_comments');


add_action("wp_enqueue_scripts", "js_enqueue_scripts");
function js_enqueue_scripts()
{
    wp_enqueue_script("jquery");

    wp_enqueue_script("my-ajax-handle", get_stylesheet_directory_uri() . "/js/ajax_inseider_v1.2.js", array('jquery'));
    //the_ajax_script will use to print admin-ajaxurl in custom ajax.js
    wp_localize_script('my-ajax-handle', 'the_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php'), 'ajax_nonce' => wp_create_nonce('ajax_nonce'),));
}

//endregion


//region Helper functions


/**
 * Updates post meta for a post. It also automatically deletes or adds the value to field_name if specified
 *
 * @access     protected
 * @param integer     The post ID for the post we're updating
 * @param string      The field we're updating/adding/deleting
 * @param string      [Optional] The value to update/add for field_name. If left blank, data will be deleted.
 * @return     void
 */
function __update_post_meta($post_id, $field_name, $value = '')
{
    if (empty($value) or !$value) {
        delete_post_meta($post_id, $field_name);
    } elseif (!get_post_meta($post_id, $field_name)) {
        add_post_meta($post_id, $field_name, $value);
    } else {
        update_post_meta($post_id, $field_name, $value);
    }
}


//formats seccons like this
//55 -> 00:55
//3800 -> 01:03:20
function format_secconds($secconds)
{
    $duration = (gmdate("H:i:s", $secconds));
    if ($duration[0] == "0" && $duration[1] == "0") {
        $duration = substr($duration, 3);
    }
    return $duration;
}


function get_timestamp_offset()
{
    return strtotime(date_i18n('Y-m-d H:i:s')) - strtotime(date_i18n('Y-m-d H:i:s', false, true));
}


//endregion


//region more_posts
function more_posts($ppp, $page, $s, $category, $favorites = false, $filter_tag = '', $sort_id = null, $show_hidden=false)
{
    $results = array();
    $isLastPage = true;
    $imageDir = get_Server_Image_Dir();

    if ($favorites) {
        $favs = get_user_meta(get_current_user_id(), 'favorite_posts', true);
        foreach ($favs as $fav) {
            $results[] = get_post($fav);
        }
    } else {


        $post_type = '';

        global $wpdb;
        global $current_user;

        if ($category < 0) {
            $show_all_categories = true;
            $category = 0;
        } else {
            if (is_array($category)) {
                $category = implode(',', $category);
            }
            $show_all_categories = false;
        }


        if (current_user_can('publish_posts')) {
            $show_all_drafts = true;
            $show_inactive_prize_draws = true;
        } else {
            $show_all_drafts = false;
            $show_inactive_prize_draws = false;
        }

        if (is_user_category_editor($current_user->ID, $category)) {
            $show_all_drafts = true;
        }

        $beginning = ($page - 1) * $ppp;

        if (empty($s)) {
            $skip_search = true;
        } else {
            $skip_search = false;
        }

        if (empty($post_type)) {
            $skip_post_type = true;
        } else {
            $skip_post_type = false;
        }

        $skip_tags = false;
        if ($filter_tag == '') {
            $skip_tags = true;
        }

        if ($ppp > 0) {
            $limit = "LIMIT $beginning, $ppp";
        } else {
            $limit = "";
        }

        $args = array(
            'paged' => $page,
            'posts_per_page' => $ppp,
            'post_type' => 'post',
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'cat' => $category,
            'suppress_filters' => false,
            'post_status' => array('publish', 'draft'),
            'sort_id' => $sort_id
        );

        if ($s) {
            $args['s'] = $s;
        }

        if (!$skip_tags) {
            $args['tag_id'] = $filter_tag;
        }


        if ($show_all_drafts) {
            //authors and admins see all posts

            $args['post_status'] = array('publish', 'draft');

            if(!$show_hidden){
                $args['meta_query'] = array(

                    array(
                        'relation' => 'OR',
                        array(
                            'key' => 'post_invisible',
                            'value' => 'true',
                            'compare' => '!='
                        ),
                        array(
                            'key' => 'post_invisible',
                            'compare' => 'NOT EXISTS',
                        ),
                        
                    )
                );
            }

            // $args['meta_query'] = array(
            // 'relation' => 'OR',
            // // array(
            // //     'key' => 'post_invisible',
            // //     'compare' => 'NOT EXISTS',
            // // ),
            // array(
            //     'key' => 'post_invisible',
            //     'value' => 'true',
            //     'compare' => '=',
            // ),
            // );

        } else {

            //normal users dont see drafts and non-active prize draws
            $args['post_status'] = 'publish';

            $args['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'prize_draw_visible',
                        'value' => 'true',
                        'compare' => '='
                    ),
                    array(
                        'key' => 'prize_draw_active',
                        'compare' => 'NOT EXISTS',
                    ),
                    
                ),
                !$show_hidden? array(
                    'relation' => 'OR',
                    array(
                        'key' => 'post_invisible',
                        'value' => 'true',
                        'compare' => '!='
                    ),
                    array(
                        'key' => 'post_invisible',
                        'compare' => 'NOT EXISTS',
                    ),
                    
                ): array()


            );



        }

        global $loop;
        if ($page == 1 && !$show_all_drafts) {
            $q1 = new WP_Query($args);
            $args['post_status'] = array('draft');
            $args['author'] = '' . get_current_user_id();
            $q2 = new WP_Query($args);

            $loop = new WP_Query();
            $loop->posts = array_merge($q2->posts, $q1->posts);
            $loop->post_count = count($loop->posts);
            $isLastPage = $q1->max_num_pages <= $page;

        } else {
            $loop = new WP_Query($args);
            $isLastPage = $loop->max_num_pages <= $page;
        }


        $results = $loop->posts;

    }
    $out = '';

    global $post;
    foreach ($results as $post) {

        $post_status = get_post_status($post->ID) == 'publish' ? 'publish-post' : 'draft-post';

        global $current_user;
        $views_html = "";
        if (user_can_see_viewcounter($current_user->roles)) {


            $view_count_meta = get_post_meta(get_the_ID(), 'view_count');
            if ($view_count_meta && $view_count_meta[0]) {
                $view_count = $view_count_meta[0];
            } else {
                $view_count = 0;
            }


            $views_html = '<div class="post-list-views">' . file_get_contents($imageDir . "/eye-icon.svg", false, null) . '<h5>' . (int)$view_count . '</h5></div>';
        }
        $comment_count = (int)get_comments_number();

        if ($comment_count > 0) {
            $comments_html = file_get_contents($imageDir . "/comment-icon.svg") . '<h5>' . $comment_count . '</h5 >';
        } else {
            $comments_html = '';
        }


        $mt = get_post_meta(get_the_ID(), 'media_type')[0];
        global $fallback_thumbnail_media_id;
        $thumbnail = has_post_thumbnail() ? get_the_post_thumbnail(null, 'medium') :
            '<img width="300" height="169" src="' . wp_get_attachment_image_src($fallback_thumbnail_media_id, '550')[0] . '" class="attachment-medium size-medium wp-post-image" alt="">';


        $desktop_postlistlables = '';
        $mobile_postlistlables = '';
        switch ($mt) {
            case "article":
                $desktop_postlistlables = '<div class="post-list-lable article-lable"><h5>Zum Artikel</h5>' . file_get_contents($imageDir . "/article-icon.svg") . '</div>';
                $mobile_postlistlables = '<div class="post-list-lable article-lable" > ' . file_get_contents($imageDir . "/article-icon.svg") . '<h5 > Artikel</h5 ></div > ';
                break;

            case "audio":
                $desktop_postlistlables = '<div class="post-list-lable audio-lable"><h5>Zum Podcast</h5>' . file_get_contents($imageDir . "/audio-icon.svg") . '</div>';
                $mobile_postlistlables = '<div class="post-list-lable audio-lable" > ' . file_get_contents($imageDir . "/audio-icon.svg") . '<h5 > Podcast</h5 ></div > ';
                break;

            case "gallery":
                $desktop_postlistlables = '<div class="post-list-lable gallery-lable"><h5>Zur Galerie</h5>' . file_get_contents($imageDir . "/gallery-icon.svg") . '</div>';
                $mobile_postlistlables = '<div class="post-list-lable gallery-lable" > ' . file_get_contents($imageDir . "/gallery-icon.svg") . '<h5 > Galerie</h5 ></div > ';
                break;

            case "video":
                $desktop_postlistlables = '<div class="post-list-lable video-lable"><h5>Zum Video</h5>' . file_get_contents($imageDir . "/video-icon.svg") . '</div>';
                $mobile_postlistlables = '<div class="post-list-lable video-lable" > ' . file_get_contents($imageDir . "/video-icon.svg") . '<h5 > Video</h5 ></div > ';
                break;

            case "prize_draw":
                $desktop_postlistlables = '<div class="post-list-lable prize_draw-lable"><h5>Zum Gewinnspiel</h5>' . file_get_contents($imageDir . "/star-icon.svg") . '</div>';
                $mobile_postlistlables = '<div class="post-list-lable prize_draw-lable" > ' . file_get_contents($imageDir . "/star-icon.svg") . '<h5 > Gewinnspiel</h5 ></div > ';
                break;
            default:
                $custom_media_postlistLables = custom_media_postlistLables($mt);
                $desktop_postlistlables = $custom_media_postlistLables[0];
                $mobile_postlistlables = $custom_media_postlistLables[1];
        }


        if (!empty(get_post_meta(get_the_ID(), 'hidden')) && get_post_meta(get_the_ID(), 'hidden')[0] == 'true') {
            $hidden = 'hidden';
        } else {
            $hidden = '';
        }

        $invisible_post = get_post_meta(get_the_ID(), 'post_invisible')[0] == 'true' ? 'invisible-post' : '';

        $out .= '
    <a class="noHighlight ' . $hidden . '" /*oncontextmenu="return false"*/  href ="' . get_the_permalink() . '" onclick="postRowClick(' . get_the_ID() . ')"> 
        <div class="post-row pre_fade_in_transparent animated ' . $post_status . ' ' . $invisible_post . '" id="post-row-' . get_the_ID() . '">

            <div class="post-list-img-container">
                <div class="post-list-img">
                    ' . $thumbnail . '
                </div>
            </div>

            <!-- Content -->
            <div class="post-list-content">
                <div class="post-list-container">
                    <h1>' . get_the_title() . '</h1>

                    <p class="post-list-desktop-excerpt">' . wp_trim_words(get_the_excerpt(), 12, '...') . '</p>
                    <p class="post-list-mobile-excerpt">' . wp_trim_words(get_the_excerpt(), 25, '...') . '</p>

                </div>
                <div class="desktop-comments-lable">
                    <div class="post-list-comments">
                        ' . $comments_html .
            $views_html . '
                    </div>' . $desktop_postlistlables . '

                </div>
                
                        <div class="mobile-comments-lable">' . $mobile_postlistlables . '
                   <div class="post-list-comments">
                       ' . $comments_html .
            $views_html . '</div>
               </div>
                
            </div>
        </div>
    </a>';

    }

    if ($out == '') {
        //there is no output -> no result
        if ($s) {
            $out .= '<div style="text-align: center"> <h2 style="color: #797979">Leider ergab die Suche kein Ergebnis.</h2> </div>';
        } else {
            if ($page == 1) {
                $out .= '<div style="text-align: center"><h2 style="color: #797979">Es wurden keine Beiträge gefunden.</h2> </div>';
            }
        }
    }


    $data = array('html' => $out, 'isLastPage' => $isLastPage);
    return $data;
}


function get_more_posts_button($s = '', $category = -1, $ppp = 10, $sort_id = null, $show_hidden = false) 
{
    if (is_array($category)) {
        $category = json_encode($category);
    }

    return '<input type="hidden" autocomplete="off" id="totalpages" value="1">
<input type="hidden" autocomplete="off" id="more_post_category" value="' . $category . '">
<input type="hidden" autocomplete="off" id="post_per_page" value="' . $ppp . '">
<input type="hidden" autocomplete="off" id="sort_id" value="' . $sort_id . '">
<input type="hidden" autocomplete="off" id="show_hidden" value="' . $show_hidden . '">
<div class="text-center">
<button id="more_posts" class="spk_wide_button" onclick="postlist_ajax(' . $s . ')">mehr Beiträge</button>
</div>';
}

//endregion

// SINGLE
$prize_draw_participation_status = null;
$custom = null;


function get_Server_Image_Dir()
{
    return get_stylesheet_directory() . "/images/";
}


function get_Client_Image_Dir()
{
    return "wp-content/themes/inseider/images/";
}


add_action('before_delete_post', function ($id) {
    $attachments = get_attached_media('', $id);
    foreach ($attachments as $attachment) {
        wp_delete_attachment($attachment->ID, 'true');
    }
});


function sort_File_upload($file_handle)
{
    require_once(ABSPATH . 'wp-content/themes/inseider/lib/class.fileuploader.php');
    $FileUploader = new FileUploader($file_handle, array(// Options will go here
        'uploadDir' => 'wp-content/uploads',
    ));

    $files = $_FILES[$file_handle];
    $new_files = array();


    $li = $FileUploader->getListInput()['values'];
    foreach ($li as $lii) {

        if (strpos($lii['file'], ':/')) {
            $offset = strpos($lii['file'], ':/') + 2;
        } else {
            $offset = 0;
        }

        $filename = substr($lii['file'], $offset);
        //$filename = substr($lii['file'], strpos($lii['file'], ':/ "'));
        $pos = array_search($filename, $files['name']);

        if ($pos !== false) {
            foreach ($files as $key => $value) {
                $new_files[$key][] = $files[$key][$pos];
            }
        } else {
            $new_files['name'][] = $lii['id'];
            $new_files['type'][] = 'preload';
            $new_files['tmp_name'][] = 'preload';
            $new_files['error'][] = 0;
            $new_files['size'][] = 0;
        }
    }


    $_FILES[$file_handle] = $new_files;

}

function dbg($to_debug, $force = false)
{
    if (!$force && !can_print_debuggs()) {
        return;
    }
    echo '<br> <pre class="insider_debug">';
    $json_string = prettyPrint(json_encode($to_debug));
    echo $json_string;
    echo '</pre><br>';
}


function prettyPrint($json)
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen($json);

    for ($i = 0; $i < $json_length; $i++) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if ($ends_line_level !== NULL) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ($in_escape) {
            $in_escape = false;
        } else if ($char === '"') {
            $in_quotes = !$in_quotes;
        } else if (!$in_quotes) {
            switch ($char) {
                case '}':
                case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{':
                case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ":
                case "\t":
                case "\n":
                case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ($char === '\\') {
            $in_escape = true;
        }
        if ($new_line_level !== NULL) {
            $result .= "\n" . str_repeat("\t", $new_line_level);
        }
        $result .= $char . $post;
    }

    return $result;
}

add_filter( 'send_email_change_email', 'customize_send_email_change_email',10,3);

function customize_send_email_change_email($send, $user, $userdata){


    if(strtolower($userdata['user_email']) == strtolower($user['user_email'])){
        return false;
    }
    $email_change_text = sprintf(
        'Hallo %s %s,<br>
        wir möchten Sie darüber informieren, dass Ihre E-Mail-Adresse im '.get_plattform_name().' aktualisiert wurde.
        Melden Sie sich dort bitte in Zukunft mit folgender E-Mail-Adresse an: %s. <br>Alternativ können Sie sich auch mit Ihrer S-Nummer anmelden.<br>',$userdata['first_name'],$userdata['last_name'],$userdata['user_email'],

    );

    spk_sendEmail($userdata['user_email'],$email_change_text,'Anpassung Ihrer E-Mail-Adresse im '.get_plattform_name());
    return false;
}