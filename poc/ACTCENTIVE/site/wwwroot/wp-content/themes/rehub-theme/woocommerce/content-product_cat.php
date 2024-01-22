<?php
/**
 * The template for displaying product category thumbnails within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product_cat.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Extra post classes
$class = array();
$class[] = 'col_item'; 
$class[] = 'rh-cartbox';
$class[] = 'woocatbox';
$class[] = 'mb20';
$class[] = 'pt10';
$class[] = 'pb10';
$class[] = 'pr10';
$class[] = 'pl10';
$class[] = 'rh-hov-bor-line';

?>
<div <?php wc_product_cat_class($class, $category); ?>>
	<?php
	/**
	 * woocommerce_before_subcategory hook.
	 *
	 * @hooked woocommerce_template_loop_category_link_open - 10
	 */
	do_action( 'woocommerce_before_subcategory', $category );
	echo '<div class="rh-flex-center-align">';

	/**
	 * woocommerce_shop_loop_subcategory_title hook.
	 *
	 * @hooked woocommerce_template_loop_category_title - 10
	 */
	echo '<div class="rh-cbox-left floatleft mr20">';
	do_action( 'woocommerce_shop_loop_subcategory_title', $category );
	do_action( 'woocommerce_after_subcategory_title', $category );
	echo '</div>';

	/**
	 * woocommerce_before_subcategory_title hook.
	 *
	 * @hooked woocommerce_subcategory_thumbnail - 10
	 */
	echo '<div class="rh-cbox-right rh-flex-right-align text-center width-80 height-80">';
	do_action( 'woocommerce_before_subcategory_title', $category );
	echo '</div>';	

	/**
	 * woocommerce_after_subcategory hook.
	 *
	 * @hooked woocommerce_template_loop_category_link_close - 10
	 */
	echo '</div>';
	do_action( 'woocommerce_after_subcategory', $category );

	 ?>
</div>