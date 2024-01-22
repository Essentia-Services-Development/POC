<?php
/*
 * Name: Price alert for lowest price product
 * Modules:
 * Module Types: PRODUCT
 * Shortcoded: FALSE
 * 
 */
?>
<?php
use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\helpers\CurrencyHelper;
// sort items by price
?> 
<?php $postid = (isset($post_id)) ? $post_id : get_the_ID();?>
<?php if (get_post_type($postid) == 'product'):?>
    <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($postid);
        $unique_id = $itemsync['unique_id']; $module_id = $itemsync['module_id'];?>
<?php else:?>
    <?php $unique_id = get_post_meta($postid, '_rehub_product_unique_id', true);?>
    <?php $module_id = get_post_meta($postid, '_rehub_module_ce_id', true);?>
<?php endif;?>

<?php if ($unique_id && $module_id) :?>
    <?php $syncitem = ($unique_id) ? $data[$module_id][$unique_id] : '';?>
    <?php if (TemplateHelper::isPriceAlertAllowed($unique_id, $module_id) && !empty($syncitem)): ?>
        <?php $offer_price = (!empty($syncitem['price'])) ? $syncitem['price'] : ''; ?>
        <?php $currency_code = (!empty($syncitem['currencyCode'])) ? $syncitem['currencyCode'] : ''; ?> 
        <?php if($offer_price && rehub_option('ce_custom_currency')) {
            $currency_code = rehub_option('ce_custom_currency');
            $currency_rate = CurrencyHelper::getCurrencyRate($syncitem['currencyCode'], $currency_code);
            if (!$currency_rate) $currency_rate = 1;
            $offer_price = $offer_price * $currency_rate;
            if(!empty($offer_price_old)){$offer_price_old = $offer_price_old * $currency_rate;}
        }?>     
        <div class="price-alert-form-ce">
            <div class="alert-form-ce-wrap">
                <h4 id="<?php echo esc_attr($unique_id); ?>"><i class="rhicon rhi-bell bigbellalert rehub-main-color" aria-hidden="true"></i><?php esc_html_e('Didn\'t find the right price? Set price alert below', 'rehub-theme');?></h4>          
                <div class="cegg-price-alert-wrap">
                    <div class="mb10 font90">
                        <?php esc_html_e('Set Alert for Product', 'rehub-theme'); ?>: <?php echo esc_attr($syncitem['title']);?> - <?php echo TemplateHelper::formatPriceCurrency($offer_price, $currency_code); ?>
                    </div>              
                    <form>
                        <input type="hidden" name="module_id" value="<?php echo esc_attr($module_id); ?>">
                        <input type="hidden" name="unique_id" value="<?php echo esc_attr($unique_id); ?>">
                        <input type="hidden" name="post_id" value="<?php echo (int)$postid; ?>">        
                        <div class="tabledisplay mobilecenterdisplay mobileblockdisplay flowhidden">
                            <div class="celldisplay pr15 rtlpl15">                    
                                <input type="email" name="email" placeholder="<?php esc_html_e('Your Email', 'rehub-theme'); ?>:" class="mb10" value="<?php if(method_exists('ContentEgg\application\helpers\TemplateHelper', 'getCurrentUserEmail')):?><?php echo esc_attr(TemplateHelper::getCurrentUserEmail()); ?><?php endif;?>">
                            </div>
                            <div class="celldisplay pr15 rtlpl15">
                                <input type="text" name="price" placeholder="<?php esc_html_e('Desired price', 'rehub-theme'); ?>:" class="mb10">
                            </div> 
                            <div class="celldisplay">
                                <input value="<?php esc_html_e('Start tracking', 'rehub-theme'); ?>" type="submit" class="wpsm-button rehub_main_btn small-btn floatright mb10" /> 
                            </div>                                
                        </div> 
                        <?php $privacy_url = TemplateHelper::getPrivacyUrl(); ?>
                        <?php if ($privacy_url): ?>
                            <div style="display: none;" class="price-alert-agree-wrap mt10 font80">
                                <label class="price-alert-agree-label">
                                    <input type="checkbox" name="accepted" value="1" id="cegg_alert_accepted" required />
                                    <?php esc_html_e( 'I agree to the', 'rehub-theme' ); ?> <a href="<?php echo esc_url($privacy_url);?>" target="_blank"><?php esc_html_e( 'Privacy Policy', 'rehub-theme' ); ?></a>
                                </label>
                            </div>
                        <?php endif; ?>                        
                    </form>
                    <div class="cegg-price-loading-image" style="display: none;"><img src="<?php echo get_template_directory_uri() . '/images/ajax-loader.gif' ?>" /></div>
                    <div class="cegg-price-alert-result-succcess" style="display: none; color: green;"></div>
                    <div class="cegg-price-alert-result-error" style="display: none; color: red;"></div>        
                </div>  
            </div>
        </div>
    <?php endif;?>
<?php endif;?>