<?php
/**
 * Template Name: Postlist
 */

add_action('get_header', 'handle_viewing_restrictions');

global $category;
global $top_category;
global $sort_id;
global $link_left;
global $link_right;
global $custom_name;
global $is_small;
global $cat_name;

add_action('genesis_before_header', 'renew_wp_cookie', 35);


add_action('genesis_before', 'extract_feed_data');
function extract_feed_data()
{

    $feed_data = get_feeds();
    $postlist_index = get_post_meta(get_the_ID(), 'postlist_index')[0];

    if ($postlist_index <= 0) {
        $postlist_prev = count($feed_data) - 1;
    } else {
        $postlist_prev = $postlist_index - 1;
    }

    if ($postlist_index >= count($feed_data) - 1) {
        $postlist_next = 0;
    } else {
        $postlist_next = $postlist_index + 1;
    }

    global $category;
    global $link_left;
    global $link_right;
    global $custom_name;
    global $top_category;
    global $sort_id;
    global $is_small;

    $postlist_data_overridden = get_post_meta(get_the_ID(), 'postlist_data_overridden')[0] ?? null;

    if ($postlist_data_overridden) {
        $data = explode(",", $postlist_data_overridden);

        $link_left = $data[0];
        $link_right = $data[1];
        $custom_name = $data[2];
        $category = $data[3];
        $is_small = (bool)$data[4];
        $sort_id = $data[5];
    } else {
        $link_left = $feed_data[$postlist_prev]['link'];
        $link_right = $feed_data[$postlist_next]['link'];

        $custom_name = $feed_data[$postlist_index]['title'] ?? null;
        $category = $feed_data[$postlist_index]['cat'];
        $top_category = $feed_data[$postlist_index]['top_category'];
        $sort_id = $feed_data[$postlist_index]['sort_id'];
        $is_small = (bool)($feed_data[$postlist_index]['small'] ?? null);
    }
}

add_action('genesis_after_header', 'feed_top_bar');
function feed_top_bar()
{
    global $category;
    global $is_small;

    global $link_left;
    global $link_right;

    if (!empty($_GET['password']) && $_GET['password'] == 'changed') {

        print_modal('Passwort geändert', 'Ihr Passwort wurde erfolgreich geändert.', 'ok');
    }

    ?>

    <div id="feed-bar" class="row">
        <script><?php echo custom_js('List', $category); ?></script>
        <style><?php echo custom_css('List', $category); ?></style>
        <a href="<?php echo $link_left; ?>"><span class="feeds-nav-button" id="feeds-nav-prev-button">
        <?php echo file_get_contents(get_Server_Image_Dir() . 'feed-arrow-prev.svg') ?>
        </span>
        </a>

        <span><?php

            global $custom_name;
            global $cat_name;
            if ($custom_name) {
                $cat_name = $custom_name;
            } else {
                $cat_name = get_category($category)->name;
            }
            echo $cat_name;
            ?></span>


        <a href="<?php echo $link_right; ?>"><span class="feeds-nav-button" id="feeds-nav-prev-button">
        <?php echo file_get_contents(get_Server_Image_Dir() . 'feed-arrow-next.svg') ?>
    </span>
        </a>
    </div>


    <?php


}

get_header();


$top_cat = $category;
if ($top_category) {
    $top_cat = $top_category;
}


echo '<div class="text-center">';
if (can_user_sort_feed($top_cat)) {
    ?>
        <a href="/sort?s_id= <?php echo $sort_id ?>&feed_name=<?php echo $cat_name; ?>">
            <button class="spk_wide_button sort-buton dark_button" style="margin-left: 10px; margin-right: 10px;"><span
                        class="filter-buttons-svg-icon"><?php echo file_get_contents(get_Server_Image_Dir() . 'star.svg'); ?></span>Sortieren
            </button>
        </a>
        <?php
}

if (can_user_see_hidden_posts()) {
    if ($_GET['show_hidden'] == 'true') {
        ?>
            <a href="<?php echo remove_query_arg('show_hidden'); ?>">
                <button class="spk_wide_button sort-buton dark_button" style="margin-left: 10px; margin-right: 10px;"><span
                            class="filter-buttons-svg-icon"><?php echo file_get_contents(get_Server_Image_Dir() . 'eye-icon-black.svg'); ?></span>Versteckte Beiträge verbergen
                </button>
            </a>
        <?php
    } else {
        ?>
            <a href="<?php echo add_query_arg('show_hidden', 'true'); ?>">
                <button class="spk_wide_button sort-buton dark_button" style="margin-left: 10px; margin-right: 10px;"><span
                            class="filter-buttons-svg-icon"><?php echo file_get_contents(get_Server_Image_Dir() . 'eye-icon-black.svg'); ?></span>Versteckte Beiträge anzeigen
                </button>
            </a>
        <?php
    }
}
echo '</div>';


if ($_GET && ($_GET['favorite'] == 'true' || $_GET['filter_tag'])) {
//todo clear url params

    if ($_GET['favorite']) {
        $filter_name = 'Favoriten';
    } else {
        $filter_name = get_tag($_GET['filter_tag'])->description;
    }

    echo '<div id="filter-bar"><span>Filter: ' . $filter_name . '</span>'
    ?>
    <div class="cancel_filter_form">
        <button onclick="remove_param()" class="secondary_button slim-button" name="favorite" value="true"
                type="submit">zurücksetzen
        </button>
    </div>
    </div>
    <script>
        function remove_param() {
            history.replaceState(
                null, '', location.pathname + location.search.replace(/[\?].*=[^&]+/, '')
            );
            location.reload();
        }
    </script>
    <?php

} else if (has_favorite_button($category) || has_filter_button($category)) {


echo '<form class="filter_form"><div class="filter-button-wrapper">';

if (has_favorite_button($category)) {
    ?>
    <button class="flex_button dark_button" name="favorite" value="true" type="submit"><span
                class="filter-buttons-svg-icon"><?php echo file_get_contents(get_Server_Image_Dir() . 'star.svg'); ?></span>Favoriten
    </button>
    <?php
}

if (has_filter_button($category)) {
?>
<button class="flex_button dark_button" type="button" onclick="toggle_filter_tags()"><span
            class="filter-buttons-svg-icon"
    ><?php echo file_get_contents(get_Server_Image_Dir() . 'filter.svg'); ?></span>Filter
</button>

<!--closing filter button wrapper-->
</div>
<div id="tags_filter_cont" style="max-height:0em">
    <span class="tf_close_icon"
          onclick="toggle_filter_tags()"><?php echo file_get_contents(get_Server_Image_Dir() . 'close_icon.svg'); ?></span>
    <br>
    <?php
    $count = 0;
    foreach (get_filter_tags($category) as $tag) {
        $count++;
        echo '<button name="filter_tag" type=submit value="' . $tag . '">' . get_tag($tag)->description . '</button>';
    }

    ?>
    <script>
        function toggle_filter_tags() {
            var tfc = document.getElementById('tags_filter_cont');
            if (tfc != null) {
                if (tfc.style.maxHeight === '0em') {
                    tfc.style.maxHeight = '<?php echo $count * 1.5 + 3 ?>em';
                } else {
                    tfc.style.maxHeight = '0em';
                }
            }
        }
    </script>

    <?php

    echo '</div>';
    } else {
        echo '</div>';
    }
    echo '</form>';

    }
    ?>

    <div id="ajax-posts" class="row<?php if ($is_small) {
        echo ' small_feed';
    } ?>"><?php

        //categories and Tags are both quried over term_taxonomy_id so they are used interchangerble here
        $cat_tag = $category;
        if ($top_category) {
            $cat_tag = $top_category;
        }
        $filter_tag = '';
        if (!empty($_GET['filter_tag'])) {
            $filter_tag = $_GET['filter_tag'];
        }
        $data = more_posts(get_post_per_page($category), 1, null, $cat_tag, $_GET['favorite'], $filter_tag, $sort_id, $_GET['show_hidden']);
        echo $data['html'];

        ?></div><?php

    if (!$data['isLastPage']) {
        echo get_more_posts_button('', $cat_tag, get_post_per_page($category), $sort_id,$_GET['show_hidden']);
    }
    add_action('genesis_after', 'list_js');
    function list_js()
    {
        $link_left = get_post_meta(get_the_ID(), 'postlist_link_left')[0] ?? null;
        $link_right = get_post_meta(get_the_ID(), 'postlist_link_right')[0] ?? null;
        postlist_js($link_left, $link_right);

        onesignal_player_ids_js();
    }

    wp_reset_postdata();
    get_footer(); ?>

