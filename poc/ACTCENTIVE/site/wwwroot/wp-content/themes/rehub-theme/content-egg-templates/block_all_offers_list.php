<?php
/*
 * Name: Offers list from all affiliate modules
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
<div class="rh_listoffers rh_listoffers_price_col">
    <?php  foreach ($all_items as $key => $item): ?>
        <?php $stock_status_str = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusStr($item) : '';?>
        <?php $stock_status_class = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusClass($item) : '';?>            
        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
        <?php $offer_price_old = (!empty($item['priceOld'])) ? $item['priceOld'] : ''; ?>
        <?php $currency_code = (!empty($item['currencyCode'])) ? $item['currencyCode'] : ''; ?>
        <?php $percentageSaved = (!empty($item['percentageSaved'])) ? $item['percentageSaved'] : ''; ?>
        <?php $availability = (!empty($item['availability'])) ? $item['availability'] : ''; ?>
        <?php $offer_post_url = $item['url'] ;?>
        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
        <?php $aff_thumb = (!empty($item['img'])) ? $item['img'] : '' ;?>
        <?php $offer_title = (!empty($item['title'])) ? wp_trim_words( $item['title'], 12, '...' ) : ''; ?>
        <?php $merchant = (!empty($item['merchant'])) ? $item['merchant'] : ''; ?>
        <?php $modulecode = (!empty($item['module_id'])) ? $item['module_id'] : ''; ?>            
        <?php $offer_title = wp_trim_words( $item['title'], 10, '...' ); ?>
        <?php $merchant = (!empty($item['merchant'])) ? $item['merchant'] : ''; ?>
        <?php $manufacturer = (!empty($item['manufacturer'])) ? $item['manufacturer'] : ''; ?>
        <?php $description = (!empty($item['description'])) ? $item['description'] : '';?>
        <?php if (!empty($item['domain'])):?>
            <?php $domain = $item['domain'];?>
        <?php elseif (!empty($item['extra']['domain'])):?>
            <?php $domain = $item['extra']['domain'];?>
        <?php else:?>
            <?php $domain = '';?>        
        <?php endif;?>              
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
                <div class="rh_listcolumn rh_listcolumn_image text-center">   
                    <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                        <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'height'=> 100, 'title' => $offer_title, 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_100_70.png'));?>                                    
                    </a>
                </div>
                <div class="rh_listcolumn rh-flex-grow1 rh_listcolumn_text">
                    <div class="fontbold rehub-main-font lineheight20">
                        <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn blackcolor" href="<?php echo esc_url($afflink) ?>"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                            <?php echo esc_attr($offer_title); ?>
                        </a>
                    </div>
                    <?php $merchanttext = \ContentEgg\application\helpers\TemplateHelper::getShopInfo($item);?>
                    <?php if($merchanttext):?>
                        <div class="font80 mb10 margincenter">
                            <?php echo ''.$merchanttext; ?>
                        </div> 
                    <?php endif;?> 
                    <?php if (method_exists('TemplateHelper', 'getCashbackStr') && $cashback_str = TemplateHelper::getCashbackStr($item)): ?>
                        <div class="font90 inlinestyle mb10 rehub-main-color"><?php echo sprintf(esc_html__('Plus %s CashBack', 'rehub-theme'), $cashback_str); ?></div>
                    <?php endif; ?>                          
                    <?php if (!empty($item['extra']['estimatedDeliveryTime'])): ?>
                        <small class="small_size">
                            <span class="yes_available"><?php echo ''.$item['extra']['estimatedDeliveryTime'] ;?></span>
                        </small>
                    <?php endif; ?> 
                    <?php if ($description): ?>
                        <div class="font80 greycolor lineheight20"><?php echo do_shortcode($description); ?></div> 
                    <?php endif; ?>                        
                    <?php if (!empty($item['extra']['offers'])): ?>
                        <?php $offers_flipkart = $item['extra']['offers'];?>
                        <?php foreach ($offers_flipkart as $offer_flipkart):?>
                            <div class="font80 flipkart_offers_extra lineheight15">
                                <i class="rhicon rhi-check-square greencolor" aria-hidden="true"></i> <span><?php echo ''.$offer_flipkart.'';?></span>
                            </div>
                        <?php endforeach ;?>
                    <?php endif; ?>                              
                </div>                    
                <div class="rh_listcolumn rh_listcolumn_price text-center">
                    <?php if($offer_price) : ?>
                        <span class="price_count rehub-main-color rehub-btn-font">
                            <?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code, '<span class="cur_sign">', '</span>'); ?>
                            <?php if($offer_price_old) : ?>
                            <strike class="lightgreycolor">
                                <span class="amount"><?php echo TemplateHelper::formatPriceCurrency($offer_price_old, $currency_code); ?></span>
                            </strike>
                            <?php endif ;?>                                      
                        </span>                   
                    <?php endif ;?> 
                    <?php if (!empty($item['extra']['totalUsed'])): ?>
                        <span class="val_sim_price_used_merchant">
                        <?php echo (int)$item['extra']['totalUsed']; ?>
                        <?php esc_html_e('used', 'rehub-theme'); ?> <?php esc_html_e('from', 'rehub-theme'); ?>
                            <?php echo TemplateHelper::formatPriceCurrency($lowestused_price, $currency_code); ?>
                        </span>
                    <?php endif; ?>   
                    <?php if (!empty($item['extra']['totalNew'])): ?>
                        <span class="val_sim_price_used_merchant">
                            <?php echo (int)$item['extra']['totalNew']; ?>
                            <?php esc_html_e('new', 'rehub-theme'); ?> 
                            <?php if ($item['extra']['lowestNewPrice']): ?>
                                 <?php esc_html_e('from', 'rehub-theme'); ?> <?php echo TemplateHelper::formatPriceCurrency($lowestnew_price, $currency_code); ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>  
                    <?php if ($stock_status_str): ?>
                        <div title="<?php esc_html_e('Last update was on: ', 'rehub-theme'); ?><?php echo TemplateHelper::getLastUpdateFormatted($item['module_id'], $post_id); ?>" class="cegg-lineheight15 blockstyle greencolor font80 stock-status status-<?php echo esc_attr($stock_status_class);?>">
                            <?php echo esc_html($stock_status_str); ?>
                        </div>
                    <?php endif; ?>                                                                                           
                </div>
                <div class="rh_listcolumn rh_listcolumn_shop text-center">
                    <?php if($logo) :?>
                        <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($offer_title); ?>" width=70 />
                    <?php elseif ($merchant) :?>
                        <div class="aff_tag"><?php echo esc_attr($merchant); ?></div>
                    <?php elseif ($manufacturer) :?>
                        <div class="aff_tag"><?php echo esc_attr($manufacturer); ?></div>              
                    <?php endif ;?>                         
                </div>
                <div class="text-right-align rh_listcolumn_btn">
                    <div class="priced_block clearfix">
                        <div>
                            <a class="re_track_btn btn_offer_block" href="<?php echo esc_url($afflink) ?>" target="_blank" <?php echo ce_printRel();?>  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                                <?php echo esc_html($btn_txt) ; ?>
                            </a>                                                        
                        </div>
                    </div>
                </div>
            </div>
                                                                      
        </div>
    <?php endforeach; ?>                   
</div>
<div class="clearfix"></div>