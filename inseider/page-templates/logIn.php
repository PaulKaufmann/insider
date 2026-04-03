<?php
/**
 * Template Name: Log in
 */


add_action('genesis_before_header', 'remove_header');

add_action('genesis_before_header', 'hide_push_popup');

function hide_push_popup()
{
    echo '<style>
    #onesignal-slidedown-container{
    display: none;
    }
</style>';
}

//add_action('genesis_before_footer', 'show_error_landing');
function show_error_landing()
{
    echo '<h3 style="text-align: center">Aktuell wird eine Serverwartung durchgeführt, bitte versuchen Sie es später noch einmal.</h3>';#
    echo '<style>
    .login-form-container,#wp-submit{
    display: none !important;
    }
</style>';
}


add_filter('genesis_attr_site-inner', 'inseider_add_css_attr_site_inner');
function inseider_add_css_attr_site_inner($attributes)
{

    // add original plus extra CSS classes
    $attributes['class'] .= ' noMargin';

    // return the attributes
    return $attributes;

}

genesis();
