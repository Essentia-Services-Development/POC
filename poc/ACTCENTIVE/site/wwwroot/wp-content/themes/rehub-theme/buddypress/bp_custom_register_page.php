<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!DOCTYPE html>
<!--[if IE 8]>    <html class="ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9]>    <html class="ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gt IE 9)|!(IE)] <?php language_attributes(); ?>><![endif]-->
<html <?php language_attributes(); ?>>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width" />
<!-- feeds & pingback -->
  <link rel="profile" href="http://gmpg.org/xfn/11" />
  <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />  
<?php wp_head(); ?>
<?php $islogin = (!empty($_GET['type'])) ? esc_html($_GET['type']) : '';?>
<?php $membertype = (!empty($_GET['membertype'])) ? esc_html($_GET['membertype']) : '';?>
<?php $membertypelink = ($membertype) ? '&membertype='.$membertype : '';?>
<?php $memberpx = ($islogin == 'login') ? '500px' : '900px';?>
<?php $addstyles = '.buddypress-page.main-side.full_width{padding: 30px 35px 20px 35px; background: #fff;}.rh-container{max-width:'.$memberpx.'}';
if (rehub_option('rehub_header_color_background') !=''){
    $addstyles .= 'body{background: none '.rehub_option("rehub_header_color_background").' !important}.bp-text-bottom-r{color:#fff}';
}
else{
    $addstyles .= 'body{background: none white !important}.buddypress-page.main-side.full_width{box-shadow: 0 0 50px #e3e3e3;}';
}
wp_register_style( 'rhheader-inline-style', false );
wp_enqueue_style( 'rhheader-inline-style' );
wp_add_inline_style('rhheader-inline-style', $addstyles);
wp_enqueue_script('rehubuserlogin');
?> 
</head>
<body <?php body_class('page-template-template-systempages'); ?>>
<div class="rh-outer-wrap register_wrap_type<?php echo esc_html($membertype);?>" id="rh_user_create_bp"> 
    <div class="mt30 mb20 clearfix"></div>
    <?php if(rehub_option('rehub_logo')) : ?>
    <div class="logo text-center mt30 mb35">
        <a href="<?php echo esc_url(home_url()); ?>" class="logo_image"><img src="<?php echo rehub_option('rehub_logo'); ?>" alt="<?php bloginfo( 'name' ); ?>" height="<?php echo rehub_option( 'rehub_logo_retina_height' ); ?>" width="<?php echo rehub_option( 'rehub_logo_retina_width' ); ?>" /></a>      
    </div>
    <?php endif; ?>
    <!-- CONTENT -->
    <div class="rh-container clearfix mt30 mb30"> 
        <div class="buddypress-page main-side clearfix full_width">            
            <article class="post" id="page-<?php the_ID(); ?>"> 
			    <?php if($islogin == 'login'):?>
			    	<div id="buddypress">
					 	<div class="rehub-login-popup re-user-popup-wrap">
							<?php if (rehub_option('custom_msg_popup') !='') {
								echo '<div class="mb15 mt15 rh_custom_msg_popup">';
								echo do_shortcode(rehub_option('custom_msg_popup'));
								echo '</div>';
								} ?>					 		
							<form id="rehub_login_form_modal" action="<?php echo home_url( '/' ); ?>" method="post">
								<?php do_action( 'wordpress_social_login' ); ?>
								<div class="re-form-group mb20">
									<label><?php esc_html_e('Username', 'rehub-theme') ?></label>
									<input class="re-form-input required" name="rehub_user_login" type="text"/>
								</div>
								<div class="re-form-group mb20">
									<label for="rehub_user_pass"><?php esc_html_e('Password', 'rehub-theme')?></label>
									<input class="re-form-input required" name="rehub_user_pass" id="rehub_user_pass" type="password" autocomplete="on" />
									<span class="alignright"><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="color_link bp_resset_link_login"><?php esc_html_e('Lost Password?', 'rehub-theme');  ?></a></span>							
								</div>
								<div class="re-form-group mb20">
									<label for="rehub_remember"><input name="rehub_remember" id="rehub_remember" type="checkbox" value="forever" />
									<?php esc_html_e('Remember me', 'rehub-theme'); ?></label>
								</div>
								<div class="re-form-group mb20">
									<input type="hidden" name="action" value="rehub_login_member_popup_function"/>
									<button class="wpsm-button rehub_main_btn" type="submit"><?php esc_html_e('Login', 'rehub-theme'); ?></button>
								</div>
								<?php wp_nonce_field( 'ajax-login-nonce', 'loginsecurity' ); ?>
							</form>
							<div class="rehub-errors"></div>
							<div class="rehub-login-popup-footer"><?php esc_html_e('Don\'t have an account?', 'rehub-theme'); ?> 
								<a href="<?php echo esc_url(bp_get_signup_page()); ?>" class="color_link bp_reg_link_login"><?php esc_html_e('Sign Up', 'rehub-theme'); ?></a>
							</div>
						</div>
			        </div>   
			    <?php else:?>
			        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			            <?php the_content(); ?>
			        <?php endwhile; endif; ?>			    	
			    <?php endif;?>                        
            </article>            
        </div>    
    </div>
    <!-- /CONTENT -->    

<div class="mt15 mb30 text-center rh-container bp-text-bottom-r">
	<?php if($islogin == ''):?>	
		<div class="font120"><?php esc_html_e('Already have an account?', 'rehub-theme'); ?> <a href="<?php echo esc_url(bp_get_signup_page()); ?>?type=login<?php echo ''.$membertypelink;?>" class="color_link bp_log_link_login"><?php esc_html_e('Login', 'rehub-theme'); ?></a>
		</div>
		<div class="rh-line mt20 mb20"></div>
	<?php endif;?>
	<a href="<?php echo esc_url(home_url()); ?>" class="bp_return_home"><?php esc_html_e('Return to Home', 'rehub-theme');?></a>
</div>

</div>
<?php wp_footer(); ?>
</body>
</html>    