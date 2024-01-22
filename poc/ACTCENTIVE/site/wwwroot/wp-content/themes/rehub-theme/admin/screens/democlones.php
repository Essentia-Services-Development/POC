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
$demos = '';
if($registeredlicense){
	$demos = array(
		"demo10"=> array(
			"link"=>"http://recompare.wpsoul.net/",
			"name"=>"ReCompare"
		),
		"demo11"=> array(
			"link"=>"https://recart.wpsoul.com/",
			"name"=>"ReCart"
		),
		"demo14"=> array(
			"link"=>"https://redeal.lookmetrics.co/",
			"name"=>"ReDeal"
		),
		"demo12"=> array(
			"link"=>"https://retour.wpsoul.com/",
			"name"=>"ReTour"
		),
		"demo7"=> array(
			"link"=>"http://rewise.wpsoul.net/",
			"name"=>"ReWise"
		),	
		"demo8"=> array(
			"link"=>"http://redokan.wpsoul.net/",
			"name"=>"ReDokanNew"
		),
		"demo9"=> array(
			"link"=>"http://remarket.wpsoul.com/",
			"name"=>"ReMarket"
		),
		"demo2"=> array(
			"link"=>"http://repick.wpsoul.net/",
			"name"=>"RePick"
		),	
		"demo3"=> array(
			"link"=>"http://rething.wpsoul.net/",
			"name"=>"ReThing"
		),
		"demo4"=> array(
			"link"=>"http://recash.wpsoul.net/",
			"name"=>"ReCash"
		),
		"demo5"=> array(
			"link"=>"http://redirect.wpsoul.net/",
			"name"=>"ReDirect"
		),
		"demo6"=> array(
			"link"=>"http://revendor.wpsoul.net/",
			"name"=>"ReVendor"
		),	
		"demo1"=> array(
			"link"=>"https://remag.wpsoul.net/",
			"name"=>"ReMag"
		),	
		"demo13"=> array(
			"link"=>"https://redokan.wpsoul.com/",
			"name"=>"ReDokanNew"
		),
		"demo15"=> array(
			"link"=>"https://refashion.wpsoul.net/",
			"name"=>"ReFashion"
		),
		"demo16"=> array(
			"link"=>"https://reviewit.wpsoul.net/",
			"name"=>"ReViewit"
		),
		"demo19"=> array(
			"link"=>"https://reviewit.wpsoul.net/",
			"name"=>"ReLearn"
		),
		"demo20"=> array(
			"link"=>"https://remart.lookmetrics.co/",
			"name"=>"ReMart"
		),						
	);
}
?>
<div class="wrap about-wrap rehub-wrap">
	<?php add_thickbox(); ?>
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
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=rehub' ),  esc_html__( "Registration", "rehub-theme" ) );
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=rehub-support' ), esc_html__( "Support and tips", "rehub-theme" ) );
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=rehub-plugins' ), esc_html__( "Plugins", "rehub-theme" ) );
		printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=import_demo' ), esc_html__( "Demo Import", "rehub-theme" ) );		
		printf( '<a href="#" class="nav-tab nav-tab-active">%s</a>', esc_html__( "Alternative Import", "rehub-theme" ) );
		?>
	</h2>
	<div class="rehub-important-notice spoil-re">
	<?php if( $registeredlicense == true ) :?>
		<p class="about-description">
			<strong style="color:red; font-size: 16px">This is alternative way, use it only if you have problem with regular <?php printf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=import_demo' ), esc_html__( "Demo Import", "rehub-theme" ) );?></strong><br /><br />
 
			Make next steps to use alternative demo import:
			<br /><br />
			<strong style="color:green; font-size: 16px">1</strong> Download 3 files of demo site: Post content file, Widget File and Theme option file to your computer. To do this, Click on Download Button, then, in popup, Right Click on Download Link and choose "Save Link As".
			<br /><br />
			<strong style="color:green; font-size: 16px">2</strong> Go to <?php printf( '<a href="%s">%s</a>', admin_url( 'import.php' ), 'Tools - Import' );?> and use Wordpress Import. Then, upload first Post content import file. It has name like "rehub-content.xml" or similar. While importing, enable option to import attachments. After import, go to Appearance - Menus. Sometimes, imported menu is not assigned to proper area. Make sure that your Main(Primary) menu is assigned to Primary menu Location.
			<br /><br />
			<strong style="color:green; font-size: 16px">3</strong> Open imported Theme option file in any text editor. It has name like "rehub-theme.json", copy content of file. Then, go to <?php printf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=vpt_option#_menu_util' ), 'Theme option - Utility' );?> and place content in Import section and import options.
			<br /><br />
			<strong style="color:green; font-size: 16px">4</strong> Go to <?php printf( '<a href="%s">%s</a>', admin_url( 'plugin-install.php' ), 'Plugins - add new' );?> and install plugin - Widget Importer & Exporter. You can insert this name in search form. Activate plugin, then go to <?php printf( '<a href="%s">%s</a>', admin_url( 'tools.php?page=widget-importer-exporter' ), 'Widget import page' );?> and import last file, it has name like "rehub-widgets.wie"
			<br /><br />			


		</p>
	<?php else :?>
		<p class="about-description">
			<?php echo esc_html__( "To get access to demo stacks, you first must register your purchase.
See the", "rehub-theme" ); ?><br /> <?php printf( '<a href="%s">%1s</a> %2s', admin_url( 'admin.php?page=rehub' ), esc_html__( "Product Registration", "rehub-theme" ), esc_html__("tab for instructions on how to complete registration.", "rehub-theme" ) ); ?></p>
		</p>
	<?php endif ;?>	
	 
		
	</div>
	<div class="rehub-demo-themes">
		<div class="feature-section theme-browser rendered">
			<?php
			if (!empty ($demos)) {
				// Loop through all demos
				foreach ( $demos as $demo => $demo_details ) { ?>
					<div class="theme">
						<div class="theme-screenshot">
							<img src="<?php echo REHUB_ADMIN_DIR . 'screens/images/' . $demo . '_preview.jpg'; ?>" />
						</div>
						<h3 class="theme-name"><?php echo esc_html($demo_details['name']); ?></h3>
						<div id="linkdemo-<?php echo esc_attr($demo); ?>" style="display:none;">
	     					<p>	
	     					<?php $themename = $demo_details['name'];?>
	     					<?php if($demo_details['name'] == 'ReDokanNew' || $demo_details['name'] == 'ReMarket' || $demo_details['name'] == 'ReVendor'):?>
	     						Post Content file - <a href="<?php echo esc_url($lb_verify_res["data"]["themes"]["ReDokanNew"]["content"]) ?>" download target="_blank">Download</a>
	     						<br />     					
	     					<?php else:?>
	     						Post Content file - <a href="<?php echo esc_url($lb_verify_res["data"]["themes"][$themename]["content"])?>" download target="_blank">Download</a>
	     						<br />
	     					<?php endif;?>
	     						Widget File - <a href="<?php echo esc_url($lb_verify_res["data"]["themes"][$themename]["widgets"])?>" download target="_blank">Download</a>
	     						<br />	     					
	     						Theme option file - <a href="<?php echo get_template_directory_uri() .'/admin/demo/'.strtolower($demo_details['name']).'-theme.json'?>" download target="_blank">Download</a>
	     						<br />

	     					</p>
						</div>
						<div class="theme-actions">
							<?php if( $registeredlicense == true ) { ?>
							<?php printf( '<a class="button button-primary button-install-demo thickbox" href="#TB_inline?width=600&height=100&inlineId=linkdemo-%s">%s</a>', $demo, esc_html__( "Download", "rehub-theme" ) ); ?>
							<?php printf( '<a class="button button-primary" target="_blank" href="%1s">%2s</a>', $demo_details["link"], esc_html__( "Preview", "rehub-theme" ) ); ?>
							<?php } else { ?>
							<?php printf( '<a class="button button-primary disabled button-install-demo">%s</a>', esc_html__( "Download", "rehub-theme" ) ); ?>
							<?php printf( '<a class="button button-primary" target="_blank" href="%1s">%2s</a>', $demo_details["link"], esc_html__( "Preview", "rehub-theme" ) ); ?>
							<?php } ?>
						</div>
						<?php if( isset( $demo_details['new'] ) && $demo_details['new'] == true ): ?>
						<div class="plugin-required">
							<?php esc_html_e( 'New', 'rehub-theme' ); ?>
						</div>
						<?php endif; ?>
					</div>
				<?php } 
				echo '<div class="theme"><div class="theme-screenshot"><img src="'.REHUB_ADMIN_DIR . 'screens/images/soon.jpg" /></div></div>';
			}
			?>
		</div>
	</div>
</div>
