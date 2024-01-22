<?php
/*
 * Name: Price comparison widget
 * Modules:
 * Module Types: PRODUCT
 * 
 */

__('Price comparison widget', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;
?>

<?php
$all_items = TemplateHelper::sortAllByPrice($data, $order);
$amazon_last_updated = TemplateHelper::getLastUpdateFormattedAmazon($data);
?>

<div class="egg-container">
    <?php if ($title): ?>
        <h3><?php echo \esc_html($title); ?></h3>
    <?php endif; ?>

    <table class="cegg-price-comparison table table-hover table-condensed table-striped1">
        <tbody>
            <?php foreach ($all_items as $key => $item): ?>           
                <tr>
                    <td class="cegg-merhant_col">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">                    
                            <?php $merhant_ico = TemplateHelper::getMerhantIconUrl($item, true); ?>
                            <?php if ($merhant_ico): ?><img src="<?php echo esc_attr($merhant_ico); ?>" alt="<?php echo esc_attr($item['domain']); ?>" /><?php endif; ?>
                            <?php if ($merhant = TemplateHelper::getMerhantName($item)): ?>
                                <span class="title-case"> <?php echo esc_html($merhant); ?></span>
                            <?php endif; ?>
                        </a>
                    </td>
                    <td class="cegg-price_col text-center">
                        <?php if ($item['price']): ?>
                            <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">                    
                                <?php echo esc_html(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'])); ?>
                            <?php endif; ?>

                            <?php if (isset($item['stock_status']) && $item['stock_status'] == \ContentEgg\application\components\ContentProduct::STOCK_STATUS_OUT_OF_STOCK): ?>
                                <div title="<?php echo \esc_attr(sprintf(TemplateHelper::__('Last updated on %s'), TemplateHelper::getLastUpdateFormatted($item['module_id'], $post_id))); ?>" class="cegg-font60 cegg-lineheight15 stock-status status-<?php echo \esc_attr(TemplateHelper::getStockStatusClass($item)); ?>">
                                    <?php echo \esc_html(TemplateHelper::getStockStatusStr($item)); ?>
                                </div>
                            <?php endif; ?>                                

                            <?php if (!empty($item['extra']['totalNew']) && $item['extra']['totalNew'] > 1): ?>
                                <div class="cegg-font60 cegg-lineheight15">
                                    <?php echo esc_html(sprintf(TemplateHelper::__('%d new from %s'), $item['extra']['totalNew'], TemplateHelper::formatPriceCurrency($item['extra']['lowestNewPrice'], $item['currencyCode']))); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($item['extra']['totalUsed'])): ?>
                                <div class="cegg-font60 cegg-lineheight15">
                                    <?php echo esc_html(sprintf(TemplateHelper::__('%d used from %s'), $item['extra']['totalUsed'], TemplateHelper::formatPriceCurrency($item['extra']['lowestUsedPrice'], $item['currencyCode']))); ?>
                                </div>
                            <?php endif; ?>


                        </a>

                    </td>
                    <td class="cegg-buttons_col" style="background-color:<?php echo esc_attr(TemplateHelper::getButtonColor()); ?>;">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">                    
                            <?php TemplateHelper::buyNowBtnText(true, $item, $btn_text); ?>
                        </a>
                    </td>


                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($amazon_last_updated): ?>
        <div class="cegg-font60 cegg-lineheight15 text-right">
            <?php echo esc_html(sprintf(TemplateHelper::__('Last Amazon price update was: %s'), $amazon_last_updated)); ?>
            <?php TemplateHelper::printAmazonDisclaimer(); ?>        
        </div>
    <?php endif; ?>
    <div class="cegg-mb15"></div>
</div>