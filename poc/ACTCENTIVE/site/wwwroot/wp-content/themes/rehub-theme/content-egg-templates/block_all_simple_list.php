<?php
/*
 * Name: Simple 4 List item
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
<div class=" clearfix"></div>
<div class="ce_common_simple_list">
    
    <?php  foreach ($all_items as $key => $item): ?>
        <?php if ($key > 3){break;}?>
        <?php $stock_status_str = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusStr($item) : '';?>
        <?php $stock_status_class = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusClass($item) : '';?>        
        <?php $offer_post_url = $item['url'] ;?>
        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
        <?php $aff_thumb = $item['img'] ;?>
        <?php $offer_title = wp_trim_words( $item['title'], 10, '...' ); ?>
        <?php $merchant = (!empty($item['merchant'])) ? $item['merchant'] : ''; ?>
        <?php $manufacturer = (!empty($item['manufacturer'])) ? $item['manufacturer'] : ''; ?>
        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
        <?php $offer_price_old = (!empty($item['priceOld'])) ? $item['priceOld'] : ''; ?> 
        <?php $currency_code = (!empty($item['currencyCode'])) ? $item['currencyCode'] : ''; ?>
        <?php $modulecode = (!empty($item['module_id'])) ? $item['module_id'] : ''; ?>              
        <?php if (!empty($item['domain'])):?>
            <?php $domain = $item['domain'];?>
        <?php elseif (!empty($item['extra']['domain'])):?>
            <?php $domain = $item['extra']['domain'];?>
        <?php else:?>
            <?php $domain = '';?>        
        <?php endif;?>    
        <?php $domain = rh_fix_domain($merchant, $domain);?> 
        <?php if(empty($merchant) && !empty($domain)) {
            $merchant = $domain;
        }
        ?>
        <?php $lowestnew_price = (!empty($item['extra']['lowestNewPrice'])) ? $item['extra']['lowestNewPrice'] : ''; ?> 
        <?php $lowestused_price = (!empty($item['extra']['lowestUsedPrice'])) ? $item['extra']['lowestUsedPrice'] : ''; ?>         
        <?php if($offer_price && rehub_option('ce_custom_currency')) {
            $currency_code = rehub_option('ce_custom_currency');
            $currency_rate = CurrencyHelper::getCurrencyRate($item['currencyCode'], $currency_code);
            if (!$currency_rate) $currency_rate = 1;
            $offer_price = $offer_price * $currency_rate;
            if($offer_price_old){$offer_price_old = $offer_price_old * $currency_rate;}
        }?>

        <?php if(rehub_option('rehub_btn_text') !='') :?><?php $btn_txt = rehub_option('rehub_btn_text') ; ?><?php else :?><?php $btn_txt = TemplateHelper::buyNowBtnText(false, $item);?><?php endif ;?>        
        <?php $logo = TemplateHelper::getMerhantLogoUrl($item, true);?>    
        <div class="flowhidden pb10 pt15 border-grey-bottom module_class_<?php echo esc_attr($modulecode);?> rh_stock_<?php echo esc_attr($stock_status_class);?>">               
            <div class="floatleft mobileblockdisplay mb15 offer_thumb<?php if(!$logo) {echo ' nologo_thumb';}?>">   
                <?php if($logo) :?>
                    <a <?php echo ce_printRel();?> target="_blank" href="<?php echo esc_url($afflink) ?>" class="re_track_btn"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                    <img src="<?php echo esc_attr($logo); ?>" alt="<?php echo esc_attr($offer_title); ?>" height="40" style="max-height: 40px" />
                    </a>
                    <?php $merchanttext = \ContentEgg\application\helpers\TemplateHelper::getShopInfo($item);?>
                    <?php if($merchanttext):?>
                        <div class="font80 mb10 margincenter">
                            <?php echo ''.$merchanttext; ?>
                        </div> 
                    <?php endif;?> 
                    <?php if (!empty($item['extra']['estimatedDeliveryTime'])): ?>
                        <small class="font70 blockstyle lineheight15">
                            <span class="yes_available"><?php echo ''.$item['extra']['estimatedDeliveryTime'] ;?></span>
                        <br />
                        </small>
                    <?php endif; ?>  
                    <?php if (!empty($item['extra']['IsEligibleForSuperSaverShipping'])): ?>
                        <small class="font70 blockstyle lineheight15">
                            <?php esc_html_e('& Free shipping', 'rehub-theme'); ?>
                        </small> 
                    <?php endif; ?>                     
                <?php endif ;?>                                                           
            </div>
            <div class="floatright buttons_col pl20 rtlpr20 wpsm-one-half-mobile wpsm-column-last">
                <div class="priced_block clearfix mt0 floatright">
                    <a class="re_track_btn btn_offer_block" href="<?php echo esc_url($afflink) ?>" target="_blank" <?php echo ce_printRel();?>  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                        <?php echo esc_attr($btn_txt);?>
                    </a>                                                        
                </div>                                  
            </div>                                  
            <div class="floatright text-right-align disablemobilealign wpsm-one-half-mobile">
                <?php if(!empty($item['price'])) : ?>
                    <span class="font120 rehub-btn-font fontbold">
                        <a <?php echo ce_printRel();?> target="_blank" href="<?php echo esc_url($afflink) ?>" class="re_track_btn blackcolor blockstyle lineheight20"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                            <span><?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code); ?></span>
                            <?php if($offer_price_old) : ?>
                            <strike class="blockstyle">
                                <span class="amount font70 rh_opacity_3">
                                    <?php echo TemplateHelper::formatPriceCurrency($offer_price_old, $currency_code); ?>
                                </span>
                            </strike>
                            <?php endif ;?>                                     
                        </a>
                    </span>
                    <?php if (!empty($item['extra']['totalNew'])): ?>
                        <div class="font60 lineheight15">
                            <?php echo (int)$item['extra']['totalNew']; ?>
                            <?php esc_html_e('new', 'rehub-theme'); ?> 
                            <?php if ($lowestnew_price): ?>
                                 <?php esc_html_e('from', 'rehub-theme'); ?> <?php echo TemplateHelper::formatPriceCurrency($lowestnew_price, $currency_code); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>                            
                    <?php if (!empty($item['extra']['totalUsed'])): ?>
                        <span class="val_sim_price_used_merchant">
                        <?php esc_html_e('Used', 'rehub-theme'); ?> - <?php echo TemplateHelper::formatPriceCurrency($lowestused_price, $currency_code); ?>
                        </span>
                    <?php endif; ?>                                                                       
                <?php endif ;?>
                <?php if($stock_status_class == 'outofstock'):?>
                    <span class="blockstyle redbrightcolor font80"><?php echo esc_attr($stock_status_str);?></span>
                <?php endif;?>                                       
            </div> 
                                                              
        </div>
    <?php endforeach; ?>                   
</div>
<div class="clearfix"></div>