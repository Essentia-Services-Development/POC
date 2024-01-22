<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<?php global $product, $post; ?>
<?php
if (post_password_required()) {
    echo '<div class="rh-container"><div class="rh-content-wrap clearfix"><div class="main-side clearfix full_width" id="content"><div class="post text-center">';
    echo get_the_password_form();
    echo '</div></div></div></div>';
    return;
}
?>
<div class="full_width woo_full_width_advanced lightgreybg" id="content">
    <?php echo rh_generate_incss('fullwidthmarketplace'); ?>
    <div class="rh-container">
        <div class="rh-content-wrap clearfix">
            <?php if (!rehub_option('rehub_disable_breadcrumbs')) {
                woocommerce_breadcrumb();
            } ?>
            <div class="post">
                <?php do_action('woocommerce_before_main_content'); ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php do_action('woocommerce_before_single_product'); ?>
                    <div id="product-<?php echo (int)$post->ID; ?>" <?php post_class(); ?>>

                        <div class="top-woo-area rh-flex-columns pt15 pr15 pl15 pb15 mobilepadding whitebg">
                            <div class="rh-300-content-area tabletblockdisplay">
                                <div class="wpsm-one-half tabletblockdisplay">
                                    <div class="woo-image-part position-relative">
                                        <?php woocommerce_show_product_sale_flash(); ?>
                                        <?php $width_woo_main = 760;
                                        $height_woo_main = 540;
                                        $columns_thumbnails = 1 ?>
                                        <?php include(rh_locate_template('woocommerce/single-product/product-image.php')); ?>
                                        <?php do_action('rh_woo_after_single_image'); ?>
                                    </div>
                                </div>
                                <div class="wpsm-one-half tabletblockdisplay wpsm-column-last">
                                    <div class="woo_bl_title flowhidden mb20">
                                        <h1 class="fontnormal font150 mb10 mt0 <?php if (rehub_option('wishlist_disable') != '1') : ?><?php echo getHotIconclass($post->ID, true); ?><?php endif; ?>"><?php if ($product->is_featured()) : ?><i class="rhicon rhi-bolt mr5 ml5 orangecolor" aria-hidden="true"></i><?php endif; ?> <?php the_title(); ?></h1>
                                        <?php do_action('rh_woo_single_product_title'); ?>
                                        <div class="woo_top_meta mobileblockdisplay mb10">
                                            <?php if ('no' !== get_option('woocommerce_enable_review_rating')) : ?>
                                                <div class="mr15">
                                                    <?php $rating_count = $product->get_rating_count(); ?>
                                                    <?php if ($rating_count < 1) : ?>
                                                        <span data-scrollto="#reviews" class="rehub_scroll cursorpointer font80 greycolor"><?php esc_html_e("Add your review", "rehub-theme"); ?></span>
                                                    <?php else : ?>
                                                        <?php woocommerce_template_single_rating(); ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="rh-flex-align-end rh-flex-eq-height mb25">
                                        <div class="woo-price-area rehub-btn-font">
                                            <?php woocommerce_template_single_price(); ?>
                                        </div>
                                        <?php if ($product->is_on_sale()) : ?>
                                            <?php
                                            $percentage = 0;
                                            $sales_html = '';
                                            if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() != 0) {
                                                $percentage = round((($product->get_regular_price() - $product->get_price()) / $product->get_regular_price()) * 100);
                                            }
                                            if ($percentage && $percentage > 0 && !$product->is_type('variable')) {
                                                $sales_html = apply_filters('woocommerce_sale_flash', '<span class="greencolor ml5">(-' . $percentage . '%)</span>', $post, $product);
                                            }                                    ?>
                                            <?php echo '' . $sales_html; ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php do_action('rh_woo_single_product_price'); ?>
                                    <?php if (wp_is_mobile()) : ?>
                                        <div class="mb20 summary">
                                            <div class="woo-button-area woo-ext-btn" id="woo-button-area">
                                                <div class="clearfix"></div>
                                                <?php rh_woo_code_zone('bottom'); ?>
                                                <?php do_action('rhwoo_template_single_add_to_cart'); ?>
                                                <?php rh_woo_code_zone('button'); ?>
                                                <?php rh_show_vendor_info_single('mt30 mb10'); ?>
                                                <div class="woo-button-actions-area tabletblockdisplay text-center mt15">
                                                    <?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme'); ?>
                                                    <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme'); ?>
                                                    <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme'); ?>
                                                    <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved); ?>
                                                    <?php if (rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) : ?>
                                                        <?php
                                                        $cmp_btn_args = array();
                                                        $cmp_btn_args['class'] = 'rhwoosinglecompare mb15';
                                                        if (rehub_option('compare_woo_cats') != '') {
                                                            $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                                        }
                                                        ?>
                                                        <?php echo wpsm_comparison_button($cmp_btn_args); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                        </div>
                                    <?php endif; ?>
                                    <?php $thecriteria = get_post_meta((int)$id, '_review_post_criteria', true); ?>
                                    <?php
                                    if (!empty($thecriteria[0]['review_post_name'])) {
                                        $review = true;
                                    } else {
                                        $review = false;
                                    }
                                    ?>
                                    <?php if ($review) : ?>
                                        <?php $rate_position = rh_get_product_position($post->ID); ?>
                                        <?php if (!empty($rate_position['rate_pos'])) : ?>
                                            <div class="font90 fontnormal mb20 rh-pr-rated-block">
                                                <?php
                                                if ($rate_position['rate_pos'] < 3) {
                                                    echo '<i class="rhicon rhi-trophy-alt font150 orangecolor mr10 vertmiddle rtlml10"></i>';
                                                }
                                                ?>
                                                <?php esc_html_e('Product is rated as', 'rehub-theme'); ?> <strong>#<?php echo '' . $rate_position['rate_pos']; ?></strong> <?php esc_html_e('in category', 'rehub-theme'); ?> <a href="<?php echo esc_url($rate_position['link']); ?>"><?php echo esc_attr($rate_position['cat_name']); ?></a>
                                            </div>
                                        <?php endif; ?>

                                    <?php endif; ?>
                                    <?php rh_woo_code_zone('content'); ?>
                                    <div class="mobilesblockdisplay font90 lineheight20 woo_desc_part">
                                        <?php if (has_excerpt($post->ID)) : ?>
                                            <?php woocommerce_template_single_excerpt(); ?>
                                        <?php endif; ?>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <?php do_action('rh_woo_single_product_description'); ?>
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
                                    do_action('woocommerce_single_product_summary');
                                    ?>
                                    <div class="woo-single-meta font80 mb10">
                                        <?php woocommerce_template_single_meta(); ?>
                                    </div>
                                    <?php woocommerce_template_single_sharing(); ?>
                                </div>
                                <div class="clearfix"></div>
                                <div class="rh-line mt10"></div>
                                <?php $tabs = apply_filters('woocommerce_product_tabs', array());
                                if (defined('\ContentEgg\PLUGIN_PATH')) {
                                    $youtubecontent = \ContentEgg\application\components\ContentManager::getViewData('Youtube', $post->ID);
                                    if (!empty($youtubecontent)) {
                                        $tabs['woo-ce-videos'] = array(
                                            'title' => esc_html__('Videos', 'rehub-theme'),
                                            'priority' => '21',
                                            'callback' => 'woo_cevideo_booking_out'
                                        );
                                        uasort($tabs, '_sort_priority_callback');
                                    }
                                }
                                if (!empty($tabs)) : ?>
                                    <?php wp_enqueue_script('customfloatpanel'); ?>
                                    <div id="contents-section-woo-area">
                                        <ul class="tabletsblockdisplay smart-scroll-desktop clearfix contents-woo-area rh-big-tabs-ul">
                                            <?php $i = 0;
                                            foreach ($tabs as $key => $tab) : ?>
                                                <li class="rh-hov-bor-line <?php if ($i == 0) echo 'active '; ?>rh-big-tabs-li <?php echo esc_attr($key); ?>_tab" id="tab-title-<?php echo esc_attr($key); ?>">
                                                    <a href="#section-<?php echo esc_attr($key); ?>"><?php echo apply_filters('woocommerce_product_' . $key . '_tab_title', esc_html($tab['title']), $key); ?></a>
                                                </li>
                                                <?php $i++; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="content-woo-area">
                                        <?php foreach ($tabs as $key => $tab) : ?>
                                            <div class="content-woo-section pt30 pb20 content-woo-section--<?php echo esc_attr($key); ?>" id="section-<?php echo esc_attr($key); ?>">
                                                <div class="">
                                                    <?php call_user_func($tab['callback'], $key, $tab); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                    </div>

                                <?php endif; ?>


                            </div>
                            <div class="rh-300-sidebar sticky-psn tabletblockdisplay">
                                <?php if (!wp_is_mobile()) : ?>
                                    <div class="re_wooinner_cta_wrapper padd15 mb20 summary">
                                        <div class="woo-button-area woo-ext-btn" id="woo-button-area">
                                            <div class="woo-price-area rehub-btn-font font80 floatleft mr10 rtlml10"><?php woocommerce_template_single_price(); ?>
                                            </div>
                                            <div class="clearfix"></div>
                                            <?php rh_woo_code_zone('bottom'); ?>
                                            <div class="clearfix"></div>
                                            <?php do_action('rhwoo_template_single_add_to_cart'); ?>
                                            <?php rh_woo_code_zone('button'); ?>
                                            <?php rh_show_vendor_info_single('mt20 mb10'); ?>
                                            <div class="woo-button-actions-area tabletblockdisplay text-center mt15">
                                                <?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme'); ?>
                                                <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme'); ?>
                                                <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme'); ?>
                                                <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved); ?>
                                                <?php if (rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) : ?>
                                                    <?php
                                                    $cmp_btn_args = array();
                                                    $cmp_btn_args['class'] = 'rhwoosinglecompare mb15';
                                                    if (rehub_option('compare_woo_cats') != '') {
                                                        $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                                    }
                                                    ?>
                                                    <?php echo wpsm_comparison_button($cmp_btn_args); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                    </div>
                                <?php endif; ?>
                                <!-- Sidebar -->
                                <?php if (is_active_sidebar('sidebarwooinner')) : ?>
                                    <?php dynamic_sidebar('sidebarwooinner'); ?>
                                <?php endif; ?>
                                <!-- /Sidebar -->

                            </div>
                        </div>
                    </div>

                    <div class="woo-content-area-full">
                        <?php
                        /**
                         * woocommerce_after_single_product_summary hook.
                         *
                         * @hooked woocommerce_output_product_data_tabs - 10
                         * @hooked woocommerce_upsell_display - 15
                         * @hooked woocommerce_output_related_products - 20
                         */
                        do_action('woocommerce_after_single_product_summary');
                        ?>
                    </div>

                    <!-- Related -->
                    <?php include(rh_locate_template('woocommerce/single-product/full-width-related.php')); ?>
                    <!-- /Related -->

                    <!-- Upsell -->
                    <?php include(rh_locate_template('woocommerce/single-product/full-width-upsell.php')); ?>
                    <!-- /Upsell -->

                    <?php wp_enqueue_script('customfloatpanel'); ?>
                    <div class="flowhidden rh-float-panel desktabldisplaynone" id="float-panel-woo-area">
                        <div class="rh-container rh-flex-center-align pt10 pb10">
                            <div class="float-panel-woo-image">
                                <?php WPSM_image_resizer::show_static_resized_image(array('lazy' => false, 'thumb' => true, 'width' => 50, 'height' => 50)); ?>
                            </div>
                            <div class="float-panel-woo-info wpsm_pretty_colored rh-line-left pl15 ml15">
                                <div class="float-panel-woo-title rehub-main-font mb5 font110">
                                    <?php the_title(); ?>
                                </div>
                                <ul class="float-panel-woo-links list-unstyled list-line-style font80 fontbold lineheight15">
                                    <?php foreach ($tabs as $key => $tab) : ?>
                                        <li class="<?php echo esc_attr($key); ?>_tab" id="tab-title-<?php echo esc_attr($key); ?>">
                                            <?php $tab_title = $tab['title']; ?>
                                            <a href="#section-<?php echo esc_attr($key); ?>"><?php echo apply_filters('woocommerce_product_' . $key . '_tab_title', esc_html($tab_title), $key); ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="float-panel-woo-btn rh-flex-columns rh-flex-right-align rh-flex-nowrap">
                                <div class="float-panel-woo-price fontbold rh-flex-center-align font120 rh-flex-right-align">
                                    <?php woocommerce_template_single_price(); ?>
                                </div>
                                <div class="float-panel-woo-button rh-flex-center-align rh-flex-right-align">
                                    <?php if (!rehub_option('woo_btn_inner_disable')) : ?>
                                        <?php if (!empty($itemsync)) : ?>
                                            <a href="#section-woo-ce-pricelist" class="single_add_to_cart_button rehub_scroll">
                                                <?php if (rehub_option('rehub_btn_text_aff_links') != '') : ?>
                                                    <?php echo rehub_option('rehub_btn_text_aff_links'); ?>
                                                <?php else : ?>
                                                    <?php esc_html_e('Choose offer', 'rehub-theme') ?>
                                                <?php endif; ?>
                                            </a>
                                        <?php else : ?>
                                            <?php if ($product->add_to_cart_url() != '') : ?>
                                                <?php if ($product->get_type() == 'variable' || $product->get_type() == 'booking') {
                                                    $url = '#woo-button-area';
                                                } else {
                                                    $url = esc_url($product->add_to_cart_url());
                                                }

                                                ?>
                                                <?php echo apply_filters(
                                                    'woo_float_add_to_cart_link',
                                                    sprintf(
                                                        '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn btn_offer_block single_add_to_cart_button %s %s product_type_%s"%s %s>%s</a>',
                                                        $url,
                                                        esc_attr($product->get_id()),
                                                        esc_attr($product->get_sku()),
                                                        $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                                                        $product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : '',
                                                        esc_attr($product->get_type()),
                                                        $product->get_type() == 'external' ? ' target="_blank"' : '',
                                                        $product->get_type() == 'external' ? ' rel="nofollow sponsored"' : '',
                                                        esc_html($product->add_to_cart_text())
                                                    ),
                                                    $product
                                                ); ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php rh_woo_code_zone('float'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php do_action('woocommerce_after_single_product'); ?>
                <?php endwhile; // end of the loop. ?>
            </div><!-- #product-<?php the_ID(); ?> -->
        <?php do_action('woocommerce_after_main_content'); ?>
        </div>
    </div>
</div>