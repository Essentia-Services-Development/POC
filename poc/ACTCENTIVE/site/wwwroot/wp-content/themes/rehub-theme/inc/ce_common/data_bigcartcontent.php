<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<?php

use ContentEgg\application\helpers\TemplateHelper; ?>

<div class="col_wrap_two">
    <div class="product_egg single_product_egg">

        <div class="image col_item">
            <a <?php echo ce_printRel(); ?> target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>">
                <?php WPSM_image_resizer::show_static_resized_image(array('src' => $aff_thumb, 'width' => 500, 'title' => $offer_title)); ?>
                <?php if ($percentageSaved) : ?>
                    <span class="sale_a_proc">
                        <?php
                        echo '-' . $percentageSaved . '%';; ?>
                    </span>
                <?php endif; ?>
            </a>
            <?php if (!empty($item['extra']['itemLinks'][3])) : ?>
                <span class="add_wishlist_ce">
                    <a href="<?php echo esc_url($item['extra']['itemLinks'][3]['URL']); ?>" <?php echo ce_printRel(); ?> target="_blank"><i class="rhicon rhi-heart"></i> <?php echo esc_attr($item['extra']['itemLinks'][3]['Description']); ?></a>
                </span>
            <?php endif; ?>
        </div>

        <div class="product-summary col_item">

            <?php if ($showtitle == 1) : ?>
                <h2 class="product_title entry-title">
                    <a <?php echo ce_printRel(); ?> target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>">
                        <?php echo esc_attr($offer_title); ?>
                    </a>
                </h2>
            <?php endif; ?>

            <?php if ((int) $item['rating'] > 0 && (int) $item['rating'] <= 5) : ?>
                <div class="cegg-rating">
                    <?php
                    echo str_repeat("<span>&#x2605;</span>", (int) $item['rating']);
                    echo str_repeat("<span>☆</span>", 5 - (int) $item['rating']);
                    ?>
                </div>
            <?php elseif (!empty($item['extra']['data']['rating'])) : ?>
                <div class="cegg-rating">
                    <?php
                    echo str_repeat("<span>&#x2605;</span>", $item['extra']['data']['rating']);
                    echo str_repeat("<span>☆</span>", 5 - $item['extra']['data']['rating']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (method_exists('TemplateHelper', 'getCashbackStr') && $cashback_str = TemplateHelper::getCashbackStr($item)) : ?>
                <div class="font90 inlinestyle mb10 rehub-main-color"><?php echo sprintf(__('Plus %s CashBack', 'rehub-theme'), $cashback_str); ?></div>
            <?php endif; ?>

            <?php if ($offer_price) : ?>
                <div class="deal-box-price">
                    <?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code, '<span class="cur_sign">', '</span>'); ?>
                    <?php if ($offer_price_old) : ?>
                        <span class="retail-old">
                            <strike><?php echo TemplateHelper::formatPriceCurrency($offer_price_old, $currency_code, '<span class="value">', '</span>'); ?></strike>
                        </span>
                    <?php endif; ?>
                    <?php $stock_status_str = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusStr($item) : ''; ?>
                    <?php $stock_status_class = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusClass($item) : ''; ?>
                    <?php if ($stock_status_str) : ?>
                        <mark title="<?php esc_html_e('Last update was on: ', 'rehub-theme'); ?><?php echo TemplateHelper::getLastUpdateFormatted($item['module_id'], $post_id); ?>" class="rh-stock-status status-<?php echo esc_attr($stock_status_class); ?>">
                            <?php echo esc_html($stock_status_str); ?>
                        </mark>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($module_id == 'Ebay') : ?>
                <?php $time_left = TemplateHelper::getTimeLeft($item['extra']['listingInfo']['endTimeGmt']); ?>
                <small class="small_size">
                    <?php if ($time_left) : ?>
                        <span class="time_left_ce yes_available">
                            <i class="rhicon rhi-clock"></i> <?php esc_html_e('Time left:', 'rehub-theme'); ?>
                            <span <?php if (strstr($time_left, 'm')) echo 'class="text-danger"'; ?>><?php echo '' . $time_left; ?></span>
                        </span>
                        <br />
                    <?php else : ?>
                        <span class="time_left_ce">
                            <span class='text-warning'>
                                <?php esc_html_e('Ended:', 'rehub-theme'); ?>
                                <?php echo date('M j, H:i', strtotime($item['extra']['listingInfo']['endTime'])); ?> <?php echo '' . $item['extra']['listingInfo']['timeZone']; ?>
                            </span>
                        </span>
                        <br />
                    <?php endif; ?>
                    <?php if (!empty($item['extra']['conditionDisplayName'])) : ?>
                        <?php esc_html_e('Condition: ', 'rehub-theme'); ?><span><?php echo '' . $item['extra']['conditionDisplayName']; ?></span>
                        <br />
                    <?php endif; ?>
                </small>
            <?php endif; ?>

            <?php if (!empty($item['extra']['totalNew'])) : ?>
                <span class="new-or-used-amazon">
                    <?php echo (int)$item['extra']['totalNew']; ?>
                    <?php esc_html_e('new', 'rehub-theme'); ?>
                    <?php if ($item['extra']['lowestNewPrice']) : ?>
                        <?php esc_html_e('from', 'rehub-theme'); ?>
                        <?php echo TemplateHelper::formatPriceCurrency($item['extra']['lowestNewPrice'], $item['currencyCode']); ?>
                    <?php endif; ?>
                    <br>
                </span>
            <?php endif; ?>
            <?php if (!empty($item['extra']['totalUsed'])) : ?>
                <span class="new-or-used-amazon">
                    <?php echo (int)$item['extra']['totalUsed']; ?>
                    <?php esc_html_e('used', 'rehub-theme'); ?> <?php esc_html_e('from', 'rehub-theme'); ?>
                    <?php echo TemplateHelper::formatPriceCurrency($item['extra']['lowestUsedPrice'], $item['currencyCode']); ?>
                    <br>
                </span>
            <?php endif; ?>
            <?php if (!empty($item['extra']['IsEligibleForSuperSaverShipping'])) : ?>
                <small class="small_size"><span class="yes_available"><?php esc_html_e('Free shipping', 'rehub-theme'); ?></span></small><br>
            <?php endif; ?>

            <div class="buttons_col">
                <div class="priced_block clearfix">
                    <div>
                        <a class="re_track_btn btn_offer_block" href="<?php echo esc_url($afflink) ?>" target="_blank" <?php echo ce_printRel(); ?>>
                            <?php echo esc_attr($btn_txt); ?>
                        </a>
                    </div>
                </div>
                <span class="aff_tag">
                    <img src="<?php echo esc_attr(TemplateHelper::getMerhantIconUrl($item, true)); ?>" alt="<?php echo '' . $module_id; ?>" />
                    <?php if ($merchant) : ?>
                        <?php echo esc_html($merchant); ?>
                    <?php elseif ($domain) : ?>
                        <?php echo esc_html($domain); ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php if ($merchanttext) : ?>
                <div class="font80">
                    <?php echo '' .$merchanttext; ?>
                </div>
            <?php endif; ?>
            <div class="font80 rh_opacity_7 mb15"><?php esc_html_e('Last update was on: ', 'rehub-theme'); ?><?php echo TemplateHelper::getLastUpdateFormatted($module_id, $post_id); ?></div>

            <?php if ($features) : ?>
                <p>
                <ul class="featured_list">
                    <?php $length = $maxlength = 0; ?>
                    <?php foreach ($item['extra']['itemAttributes']['Feature'] as $k => $feature) : ?>
                        <?php if (is_array($feature)) {
                            continue;
                        } ?>
                        <?php $length = strlen($feature);
                        $maxlength += $length; ?>
                        <li><?php echo esc_attr($feature); ?></li>
                        <?php if ($k >= 5 || $maxlength > 400) break; ?>
                    <?php endforeach; ?>
                </ul>
                </p>
            <?php elseif ($keyspecs) : ?>
                <p>
                <ul class="featured_list">
                    <?php foreach ($keyspecs as $keyspec) : ?>
                        <li><?php echo esc_attr($keyspec); ?></li>
                    <?php endforeach; ?>
                </ul>
                </p>
            <?php elseif ($description) : ?>
                <p><?php echo '' . $description; ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="clearfix"></div>