<?php
/**
 * The Template for displaying store.
 *
 * @package WCfM Markeplace Views Store
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$store_user   = wcfmmp_get_store( get_query_var( 'author' ) );
$store_info   = $store_user->get_shop_info();

$store_sidebar_pos = isset( $WCFMmp->wcfmmp_marketplace_options['store_sidebar_pos'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_sidebar_pos'] : 'left';

$wcfm_store_wrapper_class = apply_filters( 'wcfm_store_wrapper_class', '' );

get_header( 'shop' );
?>

<?php if( $WCFMmp->wcfmmp_vendor->is_store_sidebar() && ($store_sidebar_pos == 'left' ) ): ?>
	<?php $contentclass = 'floatright'; ?>
<?php else:?>
	<?php $contentclass = 'floatleft'; ?>
<?php endif; ?>
<?php //do_action( 'woocommerce_before_main_content' ); ?>
<?php echo '<div id="primary" class="content-area"><main id="main" class="site-main" role="main">'; ?>
<?php do_action( 'wcfmmp_before_store' ); ?>

<div id="wcfmmp-store" class="wcfmmp-single-store-holder <?php echo esc_attr($wcfm_store_wrapper_class); ?>">
	<div id="wcfmmp-store-content" class="wcfmmp-store-page-wrap woocommerce" role="main">	
		<div class="wcvendor_store_wrap_bg position-relative">
			<?php $WCFMmp->template->get_template( 'store/wcfmmp-view-store-banner.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) ); ?>			
			<div id="wcvendor_profile_wrap">
				<?php 
				if( apply_filters( 'wcfmmp_is_allow_legacy_header', false ) ) {
					$WCFMmp->template->get_template( 'store/legacy/wcfmmp-view-store-header.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'store_tab'=> $store_tab ) );
				} else {
					$WCFMmp->template->get_template( 'store/wcfmmp-view-store-header.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'store_tab'=> $store_tab ) );
				}
				?>
				<?php do_action( 'wcfmmp_after_store_header', $store_user->data, $store_info ); ?>
			</div>
			<?php if( !empty( $store_info['social'] ) && $store_user->has_social() && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_social' ) ) { ?>
				<div class="social_area" id="wcvendor_social_btns">
				<?php $WCFMmp->template->get_template( 'store/wcfmmp-view-store-social.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) ); ?>
				</div>
		<?php } ?>
		</div>          
	    <div class="body_area rh-container wcvcontent">
	    	<div class="rh-content-wrap clearfix">
	      	<?php 
				if( !apply_filters( 'wcfmmp_is_allow_mobile_sidebar_at_bottom', true ) ) {
					$WCFMmp->template->get_template( 'store/wcfmmp-view-store-sidebar.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
				}
				?>
				<div class="<?php if( $WCFMmp->wcfmmp_vendor->is_store_sidebar() ) echo 'rh-mini-sidebar-content-area'; ?> page clearfix tabletsblockdisplay <?php echo ''.$contentclass;?>">
					<div id="tabsWithStyle" class="tab_area">
						<?php 
							switch( $store_tab ) {
								case 'about':
									$WCFMmp->template->get_template( 'store/wcfmmp-view-store-about.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
									break;
									
								case 'policies':
									$WCFMmp->template->get_template( 'store/wcfmmp-view-store-policies.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
									break;
									
								case 'reviews':
									$WCFMmp->template->get_template( 'store/wcfmmp-view-store-reviews.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
									break;
									
								case 'followers':
									$WCFMmp->template->get_template( 'store/wcfmmp-view-store-followers.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
									break;
									
								case 'followings':
									$WCFMmp->template->get_template( 'store/wcfmmp-view-store-followings.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
									break;
									
							  case 'articles':
									$WCFMmp->template->get_template( 'store/wcfmmp-view-store-articles.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
									break;
									
								default:
									$WCFMmp->template->get_template( apply_filters( 'wcfmp_store_default_template', 'store/wcfmmp-view-store-products.php', $store_tab ), array( 'store_user' => $store_user, 'store_info' => $store_info ), '', apply_filters( 'wcfmp_store_default_template_path', '', $store_tab ) );
									break;
							}	
						?>
						
					</div><!-- .tab_area -->
				</div><!-- .right_side -->
				
				<?php 
				if( apply_filters( 'wcfmmp_is_allow_mobile_sidebar_at_bottom', true ) ) {
					$WCFMmp->template->get_template( 'store/wcfmmp-view-store-sidebar.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
				}
				?>
				 
				<div class="spacer"></div>
	    	</div>
	    </div><!-- .body_area -->
    	<div class="wcfm-clearfix"></div>
	</div><!-- .wcfmmp-store-page-wrap -->
	<div class="wcfm-clearfix"></div>
</div><!-- .wcfmmp-single-store-holder -->

<div class="wcfm-clearfix"></div>

<?php do_action( 'wcfmmp_after_store' ); ?>
<?php //do_action( 'woocommerce_after_main_content' ); ?>
<?php echo '</main></div>'; ?>

<?php get_footer( 'shop' ); ?>