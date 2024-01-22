<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
$rehub_theme = wp_get_theme();
if($rehub_theme->parent_theme) {
    $template_dir =  basename(get_template_directory());
    $rehub_theme = wp_get_theme($template_dir);
}
$rehub_version = $rehub_theme->get( 'Version' );
$tf_support_date = '';
$rehub_options = get_option( 'Rehub_Key' );
$tf_username = isset( $rehub_options[ 'tf_username' ] ) ? $rehub_options[ 'tf_username' ] : '';
$tf_purchase_code = isset( $rehub_options[ 'tf_purchase_code' ] ) ? $rehub_options[ 'tf_purchase_code' ] : '';

require_once ( 'lhelper.php');
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
$theme_url = 'https://wpsoul.com/';
?>
<div class="wrap about-wrap rehub-wrap">
	<h1><?php echo esc_html__( "Welcome to ReHub Theme!", "rehub-theme" ); ?></h1>
    <?php if( $registeredlicense == true ) :?>
    <div class="about-text">
        <?php echo esc_html__( "Theme is registered on your site! ", "rehub-theme" ); ?>
        <?php if ($tf_support_date):?>
            <?php echo esc_html__( "You have support until: ", "rehub-theme" ); ?><?php $date = date_create($tf_support_date); echo date_format($date, 'Y-m-d');?>
            <a href="http://themeforest.net/item/rehub-directory-shop-coupon-affiliate-theme/7646339" target="_blank"><?php echo esc_html__( "(extend support)", "rehub-theme" ); ?></a><br />
        <?php endif;?>
        <?php if ( ! function_exists( 'envato_market' ) ) :?>
            <?php echo esc_html__( "If you need automatic theme updates, install Envato Market plugin from ", "rehub-theme" ); ?>
            <a href="<?php echo admin_url( 'admin.php?page=rehub-plugins' );?>"><?php echo esc_html__( "Plugins Tab", "rehub-theme" ); ?></a>
        <?php endif;?>  
    </div>
    <?php else :?>
    <div class="about-text"><?php echo esc_html__( "ReHub Theme is now installed and ready to use! Please register your purchase to get support, automatic theme updates, demo stacks, bonuses.", "rehub-theme" ); ?></div> 
    <?php endif;?>
    <div class="rehub-logo"><span class="rehub-version"><?php esc_html_e( "Version", "rehub-theme" ); ?> <?php echo esc_html($rehub_version); ?></span></div>
	<h2 class="nav-tab-wrapper">
    	<?php
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__( "Registration", "rehub-theme" ) );
		printf( '<a href="#" class="nav-tab nav-tab-active">%s</a>', esc_html__( "Support and tips", "rehub-theme" ) );
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=rehub-plugins' ), esc_html__( "Plugins", "rehub-theme" ) );
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=import_demo' ), esc_html__( "Demo import", "rehub-theme" ) );
		?>
	</h2>
    <?php if( !$registeredlicense == true ) :?>
    <div class="rehub-important-notice">
		<p class="about-description"><?php echo esc_html__( "To access our support forum, demo stacks, bonuses, you must be official buyer.
        If you don't have official license of theme - ", "rehub-theme" ); ?><a href="http://themeforest.net/item/rehub-directory-shop-coupon-affiliate-theme/7646339" target="_blank"><?php echo esc_html__( "buy theme on Themeforest", "rehub-theme" ); ?></a></p>
    </div>
    <?php endif ;?>
	<div class="rehub-registration-steps">
        <script>
            function rhperformSearch(type = 'docs') {
                var searchTerm = encodeURIComponent(document.getElementById('searchField').value);
                if(type == 'doc'){
                    window.open('http://rehubdocs.wpsoul.com/?s=' + searchTerm, 'rehub_search');                    
                }
                else if(type == 'blog'){
                    window.open('https://wpsoul.com/?s=' + searchTerm, 'blog_search');
                }else{
                    window.open('http://www.google.com/search?q=' + searchTerm, 'google_search');
                }
                
            }
        </script>
        <form id="formsearchsection">
            <h3>Quick Search</h3>
            <input id="searchField" type="text" style="width:100%; max-width: 420px; font-size:20px;" />
            <div style="display: flex; margin-top:20px; margin-bottom: 30px">
            <span class="button button-large button-primary rehub-large-button" style="margin-right: 15px" onclick="rhperformSearch('doc');">Search in Docs</span>
            <span class="button button-large button-primary rehub-large-button" style="margin-right: 15px" onclick="rhperformSearch('blog');">Search in Blog</span>
            <span class="button button-large button-primary rehub-large-button" onclick="rhperformSearch('google');">Search in Google</span>
            </div>
        </form>
        <div class="feature-section">
            <strong>Some important tutorials to make your site better:</strong>
                <ul>
                <li><a href="https://wpsoul.com/make-smart-profitable-deal-affiliate-comparison-site-woocommerce/" target="_blank">Step by step guide to creating affiliate profitable price comparison site on WooCommerce</a></li>
                <li><a href="https://wpsoul.com/smart-autoblog-affiliate-websites-rehub-theme-plugins/" target="_blank">Making smart autoblog on theme and plugins</a></li>
                <li><a href="https://wpsoul.com/how-to-make-cashback-site-on-wordpress-and-rehub-theme/" target="_blank">How to create cashback site on WordPress</a></li>
                <li><a href="https://wpsoul.com/guide-creating-profitable/" target="_blank">Step by step guide for affiliate websites based on posts</a></li>
                <li><a href="https://wpsoul.com/how-optimize-speed-of-wordpress/" target="_blank">How to optimize speed of site</a></li>
                <li><a href="https://wpsoul.com/optimize-seo-wordpress/" target="_blank">How to make the best SEO optimization on site</a></li>
                <li><a href="https://wpsoul.com/creating-social-business-advanced-membership-site-buddypress-and-s2member/" target="_blank">Set extended Membership on your site</a></li>
                <li><a href="https://wpsoul.com/directory-review-classified-on-woocommerce/" target="_blank">Creating Directory. Classified, Review site with Rehub</a></li>
                <li><a href="https://wpsoul.com/how-to-create-multi-vendor-shop-on-wordpress/" target="_blank">Creating Multivendor site with Rehub</a></li>
                <li><a href="https://wpsoul.com/amp-wordpress-setup/" target="_blank">How to use AMP</a>. How to <a href="https://wpsoul.com/create-mobile-app-wordpress/" target="_blank">create mobile App</a></li>
                <li><a href="https://wpsoul.com/how-to-know-what-error-do-you-have-on-wordpress-site/" target="_blank">How to know what error do you have on WordPress site</a></li>
                <li><a href="https://wpsoul.com/how-to-make-user-driven-community-with-rehub-theme/" target="_blank">How to make User Driven Community</a></li>
                </ul>
        </div>
    	<div class="feature-section col three-col">
            <div class="col">
                <h4><span class="dashicons dashicons-format-video"></span><?php echo esc_html__( "Video Tutorials", "rehub-theme" ); ?></h4>
                <p><?php echo esc_html__( "We have a growing library of video tutorials to help teach you the different aspects of using ReHub Theme.", "rehub-theme" ); ?></p>
                <?php printf( '<a href="%s" class="button button-large button-primary rehub-large-button" target="_blank">%s</a>', 'https://www.youtube.com/c/WPSoul/videos', esc_html__( "Watch Videos", "rehub-theme" ) ); ?>
            </div>
            <div class="col">
				<h4><span class="dashicons dashicons-book"></span><?php echo esc_html__( "Documentation", "rehub-theme" ); ?></h4>
				<p><?php echo esc_html__( "This is the place where you should start to learn enhanced functions of ReHub Theme.", "rehub-theme" ); ?></p>
                <?php printf( '<a href="%s" class="button button-large button-primary rehub-large-button" target="_blank">%s</a>', 'http://rehubdocs.wpsoul.com/docs/rehub-theme/', esc_html__( "Documentation", "rehub-theme" ) ); ?>
            </div>
        	<div class="col last-feature">
				<h4><span class="dashicons dashicons-portfolio"></span><?php echo esc_html__( "Advanced tutorials", "rehub-theme" ); ?></h4>
				<p><?php echo esc_html__( "Our knowledgebase contains additional content that is not inside of our documentation. This information is more specific and teach advanced technics of ReHub Theme.", "rehub-theme" ); ?></p>
                <?php printf( '<a href="%s" class="button button-large button-primary rehub-large-button" target="_blank">%s</a>', $theme_url, esc_html__( "Check tutorials", "rehub-theme" ) ); ?>
            </div>
            <?php if (!defined('ENVATO_HOSTED_SITE')):?>
                <div class="col">
                    <h4><span class="dashicons dashicons-sos"></span><?php echo esc_html__( "Submit A Ticket", "rehub-theme" ); ?></h4>
                    <p><?php echo esc_html__( "We offer excellent support through Themeforest comment system. Write to us from your account on Themeforest.", "rehub-theme" ); ?></p>
                    <?php printf( '<a href="%s" class="button button-large button-primary rehub-large-button" target="_blank">%s</a>', 'http://themeforest.net/item/rehub-directory-shop-coupon-affiliate-theme/7646339/support/contact/', esc_html__( "Submit A Question", "rehub-theme" ) ); ?>
                </div>            
    			<div class="col">
    				<h4><span class="dashicons dashicons-groups"></span><?php echo esc_html__( "Write us a letter", "rehub-theme" ); ?></h4>
    				<p><?php echo esc_html__( "Want to send private information or access to your site? Use this link to contact us with email", "rehub-theme" ); ?></p>
                    <?php printf( '<a href="%s" class="button button-large button-primary rehub-large-button" target="_blank">%s</a>', 'http://themeforest.net/user/sizam#contact', esc_html__( "Write to email", "rehub-theme" ) ); ?>
                </div>            
                <!--<div class="col last-feature">
                	<h4><span class="dashicons dashicons-carrot"></span><?php echo esc_html__( "Give us a new Idea", "rehub-theme" ); ?></h4>
    				<p><?php echo esc_html__( "Want to give idea for new updates. Use our site", "rehub-theme" ); ?></p>
                    <?php printf( '<a href="%s" class="button button-large button-primary rehub-large-button" target="_blank">%s</a>', 'https://wpsoul.com/questions/', esc_html__( "Give an Idea", "rehub-theme" ) ); ?>
                </div> -->
            <?php endif ;?>

        </div>
    </div>
</div>
