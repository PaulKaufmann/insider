<?php
/**
 * inseider child theme.
 *
 * @package inseider
 * @author  StudioPress
 * @license GPL-2.0-or-later
 * @link    https://my.studiopress.com/themes/genesis-sample/
 */

/**
 * Genesis responsive menus settings. (Requires Genesis 3.0+.)
 */
return array(
	'script' => array(
	    'mainMenu' => __( '', 'genesis-sample' ),
		'menuClasses' => array(
			'others' => array( '.nav-primary' ),
		),
	),
	'extras' => array(
		'media_query_width' => '960px',
	),
);
