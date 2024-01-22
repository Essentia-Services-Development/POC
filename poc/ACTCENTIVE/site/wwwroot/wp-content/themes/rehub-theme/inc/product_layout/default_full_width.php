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
        <div class="main-side page clearfix full_width woo_default_full_width" id="content">
            <div class="post" id="contents-section-woo-area">
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
                        <div class="woo-image-part position-relative">
                            <?php woocommerce_show_product_sale_flash();?>
                            <?php $width_woo_main = 540; $height_woo_main = 540; $columns_thumbnails = 1?>
                            <?php include(rh_locate_template('woocommerce/single-product/product-image.php')); ?>
                            <?php do_action('rh_woo_after_single_image');?>
                        </div>

                        <div class="summary entry-summary">
                            <div class="re_wooinner_info mb30">
                                <div class="re_wooinner_title_compact flowhidden">
                                    <?php echo re_badge_create('labelsmall'); ?>
                                    <?php woocommerce_template_single_title();?>
                                    <?php woocommerce_template_single_rating();?>
                                    <?php do_action('rh_woo_single_product_title');?>
                                </div>
                                <div class="clear"></div>
                                <?php echo wpsm_reviewbox(array('compact'=>1, 'id'=> $post->ID, 'scrollid'=>'tab-title-description'));?>                                
                                <?php rh_show_vendor_info_single();?>
                                <?php rh_woo_code_zone('content');?>
                                <?php woocommerce_template_single_excerpt();?>
                                <?php do_action('rh_woo_single_product_description');?>                  
                            </div>
                            <div class="re_wooinner_cta_wrapper mb35">
                                <div class="woo-price-area mb10"><?php woocommerce_template_single_price();?></div>
                                <?php do_action('rh_woo_single_product_price');?>
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
                            </div>
                            <?php rh_woo_code_zone('button');?>
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
                            <div class="mb20"><?php woocommerce_template_single_meta();?></div>
                            <?php woocommerce_template_single_sharing();?>

                        </div><!-- .summary -->

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
                        <?php woocommerce_output_product_data_tabs();?>
                        </div>

                        <!-- Related -->
                            <?php include(rh_locate_template( 'woocommerce/single-product/full-width-related-no-margin.php' ) ); ?>                        
                        <!-- /Related -->

                        <!-- Upsell -->
                            <?php include(rh_locate_template( 'woocommerce/single-product/full-width-upsell-no-margin.php' ) ); ?>
                        <!-- /Upsell --> 

                        <?php $tabs = apply_filters( 'woocommerce_product_tabs', array() );?>

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
                                    <ul class="float-panel-woo-tabs list-unstyled list-line-style font80 fontbold lineheight15">
                                        <?php foreach ( $tabs as $key => $tab ) : ?>
                                            <li class="<?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                                                <?php $tab_title = $tab['title'];?>
                                                <a href="#tab-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html($tab_title), $key ); ?></a>
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