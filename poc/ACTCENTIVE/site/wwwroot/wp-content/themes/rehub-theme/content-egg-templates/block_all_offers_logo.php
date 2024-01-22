<?php
/*
 * Name: Sorted list with store logo
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
<div class="rh_listoffers">
    <?php  foreach ($all_items as $key => $item): ?>
        <?php $stock_status_str = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusStr($item) : '';?>
        <?php $stock_status_class = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusClass($item) : '';?>
        <?php $offer_post_url = $item['url'] ;?>
        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
        <?php $aff_thumb = $item['img'] ;?>
        <?php $offer_title = wp_trim_words( $item['title'], 10, '...' ); ?>
        <?php $merchant = (!empty($item['merchant'])) ? $item['merchant'] : ''; ?>
        <?php $manufacturer = (!empty($item['manufacturer'])) ? $item['manufacturer'] : ''; ?>
        <?php $modulecode = (!empty($item['module_id'])) ? $item['module_id'] : ''; ?>            
        <?php if (!empty($item['domain'])):?>
            <?php $domain = $item['domain'];?>
        <?php elseif (!empty($item['extra']['domain'])):?>
            <?php $domain = $item['extra']['domain'];?>
        <?php else:?>
            <?php $domain = '';?>        
        <?php endif;?>      
        <?php $domain = rh_fix_domain($merchant, $domain);?> 
        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
        <?php $offer_price_old = (!empty($item['priceOld'])) ? $item['priceOld'] : ''; ?>            
        <?php $currency_code = (!empty($item['currencyCode'])) ? $item['currencyCode'] : ''; ?>            
        <?php if(empty($merchant) && !empty($domain)) {
            $merchant = $domain;
        }
        ?>
        <?php $logo = TemplateHelper::getMerhantLogoUrl($item, true);?>
        <?php if(rehub_option('rehub_btn_text') !='') :?><?php $btn_txt = rehub_option('rehub_btn_text') ; ?><?php else :?><?php $btn_txt = TemplateHelper::buyNowBtnText(false, $item);?><?php endif ;?> 
        <?php $lowestnew_price = (!empty($item['extra']['lowestNewPrice'])) ? $item['extra']['lowestNewPrice'] : ''; ?> 
        <?php $lowestused_price = (!empty($item['extra']['lowestUsedPrice'])) ? $item['extra']['lowestUsedPrice'] : ''; ?>         
        <?php if($offer_price && rehub_option('ce_custom_currency')) {
            $currency_code = rehub_option('ce_custom_currency');
            $currency_rate = CurrencyHelper::getCurrencyRate($item['currencyCode'], $currency_code);
            if (!$currency_rate) $currency_rate = 1;
            $offer_price = $offer_price * $currency_rate;
            if($offer_price_old){$offer_price_old = $offer_price_old * $currency_rate;}
        }?>              
        <div class="rh_listofferitem rh_list_mbl_im_left border-grey-bottom module_class_<?php echo esc_attr($modulecode);?> rh_stock_<?php echo esc_attr($stock_status_class);?>"> 
            <div class="rh-flex-center-align rh-flex-justify-center pt15 pb15 mobileblockdisplay">              
                <div class="rh_listcolumn rh_listcolumn_image text-center<?php if(!$logo) {echo ' nologo_thumb';}?>">   
                    <a <?php echo ce_printRel();?> target="_blank" href="<?php echo esc_url($afflink) ?>" class="re_track_btn"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                        <?php if($logo) :?>
                            <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($offer_title); ?>" height="50" />
                        <?php endif ;?>                                                           
                    </a>
                </div>
                <div class="rh_listcolumn rh-flex-grow1 rh_listcolumn_text">
                    <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn font100 blackcolor blockstyle rehub-main-font lineheight20" href="<?php echo esc_url($afflink) ?>"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                        <?php echo esc_attr($offer_title); ?>
                    </a>  
                    <?php $merchanttext = \ContentEgg\application\helpers\TemplateHelper::getShopInfo($item);?>
                    <?php if($merchanttext):?>
                        <div class="font80 mb10 margincenter">
                            <?php echo ''.$merchanttext; ?>
                        </div> 
                    <?php endif;?> 
                    <?php if (method_exists('TemplateHelper', 'getCashbackStr') && $cashback_str = TemplateHelper::getCashbackStr($item)): ?>
                        <div class="font90 inlinestyle mb10 rehub-main-color"><?php echo sprintf(esc_html__('Plus %s CashBack', 'rehub-theme'), $cashback_str); ?></div>
                    <?php endif; ?>                                                  
                </div>                    
                <div class="rh_listcolumn rh_listcolumn_price text-center">
                    <?php if($offer_price) : ?>
                        <span class="price_count rehub-main-color rehub-btn-font fontbold">
                            <?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code); ?>
                            <?php if($offer_price_old) : ?>
                            <strike class="lightgreycolor fontnormal">
                                <span class="amount">
                                    <?php echo TemplateHelper::formatPriceCurrency($offer_price_old, $currency_code); ?>
                                </span>
                            </strike>
                            <?php endif ;?>                                      
                        </span>

                        <?php if($modulecode == 'Amazon'):?>
                            <?php $pop = mt_rand();?>
                            <div class="font60 lineheight15"><?php echo TemplateHelper::getLastUpdateFormattedAmazon($data);?>
                                
                                <span class="csspopuptrigger" data-popup="ce-amazon-disclaimer<?php echo esc_attr($pop);?>"><i class="rhicon rhi-question-circle greycolor"></i></span></div>
                                <div class="csspopup" id="ce-amazon-disclaimer<?php echo esc_attr($pop);?>">
                                    <div class="csspopupinner">
                                        <span class="cpopupclose cursorpointer lightgreybg rh-close-btn rh-flex-center-align rh-flex-justify-center rh-shadow5 roundborder">×</span>
                                        <?php esc_html_e('Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on Amazon.com (Amazon.in, Amazon.co.uk, Amazon.de, etc) at the time of purchase will apply to the purchase of this product.', 'rehub-theme');?>
                                    </div>                                
                                </div>
                            <?php if (!empty($item['extra']['totalNew'])): ?>
                                <div class="font60 lineheight15">
                                    <?php echo (int)$item['extra']['totalNew']; ?>
                                    <?php esc_html_e('new', 'rehub-theme'); ?> 
                                    <?php if ($item['extra']['lowestNewPrice']): ?>
                                         <?php esc_html_e('from', 'rehub-theme'); ?> <?php echo TemplateHelper::formatPriceCurrency($lowestnew_price, $currency_code); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>                            
                            <?php if (!empty($item['extra']['totalUsed'])): ?>
                                <div class="font60 lineheight15">
                                <?php echo (int)$item['extra']['totalUsed']; ?>
                                <?php esc_html_e('used', 'rehub-theme'); ?> <?php esc_html_e('from', 'rehub-theme'); ?>
                                    <?php echo TemplateHelper::formatPriceCurrency($lowestused_price, $currency_code); ?>
                                </div>
                            <?php endif; ?>                            
                        <?php endif;?>                        
                    <?php endif ;?> 
                    <?php if($stock_status_class == 'outofstock'):?>
                        <span class="blockstyle redbrightcolor font80"><?php echo esc_attr($stock_status_str);?></span>
                    <?php endif;?>                                           
                </div>
                <div class="text-right-align rh_listcolumn_btn">
                    <div class="priced_block clearfix">
                        <a class="re_track_btn btn_offer_block" href="<?php echo esc_url($afflink) ?>" target="_blank" <?php echo ce_printRel();?>  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                            <?php echo esc_html($btn_txt) ; ?>
                        </a>                                                        
                    </div>
                </div> 
            </div>                                                                        
        </div>
    <?php endforeach; ?>                   
</div>
<div class="clearfix"></div>