<?php
/*
 * Name: Sidebar Top Offers
 * 
 */

use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\helpers\CurrencyHelper;
?>
<?php echo rh_generate_incss('widgettopoffers');?>
<div class="rh_deal_block">
        <?php $i = $number = 0; ?>     
        <?php if (is_array($items)) $number = count($items);?>
        <?php foreach ($items as $key => $item): ?> 
        <?php $i++; ?>   
        <?php $aff_thumb = (!empty($item['img'])) ? esc_attr($item['img']) : '' ;?>  
        <?php $offer_title = (!empty($item['title'])) ? wp_trim_words( $item['title'], 12, '...' ) : ''; ?>
        <?php $offer_post_url = $item['url'] ;?>
        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
        <?php $offer_price_old = (!empty($item['_price_movers']['price_old'])) ? $item['_price_movers']['price_old'] : ''; ?> 
        <?php $currency_code = (!empty($item['currencyCode'])) ? $item['currencyCode'] : ''; ?> 
        <?php $modulecode = (!empty($item['module_id'])) ? $item['module_id'] : ''; ?>
        <?php if($offer_price && rehub_option('ce_custom_currency')) {
            $currency_code = rehub_option('ce_custom_currency');
            $currency_rate = CurrencyHelper::getCurrencyRate($item['currencyCode'], $currency_code);
            if (!$currency_rate) $currency_rate = 1;
            $offer_price = $offer_price * $currency_rate;
            if($offer_price_old){$offer_price_old = $offer_price_old * $currency_rate;}
        }?>        
        <div class="deal_block_row flowhidden clearbox<?php if($i != $number): ?> mb15 pb15 border-grey-bottom<?php endif;?>">
            <div class="deal-pic-wrapper width-80 floatleft text-center img-maxh-100">
                <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink); ?>"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                    <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'crop'=> false, 'width'=> 80, 'height'=> 80, 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_70_70.png'));?>
                </a>                
            </div>
            <div class="rh-deal-details width-80-calc pl15 rtlpr15 floatright">
                <div>
                    <div class="fontnormal mt0 mb15"> 
                    <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                        <?php echo esc_attr($offer_title); ?>
                    </a>  
                    </div>                  
                </div>                                    
                <div class="rh-deal-price-cegg">
                    <div class="floatleft">
                        <?php if ($offer_price): ?>
                            <div class="product-price-new lineheight15">
                                <span class="font110 rehub-main-color"><?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code); ?></span>
                                <?php if ($item['_price_movers']['discount_value'] > 0): ?>
                                    <i class="rhicon rhi-arrow-down greencolor"></i>
                                <?php endif; ?>
                                <?php if ($item['_price_movers']['discount_value'] < 0): ?>
                                    <i class="rhicon rhi-arrow-up redcolor"></i>
                                <?php endif; ?>
                                <i class="rhicon rhi-question-circle greycolor" title="<?php echo \esc_attr(__('as of', 'rehub-theme') . ' ' . TemplateHelper::formatDatetime($item['_price_movers']['create_date'])); ?>"></i>
                            </div>
                        <?php endif; ?>                        
                        <?php if ($item['_price_movers']['discount_value']): ?>
                            <span class="product-price-old rh_opacity_5 font80">
                                <del>
                                <?php echo TemplateHelper::formatPriceCurrency($offer_price_old, $currency_code); ?>
                                </del>
                            </span> 
                        <?php endif; ?>
                    </div>                                              
                    <div class="floatright rh-deal-tag aff_tag">
                        <?php if ($logo = TemplateHelper::getMerhantLogoUrl($item, true)): ?>
                            <img class="cegg-merhant-logo" src="<?php echo esc_attr($logo); ?>" title="<?php echo esc_attr($item['domain']); ?>" alt="<?php echo esc_attr($item['domain']); ?>" />
                        <?php endif; ?>                   
                    </div>                
                </div>
            </div>
        </div>
        <?php endforeach; ?>
</div>