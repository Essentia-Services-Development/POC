<?php
/*
 * Name: Price comparison card
 * Modules:
 * Module Types: PRODUCT
 * 
 */

use ContentEgg\application\helpers\TemplateHelper;

if (!$all_items = TemplateHelper::sortAllByPrice($data, $order))
    return;

if (TemplateHelper::isModuleDataExist($all_items, 'Amazon', 'AmazonNoApi'))
    \wp_enqueue_script('cegg-frontend', \ContentEgg\PLUGIN_RES . '/js/frontend.js', array('jquery'));

$amazon_last_updated = TemplateHelper::getLastUpdateFormattedAmazon($data);
$item = reset($all_items);

if (!$title)
    $title = $item['title'];

?>


<div class="egg-container egg-price-comparison-card">
    <div class="products">

        <div class="row">
            <div class="col-sm-6 text-center cegg-image-container cegg-mb20">
                <?php if ($item['img']): ?>
                    <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">                    
                        <?php TemplateHelper::displayImage($item, 350, 350); ?>                        
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <h3 class="cegg-item-title"><?php echo \esc_html($title); ?></h3>

                <div class="list-group cegg-mt10">

                    <?php foreach ($all_items as $key => $item): ?>    
                        <a<?php TemplateHelper::printRel(); ?> class="list-group-item" target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
                            <?php echo \esc_html(TemplateHelper::getMerhantName($item)); ?>
                            <?php if ($item['price']): ?>
                                <span<?php if ($item['stock_status'] != -1) echo ' style="background-color: ' . esc_attr(TemplateHelper::getPriceColor()) . '"'; ?> class="cegg-price-badge"><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'])); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>            

                </div>   

                <?php if ($amazon_last_updated): ?>
                    <div class="cegg-font60 cegg-lineheight15 text-right">
                        <?php echo esc_html(sprintf(TemplateHelper::__('Last Amazon price update was: %s'), $amazon_last_updated)); ?>
                        <?php TemplateHelper::printAmazonDisclaimer(); ?>
                    </div>
                <?php endif; ?>                     

            </div>
        </div>
    </div>
</div>