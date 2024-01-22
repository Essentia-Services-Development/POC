<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

return apply_filters('rh_layout_builder_fields', array(
	'id'          => 'page_opt',
	'types'       => array('page'),
	'title'       => esc_html__('Page options', 'rehub-framework'),
	'priority'    => 'low',
	'context'     => 'side',
	'mode'        => WPALCHEMY_MODE_EXTRACT,
	'template'    => array(
		array(
			'type' => 'radiobutton',
			'name' => 'content_type',
			'label' => esc_html__('Type of content area', 'rehub-framework'),
			'default' => 'def',
			'items' => array(
				array(
					'value' => 'def',
					'label' => esc_html__('Content with sidebar', 'rehub-framework'),
				),
				array(
					'value' => 'full_width',
					'label' => esc_html__('Full Width Content Box', 'rehub-framework'),
				),
				array(
					'value' => 'full_post_area',
					'label' => esc_html__('Full width of browser window', 'rehub-framework'),
				),				
			),
			'default' => array(
				'def',
			),	
		),		
		array(
			'type' => 'radiobutton',
			'name' => '_header_disable',
			'label' => esc_html__('How to show header?', 'rehub-framework'),
			'default' => '0',
			'items' => array(
				array(
					'value' => '0',
					'label' => esc_html__('Default', 'rehub-framework'),
				),
				array(
					'value' => '1',
					'label' => esc_html__('Disable header', 'rehub-framework'),
				),
				array(
					'value' => '2',
					'label' => esc_html__('Transparent', 'rehub-framework'),
				),				
			)
		),
		array(
			'type' => 'toggle',
			'name' => '_title_disable',
			'label' => esc_html__('Disable title', 'rehub-framework'),
		),
		array(
			'type' => 'toggle',
			'name' => '_enable_preloader',
			'label' => esc_html__('Enable preloader', 'rehub-framework'),
		),			
		array(
			'type' => 'toggle',
			'name' => '_enable_comments',
			'label' => esc_html__('Enable comments', 'rehub-framework'),
		),					
		array(
			'type' => 'toggle',
			'name' => 'menu_disable',
			'label' => esc_html__('Disable menu', 'rehub-framework'),
		),			
		array(
			'type' => 'toggle',
			'name' => '_footer_disable',
			'label' => esc_html__('Disable footer', 'rehub-framework'),
		),	
		array(
			'type' => 'toggle',
			'name' => 'bg_disable',
			'label' => esc_html__('Disable default background image', 'rehub-framework'),
		),																			
	),
	'include_template' => 'def_page.php',
));

/**
 * EOF
 */