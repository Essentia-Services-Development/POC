<?php defined( '\ABSPATH' ) || exit; ?>
<?php

use Keywordrush\AffiliateEgg\TemplateHelper;

?>
<h4><?php _e('Price History', 'affegg-tpl'); ?></h4>
<?php TemplateHelper::priceHistoryMorrisChart($item['id'], 180, array('lineWidth' => 2, 'postUnits' => ' ' . $item['currency_code'], 'goals' => array($item['price_raw']), 'fillOpacity' => 0.5), array('style' => 'height: 220px;')); ?>

<?php $prices = TemplateHelper::priceHistoryPrices($item['id'], $limit = 5); ?>
<?php if ($prices): ?>

    <div class="row">
        <div class='col-md-7'>
            <h4><?php _e('Statistics', 'affegg-tpl'); ?></h4>
            <table class="table table-hover">
                <tr>
                    <td><?php _e('Current Price', 'affegg-tpl'); ?></td> 
                    <td>
                        <?php if ($item['price']): ?>
                            <?php echo TemplateHelper::formatPriceCurrency($item['price_raw'], $item['currency_code']); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?php echo TemplateHelper::getLastUpdateFormatted($item['id'], false, false); ?></td>
                </tr>        
                <?php $price = TemplateHelper::priceHistoryMax($item['id']); ?>
                <tr>
                    <td class="text-danger"><?php _e('Highest Price', 'affegg-tpl'); ?></td> 
                    <td><?php echo TemplateHelper::formatPriceCurrency($price['price'], $item['currency_code']); ?></td>
                    <td><?php echo date(get_option('date_format'), $price['date']); ?></td>
                </tr>
                <?php $price = TemplateHelper::priceHistoryMin($item['id']); ?>
                <tr>
                    <td class="text-success"><?php _e('Lowest Price', 'affegg-tpl'); ?></td> 
                    <td><?php echo TemplateHelper::formatPriceCurrency($price['price'], $item['currency_code']); ?></td>
                    <td><?php echo date(get_option('date_format'), $price['date']); ?></td>
                </tr>
            </table>   
            <?php $since = TemplateHelper::priceHistorySinceDate($item['id']); ?>
            <div class='text-right text-muted'><?php _e('Since', 'affegg-tpl'); ?> <?php echo date(get_option('date_format'), $since); ?></div>
        </div>
        <div class='col-md-5'>
            <h4><?php _e('Last price changes', 'affegg-tpl'); ?></h4>
            <table class="table table-hover table-condensed">
                <?php foreach ($prices as $price): ?>
                    <tr>
                        <td><?php echo TemplateHelper::formatPriceCurrency($price['price'], $item['currency_code']); ?></td>
                        <td><?php echo date(get_option('date_format'), $price['date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>        

        </div>
    </div>
<?php endif; ?>
