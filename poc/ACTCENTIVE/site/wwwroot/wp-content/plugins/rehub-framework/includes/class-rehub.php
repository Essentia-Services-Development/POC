<?php
/**
 * REHub setup
 *
 * @package REHub
 * @since   1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main REHub Class.
 */
final class REHub_Framework {
	
	public $version = RH_PLUGIN_VER;

	/* REHub Constructor.*/
	public function __construct() {
		$this->includes();
		$this->init_hooks();

		do_action( 'rehub_framework_loaded' );
	}

	/* Include required core files */
	public function includes() {

		include_once RH_FRAMEWORK_ABSPATH .'/includes/helper-functions.php';
		// Run framework
		include_once RH_FRAMEWORK_ABSPATH .'/vendor/vafpress/bootstrap.php';
		include_once RH_FRAMEWORK_ABSPATH .'/includes/option_helpers.php';
		//Options load
		include_once RH_FRAMEWORK_ABSPATH .'/includes/option_functions.php';
		include_once RH_FRAMEWORK_ABSPATH .'/includes/customizer.php';
		//add custom taxonomy and CPT
		include_once RH_FRAMEWORK_ABSPATH .'/includes/taxonomy_cpt.php';

		include_once RH_FRAMEWORK_ABSPATH .'/includes/woo_group_attributes_class.php';

		//add meta panels
		include_once RH_FRAMEWORK_ABSPATH .'/includes/vp_metabox.php';
		if( is_admin() ) {
			include_once RH_FRAMEWORK_ABSPATH .'/includes/theme_metabox.php';
			require_once RH_FRAMEWORK_ABSPATH .'/install/index.php';
		}
		//add widgets
		include_once RH_FRAMEWORK_ABSPATH .'/includes/widgets.php';
	}

	/* Hook into actions and filters.*/
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ));
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ));
		add_action( 'wp_enqueue_scripts', array( $this, 'front_styles_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles_scripts' ) );
	}

	/* Init REHub when WordPress Initialises.*/
	public function init() {

		// Init WooCommerce Group Attributes
		if( class_exists( 'WooCommerce' ) ){
			$group_attributes_post_type = new REHub_WC_Group_Attributes( $this->version );
			$group_attributes_post_type->init();
		}
		
		// Add Theme shortcodes
		$this->init_shortcodes();
	}

	public function plugins_loaded() {
		load_plugin_textdomain( 'rehub-framework', false, RH_FRAMEWORK_ABSPATH . '/lang/' );
	}				
	
	/*  */
	private function init_shortcodes() {
		$shortcodes = include RH_FRAMEWORK_ABSPATH . '/includes/shortcodes.php';
		
		if( empty( $shortcodes ) )
			return;
	
		foreach( $shortcodes as $shortcode => $function ){
			if( function_exists( $function ) ){
				add_shortcode( $shortcode, $function );
			}
		}
	}

	/* Enqueue styles & scripts */
	public function admin_styles_scripts( $hook ) {
		wp_enqueue_style( 'rh_admin_css', RH_FRAMEWORK_URL .'/assets/css/admin.css', false, $this->version );
		VP_Site_GoogleWebFont::instance()->register_and_enqueue();
	}
	public function front_styles_scripts() {
		VP_Site_GoogleWebFont::instance()->register_and_enqueue();
	}	
	
	/* Get REHub theme option value by key */
	public static function get_option( $key ) {
		if ( function_exists( 'vp_option' ) ) {
			$value = vp_option( "rehub_option." . $key );
		}
		else {
			$options = get_option( 'rehub_option' );
			$value = (!empty($options[$key])) ? $options[$key] : '';
		}
		return $value;
	}

}