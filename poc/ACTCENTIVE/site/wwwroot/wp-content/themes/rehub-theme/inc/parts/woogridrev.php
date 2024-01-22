<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
global $post; global $product;
?>  
<?php if (empty( $product ) ) {return;}?>
<?php $classes = array('product', 'col_item', 'column_grid', 'type-product', 'rh-cartbox', 'hide_sale_price', 'two_column_mobile', 'woo_column_grid', 'rh-shadow4', 'flowvisible');?>
<?php $attrelpanel = (isset($attrelpanel)) ? $attrelpanel : '';?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn');?>
<?php $affiliatetype = ($product->get_type() =='external') ? true : false;?>
<?php $soldout = (isset($soldout)) ? $soldout : '';?>
<?php $custom_img_width = (isset($custom_img_width)) ? $custom_img_width : '';?>
<?php $custom_img_height = (isset($custom_img_height)) ? $custom_img_height : '';?>
<?php $custom_col = (isset($custom_col)) ? $custom_col : '';?>
<?php $customcrop = (get_option('woocommerce_thumbnail_cropping') == 'custom') ? true : false;?>
<?php if($affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn)) :?>
    <?php $woolink = $product->add_to_cart_url(); $wootarget = ' target="_blank" rel="nofollow sponsored"';?>
<?php else:?>
    <?php $woolink = get_post_permalink($post->ID); $wootarget = '';?>
<?php endif;?>
<?php $woo_enable_btn = rehub_option('woo_compact_loop_btn');?>
<?php $sales_html = ''; if ( $product->is_on_sale()) : ?>
    <?php 
    $percentage=0;
    if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0 && is_numeric($product->get_price()) ) {
        $percentage = round( ( ( $product->get_regular_price() - $product->get_price() ) / $product->get_regular_price() ) * 100 );
    }
    if ($percentage && $percentage>0 && !$product->is_type( 'variable' )) {
        $sales_html = '<div class="font80 text-right-align greencolor"><span><i class="rhicon rhi-arrow-down"></i> ' . $percentage . '%</span></div>';
        $classes[] = 'prodonsale';
    }
    ?>
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
    <div class="<?php echo implode(' ', $classes); ?>">   
        <div class="position-relative woofigure pb15<?php if(!$customcrop) echo ' pt15 pl15 pr15';?>">
        <?php echo re_badge_create('ribbon'); ?>
        <div class="button_action rh-shadow-sceu pt5 pb5 rhhidden showonsmobile">
            <div>
                <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                <?php echo RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved);?>  
            </div>
            <?php if(rehub_option('woo_quick_view')):?>
                <div>
                    <?php echo RH_get_quick_view($post->ID, 'icon', 'pt5 pl5 pr5 pb5'); ?>
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
        <figure class="text-center mb0<?php if(!$customcrop) echo ' eq_figure';?>">  
            <a class="img-centered-flex rh-flex-justify-center<?php if(!$customcrop) echo ' rh-flex-center-align';?>" href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?>>
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
        <?php do_action( 'rehub_after_grid_column_figure' ); ?>
        <div class="pb10 pr15 pl15">
            <div class="colored_rate_bar floatright ml15 mb15 rtlmr15">
                <?php $reviewscore = wpsm_reviewbox(array('compact'=>'mediumcircle', 'id'=> $product->get_id()));?><?php echo ''.$reviewscore;?>
            </div>         
            <h3 class="text-clamp text-clamp-3 mb15 mt0 font105 mobfont100 fontnormal lineheight20"<?php if ($woo_enable_btn):?>  style="height:60px; overflow:hidden"<?php endif;?>>
                <?php if ( $product->is_featured() ) : ?>
                    <i class="rhicon rhi-bolt mr5 ml5 orangecolor" aria-hidden="true"></i>
                <?php endif; ?>
                <a href="<?php echo esc_url($woolink);?>"<?php echo ''.$wootarget;?>><?php the_title();?></a>
            </h3> 
            <?php if ( ! $product->is_in_stock() ):?>
                <div class="stock out-of-stock mb5"><?php esc_html_e('Out of Stock', 'rehub-theme');?></div>
            <?php endif;?> 
            <div class="clearbox"></div>    
            <?php if ( ! empty( $gmw['form_values']['lat'] ) ) : ?>
                <span class="radius-dis">(<?php gmw_distance_to_location( $post, $gmw ); ?>)</span>       
            <?php endif ?> 
            <?php if ( isset( $gmw ) ) { ?>
                <div class="wppl-address font90 lineheight15 mb10 greycolor">
                    <i class="rhicon rhi-map-marker-alt" aria-hidden="true"></i> <?php echo ''.$post->address; ?>
                </div> 
            <?php } ?>                                            
            <?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
                <!-- Get directions -->
                <div class="get-directions-link font80">
                    <?php $labels = (!empty($gmw['labels']['search_results']['directions'])) ? $gmw['labels']['search_results']['directions'] : '';?>
                    <?php gmw_directions_link( $post, $gmw, $labels ); ?>
                </div>
            <?php } ?>            
            <?php do_action( 'rehub_vendor_show_action' ); ?> 
            <?php if($soldout):?>
                <?php rh_soldout_bar($post->ID);?>
            <?php endif; ?>         
            <?php rh_wooattr_code_loop($attrelpanel);?>
            <?php if ($woo_enable_btn):?>
                <div class="woo_gridloop_btn mb10 mt10 text-center">
                    <?php if($countoffers > 1):?>
                        <a href="<?php echo get_post_permalink($post->ID);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn woo_loop_btn btn_offer_block   product_type_external" target="_blank" rel="nofollow sponsored">
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
                            ), $product );?>                                     
                    <?php endif;?>
                    <?php do_action( 'rh_woo_button_loop' ); ?>            
                </div>
            <?php endif; ?>        
            <?php do_action( 'woocommerce_after_shop_loop_item' );?>
        </div>
        <div class="border-top pt10 pr10 pl10 pb10 rh-flex-center-align abposbot">
            <div class="button_action position-static hideonsmobile rh-flex-center-align">
                <div class="floatleft mr5 rtlfloatleft">
                    <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                    <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                    <?php echo RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved);?>  
                </div>
                <?php if(rehub_option('woo_quick_view')):?>
                    <div class="floatleft rtlfloatleft">
                        <?php echo RH_get_quick_view($post->ID, 'icon', 'pl10 pr10'); ?>
                    </div>
                <?php endif;?>
                <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>
                    <span class="compare_for_grid floatleft rtlfloatleft">            
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
            <div class="rh-flex-right-align mobilesblockdisplay rehub-btn-font pr5 pricefont100 redbrightcolor fontbold mb0 lineheight20 text-right-align">
                <?php wc_get_template( 'loop/price.php' ); ?>
                <?php echo ''.$sales_html; ?>            
            </div>        
        </div>                                        
    </div>
