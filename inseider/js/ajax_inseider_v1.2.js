

function postlist_ajax() {
    var searchstring = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
    var pageNumber = jQuery('#totalpages').val();
    var cat = jQuery('#more_post_category').val();
    var ppp = jQuery('#post_per_page').val();
    var sort_id = jQuery('#sort_id').val();
    var show_hidden = jQuery('#show_hidden').val();

    jQuery("#more_posts").attr("disabled", true); // Disable the button, temp.

    pageNumber++;
    var str = "&pageNumber=".concat(pageNumber, "&ppp=").concat(ppp, "&action=more_post_ajax").concat("&cat=",cat).concat("&sort_id=",sort_id).concat("&show_hidden=",show_hidden);
    if (searchstring) {
        str += '&search=' + searchstring;
    }

    jQuery.ajax({
        type: "POST",
        dataType: "html",
        url: the_ajax_script.ajaxurl,
        data: str,
        success: function success(data) {
            if (data === 'false') {
                //force reload, this will identify user or redirect him to login
                location.reload();
            } else {
                var $data = jQuery.parseJSON(data);

                if ($data.html) {
                    jQuery("#ajax-posts").append($data.html);
                    jQuery("#more_posts").attr("disabled", false);
                    jQuery('#totalpages').val(pageNumber++); // When btn is pressed.

                } else {
                    jQuery("#more_posts").attr("disabled", true);
                }

                if ($data.isLastPage) {
                    jQuery("#more_posts").hide();
                } else {
                    jQuery("#more_posts").show();
                }
            }
        },
        error: function error(jqXHR, textStatus, errorThrown) {
            location.reload();
        }
    });
}

function edit_user_ajax(user_login) {
    var str = '&action=edit_user&user=' + user_login;
    jQuery.ajax({
        type: "POST",
        dataType: "html",
        url: the_ajax_script.ajaxurl,
        data: str,
        success: function success(rawdata) {
            if (rawdata === 'false') {
                //force reload, this will identify user or redirect him to login
                location.reload();
            } else {
                var data = jQuery.parseJSON(rawdata);
                return data;
            }
        },
        error: function error(jqXHR, textStatus, errorThrown) {
            location.reload();
        }
    });
}

function onesignal_player_id_ajax(p_id) {
    var str = '&action=onesignal_player_id&id=' + p_id;
    jQuery.ajax({
        type: "POST",
        dataType: "html",
        url: the_ajax_script.ajaxurl,
        data: str,
    });
}




