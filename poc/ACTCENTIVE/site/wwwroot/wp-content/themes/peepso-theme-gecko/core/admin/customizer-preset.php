<?php

//  Exit if accessed directly
if (!defined('ABSPATH')) {
	exit();
}

if (!class_exists('Gecko_Customizer_Preset')) {
	/**
	 * Gecko_Customizer_Preset class.
	 *
	 * @since 3.0.0.0
	 */
	class Gecko_Customizer_Preset {
		private static $instance = null;

		public static function get_instance() {
			if (null === self::$instance) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Default constructor.
		 *
		 * @since 3.0.0.0
		 */
		private function __construct() {
		}

		/**
		 * Get default preset settings.
		 *
		 * @since 3.0.0.0
		 *
		 * @return array
		 */
		protected function get_default() {
			// Default backend settings.
			$settings = [
				'opt_custom_logo' => '',
				'opt_custom_mobile_logo' => '',
				'opt_custom_icon' => '',
				'opt_logo_link_redirect' => '',
				'opt_header_vis' => '1',
				'opt_header_full_width' => '',
				'opt_header_logo_desktop_vis' => '1',
				'opt_header_logo_mobile_vis' => '1',
				'opt_header_tagline_vis' => '',
				'opt_header_tagline_mobile_vis' => '',
				'opt_enable_longmenu' => '',
				'opt_show_header_sidebar_logo' => '1',
				'opt_show_header_sidebar_position' => '1',
				'opt_user_preset' => '',
				'opt_show_search_in_header' => '1',
				'opt_zoom_feature' => '',
				//'opt_show_adminbar' => '2',
				'opt_limit_page_options' => '',
				'opt_scroll_to_top' => '1',
				'opt_edit_link_bottom' => '',
				'opt_sticky_sidebar' => '',
				'opt_scroll_sidebar_left' => '',
				'opt_scroll_sidebar_right' => '',
				'opt_sidebar_left_vis' => '1',
				'opt_sidebar_right_vis' => '1',
				'opt_sidebar_left_mobile_vis' => '1',
				'opt_sidebar_right_mobile_vis' => '1',
				'opt_blog_sidebars' => '1',
				'opt_limit_blog_post' => '',
				'opt_limit_blog_post_words_number' => '55',
				'opt_blog_update' => '1',
				'opt_blog_grid_col' => '2',
				'opt_blog_grid' => '',
				'opt_blog_image_caption' => '',
				'opt_blog_single_post_image_caption' => '',
				'opt_archives_grid' => '',
				'opt_search_grid' => '',
				'opt_sidebar_left_search_vis' => '1',
				'opt_sidebar_right_search_vis' => '1',
				'opt_search_header_vis' => '1',
				'opt_search_footer_vis' => '1',
				'opt_header_menu_search_vis' => '1',
				'opt_search_full_width_layout' => '0',
				'opt_search_full_width_header' => '0',
				'opt_sticky_bar_above_full_width' => '0',
				'opt_sticky_bar_under_full_width' => '0',
				'opt_sticky_bar_mobile_full_width' => '0',
				'opt_show_sidenav' => '',
				'opt_landing_footer_full_width' => '',
				'opt_yoastseo_breadcrumbs' => '1',

				'opt_footer_vis' => '1',
				'opt_footer_text_line_1' => get_bloginfo('name'),
				'opt_footer_text_line_2' => 'All rights reserved',
				'opt_footer_sticky' => '1',
				// 'opt_widget_icon' => '1',
				'gc-body-bg-image' => '',
				// PeepSo
				'opt_ps_side_to_side' => '',
				'opt_ps_profile_layout' => '1',
				'opt_ps_profile_page_cover_centered' => '',
				'opt_ps_navbar_sticky' => '',
				// WooCommerce
				'opt_woo_builder' => '',
				'opt_woo_sidebars' => '1',
				'opt_woo_mobile_single_col' => '1',
				'opt_woo_columns' => '3',
				// LearnDash
				'opt_ld_sidebars' => '1',
				// Tutor LMS
				'opt_tutorlms_overrides' => '1',
			];

			// Default CSS variables.
			$css_vars = [
				'--COLOR--PRIMARY' => '#6271eb',
				'--COLOR--PRIMARY--SHADE' => '#bfc5f1',
				'--COLOR--PRIMARY--LIGHT' => '#8e99f1',
				'--COLOR--PRIMARY--ULTRALIGHT' => '#e9ebfa',
				'--COLOR--PRIMARY--DARK' => '#4e5bc5',

				'--COLOR--ALT' => '#27b194',
				'--COLOR--ALT--LIGHT' => '#60d6bd',
				'--COLOR--ALT--DARK' => '#199c80',

				'--COLOR--GRADIENT--DEG' => '0deg',
				'--COLOR--GRADIENT--ONE' => 'var(--COLOR--PRIMARY--LIGHT)',
				'--COLOR--GRADIENT--TWO' => 'var(--COLOR--ALT--LIGHT)',
				'--COLOR--GRADIENT--TEXT' => '#fff',
				'--COLOR--GRADIENT--LINKS' => 'rgba(255, 255, 255, 0.8)',
				'--COLOR--GRADIENT--LINKS--HOVER' => '#fff',
				'--COLOR--GRADIENT' => 'linear-gradient(var(--COLOR--GRADIENT--DEG), var(--COLOR--GRADIENT--ONE) 0%, var(--COLOR--GRADIENT--TWO) 100%)',

				'--COLOR--INFO' => '#0085ff',
				'--COLOR--INFO--LIGHT' => '#BBDEFB',
				'--COLOR--INFO--ULTRALIGHT' => '#E3F2FD',
				'--COLOR--INFO--DARK' => '#016df7',

				'--COLOR--SUCCESS' => '#66BB6A',
				'--COLOR--SUCCESS--LIGHT' => '#C8E6C9',
				'--COLOR--SUCCESS--ULTRALIGHT' => '#E8F5E9',
				'--COLOR--SUCCESS--DARK' => '#4CAF50',

				'--COLOR--WARNING' => '#FFA726',
				'--COLOR--WARNING--LIGHT' => '#FFE0B2',
				'--COLOR--WARNING--ULTRALIGHT' => '#FFF3E0',
				'--COLOR--WARNING--DARK' => '#F57C00',

				'--COLOR--ABORT' => '#E53935',
				'--COLOR--ABORT--LIGHT' => '#FFCDD2',
				'--COLOR--ABORT--ULTRALIGHT' => '#FFEBEE',
				'--COLOR--ABORT--DARK' => '#D32F2F',

				'--FONT-SIZE' => '18px',
				'--LINE-HEIGHT' => '1.4',

				// TEMPORARY LIGHT PRESET
				'--BORDER-RADIUS' => '8px',
				'--COLOR--APP' => '#fff',
				'--COLOR--APP--GRAY' => '#F8F9FB',
				'--COLOR--APP--LIGHTGRAY' => '#FBFBFB',
				'--COLOR--APP--DARKGRAY' => '#ECEFF4',
				'--COLOR--APP--DARK' => '#46494f',
				'--COLOR--APP--DARKER' => '#202124',
				'--COLOR--TEXT' => '#494954',
				'--COLOR--TEXT--LIGHT' => '#91919d',
				'--COLOR--TEXT--LIGHTEN' => '#b0b0b9',
				'--COLOR--TEXT--INVERT' => '#fff',
				'--COLOR--HEADING' => '#333',
				'--COLOR--LINK' => 'var(--COLOR--PRIMARY)',
				'--COLOR--LINK-HOVER' => 'var(--COLOR--PRIMARY--DARK)',
				'--COLOR--LINK-FOCUS' => 'var(--COLOR--PRIMARY--DARK)',
				'--DIVIDER' => 'rgba(70, 77, 87, 0.15)',
				'--DIVIDER--LIGHT' => 'rgba(70, 77, 87, 0.1)',
				'--DIVIDER--LIGHTEN' => 'rgba(18, 38, 65, 0.0901)',
				'--DIVIDER--DARK' => 'rgba(70, 77, 87, 0.25)',
				'--DIVIDER--R' => 'rgba(255, 255, 255, 0.1)',
				'--DIVIDER--R--LIGHT' => 'rgba(255, 255, 255, 0.05)',
				'--BOX-SHADOW-DIS' => '0px',
				'--BOX-SHADOW-BLUR' => '0px',
				'--BOX-SHADOW-THICKNESS' => '1px',
				'--BOX-SHADOW-COLOR' => 'rgba(18, 38, 65, 0.0901)',
				'--BOX-SHADOW--HARD' => '0 var(--BOX-SHADOW-DIS) var(--BOX-SHADOW-BLUR) var(--BOX-SHADOW-THICKNESS) var(--BOX-SHADOW-COLOR)',

				// THEME
				'--c-gc-layout-width' => '1280px',
				'--c-gc-main-column' => '2fr',
				'--c-gc-main-column-maxwidth' => '100%',
				'--c-gc-layout-gap' => '20px',
				'--c-gc-header-height' => '80px',
				'--c-gc-header-bg' => 'var(--COLOR--APP)',
				'--c-gc-header-text-color' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-header-link-color' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-header-link-color-hover' => 'var(--COLOR--TEXT)',
				'--c-gc-header-link-active-indicator' => 'var(--COLOR--PRIMARY)',
				'--c-gc-header-font-size' => '100%',
				'--c-gc-header-sticky' => 'fixed',
				'--c-gc-header-sticky-mobile' => 'fixed',
				'--c-gc-header-menu-align' => 'flex-start',
				'--c-gc-header-menu-font-size' => '90%',
				'--c-gc-header-logo-color' => '#333',
				'--c-gc-header-tagline-color' => '#555',
				'--c-gc-header-logo-height' => '60px',
				'--c-gc-header-logo-height-mobile' => '60px',
				'--c-gc-header-tagline-font-size' => '90%',
				'--c-gc-header-sidebar-bg' => 'var(--COLOR--APP)',
				'--c-gc-header-sidebar-overlay-bg' => 'var(--COLOR--APP)',
				'--c-gc-header-sidebar-close-color' => 'var(--COLOR--HEADING)',
				'--c-gc-header-sidebar-arrow-color' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-header-sidebar-logo-height' => '60px',
				'--c-gc-header-sidebar-logo-bg' => 'var(--COLOR--APP)',
				'--c-gc-header-sidebar-logo-text-color' => 'var(--COLOR--HEADING)',
				'--c-gc-header-sidebar-logo-font-size' => '150%',
				'--c-gc-header-sidebar-menu-links-color' => 'var(--COLOR--TEXT)',
				'--c-gc-header-sidebar-menu-active-link-color'=> 'var(--COLOR--PRIMARY)',
				'--c-gc-header-sidebar-menu-active-indicator-color'=> 'var(--COLOR--PRIMARY)',
				'--c-gc-header-sidebar-menu-bg' => 'var(--COLOR--APP)',
				'--c-gc-header-sidebar-menu-font-size' => '100%',
				'--c-gc-header-sidebar-above-menu-text-color' => 'var(--COLOR--TEXT)',
				'--c-gc-header-sidebar-above-menu-links-color' => 'var(--COLOR--LINK)',
				'--c-gc-header-sidebar-above-menu-bg' => 'var(--COLOR--APP)',
				'--c-gc-header-sidebar-under-menu-text-color' => 'var(--COLOR--TEXT)',
				'--c-gc-header-sidebar-under-menu-links-color' => 'var(--COLOR--LINK)',
				'--c-gc-header-sidebar-under-menu-bg' => 'var(--COLOR--APP)',
				'--c-gc-header-search-vis' => 'block',
				'--c-gc-header-search-vis-mobile' => 'block',
				'--c-gc-footer-col' => '4',
				'--c-gc-footer-bg' => 'var(--COLOR--APP)',
				'--c-gc-footer-text-color' => 'var(--COLOR--TEXT)',
				'--c-gc-footer-text-color-light' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-footer-links-color' => 'var(--COLOR--LINK)',
				'--c-gc-footer-links-color-hover' => 'var(--COLOR--LINK-HOVER)',
				'--c-gc-footer-widgets-vis' => 'grid',
				'--c-gc-footer-widgets-vis-mobile' => 'block',
				'--c-gc-sidebar-left-width' => '1fr',
				'--c-gc-sidebar-right-width' => '1fr',
				'--c-gc-sidebar-widgets-gap' => '20px',
				'--c-gc-post-image-max-height' => '100%',
				'--c-gc-blog-image-max-height' => '100%',
				'--c-gc-landing-footer-widgets-vis' => 'none',
				'--c-gc-landing-footer-social-widgets-vis' => 'none',
				'--c-gc-sidenav-menu-icon' => 'var(--GC-COLOR--TEXT)',
				'--c-gc-sidenav-menu-icon-hover' => 'var(--GC-COLOR--PRIMARY)',
				'--c-gc-sidenav-menu-icon-bg-hover' => 'var(--COLOR--PRIMARY--ULTRALIGHT)',
				
				'--c-gc-body-bg-image-fixed' => 'unset',
				'--c-gc-body-bg-image-size' => 'auto',
				'--c-gc-body-bg-image-repeat' => 'no-repeat',

				// WIDGETS
				'--c-gc-widgets-top-col' => '4',
				'--c-gc-widgets-bottom-col' => '4',
				'--c-gc-widgets-top-vis' => 'block',
				'--c-gc-widgets-top-vis-mobile' => 'block',
				'--c-gc-widgets-bottom-vis' => 'block',
				'--c-gc-widgets-bottom-vis-mobile' => 'block',
				'--c-gc-widgets-above-content-vis' => 'block',
				'--c-gc-widgets-above-content-vis-mobile' => 'block',
				'--c-gc-widgets-under-content-vis' => 'block',
				'--c-gc-widgets-under-content-vis-mobile' => 'block',

				'--c-gc-widget-bg' => 'var(--COLOR--APP)',
				'--c-gc-widget-text-color' => 'var(--COLOR--TEXT)',
				// '--c-gc-widget-links-color' => 'var(--COLOR--LINK)',
				// '--c-gc-widget-links-color-hover' => 'var(--COLOR--LINK--HOVER)',

				'--c-gc-sticky-bar-under-bg' => 'var(--COLOR--APP)',
				'--c-gc-sticky-bar-under-text-color' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-sticky-bar-under-link-color' => 'var(--COLOR--TEXT)',
				'--c-gc-sticky-bar-under-link-color-hover' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-sticky-bar-under-font-size' => '100%',
				'--c-gc-sticky-bar-under-add-padd' => '0',
				'--c-gc-sticky-bar-under-vis' => 'block',
				'--c-gc-sticky-bar-under-vis-mobile' => 'block',

				'--c-gc-sticky-bar-above-bg' => 'var(--COLOR--APP)',
				'--c-gc-sticky-bar-above-text-color' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-sticky-bar-above-link-color' => 'var(--COLOR--TEXT)',
				'--c-gc-sticky-bar-above-link-color-hover' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-sticky-bar-above-font-size' => '100%',
				'--c-gc-sticky-bar-above-add-padd' => '0',
				'--c-gc-sticky-bar-above-vis' => 'block',
				'--c-gc-sticky-bar-above-vis-mobile' => 'block',

				'--c-gc-sticky-bar-mobile-bg' => 'var(--COLOR--APP)',
				'--c-gc-sticky-bar-mobile-text-color' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-sticky-bar-mobile-link-color' => 'var(--COLOR--TEXT)',
				'--c-gc-sticky-bar-mobile-link-color-hover' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-gc-sticky-bar-mobile-font-size' => '100%',
				'--c-gc-sticky-bar-mobile-add-padd' => '0',

				'--s-widget--gradient-deg' => 'var(--COLOR--GRADIENT--DEG)',
				'--s-widget--gradient-bg' => 'var(--COLOR--GRADIENT--ONE)',
				'--s-widget--gradient-bg-2' => 'var(--COLOR--GRADIENT--TWO)',
				'--s-widget--gradient-text' => 'var(--COLOR--GRADIENT--TEXT)',
				'--s-widget--gradient-links' => 'var(--COLOR--GRADIENT--LINKS)',
				'--s-widget--gradient-links-hover' => 'var(--COLOR--GRADIENT--LINKS--HOVER)',

				// Temporary settings - BETA 3
				'--c-gc-show-page-title' => 'none',
				'--c-ps-avatar-style' => '100%',
				'--c-gc-body-bg' => '#F5F8FC',
				'--GC-FONT-FAMILY' => 'Rubik',

				// PeepSo - Post
				'--c-ps-post-gap' => '20px',
				'--c-ps-post-bg' => 'var(--COLOR--APP)',
				'--c-ps-post-text-color' => 'var(--COLOR--TEXT)',
				'--c-ps-post-text-color-light' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-ps-post-font-size' => '16px',
				'--c-ps-post-pinned-border-color' => 'var(--COLOR--PRIMARY--LIGHT)',
				'--c-ps-post-pinned-border-size' => '3px',
				'--c-ps-post-pinned-text-color' => 'var(--COLOR--PRIMARY--LIGHT)',

				'--c-ps-post-photo-width' => 'auto',
				'--c-ps-post-photo-limit-width' => '100%',
				'--c-ps-post-photo-height' => '500px',
				'--c-ps-post-gallery-width' => '100%',
				'--c-ps-post-attachment-bg' => 'var(--COLOR--APP--GRAY)',

				'--c-ps-btn-bg' => 'var(--COLOR--APP--GRAY)',
				'--c-ps-btn-color' => 'var(--COLOR--TEXT)',
				'--c-ps-btn-bg-hover' => 'var(--COLOR--APP--DARKGRAY)',
				'--c-ps-btn-color-hover' => 'var(--COLOR--TEXT)',
				'--c-ps-btn-action-bg' => 'var(--COLOR--PRIMARY)',
				'--c-ps-btn-action-color' => '#fff',
				'--c-ps-btn-action-bg-hover' => 'var(--COLOR--PRIMARY--DARK)',
				'--c-ps-btn-action-color-hover' => '#fff',

				'--c-ps-navbar-bg' => 'var(--COLOR--APP)',
				'--c-ps-navbar-links-color' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-ps-navbar-links-color-hover' => 'var(--COLOR--TEXT)',
				'--c-ps-navbar-font-size' => '14px',
				'--c-ps-navbar-icons-size' => '16px',

				'--c-ps-postbox-bg' => 'var(--COLOR--APP)',
				'--c-ps-postbox-text-color' => 'var(--COLOR--TEXT)',
				'--c-ps-postbox-text-color-light' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-ps-postbox-icons-color' => 'var(--c-ps-postbox-text-color-light)',
				'--c-ps-postbox-icons-active-color' => 'var(--COLOR--PRIMARY--LIGHT)',
				'--c-ps-postbox-type-bg' => '#f3f4f5',
				'--c-ps-postbox-type-bg-hover' => '#ecedee',
				'--c-ps-postbox-type-icons-active-color' => 'var(--c-ps-postbox-icons-active-color)',
				'--c-ps-postbox-separator-color' => 'var(--DIVIDER--LIGHT)',
				'--c-ps-postbox-dropdown-bg' => 'var(--COLOR--APP)',
				'--c-ps-postbox-dropdown-bg-light' => 'var(--COLOR--APP--GRAY)',
				'--c-ps-postbox-dropdown-text-color' => 'var(--COLOR--TEXT)',
				'--c-ps-postbox-dropdown-icon-color' => 'var(--COLOR--TEXT--LIGHT)',
				'--c-ps-checkbox-border' => 'rgba(0,0,0, .1)', // add as option

				// User profile
				'--c-ps-profile-cover-height' => '40%',
				'--c-ps-profile-avatar-size' => '160px',

				// Groups
				'--c-ps-group-focus-cover-height' => '40%',
				'--c-ps-group-focus-avatar-size' => '160px',

				'--c-ps-bubble-bg' => '#FFA726',
				'--c-ps-bubble-color' => '#fff',
				'--c-ps-notification-unread-bg' => 'var(--COLOR--PRIMARY--ULTRALIGHT)',

				'--c-ps-chat-window-notif-bg' => 'var(--COLOR--WARNING)',
				'--c-ps-chat-message-bg' => 'var(--COLOR--APP--DARKGRAY)',
				'--c-ps-chat-message-text-color' => 'var(--COLOR--TEXT)',
				'--c-ps-chat-message-bg-me' => 'var(--COLOR--PRIMARY)',
				'--c-ps-chat-message-text-color-me' => '#fff',

				'--c-ps-poll-item-color' => 'var(--COLOR--TEXT)',
				'--c-ps-poll-item-bg' => 'var(--COLOR--APP--GRAY)',
				'--c-ps-poll-item-bg-fill' => 'var(--COLOR--PRIMARY--SHADE)',

				'--c-ps-hashtag-bg' => 'var(--COLOR--PRIMARY--ULTRALIGHT)',
				'--c-ps-hashtag-color' => 'var(--COLOR--PRIMARY--DARK)',

				'--c-ps-hashtag-postbox-bg' => 'var(--PS-COLOR--PRIMARY--LIGHT)',
				'--c-ps-hashtag-postbox-color' => '#fff',

				// PeepSo - Landing box
				'--c-ps-landing-background-color' => 'var(--COLOR--APP--GRAY)',
				'--c-ps-landing-image-height-mobile' => '60%',
				'--c-ps-landing-image-position-mobile' => 'center',
				'--c-ps-landing-image-size-mobile' => 'cover',
				'--c-ps-landing-image-repeat-mobile' => 'no-repeat',
				'--c-ps-landing-image-height' => '40%',
				'--c-ps-landing-image-position' => 'center',
				'--c-ps-landing-image-size' => 'cover',
				'--c-ps-landing-image-repeat' => 'no-repeat',
			];

			return [
				'settings' => $settings,
				'css_vars' => $css_vars,
			];
		}

		/**
		 * Get all presets.
		 *
		 * @since 3.0.0.0
		 *
		 * @return array
		 */
		public function list($defaults = TRUE) {
			$presets = [];

			if($defaults) {
                $defaults = $this->get_default();
                $default_settings = $defaults['settings'];
                $default_css_vars = $defaults['css_vars'];

                $presets['light'] = [
                    'id' => 'light',
                    'label' => __('Gecko:', 'peepso-theme-gecko') . ' ' . __('Light', 'peepso-theme-gecko'),
                    'readonly' => true,
                    'settings' => array_merge($default_settings, []),
                    'css_vars' => array_merge($default_css_vars, []),
                ];

                $presets['dark'] = [
                    'id' => 'dark',
                    'label' => __('Gecko:', 'peepso-theme-gecko') . ' ' . __('Dark', 'peepso-theme-gecko'),
                    'readonly' => true,
                    'settings' => array_merge($default_settings, []),
                    'css_vars' => array_merge($default_css_vars, [
											'--COLOR--PRIMARY' => '#fffd01',
											'--COLOR--PRIMARY--SHADE' => '#868524',
											'--COLOR--PRIMARY--LIGHT' => '#e0de38',
											'--COLOR--PRIMARY--ULTRALIGHT' => '#84834a',
											'--COLOR--PRIMARY--DARK' => '#d5d302',
											'--COLOR--APP' => '#191919',
											'--COLOR--APP--GRAY' => '#222',
											'--COLOR--APP--LIGHTGRAY' => '#121212',
											'--COLOR--APP--DARKGRAY' => '#1a1a1a',
											'--COLOR--APP--DARK' => '#46494f',
											'--COLOR--APP--DARKER' => '#202124',
											'--COLOR--TEXT' => '#f9f9f9',
											'--COLOR--TEXT--LIGHT' => '#8c8c8c',
											'--COLOR--HEADING' => '#fff',
											'--BOX-SHADOW-DIS' => '0',
											'--BOX-SHADOW-BLUR' => '0',
											'--BOX-SHADOW-THICKNESS' => '0',
											'--BOX-SHADOW-COLOR' => 'rgba(0, 0, 0, 0)',
											'--c-gc-body-bg' => '#111',
											'--c-ps-navbar-bg' => 'var(--COLOR--APP--GRAY)',
											'--c-gc-header-logo-color' => '#fff',
											'--c-gc-header-tagline-color' => '#999',
											'--c-ps-checkbox-border' => 'rgba(255,255,255, .1)', // add as option
											'--c-ps-notification-unread-bg' => '#51503b', // add as option
											'--c-ps-post-pinned-text-color' => 'var(--COLOR--PRIMARY--ULTRALIGHT)',
											'--c-ps-post-pinned-border-color' => 'var(--COLOR--PRIMARY--ULTRALIGHT)',
											'--c-ps-bubble-bg' => 'var(--COLOR--ALT)',
											'--c-ps-btn-action-color' => '#111',
											'--c-ps-btn-action-color-hover' => '#111',
                    ]),
                ];

								$presets['gecko-sunset'] = [
                    'id' => 'gecko-sunset',
                    'label' => __('Gecko:', 'peepso-theme-gecko') . ' ' . __('Sunset', 'peepso-theme-gecko'),
                    'readonly' => true,
                    'settings' => array_merge($default_settings, []),
                    'css_vars' => array_merge($default_css_vars, [
											'--COLOR--PRIMARY' => '#FF7B54',
											'--COLOR--PRIMARY--SHADE' => '#EFC3A8',
											'--COLOR--PRIMARY--LIGHT' => '#FFA872',
											'--COLOR--PRIMARY--ULTRALIGHT' => '#FFD7AB',
											'--COLOR--PRIMARY--DARK' => '#F36F49',
											'--COLOR--ALT' => '#6AC8D2',
											'--COLOR--ALT--LIGHT' => '#8CE3EC',
											'--COLOR--ALT--DARK' => '#48ACB7',
											'--COLOR--APP' => '#FFFFFF',
											'--COLOR--APP--GRAY' => '#FAF8F6',
											'--COLOR--APP--LIGHTGRAY' => '#F6F4F1',
											'--COLOR--APP--DARKGRAY' => '#F1EBE7',
											'--COLOR--APP--DARK' => '#46494F',
											'--COLOR--APP--DARKER' => '#202124',
											'--COLOR--TEXT' => '#222222',
											'--COLOR--TEXT--LIGHT' => '#807070',
											'--COLOR--TEXT--LIGHTEN' => '#A59494',
											'--COLOR--HEADING' => '#000000',
											'--DIVIDER' => 'rgba(75, 51, 30, 0.1803)',
											'--DIVIDER--LIGHT' => 'rgba(70, 45, 33, 0.1411)',
											'--DIVIDER--LIGHTEN' => 'rgba(70, 45, 33, 0.1019)',
											'--DIVIDER--DARK' => 'rgba(75, 51, 30, 0.2901)',
											'--BORDER-RADIUS' => '8px',
											'--BOX-SHADOW-DIS' => '0px',
											'--BOX-SHADOW-BLUR' => '0px',
											'--BOX-SHADOW-THICKNESS' => '1px',
											'--BOX-SHADOW-COLOR' => 'rgba(70, 45, 33, 0.1019)',
											'--COLOR--GRADIENT--ONE' => '#F97485',
											'--COLOR--GRADIENT--TWO' => '#FCA075',
											'--c-gc-body-bg' => '#F9F8F6',
                    ]),
                ];

								$presets['gecko-dating'] = [
                    'id' => 'gecko-dating',
                    'label' => __('Gecko:', 'peepso-theme-gecko') . ' ' . __('Dating', 'peepso-theme-gecko'),
                    'readonly' => true,
                    'settings' => array_merge($default_settings, []),
                    'css_vars' => array_merge($default_css_vars, [
											'--COLOR--PRIMARY' => '#FF6088',
											'--COLOR--PRIMARY--SHADE' => '#FFC6D3',
											'--COLOR--PRIMARY--LIGHT' => '#FF7599',
											'--COLOR--PRIMARY--ULTRALIGHT' => '#FFD7E0',
											'--COLOR--PRIMARY--DARK' => '#F34D77',
											'--COLOR--INFO' => '#A059B0',
											'--COLOR--INFO--LIGHT' => '#E1C9E8',
											'--COLOR--INFO--ULTRALIGHT' => '#EFE2EF',
											'--COLOR--INFO--DARK' => '#824091',
											'--COLOR--ALT' => '#531B5E',
											'--COLOR--ALT--LIGHT' => '#824091',
											'--COLOR--ALT--DARK' => '#3F1248',
											'--COLOR--APP' => '#FFFFFF',
											'--COLOR--APP--GRAY' => '#FAF9FA',
											'--COLOR--APP--LIGHTGRAY' => '#FAF9FA',
											'--COLOR--APP--DARKGRAY' => '#F2EDF2',
											'--COLOR--TEXT' => '#514D51',
											'--COLOR--TEXT--LIGHT' => '#A297A2',
											'--COLOR--TEXT--LIGHTEN' => '#BFB5BF',
											'--COLOR--HEADING' => '#333333',
											'--BORDER-RADIUS' => '10px',
											'--BOX-SHADOW-DIS' => '0px',
											'--BOX-SHADOW-BLUR' => '0px',
											'--BOX-SHADOW-THICKNESS' => '1px',
											'--BOX-SHADOW-COLOR' => 'rgba(156, 93, 118, 0.0392)',
											'--COLOR--GRADIENT--ONE' => '#FF4B4B',
											'--COLOR--GRADIENT--TWO' => '#FF7599',
											'--c-gc-body-bg' => '#F9F2F6',
											'--c-ps-navbar-bg' => '#FF6088',
											'--c-ps-navbar-links-color' => '#FFC7CE',
											'--c-ps-navbar-links-color-hover' => '#fff',
											'--c-ps-post-pinned-text-color' => '#FAA3BA',
											'--c-ps-post-pinned-border-color' => '#FAA3BA',
                    ]),
                ];

								$presets['gecko-lime'] = [
                    'id' => 'gecko-lime',
                    'label' => __('Gecko:', 'peepso-theme-gecko') . ' ' . __('Lime Green', 'peepso-theme-gecko'),
                    'readonly' => true,
                    'settings' => array_merge($default_settings, []),
                    'css_vars' => array_merge($default_css_vars, [
											'--COLOR--PRIMARY' => '#D7FE64',
											'--COLOR--PRIMARY--SHADE' => '#D5E49F',
											'--COLOR--PRIMARY--LIGHT' => '#EFFFA9',
											'--COLOR--PRIMARY--ULTRALIGHT' => '#EAF3D2', 
											'--COLOR--PRIMARY--DARK' => '#B7D654', 
											'--COLOR--ALT' => '#27B194',
											'--COLOR--ALT--LIGHT' => '#60D6BD',
											'--COLOR--ALT--DARK' => '#199C80',
											'--COLOR--APP' => '#FFFFFF',
											'--COLOR--APP--GRAY' => '#f1f7f2',
											'--COLOR--APP--LIGHTGRAY' => '#F6F8F5',
											'--COLOR--APP--DARKGRAY' => '#e4edd3',
											'--COLOR--TEXT' => '#060606',
											'--COLOR--TEXT--LIGHT' => '#9ea294',
											'--COLOR--TEXT--LIGHTEN' => '#bdc0b5',
											'--COLOR--HEADING' => '#333333',
											'--COLOR--LINK' => '#CBDD36',
											'--COLOR--LINK-HOVER' => '#DAEA37',
											'--COLOR--LINK-FOCUS' => '#DAEA37',											
											'--BORDER-RADIUS' => '8px',
											'--BOX-SHADOW-DIS' => '0px',
											'--BOX-SHADOW-BLUR' => '0px',
											'--BOX-SHADOW-THICKNESS' => '1px',
											'--BOX-SHADOW-COLOR' => 'rgba(150, 179, 151, 0.2)',
											'--COLOR--GRADIENT--ONE' => 'rgba(0, 212, 176, 0.4)',
											'--COLOR--GRADIENT--TWO' => '#E4FF99',
											'--COLOR--GRADIENT--TEXT' => '#060606',
											'--COLOR--GRADIENT--LINKS' => 'rgba(6, 6, 6, 0.8)',
											'--c-gc-body-bg' => '#F6F8F6',
											'--c-gc-header-bg' => '#060606',
											'--c-gc-header-logo-color' => '#fff',
											'--c-gc-header-tagline-color' => '#fff',
											'--c-gc-header-text-color' => '#fff',
											'--c-gc-header-link-color' => '#ACACAC',
											'--c-gc-header-link-color-hover' => '#fff',
											'--c-gc-header-link-active-indicator' => '#D7FE64',
											'--c-ps-btn-action-color' => '#060606',
											'--c-ps-btn-action-bg-hover' => '#E1FF8A',
											'--c-ps-btn-action-color-hover' => '#060606',
											'--c-ps-post-pinned-border-color' => '#D7FE64',
											'--c-ps-post-pinned-text-color' => '#B7D654',					
											'--c-ps-postbox-icons-active-color' => '#B7D654',		
											'--c-ps-navbar-bg' => '#D7FE64',
											'--c-ps-navbar-links-color' => '#060606',
											'--c-ps-navbar-links-color-hover' => '#6B6B6B',
											'--c-ps-chat-message-bg-me' => '#D7F066',
											'--c-ps-chat-message-text-color-me' => '#060606',
											'--c-ps-hashtag-postbox-bg' => '#D5E49F',
											'--c-gc-sidenav-menu-icon-hover' => '#B7D654',
                    ]),
                ];
            }

			$custom_presets = get_option('gecko_custom_presets', []);

			if(is_array($custom_presets) && count($custom_presets)) {
				foreach ( $custom_presets as $id => $preset ) {

					if(!is_array($preset)) {
						continue;
					}

					$id             = $preset['id'];
					$presets[ $id ] = [
						'id'       => $id,
						'label'    => stripslashes($preset['label']),
						'settings' => $preset['settings'],
						'css_vars' => $preset['css_vars'],
					];
				}
			}
			return $presets;
		}

		/**
		 * Get a single preset.
		 *
		 * @since 3.0.0.0
		 *
		 * @param string $id
		 * @return array|boolean
		 */
		public function get($id) {
			$presets = $this->list();
			if (isset($presets[$id])) {
				return $presets[$id];
			}

			return false;
		}

		/**
		 * Add a new custom preset.
		 *
		 * @since 3.0.0.0
		 *
		 * @param string $label
		 * @param array $configs
		 * @return array
		 */
		public function add($label, $configs = []) {
			$label = sanitize_text_field($label);

			$id = preg_replace('#\s+#', '_', $label);
			$id = sanitize_title($id);

			// Check for existing preset IDs to prevent overwriting.
			$presets = $this->list();
			while (isset($presets[$id])) {
				$counter = 1;
				if (preg_match('#_(\d+)$#', $id, $matches)) {
					$counter += (int) $matches[1];
				}
				$id = preg_replace('#_(\d+)$#', '', $id);
				$id = $id . '_' . $counter;
			}

			$custom_presets = get_option('gecko_custom_presets', []);
			$custom_presets[$id] = [
				'id' => $id,
				'label' => stripslashes($label),
				'settings' => isset($configs['settings']) ? $configs['settings'] : (object) [],
				'css_vars' => isset($configs['css_vars']) ? $configs['css_vars'] : (object) [],
			];
			update_option('gecko_custom_presets', $custom_presets);

			return $custom_presets[$id];
		}

		/**
		 * Update a custom preset.
		 *
		 * @since 3.0.0.0
		 *
		 * @param string $id
		 * @param array $configs
		 * @return array|boolean
		 */
		public function update($id, $configs = []) {
			$custom_presets = get_option('gecko_custom_presets', []);
			if (isset($custom_presets[$id])) {
				$preset = $custom_presets[$id];
				$custom_presets[$id] = [
					'id' => $id,
					'label' => stripslashes($preset['label']),
					'settings' => isset($configs['settings']) ? $configs['settings'] : (object) [],
					'css_vars' => isset($configs['css_vars']) ? $configs['css_vars'] : (object) [],
				];
				update_option('gecko_custom_presets', $custom_presets);
				return $custom_presets[$id];
			}

			return false;
		}

		/**
		 * Rename a custom preset.
		 *
		 * @since 3.0.0.0
		 *
		 * @param string $id
		 * @param string $label
		 * @return array|boolean
		 */
		public function rename($id, $label) {
			$label = sanitize_text_field($label);
			if (strlen($label) === 0) {
				return false;
			}

			$custom_presets = get_option('gecko_custom_presets', []);
			if (isset($custom_presets[$id])) {
				$custom_presets[$id] = array_merge($custom_presets[$id], [ 'label' => stripslashes($label) ]);
				update_option('gecko_custom_presets', $custom_presets);
				return $custom_presets[$id];
			}

			return false;
		}

		/**
		 * Delete a custom preset.
		 *
		 * @since 3.0.0.0
		 *
		 * @param string $id
		 * @return array|boolean
		 */
		public function delete($id) {
			$custom_presets = get_option('gecko_custom_presets', []);
			if (isset($custom_presets[$id])) {
				$preset = $custom_presets[$id];
				unset($custom_presets[$id]);
				update_option('gecko_custom_presets', $custom_presets);
				return $preset;
			}

			return false;
		}

		/**
		 * Delete all custom presets.
		 *
		 * @since 3.0.0.0
		 */
		public function clear() {
			update_option('gecko_custom_presets', []);
		}
	}
}
