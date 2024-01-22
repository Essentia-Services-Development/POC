<?php
/*
 * Name: List widget with store logos
 * Modules:
 * Module Types: PRODUCT
 * 
 */
?>
<?php

use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\helpers\CurrencyHelper;
// sort items by price
?>
<?php
$all_items = TemplateHelper::sortAllByPrice($data);
?>

<div class="widget_logo_list">

    <?php foreach ($all_items as $key => $item) : ?>
        <?php $stock_status_str = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusStr($item) : ''; ?>
        <?php $stock_status_class = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusClass($item) : ''; ?>
        <?php $offer_post_url = $item['url']; ?>
        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url); ?>
        <?php $aff_thumb = $item['img']; ?>
        <?php $offer_title = wp_trim_words($item['title'], 10, '...'); ?>
        <?php $merchant = (!empty($item['merchant'])) ? $item['merchant'] : ''; ?>
        <?php $manufacturer = (!empty($item['manufacturer'])) ? $item['manufacturer'] : ''; ?>
        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
        <?php $offer_price_old = (!empty($item['priceOld'])) ? $item['priceOld'] : ''; ?>
        <?php $currency_code = (!empty($item['currencyCode'])) ? $item['currencyCode'] : ''; ?>
        <?php $modulecode = (!empty($item['module_id'])) ? $item['module_id'] : ''; ?>
        <?php if (!empty($item['domain'])) : ?>
            <?php $domain = $item['domain']; ?>
        <?php elseif (!empty($item['extra']['domain'])) : ?>
            <?php $domain = $item['extra']['domain']; ?>
        <?php else : ?>
            <?php $domain = ''; ?>
        <?php endif; ?>
        <?php $domain = rh_fix_domain($merchant, $domain); ?>
        <?php if (empty($merchant) && !empty($domain)) {
            $merchant = $domain;
        }
        ?>
        <?php $lowestused_price = (!empty($item['extra']['lowestUsedPrice'])) ? $item['extra']['lowestUsedPrice'] : ''; ?>
        <?php if ($offer_price && rehub_option('ce_custom_currency')) {
            $currency_code = rehub_option('ce_custom_currency');
            $currency_rate = CurrencyHelper::getCurrencyRate($item['currencyCode'], $currency_code);
            if (!$currency_rate) $currency_rate = 1;
            $offer_price = $offer_price * $currency_rate;
            if ($offer_price_old) {
                $offer_price_old = $offer_price_old * $currency_rate;
            }
        } ?>
        <?php $logo = TemplateHelper::getMerhantLogoUrl($item, true); ?>
        <div class="table_div_list module_class_<?php echo esc_attr($modulecode); ?><?php if ($item['domain'] == 'amazon.com') : ?> amazoncom<?php endif; ?> rh_stock_<?php echo esc_attr($stock_status_class); ?>">
            <a <?php echo ce_printRel(); ?> target="_blank" href="<?php echo esc_url($afflink) ?>" class="re_track_btn" data-tracking-group="<?php echo esc_attr($modulecode); ?>">
                <div class="offer_thumb<?php if (!$logo) {
                                            echo ' nologo_thumb';
                                        } ?>">
                    <?php if ($logo) : ?>
                        <?php if ($item['domain'] == 'amazon.com') : ?>
                            <img src="<?php echo get_template_directory_uri() . '/images/logos/amazonbuy.gif'; ?>" alt="<?php echo esc_attr($offer_title); ?>" height="30" />
                        <?php else : ?>
                            <img src="<?php echo esc_attr(TemplateHelper::getMerhantLogoUrl($item, true)); ?>" alt="<?php echo esc_attr($offer_title); ?>" height="30" />
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="price_simple_col">
                    <?php if (!empty($item['price'])) : ?>
                        <div>
                            <span class="val_sim_price">
                                <?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code); ?>
                            </span>
                            <?php if (!empty($item['extra']['totalUsed'])) : ?>
                                <span class="val_sim_price_used_merchant">
                                    <?php esc_html_e('Used', 'rehub-theme'); ?> - <?php echo TemplateHelper::formatPriceCurrency($lowestused_price, $currency_code); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php $merchanttext = \ContentEgg\application\helpers\TemplateHelper::getShopInfo($item); ?>
                            <?php if ($merchanttext) : ?>
                                <span class="font80">
                                    <?php echo '' . $merchanttext; ?>
                                </span>
                            <?php endif; ?>
                    <span class="vendor_sim_price"><?php echo esc_attr($merchant); ?> </span>
                    <?php if ($stock_status_class == 'outofstock') : ?>
                        <span class="blockstyle redbrightcolor font80"><?php echo esc_attr($stock_status_str); ?></span>
                    <?php endif; ?>
                </div>
                <div class="buttons_col">
                    <i class="rhicon rhi-chevron-circle-right" aria-hidden="true"></i>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<div class="clearfix"></div>