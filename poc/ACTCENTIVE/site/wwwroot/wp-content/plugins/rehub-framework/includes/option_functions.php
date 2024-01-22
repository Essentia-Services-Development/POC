<?php
/**
 * Rehub Framework Theme Option Functions
 *
 * @package ReHub\Functions
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_options = RH_FRAMEWORK_ABSPATH . '/inc/options/option.php';

$theme_options_obj = new VP_Option(array(
	'is_dev_mode' => false, // dev mode, default to false
	'option_key' => 'rehub_option',
	'page_slug'  => 'vpt_option',
	'template'   => $theme_options,
	'menu_page'  => array(),
	'page_title' => esc_html__( "Theme Options", "rehub-framework" ),
	'menu_label' => esc_html__( "Theme Options", "rehub-framework" )
));