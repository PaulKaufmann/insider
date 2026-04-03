<?php

/**
 * Load jQuery datepicker.
 *
 */
function enqueue_datepicker()
{
    // Load the datepicker script.
    wp_enqueue_script('jquery-ui-datepicker');
    wp_register_style('jquery-ui', '/wp-content/themes/inseider/css/jquery-ui-timepicker.css');
    wp_enqueue_style('jquery-ui');
}




//Global PullToRefresh
add_action('genesis_header', 'enque_pull_to_refresh_script');
function enque_pull_to_refresh_script()
{

    if (is_dev_version()){
        echo '<style>

#wpadminbar {
    display: block!important;
}
#mobile-fullscreen-search,.site-header,.sub-menu .menu-item a{
background: #000!important;
}

.menu-item span{
color: white!important;
}
</style>';
    }

    echo '<script src="wp-content/themes/inseider/pull_to_refresh.min.js"></script>    
          <script>
            window.addEventListener(\'load\', function () {   
                  
                const ptr = PullToRefresh.init({
                  mainElement: \'body\',
                  instructionsRefreshing:\'Lädt\',
                  instructionsReleaseToRefresh:\'Neu laden\',
                  instructionsPullToRefresh:\'Nach unten ziehen um neu zu laden\',
                  onRefresh: function() {
                    window.location.reload();
                  }
                });
                                      
                jQuery(\'.custom-logo-link\').click(function () {
                     localStorage.removeItem("scrollPosition");
                
                });
                
                jQuery(\'.menu-item-home\').click(function () {
                     localStorage.removeItem("scrollPosition");
                });
               
                
                jQuery(\'.search-form\').submit(function () {
                     localStorage.removeItem("scrollPosition");
                });
            
           });
        </script>';
}

function form_js()
{
    echo '<script>


function isInternetExplorer() {

    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))  // If Internet Explorer, return true
    {
        return true;
    } else  // If another browser, return false
    {
        return false;
    }
}
        function auto_grow(element) {
            element.style.height = "5px";
            element.style.height = (element.scrollHeight) + "px";
        }
        
        function clickThrough(handle) {
            jQuery(\'#\'+handle).click();
        }
        
        
        function showLoading(visible) {
            if(visible){
                jQuery(\'#upload-loader\').removeClass(\'hidden\');
            }else{
                jQuery(\'#upload-loader\').addClass(\'hidden\');
            }
            
            return true;
        }
        
        </script>
<link href="wp-content/themes/inseider/js/file-uploader/dist/jquery.fileuploader.min.css" media="all" rel="stylesheet">
<script src="wp-content/themes/inseider/js/file-uploader/dist/jquery.fileuploader.min.js"
        type="text/javascript"></script>
<!-- font -->
<link href="wp-content/themes/inseider/js/file-uploader/dist/font/font-fileuploader.css" media="all" rel="stylesheet">

        
        ';
}

function padStart_js()
{
    echo '<script>
//polyfill padstart for ie
                // https://github.com/uxitten/polyfill/blob/master/string.polyfill.js
                // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/padStart
                if (!String.prototype.padStart) {
                    String.prototype.padStart = function padStart(targetLength, padString) {
                        targetLength = targetLength >> 0; //truncate if number or convert non-number to 0;
                        padString = String((typeof padString !== \'undefined\' ? padString : \' \'));
                        if (this.length > targetLength) {
                            return String(this);
                        } else {
                            targetLength = targetLength - this.length;
                            if (targetLength > padString.length) {
                                padString += padString.repeat(targetLength / padString.length); //append to original to ensure we are longer than needed
                            }
                            return padString.slice(0, targetLength) + String(this);
                        }
                    };
                }
</script>';
}



function postlist_js($link1 = null, $link2 = null)
{

    echo '<script>';
    if (!is_null($link1) && !is_null($link2)) {
        echo 'let touchstartX = 0;
                let touchendX = 0;
               
                const site_inner = document.getElementsByTagName("html")[0];
                const top_bar = document.getElementById("feed-bar");
                
                function handleGesture() {
                let deltatouch = touchendX - touchstartX 
                  if (deltatouch>200) location.replace("' . $link2 . '");
                  if (deltatouch<-200) location.replace("' . $link1 . '");
                }
                
                 site_inner.addEventListener("touchstart", e => {
                  touchstartX = e.changedTouches[0].screenX;
                });
               
                site_inner.addEventListener("touchend", e => {
                  touchendX = e.changedTouches[0].screenX;
                  handleGesture();
                });
                
               /* top_bar.addEventListener("touchstart", e => {
                  touchstartX = e.changedTouches[0].screenX;
                });
               
                top_bar.addEventListener("touchend", e => {
                  touchendX = e.changedTouches[0].screenX;
                  handleGesture();
                });*/
                ';
    }

    echo '
    function postRowClick(id) {
        document.getElementById(\'post-row-\' + id).classList.add(\'scaled\');
        document.getElementsByClassName(\'site-inner\')[0].classList.remove(\'postlist_fade_in\');
        document.getElementsByClassName(\'site-inner\')[0].classList.add(\'postlist_fade_out\');
        var scrollPosition = jQuery("html").scrollTop();
        localStorage.setItem("scrollPosition", scrollPosition);
    }

    jQuery(function() {
        if(localStorage.scrollPosition) {
            jQuery("html").scrollTop(localStorage.getItem("scrollPosition"));
        }
    });


</script>';

}

function render_loader()
{
    $showloader = false;
    ?>
    <div id="upload-loader" class="<?php if (!$showloader) {
        echo 'hidden';
    } ?>">
        <div class="spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
        <h2>Bitte warten</h2>
    </div>
    <?php
}

function get_users_datalist_options()
{
    $users = get_users();

    $data = null;
    foreach ($users as $user) {
        $data = get_userdata($user->ID);
        echo '<option value="' . $user->data->user_login . '">' . $data->first_name . ' ' . $data->last_name .' - ' . $user->data->user_login . '</option>';
    }
}

function print_modal($title,$message,$button_text,$url=null){

    if(!is_null($url)){
        $url = 'href ="'.$url.'"';
    }
    echo '
    <div id="modal_bg"></div>
    <div class="insider_modal" id="insider_modal" style="display: block;">
        <h3>'.$title.'</h3>
        <p>'.$message.'</p>
        <a '.$url.' ><button class="button spk_wide_button" onclick="hide_insider_modal()">'.$button_text. '</button></a>
    </div>
    <script>
        function hide_insider_modal() {
            document.getElementById("insider_modal").style.display = "none";
            document.getElementById("modal_bg").style.display = "none";
        }

    </script>

    ';

}