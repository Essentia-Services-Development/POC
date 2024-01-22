<?php
/*
 * Name: Price history table with graph
 * Modules:
 * Module Types: PRODUCT
 * Shortcoded: FALSE
 */
?>
<?php
use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\helpers\CurrencyHelper;
// sort items by price
?>
<?php $postid = (isset($post_id)) ? $post_id : get_the_ID();?>
<?php if (get_post_type($postid) == 'product'):?>
    <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($postid);
        $unique_id = $itemsync['unique_id']; $module_id = $itemsync['module_id'];?>
<?php else:?>
    <?php $unique_id = get_post_meta($postid, '_rehub_product_unique_id', true);?>
    <?php $module_id = get_post_meta($postid, '_rehub_module_ce_id', true);?>
<?php endif;?>

<?php if ($unique_id && $module_id) :?>
    <?php $syncitem = ($unique_id) ? $data[$module_id][$unique_id] : '';?>
    <?php $pricesarray = TemplateHelper::priceHistoryPrices($unique_id, $module_id, $limit = 3);
        $pricescheck = '';
        if (!empty($pricesarray) && is_array($pricesarray)){
            $pricescheck = (count($pricesarray) > 1) ? true : '';
        }
    ?>    
    <?php if (!empty($pricescheck) && !empty($syncitem)) :?>
        <?php 
            $currency_code = (!empty($syncitem['currencyCode'])) ? $syncitem['currencyCode'] : ''; 
            $currency_rate = 1; 
        ?>
        <?php if(rehub_option('ce_custom_currency')) {
            $currency_code = rehub_option('ce_custom_currency');
            $currency_rate = CurrencyHelper::getCurrencyRate($syncitem['currencyCode'], $currency_code);
            if (!$currency_rate) $currency_rate = 1;            
        }?>         
        <table class="border-lightgrey clearbox flowhidden mb25 rh-shadow1 rh-tabletext-block whitebg width-100p ce-price-hist">
            <style scoped>
                body .ce-price-hist ul li{padding: 0 0 5px 0; margin: 0; list-style: none !important;}
                body .ce-price-hist ul{margin: 0 0 10px 0}
                body .ce-price-hist ul.rh-lowest-highest {margin: 20px 0 0 10px}
            </style>
            <tr>
            <th class="rh-tabletext-block-heading fontbold border-grey-bottom" colspan="2"><?php esc_html_e('Price history for ', 'rehub-theme');?><?php echo esc_attr($syncitem['title']); ?></th>
            </tr>
            <tr>
            <td class="rh-tabletext-block-left padd15 verttop">

                <div class="rh-tabletext-block-latest"> 
                <div class="mb10"><strong><?php esc_html_e('Latest updates:', 'rehub-theme');?></strong></div>                             
                <?php $prices = TemplateHelper::priceHistoryPrices($unique_id, $module_id, $limit = 8); ?>
                <?php if ($prices): ?>
                    <ul>
                        <?php foreach ($prices as $price): ?>
                            <li>
                                <?php echo TemplateHelper::formatPriceCurrency($price['price']*$currency_rate, $currency_code); ?>                    
                                - <?php echo date_i18n(get_option('date_format'), $price['date']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php $since = TemplateHelper::priceHistorySinceDate($unique_id, $module_id); ?>
                <?php if ($since): ?>
                    <?php esc_html_e('Since:', 'rehub-theme');?> <?php echo date_i18n(get_option('date_format'), $since); ?>
                <?php endif; ?>                    
                </div>
            </td>
            <td class="rh-tabletext-block-right padd15 verttop">
                <div class="rh-table-price-graph">
                <?php TemplateHelper::priceHistoryMorrisChart($unique_id, $module_id, 180, array('lineWidth' => 2, 'postUnits' => ' ' . $currency_code, 'goals' => array((int) $syncitem['price']*$currency_rate), 'fillOpacity' => 0.5), array('style' => 'height: 230px;')); ?>
                </div>
                <ul class="rh-lowest-highest">
                    <?php $price = TemplateHelper::priceHistoryMax($unique_id, $module_id); ?>
                    <?php if ($price): ?>                        
                        <li>
                            <b style="color: red;"><?php esc_html_e('Highest Price:', 'rehub-theme');?></b> 
                            <?php echo TemplateHelper::formatPriceCurrency($price['price']*$currency_rate, $currency_code); ?> 
                            - <?php echo date_i18n(get_option('date_format'), $price['date']); ?>
                        </li>
                    <?php endif; ?>

                    <?php $price = TemplateHelper::priceHistoryMin($unique_id, $module_id); ?>
                    <?php if ($price): ?>
                        <li>
                            <b style="color: green;"><?php esc_html_e('Lowest Price:', 'rehub-theme');?></b> 
                            <?php echo TemplateHelper::formatPriceCurrency($price['price']*$currency_rate, $currency_code); ?>   
                            - <?php echo date_i18n(get_option('date_format'), $price['date']); ?>
                        </li>
                    <?php endif; ?>
                </ul>            
            </td>
            </tr>                
        </table>
    <?php else:?>
        <div class="rhhidden" id="nopricehsection">-</div>
    <?php endif;?>
<?php endif;?>