<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
$additional_option = array();
if(defined( 'WCFMmp_TOKEN' )){
	$additional_option+= array(
		'title' => esc_html__('Additional links for Vendor panel', 'rehub-framework'),
		'name' => 'menu_wcfm',
		'icon' => 'rhicon rhi-microchip',
		'controls' => array(
			array(
				'type' => 'section',
				'title' => esc_html__('Additional options', 'rehub-framework'),
				'fields' => array(
					array(
						'type' => 'textbox',
						'name' => 'url_for_add_one',
						'label' => esc_html__('Add url of first menu item', 'rehub-framework'),				
					),
					array(
						'type' => 'textbox',
						'name' => 'label_for_add_one',
						'label' => esc_html__('Add label of first menu item', 'rehub-framework'),					
					),
					array(
						'type' => 'textbox',
						'name' => 'url_for_add_two',
						'label' => esc_html__('Add url of second menu item', 'rehub-framework'),				
					),	
					array(
						'type' => 'textbox',
						'name' => 'label_for_add_two',
						'label' => esc_html__('Add label of second menu item', 'rehub-framework'),					
					),
					array(
						'type' => 'textbox',
						'name' => 'url_for_add_three',
						'label' => esc_html__('Add url of third menu item', 'rehub-framework'),				
					),
					array(
						'type' => 'textbox',
						'name' => 'label_for_add_three',
						'label' => esc_html__('Add label of third menu item', 'rehub-framework'),					
					),	
					array(
						'type' => 'textbox',
						'name' => 'url_for_add_four',
						'label' => esc_html__('Add url of fourth menu item', 'rehub-framework'),				
					),
					array(
						'type' => 'textbox',
						'name' => 'label_for_add_four',
						'label' => esc_html__('Add label of fourth menu item', 'rehub-framework'),					
					),											
				),
			),	
		),
	);
}
if(class_exists('WC_Vendors')){
	$additional_option+= array(
		'title' => esc_html__('Additional links for Vendor panel', 'rehub-framework'),
		'name' => 'menu_wcvendor',
		'icon' => 'rhicon rhi-microchip',
		'controls' => array(
			array(
				'type' => 'section',
				'title' => esc_html__('Vendor settings', 'rehub-framework'),
				'fields' => array(
					array(
						'type' => 'textbox',
						'name' => 'url_for_add_product',
						'label' => esc_html__('Add url of submit product page', 'rehub-framework'),
						'description' => esc_html__('Use it if you want to change default submit page of WC Vendor Free. You can use our RH Frontend PRO plugin to create frontend form for woocommerce. Find it in Rehub-Plugins', 'rehub-framework'),					
					),
					array(
						'type' => 'textbox',
						'name' => 'url_for_edit_product',
						'label' => esc_html__('Add url of edit product page', 'rehub-framework'),					
					),											
				),
			),	
		),
	);	
}
if(REHub_Framework::get_option('theme_subset') == 'repick'){
	$additional_option+= array(
		'title' => esc_html__('Re pick settings', 'rehub-framework'),
		'name' => 'menu_pick',
		'icon' => 'rhicon rhi-microchip',
		'controls' => array(
			array(
				'type' => 'section',
				'title' => esc_html__('Common options', 'rehub-framework'),
				'fields' => array(
					array(
						'type' => 'toggle',
						'name' => 'repick_white',
						'label' => esc_html__('Enable this option if you use white background', 'rehub-framework'),
						'default' => '0',
					),					
					array(
						'type' => 'select',
						'name' => 'rehub_grid_images',
						'label' => esc_html__('Select Image size in grid', 'rehub-framework'),
						'items' => array(
							array(
								'value' => 'contain',
								'label' => esc_html__('Images contain full container', 'rehub-framework'),
							),
							array(
								'value' => 'center',
								'label' => esc_html__('Full images without crop', 'rehub-framework'),
							),														
						),
							'default' => array(
							'contain',
						),
					),							

					array(
						'type' => 'multiselect',
						'name' => 'rehub_grid_ad_count',
						'label' => esc_html__('Add ads code after each N item in grid', 'rehub-framework'),
						'items' => array(
							array(
								'value' => '1',
								'label' => 1,
							),
							array(
								'value' => '2',
								'label' => 2,
							),																
							array(
								'value' => '3',
								'label' => 3,
							),
							array(
								'value' => '4',
								'label' => 4,
							),
							array(
								'value' => '5',
								'label' => 5,
							),
							array(
								'value' => '6',
								'label' => 6,
							),
							array(
								'value' => '7',
								'label' => 7,
							),
							array(
								'value' => '8',
								'label' => 8,
							),
							array(
								'value' => '9',
								'label' => 9,
							),
							array(
								'value' => '10',
								'label' => 10,
							),																																																																					
						),								
					),
					array(
						'type' => 'textarea',
						'name' => 'rehub_grid_ads_code',
						'label' => esc_html__('Ads code to insert in grid item', 'rehub-framework'),
						'description' => esc_html__('Enter your Analytics code or any html, js code', 'rehub-framework'),
					),	
					array(
						'type' => 'textarea',
						'name' => 'rehub_grid_ads_desc',
						'label' => esc_html__('Set descriptions for item with ads.', 'rehub-framework'),
						'description' => esc_html__('Add simple text, each text stroke from next line. Text will be used randomly', 'rehub-framework'),
					),						
					array(
						'type' => 'textbox',
						'name' => 'rehub_amazon_btn',
						'label' => esc_html__('Specify text for search link for Amazon', 'rehub-framework'),
						'description' => esc_html__('This is used in top offer block', 'rehub-framework'),
					),	
					array(
						'type' => 'textbox',
						'name' => 'rehub_amazon_surl',
						'label' => esc_html__('Specify generated search url for Amazon', 'rehub-framework'),
						'description' => 'You can copy it from url generator on amazon. <a href="http://rehubdocs.wpsoul.com/docs/rehub-theme/child-themes/repick-settings/" target="_blank">Check tutorial</a>',
					),	
					array(
						'type' => 'textbox',
						'name' => 'rehub_ebay_surl',
						'label' => esc_html__('Specify generated search url for Ebay', 'rehub-framework'),
						'description' => 'You can copy it from url generator on ebay. <a href="http://rehubdocs.wpsoul.com/docs/rehub-theme/child-themes/repick-settings/" target="_blank">Check tutorial</a>',
					),											
					array(
						'type' => 'textbox',
						'name' => 'rehub_ebay_btn',
						'label' => esc_html__('Specify text for search link for Ebay', 'rehub-framework'),
						'description' => esc_html__('This is used in top offer block', 'rehub-framework'),
					),							
					array(
						'type' => 'toggle',
						'name' => 'aff_link_image',
						'label' => esc_html__('Make link from image to affiliate post instead link to post', 'rehub-framework'),
						'default' => '0',
					),	
					array(
						'type' => 'toggle',
						'name' => 'aff_link_title',
						'label' => esc_html__('Make link from title to affiliate post instead link to post', 'rehub-framework'),
						'default' => '0',
					),											    					   																																																				
				),
			),	
		),
	);
}
return $additional_option;

/**
 *EOF
 */