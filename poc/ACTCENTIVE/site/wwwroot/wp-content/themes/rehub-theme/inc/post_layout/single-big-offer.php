<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<!-- CONTENT -->
<div class="rh-container">
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side single<?php if (get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?> full_width<?php endif; ?> clearfix">
            <div class="rh-post-wrapper">
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                        <?php $expiredclass = rh_expired_or_not($post->ID, 'class'); ?>
                        <?php $postclasses = array('post-inner', 'post', $expiredclass); ?>
                        <article <?php post_class($postclasses); ?> id="post-<?php the_ID(); ?>">
                            <div class="rh_post_layout_big_offer">
                                <?php echo rh_generate_incss('singlebigoffer'); ?>
                                <div class="title_single_area mb15">
                                    <?php
                                    $crumb = '';
                                    if (function_exists('yoast_breadcrumb')) {
                                        $crumb = yoast_breadcrumb('<div class="breadcrumb">', '</div>', false);
                                    } else if (function_exists('rank_math_the_breadcrumbs')) {
                                        $crumb = rank_math_get_breadcrumbs('wrap_before=<div class="breadcrumb">&wrap_after=</div>');
                                    }
                                    if (!is_string($crumb) || $crumb === '') {
                                        if (rehub_option('rehub_disable_breadcrumbs') == '1') {
                                            echo '';
                                        } elseif (function_exists('dimox_breadcrumbs')) {
                                            dimox_breadcrumbs();
                                        }
                                    }
                                    echo '' . $crumb;
                                    ?>
                                    <?php echo re_badge_create('labelsmall'); ?><?php rh_post_header_cat('post', true); ?>
                                </div>

                                <?php
                                $offer_post_url = get_post_meta($post->ID, 'rehub_offer_product_url', true);
                                $offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
                                $offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url);
                                $offer_price = get_post_meta($post->ID, 'rehub_offer_product_price', true);
                                $offer_price = apply_filters('rehub_create_btn_price', $offer_price);
                                //$offer_title = get_post_meta( $post->ID, 'rehub_offer_name', true );
                                $offer_thumb = get_post_meta($post->ID, 'rehub_offer_product_thumb', true);
                                $offer_btn_text = get_post_meta($post->ID, 'rehub_offer_btn_text', true);
                                $offer_price_old = get_post_meta($post->ID, 'rehub_offer_product_price_old', true);
                                $offer_price_old = apply_filters('rehub_create_btn_price_old', $offer_price_old);
                                $offer_coupon = get_post_meta($post->ID, 'rehub_offer_product_coupon', true);
                                $offer_coupon_date = get_post_meta($post->ID, 'rehub_offer_coupon_date', true);
                                $offer_coupon_mask = get_post_meta($post->ID, 'rehub_offer_coupon_mask', true);
                                $offer_desc = get_post_meta($post->ID, 'rehub_offer_product_desc', true);
                                ?>

                                <?php $coupon_style = $expired = '';
                                if (!empty($offer_coupon_date)) : ?>
                                    <?php
                                    $timestamp1 = strtotime($offer_coupon_date);
                                    if (strpos($offer_coupon_date, ':') === false) {
                                        $timestamp1 += 86399;
                                    }
                                    $seconds = $timestamp1 - (int)current_time('timestamp', 0);
                                    $days = floor($seconds / 86400);
                                    $seconds %= 86400;
                                    if ($days > 0) {
                                        $coupon_text = $days . ' ' . __('days left', 'rehub-theme');
                                        $coupon_style = '';
                                        $expired = 'no';
                                    } elseif ($days == 0) {
                                        $coupon_text = esc_html__('Last day', 'rehub-theme');
                                        $coupon_style = '';
                                        $expired = 'no';
                                    } else {
                                        $coupon_text = esc_html__('Expired', 'rehub-theme');
                                        $coupon_style = ' expired_coupon';
                                        $expired = '1';
                                    }
                                    ?>
                                <?php endif; ?>
                                <?php do_action('post_change_expired', $expired); //Here we update our expired
                                ?>
                                <?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask == '1' || $offer_coupon_mask == 'on') && $expired != '1') ? '1' : ''; ?>
                                <?php $reveal_enabled = ($coupon_mask_enabled == '1') ? ' reveal_enabled' : ''; ?>
                                <?php $outsidelinkpart = ($coupon_mask_enabled == '1') ? 'data-codeid="' . $post->ID . '" data-dest="' . $offer_url . '" data-clipboard-text="' . $offer_coupon . '" class="re_track_btn masked_coupon"' : 'class="re_track_btn"'; ?>
                                <div class="border-grey-bottom flowhidden mb25 pb20 <?php echo '' . $reveal_enabled;echo '' . $coupon_style; ?>">
                                    <?php $disableimage = get_post_meta($post->ID, 'show_featured_image', true); ?>
                                    <?php if (!$disableimage) : ?>
                                        <div class="featured_compare_left wpsm-one-half">
                                            <figure class="position-relative text-center img-maxh-350 img-width-auto img-mobs-maxh-250">
                                                <?php
                                                if (!empty($offer_price_old)) {
                                                    if (!empty($offer_price)) {
                                                        $offer_pricesale = (float)rehub_price_clean($offer_price); //Clean price from currence symbols
                                                        $offer_priceold = (float)rehub_price_clean($offer_price_old); //Clean price from currence symbols
                                                        if ($offer_priceold != '0' && is_numeric($offer_priceold) && $offer_priceold > $offer_pricesale) {
                                                            $off_proc = 0 - (100 - ($offer_pricesale / $offer_priceold) * 100);
                                                            $off_proc = round($off_proc);
                                                            echo '<span class="sale_a_proc">' . $off_proc . '%</span>';
                                                        }
                                                    }
                                                } ?>
                                                <a href="<?php echo esc_url($offer_url) ?>" target="_blank" rel="nofollow sponsored" <?php echo '' . $outsidelinkpart; ?>>
                                                    <?php if (!has_post_thumbnail() && !empty($offer_thumb)) : ?>
                                                        <?php WPSM_image_resizer::show_static_resized_image(array('lazy' => false, 'src' => $offer_thumb, 'crop' => false, 'height' => 450, 'width' => 350, 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_450_350.png')); ?>
                                                    <?php else : ?>
                                                        <?php echo WPSM_image_resizer::show_wp_image('large_inner', '', array('lazydisable'=>true, 'loading'=>'eager')); ?>
                                                    <?php endif; ?>
                                                </a>
                                            </figure>
                                            <div class="compare-full-images">
                                                <?php echo rh_get_post_thumbnails(array('video' => 1, 'columns' => 4, 'height' => 60)); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="single_compare_right wpsm-one-half wpsm-column-last">
                                        <div class="title_single_area mb25">
                                            <h1>
                                                <?php echo rh_expired_or_not($post->ID, 'span'); ?><?php the_title(); ?>
                                            </h1>
                                        </div>
                                        <?php if (rehub_option('rehub_single_after_title')) : ?><div class="mediad mediad_top mb15"><?php echo do_shortcode(rehub_option('rehub_single_after_title')); ?></div>
                                            <div class="clearfix"></div><?php endif; ?>

                                        <div class="brand_logo_small">
                                            <?php WPSM_Postfilters::re_show_brand_tax('logo'); //show brand logo
                                            ?>
                                        </div>

                                        <?php $verify = get_post_meta($post->ID, 'rehub_offer_verify_label', true);
                                        if ($verify) {
                                            echo '<span class="verifymeta mr5 mb10 greencolor blockstyle"><i class="rhicon rhi-shield-check"></i> ' . esc_attr($verify) . '</span>';
                                        } ?>

                                        <?php block_template_part( 'post-single-button' );?>

                                        <?php if ($offer_url) : ?>
                                            <div class="wpsm_score_box buttons_col mb25">
                                                <div class="priced_block">
                                                    <?php rehub_generate_offerbtn('updateclean=1'); ?>
                                                </div>
                                                <?php $disclaimer = get_post_meta($post->ID, 'rehub_offer_disclaimer', true); ?>
                                                <?php if ($disclaimer) : ?>
                                                    <div class="font80 guten-disclaimer greycolor lineheight15 pb15"><?php echo wp_kses($disclaimer, 'post'); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <?php wp_enqueue_script('customfloatpanel'); ?>
                                            <div id="contents-section-woo-area"></div>
                                        <?php endif; ?>
                                        <?php if (!empty($offer_coupon_date) && $expired != 1) {

                                            echo '<div class="leftcountdown gridcountdown" style="width:250px">';
                                            $year = date('Y', $timestamp1);
                                            $month = date('m', $timestamp1);
                                            $day  = date('d', $timestamp1);
                                            $hour  = date('H', $timestamp1);
                                            $minute  = date('i', $timestamp1);
                                            echo wpsm_countdown(array('year' => $year, 'month' => $month, 'day' => $day, 'minute' => $minute, 'hour' => $hour));
                                            echo '</div>';
                                        } ?>
                                        <p class="big-offer-desc"><?php echo do_shortcode($offer_desc);  ?></p>
                                        <?php if (rehub_option('rehub_disable_share_top') == '1') : ?>
                                            <?php
                                            $wishlistadd = esc_html__('Save', 'rehub-theme');
                                            $wishlistadded = esc_html__('Saved', 'rehub-theme');
                                            $wishlistremoved = esc_html__('Removed', 'rehub-theme');
                                            ?>
                                            <div class="favour_in_row clearbox favour_btn_red">
                                                <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved); ?>
                                            </div>
                                        <?php else : ?>
                                            <div class="top_share notextshare"><?php include(rh_locate_template('inc/parts/post_share.php')); ?></div>
                                            <div class="clearfix"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="meta post-meta-big flowhidden mb15 pb15 greycolor border-grey-bottom">
                                    <?php rh_post_header_meta_big(); ?>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <?php $no_featured_image_layout = 1; ?>
                            <?php include(rh_locate_template('inc/parts/top_image.php')); ?>
                            <?php $disableads = get_post_meta($post->ID, 'show_banner_ads', true); ?>
                            <?php if (rehub_option('rehub_single_before_post') && $disableads != '1') : ?><div class="mediad mediad_before_content mb15"><?php echo do_shortcode(rehub_option('rehub_single_before_post')); ?></div><?php endif; ?>
                            <?php the_content(); ?>
                            <?php if ($offer_post_url) : ?>
                                <div class="flowhidden rh-float-panel" id="float-panel-woo-area">
                                    <div class="rh-container rh-flex-center-align pt10 pb10">
                                        <div class="float-panel-woo-image hideonsmobile">
                                            <?php WPSM_image_resizer::show_static_resized_image(array('lazy' => true, 'thumb' => true, 'width' => 50, 'height' => 50)); ?>
                                        </div>
                                        <div class="wpsm_pretty_colored rh-line-left pl15 ml15 rtlmr15 rtlpr15 hideonsmobile">
                                            <div class="hideontablet mb5 font110 fontbold">
                                                <?php the_title(); ?>
                                            </div>
                                            <div class="float-panel-price">
                                                <div class="fontbold font110 rehub-btn-font rehub-main-color">
                                                    <?php echo esc_html($offer_price) ?>
                                                    <?php if ($offer_price_old != '') : ?>
                                                        <span class="retail-old greycolor rh_opacity_5 font90">
                                                            <strike><span class="value"><?php echo esc_html($offer_price_old); ?></span></strike>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php $reveal_enabled = ($coupon_mask_enabled == '1') ? ' reveal_enabled' : ''; ?>
                                        <div class="float-panel-woo-btn rh-flex-columns rh-flex-right-align rh-flex-nowrap showonsmobile">
                                            <div class="float-panel-woo-button rh-flex-center-align rh-flex-right-align showonsmobile<?php echo '' . $reveal_enabled; ?>">
                                                <div class="priced_block mb5 showonsmobile clearfix">
                                                    <?php if ($coupon_mask_enabled == '1') : ?>
                                                        <?php wp_enqueue_script('zeroclipboard'); ?>
                                                        <a class="coupon_btn showonsmobile re_track_btn btn_offer_block rehub_offer_coupon masked_coupon <?php if (!empty($offer_coupon_date)) {echo '' . $coupon_style;} ?>" <?php echo '' . $outsidelinkpart; ?>>
                                                            <?php if ($offer_btn_text != '') : ?>
                                                                <?php echo esc_html($offer_btn_text); ?>
                                                            <?php elseif (rehub_option('rehub_mask_text') != '') : ?>
                                                                <?php echo rehub_option('rehub_mask_text'); ?>
                                                            <?php else : ?>
                                                                <?php esc_html_e('Reveal coupon', 'rehub-theme') ?>
                                                            <?php endif; ?>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a class="re_track_btn showonsmobile btn_offer_block" href="<?php echo esc_url($offer_url) ?>" target="_blank" rel="nofollow sponsored" <?php echo '' . $outsidelinkpart; ?>>
                                                        <?php if ($offer_btn_text != '') : ?>
                                                            <?php echo esc_attr($offer_btn_text); ?>
                                                        <?php elseif (rehub_option('rehub_btn_text') != '') : ?>
                                                            <?php echo rehub_option('rehub_btn_text'); ?>
                                                        <?php else : ?>
                                                            <?php esc_html_e('Buy this item', 'rehub-theme') ?>
                                                        <?php endif; ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </article>
                        <div class="clearfix"></div>
                        <?php include(rh_locate_template('inc/post_layout/single-common-footer.php')); ?>
                <?php endwhile;
                endif; ?>
                <?php comments_template(); ?>
            </div>
        </div>
        <!-- /Main Side -->
        <!-- Sidebar -->
        <?php if (get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?><?php else : ?><?php get_sidebar(); ?><?php endif; ?>
        <!-- /Sidebar -->
    </div>
</div>
<!-- /CONTENT -->