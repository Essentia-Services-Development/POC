<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $product, $post;?>
<?php                             
    if ( post_password_required() ) {
        echo '<div class="rh-container"><div class="rh-content-wrap clearfix"><div class="main-side clearfix full_width" id="content"><div class="post text-center">';
            echo get_the_password_form();
        echo '</div></div></div></div>';
        return;
    }
?>
<style scoped>
    .main-nav.white_style{border-bottom:none;}
    .woocommerce .summary table.shop_attributes{margin: 10px 0; font-size:90%; border:none; display: block; max-height:430px; overflow-y:auto;}
    .woocommerce .summary table.shop_attributes th, .woocommerce .summary table.shop_attributes td{border:none;padding: 5px 10px; text-align:inherit}
    .woocommerce .summary table.shop_attributes th{padding-left:0;}
    .woocommerce .summary table.shop_attributes th:after{content: ":";}
    #re-compare-icon-fixed{box-shadow:none}
</style>
<div class="side_block_video" id="content">
    <div class="post">
        <?php do_action( 'woocommerce_before_main_content' );?>
        <?php while ( have_posts() ) : the_post(); ?>
            <div id="product-<?php echo (int)$post->ID; ?>" <?php post_class(); ?>>

                <div class="top-woo-area darkbgl" id="rh_woo_layout_inimage">
                    <div class="rh-container wide_width_restricted position-static flowhidden pt30 pb30">                                   
                        <div class="rh-360-content-area tabletsblockdisplay woo_white_text_layout whitecolor mb20">
                            <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>
                            <?php do_action( 'woocommerce_before_single_product' ); ?> 
                            <?php do_action('rh_woo_single_product_title');?> 
                            <?php rh_woo_code_zone('content');?> 

                            <?php 
                                $post_image_videos = get_post_meta( $post->ID, 'rh_product_video', true );
                            ?>
                            <?php $videos=array(); if($post_image_videos):?>
                                <?php 
                                    $videos = array_map('trim', explode(PHP_EOL, $post_image_videos));
                                    $video = $videos[0]; 
                                    wp_enqueue_script('rhvideolazy');
                                ?>
                                <div class="rh-video-scroll-wrap">
                                    <div class="rh-video-scroll-cont">
                                        <?php $videodata = parse_video_url(esc_url($video), 'data');?>
                                        <div class="rh_video_thumb_schema"  itemscope itemtype="http://schema.org/VideoObject">
                                            <meta content="<?php echo get_the_title();?>" itemprop="name" />
                                            <meta itemprop="uploadDate" content="<?php echo ''.$post->post_date;?>" />
                                            <meta itemprop="thumbnailURL" content="<?php echo ''.$videodata['image'];?>" />
                                            <meta itemprop="embedUrl" content="<?php echo ''.$videodata['embed'];?>" />
                                            <meta itemprop="description" content="<?php echo wp_strip_all_tags($product->get_short_description('edit')); ?>" />
                                            <div class="rh_videothumb_link text-center cursorpointer rh_lazy_load_video" data-hoster="<?php echo ''.$videodata['hoster'];?>" data-width="930" data-height="520" data-videoid="<?php echo ''.$videodata['id'];?>">
                                            <?php if($product->get_image_id()):?>
                                                <?php echo WPSM_image_resizer::show_wp_image('full', '', array('max_height'=>'520px', 'emptyimage'=> get_template_directory_uri() . '/images/default/noimage_800_520.png' )); ?>  
                                            <?php else:?>
                                                <img src="<?php echo ''.$videodata['image'];?>" alt="video <?php echo get_the_title();?>" />
                                            <?php endif;?>     
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else:?>
                                <div class="flowhidden text-center position-relative woo-image-part">
                                    <?php $width_woo_main = 930; $height_woo_main = 520; $columns_thumbnails = 1?>
                                    <?php include(rh_locate_template('woocommerce/single-product/product-image.php')); ?>
                                </div> 
                            <?php endif;?> 
                            <?php do_action('rh_woo_after_single_image');?>                     
                        </div>
                        <div class="rh-360-sidebar tabletsblockdisplay summary woo_white_text_layout darkbgl padd15 whitecolorinner calcposright float_p_trigger float_trigger_clr_change">
                            <div class="hideonfloattablet">
                                <?php echo re_badge_create('labelsmall'); ?>

                                <div class="woo-title-area flowhidden mb20">
                                    <?php woocommerce_template_single_title();?>
                                </div>
                                <div class="mb20 font110"><?php woocommerce_template_single_excerpt();?></div>
                            </div>
                            <div class="rh-video-scroll-copy"></div>
                            <div class="re_wooinner_cta_wrapper"> 
                                <div class="woo-price-area redbrightcolor">
                                    <?php woocommerce_show_product_sale_flash();?>
                                    <?php woocommerce_template_single_price();?>

                                </div>
                                <?php do_action('rh_woo_single_product_price');?>
                                
                                <div class="woo-button-area mb30" id="woo-button-area"><?php do_action('rhwoo_template_single_add_to_cart');?></div>
                                <div class="clearfix"></div>
                                <?php rh_woo_code_zone('button');?> 
                                <div class="rhhidden showonfloat">
                                    <div class="rh-line mb10 mt10"></div> 
                                    <?php do_action( 'woocommerce_product_additional_information', $product ); ?> 
                                </div> 
                                <div class="hideonfloattablet">                        
                                    <div class="re_wooinner_info">
                                        <?php
                                            /**
                                             * woocommerce_single_product_summary hook. was removed in theme and added as functions directly in layout
                                             *
                                             * @dehooked woocommerce_template_single_title - 5
                                             * @dehooked woocommerce_template_single_rating - 10
                                             * @dehooked woocommerce_template_single_price - 10
                                             * @dehooked woocommerce_template_single_excerpt - 20
                                             * @dehooked woocommerce_template_single_add_to_cart - 30
                                             * @dehooked woocommerce_template_single_meta - 40
                                             * @dehooked woocommerce_template_single_sharing - 50
                                             * @hooked WC_Structured_Data::generate_product_data() - 60
                                             */
                                            do_action( 'woocommerce_single_product_summary' );
                                        ?>              
                                    </div>
                                </div> 
                                <?php rh_show_vendor_info_single(); ?>
                            </div> 
                        </div> 
                        <div class="rh-360-content-area tabletsblockdisplay woo_white_text_layout">
                            
                            <?php do_action('rh_woo_single_product_description');?>
                            <div class="woo-top-actions tabletsblockdisplay">
                                <div class="woo-button-actions-area pb25 pr5">
                                    <?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme');?>
                                    <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                                    <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                                    <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved);?>
                                    <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>           
                                        <?php 
                                            $cmp_btn_args = array(); 
                                            $cmp_btn_args['class']= 'rhwoosinglecompare';
                                            if(rehub_option('compare_woo_cats') != '') {
                                                $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                            }
                                        ?>                                                  
                                        <?php echo wpsm_comparison_button($cmp_btn_args); ?> 
                                    <?php endif;?>
                                </div> 
                            </div>
                            <div class="rh-flex-center-align woo_top_meta mobileblockdisplay mb10">
                                <?php if ( 'no' !== get_option( 'woocommerce_enable_review_rating' ) ):?> 
                                    <div class="floatleft mr15 disablefloatmobile rtlml15">
                                        <?php $rating_count = $product->get_rating_count();?>
                                        <?php if ($rating_count < 1):?>
                                            <span data-scrollto="#reviews" class="rehub_scroll cursorpointer greycolor font80"><?php esc_html_e("Add your review", "rehub-theme");?></span>
                                        <?php else:?>
                                            <?php woocommerce_template_single_rating();?>
                                        <?php endif;?>
                                    </div>
                                    <?php if($rating_count >=1) :?>
                                        <?php $rate_position = rh_get_product_position($post->ID, 'product_cat', '_wc_average_rating');?>
                                        <?php if (!empty($rate_position['rate_pos'])):?>
                                            <div class="clearbox mr25 rtlml15 rh-pr-rated-block">
                                                <span class="font80 fontnormal mobileblockdisplay">
                                                    <?php 
                                                        if($rate_position['rate_pos'] < 3){
                                                            echo '<i class="rhicon rhi-trophy-alt font150 orangecolor mr5 vertmiddle rtlml10"></i>';
                                                        }
                                                    ?> 
                                                    <?php esc_html_e( 'Product is rated as', 'rehub-theme' ); ?> <strong>#<?php echo ''.$rate_position['rate_pos'];?></strong> <?php esc_html_e( 'in category', 'rehub-theme' ); ?> <a href="<?php echo esc_url($rate_position['link']);?>"><?php echo esc_attr($rate_position['cat_name']); ?></a>                  
                                                </span>
                                            </div> 
                                        <?php endif;?>
                                    <?php endif;?>
                                <?php endif;?>

                                <span class="floatleft mr10 rtlml10 meta post-meta mt0 mb0 disablefloatmobile">
                                    <?php
                                    if(rehub_option('post_view_disable') != 1){ 
                                        $rehub_views = get_post_meta ($post->ID,'rehub_views',true); 
                                        if($rehub_views){
                                            echo '<span class="postview_meta mr10">'.$rehub_views.'</span>';
                                        }
                                        
                                    } 
                                    ?>                                     
                                </span> 
                                <?php $term_ids =  wc_get_product_terms($product->get_id(), 'store', array("fields" => "ids")); ?>
                                <?php if (!empty($term_ids) && ! is_wp_error($term_ids)) :?>
                                    <div class="woostorewrap flowhidden post-meta floatleft mb0 mt0 mr10 rtlml10">         
                                        <div class="store_tax">       
                                            #<?php WPSM_Woohelper::re_show_brand_tax(); //show brand taxonomy?>
                                        </div>  
                                    </div>
                                <?php endif;?>
                                <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
                                    <?php $sku = $product->get_sku();?>
                                    <?php 
                                        if(!$sku){
                                            $sku = esc_html__( 'N/A', 'rehub-theme' );
                                        };
                                    ?>                            
                                    <span class="sku_wrapper floatleft post-meta mt0 mb0"><?php esc_html_e( 'SKU:', 'rehub-theme' ); ?> <span class="sku"><?php echo esc_html($sku); ?></span></span>
                                <?php endif; ?>

                            </div>                                    
                            <div class="top_share_small top_share notextshare">
                                <?php woocommerce_template_single_sharing();?>
                            </div> 

                        </div>                    
                    </div>
                </div>             
                <?php if (count($videos) > 1):?>
                    <div class="darkbg flowhidden pt20 woo-video-top-area">
                        <div class="rh-container wide_width_restricted">
                            <div class="rh-360-content-area tabletsblockdisplay">
                                <?php echo woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link&rel=rh_top_gallery&wrapper=1&title=no&exceptfirst=1');?> 
                            </div>
                        </div>
                    </div>
                <?php endif;?>
                <?php $tabs = apply_filters( 'woocommerce_product_tabs', array() );

                if ( ! empty( $tabs ) ) : ?>
                    <?php 
                        unset($tabs['woo-custom-videos']); 
                        if($post_image_videos){
                            $attachment_ids = $product->get_gallery_image_ids();
                            if(!empty($attachment_ids)){
                                $tabs['woo-photo-booking'] = array(
                                    'title' => esc_html__('Photos', 'rehub-theme'),
                                    'priority' => '22',
                                    'callback' => 'woo_photo_booking_out'
                                );                                                                         
                            }
                        }                                                              
                        uasort( $tabs, '_sort_priority_callback' );                                 
                    ?>
                    <?php wp_enqueue_script('customfloatpanel');?>
                    <div id="contents-section-woo-area" class="rh-shadow5">
                        <div class="rh-container wide_width_restricted">
                            <ul class="rh-360-content-area tabletsblockdisplay smart-scroll-desktop clearfix contents-woo-area rh-big-tabs-ul">
                                <?php $i = 0; foreach ( $tabs as $key => $tab ) : ?>
                                    <li class="below-border rh-hov-bor-line <?php if($i == 0) echo 'active '; ?>rh-big-tabs-li <?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                                        <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?></a>
                                    </li>
                                    <?php $i ++;?>
                                <?php endforeach; ?>
                            </ul> 
                        </div> 
                    </div>  
                    <div class="woo-content-area">
                        
                        <?php foreach ( $tabs as $key => $tab ) : ?>
                            <div class="content-woo-section pt30 pb20 content-woo-section--<?php echo esc_attr( $key ); ?>" id="section-<?php echo esc_attr( $key ); ?>">
                                <div class="rh-container wide_width_restricted">
                                    <div class="rh-360-content-area tabletsblockdisplay">
                                        <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>                           

                        <div class="flowhidden rh-float-panel darkbg woo_white_text_layout" id="float-panel-woo-area">
                            <div class="rh-container wide_width_restricted rh-flex-center-align pt10 pb10">
                                <div class="float-panel-woo-image">
                                    <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'thumb'=> true, 'width'=> 50, 'height'=> 50));?>
                                </div>
                                <div class="float-panel-woo-info pl20">
                                    <div class="float-panel-woo-title rehub-main-font mb5 font110 whitecolor">
                                        <?php the_title();?>
                                    </div>
                                    <ul class="float-panel-woo-links list-unstyled list-line-style font80 fontbold lineheight15">
                                        <?php foreach ( $tabs as $key => $tab ) : ?>
                                            <li class="<?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                                                <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?></a>
                                            </li>                                                
                                        <?php endforeach; ?>                                        
                                    </ul>
                                </div>
                                <div class="float-panel-woo-btn rh-flex-columns rh-flex-right-align rh-flex-nowrap desktabldisplaynone">
                                    <div class="float-panel-woo-price rh-flex-center-align font120 rh-flex-right-align whitecolor fontbold"><?php woocommerce_template_single_price();?></div>
                                    <div class="float-panel-woo-button rh-flex-center-align rh-flex-right-align">       
                                        <?php if(!rehub_option('woo_btn_inner_disable')) :?>         
                                            <?php if ( $product->add_to_cart_url() !='') : ?>
                                                <?php if($product->get_type() == 'variable' || $product->get_type() == 'booking') {
                                                    $url = '#woo-button-area';
                                                }else{
                                                    $url = esc_url( $product->add_to_cart_url() );
                                                }

                                                ?>
                                                <?php  echo apply_filters( 'woo_float_add_to_cart_link',
                                                    sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn btn_offer_block single_add_to_cart_button %s %s product_type_%s"%s %s>%s</a>',
                                                    $url,
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
                                        <?php rh_woo_code_zone('float');?>
                                    </div>                                        
                                </div>                                    
                            </div>                           
                        </div>
                    </div>
                <?php endif; ?> 

                <div class="other-woo-area clearfix">
                    <div class="related-woo-section pt30 pb20">
                        <div class="rh-container wide_width_restricted">
                            <div class="rh-360-content-area tabletsblockdisplay">
                                <?php
                                    /**
                                     * woocommerce_after_single_product_summary hook.
                                     *
                                     * @hooked woocommerce_output_product_data_tabs - 10
                                     * @hooked woocommerce_upsell_display - 15
                                     * @hooked woocommerce_output_related_products - 20
                                     */
                                    do_action( 'woocommerce_after_single_product_summary' );
                                ?>
                                <!-- Related -->
                                    <?php include(rh_locate_template( 'woocommerce/single-product/related-with-sidebar.php' ) ); ?>                         
                                <!-- /Related --> 

                                <!-- Upsell -->
                                    <?php include(rh_locate_template( 'woocommerce/single-product/upsell-with-sidebar.php' ) ); ?>
                                <!-- /Upsell -->  
                            </div>
                        </div>
                    </div> 
                </div>               

            </div><!-- #product-<?php the_ID(); ?> -->

            <?php do_action( 'woocommerce_after_single_product' ); ?>
        <?php endwhile; // end of the loop. ?>
        <?php do_action( 'woocommerce_after_main_content' ); ?>               
    </div>
</div>  

<?php rh_woo_code_zone('bottom');?>