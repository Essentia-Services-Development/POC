<?php
/*
 * Plugin Name: TNC FlipBook - PDF viewer for WordPress
 * Plugin URI: https://themencode.com/pdf-viewer-for-wordpress/
 * Description: The best PDF Reader & FlipBook Plugin for WordPress since 2014, Powers up your WordPress website with a smart and modern PDF Reader & FlipBook.
 * Version: 11.6.0
 * Author: ThemeNcode
 * Author URI: https://themencode.com
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define constants.
define( 'PVFW_PLUGIN_NAME', 'TNC FlipBook - PDF viewer for WordPress' );
define( 'PVFW_PLUGIN_DIR', 'pdf-viewer-for-wordpress' );
define( 'PVFW_PLUGIN_VERSION', '11.6.0' );
define( 'TNC_PVFW_WEB_DIR', 'pdf-viewer-for-wordpress/web' );
define( 'TNC_PVFW_BUILD_DIR', 'pdf-viewer-for-wordpress/build' );
define( 'TNC_PVFW_RESOURCES_DIR', 'pdf-viewer-for-wordpress/tnc-resources' );

add_action( 'init', 'tnc_pvfw_autoupdate_checker' );

function tnc_pvfw_autoupdate_checker() {
	require_once plugin_dir_path( __FILE__ ) . '/admin/autoupdate.php';
	$tnc_pvfw_plugin_current_version = PVFW_PLUGIN_VERSION;
	$tnc_pvfw_plugin_remote_path     = 'https://updates.themencode.com/pvfw/update.php';
	$tnc_pvfw_plugin_slug            = plugin_basename( __FILE__ );
	$site_url_parse                  = parse_url( site_url() );
	$tnc_pvfw_domain                 = $site_url_parse['host'];
	$tnc_pvfw_license_key            = get_option( 'tnc_pvfw_sitekey' );

	new TncAutoUpdatePVFW( $tnc_pvfw_plugin_current_version, $tnc_pvfw_plugin_remote_path, $tnc_pvfw_plugin_slug, $tnc_pvfw_domain, $tnc_pvfw_license_key );
}

// Include files.
require_once plugin_dir_path( __FILE__ ) . 'admin/tnc-pdf-viewer-options.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/tnc-flipbook-metabox.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/helper-functions.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/cpt.php';
if( ! class_exists( 'PVFWOF' ) ) {
	require_once plugin_dir_path( __FILE__ ) . '/includes/csf/pvfwof-framework.php';
}
require_once plugin_dir_path( __FILE__ ) . '/includes/pvfw-csf-options.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/pvfw-csf-custom-field.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/pvfw-csf-sc.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/automatic-features.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/tnc_shortcodes.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/pvfw-new-shortcodes.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/pvfw-x-shortcodes.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/scripts.php';



class TncRegisterPT {
		/**
		 * A reference to an instance of this class.
		 */
	private static $instance;
		/**
		 * The array of templates that this plugin tracks.
		 */
		protected $templates;
		/**
		 * Returns an instance of this class.
		 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new TncRegisterPT();
		}
		return self::$instance;
	}
		/**
		 * Initializes the plugin by setting filters and administration functions.
		 */
	private function __construct() {
		$this->templates = array();
		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter(
			'page_attributes_dropdown_pages_args',
			array( $this, 'register_tnc_pdf_templates' )
		);
			// Add a filter to the save post to inject out template into the page cache.
		add_filter(
			'wp_insert_post_data',
			array( $this, 'register_tnc_pdf_templates' )
		);
		// Add a filter to the template include to determine if the page has our.
		// template assigned and return it's path.
		add_filter(
			'template_include',
			array( $this, 'view_tnc_pdf_template' )
		);
		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			// 4.6 and older
			add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_tnc_pdf_templates' ) );
		} else {
			// Add a filter to the wp 4.7 version attributes metabox.
			add_filter( 'theme_page_templates', array( $this, 'add_new_tnc_pdf_template' ) );
		}
		// Add your templates to this array.
		$this->templates = array(
			'tnc-pdf-viewer.php'           => 'PDF Viewer Template',
			'tnc-pdf-viewer-shortcode.php' => 'PDF Viewer Shortcode Template',
		);
	}
		/**
		 * Adds our template to the pages cache in order to trick WordPress
		 * into thinking the template file exists where it doens't really exist.
		 */
	public function register_tnc_pdf_templates( $atts ) {
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}
		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key, 'themes' );
		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );
		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_tnc_pdf_template( $template ) {
		global $post;
		if( ! empty( $post ) ){
			if ( ! isset(
				$this->templates[ get_post_meta(
					$post->ID,
					'_wp_page_template',
					true
				) ]
			) ) {
				return $template;
			}

			$file = plugin_dir_path( __FILE__ ) . get_post_meta(
				$post->ID,
				'_wp_page_template',
				true
			);

			// Just to be safe, we check if the file exist first.
			if ( file_exists( $file ) ) {
				return $file;
			} else {
				echo $file;
			}
		}

		return $template;
	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 * @param [type] $posts_templates post templates.
	 * @return array
	 */
	public function add_new_tnc_pdf_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}
}
add_action( 'plugins_loaded', array( 'TncRegisterPT', 'get_instance' ) );
register_activation_hook( __FILE__, 'pvfw_activation' );
register_deactivation_hook( __FILE__, 'pvfw_deactivation' );

/**
 * Activation function
 */
function pvfw_activation() {
	$pdf_viewer_page = pvfw_get_page_by_name( 'themencode-pdf-viewer' );

	if ( ! empty( $pdf_viewer_page ) ) {
		$get_pvfw_global_settings                             = get_option( 'pvfw_csf_options' );
		if( $get_pvfw_global_settings['advanced-pdf-viewer-page'] == '' ){
			$themencode_pdf_viewer_page_post_id                   = $pdf_viewer_page->ID;
			$get_pvfw_global_settings['advanced-pdf-viewer-page'] = $themencode_pdf_viewer_page_post_id;
			update_option( 'pvfw_csf_options', $get_pvfw_global_settings, true );
		}
	} else {
		$themencode_pdf_viewer_page                           = array(
			'post_name'     => 'themencode-pdf-viewer',
			'post_title'    => 'ThemeNcode PDF Viewer [Do not Delete]',
			'post_content'  => 'This page is used for Viewing PDF.',
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'page_template' => 'tnc-pdf-viewer.php',
		);
		$themencode_pdf_viewer_page_post_id                   = wp_insert_post( $themencode_pdf_viewer_page );
		$get_pvfw_global_settings                             = get_option( 'pvfw_csf_options' );
		$get_pvfw_global_settings['advanced-pdf-viewer-page'] = $themencode_pdf_viewer_page_post_id;
		update_option( 'pvfw_csf_options', $get_pvfw_global_settings, true );
		update_post_meta( $themencode_pdf_viewer_page_post_id, '_wp_page_template', 'tnc-pdf-viewer.php' );
	}
	$pdf_viewer_sc_page = pvfw_get_page_by_name( 'themencode-pdf-viewer-sc' );
	if ( ! empty( $pdf_viewer_sc_page ) ) {
		$get_pvfws_global_settings                                = get_option( 'pvfw_csf_options' );
		if( $get_pvfws_global_settings['advanced-pdf-viewer-sc-page'] == '' ){
			$themencode_pdf_viewer_sc_page_post_id                    = $pdf_viewer_sc_page->ID;
			$get_pvfws_global_settings['advanced-pdf-viewer-sc-page'] = $themencode_pdf_viewer_sc_page_post_id;
			update_option( 'pvfw_csf_options', $get_pvfws_global_settings, true );
		}
	} else {
		$themencode_pdf_viewer_sc_page                            = array(
			'post_name'    => 'themencode-pdf-viewer-sc',
			'post_title'   => 'ThemeNcode PDF Viewer SC [Do not Delete]',
			'post_content' => 'This page is used for Viewing PDF.',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);
		$themencode_pdf_viewer_sc_page_post_id                    = wp_insert_post( $themencode_pdf_viewer_sc_page );
		$get_pvfws_global_settings                                = get_option( 'pvfw_csf_options' );
		$get_pvfws_global_settings['advanced-pdf-viewer-sc-page'] = $themencode_pdf_viewer_sc_page_post_id;
		update_option( 'pvfw_csf_options', $get_pvfws_global_settings, true );
		update_post_meta( $themencode_pdf_viewer_sc_page_post_id, '_wp_page_template', 'tnc-pdf-viewer-shortcode.php' );
	}

	$get_initial_csf_settings = get_option( 'pvfw_csf_options' );

	if( isset( $get_initial_csf_settings['select-automatic-display'])  && !empty( $get_initial_csf_settings['select-automatic-display']) ){    	
		$get_initial_csf_settings['select-automatic-display'] = $get_initial_csf_settings['select-automatic-display'];
	} else {
		$get_initial_csf_settings['select-automatic-display'] = '';		
	}

	if( isset($get_initial_csf_settings['select-automatic-link-target'] )  && !empty( $get_initial_csf_settings['select-automatic-link-target'] ) ){
		$get_initial_csf_settings['select-automatic-link-target'] = $get_initial_csf_settings['select-automatic-link-target'];
	} else {
		$get_initial_csf_settings['select-automatic-link-target'] = '';
	}

	if( isset($get_initial_csf_settings['select-automatic-iframe-width'] )  && !empty( $get_initial_csf_settings['select-automatic-iframe-width'] ) ){
		$get_initial_csf_settings['select-automatic-iframe-width'] = $get_initial_csf_settings['select-automatic-iframe-width'];		
	} else {
		$get_initial_csf_settings['select-automatic-iframe-width'] = '100%';
	}

	if( isset($get_initial_csf_settings['select-automatic-iframe-height'] )  && !empty( $get_initial_csf_settings['select-automatic-iframe-height'] ) ){
		$get_initial_csf_settings['select-automatic-iframe-height'] = $get_initial_csf_settings['select-automatic-iframe-height'];
	} else {
		$get_initial_csf_settings['select-automatic-iframe-height'] = '800';
	}

	if( isset( $get_initial_csf_settings['general-logo'] )  && !empty( $get_initial_csf_settings['general-logo'] ) ) {
		$get_initial_csf_settings['general-logo'] = array(
			$get_initial_csf_settings['general-logo']['url'] => $get_initial_csf_settings['general-logo']['url'],
			$get_initial_csf_settings['general-logo']['id'] => $get_initial_csf_settings['general-logo']['id'],
			$get_initial_csf_settings['general-logo']['width'] => $get_initial_csf_settings['general-logo']['width'],
			$get_initial_csf_settings['general-logo']['height'] => $get_initial_csf_settings['general-logo']['height'],
			$get_initial_csf_settings['general-logo']['thumbnail'] => $get_initial_csf_settings['general-logo']['thumbnail'],
			$get_initial_csf_settings['general-logo']['alt'] => $get_initial_csf_settings['general-logo']['alt'],
			$get_initial_csf_settings['general-logo']['title'] => $get_initial_csf_settings['general-logo']['title'],
			$get_initial_csf_settings['general-logo']['description'] => $get_initial_csf_settings['general-logo']['description']
		);
	} else {
		$get_initial_csf_settings['general-logo'] = array(
			'url' => "",
			'id' => "",
			"width" => "",
			"height" => "",
			"thumbnail" => "",
			"alt" => "",
			"title" => "",
			"description" => ""
		);
	}

	if( isset( $get_initial_csf_settings['general-favicon'] )  && !empty( $get_initial_csf_settings['general-favicon'] ) ) {
		$get_initial_csf_settings['general-favicon'] = array(
			$get_initial_csf_settings['general-favicon']['url'] => $get_initial_csf_settings['general-favicon']['url'],
			$get_initial_csf_settings['general-favicon']['id'] => $get_initial_csf_settings['general-favicon']['id'],
			$get_initial_csf_settings['general-favicon']['width'] => $get_initial_csf_settings['general-favicon']['width'],
			$get_initial_csf_settings['general-favicon']['height'] => $get_initial_csf_settings['general-favicon']['height'],
			$get_initial_csf_settings['general-favicon']['thumbnail'] => $get_initial_csf_settings['general-favicon']['thumbnail'],
			$get_initial_csf_settings['general-favicon']['alt'] => $get_initial_csf_settings['general-favicon']['alt'],
			$get_initial_csf_settings['general-favicon']['title'] => $get_initial_csf_settings['general-favicon']['title'],
			$get_initial_csf_settings['general-favicon']['description'] => $get_initial_csf_settings['general-favicon']['description']
		);
	} else {
		$get_initial_csf_settings['general-favicon'] = array(
			'url' => "",
			'id' => "",
			"width" => "",
			"height" => "",
			"thumbnail" => "",
			"alt" => "",
			"title" => "",
			"description" => ""
		);
	}

	if( isset($get_initial_csf_settings['general-fullscreen-text'] )  && !empty( $get_initial_csf_settings['general-fullscreen-text'] ) ){
		$get_initial_csf_settings['general-fullscreen-text'] = $get_initial_csf_settings['general-fullscreen-text'];
	} else {
		$get_initial_csf_settings['general-fullscreen-text'] = 'Fullscreen Mode';
	}

	if( isset($get_initial_csf_settings['general-return-text'] )  && !empty( $get_initial_csf_settings['general-return-text'] ) ){
		$get_initial_csf_settings['general-return-text'] = $get_initial_csf_settings['general-return-text'];
	} else {
		$get_initial_csf_settings['general-return-text'] = 'Return to Site';
	}

	if( isset($get_initial_csf_settings['general-analytics-id'] )  && !empty( $get_initial_csf_settings['general-analytics-id'] ) ){
		$get_initial_csf_settings['general-analytics-id'] = $get_initial_csf_settings['general-analytics-id'];
	} else {
		$get_initial_csf_settings['general-analytics-id'] = '';
	}

	if( isset($get_initial_csf_settings['general-mobile-iframe-height'] )  && !empty( $get_initial_csf_settings['general-mobile-iframe-height'] ) ){
		$get_initial_csf_settings['general-mobile-iframe-height'] = $get_initial_csf_settings['general-mobile-iframe-height'];
	} else {
		$get_initial_csf_settings['general-mobile-iframe-height'] = '400px';
	}

	if( isset($get_initial_csf_settings['general-iframe-responsive-fix'] )  && !empty( $get_initial_csf_settings['general-iframe-responsive-fix'] ) ){
		$get_initial_csf_settings['general-iframe-responsive-fix'] = $get_initial_csf_settings['general-iframe-responsive-fix'];
	} else {
		$get_initial_csf_settings['general-iframe-responsive-fix'] = '';
	}

	if( isset($get_initial_csf_settings['appearance-disable-flip-sound'] )  && !empty( $get_initial_csf_settings['appearance-disable-flip-sound'] ) ){
		$get_initial_csf_settings['appearance-disable-flip-sound'] = $get_initial_csf_settings['appearance-disable-flip-sound'];
	} else {
		$get_initial_csf_settings['appearance-disable-flip-sound'] = '0';
	}

	if( isset($get_initial_csf_settings['appearance-select-type'] )  && !empty( $get_initial_csf_settings['appearance-select-type'] ) ){
		$get_initial_csf_settings['appearance-select-type'] = $get_initial_csf_settings['appearance-select-type'];
	} else {
		$get_initial_csf_settings['appearance-select-type'] = 'select-theme';
	}

	if( isset($get_initial_csf_settings['appearance-select-theme'] )  && !empty( $get_initial_csf_settings['appearance-select-theme'] ) ){
		$get_initial_csf_settings['appearance-select-theme'] = $get_initial_csf_settings['appearance-select-theme'];
	} else {
		$get_initial_csf_settings['appearance-select-theme'] = 'midnight-calm';
	}

	if( isset( $get_initial_csf_settings['appearance-select-colors'] )  && !empty( $get_initial_csf_settings['appearance-select-colors'] ) ){
		$get_initial_csf_settings['appearance-select-colors'] = array (
			$get_initial_csf_settings['appearance-select-colors']['primary-color'] => $get_initial_csf_settings['appearance-select-colors']['primary-color'],
			$get_initial_csf_settings['appearance-select-colors']['secondary-color'] => $get_initial_csf_settings['appearance-select-colors']['secondary-color'],
			$get_initial_csf_settings['appearance-select-colors']['text-color'] => $get_initial_csf_settings['appearance-select-colors']['text-color']
		);
	} else {
		$get_initial_csf_settings['appearance-select-colors'] = array(
			'primary-color' => '',
			'secondary-color' => '',
			'text-color' => ''
		);
	}

	if( isset($get_initial_csf_settings['appearance-select-icon'] )  && !empty( $get_initial_csf_settings['appearance-select-icon'] ) ){
		$get_initial_csf_settings['appearance-select-icon'] = $get_initial_csf_settings['appearance-select-icon'];
	} else {
		$get_initial_csf_settings['appearance-select-icon'] = '';
	}

	if( isset($get_initial_csf_settings['toolbar-default-scroll'] )  && !empty( $get_initial_csf_settings['toolbar-default-scroll'] ) ){
		$get_initial_csf_settings['toolbar-default-scroll'] = $get_initial_csf_settings['toolbar-default-scroll'];
	} else {
		$get_initial_csf_settings['toolbar-default-scroll'] = '';
	}

	if( isset($get_initial_csf_settings['toolbar-default-spread'] )  && !empty( $get_initial_csf_settings['toolbar-default-spread'] ) ){
		$get_initial_csf_settings['toolbar-default-spread'] = $get_initial_csf_settings['toolbar-default-spread'];
	} else {
		$get_initial_csf_settings['toolbar-default-spread'] = '';
	}

	if( isset($get_initial_csf_settings['toolbar-default-zoom'] )  && !empty( $get_initial_csf_settings['toolbar-default-zoom'] ) ){
		$get_initial_csf_settings['toolbar-default-zoom'] = $get_initial_csf_settings['toolbar-default-zoom'];
	} else {
		$get_initial_csf_settings['toolbar-default-zoom'] = 'auto';
	}

	if( isset($get_initial_csf_settings['toolbar-viewer-language'] )  && !empty( $get_initial_csf_settings['toolbar-viewer-language'] ) ){
		$get_initial_csf_settings['toolbar-viewer-language'] = $get_initial_csf_settings['toolbar-viewer-language'];
	} else {
		$get_initial_csf_settings['toolbar-viewer-language'] = '';
	}

	if( isset($get_initial_csf_settings['toolbar-share'] )  && !empty( $get_initial_csf_settings['toolbar-share'] ) ){
		$get_initial_csf_settings['toolbar-share'] = $get_initial_csf_settings['toolbar-share'];
	} else {
		$get_initial_csf_settings['toolbar-share'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-print'] )  && !empty( $get_initial_csf_settings['toolbar-print'] ) ){
		$get_initial_csf_settings['toolbar-print'] = $get_initial_csf_settings['toolbar-print'];
	} else {
		$get_initial_csf_settings['toolbar-print'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-download'] )  && !empty( $get_initial_csf_settings['toolbar-download'] ) ){
		$get_initial_csf_settings['toolbar-download'] = $get_initial_csf_settings['toolbar-download'];
	} else {
		$get_initial_csf_settings['toolbar-download'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-open'] )  && !empty( $get_initial_csf_settings['toolbar-open'] ) ){
		$get_initial_csf_settings['toolbar-open'] = $get_initial_csf_settings['toolbar-open'];
	} else {
		$get_initial_csf_settings['toolbar-open'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-zoom'] )  && !empty( $get_initial_csf_settings['toolbar-zoom'] ) ){
		$get_initial_csf_settings['toolbar-zoom'] = $get_initial_csf_settings['toolbar-zoom'];
	} else {
		$get_initial_csf_settings['toolbar-zoom'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-fullscreen'] )  && !empty( $get_initial_csf_settings['toolbar-fullscreen'] ) ){
		$get_initial_csf_settings['toolbar-fullscreen'] = $get_initial_csf_settings['toolbar-fullscreen'];
	} else {
		$get_initial_csf_settings['toolbar-fullscreen'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-logo'] )  && !empty( $get_initial_csf_settings['toolbar-logo'] ) ){
		$get_initial_csf_settings['toolbar-logo'] = $get_initial_csf_settings['toolbar-logo'];
	} else {
		$get_initial_csf_settings['toolbar-logo'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-find'] )  && !empty( $get_initial_csf_settings['toolbar-find'] ) ){
		$get_initial_csf_settings['toolbar-find'] = $get_initial_csf_settings['toolbar-find'];
	} else {
		$get_initial_csf_settings['toolbar-find'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-pagenav'] )  && !empty( $get_initial_csf_settings['toolbar-pagenav'] ) ){
		$get_initial_csf_settings['toolbar-pagenav'] = $get_initial_csf_settings['toolbar-pagenav'];
	} else {
		$get_initial_csf_settings['toolbar-pagenav'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-current-view'] )  && !empty( $get_initial_csf_settings['toolbar-current-view'] ) ){
		$get_initial_csf_settings['toolbar-current-view'] = $get_initial_csf_settings['toolbar-current-view'];
	} else {
		$get_initial_csf_settings['toolbar-current-view'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-rotate'] )  && !empty( $get_initial_csf_settings['toolbar-rotate'] ) ){
		$get_initial_csf_settings['toolbar-rotate'] = $get_initial_csf_settings['toolbar-rotate'];
	} else {
		$get_initial_csf_settings['toolbar-rotate'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-handtool'] )  && !empty( $get_initial_csf_settings['toolbar-handtool'] ) ){
		$get_initial_csf_settings['toolbar-handtool'] = $get_initial_csf_settings['toolbar-handtool'];
	} else {
		$get_initial_csf_settings['toolbar-handtool'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-doc-prop'] )  && !empty( $get_initial_csf_settings['toolbar-doc-prop'] ) ){
		$get_initial_csf_settings['toolbar-doc-prop'] = $get_initial_csf_settings['toolbar-doc-prop'];
	} else {
		$get_initial_csf_settings['toolbar-doc-prop'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-left-toggle'] )  && !empty( $get_initial_csf_settings['toolbar-left-toggle'] ) ){
		$get_initial_csf_settings['toolbar-left-toggle'] = $get_initial_csf_settings['toolbar-left-toggle'];
	} else {
		$get_initial_csf_settings['toolbar-left-toggle'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-right-toggle'] )  && !empty( $get_initial_csf_settings['toolbar-right-toggle'] ) ){
		$get_initial_csf_settings['toolbar-right-toggle'] = $get_initial_csf_settings['toolbar-right-toggle'];
	} else {
		$get_initial_csf_settings['toolbar-right-toggle'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-scroll'] )  && !empty( $get_initial_csf_settings['toolbar-scroll'] ) ){
		$get_initial_csf_settings['toolbar-scroll'] = $get_initial_csf_settings['toolbar-scroll'];
	} else {
		$get_initial_csf_settings['toolbar-scroll'] = '1';
	}

	if( isset($get_initial_csf_settings['toolbar-spread'] )  && !empty( $get_initial_csf_settings['toolbar-spread'] ) ){
		$get_initial_csf_settings['toolbar-spread'] = $get_initial_csf_settings['toolbar-spread'];
	} else {
		$get_initial_csf_settings['toolbar-spread'] = '1';
	}

	if( isset($get_initial_csf_settings['advanced-pdf-viewer-page'] )  && !empty( $get_initial_csf_settings['advanced-pdf-viewer-page'] ) ){
		$get_initial_csf_settings['advanced-pdf-viewer-page'] = $get_initial_csf_settings['advanced-pdf-viewer-page'];
	} else {
		$get_initial_csf_settings['advanced-pdf-viewer-page'] = '';
	}

	if( isset($get_initial_csf_settings['advanced-pdf-viewer-sc-page'] )  && !empty( $get_initial_csf_settings['advanced-pdf-viewer-sc-page'] ) ){
		$get_initial_csf_settings['advanced-pdf-viewer-sc-page'] = $get_initial_csf_settings['advanced-pdf-viewer-sc-page'];
	} else {
		$get_initial_csf_settings['advanced-pdf-viewer-sc-page'] = '';
	}

	if( isset($get_initial_csf_settings['advanced-context-menu'] )  && !empty( $get_initial_csf_settings['advanced-context-menu'] ) ){
		$get_initial_csf_settings['advanced-context-menu'] = $get_initial_csf_settings['advanced-context-menu'];
	} else {
		$get_initial_csf_settings['advanced-context-menu'] = '1';
	}

	if( isset($get_initial_csf_settings['advanced-text-copying'] )  && !empty( $get_initial_csf_settings['advanced-text-copying'] ) ){
		$get_initial_csf_settings['advanced-text-copying'] = $get_initial_csf_settings['advanced-text-copying'];
	} else {
		$get_initial_csf_settings['advanced-text-copying'] = '1';
	}

	if( isset($get_initial_csf_settings['advanced-oxygen-integration'] )  && !empty( $get_initial_csf_settings['advanced-oxygen-integration'] ) ){
		$get_initial_csf_settings['advanced-oxygen-integration'] = $get_initial_csf_settings['advanced-oxygen-integration'];
	} else {
		$get_initial_csf_settings['advanced-oxygen-integration'] = '0';
	}

	if( isset($get_initial_csf_settings['custom-css'] )  && !empty( $get_initial_csf_settings['custom-css'] ) ){
		$get_initial_csf_settings['custom-css'] = $get_initial_csf_settings['custom-css'];
	} else {
		$get_initial_csf_settings['custom-css'] = '';
	}

	if( isset($get_initial_csf_settings['custom-js'] )  && !empty( $get_initial_csf_settings['custom-js'] ) ){
		$get_initial_csf_settings['custom-js'] = $get_initial_csf_settings['custom-js'];
	} else {
		$get_initial_csf_settings['custom-js'] = '';
	}
	update_option( 'pvfw_csf_options', $get_initial_csf_settings, true );

	// Delete TNC PDF Viewer if activated.
	if ( is_plugin_active( 'pdf-viewer-by-themencode/pdf-viewer-by-themencode.php' ) ) {
		deactivate_plugins( 'pdf-viewer-by-themencode/pdf-viewer-by-themencode.php', true );
	}
}




/**
 * Deactivation Function
 *
 * @return void
 */
function pvfw_deactivation() {
	// Do Nothing Right Now.
}



/**
 *  Add some extra link in settings page in the wordpress plugin list
 */

function tnc_pdf_add_links_to_plugin_page ( $actions ) {
	$tnc_pdf_custom_links_array = array(
	   '<a href="https://youtu.be/w1hDnrfxEwQ" target="_blank">Videos</a>'
	);
	$actions = array_merge( $actions, $tnc_pdf_custom_links_array );
	return $actions;
 }
 add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'tnc_pdf_add_links_to_plugin_page' );

 function tnc_pdf_visit_plugin_site_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
	if ( strpos( $plugin_file_name, basename(__FILE__) ) ) {
		$links_array[] = '<a class="tnc_pdf_visit_plugin_site" target="_blank" href="https://themencode.support-hub.io/knowledgebase/977/">Documentation</a>';
	} 
	return $links_array;
}
add_filter( 'plugin_row_meta', 'tnc_pdf_visit_plugin_site_links', 10, 4 );


/**
 *   plugin action and settings links and flipbooks
 */



 /* 
*   Plugin Add Settings Link 
*/
function tnc_pdf_flipbook_settings_link($links) { 
	$settings_links = array(
		'<a href="edit.php?post_type=pdfviewer&page=pdf-viewer-options">Settings</a>',
		'<a href="edit.php?post_type=pdfviewer">FlipBooks</a>',
	);

	$links = array_merge($settings_links, $links);
	return $links;
}

$plugin = plugin_basename(__FILE__); 

add_filter("plugin_action_links_$plugin", 'tnc_pdf_flipbook_settings_link');

