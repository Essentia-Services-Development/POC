<?php
/*
Plugin Name: REHub Framework
Plugin URI: https://themeforest.net/item/rehub-directory-multi-vendor-shop-coupon-affiliate-theme/7646339
Description: Framework plugin for REHub - Price Comparison, Affiliate Marketing, Multi Vendor Store, Community Theme.
Version: 19.5
Author: Wpsoul
Author URI: https://wpsoul.com/
Text Domain: rehub-framework
Domain Path: /lang/
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/* Costants */
if ( ! defined( 'RH_PLUGIN_VER' ) ){
	define( 'RH_PLUGIN_VER', '19.5' );
}

if ( ! defined( 'RH_FRAMEWORK_ABSPATH' ) ) {
	define( 'RH_FRAMEWORK_ABSPATH', dirname( __FILE__ ) );
}

if ( ! defined( 'RH_FRAMEWORK_URL' ) ) {
	define( 'RH_FRAMEWORK_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
}

if ( get_template() === 'rehub-theme' ) {	
	
	/* Include the main Rehub Framework class. */
	if ( ! class_exists( 'REHub_Framework' ) ) {
		include_once RH_FRAMEWORK_ABSPATH .'/includes/class-rehub.php';
	}
	
	// run the plugin
	new REHub_Framework;

	//run Gutenberg
	require_once RH_FRAMEWORK_ABSPATH .'/class-autoload.php';
	new \Rehub\Gutenberg\Init;

	// Gutenberg patterns
	if(function_exists('register_block_pattern')){
		require_once RH_FRAMEWORK_ABSPATH .'/gutenberg/gutenbergtemplates.php';
		remove_theme_support( 'core-block-patterns' );
	}	

}
else {
	add_action( 'admin_notices', 'rh_admin_notice_warning' );
}


/* Show notice in the admin dashboard if the REHub theme is not active */
function rh_admin_notice_warning() {
	if ( is_plugin_active( plugin_basename( __FILE__ ) ) ){
		// deactivate_plugins( plugin_basename( __FILE__ ) );
		?>
		<div class="notice notice-warning">
			<p><?php printf( esc_html__( 'Sorry, REHub Framework plugin works only with REHub themes. Please, deactivate Rehub Framework plugin or activate REHub theme %s', 'rehub-framework' ), '<a href="https://1.envato.market/JZgzN" target="_blank">here</a>' ) ; ?></p>
		</div>
		<?php
	}
}

//Installation wizard
register_activation_hook(__FILE__, 'rehub_framework_plugin_activate');
function rehub_framework_plugin_activate() {
set_transient( '_rehub_activation_redirect', true, 0 );
}

add_action( 'admin_init', 'rehub_welcome_screen_do_activation_redirect' );
function rehub_welcome_screen_do_activation_redirect() {
    // Bail if no activation redirect
    if ( ! get_transient( '_rehub_activation_redirect' ) )
        return;

    // Delete the redirect transient
    delete_transient( '_rehub_activation_redirect' );

    // Bail if activating from network, or bulk
    if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
        return;

    wp_redirect( html_entity_decode(wp_nonce_url(admin_url('plugins.php?page=rehub_wizard&rehub_install=1'), '_wpnonce') )); exit;
}