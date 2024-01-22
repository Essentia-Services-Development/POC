<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php use ContentEgg\application\helpers\TemplateHelper;?>

<?php $i=0; foreach ($items as $key => $item): ?>
    <?php $stock_status_str = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusStr($item) : '';?>
    <?php $stock_status_class = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusClass($item) : '';?>
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
    <?php $description = (!empty($item['description'])) ? $item['description'] : '';?>
    <?php $features = (!empty($item['extra']['itemAttributes']['Feature'])) ? $item['extra']['itemAttributes']['Feature'] : ''?>
    <?php $keyspecs = (!empty($item['extra']['keyspecs'])) ? $item['extra']['keyspecs'] : ''?>                         
    <?php $i++;?>  

    <div class="rh_ce_compact_block">
        <div class="r_offer_details rh_listitem top_rating_item">
            <div class="rh-flex-center-align rh-flex-justify-center pt15 pb15 mobileblockdisplay">
                <div class="listbuild_image listitem_column text-center">   
                    <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn img-centered-flex rh-flex-center-align rh-flex-justify-center" href="<?php echo esc_url($afflink) ?>">
                        <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'width'=> 126, 'title' => $offer_title));?>                                   
                    </a> 
                    <?php if (!empty($item['extra']['itemLinks'][3])): ?>
                        <span class="add_wishlist_ce">
                            <a href="<?php echo esc_url($item['extra']['itemLinks'][3]['URL']);?>" <?php echo ce_printRel();?> target="_blank" ><i class="rhicon rhi-heart"></i></a>
                        </span>
                    <?php endif; ?>                                                                  
                </div>
                <div class="rh-flex-grow1 listitem_title listitem_column">
                    <h4 class="offer_title">
                        <a <?php echo ce_printRel();?> target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>">
                            <?php echo esc_attr($offer_title); ?>
                        </a>
                    </h4> 
                    <?php $merchanttext = \ContentEgg\application\helpers\TemplateHelper::getShopInfo($item);?>
                    <?php if($merchanttext):?>
                        <div class="font80 mb10">
                            <?php echo ''.$merchanttext; ?>
                        </div> 
                    <?php endif;?> 
                    <?php if($keyspecs):?>
                        <div class="featured_list font90 greycolor lineheight20">
                            <?php $total_spec = count($keyspecs); $count = 0;?>
                            <?php foreach ($keyspecs as $keyspec) :?>
                                <?php echo esc_attr($keyspec); $count ++; ?><?php if ($count != $total_spec) :?>, <?php endif;?>
                            <?php endforeach; ?>   
                        </div>
                    <?php elseif ($features): ?>  
                        <ul class="featured_list font90 greycolor lineheight20">
                            <?php $length = $maxlength = 0;?>
                            <?php foreach ($features as $k => $feature): ?>
                                <?php if(is_array($feature)){continue;}?>
                                <?php $length = strlen($feature); $maxlength += $length; ?> 
                                <li><?php echo esc_attr($feature); ?></li>
                                <?php if($k >= 4 || $maxlength > 200) break; ?>                                    
                        <?php endforeach; ?>
                        </ul> 
                    <?php elseif ($description): ?>
                        <div class="font90 greycolor lineheight20"><?php kama_excerpt('maxchar=180&text='.$description); ?></div>
                    <?php endif; ?>  
                    <?php if (!empty($item['extra']['conditionDisplayName'])): ?>
                        <small class="small_size">
                        <?php esc_html_e('Condition: ', 'rehub-theme') ;?><span class="yes_available"><?php echo ''.$item['extra']['conditionDisplayName'] ;?></span>
                        <br />
                        </small>
                    <?php endif; ?>
                    <?php if (!empty($item['extra']['totalUsed'])): ?>
                        <span class="new-or-used-amazon">
                        <?php echo (int)$item['extra']['totalUsed']; ?>
                        <?php esc_html_e('used', 'rehub-theme'); ?> <?php esc_html_e('from', 'rehub-theme'); ?>
                            <?php echo TemplateHelper::formatPriceCurrency($item['extra']['lowestUsedPrice'], $item['currencyCode']); ?>
                        <br>
                        </span>
                    <?php endif; ?> 
                    <?php if (!empty($item['extra']['IsEligibleForSuperSaverShipping'])): ?>
                        <small class="small_size"><span class="yes_available"><?php esc_html_e('Free shipping', 'rehub-theme'); ?></span></small><br>
                    <?php endif; ?>
                    <?php if($module_id == 'Amazon'):?>
                        <div class="font80 rh_opacity_7">
                            <?php esc_html_e('Last update was on: ', 'rehub-theme'); ?><?php echo TemplateHelper::getLastUpdateFormatted('Amazon', $post_id); ?>
                        </div> 
                    <?php endif; ?>
                    <?php if ($stock_status_str): ?>
                        <div title="<?php esc_html_e('Last update was on: ', 'rehub-theme'); ?><?php echo TemplateHelper::getLastUpdateFormatted($item['module_id'], $post_id); ?>" class="cegg-lineheight15 blockstyle font80 stock-status status-<?php echo esc_attr($stock_status_class);?>">
                            <?php echo esc_html($stock_status_str); ?>
                        </div>
                    <?php endif; ?>                                                                                                     
                </div>
                <div class="listbuild_btn listitem_column text-center">
                    <div class="priced_block clearfix">
                        <?php if(!empty($offer_price)) : ?>
                            <div class="rh_price_wrapper mb5">
                                <span class="price_count">
                                    <ins class="rehub-main-color font110">                        
                                        <?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code, '<span class="cur_sign">', '</span>'); ?>
                                    </ins>
                                    <?php if(!empty($offer_price_old)) : ?>
                                    <del>
                                        <span class="amount">
                                            <?php echo TemplateHelper::formatPriceCurrency($offer_price_old, $currency_code, '<span class="value">', '</span>'); ?>
                                        </span>
                                    </del>
                                    <?php endif ;?>                                       
                                </span>                         
                            </div>
                        <?php endif ;?>
                        <div>
                            <a class="re_track_btn btn_offer_block" href="<?php echo esc_url($afflink) ?>" target="_blank" <?php echo ce_printRel();?>>
                                <?php echo esc_attr($btn_txt) ; ?>
                            </a>
                            <?php $logo = TemplateHelper::getMerhantLogoUrl($item, false);?>
                            <?php if(!empty($logo)) :?>
                                <div class="egg-logo mt10">
                                <img src="<?php echo esc_attr(TemplateHelper::getMerhantLogoUrl($item, true)); ?>" alt="<?php echo esc_attr($offer_title); ?>" />
                                </div>
                            <?php endif;?>                     
                        </div>
                    </div>
                </div>
            </div>                                             
        </div>
    </div>
    <div class="clearfix"></div>


<?php endforeach; ?>