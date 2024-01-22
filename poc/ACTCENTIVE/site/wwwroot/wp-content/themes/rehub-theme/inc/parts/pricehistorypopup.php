<?php if ( ! defined( 'ABSPATH' ) ) {exit;}?>
<?php if(defined('\ContentEgg\PLUGIN_PATH')):?>
    <?php   $pricesarray = ContentEgg\application\helpers\TemplateHelper::priceHistoryPrices($unique_id, $module_id, $limit = 3);
        $pricescheck = '';
        if (!empty($pricesarray) && is_array($pricesarray)){
            $pricescheck = (count($pricesarray) > 1) ? true : '';
        }
    ?>
    <?php if($pricescheck):?>
        <?php $rand = mt_rand();?>
        <?php 
            $currency_code = (!empty($syncitem['currencyCode'])) ? $syncitem['currencyCode'] : ''; 
            $currency_rate = 1; 
        ?>
        <?php if(rehub_option('ce_custom_currency')) {
            $currency_code = rehub_option('ce_custom_currency');
            $currency_rate = ContentEgg\application\helpers\CurrencyHelper::getCurrencyRate($syncitem['currencyCode'], $currency_code);
            if (!$currency_rate) $currency_rate = 1;            
        }?>        
        <span class="cursorpointer csspopuptrigger floatright" data-popup="pricehistory_<?php echo ''.$rand;?>"><i class="rhicon rhi-chart-bar" aria-hidden="true"></i> <?php esc_html_e('Price history', 'rehub-theme');?></span>
        <div class="csspopup" id="pricehistory_<?php echo ''.$rand;?>">
            <div class="csspopupinner">
                <span class="cpopupclose cursorpointer lightgreybg rh-close-btn rh-flex-center-align rh-flex-justify-center rh-shadow5 roundborder">×</span>
                <table class="border-lightgrey clearbox flowhidden mb25 rh-shadow1 rh-tabletext-block whitebg width-100p">
                    <tr>
                    <th class="rh-tabletext-block-heading fontbold border-grey-bottom" colspan="2"><?php esc_html_e('Price history for ', 'rehub-theme');?><?php echo esc_attr($syncitem['title']); ?></th>
                    </tr>
                    <tr>
                    <td class="rh-tabletext-block-left padd15 verttop">
                        <div class="rh-tabletext-block-latest"> 
                        <div class="mb10"><strong><?php esc_html_e('Latest updates:', 'rehub-theme');?></strong></div>                             
                        <?php $prices = ContentEgg\application\helpers\TemplateHelper::priceHistoryPrices($unique_id, $module_id, $limit = 8); ?>
                        <?php if ($prices): ?>
                            <ul>
                                <?php foreach ($prices as $price): ?>
                                    <li>
                                        <?php echo ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($price['price']*$currency_rate, $currency_code); ?>                    
                                        - <?php echo date_i18n(get_option('date_format'), $price['date']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php $since = ContentEgg\application\helpers\TemplateHelper::priceHistorySinceDate($unique_id, $module_id); ?>
                        <?php if ($since): ?>
                            <?php esc_html_e('Since:', 'rehub-theme');?> <?php echo date_i18n(get_option('date_format'), $since); ?>
                        <?php endif; ?>                    
                        </div>
                    </td>
                    <td class="rh-tabletext-block-right padd15 verttop">
                        <?php ContentEgg\application\helpers\TemplateHelper::priceHistoryMorrisChart($unique_id, $module_id, 180, array('lineWidth' => 2, 'postUnits' => ' ' . $currency_code, 'goals' => array((int) $syncitem['price']*$currency_rate), 'fillOpacity' => 0.5), array('style' => 'height: 230px;')); ?>
                        <ul class="rh-lowest-highest">
                            <?php $price = ContentEgg\application\helpers\TemplateHelper::priceHistoryMax($unique_id, $module_id); ?>
                            <?php if ($price): ?>
                                <li>
                                    <b style="color: red;"><?php esc_html_e('Highest Price:', 'rehub-theme');?></b> 
                                    <?php echo ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($price['price']*$currency_rate, $currency_code); ?> 
                                    - <?php echo date_i18n(get_option('date_format'), $price['date']); ?>
                                </li>
                            <?php endif; ?>

                            <?php $price = ContentEgg\application\helpers\TemplateHelper::priceHistoryMin($unique_id, $module_id); ?>
                            <?php if ($price): ?>
                                <li>
                                    <b style="color: green;"><?php esc_html_e('Lowest Price:', 'rehub-theme');?></b> 
                                    <?php echo ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($price['price']*$currency_rate, $currency_code); ?>   
                                    - <?php echo date_i18n(get_option('date_format'), $price['date']); ?>
                                </li>
                            <?php endif; ?>
                        </ul>            
                    </td>
                    </tr>                
                </table>           
            </div>
        </div>
    <?php endif;?>
<?php endif;?>