<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * @since 4.4
 * Default templates list
 */

$data = array();
$data['name'] = esc_html__( 'Row with sidebar area', 'rehub-theme' );
$data['custom_class'] = 'img_row_sidebar'; // default is ''
$data['content'] = <<<CONTENT
[vc_section][vc_row rehub_container="true"][vc_column width="2/3"][/vc_column][vc_column width="1/3"][vc_widget_sidebar sidebar_id="rhsidebar"][/vc_column][/vc_row][/vc_section]
CONTENT;
vc_add_default_templates( $data );