<?php

/*
 *  returns 1-> post must have comments
 *  returns -1 -> post must not have comments
 *  returns 0 -> post comments can be configured
 */
function force_post_comments($post_id, $categories = null, $media_type = null):int
{
    if ($post_id != null) {
        $categories_objects =get_post($post_id)->post_category;
        foreach ($categories_objects as $cat){
            $categories[] = $cat->term_id;
        }
    }

    $fcc = force_comment_categories();

    foreach ($categories as $cat) {
        if (array_key_exists($cat,$fcc )) {
            if( $fcc[$cat]){
                return 1;
            }else{
                return -1;
            }
        }
    }
    return 0;
}


function exclude_from_email_recent_comments($post_id, $categories = null, $media_type = null):bool
{
    return force_post_comments($post_id,$categories,$media_type)>1;
}

function notify_author_about_recent_comments($post_id, $categories = null, $media_type = null):bool
{
    return force_post_comments($post_id,$categories,$media_type)>1;
}

//remove comments from specific posts
add_filter('comments_open', 'inseider_comments_open', 10, 2);
function inseider_comments_open($open, $post_id)
{
    return post_has_comments($post_id);
}

function post_has_comments($post_id)
{
    // disallow comments for not published posts
    if (get_post_status($post_id) !== 'publish') {
        return false;
    }

    $allow_comments = get_post_meta($post_id, 'allow_comments')[0] ?? null;
    if (!is_null($allow_comments)) {
        return $allow_comments == 'true';

    } else {
        return true;
    }


}

//removes "says:" - text after author name and replaces it with just the date, witch is stored in the desired format (dd.mm.yyyy)
add_filter('comment_author_says_text', 'change_comment_author_says_text');
function change_comment_author_says_text()
{
    return '[' . get_comment_date() . ']';
}

// Remove Comment Time & Link Inside the Date Field in Genesis
add_filter('genesis_show_comment_date', 'remove_comment_time_and_link');
function remove_comment_time_and_link($comment_date)
{
    // Return false so that the parent function doesn't output the comment date and time
    return false;
}




// custom callback for comment_list
add_action('genesis_after_content', 'inseider_list_comments');
function inseider_list_comments($postID)
{
    if (post_has_comments(get_the_ID())) {
        $args = array(
            'type' => 'comment',
            'callback' => 'inseider_comment_callback',
        );

        $args = apply_filters('genesis_comment_list_args', $args);

        echo '<script>

 function setCaretPosition(ctrl, pos) {
  if (ctrl.setSelectionRange) {
    setTimeout(function() {
      ctrl.focus();
    }, 1);
    ctrl.setSelectionRange(pos, pos);
  }
}

            function openReplyFor(name) {
                
                
                open_mobile_fs_comment();
                document.getElementById("comment_id").value = \'\';
                document.getElementById("comment").value = "@" + name + "\\n";
                document.getElementById("submit").style.display = \'block\';
                document.getElementById("submit_change_comment").style.display = \'none\';
                  setCaretPosition(document.getElementById("comment"), 9000);
            }
            

            function changeComment(id) {
                if(id>0){
                var text = document.getElementById("comment-" + id).getElementsByClassName("comment-content")[0].innerText;
                document.getElementById("comment_id").value = id;
                document.getElementById("comment").value = text;
                document.getElementById("submit").style.display = \'none\';
                document.getElementById("submit_change_comment").style.display = \'block\';
                open_mobile_fs_comment();
                }else{   
                    document.getElementById("comment_id").value = 0;
                       document.getElementById("submit").style.display = \'block\';
                document.getElementById("submit_change_comment").style.display = \'none\';
                }
                
                setCaretPosition(document.getElementById("comment"), 9000);

            }
        </script>';

        wp_list_comments($args);
    }
}

// Customizing the HTML output
function inseider_comment_callback($comment, $args, $depth)
{

    //update_comment_meta();
    $GLOBALS['comment'] = $comment;

    $user = get_user_by('id', get_comment()->user_id);

    $full_name = $user ? $user->first_name . ' ' . $user->last_name : 'Gelöschter Nutzer';
    ?>

<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">

    <?php do_action('genesis_before_comment'); ?>

    <div class="comment-left">

        <div class="comment-author vcard">
            <?php echo '<span class="comment-autor">' . $full_name . '</span> <span class="comment-date">' . '[' . get_comment_date() . '] </span>'; ?>
        </div><!-- end .comment-author -->

    </div>

    <div class="comment-right">

        <div class="comment-content">
            <?php comment_text(); ?>
        </div><!-- end .comment-content -->
        <div class="comment-edited-on">
            <?php $edited = get_comment_meta(get_comment_ID(), 'editedOn', true);
            if ($edited) {
                echo 'zuletzt geändert am ' . $edited;
            } ?>
        </div><!-- end .comment-content -->

        <div class="comment-bottom">
            <div class="reply" ontouchstart="openReplyFor('<?php echo $full_name ?>')"
                 onclick="openReplyFor('<?php echo $full_name ?>')">
                <?php comment_reply_link(array_merge($args, array('reply_text' => 'antworten', 'depth' => $depth, 'max_depth' => $args['max_depth'], 'reply_to_text' => 'antworten'))); ?>
            </div>
            <?php
            if (current_user_can_edit_comment(get_comment_ID())) {

                echo '<div id="change-comment-button"
                     onclick="changeComment(' . get_comment_ID() . ')"
                     ontouchstart="changeComment(' . get_comment_ID() . ')"
                     >';
                comment_reply_link(array_merge($args, array('reply_text' => 'editieren', 'depth' => $depth, 'max_depth' => $args['max_depth'], 'reply_to_text' => 'editieren')));
                echo '
                </div>';
            }
            ?>

        </div
    </div>

    <?php do_action('genesis_after_comment');

    /** No ending </li> tag because of comment threading */

}

//Titel für Kommentar-Formular anpassen
add_filter('comment_form_defaults', 'sp_comment_form_defaults');
function sp_comment_form_defaults($defaults)
{

    $defaults['title_reply'] = __('Kommentar verfassen:');
    return $defaults;

}

// remove author name from comment-class
add_filter('comment_class', 'filter_comment_class', 10, 5);
function filter_comment_class($classes, $class, $comment_comment_id, $comment, $post_id)
{
    $classes[2] = '';
    return $classes;
}


//Speichern-button für Kommentar-Formular anpassen
add_filter('comment_form_defaults', 'sp_comment_submit_button');
function sp_comment_submit_button($defaults)
{
    $defaults['label_submit'] = __('speichern', 'custom');
    return $defaults;
}

//Titel für Kommentar-Bereich
add_filter('genesis_title_comments', 'custom_comment_text');
function custom_comment_text()
{
    $title = '';
    if (post_has_comments(get_the_ID())) {

        $title = '<h2 class="discussion_heading">Diskussion:</h2>';
    }

    return $title;
}

add_filter('cancel_comment_reply_link', 'remove_comment_reply_link', 10);

// Remove the comment reply button from it's default location
function remove_comment_reply_link($link)
{
    return '';
}


// adds additional hidden inputs for editing comments and enables the reply button
// (after the first, unwanted one is blocked) and creates one in the desired location
add_filter('comment_form_submit_button', 'filter_comment_form_submit_button', 10, 2);
function filter_comment_form_submit_button($submit_button, $args)
{

    $cancel_comment_text = "abbrechen";

    // two hidden inputs for editing comments
    $hiddenInputs = '<input hidden style="display: none" name="submit" formaction="' . $_SERVER['REQUEST_URI'] . '" type="submit" id="submit_change_comment" class="submit" value="speichern">
                     <input hidden style="display: none" name="comment_id" type="hidden" id="comment_id">';

    // add custom close button (default is not rednered due to filter)
    remove_filter('cancel_comment_reply_link', 'my_remove_comment_reply_link');
    add_filter('cancel_comment_reply_link', 'custom_cancel_comment_reply_link', 10, 3);

    cancel_comment_reply_link($cancel_comment_text);

    return $hiddenInputs . $submit_button;
}


// returns if current user can edit given comment
// at this time each user can edit his own comments only, authors and administrators dont have permission to edit other users comments
function current_user_can_edit_comment($comment_id)
{
    $comment = get_comment($comment_id);
    $comment_author_id = $comment->user_id;

    $isOwnComment = get_current_user_id() == $comment_author_id;

    return $isOwnComment;
}


/**
 * Change cancel comment button markup to close fullscreen search on mobile
 *
 * @return string Modified HTML for search forms' cancel button.
 */
function custom_cancel_comment_reply_link($formatted_link, $link, $text)
{
    $link = get_permalink() . '#respond';
    $newformatted_link = '<a rel="nofollow" id="cancel-comment-reply-link" href="' . $link . '"' . '>' . $text . '</a>
 <script>
 document.getElementById("cancel-comment-reply-link").addEventListener(\'click\', function(){close_mobile_fs_comment();changeComment(-1);})
  document.getElementById("cancel-comment-reply-link").addEventListener(\'touchstart\', function(){ close_mobile_fs_comment();changeComment(-1);})


</script>';
    return sprintf($newformatted_link);
}