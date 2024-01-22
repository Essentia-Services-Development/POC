<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
$rehub_theme = wp_get_theme();
if($rehub_theme->parent_theme) {
    $template_dir =  basename(get_template_directory());
    $rehub_theme = wp_get_theme($template_dir);
}
$rehub_version = $rehub_theme->get( 'Version' );
$tf_support_date = '';
?>
<?php 
	$rehub_options = get_option( 'Rehub_Key' );
	$tf_username = isset( $rehub_options[ 'tf_username' ] ) ? $rehub_options[ 'tf_username' ] : '';
	$tf_purchase_code = isset( $rehub_options[ 'tf_purchase_code' ] ) ? $rehub_options[ 'tf_purchase_code' ] : '';

	

	require_once ( 'lhelper.php');
	// Create a new LicenseBoxAPI helper class.
	$lbapi = new LicenseBoxAPI();

	// Performs background license check, pass TRUE as 1st parameter to perform periodic verifications only.
	$registeredlicense = false;
	if($tf_username && $tf_purchase_code){
		$lb_verify_res = $lbapi->verify_license(true, sanitize_text_field($tf_purchase_code), sanitize_text_field($tf_username));
		if(!empty($lb_verify_res['status'])){
			$registeredlicense = true;
		}
	}
	
	$lb_deactivate_res = $activationmessage = $deactivationmessage= $lb_activate_res = null;

	if(!empty($_POST['client_name'])&&!empty($_POST['license_code'])){
		check_admin_referer('lb_update_license', 'lb_update_license_sec');
		$licode = sanitize_text_field(trim($_POST['license_code']));
		$liuser = sanitize_text_field(trim($_POST['client_name']));
		$lb_verify_res = $lbapi->verify_license(false, $licode, $liuser);

		if(empty($lb_verify_res['status'])){
			$lb_activate_res = $lbapi->activate_license($licode, $liuser, false);
		}
		
		if(!empty($lb_activate_res['status']) || !empty($lb_verify_res['status']) ){
			$rehub_options = array('tf_username'=>$liuser, 'tf_purchase_code' => $licode);
			update_option( 'Rehub_Key', $rehub_options );
			$tf_username = $liuser;
			$tf_purchase_code = $licode;
			$registeredlicense = true;
		}else{
			$activationmessage = $lb_activate_res['message'];
			$registeredlicense = false;
		}
	}
	if(!empty($_POST['lb_deactivate'])){
		if(empty($tf_purchase_code)){
			$tf_purchase_code = trim($_POST['deactivate_license_code']);
		}
		if(empty($tf_username)){
			$tf_username = trim($_POST['deactivate_client_name']);
		}
		check_admin_referer('lb_deactivate_license', 'lb_deactivate_license_sec');
		$lb_deactivate_res = $lbapi->deactivate_license(sanitize_text_field($tf_purchase_code), sanitize_text_field($tf_username));
		if(!empty($lb_deactivate_res['status'])){
			delete_option( 'Rehub_Key' );
			$tf_purchase_code = $tf_username = '';
			$registeredlicense = false;
		}else{
			$deactivationmessage = $lb_deactivate_res['message'];
		}
	}
?>
<div class="wrap about-wrap rehub-wrap">
	<h1><?php esc_html_e( "Welcome to ReHub Theme!", "rehub-theme" ); ?></h1>
	<div class="updated registration-notice-1" style="display: none;">
		<p><strong><?php esc_html_e( "Thanks for registering your purchase. You have now access to demo stacks, support and additional bonuses. ", "rehub-theme" ); ?> </strong></p>		
		<?php if ( ! function_exists( 'envato_market' ) ) :?>
			<?php esc_html_e( "If you need automatic theme updates, install Envato Market plugin from ", "rehub-theme" ); ?>
			<a href="<?php echo admin_url( 'admin.php?page=rehub-plugins' );?>"><?php esc_html_e( "Plugins Tab", "rehub-theme" ); ?></a>
		<?php endif;?>
	</div>
	<div class="updated error registration-notice-2" style="display: none;"><p><strong><?php esc_html_e( "Please provide all details for registering your copy of ReHub Theme.", "rehub-theme" ); ?>.</strong></p></div>
	<div class="updated error registration-notice-3" style="display: none;"><p><strong><?php esc_html_e( "Something went wrong. Please try again.", "rehub-theme" ); ?></strong></p></div>
	<div class="updated error registration-notice-4" style="display: none;"><p><strong><?php esc_html_e( "You used not correct name. Please, use your official login name on Envato", "rehub-theme" ); ?></strong></p></div>
	
	<?php if( $registeredlicense == true ) :?>
	<div class="about-text">
		<?php esc_html_e( "Theme is registered on your site! ", "rehub-theme" ); ?>
        <?php if ($tf_support_date):?>
	        <?php esc_html_e( "You have support until: ", "rehub-theme" ); ?><?php $date = date_create($tf_support_date); echo date_format($date, 'Y-m-d');?>
	        <a href="http://themeforest.net/item/rehub-directory-shop-coupon-affiliate-theme/7646339" target="_blank"><?php esc_html_e( "(extend support)", "rehub-theme" ); ?></a><br />
        <?php endif;?>
		<div class="rh-admin-note"><?php esc_html_e( "Please, use search in Online docs before you write to our support. Example: ", "rehub-theme" ); ?> <a href="http://rehubdocs.wpsoul.com/?s=How+to+set+Mega+menu&search_param=all" target="_blank">How to set Mega menu</a> </div>    
		<?php if ( ! function_exists( 'envato_market' ) ) :?>
			<?php esc_html_e( "If you need automatic theme updates, install Envato Market plugin from ", "rehub-theme" ); ?>
			<a href="<?php echo admin_url( 'admin.php?page=rehub-plugins' );?>"><?php esc_html_e( "Plugins Tab", "rehub-theme" ); ?></a>
		<?php endif;?>	
	</div>
	<?php else :?>
	<div class="about-text"><?php esc_html_e( "ReHub Theme is now installed and ready to use! Please register your purchase to get support, automatic theme updates, demo stacks, bonuses.", "rehub-theme" ); ?></div>	
	<?php endif;?>
	
    <div class="rehub-logo"><span class="rehub-version"><?php esc_html_e( "Version", "rehub-theme" ); ?> <?php echo esc_html($rehub_version); ?></span></div>
	<h2 class="nav-tab-wrapper">
    	<?php
		printf( '<a href="#" class="nav-tab nav-tab-active">%s</a>', esc_html__( "Registration", "rehub-theme" ) );
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=rehub-support' ), esc_html__( "Support and tips", "rehub-theme" ) );
        printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=rehub-plugins' ), esc_html__( "Plugins", "rehub-theme" ) );
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=import_demo' ), esc_html__( "Demo import", "rehub-theme" ) );
		?>
	</h2>
    <div class="feature-section">
		<div class="rehub-important-notice registration-form-container">
			<?php
			if( $registeredlicense == true ) {
				echo '<p class="about-description"><span class="dashicons dashicons-yes"></span>'.__("Registration Complete! You have full access to theme data now.", "rehub-theme").'</p>';
			} else {
			?>
			<p class="about-description"><?php esc_html_e( "Enter your credentials below to complete product registration.", "rehub-theme" ); ?></p>
			<div class="rehub-registration-steps">
		    	<div class="feature-section col three-col">
		            <div class="col">
		            	<?php add_thickbox(); ?>
						<h4><?php esc_html_e( "Step 1 - Get your purchase code", "rehub-theme" ); ?></h4>
						<p><?php esc_html_e( 'Please, get your purchase key in download section of theme. View a tutorial&nbsp;', 'rehub-theme' );
						printf( '<a href="%s" class="thickbox" target="_blank">%s</a>.', REHUB_ADMIN_DIR . 'screens/images/api_key.jpg?rel=0&TB_iframe=true&height=792&width=1024',  esc_html__('here', "rehub-theme" ) ); ?></p>
		            </div>
		        	<div class="col">
						<h4><?php esc_html_e( "Step 2 - Purchase Validation", "rehub-theme" ); ?></h4>
						<p><?php esc_html_e( "Enter your ThemeForest username, purchase code into the fields below. This will give you access to automatic theme updates, demo stacks, support, etc.", "rehub-theme" ); ?></p>
		            </div>               	
		            <div class="col last-feature">
						<h4><?php esc_html_e( "Step 3 - Next Steps", "rehub-theme" ); ?></h4>
						<p><?php esc_html_e( "After activating of theme, you can install bundled plugins, get access to demo stacks, tips, support, bonuses", "rehub-theme" ); ?></p>
		            </div>
		        </div>
		    </div>						
			<?php } ?>

			<?php if(!$registeredlicense) : ?>
				<?php if(!empty($deactivationmessage)):?>
					<p style="color: red; clear:both"><?php echo esc_attr( $deactivationmessage );?></p>
				<?php endif;?>
				<?php if(!empty($activationmessage)):?>
					<p style="color: red; clear:both"><?php echo esc_attr( $activationmessage );?></p>
					<?php if(stripos($activationmessage, 'License is already active on maximum') === 0 && $licode && $liuser):?>
						<style>
							.rehub-activate-form{display:none;}
						</style>
						<p>
							<span><strong style="color:red"><?php esc_html_e( "One license can be active only on one site.", "rehub-theme" ); ?></strong><br><?php esc_html_e( "If you want to deactivate license, you need to install theme on NEW site. Then, use your license key as usual. You will see a message about the License limit and form below where you can deactivate the license. License deactivation can be made only from a NEW site to prevent bad scenarios when you lost access to the old site", "rehub-theme");?><br><br><?php esc_html_e( "You can use extension packs to add more sites. After purchase, please, use this", "rehub-theme");?> <a href="https://wpsoul.com/extended-license-request/" target="_blank"><?php esc_html_e('Request form', 'rehub-theme');?></a> <?php esc_html_e( "and provide list of purchased licenses which you want to extend. Please, note that all your purchased licenses must be activated on sites before sending request for bonus pack.", "rehub-theme" ); ?></span><br>
						</p>
						<div style=" margin-top:20px; border-radius:15px; display:flex;">
							<div style="border:1px solid #eee; padding:15px;flex-grow:1;">
								<a href="https://1.envato.market/n1LqK7" target="_blank" style="color:#444; text-decoration:none">
								<div style="float: left;font-size: 40px;margin-bottom: 5px;margin-right: 20px;color: #ff9800;"><i class="rhicon rhi-gift"></i></div>
								<div style="font-size:18px;font-weight:bold">Buy 3 licenses</div>
								<div style="font-size:15px; margin-top:8px;">and Get <strong style="color:#8234e4">10</strong> allowed sites</div>
								</a>
							</div>
							<div style="border:1px solid #eee; padding:15px;flex-grow:1;margin:0 15px">
								<a href="https://1.envato.market/n1LqK7" target="_blank" style="color:#444; text-decoration:none">
								<div style="float: left;font-size: 40px;margin-bottom: 5px;margin-right: 20px;color: #ff9800;"><i class="rhicon rhi-trophy-alt"></i></div>
								<div style="font-size:18px;font-weight:bold">Buy 5 licenses</div>
								<div style="font-size:15px; margin-top:8px">and Get <strong style="color:#8234e4">20</strong> allowed sites</div>
								</a>
							</div>
							<div style="border:1px solid #eee; padding:15px;flex-grow:1">
								<a href="https://1.envato.market/n1LqK7" target="_blank" style="color:#444; text-decoration:none">
								<div style="float: left;font-size: 40px;margin-bottom: 5px;margin-right: 20px;color: #ff9800;"><i class="rhicon rhi-crown"></i></div>
								<div style="font-size:18px;font-weight:bold">Buy 10 licenses</div>
								<div style="font-size:15px; margin-top:8px">and Get <strong style="color:#8234e4">100</strong> allowed sites</div>
								</a>
							</div>					
						</div>
						<p style="clear:both;color:green"><?php esc_html_e("You can use form below to deactivate your license on all of your existed activated sites.", "rehub-theme");?> Check also explanation of <a href="https://wpsoul.com/extended-license-request/" target="_blank">Envato license rules</a> </p>
							<div class="rehub-registration-form">
								<form action="" method="post">
									<?php wp_nonce_field('lb_deactivate_license', 'lb_deactivate_license_sec'); ?>
									
									<input type="text" name="deactivate_client_name" size="50" placeholder="<?php esc_html_e( "YOUR Themeforest Username", "rehub-theme" ); ?>" required value="<?php echo esc_attr($tf_username); ?>">
									<input type="password" name="deactivate_license_code" size="50" placeholder="<?php esc_html_e( "Enter Themeforest Purchase Code", "rehub-theme" ); ?>" required value="<?php echo esc_attr($tf_purchase_code); ?>">
									<input type="hidden" name="lb_deactivate" value="yes">
									<input type="submit" value="<?php esc_html_e( "Deactivate", "rehub-theme" ); ?>" class="button button-large button-primary rehub-large-button">
								</form>
							</div>
					<?php endif;?>
				<?php endif;?>
				<div class="rehub-registration-form rehub-activate-form">
				<form action="" method="post">
					<?php wp_nonce_field('lb_update_license', 'lb_update_license_sec'); ?>
						<input type="text" name="client_name" size="50" placeholder="<?php esc_html_e( "Themeforest Username", "rehub-theme" ); ?>" required value="<?php echo esc_attr($tf_username); ?>">
						<input type="password" name="license_code" size="50" placeholder="<?php esc_html_e( "Enter Themeforest Purchase Code", "rehub-theme" ); ?>" required value="<?php echo esc_attr($tf_purchase_code); ?>">
						<input type="submit" value="<?php esc_html_e( "Submit", "rehub-theme" ); ?>" class="button button-large button-primary rehub-large-button">
				</form>
				</div>
			<?php else: ?>
				<?php if(!empty($deactivationmessage)):?>
					<p style="color: red; clear:both"><?php echo esc_attr( $deactivationmessage );?></p>
				<?php endif;?>
				<div class="clear"></div>
				<p><?php esc_html_e( "Next license is Active - ", "rehub-theme" ); ?><span style="color: green"><?php echo esc_attr($tf_purchase_code);?></span></p>
				<?php /*if(empty($lb_deactivate_res)){ ?>
					<form action="" method="post">
						<?php wp_nonce_field('lb_deactivate_license', 'lb_deactivate_license_sec'); ?>
						<input type="hidden" name="lb_deactivate" value="yes">
						<input type="submit" value="<?php esc_html_e( "Deactivate", "rehub-theme" ); ?>" class="button button-large button-primary rehub-large-button">
					</form>
				<?php }*/ ?>
				<p>
					<span><strong style="color:red"><?php esc_html_e( "One license can be active only on one site.", "rehub-theme" ); ?></strong><br><?php esc_html_e( "You can deactivate your license  from old site when you activate it on new site.", "rehub-theme");?><br><br><?php esc_html_e( "You can use extension packs to add more sites. After purchase, please, use this", "rehub-theme");?> <a href="https://wpsoul.com/extended-license-request/" target="_blank"><?php esc_html_e('Request form', 'rehub-theme');?></a> <?php esc_html_e( "and provide list of purchased licenses which you want to extend. Please, note that all your purchased licenses must be activated on sites before sending request for bonus pack.", "rehub-theme" ); ?></span><br>
				</p>
				<div style=" margin-top:20px; border-radius:15px; display:flex;">
					<div style="border:1px solid #eee; padding:15px;flex-grow:1;">
						<a href="https://1.envato.market/n1LqK7" target="_blank" style="color:#444; text-decoration:none">
						<div style="float: left;font-size: 40px;margin-bottom: 5px;margin-right: 20px;color: #ff9800;"><i class="rhicon rhi-gift"></i></div>
						<div style="font-size:18px;font-weight:bold">Buy 3 licenses</div>
						<div style="font-size:15px; margin-top:8px;">and Get <strong style="color:#8234e4">10</strong> allowed sites</div>
						</a>
					</div>
					<div style="border:1px solid #eee; padding:15px;flex-grow:1;margin:0 15px">
						<a href="https://1.envato.market/n1LqK7" target="_blank" style="color:#444; text-decoration:none">
						<div style="float: left;font-size: 40px;margin-bottom: 5px;margin-right: 20px;color: #ff9800;"><i class="rhicon rhi-trophy-alt"></i></div>
						<div style="font-size:18px;font-weight:bold">Buy 5 licenses</div>
						<div style="font-size:15px; margin-top:8px">and Get <strong style="color:#8234e4">20</strong> allowed sites</div>
						</a>
					</div>
					<div style="border:1px solid #eee; padding:15px;flex-grow:1">
						<a href="https://1.envato.market/n1LqK7" target="_blank" style="color:#444; text-decoration:none">
						<div style="float: left;font-size: 40px;margin-bottom: 5px;margin-right: 20px;color: #ff9800;"><i class="rhicon rhi-crown"></i></div>
						<div style="font-size:18px;font-weight:bold">Buy 10 licenses</div>
						<div style="font-size:15px; margin-top:8px">and Get <strong style="color:#8234e4">100</strong> allowed sites</div>
						</a>
					</div>					
				</div>
			<?php endif ?>
			<div class="clear"></div>

		</div>
	</div>
    <div class="feature-section">
    	<br />
        <strong>Some important tutorials to make your site better:</strong>
        <ul>
        	<li><a href="<?php echo esc_url(wp_nonce_url(admin_url('plugins.php?page=rehub_wizard&rehub_install=1'), '_wpnonce'));?>"><?php echo esc_html__("Run Installation Wizard","rehub-theme") ?></a></li>
			<li><a href="https://wpsoul.com/make-smart-profitable-deal-affiliate-comparison-site-woocommerce/" target="_blank" rel="noopener">Step by step guide to create affiliate profitable price comparison site on woocommerce</a></li>        	
 			<li><a href="https://wpsoul.com/guide-creating-profitable/" target="_blank">Step by step guide for affiliate websites</a></li>        
            <li><a href="https://wpsoul.com/how-optimize-speed-of-wordpress/" target="_blank">How to optimize speed of site</a></li>
            <li><a href="https://wpsoul.com/optimize-seo-wordpress/" target="_blank">How to make the best SEO optimization on site</a></li>
            <li><a href="https://wpsoul.com/creating-social-business-advanced-membership-site-buddypress-and-s2member/" target="_blank">Set extended Membership on your site</a></li>
            <li><a href="https://wpsoul.com/directory-review-classified-on-woocommerce/" target="_blank">Creating Directory site with Rehub</a></li>    
            <li><a href="https://wpsoul.com/how-to-create-multi-vendor-shop-on-wordpress/" target="_blank">Creating Multivendor site with Rehub</a></li> 

        </ul>
    </div>	
</div>
