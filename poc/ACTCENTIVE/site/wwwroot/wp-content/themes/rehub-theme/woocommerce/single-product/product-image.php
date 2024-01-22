<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $product;
$columns           = (!isset($columns_thumbnails)) ? apply_filters( 'woocommerce_product_thumbnails_columns', 5 ) : $columns_thumbnails;
$post_thumbnail_id = $product->get_image_id();
$placeholder       = $post_thumbnail_id ? 'with-images' : 'without-images';
$attachment_ids = $product->get_gallery_image_ids();
$wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
	'woocommerce-product-gallery',
	'woocommerce-product-gallery--' . $placeholder,
	'woocommerce-product-gallery--columns-' . absint( $columns ),
	'images',
	(empty ($attachment_ids)) ? 'no-gallery-thumbnails' : 'gallery-thumbnails-enabled',
	(empty ($attachment_ids) && isset($height_woo_main)) ? 'img-mobs-maxh-250' : '',
	(empty ($attachment_ids)) ? '' : 'flowhidden',
) );
?>
<?php $opacityinit = (empty ($attachment_ids)) ? '1': '0';?>
<?php 
    $height_resize = (!isset($noresize)) ? true : false;
    if(isset($height_woo_main) && $height_resize){
		echo rh_generate_incss('woosingleimage', '', array('height'=>$height_woo_main));           	
    }       
    //$image_url = $showimg->get_not_resized_url();	
?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: <?php echo ''.$opacityinit;?>; transition: opacity .25s ease-in-out;">
	<figure class="woocommerce-product-gallery__wrapper">
			<?php		
			if ( $post_thumbnail_id ) {
				$html = wc_get_gallery_image_html( $post_thumbnail_id, true );
			} else {
				$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
				$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src('woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'rehub-theme' ) );
				$html .= '</div>';
			}

			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped


			?>
		<?php do_action( 'woocommerce_product_thumbnails' ); ?>
	</figure>
	<?php do_action( 'rehub_360_product_image' ); ?>
</div>