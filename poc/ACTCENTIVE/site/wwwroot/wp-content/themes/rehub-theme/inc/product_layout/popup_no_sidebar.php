<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $post; ?>
<?php                             
    if ( post_password_required() ) {
        echo '<div class="rh-container"><div class="rh-content-wrap clearfix"><div class="main-side clearfix full_width" id="content"><div class="post text-center">';
            echo get_the_password_form();
        echo '</div></div></div></div>';
        return;
    }
?>
<!-- CONTENT -->
<?php wp_enqueue_style('rhwoosingle');?>
<div class="rh-container-pop woocommerce"> 
    <div class="clearfix">
        <!-- Main Side -->
        <div class="woo_default_w_sidebar" id="content">
            <div class="post">
                <?php do_action( 'woocommerce_before_main_content' ); ?>
                    <div id="product-<?php the_ID(); ?>" <?php post_class('product'); ?>>
                        <?php
                            /**
                             * woocommerce_before_single_product hook.
                             *
                             * @hooked wc_print_notices - 10
                             */
                             do_action( 'woocommerce_before_single_product' );
                        ?>                        

                        <div class="woo-image-part position-relative">
                            <?php
                                /**
                                 * woocommerce_before_single_product_summary hook.
                                 *
                                 * @hooked woocommerce_show_product_sale_flash - 10
                                 * @hooked woocommerce_show_product_images - 20
                                 */
                                do_action( 'woocommerce_before_single_product_summary' );
                            ?>
                            <?php $post_image_videos = get_post_meta( $post->ID, 'rh_product_video', true ); if ($post_image_videos):?>
                                <div class="rh_mini_thumbs compare-full-thumbnails">
                                    <div class="fontbold pb10"><i class="mr10 rehub-main-color rhi-video rhicon"></i><?php esc_html_e('Videos', 'rehub-theme');?></div>
                                    <?php echo woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link&rel=rh_top_gallery&wrapper=1&title=no');?> 
                                </div>
                            <?php endif;?>
                        </div>

                        <div class="summary entry-summary">
                            <div class="re_wooinner_info mb30">
                                <div class="re_wooinner_title_compact flowhidden">
                                    <?php echo re_badge_create('labelsmall'); ?>
                                    <?php woocommerce_template_single_title();?>
                                    <?php woocommerce_template_single_rating();?>
                                    <?php do_action('rh_woo_single_product_title');?>
                                    <div class="woo-button-actions-area mb15 pl5 pr5 pb5">
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
                                <div class="clear"></div>                               
                                <?php rh_show_vendor_info_single();?>
                                <?php rh_woo_code_zone('content');?>
                                <?php woocommerce_template_single_excerpt();?>
                                <?php do_action('rh_woo_single_product_description');?>                  
                            </div>
                            <div class="re_wooinner_cta_wrapper mb20">
                                <div class="woo-price-area mb10"><?php woocommerce_template_single_price();?></div>
                                <?php do_action('rh_woo_single_product_price');?>
                                <div class="woo-button-area wooquickviewbtn"><?php do_action('rhwoo_template_single_add_to_cart');?></div>
                                <?php 
                                    $code_incart = get_post_meta($post->ID, 'rh_code_incart', true );
                                    if (defined('\ContentEgg\PLUGIN_PATH') && !$code_incart){
                                        $attsce = array();
                                        $attsce['template']= 'custom/all_merchant_widget';
                                        $attsce['post_id'] = get_the_ID();
                                        echo \ContentEgg\application\BlockShortcode::getInstance()->viewData($attsce);
                                    }
                                ?>
                                <?php rh_woo_code_zone('button');?> 
                            </div>

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
                            <div class="mt20"><a href="<?php echo get_the_permalink($post->ID);?>" class="def_btn fontnormal fullpagelink"><?php esc_html_e('Open full product page', 'rehub-theme');?></a></div>
                        </div><!-- .summary -->                                          

                        <?php
                            /**
                             * woocommerce_after_single_product_summary hook.
                             *
                             * @hooked woocommerce_output_product_data_tabs - 10
                             * @hooked woocommerce_upsell_display - 15
                             * @hooked woocommerce_output_related_products - 20
                             */
                            // do_action( 'woocommerce_after_single_product_summary' );
                        ?>

                    </div><!-- #product-<?php the_ID(); ?> -->

                    <?php do_action( 'woocommerce_after_single_product' ); ?>

                <?php do_action( 'woocommerce_after_main_content' ); ?>                             
            </div>
        </div>  
        <!-- /Main Side --> 
    </div>
</div>
<!-- /CONTENT -->
<?php rh_woo_code_zone('bottom');?>