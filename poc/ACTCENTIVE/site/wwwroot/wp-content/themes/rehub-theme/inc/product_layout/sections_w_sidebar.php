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
<div class="sections_w_sidebar lightgreybg pb30" id="content">
    <?php echo rh_generate_incss('section_w_sidebar');?>
    <div class="post mb0">
        <?php while ( have_posts() ) : the_post(); ?> 
            <div id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

                <?php wp_enqueue_script('stickysidebar');?>
                <div class="content-woo-area rh-container flowhidden rh-stickysidebar-wrapper">                                
                    <div class="rh-336-content-area tabletblockdisplay floatleft pt15 rh-sticky-container">
                        <?php do_action( 'woocommerce_before_main_content' );?>                       
                        <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>
                        <?php do_action( 'woocommerce_before_single_product' );?>                         
                        <div class="woo-title-area mb10 flowhidden">

                            <div class="floatleft font90">
                                <?php echo re_badge_create('labelsmall'); ?>
                                <?php woocommerce_template_single_title();?>
                            </div>
                            <div class="floatright ml30 rtlmr30"><?php woocommerce_template_single_rating();?></div>
                        </div> 
                        <?php do_action('rh_woo_single_product_title');?>                       
                        <div class="woo-image-part position-relative"><?php woocommerce_show_product_sale_flash();?>
                            <?php $width_woo_main = 840; $height_woo_main = 560; $columns_thumbnails = 10; $noresize = true;?>
                            <?php include(rh_locate_template('woocommerce/single-product/product-image.php')); ?>
                            <?php do_action('rh_woo_after_single_image');?>
                        </div> 

                        <div class="padd20 summary border-grey whitebg rh_vert_bookable mb20 rhhidden tabletblockdisplay"> 
                            <?php echo rh_generate_incss('vertbookable');?>
                            <div class="float_p_trigger woo-price-area"><?php woocommerce_template_single_price();?></div>
                            <?php do_action('rh_woo_single_product_price');?>
                            <?php if( wp_is_mobile() ):?>
                                <div class="woo-button-area mb30">
                                    <?php do_action('rhwoo_template_single_add_to_cart');?>
                                </div>
                            <?php endif;?>
                            <?php rh_woo_code_zone('button');?>
                            <div class="woo-button-actions-area tabletblockdisplay pt15 border-top mt15">
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
                            <?php rh_show_vendor_info_single(); ?>                                                       
                        </div>                         

                        <?php $tabs = apply_filters( 'woocommerce_product_tabs', array() );

                        if ( ! empty( $tabs ) ) : ?>
                            <div id="contents-section-woo-area">
                                <ul class="smart-scroll-desktop mb20 whitebg clearfix contents-woo-area rh-big-tabs-ul">
                                    <?php $i = 0; foreach ( $tabs as $key => $tab ) : ?>
                                        <li class="rh-hov-bor-line <?php if($i == 0) echo 'active '; ?>rh-big-tabs-li <?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                                            <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?></a>
                                        </li>
                                        <?php $i ++;?>
                                    <?php endforeach; ?>
                                </ul> 
                            </div> 
                        
                        <?php endif;?>                       

                        <div class="padd20 mb20 whitebg re_wooinner_info">
                            <?php rh_woo_code_zone('content');?>
                            <?php woocommerce_template_single_excerpt();?>
                            <?php do_action('rh_woo_single_product_description');?>
                        </div> 

                        <?php foreach ( $tabs as $key => $tab ) : ?>
                            <div class="padd20 mb20 whitebg content-woo-section--<?php echo esc_attr( $key ); ?>" id="section-<?php echo esc_attr( $key ); ?>">
                                <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                            </div>
                        <?php endforeach; ?> 
                        <?php wp_enqueue_script('customfloatpanel');?>
                        <div class="flowhidden rh-float-panel" id="float-panel-woo-area">
                            <div class="rh-container rh-flex-eq-height rh-flex-nowrap">
                                <div class="pt5 pb5 rh-336-content-area rh-flex-center-align float-panel-img-wrap">
                                    <div class="float-panel-woo-image hideonsmobile">
                                        <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'thumb'=> true, 'width'=> 50, 'height'=> 50));?>
                                    </div>
                                    <div class="float-panel-woo-info wpsm_pretty_colored rh-line-left pl15 ml15">
                                        <div class="float-panel-woo-title rehub-main-font mb5 font110">
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
                                </div>
                                <div class="darkbg float-panel-woo-btn rh-flex-center-align mb0 rh-336-sidebar rh-flex-right-align">
                                    <div class="whitecolor float-panel-woo-price font120 margincenter"><?php woocommerce_template_single_price();?>
                                    </div> 
                                    <div class="float-panel-woo-button rhhidden rh-flex-right-align tabletblockdisplay mb0">
                                        <?php if(!rehub_option('woo_btn_inner_disable')) :?>
                                            <?php if ( $product->add_to_cart_url() !='') : ?>
                                                <?php if($product->get_type() == 'variable' || $product->get_type() == 'booking') {
                                                    $url = '#woo-button-area';
                                                }else{
                                                    $url = esc_url( $product->add_to_cart_url() );
                                                }

                                                ?>
                                                <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
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
                        <div class="other-woo-area clearfix">
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
                        <?php do_action( 'woocommerce_after_main_content' ); ?>             
                    </div>  
                    <div class="rh-336-sidebar mt20 floatright rh-sticky-container tabletblockdisplay">
                        <div class="padd20 summary border-grey whitebg rh_vert_bookable stickyonfloatpanel mb20"> 
                            <div class="float_p_trigger woo-price-area"><?php woocommerce_template_single_price();?></div>
                            <?php do_action('rh_woo_single_product_price');?>
                            <?php if(!rehub_option('woo_btn_inner_disable')) :?>
                                <?php if( !wp_is_mobile() ):?>
                                <div class="woo-button-area mb30" id="woo-button-area">
                                    <?php do_action('rhwoo_template_single_add_to_cart');?>
                                </div>
                                <?php endif;?>
                            <?php endif ;?>
                            <?php echo rh_woo_code_zone('button');?>
                            <div class="mb5"><?php woocommerce_template_single_meta();?></div>
                            <?php woocommerce_template_single_sharing();?>  
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
                            <div class="woo-button-actions-area tabletblockdisplay pt15 border-top mt20">
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

                        <?php echo wpsm_reviewbox(array('compact'=>1, 'id'=> $post->ID, 'scrollid'=>'tab-title-description'));?>                         

                        <?php rh_show_vendor_info_single(); ?>

                    </div>                      
                </div>

            </div><!-- #product-<?php the_ID(); ?> -->

            <?php do_action( 'woocommerce_after_single_product' ); ?>

        <?php endwhile; // end of the loop. ?>               
    </div>
</div>  
 

<?php rh_woo_code_zone('bottom');?>