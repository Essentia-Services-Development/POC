<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
global $post; global $product;
?>  
<?php if (empty( $product ) ) {return;}?>
<?php $classes = array('product', 'col_item', 'column_grid', 'type-product', 'rh-cartbox', 'rehub-sec-smooth', 'woo_column_grid', 'hide_sale_price', 'rh-shadow5', 'flowvisible', 'woo_digi_grid');?>
<?php $attrelpanel = (isset($attrelpanel)) ? $attrelpanel : '';?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn');?>
<?php $affiliatetype = ($product->get_type() =='external') ? true : false;?>
<?php $soldout = (isset($soldout)) ? $soldout : '';?>
<?php $custom_img_width = (isset($custom_img_width)) ? $custom_img_width : '';?>
<?php $custom_img_height = (isset($custom_img_height)) ? $custom_img_height : '';?>
<?php $custom_col = (isset($custom_col)) ? $custom_col : '';?>
<?php $customcrop = (get_option('woocommerce_thumbnail_cropping') == 'custom') ? true : false;?>
<?php $woo_enable_btn = rehub_option('woo_compact_loop_btn');?>
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
        $sales_html = '<div class="font80 text-right-align greencolor"><span><i class="rhicon rhi-arrow-down"></i> ' . $percentage . '%</span></div>';
        $classes[] = 'prodonsale';
    }
    ?>
<?php endif; ?>
<?php if($customcrop) {$classes[] = 'noborder';}?>
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
    <figure class="rehub-sec-smooth text-center flowhidden mb0<?php if(!$customcrop) echo ' eq_figure';?>">  
        <?php 
            $post_image_videos = get_post_meta( $post->ID, 'rh_product_video', true );
        ?>
        <?php if($post_image_videos && rehub_option('theme_subset') != 'regame'):?>
            <?php 
                $videos = array_map('trim', explode(PHP_EOL, $post_image_videos));
                $video = $videos[0]; 
                wp_enqueue_script('rhvideolazy');
            ?>
            <div class="compare-full-thumbnails">
                <div class="rh_videothumb_link cursorpointer rh_lazy_load_video" data-hoster="<?php echo parse_video_url(esc_url($video), 'hoster');?>" data-width="560" data-height="315" data-videoid="<?php echo parse_video_url(esc_url($video), 'id');?>">
                        <?php if($product->get_image_id()):?>
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
                                <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail', '', array('emptyimage'=>get_template_directory_uri() . '/images/default/noimage_336_220.png')); ?>      
                            <?php endif ; ?> 
                        <?php else:?>
                            <img data-src="<?php echo parse_video_url(esc_url($video), "maxthumb");?>" alt="video <?php echo get_the_title();?>" width="560" class="lazyload" src="<?php echo get_template_directory_uri() . '/images/default/noimage_336_220.png';?>" height="315" />
                        <?php endif;?> 
                </div>
            </div>
        <?php else:?>
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
                    <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail', '', array('emptyimage'=>get_template_directory_uri() . '/images/default/noimage_336_220.png')); ?>     
                <?php endif ; ?> 
            </a>
        <?php endif;?>
        <?php do_action( 'rh_woo_thumbnail_loop' ); ?>        
    </figure>
    </div>
    <?php do_action( 'rehub_after_grid_column_figure' ); ?>
    <div class="pr10 pl10">  
        <div class="upper-text-trans font70 lineheight15 mb5">
            <?php $categories = wc_get_product_terms($post->ID, 'product_cat');  ?>
            <?php if (!empty($categories)) {
                $first_cat = $categories[0]->term_id;
                echo '<a href="'.get_term_link((int)$categories[0]->term_id, 'product_cat').'" class="woocat greycolor">'.$categories[0]->name.'</a>'; 
            } ?>                         
        </div> 
        <div class="rh-flex-columns rh-flex-nowrap"> 
            <div class="digititlearea wordbreak">    
                <h3 class="text-clamp text-clamp-3 mb10 mt0 font110 fontnormal lineheight20"<?php if ($woo_enable_btn):?> style="height:60px; overflow:hidden"<?php endif;?>>
                    <?php if ( $product->is_featured() ) : ?>
                        <i class="rhicon rhi-bolt mr5 ml5 orangecolor" aria-hidden="true"></i>
                    <?php endif; ?>
                    <a href="<?php echo esc_url($woolink);?>"<?php echo ''.$wootarget;?>><?php the_title();?></a>
                </h3> 
                <?php wc_get_template( 'loop/rating.php' );?> 
            </div>
            <div class="rh-flex-right-align pl15">
                <div class="lineheight20 mb0 mr0 pricefont80 rehub-btn-font rh-label-string rehub-main-color lightgreybg ">
                    <?php wc_get_template( 'loop/price.php' ); ?>           
                </div> 
                <?php echo ''.$sales_html; ?> 
            </div>
        </div>
        <?php if ( ! $product->is_in_stock() ):?>
            <div class="stock out-of-stock mb5"><?php esc_html_e('Out of Stock', 'rehub-theme');?></div>
        <?php endif;?> 
        <div class="clearbox"></div>    
        <?php if ( ! empty( $gmw['form_values']['lat'] ) ) { ?>
            <span class="radius-dis">(<?php gmw_distance_to_location( $post, $gmw ); ?>)</span>       
        <?php } ?> 
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
    <div class="pt10 pr5 pl10 pb10 rh-flex-center-align abposbot">
        <div class="rh-flex-center-align aff_tag">
            <?php if(defined( 'WCFMmp_TOKEN' ) || class_exists( 'WeDevs_Dokan' ) ):?>
                <?php do_action( 'rehub_vendor_show_action' ); ?> 
            <?php else:?>
                <?php WPSM_Woohelper::re_show_brand_tax('name'); //show brand taxonomy?>
            <?php endif;?>                                                          
        </div>
        <div class="rh-flex-right-align pr5 text-right-align button_action position-static rh-flex-center-align">
            <?php if(rehub_option('woo_quick_view')):?>
                <div class="floatleft rtlfloatleft">
                    <?php echo RH_get_quick_view($post->ID, 'icon', 'pl5 pr5'); ?>
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
            <div class="floatleft rtlfloatleft">
                <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                <?php echo RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved);?>  
            </div>          
        </div>        
    </div>                                        
</div>