<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $product;?>
<?php                             
    if ( post_password_required() ) {
        echo '<div class="rh-container"><div class="rh-content-wrap clearfix"><div class="main-side clearfix full_width" id="content"><div class="post text-center">';
            echo get_the_password_form();
        echo '</div></div></div></div>';
        return;
    }
?>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side page clearfix full_width" id="content">
            <div class="post">
                <?php do_action( 'woocommerce_before_main_content' );?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <div id="product-<?php echo (int)$post->ID; ?>" <?php post_class(); ?>>
                        <?php
                            /**
                             * woocommerce_before_single_product hook.
                             *
                             * @hooked wc_print_notices - 10
                             */
                             do_action( 'woocommerce_before_single_product' );

                        ?>                        
                        <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?> 
                        <?php  wp_enqueue_script('modulobox');  wp_enqueue_style('modulobox'); ?>
                        <div class="rh-stickysidebar-wrapper" id="woostackwrapper"> 
                            <?php echo rh_generate_incss('woostack');?>
                            <div class="woo-image-part position-relative">
                                <div class="modulo-lightbox">
                                    <figure class="text-center" id="photo_stack_main_img">
                                        <?php  $badge = get_post_meta($post->ID, 'is_editor_choice', true); ?>
                                        <?php if ($badge !='' && $badge !='0') :?> 
                                            <?php echo re_badge_create('ribbon'); ?>
                                        <?php else:?>                                        
                                            <?php woocommerce_show_product_sale_flash();?>
                                        <?php endif;?>
                                        <?php           
                                            $image_id = get_post_thumbnail_id($post->ID);  
                                            $image_url = wp_get_attachment_image_src($image_id,'full');
                                            $image_url = $image_url[0]; 
                                        ?> 
                                        <a data-rel="rh_top_gallery" id="navigation-image-1" href="<?php echo esc_url($image_url);?>" target="_blank" data-thumb="<?php echo esc_url($image_url);?>">            
                                        
                                            <?php echo WPSM_image_resizer::show_wp_image('full', '', array('lazydisable'=>true, 'loading'=>'eager')); ?>
                                        </a>
                                    </figure>
                                    <?php $post_image_gallery = $product->get_gallery_image_ids();?>
                                    <?php $post_image_videos = get_post_meta( $post->ID, 'rh_product_video', true );?>
                                    <div class="mt20 mb15 smart-scroll-mobile three-col-mob">
                                        <div id="rh-product-images-dots" class="hideonstablet">
                                            <?php echo rh_generate_incss('imagenavdot');?>
                                            <div class="mr5 ml5 mb15">
                                                <span class="rhdot margincenter rehub_scroll" data-scrollto="#navigation-image-1"></span>
                                            </div>
                                            <?php if(!empty($post_image_gallery)) :?> 
                                                <?php foreach($post_image_gallery as $key=>$image_gallery):?>
                                                    <div class="mr5 ml5 mb15">
                                                        <span class="rhdot margincenter rehub_scroll" data-scrollto="#navigation-image-<?php echo esc_attr($key+2);?>"></span>
                                                    </div>
                                                <?php endforeach;?>
                                            <?php endif;?>
                                            <?php if(!empty($post_image_videos)) :?> 
                                                <div class="mr5 ml5 mb15">
                                                    <span class="rehub_scroll" data-scrollto="#navigation-image-video-1"><i class="rhicon rhi-play-circle"></i></span>
                                                </div>
                                            <?php endif;?>
                                        </div>
                                        <?php if(!empty($post_image_gallery)) :?> 
                                            <?php foreach($post_image_gallery as $key=>$image_gallery):?>
                                                <?php if(!$image_gallery) continue;?>
                                                <?php $image = wp_get_attachment_image_src($image_gallery, 'full'); $imgurl = (!empty($image[0])) ? $image[0] : ''; ?>
                                                    <a data-rel="rh_top_gallery" id="navigation-image-<?php echo esc_attr($key+2);?>" href="<?php echo esc_url($imgurl);?>" target="_blank" class="rh-flex-center-align mb20 col_item rh-flex-justify-center" data-title="<?php echo esc_attr(get_post_field( 'post_excerpt', $image_gallery));?>">
                                                        <?php echo WPSM_image_resizer::show_wp_image('full', $image_gallery, array('nofeatured'=>1)); ?>
                                                    </a>                
                                            <?php endforeach;?> 
                                        <?php endif;?> 
                                        <?php echo woo_custom_video_output('class=col_item rh-flex-justify-center rh-flex-center-align mt15 rh_videothumb_link&rel=rh_top_gallery&wrapper=no&title=no&fullsize=1&id=navigation-image-video');?>                      
                                    </div>                                                   
                                    
                                    <?php do_action('rh_woo_after_single_image');?> 
                                </div>
                                <?php do_action( 'rehub_360_product_image' ); ?>
                            </div>


                            <div class="summary entry-summary sticky-psn" style="top:80px">

                                <div class="re_wooinner_info mb30 pb10">
                                    <div class="re_wooinner_title_compact flowhidden">
                                        <?php woocommerce_template_single_title();?>
                                        <?php woocommerce_template_single_rating();?>
                                        <?php do_action('rh_woo_single_product_title');?>
                                        <div class="clearfix"></div>
                                        <div class="mt20 mb20 woo-price-area rehub-btn-font rehub-main-color font120 fontbold"><?php woocommerce_template_single_price();?></div>
                                        <?php do_action('rh_woo_single_product_price');?>
                                    </div>
                                    <div class="clear"></div>                              
                                    <?php rh_show_vendor_info_single();?>
                                    <?php rh_woo_code_zone('content');?>
                                    <?php woocommerce_template_single_excerpt();?>
                                    <?php do_action('rh_woo_single_product_description');?>                  
                                </div>
                                <div class="re_wooinner_cta_wrapper mb30">
                                    <div class="woo-button-area mb30" id="woo-button-area">
                                        <div><?php do_action('rhwoo_template_single_add_to_cart');?></div>
                                        <div class="button_action mt30">
                                            <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                                            <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                                            <?php $wishlist = RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved);?>
                                            <?php if($wishlist):?>
                                                <div class="floatleft mr15 def_btn rh-sq-icon-btn-big rh-flex-center-align rh-flex-justify-center">
                                                    <?php echo ''.$wishlist;?>  
                                                </div>
                                            <?php endif;?>
                                            <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>
                                                <span class="compare_for_grid mr15 def_btn floatleft rh-sq-icon-btn-big rh-flex-center-align rh-flex-justify-center">            
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
                                            <div class="clearfix"></div>                                                         
                                        </div> 
                                    </div> 
                                    <?php rh_woo_code_zone('button');?> 
                                </div>
                                <div class="clearfix"></div>
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
                                <div class="mb20 mt30"><?php woocommerce_template_single_meta();?></div>
                                <?php woocommerce_template_single_sharing();?>

                                </div><!-- .summary -->
                            </div>
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
                        <div class="clear"></div>
                        <div class="mt25">
                            <?php $tabs = apply_filters( 'woocommerce_product_tabs', array() ); 
                            unset($tabs['woo-custom-videos']);                        
                            if ( ! empty( $tabs ) ) : ?>
                                <div id="contents-section-woo-area" class="border-grey-bottom flowhidden whitebg">
                                    <div class="rh-container pl0 pr0">
                                        <ul class="smart-scroll-desktop clearfix contents-woo-area rh-big-tabs-ul text-center">
                                            <?php $i = 0; foreach ( $tabs as $key => $tab ) : ?>
                                                <li class="rh-hov-bor-line below-border <?php if($i == 0) echo 'active '; ?>rh-big-tabs-li <?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                                                    <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?></a>
                                                </li>
                                                <?php $i ++;?>
                                            <?php endforeach; ?>
                                        </ul> 
                                    </div> 
                                </div>         
                            <?php endif;?>                                      

                            <div class="woo-content-area-full">
                                <div class="content-woo-area">
                                    <?php foreach ( $tabs as $key => $tab ) : ?>
                                        <div class="content-woo-section pt30 pb20 content-woo-section--<?php echo esc_attr( $key ); ?>" id="section-<?php echo esc_attr( $key ); ?>"><div class="rh-container rh-shadow3">
                                            <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                                        </div></div>
                                    <?php endforeach; ?>                                                  

                                </div>
                            </div> 
                        </div>

                        <!-- Related -->
                            <?php include(rh_locate_template( 'woocommerce/single-product/full-width-related-no-margin.php' ) ); ?>                        
                        <!-- /Related -->

                        <!-- Upsell -->
                            <?php include(rh_locate_template( 'woocommerce/single-product/full-width-upsell-no-margin.php' ) ); ?>
                        <!-- /Upsell --> 

                        <?php wp_enqueue_script('customfloatpanel');?> 
                        <div class="flowhidden rh-float-panel" id="float-panel-woo-area">
                            <div class="rh-container rh-flex-center-align pt10 pb10">
                                <div class="float-panel-woo-image">
                                    <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'thumb'=> true, 'width'=> 50, 'height'=> 50));?>
                                </div>
                                <div class="float-panel-woo-info wpsm_pretty_colored rh-line-left pl15 ml15">
                                    <div class="float-panel-woo-title rehub-main-font mb5 font110">
                                        <?php the_title();?>
                                    </div>
                                    <ul class="float-panel-woo-links list-unstyled list-line-style font80 fontbold lineheight15">
                                        <?php foreach ( $tabs as $key => $tab ) : ?>
                                            <li class="<?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                                                <?php $tab_title = $tab['title'];?>
                                                <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html($tab_title), $key ); ?></a>
                                            </li>                                                
                                        <?php endforeach; ?>                                        
                                    </ul>                                  
                                </div>
                                <div class="float-panel-woo-btn rh-flex-columns rh-flex-right-align rh-flex-nowrap">
                                    <div class="float-panel-woo-price fontbold rh-flex-center-align font120 rh-flex-right-align">
                                        <?php woocommerce_template_single_price();?>
                                    </div>
                                    <div class="float-panel-woo-button rh-flex-center-align rh-flex-right-align">
                                        <?php if(!rehub_option('woo_btn_inner_disable')) :?>
                                            <?php if(!empty($itemsync)):?>
                                                <a href="#section-woo-ce-pricelist" class="single_add_to_cart_button rehub_scroll">
                                                    <?php if(rehub_option('rehub_btn_text_aff_links') !='') :?>
                                                        <?php echo rehub_option('rehub_btn_text_aff_links') ; ?>
                                                    <?php else :?>
                                                        <?php esc_html_e('Choose offer', 'rehub-theme') ?>
                                                    <?php endif ;?>
                                                </a> 
                                            <?php else:?>
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
                                            <?php endif;?>
                                        <?php endif;?> 
                                        <?php rh_woo_code_zone('float');?>                                                            
                                    </div>                                        
                                </div>                                    
                            </div>                           
                        </div>                                               

                    </div><!-- #product-<?php echo (int)$post->ID; ?> -->

                    <?php do_action( 'woocommerce_after_single_product' ); ?>
                <?php endwhile; // end of the loop. ?> 
                <?php do_action( 'woocommerce_after_main_content' ); ?>                             
            </div>
        </div>  
        <!-- /Main Side --> 

    </div>
</div>
<!-- /CONTENT -->  
<?php rh_woo_code_zone('bottom');?>