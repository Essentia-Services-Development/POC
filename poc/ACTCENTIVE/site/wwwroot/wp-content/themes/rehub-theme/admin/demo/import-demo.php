<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

/* 
 * Hooks and filters
 */
add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );
add_filter( 'pt-ocdi/confirmation_dialog_options', 'rehub_modal_window_settings' );
add_filter( 'pt-ocdi/plugin_page_setup', 'rehub_plugin_page_setup' );
add_filter( 'pt-ocdi/plugin_intro_text', 'rehub_plugin_intro_text' );
add_filter( 'pt-ocdi/import_files', 'rehub_import_files' );
add_action( 'pt-ocdi/before_content_import', 'rehub_before_import_setup' );
add_action( 'pt-ocdi/after_import', 'rehub_after_import_setup' );
add_action( 'admin_print_styles', 'rehub_modal_window_styles' );
add_filter( 'pt-ocdi/regenerate_thumbnails_in_content_import', '__return_false' );

/* 
 * Menu and page settings
 */
function rehub_plugin_page_setup( $default_settings ) {
    $default_settings['parent_slug'] = 'admin.php';
    $default_settings['page_title']  = esc_html__( 'Demo Import' , 'rehub-theme' );
    $default_settings['menu_title']  = esc_html__( 'Import Demo' , 'rehub-theme' );
    $default_settings['capability']  = 'administrator';
    $default_settings['menu_slug']   = 'import_demo';
    return $default_settings;
}

function rh_get_page_by_title($search){
	$query = new WP_Query(
		array(
			'post_type'              => 'page',
			'title'                  => $search,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => 'post_date ID',
			'order'                  => 'ASC',
		)
	);
	 
	if ( ! empty( $query->post ) ) {
		$page_got_by_title = $query->post;
	} else {
		$page_got_by_title = null;
	}

	return $page_got_by_title;
}
/* 
 * Changes intro text
 */
function rehub_plugin_intro_text( $default_text ) {
	$tf_support_date = '';
	$rehub_options = get_option( 'Rehub_Key' );
	$tf_username = isset( $rehub_options[ 'tf_username' ] ) ? $rehub_options[ 'tf_username' ] : '';
	$tf_purchase_code = isset( $rehub_options[ 'tf_purchase_code' ] ) ? $rehub_options[ 'tf_purchase_code' ] : '';

	$registeredlicense = false;
	if($tf_username && $tf_purchase_code){
		$registeredlicense = true;
	}
	if(!$registeredlicense){
		$default_text = sprintf( '<h3>To get access to ALL demo stacks, you must register your purchase.<br />See the <a href="%s">Product Registration tab</a> for instructions on how to complete registration.</h3>', admin_url( 'admin.php?page=rehub' ) );
	    return $default_text;		
	}else{
	    return '<br/><a href="http://rehubdocs.wpsoul.com/docs/rehub-theme/theme-install-update-translation/importing-demo-data/" target="_blank">'.__('How to use DEMO import and possible issues. Read before import','rehub-theme').'</a><br/><br/>';
	}	

}
/* 
 * Changes modal window settings
 */
function rehub_modal_window_settings( $options ) {
    return array_merge( $options, array( 'width' => 600, 'dialogClass' => 'rh-dialog' ) );
}
/* 
 * Changes modal window styles
 */
function rehub_modal_window_styles(){
	global $current_screen;
	$current_screen_id = ( $current_screen ) ? $current_screen->id : false;
	if( $current_screen_id && 'admin_page_import_demo' === $current_screen_id ){
		echo '<style type="text/css">.rh-dialog .ocdi__modal-image-container{text-align:center}.rh-dialog .ocdi__modal-image-container img{width:auto}</style>';
	}
}
/* 
 * Before Import setup
 */
function rehub_before_import_setup( $current_import ){
	$tf_support_date = '';
	$rehub_options = get_option( 'Rehub_Key' );
	$tf_username = isset( $rehub_options[ 'tf_username' ] ) ? $rehub_options[ 'tf_username' ] : '';
	$tf_purchase_code = isset( $rehub_options[ 'tf_purchase_code' ] ) ? $rehub_options[ 'tf_purchase_code' ] : '';

	require_once ( get_template_directory().'/admin/screens/lhelper.php');
	// Create a new LicenseBoxAPI helper class.
	$lbapi = new LicenseBoxAPI();

	// Performs background license check, pass TRUE as 1st parameter to perform periodic verifications only.
	$registeredlicense = false;
	if($tf_username && $tf_purchase_code){
	    $lb_verify_res = $lbapi->verify_license(false, sanitize_text_field($tf_purchase_code), sanitize_text_field($tf_username));
	    if(!empty($lb_verify_res['status'])){
	        $registeredlicense = true;
	    }
	}
	$rplugins = admin_url( 'admin.php?page=rehub-plugins' );
	$wpplugins = admin_url( 'plugin-install.php' );	
	if(!$registeredlicense){
		printf( '<h3>To get access to ALL demo stacks, you must register your purchase.<br />See the <a href="%s">Product Registration tab</a> for instructions on how to complete registration.</h3>', admin_url( 'admin.php?page=rehub' ) );	
		exit();		
	}	
	$curimp = $current_import['import_file_name'];
	if( 'RePick' === $curimp || 'ReMag' === $curimp || 'ReCash' === $curimp || 'ReDeal' === $curimp || 'ReViewit' === $curimp || 'ReCart' === $curimp || 'ReCompare' === $curimp || 'ReMart' === $curimp || 'ReWise' === $curimp || 'ReGame' === $curimp || 'ReLearn' === $curimp || 'ReMarket' === $curimp) {
		if($registeredlicense && empty($lb_verify_res['data']['themes'])){
			echo '<p style="color:red;font-size:180%" class="notofficialtheme">You have no access to demo import, because you are using nulled or not official theme version. Please, purchase theme on <a href="https://themeforest.net/item/rehub-directory-multi-vendor-shop-coupon-affiliate-theme/7646339">Themeforest</a>, otherwise, your site can be blocked.</p>';		
			exit();			
		}
	}else{
		if($registeredlicense && empty($lb_verify_res['data']['themes'])){
			echo '<p style="color:red;font-size:180%" class="notofficialtheme">You have no access to demo import, because you are using nulled or not official theme version. Please, purchase theme on <a href="https://themeforest.net/item/rehub-directory-multi-vendor-shop-coupon-affiliate-theme/7646339">Themeforest</a>, otherwise, your site can be blocked.</p>';		
			exit();			
		}
		if ( ! did_action( 'elementor/loaded' ) ) {
			echo 'This demo requires <a href="'.$rplugins.'" target="_blank">Elementor</a> plugin to be installed and activated.';		
			exit();	
		}		
	}
						

}
/* 
 * Demo data array
 */
function rehub_import_files() {
	$demos = array();
	if((isset( $_GET['page'] ) && 'import_demo' === $_GET['page']) || (wp_doing_ajax() && isset( $_REQUEST['selected']) )){
		$tf_support_date = '';
		$rehub_options = get_option( 'Rehub_Key' );
		$tf_username = isset( $rehub_options[ 'tf_username' ] ) ? $rehub_options[ 'tf_username' ] : '';
		$tf_purchase_code = isset( $rehub_options[ 'tf_purchase_code' ] ) ? $rehub_options[ 'tf_purchase_code' ] : '';

		require_once ( get_template_directory().'/admin/screens/lhelper.php');
		// Create a new LicenseBoxAPI helper class.
		$lbapi = new LicenseBoxAPI();

		// Performs background license check, pass TRUE as 1st parameter to perform periodic verifications only.
		$registeredlicense = false;
		if($tf_username && $tf_purchase_code){
		    $lb_verify_res = $lbapi->verify_license(false, sanitize_text_field($tf_purchase_code), sanitize_text_field($tf_username));
		    if(!empty($lb_verify_res['status'])){
		        $registeredlicense = true;
		    }
		}else{
			$lb_verify_res = '';
		}
		$rplugins = admin_url( 'admin.php?page=rehub-plugins' );
		$wpplugins = admin_url( 'plugin-install.php' );
		$themeaffoptions = admin_url( 'admin.php?page=vpt_option#_menu_aff' );
		$themegenoptions = admin_url( 'admin.php?page=vpt_option#_menu_1' );
		$requirednotice = esc_html__('Make sure that you have active next required plugins:', 'rehub-theme');
		$optionalnotice = esc_html__('Next plugins are optional. To get full demo functions, make sure that you installed and activated them. However, they are not required for demo and theme, if you think that you will not need them, just ignore them. If you have no optional plugins before import, you will have some warnings after demo import. Ignore them!', 'rehub-theme');
		$themeoptionnotice = esc_html__('Make sure that you have active next Theme options:', 'rehub-theme');
		$installpnotice = esc_html__('Install plugins', 'rehub-theme');
		$installonotice = esc_html__('Activate option', 'rehub-theme');

		if (!defined( 'WPFEPP_VERSION' )){
			$rhfrontendnotice = '<li><span style="color:red">RH Frontend Publishing Pro - NOT active</span>. <a href="'.$rplugins.'" target="_blank">Install plugin</a></li>';
		}
		else{
			$rhfrontendnotice = '<li>RH Frontend Publishing Pro - <span style="color:green">active</span>. Attention, demo import will overwrite your existing forms for plugin</li>';
		}
		if (!class_exists('WPBakeryVisualComposerAbstract')){
			$rhvcnotice = '<li><span style="color:red">Visual Composer - NOT active</span>. <a href="'.$rplugins.'" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhvcnotice = '<li>WP Bakery Visual Composer - <span style="color:green">active</span></li>';
		}
		if( !defined( 'RHWCT_VERSION' ) ){
			$rhwootoolnotice = '<li><span style="color:red">RH Woo Tools - NOT active</span>. <a href="'.$rplugins.'" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhwootoolnotice = '<li>RH Woo Tools - <span style="color:green">active</span></li>';
		}	
		if ( ! did_action( 'elementor/loaded' ) ) {
			$rhelnotice = '<li><span style="color:red">Elementor plugin - NOT active</span>. <a href="'.$rplugins.'" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhelnotice = '<li>Elementor - <span style="color:green">active</span></li>';
		}	
		if (!defined('\ContentEgg\PLUGIN_PATH')){
			$rhcenotice = '<li><span style="color:red">Content Egg (Offer module) - NOT active</span>. <a href="'.$rplugins.'" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhcenotice = '<li>Content Egg - <span style="color:green">active</span>. Enable Offer module</li>';
		}

		if (!defined('GREENSHIFT_DIR_URL')){
			$rhgsnotice = '<li><span style="color:red">Greenshift - NOT active</span>. <a href="'.$rplugins.'" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhgsnotice = '<li>Greenshift - <span style="color:green">active</span></li>';
		}

		if (!defined('GREENSHIFTGSAP_DIR_URL')){
			$rhgsanimatenotice = '<li><span style="color:red">Greenshift Animation Addon - NOT active</span>. <a href="'.$rplugins.'" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhgsanimatenotice = '<li>Greenshift Animation Addon - <span style="color:green">active</span></li>';
		}

		if (!class_exists('Woocommerce')){
			$rhwoonotice = '<li><span style="color:red">Woocommerce - NOT active</span>. <a href="'.$wpplugins.'?s=woocommerce&tab=search&type=term" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhwoonotice = '<li>Woocommerce - <span style="color:green">active</span></li>';
		}
		if(!class_exists( 'BuddyPress' ) ) {
			$rhbpnotice = '<li><span style="color:red">Buddypress - NOT active</span>. <a href="'.$wpplugins.'?s=buddypress&tab=search&type=term" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhbpnotice = '<li>Buddypress - <span style="color:green">active</span></li>';
		}	

		if (!class_exists('GMW_Posts_Locator_Addon')){
			$rhgmwpostnotice = '<li><span style="color:red">Geo My wordpress - NOT active</span>. <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a>. After installing plugin, go to Geo My Wordpress - extensions and activate Post Addon and Single Location addon, then Geo My Wordpress - settings and add your google map API keys</li>';
		}
		else{
			$rhgmwpostnotice = '<li>Geo My wordpress - <span style="color:green">active</span></li>';
		}	

		if (!class_exists('GMW_Members_locator_Addon') || !class_exists( 'BuddyPress' )){
			$rhgmwnotice = '<li><span style="color:red">Geo My wordpress - NOT active</span>. <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a>. After installing plugin, go to Geo My Wordpress - extensions and activate Member Addon and Single Location addon, then Geo My Wordpress - settings and add your google map API keys</li>';
		}
		else{
			$rhgmwnotice = '<li>Geo My wordpress - <span style="color:green">active</span></li>';
		}	

		if (!class_exists('WeDevs_Dokan')){
			$rhdokannotice = '<li><span style="color:red">Dokan - NOT active</span>. <a href="'.$wpplugins.'?s=dokan&tab=search&type=term" target="_blank">'.$installpnotice.'</a>. After activation - set google API keys in settings of Dokan to have store locator</li>';
		}
		else{
			$rhdokannotice = '<li>Dokan - <span style="color:green">active</span></li>';
		}

		if (!defined( 'wcv_plugin_dir' )){
			$rhvendornotice = '<li><span style="color:red">WC Vendor - NOT active</span>. <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhvendornotice = '<li>WC Vendor - <span style="color:green">active</span></li>';
		}

		if (!function_exists('get_mvx_vendor')){
			$rhmarketnotice = '<li><span style="color:red">MultivendorX - NOT active</span>. <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a></li>';
		}
		else{
			$rhmarketnotice = '<li>MultivendorX - <span style="color:green">active</span></li>';
		}			

		if (rehub_option('enable_brand_taxonomy') ){
			$rhstorenotice = '<li><code>Theme options -> Affiliate options -> Enable Affiliate Store taxonomy for posts</code> - <span style="color:green">active</span></li>';
		}
		else{
			$rhstorenotice = '<li><code>Theme options -> Affiliate options -> Enable Affiliate Store taxonomy for posts</code> <span style="color:red"> - NOT active</span>. <a href="'.$themeaffoptions.'" target="_blank">'.$installonotice.'</a></li>';		
		}
		if (rehub_option('enable_blog_posttype') ){
			$rhblognotice = '<li><code>Theme options -> General options -> Enable separate blog post type</code> - <span style="color:green">active</span></li>';
		}
		else{
			$rhblognotice = '<li><code>Theme options -> General options -> Enable separate blog post type</code> <span style="color:red"> - NOT active</span>. <a href="'.$themegenoptions.'" target="_blank">'.$installonotice.'</a></li>';		
		}

		$tutorialnotice = 'After installation, please, go to Tutorial Link in your main menu for further setup and explanations';

		$remagnotice = $requirednotice.'<ol>';
		$remagnotice .= $rhgsnotice;
		$remagnotice .='</ol>';
		$remagnotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$remagnotice .= $rhbpnotice;
		$remagnotice .= $rhfrontendnotice;	
		$remagnotice .= $rhcenotice;
		$remagnotice .= '<li>Mycred</li>';	
		$remagnotice .='</ol>';		

		$reviewitnotice = $requirednotice.'<ol>';
		$reviewitnotice .= $rhgsnotice;
		$reviewitnotice .= $rhgsanimatenotice;
		$reviewitnotice .='</ol>';
		$reviewitnotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$reviewitnotice .= $rhfrontendnotice;	
		$reviewitnotice .= $rhblognotice;
		$reviewitnotice .='</ol>';		

		$recashnotice = $requirednotice.'<ol>';
		$recashnotice .= $rhfrontendnotice;
		$recashnotice .= $rhgsnotice;
		$recashnotice .='</ol>';
		$recashnotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$recashnotice .= $rhbpnotice;
		$recashnotice .= '<li>MyCred</li>';	
		$recashnotice .='</ol>';
		$recashnotice .= $themeoptionnotice.'<ol>';
		$recashnotice .= $rhstorenotice;
		$recashnotice .='</ol>';

		$redealnotice = $requirednotice.'<ol>';
		$redealnotice .= $rhgsnotice;
		$redealnotice .= $rhgsanimatenotice;
		$redealnotice .='</ol>';
		$redealnotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$redealnotice .= $rhbpnotice;
		$redealnotice .= $rhfrontendnotice;
		$redealnotice .= '<li>MyCred (for cashback points)</li>';	
		$redealnotice .= '<li>Contact form 7 (for payment requests)</li>';
		$redealnotice .= '<li>WP Enable WebP (to enable WebP image format in WP)</li>';		
		$redealnotice .= '<li>CashbackTracker PRO (Paid plugin, for auto cashback tracking)</li>';
		$redealnotice .='</ol>';
		$redealnotice .= $themeoptionnotice.'<ol>';
		$redealnotice .= $rhstorenotice;
		$redealnotice .= $rhblognotice;
		$redealnotice .='</ol>';	


		$redirectnotice = $requirednotice.'<ol>';
		$redirectnotice .= $rhfrontendnotice;
		$redirectnotice .= $rhelnotice;
		$redirectnotice .= $rhwoonotice;		
		$redirectnotice .='</ol>';
		$redirectnotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$redirectnotice .= $rhgmwpostnotice;	
		$redirectnotice .= '<li>Contact form 7 (for contact forms)</li>';	
		$redirectnotice .= '<li>WCFM, WCFM Frontend, WCFM Membership for vendor options</li>';	
		$redirectnotice .='</ol>';
		$redirectnotice .= $themeoptionnotice.'<ol>';
		$redirectnotice .= $rhblognotice;
		$redirectnotice .= $rhstorenotice;	
		$redirectnotice .='</ol>';
		$redirectnotice .= $tutorialnotice;		

		$repicknotice = $requirednotice.'<ol>';
		$repicknotice .= $rhcenotice;
		$repicknotice .='</ol>';
		$repicknotice .= 'After installation, go to settings of Content Egg and enable Amazon and other modules. <a href="http://www.keywordrush.com/en/docs/content-egg" target="_blank">Check docs of Content Egg</a>. Choose "Shortcode only" for Add Content Option. <a href="https://wpsoul.com/guide-creating-profitable/" target="_blank">How to use plugin with theme in posts.</a>';

		$rewisenotice = $requirednotice.'<ol>';
		$rewisenotice .= $rhcenotice;
		$rewisenotice .= $rhgsnotice;
		$rewisenotice .= $rhwoonotice;
		$rewisenotice .='</ol>';
		$rewisenotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$rewisenotice .='</ol>';
		$rewisenotice .= $themeoptionnotice.'<ol>';
		$rewisenotice .= $rhstorenotice;
		$rewisenotice .= $rhblognotice;
		$rewisenotice .='</ol>';
		$rewisenotice .= 'After installation, go to settings of Content Egg and enable Amazon and other modules. <a href="http://www.keywordrush.com/en/docs/content-egg" target="_blank">Check docs of Content Egg</a>. Choose "Shortcode only" for Add Content Option. <br><br><a href="https://wpsoul.com/guide-creating-profitable/" target="_blank">How to use plugin with theme in posts.</a>, <br><br><a href="https://wpsoul.com/make-smart-profitable-deal-affiliate-comparison-site-woocommerce/" target="_blank">How to use plugin with theme for price comparison in products.</a>, <br><br><a href="http://rehubdocs.wpsoul.com/docs/rehub-theme/shop-options-woo-edd/better-product-filtering/" target="_blank">Better Product Filtering.</a>';

		$retournotice = $requirednotice.'<ol>';
		$retournotice .= $rhelnotice;
		$retournotice .= $rhwoonotice;
		$retournotice .='</ol>';
		$retournotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$retournotice .= $rhgmwnotice;	
		$retournotice .='</ol>';
		$retournotice .= '<span style="color:red">If you need Bookable options for product, you must use one of Booking plugins for Woocommerce. Plugins are not bundled with theme.</span> We recommend: <ul><li>Woocommerce Booking</li> <li>Woocommerce Appointment</li> <li>Woocommerce Booking and Rental</li></ul> If you need also to have multivendor option, these are possible combinations: <ul><li>WCFM (free or Ultimate) + any of Booking plugins</li><li>Dokan PRO with Booking addon + Woocommerce Booking</li><li>WC Vendor + Booking addon + Woocommerce Booking</li></ul>';	

		$recomparenotice = $requirednotice.'<ol>';
		$recomparenotice .= $rhcenotice;
		$recomparenotice .= $rhgsnotice;
		$recomparenotice .= $rhgsanimatenotice;
		$recomparenotice .= $rhwoonotice;
		$recomparenotice .='</ol>';
		$recomparenotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';	
		$recomparenotice .='</ol>';
		$recomparenotice .= 'After installation, go to settings of Content Egg and enable Amazon and other modules. <a href="http://www.keywordrush.com/en/docs/content-egg" target="_blank">Check docs of Content Egg</a>. Choose "Shortcode only" for Add Content Option. <br><br><a href="https://wpsoul.com/make-smart-profitable-deal-affiliate-comparison-site-woocommerce/" target="_blank">How to use plugin with theme for price comparison in products.</a><br><br>Revolution slider is not included in demo, but you can download plugin <a href="'.$rplugins.'" target="_blank">from bonus plugins</a> and download separate Sliders from our <a href="http://rehubdocs.wpsoul.com/docs/rehub-theme/page-builder/slider-and-top-area-ready-templates/" target="_blank">Slider import page</a>. <br><br><a href="http://rehubdocs.wpsoul.com/docs/rehub-theme/shop-options-woo-edd/better-product-filtering/" target="_blank">Better Product Filtering.</a>';

		$redokannotice = $requirednotice.'<ol>';
		$redokannotice .= $rhvcnotice;
		$redokannotice .= $rhwoonotice;
		$redokannotice .= $rhdokannotice;
		$redokannotice .= $rhbpnotice;	
		$redokannotice .='</ol>';
		$redokannotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$redokannotice .= '<li>Buddypress Follow</li>';
		$redokannotice .= $rhgmwnotice;	
		$redokannotice .='</ol>';	
		$redokannotice .= 'After installation, go to settings of vendor plugin for basic configuration. We recommend to read our guide for some additional information about <a href="https://wpsoul.com/how-to-create-multi-vendor-shop-on-wordpress/" target="_blank">Multi vendor sites</a> and also docs for Vendor plugin';

		$redokannewnotice = $requirednotice.'<ol>';
		$redokannewnotice .= $rhelnotice;
		$redokannewnotice .= $rhwoonotice;	
		$redokannewnotice .='</ol>';		
		$redokannewnotice .= $tutorialnotice;	

		$revendornotice = $requirednotice.'<ol>';
		$revendornotice .= $rhelnotice;
		$revendornotice .= $rhwoonotice;	
		$revendornotice .='</ol>';
		$revendornotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$revendornotice .= $rhbpnotice;
		$revendornotice .= $rhgmwnotice;
		$revendornotice .='</ol>';		
		$revendornotice .= $tutorialnotice;	

		$remarketnotice = $requirednotice.'<ol>';
		$remarketnotice .= $rhgsnotice;
		$remarketnotice .= $rhgsanimatenotice;
		$remarketnotice .= $rhwoonotice;	
		$remarketnotice .='</ol>';		
		$remarketnotice .= $tutorialnotice;

		$remartnotice = $requirednotice.'<ol>';
		$remartnotice .= $rhgsnotice;
		$remartnotice .= $rhwoonotice;	
		$remartnotice .='</ol>';		
		$remartnotice .= $tutorialnotice;

		$rethingnotice = $requirednotice.'<ol>';
		$rethingnotice .= $rhelnotice;
		$rethingnotice .='</ol>';
		$rethingnotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$rethingnotice .= $rhcenotice;	
		$rethingnotice .='</ol>';
		$rethingnotice .= 'After installation, go to settings of Content Egg and enable Offer or other modules. <a href="http://www.keywordrush.com/en/docs/content-egg" target="_blank">Check docs of Content Egg</a>. Choose "Shortcode only" for Add Content Option. <br><br><a href="https://wpsoul.com/guide-creating-profitable/" target="_blank">How to use plugin with theme in posts</a>, <br><br><a href="https://wpsoul.com/make-smart-profitable-deal-affiliate-comparison-site-woocommerce/" target="_blank">How to use plugin with theme for price comparison in products.</a>';

		$recartnotice = $requirednotice.'<ol>';
		$recartnotice .= $rhgsnotice;
		$recartnotice .= $rhgsanimatenotice;
		$recartnotice .= $rhwoonotice;
		$recartnotice .='</ol>';
		$recartnotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$recartnotice .= $rhcenotice;	
		$recartnotice .= $rhwootoolnotice;
		$recartnotice .='</ol>';
		$recartnotice .= '';

		$shopnotice = $requirednotice.'<ol>';
		$shopnotice .= $rhelnotice;
		$shopnotice .= $rhwoonotice;
		$shopnotice .='</ol>';
		$shopnotice .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$shopnotice .= $rhwootoolnotice;
		$shopnotice .='</ol>';
		$shopnotice .= '';

		$shopnoticeclean = $requirednotice.'<ol>';
		$shopnoticeclean .= $rhelnotice;
		$shopnoticeclean .= $rhwoonotice;
		$shopnoticeclean .='</ol>';
		$shopnoticeclean .= $optionalnotice.' <a href="'.$wpplugins.'" target="_blank">'.$installpnotice.'</a><ol>';
		$shopnoticeclean .='</ol>';

		if(!empty($lb_verify_res["data"])){
			$demos = array(
				array(
					'import_file_name' => 'ReMart',
					'categories' => array( esc_html__( 'Multi vendor', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReMart"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReMart"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/remart-theme.json',			
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo20_preview.jpg',
					'import_notice' => $remartnotice,
					'preview_url' => 'https://remart.lookmetrics.co/',
				),
				array(
					'import_file_name' => 'ReCart',
					'categories' => array( esc_html__( 'E-commerce', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReCart"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReCart"]["widgets"]),				
					'local_import_theme_file' => get_template_directory() . '/admin/demo/recart-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo11_preview.jpg',
					'import_notice' => $recartnotice,
					'preview_url' => 'https://recart.wpsoul.com/',
				),		
				array(
					'import_file_name' => 'ReCompare',
					'categories' => array( esc_html__( 'Comparison', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReCompare"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReCompare"]["widgets"]),				
					'local_import_theme_file' => get_template_directory() . '/admin/demo/recompare-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo10_preview.jpg',
					'import_notice' => $recomparenotice,
					'preview_url' => 'https://recompare.wpsoul.net/',
				),
				array(
					'import_file_name' => 'ReDeal',
					'categories' => array( esc_html__( 'Deal Site', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReDeal"]["content"]),
					'rhfrontend' => esc_url($lb_verify_res["data"]["themes"]["ReDeal"]["frontend"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReDeal"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/redeal-theme.json',		
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo14_preview.jpg',
					'import_notice' => $redealnotice,
					'preview_url' => 'https://redeal.lookmetrics.co/',
				),	
				array(
					'import_file_name' => 'ReFashion',
					'categories' => array( esc_html__( 'E-commerce', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReFashion"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReFashion"]["widgets"]),				
					'local_import_theme_file' => get_template_directory() . '/admin/demo/refashion-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo15_preview.jpg',
					'import_notice' => $shopnotice,
					'preview_url' => 'https://refashion.wpsoul.net/',
				),
				array(
					'import_file_name' => 'ReGame',
					'categories' => array( esc_html__( 'E-commerce', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReGame"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReGame"]["widgets"]),				
					'local_import_theme_file' => get_template_directory() . '/admin/demo/regame-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo18_preview.jpg',
					'import_notice' => $remartnotice,
					'preview_url' => 'https://regame.lookmetrix.com/',
				),
				array(
					'import_file_name' => 'ReLearn',
					'categories' => array( esc_html__( 'E-commerce', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReLearn"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReLearn"]["widgets"]),				
					'local_import_theme_file' => get_template_directory() . '/admin/demo/relearn-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo19_preview.jpg',
					'import_notice' => $remartnotice,
					'preview_url' => 'https://relearn.lookmetrics.co/',
				),		
				array(
					'import_file_name' => 'ReDigit',
					'categories' => array( esc_html__( 'E-commerce', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReDigit"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReDigit"]["widgets"]),				
					'local_import_theme_file' => get_template_directory() . '/admin/demo/redigit-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo17_preview.jpg',
					'import_notice' => $shopnoticeclean,
					'preview_url' => 'https://redigit.lookmetrix.com/',
				),	
				array(
					'import_file_name' => 'ReTour',
					'gmwforms' => esc_url($lb_verify_res["data"]["themes"]["ReTour"]["gmwforms"]),						
					'categories' => array( esc_html__( 'Multi vendor', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReTour"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReTour"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/retour-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo12_preview.jpg',
					'import_notice' => $retournotice,
					'preview_url' => 'https://retour.wpsoul.com/',
				),		
				array(
					'import_file_name' => 'RePick',
					'categories' => array( esc_html__( 'Deal Site', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["RePick"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["RePick"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/repick-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo2_preview.jpg',
					'import_notice' => $repicknotice,
					'preview_url' => 'https://repick.wpsoul.net/',
				),
				array(
					'import_file_name' => 'ReCash',
					'categories' => array( esc_html__( 'Deal Site', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReCash"]["content"]),
					'rhfrontend' => esc_url($lb_verify_res["data"]["themes"]["ReCash"]["frontend"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReCash"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/recash-theme.json',		
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo4_preview.jpg',
					'import_notice' => $recashnotice,
					'preview_url' => 'https://recash.wpsoul.net/',
				),
				array(
					'import_file_name' => 'ReDokanNew',
					'categories' => array( esc_html__( 'Multi vendor', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReDokanNew"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReDokanNew"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/redokannew-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo13_preview.jpg',
					'import_notice' => $redokannewnotice,
					'preview_url' => 'https://redokan.wpsoul.com/',
				),
				array(
					'import_file_name' => 'ReWise',
					'categories' => array( esc_html__( 'Comparison', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReWise"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReWise"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/rewise-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo7_preview.jpg',
					'import_notice' => $rewisenotice,
					'preview_url' => 'http://rewise.wpsoul.net/',
				),					
				array(
					'import_file_name' => 'ReVendor',
					'categories' => array( esc_html__( 'Multi vendor', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReVendor"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReVendor"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/revendor-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo6_preview.jpg',				
					'import_notice' => $revendornotice,
					'preview_url' => 'https://revendor.wpsoul.net/',
				),													
				array(
					'import_file_name' => 'ReThing',
					'categories' => array( esc_html__( 'Other', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReThing"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReThing"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/rething-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo3_preview.jpg',
					'import_notice' => $rethingnotice,
					'preview_url' => 'https://rething.wpsoul.net/',
				),
				array(
					'import_file_name' => 'ReDirect',
					'gmwforms' => esc_url($lb_verify_res["data"]["themes"]["ReDirect"]["gmwforms"]),				
					'rhfrontend' => esc_url($lb_verify_res["data"]["themes"]["ReDirect"]["frontend"]),			
					'categories' => array( esc_html__( 'Other', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReDirect"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReDirect"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/redirect-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo5_preview.jpg',
					'import_notice' => $redirectnotice,
					'preview_url' => 'https://redirect.wpsoul.net/',
				),
				array(
					'import_file_name' => 'ReMag',
					'categories' => array( esc_html__( 'Reviews', 'rehub-theme' ) ),				
					'rhfrontend' => esc_url($lb_verify_res["data"]["themes"]["ReMag"]["frontend"]),			
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReMag"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReMag"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/remag-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo1_preview.jpg',
					'import_notice' => $remagnotice,
					'preview_url' => 'https://remag.wpsoul.net/',
				),	
				array(
					'import_file_name' => 'ReViewit',
					'categories' => array( esc_html__( 'Reviews', 'rehub-theme' ) ),				
					'rhfrontend' => esc_url($lb_verify_res["data"]["themes"]["ReViewit"]["frontend"]),			
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReViewit"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReViewit"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/reviewit-theme.json',
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo16_preview.jpg',
					'import_notice' => $reviewitnotice,
					'preview_url' => 'https://reviewit.wpsoul.net/',
				),	
				array(
					'import_file_name' => 'ReMarket',
					'categories' => array( esc_html__( 'Multi vendor', 'rehub-theme' ) ),
					'import_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReMarket"]["content"]),
					'import_widget_file_url' => esc_url($lb_verify_res["data"]["themes"]["ReMarket"]["widgets"]),
					'local_import_theme_file' => get_template_directory() . '/admin/demo/remarket-theme.json',			
					'import_preview_image_url'   => get_template_directory_uri() .'/admin/screens/images/demo9_preview.jpg',
					'import_notice' => $remarketnotice,
					'preview_url' => 'https://remarket.wpsoul.com/',
				),				
			);	
		}		
	}
	return $demos;
	
}
/* 
 * After Import setup
 */
function rehub_after_import_setup( $current_import ) {
	
	$front_page = $blog_page = $main_menu = $top_menu = $user_menu = $stylesheet = '';
	$import_file_name = $current_import['import_file_name'];
	$options_file_path = isset( $current_import['local_import_theme_file'] ) ? $current_import['local_import_theme_file'] : '';
	$gmwforms = isset( $current_import['gmwforms'] ) ? $current_import['gmwforms'] : '';
	$rhfrontend = isset( $current_import['rhfrontend'] ) ? $current_import['rhfrontend'] : '';	
	$sliders = isset( $current_import['sliders'] ) ? $current_import['sliders'] : '';
	
	switch ( $import_file_name ) {
		case 'ReMag' :
			$front_page = rh_get_page_by_title( 'Homepage Remag' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );			
			break;
		case 'ReViewit' :
			$front_page = rh_get_page_by_title( 'Greenshift Reviewit Homepage' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			break; 	
		case 'ReGame' :
			$front_page = rh_get_page_by_title( 'Regame Homepage Greenshift' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			break; 	
		case 'ReFashion' :
			$front_page = rh_get_page_by_title( 'Homepage Refashion' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );			
			break;
		case 'ReDigit' :
			$front_page = rh_get_page_by_title( 'Homepage Redigit' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );			
			break;
		case 'RePick':
			$front_page = rh_get_page_by_title( 'Home page' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			$top_menu = get_term_by( 'slug', 'top-menu', 'nav_menu' );
			break;
		case 'ReThing':
			$front_page = rh_get_page_by_title( 'Homepage Rething' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			break;
		case 'ReCash':
			$front_page = rh_get_page_by_title( 'Greenshift Recash' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			break;
		case 'ReDeal':
			$front_page = rh_get_page_by_title( 'Greenshift Frontpage' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			$themeoptionmenu = get_term_by('name', 'Menu for logo section', 'nav_menu');
			break;			
		case 'ReDirect':
			$front_page = rh_get_page_by_title( 'Homepage Redirect' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );		
			break;
		case 'ReVendor':
			$front_page = rh_get_page_by_title( 'Revendor Home' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );			
			break;
		case 'ReLearn':
			$front_page = rh_get_page_by_title( 'Relearn Homepage Greenshift' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );			
			break;
		case 'ReWise':
			$front_page = rh_get_page_by_title( 'Homepage Rewise Greenshift' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			break;		
		case 'ReDokan':
			$front_page = rh_get_page_by_title( 'Home Redokan' );
			$blog_page = rh_get_page_by_title( 'Reviews' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			$userarray = array(
				array (
					'email' => 'redokan@test.com',
					'name' => 'Redokanvendor',
					'role' => 'seller',
					'meta' => array(
						'dokan_store_name' => 'Redokanvendor'
					),
					'location'=> '18 West St, Brooklyn, NY 11222, USA',
					'posts' => 3,
					'products' => 5,
				),
			);			
			break;
		case 'ReDokanNew':
			$front_page = rh_get_page_by_title( 'Homepage Redokan' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			$top_menu = get_term_by( 'slug', 'top-menu', 'nav_menu' );		
			break;			
		case 'ReMarket':
			$front_page = rh_get_page_by_title( 'Remarket Homepage Greenshift' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );	
			$top_menu = get_term_by( 'slug', 'top-menu', 'nav_menu' );		
			break;
		case 'ReCart':
			$front_page = rh_get_page_by_title( 'Homepage Greenshift' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );			
			break;	
		case 'ReMart':
			$front_page = rh_get_page_by_title( 'Homepage Greenshift' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );			
			break;	
		case 'ReTour':
			$front_page = rh_get_page_by_title( 'Homepage Booking' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );			
			break;							
			
		case 'ReCompare':
			$front_page = rh_get_page_by_title( 'Greenshift Homepage' );
			$blog_page = rh_get_page_by_title( 'News and reviews' );
			$main_menu = get_term_by( 'slug', 'main-menu', 'nav_menu' );
			break;
		default:			
	}
	
	if( $options_file_path ) {
		$options_raw_data = OCDI\Helpers::data_from_file( $options_file_path );
		if ( !is_wp_error( $options_raw_data ) ) {
			$theme_options_data = json_decode( $options_raw_data, true );
			$rehub_wizard_option = get_option('rehub_wizard_option');
			if(!empty($rehub_wizard_option)){
				foreach ($rehub_wizard_option as $opkey => $opvalue) {
					$theme_options_data[$opkey] = sanitize_text_field($opvalue);
				}
			}
			update_option( 'rehub_option', $theme_options_data );	
			echo 'Theme Options was updated-------';	
			if(class_exists('REHub_Framework_Customizer')){
				$customizer = new REHub_Framework_Customizer();
        		$customizer->rh_save_customizer_options( $theme_options_data );	
        		echo 'Customizer was updated-------';			
			}
	
		}
	}

	if( $gmwforms && function_exists('gmw_object_to_array')) {
		$gmwforms_data = OCDI\Helpers::data_from_file( $gmwforms );
		if ( !is_wp_error( $gmwforms_data ) ) {
			$gmwforms = json_decode( $gmwforms_data, true );
			$forms = gmw_object_to_array( $gmwforms );
			global $wpdb;	
			foreach ( $forms as $form ) {
		        $wpdb->insert( 
		            $wpdb->prefix . 'gmw_forms', 
		            array( 
		            	'slug'   => $form['slug'], 
		            	'addon'  => $form['addon'],  
		                'name'   => $form['name'],
		                'title'  => $form['title'],
		                'prefix' => $form['prefix'],
		                'data'	 => $form['data']
		            ),
		            array(
		                '%s',
		                '%s',
		                '%s',
		                '%s',
		                '%s',
		                '%s'
		            )
		        );
		    }			
			echo 'GMW forms was added-------';		
		}
	}

	if( $rhfrontend && defined( 'WPFEPP_SLUG' )) {
		rh_import_tables_from_json('wpfepp_forms', $rhfrontend );			
		echo 'RH Frontend forms was added-------';		
	}		
	
	$main_menu_id = ($main_menu ) ? (int) $main_menu->term_id : '';
	$top_menu_id =( $top_menu ) ? (int) $top_menu->term_id : '';
	$themeoptionmenu_id = (isset($themeoptionmenu)) ? (int) $themeoptionmenu->term_id : '';
	set_theme_mod( 'nav_menu_locations', array( 'primary-menu' => $main_menu_id, 'top-menu' => $top_menu_id) );
	if($themeoptionmenu_id){
		$theme_option = get_option('rehub_option');
		$theme_option['header_six_menu'] = $themeoptionmenu_id;
		update_option( 'rehub_option', $theme_option );
	}
	echo 'Menu was assigned-------';

	if ($import_file_name == 'ReCash'){
		$firstparent = rh_get_page_by_title( 'Layout examples', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'273', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($firstparent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $firstparent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
	}

	if ($import_file_name == 'ReDokan'){
		$firstparent = rh_get_page_by_title( 'Unique product types', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'531', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($firstparent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $firstparent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
	}				

	if ($import_file_name == 'RePick'){
		$firstparent = rh_get_page_by_title( 'Post Layouts', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'131', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($firstparent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $firstparent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( 'Basic', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'4461', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent3 = rh_get_page_by_title( 'Advanced', OBJECT, 'nav_menu_item');
		$menus3 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'4462', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus3) && !empty($parent3)){
			foreach ($menus3 as $menu3) {
				update_post_meta($menu3->ID, '_menu_item_menu_item_parent', $parent3->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
	}	

	if ($import_file_name == 'ReGame'){
		update_option('woocommerce_thumbnail_cropping_custom_height', 4);
		update_option('woocommerce_thumbnail_cropping_custom_width', 3);
		update_option('woocommerce_thumbnail_cropping', 'custom');
		echo 'Custom image size was set-------';
	}

	if ($import_file_name == 'ReCompare'){
		$parent = rh_get_page_by_title( 'Best conversion pages', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'566', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( '🔥 Unique Functions', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'539', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent3 = rh_get_page_by_title( 'Simple Product Layouts', OBJECT, 'nav_menu_item');
		$menus3 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'541', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus3) && !empty($parent3)){
			foreach ($menus3 as $menu3) {
				update_post_meta($menu3->ID, '_menu_item_menu_item_parent', $parent3->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent4 = rh_get_page_by_title( 'Advanced Product Layout', OBJECT, 'nav_menu_item');
		$menus4 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'546', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus4) && !empty($parent4)){
			foreach ($menus4 as $menu4) {
				update_post_meta($menu4->ID, '_menu_item_menu_item_parent', $parent4->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}						
	}

	if ($import_file_name == 'ReTour'){
		$parent = rh_get_page_by_title( 'Layout Variants', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'201', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( 'System Pages', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'366', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent3 = rh_get_page_by_title( 'Inner Product', OBJECT, 'nav_menu_item');
		$menus3 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'202', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus3) && !empty($parent3)){
			foreach ($menus3 as $menu3) {
				update_post_meta($menu3->ID, '_menu_item_menu_item_parent', $parent3->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent4 = rh_get_page_by_title( 'Inner Blog', OBJECT, 'nav_menu_item');
		$menus4 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'203', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus4) && !empty($parent4)){
			foreach ($menus4 as $menu4) {
				update_post_meta($menu4->ID, '_menu_item_menu_item_parent', $parent4->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}	
		$parent5 = rh_get_page_by_title( 'Archive pages', OBJECT, 'nav_menu_item');
		$menus5 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'204', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus5) && !empty($parent5)){
			foreach ($menus5 as $menu5) {
				update_post_meta($menu5->ID, '_menu_item_menu_item_parent', $parent5->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}									
	}	

	if ($import_file_name == 'ReMag'){
		$parent = rh_get_page_by_title( 'Page layouts', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'4008', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( 'Basic Post layouts', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'4009', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent3 = rh_get_page_by_title( 'Deal Post layouts', OBJECT, 'nav_menu_item');
		$menus3 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'4018', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus3) && !empty($parent3)){
			foreach ($menus3 as $menu3) {
				update_post_meta($menu3->ID, '_menu_item_menu_item_parent', $parent3->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent4 = rh_get_page_by_title( 'Extended layouts', OBJECT, 'nav_menu_item');
		$menus4 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'4022', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus4) && !empty($parent4)){
			foreach ($menus4 as $menu4) {
				update_post_meta($menu4->ID, '_menu_item_menu_item_parent', $parent4->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}	
		$parent5 = rh_get_page_by_title( 'Unique pages', OBJECT, 'nav_menu_item');
		$menus5 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'4027', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus5) && !empty($parent5)){
			foreach ($menus5 as $menu5) {
				update_post_meta($menu5->ID, '_menu_item_menu_item_parent', $parent5->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}														
	}	

	if ($import_file_name == 'ReFashion'){
		$parent = rh_get_page_by_title( 'Woocommerce Pages', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'2227', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( 'Advanced Product Layouts', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'2235', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent3 = rh_get_page_by_title( 'Basic product Layouts', OBJECT, 'nav_menu_item');
		$menus3 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'2236', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus3) && !empty($parent3)){
			foreach ($menus3 as $menu3) {
				update_post_meta($menu3->ID, '_menu_item_menu_item_parent', $parent3->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent4 = rh_get_page_by_title( 'Shop pages', OBJECT, 'nav_menu_item');
		$menus4 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'2237', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus4) && !empty($parent4)){
			foreach ($menus4 as $menu4) {
				update_post_meta($menu4->ID, '_menu_item_menu_item_parent', $parent4->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}	
		$parent5 = rh_get_page_by_title( 'Post Pages', OBJECT, 'nav_menu_item');
		$menus5 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'2239', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus5) && !empty($parent5)){
			foreach ($menus5 as $menu5) {
				update_post_meta($menu5->ID, '_menu_item_menu_item_parent', $parent5->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsix = rh_get_page_by_title( 'Deal and Coupon Layouts', OBJECT, 'nav_menu_item');
		$menussix = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'2240', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussix) && !empty($parentsix)){
			foreach ($menussix as $menusix) {
				update_post_meta($menusix->ID, '_menu_item_menu_item_parent', $parentsix->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}	
		$parent7 = rh_get_page_by_title( 'Post and Review Layouts', OBJECT, 'nav_menu_item');
		$menus7 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'2241', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus7) && !empty($parent7)){
			foreach ($menus7 as $menu7) {
				update_post_meta($menu7->ID, '_menu_item_menu_item_parent', $parent7->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}	
		$parenteight = rh_get_page_by_title( 'Promo pages', OBJECT, 'nav_menu_item');
		$menuseight = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'2228', 'post_type'=> 'nav_menu_item'));
		if(!empty($menuseight) && !empty($parenteight)){
			foreach ($menuseight as $menueight) {
				update_post_meta($menueight->ID, '_menu_item_menu_item_parent', $parenteight->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}													
	}

	if ($import_file_name == 'ReDigit'){
		$parent = rh_get_page_by_title( 'Product Layouts', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'63', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( 'Basic Layouts', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'74', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent3 = rh_get_page_by_title( 'Advanced Layouts', OBJECT, 'nav_menu_item');
		$menus3 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'84', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus3) && !empty($parent3)){
			foreach ($menus3 as $menu3) {
				update_post_meta($menu3->ID, '_menu_item_menu_item_parent', $parent3->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent4 = rh_get_page_by_title( 'Other pages', OBJECT, 'nav_menu_item');
		$menus4 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'379', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus4) && !empty($parent4)){
			foreach ($menus4 as $menu4) {
				update_post_meta($menu4->ID, '_menu_item_menu_item_parent', $parent4->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}													
	}

	if ($import_file_name == 'ReViewit'){
		$parent = rh_get_page_by_title( 'Special layouts', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'775', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( 'Post Layouts', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'1130', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent3 = rh_get_page_by_title( 'Top Listing layouts', OBJECT, 'nav_menu_item');
		$menus3 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'1131', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus3) && !empty($parent3)){
			foreach ($menus3 as $menu3) {
				update_post_meta($menu3->ID, '_menu_item_menu_item_parent', $parent3->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}														
	}

	if ($import_file_name == 'ReDirect'){
		$parent = rh_get_page_by_title( 'Page examples', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'257', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentfour = rh_get_page_by_title( 'Inner Page Layouts', OBJECT, 'nav_menu_item');
		$menusfour = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'438', 'post_type'=> 'nav_menu_item'));
		if(!empty($menusfour) && !empty($parentfour)){
			foreach ($menusfour as $menufour) {
				update_post_meta($menufour->ID, '_menu_item_menu_item_parent', $parentfour->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentfive = rh_get_page_by_title( 'Main Layouts', OBJECT, 'nav_menu_item');
		$menusfive = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'1296', 'post_type'=> 'nav_menu_item'));
		if(!empty($menusfive) && !empty($parentfive)){
			foreach ($menusfive as $menufive) {
				update_post_meta($menufive->ID, '_menu_item_menu_item_parent', $parentfive->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsix = rh_get_page_by_title( 'With custom elements', OBJECT, 'nav_menu_item');
		$menussix = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'1297', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussix) && !empty($parentsix)){
			foreach ($menussix as $menusix) {
				update_post_meta($menusix->ID, '_menu_item_menu_item_parent', $parentsix->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}											
	}

	if ($import_file_name == 'ReThing'){
		$parent = rh_get_page_by_title( 'Post formats', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'250', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( 'Custom pages', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'270', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}			
	}

	if ($import_file_name == 'ReCart'){
		$parent = rh_get_page_by_title( 'Browse Categories', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'487', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( 'Woocommerce Layouts', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'489', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent3 = rh_get_page_by_title( 'Post & Review Layouts', OBJECT, 'nav_menu_item');
		$menus3 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'1019', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus3) && !empty($parent3)){
			foreach ($menus3 as $menu3) {
				update_post_meta($menu3->ID, '_menu_item_menu_item_parent', $parent3->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent4 = rh_get_page_by_title( 'Promo pages', OBJECT, 'nav_menu_item');
		$menus4 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'490', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus4) && !empty($parent4)){
			foreach ($menus4 as $menu4) {
				update_post_meta($menu4->ID, '_menu_item_menu_item_parent', $parent4->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}	
		$parent5 = rh_get_page_by_title( 'Audio gadgets', OBJECT, 'nav_menu_item');
		$menus5 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'962', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus5) && !empty($parent5)){
			foreach ($menus5 as $menu5) {
				update_post_meta($menu5->ID, '_menu_item_menu_item_parent', $parent5->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent6 = rh_get_page_by_title( 'Television and Systems', OBJECT, 'nav_menu_item');
		$menus6 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'963', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus6) && !empty($parent6)){
			foreach ($menus6 as $menu6) {
				update_post_meta($menu6->ID, '_menu_item_menu_item_parent', $parent6->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}										
	}	

	if ($import_file_name == 'ReDokanNew' || $import_file_name == 'ReMarket' || $import_file_name == 'ReVendor'){
		$parent = rh_get_page_by_title( 'Browse Categories', OBJECT, 'nav_menu_item');
		$menus = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'487', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus) && !empty($parent)){
			foreach ($menus as $menu) {
				update_post_meta($menu->ID, '_menu_item_menu_item_parent', $parent->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parentsec = rh_get_page_by_title( 'Woocommerce Layouts', OBJECT, 'nav_menu_item');
		$menussec = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'489', 'post_type'=> 'nav_menu_item'));
		if(!empty($menussec) && !empty($parentsec)){
			foreach ($menussec as $menusec) {
				update_post_meta($menusec->ID, '_menu_item_menu_item_parent', $parentsec->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent3 = rh_get_page_by_title( 'Post & Review Layouts', OBJECT, 'nav_menu_item');
		$menus3 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'1019', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus3) && !empty($parent3)){
			foreach ($menus3 as $menu3) {
				update_post_meta($menu3->ID, '_menu_item_menu_item_parent', $parent3->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent4 = rh_get_page_by_title( 'Promo pages', OBJECT, 'nav_menu_item');
		$menus4 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'490', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus4) && !empty($parent4)){
			foreach ($menus4 as $menu4) {
				update_post_meta($menu4->ID, '_menu_item_menu_item_parent', $parent4->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}	
		$parent5 = rh_get_page_by_title( 'Audio gadgets', OBJECT, 'nav_menu_item');
		$menus5 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'962', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus5) && !empty($parent5)){
			foreach ($menus5 as $menu5) {
				update_post_meta($menu5->ID, '_menu_item_menu_item_parent', $parent5->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent6 = rh_get_page_by_title( 'Television and Systems', OBJECT, 'nav_menu_item');
		$menus6 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'963', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus6) && !empty($parent6)){
			foreach ($menus6 as $menu6) {
				update_post_meta($menu6->ID, '_menu_item_menu_item_parent', $parent6->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}	
		$parent8 = rh_get_page_by_title( 'Currency', OBJECT, 'nav_menu_item');
		$menus8 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'495', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus8) && !empty($parent8)){
			foreach ($menus8 as $menu8) {
				update_post_meta($menu8->ID, '_menu_item_menu_item_parent', $parent8->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}
		$parent9 = rh_get_page_by_title( 'Language', OBJECT, 'nav_menu_item');
		$menus9 = get_posts(array('meta_key'=>'_menu_item_menu_item_parent', 'meta_value'=>'498', 'post_type'=> 'nav_menu_item'));
		if(!empty($menus9) && !empty($parent9)){
			foreach ($menus9 as $menu9) {
				update_post_meta($menu9->ID, '_menu_item_menu_item_parent', $parent9->ID);
			}
			echo 'Menu hierarchy was fixed-------';
		}																
	}			

	
    if( $front_page ){
		$front_page_id = (int) $front_page->ID;
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_page_id );
		echo 'HomePage was assigned-------';
	}
   
	if( $blog_page ){
		$blog_page_id = (int) $blog_page->ID;
		update_option( 'page_for_posts', $blog_page_id );
		echo 'Blog page was assigned-------';
	}

	if(!empty($sliders) && function_exists( 'download_url' ) && function_exists('media_handle_sideload') && class_exists( 'RevSlider' )){
        foreach( $sliders as $slider_url ) {
            $temp = download_url( $slider_url );
            $file_array = array(
                'name'     => basename( $slider_url ),
                'tmp_name' => $temp
            );
            if ( is_wp_error( $temp ) ) {
				echo 'Slider has error-------';
                unlink( $file_array[ 'tmp_name' ] );
                continue;
            }

            $id = media_handle_sideload( $file_array, 0 );
            if ( is_wp_error( $id ) ) {
				echo 'Slider has error-------';
                unlink( $file_array['tmp_name'] );
                continue;
            }

            $attachment_url = get_attached_file( $id );
            $slider = new RevSlider();
            $slider->importSliderFromPost( true, true, $attachment_url );
            echo 'Slider was imported-------';
        }		
	}

	if(!empty($userarray)){
		foreach ($userarray as $userset) {

			if( null == username_exists( $userset['email'] ) ) {
				$password = wp_generate_password( 12, false );
				$user_id = wp_create_user( $userset['email'], $password, $userset['email'] );
				wp_update_user(
					array(
					  	'ID'          =>    $user_id,
					  	'nickname'    =>    $userset['name'],
					  	'first_name'  =>	$userset['name'],
					)
				);
				$user = new WP_User( $user_id );
				$user->set_role( $userset['role'] );
				echo 'User '.$user_id.' was created-------';

				if(!empty($userset['location']) && function_exists('gmw_update_user_location') && class_exists('GMW_Members_locator_Addon')){
					gmw_update_user_location( $user_id, $userset['location'], true );
					echo 'User '.$user_id.' has location now-------';
				}
				if(!empty($userset['meta'])){
					foreach ($userset['meta'] as $key => $value) {
						update_user_meta( $user_id, $key, $value);
						echo 'User '.$user_id.' has meta now for '.$key.'-------';
					}
				}
				if(!empty($userset['posts'])){
					$number = $userset['posts'];
					$changedposts = get_posts(array('numberposts' => $number, 'post_type' => 'post'));
					if(!empty($changedposts)){
						foreach ($changedposts as $changedpost) {
							$arg = array(
							    'ID' => $changedpost->ID,
							    'post_author' => $user_id,
							);
							wp_update_post( $arg );	
						}
						echo 'User '.$user_id.' has posts now-------';						
					}				
				}				
				if(!empty($userset['products'])){
					$number = $userset['products'];
					$changedproducts = get_posts(array('numberposts' => $number, 'post_type' => 'product'));
					if(!empty($changedproducts)){
						foreach ($changedproducts as $changedproduct) {
							$arg = array(
							    'ID' => $changedproduct->ID,
							    'post_author' => $user_id,
							);
							wp_update_post( $arg );	
						}
						echo 'User '.$user_id.' has products now-------';	
					}				
				}
			} 
		}
	}
	
}