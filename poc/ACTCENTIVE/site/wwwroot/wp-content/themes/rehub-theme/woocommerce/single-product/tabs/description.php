<?php
/**
 * Description tab
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/description.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

?>
<div class="clearfix"></div>
<?php the_content(); ?>
<?php
$score = get_post_meta($post->ID, 'rehub_review_overall_score', true);
$shortcodeinserting = get_post_meta($post->ID, 'review_woo_shortcode', true);
$rh_product_layout_style = get_post_meta($post->ID, '_rh_woo_product_layout', true);
if ($rh_product_layout_style == '' || $rh_product_layout_style == 'global') {
	$rh_product_layout_style = rehub_option('product_layout_style');
}
if($score && !$shortcodeinserting && $rh_product_layout_style != 'woo_compact' && $rh_product_layout_style != 'woo_directory'){
	echo wpsm_reviewbox(array('id'=> $post->ID, 'regular'=>1));
}
?>