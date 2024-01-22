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
<div class="side_block_layout side_block_light" id="content">
    <div class="post">
        <?php do_action( 'woocommerce_before_main_content' );?>
        <?php while ( have_posts() ) : the_post(); ?>
            <div id="product-<?php echo (int)$post->ID; ?>" <?php post_class(); ?>>

                <div class="top-woo-area" id="rh_woo_layout_inimage">
                        <?php 
                            if (rehub_option('rehub_third_color')) {
                                $maincolor = rehub_option('rehub_third_color');
                            }   
                            else if (rehub_option('rehub_custom_color')) {
                                $maincolor = rehub_option('rehub_custom_color');
                            } 
                            else {
                                $maincolor = REHUB_MAIN_COLOR;
                            }?>
                        <style scoped>
                            #rh_woo_layout_inimage{background: <?php echo hex2rgba($maincolor, 0.05);?>}
                            .woocommerce-breadcrumb span.delimiter+a{background:transparent;}
                            .woocommerce .summary table.shop_attributes{margin: 10px 0; font-size:90%; border:none; display: block; max-height:430px; overflow-y:auto;}
                            .woocommerce .summary table.shop_attributes th, .woocommerce .summary table.shop_attributes td{border:none;padding: 5px 10px; text-align:inherit}
                            .woocommerce .summary table.shop_attributes th{padding-left:0;}
                            .woocommerce .summary table.shop_attributes th:after{content: ":";}
                            .woo_single_excerpt{color:rgb(0 0 0 / 50%);}
                        </style>
                    <div class="rh-container position-static flowhidden pt15 pb30">                                   
                        <div class="rh-360-content-area tabletsblockdisplay">
                            <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>
                            <?php do_action( 'woocommerce_before_single_product' ); ?> 
                            <div class="woo-title-area mb10 flowhidden">
                                <?php woocommerce_template_single_title();?>
                            </div>
                            <?php do_action('rh_woo_single_product_title');?>                      
                        </div>
                        <div class="rh-360-sidebar tabletsblockdisplay summary whitebg rh-shadow3 rehub-sec-smooth calcposright float_p_trigger">
                            <div class="woo-image-part position-relative modulo-lightbox hideonfloattablet">
                                <?php  $badge = get_post_meta($post->ID, 'is_editor_choice', true); ?>
                                <?php if ($badge !='' && $badge !='0') :?> 
                                    <?php echo re_badge_create('ribbonleft'); ?>
                                <?php endif;?>

                                <?php 
                                    $post_image_videos = get_post_meta( $post->ID, 'rh_product_video', true );
                                    if($post_image_videos){
                                        echo woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link&rel=rh_top_gallery&wrapper=no&title=no&onlyone=yes');
                                    }else{
                                        echo '<div class="pt15 pl15 pr15 text-center">';
                                        $width_woo_main = 300; $height_woo_main = 240;
                                        include(rh_locate_template('woocommerce/single-product/product-image.php'));
                                        echo '</div>';
                                    }
                                ?>
                                <?php do_action('rh_woo_after_single_image');?>
                            </div>
                            <div class="re_wooinner_cta_wrapper padd20"> 
                                <div class="woo-price-area">
                                    <?php woocommerce_show_product_sale_flash();?>
                                    <?php woocommerce_template_single_price();?>

                                </div>
                                <?php do_action('rh_woo_single_product_price');?>
                                
                                <div class="woo-button-area mb30" id="woo-button-area"><?php do_action('rhwoo_template_single_add_to_cart');?></div>
                                <div class="clearfix"></div>
                                <?php rh_woo_code_zone('button');?> 
                                <div class="rh-line mb10 mt10"></div> 
                                <?php do_action( 'woocommerce_product_additional_information', $product ); ?>                           
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
                                <div class="mb15 mt15 pt15 border-top font90 hideonfloattablet"><?php woocommerce_template_single_meta();?></div>
                                <div class="top_share_small top_share notextshare">
                                    <?php woocommerce_template_single_sharing();?>
                                </div> 
                            </div> 
                        </div> 
                        <div class="rh-360-content-area tabletsblockdisplay">
                            <div class="mb20 font120 woo_single_excerpt fontbold"><?php woocommerce_template_single_excerpt();?></div>
                            <?php do_action('rh_woo_single_product_description');?>
                            <?php rh_woo_code_zone('content');?> 
                            <div class="rh-flex-center-align woo_top_meta mobileblockdisplay mb20">
                                <?php if ( 'no' !== get_option( 'woocommerce_enable_review_rating' ) ):?> 
                                    <div class="floatleft mr15 disablefloatmobile rtlml15">
                                        <?php $rating_count = $product->get_rating_count();?>
                                        <?php if ($rating_count < 1):?>
                                            <span data-scrollto="#reviews" class="rehub_scroll cursorpointer font80 greycolor"><?php esc_html_e("Add your review", "rehub-theme");?></span>
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

                                <span class="floatleft meta post-meta mt0 mb0 disablefloatmobile">
                                    <?php
                                    if(rehub_option('post_view_disable') != 1){ 
                                        $rehub_views = get_post_meta ($post->ID,'rehub_views',true); 
                                        if($rehub_views){
                                            echo '<span class="postview_meta mr10">'.$rehub_views.'</span>';
                                        }
                                    } 
                                    ?>                                     
                                </span>                                        
                            </div>
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
                            <?php rh_show_vendor_info_single(); ?>
                        </div>                    
                    </div>
                </div>                

                <?php $tabs = apply_filters( 'woocommerce_product_tabs', array() );

                if ( ! empty( $tabs ) ) : ?>
                    <?php 
                        unset($tabs['additional_information']); 
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
                        <div class="rh-container">
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

                        <?php   
                            $prosvalues = get_post_meta($post->ID, '_review_post_pros_text', true);
                        ?> 
                        <?php if(!empty($prosvalues)):?>
                            <!-- PROS CONS BLOCK-->
                            <div class="content-woo-section pt30 pb10">
                                <div class="rh-container">
                                    <div class="rh-360-content-area tabletsblockdisplay">                                                  
                                        <?php                             
                                            $criteriascore = rehub_exerpt_function(array('reviewcriterias'=> 'editor'));
                                        ?>
                                        <div class="padd20 border-lightgrey woo_comment_text_pros mt15">
                                            <span class="mb10 blockstyle fontbold font120 mb20">
                                                <?php esc_html_e('You will get:', 'rehub-theme') ?>
                                            </span>
                                            <?php $prosvalues = explode(PHP_EOL, $prosvalues);?>
                                            <?php foreach ($prosvalues as $prosvalue) {
                                                if(!$prosvalue) continue;
                                                echo '<span class="blockstyle mb10"><i class="rhicon rhi-check mr10 rtlml10 greencolor"></i>'.$prosvalue.'</span>';
                                            }?>
                                        </div>                                
                                    </div>
                                </div>
                            </div>
                        <?php endif;?> 
                        
                        <?php foreach ( $tabs as $key => $tab ) : ?>
                            <div class="content-woo-section pt30 pb20 content-woo-section--<?php echo esc_attr( $key ); ?>" id="section-<?php echo esc_attr( $key ); ?>">
                                <div class="rh-container">
                                    <div class="rh-360-content-area tabletsblockdisplay">
                                        <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>                           

                        <div class="flowhidden rh-float-panel darkbg woo_white_text_layout" id="float-panel-woo-area">
                            <div class="rh-container rh-flex-center-align pt10 pb10">
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
                        <div class="rh-container">
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