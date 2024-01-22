<?php
/*
/**
 * Plugin Name:       MarketKing Pro
 * Plugin URI:        woocommerce-multivendor.com
 * Description:       MarketKing is the complete solution for turning WooCommerce into a powerful multivendor marketplace.
 * Version:           1.8.20
 * Author:            WebWizards
 * Author URI:        webwizards.dev
 * Text Domain:       marketking
 * Domain Path:       /languages
 * WC requires at least: 5.0.0
 * WC tested up to: 8.3.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//version necessary for Stripe and others
define ( 'MARKETKINGPRO_VERSION', 'v1.8.20' );
define( 'MARKETKINGPRO_DIR', plugin_dir_path( __FILE__ ) );
define( 'MARKETKINGPRO_URL', plugin_dir_url( __FILE__ ) );


// Autoupdates
require 'includes/assets/lib/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Autoupdates
$license = get_option('marketking_license_key_setting', '');
$email = get_option('marketking_license_email_setting', '');
$info = parse_url(get_site_url());
$host = $info['host'];
$host_names = explode(".", $host);

if (isset($host_names[count($host_names)-2])){ // e.g. if not on localhost, xampp etc

	$bottom_host_name = $host_names[count($host_names)-2] . "." . $host_names[count($host_names)-1];

	if (strlen($host_names[count($host_names)-2]) <= 3){    // likely .com.au, .co.uk, .org.uk etc
	    $bottom_host_name_new = $host_names[count($host_names)-3] . "." . $host_names[count($host_names)-2] . "." . $host_names[count($host_names)-1];
	    $bottom_host_name = $bottom_host_name_new;
	}


	$activation = get_option('pluginactivation_'.$email.'_'.$license.'_'.$bottom_host_name);

	if ($activation == 'active'){
		$myUpdateChecker = PucFactory::buildUpdateChecker(
			'https://kingsplugins.com/wp-json/licensing/v1/request?email='.$email.'&license='.$license.'&requesttype=autoupdates&plugin=MK&website='.$bottom_host_name,
			__FILE__,
			'marketking'
		);
	}
}

// Begins execution of the plugin.
if (!function_exists('marketkingpro_run')){
	function marketkingpro_run() {

		function marketkingpro_activate() {
			require_once MARKETKINGPRO_DIR . 'includes/class-marketking-pro-activator.php';
			Marketkingpro_Activator::activate();

		}
		register_activation_hook( __FILE__, 'marketkingpro_activate' );

		require_once ( MARKETKINGPRO_DIR . 'includes/class-marketking-pro-helper.php' );
		require MARKETKINGPRO_DIR . 'includes/class-marketking-pro.php';

		// Load plugin language
		add_action( 'plugins_loaded', 'marketkingpro_load_language');
		function marketkingpro_load_language() {
			load_plugin_textdomain( 'marketking', FALSE, basename( dirname( __FILE__ ) ) . '/languages');
		}

		/** * @return Marketkingpro_Helper */
		function marketkingpro() {
		    return Marketkingpro_Helper::init();
		}

		$plugin = new Marketkingpro();
	}

	marketkingpro_run();
} else {
	
    register_activation_hook( __FILE__, 'marketking_activation_error' );
    function marketking_activation_error() {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
        wp_die( 'The plugin could not be activated because another version of MarketKing Pro, version '.MARKETKINGPRO_VERSION.' is already active. <strong>Please deactivate version '.MARKETKINGPRO_VERSION.' before activating this one.</strong>');
    }

}



