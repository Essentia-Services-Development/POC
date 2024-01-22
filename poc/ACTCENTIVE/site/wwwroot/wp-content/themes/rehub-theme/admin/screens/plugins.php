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
$plugins = TGM_Plugin_Activation::$instance->plugins;
$installed_plugins = get_plugins();
$theme_url = 'https://wpsoul.com/';
?>
<div class="wrap about-wrap rehub-wrap">
	<h1><?php esc_html_e( "Welcome to ReHub Theme!", "rehub-theme" ); ?></h1>
    <?php if( $registeredlicense == true ) :?>
    <div class="about-text">
        <?php esc_html_e( "Theme is registered on your site! ", "rehub-theme" ); ?>
        <?php if ($tf_support_date):?>
	        <?php esc_html_e( "You have support until: ", "rehub-theme" ); ?><?php $date = date_create($tf_support_date); echo date_format($date, 'Y-m-d');?>
	        <a href="http://themeforest.net/item/rehub-directory-shop-coupon-affiliate-theme/7646339" target="_blank"><?php esc_html_e( "(extend support)", "rehub-theme" ); ?></a><br />
        <?php endif;?> 
        <?php if ( ! function_exists( 'envato_market' ) ) :?>
            <?php esc_html_e( "If you need automatic theme updates, install Envato Market plugin from ", "rehub-theme" ); ?>
            <a href="<?php echo admin_url( 'admin.php?page=rehub-plugins' );?>"><?php esc_html_e( "Plugins Tab", "rehub-theme" ); ?></a>
        <?php endif;?>  
    </div>
    <?php else :?>
    <div class="about-text"><?php esc_html_e( "ReHub Theme is now installed and ready to use! Please register your purchase to get support, automatic theme updates, demo stacks, bonuses.", "rehub-theme" ); ?></div> 
    <?php endif;?>
	<div class="rehub-logo"><span class="rehub-version"><?php  esc_html_e( "Version", "rehub-theme"); ?> <?php echo esc_html($rehub_version); ?></span></div>
	<h2 class="nav-tab-wrapper">
		<?php
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Registration", "rehub-theme" ) );
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=rehub-support' ), esc_html__("Support and tips", "rehub-theme" ) );
		printf( '<a href="#" class="nav-tab nav-tab-active">%s</a>', esc_html__("Plugins", "rehub-theme" ) );
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=import_demo' ), esc_html__("Demo Import", "rehub-theme" ) );
		?>
	</h2>
	 <div class="rehub-important-notice">
		<p class="about-description"><?php esc_html_e( "Rehub Theme has some bundled paid plugins which you can install from this page.", "rehub-theme");?>
		<br> 
		<a href='http://rehubdocs.wpsoul.com/docs/rehub-theme/theme-install-update-translation/updating-theme-and-bundled-plugins/' target='_blank'><?php esc_html_e( "Check how to update them.", "rehub-theme");?></a></p>
	</div>
	
	<div class="rehub-demo-themes rehub-install-plugins">
		<div class="feature-section theme-browser rendered">
			<?php
			foreach( $plugins as $plugin ):
				$class = '';
				$plugin_status = '';
				$file_path = $plugin['file_path'];
				$plugin_action = $this->plugin_link( $plugin );

				if( rh_check_plugin_active( $file_path ) ) {
					$plugin_status = 'active';
					$class = 'active';
				}
			?>
			<div class="theme <?php echo esc_attr($class); ?>">
				<div class="theme-screenshot">
					<img src="<?php echo esc_url($plugin['image_url']); ?>" alt="theme" />
					<div class="plugin-info">
					<?php echo esc_html($plugin['description']); ?><br />
					<?php if( isset( $installed_plugins[$plugin['file_path']] ) ): ?>
						<?php printf('%s %s | <a href="%s" target="_blank">%s</a>', esc_html__('Version:', 'rehub-theme' ), $installed_plugins[$plugin['file_path']]['Version'], $installed_plugins[$plugin['file_path']]['AuthorURI'], $installed_plugins[$plugin['file_path']]['Author'] ); ?>
					<?php elseif ( $plugin['source_type'] == 'bundled' ) : ?>
						<?php printf('%s %s', esc_html__('Available Version:', 'rehub-theme' ), $plugin['version'] ); ?>					
					<?php endif; ?>
					</div>
				</div>
				<h3 class="theme-name">
					<?php
					if( $plugin_status == 'active' ) {
						printf( '<span>%s</span> ', esc_html__('Active:', 'rehub-theme' ) );
					}
					echo esc_html($plugin['name']);
					?>
				</h3>
				<div class="theme-actions">
					<?php foreach( $plugin_action as $action ) { echo wp_kses_post($action); } ?>
				</div>
				<?php if( isset( $plugin_action['update'] ) && $plugin_action['update'] ): ?>
				<div class="theme-update">Update Available: Version <?php echo esc_html($plugin['version']); ?></div>
				<?php endif; ?>
				<?php if( $plugin['required'] ): ?>
				<div class="plugin-required">
					<?php esc_html_e( 'Required', 'rehub-theme' ); ?>
				</div>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
		<h2><?php esc_html_e( "Bonus plugins", "rehub-theme");?></h2>
		<?php if( $registeredlicense == true && empty($lb_verify_res['data']['plugins']) ) :?>
			<p style="color:red;font-size:180%" class="notofficialtheme">You can't download bonus plugins, because you are using nulled or not official theme version. Please, purchase theme on <a href="https://themeforest.net/item/rehub-directory-multi-vendor-shop-coupon-affiliate-theme/7646339">Themeforest</a>, otherwise, your site can be blocked.</p>
		<?php endif;?>
		<div class="feature-section theme-browser rendered">				
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/apf.jpg" alt="theme">
					<div class="plugin-info">Woocommerce Product Filters</div>
				</div>
				<h3 class="theme-name">Advanced Product Filters</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['woocommerce-product-filter']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/salenotification.jpg" alt="theme">
					<div class="plugin-info">For Live Woo Notifications</div>
				</div>
				<h3 class="theme-name">Woocommerce Sales Notifications</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['salenotification']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>				
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/revslider.jpg" alt="theme">
					<div class="plugin-info">For super sliders</div>
				</div>
				<h3 class="theme-name">Revolution slider</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['slider-revolution']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>					
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/rhfrontend.png" alt="theme">
					<div class="plugin-info">For frontend posting</div>
				</div>
				<h3 class="theme-name">RH Frontend Posting</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['rh-frontend']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/popup.png" alt="theme">
					<div class="plugin-info">Advanced Popups</div>
				</div>
				<h3 class="theme-name">Advanced Layered Popups</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['layered-popups']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>			
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/rhchart.jpg" alt="theme">
					<div class="plugin-info">RH Chart - dynamic chart builder</div>
				</div>
				<h3 class="theme-name">Gutenberg block for dynamic charts</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['rh-chart']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>	
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/rhlinkhider.png" alt="theme">
					<div class="plugin-info">For Links managing</div>
				</div>
				<h3 class="theme-name">RH Link Offer Cloaking</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['rh-cloak-affiliate-links']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>	
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/rhwootools.png" alt="theme">
					<div class="plugin-info">Set of useful Woocommerce Tools</div>
				</div>
				<h3 class="theme-name">RH Woo Tools</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['rh-woo-tools']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>												
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/importwp.jpg" alt="theme">
					<div class="plugin-info">Plugin for mass import</div>
				</div>
				<h3 class="theme-name">Import WP</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['importwp-pro']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>	
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/importwprh.jpg" alt="theme">
					<div class="plugin-info">Rehub addon for mass import</div>
				</div>
				<h3 class="theme-name">Rehub Addon for Import WP</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['importwp-rhaddon']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/importwpwoo.jpg" alt="theme">
					<div class="plugin-info">Woo addon for mass import</div>
				</div>
				<h3 class="theme-name">Woo Addon for Import WP</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['importwp-woocommerce']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri() . '/admin/screens/images/vcomposer.png';?>" alt="theme">
					<div class="plugin-info">Enhanced layout builder</div>
				</div>
				<h3 class="theme-name">WP Bakery Composer</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['js_composer']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>
			<div class="theme">
				<div class="theme-screenshot">
					<img src="<?php echo get_template_directory_uri()?>/admin/screens/images/rhfakeactivity.png" alt="theme">
					<div class="plugin-info">Custom Fields Bulk update</div>
				</div>
				<h3 class="theme-name">RH Fake Activity</h3>
				<div class="theme-actions">
					<?php if( $registeredlicense == true ) :?>						
						<a href="<?php echo esc_url($lb_verify_res['data']['plugins']['rh-fake-acivity']);?>" class="button button-primary" title="Get link">Download</a>
					<?php else :?>
						<?php printf( '<a href="%s" class="button button-primary">%s</a>', admin_url( 'admin.php?page=rehub' ), esc_html__("Register theme to get link", "rehub-theme" ) ); ?>
					<?php endif;?>		
				</div>
			</div>											
		</div>
	</div>

	<div class="rehub-thanks">
		<p class="description"><?php esc_html_e( "Thank you for choosing ReHub Theme. We are honored and are fully dedicated to making your experience perfect.", "rehub-theme" ); ?></p>
	</div>
</div>
<div class="wpsm-clearfix" style="clear: both;"></div>