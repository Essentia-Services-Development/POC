<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $product; global $post;?>
<?php if (empty( $product ) ) {return;}?>
<?php $attrelpanel = (isset($attrelpanel)) ? $attrelpanel : '';?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn');?>
<?php $affiliatetype = ($product->get_type() =='external') ? true : false;?>
<?php if($affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn)) :?>
    <?php $woolink = $product->add_to_cart_url(); $wootarget = ' target="_blank" rel="nofollow sponsored"';?>
<?php else:?>
    <?php $woolink = get_post_permalink($post->ID); $wootarget = '';?>
<?php endif;?>
<?php $offer_coupon = get_post_meta( $post->ID, 'rehub_woo_coupon_code', true ) ?>
<?php $offer_coupon_date = get_post_meta( $post->ID, 'rehub_woo_coupon_date', true ) ?>
<?php $offer_coupon_mask = get_post_meta( $post->ID, 'rehub_woo_coupon_mask', true ) ?>
<?php $offer_coupon_url = esc_url( $product->add_to_cart_url() ); ?>
<?php $discountpercentage = get_post_meta($post->ID, 'rehub_offer_discount', true);?>
<?php $coupon_style = $expired =''; if(!empty($offer_coupon_date)) : ?>
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
      $coupon_style = ' expired_coupon';
      $expired = '1';
    }
    ?>
<?php endif ;?>
<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : ''; ?>
<?php $reveal_enabled = ($coupon_mask_enabled =='1') ? ' reveal_enabled' : '';?>
<?php $outsidelinkpart = ($coupon_mask_enabled=='1') ? ' data-codeid="'.$product->get_id().'" data-dest="'.$offer_coupon_url.'" data-clipboard-text="'.$offer_coupon.'" class="re_track_btn masked_coupon"' : ' class="re_track_btn"';?>
<?php 
if (!empty($offer_coupon)) {
    $deal_type = ' coupontype';
    $deal_type_string = esc_html__('Coupon', 'rehub-theme');
}
elseif ($product->is_on_sale()){
    $deal_type = ' saledealtype';
    $deal_type_string = esc_html__('Sale', 'rehub-theme');
}
else {
    $deal_type = ' defdealtype';
    $deal_type_string = esc_html__('Deal', 'rehub-theme');
}
?>
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
<div class="woocommerce type-product rh_offer_list rh_actions_padd <?php echo ''.$reveal_enabled.$coupon_style.$deal_type; ?>">    
    <?php do_action('woocommerce_before_shop_loop_item');?>
    <div class="button_action">
        <div class="floatleft mr5">
            <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
            <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
            <?php echo RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved);?>  
        </div>
        <?php if(rehub_option('woo_quick_view')):?>
            <div class="floatleft">
                <?php echo RH_get_quick_view($post->ID, 'icon', 'pl10 pr10'); ?>
            </div>
        <?php endif;?>
        <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>
            <span class="compare_for_grid floatleft">            
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
    <div class="rh_grid_image_3_col">
        <div class="rh_gr_img_first offer_thumb"> 
            <div class="border-grey deal_img_wrap position-relative text-center width-100">       
            <a href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?> <?php echo ''.$outsidelinkpart; ?>>
            <?php if ($discountpercentage) :?>
                <span class="height-80 rh-flex-center-align rh-flex-justify-center sale_tag_inwoolist text-center"><div class="font150 fontbold greencolor mb0 ml0 mr0 mt0 overflow-elipse pb0 pl0 pr0 pt0"><?php echo ''.$discountpercentage;?></div></span>     
            <?php elseif (!has_post_thumbnail() && $product->is_on_sale() && $product->get_regular_price() && $product->get_price() > 0 && !$product->is_type( 'variable' )) :?>
                <span class="height-80 rh-flex-center-align rh-flex-justify-center sale_tag_inwoolist text-center">
                    <h5 class="font150 fontbold greencolor mb0 ml0 mr0 mt0 overflow-elipse pb0 pl0 pr0 pt0">
                    <?php   
                        $offer_price_calc = (float) $product->get_price();
                        $offer_price_old_calc = (float) $product->get_regular_price();
                        $sale_proc = 0 -(100 - ($offer_price_calc / $offer_price_old_calc) * 100); 
                        $sale_proc = round($sale_proc); 
                        echo ''.$sale_proc.'%';
                    ;?>
                    </h5>
                </span>
            <?php else :?>             
                <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail'); ?>
            <?php endif;?>
            </a>
            <div class="<?php echo ''.$deal_type;?>_deal_string deal_string border-top font70 lineheight25 text-center upper-text-trans"><?php echo ''.$deal_type_string;?></div>
            </div>
            <?php do_action( 'rh_woo_thumbnail_loop' ); ?>
        </div>
        <div class="rh_gr_top_middle">
            <div class="woo_list_desc">  
                <div class="woolist_meta mb10">
                    <?php if(rehub_option('exclude_date_meta') != 1):?>
                        <span class="date_ago mr5">
                            <i class="rhicon rhi-clock"></i> <?php printf( esc_html__( '%s ago', 'rehub-theme' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?>
                        </span> 
                    <?php endif;?>
                    <?php if(!empty($offer_coupon_date)) {echo '<span class="listtimeleft mr5 rh-nowrap"> <i class="rhicon rhi-hourglass"></i> '.$coupon_text.'</span>';} ?>                         
                </div>                          
                <h3 class="font120 mb10 mt0 mobfont110 moblineheight20 <?php echo getHotIconclass($post->ID, true); ?>"><a href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?> <?php echo ''.$outsidelinkpart; ?>><?php echo rh_expired_or_not($post->ID, 'span');?><?php the_title();?></a></h3>
                <?php rh_wooattr_code_loop($attrelpanel);?> 
                <?php if ($product->get_price() !='') : ?>
                <?php echo '<span class="pricefont110 rehub-main-color mobpricefont90 fontbold mb10 mr10 lineheight20 floatleft"><span class="price">'.$product->get_price_html().'</span></span>';?>
                <?php endif ;?>
                <?php 
                    if($product->is_on_sale() && $product->get_regular_price() && $product->get_price() > 0 && !$product->is_type( 'variable' )){
                        $offer_price_calc = (float) $product->get_price();
                        $offer_price_old_calc = (float) $product->get_regular_price();
                        $sale_proc = 0 -(100 - ($offer_price_calc / $offer_price_old_calc) * 100); 
                        $sale_proc = round($sale_proc);
                        echo '<span class="rh-label-string mr10 mb5 floatleft">'.$sale_proc.'%</span>';
                    }

                ?>                                             
                <div class="clearfix"></div>
            </div>
        </div> 
        <div class="rh_gr_middle_desc font90 lineheight20">
            <?php echo strip_shortcodes($post->post_excerpt); ?>
            <div class="mt10"><?php wc_get_template( 'loop/rating.php' );?> </div>
        </div>                  
        <div class="rh_gr_btn_block">
            <div class="block_btnblock priced_block mb10">
                <?php if($countoffers > 1):?>
                    <a href="<?php echo get_post_permalink($post->ID);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn woo_loop_btn btn_offer_block product_type_cegg">
                        <?php if(rehub_option('rehub_btn_text_aff_links') !='') :?>
                            <?php echo rehub_option('rehub_btn_text_aff_links') ; ?>
                        <?php else :?>
                            <?php esc_html_e('Choose offer', 'rehub-theme') ?>
                        <?php endif ;?>
                    </a>
                <?php elseif($countoffers == 1 && !empty($itemsync['url'])):?>
                    <?php $ceofferurl = apply_filters('rh_post_offer_url_filter', $itemsync['url']);?>
                    <a href="<?php echo esc_url($ceofferurl);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn woo_loop_btn btn_offer_block product_type_external" target="_blank" rel="nofollow sponsored">
                        <?php if(rehub_option('rehub_btn_text') !='') :?>
                            <?php echo rehub_option('rehub_btn_text') ; ?>
                        <?php else :?>
                            <?php esc_html_e('Buy Now', 'rehub-theme') ?>
                        <?php endif ;?>
                    </a>
                <?php elseif ( $product->add_to_cart_url() !='') : ?>
                    <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
                        sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn woo_loop_btn btn_offer_block %s %s product_type_%s"%s %s>%s</a>',
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
                <?php if ($coupon_mask_enabled =='1') :?>
                    <?php wp_enqueue_script('zeroclipboard'); ?>                
                    <a class="woo_loop_btn coupon_btn re_track_btn btn_offer_block rehub_offer_coupon masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" href="<?php echo esc_url($woolink); ?>"<?php if ($product->get_type() =='external'){echo ' target="_blank" rel="nofollow sponsored"'; echo ''.$outsidelinkpart; } ?>>
                        <?php if(rehub_option('rehub_mask_text') !='') :?><?php echo rehub_option('rehub_mask_text') ; ?><?php else :?><?php esc_html_e('Reveal coupon', 'rehub-theme') ?><?php endif ;?>
                    </a>
                <?php else :?> 
                    <?php if(!empty($offer_coupon)) : ?>
                        <?php wp_enqueue_script('zeroclipboard'); ?>
                        <div class="rehub_offer_coupon not_masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo ''.$offer_coupon ?>">
                            <i class="rhicon rhi-scissors fa-rotate-180"></i>
                            <span class="coupon_text"><?php echo esc_attr($offer_coupon); ?></span>
                        </div>
                    <?php endif ;?>                                               
                <?php endif;?>                 
            </div>
            <?php if ( $product->managing_stock() && ! $product->is_in_stock() ):?>
                <div class="stock out-of-stock mt5 redbrightcolor mb5"><?php esc_html_e('Out of Stock', 'rehub-theme');?></div>
            <?php endif;?>
            <span class="woolist_vendor">
                <?php if(!empty($syncitem)):?>
                    <div class="font80 greycolor lineheight15">
                    <?php echo rh_best_syncpost_deal($itemsync, 'mb10 compare-domain-icon', false);?>
                    </div>
                <?php else:?>
                    <?php do_action( 'rehub_vendor_show_action' ); ?>        
                <?php endif;?>                     
            </span>            
            <?php do_action( 'rh_woo_button_loop' ); ?>
        </div>
    </div>
    <?php do_action( 'woocommerce_after_shop_loop_item' );?>
</div>