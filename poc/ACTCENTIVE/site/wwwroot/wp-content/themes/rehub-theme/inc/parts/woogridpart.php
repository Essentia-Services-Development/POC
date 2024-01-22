<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
    if(rehub_option('theme_subset') == 'repick') {
        return include(rh_locate_template('repicksub/inc/parts/woogridpart.php'));
    }
?>
<?php global $product; global $post;?>
<?php if (empty( $product ) ) {return;}?>
<?php $classes = array('product', 'col_item', 'woo_grid_compact', 'two_column_mobile', 'type-product');?>
<?php if (rehub_option('woo_btn_disable') == '1'){$classes[] = 'no_btn_enabled';}?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $attrelpanel = (isset($attrelpanel)) ? $attrelpanel : '';?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn');?>
<?php $affiliatetype = ($product->get_type() =='external') ? true : false;?>
<?php if($affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn)) :?>
    <?php $woolink = $product->add_to_cart_url(); $wootarget = ' target="_blank" rel="nofollow sponsored"';?>
<?php else:?>
    <?php $woolink = get_post_permalink($post->ID); $wootarget = '';?>
<?php endif;?>
<?php $offer_coupon_date = get_post_meta( $post->ID, 'rehub_woo_coupon_date', true ) ?>
<?php $custom_img_width = (isset($custom_img_width)) ? $custom_img_width : '';?>
<?php $custom_img_height = (isset($custom_img_height)) ? $custom_img_height : '';?>
<?php $custom_col = (isset($custom_col)) ? $custom_col : '';?>
<?php $soldout = (isset($soldout)) ? $soldout : '';?>
<?php $coupon_style = $expired = ''; if(!empty($offer_coupon_date)) : ?>
    <?php 
    $timestamp1 = strtotime($offer_coupon_date);
    if(strpos($offer_coupon_date, ':') ===false){
        $timestamp1 += 86399;
    } 
    $seconds = $timestamp1 - (int)current_time('timestamp',0); 
    $days = floor($seconds / 86400);
    $seconds %= 86400;
    if ($days > 0) {
        $coupon_text = $days.' '.__('days left', 'rehub-theme');
        $coupon_style = '';
        $expired = 'no';        
    }
    elseif ($days == 0){
        $coupon_text = esc_html__('Last day', 'rehub-theme');
        $coupon_style = '';
        $expired = 'no';        
    }
    else {
        $coupon_text = esc_html__('Expired', 'rehub-theme');
        $coupon_style = ' expired';
        $expired = '1';
    }                 
    ?>
<?php endif ;?>
<?php $classes[] = $coupon_style;?>
<div class="<?php echo implode(' ', $classes); ?>">
    <div class="button_action rh-shadow-sceu pt5 pb5">
        <div>
            <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
            <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
            <?php echo RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved);?>  
        </div>
        <?php if(rehub_option('woo_quick_view')):?>
            <div>
                <?php echo RH_get_quick_view($post->ID, 'icon', 'pt10 pl5 pr5 pb10'); ?>
            </div>
        <?php endif;?>
        <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>
            <span class="compare_for_grid">            
                <?php 
                    $cmp_btn_args = array(); 
                    $cmp_btn_args['class']= 'comparecompact';
                    if(rehub_option('compare_woo_cats') != '') {
                        $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                    }
                ?>                                                  
                <?php echo wpsm_comparison_button($cmp_btn_args); ?> 
            </span>
        <?php endif;?>                                                            
    </div>   
    <?php  $badge = get_post_meta($post->ID, 'is_editor_choice', true); ?>
    <?php if ($badge !='' && $badge !='0') :?> 
        <?php echo re_badge_create('ribbon'); ?> 
    <?php elseif ( $product->is_featured() ) : ?>
        <?php echo apply_filters( 'woocommerce_featured_flash', '<span class="re-ribbon-badge badge_2"><span>' . esc_html__( 'Featured!', 'rehub-theme' ) . '</span></span>', $post, $product ); ?>          
    <?php elseif ( $product->is_on_sale()) : ?>
        <?php 
        $percentage=0;
        if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0 && is_numeric($product->get_price()) ) {
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
    <figure class="mb15 mt25 position-relative<?php if($custom_col) : ?> notresized<?php endif ; ?>">    
        <a class="img-centered-flex rh-flex-justify-center rh-flex-center-align" title="<?php the_title();?>" href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?>>
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
        <div class="gridcountdown"><?php rehub_woo_countdown('no');?></div>        
    </figure>
    <div class="cat_for_grid lineheight15">
        <?php $categories = wc_get_product_terms($post->ID, 'product_cat');  ?>
        <?php if (!empty($categories)) {
            $first_cat = $categories[0]->term_id;
            echo '<a href="'.get_term_link((int)$categories[0]->term_id, 'product_cat').'" class="woocat">'.$categories[0]->name.'</a>'; 
        } ?>                         
    </div>
    <?php do_action('woocommerce_before_shop_loop_item');?> 
    <h3 class="<?php echo getHotIconclass($post->ID, true); ?> text-clamp text-clamp-2">
        <?php echo rh_expired_or_not($post->ID, 'span');?>
        <?php if ( $product->is_featured() ) : ?>
            <i class="rhicon rhi-bolt mr5 ml5 orangecolor" aria-hidden="true"></i>
        <?php endif; ?>
        <a href="<?php echo esc_url($woolink);?>"<?php echo ''.$wootarget;?>><?php the_title();?></a>
    </h3>
    <?php if ( ! $product->is_in_stock() ):?>
        <div class="stock out-of-stock mb5"><?php esc_html_e('Out of Stock', 'rehub-theme');?></div>
    <?php endif;?>
    <?php if($soldout):?>
        <?php rh_soldout_bar($post->ID);?>
    <?php endif; ?>     
    <?php wc_get_template( 'loop/rating.php' );?> 
    <?php $syncitem = $ceofferurl = ''; $countoffers = 0;?>
    <?php if (defined('\ContentEgg\PLUGIN_PATH')):?>
        <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($post->ID);?>
        <?php if(!empty($itemsync)):?>
            <?php                            
                $syncitem = $itemsync;                            
            ?>
            <?php $countoffers = rh_ce_found_total_offers($post->ID);?>
        <?php endif;?>
    <?php endif;?>
    <?php if(!empty($syncitem)):?>
        <div class="font80 greycolor lineheight15">
        <?php echo rh_best_syncpost_deal($itemsync, 'mb10 compare-domain-icon', false);?>
        </div>
    <?php else:?>
        <?php do_action( 'rehub_vendor_show_action' ); ?>        
    <?php endif;?>
    <?php rh_wooattr_code_loop($attrelpanel);?>    

    <div class="border-top pt10 pr10 pl10 pb10 rh-flex-center-align abposbot">
        <div class="price_for_grid redbrightcolor floatleft rehub-btn-font mr10">
            <?php wc_get_template( 'loop/price.php' ); ?>
        </div>
        <div class="rh-flex-right-align btn_for_grid floatright">
            <?php if (rehub_option('woo_btn_disable') != '1'):?> 
                <?php if($countoffers > 1):?>
                    <a class="font90 greencolor" href="<?php the_permalink();?>">+ <?php echo (int)$countoffers - 1; ?> <?php esc_html_e('more', 'rehub-theme');?></a>
                <?php elseif($countoffers == 1 && !empty($itemsync['url'])):?>
                    <?php $ceofferurl = apply_filters('rh_post_offer_url_filter', $itemsync['url']);?>
                    <a href="<?php echo esc_url($ceofferurl);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn woo_loop_btn rh-flex-center-align rh-flex-justify-center rh-shadow-sceu product_type_external" target="_blank" rel="nofollow sponsored">
                        <svg height="24px" version="1.1" viewBox="0 0 64 64" width="24px" xmlns="http://www.w3.org/2000/svg"><g><path d="M56.262,17.837H26.748c-0.961,0-1.508,0.743-1.223,1.661l4.669,13.677c0.23,0.738,1.044,1.336,1.817,1.336h19.35   c0.773,0,1.586-0.598,1.815-1.336l4.069-14C57.476,18.437,57.036,17.837,56.262,17.837z"/><circle cx="29.417" cy="50.267" r="4.415"/><circle cx="48.099" cy="50.323" r="4.415"/><path d="M53.4,39.004H27.579L17.242,9.261H9.193c-1.381,0-2.5,1.119-2.5,2.5s1.119,2.5,2.5,2.5h4.493l10.337,29.743H53.4   c1.381,0,2.5-1.119,2.5-2.5S54.781,39.004,53.4,39.004z"/></g></svg>
                        <?php if(rehub_option('rehub_btn_text') !='') :?>
                            <?php echo rehub_option('rehub_btn_text') ; ?>
                        <?php else :?>
                            <?php esc_html_e('Buy Now', 'rehub-theme') ?>
                        <?php endif ;?>
                    </a>
                <?php elseif ( $product->add_to_cart_url() !='') : ?>
                    <?php  echo apply_filters( 'reg_grid_loop_add_to_cart_link',
                        sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn woo_loop_btn rh-flex-center-align rh-flex-justify-center rh-shadow-sceu %s %s product_type_%s"%s %s><svg height="24px" version="1.1" viewBox="0 0 64 64" width="24px" xmlns="http://www.w3.org/2000/svg"><g><path d="M56.262,17.837H26.748c-0.961,0-1.508,0.743-1.223,1.661l4.669,13.677c0.23,0.738,1.044,1.336,1.817,1.336h19.35   c0.773,0,1.586-0.598,1.815-1.336l4.069-14C57.476,18.437,57.036,17.837,56.262,17.837z"/><circle cx="29.417" cy="50.267" r="4.415"/><circle cx="48.099" cy="50.323" r="4.415"/><path d="M53.4,39.004H27.579L17.242,9.261H9.193c-1.381,0-2.5,1.119-2.5,2.5s1.119,2.5,2.5,2.5h4.493l10.337,29.743H53.4   c1.381,0,2.5-1.119,2.5-2.5S54.781,39.004,53.4,39.004z"/></g></svg> %s</a>',
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
                <?php do_action( 'rh_woo_button_loop' ); ?>                    
            <?php endif;?>            
        </div>            
    </div>
    <?php do_action( 'woocommerce_after_shop_loop_item' );?>       
</div>