<?php

add_filter('genesis_markup_nav-primary_close', 'insert_search_after_navigation', 10, 2);

function insert_search_after_navigation($close_html, $args)
{

    global $current_user;
    if(in_array('business_partner', $current_user->roles)){
        return '';
    }


    if ($close_html) {
        $search_html = '<div onclick="activate_fullscreen_search()" id="mobile-search-button"></div><div class="desktop-header-search">' . get_search_form(false) . '</div>';
        $close_html = $close_html . $search_html;
    }

    return $close_html;
}


add_filter('genesis_search_form', 'my_search_form_filter', 5);
function my_search_form_filter($form)
{

    global $current_user;
    if(in_array('business_partner', $current_user->roles)){
        return '';
    }

    $pos = strpos($form, 'class="search-form-input"');

    $newform = substr_replace($form, ' autocomplete="off"', $pos + 25, 0);

    return $newform;
}

//custom search html
add_filter('genesis_markup_site-header_close', 'custom_search_html', 10, 2);

function custom_search_html($close_html, $args)
{
    global $current_user;
    if(in_array('business_partner', $current_user->roles)){
        return $close_html;
    }

    if ($close_html) {

        $script = '<script>
        
        
        //close navigation when user leaves page 
        window.addEventListener("pagehide", function() {
            
            var nav = document.getElementById("genesis-mobile-nav-primary");
            if(nav.classList.contains("activated")){
                nav.click();
            }
          
                deactivate_fullscreen_search();
            });
        
        function activate_fullscreen_search() {
                 
            var button = document.getElementById(\'mobile-search-button\');
            
            document.getElementById(\'genesis-mobile-nav-primary\').style.display=\'none\';
    
             
            var mfs = document.getElementById(\'mobile-fullscreen-search\');
            mfs.classList.remove("fullscreen-visible");
            mfs.classList.add("fullscreen-visible");
            mfs.getElementsByClassName(\'search-form-input\')[0].focus();
            
            button.classList.remove("close-button");
            button.classList.add("close-button");
            
            button.onclick=function (){
                deactivate_fullscreen_search();    
            };
        }
        
        function deactivate_fullscreen_search() {
            document.getElementById(\'genesis-mobile-nav-primary\').style.display=\'block\';
    
            var button = document.getElementById(\'mobile-search-button\');
            button.classList.remove("close-button");
            
            button.onclick=function (){
                activate_fullscreen_search();   
            };
    
           
            var mfs = document.getElementById(\'mobile-fullscreen-search\');
            mfs.classList.remove("fullscreen-visible");
            mfs.getElementsByClassName(\'search-form-input\')[0].blur();
        }
        
         function do_search() {
            document.getElementsByClassName(\'mfs-submit-button\')[0].style.border= \'2px solid #f00\';
            document.getElementsByClassName(\'mfs-submit-button\')[0].style.background= \'#fff\';
    
            document.getElementsByClassName(\'mfs-submit-button\')[0].style.color= \'#f00\';
    
            
            
            var mfs = document.getElementById(\'mobile-fullscreen-search\');    
             mfs.getElementsByClassName(\'search-form-submit\')[0].click();
        }
        
        </script>';

        $search_html = "<div id=\"mobile-fullscreen-search\">" . get_search_form(false) .
            "<button class='mfs-cancel-button' onclick='deactivate_fullscreen_search()'>abbrechen</button> <button class='mfs-submit-button' onclick='do_search()'>suchen</button></div>";

        $close_html = $search_html . $script . $close_html;
    }
    return $close_html;
}


add_filter('genesis_search_text', 'inseider_search_text');

function inseider_search_text($text)
{
    return ('Suche');
}

add_filter('genesis_search_button_text', 'inseider_search_button_text');
function inseider_search_button_text($text)
{
    return "";
}

add_filter('genesis_markup_search-form-submit_open', 'custom_search_form_submit');
/**
 * Change Search Form submit button markup.
 *
 * @return string Modified HTML for search forms' submit button.
 */
function custom_search_form_submit()
{
    $search_button_text = apply_filters('genesis_search_button_text', esc_attr__('Search', 'genesis'));
    $searchicon = '<div class="search-icon">' . file_get_contents(get_Server_Image_Dir() . "search_icon.svg") . '</div>';

    return sprintf('<button type="submit" class="search-form-submit" aria-label="Search">%s<span class="screen-reader-text">%s</span></button>', $searchicon, $search_button_text);
}
