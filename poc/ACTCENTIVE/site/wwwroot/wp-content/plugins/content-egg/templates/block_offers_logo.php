<?php
/*
 * Name: Sorted offers list with store logos
 * Modules:
 * Module Types: PRODUCT
 * 
 */

__('Sorted offers list with store logos', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\helpers\TextHelper;

if (isset($data['Amazon']) || isset($data['AmazonNoApi']))
    \wp_enqueue_script('cegg-frontend', \ContentEgg\PLUGIN_RES . '/js/frontend.js', array('jquery'));

$all_items = TemplateHelper::sortAllByPrice($data, $order);
$amazon_last_updated = TemplateHelper::getLastUpdateFormattedAmazon($data);
?>

<div class="egg-container">
    <?php if ($title): ?>
        <h3><?php echo \esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="egg-listcontainer cegg-list-withlogos">
        <?php foreach ($all_items as $key => $item): ?>  
            <div class="cegg-list-logo-title cegg-mt10 visible-xs text-center">
                <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"><?php echo esc_html(TextHelper::truncate($item['title'], 100)); ?></a>
            </div>

            <div class="row-products">
                <div class="col-md-2 col-sm-2 col-xs-12 cegg-image-cell">
                    <?php if ($logo = TemplateHelper::getMerhantLogoUrl($item, true)): ?>
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
                            <img class="cegg-merhant-logo" src="<?php echo \esc_attr($logo); ?>" alt="<?php echo \esc_attr($item['domain']); ?>" />
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-md-5 col-sm-5 col-xs-12 cegg-desc-cell hidden-xs">
                    <div class="cegg-no-top-margin cegg-list-logo-title">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"><?php echo esc_html(TextHelper::truncate($item['title'], 100)); ?></a>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3 col-xs-12 cegg-price-cell text-center">
                    <div class="cegg-price-row">

                        <?php if ($item['price']): ?>
                            <div class="cegg-price cegg-price-color cegg-price-<?php echo \esc_attr(TemplateHelper::getStockStatusClass($item)); ?>"><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'])); ?></div>
                        <?php endif; ?> 
                        <?php if ($item['priceOld']): ?>
                            <div class="text-muted"><s><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['priceOld'], $item['currencyCode'])); ?></s></div>
                        <?php endif; ?>
                        <?php if ($stock_status = TemplateHelper::getStockStatusStr($item)): ?>
                            <div title="<?php echo \esc_attr(sprintf(TemplateHelper::__('Last updated on %s'), TemplateHelper::getLastUpdateFormatted($item['module_id'], $post_id))); ?>" class="cegg-lineheight15 stock-status status-<?php echo \esc_attr(TemplateHelper::getStockStatusClass($item)); ?>">
                                <?php echo \esc_html($stock_status); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($item['module_id'] == 'Amazon'): ?>

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
                        <?php endif; ?>

                        <?php if ($item['module_id'] == 'Amazon' || $item['module_id'] == 'AmazonNoApi'): ?>
                            <div class="cegg-font60 cegg-lineheight15">
                                <?php echo esc_html(sprintf(TemplateHelper::__('as of %s'), TemplateHelper::dateFormatFromGmt($item['last_update']))); ?>
                                <?php TemplateHelper::printAmazonDisclaimer(); ?>
                            </div>
                        <?php endif; ?>

                    </div> 
                </div>                    
                <div class="col-md-2 col-sm-2 col-xs-12 cegg-btn-cell">        
                    <div class="cegg-btn-row">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>" class="btn btn-danger btn-block"><span><?php TemplateHelper::buyNowBtnText(true, $item, $btn_text); ?></span></a> 
                    </div>  
                    <?php if ($merchant = TemplateHelper::getMerchantName($item)): ?>
                        <div class="text-center">
                            <small class="text-muted title-case">
                                <?php echo \esc_html($merchant); ?>
                                <?php TemplateHelper::printShopInfo($item); ?>
                            </small>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
