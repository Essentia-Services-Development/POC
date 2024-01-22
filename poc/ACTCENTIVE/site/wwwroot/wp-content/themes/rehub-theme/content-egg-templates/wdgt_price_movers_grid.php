<?php
/*
 * Name: Content Grid
 * 
 */

use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\helpers\CurrencyHelper;
$cols = (isset($cols)) ? (int)$cols : 4;
if ($cols == 4){
    $col_wrap = 'col_wrap_fourth';
}  
elseif ($cols == 5){
    $col_wrap = 'col_wrap_fifth';
} 
elseif ($cols == 6){
    $col_wrap = 'col_wrap_six';
} 
elseif($cols == 3) {
   $col_wrap = 'col_wrap_three'; 
} 
else{
   $col_wrap = 'col_wrap_fourth'; 
}
?>

<div class="eq_grid pt5 rh-flex-eq-height <?php echo esc_attr($col_wrap);?>">
      
<?php foreach ($items as $key => $item): ?> 
<?php $aff_thumb = (!empty($item['img'])) ? esc_attr($item['img']) : '' ;?>  
<?php $offer_title = (!empty($item['title'])) ? wp_trim_words( $item['title'], 12, '...' ) : ''; ?>
<?php $offer_post_url = $item['url'] ;?>
<?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?> 
<?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
<?php $offer_price_old = (!empty($item['_price_movers']['price_old'])) ? $item['_price_movers']['price_old'] : ''; ?> 
<?php $currency_code = (!empty($item['currencyCode'])) ? $item['currencyCode'] : ''; ?>
<?php $modulecode = (!empty($item['module_id'])) ? $item['module_id'] : ''; ?>
<?php $lowestused_price = (!empty($item['extra']['lowestUsedPrice'])) ? $item['extra']['lowestUsedPrice'] : ''; ?>  
<?php if($offer_price && rehub_option('ce_custom_currency')) {
    $currency_code = rehub_option('ce_custom_currency');
    $currency_rate = CurrencyHelper::getCurrencyRate($item['currencyCode'], $currency_code);
    if (!$currency_rate) $currency_rate = 1;
    $offer_price = $offer_price * $currency_rate;
    if($offer_price_old){$offer_price_old = $offer_price_old * $currency_rate;}
}?> 
<?php echo rh_generate_incss('offergrid');?>
<article class="col_item offer_grid mobile_compact_grid offer_grid_com no_btn_enabled"> 
    <div class="info_in_dealgrid">       
        <figure class="mb15">
            <?php if ($item['_price_movers']['discount_percent'] > 0): ?>
                <span class="grid_onsale"><?php echo (int)$item['_price_movers']['discount_percent']; ?>%</span>
            <?php endif ; ?>

            <a class="img-centered-flex rh-flex-center-align rh-flex-justify-center re_track_btn" href="<?php echo esc_attr($afflink);?>" <?php echo ce_printRel();?> target="_blank">
                <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'crop'=> false, 'width'=> 250, 'height'=> 180, 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_250_180.png'));?>                
            </a>
        </figure>
        <div class="grid_desc_and_btn">
            <div class="grid_row_info">
                <div class="flowhidden mb10">
                    <div class="price_for_grid redbrightcolor floatleft">
                        <div class="priced_block clearfix mt0">
                            <span class="rh_price_wrapper">
                                <span class="price_count" title="<?php echo \esc_attr(__('as of', 'rehub-theme') . ' ' . TemplateHelper::formatDatetime($item['_price_movers']['create_date'])); ?>">
                                    <ins><?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code); ?></ins> 
                                    <?php if ($item['_price_movers']['discount_value'] > 0): ?>
                                        <i class="rhicon rhi-arrow-down greencolor"></i>
                                    <?php endif; ?>
                                    <?php if ($item['_price_movers']['discount_value'] < 0): ?>
                                        <i class="rhicon rhi-arrow-up redcolor"></i>
                                    <?php endif; ?>                                          
                                    <del>
                                    <?php echo TemplateHelper::formatPriceCurrency($offer_price_old, $currency_code); ?>                                     
                                    <i class="rhicon rhi-question-circle greycolor"></i> 
                                    </del>                      
                                </span>
                            </span>                                 
                        </div>                        
                    </div>
                    <div class="floatright vendor_for_grid aff_tag">
                        <?php if ($logo = TemplateHelper::getMerhantLogoUrl($item, true)): ?>
                            <img src="<?php echo esc_attr($logo); ?>" title="<?php echo esc_attr($item['domain']); ?>" alt="<?php echo esc_attr($item['domain']); ?>" />
                        <?php endif; ?> 
                    </div>
                </div>     
       
                <h3 class="flowhidden mb10 fontnormal position-relative"><a href="<?php echo esc_attr($afflink);?>" <?php echo ce_printRel();?> target="_blank" class="re_track_btn"  data-tracking-group="<?php echo esc_attr($modulecode);?>"><?php echo esc_attr($offer_title); ?></a></h3> 
            </div>
 
        </div>                                       
    </div>
    <div class="meta_for_grid">
        <div class="date_for_grid floatleft mr5">
            <span class="date_ago">
                <i class="rhicon rhi-clock"></i><?php printf( esc_html__( '%s ago', 'rehub-theme' ), human_time_diff( $item['_price_movers']['create_date'], current_time( 'timestamp' ) ) ); ?>
            </span>        
        </div>        
        <div class="cat_store_for_grid floatright">
            <div class="cat_for_grid font70"> 
                <?php if (!empty($item['extra']['totalUsed'])): ?>
                    <span class="val_sim_price_used_merchant">
                    <?php echo (int)$item['extra']['totalUsed']; ?>
                    <?php esc_html_e('used', 'rehub-theme'); ?> <?php esc_html_e('from', 'rehub-theme'); ?>
                        <?php echo TemplateHelper::formatPriceCurrency($lowestused_price, $currency_code); ?>
                    </span>
                <?php endif; ?>             
                <?php if (!empty($item['extra']['conditionDisplayName'])): ?>
                    <small class="font70">
                    <?php esc_html_e('Condition: ', 'rehub-theme') ;?><span class="yes_available"><?php echo ''.$item['extra']['conditionDisplayName'] ;?></span>
                    <br />
                    </small>
                <?php endif; ?>
                <?php if (!empty($item['extra']['estimatedDeliveryTime'])): ?>
                    <small class="greencolor">
                        <span class="yes_available"><?php echo ''.$item['extra']['estimatedDeliveryTime'] ;?></span>
                    <br />
                    </small>
                <?php endif; ?>  
                <?php if (!empty($item['extra']['IsEligibleForSuperSaverShipping'])): ?>
                    <small class="greencolor">
                        <?php esc_html_e('& Free shipping', 'rehub-theme'); ?>
                    </small> 
                <?php endif; ?>                                    
            </div>          
        </div>   
    </div>     
</article>
<?php endforeach; ?>

</div>