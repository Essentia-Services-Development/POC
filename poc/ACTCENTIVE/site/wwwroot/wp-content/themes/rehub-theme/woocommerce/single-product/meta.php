<?php
/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
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
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>
<div class="product_meta">

	<?php $term_ids =  wc_get_product_terms($product->get_id(), 'store', array("fields" => "ids")); ?>
	<?php if (!empty($term_ids) && ! is_wp_error($term_ids)) :?>
		<div class="woostorewrap flowhidden mb10 rh-flex-center-align">
			<div class="brand_logo_small">       
				<?php WPSM_Woohelper::re_show_brand_tax('logo'); //show brand logo?>
			</div>			
			<div class="store_tax">       
				<?php WPSM_Woohelper::re_show_brand_tax(); //show brand taxonomy?>
			</div>	
		</div>
	<?php endif;?>

	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
        <?php $sku = $product->get_sku();?>
        <?php 
            if(!$sku){
                $sku = esc_html__( 'N/A', 'rehub-theme' );
            };
        ?> 
		<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'rehub-theme' ); ?> <span class="sku"><?php echo esc_html($sku); ?></span></span>

	<?php endif; ?>

	<?php if(isset($nocategory) && $nocategory ==true):?>
	<?php else:?>
		<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'rehub-theme' ) . ' ', '</span>' ); ?>		
	<?php endif;?>

	<?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'rehub-theme' ) . ' ', '</span>' ); ?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>