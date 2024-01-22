<?php
/**
 * The template for displaying product search form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/product-searchform.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_style('rhniceselect');
wp_enqueue_script('rhniceselect');
wp_enqueue_script('rhwoodropcat');
?>
<?php $search_text = (rehub_option('rehub_search_text')) ? rehub_option('rehub_search_text') : esc_html__("Search", "rehub-theme"); ?>
<?php $rehub_ajax_search = rehub_option('rehub_ajax_search'); ?>
<form role="search" method="get" class="search-form product-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="woocommerce-product-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>"><?php esc_html_e( 'Search for:', 'rehub-theme' ); ?></label>
	<input type="text" id="woocommerce-product-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>"  name="s" placeholder="<?php echo esc_attr($search_text); ?>" value="<?php echo get_search_query(); ?>" data-enable_compare="1" data-posttype="product" <?php if ($rehub_ajax_search) { ?>class="re-ajax-search" autocomplete="off" data-catid=""<?php } ?> />
	<input type="hidden" name="post_type" value="product" />
	<?php wc_product_dropdown_categories(array('show_count' => 0, 'parent' => 0, 'show_option_none' => esc_html__("All categories", "rehub-theme"), 'class'=> 'rh_woo_drop_cat rhhidden rhniceselect hideonmobile', 'id'=>mt_rand())); ?>
	<button type="submit" class="btnsearch hideonmobile"  aria-label="<?php echo esc_attr($search_text) ;?>"><i class="rhicon rhi-search"></i></button>
</form>
<?php if ($rehub_ajax_search) { ?>
<div class="re-aj-search-wrap rhscrollthin"></div>
<?php } ?>