<?php

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

//////////////////////////////////////////////////////////////////
// Fallbacks
//////////////////////////////////////////////////////////////////
if(!function_exists('rehub_option')){
	function rehub_option( $key ) {
	    if( is_customize_preview() ) {
	    	$fontarray = array('rehub_nav_font', 'rehub_nav_font_style', 'rehub_nav_font_weight', 'rehub_nav_font_subset', 'rehub_headings_font', 'rehub_headings_font_style', 'rehub_headings_font_weight', 'rehub_headings_font_subset', 'rehub_headings_font_upper', 'rehub_body_font', 'rehub_body_font_style', 'rehub_body_font_weight', 'rehub_body_font_subset', 'body_font_size');
	    	if(in_array( $key, $fontarray)){
				$options = get_option( 'rehub_option' );
				$value = (!empty($options[$key])) ? $options[$key] : '';	    		
	    	}else{
	    		$value = get_theme_mod( $key );
	    	}

		} 
		else {
			if( class_exists( 'REHub_Framework' ) ){
				$localizationarray = array('rehub_logo', 'rehub_logo_retina','rehub_logo_sticky_url','rehub_logo_inmenu_url','logo_mobilesliding','header_six_btn_txt', 'header_six_btn_url', 'header_seven_login_label', 'header_seven_compare_btn_label', 'header_seven_wishlist_label', 'header_seven_wishlist', 'rehub_text_logo', 'rehub_text_slogan', 'rehub_newstick_label', 'rehub_footer_text', 'rehub_homecarousel_label_text', 'rehub_btn_text', 'rehub_btn_text_aff_links', 'rehub_mask_text', 'rehub_review_text', 'rehub_readmore_text', 'rehub_search_text', 'rehub_btn_text_best', 'rehub_choosedeal_text', 'rehub_related_text', 'rehub_commenttitle_text', 'ce_custom_currency', 'buy_best_text', 'amp_custom_in_header', 'rh_bp_user_post_name', 'rh_bp_user_product_name', 'rh_bp_custom_message_profile', 'badge_label_1', 'badge_label_2', 'badge_label_3', 'badge_label_4', 'header_seven_more_element', 'rehub_user_rev_criterias', 'compare_multicats_textarea', 'compare_page', 'rehub_single_before_post', 'rehub_single_code', 'custom_register_link','woo_code_zone_button','woo_code_zone_content','woo_code_zone_footer','woo_code_zone_float','woo_code_zone_loop','rh_woo_shop_global', 'wishlistpage', 'rehub_top_line_content');
				if ((defined( 'POLYLANG_BASENAME' ) || defined( 'WPML_PLUGIN_BASENAME' )) && in_array( $key, $localizationarray) ){
					$options = get_option( 'rehub_option' );
					$value = (!empty($options[$key])) ? $options[$key] : '';
				}else{
					$value = REHub_Framework::get_option( $key );					
				}

			}
			else {
				$value = get_theme_mod( $key );
			}
		}
		return $value;
	}
}
if( !class_exists( 'REHub_Framework' ) ){
	function vp_metabox(){
		return;
	}
}

//////////////////////////////////////////////////////////////////
// Constants
//////////////////////////////////////////////////////////////////
if ( ! defined( 'REHUB_ADMIN_DIR' ) ) {
	define( 'REHUB_ADMIN_DIR', get_template_directory_uri() . '/admin/' );
}
if(!defined('PLUGIN_REPO')){
	define('PLUGIN_REPO', 'https://wpsoul.net/serverupdate/');
}

//Set default colors
define( 'REHUB_MAIN_COLOR', '#8035be');
define( 'REHUB_SECONDARY_COLOR', '#000000');
define( 'REHUB_BUTTON_COLOR', '#de1414');
define( 'REHUB_DEFAULT_LAYOUT', 'communitylist');
define( 'REHUB_BOX_DISABLE', '0');
define( 'REHUB_BUTTON_COLOR_TEXT', '#ffffff');				

//////////////////////////////////////////////////////////////////
// Demo import
//////////////////////////////////////////////////////////////////
require_once( 'demo/import-demo.php' );


//////////////////////////////////////////////////////////////////
// Admin class
//////////////////////////////////////////////////////////////////
if ( ! class_exists( 'Rehub_Admin' ) ) {

	class Rehub_Admin{

		function __construct(){

			add_action( 'admin_init', array( $this, 'rehub_admin_init' ) );
			add_action( 'admin_menu', array( $this, 'rehub_admin_menu' ) );
			add_action( 'admin_head', array( $this, 'rehub_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'edit_admin_menus' ) );
			add_action( 'after_switch_theme', array( $this, 'rehub_activation_redirect' ) );
			add_action( 'admin_notices', array( $this, 'rehub_framework_required' ) );			
		}

		/**
		 * Add the top-level menu item to the adminbar.
		 */
		function rehub_add_wp_toolbar_menu_item( $title, $parent = FALSE, $href = '', $custom_meta = array(), $custom_id = '' ) {

			global $wp_admin_bar;

			if ( current_user_can( 'edit_theme_options' ) ) {
				if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
					return;
				}

				// Set custom ID
				if ( $custom_id ) {
					$id = $custom_id;
				// Generate ID based on $title
				} else {
					$id = strtolower( str_replace( ' ', '-', $title ) );
				}

				// links from the current host will open in the current window
				$meta = strpos( $href, site_url() ) !== false ? array() : array( 'target' => '_blank' ); // external links open in new tab/window
				$meta = array_merge( $meta, $custom_meta );

				$wp_admin_bar->add_node( array(
					'parent' => $parent,
					'id'     => $id,
					'title'  => $title,
					'href'   => $href,
					'meta'   => $meta,
				) );
			}

		}

		function rehub_framework_required() {
			if( !class_exists( 'REHub_Framework' ) ){
				?>
			    <div class="error" style="display:block !important"><p><?php esc_html_e( 'Rehub theme requires Rehub framework plugin to be installed. Please install and activate it', 'rehub-theme'); ?> <a href="<?php echo admin_url( 'admin.php?page=rehub-plugins' );?>"><?php esc_html_e( 'on this page', 'rehub-theme'); ?></a>
			    	</p></div>
			    <?php
			}
		}		

		/**
		 * Modify the menu
		 */
		function edit_admin_menus() {
			global $submenu;

			if ( current_user_can( 'edit_theme_options' ) ) {
				$submenu['rehub'][0][0] = 'Registration'; // Change Rehub to Product Registration
			}
		}

		/**
		 * Redirect to admin page on theme activation
		 */
		function rehub_activation_redirect() {
		    $elementor_disable_typography_schemes = get_option('elementor_disable_typography_schemes');
		    if (empty($elementor_disable_typography_schemes)) {
		        update_option('elementor_disable_typography_schemes', 'yes');
		    }
	        $elementor_disable_color_schemes = get_option('elementor_disable_color_schemes');
	        if (empty($elementor_disable_color_schemes)) {
	            update_option('elementor_disable_color_schemes', 'yes');
	        }
	        $elementor_minicart = get_option('elementor_use_mini_cart_template');
	        if (empty($elementor_minicart) || $elementor_minicart=='yes' || $elementor_minicart=='initial') {
	            update_option('elementor_use_mini_cart_template', 'no');
	        }
	        if(function_exists('wc_get_page_id')){
	        	$myaccountid = wc_get_page_id('myaccount');
	        	if($myaccountid > 0){
	        		$myaccounttemplate = get_post_meta($myaccountid, '_wp_page_template', true);
	        		$contenttype = get_post_meta($myaccountid, 'content_type', true);
				    if ( ! $myaccounttemplate || 'default' == $myaccounttemplate ) {
				    	if(!$contenttype || $contenttype == 'def'){
				    		update_post_meta($myaccountid, '_wp_page_template', 'template-systempages.php');
				    	}
				    }	        		
	        	}
	        	$cartid = wc_get_page_id('cart');
	        	if($cartid > 0){
	        		$carttemplate = get_post_meta($cartid, '_wp_page_template', true);
	        		$contenttype = get_post_meta($cartid, 'content_type', true);
				    if ( ! $carttemplate || 'default' == $carttemplate ) {
				    	if(!$contenttype || $contenttype == 'def'){
				        	update_post_meta($cartid, '_wp_page_template', 'template-systempages.php');
				    	}
				    }	        		
	        	}
	        	$checkoutid = wc_get_page_id('checkout');
	        	if($checkoutid > 0){
	        		$checkouttemplate = get_post_meta($checkoutid, '_wp_page_template', true);
	        		$contenttype = get_post_meta($checkoutid, 'content_type', true);
				    if ( ! $checkouttemplate || 'default' == $checkouttemplate ) {
				    	if(!$contenttype || $contenttype == 'def'){
				        	update_post_meta($checkoutid, '_wp_page_template', 'template-systempages.php');
				    	}
				    }	        		
	        	}	        		        	
	        }	    
			if ( current_user_can( 'edit_theme_options' ) ) {
				header( 'Location:' . admin_url() . 'admin.php?page=rehub' );
			}
		}

		/**
		 * Actions to run on initial theme activation
		 */
		function rehub_admin_init() {			

			if ( current_user_can( 'edit_theme_options' ) ) {

				if ( isset( $_GET['rehub-deactivate'] ) && $_GET['rehub-deactivate'] == 'deactivate-plugin' ) {
					check_admin_referer( 'rehub-deactivate', 'rehub-deactivate-nonce' );

					$plugins = TGM_Plugin_Activation::$instance->plugins;

					foreach( $plugins as $plugin ) {
						if ( $plugin['slug'] == $_GET['plugin'] ) {
							deactivate_plugins( $plugin['file_path'] );
						}
					}
				} if ( isset( $_GET['rehub-activate'] ) && $_GET['rehub-activate'] == 'activate-plugin' ) {
					check_admin_referer( 'rehub-activate', 'rehub-activate-nonce' );

					$plugins = TGM_Plugin_Activation::$instance->plugins;

					foreach( $plugins as $plugin ) {
						if ( $plugin['slug'] == $_GET['plugin'] ) {
							activate_plugin( $plugin['file_path'] );

							wp_redirect( admin_url( 'admin.php?page=rehub-plugins' ) );
							exit;
						}
					}
				}

				//if(!defined('THEMESHILD_SLUG')){
					//define('THEMESHILD_SLUG', 'rewise');
				//}
				//require_once ( locate_template( 'admin/update-checker.php' ) );

			}
		}

		function rehub_admin_menu(){

			if ( current_user_can( 'edit_theme_options' ) ) {
				// Work around for theme check
				//$rehub_menu_page_creation_method    = 'add_menu_page';
				//$rehub_submenu_page_creation_method = 'add_submenu_page';

				$welcome_screen = add_menu_page( 'ReHub', 'ReHub', 'administrator', 'rehub', array( $this, 'rehub_welcome_screen' ), 'dashicons-rehub-logo', 3 );
				$support = add_submenu_page( 'rehub', esc_html__( 'ReHub Theme Support', 'rehub-theme' ), esc_html__( 'Support and tips', 'rehub-theme' ), 'administrator', 'rehub-support', array( $this, 'rehub_support_tab' ) );
				$plugins = add_submenu_page( 'rehub', esc_html__( 'Plugins', 'rehub-theme' ), esc_html__( 'Plugins', 'rehub-theme' ), 'administrator', 'rehub-plugins', array( $this, 'rehub_plugins_tab' ) );
				//$required_plugins = add_submenu_page( 'rehub', esc_html__( 'Required plugins', 'rehub-theme' ), esc_html__( 'Required plugins', 'rehub-theme' ), 'administrator', 'rehub-install-plugins', array( $this, 'rehub_plugins_sub' ) );
				$demo_content = add_submenu_page( 'rehub', esc_html__( 'Demo content', 'rehub-theme' ), esc_html__( 'Demo Import', 'rehub-theme' ), 'administrator', 'import_demo', array( $this, 'demo_content_sub' ));
				$demos = add_submenu_page( 'rehub', esc_html__( 'Alternative Import', 'rehub-theme' ), esc_html__( 'Alternative Import', 'rehub-theme' ), 'administrator', 'rehub-demos', array( $this, 'rehub_demos_tab' ) );	
				if ( class_exists( 'REHub_Framework' ) ) {			
					$theme_options  = add_submenu_page( 'rehub', esc_html__( 'Theme Options', 'rehub-theme' ), esc_html__( 'Theme Options', 'rehub-theme' ), 'administrator', 'vpt_option');
				}

				add_action( 'admin_print_scripts-'.$welcome_screen, array( $this, 'welcome_screen_scripts' ) );
				add_action( 'admin_print_scripts-'.$support, array( $this, 'support_screen_scripts' ) );
				add_action( 'admin_print_scripts-'.$demos, array( $this, 'demos_screen_scripts' ) );
				add_action( 'admin_print_scripts-'.$plugins, array( $this, 'plugins_screen_scripts' ) );
			}
		}

		function rehub_welcome_screen() {
			require_once( 'screens/welcome.php' );
		}

		function rehub_support_tab() {
			require_once( 'screens/support.php' );
		}

		function rehub_demos_tab() {
			require_once( 'screens/democlones.php' );
		}

		function rehub_plugins_tab() {
			require_once( 'screens/plugins.php' );
		}
		
		function demo_content_sub(){
			if ( !rh_check_plugin_active( 'one-click-demo-import/one-click-demo-import.php' ) ) { ?>
			<h2></h2>
		   <div class="notice notice-info"><p><?php esc_html_e('Please, install and activate One Click Demo Import plugin', 'rehub-theme');?> <a href="<?php echo admin_url( 'admin.php?page=rehub-plugins' );?>"><?php esc_html_e('on page', 'rehub-theme');?></a></p></div>
			<?php
			} 
		}
		
		function rehub_plugins_sub(){			
		}

		function rehub_admin_scripts() {
			if ( is_admin() ) {

			?>
			<?php 
				if (rehub_option('rehub_custom_color')) {
					$maincolor = rehub_option('rehub_custom_color');
				} 
				else {
					$maincolor = REHUB_MAIN_COLOR;			
				}
			?>	
			<?php 
				if (rehub_option('rehub_sec_color')) {
					$secondarycolor = rehub_option('rehub_sec_color');
				} 
				else {
					$secondarycolor = REHUB_SECONDARY_COLOR;
				}
			?>
			<?php 
				$boxshadow = $boxshadowhover = '';
				if (rehub_option('rehub_btnoffer_color')) {
					$btncolor = rehub_option('rehub_btnoffer_color');
				} 	
				else {
					$btncolor = REHUB_BUTTON_COLOR;
				}
				if (rehub_option('rehub_btnoffer_color_hover')) {
					$btncolorhover = rehub_option('rehub_btnoffer_color_hover');
				}else{
					$btncolorhover = $btncolor;
				}
				if (rehub_option('rehub_btnoffer_color_text')) {
					$btncolortext = rehub_option('rehub_btnoffer_color_text');
				}else{
					$btncolortext = REHUB_BUTTON_COLOR_TEXT;
				}		
			?>
			<?php 
				$bgheader = rehub_option('rehub_header_color_background');
			?>		
			<style type="text/css">
			<?php if ($bgheader):?>
				body #wp-auth-check-wrap #wp-auth-check{background: none <?php echo esc_attr($bgheader);?>}
    		<?php endif;?>
			<?php if(rehub_option('body_font_size')) : ?>
				<?php 
					$sizearray = array_map( 'trim', explode( ":", rehub_option('body_font_size') ) );
				?>
				.block-editor-block-list__layout{
					font-size:<?php echo intval($sizearray[0]);?>px;
					line-height: <?php echo (!empty($sizearray[1])) ? intval($sizearray[1]) : intval($sizearray[0])+12;?>px;	
				}
			<?php else:?>
				.block-editor-block-list__layout{
					font-size:16px;
					line-height: 28px;	
				}				
			<?php endif; ?>	
			<?php if(rehub_option('rehub_body_font')) : ?>
				.wp-block, .editor-styles-wrapper .editor-post-title__block .editor-post-title__input, .editor-styles-wrapper blockquote.is-style-large, .editor-styles-wrapper .wp-block-button .wp-block-button__link {
					font-family:"<?php echo rehub_option('rehub_body_font'); ?>", arial !important;
					font-weight:<?php echo rehub_option('rehub_body_font_weight'); ?>!important;
					font-style:<?php echo rehub_option('rehub_body_font_style'); ?> !important;			
				}
				:root {
				--rehub-body-font: <?php echo rehub_option('rehub_body_font'); ?>;
				}
			<?php else:?>
				.editor-styles-wrapper .wp-block{font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif}
			<?php endif; ?>	
			<?php if(rehub_option('rehub_headings_font')) : ?>
				.editor-styles-wrapper .editor-post-title__block .editor-post-title__input, .editor-styles-wrapper h1, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4, .editor-styles-wrapper h5, .editor-styles-wrapper h6, .wp-block-quote.is-style-large{
					font-family:"<?php echo rehub_option('rehub_headings_font'); ?>", trebuchet ms !important;
					font-weight:<?php echo rehub_option('rehub_headings_font_weight'); ?> !important;
					font-style:<?php echo rehub_option('rehub_headings_font_style'); ?> !important;
					<?php if(rehub_option('rehub_headings_font_upper') =='1') : ?>text-transform:uppercase !important;<?php endif; ?>			
				}	
				:root {
				--rehub-head-font: <?php echo rehub_option('rehub_headings_font'); ?>;
				}			
			<?php endif;?>
			<?php if(rehub_option('rehub_nav_font')) : ?>
				:root {
				--rehub-nav-font: <?php echo rehub_option('rehub_nav_font'); ?>;
				}
			<?php endif; ?>	
			<?php if(rehub_option('rehub_btn_font')) : ?>
				.wp-block-button .wp-block-button__link, 
				.def_btn, 
				.wpsm-button, 
				.wpsm-button.rehub_main_btn,
				.woocommerce button.button,
				.rehub-main-btn-bg,
				.wp-block .c-offer-box .c-offer-box__button,
				.wp-block .c-offer-listing-btn .c-offer-listing-btn__text,
				.c-ws-box-cta__btn,
				.priced_block .btn_offer_block {
					font-family:"<?php echo rehub_option('rehub_headings_font'); ?>", trebuchet ms !important;
					font-weight:<?php echo rehub_option('rehub_headings_font_weight'); ?> !important;
					font-style:<?php echo rehub_option('rehub_headings_font_style'); ?> !important;
					<?php if(rehub_option('rehub_headings_font_upper') =='1') : ?>text-transform:uppercase !important;<?php endif; ?>			
				}	
				:root {
				--rehub-btn-font: <?php echo rehub_option('rehub_btn_font'); ?>;
				}			
			<?php endif;?>
			<?php if (rehub_option('enable_smooth_btn') == 1):?>
				<?php $boxshadow = hex2rgba($btncolor, 0.25);?>
				<?php $boxshadowhover = hex2rgba($btncolorhover, 0.35);?>

				.price_count, .rehub_offer_coupon, .rh_post_layout_big_offer .priced_block .btn_offer_block, .btn_more, .rate-bar-bar, .rate-bar, .rehub-main-smooth, .re_filter_instore span.re_filtersort_btn:hover, .re_filter_instore span.active.re_filtersort_btn{border-radius: 100px}
				.news .priced_block .price_count, .blog_string  .priced_block .price_count, .main_slider .price_count{margin-right: 5px}
				.woocommerce .products.grid_woo .product, .rh_offer_list .offer_thumb .deal_img_wrap, .grid_onsale, .def_btn, .wpsm-button, .blacklabelprice{border-radius: 5px}
				.news-community, .review-top .overall-score, .rate_bar_wrap, .rh_offer_list, .woo-tax-logo, #buddypress form#whats-new-form, #buddypress div#invite-list, #buddypress #send-reply div.message-box, .rehub-sec-smooth, #wcfm-main-contentainer #wcfm-content, .wcfm_welcomebox_header{border-radius: 8px}
				.review-top .overall-score span.overall-text{border-radius: 0 0 8px 8px}
				.coupon_btn:before{display: none;}
				form.search-form.product-search-form input[type="text"]{border-radius: 100px 0 0 100px;}
				form.search-form [type="submit"]{border-radius: 0 100px 100px 0;}
				.rtl form.search-form.product-search-form input[type="text"]{border-radius: 0 100px 100px 0;}
				.rtl form.search-form [type="submit"]{border-radius: 100px 0 0 100px;}
			<?php elseif (rehub_option('enable_smooth_btn') == 2):?>
				<?php $boxshadow = hex2rgba($btncolor, 0.2);?>
				<?php $boxshadowhover = hex2rgba($btncolorhover, 0.4);?>
				.news .priced_block .price_count, .blog_string  .priced_block .price_count, .main_slider .price_count{margin-right: 5px}	
				.price_count, .rehub_offer_coupon, .btn_more, .def_btn, .rh_offer_list .offer_thumb .deal_img_wrap, .grid_onsale, .rehub-main-smooth, .re_filter_instore span.re_filtersort_btn:hover, .re_filter_instore span.active.re_filtersort_btn, .blacklabelprice{border-radius: 4px}
				.news-community, .woocommerce .products.grid_woo .product, .rh_offer_list, .woo-tax-logo, .rehub-sec-smooth, .rate-bar-bar, .rate-bar{border-radius: 5px}
				form.search-form.product-search-form input[type="text"]{border-radius: 4px 0 0 4px;}
				form.search-form [type="submit"]{border-radius: 0 4px 4px 0;}
				.rtl form.search-form.product-search-form input[type="text"]{border-radius: 0 4px 4px 0;}
				.rtl form.search-form [type="submit"]{border-radius: 4px 0 0 4px;}
			<?php endif;?>
			.wpsm-button.rehub_main_btn,
			.woocommerce button.button,
			.rehub-main-btn-bg,
			.wp-block .c-offer-box .c-offer-box__button,
			.wp-block .c-offer-listing-btn .c-offer-listing-btn__text,
			.c-ws-box-cta__btn,
			.priced_block .btn_offer_block,
			.woocommerce a.woo_loop_btn,
			.wc-block-grid__product-add-to-cart.wp-block-button .wp-block-button__link
			{ background: none <?php echo ''.$btncolor ?> !important; 
				color: <?php echo ''.$btncolortext ?> !important; 
				fill: <?php echo ''.$btncolortext ?> !important;
				border:none !important;
				text-decoration: none !important; 
				outline: 0; 
				text-shadow: none;
				<?php 
					if($boxshadow){
						echo 'box-shadow: -1px 6px 19px '.$boxshadow.' !important;';
					}else{
						echo 'box-shadow: 0 2px 2px #E7E7E7 !important;';
					}
				?>		
				<?php 
					if(rehub_option('enable_smooth_btn') == 1){
						echo 'border-radius: 100px !important;';
					}
					elseif (rehub_option('enable_smooth_btn') == 2){
						echo 'border-radius: 4px !important;';
					}
					else{
						echo 'border-radius: 0 !important;';
					}
				?>		
			}
			.widget_merchant_list .buttons_col{background-color: <?php echo ''.$btncolor ?> !important;}
			.widget_merchant_list .buttons_col a{color: <?php echo ''.$btncolortext ?> !important;}
			.rehub-main-btn-bg > a{color: <?php echo ''.$btncolortext ?> !important;}
			.rehub-main-color, .wp-block-rehub-offer-listing .c-offer-listing__title, .wp-block .c-offer-listing .c-offer-listing__read-more{color: <?php echo ''.$maincolor; ?>;}		
			.rehub-main-color-bg{background-color: <?php echo ''.$maincolor; ?>;}	
			.rehub-main-color-border, nav.top_menu > ul > li.vertical-menu.border-main-color .sub-menu, .rh-main-bg-hover:hover, .wp-block-quote, ul.def_btn_link_tabs li.active a, .editor-styles-wrapper .wp-block-pullquote{border-color: <?php echo ''.$maincolor; ?> !important;}
			.rehub-sec-color-bg,
			.rh-slider-arrow,
			form.search-form [type="submit"]{ background: <?php echo ''.$secondarycolor ?> !important; color: #fff !important; outline: 0}
			.rh-slider-thumbs-item--active, .rehub-sec-color-border{border-color: <?php echo ''.$secondarycolor ?>}
			.wp-block-pullquote cite, .wp-block-pullquote footer, .wp-block-pullquote__citation{color: #111 !important}	
			.wp-block-freeform.block-library-rich-text__tinymce a, .wp-block-quote.is-style-large p, .wp-block-pullquote{color: <?php echo ''.$maincolor; ?> !important; text-decoration: none !important;}
			.rh-admin-note{background: lightblue; padding: 15px;margin: 15px 0;border-radius: 5px;border: 1px solid #65b2c7; font-size: 15px}
			.re-line-badge.re-line-badge--default{font-size:11px;line-height:1;text-shadow:none}.re-line-badge.re-line-badge--default span:after,.re-line-badge.re-line-badge--default span:before{display:none}
			.rh-border-line:after{color: <?php echo ''.$maincolor; ?>;}
			.widget.deal_daywoo, .elementor-widget-wpsm_woofeatured .deal_daywoo{border: 3px solid <?php echo ''.$maincolor; ?>; padding: 20px; background: #fff; }
			.deal_daywoo .title{color: <?php echo ''.$maincolor; ?>}
			.deal_daywoo .wpsm-bar-bar{background-color: <?php echo ''.$maincolor; ?> !important}
			/*.wp-block{max-width:760px;}*/
			@media (min-width:600px){.editor-post-title__block:not(.is-focus-mode).is-selected .editor-post-title__input{box-shadow:-3px 0 0 0 <?php echo ''.$maincolor; ?>}}
			@media screen and (max-width: 782px) {
				#wp-toolbar > ul > .rehub-menu {
					display: block;
				}

				#wpadminbar .rehub-menu > .ab-item .ab-icon {
					padding-top: 6px !important;
					height: 40px !important;
					font-size: 30px !important;
				}
			}
			#wpadminbar .rehub-menu > .ab-item .ab-icon:before,
            .dashicons-rehub-logo:before{
                content: "\f115";
                font-style: normal;
                font-weight: normal;
                font-variant: normal;
                text-transform: none;
                line-height: 1;

                /* Better Font Rendering =========== */
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
            .mce-i-footer-columns{background: url(<?php echo get_template_directory_uri();?>/shortcodes/tinyMCE/images/column.png) #eee !important;}
            .prdctfltr-menu li.pink{display: none;}    
            .column-elementor_library_type a:nth-child(2), .menu-icon-elementor_library .wp-first-item, .svx-license{display: none;}
             /*Elementor fix*/

            .ocdi{max-width: 1050px !important} /*fix for demo import */
			.ocdi-install-plugins-content-content{display:none !important}

			:root {
				--rehub-main-color: <?php echo ''.$maincolor; ?>;
				--rehub-sec-color: <?php echo ''.$secondarycolor; ?>;
				--rehub-main-btn-bg: <?php echo ''.$btncolor; ?>;
				<?php if (rehub_option('rehub_color_link')):?>
					--rehub-link-color: <?php echo rehub_option('rehub_color_link');?>;
				<?php elseif($maincolor):?>
					--rehub-link-color: <?php echo ''.$maincolor;?>;
				<?php endif; ?>
				
			}
  
            </style>
            <script type="text/javascript">
            	jQuery(function() {
            		if (jQuery('#footerfirst').length > 0) { 
						jQuery( document ).on( 'tinymce-editor-setup', function( event, editor ) {
						    editor.settings.toolbar1 += ',footercolumns,footercontact';
						    editor.addButton( 'footercolumns', {
						        text: '',
						        icon: 'footer-columns',
						        onclick: function () {
						            editor.insertContent( '[wpsm_column size="one-half"]<div class="widget_recent_entries"><div class="title">For customers</div><ul><li><a href="#">First link</a></li><li><a href="#">Second Link</a></li><li><a href="#">Third link</a></li><li><a href="#">Fourth link</a></li></ul></div>[/wpsm_column][wpsm_column size="one-half" position="last"]<div class="widget_recent_entries"><div class="title">For vendors</div><ul><li><a href="#">First link</a></li><li><a href="#">Second Link</a></li><li><a href="#">Third link</a></li><li><a href="#">Fourth link</a></li></ul></div>[/wpsm_column]' );
						        }
						    });
						    editor.addButton( 'footercontact', {
						        text: '',
						        icon: 'footer-contact',
						        onclick: function () {
						            editor.insertContent( '<div class="tabledisplay footer-contact mb30"><div class="left-ficon-contact celldisplay"></div><div class="fcontact-body celldisplay"><span class="call-us-text">Got Questions? Call us 24/7!</span> <span class="call-us-number">(800) 5000-8888</span> <span class="other-fcontact"><a href="mailto:#">test@gmail.com</a></span></div></div>' );
						        }
						    });						    
						});
					}
					if(jQuery('.post-type-elementor_library .nav-tab-wrapper').length > 1){ //Fix Elementor library
						jQuery('.post-type-elementor_library .nav-tab-wrapper').first().hide();
					}

					if(jQuery('.elementor-template_library-blank_state').length > 1){ //Fix Elementor library
						jQuery('.elementor-template_library-blank_state').first().hide();
					}	
					
					if(jQuery('.button-cegg-banner').length){
						jQuery('.button-cegg-banner').attr("href", "https://www.keywordrush.com/externalimporter?ref=WPSOUL");
					}
				});
            </script>
            <?php
			}
		}

		function welcome_screen_scripts(){
			wp_enqueue_style( 'rehub_admin_css', REHUB_ADMIN_DIR . 'screens/css/rehub-admin.css' );
		}

		function support_screen_scripts(){
			wp_enqueue_style( 'rehub_admin_css', REHUB_ADMIN_DIR . 'screens/css/rehub-admin.css' );
		}

		function demos_screen_scripts(){
			wp_enqueue_style( 'rehub_admin_css', REHUB_ADMIN_DIR . 'screens/css/rehub-admin.css' );
			wp_enqueue_script( 'rehub_admin_js', REHUB_ADMIN_DIR . 'screens/js/rehub-demo.js' );
		}

		function plugins_screen_scripts(){
			wp_enqueue_style( 'rehub_admin_css', REHUB_ADMIN_DIR . 'screens/css/rehub-admin.css' );
		}

		function plugin_link( $item ) {
			$installed_plugins = get_plugins();
			$item['sanitized_plugin'] = $item['name'];

			// We have a repo plugin
			if ( ! $item['version'] ) {
				$item['version'] = TGM_Plugin_Activation::$instance->does_plugin_have_update( $item['slug'] );
			}

			/** We need to display the 'Install' hover link */
			if ( ! isset( $installed_plugins[$item['file_path']] ) ) {
				$actions = array(
					'install' => sprintf(
						'<a href="%1$s" class="button button-primary" title="Install %2$s">Install</a>',
						esc_url( wp_nonce_url(
							add_query_arg(
								array(
									'page'          => urlencode( TGM_Plugin_Activation::$instance->menu ),
									'plugin'        => urlencode( $item['slug'] ),
									'plugin_name'   => urlencode( $item['sanitized_plugin'] ),
									'plugin_source' => urlencode( $item['source'] ),
									'tgmpa-install' => 'install-plugin',
									'return_url'    => 'rehub-plugins'
								),
								TGM_Plugin_Activation::$instance->get_tgmpa_url()
							),
							'tgmpa-install',
							'tgmpa-nonce'
						) ),
						$item['sanitized_plugin']
					),
				);
			}
			/** We need to display the 'Activate' hover link */
			elseif ( is_plugin_inactive( $item['file_path'] ) ) {
				$actions = array(
					'activate' => sprintf(
						'<a href="%1$s" class="button button-primary" title="Activate %2$s">Activate</a>',
						esc_url( add_query_arg(
							array(
								'plugin'               => urlencode( $item['slug'] ),
								'plugin_name'          => urlencode( $item['sanitized_plugin'] ),
								'plugin_source'        => urlencode( $item['source'] ),
								'rehub-activate'       => 'activate-plugin',
								'rehub-activate-nonce' => wp_create_nonce( 'rehub-activate' ),
							),
							admin_url( 'admin.php?page=rehub-plugins' )
						) ),
						$item['sanitized_plugin']
					),
				);
			}
			/** We need to display the 'Update' hover link */
			elseif ( version_compare( $installed_plugins[$item['file_path']]['Version'], $item['version'], '<' ) ) {
				$actions = array(
					'update' => sprintf(
						'<a href="%1$s" class="button button-primary" title="Install %2$s">Update</a>',
						wp_nonce_url(
							add_query_arg(
								array(
									'page'          => urlencode( TGM_Plugin_Activation::$instance->menu ),
									'plugin'        => urlencode( $item['slug'] ),
									
									'tgmpa-update'  => 'update-plugin',
									'plugin_source' => urlencode( $item['source'] ),
									'version'       => urlencode( $item['version'] ),
									'return_url'    => 'rehub-plugins'
								),
								TGM_Plugin_Activation::$instance->get_tgmpa_url()
							),
							'tgmpa-update',
							'tgmpa-nonce'
						),
						$item['sanitized_plugin']
					),
				);
			} elseif ( rh_check_plugin_active( $item['file_path'] ) ) {
				$actions = array(
					'deactivate' => sprintf(
						'<a href="%1$s" class="button button-primary" title="Deactivate %2$s">Deactivate</a>',
						esc_url( add_query_arg(
							array(
								'plugin'                 => urlencode( $item['slug'] ),
								'plugin_name'            => urlencode( $item['sanitized_plugin'] ),
								'plugin_source'          => urlencode( $item['source'] ),
								'rehub-deactivate'       => 'deactivate-plugin',
								'rehub-deactivate-nonce' => wp_create_nonce( 'rehub-deactivate' ),
							),
							admin_url( 'admin.php?page=rehub-plugins' )
						) ),
						$item['sanitized_plugin']
					),
				);
			}

			return $actions;
		}
	}

	new Rehub_Admin;
}

//////////////////////////////////////////////////////////////////
// UI for standard WP pages of login
//////////////////////////////////////////////////////////////////

function rh_standard_wp_pages_styles() { ?>
	<?php 
		$logoimage = rehub_option('rehub_logo'); 
		$bg = rehub_option('rehub_header_color_background');
		$btn = rehub_option('rehub_btnoffer_color');
		$btntxt = rehub_option('rehub_btnoffer_color_text');
	?>
    <style type="text/css">
    	body:not(.interim-login) #login{width:450px !important;}
    	body.login form{box-shadow: 0 5px 23px rgba(188, 207, 219, 0.35); border:none;}
    	body.login #login_error, body.login .message, body.login .success, body.login form{border-radius: 10px;} 
    	body.login.interim-login h1 a{display:none;}
		.interim-login #login{padding-top:20px}
    	<?php if($logoimage):?>
    		#login h1 a, body.login h1 a {background-image:url(<?php echo esc_url($logoimage);?>);width:auto; background-size:auto; background-position:bottom center;}
    	<?php else:?>
    		#login h1 a, body.login h1 a {display:none;}
    	<?php endif;?>
    	<?php if ($bg):?>
    		body.login{background: none <?php echo esc_attr($bg);?>}
    		body.login #backtoblog a, body.login #nav a, body.login #backtoblog a:hover, body.login #nav a:hover, body.login h1 a:hover, body.login #backtoblog a:focus, body.login #nav a:focus, body.login h1 a:focus{color:#ccc;}
    		body.login a{color:#999; text-decoration:underline;}
    	<?php else:?>
    		
    	<?php endif;?>
    	body.login.wp-core-ui:not(.login-action-confirm_admin_email) .button-group.button-large .button, body.login.wp-core-ui:not(.login-action-confirm_admin_email) .button.button-large{
			padding:4px; font-size:18px; width:100%;
		}
		<?php if($btn):?>
			body.login.wp-core-ui:not(.login-action-confirm_admin_email) .button-group.button-large .button, body.login.wp-core-ui:not(.login-action-confirm_admin_email) .button.button-large{background: <?php echo esc_attr($btn);?>; border-color:transparent}
		<?php endif;?>
		<?php if($btntxt):?>
			body.login.wp-core-ui:not(.login-action-confirm_admin_email) .button-group.button-large .button, body.login.wp-core-ui:not(.login-action-confirm_admin_email) .button.button-large{color: <?php echo esc_attr($btntxt);?>; }
		<?php endif;?>
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'rh_standard_wp_pages_styles' );

function rh_standard_wp_pages_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'rh_standard_wp_pages_logo_url' );

// Omit closing PHP tag to avoid "Headers already sent" issues.