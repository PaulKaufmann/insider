<?php

add_action('genesis_before_header', 'renew_wp_cookie',35);

add_action('genesis_after_header', 'search_top_bar');
function search_top_bar()
{
    $s = isset($_GET["s"]) ? $_GET["s"] : "";
    echo '<div id="search_Top_Bar"><a href="'.home_url().'" id="search_Top_Bar-close">'.file_get_contents(get_Server_Image_Dir() . "close_icon_white.svg").'</a><p>Suche: '.$s.'</p></div>';
}

get_header();
?>

<div id="ajax-posts" class="row">
    <?php $data = more_posts(get_post_per_page(-1), 1, $_GET["s"],-1);
    echo $data['html']; ?>
</div>
<?php

if (!$data['isLastPage']) {
    echo get_more_posts_button( "'".$_GET["s"]."'",'-1',get_post_per_page(-1));
}

echo '<script>
 jQuery(\'#search_Top_Bar-close\').click(function () {
                     localStorage.removeItem("scrollPosition");
                
                });
</script>';

postlist_js();
wp_reset_postdata();
get_footer(); ?>

