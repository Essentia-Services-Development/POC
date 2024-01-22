<?php
/*
 * Name: Text links
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
?>


<div class="egg-container egg-price-text-links">
    <ul>
        <?php foreach ($all_items as $key => $item): ?>    
            <li>
                <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"><?php echo \esc_html(TemplateHelper::truncate($item['title'], 80)); ?></a>
                &mdash;
                <?php if ($item['price']): ?>
                    <b><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'])); ?></b>
                <?php endif; ?> 
                <?php if ($item['priceOld']): ?>
                <strike class="text-muted"><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['priceOld'], $item['currencyCode'])); ?></strike>
            <?php endif; ?>
            </li>
        <?php endforeach; ?>  
    </ul>

    <?php if ($amazon_last_updated): ?>
        <div class="cegg-font60 cegg-lineheight15 text-right">
            <?php echo esc_html(sprintf(TemplateHelper::__('Last Amazon price update was: %s'), $amazon_last_updated)); ?>
            <?php TemplateHelper::printAmazonDisclaimer(); ?>
        </div>
    <?php endif; ?>                     

</div>