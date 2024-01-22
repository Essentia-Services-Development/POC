<?php if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}?>
<?php global $product, $post?>
<div class="price-in-compare-flip mt10 woocommerce">
 
    <?php if ($product->get_price() !='') : ?>
        <span class="price-woo-compare-chart rehub-btn-font rehub-main-color mb15 fontbold"><?php echo ''.$product->get_price_html(); ?></span>
        <div class="mb10"></div>
    <?php endif;?>
    <?php $syncitem = '';?>
    <?php if (defined('\ContentEgg\PLUGIN_PATH')):?>
        <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($post->ID);?>
        <?php if(!empty($itemsync)):?>
            <?php                            
                $syncitem = $itemsync;                            
            ?>
        <?php endif;?>
    <?php endif;?>
    <?php if (rehub_option('woo_btn_disable') != '1'):?>
        <?php if(!empty($syncitem)):?>
            <a href="<?php the_permalink();?>" class="btn_offer_block btn-woo-compare-chart woo_loop_btn add_to_cart_button">
                <?php if(rehub_option('rehub_btn_text_aff_links') !='') :?><?php echo rehub_option('rehub_btn_text_aff_links') ; ?><?php else :?><?php esc_html_e('Choose offer', 'rehub-theme') ?><?php endif ;?>
            </a>
        <?php else:?>    
            <?php if ($product->add_to_cart_url() !='') : ?>
                <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
                    sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn btn_offer_block btn-woo-compare-chart woo_loop_btn %s %s product_type_%s"%s%s>%s</a>',
                    esc_url( $product->add_to_cart_url() ),
                    esc_attr( $product->get_id() ),
                    esc_attr( $product->get_sku() ),
                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                    $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
                    esc_attr( $product->get_type() ),
                    $product->get_type() =='external' ? ' target="_blank"' : '',
                    $product->get_type() =='external' ? ' rel="nofollow sponsored"' : '',
                    esc_html( $product->add_to_cart_text() )
                    ),
                $product );?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
    <div class="yith_woo_chart mt10"> 
        <?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme');?>
        <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
        <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
        <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved);?> 
	</div> 
                     
</div> 