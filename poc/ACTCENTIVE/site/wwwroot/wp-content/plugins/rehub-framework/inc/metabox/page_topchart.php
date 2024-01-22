<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

return array(
	'id'          => 'rehub_top_chart',
	'types'       => array('page'),
	'title'       => esc_html__('Comparison chart settings', 'rehub-framework'),
	'priority'    => 'low',
	'mode'        => WPALCHEMY_MODE_EXTRACT,
	'template'    => array(		
		array(
			'type' => 'textbox',
			'name' => 'top_chart_ids',
			'label' => esc_html__('Enter post ids for comparison', 'rehub-framework'),
			'description' => esc_html__('Set with comma without spaces, example: 2,34,67,88', 'rehub-framework'),			
		),	
		array(
			'type' => 'textbox',
			'name' => 'top_chart_type',
			'label' => esc_html__('Enter name of custom post type or leave blank (default, post). For woocommerce - type "product"', 'rehub-framework'),			
		),												
	    array(
			'type'      => 'group',
			'repeating' => true,
			'sortable'  => true,
			'name'      => 'columncontents',
			'title'     => esc_html__('Row', 'rehub-framework'),
			'fields'    => array(
				array(
					'type' => 'textbox',
					'name' => 'column_name',
					'label' => esc_html__('Set heading name for row', 'rehub-framework'),				
				),			    				
				array(
					'type' => 'select',
					'name' => 'column_type',
					'label' => esc_html__('Select generated content:', 'rehub-framework'),
					'items' => array(
						array(
							'value' => 'heading',
							'label' => esc_html__('Row heading', 'rehub-framework'),
						),						
						array(
							'value' => 'image',
							'label' => esc_html__('Thumbnail with title', 'rehub-framework'),
						),
						array(
							'value' => 'imagefull',
							'label' => esc_html__('Thumbnail with title, review score, button and price', 'rehub-framework'),
						),						
						array(
							'value' => 'title',
							'label' => esc_html__('Title', 'rehub-framework'),
						),	
						array(
							'value' => 'excerpt',
							'label' => esc_html__('Short description', 'rehub-framework'),
						),																							
						array(
							'value' => 'meta_value',
							'label' => esc_html__('Meta value', 'rehub-framework'),
						),
						array(
							'value' => 'taxonomy_value',
							'label' => esc_html__('Taxonomy value / Woocommerce attribute', 'rehub-framework'),
						),																
						array(
							'value' => 'review_function',
							'label' => esc_html__('Editor\'s Review score', 'rehub-framework'),
						),	
						array(
							'value' => 'user_review_function',
							'label' => esc_html__('User stars rating', 'rehub-framework'),
						),
						array(
							'value' => 'static_user_review_function',
							'label' => esc_html__('Static User stars rating (based on full user reviews)', 'rehub-framework'),
						),						
						array(
							'value' => 'review_link',
							'label' => esc_html__('Link on post review', 'rehub-framework'),
						),
						array(
							'value' => 'review_criterias',
							'label' => esc_html__('Review Criterias (Editor)', 'rehub-framework'),
						),												
						array(
							'value' => 'affiliate_btn',
							'label' => esc_html__('Affiliate button', 'rehub-framework'),
						),
						array(
							'value' => 'woo_attribute',
							'label' => esc_html__('Woocommerce attribute by slug', 'rehub-framework'),
						),						
						array(
							'value' => 'woo_review',
							'label' => esc_html__('Woocommerce review score', 'rehub-framework'),
						),	
						array(
							'value' => 'woo_btn',
							'label' => esc_html__('Woocommerce button with price', 'rehub-framework'),
						),
						array(
							'value' => 'woo_vendor',
							'label' => esc_html__('Woocommerce vendor', 'rehub-framework'),
						),																		
						array(
							'value' => 'shortcode',
							'label' => esc_html__('Shortcode', 'rehub-framework'),
						),																												
					),
				),
			    array(
			        'type' => 'notebox',
			        'name' => 'nb_1',
			        'label' => esc_html__('Note', 'rehub-framework'),
			        'description' => esc_html__('By default button will grab data from product review, but you can set your own meta names where data are stored', 'rehub-framework'),
			        'status' => 'normal',
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_btn',
					),			        
			    ),
				array(
					'type' => 'textbox',
					'name' => 'btn_text',
					'label' => esc_html__('Insert button text or leave blank', 'rehub-framework'),	
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_btn',
					),					
				),	
				array(
					'type' => 'textbox',
					'name' => 'btn_url',
					'label' => esc_html__('Insert meta value where affiliate url is stored', 'rehub-framework'),	
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_btn',
					),					
				),
				array(
					'type' => 'textbox',
					'name' => 'btn_price',
					'label' => esc_html__('Insert meta value where affiliate price is stored', 'rehub-framework'),	
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_btn',
					),					
				),															
				array(
					'type' => 'textbox',
					'name' => 'tax_name',
					'label' => esc_html__('Enter taxonomy slug', 'rehub-framework'),
					'description' => esc_html__('Enter slug of your taxonomy. If you want to get woocommerce attribute, enable checkbox below.', 'rehub-framework'),
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_tax',
					),					
				),
				array(
					'type' => 'textbox',
					'name' => 'woo_attr',
					'label' => esc_html__('Enter attribute slug', 'rehub-framework'),
					'description' => esc_html__('Enter slug of your woocommerce attribute.', 'rehub-framework'),
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_attr',
					),					
				),				
				array(
					'type' => 'toggle',
					'name' => 'is_attribute',
					'label' => esc_html__('Is this woocommerce attribute?', 'rehub-framework'),
					'default' => '0',
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_tax',
					),					
				),							    
				array(
					'type' => 'toggle',
					'name' => 'image_link_affiliate',
					'label' => esc_html__('Enable link on affiliate product from thumbnail?', 'rehub-framework'),
					'default' => '0',
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_image',
					),					
				),
				array(
					'type' => 'toggle',
					'name' => 'title_link_affiliate',
					'label' => esc_html__('Enable link on affiliate product from title?', 'rehub-framework'),
					'default' => '0',
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_image',
					),					
				),	
				array(
					'type' => 'toggle',
					'name' => 'sticky_header',
					'label' => esc_html__('Enable sticky on scroll in this row (expiremental)?', 'rehub-framework'),
					'default' => '0',
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_image',
					),					
				),	
				array(
					'type' => 'toggle',
					'name' => 'enable_diff',
					'label' => esc_html__('Enable checkbox for dynamic differences?', 'rehub-framework'),
					'default' => '0',
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_image',
					),					
				),				    
				array(
					'type'      => 'group',
					'repeating' => false,
					'sortable'  => false,
					'name'      => 'column_meta_fields',
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_meta_value',
					),					
					'title'     => esc_html__('Value from custom field', 'rehub-framework'),
					'fields'    => array(
						array(
							'type' => 'select',
							'name' => 'column_meta_type',
							'label' => esc_html__('Type of meta value', 'rehub-framework'),					
							'items' => array(
								array(
									'value' => 'text',
									'label' => esc_html__('Text value', 'rehub-framework'),
								),
								array(
									'value' => 'checkbox',
									'label' => esc_html__('Checkbox (true or false)', 'rehub-framework'),
								),	
								array(
									'value' => 'acfmulti',
									'label' => esc_html__('ACF multichoice field', 'rehub-framework'),
								),																			
							),
							'default' => 'text',
						),
						array(
							'type'      => 'textbox',
							'name'      => 'column_meta_name',
							'label'     => esc_html__('Key (slug) of custom field', 'rehub-framework'),
							'description'     => esc_html__('Find some important', 'rehub-theme').'<a href="http://rehubdocs.wpsoul.com/docs/rehub-theme/list-of-important-meta-fields/" target="_blank"> '.esc_html__('meta keys', 'rehub-theme').'</a>',
						),
						array(
							'type'      => 'textbox',
							'name'      => 'column_meta_prefix',
							'label'     => esc_html__('Prefix for field', 'rehub-framework'),
						),
						array(
							'type'      => 'textbox',
							'name'      => 'column_meta_postfix',
							'label'     => esc_html__('Postfix for field', 'rehub-framework'),
						),																																												
						array(
							'type' => 'toggle',
							'name' => 'column_customize',
							'label' => esc_html__('Customize font size and colors of column?', 'rehub-framework'),
							'default' => '0',
						),
						array(
						    'type' => 'slider',
						    'name' => 'column_meta_value_size',
						    'label' => esc_html__('Meta Value Font size', 'rehub-framework'),
						    'description' => esc_html__('Default - 15px', 'rehub-framework'),
						    'min' => '10',
						    'max' => '36',
						    'step' => '1',
						    'default' => '',
							'dependency' => array(
								'field' => 'column_customize',
								'function' => 'vp_dep_boolean',
						 	),						    
						),
						array(
						    'type' => 'color',
						    'name' => 'column_meta_value_color',
						    'label' => esc_html__('Meta Value Font Color', 'rehub-framework'),
						    'description' => esc_html__('Default - #111111', 'rehub-framework'),
						    'default' => '',
						    'format' => 'hex',	
							'dependency' => array(
								'field' => 'column_customize',
								'function' => 'vp_dep_boolean',
						 	),						    					    
						),																					
					),
				),	
				array(
					'type' => 'select',
					'name' => 'top_review_circle',
					'label' => esc_html__('Design of rating', 'rehub-framework'),
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_review_function',
					),					
					'items' => array(
						array(
							'value' => '0',
							'label' => esc_html__('Simple text', 'rehub-framework'),
						),
						array(
							'value' => '1',
							'label' => esc_html__('Circle design', 'rehub-framework'),
						),
						array(
							'value' => '2',
							'label' => esc_html__('Star design', 'rehub-framework'),
						),															
					),
					'default' => '2',
				),
				array(
					'type' => 'textbox',
					'name' => 'shortcode_value',
					'label' => esc_html__('Enter shortcode', 'rehub-framework'),
					'description' => esc_html__('Enter shortcode value. Example, [shortcode]', 'rehub-framework'),
					'dependency' => array(
						'field'    => 'column_type',
						'function' => 'rehub_column_is_short',
					),					
				),															
			),
		),	
		array(
			'type' => 'toggle',
			'name' => 'top_chart_width',
			'label' => esc_html__('Make page full width?', 'rehub-framework'),
			'default' => '1',
		),														
		array(
			'type' => 'html',
			'name' => 'shortcode_charts',
			'label' => esc_html__('Shortcode', 'rehub-framework'),
			'description' => esc_html__('Shortcode', 'rehub-framework'),
			'binding' => array(
				'field' => '',
				'function' => 'top_charts_shortcode',
			)
		),
		array(
			'type' => 'toggle',
			'name' => 'shortcode_charts_enable',
			'label' => esc_html__('If enabled - content of chart will be inserted on page only by shortcode above', 'rehub-framework'),
			'default' => '0',
		),									
	),
    'include_template' => 'template-topcharts.php',
);

/**
 * EOF
 */