<?php
/*
 * Name: Button with price comparison popup
 * Modules:
 * Module Types: PRODUCT
 * 
 */

use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\helpers\TextHelper;

\wp_enqueue_script('bootstrap-modal');

$all_items = TemplateHelper::sortAllByPrice($data, $order);
$item = TemplateHelper::selectItemByDescription($all_items);

if (!$btn_text)
    $btn_text = TemplateHelper::t('Shop %d Offers');

if (strstr($btn_text, '%d'))
    $btn_text = sprintf($btn_text, count($all_items));

$modal_id = TemplateHelper::generateGlobalId('cegg-popup-');
$modal_label = TemplateHelper::generateGlobalId('cegg-popup-label');

if (!empty($atts['btn_class']))
    $btn_class = $atts['btn_class'];
if (!isset($btn_class))
    $btn_class = '';
?>

<?php if (count($all_items) == 1): ?>
    <div class="egg-container egg-item-popup">
        <?php $item = reset($all_items); ?>
        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>" class="btn btn-danger <?php echo esc_attr($btn_class); ?>"><?php TemplateHelper::buyNowBtnText(true, $item); ?></a> 
    </div>
    <?php return; ?>
<?php endif; ?>

<div class="egg-container egg-item-popup">

    <button type="button" class="btn btn-danger <?php echo esc_attr($btn_class); ?>" data-toggle="modal" data-target="#<?php echo esc_attr($modal_id); ?>">
        <?php echo esc_html($btn_text); ?>
    </button>

    <div class="modal fade" id="<?php echo esc_attr($modal_id); ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo esc_attr($modal_label); ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="padding: 0px 5px 0px 5px"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="<?php echo esc_attr($modal_label); ?>">
                        <?php echo esc_html(TextHelper::truncate($item['title'], 100)); ?>
                    </h4>
                </div>
                <div class="modal-body">

                    <div class="egg-listcontainer cegg-list-withlogos">
                        <?php foreach ($all_items as $key => $item): ?>  

                            <div class="row-products">
                                <div class="col-md-2 col-sm-2 col-xs-12 cegg-image-cell">
                                    <?php if ($logo = TemplateHelper::getMerhantLogoUrl($item, true)): ?>
                                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
                                            <img class="cegg-merhant-logo" src="<?php echo \esc_attr($logo); ?>" alt="<?php echo \esc_attr($item['domain']); ?>" />
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-8 col-sm-8 col-xs-12 cegg-price-cell text-center">
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
                                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>" class="btn btn-danger btn-block"><span><?php TemplateHelper::buyNowBtnText(true, $item); ?></span></a> 
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
            </div>
        </div>
    </div>
</div>




