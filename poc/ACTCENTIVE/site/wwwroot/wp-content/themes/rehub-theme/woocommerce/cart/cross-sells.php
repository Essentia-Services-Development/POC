<?php
/**
 * Cross-sells
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cross-sells.php.
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
 * @version     4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$classes = array('products', 'col_wrap_three', 'rh-flex-eq-height', 'woorelatedgrid', 'compact_rel_grid');
if ( $cross_sells ) : ?>
	<div class="cross-sells">
		<?php $heading = apply_filters( 'woocommerce_product_cross_sells_products_heading', esc_html__( 'You may be interested in&hellip;', 'rehub-theme' ) );
		if ( $heading ) :
			?>
			<h2 class="font120"><?php echo ''.$heading; ?></h2>
		<?php endif; ?>
        <?php $ids = array(); ?>
        <?php foreach ($cross_sells as $cross_sell){$ids[] = $cross_sell->get_id();} ?>
        <?php echo wpsm_woogrid_shortcode(array('gridtype' => 'dealwhite', 'data_source'=> 'ids', 'columns'=>'3_col', 'ids'=> implode(',',$ids), 'price_meta'=> 'productimage', 'iscart'=> '1'));?>
	</div>
<?php endif;
wp_reset_postdata();