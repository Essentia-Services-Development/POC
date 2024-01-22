<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<?php
$schemaenable = (isset($schemaenable) && $schemaenable) ? $schemaenable : '';
$schemarender = (!empty($schemaenable)) ? ' itemtype="http://schema.org/Product" itemscope' : '';
$schemaoffer = (!empty($schemaenable)) ? ' itemprop="offers" itemtype="http://schema.org/Offer" itemscope' : '';
$schemarating = (!empty($schemaenable)) ? ' itemprop="aggregateRating" itemtype="http://schema.org/AggregateRating" itemscope' : '';
$schemaname = (!empty($schemaenable)) ? 'itemprop="name"' : '';
$schemadescription = (!empty($schemaenable)) ? 'itemprop="description"' : '';
$schemaurl = (!empty($schemaenable)) ? ' itemprop="url"' : '';
$offer_thumbhtml = (!empty($offer_thumbhtml)) ? $offer_thumbhtml : '';
?>
<?php if (!empty($btnwoo)) : ?>
    <?php if ($product->get_type() == 'external') : ?>
        <?php $afflink = $product->add_to_cart_url();
        $afftarget = ' target="_blank" rel="nofollow sponsored"'; ?>
    <?php else : ?>
        <?php $afflink = get_post_permalink($id);
        $afftarget = ''; ?>
    <?php endif; ?>
<?php else : ?>
    <?php $afflink = $offer_url;
    $afftarget = ' target="_blank" rel="nofollow sponsored"'; ?>
<?php endif; ?>
<?php $afflink = apply_filters('rh_post_offer_url_filter', $afflink); ?>
<?php if (!isset($title_tag)) $title_tag = 'h3'; ?>

<?php $coupon_style = $expired = '';
if (!empty($offer_coupon_date)) : ?>
    <?php
    $timestamp1 = strtotime($offer_coupon_date);
    if(strpos($offer_coupon_date, ':') ===false){
        $timestamp1 += 86399;
    }
    $seconds = $timestamp1 - (int)current_time('timestamp', 0);
    $days = floor($seconds / 86400);
    $seconds %= 86400;
    if ($days > 0) {
        $coupon_text = $days . ' ' . esc_html__('days left', 'rehub-theme');
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
<?php
if (empty($offer_coupon_mask_text)) {
    if (rehub_option('rehub_mask_text') != '') {
        $offer_coupon_mask_text = rehub_option('rehub_mask_text');
    } else {
        $offer_coupon_mask_text = esc_html__('Reveal', 'rehub-theme');
    }
}
$styles = '';
if (isset($inline_styles)) {
    $styles .= ' style="' . esc_attr($inline_styles) . '"';
}
?>
<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask == '1' || $offer_coupon_mask == 'on') && $expired != '1') ? '1' : ''; ?> <?php $reveal_enabled = ($coupon_mask_enabled == '1') ? ' reveal_enabled' : ''; ?>
<div class="bigofferblock margincenter mb30 pt20 pl20 pr20 <?php echo '' . $reveal_enabled; echo '' . $coupon_style; ?>" <?php echo '' . $styles; ?><?php echo '' . $schemarender; ?>>
    <?php if ($schemaenable) : ?>
        <meta itemprop="mpn" content="<?php echo esc_attr($schemafields['mpn']); ?>" />
        <meta itemprop="sku" content="<?php echo esc_attr($schemafields['sku']); ?>" />
        <link itemprop="image" href="<?php echo esc_url($offer_thumb); ?>" />
    <?php endif; ?>
    <div class="col_wrap_two mb0">
        <div class="offerbox_big_wrap flowhidden">
            <div class="image position-relative text-center col_item mobileblockdisplay img-maxh-350 img-width-auto">
                <?php if($offer_thumbhtml):?>
                    <?php echo wp_kses_post($offer_thumbhtml);?>
                <?php else:?>
                    <a class="re_track_btn" href="<?php echo esc_url($afflink); ?>" <?php echo '' . $afftarget; ?>>
                        <?php WPSM_image_resizer::show_static_resized_image(array('src' => $offer_thumb, 'width' => 500, 'title' => $offer_title)); ?>
                        <?php if (!empty($percentageSaved)) : ?>
                            <span class="sale_a_proc">
                                <?php
                                echo '-' . $percentageSaved . '%';; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="product-summary col_item mobileblockdisplay">
                <<?php echo '' . $title_tag; ?> class="product_title entry-title font150 lineheight25 mb30">
                    <a class="re_track_btn" href="<?php echo esc_url($afflink); ?>" <?php echo '' . $afftarget; ?>>
                        <span <?php echo '' . $schemaname; ?>><?php echo wp_kses_post($offer_title); ?></span>
                    </a>
                </<?php echo '' . $title_tag; ?>>
                <?php if ((int)$rating > 0 && (int)$rating <= 5) : ?>
                    <div class="cegg-rating mb15 flowhidden font130" <?php echo '' . $schemarating; ?>>
                        <?php if ($schemaenable) : ?>
                            <meta itemprop="reviewCount" content="<?php echo (int)$schemafields['count']; ?>" />
                            <meta itemprop="ratingValue" content="<?php echo (float)$rating; ?>" />
                        <?php endif; ?>
                        <?php
                        echo str_repeat("<span class='orangecolor'>&#x2605;</span>", (int)$rating);
                        echo str_repeat("<span>☆</span>", 5 - (int)$rating);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($offer_price) : ?>
                    <div class="deal-box-price font200 fontbold mb10 redbrightcolor redcolor">
                        <?php echo esc_html($offer_price); ?>
                        <?php if ($offer_price_old) : ?>
                            <span class="retail-old font70 lightgreycolor retail-old">
                                <strike><?php echo esc_html($offer_price_old); ?></strike>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($disclaimer) : ?>
                    <div class="rev_disclaimer font70 greencolor lineheight15 mb20"><?php echo wp_kses($disclaimer, 'post'); ?></div>
                <?php endif; ?>

                <div class="buttons_col mb25 rh_big_btn_inline">
                    <div class="priced_block clearfix" <?php echo '' . $schemaoffer; ?>>
                        <?php if ($schemaenable) : ?>
                            <meta itemprop="availability" content="https://schema.org/InStock" />
                            <meta itemprop="priceCurrency" content="<?php echo esc_attr($schemafields['currency']); ?>" />
                            <meta itemprop="itemCondition" content="https://schema.org/NewCondition" />
                            <?php if(!empty($schemafields['price'])):?>
                                <?php $schemaprice = $schemafields['price'];?>
                            <?php else:?>
                                <?php $schemaprice = (float)$offer_price;?>
                            <?php endif;?>
                            <meta itemprop="price" content="<?php echo esc_attr($schemaprice); ?>" />
                            <?php if ($offer_coupon_date) : ?>
                                <meta itemprop="priceValidUntil" content="<?php echo esc_attr($offer_coupon_date); ?>" />
                            <?php endif; ?>
                        <?php endif; ?>
                        <div>
                            <?php if (!empty($btnwoo)) : ?>
                                <?php echo '' . $btnwoo; ?>
                            <?php else : ?>
                                <a class="re_track_btn btn_offer_block" href="<?php echo esc_url($afflink); ?>" <?php echo '' . $afftarget; ?><?php echo ''.$schemaurl;?>>
                                    <?php if ($offer_btn_text != '') : ?>
                                        <?php echo '' . $offer_btn_text; ?>
                                    <?php elseif (rehub_option('rehub_btn_text') != '') : ?>
                                        <?php echo rehub_option('rehub_btn_text'); ?>
                                    <?php else : ?>
                                        <?php esc_html_e('Buy this item', 'rehub-theme') ?>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($offer_coupon)) :
                            wp_enqueue_script('zeroclipboard');
                            if (!$offer_coupon_mask) :
                                echo '<div class="rehub_offer_coupon mt15 not_masked_coupon ';
                                if (!empty($offer_coupon_date)) :
                                    echo '' . $coupon_style;
                                endif;
                                echo '" data-clipboard-text="' . $offer_coupon . '"><i class="rhicon rhi-scissors fa-rotate-180"></i><span class="coupon_text">' . $offer_coupon . '</span></div>';
                            else :
                                wp_enqueue_script('affegg_coupons');
                                echo '<div class="coupon_btn re_track_btn btn_offer_block rehub_offer_coupon masked_coupon ';
                                if (!empty($offer_coupon_date)) :
                                    echo '' . $coupon_style;
                                endif;
                                echo '" data-clipboard-text="' . rawurlencode(esc_html($offer_coupon)) . '" data-codetext="' . rawurlencode(esc_html($offer_coupon)) . '" data-dest="' . esc_url($offer_url) . '">' . $offer_coupon_mask_text . '<i class="rhicon rhi-external-link-square"></i></div>';
                            endif;
                        endif;
                        if (!empty($offer_coupon_date)) :
                            echo '<div class="time_offer">' . $coupon_text . '</div>';
                        endif;
                        ?>
                    </div>
                </div>

                <?php if ($offer_desc) : ?>
                    <div class="bigofferdesc" <?php echo ''.$schemadescription;?>><?php echo '' . $offer_desc; ?></div>
                <?php endif; ?>
                <?php if ($schemaenable) : ?>
                    <div itemprop="brand" itemtype="http://schema.org/Brand" itemscope>
                        <meta itemprop="name" content="<?php echo esc_attr($schemafields['brand']); ?>" />
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>