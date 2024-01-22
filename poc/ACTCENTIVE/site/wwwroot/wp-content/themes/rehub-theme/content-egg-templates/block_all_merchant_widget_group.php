<?php
/*
 * Name: List widget with merchants + group tabs
 * Modules:
 * Module Types: PRODUCT
 * 
 */
?>
<?php 
wp_enqueue_script('rhcuttab');
use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\helpers\CurrencyHelper;
if (!$groups = TemplateHelper::getGroupsList($data, $groups))
{
    include(rh_locate_template('content-egg-templates/block_all_merchant_widget.php'));
    return;
}
?>
<?php if(is_array($groups) && count($groups) < 2) {
    include(rh_locate_template('content-egg-templates/block_all_merchant_widget.php'));
    return;
}?>
<div class=" clearfix"></div>

<?php $postid = (isset($post_id)) ? $post_id : get_the_ID();?>
<?php if (get_post_type($postid) == 'product'):?>
    <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($postid);
        $unique_id = $itemsync['unique_id']; $module_id = $itemsync['module_id'];?>
<?php else:?>
    <?php $unique_id = get_post_meta($postid, '_rehub_product_unique_id', true);?>
    <?php $module_id = get_post_meta($postid, '_rehub_module_ce_id', true);?>
<?php endif;?>
<?php $syncitem = ($unique_id) ? $data[$module_id][$unique_id] : '';?>

<?php if ($unique_id && $module_id && !empty($syncitem)) :?>
    <?php include(rh_locate_template( 'inc/parts/pricealertpopup.php' ) ); ?>                 
<?php endif;?>

<ul class="def_btn_link_tabs list-unstyled list-line-style lineheight15">
<?php foreach ($groups as $i => $group): ?>
    <li class="mr0 ml0 mb5<?php if ($i == 0): ?> active<?php endif; ?>">
    <?php $group_ids[$i] = TemplateHelper::generateGlobalId('rh-cegg-widget-merch-'); ?>
    <a role="tab" data-toggle="tab" href="#<?php echo \esc_attr($group_ids[$i]); ?>" class="rh-ce-gr-tabs floatleft font70"><?php echo \esc_html($group); ?></a>
    </li>
<?php endforeach; ?>
</ul>
<div class="clearbox"></div>
<?php $globaldata = $data; foreach ($groups as $i => $group): ?>
    <div role="tabpanel" class="tab-pane rh-ce-gr-cont<?php if ($i == 0): ?> active<?php endif; ?>" id="<?php echo \esc_attr($group_ids[$i]); ?>">

        <?php $data = TemplateHelper::filterByGroup($globaldata, $group);?>
        <?php $all_items = TemplateHelper::sortAllByPrice($data); ?>
        <?php $countitems = count($all_items);?>

        <?php if(!empty($all_items)):?>

            <div class="widget_merchant_list<?php if ($countitems > 7):?> expandme<?php endif;?>">
                <div class="tabledisplay">
                    <?php  foreach ($all_items as $key => $item): ?>
                        <?php $stock_status_str = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusStr($item) : '';?>
                        <?php $stock_status_class = (!empty($item['stock_status'])) ? TemplateHelper::getStockStatusClass($item) : '';?>                 
                        <?php $offer_post_url = $item['url'] ;?>
                        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
                        <?php $merchant = (!empty($item['merchant'])) ? $item['merchant'] : ''; ?>
                        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
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
                        <?php $lowestused_price = (!empty($item['extra']['lowestUsedPrice'])) ? $item['extra']['lowestUsedPrice'] : ''; ?>                
                        <?php if($offer_price && rehub_option('ce_custom_currency')) {
                            $currency_code = rehub_option('ce_custom_currency');
                            $currency_rate = CurrencyHelper::getCurrencyRate($item['currencyCode'], $currency_code);
                            if (!$currency_rate) $currency_rate = 1;
                            $offer_price = $offer_price * $currency_rate;
                        }?>                  
                        <?php if(rehub_option('rehub_btn_text') !='') :?><?php $btn_txt = rehub_option('rehub_btn_text') ; ?><?php else :?><?php $btn_txt = esc_html__('See it', 'rehub-theme') ;?><?php endif ;?>  
                        <div class="table_merchant_list module_class_<?php echo esc_attr($modulecode);?> rh_stock_<?php echo esc_attr($stock_status_class);?>">               
                            <div class="merchant_thumb">   
                                <a <?php echo ce_printRel();?> target="_blank" href="<?php echo esc_url($afflink) ?>" class="re_track_btn"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                                    <img src="<?php echo esc_attr(TemplateHelper::getMerhantIconUrl($item, true)); ?>" alt="<?php echo esc_attr($modulecode);?>" />
                                    <?php if (!empty($merchant)):?>
                                        <?php echo esc_html($merchant); ?>
                                    <?php elseif(!empty($domain)):?>
                                        <?php echo esc_html($domain); ?>                                      
                                    <?php endif;?>                                                          
                                </a>
                            </div>                  
                            <div class="price_simple_col">
                                <?php if(!empty($item['price'])) : ?>
                                    <div>
                                        <a <?php echo ce_printRel();?> target="_blank" href="<?php echo esc_url($afflink) ?>" class="re_track_btn"  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                                            <span class="val_sim_price">
                                                <?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code); ?>
                                            </span>
                                            <?php if (!empty($item['extra']['totalUsed'])): ?>
                                                <span class="val_sim_price_used_merchant">
                                                <?php echo (int)$item['extra']['totalUsed']; ?>
                                                <?php esc_html_e('used', 'rehub-theme'); ?> <?php esc_html_e('from', 'rehub-theme'); ?>
                                                    <?php echo TemplateHelper::formatPriceCurrency($lowestused_price, $currency_code); ?>
                                                </span>
                                            <?php endif; ?>                    
                                        </a>                       
                                    </div>
                                <?php endif ;?> 
                                <?php if($stock_status_class == 'outofstock'):?>
                                    <span class="blockstyle redbrightcolor font80"><?php echo esc_attr($stock_status_str);?></span>
                                <?php endif;?>                                              
                            </div>
                            <div class="buttons_col">
                                <a class="re_track_btn" href="<?php echo esc_url($afflink) ?>" target="_blank" <?php echo ce_printRel();?>  data-tracking-group="<?php echo esc_attr($modulecode);?>">
                                    <?php echo esc_html($btn_txt) ; ?>
                                </a>                                                            
                            </div>
                                                                                      
                        </div>
                    <?php endforeach; ?> 
                </div>     
                <div class="additional_line_merchant flowhidden">
                    <?php if ($countitems > 7):?>
                    <?php wp_enqueue_script('rhexpandoffers');?>
                    <span class="expand_all_offers"><?php esc_html_e('Show all', 'rehub-theme');?> <span class="expandme">+</span></span>
                    <?php endif;?>
                    <?php if ($unique_id && $module_id && !empty($syncitem)) {
                        include(rh_locate_template( 'inc/parts/pricehistorypopup.php' ) );
                    } ?>    
                </div>         
            </div>
            <div class="clearfix"></div>               
        <?php endif;?>        
    </div>
<?php endforeach; ?>
<?php $product_update = TemplateHelper::getLastUpdateFormattedAmazon($data);?>
<?php if($product_update):?>
    <div class="font60 lineheight20"><?php esc_html_e('Last Amazon price update was:', 'rehub-theme');?> <?php echo esc_html($product_update);?> <span class="csspopuptrigger" data-popup="ce-widgetamazon-disclaimer"><i class="rhicon rhi-question-circle greycolor font110"></i></span></div>
    <div class="csspopup" id="ce-widgetamazon-disclaimer">
        <div class="csspopupinner">
            <span class="cpopupclose cursorpointer lightgreybg rh-close-btn rh-flex-center-align rh-flex-justify-center rh-shadow5 roundborder">×</span>
            <?php esc_html_e('Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on Amazon.com (Amazon.in, Amazon.co.uk, Amazon.de, etc) at the time of purchase will apply to the purchase of this product.', 'rehub-theme');?>
        </div>
    </div>
<?php endif;?> 
<div class="clearfix"></div>