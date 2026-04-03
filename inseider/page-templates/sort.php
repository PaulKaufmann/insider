<?php
/**
 * Template Name: sort
 */


get_header();


add_filter('apto/reorder_item_additional_options', 'filter_options', 10, 2);
function filter_options($options, $post)
{

    unset($options['edit']);
    unset($options['move_bottom']);
    // $options['move_bottom']="<span class='option move_bottom'>nach unten</span>";
    // Maybe modify $example in some way.
    return $options;
}

add_filter('apto/wp-admin/reorder-interface/filter-area-html', 'filter_area', 10, 2);
function filter_area($html, $el)
{
    $feed_name = $_GET['feed_name'] ?? null;

    return "<h2 class='text-center'>Neu sortieren: $feed_name</h2>";
}

add_filter('apto/reorder_item_additional_details', 'filter_details', 10, 2);
function filter_details($details, $data)
{
    $desc = $data->post_excerpt;
    return $data->post_title . "<p>$desc</p>";
}


$sort_id = $_GET['s_id'] ?? null;
if (empty($sort_id)) {
    wp_safe_redirect(home_url());
}

echo "<img id='scroll-indicator' src='" . get_Client_Image_Dir() . 'scrollbar.svg' . "'>";
echo "<div id='apto_container' style='display: none'>";
echo do_shortcode('[apto_reorder sort_id="' . $sort_id . '"]');
echo "</div>";

get_footer();
?>
<script>
    (function ($) {
        $(document).ready(function () {
            //APTO.toggle_thumbnails();
            $('#apto_container').show();
            $('#apto h2').hide();

            // Get the input field
            var input = document.getElementById("apto");

// Execute a function when the user presses a key on the keyboard
            input.addEventListener("keypress", function (event) {
                // If the user presses the "Enter" key on the keyboard
                if (event.key === "Enter") {
                    // Cancel the default action, if needed
                    event.preventDefault();
                    // Trigger the button element with a click
                    document.getElementsByClassName("save-order")[0].click();
                }
            });
        });
    })(jQuery);
</script>


