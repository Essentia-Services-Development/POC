<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $product; global $post;?>
<?php if (empty( $product )) {return;}?>
<?php $classes = array('product', 'col_item', 'woo_column_item', 'two_column_mobile', 'type-product');?>
<?php if (rehub_option('woo_btn_disable') == '1'){$classes[] = 'non_btn';}?>
<?php $custom_img_width = (isset($custom_img_width)) ? $custom_img_width : '';?>
<?php $custom_img_height = (isset($custom_img_height)) ? $custom_img_height : '';?>
<?php $custom_col = (isset($custom_col)) ? $custom_col : '';?>
<?php $soldout = (isset($soldout)) ? $soldout : '';?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $attrelpanel = (isset($attrelpanel)) ? $attrelpanel : '';?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn');?>
<?php $affiliatetype = ($product->get_type() =='external') ? true : false;?>
<?php if($affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn)) :?>
    <?php $woolink = $product->add_to_cart_url(); $wootarget = ' target="_blank" rel="nofollow sponsored"';?>
<?php else:?>
    <?php $woolink = get_post_permalink($post->ID); $wootarget = '';?>
<?php endif;?>
<?php $offer_coupon = get_post_meta( $post->ID, 'rehub_woo_coupon_code', true ) ?>
<?php $offer_coupon_date = get_post_meta( $post->ID, 'rehub_woo_coupon_date', true ) ?>
<?php $offer_coupon_mask = '1' ?>
<?php $offer_url = esc_url( $product->add_to_cart_url() ); ?>
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
        $coupon_style = ' expired_coupon';
        $expired = '1';
    }                 
    ?>
<?php endif ;?>
<?php $classes[] = $coupon_style;?>
<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : ''; ?>
<?php if($coupon_mask_enabled =='1') {$classes[] = 'reveal_enabled';}?>
<div class="<?php echo implode(' ', $classes); ?>">
    <?php  $badge = get_post_meta($post->ID, 'is_editor_choice', true); ?>
    <?php if ($badge !='' && $badge !='0') :?> 
        <?php echo re_badge_create('ribbon'); ?>   
    <?php else : ?>      
        <?php if ( $product->is_featured() ) : ?>
                <?php echo apply_filters( 'woocommerce_featured_flash', '<span class="onfeatured">' . esc_html__( 'Featured!', 'rehub-theme' ) . '</span>', $post, $product ); ?>
        <?php endif; ?>        
        <?php if ( $product->is_on_sale()) : ?>
            <?php 
            $percentage=0;
            $featured = ($product->is_featured()) ? ' onsalefeatured' : '';
            if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0) {
                $percentage = round( ( ( $product->get_regular_price() - $product->get_price() ) / $product->get_regular_price() ) * 100 );
            }
            if ($percentage && $percentage>0 && !$product->is_type( 'variable' )) {
                $sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale'.$featured.'"><span>- ' . $percentage . '%</span></span>', $post, $product );
            }
            else{
                $sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale'.$featured.'">' . esc_html__( 'Sale!', 'rehub-theme' ) . '</span>', $post, $product );  
            }              
            ?>
            <?php echo ''.$sales_html; ?>
        <?php endif; ?>
    <?php endif; ?>     
    <?php do_action('woocommerce_before_shop_loop_item');?>
    <figure class="full_image_woo rh-hovered-wrap flowhidden mb0">
        <div class="button_action rh-shadow-sceu pt5 pb5">
            <div class="">
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
        <a class="img-centered-flex rh-flex-center-align rh-flex-justify-center" href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?>>
            <?php if($custom_col) : ?>
                <?php 
                    $showimg = new WPSM_image_resizer();
                    $showimg->use_thumb = true; 
                    $showimg->no_thumb = rehub_woocommerce_placeholder_img_src('');
                    $showimg->width = (int)$custom_img_width;    
                    $showimg->height = (int)$custom_img_height;
                    $showimg->crop = true;
                    $showimg->show_resized_image();                               
                ?>                                                 
            <?php else : ?>
                <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail'); ?>      
            <?php endif ; ?> 

            <?php $attachment_ids = $product->get_gallery_image_ids();?>
            <?php if ( $attachment_ids && $product->get_image_id() ):?>
                <div class="rh-hov-img-trans rh-flex-center-align rh-flex-justify-center abdfullwidth">
                    <?php if($custom_col) : ?>
                        <?php 
                            $thumbnail_sec = wp_get_attachment_image_src( $attachment_ids[0], 'full' );
                            $image_url = $thumbnail_sec[0];
                        ?>
                        <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $image_url, 'width'=> (int)$custom_img_width, 'height'=> (int)$custom_img_height, 'crop' => true, 'title' => get_post_field( 'post_title', $attachment_ids[0] )));?>                                              
                    <?php else : ?>
                        <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail', $attachment_ids[0]); ?>      
                    <?php endif ; ?> 
                </div>
            <?php endif;?>            
        </a>          
        <?php do_action( 'rehub_after_woo_brand' ); ?>
        <?php do_action( 'rh_woo_thumbnail_loop' ); ?>
    </figure>
    <div class="woo_column_desc padd15 csstranstrans text-center">     
        <h3 class="fontnormal mb10 mt0 lineheight25">
            <?php echo rh_expired_or_not($post->ID, 'span');?>
            <?php if ( $product->is_featured() ) : ?>
                <i class="rhicon rhi-bolt mr5 ml5 orangecolor" aria-hidden="true"></i>
            <?php endif; ?>
            <a href="<?php echo esc_url($woolink);?>"<?php echo ''.$wootarget;?>><?php the_title();?></a>
        </h3> 
        <?php if($soldout):?>
            <?php rh_soldout_bar($post->ID);?>
        <?php endif; ?>        
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
        <div class="woo_column_price csstranstrans-o mt15 rehub-main-price redbrightcolor">
            <?php
                /**
                 * woocommerce_after_shop_loop_item_title hook.
                 *
                 * @hooked woocommerce_template_loop_rating - 5
                 * @hooked woocommerce_template_loop_price - 10
                 */
                do_action( 'woocommerce_after_shop_loop_item_title' );
            ?>
        </div> 
        <?php if ( ! $product->is_in_stock() ):?>
            <div class="stock out-of-stock mb5"><?php esc_html_e('Out of Stock', 'rehub-theme');?></div>
        <?php endif;?>  
    </div>
    <?php if (rehub_option('woo_btn_disable') != '1'):?>
        <div class="woo_column_btn text-center">   
            <?php if($countoffers > 1):?>

                <a href="<?php echo get_post_permalink($post->ID);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn fontbold rehub-btn-font rehub-main-color product_type_cegg">
                    <?php if(rehub_option('rehub_btn_text_aff_links') !='') :?>
                        <?php echo rehub_option('rehub_btn_text_aff_links') ; ?>
                    <?php else :?>
                        <?php esc_html_e('Choose offer', 'rehub-theme') ?>
                    <?php endif ;?>
                </a>

            <?php elseif($countoffers == 1 && !empty($itemsync['url'])):?>
                <?php $ceofferurl = apply_filters('rh_post_offer_url_filter', $itemsync['url']);?>
                <a href="<?php echo esc_url($ceofferurl);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn rehub-btn-font rehub-main-color product_type_external" target="_blank" rel="nofollow sponsored">
                    <?php if(rehub_option('rehub_btn_text') !='') :?>
                        <?php echo rehub_option('rehub_btn_text') ; ?>
                    <?php else :?>
                        <?php esc_html_e('Buy Now', 'rehub-theme') ?>
                    <?php endif ;?>
                </a>

            <?php elseif ( $product->add_to_cart_url() !='') : ?>               
                <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
                        sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn rehub-btn-font rehub-main-color rh-hovered-wrap rh-flex-center-align rh-flex-justify-center %s %s product_type_%s"%s %s>%s <span class="inlinestyle lineheight15 ml5 rh-hovered-scalebig csstranstrans">+</span></a>',
                        esc_url( $product->add_to_cart_url() ),
                        esc_attr( $product->get_id() ),
                        esc_attr( $product->get_sku() ),
                        $product->is_purchasable() && $product->is_in_stock() ? '' : '',
                        $product->supports( 'ajax_add_to_cart' ) ? 'add_to_cart_button flat-woo-btn ajax_add_to_cart' : '',
                        esc_attr( $product->get_type() ),
                        $product->get_type() =='external' ? ' target="_blank"' : '',
                        $product->get_type() =='external' ? ' rel="nofollow sponsored"' : '',
                        esc_html( $product->add_to_cart_text() )
                        ), $product );?>                                     
            <?php endif;?>
            <?php do_action( 'rh_woo_button_loop' ); ?>           
        </div>
    <?php endif; ?> 
    <?php do_action( 'woocommerce_after_shop_loop_item' );?>    
</div>