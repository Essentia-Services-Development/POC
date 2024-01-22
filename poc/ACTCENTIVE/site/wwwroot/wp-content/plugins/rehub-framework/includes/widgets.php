<?php
/**
 * Rehub Framework Widget Functions
 *
 * @package ReHub\Functions
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

 
//////////////////////////////////////////////////////////////////
// Include widgets
//////////////////////////////////////////////////////////////////


include (rf_locate_template('inc/widgets/tabs_widget.php')); 
include (rf_locate_template('inc/widgets/tabsajax_widget.php'));
include (rf_locate_template('inc/widgets/posts_list.php'));
include (rf_locate_template('inc/widgets/featured_sidebar.php'));
include (rf_locate_template('inc/widgets/social_link_widget.php'));
include (rf_locate_template('inc/widgets/sticky_scroll.php'));
include (rf_locate_template('inc/widgets/related_reviews.php'));
include (rf_locate_template('inc/widgets/top_offers.php'));
include (rf_locate_template('inc/widgets/outer_ads.php'));
include (rf_locate_template('inc/widgets/better_menu.php'));
include (rf_locate_template('inc/widgets/imagetrend_sidebar.php'));
include (rf_locate_template('inc/widgets/condition_widget.php'));
include (rf_locate_template('inc/widgets/dealwoo.php'));
include (rf_locate_template('inc/widgets/latest_comparison.php'));
include (rf_locate_template( 'inc/widgets/woocategory.php' ));


add_action( 'plugins_loaded', 'rh_conditional_widget_init' );
function rh_conditional_widget_init(){
	if(defined( 'WCFMmp_TOKEN' )){
		include (rf_locate_template('inc/widgets/user_profile.php'));
	}    
	if ( class_exists('Woocommerce') ) {
		require_once dirname( WC_PLUGIN_FILE ) . '/includes/abstracts/abstract-wc-widget.php';
		include (rf_locate_template( 'inc/widgets/woofilterbrand.php' ));
	}
}