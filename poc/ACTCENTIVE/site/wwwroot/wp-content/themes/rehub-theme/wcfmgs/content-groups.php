<?php
/**
 * WCFMgs plugin templates
 *
 * Main content area
 *
 * @author 		WC Lovers
 * @package 	wcfmgs/templates/content-groups
 * @version   2.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $WCFM, $WCFMgs;

$thumbnail = get_post_meta( get_the_ID(), 'thumbnail', true );
if( !$thumbnail ) {
	$thumbnail =  apply_filters( 'woocommerce_placeholder_img_src', WC()->plugin_url() . '/assets/images/placeholder.png' );
}

$group_ele_class = 'rh-cartbox col_item';
$loop_index = wc_get_loop_prop( 'loop', 0 );
$columns    = 4;

$loop_index ++;
wc_set_loop_prop( 'loop', $loop_index );

if ( 0 === ( $loop_index - 1 ) % $columns || 1 === $columns ) {
	$group_ele_class .= ' first';
} elseif ( 0 === $loop_index % $columns ) {
	$group_ele_class .= ' last';
}
?>

<div <?php post_class( $group_ele_class ); ?>>
  <a href="<?php echo get_the_permalink(); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link text-center">
    <img src="<?php echo esc_url($thumbnail); ?>" alt="Placeholder" width="247" class="woocommerce-placeholder wp-post-image" height="300">
    <div class="woocommerce-loop-product__title rehub-main-font fontbold font100 text-center"><?php echo get_the_title(); ?></div>
  </a>
</div>