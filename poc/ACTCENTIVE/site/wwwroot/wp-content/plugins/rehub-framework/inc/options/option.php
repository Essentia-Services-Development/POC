<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

if ( defined('REHUB_MAIN_COLOR')) {
	$maincolor = REHUB_MAIN_COLOR;
	$secondarycolor = REHUB_SECONDARY_COLOR;
	$btncolor = REHUB_BUTTON_COLOR;
	$btncolortext = REHUB_BUTTON_COLOR_TEXT;
	$default_layout = REHUB_DEFAULT_LAYOUT;
	$contentboxdisable = REHUB_BOX_DISABLE;
}else{
	$maincolor = '#8035be';
	$secondarycolor = '#000000';
	$btncolor = '#de1414';
	$default_layout = 'communitylist';
	$contentboxdisable = '0';
	$btncolortext = '#ffffff';
}

$theme_options =  array(
	'title' => esc_html__('Theme Options', 'rehub-framework'),
	'page' => 'Rehub Theme Options',
	'logo' => '',
	'menus' => array(
		array(
			'title' => esc_html__('General Options', 'rehub-framework'),
			'name' => 'menu_1',
			'icon' => 'rhicon rhi-microchip',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('General Options', 'rehub-framework'),
					'fields' => array(				
						array(
							'type' => 'select',
							'name' => 'archive_layout',
							'label' => esc_html__('Select Archive Layout', 'rehub-framework'),
							'description' => esc_html__('Select what kind of post string layout you want to use for archives', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'blog',
									'label' => esc_html__('Blog Layout', 'rehub-framework'),
								),								
								array(
									'value' => 'newslist',
									'label' => esc_html__('Simple News List', 'rehub-framework'),
								),
								array(
									'value' => 'communitylist',
									'label' => esc_html__('Community List', 'rehub-framework'),
								),	
								array(
									'value' => 'deallist',
									'label' => esc_html__('Deal List', 'rehub-framework'),
								),																
								array(
									'value' => 'grid',
									'label' => esc_html__('Masonry Grid layout', 'rehub-framework'),
								),	
								array(
									'value' => 'columngrid',
									'label' => esc_html__('Equal height Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'compactgrid',
									'label' => esc_html__('Compact deal grid layout', 'rehub-framework'),
								),								
								array(
									'value' => 'dealgrid',
									'label' => esc_html__('Deal Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'mobilegrid',
									'label' => esc_html__('Mobile Optimized Deal Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'cardblog',
									'label' => esc_html__('Cards', 'rehub-framework'),
								),								
								array(
									'value' => 'dealgridfull',
									'label' => esc_html__('Full width Deal Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'mobilegridfull',
									'label' => esc_html__('Mobile Optimized Full width Deal Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'compactgridfull',
									'label' => esc_html__('Full width compact deal grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'columngridfull',
									'label' => esc_html__('Equal height Full width Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'gridfull',
									'label' => esc_html__('Full width Masonry Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'cardblogfull',
									'label' => esc_html__('Cards Full width', 'rehub-framework'),
								),									

							),
							'default' => array(
								$default_layout
							),
						),
						array(
							'type' => 'select',
							'name' => 'search_layout',
							'label' => esc_html__('Select Search Layout', 'rehub-framework'),
							'description' => esc_html__('Select what kind of post string layout you want to use for search pages', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'blog',
									'label' => esc_html__('Blog Layout', 'rehub-framework'),
								),								
								array(
									'value' => 'newslist',
									'label' => esc_html__('Simple News List', 'rehub-framework'),
								),
								array(
									'value' => 'communitylist',
									'label' => esc_html__('Community List', 'rehub-framework'),
								),	
								array(
									'value' => 'deallist',
									'label' => esc_html__('Deal List', 'rehub-framework'),
								),																	
								array(
									'value' => 'grid',
									'label' => esc_html__('Masonry Grid layout', 'rehub-framework'),
								),	
								array(
									'value' => 'columngrid',
									'label' => esc_html__('Equal height Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'compactgrid',
									'label' => esc_html__('Compact deal grid layout', 'rehub-framework'),
								),								
								array(
									'value' => 'dealgrid',
									'label' => esc_html__('Deal Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'mobilegrid',
									'label' => esc_html__('Mobile Optimized Deal Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'cardblog',
									'label' => esc_html__('Cards', 'rehub-framework'),
								),									
								array(
									'value' => 'dealgridfull',
									'label' => esc_html__('Full width Deal Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'mobilegridfull',
									'label' => esc_html__('Mobile Optimized Full width Deal Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'compactgridfull',
									'label' => esc_html__('Full width compact deal grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'columngridfull',
									'label' => esc_html__('Equal height Full width Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'gridfull',
									'label' => esc_html__('Full width Masonry Grid layout', 'rehub-framework'),
								),	
								array(
									'value' => 'cardblogfull',
									'label' => esc_html__('Cards Full width', 'rehub-framework'),
								),																														
							),
							'default' => array(
								$default_layout
							),
						),
						array(
							'type' => 'select',
							'name' => 'enable_pagination',
							'label' => esc_html__('Select pagination type for categories', 'rehub-framework'),
							'description' => esc_html__('Choose number of posts per page in Settings - Reading settings. Recommended number - 12', 'rehub-framework'),
							'items' => array(
								array(
									'value' => '1',
									'label' => esc_html__('Simple Pagination', 'rehub-framework'),
								),	
								array(
									'value' => '2',
									'label' => esc_html__('Infinite scroll', 'rehub-framework'),
								),															
								array(
									'value' => '3',
									'label' => esc_html__('Next page button', 'rehub-framework'),
								),																																
							),
							'default' => array(
								'1',
							),
						),						
						array(
							'type' => 'select',
							'name' => 'post_layout_style',
							'label' => esc_html__('Post layout', 'rehub-framework'),
							'default' => 'normal_post',
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value'  => 'rehub_get_post_layout_array',
									),
								),
							),
							'default' => array(
								'default',
							),
						),	
						array(
							'type' => 'select',
							'name' => 'width_layout',
							'label' => esc_html__('Select Width Style', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'regular',
									'label' => esc_html__('Regular (1200px)', 'rehub-framework'),
								),								
								array(
									'value' => 'extended',
									'label' => esc_html__('Extended (1530px)', 'rehub-framework'),
								),	
								array(
									'value' => 'compact',
									'label' => esc_html__('Compact (adsense banners optimized) 1080px', 'rehub-framework'),
								),	
								array(
									'value' => 'mini',
									'label' => esc_html__('Mini 1000px', 'rehub-framework'),
								),																					
							),
							'default' => array(
								'regular',
							),						
						),
						array(
							'type' => 'select',
							'name' => 'theme_subset',
							'label' => esc_html__('Select theme subset', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'flat',
									'label' => esc_html__('Clean Rehub', 'rehub-framework'),
								),	
								array(
									'value' => 'redigit',
									'label' => esc_html__('Redigit for digital products', 'rehub-framework'),
								),			
								array(
									'value' => 'regame',
									'label' => esc_html__('White search (Regame style)', 'rehub-framework'),
								),	
								array(
									'value' => 'redeal',
									'label' => esc_html__('Redeal (Big buttons in header)', 'rehub-framework'),
								),
								array(
									'value' => 'redirect',
									'label' => esc_html__('Redirect (Full width header)', 'rehub-framework'),
								),	
								array(
									'value' => 'relearn',
									'label' => esc_html__('Relearn (Full width header)', 'rehub-framework'),
								),	
								array(
									'value' => 'rething',
									'label' => esc_html__('Rething (big masonry grid)', 'rehub-framework'),
								),	
								array(
									'value' => 'repick',
									'label' => esc_html__('Repick (big grid items)', 'rehub-framework'),
								),	
								array(
									'value' => 'recash',
									'label' => esc_html__('Recash', 'rehub-framework'),
								),	
								array(
									'value' => 'remart',
									'label' => esc_html__('ReMart', 'rehub-framework'),
								),	
							),
							'default' => array(
								'flat',
							),							
						),						
						array(
							'type' => 'textarea',
							'name' => 'category_filter_panel',
							'label' => esc_html__('Category filter panel', 'rehub-framework'),		
							'description' => 'You can add additional filter panel in category page. Add each filter from next line. Example: Title:meta_key:DESC. <br />In most cases, you will need next filter panel code. <br /><br />Show all:all:DESC<br />Best price:rehub_main_product_price:ASC<br />Hottest:post_hot_count:DESC<br />Popular:rehub_views:DESC<br />Less than 100:price:0-100:DESC<br /><br />To show hottest deals sorted by date, use<br />Hottest:hot:DESC<br /><br />To show deals and coupons sorted by expiration date<br />Expired soon:expiration:ASC<br /><br />To show random<br />Random:random:ASC<br /><br /><a href="http://rehubdocs.wpsoul.com/docs/rehub-framework/list-of-important-meta-fields/" target="_blank">Check other important fields</a>',
						),	
						array(
							'type' => 'textarea',
							'name' => 'rehub_custom_css',
							'label' => esc_html__('Custom CSS', 'rehub-framework'),
							'description' => esc_html__('Write your custom CSS here', 'rehub-framework'),
						),						
						array(
							'type' => 'textarea',
							'name' => 'rehub_analytics',
							'label' => esc_html__('Js code for footer', 'rehub-framework'),
							'description' => esc_html__('Enter your Analytics code or any html, js code', 'rehub-framework'),
						),	
						array(
							'type' => 'textarea',
							'name' => 'rehub_analytics_header',
							'label' => esc_html__('Js code for header (analytics)', 'rehub-framework'),						
						),																	
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Additional Blog/News Area', 'rehub-framework'),
					'fields' => array(							
						array(
							'type' => 'toggle',
							'name' => 'enable_blog_posttype',
							'label' => esc_html__('Enable separate blog post type', 'rehub-framework'),
							'description' => esc_html__('When enabled, save permalinks in Settings - Permalinks', 'rehub-framework'),													
							'default' => '0',							
						),
						array(
							'type' => 'select',
							'name' => 'blog_layout_style',
							'label' => esc_html__('Single page Blog layout', 'rehub-framework'),
							'default' => 'normal_post',
							'dependency' => array(
	                        	'field' => 'enable_blog_posttype',
	                        	'function' => 'vp_dep_boolean',
	                        ),						
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value'  => 'rehub_get_post_layout_array',
									),
								),
							),
							'default' => array(
								'default',
							),
						),	
						array(
							'type' => 'textbox',
							'name' => 'blog_posttype_label',
							'label' => esc_html__('Set custom label for Arhive and Breadcrumbs. Default is - Blog', 'rehub-framework'),	
							'dependency' => array(
	                        	'field' => 'enable_blog_posttype',
	                        	'function' => 'vp_dep_boolean',
	                        ),												
						),					
						array(
							'type' => 'textbox',
							'name' => 'blog_posttype_slug',
							'label' => esc_html__('Set custom blog permalink slug for Blog. Update permalinks after this in Settings - permalinks', 'rehub-framework'),	
							'dependency' => array(
	                        	'field' => 'enable_blog_posttype',
	                        	'function' => 'vp_dep_boolean',
	                        ),												
						),	
						array(
							'type' => 'textbox',
							'name' => 'blog_posttypecat_slug',
							'label' => esc_html__('Set custom blog permalink slug for Blog Category. Update permalinks after this in Settings - permalinks', 'rehub-framework'),	
							'dependency' => array(
	                        	'field' => 'enable_blog_posttype',
	                        	'function' => 'vp_dep_boolean',
	                        ),												
						),	
						array(
							'type' => 'textbox',
							'name' => 'blog_posttypetag_slug',
							'label' => esc_html__('Set custom blog permalink slug for Blog Tag. Update permalinks after this in Settings - permalinks', 'rehub-framework'),	
							'dependency' => array(
	                        	'field' => 'enable_blog_posttype',
	                        	'function' => 'vp_dep_boolean',
	                        ),												
						),													
						array(
							'type' => 'select',
							'name' => 'blog_archive_layout',
							'label' => esc_html__('Select Blog Archive Layout', 'rehub-framework'),
							'description' => esc_html__('Select what kind of post string layout you want to use for blog  archives', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'big_blog',
									'label' => esc_html__('Big images Blog Layout', 'rehub-framework'),
								),								
								array(
									'value' => 'list_blog',
									'label' => esc_html__('List Layout with left thumbnails', 'rehub-framework'),
								),	
								array(
									'value' => 'grid_blog',
									'label' => esc_html__('Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'cardblog',
									'label' => esc_html__('Cards', 'rehub-framework'),
								),
								array(
									'value' => 'cardblogfull',
									'label' => esc_html__('Full width Cards', 'rehub-framework'),
								),																
								array(
									'value' => 'gridfull_blog',
									'label' => esc_html__('Full width Grid layout', 'rehub-framework'),
								),																							
							),
							'default' => array(
								'list_blog',
							),
							'dependency' => array(
	                        	'field' => 'enable_blog_posttype',
	                        	'function' => 'vp_dep_boolean',
	                        ),						
						),			
					),
				),	
			),
		),
		array(
			'title' => esc_html__('Appearance/Color', 'rehub-framework'),
			'name' => 'menu_6',
			'icon' => 'rhicon rhi-edit',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Color schema of website', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'color',
							'name' => 'rehub_custom_color',
							'label' => esc_html__('Main Highlight color', 'rehub-framework'),
							'description' => esc_html__('Color to highlight items', 'rehub-framework'),
							'format' => 'hex',
							'default'=> $maincolor,
						),
						array(
							'type' => 'color',
							'name' => 'rehub_sec_color',
							'label' => esc_html__('Secondary color', 'rehub-framework'),
							'description' => esc_html__('Color for system forms (for search buttons, tabs, etc)', 'rehub-framework'),
							'format' => 'hex',
							'default'=> $secondarycolor,							
						),	
						array(
							'type' => 'color',
							'name' => 'rehub_third_color',
							'label' => esc_html__('Background hightlight color', 'rehub-framework'),
							'description' => esc_html__('Color for background on extended layouts. Leave empty to use main Highlight color', 'rehub-framework'),
							'format' => 'hex',							
						),						
						array(
							'type' => 'color',
							'name' => 'rehub_btnoffer_color',
							'label' => esc_html__('Offer buttons color', 'rehub-framework'),
							'format' => 'hex',
							'default'=> $btncolor,						
						),	
						array(
							'type' => 'color',
							'name' => 'rehub_btnoffer_color_hover',
							'label' => esc_html__('Offer button hover color', 'rehub-framework'),
							'format' => 'hex',						
						),
						array(
							'type' => 'color',
							'name' => 'rehub_btnoffer_color_text',
							'label' => esc_html__('Offer button text color', 'rehub-framework'),
							'format' => 'hex',
							'default' => $btncolortext,						
						),
						array(
							'type' => 'color',
							'name' => 'rehub_btnofferhover_color_text',
							'label' => esc_html__('Offer button Hover text color', 'rehub-framework'),
							'format' => 'hex',					
						),																		
						array(
							'type' => 'select',
							'name' => 'enable_smooth_btn',
							'label' => esc_html__('Enable smooth design for buttons?', 'rehub-framework'),
							'items' => array(
								array(
									'value' => '0',
									'label' => esc_html__('No', 'rehub-framework'),
								),								
								array(
									'value' => '1',
									'label' => esc_html__('Rounded', 'rehub-framework'),
								),	
								array(
									'value' => '2',
									'label' => esc_html__('Soft Rounded', 'rehub-framework'),
								),																						
							),
							'default' => array(
								'2',
							),						
						),												
						array(
							'type' => 'color',
							'name' => 'rehub_color_link',
							'label' => esc_html__('Custom color for links inside posts', 'rehub-framework'),
							'format' => 'hex',	
						),											
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Layout settings', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'rehub_sidebar_left',
							'label' => esc_html__('Set sidebar to left side?', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'rehub_body_block',
							'label' => esc_html__('Enable boxed version?', 'rehub-framework'),
							'default' => '0',
						),						
						array(
							'type' => 'toggle',
							'name' => 'rehub_content_shadow',
							'label' => esc_html__('Disable box borders under content box?', 'rehub-framework'),			
							'default' => $contentboxdisable,	
						),													
						array(
							'type' => 'color',
							'name' => 'rehub_color_background',
							'label' => esc_html__('Background Color', 'rehub-framework'),
							'description' => esc_html__('Choose the background color', 'rehub-framework'),
							'format' => 'hex',
						),
						array(
							'type' => 'toggle',
							'name' => 'dark_theme',
							'label' => esc_html__('Dark theme', 'rehub-framework'),
							'description' => esc_html__('Use it if you need white text on dark background', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'upload',
							'name' => 'rehub_background_image',
							'label' => esc_html__('Background Image', 'rehub-framework'),
							'description' => esc_html__('Upload a background image. Works only if you set also background color above', 'rehub-framework'),
							'default' => '',
						),
						array(
							'type' => 'select',
							'name' => 'rehub_background_repeat',
							'label' => esc_html__('Background Repeat', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'repeat',
									'label' => esc_html__('Repeat', 'rehub-framework'),
								),
								array(
									'value' => 'no-repeat',
									'label' => esc_html__('No Repeat', 'rehub-framework'),
								),
								array(
									'value' => 'repeat-x',
									'label' => esc_html__('Repeat X', 'rehub-framework'),
								),
								array(
									'value' => 'repeat-y',
									'label' => esc_html__('Repeat Y', 'rehub-framework'),
								),
							),
							'default' => array(
								'repeat',
							),
						),
						array(
							'type' => 'select',
							'name' => 'rehub_background_position',
							'label' => esc_html__('Background Position', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'left',
									'label' => 'Left',
								),
								array(
									'value' => 'center',
									'label' => 'Center',
								),
								array(
									'value' => 'right',
									'label' => 'Right',
								),
							),
						),
						array(
							'type' => 'toggle',
							'name' => 'rehub_background_fixed',
							'label' => esc_html__('Fixed Background Image?', 'rehub-framework'),
							'description' => esc_html__('The background is fixed with regard to the viewport.', 'rehub-framework'),
						),												
						array(
							'type' => 'textbox',
							'name' => 'rehub_branded_bg_url',
							'label' => esc_html__('Url for branded background', 'rehub-framework'),
							'description' => esc_html__('Insert url that will be display on background', 'rehub-framework'),
							'default' => '',
							'validation' => 'url',
						),																			
					),
				),				
			),
		),
		array(
			'title' => esc_html__('Logo Settings', 'rehub-framework'),
			'name' => 'menu_12',
			'icon' => 'rhicon rhi-cog',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Logo settings', 'rehub-framework'),
					'fields' => array(						
						array(
							'type' => 'upload',
							'name' => 'rehub_logo',
							'label' => esc_html__('Upload Logo', 'rehub-framework'),
							'description' => esc_html__('Upload your logo. Max width is 450px. (1200px for full width, 180px for logo + menu row layout)', 'rehub-framework'),
							'default' => '',
						),
																	
						array(
							'type' => 'upload',
							'name' => 'rehub_logo_retina',
							'label' => esc_html__('Upload Logo (retina version)', 'rehub-framework'),
							'description' => esc_html__('Upload retina version of the logo. It should be 2x the size of main logo. Then, set regular logo width and height in fields below', 'rehub-framework'),
							'default' => '',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_logo_retina_width',
							'label' => esc_html__('Logo width', 'rehub-framework'),
							'description' => esc_html__('Please, enter logo width (without px)', 'rehub-framework'),
						),	
						array(
							'type' => 'textbox',
							'name' => 'rehub_logo_retina_height',
							'label' => esc_html__('Logo height', 'rehub-framework'),							
							'description' => esc_html__('Please, enter logo height (without px)', 'rehub-framework'),
						),																	
						array(
							'type' => 'textbox',
							'name' => 'rehub_text_logo',
							'label' => esc_html__('Text logo', 'rehub-framework'),							
							'description' => esc_html__('You can type text logo. Use this field only if no image logo', 'rehub-framework'),
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_text_slogan',
							'label' => esc_html__('Slogan', 'rehub-framework'),							
							'description' => esc_html__('You can type slogan below text logo. Use this field only if no image logo', 'rehub-framework'),
						),							
					),
				),
			),
		),
		array(
			'title' => esc_html__('Header and Menu', 'rehub-framework'),
			'name' => 'menu_2',
			'icon' => 'rhicon rhi-wrench ',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Main Header Options', 'rehub-framework'),
					'fields' => array(						
						array(
							'type' => 'select',
							'name' => 'rehub_header_style',
							'label' => esc_html__('Select Header Layout', 'rehub-framework'),
							'description' => esc_html__('Code for code zone can be added in Theme option - Ads and Code zones', 'rehub-framework'),							
							'items' => rehub_get_header_layouts(),
							'default' => array('header_seven'),
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_logo_pad',
							'label' => esc_html__('Set padding from top and bottom (without px)', 'rehub-framework'),
							'description' => esc_html__('This will add custom padding from top and bottom for all custom elements in logo section. Default is 15', 'rehub-framework'),						
						),
						array(
							'type' => 'toggle',
							'name' => 'header_seven_compare_btn',
							'label' => esc_html__('Enable Compare Icon', 'rehub-framework'),
							'default' => '1',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_seven_five',
							),							
						),
						array(
							'type' => 'textbox',
							'name' => 'header_seven_compare_btn_label',
							'label' => esc_html__('Label for compare icon', 'rehub-framework'),	
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_seven',
							),											
						),					
						array(
							'type' => 'toggle',
							'name' => 'header_seven_cart',
							'label' => esc_html__('Enable Cart Icon', 'rehub-framework'),
							'default' => '1',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_seven_five',
							),							
						),	
						array(
							'type' => 'toggle',
							'name' => 'header_seven_cart_as_btn',
							'label' => esc_html__('Enable Cart as button', 'rehub-framework'),
							'default' => '0',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_seven_five',
							),							
						),										
						array(
							'type' => 'toggle',
							'name' => 'header_seven_login',
							'label' => esc_html__('Enable Login Icon', 'rehub-framework'),
							'default' => '0',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_seven',
							),							
						),	
						array(
							'type' => 'toggle',
							'name' => 'header_five_menucenter',
							'label' => esc_html__('Enable centered menu?', 'rehub-framework'),
							'default' => '0',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_five',
							),							
						),
						array(
							'type' => 'textbox',
							'name' => 'header_seven_login_label',
							'label' => esc_html__('Label for login icon', 'rehub-framework'),	
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_seven',
							),											
						),					
						array(
							'type' => 'textbox',
							'name' => 'header_seven_wishlist',
							'label' => esc_html__('Enable Wishlist Icon and set Url', 'rehub-framework'),
							'default' => '',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_seven_five',
							),
							'description' => esc_html__('Set url on your page where you have [rh_get_user_favorites] shortcode. All icons in header will be available also in mobile logo panel. We don\'t recommend to enable more than 2 icons with Mobile logo.', 'rehub-framework'),											
						),					
						array(
							'type' => 'textbox',
							'name' => 'header_seven_wishlist_label',
							'label' => esc_html__('Label for wishlist icon', 'rehub-framework'),	
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_seven',
							),											
						),	
						array(
							'type' => 'textarea',
							'name' => 'header_seven_more_element',
							'label' => esc_html__('Add additional element (shortcodes and html supported)', 'rehub-framework'),
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_seven_five',
							),														
						),																		

						array(
							'type' => 'toggle',
							'name' => 'header_six_login',
							'label' => esc_html__('Enable login/register section', 'rehub-framework'),
							'description' => esc_html__('Also, login popup must be enabled in Theme option - User options', 'rehub-framework'),
							'default' => '0',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_six_five',
							),							
						),					
						array(
							'type' => 'toggle',
							'name' => 'header_six_btn',
							'label' => esc_html__('Enable additional button in header', 'rehub-framework'),
							'description' => esc_html__('This will add button in header', 'rehub-framework'),
							'default' => '0',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_six_five',
							),							
						),	
						array(
							'type' => 'select',
							'name' => 'header_six_btn_color',
							'label' => esc_html__('Choose color style of button', 'rehub-framework'),						
							'description' => esc_html__('You can set theme colors in Theme option - appearance or via Customizer', 'rehub-framework'),	
							'items' => array(
								array(
									'value' => 'btncolor',
									'label' => esc_html__('Main Color of Buttons', 'rehub-framework'),
								),							
								array(
									'value' => 'main',
									'label' => esc_html__('Main Theme Color', 'rehub-framework'),
								),							
								array(
									'value' => 'secondary',
									'label' => esc_html__('Secondary Theme Color', 'rehub-framework'),
								),							
								array(
									'value' => 'green',
									'label' => esc_html__('green', 'rehub-framework'),
								),
								array(
									'value' => 'red',
									'label' => esc_html__('red', 'rehub-framework'),
								),
								array(
									'value' => 'black',
									'label' => esc_html__('black', 'rehub-framework'),
								),
								array(
									'value' => 'gold',
									'label' => esc_html__('gold', 'rehub-framework'),
								),																															
							),
							'default' => array(
								'green',
							),
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_six_five',
							),						
						),						
						array(
							'type' => 'textbox',
							'name' => 'header_six_btn_txt',
							'label' => esc_html__('Type label for button', 'rehub-framework'),
							'default' => 'Submit a deal',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_six_five',
							),														
						),	
						array(
							'type' => 'textbox',
							'name' => 'header_six_btn_url',
							'label' => esc_html__('Type url for button', 'rehub-framework'),
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_six_five',
							),														
						),	
						array(
							'type' => 'toggle',
							'name' => 'header_six_btn_login',
							'label' => esc_html__('Enable login popup for non registered users', 'rehub-framework'),
							'description' => esc_html__('This will open popup if non registered user clicks on button. Also, login popup must be enabled in Theme option - User options', 'rehub-framework'),
							'default' => '0',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_six_five',
							),							
						),	
						array(
							'type' => 'toggle',
							'name' => 'header_six_src',
							'label' => esc_html__('Enable search form in header', 'rehub-framework'),
							'default' => '0',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_six_five',
							),							
						),
						array(
							'type' => 'toggle',
							'name' => 'header_src_icon',
							'label' => esc_html__('Enable search icon in header', 'rehub-framework'),
							'default' => '0',
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_five',
							),							
						),											
						array(
							'type' => 'select',
							'name' => 'header_six_menu',
							'label' => esc_html__('Enable additional menu in logo area', 'rehub-framework'),
							'description' => esc_html__('Use short menu with small number of items!!!', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_menus',
									),
								),
							),
							'dependency' => array(
								'field'    => 'rehub_header_style',
								'function' => 'rehub_framework_is_header_six_five',
							),														
						),																						
						array(
							'type' => 'toggle',
							'name' => 'rehub_sticky_nav',
							'label' => esc_html__('Sticky Menu Bar', 'rehub-framework'),
							'description' => esc_html__('Enable/Disable Sticky navigation bar.', 'rehub-framework'),
							'default' => '0',
						),		
						array(
							'type' => 'upload',
							'name' => 'rehub_logo_sticky_url',
							'label' => esc_html__('Upload Logo for sticky menu', 'rehub-framework'),
							'description' => esc_html__('Upload your logo. Max height is 40px.', 'rehub-framework'),
							'default' => '',
							'dependency' => array(
	                        	'field' => 'rehub_sticky_nav',
	                        	'function' => 'vp_dep_boolean',
	                        ),							
						),															
						array(
							'type' => 'select',
							'name' => 'header_logoline_style',
							'label' => esc_html__('Choose color style of header logo section', 'rehub-framework'),							
							'items' => array(
								array(
									'value' => '0',
									'label' => esc_html__('White style and dark fonts', 'rehub-framework'),
								),
								array(
									'value' => '1',
									'label' => esc_html__('Dark style and white fonts', 'rehub-framework'),
								),
							),
							'default' => array(
								'0',
							),
						),
						array(
							'type' => 'color',
							'name' => 'rehub_header_color_background',
							'label' => esc_html__('Custom Background Color', 'rehub-framework'),
							'description' => esc_html__('Choose the background color or leave blank for default', 'rehub-framework'),
							'format' => 'hex',	
						),
						array(
							'type' => 'upload',
							'name' => 'rehub_header_background_image',
							'label' => esc_html__('Custom Background Image', 'rehub-framework'),
							'description' => esc_html__('Upload a background image or leave blank', 'rehub-framework'),
							'default' => '',
						),
						array(
							'type' => 'select',
							'name' => 'rehub_header_background_repeat',
							'label' => esc_html__('Background Repeat', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'repeat',
									'label' => esc_html__('Repeat', 'rehub-framework'),
								),
								array(
									'value' => 'no-repeat',
									'label' => esc_html__('No Repeat', 'rehub-framework'),
								),
								array(
									'value' => 'repeat-x',
									'label' => esc_html__('Repeat X', 'rehub-framework'),
								),
								array(
									'value' => 'repeat-y',
									'label' => esc_html__('Repeat Y', 'rehub-framework'),
								),
							),
							
						),
						array(
							'type' => 'select',
							'name' => 'rehub_header_background_position',
							'label' => esc_html__('Background Position', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'left',
									'label' => 'Left',
								),
								array(
									'value' => 'center',
									'label' => 'Center',
								),
								array(
									'value' => 'right',
									'label' => 'Right',
								),
							),													
						),																										
					),
				),

				array(
					'type' => 'section',
					'title' => esc_html__('Header main menu Options', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'select',
							'name' => 'header_menuline_style',
							'label' => esc_html__('Choose color style of header menu section', 'rehub-framework'),							
							'items' => array(
								array(
									'value' => '0',
									'label' => esc_html__('White style and dark fonts', 'rehub-framework'),
								),
								array(
									'value' => '1',
									'label' => esc_html__('Dark style and white fonts', 'rehub-framework'),
								),
							),
							'default' => array(
								'0',
							),
						),
						array(
							'type' => 'select',
							'name' => 'header_menuline_type',
							'label' => esc_html__('Choose type of font and padding', 'rehub-framework'),							
							'items' => array(
								array(
									'value' => '0',
									'label' => esc_html__('Middle size and padding', 'rehub-framework'),
								),
								array(
									'value' => '1',
									'label' => esc_html__('Compact size and padding', 'rehub-framework'),
								),							
								array(
									'value' => '2',
									'label' => esc_html__('Big size and padding', 'rehub-framework'),
								),							
							),
							'default' => array(
								'0',
							),
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_nav_font_custom',
							'label' => esc_html__('Add custom font size', 'rehub-framework'),
							'description' => esc_html__('Default is 15. Put just number', 'rehub-framework'),						
						),					
						array(
							'type' => 'toggle',
							'name' => 'rehub_nav_font_upper',
							'label' => esc_html__('Enable uppercase font?', 'rehub-framework'),
							'default' => '0',							
						),	
						array(
							'type' => 'toggle',
							'name' => 'rehub_nav_font_light',
							'label' => esc_html__('Enable Light font weight?', 'rehub-framework'),
							'default' => '1',							
						),	
						array(
							'type' => 'toggle',
							'name' => 'rehub_nav_font_border',
							'label' => esc_html__('Disable border of items?', 'rehub-framework'),
							'default' => '0',							
						),																		
						array(
							'type' => 'toggle',
							'name' => 'rehub_enable_menu_shadow',
							'label' => esc_html__('Menu shadow', 'rehub-framework'),
							'description' => esc_html__('Enable/Disable shadow under menu', 'rehub-framework'),
							'default' => '0',
						),					
						array(
							'type' => 'color',
							'name' => 'rehub_custom_color_nav',
							'label' => esc_html__('Custom color of menu background', 'rehub-framework'),
							'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
							'format' => 'hex',
							
						),	
						 array(
							'type' => 'color',
							'name' => 'rehub_custom_color_nav_font',
							'label' => esc_html__('Custom color of menu font', 'rehub-framework'),
							'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
							'format' => 'hex',							
						),
					),
				),

				array(
					'type' => 'section',
					'title' => esc_html__('Search', 'rehub-framework'),
					'fields' => array(				
						array(
							'type' => 'toggle',
							'name' => 'rehub_ajax_search',
							'label' => esc_html__('Add ajax search for header search', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'multiselect',
							'name' => 'rehub_search_ptypes',
							'label' => esc_html__('Choose custom post type for search', 'rehub-framework'),
							'description' => esc_html__('By default search form shows post and pages. You can change this here. Multiple post types are supported only for ajax search', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value'  => 'rehub_get_cpost_type',
									),
								),
							),
							'default' => '',			
						),							


					),
				),			

				array(
					'type' => 'section',
					'title' => esc_html__('Header top line Options', 'rehub-framework'),
					'fields' => array(	
						array(
							'type' => 'toggle',
							'name' => 'rehub_header_top_enable',
							'label' => esc_html__('Enable top line', 'rehub-framework'),
							'default' => '0',
						),									
						array(
							'type' => 'select',
							'name' => 'header_topline_style',
							'label' => esc_html__('Choose color style of header top line', 'rehub-framework'),							
							'items' => array(
								array(
									'value' => '0',
									'label' => esc_html__('White style and dark fonts', 'rehub-framework'),
								),
								array(
									'value' => '1',
									'label' => esc_html__('Dark style and white fonts', 'rehub-framework'),
								),
							),
							'default' => array(
								'0',
							),
						),
						 array(
							'type' => 'color',
							'name' => 'rehub_custom_color_top',
							'label' => esc_html__('Custom color for top line of header', 'rehub-framework'),
							'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
							'format' => 'hex',
							
						),	
						 array(
							'type' => 'color',
							'name' => 'rehub_custom_color_top_font',
							'label' => esc_html__('Custom color of menu font for top line of header', 'rehub-framework'),
							'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
							'format' => 'hex',				
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_top_line_content',
							'label' => esc_html__('Add custom content to top line', 'rehub-framework'),
						),																					
					),
				),											
			),
		),
		array(
			'title' => esc_html__('Footer Options', 'rehub-framework'),
			'name' => 'menu_3',
			'icon' => 'rhicon rhi-caret-square-down',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Custom footer templates', 'rehub-framework'),
					'fields' => array(						
						array(
							'type' => 'select',
							'name' => 'footer_template',
							'label' => esc_html__('Select Footer Layout', 'rehub-framework'),
							'description' => esc_html__('You can create them in Reusable template section', 'rehub-framework'),							
							'items' => rehub_get_footer_layouts(),
						),
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Footer options', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'rehub_footer_widgets',
							'label' => esc_html__('Footer Widgets', 'rehub-framework'),
							'description' => esc_html__('Enable or Disable the footer widget area', 'rehub-framework'),
							'default' => '1',
						),
						array(
							'type' => 'select',
							'name' => 'footer_style',
							'label' => esc_html__('Choose color style of footer widget section', 'rehub-framework'),							
							'items' => array(
								array(
									'value' => '0',
									'label' => esc_html__('Dark style and white fonts', 'rehub-framework'),
								),
								array(
									'value' => '1',
									'label' => esc_html__('White style and dark fonts', 'rehub-framework'),
								),
							),
							'default' => array(
								'0',
							),
						),
						array(
							'type' => 'color',
							'name' => 'footer_color_background',
							'label' => esc_html__('Custom Background Color', 'rehub-framework'),
							'description' => esc_html__('Choose the background color or leave blank for default', 'rehub-framework'),
							'format' => 'hex',	
						),
						array(
							'type' => 'upload',
							'name' => 'footer_background_image',
							'label' => esc_html__('Custom Background Image', 'rehub-framework'),
							'description' => esc_html__('Upload a background image or leave blank', 'rehub-framework'),
							'default' => '',
							
						),
						array(
							'type' => 'select',
							'name' => 'footer_background_repeat',
							'label' => esc_html__('Background Repeat', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'repeat',
									'label' => esc_html__('Repeat', 'rehub-framework'),
								),
								array(
									'value' => 'no-repeat',
									'label' => esc_html__('No Repeat', 'rehub-framework'),
								),
								array(
									'value' => 'repeat-x',
									'label' => esc_html__('Repeat X', 'rehub-framework'),
								),
								array(
									'value' => 'repeat-y',
									'label' => esc_html__('Repeat Y', 'rehub-framework'),
								),
							),
							
						),
						array(
							'type' => 'select',
							'name' => 'footer_background_position',
							'label' => esc_html__('Background Position', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'left',
									'label' => 'Left',
								),
								array(
									'value' => 'center',
									'label' => 'Center',
								),
								array(
									'value' => 'right',
									'label' => 'Right',
								),
							),													
						),	
						array(
							'type' => 'select',
							'name' => 'footer_style_bottom',
							'label' => esc_html__('Choose color style of bottom section', 'rehub-framework'),							
							'items' => array(
								array(
									'value' => '0',
									'label' => esc_html__('Dark style and white fonts', 'rehub-framework'),
								),
								array(
									'value' => '1',
									'label' => esc_html__('White style and dark fonts', 'rehub-framework'),
								),
							),
							'default' => array(
								'0',
							),
						),						
						array(
							'type' => 'textarea',
							'name' => 'rehub_footer_text',
							'label' => esc_html__('Footer Bottom Text', 'rehub-framework'),
							'description' => esc_html__('Enter your copyright text or whatever you want right here.', 'rehub-framework'),
							'default' => '',
						),
						array(
							'type' => 'upload',
							'name' => 'rehub_footer_logo',
							'label' => esc_html__('Upload Logo for footer', 'rehub-framework'),
							'description' => esc_html__('Upload your logo for footer.', 'rehub-framework'),
							'default' => '',
						),																
					),
				),
			),
		),
		array(
			'title' => esc_html__('Mobile & AMP', 'rehub-framework'),
			'name' => 'menu_mobile',
			'icon' => 'rhicon rhi-mobile',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('General', 'rehub-framework'),
					'fields' => array(	
						array(
							'type' => 'upload',
							'name' => 'rehub_logo_inmenu_url',
							'label' => esc_html__('Upload Logo for mobiles', 'rehub-framework'),
							'description' => esc_html__('Upload your logo. Max height is 40px. By default, your main logo will be used', 'rehub-framework'),
							'default' => '',							
						),	
						array(
							'type' => 'color',
							'name' => 'rehub_mobile_header_bg',
							'label' => esc_html__('Mobile header background', 'rehub-framework'),
							'description' => esc_html__('Leave blank to use colors of menu', 'rehub-framework'),
							'format' => 'hex',
							
						),	
						 array(
							'type' => 'color',
							'name' => 'rehub_mobile_header_color',
							'label' => esc_html__('Mobile header link color', 'rehub-framework'),
							'description' => esc_html__('Leave blank to use colors of menu', 'rehub-framework'),
							'format' => 'hex',							
						),
						array(
							'type' => 'color',
							'name' => 'rehub_mobtool_bg',
							'label' => esc_html__('Mobile Toolbar background', 'rehub-framework'),
							'description' => esc_html__('Toolbar is visible if you have more than 2 additional icons in header', 'rehub-framework'),
							'format' => 'hex',
							
						),	
						 array(
							'type' => 'color',
							'name' => 'rehub_mobtool_color',
							'label' => esc_html__('Mobile Toolbar link color', 'rehub-framework'),
							'format' => 'hex',							
						),
						array(
							'type' => 'toggle',
							'name' => 'rehub_mobtool_top',
							'label' => esc_html__('Set mobile toolbar to top', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'rehub_mobtool_force',
							'label' => esc_html__('Force mobile toolbar', 'rehub-framework'),
							'description' => esc_html__('By default, icon toolbar is generated if you have 3 elements or more in header, but you can enable this option to force it', 'rehub-framework'),
							'default' => '0',
						),					
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Mobile Sliding panel', 'rehub-framework'),
					'fields' => array(	
						array(
							'type' => 'upload',
							'name' => 'logo_mobilesliding',
							'label' => esc_html__('Enable logo in sliding mobile panel', 'rehub-framework'),			
						),	
						array(
							'type' => 'color',
							'name' => 'color_mobilesliding',
							'label' => esc_html__('Background color under logo in Sliding panel', 'rehub-framework'),	
							'format' => 'hex',							
						),						
						array(
							'type' => 'textarea',
							'name' => 'text_mobilesliding',
							'label' => esc_html__('Add custom element or shortcode', 'rehub-framework'),
						),														
					),
				),			
				array(
					'type' => 'section',
					'title' => esc_html__('AMP', 'rehub-framework'),
					'fields' => array(
						 array(
							'type' => 'notebox',
							'name' => 'rehub_single_before_post_note',
							'label' => esc_html__('Note', 'rehub-framework'),
							'description' => esc_html__('Read about setup for', 'rehub-framework').' <a href="https://wpsoul.com/amp-wordpress-setup/" target="_blank">AMP,</a> <a href="https://wpsoul.com/create-mobile-app-wordpress/" target="_blank">mobile App</a>',
							'status' => 'info',
						),		
						array(
							'type' => 'upload',
							'name' => 'rehub_logo_amp',
							'label' => esc_html__('Load logo for AMP version', 'rehub-framework'),
							'description' => esc_html__('Recommended size is 190*36', 'rehub-framework'),
							'default' => '',
						),										
						array(
							'type' => 'textarea',
							'name' => 'amp_custom_in_header_top',
							'label' => esc_html__('Before Title', 'rehub-framework'),
						),														
						array(
							'type' => 'textarea',
							'name' => 'amp_custom_in_header',
							'label' => esc_html__('Before content', 'rehub-framework'),
						),		
						array(
							'type' => 'textarea',
							'name' => 'amp_custom_in_footer',
							'label' => esc_html__('After content', 'rehub-framework'),
						),
						array(
							'type' => 'textarea',
							'name' => 'amp_custom_in_head_section',
							'label' => esc_html__('Header section', 'rehub-framework'),
							'description'=> esc_html__('Insert custom code for head section before closed HEAD tag', 'rehub-framework'),						
						),
						array(
							'type' => 'textarea',
							'name' => 'amp_custom_in_footer_section',
							'label' => esc_html__('Footer section', 'rehub-framework'),
							'description'=> esc_html__('Insert custom code for footer section, before closed BODY tag', 'rehub-framework'),
						),	
						array(
							'type' => 'toggle',
							'name' => 'amp_default_css_disable',
							'label' => esc_html__('Disable default amp styles of theme. Disable this only if you have custom plugin for AMP', 'rehub-framework'),
							'default' => '0',
						),										
						array(
							'type' => 'textarea',
							'name' => 'amp_custom_css',
							'label' => esc_html__('Custom css', 'rehub-framework'),
						),																													
					),
				),			
			),
		),	
		array(
			'title' => esc_html__('Loop customization', 'rehub-framework'),
			'name' => 'menu_loop',
			'icon' => 'rhicon rhi-expand-alt',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Woocommerce Loop', 'rehub-framework'),
					'fields' => array(							
						array(
							'type' => 'toggle',
							'name' => 'woo_btn_disable',
							'label' => esc_html__('Disable button in ALL product loops?', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'toggle',
							'name' => 'woo_compact_loop_btn',
							'label' => esc_html__('Enable button in compact grid and directory grid', 'rehub-framework'),
							'description' => esc_html__('Will not work if you disable buttons in previous field', 'rehub-framework'),						
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'woo_wholesale',
							'label' => esc_html__('Enable quantity near button for regular grid', 'rehub-framework'),
							'description' => esc_html__('This will also disable sliding cart in Market grid layout. Enable also ajax add to cart option in Woocommerce - settings - products', 'rehub-framework'),						
							'default' => '0',
						),	
						array(
							'type' => 'select',
							'name' => 'price_meta_woogrid',
							'label' => esc_html__('Show in price area of deal grid layouts', 'rehub-framework'),
							'items' => array(
								array(
									'value' => '1',
									'label' => esc_html__('Content Egg synchronized offer', 'rehub-framework'),
								),	
								array(
									'value' => '2',
									'label' => esc_html__('Brand logo', 'rehub-framework'),
								),	
								array(
									'value' => '3',
									'label' => esc_html__('Discount', 'rehub-framework'),
								),	
								array(
									'value' => '4',
									'label' => esc_html__('Nothing', 'rehub-framework'),
								),																				
							),
							'default' => array(
								'2',
							),
						),														
						array(
							'type' => 'toggle',
							'name' => 'woo_aff_btn',
							'label' => esc_html__('Enable affiliate links instead inner?', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'color',
							'name' => 'wooloop_heading_color',
							'label' => esc_html__('Headings color', 'rehub-framework'),
							'format' => 'hex',							
						),
						array(
					        'type' => 'textbox',
					        'name' => 'wooloop_heading_size',
					        'label' => esc_html__('Heading Font size', 'rehub-framework'),
					        'default' => '',
					        'validation' => 'numeric',
						),					
						array(
							'type' => 'color',
							'name' => 'wooloop_price_color',
							'label' => esc_html__('Price color', 'rehub-framework'),
							'format' => 'hex',							
						),
						array(
					        'type' => 'textbox',
					        'name' => 'wooloop_price_size',
					        'label' => esc_html__('Price Font size', 'rehub-framework'),
					        'default' => '',
					        'validation' => 'numeric',
						),					
						array(
							'type' => 'color',
							'name' => 'wooloop_sale_color',
							'label' => esc_html__('Sale tag color', 'rehub-framework'),
							'format' => 'hex',							
						),																																
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Post Loop', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'disable_btn_offer_loop',
							'label' => esc_html__('Disable offer button in ALL loops?', 'rehub-framework'),
							'default' => '0',
						),											
						array(
							'type' => 'toggle',
							'name' => 'rehub_enable_btn_recash',
							'label' => esc_html__('Enable button in deal grid layout?', 'rehub-framework'),	
							'description' => esc_html__('Will not work if you disable buttons in previous field', 'rehub-framework'),							
							'default' => 0,							
						),		
						array(
							'type' => 'toggle',
							'name' => 'disable_grid_actions',
							'label' => esc_html__('Disable comment and thumbs in deal grid layout?', 'rehub-framework'),		
							'default' => 0,							
						),						
						array(
							'type' => 'select',
							'name' => 'price_meta_grid',
							'label' => esc_html__('Show in price area of deal grid', 'rehub-framework'),
							'items' => array(
								array(
									'value' => '1',
									'label' => esc_html__('User logo + Price', 'rehub-framework'),
								),	
								array(
									'value' => '2',
									'label' => esc_html__('Brand logo + Price', 'rehub-framework'),
								),	
								array(
									'value' => '3',
									'label' => esc_html__('Only Price', 'rehub-framework'),
								),	
								array(
									'value' => '4',
									'label' => esc_html__('Nothing', 'rehub-framework'),
								),																				
							),
							'default' => array(
								'1',
							),
						),
						array(
							'type' => 'toggle',
							'name' => 'disable_inner_links',
							'label' => esc_html__('Enable affiliate links instead inner?', 'rehub-framework'),		
							'default' => 0,							
						),																		
						array(
							'type' => 'toggle',
							'name' => 'rehub_enable_expand',
							'label' => esc_html__('Enable expand button in list layout?', 'rehub-framework'),
					        'description' => esc_html__('Sometimes can be buggy', 'rehub-framework'),							
							'default' => 0,							
						),	
						array(
					        'type' => 'slider',
					        'name' => 'hot_max',
					        'label' => esc_html__('Hottest value', 'rehub-framework'),
					        'description' => esc_html__('After hot metter reach this value, scale will have hot image and 100 percent fill + will be used in hottest filter', 'rehub-framework'),
					        'min' => '5',
					        'max' => '500',
					        'step' => '5',
					        'default' => '10',
						),
						array(
					        'type' => 'slider',
					        'name' => 'hot_min',
					        'label' => esc_html__('Coldest value', 'rehub-framework'),
					        'description' => esc_html__('After hot metter reach this value, scale will have cold image and 100 percent fill of cold', 'rehub-framework'),
					        'min' => '-500',
					        'max' => '-10',
					        'step' => '5',
					        'default' => '-10',
						),																					
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Other', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'textbox',
							'name' => 'featured_fallback_img',
							'label' => esc_html__('Url to custom fallback image for Featured Images', 'rehub-framework'),	
						),															
					),
				),						
			),
		),
		array(
			'title' => esc_html__('Shop settings', 'rehub-framework'),
			'name' => 'menu_woo',
			'icon' => 'rhicon rhi-params',
			'controls' => array(				
				array(
					'type' => 'section',
					'title' => esc_html__('General settings', 'rehub-framework'),
					'fields' => array(				
						array(
							'type' => 'select',
							'name' => 'woo_columns',
							'label' => esc_html__('How to show archives', 'rehub-framework'),
							'default' => '3_col',
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value'  => 'rehub_get_productarchive_layout_array',
									),
								),
							),
							'description' => esc_html__('Use 5 columns only in Extended Width Layout (Theme option - General - Width Style) and 30 products in loop', 'rehub-framework'),		
						),	
						array(
							'type' => 'toggle',
							'name' => 'rehub_sidebar_left_shop',
							'label' => esc_html__('Set sidebar to left side?', 'rehub-framework'),
							'default' => '0',
						),											
						array(
							'type' => 'select',
							'name' => 'woo_design',
							'label' => esc_html__('Set design of woo archive', 'rehub-framework'),
							'items' => array(
								array(
								'value' => 'simple',
								'label' => esc_html__('Columns', 'rehub-framework'),
								),
								array(
								'value' => 'grid',
								'label' => esc_html__('Grid', 'rehub-framework'),
								),
								array(
									'value' => 'gridmart',
									'label' => esc_html__('Market Grid', 'rehub-framework'),
								),
								array(
								'value' => 'gridtwo',
								'label' => esc_html__('Compact Grid', 'rehub-framework'),
								),	
								array(
								'value' => 'gridrev',
								'label' => esc_html__('Directory Grid', 'rehub-framework'),
								),	
								array(
								'value' => 'griddigi',
								'label' => esc_html__('Digital Grid', 'rehub-framework'),
								),	
								array(
								'value' => 'dealwhite',
								'label' => esc_html__('Deal Grid', 'rehub-framework'),
								),	
								array(
								'value' => 'dealdark',
								'label' => esc_html__('Deal Grid Dark', 'rehub-framework'),
								),													
								array(
								'value' => 'list',
								'label' => esc_html__('List', 'rehub-framework'),
								),	
								array(
								'value' => 'deallist',
								'label' => esc_html__('Deal List', 'rehub-framework'),
								),	
								array(
									'value' => 'compactlist',
									'label' => esc_html__('Wholesale List', 'rehub-framework'),
								),												
							),
							'default' => 'simple',
						),
						array(
							'type' => 'select',
							'name' => 'woo_number',
							'label' => esc_html__('Set count of products in loop', 'rehub-framework'),
							'items' => array(
								array(
								'value' => '12',
								'label' => '12',
								),
								array(
								'value' => '16',
								'label' => '16',
								),	
								array(
								'value' => '20',
								'label' => '20',
								),
								array(
								'value' => '24',
								'label' => '24',
								),
								array(
								'value' => '30',
								'label' => '30',
								),	
								array(
									'value' => '36',
									'label' => '36',
								),																					
							),
							'default' => '12',
						),	
						array(
							'type' => 'select',
							'name' => 'product_layout_style',
							'label' => esc_html__('Product layout', 'rehub-framework'),
							'default' => 'normal_post',
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value'  => 'rehub_get_product_layout_array',
									),
								),
							),
							'default' => array(
								'default_full_width',
							),
						),																				
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Custom Code Areas', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'textarea',
							'name' => 'woo_code_zone_button',
							'label' => esc_html__('After Button Area', 'rehub-framework'),
							'description' => esc_html__('This code zone is visible on all products after Add to cart Button', 'rehub-framework'),
							'default' => '',
						),
						array(
							'type' => 'textarea',
							'name' => 'woo_code_zone_content',
							'label' => esc_html__('Before Content', 'rehub-framework'),
							'description' => esc_html__('This code zone is visible on all products before Content', 'rehub-framework'),
							'default' => '',
						),	
						array(
							'type' => 'textarea',
							'name' => 'woo_code_zone_footer',
							'label' => esc_html__('Additional zone', 'rehub-framework'),
							'description' => esc_html__('This code zone is visible on all products and place of rendering is depending on product layout', 'rehub-framework'),
							'default' => '',
						),	
						array(
							'type' => 'textarea',
							'name' => 'woo_code_zone_float',
							'label' => esc_html__('In floating panel', 'rehub-framework'),
							'default' => '',
						),					
						array(
							'type' => 'textarea',
							'name' => 'woo_code_zone_loop',
							'label' => esc_html__('Code zone inside product loop', 'rehub-framework'),
							'description' => esc_html__('This code zone is visible on shop pages inside each product item.', 'rehub-framework').' <a href="https://wpsoul.com/make-smart-profitable-deal-affiliate-comparison-site-woocommerce/#featured-attributes-area-in-product-grid">Read more about code zones</a>',
							'default' => '',
						),	
						array(
							'type' => 'textarea',
							'name' => 'rh_woo_shop_global',
							'label' => esc_html__('Code zone on Shop archives', 'rehub-framework'),
							'default' => '',
						),																			
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Enable/Disable', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'woo_btn_inner_disable',
							'label' => esc_html__('Disable button inside Product page', 'rehub-framework'),
							'default' => '0',
						),					
						array(
							'type' => 'toggle',
							'name' => 'disable_woo_scripts',
							'label' => esc_html__('Disable Woocommerce Cart scripts', 'rehub-framework'),
							'description' => esc_html__('This will disable All Cart scripts of woocommerce. Use this only when you use woocommerce for affiliate site without cart', 'rehub-framework'),
							'default' => '0',
						),																			
						array(
							'type' => 'toggle',
							'name' => 'woo_enable_share',
							'label' => esc_html__('Enable share buttons on product page?', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'woo_quick_view',
							'label' => esc_html__('Enable quick view', 'rehub-framework'),
							'default' => '0',
						),																							
					),
				),	
				array(
					'type' => 'section',
					'title' => esc_html__('Synchronizations', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'wooregister_xprofile',
							'label' => esc_html__('Add xprofile fields to register form?', 'rehub-framework'),
							'description' => esc_html__('Set additional fields in User - Profile fields. Works only with enabled Buddypress', 'rehub-framework'),
							'default' => '0',
						),						
						array(
							'type' => 'toggle',
							'name' => 'post_sync_with_user_location',
							'label' => esc_html__('Synchronize product and user location?', 'rehub-framework'),
							'description' => esc_html__('This works for Geo My wordpress plugin. If user has location and adds a product, product will have also his location automatically', 'rehub-framework'),
							'default' => '0',
						),													
					),
				),								
			),
		),
		array(
			'title' => esc_html__('Affiliate and Seo', 'rehub-framework'),
			'name' => 'menu_aff',
			'icon' => 'rhicon rhi-money',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Content Egg synchronization', 'rehub-framework'),
					'fields' => array(					
						array(
							'type' => 'multiselect',
							'name' => 'save_meta_for_ce',
							'label' => esc_html__('Save data from Content Egg to post offer section', 'rehub-framework'),
							'description' => esc_html__('This option will store data from Content Egg modules to main offer of post. Works only with enabled Content Egg plugin', 'rehub-framework'),	
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'get_ce_modules_id_for_sinc',
									),
								),
							),
							'default' => '',
						),
						array(
							'type' => 'textbox',
							'name' => 'ce_custom_currency',
							'label' => esc_html__('Custom currency', 'rehub-framework'),
							'description' => esc_html__('Use this if you want to convert all prices of Content Egg into your currency. Currency in ISO 4217. Example: USD or EUR', 'rehub-framework'),							
						),																						
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('CashBack Options', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'enable_user_sub_id',
							'label' => esc_html__('Add user info as sub ID to links', 'rehub-framework'),
							'default' => '0',							
						),	
						array(
							'type' => 'select',
							'name' => 'sub_id_show',
							'label' => esc_html__('Which info you want to use', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'id',
									'label' => esc_html__('User ID', 'rehub-framework'),
								),
								array(
									'value' => 'name',
									'label' => esc_html__('User login name', 'rehub-framework'),
								),																
								array(
									'value' => 'author',
									'label' => esc_html__('Login name of author of post', 'rehub-framework'),
								),
								array(
									'value' => 'authorid',
									'label' => esc_html__('ID of author of post', 'rehub-framework'),
								),																		
							),						
						),						
						array(
							'type' => 'textarea',
							'name' => 'custom_sub_id',
							'label' => esc_html__('Set custom url parameter for sub ID', 'rehub-framework'),
							'description' => esc_html__('default is subid= Make sure that you added symbol = or other which is used in your network for parameters. If you have several networks, add them from separate line. Example is next - amazon.com@subid=, where amazon.com is domain of link and subid is url parameter. Default subid which will be triggered for all other links can be added in last line without domain. If you want to exclude some domain, add them like domain.com@exclude, this will not add subid parameters to them', 'rehub-framework'),
							'dependency' => array(
	                        	'field' => 'enable_user_sub_id',
	                        	'function' => 'vp_dep_boolean',
	                        ),												
						),	
						array(
							'type' => 'textbox',
							'name' => 'cashback_points',
							'label' => esc_html__('Set key of Mycred points for cashback', 'rehub-framework'),
							'description' => esc_html__('Set custom point key where you store approved cashback points', 'rehub-framework'),												
						),
						array(
							'type' => 'textbox',
							'name' => 'cashback_pending_points',
							'label' => esc_html__('Set key of Mycred points for pending cashback', 'rehub-framework'),
						),
						array(
							'type' => 'textbox',
							'name' => 'cashback_declined_points',
							'label' => esc_html__('Set key of Mycred points for declined cashback', 'rehub-framework'),
						),																	
					),
				),				
				array(
					'type' => 'section',
					'title' => esc_html__('Other', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'rehub_post_exclude_expired',
							'label' => esc_html__('Hide all expired offers', 'rehub-framework'),
							'description' => esc_html__('This will hide expired offers for archives', 'rehub-framework'),
							'default' => '0',							
						),	
						array(
							'type' => 'toggle',
							'name' => 'enable_title_shortcode',
							'label' => esc_html__('Enable shortcodes in title', 'rehub-framework'),
							'description' => esc_html__('You can use date shortcode to generate current date. Example: [wpsm_custom_meta type=date field=month] or [wpsm_custom_meta type=date field=year]', 'rehub-framework'),
							'default' => '0',							
						),									
						array(
							'type' => 'toggle',
							'name' => 'enable_brand_taxonomy',
							'label' => esc_html__('Enable Affiliate Store taxonomy for posts', 'rehub-framework'),
							'description' => esc_html__('When enabled, save permalinks in Settings - Permalinks', 'rehub-framework'),			
							'default' => '0',							
						),
						array(
							'type' => 'select',
							'name' => 'brand_taxonomy_layout',
							'label' => esc_html__('Select Affiliate Store Layout', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'compact_list',
									'label' => esc_html__('Deal list', 'rehub-framework'),
								),								
								array(
									'value' => 'regular_list',
									'label' => esc_html__('Regular List Layout', 'rehub-framework'),
								),	
								array(
									'value' => 'deal_grid',
									'label' => esc_html__('Deal grid', 'rehub-framework'),
								),
								array(
									'value' => 'mobilegrid',
									'label' => esc_html__('Mobile Optimized Deal Grid layout', 'rehub-framework'),
								),
								array(
									'value' => 'regular_grid',
									'label' => esc_html__('Regular grid', 'rehub-framework'),
								),
								array(
									'value' => 'compact_grid',
									'label' => esc_html__('Compact grid', 'rehub-framework'),
								),																														
							),
							'default' => array(
								'compact_list',
							),
							'dependency' => array(
	                        	'field' => 'enable_brand_taxonomy',
	                        	'function' => 'vp_dep_boolean',
	                        ),						
						),						
						array(
							'type' => 'textbox',
							'name' => 'rehub_deal_store_tag',
							'label' => esc_html__('Set custom link slug for Affiliate Store. Update permalinks after this in Settings - permalinks', 'rehub-framework'),							
						),				
					),
				),						
			),
		),
		array(
			'title' => esc_html__('Fonts Options', 'rehub-framework'),
			'name' => 'menu_7',
			'icon' => 'rhicon rhi-font',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('General', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'disable_google_fonts',
							'label' => esc_html__('Enable Inner Storage of Google Fonts', 'rehub-framework'),
							'description' => 'Read how to use Inner Storage for Google Fonts <a href="http://rehubdocs.wpsoul.com/docs/rehub-framework/how-to/local-google-fonts-for-gdpr/" target="_blank">in tutorial</a>',
							'default' => '0',
						),																
					),
				),				

				array(
					'type' => 'section',
					'title' => esc_html__('Navigation Font', 'rehub-framework'),
					'fields' => array(						
						array(
							'type' => 'select',
							'name' => 'rehub_nav_font',
							'label' => esc_html__('Navigation Font Family', 'rehub-framework'),
							'description' => esc_html__('Font for navigation', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_gwf_family',
									),
								),
							),
						),
						array(
							'type' => 'radiobutton',
							'name' => 'rehub_nav_font_style',
							'label' => esc_html__('Font Style', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_nav_font',
										'value' => 'vp_get_gwf_style',
									),
								),
							),
							'default' => array(
								'{{first}}',
							),							
						),
						array(
							'type' => 'radiobutton',
							'name' => 'rehub_nav_font_weight',
							'label' => esc_html__('Font Weight', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_nav_font',
										'value' => 'vp_get_gwf_weight',
									),
								),
							),
						),
						array(
							'type' => 'multiselect',
							'name' => 'rehub_nav_font_subset',
							'label' => esc_html__('Font Subset', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_nav_font',
										'value' => 'vp_get_gwf_subset',
									),
								),
							),
							'default' => 'latin',
						),												
					),
				),//END NAV FONT

				array(
					'type' => 'section',
					'title' => esc_html__('Headings Font', 'rehub-framework'),
					'fields' => array(						
						array(
							'type' => 'select',
							'name' => 'rehub_headings_font',
							'label' => esc_html__('Headings Font Family', 'rehub-framework'),
							'description' => esc_html__('Font for headings in text, sidebar, footer', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_gwf_family',
									),
								),
							),
						),
						array(
							'type' => 'radiobutton',
							'name' => 'rehub_headings_font_style',
							'label' => esc_html__('Font Style', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_headings_font',
										'value' => 'vp_get_gwf_style',
									),
								),
							),
							'default' => array(
								'{{first}}',
							),							
						),
						array(
							'type' => 'radiobutton',
							'name' => 'rehub_headings_font_weight',
							'label' => esc_html__('Font Weight', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_headings_font',
										'value' => 'vp_get_gwf_weight',
									),
								),
							),
						),
						array(
							'type' => 'multiselect',
							'name' => 'rehub_headings_font_subset',
							'label' => esc_html__('Font Subset', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_headings_font',
										'value' => 'vp_get_gwf_subset',
									),
								),
							),
							'default' => 'latin',
						),
						array(
							'type' => 'toggle',
							'name' => 'rehub_headings_font_upper',
							'label' => esc_html__('Enable uppercase?', 'rehub-framework'),
							'default' => '0',							
						),												
					),
				),//END Headings FONT

				array(
					'type' => 'section',
					'title' => esc_html__('Button Font', 'rehub-framework'),
					'fields' => array(						
						array(
							'type' => 'select',
							'name' => 'rehub_btn_font',
							'label' => esc_html__('Button Font Family', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_gwf_family',
									),
								),
							),
						),
						array(
							'type' => 'radiobutton',
							'name' => 'rehub_btn_font_style',
							'label' => esc_html__('Font Style', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_btn_font',
										'value' => 'vp_get_gwf_style',
									),
								),
							),
							'default' => array(
								'{{first}}',
							),							
						),
						array(
							'type' => 'radiobutton',
							'name' => 'rehub_btn_font_weight',
							'label' => esc_html__('Font Weight', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_btn_font',
										'value' => 'vp_get_gwf_weight',
									),
								),
							),
						),
						array(
							'type' => 'multiselect',
							'name' => 'rehub_btn_font_subset',
							'label' => esc_html__('Font Subset', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_btn_font',
										'value' => 'vp_get_gwf_subset',
									),
								),
							),
							'default' => 'latin',
						),
						array(
							'type' => 'toggle',
							'name' => 'rehub_btn_font_upper_dis',
							'label' => esc_html__('Disable uppercase?', 'rehub-framework'),
							'default' => '0',							
						),												
					),
				),//END BTN FONT

				array(
					'type' => 'section',
					'title' => esc_html__('Body Font', 'rehub-framework'),
					'fields' => array(						
						array(
							'type' => 'select',
							'name' => 'rehub_body_font',
							'label' => esc_html__('Body Font Family', 'rehub-framework'),
							'description' => esc_html__('Font for body text', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_gwf_family',
									),
								),
							),
						),
						array(
							'type' => 'radiobutton',
							'name' => 'rehub_body_font_style',
							'label' => esc_html__('Font Style', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_body_font',
										'value' => 'vp_get_gwf_style',
									),
								),
							),
							'default' => array(
								'{{first}}',
							),							
						),
						array(
							'type' => 'radiobutton',
							'name' => 'rehub_body_font_weight',
							'label' => esc_html__('Font Weight', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_body_font',
										'value' => 'vp_get_gwf_weight',
									),
								),
							),
						),
						array(
							'type' => 'multiselect',
							'name' => 'rehub_body_font_subset',
							'label' => esc_html__('Font Subset', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'binding',
										'field' => 'rehub_body_font',
										'value' => 'vp_get_gwf_subset',
									),
								),
							),
							'default' => 'latin',
						),	
						array(
							'type' => 'textbox',
							'name' => 'body_font_size',
							'label' => esc_html__('Set body font size', 'rehub-framework'),
							'description' => esc_html__('Set font size in px. If you want to add also line height, add it after symbol ":". Example, 20:24, where 20px is font size, 24px is line height', 'rehub-framework'),
						),											
					),
				),//END Body FONT


			),
		),
		array(
			'title' => esc_html__('Global Enable/Disable', 'rehub-framework'),
			'name' => 'menu_8',
			'icon' => 'rhicon rhi-globe',
			'controls' => array(		
				array(
					'type' => 'section',
					'title' => esc_html__('Global options', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'rh_image_resize',
							'label' => esc_html__('Disable resize for Featured images', 'rehub-framework'),
							'description' => esc_html__('Will be used 100% original image. Can slow down a site.', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'enable_lazy_images',
							'label' => esc_html__('Enable lazyload script on thumbnails', 'rehub-framework'),
							'description' => esc_html__('For better image perfomance. Sometimes can be buggy with other scripts', 'rehub-framework'),
							'default' => '1',
						),																				
						array(
							'type' => 'toggle',
							'name' => 'exclude_author_meta',
							'label' => esc_html__('Disable author link', 'rehub-framework'),
							'description' => esc_html__('Disable author link from meta in string', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'toggle',
							'name' => 'exclude_cat_meta',
							'label' => esc_html__('Disable category link', 'rehub-framework'),
							'description' => esc_html__('Disable category link from meta in string', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'exclude_date_meta',
							'label' => esc_html__('Disable date', 'rehub-framework'),
							'description' => esc_html__('Disable date from meta in string', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'toggle',
							'name' => 'exclude_comments_meta',
							'label' => esc_html__('Disable comments count', 'rehub-framework'),
							'description' => esc_html__('Disable comments count from meta in string', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'hotmeter_disable',
							'label' => esc_html__('Disable hot and thumb metter', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'toggle',
							'name' => 'wishlist_disable',
							'label' => esc_html__('Disable wishlist', 'rehub-framework'),
							'default' => '0',
						),					
						array(
							'type' => 'select',
							'name' => 'wishlistpage',
							'label' => esc_html__('Select page for Wishlist', 'rehub-framework'),
							'description' => esc_html__('By default, second click on heart icon will remove item from wishlist. If you set page here, such click will redirect user to wishlist page. Page must have shortcode [rh_get_user_favorites]', 'rehub-framework'),				
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_pages',
									),
								),
							),													
						),
						array(
							'type' => 'toggle',
							'name' => 'wish_cache_enabled',
							'label' => esc_html__('Wishlist Button Support for Cache plugins', 'rehub-framework'),
							'default' => '0',
						),										
						array(
							'type' => 'toggle',
							'name' => 'thumb_only_users',
							'label' => esc_html__('Allow to use hot and thumbs only for logged users', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'toggle',
							'name' => 'wish_only_users',
							'label' => esc_html__('Allow to use wishlist only for logged users', 'rehub-framework'),
							'default' => '0',
						),										
						array(
							'type' => 'toggle',
							'name' => 'post_view_disable',
							'label' => esc_html__('Disable post view script', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'date_publish',
							'label' => esc_html__('Enable to show Date of publishing as date meta', 'rehub-framework'),
							'default' => '0',
						),	
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Global disabling parts on single pages', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'multiselect',
							'name' => 'rehub_ptype_formeta',
							'label' => esc_html__('Duplicate Post Meta boxes', 'rehub-framework'),
							'description' => esc_html__('You can enable Post offer, Post format and Post Thumbnails meta panels for other several post types here (By default, only in Posts)', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value'  => 'rehub_get_cpost_type',
									),
								),
							),
							'default' => '',			
						),																
						array(
							'type' => 'toggle',
							'name' => 'rehub_disable_breadcrumbs',
							'label' => esc_html__('Disable breadcrumbs', 'rehub-framework'),
							'description' => esc_html__('Disable breadcrumbs from pages', 'rehub-framework'),
							'default' => '0',
						),

						array(
							'type' => 'toggle',
							'name' => 'rehub_disable_share',
							'label' => esc_html__('Disable share buttons', 'rehub-framework'),
							'description' => esc_html__('Disable share buttons after content on pages', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'rehub_disable_share_top',
							'label' => esc_html__('Disable share buttons', 'rehub-framework'),
							'description' => esc_html__('Disable share buttons before content on pages', 'rehub-framework'),
							'default' => '0',
						),																	
						array(
							'type' => 'toggle',
							'name' => 'rehub_disable_prev',
							'label' => esc_html__('Disable previous and next', 'rehub-framework'),
							'description' => esc_html__('Disable previous and next post buttons', 'rehub-framework'),
							'default' => '0',
						),																	
						array(
							'type' => 'toggle',
							'name' => 'rehub_disable_tags',
							'label' => esc_html__('Disable tags', 'rehub-framework'),
							'description' => esc_html__('Disable tags after content from pages', 'rehub-framework'),
							'default' => '0',
						),
		
						array(
							'type' => 'toggle',
							'name' => 'rehub_disable_author',
							'label' => esc_html__('Disable author box', 'rehub-framework'),
							'description' => esc_html__('Disable author box after content from pages', 'rehub-framework'),
							'default' => '1',
						),
						array(
							'type' => 'toggle',
							'name' => 'rehub_disable_relative',
							'label' => esc_html__('Disable relative posts', 'rehub-framework'),
							'description' => esc_html__('Disable relative posts box after content from pages', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'toggle',
							'name' => 'crop_dis_related',
							'label' => esc_html__('Disable crop in related', 'rehub-framework'),
							'default' => '0',
						),					
						array(
							'type' => 'toggle',
							'name' => 'rehub_disable_feature_thumb',
							'label' => esc_html__('Disable top thumbnail on single page', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'disable_post_sidebar',
							'label' => esc_html__('Disable sidebar on posts', 'rehub-framework'),
							'default' => '0',
						),											
						array(
							'type' => 'toggle',
							'name' => 'rehub_disable_comments',
							'label' => esc_html__('Disable standart comments', 'rehub-framework'),
							'default' => '0',
						),								
						array(
							'type' => 'toggle',
							'name' => 'old_review_meta',
							'label' => esc_html__('Enable Old review post panel', 'rehub-framework'),
							'default' => '0',
						),																										
					),
				),
			),
		),
		array(
			'title' => esc_html__('Ads and Code Zones', 'rehub-framework'),
			'name' => 'menu_9',
			'icon' => 'rhicon rhi-code',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Ads code in header and footer', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'textarea',
							'name' => 'rehub_ads_top',
							'label' => esc_html__('Header area', 'rehub-framework'),
							'description' => esc_html__('This banner code will be visible in header. Width of this zone depends on style of header (You can choose it in Header and menu tab)', 'rehub-framework'),
							'default' => '',
						),	
						array(
							'type' => 'textarea',
							'name' => 'rehub_ads_megatop',
							'label' => esc_html__('Before header area', 'rehub-framework'),
							'description' => esc_html__('This banner code will be visible before header.', 'rehub-framework'),
							'default' => '',
						),
						array(
							'type' => 'textarea',
							'name' => 'rehub_ads_infooter',
							'label' => esc_html__('Before footer area', 'rehub-framework'),
							'description' => esc_html__('This banner code will be visible before footer', 'rehub-framework'),
							'default' => '',
						),																																				
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Global code for single page', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'textarea',
							'name' => 'rehub_single_after_title',
							'label' => esc_html__('After title area', 'rehub-framework'),
							'description' => esc_html__('This code will be visible after title', 'rehub-framework'),
							'default' => '',
						),	
						array(
							'type' => 'textarea',
							'name' => 'rehub_single_before_post',
							'label' => esc_html__('Before content area', 'rehub-framework'),
							'description' => esc_html__('This code will be visible before post content', 'rehub-framework'),
							'default' => '',
						),	
						 array(
							'type' => 'notebox',
							'name' => 'rehub_single_before_post_note',
							'label' => esc_html__('Tips', 'rehub-framework'),
							'description' => esc_html__('You can wrap your code with &lt;div class=&quot;floatright ml15&quot;&gt;your ads code&lt;/div&gt; if you want to add right float or &lt;div class=&quot;floatleft mr15&quot;&gt;your ads code&lt;/div&gt; for left float. Please, use square ads with width 250-300px for floated ads.', 'rehub-framework'),
							'status' => 'info',
						),																	
						array(
							'type' => 'textarea',
							'name' => 'rehub_single_code',
							'label' => esc_html__('After post area', 'rehub-framework'),
							'description' => esc_html__('This code will be visible after post', 'rehub-framework'),
							'default' => '',
						),	
						array(
							'type' => 'textarea',
							'name' => 'rehub_single_after_comment',
							'label' => esc_html__('After comment', 'rehub-framework'),
							'description' => esc_html__('This code will be visible after comment section', 'rehub-framework'),
							'default' => '',
						),	
						array(
							'type' => 'textarea',
							'name' => 'rehub_shortcode_ads',
							'label' => esc_html__('Insert custom ads code for shortcode', 'rehub-framework'),
							'description' => esc_html__('You can insert this code in any place of content by shortcode [wpsm_ads1]', 'rehub-framework'),
						),
						array(
							'type' => 'textarea',
							'name' => 'rehub_shortcode_ads_2',
							'label' => esc_html__('Insert custom ads code for shortcode', 'rehub-framework'),
							'description' => esc_html__('You can insert this code in any place of content by shortcode [wpsm_ads2]', 'rehub-framework'),
						),	
						array(
							'type' => 'textarea',
							'name' => 'rehub_ads_coupon_area',
							'label' => esc_html__('Coupon area', 'rehub-framework'),
							'description' => esc_html__('This banner code will be visible in coupon modal', 'rehub-framework'),
							'default' => '',
						),																											
					),
				),																
				array(
					'type' => 'section',
					'title' => esc_html__('Global branded area', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'notebox',
							'name' => 'rehub_branded_banner_note',
							'label' => esc_html__('Note', 'rehub-framework'),
							'description' => esc_html__('Branded area displays after header. You can set direct link on image or insert any html code or shortcode', 'rehub-framework'),
							'status' => 'normal',							
						),						
						array(
							'type' => 'textarea',
							'name' => 'rehub_branded_banner_image',
							'label' => esc_html__('Branded area', 'rehub-framework'),
							'description' => esc_html__('Set any custom code or link to image', 'rehub-framework'),
							'default' => '',
						),												
					),
				),

			),
		),
		array(
			'title' => esc_html__('Reviews', 'rehub-framework'),
			'name' => 'menu_10',
			'icon' => 'rhicon rhi-star',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Reviews, links, rating', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'select',
							'name' => 'type_user_review',
							'label' => esc_html__('Type of user ratings', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'simple',
									'label' => esc_html__('simple rating, no criterias', 'rehub-framework'),
								),
								array(
									'value' => 'full_review',
									'label' => esc_html__('full review with criterias and pros, cons', 'rehub-framework'),
								),	
								array(
									'value' => 'user',
									'label' => esc_html__('Show only user\'s reviews with criterias (don\'t show editor\'s review)', 'rehub-framework'),
								),									
								array(
									'value' => 'none',
									'label' => esc_html__('none', 'rehub-framework'),
								),																						
							),
							'default' => 'simple',
						),
						array(
							'type' => 'select',
							'name' => 'type_total_score',
							'label' => esc_html__('How to calculate total score of review', 'rehub-framework'),
							'items' => array(
								array(
								'value' => 'editor',
								'label' => esc_html__('based on Expert Score', 'rehub-framework'),
								),
								array(
								'value' => 'average',
								'label' => esc_html__('average (editor\'s and user\'s)', 'rehub-framework'),
								),	
								array(
								'value' => 'user',
								'label' => esc_html__('based on user\'s', 'rehub-framework'),
								),																							
							),
							'dependency' => array(
								'field'    => 'type_user_review',
								'function' => 'rehub_framework_rev_type',
							),							
							'default' => 'average',
						),							
						array(
							'type' => 'textbox',
							'name' => 'rehub_user_rev_criterias',
							'label' => esc_html__('User review criteria names', 'rehub-framework'),
							'description' => esc_html__('Type with commas and no spaces. Example: Design,Price,Battery life', 'rehub-framework'),
							'dependency' => array(
								'field'    => 'type_user_review',
								'function' => 'user_rev_type',
							),							
						),
						array(
							'type' => 'select',
							'name' => 'type_schema_review',
							'label' => esc_html__('Type of schema for reviews', 'rehub-framework'),
							'items' => array(
								array(
									'value' => 'editor',
									'label' => esc_html__('Based on editor\'s review', 'rehub-framework'),
								),
								array(
									'value' => 'user',
									'label' => esc_html__('Based on user reviews', 'rehub-framework'),
								),	
								array(
									'value' => 'none',
									'label' => esc_html__('Disable all and use your custom', 'rehub-framework'),
								),																					
							),
							'default' => 'editor',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_org_name_review',
							'label' => esc_html__('Place organization name', 'rehub-framework'),
							'description' => esc_html__('This is for seo purpose. Must be short name. Also, set correct logo width and height in theme option - logo option', 'rehub-framework'),						
						),																							
						array(
							'type' => 'select',
							'name' => 'allowtorate',
							'label' => esc_html__('Allow to rate posts', 'rehub-framework'),
							'description' => esc_html__('Who can rate review posts?', 'rehub-framework'),
							'items' => array(
								array(
								'value' => 'guests',
								'label' => esc_html__('guests', 'rehub-framework'),
								),
								array(
								'value' => 'users',
								'label' => esc_html__('users', 'rehub-framework'),
								),
								array(
								'value' => 'guests_users',
								'label' => esc_html__('guests and users', 'rehub-framework'),
								),								
								),
							'default' => 'guests_users',
						),					
						array(
							'type' => 'color',
							'name' => 'rehub_review_color',
							'label' => esc_html__('Default color for editor\'s review box and total score', 'rehub-framework'),
							'description' => esc_html__('Choose the background color or leave blank for default red color', 'rehub-framework'),	
							'format' => 'hex',							
						),	
						array(
							'type' => 'color',
							'name' => 'rehub_review_color_user',
							'label' => esc_html__('Default color for user review box and user stars', 'rehub-framework'),
							'description' => esc_html__('Choose the background color or leave blank for default blue color', 'rehub-framework'),	
							'format' => 'hex',						
						),																		
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Add review fields to RH frontend form', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'textarea',
							'name' => 'rh_front_review_fields',
							'label' => esc_html__('Form ID and names of review criterias', 'rehub-framework'),
							'description' => esc_html__('Type Form ID and names of criterias for review form like: 2:Design,Price,Usability without spaces. Place each form values from next line. You can download RH Frontend Publishing plugin in Rehub - Plugins tab', 'rehub-framework'),
							'default' => '',
						),																	
					),
				),		
			),
		),
		array(
			'title' => esc_html__('Localization', 'rehub-framework'),
			'name' => 'menu_loc',
			'icon' => 'rhicon rhi-language',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Localization', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'select',
							'name' => 'price_pattern',
							'label' => esc_html__('Choose price pattern', 'rehub-framework'),
							'items' => array(
								array(
								'value' => 'us',
								'label' => esc_html__('USA. Example: 1000.00', 'rehub-framework'),
								),
								array(
								'value' => 'eu',
								'label' => esc_html__('EU. Example: 1000,00', 'rehub-framework'),
								),	
								array(
								'value' => 'in',
								'label' => esc_html__('IN. Example: 1,000.00', 'rehub-framework'),
								),															
							),
							'default' => 'us',
						),						
						array(
							'type' => 'textbox',
							'name' => 'rehub_btn_text',
							'label' => esc_html__('Set text for button', 'rehub-framework'),
							'description' => esc_html__('It will be used on button for product reviews, top rating pages instead BUY THIS ITEM', 'rehub-framework'),
							'validation' => 'maxlength[14]',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_mask_text',
							'label' => esc_html__('Set text for coupon mask', 'rehub-framework'),
							'description' => esc_html__('It will be used on coupon mask instead REVEAL COUPON', 'rehub-framework'),
						),						
						array(
							'type' => 'textbox',
							'name' => 'rehub_btn_text_aff_links',
							'label' => esc_html__('Set text for button', 'rehub-framework'),
							'description' => esc_html__('It will be used on button for products with list of links instead CHOOSE OFFER.', 'rehub-framework'),
						),							
						array(
							'type' => 'textbox',
							'name' => 'rehub_readmore_text',
							'label' => esc_html__('Set text for read more link', 'rehub-framework'),
							'description' => esc_html__('It will be used instead READ MORE', 'rehub-framework'),
						),
						array(
							'type' => 'textbox',
							'name' => 'buy_best_text',
							'label' => esc_html__('Set text for comparison list layout', 'rehub-framework'),
							'description' => esc_html__('It will be used instead BUY FOR BEST PRICE', 'rehub-framework'),
						),																					
						array(
							'type' => 'textbox',
							'name' => 'rehub_review_text',
							'label' => esc_html__('Set text for full review link', 'rehub-framework'),
							'description' => esc_html__('It will be used in top review pages instead READ FULL REVIEW', 'rehub-framework'),
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_search_text',
							'label' => esc_html__('Set text for Search placeholder', 'rehub-framework'),
							'description' => esc_html__('It will be used in default search form instead SEARCH', 'rehub-framework'),
						),				
						array(
							'type' => 'textbox',
							'name' => 'rehub_commenttitle_text',
							'label' => esc_html__('Set text for comment title, when no comments', 'rehub-framework'),
							'description' => esc_html__('It will be used instead: We will be happy to see your thoughts', 'rehub-framework'),
						),							
						array(
							'type' => 'textbox',
							'name' => 'rehub_related_text',
							'label' => esc_html__('Set text for Related article title', 'rehub-framework'),
							'description' => esc_html__('It will be used instead Related Articles', 'rehub-framework'),
						),																																		
					),
				),
			),
		),
		array(
			'title' => esc_html__('User options', 'rehub-framework'),
			'name' => 'usersmenus',
			'icon' => 'rhicon rhi-user',
			'controls' => array(		
				array(
					'type' => 'section',
					'title' => esc_html__('Options for User login popup', 'rehub-framework'),
					'fields' => array(
						 array(
							'type' => 'notebox',
							'name' => 'rehub_user_note',
							'label' => esc_html__('Note!', 'rehub-framework'),
							'description' => esc_html__('Please, read about user functions in our', 'rehub-framework').' <a href="http://rehubdocs.wpsoul.com/docs/rehub-framework/user-submit-memberships-profiles/" target="_blank">documentation</a>',
							'status' => 'info',
						),						
						array(
							'type' => 'toggle',
							'name' => 'userlogin_enable',
							'label' => esc_html__('Enable user login modal?', 'rehub-framework'),
							'description' => esc_html__('If you disable this, user modal will not work', 'rehub-framework'),
							'default' => '0',
						),										
						array(
							'type' => 'textbox',
							'name' => 'custom_msg_popup',
							'label' => esc_html__('Add custom message', 'rehub-framework'),
							'description' => esc_html__('Add text or shortcode in registration popup', 'rehub-framework'),							
						),	
						array(
							'type' => 'textbox',
							'name' => 'custom_login_url',
							'label' => esc_html__('Type url for login button', 'rehub-framework'),
							'description' => esc_html__('By default, login button triggers login popup, but you can redirect users to any link with registration form if you set this field. Login popup will not work in this case', 'rehub-framework'),
						),					
						array(
							'type' => 'textbox',
							'name' => 'custom_register_link',
							'label' => esc_html__('Add custom register link', 'rehub-framework'),
							'description' => esc_html__('Add custom link if you want to use custom register page instead of sign up in popup', 'rehub-framework'),							
						),
						array(
							'type' => 'textbox',
							'name' => 'custom_redirect_after_login',
							'label' => esc_html__('Add custom redirect after login url', 'rehub-framework'),
							'description' => esc_html__('You can also use placeholder %%userlogin%% in url, which will be replaced by user login', 'rehub-framework'),							
						),																											
						array(
							'type' => 'textbox',
							'name' => 'userlogin_term_page',
							'label' => esc_html__('Terms and conditions page url for popup', 'rehub-framework'),
						),
						array(
							'type' => 'textbox',
							'name' => 'userlogin_policy_page',
							'label' => esc_html__('Privacy Policy page url for popup', 'rehub-framework'),
						),						
						array(
							'type' => 'textbox',
							'name' => 'userlogin_submit_page',
							'label' => esc_html__('Type additional URL', 'rehub-framework'),
							'description' => esc_html__('Used in User dropdown in header and dashboards. For example, you can use link on Submit Form for posts', 'rehub-framework'),						
						),	
						array(
							'type' => 'textbox',
							'name' => 'userlogin_submit_page_label',
							'label' => esc_html__('Type additional URL Label', 'rehub-framework'),						
						),						
						array(
							'type' => 'textbox',
							'name' => 'userlogin_edit_page',
							'label' => esc_html__('Type additional second URL', 'rehub-framework'),
							'description' => esc_html__('Used in User dropdown in header and dashboards', 'rehub-framework'),		
						),
						array(
							'type' => 'textbox',
							'name' => 'userlogin_edit_page_label',
							'label' => esc_html__('Type additional second URL Label', 'rehub-framework'),			
						),															
						array(
							'type' => 'toggle',
							'name' => 'enable_comment_link',
							'label' => esc_html__('Enable link on user profile in comment?', 'rehub-framework'),
							'description' => esc_html__('Can slow a bit your site if you have many comments', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'textbox',
							'name' => 'rh_sync_role',
							'label' => esc_html__('Synchronize one role to other', 'rehub-framework'),
							'description' => 'Useful, when you sychronize wordpress role to roles of Membership plugins and you want to deactivate/activate this role when user gets new role from another plugin. Example of settings:<br /><br />vendor:s2member_level0:s2member_level1,s2member_level2<br /><br />First name is role which you want to synchronize (you can set any other, for example seller - for Dokan or dc_vendor for WC Marketplace), next set which is divided by ":" is role which will trigger removing of this role. Next set is roles which will trigger adding this role. If you don\'t use any vendor plugin and want to allow users from S2 member to upload media, set next<br /><br /> contributor:s2member_level0:s2member_level1,s2member_level2<br /><br />',					
						),																						
					),
				),
			),
		),
		array(
			'title' => esc_html__('Buddypress options', 'rehub-framework'),
			'name' => 'bpoptions',
			'icon' => 'rhicon rhi-users',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('BuddyPress', 'rehub-framework'),
					'fields' => array(					
						array(
							'type' => 'toggle',
							'name' => 'bp_redirect',
							'label' => esc_html__('Enable redirect to BP profiles?', 'rehub-framework'),
							'description' => esc_html__('By default, user link goes to author page. You can redirect all author links from posts to BuddyPress profiles', 'rehub-framework'),
							'default' => '0',
						),
						array(
							'type' => 'toggle',
							'name' => 'bp_group_widget_area',
							'label' => esc_html__('Add additional sidebar area for Group pages?', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'upload',
							'name' => 'rehub_bpheader_image',
							'label' => esc_html__('Default background image in header. Recommended size 1900x260', 'rehub-framework'),
							'description' => esc_html__('Upload a background image or leave blank', 'rehub-framework'),
							'default' => '',
						),																			
						array(
							'type' => 'select',
							'name' => 'bp_deactivateemail_confirm',
							'label' => esc_html__('Synchronization between login popup and BP', 'rehub-framework'),
							'description' => esc_html__('You can enable BP registration logic in theme login popup', 'rehub-framework'),
							'items' => array(
								array(
									'value' => '1',
									'label' => esc_html__('Disable email and BP activation', 'rehub-framework'),
								),
								array(
									'value' => 'bp',
									'label' => esc_html__('Enable BP and email activation', 'rehub-framework'),
								),														
							),
							'default' => array(
								'bp',
							),						
						),											
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_seo_description',
							'label' => esc_html__('Add name of Xprofile field for seo Description', 'rehub-framework'),
							'description' => esc_html__('You can create such field in Users - Profile fields if you have enabled Extended Profiles in Settings - Buddypress', 'rehub-framework'),
						),
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_phone',
							'label' => esc_html__('Add name of Xprofile field for Phone', 'rehub-framework'),
							'description' => esc_html__('You can create such field in Users - Profile fields if you have enabled Extended Profiles in Settings - Buddypress', 'rehub-framework'),
						),							
						array(
							'type' => 'textarea',
							'name' => 'rh_bp_custom_message_profile',
							'label' => esc_html__('Add custom message or html in profile of User', 'rehub-framework'),
							'description' => esc_html__('You can use shortcodes to show additional info inside Profile tab of user Profile. For example, shortcodes from S2Member plugin or any conditional information. If you want to show information for owner of profile, wrap it with shortcode [rh_is_bpmember_profile]Content[/rh_is_bpmember_profile]', 'rehub-framework'),							
						),																					
					),
				),	
				array(
					'type' => 'section',
					'title' => esc_html__('Posts Profile tab', 'rehub-framework'),
					'fields' => array(																				
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_user_post_name',
							'label' => esc_html__('Add Name of Posts tab in Profile', 'rehub-framework'),
						),
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_user_post_slug',
							'label' => esc_html__('Add slug of Posts tab', 'rehub-framework'),
							'description' => esc_html__('Use only latin symbols, without spaces', 'rehub-framework'),
						),	
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_user_post_pos',
							'label' => esc_html__('Add position of tab', 'rehub-framework'),
							'default' => '20',
						),
						array(
							'type' => 'select',
							'name' => 'rh_bp_user_post_newpage',
							'label' => esc_html__('Assign page for Add new posts', 'rehub-framework'),
							'description' => esc_html__('Choose page where you have frontend form for posts. Content of this page will be assigned to tab. You can use bundled RH Frontend PRO to create such form.', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_pages',
									),
								),
							),													
						),	
						array(
							'type' => 'select',
							'name' => 'rh_bp_user_post_editpage',
							'label' => esc_html__('Assign page for Edit Posts', 'rehub-framework'),
							'description' => esc_html__('Choose page where you have EDIT form for posts. If you use RH Frontend Form, such page, usually, has shortcode like [wpfepp_post_table form="1" show_all=0]', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_pages',
									),
								),
							),													
						),									
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_user_post_type',
							'label' => esc_html__('Add member type', 'rehub-framework'),
							'description' => esc_html__('If you want to show tab only for special member type, add here slug of this member type. Note, Buddypress member type is not the same as wordpress role', 'rehub-framework'),
						),
					),
				),	
				array(
					'type' => 'section',
					'title' => esc_html__('Product Profile tab', 'rehub-framework'),
					'fields' => array(																				
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_user_product_name',
							'label' => esc_html__('Add Name of Product tab in Profile', 'rehub-framework'),
						),
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_user_product_slug',
							'label' => esc_html__('Add slug of Product tab', 'rehub-framework'),
							'description' => esc_html__('Use only latin symbols, without spaces', 'rehub-framework'),
						),	
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_user_product_pos',
							'label' => esc_html__('Add position of tab', 'rehub-framework'),
							'default' => '21',
						),
						array(
							'type' => 'select',
							'name' => 'rh_bp_user_product_newpage',
							'label' => esc_html__('Assign page for Add new Product', 'rehub-framework'),
							'description' => esc_html__('Choose page where you have frontend form for Product. Content of this page will be assigned to tab. You can use bundled RH Frontend PRO to create such form.', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_pages',
									),
								),
							),													
						),	
						array(
							'type' => 'select',
							'name' => 'rh_bp_user_product_editpage',
							'label' => esc_html__('Assign page for Edit Product', 'rehub-framework'),
							'description' => esc_html__('Choose page where you have EDIT form for products. If you use RH Frontend Form, such page, usually, has shortcode like [wpfepp_post_table form="1" show_all=0]', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_pages',
									),
								),
							),													
						),									
						array(
							'type' => 'textbox',
							'name' => 'rh_bp_user_product_type',
							'label' => esc_html__('Add member type', 'rehub-framework'),
							'description' => esc_html__('If you want to show tab only for special member type, add here slug of this member type. Note, Buddypress member type is not the same as wordpress role', 'rehub-framework'),
						),
					),
				),						
				array(
					'type' => 'section',
					'title' => esc_html__('MyCred Options', 'rehub-framework'),
					'fields' => array(																				
						array(
							'type' => 'toggle',
							'name' => 'bp_enable_mycred_comment_badge',
							'label' => esc_html__('Enable badges from MyCred plugin in comments for Buddypress?', 'rehub-framework'),
							'description' => esc_html__('Can slow your activity pages', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'toggle',
							'name' => 'rh_enable_mycred_comment',
							'label' => esc_html__('Enable badges, points, ranks from MyCred plugin in regular comments?', 'rehub-framework'),
							'description' => esc_html__('Can slow your single pages', 'rehub-framework'),
							'default' => '0',
						),	
						array(
							'type' => 'textbox',
							'name' => 'rh_mycred_custom_points',
							'label' => esc_html__('Show custom point type instead default', 'rehub-framework'),					
						),																											
						array(
							'type' => 'textarea',
							'name' => 'rh_award_role_mycred',
							'label' => esc_html__('Give user roles for their Mycred Points', 'rehub-framework'),
							'description' => esc_html__('If you use MyCred plugin and want to give user new role once he gets definite points, you can use this area. Syntaxis is next: role:1000. Where role is role which you want to give and 1000 is amount of points to get this role. Place each role with next line. Place them in ASC mode. First line, for example, 10 points, next is 100. Function also works as opposite. ', 'rehub-framework'),					
						),	
						array(
							'type' => 'toggle',
							'name' => 'rh_award_type_mycred',
							'label' => esc_html__('Give BP member types instead of roles?', 'rehub-framework'),
							'description' => esc_html__('If you want to give users member types instead of roles which are set above, enable this', 'rehub-framework'),						
							'default' => '0',					
						),																					
					),
				),					
			),
		),
		array(
			'title' => esc_html__('Dynamic comparison', 'rehub-framework'),
			'name' => 'compare',
			'icon' => 'rhicon rhi-database',
			'controls' => array(			
				array(
					'type' => 'section',
					'title' => esc_html__('Add common page for comparison', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'select',
							'name' => 'compare_page',
							'label' => esc_html__('Select page for comparison', 'rehub-framework'),
							'description' => esc_html__('Page must have top chart constructor page template or shortcode [wpsm_woocharts]. We recommend to set page as full width in right panel of Edit page area', 'rehub-framework'),
							'items' => array(
								'data' => array(
									array(
										'source' => 'function',
										'value' => 'vp_get_pages',
									),
								),
							),													
						),																				
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Options for multigroup dynamic comparison', 'rehub-framework'),
					'fields' => array(	
						array(
							'type' => 'textarea',
							'name' => 'compare_multicats_textarea',
							'label' => esc_html__('Assign categories to pages', 'rehub-framework'),
							'description' => esc_html__('Use this option if you want to have different comparison groups. Create separate pages for each group. Then, use next syntaxis: 1,2,3;Title;23, where 1,2,3 - category IDs, Title - a general name for category group, 23 - a page ID of comparison. You can add also custom taxonomy in the end. By default, product categories will be used. Delimiter is ";"', 'rehub-framework').' <br/><br/><a href="http://rehubdocs.wpsoul.com/docs/rehub-framework/comparisons-tables-charts-lists/dynamic-comparison-charts/" target="_blank">Documentation</a>',							
						),																					
					),
				),	
				array(
					'type' => 'section',
					'title' => esc_html__('Common', 'rehub-framework'),
					'fields' => array(	
						array(
							'type' => 'toggle',
							'name' => 'compare_disable_button',
							'label' => esc_html__('Disable button in right side', 'rehub-framework'),
							'description' => esc_html__('You can disable button with compare icon on right side of site. You can place this icon in header. Use Shop/Comparison header in theme option - header and menu - Header layout', 'rehub-framework'),
						),					
						array(
							'type' => 'textbox',
							'name' => 'compare_woo_cats',
							'label' => esc_html__('Set ids of product categories where to show button. Leave blank to show in all products', 'rehub-framework'),
						),
					),
				),					
			),
		),
		array(
			'title' => esc_html__('Custom badges', 'rehub-framework'),
			'name' => 'badges',
			'icon' => 'rhicon rhi-certificate',
			'controls' => array(				
				array(
					'type' => 'section',
					'title' => esc_html__('First badge', 'rehub-framework'),
					'fields' => array(
					    array(
					        'type' => 'html',
					        'name' => 'admin_badge_preview_1',
					        'binding' => array(
					            'field'    => 'badge_label_1, badge_color_1',
					            'function' => 'admin_badge_preview_html',
					        ),
					    ),						
						array(
							'type' => 'textbox',
							'name' => 'badge_label_1',
							'label' => esc_html__('Label', 'rehub-framework'),
							'default' => esc_html__('Editor choice', 'rehub-framework'),
							'validation' => 'maxlength[20]',	
						),						
						array(
							'type' => 'color',
							'name' => 'badge_color_1',
							'label' => esc_html__('Color', 'rehub-framework'),
							'format' => 'hex',	
						),						
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Second badge', 'rehub-framework'),
					'fields' => array(
					    array(
					        'type' => 'html',
					        'name' => 'admin_badge_preview_2',
					        'binding' => array(
					            'field'    => 'badge_label_2, badge_color_2',
					            'function' => 'admin_badge_preview_html',
					        ),
					    ),						
						array(
							'type' => 'textbox',
							'name' => 'badge_label_2',
							'label' => esc_html__('Label', 'rehub-framework'),
							'default' => esc_html__('Best seller', 'rehub-framework'),
							'validation' => 'maxlength[20]',																
						),						
						array(
							'type' => 'color',
							'name' => 'badge_color_2',
							'label' => esc_html__('Color', 'rehub-framework'),
							'format' => 'hex',	
						),						
					),
				),	
				array(
					'type' => 'section',
					'title' => esc_html__('Third badge', 'rehub-framework'),
					'fields' => array(
					    array(
					        'type' => 'html',
					        'name' => 'admin_badge_preview_3',
					        'binding' => array(
					            'field'    => 'badge_label_3, badge_color_3',
					            'function' => 'admin_badge_preview_html',
					        ),
					    ),						
						array(
							'type' => 'textbox',
							'name' => 'badge_label_3',
							'label' => esc_html__('Label', 'rehub-framework'),
							'default' => esc_html__('Best value', 'rehub-framework'),
							'validation' => 'maxlength[20]',															
						),						
						array(
							'type' => 'color',
							'name' => 'badge_color_3',
							'label' => esc_html__('Color', 'rehub-framework'),
							'format' => 'hex',	
						),						
					),
				),
				array(
					'type' => 'section',
					'title' => esc_html__('Fourth badge', 'rehub-framework'),
					'fields' => array(
					    array(
					        'type' => 'html',
					        'name' => 'admin_badge_preview_4',
					        'binding' => array(
					            'field'    => 'badge_label_4, badge_color_4',
					            'function' => 'admin_badge_preview_html',
					        ),
					    ),						
						array(
							'type' => 'textbox',
							'name' => 'badge_label_4',
							'label' => esc_html__('Label', 'rehub-framework'),
							'default' => esc_html__('Best price', 'rehub-framework'),
							'validation' => 'maxlength[20]',								
						),						
						array(
							'type' => 'color',
							'name' => 'badge_color_4',
							'label' => esc_html__('Color', 'rehub-framework'),
							'format' => 'hex',	
						),						
					),
				),											
			),
		),
		array(
			'title' => esc_html__('Social Media Options', 'rehub-framework'),
			'name' => 'menu_5',
			'icon' => 'rhicon rhi-facebook',
			'controls' => array(
				array(
					'type' => 'section',
					'title' => esc_html__('Social Media Pages', 'rehub-framework'),
					'fields' => array(
						array(
							'type' => 'textbox',
							'name' => 'rehub_facebook',
							'label' => esc_html__('Facebook link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_twitter',
							'label' => esc_html__('Twitter link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_instagram',
							'label' => esc_html__('Instagram link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_wa',
							'label' => esc_html__('WhatsApp link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_youtube',
							'label' => esc_html__('Youtube link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_tiktok',
							'label' => esc_html__('Tiktok link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_vimeo',
							'label' => esc_html__('Vimeo link', 'rehub-framework'),
							'validation' => 'url',
						),						
						array(
							'type' => 'textbox',
							'name' => 'rehub_pinterest',
							'label' => esc_html__('Pinterest link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_linkedin',
							'label' => esc_html__('Linkedin link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_soundcloud',
							'label' => esc_html__('Soundcloud link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_vk',
							'label' => esc_html__('Vk.com link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'rehub_telegram',
							'label' => esc_html__('Telegram link', 'rehub-framework'),
							'validation' => 'url',
						),
						array(
							'type' => 'textbox',
							'name' => 'discord',
							'label' => esc_html__('Discord link', 'rehub-framework'),
							'validation' => 'url',
						),						
						array(
							'type' => 'textbox',
							'name' => 'rehub_rss',
							'label' => esc_html__('Rss link', 'rehub-framework'),
							'validation' => 'url',
						),												
					),
				),
			),
		),
	)
);

$theme_options_additional = include(rf_locate_template( 'inc/options/option_additional.php' ));
if(!empty($theme_options_additional)){
	$theme_options['menus'][] = $theme_options_additional;
}

return $theme_options;

/**
 *EOF
 */