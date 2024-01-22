<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php use ContentEgg\application\helpers\TemplateHelper;?>

<?php $product_update = TemplateHelper::getLastUpdateFormatted($module_id, $post_id);?>

<div class="rh_listoffers rh_listoffers_price_col">  
    <?php $i=0; foreach ($items as $key => $item): ?>
        <?php $domain = $merchant = '';?>
        <?php $offer_post_url = $item['url'] ;?>
        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
        <?php $aff_thumb = $item['img'] ;?>
        <?php $merchant = (!empty($item['merchant'])) ? $item['merchant'] : ''; ?> 
        <?php if (!empty($item['domain'])):?>
            <?php $domain = $item['domain'];?>
        <?php elseif (!empty($item['extra']['domain'])):?>
            <?php $domain = $item['extra']['domain'];?>
        <?php endif;?>
        <?php $offer_title = wp_trim_words( $item['title'], 20, '...' ); ?>  
        <?php if(rehub_option('rehub_btn_text') !='') :?>
            <?php $btn_txt = rehub_option('rehub_btn_text') ; ?>
        <?php else :?>
            <?php $btn_txt = \ContentEgg\application\helpers\TemplateHelper::buyNowBtnText(false, $item);?>
        <?php endif ;?>
        <?php $percentageSaved = (!empty($item['percentageSaved'])) ? $item['percentageSaved'] : '';?>
        <?php $availability = (!empty($item['availability'])) ? $item['availability'] : '';?> 
        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
        <?php $offer_price_old = (!empty($item['priceOld'])) ? $item['priceOld'] : ''; ?>
        <?php $currency = (!empty($item['currency'])) ? $item['currency'] : ''; ?>
        <?php $currency_code = (!empty($item['currencyCode'])) ? $item['currencyCode'] : ''; ?>
        <?php $i++;?>
        <div class="rh_listofferitem rh_list_mbl_im_left border-grey-bottom<?php if ($i == 1){echo' best_price_item';}?>"> 
            <div class="rh-flex-center-align rh-flex-justify-center pt15 pb15 mobileblockdisplay">
                <div class="rh_listcolumn rh_listcolumn_image text-center">   
                    <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>">
                        <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'height'=> 90, 'title' => $offer_title, 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_100_70.png'));?>                                    
                    </a>
                </div>
                <div class="rh_listcolumn rh-flex-grow1 rh_listcolumn_text">
                    <div class="simple_title mb15">
                        <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn font100 blackcolor blockstyle rehub-main-font lineheight20" href="<?php echo esc_url($afflink) ?>">
                            <?php echo esc_attr($offer_title); ?>
                        </a>
                    </div>
                    <?php $merchanttext = \ContentEgg\application\helpers\TemplateHelper::getShopInfo($item);?>
                    <?php if($merchanttext):?>
                        <div class="font80 mb10">
                            <?php echo ''.$merchanttext; ?>
                        </div> 
                    <?php endif;?> 
                    <?php if (!empty($item['extra']['totalUsed'])): ?>
                        <div class="mb5 font80 lineheight15 rh_opacity_7">
                        <?php echo (int)$item['extra']['totalUsed']; ?>
                        <?php esc_html_e('used', 'rehub-theme'); ?> <?php esc_html_e('from', 'rehub-theme'); ?>
                            <?php echo TemplateHelper::formatPriceCurrency($item['extra']['lowestUsedPrice'], $item['currencyCode']); ?>
                        </div>
                    <?php endif; ?>                                                   
                </div>                    
                <div class="rh_listcolumn rh_listcolumn_price text-center">
                    <?php if($offer_price) : ?>
                        <span class="price_count rehub-main-color fontbold">
                            <?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code, '<span class="cur_sign">', '</span>'); ?>
                            <?php if(!empty($offer_price_old)) : ?>
                            <strike class="blockstyle">
                                <span class="amount font70 rh_opacity_3 fontnormal"><?php echo TemplateHelper::formatPriceCurrency($offer_price_old, $currency_code, '<span class="value">', '</span>'); ?></span>
                            </strike>
                            <?php endif ;?>                                      
                        </span>                      
                    <?php endif ;?>
                    <?php $stock_status_str = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusStr($item) : '';?>
                    <?php $stock_status_class = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusClass($item) : '';?>                             
                    <?php if ($stock_status_str): ?>
                        <div title="<?php esc_html_e('Last update was on: ', 'rehub-theme'); ?><?php echo TemplateHelper::getLastUpdateFormatted($item['module_id'], $post_id); ?>" class="cegg-lineheight15 blockstyle font80 stock-status status-<?php echo esc_attr($stock_status_class);?>">
                            <?php echo esc_html($stock_status_str); ?>
                        </div>
                    <?php endif; ?>                                             
                </div>
                <div class="text-right-align rh_listcolumn_btn">
                    <div class="priced_block mb0 clearfix">
                        <a class="re_track_btn btn_offer_block" href="<?php echo esc_url($afflink) ?>" target="_blank" <?php echo ce_printRel();?>>
                            <?php echo esc_attr($btn_txt) ; ?>
                        </a>                                                        
                    </div>
                    <?php if($module_id == 'Amazon'):?>
                        <div class="font70 mb10"><?php echo TemplateHelper::getLastUpdateFormatted($module_id, get_the_ID());?></div>
                    <?php endif;?>                    
                    <?php if(!empty($logo)) :?>
                        <div class="egg-logo"><img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($offer_title); ?>" /></div>
                    <?php else :?>
                        <div class="aff_tag">
                            <img src="<?php echo esc_attr(TemplateHelper::getMerhantIconUrl($item, true)); ?>" alt="<?php echo ''.$module_id;?>" />
                            <?php if ($merchant):?>
                                <?php echo esc_html($merchant); ?>
                            <?php elseif($domain):?>
                                <?php echo esc_html($domain); ?>                                     
                            <?php endif;?>
                        </div>
                    <?php endif ;?>              
                </div>  
            </div>
        </div>
    <?php endforeach; ?>               
    <?php if (!empty($product_update)) :?>
        <div class="last_update"><?php esc_html_e('Last update was on: ', 'rehub-theme'); ?><?php echo ''.$product_update;?></div>
    <?php endif ;?>    
</div>
<div class="clearfix"></div>