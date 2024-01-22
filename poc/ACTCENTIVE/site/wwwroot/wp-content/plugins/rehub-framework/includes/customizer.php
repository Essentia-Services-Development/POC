<?php
/**
 * ReHub Theme Customizer
 *
 * @package rehub
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class REHub_Framework_Customizer {
	public static $rh_cross_option_fields = array(
		'theme_subset',
	    'rehub_custom_color',
	    'rehub_sec_color',
	    'rehub_third_color',
	    'rehub_btnoffer_color',
	    'rehub_btnoffer_color_hover',
	    'rehub_btnoffer_color_text',
	    'rehub_btnofferhover_color_text',
	    'enable_smooth_btn',
	    'rehub_color_link',
	    'rehub_sidebar_left',
	    'rehub_body_block',
	    'rehub_content_shadow',
	    'rehub_color_background',
	    'rehub_background_image',
	    'rehub_background_repeat',
	    'rehub_background_position',
	    'rehub_background_fixed',
	    'rehub_branded_bg_url',
	    'rehub_logo',
	    'rehub_logo_inmenu_url',
	    'rehub_mobile_header_bg',
	    'rehub_mobile_header_color',
	    'rehub_mobtool_bg',
	    'rehub_mobtool_color',
		'rehub_mobtool_top',
		'rehub_mobtool_force',
	    'logo_mobilesliding',
	    'color_mobilesliding',
	    'rehub_logo_retina',
	    'rehub_logo_retina_width',
	    'rehub_logo_retina_height',
	    'rehub_text_logo',
	    'rehub_text_slogan',
	    'rehub_logo_pad',
	    'rehub_sticky_nav',
	    'header_src_icon',
	    'rehub_logo_sticky_url',
	    'header_logoline_style',
	    'rehub_header_color_background',
	    'dark_theme',
	    'rehub_header_background_image',
	    'rehub_header_background_repeat',
	    'rehub_header_background_position',
	    'header_menuline_style',
	    'header_menuline_type',
	    'rehub_nav_font_custom',
	    'rehub_nav_font_upper',
	    'rehub_nav_font_light',
	    'rehub_nav_font_border',
	    'rehub_enable_menu_shadow',
	    'rehub_custom_color_nav',
	    'rehub_custom_color_nav_font',
	    'header_topline_style',
	    'rehub_custom_color_top',
	    'rehub_custom_color_top_font',
	    'rehub_header_top_enable',
	    'rehub_top_line_content',
		'rehub_header_style',
		'header_seven_compare_btn',
		'header_seven_compare_btn_label',
		'header_seven_cart',
		'header_seven_cart_as_btn',
		'header_five_menucenter',
		'header_seven_login',
		'header_seven_login_label',
		'header_seven_wishlist',
		'header_seven_wishlist_label',
		'header_seven_more_element',
		'header_six_login',
		'header_six_btn',
		'header_six_btn_color',
		'header_six_btn_txt',
		'header_six_btn_url',
		'header_six_btn_login',
		'header_six_src',
		'header_six_menu',
		'rehub_footer_widgets',
		'footer_template',
		'footer_style',
		'footer_color_background',
		'footer_background_image',
		'footer_background_repeat',
		'footer_background_position',
		'footer_style_bottom',
		'rehub_footer_text',
		'rehub_footer_logo',
		'width_layout',
		'woo_compact_loop_btn',
		'woo_wholesale',
		'woo_quick_view',
		'woo_aff_btn',
		'disable_woo_scripts',
		'woo_code_zone_loop',
		'woo_code_zone_button',
		'woo_code_zone_content',
		'woo_code_zone_footer',
		'woo_code_zone_float',								
		'woo_number',
		'woo_design',
		'woo_columns',
		'price_meta_woogrid',
		'wooloop_heading_color',
		'wooloop_heading_size',
		'wooloop_price_color',
		'wooloop_price_size',
		'wooloop_sale_color',
		'rehub_sidebar_left_shop',
		'product_layout_style',
		'post_layout_style',
		'archive_layout', 
		'search_layout',
		'exclude_author_meta',
		'exclude_cat_meta',
		'exclude_date_meta',
		'exclude_comments_meta',
		'hotmeter_disable',
		'wishlist_disable',
		'wish_cache_enabled',
		'thumb_only_users',
		'wish_only_users',
		'post_view_disable',
		'date_publish',
		'rehub_disable_breadcrumbs',
		'rehub_disable_share',
		'rehub_disable_share_top',
		'rehub_disable_prev',
		'rehub_disable_tags',
		'rehub_disable_author',
		'rehub_disable_relative',
		'crop_dis_related',
		'rehub_disable_feature_thumb',
		'disable_post_sidebar',
		'rehub_disable_comments',
		'rehub_ads_top',
		'rehub_ads_megatop',
		'rehub_ads_infooter',
		'rehub_single_after_title',
		'rehub_single_before_post',
		'rehub_single_code',
		'rehub_ads_coupon_area',
		'rehub_branded_banner_image',
		'custom_msg_popup',
		'custom_login_url',
		'custom_register_link',
		'userlogin_term_page',
		'userlogin_policy_page',
		'rehub_bpheader_image',
		'rh_bp_custom_message_profile', 
		'rh_bp_user_post_name',
		'rh_bp_user_post_slug',
		'rh_bp_user_post_pos',
		'rh_bp_user_post_newpage',
		'rh_bp_user_post_editpage',
		'rh_bp_user_post_type',
		'rh_bp_user_product_name',
		'rh_bp_user_product_slug',
		'rh_bp_user_product_pos',
		'rh_bp_user_product_newpage',
		'rh_bp_user_product_editpage',
		'rh_bp_user_product_type',
		'rh_award_type_mycred',
		'rh_award_role_mycred',
		'rh_mycred_custom_points',
		'rh_enable_mycred_comment', 
		'badge_color_1',
		'badge_label_1',
		'badge_color_2',
		'badge_label_2',
		'badge_color_3',
		'badge_label_3',
		'badge_color_4',
		'badge_label_4',
		'compare_woo_cats', 
		'compare_disable_button',
		'compare_multicats_textarea',
		'compare_page',
		'rehub_nav_font_group',
		'rehub_headings_font_group',
		'rehub_btn_font_group',
		'rehub_body_font_group',
		'rehub_headings_font_upper',
		'rehub_btn_font_upper_dis', 
		'body_font_size'
	);	

	public function __construct() {
		add_action( 'customize_register', array( $this, 'rh_customize_register'));
		add_action('admin_enqueue_scripts', array( $this, 'rh_customizer_scripts'));
		add_action( 'customize_preview_init', array( $this, 'rh_live_preview_scripts'));
		add_action( 'save_post_customize_changeset', array( $this, 'rh_save_theme_options'));
		add_action('vp_option_set_before_save', array( $this, 'rh_save_customizer_options'));		
	}

	public function rh_customize_register( $wp_customize ) {

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

		/* THEME OPTIONS */
		$wp_customize->add_panel( 'panel_id', array(
			'priority' => 121,
			'title' => esc_html__('Theme Options', 'rehub-framework'),
			'description' => esc_html__('ReHub Control Center', 'rehub-framework'),
		));

		/* 
		 * APPEARANCE/COLOR
		*/
		$wp_customize->add_section( 'rh_styling_settings', array(
			'title' => esc_html__('Appearance/Color', 'rehub-framework'),
			'priority'  => 122,
			'panel' => 'panel_id',
		));

		//Width of site
		$wp_customize->add_setting('width_layout', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'regular',
		));
		$wp_customize->add_control('width_layout', array(
			'settings' => 'width_layout',
			'label' => esc_html__('Select Width Style', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'type' => 'select',
			'choices' => array(
				'regular' => esc_html__('Regular (1200px)', 'rehub-framework'),
				'extended' => esc_html__('Extended (1530px)', 'rehub-framework'),
				'compact' => esc_html__('Compact', 'rehub-framework'),
			),
		));	

		//Subset (old child themes)
		$wp_customize->add_setting('theme_subset', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'flat',
		));
		$wp_customize->add_control('theme_subset', array(
			'settings' => 'theme_subset',
			'label' => esc_html__('Select theme subset', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'type' => 'select',
			'choices' => array(
				'flat' => esc_html__('Clean Rehub', 'rehub-framework'),
				'redigit' => esc_html__('Redigit for digital products', 'rehub-framework'),
				'regame' => esc_html__('White search (Regame style)', 'rehub-framework'),
				'redeal' => esc_html__('Redeal (Big buttons in header)', 'rehub-framework'),
				'redirect' => esc_html__('Redirect (Full width header)', 'rehub-framework'),
				'rething' => esc_html__('Rething (big masonry grid)', 'rehub-framework'),
				'repick' => esc_html__('Repick (big grid items)', 'rehub-framework'),
				'recash' => esc_html__('Recash', 'rehub-framework'),
			),
		));			

		//Custom color schema
		$wp_customize->add_setting( 'rehub_custom_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => $maincolor,
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color', array(
			'label' => esc_html__('Main Highlight color', 'rehub-framework'),
			'description' => esc_html__('Color to highlight items', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_custom_color',
		)));

		//Custom secondary color
		$wp_customize->add_setting( 'rehub_sec_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => $secondarycolor,
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_sec_color', array(
			'label' => esc_html__('Secondary color', 'rehub-framework'),
			'description' => esc_html__('Color for system forms (for search buttons, tabs, etc)', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_sec_color',
		)));

		$wp_customize->add_setting( 'rehub_third_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_third_color', array(
			'label' => esc_html__('Background hightligh color', 'rehub-framework'),
			'description' => esc_html__('Color for background on extended layouts. Leave empty to use main Highlight color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_third_color',
		)));

		//Set offer buttons color
		$wp_customize->add_setting( 'rehub_btnoffer_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => $btncolor,
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_btnoffer_color', array(
			'label' => esc_html__('Set offer buttons color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_btnoffer_color',
		)));
		$wp_customize->add_setting( 'rehub_btnoffer_color_hover', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_btnoffer_color_hover', array(
			'label' => esc_html__('Set offer button hover color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_btnoffer_color_hover',
		)));
		$wp_customize->add_setting( 'rehub_btnoffer_color_text', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => $btncolortext,
		));	
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_btnoffer_color_text', array(
			'label' => esc_html__('Set offer button text color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_btnoffer_color_text',
		)));
		$wp_customize->add_setting( 'rehub_btnofferhover_color_text', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));					
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_btnofferhover_color_text', array(
			'label' => esc_html__('Set offer button text Hover color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_btnofferhover_color_text',
		)));
		//Custom color for links inside posts
		$wp_customize->add_setting( 'rehub_color_link', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_color_link', array(
			'label' => esc_html__('Custom color for links inside posts','rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_color_link',
		)));

		//Enable smooth design for inputs
		$wp_customize->add_setting( 'enable_smooth_btn', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '2',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'enable_smooth_btn', array(
			'label' => esc_html__('Enable smooth design for inputs?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'enable_smooth_btn',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('Rounded', 'rehub-framework'),
				'2' => esc_html__('Soft Rounded', 'rehub-framework'),
			),
		)));

		//Set sidebar to left side
		$wp_customize->add_setting( 'rehub_sidebar_left', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_sidebar_left', array(
			'label' => esc_html__('Set sidebar to left side?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'rehub_sidebar_left',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
				
		//Enable boxed version
		$wp_customize->add_setting( 'rehub_body_block', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_body_block', array(
			'label' => esc_html__('Enable boxed version?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'rehub_body_block',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
			
		//Disable box borders under content box
		$wp_customize->add_setting( 'rehub_content_shadow', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => $contentboxdisable,
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_content_shadow', array(
			'label' => esc_html__('Disable box borders under content box?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'rehub_content_shadow',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));

		$wp_customize->add_setting( 'dark_theme', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'dark_theme', array(
			'label' => esc_html__('Enable dark theme?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'dark_theme',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
				
		//Background Color
		$wp_customize->add_setting( 'rehub_color_background', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_color_background', array(
			'label' => esc_html__('Background Color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_color_background',
		)));
				
		//Background Image
		$wp_customize->add_setting( 'rehub_background_image', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_background_image', array(
			'label' => esc_html__('Background Image', 'rehub-framework'),
			'description' => esc_html__('Set background color before it', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_background_image',
		)));

		//Background Repeat
		$wp_customize->add_setting('rehub_background_repeat', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'repeat',
		));
		$wp_customize->add_control('rehub_background_repeat', array(
			'settings' => 'rehub_background_repeat',
			'label' => esc_html__('Background Repeat', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Repeat', 'rehub-framework'),
				'no-repeat' => esc_html__('No Repeat', 'rehub-framework'),
				'repeat-x' => esc_html__('Repeat X', 'rehub-framework'),
				'repeat-y' => esc_html__('Repeat Y', 'rehub-framework'),
			),
		));
			
		//Background Position
		$wp_customize->add_setting('rehub_background_position', array(
			'sanitize_callback' => 'sanitize_key',
		));
		$wp_customize->add_control('rehub_background_position', array(
			'settings' => 'rehub_background_position',
			'label' => esc_html__('Background Position', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Left', 'rehub-framework'),
				'center' => esc_html__('Center', 'rehub-framework'),
				'right' => esc_html__('Right', 'rehub-framework'),
			),
		));
			
			
		//Fixed Background Image
		$wp_customize->add_setting( 'rehub_background_fixed', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_background_fixed', array(
			'label' => esc_html__('Fixed Background Image?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'rehub_background_fixed',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
				
		//Url for branded background
	 	$wp_customize->add_setting('rehub_branded_bg_url', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_branded_bg_url', array(
			'label' => esc_html__('Url for branded background', 'rehub-framework'),
			'description' => esc_html__('Insert url that will be display on background', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_branded_bg_url',
			'type' => 'url',
		));

		/* 
		 * LOGO & FAVICON 
		 * Site Identity section
		*/
		
		//Upload Logo
		$wp_customize->add_setting( 'rehub_logo', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_logo', array(
			'label' => esc_html__('Upload Logo', 'rehub-framework'),
			'description' => esc_html__('Upload your logo. Max width is 450px. (1200px for full width, 180px for logo + menu row layout)', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_logo',
		)));
			
		//Retina Logo (no live preview)
		$wp_customize->add_setting( 'rehub_logo_retina', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_logo_retina', array(
			'label' => esc_html__('Upload Logo (retina version)', 'rehub-framework'),
			'description' => esc_html__('Upload retina version of the logo. It should be 2x the size of main logo.', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_logo_retina',
		)));
			
		//Logo width (no live preview)
		$wp_customize->add_setting('rehub_logo_retina_width', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_logo_retina_width', array(
			'label' => esc_html__('Logo width', 'rehub-framework'),
			'description' => esc_html__('Please, enter logo width (without px)', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_logo_retina_width',
			'type' => 'number',
		));
			
		//Logo width (no live preview)
		$wp_customize->add_setting('rehub_logo_retina_height', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_logo_retina_height', array(
			'label' => esc_html__('Retina logo height', 'rehub-framework'),
			'description' => esc_html__('Please, enter logo height (without px)', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_logo_retina_height',
			'type' => 'number',
		));
			
		//Text logo
		$wp_customize->add_setting('rehub_text_logo', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_text_logo', array(
			'label' => esc_html__('Text logo', 'rehub-framework'),
			'description' => esc_html__('You can type text logo. Use this field only if no image logo', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_text_logo',
		));
			
		//Slogan
		$wp_customize->add_setting('rehub_text_slogan', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_text_slogan', array(
			'label' => esc_html__('Slogan', 'rehub-framework'),
			'description' => esc_html__('You can type slogan below text logo. Use this field only if no image logo', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_text_slogan',
			'type' => 'textarea',
		));
			
		/* 
		 * HEADER AND MENU 
		*/
		$wp_customize->add_section( 'rh_header_settings', array(
			'title' => esc_html__('Header and Menu', 'rehub-framework'),
			'priority'  => 124,
			'panel' => 'panel_id',
		));

		//Select Header style
		$wp_customize->add_setting('rehub_header_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'header_seven'
		));
		$headerlayout = apply_filters( 'rehub_global_header_layout_array_customizer', array(
			'header_first' => esc_html__('Logo + code zone 468X60 + search box', 'rehub-framework'),
			'header_eight' => esc_html__('Logo + slogan + search box', 'rehub-framework'),
			'header_clean' => esc_html__('Clean code zone and regular menu', 'rehub-framework'),
			'header_fullclean' => esc_html__('Clean code zone and custom menu', 'rehub-framework'),
			'header_five' => esc_html__('Logo + menu in one row', 'rehub-framework'),
			'header_six' => esc_html__('Customizable header', 'rehub-framework'),
			'header_third' => esc_html__('Just Logo on center and code zone below', 'rehub-framework'),
			'header_seven' => esc_html__('Shop/Comparison header (logo + search + login + cart/compare icon)', 'rehub-framework'),											
		));
	
		$headers = get_posts(array(
			'post_type' => 'wp_block',
			'meta_key'   => '_rh_section_type',
			'meta_value' => 'header'
		 ));
	
		 if(!empty($headers)){
			 foreach($headers as $header){
				$headerlayout[$header->ID] = get_the_title($header->ID);
			 }
		 }

		$wp_customize->add_control('rehub_header_style', array(
			'type' => 'select',
			'settings' => 'rehub_header_style',
			'label' => esc_html__('Select Header style', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'choices' => $headerlayout
		));
			/* Subfields 'seven' header */
			//Enable Compare Icon
			$wp_customize->add_setting('header_seven_compare_btn', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '1'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_seven_compare_btn', array(
				'type' => 'radio',
				'settings' => 'header_seven_compare_btn',
				'label' => esc_html__('Enable Compare Icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			$wp_customize->add_setting('header_seven_compare_btn_label', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_seven_compare_btn_label', array(
				'type' => 'text',
				'settings' => 'header_seven_compare_btn_label',
				'label' => esc_html__('Label for compare icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));		
			//Enable Cart Icon
			$wp_customize->add_setting('header_seven_cart', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '1'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_seven_cart', array(
				'type' => 'radio',
				'settings' => 'header_seven_cart',
				'label' => esc_html__('Enable Cart Icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			$wp_customize->add_setting('header_seven_cart_as_btn', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_seven_cart_as_btn', array(
				'type' => 'radio',
				'settings' => 'header_seven_cart_as_btn',
				'label' => esc_html__('Enable Cart as button', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));	
			$wp_customize->add_setting('header_five_menucenter', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_five_menucenter', array(
				'type' => 'radio',
				'settings' => 'header_five_menucenter',
				'label' => esc_html__('Enable centered menu?', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));	
			//Enable Login Icon
			$wp_customize->add_setting('header_seven_login', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_seven_login', array(
				'type' => 'radio',
				'settings' => 'header_seven_login',
				'label' => esc_html__('Enable Login Icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			$wp_customize->add_setting('header_seven_login_label', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_seven_login_label', array(
				'type' => 'text',
				'settings' => 'header_seven_login_label',
				'label' => esc_html__('Label for login icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));			
			//Enable Wishlist Icon
			$wp_customize->add_setting('header_seven_wishlist', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_seven_wishlist', array(
				'type' => 'url',
				'settings' => 'header_seven_wishlist',
				'label' => esc_html__('Enable Wishlist Icon and set Url', 'rehub-framework'),
				'description' => esc_html__('Set url on your page where you have [rh_get_user_favorites] shortcode. All icons in header will be available also in mobile logo panel. We don\'t recommend to enable more than 2 icons with Mobile logo.', 'rehub-framework'),	
				'section' => 'rh_header_settings',
			));
			$wp_customize->add_setting('header_seven_wishlist_label', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_seven_wishlist_label', array(
				'type' => 'text',
				'settings' => 'header_seven_wishlist_label',
				'label' => esc_html__('Label for wishlist icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));			
			//Add additional element
			$wp_customize->add_setting('header_seven_more_element', array(
				'sanitize_callback' => 'wp_kses_post',
			));
			$wp_customize->add_control('header_seven_more_element', array(
				'type' => 'textarea',
				'settings' => 'header_seven_more_element',
				'label' => esc_html__('Add additional element (shortcodes and html supported)', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));
			
			/* Subfields 'six' header */
			//Enable login/register
			$wp_customize->add_setting('header_six_login', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_six_login', array(
				'type' => 'radio',
				'settings' => 'header_six_login',
				'label' => esc_html__('Enable login/register section', 'rehub-framework'),
				'description' => esc_html__('Also, login popup must be enabled in Theme option - User options', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			//Enable additional button
			$wp_customize->add_setting('header_six_btn', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_six_btn', array(
				'type' => 'radio',
				'settings' => 'header_six_btn',
				'label' => esc_html__('Enable additional button in header', 'rehub-framework'),
				'description' => esc_html__('This will add button in header', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			//Color style of button
			$wp_customize->add_setting('header_six_btn_color', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => 'green'
			));
			$wp_customize->add_control('header_six_btn_color', array(
				'type' => 'select',
				'settings' => 'header_six_btn_color',
				'label' => esc_html__('Choose color style of button', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'btncolor' => esc_html__('Main Color of Buttons', 'rehub-framework'),
					'secondary' => esc_html__('Secondary Theme Color', 'rehub-framework'),
					'main' => esc_html__('Main Theme Color', 'rehub-framework'),
					'green' => esc_html__('green', 'rehub-framework'),
					'orange' => esc_html__('orange', 'rehub-framework'),
					'red' => esc_html__('red', 'rehub-framework'),
					'blue' => esc_html__('blue', 'rehub-framework'),
					'black' => esc_html__('black', 'rehub-framework'),
					'rosy' => esc_html__('rosy', 'rehub-framework'),
					'brown' => esc_html__('brown', 'rehub-framework'),
					'pink' => esc_html__('pink', 'rehub-framework'),
					'purple' => esc_html__('purple', 'rehub-framework'),
					'gold' => esc_html__('gold', 'rehub-framework'),
				)
			));
			//Label for button
			$wp_customize->add_setting('header_six_btn_txt', array(
				'sanitize_callback' => 'wp_kses',
				'default' => esc_html__('Submit a deal', 'rehub-framework'),
			));
			$wp_customize->add_control('header_six_btn_txt', array(
				'settings' => 'header_six_btn_txt',
				'label' => esc_html__('Type label for button', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));
			//URL for button
			$wp_customize->add_setting('header_six_btn_url', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_six_btn_url', array(
				'type' => 'url',
				'settings' => 'header_six_btn_url',
				'label' => esc_html__('Type url for button', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));
			//Enable login popup
			$wp_customize->add_setting('header_six_btn_login', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_six_btn_login', array(
				'type' => 'radio',
				'settings' => 'header_six_btn_login',
				'label' => esc_html__('Enable login popup for non registered users', 'rehub-framework'),
				'description' => esc_html__('This will open popup if non registered user clicks on button. Also, login popup must be enabled in Theme option - User options', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			//Enable search form
			$wp_customize->add_setting('header_six_src', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_six_src', array(
				'type' => 'radio',
				'settings' => 'header_six_src',
				'label' => esc_html__('Enable search form in header', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			$wp_customize->add_setting('header_src_icon', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_src_icon', array(
				'type' => 'radio',
				'settings' => 'header_src_icon',
				'label' => esc_html__('Enable search icon in header', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			//Enable additional menu
			$wp_customize->add_setting('header_six_menu', array(
				'sanitize_callback' => 'sanitize_key',
			));
			$wp_customize->add_control('header_six_menu', array(
				'type' => 'select',
				'settings' => 'header_six_menu',
				'label' => esc_html__('Enable additional menu near logo', 'rehub-framework'),
				'description' => esc_html__('Use short menu with small number of items!!!', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => $this->rh_get_menus_customizer(),
			));		
			
		//Set padding from top and bottom
		$wp_customize->add_setting('rehub_logo_pad', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_logo_pad', array(
			'label' => esc_html__('Set padding from top and bottom', 'rehub-framework'),
			'description' => esc_html__('This will add custom padding from top and bottom for all custom elements in logo section. Default is 15', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_logo_pad',
			'type' => 'number',
		));
			
		//Sticky Menu Bar
		$wp_customize->add_setting( 'rehub_sticky_nav', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_sticky_nav', array(
			'label' => esc_html__('Sticky Menu Bar', 'rehub-framework'),
			'description' => esc_html__('Enable/Disable Sticky navigation bar.', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_sticky_nav',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
			//Upload Logo for sticky menu
			$wp_customize->add_setting( 'rehub_logo_sticky_url', array(
			'sanitize_callback' => 'esc_url_raw',
			));
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_logo_sticky_url', array(
				'label' => esc_html__('Upload Logo for sticky menu', 'rehub-framework'),
				'description' => esc_html__('Upload your logo. Max height is 40px.', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'settings' => 'rehub_logo_sticky_url',
			)));
			
		//Choose color style of header logo section
		$wp_customize->add_setting('header_logoline_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control('header_logoline_style', array(
			'settings' => 'header_logoline_style',
			'label' => esc_html__('Color style of header logo section', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'0' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'1' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));

		//Custom Background Color
		$wp_customize->add_setting( 'rehub_header_color_background', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_header_color_background', array(
			'label' => esc_html__('Custom Background Color', 'rehub-framework'),
			'description' => esc_html__('Choose the background color or leave blank for default', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_header_color_background',
		)));
			
		//Custom Background Image
		$wp_customize->add_setting( 'rehub_header_background_image', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_header_background_image', array(
			'label' => esc_html__('Custom Background Image', 'rehub-framework'),
			'description' => esc_html__('Upload a background image or leave blank', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_header_background_image',
		)));
			
		//Background Repeat
		$wp_customize->add_setting('rehub_header_background_repeat', array(
			'sanitize_callback' => 'sanitize_key',
		));
		$wp_customize->add_control('rehub_header_background_repeat', array(
			'settings' => 'rehub_header_background_repeat',
			'label' => esc_html__('Background Repeat', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Repeat', 'rehub-framework'),
				'no-repeat' => esc_html__('No Repeat', 'rehub-framework'),
				'repeat-x' => esc_html__('Repeat X', 'rehub-framework'),
				'repeat-y' => esc_html__('Repeat Y', 'rehub-framework'),
			),
		));
			
		//Background Position
		$wp_customize->add_setting('rehub_header_background_position', array(
			'sanitize_callback' => 'sanitize_key',
		));
		$wp_customize->add_control('rehub_header_background_position', array(
			'settings' => 'rehub_header_background_position',
			'label' => esc_html__('Background Position', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Left', 'rehub-framework'),
				'center' => esc_html__('Center', 'rehub-framework'),
				'right' => esc_html__('Right', 'rehub-framework'),
			),
		));
			
		//Choose color style of header menu section
		$wp_customize->add_setting('header_menuline_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control('header_menuline_style', array(
			'settings' => 'header_menuline_style',
			'label' => esc_html__('Color style of header menu section', 'rehub-framework'),	
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'0' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'1' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));
			
		//Choose type of font and padding
		$wp_customize->add_setting('header_menuline_type', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control('header_menuline_type', array(
			'settings' => 'header_menuline_type',
			'label' => esc_html__('Choose type of font and padding', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'0' => esc_html__('Middle size and padding', 'rehub-framework'),
				'1' => esc_html__('Compact size and padding', 'rehub-framework'),
				'2' => esc_html__('Big size and padding', 'rehub-framework'),
			),
		));
			
		//Add custom font size
		$wp_customize->add_setting('rehub_nav_font_custom', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_nav_font_custom', array(
			'label' => esc_html__('Add custom font size', 'rehub-framework'),
			'description' => esc_html__('Default is 15. Put just number', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_nav_font_custom',
			'type' => 'number',
		));

		//Enable uppercase font
		$wp_customize->add_setting( 'rehub_nav_font_upper', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_nav_font_upper', array(
			'label' => esc_html__('Enable uppercase font?', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_nav_font_upper',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Enable Light font weight
		$wp_customize->add_setting( 'rehub_nav_font_light', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '1',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_nav_font_light', array(
			'label' => esc_html__('Enable Light font weight?', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_nav_font_light',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Disable border of items
		$wp_customize->add_setting( 'rehub_nav_font_border', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_nav_font_border', array(
			'label' => esc_html__('Disable border of items?', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_nav_font_border',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Menu shadow
		$wp_customize->add_setting( 'rehub_enable_menu_shadow', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_enable_menu_shadow', array(
			'label' => esc_html__('Menu shadow', 'rehub-framework'),
			'description' => esc_html__('Enable/Disable shadow under menu', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_enable_menu_shadow',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Custom color of menu background
		$wp_customize->add_setting( 'rehub_custom_color_nav', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color_nav', array(
			'label' => esc_html__('Custom color of menu background', 'rehub-framework'),
			'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_custom_color_nav',
		)));
		
		//Custom color of menu font
		$wp_customize->add_setting( 'rehub_custom_color_nav_font', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color_nav_font', array(
			'label' => esc_html__('Custom color of menu font', 'rehub-framework'),
			'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_custom_color_nav_font',
		)));
		
		//Enablee top line
		$wp_customize->add_setting( 'rehub_header_top_enable', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_header_top_enable', array(
			'label' => esc_html__('Enable top line', 'rehub-framework'),
			'description' => esc_html__('You can enable top line', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_header_top_enable',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Choose color style of header top line
		$wp_customize->add_setting('header_topline_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control('header_topline_style', array(
			'settings' => 'header_topline_style',
			'label' => esc_html__('Choose color style of header top line', 'rehub-framework'),	
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'0' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'1' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));

		//Custom color for top line of header
		$wp_customize->add_setting( 'rehub_custom_color_top', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color_top', array(
			'label' => esc_html__('Custom color for top line of header', 'rehub-framework'),
			'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_custom_color_top',
		)));
		
		//Custom color of menu font for top line of header
		$wp_customize->add_setting( 'rehub_custom_color_top_font', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color_top_font', array(
			'label' => esc_html__('Custom color of menu font for top line of header', 'rehub-framework'),
			'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_custom_color_top_font',
		)));

		$wp_customize->add_setting('rehub_top_line_content', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_top_line_content', array(
			'label' => esc_html__('Top line content', 'rehub-framework'),
			'description' => esc_html__('Add custom content to top line', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_top_line_content',
			'type' => 'textarea',
		));

		/* 
		 * Mobile Options
		*/
		$wp_customize->add_section( 'rh_mobile_settings', array(
			'title' => esc_html__('Mobile Options', 'rehub-framework'),
			'priority'  => 125,
			'panel' => 'panel_id',
		));

		$wp_customize->add_setting( 'rehub_logo_inmenu_url', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_logo_inmenu_url', array(
			'label' => esc_html__('Upload Logo for mobiles', 'rehub-framework'),
			'description' => esc_html__('Upload your logo. Max height is 40px.', 'rehub-framework'),
			'section' => 'rh_mobile_settings',
			'settings' => 'rehub_logo_inmenu_url',
		)));

		$wp_customize->add_setting( 'rehub_mobile_header_bg', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_mobile_header_bg', array(
			'label' => esc_html__('Mobile header background', 'rehub-framework'),
			'description' => esc_html__('Leave blank to use colors of menu', 'rehub-framework'),
			'section' => 'rh_mobile_settings',
			'settings' => 'rehub_mobile_header_bg',
		)));

		$wp_customize->add_setting( 'rehub_mobile_header_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_mobile_header_color', array(
			'label' => esc_html__('Mobile header link color', 'rehub-framework'),
			'description' => esc_html__('Leave blank to use colors of menu', 'rehub-framework'),
			'section' => 'rh_mobile_settings',
			'settings' => 'rehub_mobile_header_color',
		)));

		$wp_customize->add_setting( 'rehub_mobtool_bg', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_mobtool_bg', array(
			'label' => esc_html__('Mobile Toolbar background', 'rehub-framework'),
			'description' => esc_html__('Toolbar is visible if you have more than 2 additional icons in header', 'rehub-framework'),
			'section' => 'rh_mobile_settings',
			'settings' => 'rehub_mobtool_bg',
		)));

		$wp_customize->add_setting( 'rehub_mobtool_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_mobtool_color', array(
			'label' => esc_html__('Mobile Toolbar link color', 'rehub-framework'),
			'section' => 'rh_mobile_settings',
			'settings' => 'rehub_mobtool_color',
		)));
		$wp_customize->add_setting( 'rehub_mobtool_top', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_mobtool_top', array(
			'label' => esc_html__('Set mobile toolbar to top', 'rehub-framework'),
			'section'  => 'rh_mobile_settings',
			'settings' => 'rehub_mobtool_top',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'rehub_mobtool_force', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_mobtool_force', array(
			'label' => esc_html__('Force mobile toolbar', 'rehub-framework'),
			'description' => esc_html__('By default, icon toolbar is generated if you have 3 elements or more in header, but you can enable this option to force it', 'rehub-framework'),
			'section'  => 'rh_mobile_settings',
			'settings' => 'rehub_mobtool_force',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'logo_mobilesliding', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'logo_mobilesliding', array(
			'label' => esc_html__('Enable logo in sliding mobile panel', 'rehub-framework'),
			'section' => 'rh_mobile_settings',
			'settings' => 'logo_mobilesliding',
		)));

		$wp_customize->add_setting( 'color_mobilesliding', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'color_mobilesliding', array(
			'label' => esc_html__('Background color under logo', 'rehub-framework'),
			'description' => esc_html__('Background color under logo in Sliding panel', 'rehub-framework'),
			'section' => 'rh_mobile_settings',
			'settings' => 'color_mobilesliding',
		)));
		
		/* 
		 * FOOTER OPTIONS
		*/
		$wp_customize->add_section( 'rh_footer_settings', array(
			'title' => esc_html__('Footer Options', 'rehub-framework'),
			'priority'  => 126,
			'panel' => 'panel_id',
		));

		$footerlayout = array('no'=> esc_html__('No', 'rehub-framework'));
		$footers = get_posts(array(
			'post_type' => 'wp_block',
			'meta_key'   => '_rh_section_type',
			'meta_value' => 'footer'
		 ));
	
		if(!empty($footers)){
			foreach($footers as $footer){
				$footerlayout[$footer->ID] = get_the_title($footer->ID);
			}
		}
		if(!empty($footerlayout)){
			$wp_customize->add_setting('footer_template', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => ''
			));
			
	
			$wp_customize->add_control('footer_template', array(
				'type' => 'select',
				'settings' => 'footer_template',
				'label' => esc_html__('Use Custom Footer template', 'rehub-framework'),
				'description' => esc_html__('You can create custom template in Reusable template section', 'rehub-framework'),
				'section' => 'rh_footer_settings',
				'choices' => $footerlayout
			));
		}

		
		// Footer Widgets
		$wp_customize->add_setting( 'rehub_footer_widgets', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '1',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_footer_widgets', array(
			'label' => esc_html__('Footer Widgets', 'rehub-framework'),
			'description' => esc_html__('Enable or Disable the footer widget area', 'rehub-framework'),
			'section'  => 'rh_footer_settings',
			'settings' => 'rehub_footer_widgets',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		// Choose color style - widget section
		$wp_customize->add_setting('footer_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		)); 
		$wp_customize->add_control('footer_style', array(
			'label' => esc_html__('Choose color style of footer widget section', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_style',
			'type' => 'select',
			'choices' => array(
				'1' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'0' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));

		// Background Color
		$wp_customize->add_setting( 'footer_color_background', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_color_background', array(
			'label' => esc_html__('Custom Background Color', 'rehub-framework'),
			'description' => esc_html__('Choose the background color or leave blank for default', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_color_background',
		)));
		
		//Background Image
		$wp_customize->add_setting( 'footer_background_image', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'footer_background_image', array(
			'label' => esc_html__('Custom Background Image', 'rehub-framework'),
			'description' => esc_html__('Upload a background image or leave blank', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_background_image',
		)));
		
		//Background Repeat
		$wp_customize->add_setting('footer_background_repeat', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'repeat',
		));
		$wp_customize->add_control('footer_background_repeat', array(
			'label' => esc_html__('Background Repeat', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_background_repeat',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Repeat', 'rehub-framework'),
				'no-repeat' => esc_html__('No Repeat', 'rehub-framework'),
				'repeat-x' => esc_html__('Repeat X', 'rehub-framework'),
				'repeat-y' => esc_html__('Repeat Y', 'rehub-framework'),
			),
		));
		
		//Background Position
		$wp_customize->add_setting('footer_background_position', array(
			'sanitize_callback' => 'sanitize_key',
		));
		$wp_customize->add_control('footer_background_position', array(
			'label' => esc_html__('Background Position', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_background_position',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Left', 'rehub-framework'),
				'center' => esc_html__('Center', 'rehub-framework'),
				'right' => esc_html__('Right', 'rehub-framework'),
			),
		));
		
		// Choose color style - bottom section
		$wp_customize->add_setting('footer_style_bottom', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		)); 
		$wp_customize->add_control('footer_style_bottom', array(
			'label' => esc_html__('Choose color style of bottom section', 'rehub-framework'),	
			'section' => 'rh_footer_settings',
			'settings' => 'footer_style_bottom',
			'type' => 'select',
			'choices' => array(
				'1' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'0' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));
		
		// Footer Bottom Text
		$wp_customize->add_setting('rehub_footer_text', array(
			'sanitize_callback' => 'wp_kses_post',
			'default' => esc_html__('2018 Wpsoul.com Design. All rights reserved.', 'rehub-framework'),
		)); 
		$wp_customize->add_control('rehub_footer_text', array(
			'label' => esc_html__('Footer Bottom Text', 'rehub-framework'),
			'description' => esc_html__('Enter your copyright text or whatever you want right here.', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'rehub_footer_text',
			'type' => 'textarea',
		));
		
		// Logo for footer
		$wp_customize->add_setting( 'rehub_footer_logo', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_footer_logo', array(
			'label' => esc_html__('Upload Logo for footer', 'rehub-framework'),
			'description' => esc_html__('Upload your logo for footer.', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'rehub_footer_logo',
		)));

		/* 
		 * Shop settings
		*/
		$wp_customize->add_section( 'rh_shop_settings', array(
			'title' => esc_html__('Woocommerce Archive settings', 'rehub-framework'),
			'priority'  => 127,
			'panel' => 'panel_id',
		));

		$productarchive_layouts = apply_filters( 'rehub_productarchive_layout_array', array(
			'3_col' => esc_html__('As 3 columns with sidebar', 'rehub-framework'),
			'4_col' => esc_html__('As 4 columns full width', 'rehub-framework'),
			'4_col_side' => esc_html__('As 4 columns + sidebar', 'rehub-framework'),
			'5_col_side' => esc_html__('As 5 columns + sidebar', 'rehub-framework'),						
			)
		);
		$productarchivelayouts = get_posts(array(
			'post_type' => 'wp_block',
			'meta_key'   => '_rh_section_type',
			'meta_value' => 'wooarchive'
		));
	
		if(!empty($productarchivelayouts)){
			foreach($productarchivelayouts as $layout){
				$productarchive_layouts[$layout->ID] = get_the_title($layout->ID);
			}
		}

		$wp_customize->add_setting('woo_columns', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '3_col',
		));
		$wp_customize->add_control('woo_columns', array(
			'settings' => 'woo_columns',
			'label' => esc_html__('How to show archives', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'type' => 'select',
			'choices' => $productarchive_layouts,
		));	
		$wp_customize->add_setting( 'rehub_sidebar_left_shop', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_sidebar_left_shop', array(
			'label' => esc_html__('Set sidebar to left side?', 'rehub-framework'),
			'section'  => 'rh_shop_settings',
			'settings' => 'rehub_sidebar_left_shop',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));				
		$wp_customize->add_setting('woo_design', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'simple',
		));
		$wp_customize->add_control('woo_design', array(
			'settings' => 'woo_design',
			'label' => esc_html__('Set design of woo archive', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'type' => 'select',
			'choices' => array(
				'simple' => esc_html__('Columns', 'rehub-framework'),
				'grid' => esc_html__('Grid', 'rehub-framework'),
				'gridmart' => esc_html__('Market Grid', 'rehub-framework'),
				'gridtwo' => esc_html__('Compact Grid', 'rehub-framework'),
				'gridrev' => esc_html__('Directory Grid', 'rehub-framework'),
				'griddigi' => esc_html__('Digital Grid', 'rehub-framework'),
				'list' => esc_html__('List', 'rehub-framework'),
                'dealwhite' => esc_html__( 'Deal Grid', 'rehub-theme' ),
                'dealdark' => esc_html__( 'Deal Grid Dark', 'rehub-theme' ),
				'deallist' => esc_html__('Deal List', 'rehub-framework'),
				'compactlist' => esc_html__('Wholesale List', 'rehub-framework'),
			),
		));	
		$wp_customize->add_setting( 'woo_compact_loop_btn', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woo_compact_loop_btn', array(
			'label' => esc_html__('Enable button in compact grid and directory grid', 'rehub-framework'),
			'section'  => 'rh_shop_settings',
			'settings' => 'woo_compact_loop_btn',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'woo_wholesale', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woo_wholesale', array(
			'label' => esc_html__('Enable quantity near button', 'rehub-framework'),
			'description' => esc_html__('Enable also ajax add to cart option in Woocommerce - settings - products and reload page', 'rehub-framework'),	
			'section'  => 'rh_shop_settings',
			'settings' => 'woo_wholesale',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'woo_quick_view', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woo_quick_view', array(
			'label' => esc_html__('Enable quick view', 'rehub-framework'),
			'section'  => 'rh_shop_settings',
			'settings' => 'woo_quick_view',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'woo_aff_btn', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woo_aff_btn', array(
			'label' => esc_html__('Enable affiliate links instead inner?', 'rehub-framework'),
			'section'  => 'rh_shop_settings',
			'settings' => 'woo_aff_btn',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'disable_woo_scripts', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'disable_woo_scripts', array(
			'label' => esc_html__('Disable Woocommerce Cart scripts', 'rehub-framework'),
			'section'  => 'rh_shop_settings',
			'settings' => 'disable_woo_scripts',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting('price_meta_woogrid', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '2',
		));
		$wp_customize->add_control('price_meta_woogrid', array(
			'settings' => 'price_meta_woogrid',
			'label' => esc_html__('Show in price area of deal grid layouts', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'type' => 'select',
			'choices' => array(
				'1' => esc_html__('Content Egg synchronized offer', 'rehub-framework'),
				'2' => esc_html__('Brand logo', 'rehub-framework'),
				'3' => esc_html__('Discount', 'rehub-framework'),
				'4' => esc_html__('Nothing', 'rehub-framework'),
			),
		));	
		$wp_customize->add_setting('woo_number', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '12',
		));
		$wp_customize->add_control('woo_number', array(
			'settings' => 'woo_number',
			'label' => esc_html__('Set count of products in loop', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'type' => 'select',
			'choices' => array(
				'12' => '12',
				'16' => '16',
				'20' => '20',
				'24' => '24',
				'30' => '30',
				'36' => '36',
			),
		));		

		$wp_customize->add_setting( 'wooloop_heading_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => '',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wooloop_heading_color', array(
			'label' => esc_html__('Headings color', 'rehub-framework'),
			'description' => esc_html__('You can set Button color in Theme options - Apearance - Offer Button color', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_heading_color',
		)));	

		$wp_customize->add_setting( 'wooloop_price_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => '',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wooloop_price_color', array(
			'label' => esc_html__('Price color', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_price_color',
		)));

		$wp_customize->add_setting( 'wooloop_sale_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => '',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wooloop_sale_color', array(
			'label' => esc_html__('Sale tag color', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_sale_color',
		)));

		$wp_customize->add_setting('wooloop_heading_size', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('wooloop_heading_size', array(
			'label' => esc_html__('Heading Font size', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_heading_size',
			'type' => 'number',
		));		

		$wp_customize->add_setting('wooloop_price_size', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('wooloop_price_size', array(
			'label' => esc_html__('Price Font size', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_price_size',
			'type' => 'number',
		));	

		$wp_customize->add_section( 'rh_woo_custom_settings', array(
			'title' => esc_html__('Woocommerce Custom Areas', 'rehub-framework'),
			'priority'  => 128,
			'panel' => 'panel_id',
		));	

		$wp_customize->add_setting('woo_code_zone_loop', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_loop', array(
			'label' => esc_html__('Code zone inside product loop', 'rehub-framework'),
			'description' => esc_html__('This code zone is visible on shop pages inside each product item.', 'rehub-framework').' <a href="https://rehubdocs.wpsoul.com/docs/rehub-theme/shop-options-woo-edd/extended-custom-product-areas/" target="_blank">Read more about code zones</a>',
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_loop',
			'type' => 'textarea',
		));	
		
		$wp_customize->add_setting('woo_code_zone_button', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_button', array(
			'label' => esc_html__('After Button Area', 'rehub-framework'),
			'description' => esc_html__('This code zone is visible on all products after Add to cart Button', 'rehub-framework'),
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_button',
			'type' => 'textarea',
		));	
		$wp_customize->add_setting('woo_code_zone_content', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_content', array(
			'label' => esc_html__('Before Content', 'rehub-framework'),
			'description' => esc_html__('This code zone is visible on all products before Content', 'rehub-framework'),
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_content',
			'type' => 'textarea',
		));	
		$wp_customize->add_setting('woo_code_zone_footer', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_footer', array(
			'label' => esc_html__('Before footer', 'rehub-framework'),
			'description' => esc_html__('This code zone is visible on all products Before Footer', 'rehub-framework'),
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_footer',
			'type' => 'textarea',
		));	
		$wp_customize->add_setting('woo_code_zone_float', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_float', array(
			'label' => esc_html__('In floating panel', 'rehub-framework'),
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_float',
			'type' => 'textarea',
		));

		$wp_customize->add_section( 'rh_woo_global_settings', array(
			'title' => esc_html__('Woocommerce Global Settings', 'rehub-framework'),
			'priority'  => 129,
			'panel' => 'panel_id',
		));
		$product_layouts = apply_filters( 'rehub_product_layout_array', array(
			'default_with_sidebar' => esc_html__('Default with sidebar', 'rehub-framework'), 
			'default_full_width' => esc_html__('Default full width 2 column', 'rehub-framework'),
			'default_no_sidebar' => esc_html__('Default full width 3 column', 'rehub-framework'),
			'full_width_extended' => esc_html__('Full width Extended', 'rehub-framework'),
			'full_width_advanced' => esc_html__('Full width Advanced', 'rehub-framework'),
			'marketplace' => esc_html__('Full Width Marketplace', 'rehub-framework'),
			'side_block' => esc_html__('Side Block', 'rehub-framework'),
			'side_block_light' => esc_html__('Side Block Light', 'rehub-framework'),
			'side_block_video' => esc_html__('Video Block', 'rehub-framework'),
			'sections_w_sidebar' => esc_html__('Sections with sidebar', 'rehub-framework'),
			'ce_woo_list' => esc_html__('Content Egg List', 'rehub-framework'),
			'ce_woo_sections' => esc_html__('Content Egg Auto Sections', 'rehub-framework'),
			'ce_woo_blocks' => esc_html__('Review with Blocks', 'rehub-framework'),			
			'vendor_woo_list' => esc_html__('Compare Prices with shortcode', 'rehub-framework'),
			'compare_woo_list' => esc_html__('Compare Prices by sku', 'rehub-framework'),			
			'full_photo_booking' => esc_html__('Full width Photo', 'rehub-framework'),
			'woo_compact' => esc_html__('Compact Style', 'rehub-framework'),
			'woo_directory' => esc_html__('Directory Style', 'rehub-framework'),
			'darkwoo' => esc_html__('Dark Layout', 'rehub-framework'),
			'woostack' => esc_html__('Photo Stack Layout', 'rehub-framework'),							
			)
		);
		$productlayouts = get_posts(array(
			'post_type' => 'wp_block',
			'meta_key'   => '_rh_section_type',
			'meta_value' => 'woosingle'
		));
	
		if(!empty($productlayouts)){
			foreach($productlayouts as $layout){
				$product_layouts[$layout->ID] = get_the_title($layout->ID);
			}
		}
		$wp_customize->add_setting('product_layout_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'default_full_width',
		));
		$wp_customize->add_control('product_layout_style', array(
			'settings' => 'product_layout_style',
			'label' => esc_html__('Product layout', 'rehub-framework'),
			'section' => 'rh_woo_global_settings',
			'type' => 'select',
			'choices' => $product_layouts,
		));	
		
		$wp_customize->add_section( 'rh_post_global_settings', array(
			'title' => esc_html__('Post Global Settings', 'rehub-framework'),
			'priority'  => 130,
			'panel' => 'panel_id',
		));
		$postlayouts = apply_filters( 'rehub_post_layouts_array', array(
			'default'=> esc_html__('Simple', 'rehub-framework'),
			'default_full_opt'=> esc_html__('Optimized Full width', 'rehub-framework'),
			'meta_outside'=> esc_html__('Title is outside content', 'rehub-framework'),
			'guten_auto'=> esc_html__('Gutenberg Auto Contents', 'rehub-framework'),
			'gutencustom'=> esc_html__('Customizable Full width', 'rehub-framework'),
			'default_text_opt'=> esc_html__('Optimized for reading with sidebar', 'rehub-framework'),
			'video_block'=> esc_html__('Video Block', 'rehub-framework'),
			'meta_center'=> esc_html__('Center aligned (Rething style)', 'rehub-framework'),
			'meta_compact'=> esc_html__('Compact (Button Block Under Title)', 'rehub-framework'),
			'meta_compact_dir'=> esc_html__('Compact (Button Block Before Title)', 'rehub-framework'),
			'corner_offer'=> esc_html__('Button in corner (Repick style)', 'rehub-framework'),
			'meta_in_image'=> esc_html__('Title Inside image', 'rehub-framework'),
			'meta_in_imagefull'=> esc_html__('Title Inside full image', 'rehub-framework'),
			'big_post_offer'=> esc_html__('Big post offer block in top', 'rehub-framework'),
			'offer_and_review'=> esc_html__('Offer and review score', 'rehub-framework'),
		));
		$wp_customize->add_setting('post_layout_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'default',
		));
		$wp_customize->add_control('post_layout_style', array(
			'settings' => 'post_layout_style',
			'label' => esc_html__('Post layout', 'rehub-framework'),
			'section' => 'rh_post_global_settings',
			'type' => 'select',
			'choices' => $postlayouts,
		));	
		$archivelayouts = apply_filters( 'rehub_archive_layouts_array', array(
			'communitylist'=> esc_html__('Community List', 'rehub-framework'),
			'blog'=> esc_html__('Blog Layout', 'rehub-framework'),
			'newslist'=> esc_html__('Simple News List', 'rehub-framework'),
			'deallist'=> esc_html__('Deal List', 'rehub-framework'),
			'grid'=> esc_html__('Masonry Grid layout', 'rehub-framework'),
			'columngrid'=> esc_html__('Equal height Grid layout', 'rehub-framework'),
			'compactgrid'=> esc_html__('Compact deal grid layout', 'rehub-framework'),
			'dealgrid'=> esc_html__('Deal Grid layout', 'rehub-framework'),
			'cardblog'=> esc_html__('Cards', 'rehub-framework'),
			'dealgridfull'=> esc_html__('Full width Deal Grid layout', 'rehub-framework'),
			'compactgridfull'=> esc_html__('Full width compact deal grid layout', 'rehub-framework'),
			'columngridfull'=> esc_html__('Equal height Full width Grid layout', 'rehub-framework'),
			'gridfull'=> esc_html__('Full width Masonry Grid layout', 'rehub-framework'),
			'cardblogfull'=> esc_html__('Cards Full width', 'rehub-framework'),
		));
		$wp_customize->add_setting('archive_layout', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'communitylist',
		));
		$wp_customize->add_control('archive_layout', array(
			'settings' => 'archive_layout',
			'label' => esc_html__('Select Archive Layout', 'rehub-framework'),
			'section' => 'rh_post_global_settings',
			'type' => 'select',
			'choices' => $archivelayouts,
		));	
		$searchlayouts = apply_filters( 'rehub_search_layouts_array', array(
			'communitylist'=> esc_html__('Community List', 'rehub-framework'),
			'blog'=> esc_html__('Blog Layout', 'rehub-framework'),
			'newslist'=> esc_html__('Simple News List', 'rehub-framework'),
			'deallist'=> esc_html__('Deal List', 'rehub-framework'),
			'grid'=> esc_html__('Masonry Grid layout', 'rehub-framework'),
			'columngrid'=> esc_html__('Equal height Grid layout', 'rehub-framework'),
			'compactgrid'=> esc_html__('Compact deal grid layout', 'rehub-framework'),
			'dealgrid'=> esc_html__('Deal Grid layout', 'rehub-framework'),
			'cardblog'=> esc_html__('Cards', 'rehub-framework'),
			'dealgridfull'=> esc_html__('Full width Deal Grid layout', 'rehub-framework'),
			'compactgridfull'=> esc_html__('Full width compact deal grid layout', 'rehub-framework'),
			'columngridfull'=> esc_html__('Equal height Full width Grid layout', 'rehub-framework'),
			'gridfull'=> esc_html__('Full width Masonry Grid layout', 'rehub-framework'),
			'cardblogfull'=> esc_html__('Cards Full width', 'rehub-framework'),
		));
		$wp_customize->add_setting('search_layout', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'communitylist',
		));
		$wp_customize->add_control('search_layout', array(
			'settings' => 'search_layout',
			'label' => esc_html__('Select Search Layout', 'rehub-framework'),
			'section' => 'rh_post_global_settings',
			'type' => 'select',
			'choices' => $searchlayouts,
		));	

		/* Global Enable/Disable */
		$wp_customize->add_section( 'rh_global_settings', array(
			'title' => esc_html__('Global Enable/Disable', 'rehub-framework'),
			'priority'  => 131,
			'panel' => 'panel_id',
		));
		$wp_customize->add_setting( 'exclude_author_meta', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'exclude_author_meta', array(
			'label' => esc_html__('Disable author link', 'rehub-framework'),
			'description' => esc_html__('Disable author link from meta in string', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'exclude_author_meta',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'exclude_cat_meta', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'exclude_cat_meta', array(
			'label' => esc_html__('Disable category link', 'rehub-framework'),
			'description' => esc_html__('Disable category link from meta in string', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'exclude_cat_meta',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'exclude_date_meta', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'exclude_date_meta', array(
			'label' => esc_html__('Disable date', 'rehub-framework'),
			'description' => esc_html__('Disable date from meta in string', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'exclude_date_meta',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'exclude_comments_meta', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'exclude_comments_meta', array(
			'label' => esc_html__('Disable comments count', 'rehub-framework'),
			'description' => esc_html__('Disable comments count from meta in string', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'exclude_comments_meta',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'hotmeter_disable', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'hotmeter_disable', array(
			'label' => esc_html__('Disable hot and thumb metter', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'hotmeter_disable',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'wishlist_disable', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'wishlist_disable', array(
			'label' => esc_html__('Disable wishlist', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'wishlist_disable',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'wish_cache_enabled', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'wish_cache_enabled', array(
			'label' => esc_html__('Wishlist Button Support for Cache plugins', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'wish_cache_enabled',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'thumb_only_users', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'thumb_only_users', array(
			'label' => esc_html__('Allow to use hot and thumbs only for logged users', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'thumb_only_users',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'wish_only_users', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'wish_only_users', array(
			'label' => esc_html__('Allow to use wishlist only for logged users', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'wish_only_users',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'post_view_disable', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'post_view_disable', array(
			'label' => esc_html__('Disable post view script', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'post_view_disable',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'date_publish', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'date_publish', array(
			'label' => esc_html__('Enable to show Date of publishing as date meta', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'date_publish',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('No', 'rehub-framework'),
				'1' => esc_html__('Yes', 'rehub-framework'),
			),
		)));
		
		// Single Page
		$wp_customize->add_setting('global_single_divider');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'global_single_divider', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('Single pages', 'rehub-framework'),
			'description' => esc_html__('Global disabling parts on single pages', 'rehub-framework'),
			'section' => 'rh_global_settings',
			'settings' => 'global_single_divider',
		)));
		$wp_customize->add_setting( 'rehub_disable_breadcrumbs', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_disable_breadcrumbs', array(
			'label' => esc_html__('Disable breadcrumbs', 'rehub-framework'),
			'description' => esc_html__('Disable breadcrumbs from pages', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'rehub_disable_breadcrumbs',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'rehub_disable_share', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_disable_share', array(
			'label' => esc_html__('Disable share buttons', 'rehub-framework'),
			'description' => esc_html__('Disable share buttons after content on pages', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'rehub_disable_share',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'rehub_disable_share_top', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_disable_share_top', array(
			'label' => esc_html__('Disable share buttons', 'rehub-framework'),
			'description' => esc_html__('Disable share buttons before content on pages', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'rehub_disable_share_top',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'rehub_disable_prev', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_disable_prev', array(
			'label' => esc_html__('Disable previous and next', 'rehub-framework'),
			'description' => esc_html__('Disable previous and next post buttons', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'rehub_disable_prev',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'rehub_disable_tags', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_disable_tags', array(
			'label' => esc_html__('Disable tags', 'rehub-framework'),
			'description' => esc_html__('Disable tags after content from pages', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'rehub_disable_tags',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'rehub_disable_author', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_disable_author', array(
			'label' => esc_html__('Disable author box', 'rehub-framework'),
			'description' => esc_html__('Disable author box after content from pages', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'rehub_disable_author',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'rehub_disable_relative', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_disable_relative', array(
			'label' => esc_html__('Disable relative posts', 'rehub-framework'),
			'description' => esc_html__('Disable relative posts box after content from pages', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'rehub_disable_relative',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'crop_dis_related', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'crop_dis_related', array(
			'label' => esc_html__('Disable crop in related post images', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'crop_dis_related',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'rehub_disable_feature_thumb', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_disable_feature_thumb', array(
			'label' => esc_html__('Disable top thumbnail on single page', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'rehub_disable_feature_thumb',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'disable_post_sidebar', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'disable_post_sidebar', array(
			'label' => esc_html__('Disable sidebar on posts', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'disable_post_sidebar',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'rehub_disable_comments', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_disable_comments', array(
			'label' => esc_html__('Disable standart comments', 'rehub-framework'),
			'section'  => 'rh_global_settings',
			'settings' => 'rehub_disable_comments',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		/* Ads and Code Zones section */
		$wp_customize->add_section( 'rh_ads_settings', array(
			'title' => esc_html__('Ads and Code Zones', 'rehub-framework'),
			'priority'  => 132,
			'panel' => 'panel_id',
		));
		$wp_customize->add_setting('rehub_ads_top', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_ads_top', array(
			'label' => esc_html__('Header area', 'rehub-framework'),
			'description' => esc_html__('This banner code will be visible in header. Width of this zone depends on style of header (You can choose it in Header and menu tab)', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'rehub_ads_top',
			'type' => 'textarea',
		));
		$wp_customize->add_setting('rehub_ads_megatop', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_ads_megatop', array(
			'label' => esc_html__('Before header area', 'rehub-framework'),
			'description' => esc_html__('This banner code will be visible before header.', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'rehub_ads_megatop',
			'type' => 'textarea',
		));
		$wp_customize->add_setting('rehub_ads_infooter', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_ads_infooter', array(
			'label' => esc_html__('Before footer area', 'rehub-framework'),
			'description' => esc_html__('This banner code will be visible before footer', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'rehub_ads_infooter',
			'type' => 'textarea',
		));
		$wp_customize->add_setting('single_ads_divider');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'single_ads_divider', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('Global code for single page', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'single_ads_divider',
		)));
		$wp_customize->add_setting('rehub_single_after_title', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_single_after_title', array(
			'label' => esc_html__('After title area', 'rehub-framework'),
			'description' => esc_html__('This code will be visible after title', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'rehub_single_after_title',
			'type' => 'textarea',
		));
		$wp_customize->add_setting('rehub_single_before_post', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_single_before_post', array(
			'label' => esc_html__('Before content area', 'rehub-framework'),
			'description' => esc_html__('This code will be visible before post content', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'rehub_single_before_post',
			'type' => 'textarea',
		));
		$wp_customize->add_setting('rehub_single_before_post_note');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'rehub_single_before_post_note', array(
			'caption' => esc_html__('Tip', 'rehub-framework'),
			'icon' => '<i class="rhicon rhi-info-circle"></i>',
			'description' => esc_html__('You can wrap your code with &lt;div class=&quot;floatright ml15&quot;&gt;your ads code&lt;/div&gt; if you want to add right float or &lt;div class=&quot;floatleft mr15&quot;&gt;your ads code&lt;/div&gt; for left float. Please, use square ads with width 250-300px for floated ads.', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'rehub_single_before_post_note',
		)));
		$wp_customize->add_setting('rehub_single_code', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_single_code', array(
			'label' => esc_html__('After post area', 'rehub-framework'),
			'description' => esc_html__('This code will be visible after post', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'rehub_single_code',
			'type' => 'textarea',
		));
		$wp_customize->add_setting('rehub_ads_coupon_area', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_ads_coupon_area', array(
			'label' => esc_html__('Coupon area', 'rehub-framework'),
			'description' => esc_html__('This banner code will be visible in coupon modal', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'rehub_ads_coupon_area',
			'type' => 'textarea',
		));
		$wp_customize->add_setting('branded_ads_divider');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'branded_ads_divider', array(
			'caption' => esc_html__('Global branded area', 'rehub-framework'),
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'section' => 'rh_ads_settings',
			'settings' => 'branded_ads_divider',
		)));
		$wp_customize->add_setting('rehub_branded_banner_image', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_branded_banner_image', array(
			'label' => esc_html__('Branded area (after menu)', 'rehub-framework'),
			'description' => esc_html__('Set any custom code or link to image', 'rehub-framework'),
			'section' => 'rh_ads_settings',
			'settings' => 'rehub_branded_banner_image',
			'type' => 'textarea',
		));

		/* User options */
		$wp_customize->add_section( 'rh_user_settings', array(
			'title' => esc_html__('User Options', 'rehub-framework'),
			'priority'  => 133,
			'panel' => 'panel_id',
		));
		$wp_customize->add_setting('custom_msg_popup', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('custom_msg_popup', array(
			'label' => esc_html__('Branded area (after menu)', 'rehub-framework'),
			'description' => esc_html__('Set any custom code or link to image', 'rehub-framework'),
			'section' => 'rh_user_settings',
			'settings' => 'custom_msg_popup',
			'type' => 'textarea',
		));
		$wp_customize->add_setting('custom_login_url', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('custom_login_url', array(
			'type' => 'text',
			'settings' => 'custom_login_url',
			'label' => esc_html__('Type url for login button', 'rehub-framework'),
			'description' => esc_html__('By default, login button triggers login popup, but you can redirect users to any link with registration form if you set this field. Login popup will not work in this case', 'rehub-framework'),
			'section' => 'rh_user_settings',
		));	
		$wp_customize->add_setting('custom_register_link', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('custom_register_link', array(
			'type' => 'text',
			'settings' => 'custom_register_link',
			'label' => esc_html__('Add custom register link', 'rehub-framework'),
			'description' => esc_html__('Add custom link if you want to use custom register page instead of sign up in popup', 'rehub-framework'),	
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('userlogin_term_page', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('userlogin_term_page', array(
			'type' => 'text',
			'settings' => 'userlogin_term_page',
			'label' => esc_html__('Terms and conditions page url for popup', 'rehub-framework'),
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('userlogin_policy_page', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('userlogin_policy_page', array(
			'type' => 'text',
			'settings' => 'userlogin_policy_page',
			'label' => esc_html__('Privacy Policy page url for popup', 'rehub-framework'),
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('user_bp_divider');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'user_bp_divider', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('Buddypress options', 'rehub-framework'),
			'section' => 'rh_user_settings',
			'settings' => 'user_bp_divider',
		)));
		$wp_customize->add_setting( 'rehub_bpheader_image', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_bpheader_image', array(
			'label' => esc_html__('Default background image in header. Recommended size 1900x260', 'rehub-framework'),
			'description' => esc_html__('Upload a background image or leave blank', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_bpheader_image',
		)));
		$wp_customize->add_setting('rh_bp_custom_message_profile', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rh_bp_custom_message_profile', array(
			'label' => esc_html__('Add custom message or html in profile of User', 'rehub-framework'),
			'description' => esc_html__('You can use shortcodes to show additional info inside Profile tab of user Profile. For example, shortcodes from S2Member plugin or any conditional information. If you want to show information for owner of profile, wrap it with shortcode [rh_is_bpmember_profile]Content[/rh_is_bpmember_profile]', 'rehub-framework'),
			'section' => 'rh_user_settings',
			'settings' => 'rh_bp_custom_message_profile',
			'type' => 'textarea',
		));
		$wp_customize->add_setting('user_bp_posts');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'user_bp_posts', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('Posts Profile tab', 'rehub-framework'),
			'section' => 'rh_user_settings',
			'settings' => 'user_bp_posts',
		)));
		$wp_customize->add_setting('rh_bp_user_post_name', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('rh_bp_user_post_name', array(
			'type' => 'text',
			'settings' => 'rh_bp_user_post_name',
			'label' => esc_html__('Add Name of Posts tab in Profile', 'rehub-framework'),
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('rh_bp_user_post_slug', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('rh_bp_user_post_slug', array(
			'type' => 'text',
			'settings' => 'rh_bp_user_post_slug',
			'label' => esc_html__('Add slug of Posts tab', 'rehub-framework'),
			'description' => esc_html__('Use only latin symbols, without spaces', 'rehub-framework'),
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('rh_bp_user_post_pos', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('rh_bp_user_post_pos', array(
			'type' => 'text',
			'settings' => 'rh_bp_user_post_pos',
			'label' => esc_html__('Add position of tab', 'rehub-framework'),
			'default' => '20',
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting( 'rh_bp_user_post_newpage', array(
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( 'rh_bp_user_post_newpage', array(
			'type' => 'dropdown-pages',
			'section' => 'rh_user_settings',
			'settings' => 'rh_bp_user_post_newpage',
			'label' => esc_html__('Assign page for Add new posts', 'rehub-framework'),
			'description' => esc_html__('Choose page where you have frontend form for posts. Content of this page will be assigned to tab. You can use bundled RH Frontend PRO to create such form.', 'rehub-framework'),
		) );
		$wp_customize->add_setting( 'rh_bp_user_post_editpage', array(
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( 'rh_bp_user_post_editpage', array(
			'type' => 'dropdown-pages',
			'section' => 'rh_user_settings',
			'settings' => 'rh_bp_user_post_editpage',
			'label' => esc_html__('Assign page for Edit Posts', 'rehub-framework'),
			'description' => esc_html__('Choose page where you have EDIT form for posts. If you use RH Frontend Form, such page, usually, has shortcode like [wpfepp_post_table form="1" show_all=0]', 'rehub-framework'),
		) );
		$wp_customize->add_setting('rh_bp_user_post_type', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('rh_bp_user_post_type', array(
			'type' => 'text',
			'settings' => 'rh_bp_user_post_type',
			'label' => esc_html__('Add member type', 'rehub-framework'),
			'description' => esc_html__('If you want to show tab only for special member type, add here slug of this member type. Note, Buddypress member type is not the same as wordpress role', 'rehub-framework'),
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('user_bp_products');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'user_bp_products', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('Products Profile tab', 'rehub-framework'),
			'section' => 'rh_user_settings',
			'settings' => 'user_bp_products',
		)));
		$wp_customize->add_setting('rh_bp_user_product_name', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('rh_bp_user_product_name', array(
			'type' => 'text',
			'settings' => 'rh_bp_user_product_name',
			'label' => esc_html__('Add Name of Products tab in Profile', 'rehub-framework'),
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('rh_bp_user_product_slug', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('rh_bp_user_product_slug', array(
			'type' => 'text',
			'settings' => 'rh_bp_user_product_slug',
			'label' => esc_html__('Add slug of Products tab', 'rehub-framework'),
			'description' => esc_html__('Use only latin symbols, without spaces', 'rehub-framework'),
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('rh_bp_user_product_pos', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('rh_bp_user_product_pos', array(
			'type' => 'text',
			'settings' => 'rh_bp_user_product_pos',
			'label' => esc_html__('Add position of tab', 'rehub-framework'),
			'default' => '20',
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting( 'rh_bp_user_product_newpage', array(
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( 'rh_bp_user_product_newpage', array(
			'type' => 'dropdown-pages',
			'section' => 'rh_user_settings',
			'settings' => 'rh_bp_user_product_newpage',
			'label' => esc_html__('Assign page for Add new products', 'rehub-framework'),
			'description' => esc_html__('Choose page where you have frontend form for products. Content of this page will be assigned to tab. You can use bundled RH Frontend PRO to create such form.', 'rehub-framework'),
		) );
		$wp_customize->add_setting( 'rh_bp_user_product_editpage', array(
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( 'rh_bp_user_product_editpage', array(
			'type' => 'dropdown-pages',
			'section' => 'rh_user_settings',
			'settings' => 'rh_bp_user_product_editpage',
			'label' => esc_html__('Assign page for Edit Products', 'rehub-framework'),
			'description' => esc_html__('Choose page where you have EDIT form for products. If you use RH Frontend Form, such page, usually, has shortcode like [wpfepp_post_table form="1" show_all=0]', 'rehub-framework'),
		) );
		$wp_customize->add_setting('rh_bp_user_product_type', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('rh_bp_user_product_type', array(
			'type' => 'text',
			'settings' => 'rh_bp_user_product_type',
			'label' => esc_html__('Add member type', 'rehub-framework'),
			'description' => esc_html__('If you want to show tab only for special member type, add here slug of this member type. Note, Buddypress member type is not the same as wordpress role', 'rehub-framework'),
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('user_bp_mycred');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'user_bp_mycred', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('MyCred Options', 'rehub-framework'),
			'section' => 'rh_user_settings',
			'settings' => 'user_bp_mycred',
		)));
		$wp_customize->add_setting( 'rh_enable_mycred_comment', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rh_enable_mycred_comment', array(
			'label' => esc_html__('Enable badges, points, ranks from MyCred plugin in regular comments?', 'rehub-framework'),
			'description' => esc_html__('Can slow your single pages', 'rehub-framework'),
			'section'  => 'rh_user_settings',
			'settings' => 'rh_enable_mycred_comment',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting('rh_mycred_custom_points', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('rh_mycred_custom_points', array(
			'type' => 'text',
			'settings' => 'rh_mycred_custom_points',
			'label' => esc_html__('Show custom point type instead default', 'rehub-framework'),	
			'section' => 'rh_user_settings',
		));
		$wp_customize->add_setting('rh_award_role_mycred', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rh_award_role_mycred', array(
			'label' => esc_html__('Give user roles for their Mycred Points', 'rehub-framework'),
			'description' => esc_html__('If you use MyCred plugin and want to give user new role once he gets definite points, you can use this area. Syntaxis is next: role:1000. Where role is role which you want to give and 1000 is amount of points to get this role. Place each role with next line. Place them in ASC mode. First line, for example, 10 points, next is 100. Function also works as opposite. ', 'rehub-framework'),
			'section' => 'rh_user_settings',
			'settings' => 'rh_award_role_mycred',
			'type' => 'textarea',
		));
		$wp_customize->add_setting( 'rh_award_type_mycred', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rh_award_type_mycred', array(
			'label' => esc_html__('Give BP member types instead of roles?', 'rehub-framework'),
			'description' => esc_html__('If you want to give users member types instead of roles which are set above, enable this', 'rehub-framework'),	
			'section'  => 'rh_user_settings',
			'settings' => 'rh_award_type_mycred',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));

		// Dynamic comparison options
		$wp_customize->add_section( 'rh_dynamic_settings', array(
			'title' => esc_html__('Dynamic Comparison', 'rehub-framework'),
			'priority'  => 134,
			'panel' => 'panel_id',
		));
		$wp_customize->add_setting( 'compare_page', array(
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( 'compare_page', array(
			'type' => 'dropdown-pages',
			'section' => 'rh_dynamic_settings',
			'settings' => 'compare_page',
			'label' => esc_html__('Select page for comparison', 'rehub-framework'),
			'description' => esc_html__('Page must have top chart constructor page template or shortcode [wpsm_woocharts]. We recommend to set page as full width in right panel of Edit page area', 'rehub-framework'),
		) );
		$wp_customize->add_setting('compare_multicats_textarea', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('compare_multicats_textarea', array(
			'label' => esc_html__('Assign categories to pages', 'rehub-framework'),
			'description' => esc_html__('Use this option if you want to have different comparison groups. Create separate pages for each group. Then, use next syntaxis: 1,2,3;Title;23, where 1,2,3 - category IDs, Title - a general name for category group, 23 - a page ID of comparison. You can add also custom taxonomy in the end. By default, product categories will be used. Delimiter is ";"', 'rehub-framework').' <br/><br/><a href="http://rehubdocs.wpsoul.com/docs/rehub-framework/comparisons-tables-charts-lists/dynamic-comparison-charts/" target="_blank">Documentation</a>',		
			'section' => 'rh_user_settings',
			'settings' => 'rh_dynamic_settings',
			'type' => 'textarea',
		));
		$wp_customize->add_setting( 'compare_disable_button', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'compare_disable_button', array(
			'label' => esc_html__('Disable button in right side', 'rehub-framework'),
			'description' => esc_html__('You can disable button with compare icon on right side of site. You can place this icon in header. Use Shop/Comparison header in theme option - header and menu - Header layout', 'rehub-framework'),
			'section'  => 'rh_dynamic_settings',
			'settings' => 'compare_disable_button',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting('compare_woo_cats', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('compare_woo_cats', array(
			'type' => 'text',
			'settings' => 'compare_woo_cats',
			'label' => esc_html__('Set ids of product categories where to show button. Leave blank to show in all products', 'rehub-framework'),
			'section' => 'rh_dynamic_settings',
		));

		// Custom badges
		$wp_customize->add_section( 'rh_badges_settings', array(
			'title' => esc_html__('Custom Badges', 'rehub-framework'),
			'priority'  => 135,
			'panel' => 'panel_id',
		));
		$wp_customize->add_setting('badge_label_1', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('badge_label_1', array(
			'type' => 'text',
			'settings' => 'badge_label_1',
			'label' => esc_html__('Label for first badge', 'rehub-framework'),
			'default' => esc_html__('Editor choice', 'rehub-framework'),
			'description' => esc_html__('Maximum safe length - 20 symbols', 'rehub-framework'),
			'section' => 'rh_badges_settings',
		));
		$wp_customize->add_setting( 'badge_color_1', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'badge_color_1', array(
			'label' => esc_html__('Color for first badge', 'rehub-framework'),
			'section' => 'rh_badges_settings',
			'settings' => 'badge_color_1',
		)));

		$wp_customize->add_setting('badge_label_2', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('badge_label_2', array(
			'type' => 'text',
			'settings' => 'badge_label_2',
			'label' => esc_html__('Label for second badge', 'rehub-framework'),
			'default' => esc_html__('Best seller', 'rehub-framework'),
			'description' => esc_html__('Maximum safe length - 20 symbols', 'rehub-framework'),
			'section' => 'rh_badges_settings',
		));
		$wp_customize->add_setting( 'badge_color_2', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'badge_color_2', array(
			'label' => esc_html__('Color for second badge', 'rehub-framework'),
			'section' => 'rh_badges_settings',
			'settings' => 'badge_color_2',
		)));

		$wp_customize->add_setting('badge_label_3', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('badge_label_3', array(
			'type' => 'text',
			'settings' => 'badge_label_3',
			'label' => esc_html__('Label for third badge', 'rehub-framework'),
			'default' => esc_html__('Best value', 'rehub-framework'),
			'description' => esc_html__('Maximum safe length - 20 symbols', 'rehub-framework'),
			'section' => 'rh_badges_settings',
		));
		$wp_customize->add_setting( 'badge_color_3', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'badge_color_3', array(
			'label' => esc_html__('Color for third badge', 'rehub-framework'),
			'section' => 'rh_badges_settings',
			'settings' => 'badge_color_3',
		)));

		$wp_customize->add_setting('badge_label_4', array(
			'sanitize_callback' => 'wp_kses',
		));
		$wp_customize->add_control('badge_label_4', array(
			'type' => 'text',
			'settings' => 'badge_label_4',
			'label' => esc_html__('Label for fourth badge', 'rehub-framework'),
			'default' => esc_html__('Best price', 'rehub-framework'),
			'description' => esc_html__('Maximum safe length - 20 symbols', 'rehub-framework'),
			'section' => 'rh_badges_settings',
		));
		$wp_customize->add_setting( 'badge_color_4', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'badge_color_4', array(
			'label' => esc_html__('Color for fourth badge', 'rehub-framework'),
			'section' => 'rh_badges_settings',
			'settings' => 'badge_color_4',
		)));


		/* Google Fonts Section */
		$wp_customize->add_section( 'rh_font_settings', array(
			'title' => esc_html__('Fonts Options', 'rehub-framework'),
			'priority'  => 136,
			'panel' => 'panel_id',
		));
		$wp_customize->add_setting('nav_font_divider');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'nav_font_divider', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('Navigation Font', 'rehub-framework'),
			'description' => esc_html__('Font for navigation', 'rehub-framework'),
			'section' => 'rh_font_settings',
			'settings' => 'nav_font_divider',
		)));
		$wp_customize->add_setting( 'rehub_nav_font_group', array(
			'sanitize_callback' => array($this, 'google_font_sanitization'),
		));
		$wp_customize->add_control( new RH_Google_Font_Select_Control( $wp_customize, 'rehub_nav_font_group',
			array(
				'label' => esc_html__('Navigation Font', 'rehub-framework'),
				'section' => 'rh_font_settings',
				'settings' => 'rehub_nav_font_group',
			)
		) );
		$wp_customize->add_setting('headings_font_divider');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'headings_font_divider', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('Headings Font', 'rehub-framework'),
			'description' => esc_html__('Font for headings in text, sidebar, footer', 'rehub-framework'),
			'section' => 'rh_font_settings',
			'settings' => 'headings_font_divider',
		)));
		$wp_customize->add_setting( 'rehub_headings_font_group', array(
			'sanitize_callback' => array($this, 'google_font_sanitization'),
		));
		$wp_customize->add_control( new RH_Google_Font_Select_Control( $wp_customize, 'rehub_headings_font_group',
			array(
				'label' => esc_html__('Headings Font', 'rehub-framework'),
				'section' => 'rh_font_settings',
				'settings' => 'rehub_headings_font_group',
			)
		) );
		$wp_customize->add_setting( 'rehub_headings_font_upper', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_headings_font_upper', array(
			'label' => esc_html__('Enable uppercase?', 'rehub-framework'),
			'section'  => 'rh_font_settings',
			'settings' => 'rehub_headings_font_upper',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting('btn_font_divider');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'btn_font_divider', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('Button Font', 'rehub-framework'),
			'description' => esc_html__('Button Font Family', 'rehub-framework'),
			'section' => 'rh_font_settings',
			'settings' => 'btn_font_divider',
		)));
		$wp_customize->add_setting( 'rehub_btn_font_group', array(
			'sanitize_callback' => array($this, 'google_font_sanitization'),
		));
		$wp_customize->add_control( new RH_Google_Font_Select_Control( $wp_customize, 'rehub_btn_font_group',
			array(
				'label' => esc_html__('Button Font', 'rehub-framework'),
				'section' => 'rh_font_settings',
				'settings' => 'rehub_btn_font_group',
			)
		) );
		$wp_customize->add_setting( 'rehub_btn_font_upper_dis', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_btn_font_upper_dis', array(
			'label' => esc_html__('Disable uppercase?', 'rehub-framework'),
			'section'  => 'rh_font_settings',
			'settings' => 'rehub_btn_font_upper_dis',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting('body_font_divider');
		$wp_customize->add_control(new RH_Divider_Control($wp_customize, 'body_font_divider', array(
			'icon' => '<i class="rhicon rhi-bars"></i>',
			'caption' => esc_html__('Body Font', 'rehub-framework'),
			'description' => esc_html__('Font for body text', 'rehub-framework'),
			'section' => 'rh_font_settings',
			'settings' => 'body_font_divider',
		)));
		$wp_customize->add_setting( 'rehub_body_font_group', array(
			'sanitize_callback' => array($this, 'google_font_sanitization'),
		));
		$wp_customize->add_control( new RH_Google_Font_Select_Control( $wp_customize, 'rehub_body_font_group',
			array(
				'label' => esc_html__('Body Font', 'rehub-framework'),
				'section' => 'rh_font_settings',
				'settings' => 'rehub_body_font_group',
			)
		) );

		$wp_customize->add_setting('body_font_size', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('body_font_size', array(
			'label' => esc_html__('Set body font size', 'rehub-framework'),
			'description' => esc_html__('Set font size in px. If you want to add also line height, add it after symbol ":". Example, 20:24, where 20px is font size, 24px is line height', 'rehub-framework'),
			'section' => 'rh_font_settings',
			'settings' => 'body_font_size',
		));
	

		$wp_customize->get_setting( 'rehub_body_block' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_content_shadow' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo_retina' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo_retina_width' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo_retina_height' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_text_logo' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_text_slogan' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_sticky_nav' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo_sticky_url' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_logoline_style' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_menuline_style' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_topline_style' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_six_btn_login' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_nav_font_group' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_headings_font_group' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_btn_font_group' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_body_font_group' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_headings_font_upper' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_btn_font_upper_dis' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'body_font_size' )->transport  = 'postMessage';

	}

	function rh_sanitize_dropdown_pages( $page_id, $setting ) {
		// Ensure $input is an absolute integer.
		$page_id = absint( $page_id );
	  
		// If $page_id is an ID of a published page, return it; otherwise, return the default.
		return ( 'publish' == get_post_status( $page_id ) ? $page_id : $setting->default );
	  }

	/* Adds admin scripts and styles */
	public function rh_customizer_scripts() {
		$screen = get_current_screen();
		$screen_id = $screen->id;

		if( 'customize' == $screen_id ) {
			wp_enqueue_script( 'customizer-js', RH_FRAMEWORK_URL .'/assets/js/customizer.js', array('jquery'), '1.3', true );
			wp_enqueue_style( 'customizer-css', RH_FRAMEWORK_URL .'/assets/css/customizer.css', array(), '1.4' );
	    }
	}

	/* Adds scripts to Preview frame */
	public function rh_live_preview_scripts() {
		wp_enqueue_script( 'font-loader-js', 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js', array( 'customize-preview' ), '1.6.26', true );
		wp_enqueue_script( 'rh-customizer-js', RH_FRAMEWORK_URL .'/assets/js/theme-customizer.js', array( 'jquery','customize-preview' ), '1.8', true );
		wp_enqueue_script( 'sticky' );
	}

	/*  */
	public function fonts_saving_theme( $key, $new_value, $opt ){
		if( 'rehub_nav_font_group' == $key ){
			$new_value = json_decode( $new_value, true );
			if( !empty( $new_value ) ){
				$opt['rehub_nav_font'] = $new_value['font'];
				$opt['rehub_nav_font_weight'] = $new_value['weights'];
				$opt['rehub_nav_font_style'] = $new_value['styles'];
				$opt['rehub_nav_font_subset'] = $new_value['subsets'];
			}
		}
		if( 'rehub_headings_font_group' == $key ){
			$new_value = json_decode( $new_value, true );
			if( !empty( $new_value ) ){
				$opt['rehub_headings_font'] = $new_value['font'];
				$opt['rehub_headings_font_weight'] = $new_value['weights'];
				$opt['rehub_headings_font_style'] = $new_value['styles'];
				$opt['rehub_headings_font_subset'] = $new_value['subsets'];
			}
		}
		if( 'rehub_btn_font_group' == $key ){
			$new_value = json_decode( $new_value, true );
			if( !empty( $new_value ) ){
				$opt['rehub_btn_font'] = $new_value['font'];
				$opt['rehub_btn_font_weight'] = $new_value['weights'];
				$opt['rehub_btn_font_style'] = $new_value['styles'];
				$opt['rehub_btn_font_subset'] = $new_value['subsets'];
			}
		}
		if( 'rehub_body_font_group' == $key ){
			$new_value = json_decode( $new_value, true );
			if( !empty( $new_value ) ){
				$opt['rehub_body_font'] = $new_value['font'];
				$opt['rehub_body_font_weight'] = $new_value['weights'];
				$opt['rehub_body_font_style'] = $new_value['styles'];
				$opt['rehub_body_font_subset'] = $new_value['subsets'];
			}
		}	
		return $opt;
	}
	
	/*  */
	public function fonts_saving_customizer( $key, $opt ){
		$new_value = [];
		if( 'rehub_nav_font_group' == $key ){
			$new_value['font'] = (!empty($opt['rehub_nav_font'])) ? $opt['rehub_nav_font'] : '';
			$new_value['weights'] = (!empty($opt['rehub_nav_font_weight'])) ? $opt['rehub_nav_font_weight'] : '';
			$new_value['styles'] = (!empty($opt['rehub_nav_font_style'])) ? $opt['rehub_nav_font_style'] : '';
			$new_value['subsets'] = (!empty($opt['rehub_nav_font_subset'])) ? $opt['rehub_nav_font_subset'] : '';
		}
		if( 'rehub_headings_font_group' == $key ){
			$new_value['font'] = (!empty($opt['rehub_headings_font'])) ? $opt['rehub_headings_font'] : '';
			$new_value['weights'] = (!empty($opt['rehub_headings_font_weight'])) ? $opt['rehub_headings_font_weight'] : '';
			$new_value['styles'] = (!empty($opt['rehub_headings_font_style'])) ? $opt['rehub_headings_font_style'] : '';
			$new_value['subsets'] = (!empty($opt['rehub_headings_font_subset'])) ? $opt['rehub_headings_font_subset'] : '';
		}
		if( 'rehub_btn_font_group' == $key ){
			$new_value['font'] = (!empty($opt['rehub_btn_font'])) ? $opt['rehub_btn_font'] : '';
			$new_value['weights'] = (!empty($opt['rehub_btn_font_weight'])) ? $opt['rehub_btn_font_weight'] : '';
			$new_value['styles'] = (!empty($opt['rehub_btn_font_style'])) ? $opt['rehub_btn_font_style'] : '';
			$new_value['subsets'] = (!empty($opt['rehub_btn_font_subset'])) ? $opt['rehub_btn_font_subset'] : '';
		}
		if( 'rehub_body_font_group' == $key ){
			$new_value['font'] = (!empty($opt['rehub_body_font'])) ? $opt['rehub_body_font'] : '';
			$new_value['weights']= (!empty($opt['rehub_body_font_weight'])) ? $opt['rehub_body_font_weight'] : '';
			$new_value['styles'] = (!empty($opt['rehub_body_font_style'])) ? $opt['rehub_body_font_style'] : '';
			$new_value['subsets'] = (!empty($opt['rehub_body_font_subset'])) ? $opt['rehub_body_font_subset'] : '';
		}
		$new_value = json_encode( $new_value );
		return $new_value;
	}

	/* Saves Customizer options to Theme ones */
	public function rh_save_theme_options() {
		$opt = get_option( 'rehub_option' );
		$font_group = [ 'rehub_nav_font_group', 'rehub_headings_font_group', 'rehub_btn_font_group', 'rehub_body_font_group' ];
		foreach(self::$rh_cross_option_fields as $key ) {
			$new_value = get_theme_mod( $key );
			if( in_array( $key, $font_group ) ){
				$opt = $this->fonts_saving_theme( $key, $new_value, $opt );
				continue;
			}
			$old_value = ( !empty($opt[$key]) ) ? $opt[$key] : '';
			if( $new_value != $old_value ){
				$opt[$key] = $new_value;
			}else{
				continue;
			}
		}
		$logo = get_theme_mod('rehub_logo');
		if($logo){
			$logoid = attachment_url_to_postid($logo);
			if($logoid){
				set_theme_mod('custom_logo', $logoid);
			}
		}
		
		update_option( 'rehub_option', $opt );
		do_action('rehub_after_saving_customizer');
	}

	/* Saves Theme options to Customizer ones */
	public function rh_save_customizer_options( $opt ){
		$font_group = [ 'rehub_nav_font_group', 'rehub_headings_font_group', 'rehub_btn_font_group', 'rehub_body_font_group' ];
	    foreach( self::$rh_cross_option_fields as $key ){
	        $old_value = get_theme_mod( $key );
			$new_value = ( !empty( $opt[$key] ) ) ? $opt[$key] : '';		
			if( in_array( $key, $font_group ) ){
				$new_value = $this->fonts_saving_customizer( $key, $opt );
			}
			if( $new_value != $old_value ){
				set_theme_mod( $key, $new_value );
			}
	        continue;
	    }
		$logo = (!empty($opt['rehub_logo'])) ? $opt['rehub_logo'] : '';
		if($logo){
			$logoid = attachment_url_to_postid($logo);
			if($logoid){
				set_theme_mod('custom_logo', $logoid);
			}
		}
	}		

	/* Google Font sanitization */
	public function google_font_sanitization( $input = '' ) {
		if( $input == '' ) return;
		$val = json_decode( $input, true );
		foreach ( $val as $key => $value ) {
			if( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					$value[$k] = sanitize_text_field( $v );
				}
			} else {
				$val[$key] = sanitize_text_field( $value );
			}
		}
		return json_encode( $val );
	}

	/* Get current menus array */
	public function rh_get_menus_customizer() {
		$choices = array();
		$menus = wp_get_nav_menus();
		foreach ($menus as $menu) {
			$choices[$menu->term_id] = $menu->name;
		}
		return $choices;
	}				
}

add_action( 'customize_register', 'rh_customizer_extend_classes' );
function rh_customizer_extend_classes($wp_customize) {
	if ( class_exists( 'WP_Customize_Control' ) ) {
		class RH_Divider_Control extends WP_Customize_Control {
	
			public $type = 'divider';
	
			public $caption = '';
			
			public $icon = '';
	
			protected function render_content() {
				?>
				<?php if ( ! empty( $this->caption ) ) : ?>
					<?php echo $this->icon; ?>
				<?php endif; ?>
				<?php if ( ! empty( $this->caption ) ) : ?>
					<span class="customize-control-caption"><?php echo esc_html( $this->caption ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php endif; ?>
				<?php
			}
		}

		
		//Fobt Class
		class RH_Google_Font_Select_Control extends WP_Customize_Control {

			public $type = 'google_fonts';
			private $fontList = false;
			private $fontValues = [];
			private $fontListIndex = 0;
			private $current_font;
			private $current_styles;
			private $current_weights;
			private $current_subsets;

			public function __construct( $manager, $id, $args = array(), $options = array() ) {
				parent::__construct( $manager, $id, $args );

				$this->fontList = $this->get_google_fonts();
				$this->fontValues = json_decode( $this->value() );
				$this->current_font = ( !empty( $this->fontValues->font ) ) ? $this->fontValues->font : '';
				$this->current_styles = ( !empty( $this->fontValues->styles ) ) ? $this->fontValues->styles : '';
				$this->current_weights= ( !empty( $this->fontValues->weights ) ) ? $this->fontValues->weights : '';
				$this->current_subsets = ( !empty( $this->fontValues->subsets ) ) ? $this->fontValues->subsets : array();
				$this->fontListIndex = $this->get_font_index( $this->fontList, $this->current_font );
			}
			
			public function to_json() {
				parent::to_json();
				$this->json['fontslist'] = $this->fontList;
			}
			
			public function get_font_index( $haystack, $needle ) {
				foreach( $haystack as $key => $value ) {
					if( $value->family == $needle ) {
						return $key;
					}
				}
				return false;
			}

			public function get_google_fonts() {
				
				$fonts = json_decode(rf_filesystem('get_content', RH_FRAMEWORK_ABSPATH .'/vendor/vafpress/data/gwf.json')); 
				$items = [];
				
				$default = new stdClass;
				$default->family = '';
				$default->styles = [];
				$default->weights = [];
				$default->subsets = [];
				
				$items[] = $default;
				
				foreach( $fonts as $key => $font ){
					$obj = new stdClass;
					$obj->family = $key;
					$obj->styles = $font->styles;
					$obj->weights = $font->weights;
					$obj->subsets = $font->subsets;
					$items[] = $obj;
				}

				return $items;
			}
			
			/**
			 * Render the control in the customizer
			 */
			public function render_content() {
				$isFontInList = false;
				$fontListStr = '';

				if( !empty($this->fontList) ) {
					?>
					<div class="google_fonts_select_control">
						<?php if( !empty( $this->label ) ) { ?>
							<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
						<?php } ?>
						<?php if( !empty( $this->description ) ) { ?>
							<span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
						<?php } ?>
						<input type="hidden" id="<?php echo esc_attr( $this->id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $this->value() ); ?>" class="customize-control-google-font-selection" <?php $this->link(); ?> />
						<div class="google-fonts">
							<select class="google-fonts-list" control-name="<?php echo esc_attr( $this->id ); ?>" autocomplete="off">
								<?php
									foreach( $this->fontList as $key => $value ) {
										$font_name = ( $value->family ) ? $value->family : esc_html__( 'Default', 'rehub-framework' );
										$fontListStr .= '<option value="' . $value->family . '" ' . selected( $this->current_font, $value->family, false ) . '>' . $font_name . '</option>';
										if ( $this->current_font === $value->family ) {
											$isFontInList = true;
										}
									}
									echo $fontListStr;
								?>
							</select>
						</div>
						<div class="customize-control-description"><?php esc_html_e( 'Font Style', 'rehub-framework' ) ?></div>
						<div class="google-font-style">
							<select class="google-fonts-styles-style">
								<?php
									$styles = $this->fontList[$this->fontListIndex]->styles;
									if( !empty( $styles ) ){
										foreach( $styles as $key => $value ) {
											echo '<option value="' . $value . '" ' . selected( $this->current_styles, $value, false ) . '>' . $value . '</option>';
										}
									} else {
										echo '<option value="">'. esc_html__( 'Not Available', 'rehub-framework' ) .'</option>';
									}
								?>
							</select>
						</div>
						<div class="customize-control-description"><?php esc_html_e( 'Font Weight', 'rehub-framework' ) ?></div>
						<div class="google-font-weight">
							<select class="google-fonts-weights-style">
								<?php
									$weights = $this->fontList[$this->fontListIndex]->weights;
									if( !empty( $weights ) ){
										foreach( $weights as $key => $value ) {
											echo '<option value="' . $value . '" ' . selected( $this->current_weights, $value, false ) . '>' . $value . '</option>';
										}
									} else {
										echo '<option value="">'. esc_html__( 'Not Available', 'rehub-framework' ) .'</option>';
									}
								?>
							</select>
						</div>
						<div class="customize-control-description"><?php esc_html_e( 'Font Subset', 'rehub-framework' ) ?></div>
						<div class="google-font-subset">
							<select class="google-fonts-subsets-style" multiple='multiple'>
								<?php
									$curent_subsets = $this->current_subsets;
									$subsets = $this->fontList[$this->fontListIndex]->subsets;
									if( !empty( $subsets ) ){
										foreach( $subsets as $key => $value ) {
											$selected = ( in_array( $value, $curent_subsets ) ) ? 'selected="selected"' : '';
											echo '<option value="'. $value .'" '. $selected .'>' . $value . '</option>';
										}
									} else {
										echo '<option value="">'. esc_html__( 'Not Available', 'rehub-framework' ) .'</option>';
									}
								?>
							</select>
						</div>
					</div>
					<?php
				}
			}
		}

	}
}


new REHub_Framework_Customizer;