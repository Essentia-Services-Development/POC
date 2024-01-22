<?php
/*
 * Name: Offers grid from all affiliate modules
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
<div class="masonry_grid_fullwidth col_wrap_fourth egg_grid">
<?php echo rh_generate_incss('masonry');?>
<?php $i=0; foreach ($all_items as $key => $item): ?>
    <?php $stock_status_str = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusStr($item) : '';?>
    <?php $stock_status_class = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusClass($item) : '';?> 
    <?php if (!empty($item['domain'])):?>
        <?php $domain = $item['domain'];?>
    <?php elseif (!empty($item['extra']['domain'])):?>
        <?php $domain = $item['extra']['domain'];?>
    <?php else:?>
        <?php $domain = '';?>
    <?php endif;?>     
    <?php if(rehub_option('rehub_btn_text') !='') :?>
        <?php $btn_txt = rehub_option('rehub_btn_text') ; ?>
        <?php else :?><?php $btn_txt = TemplateHelper::buyNowBtnText(false, $item);?>
        <?php endif ;?>
    <?php $i++;?>     
        
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
    <?php if (!empty($item['domain'])):?>
        <?php $domain = $item['domain'];?>
    <?php elseif (!empty($item['extra']['domain'])):?>
        <?php $domain = $item['extra']['domain'];?>
    <?php endif;?>     
    <?php if(rehub_option('rehub_btn_text') !='') :?><?php $btn_txt = rehub_option('rehub_btn_text') ; ?><?php else :?><?php $btn_txt = esc_html__('Buy this item', 'rehub-theme') ;?><?php endif ;?>
    <?php $lowestnew_price = (!empty($item['extra']['lowestNewPrice'])) ? $item['extra']['lowestNewPrice'] : ''; ?> 
    <?php $lowestused_price = (!empty($item['extra']['lowestUsedPrice'])) ? $item['extra']['lowestUsedPrice'] : ''; ?>         
    <?php if($offer_price && rehub_option('ce_custom_currency')) {
        $currency_code = rehub_option('ce_custom_currency');
        $currency_rate = CurrencyHelper::getCurrencyRate($item['currencyCode'], $currency_code);
        if (!$currency_rate) $currency_rate = 1;
        $offer_price = $offer_price * $currency_rate;
        if($offer_price_old){$offer_price_old = $offer_price_old * $currency_rate;}
    }?>    
    <?php $i++;?>  
    <div class="small_post col_item">
        <figure class="width-100p position-relative mb20">
            <?php if($percentageSaved) : ?>
                <span class="sale_a_proc">
                    <?php    
                        echo '-'.$percentageSaved.'%';
                    ;?>
                </span>
            <?php endif ;?>                 
            <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'width'=> 336, 'title' => $offer_title, 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_336_220.png'));?>                                    
            </a>
        </figure>
        <div class="affegg_grid_title">
            <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                <?php echo esc_attr($offer_title); ?>
            </a>
        </div>
        <?php $merchanttext = \ContentEgg\application\helpers\TemplateHelper::getShopInfo($item);?>
            <?php if($merchanttext):?>
                <div class="font80 mb10 margincenter">
                    <?php echo ''.$merchanttext; ?>
                </div> 
            <?php endif;?> 
        <div class="buttons_col width-100p">
            <div class="priced_block clearfix">
                <?php if($offer_price) : ?>
                    <div class="rh_price_wrapper">
                        <span class="price_count">
                            <ins>                        
                                <?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code, '<span class="cur_sign">', '</span>'); ?>
                            </ins>
                            <?php if($offer_price_old) : ?>
                            <del>
                                <span class="amount">
                                    <?php echo TemplateHelper::formatPriceCurrency($offer_price_old, $currency_code, '<span class="value">', '</span>'); ?>
                                </span>
                            </del>
                            <?php endif ;?>                                      
                        </span>
                        <?php if (!empty($item['extra']['totalUsed'])): ?>
                            <span class="val_sim_price_used_merchant">
                            <?php echo (int)$item['extra']['totalUsed']; ?>
                            <?php esc_html_e('used', 'rehub-theme'); ?> <?php esc_html_e('from', 'rehub-theme'); ?>
                                <?php echo TemplateHelper::formatPriceCurrency($lowestused_price, $currency_code); ?>
                            </span>
                        <?php endif; ?>                                                                          
                    </div>
                <?php endif ;?>
                <?php if($stock_status_class == 'outofstock'):?>
                    <span class="blockstyle redbrightcolor font80"><?php echo esc_attr($stock_status_str);?></span>
                <?php endif;?>                                  
                <div>
                    <a class="re_track_btn btn_offer_block" href="<?php echo esc_url($afflink) ?>" target="_blank" <?php echo ce_printRel();?>  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                        <?php echo esc_html($btn_txt) ; ?>
                    </a> 
                    <div class="aff_tag mt10 small_size">
                        <img src="<?php echo esc_attr(TemplateHelper::getMerhantIconUrl($item, true)); ?>" alt="<?php echo esc_html($domain); ?>" />
                        <?php if ($merchant):?>
                            <?php echo esc_html($merchant); ?>
                        <?php elseif($domain):?>
                            <?php echo esc_html($domain); ?>                                     
                        <?php endif;?>                            
                    </div>                            
                </div>
            </div>
        </div>            
    </div>
           
<?php endforeach; ?> 
</div>  
<div class="clearfix"></div>