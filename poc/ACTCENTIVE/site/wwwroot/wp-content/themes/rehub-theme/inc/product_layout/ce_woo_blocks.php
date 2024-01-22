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
<?php
$script = '
var cenoprice = document.getElementById("nopricehsection");
if(cenoprice !== null){
    document.getElementById("section-woo-ce-pricehistory").remove();
    document.getElementById("tab-title-woo-ce-pricehistory").remove();
}
    ';
wp_add_inline_script('rehub', $script);
?>
<?php $unique_id = $module_id = $itemsync = $syncitem = $youtubecontent = $replacetitle = ''; ?>
<?php if (defined('\ContentEgg\PLUGIN_PATH')) : ?>
    <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($post->ID); ?>
    <?php $domain = $merchant = ''; ?>
    <?php if (!empty($itemsync)) : ?>
        <?php
        $unique_id = $itemsync['unique_id'];
        $module_id = $itemsync['module_id'];
        $domain = $itemsync['domain'];
        $merchant = $itemsync['merchant'];
        $syncitem = $itemsync;
        ?>
    <?php endif; ?>
    <?php $postid = $post->ID; ?>
    <?php $youtubecontent = \ContentEgg\application\components\ContentManager::getViewData('Youtube', $postid); ?>
<?php endif; ?>

<!-- CONTENT -->
<div class="rh-container">
    <div class="rh-content-wrap clearfix">
        <div id="contents-section-woo-area" class="rh-stickysidebar-wrapper">
            <div class="ce_woo_auto_sections ce_woo_blocks main-side rh-sticky-container clearfix <?php echo (is_active_sidebar('sidebarwooinner')) ? 'woo_default_w_sidebar' : 'full_width woo_default_no_sidebar'; ?>" id="content">
                <?php echo rh_generate_incss('cewooblocks'); ?>
                <div class="post">
                    <?php do_action('woocommerce_before_main_content'); ?>
                    <?php if (defined('\ContentEgg\PLUGIN_PATH')) : ?>
                        <?php $amazonupdate = get_post_meta($postid, \ContentEgg\application\components\ContentManager::META_PREFIX_LAST_ITEMS_UPDATE . 'Amazon', true); ?>
                        <div class="floatright pl20">
                            <?php $product_update = \ContentEgg\application\helpers\TemplateHelper::getLastUpdateFormatted('Amazon', $postid); ?>
                            <?php if ($amazonupdate && $product_update) : ?>
                                <div class="font60 lineheight20"><?php esc_html_e('Last price update was:', 'rehub-theme'); ?> <?php echo '' . $product_update; ?> <span class="csspopuptrigger" data-popup="ceblocks-amazon-disclaimer"><i class="rhicon rhi-question-circle greycolor font90"></i></span></div>
                                <div class="csspopup" id="ceblocks-amazon-disclaimer">
                                    <div class="csspopupinner">
                                        <span class="cpopupclose cursorpointer lightgreybg rh-close-btn rh-flex-center-align rh-flex-justify-center rh-shadow5 roundborder">×</span>
                                        <?php esc_html_e('Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on Amazon at the time of purchase will apply to the purchase of this product.', 'rehub-theme'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!rehub_option('rehub_disable_breadcrumbs')) {
                        woocommerce_breadcrumb();
                    } ?>

                    <?php while (have_posts()) : the_post(); ?>
                        <div id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <div class="ce_woo_block_top_holder">
                                <div class="woo_bl_title flowhidden mb10">
                                    <?php do_action('woocommerce_before_single_product'); ?>

                                    <h1 class="floatleft tabletblockdisplay pr20 <?php if (rehub_option('wishlist_disable') != '1') : ?><?php echo getHotIconclass($post->ID, true); ?><?php endif; ?>"><?php the_title(); ?></h1>
                                    <?php do_action('rh_woo_single_product_title'); ?>
                                    <div class="woo-top-actions tabletblockdisplay floatright">
                                        <div class="woo-button-actions-area pl5 pb5 pr5">
                                            <?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme'); ?>
                                            <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme'); ?>
                                            <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme'); ?>
                                            <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved); ?>
                                            <?php if (rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) : ?>
                                                <?php
                                                $cmp_btn_args = array();
                                                $cmp_btn_args['class'] = 'rhwoosinglecompare';
                                                if (rehub_option('compare_woo_cats') != '') {
                                                    $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                                }
                                                ?>
                                                <?php echo wpsm_comparison_button($cmp_btn_args); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="border-grey-bottom clearfix mb15"></div>

                                <div class="wpsm-one-third wpsm-column-first tabletblockdisplay compare-full-images modulo-lightbox mb30">
                                    <?php
                                    wp_enqueue_script('modulobox');
                                    wp_enqueue_style('modulobox');
                                    ?>
                                    <figure class="text-center">
                                        <?php $badge = get_post_meta($post->ID, 'is_editor_choice', true); ?>
                                        <?php if ($badge != '' && $badge != '0') : ?>
                                            <?php echo re_badge_create('ribbon'); ?>
                                        <?php else : ?>
                                            <?php woocommerce_show_product_sale_flash(); ?>
                                        <?php endif; ?>
                                        <?php
                                        $image_id = get_post_thumbnail_id($post->ID);
                                        $image_url = wp_get_attachment_image_src($image_id, 'full');
                                        $image_url = $image_url[0];
                                        ?>
                                        <a data-rel="rh_top_gallery" href="<?php echo esc_url($image_url); ?>" target="_blank" data-thumb="<?php echo esc_url($image_url); ?>">
                                            <?php echo WPSM_image_resizer::show_wp_image('woocommerce_single', '', array('lazydisable'=>true, 'loading'=>'eager')); ?>
                                        </a>
                                        <?php do_action( 'rehub_360_product_image' ); ?>
                                    </figure>
                                    <?php $post_image_gallery = $product->get_gallery_image_ids(); ?>
                                    <?php if (!empty($post_image_gallery)) : ?>
                                        <div class="rh-flex-eq-height rh_mini_thumbs compare-full-thumbnails mt15 mb15">
                                            <?php foreach ($post_image_gallery as $key => $image_gallery) : ?>
                                                <?php if (!$image_gallery) continue; ?>
                                                <?php $image = wp_get_attachment_image_src($image_gallery, 'full');
                                                $imgurl = (!empty($image[0])) ? $image[0] : ''; ?>
                                                <a data-rel="rh_top_gallery" data-thumb="<?php echo esc_url($imgurl); ?>" href="<?php echo esc_url($imgurl); ?>" target="_blank" class="rh-flex-center-align mb10" data-title="<?php echo esc_attr(get_post_field('post_excerpt', $image_gallery)); ?>">
                                                    <?php WPSM_image_resizer::show_static_resized_image(array('lazy' => false, 'src' => esc_url($imgurl), 'crop' => false, 'height' => 60, 'width'=>60)); ?>
                                                </a>
                                            <?php endforeach; ?>
                                            <?php if (!empty($youtubecontent)) : ?>
                                                <?php foreach ($youtubecontent as $videoitem) : ?>
                                                    <a href="<?php echo esc_url($videoitem['url']); ?>" data-rel="rh_top_gallery" target="_blank" class="rh-flex-center-align mb10 rh_videothumb_link" data-poster="<?php echo parse_video_url($videoitem['url'], 'hqthumb'); ?>" data-thumb="<?php echo esc_url($videoitem['img']) ?>">
                                                        <img src="<?php echo esc_url($videoitem['img']) ?>" alt="<?php echo '' . $videoitem['title'] ?>"  width="115" height="65" />
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <?php echo woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link&rel=rh_top_gallery&wrapper=no&title=no'); ?>
                                        </div>
                                    <?php else : ?>
                                        <?php if (defined('\ContentEgg\PLUGIN_PATH')) : ?>
                                            <?php if (!empty($itemsync['extra']['imageSet'])) {
                                                $ceimages = $itemsync['extra']['imageSet'];
                                            } elseif (!empty($itemsync['extra']['images'])) {
                                                $ceimages = $itemsync['extra']['images'];
                                            } else {
                                                $qwantimages = \ContentEgg\application\components\ContentManager::getViewData('GoogleImages', $post->ID);
                                                if (!empty($qwantimages)) {
                                                    $ceimages = wp_list_pluck($qwantimages, 'img');
                                                } else {
                                                    $ceimages = '';
                                                }
                                            } ?>
                                            <?php if (!empty($ceimages)) : ?>
                                                <div class="rh_mini_thumbs compare-full-thumbnails limited-thumb-number mt15 mb15">
                                                    <?php foreach ((array)$ceimages as $gallery_img) : ?>
                                                        <?php if (isset($gallery_img['LargeImage'])) {
                                                            $image = $gallery_img['LargeImage'];
                                                        } else {
                                                            $image = $gallery_img;
                                                        } ?>
                                                        <a data-thumb="<?php echo esc_url($image) ?>" data-rel="rh_top_gallery" href="<?php echo esc_url($image); ?>" data-title="<?php echo esc_attr($itemsync['title']); ?>" class="rh-flex-center-align mb10">
                                                            <?php WPSM_image_resizer::show_static_resized_image(array('src' => $image, 'height' => 65, 'width'=>65, 'title' => $itemsync['title'], 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_100_70.png')); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                    <?php if (!empty($youtubecontent)) : ?>
                                                        <?php foreach ($youtubecontent as $videoitem) : ?>
                                                            <a href="<?php echo '' . $videoitem['url']; ?>" data-rel="rh_top_gallery" target="_blank" class="mb10 rh-flex-center-align rh_videothumb_link" data-poster="<?php echo parse_video_url($videoitem['url'], 'hqthumb'); ?>" data-thumb="<?php echo esc_url($videoitem['img']) ?>">
                                                                <img src="<?php echo esc_url($videoitem['img']) ?>" alt="<?php echo '' . $videoitem['title'] ?>" />
                                                            </a>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                    <?php echo woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link&rel=rh_top_gallery&wrapper=no&title=no'); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php do_action('rh_woo_after_single_image'); ?>
                                </div>
                                <div class="wpsm-two-third tabletblockdisplay wpsm-column-last mb30">
                                    <?php $reviewblock = wpsm_reviewbox(array('compact' => 'circle', 'id' => $post->ID, 'scrollid' => 'tab-title-description')); ?>
                                    <?php $featuredreview = false; ?>
                                    <?php if ($reviewblock) : ?>
                                        <?php $rate_position = rh_get_product_position($post->ID); ?>
                                        <?php if (!empty($rate_position['rate_pos'])) : ?>
                                            <?php $featuredreview = true; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <div class="rh-flex-center-align woo_top_meta mobileblockdisplay <?php echo '' . ($featuredreview) ? 'mb5' : 'mb10'; ?>">
                                        <?php if ('no' !== get_option('woocommerce_enable_review_rating')) : ?>
                                            <div class="floatleft mr15 disablefloatmobile">
                                                <?php $rating_count = $product->get_rating_count(); ?>
                                                <?php if ($rating_count < 1) : ?>
                                                    <span data-scrollto="#reviews" class="rehub_scroll cursorpointer font80 greycolor"><?php esc_html_e("Add your review", "rehub-theme"); ?></span>
                                                <?php else : ?>
                                                    <?php woocommerce_template_single_rating(); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <span class="floatleft meta post-meta mt0 mb0 disablefloatmobile">
                                            <?php
                                            if (rehub_option('post_view_disable') != 1) {
                                                $rehub_views = get_post_meta($post->ID, 'rehub_views', true);
                                                echo '<span class="greycolor postview_meta mr10">' . $rehub_views . '</span>';
                                            }
                                            if (!$featuredreview) {
                                                $categories = wc_get_product_terms($post->ID, 'product_cat', array("fields" => "all"));
                                                $separator = '';
                                                $output = '';
                                                if (!empty($categories)) {
                                                    foreach ($categories as $category) {
                                                        $output .= '<a class="mr5 ml5 rh-cat-inner rh-cat-' . $category->term_id . '" href="' . esc_url(get_term_link($category->term_id, 'product_cat')) . '" title="' . esc_attr(sprintf(esc_html__('View all posts in %s', 'rehub-theme'), $category->name)) . '">' . esc_html($category->name) . '</a>' . $separator;
                                                    }
                                                    echo trim($output, $separator);
                                                }
                                            }
                                            ?>
                                        </span>
                                    </div>

                                    <?php if ($featuredreview) : ?>
                                        <div class="clearbox mb5 rh-pr-rated-block">
                                            <span class="font80 fontnormal mobileblockdisplay">
                                                <?php
                                                if ($rate_position['rate_pos'] < 3) {
                                                    echo '<i class="rhicon rhi-trophy-alt font150 orangecolor mr10 vertmiddle rtlml10"></i>';
                                                }

                                                ?>
                                                <?php esc_html_e('Product is rated as', 'rehub-theme'); ?> <strong>#<?php echo '' . $rate_position['rate_pos']; ?></strong> <?php esc_html_e('in category', 'rehub-theme'); ?> <a href="<?php echo esc_url($rate_position['link']); ?>"><?php echo esc_attr($rate_position['cat_name']); ?></a>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="rh-line mb20 mt10"></div>
                                    <div class="rh_post_layout_rev_price_holder position-relative flowhidden">
                                        <?php if ($reviewblock) : ?>
                                            <div class="floatleft mb15 mobileblockdisplay">
                                                <?php echo '' . $reviewblock; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="compare-button-holder floatright pl20 width-100-calc disablemobilepadding mobileblockdisplay">
                                            <div class="floatright ml10 mobileinline">
                                                <?php if ($unique_id && $module_id && !empty($syncitem)) : ?>
                                                    <?php include(rh_locate_template('inc/parts/pricealertpopup.php')); ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php woocommerce_template_single_price(); ?>
                                            <?php do_action('rh_woo_single_product_price'); ?>
                                            <?php if (!empty($itemsync)) : ?>
                                                <?php echo rh_best_syncpost_deal($itemsync, 'mb10 compare-domain-icon lineheight20', true); ?>
                                                <?php $offer_post_url = $itemsync['url']; ?>
                                                <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url); ?>
                                                <?php $aff_btn_text = get_post_meta($post->ID, '_button_text', true); ?>
                                                <?php
                                                if ($aff_btn_text) {
                                                    $buy_best_text = $aff_btn_text;
                                                } elseif (rehub_option('buy_best_text') != '') {
                                                    $buy_best_text = rehub_option('buy_best_text');
                                                } else {
                                                    $buy_best_text = esc_html__('Buy for best price', 'rehub-theme');
                                                }
                                                ?>
                                                <a href="<?php echo esc_url($afflink); ?>" class="re_track_btn wpsm-button rehub_main_btn btn_offer_block" target="_blank" rel="nofollow sponsored"><?php echo esc_html($buy_best_text); ?>
                                                </a>
                                            <?php else : ?>
                                                <div class="woo-button-area mb30" id="woo-button-area">
                                                    <?php do_action('rhwoo_template_single_add_to_cart'); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <?php rh_woo_code_zone('button'); ?>
                                    <?php rh_show_vendor_info_single('mt20 mb10'); ?>
                                    <div class="rh-line mt30 mb25"></div>
                                    <?php $thecriteria = get_post_meta((int)$id, '_review_post_criteria', true); ?>
                                    <?php
                                    if (!empty($thecriteria[0]['review_post_name'])) {
                                        $review = true;
                                    } else {
                                        $review = false;
                                    }
                                    ?>
                                    <div<?php if ($review) {
                                            echo ' class="woo-desc-w-review"';
                                        } ?>>
                                        <?php
                                        if ($review) {
                                            echo '<div class="review_score_min mobilesblockdisplay mb15 mr30 rtlml30 font70 pr20 rtlpl20 rh-line-right floatleft"><table><tbody>';
                                            foreach ($thecriteria as $criteria) {
                                                if (!empty($criteria)) {
                                                    $criteriascore = $criteria['review_post_score'];
                                                    $criterianame = $criteria['review_post_name'];
                                                    echo '<tr><th class="pr10 rtlpl10">' . $criterianame . '</th>';
                                                    echo '<td><strong>' . $criteriascore . '</strong></td>';
                                                    echo '</tr>';
                                                }
                                            }
                                            echo '</tbody></table></div>';
                                        }
                                        ?>
                                        <div class="mobilesblockdisplay font90 lineheight20 woo_desc_part<?php if ($review) {
                                                                                                                echo ' floatleft';
                                                                                                            } ?>">
                                            <?php if (has_excerpt($post->ID)) : ?>
                                                <?php woocommerce_template_single_excerpt(); ?>
                                            <?php else : ?>
                                                <?php if (!empty($itemsync['extra']['itemAttributes']['Feature'])) {
                                                    $features = $itemsync['extra']['itemAttributes']['Feature'];
                                                } elseif (!empty($itemsync['extra']['keySpecs'])) {
                                                    $features = $itemsync['extra']['keySpecs'];
                                                }
                                                ?>
                                                <?php if (!empty($features)) : ?>
                                                    <ul class="featured_list mt0">
                                                        <?php $length = $maxlength = 0; ?>
                                                        <?php foreach ($features as $k => $feature) : ?>
                                                            <?php if (is_array($feature)) {
                                                                continue;
                                                            } ?>
                                                            <?php $length = strlen($feature);
                                                            $maxlength += $length; ?>
                                                            <li><?php echo esc_attr($feature); ?></li>
                                                            <?php if ($k >= 5 || $maxlength > 200) break; ?>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else : ?>
                                                    <?php if (defined('\ContentEgg\PLUGIN_PATH')) : ?>
                                                        <?php $currency_code = rehub_option('ce_custom_currency'); ?>
                                                        <?php echo do_shortcode('[content-egg-block template=price_statistics currency=' . $currency_code . ']'); ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <div class="clearfix"></div>
                                            <?php endif; ?>
                                            <?php rh_woo_code_zone('content'); ?>
                                            <?php do_action('rh_woo_single_product_description'); ?>
                                        </div>
                                        <div class="clearfix"></div>
                                </div>
                                <div class="woo-single-meta font80">
                                    <?php do_action('woocommerce_product_meta_start'); ?>
                                    <?php $term_ids =  wc_get_product_terms($post->ID, 'store', array("fields" => "ids")); ?>
                                    <?php if (!empty($term_ids) && !is_wp_error($term_ids)) : ?>
                                        <div class="woostorewrap flowhidden mb10">
                                            <div class="brand_logo_small">
                                                <?php WPSM_Woohelper::re_show_brand_tax('logo'); //show brand logo
                                                ?>
                                            </div>
                                            <div class="store_tax">
                                                <?php WPSM_Woohelper::re_show_brand_tax(); //show brand taxonomy
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php do_action('woocommerce_product_meta_end'); ?>
                                </div>
                                <div class="top_share_small top_share notextshare">
                                    <?php woocommerce_template_single_sharing(); ?>
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
                                do_action('woocommerce_single_product_summary');
                                ?>
                            </div>
                        </div>

                        <div class="other-woo-area clearfix">
                            <div class="rh-container">
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
                        </div>

                        <?php $tabs = apply_filters('woocommerce_product_tabs', array());

                        if (!empty($tabs)) : ?>

                            <?php if (defined('\ContentEgg\PLUGIN_PATH')) : ?>

                                <?php
                                $replacetitle = apply_filters('woo_product_section_title', get_the_title() . ' ');
                                if (!empty($syncitem)) {
                                    $tabs['woo-ce-pricelist'] = array(
                                        'title' => $replacetitle . __('Prices', 'rehub-theme'),
                                        'priority' => '8',
                                        'callback' => 'woo_ce_pricelist_output'
                                    );
                                    $tabs['woo-ce-pricehistory'] = array(
                                        'title' => esc_html__('Price History', 'rehub-theme'),
                                        'priority' => '9',
                                        'callback' => 'woo_ce_history_output'
                                    );
                                }
                                if (!empty($youtubecontent)) {
                                    $tabs['woo-ce-videos'] = array(
                                        'title' => $replacetitle . __('Videos', 'rehub-theme'),
                                        'priority' => '21',
                                        'callback' => 'woo_ce_video_output'
                                    );
                                }
                                $googlenews = get_post_meta($post->ID, '_cegg_data_GoogleNews', true);
                                if (!empty($googlenews)) {
                                    $tabs['woo-ce-news'] = array(
                                        'title' => esc_html__('World News', 'rehub-theme'),
                                        'priority' => '23',
                                        'callback' => 'woo_ce_news_output'
                                    );
                                }
                                uasort($tabs, '_sort_priority_callback');
                                ?>

                            <?php endif; ?>

                            <?php wp_enqueue_script('customfloatpanel'); ?>
                            <div class="flowhidden rh-float-panel" id="float-panel-woo-area">
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
                                                    <?php $tab_title = str_replace($replacetitle, '', $tab['title']); ?>
                                                    <a href="#section-<?php echo esc_attr($key); ?>"><?php echo apply_filters('woocommerce_product_' . $key . '_tab_title', esc_html($tab_title), $key); ?></a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="float-panel-woo-btn rh-flex-columns rh-flex-right-align rh-flex-nowrap">
                                        <div class="float-panel-woo-price rh-flex-center-align font120 rh-flex-right-align">
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

                            <div class="content-woo-area">
                                <?php foreach ($tabs as $key => $tab) : ?>
                                    <div class="border-lightgrey clearbox flowhidden mb25 rh-shadow1 rh-tabletext-block rh-tabletext-wooblock whitebg width-100p" id="section-<?php echo esc_attr($key); ?>">
                                        <div class="rh-tabletext-block-heading fontbold border-grey-bottom">
                                            <span class="cursorpointer floatright lineheight15 ml10 toggle-this-table rtlmr10"></span>
                                            <h4 class="rh-heading-icon"><?php echo esc_attr($tab['title']); ?></h4>
                                        </div>
                                        <div class="rh-tabletext-block-wrapper padd20">
                                            <?php call_user_func($tab['callback'], $key, $tab); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php endif; ?>

                        <!-- Related -->
                        <?php $sidebar = (is_active_sidebar('sidebarwooinner')) ? true : false; ?>
                        <?php include(rh_locate_template('woocommerce/single-product/related-compact.php')); ?>
                        <!-- /Related -->
                        <!-- Upsell -->
                        <?php include(rh_locate_template('woocommerce/single-product/upsell-compact.php')); ?>
                        <!-- /Upsell -->

                </div><!-- #product-<?php the_ID(); ?> -->
                <?php do_action('woocommerce_after_single_product'); ?>
            <?php endwhile; // end of the loop. 
            ?>
            <?php do_action('woocommerce_after_main_content'); ?>

            </div>

        </div>
        <?php if (is_active_sidebar('sidebarwooinner')) : ?>
            <?php wp_enqueue_script('stickysidebar'); ?>
            <aside class="sidebar rh-sticky-container">
                <?php dynamic_sidebar('sidebarwooinner'); ?>
            </aside>
        <?php endif; ?>
    </div>
</div>
</div>
<!-- /CONTENT -->

<?php rh_woo_code_zone('bottom'); ?>