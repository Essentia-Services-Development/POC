<?php
/**
 * The Template for displaying store sidebar.
 *
 * @package WCfM Markeplace Views Store Sidebar
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $WCFM, $WCFMmp;
$store_sidebar_pos = isset( $WCFMmp->wcfmmp_marketplace_options['store_sidebar_pos'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_sidebar_pos'] : 'left';
if( $store_sidebar_pos == 'left' ) { 
	$sideclass = 'floatleft'; 
}else{
	$sideclass = 'floatright'; 
} 
if( !$WCFMmp->wcfmmp_vendor->is_store_sidebar() ) return;
$widget_args = apply_filters( 'wcfmmp_store_sidebar_args', array(
	'before_widget' => '<div class="rh-cartbox widget"><div>',
	'after_widget'  => '</div></div>',
	'before_title'  => '<div class="widget-inner-title rehub-main-font">',
	'after_title'   => '</div>',
	) );
?>

<aside class="rh-mini-sidebar user-profile-div tabletsblockdisplay <?php echo ''.$sideclass;?>">

  <?php do_action( 'wcfmmp_store_before_sidabar', $store_user->get_id() ); ?>
  
  <?php if( !is_active_sidebar( 'sidebar-wcfmmp-store' ) ) { ?>
  	
  		<?php the_widget( 'WCFMmp_Store_Product_Search', array( 'title' => esc_html__( 'Search', 'rehub-theme' ) ), $widget_args ); ?>
  	
  		<?php the_widget( 'WCFMmp_Store_Category', array( 'title' => esc_html__( 'Categories', 'rehub-theme' ) ), $widget_args ); ?>
		
		<?php the_widget( 'WCFMmp_Store_Location', array( 'title' => esc_html__( 'Store Location', 'rehub-theme' ) ), $widget_args ); ?>
		
	<?php } else { ?>
		<?php dynamic_sidebar( 'sidebar-wcfmmp-store' ); ?>
	<?php } ?>
	
	<?php do_action( 'wcfmmp_store_after_sidebar', $store_user->get_id() ); ?>
	
</aside><!-- .left_sidebar -->