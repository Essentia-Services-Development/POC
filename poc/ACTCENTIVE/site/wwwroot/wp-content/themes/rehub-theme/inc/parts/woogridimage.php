<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
global $post; global $product;
?>  
<?php if (empty( $product ) ) {return;}?>
<?php $classes = array('type-product','rh-hover-up', 'rh-cartbox','product', 'col_item','two_column_mobile', 'column_grid', 'flowvisible', 'pt0', 'pb0', 'pr0', 'pl0', 'rh-shadow4', 'woo_column_image');?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn');?>
<?php $affiliatetype = ($product->get_type() =='external') ? true : false;?>
<?php $custom_img_width = (isset($custom_img_width)) ? $custom_img_width : '';?>
<?php $custom_img_height = (isset($custom_img_height)) ? $custom_img_height : '';?>
<?php $custom_col = (isset($custom_col)) ? $custom_col : '';?>
<?php if($affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn)) :?>
    <?php $woolink = $product->add_to_cart_url(); $wootarget = ' target="_blank" rel="nofollow sponsored"';?>
<?php else:?>
    <?php $woolink = get_post_permalink($post->ID); $wootarget = '';?>
<?php endif;?>
<?php $sales_html = ''; if ( $product->is_on_sale()) : ?>
    <?php 
    $percentage=0;
    if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0 && is_numeric($product->get_price()) ) {
        $percentage = round( ( ( $product->get_regular_price() - $product->get_price() ) / $product->get_regular_price() ) * 100 );
    }
    if ($percentage && $percentage>0 && !$product->is_type( 'variable' )) {
        $sales_html = '<div class="font80 text-right-align"><span><i class="rhicon rhi-arrow-down"></i> ' . $percentage . '%</span></div>';
        $classes[] = 'prodonsale';
    }
    ?>
<?php endif; ?>
<div class="<?php echo implode(' ', $classes); ?>">   
    <?php echo re_badge_create('ribbonleft'); ?>    
    <div class="position-relative woofigure pt30 pb30 pl20 pr20">
    <?php if ( $product->is_on_sale()) : ?>
        <?php 
        $percentage=0;
        if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0) {
            $percentage = round( ( ( $product->get_regular_price() - $product->get_price() ) / $product->get_regular_price() ) * 100 );
        }
        if ($percentage && $percentage>0  && !$product->is_type( 'variable' )) {
            $sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale"><span>- ' . $percentage . '%</span></span>', $post, $product );
        } else {
            $sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale">' . esc_html__( 'Sale!', 'rehub-theme' ) . '</span>', $post, $product );
        }
        ?>
        <?php echo ''.$sales_html; ?>
    <?php endif; ?>         
    <figure class="text-center eq_figure mb0">      
        <a class="img-centered-flex rh-flex-center-align rh-flex-justify-center" href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?>>
            <?php if($custom_col) : ?>
                <?php 
                    $showimg = new WPSM_image_resizer();
                    $showimg->use_thumb = true; 
                    $showimg->no_thumb = rehub_woocommerce_placeholder_img_src('');
                    $showimg->width = (int)$custom_img_width;    
                    $showimg->height = (int)$custom_img_height;
                    $showimg->show_resized_image();                               
                ?>                                                 
            <?php else : ?>
                <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail'); ?>      
            <?php endif ; ?> 
        </a>
        <?php do_action( 'rh_woo_thumbnail_loop' ); ?>        
    </figure>
    </div> 
    <?php do_action( 'woocommerce_after_shop_loop_item' );?>                                      
</div>