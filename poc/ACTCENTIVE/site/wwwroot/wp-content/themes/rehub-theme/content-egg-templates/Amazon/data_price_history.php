<?php
/*
  Name: Price history full table
 */

use ContentEgg\application\helpers\TemplateHelper;
?>

<?php foreach ($items as $item): ?>
    <?php 
        $pricesarray = TemplateHelper::priceHistoryPrices($item['unique_id'], $module_id, $limit = 2);
        $pricescheck = '';
        if (!empty($pricesarray) && is_array($pricesarray)){
            $pricescheck = (count($pricesarray) > 1) ? true : '';
        }        
    ?>
    <?php if (!empty($pricescheck)) :?>
    <table class="border-lightgrey clearbox flowhidden mb25 rh-shadow1 rh-tabletext-block whitebg width-100p">
        <tr>
        <th class="rh-tabletext-block-heading fontbold border-grey-bottom" colspan="2"><?php esc_html_e('Price history for ', 'rehub-theme');?><?php echo esc_attr($item['title']); ?></th>
        </tr>
        <tr>
        <td class="rh-tabletext-block-left padd15 verttop">
 
            <div class="rh-tabletext-block-latest"> 
            <div class="mb10"><strong><?php esc_html_e('Latest updates:', 'rehub-theme');?></strong></div>                             
            <?php $prices = TemplateHelper::priceHistoryPrices($item['unique_id'], $module_id, $limit = 7); ?>
            <?php if ($prices): ?>
                <ul>
                    <?php foreach ($prices as $price): ?>
                        <li>
                            <?php echo TemplateHelper::formatPriceCurrency($price['price'], $item['currencyCode']); ?>                    
                            - <?php echo date(get_option('date_format'), $price['date']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php $since = TemplateHelper::priceHistorySinceDate($item['unique_id'], $module_id); ?>
            <?php if ($since): ?>
                <?php esc_html_e('Since:', 'rehub-theme');?> <?php echo date(get_option('date_format'), $since); ?>
            <?php endif; ?>                    
            </div>
        </td>
        <td class="rh-tabletext-block-right padd15 verttop">
            <div class="rh-table-price-graph">
            <?php TemplateHelper::priceHistoryMorrisChart($item['unique_id'], $module_id, 180, array('lineWidth' => 2, 'postUnits' => ' ' . $item['currencyCode'], 'goals' => array((int) $item['price']), 'fillOpacity' => 0.5), array('style' => 'height: 230px;')); ?>
            </div>
            <ul class="rh-lowest-highest">
                <?php $price = TemplateHelper::priceHistoryMax($item['unique_id'], $module_id); ?>
                <?php if ($price): ?>
                    <li>
                        <b style="color: red;"><?php esc_html_e('Highest Price:', 'rehub-theme');?></b> 
                        <?php echo TemplateHelper::formatPriceCurrency($price['price'], $item['currencyCode']); ?> 
                        - <?php echo date(get_option('date_format'), $price['date']); ?>
                    </li>
                <?php endif; ?>

                <?php $price = TemplateHelper::priceHistoryMin($item['unique_id'], $module_id); ?>
                <?php if ($price): ?>
                    <li>
                        <b style="color: green;"><?php esc_html_e('Lowest Price:', 'rehub-theme');?></b> 
                        <?php echo TemplateHelper::formatPriceCurrency($price['price'], $item['currencyCode']); ?>   
                        - <?php echo date(get_option('date_format'), $price['date']); ?>
                    </li>
                <?php endif; ?>
            </ul>            
        </td>
        </tr>                
    </table>
    <?php endif;?>
<?php endforeach; ?>