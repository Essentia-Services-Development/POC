<?php if ( ! defined( 'ABSPATH' ) ) {exit;}?>
<?php if(defined('\ContentEgg\PLUGIN_PATH')):?>
    <?php if (ContentEgg\application\helpers\TemplateHelper::isPriceAlertAllowed($unique_id, $module_id)): ?>
        <?php $rand = mt_rand();?>
        <div class="pricealertpopup-wrap flowhidden">
            <span class="cursorpointer csspopuptrigger floatright mb10 greencolor" data-popup="pricealert_<?php echo ''.$rand;?>"><i class="rhicon rhi-bell mr5" aria-hidden="true"></i> <?php esc_html_e('Set Lowest Price Alert', 'rehub-theme');?></span>
            <div class="csspopup" id="pricealert_<?php echo ''.$rand;?>">
                <div class="csspopupinner cegg-price-alert-popup">
                    <span class="cpopupclose cursorpointer lightgreybg rh-close-btn rh-flex-center-align rh-flex-justify-center rh-shadow5 roundborder">×</span>            
                    <div class="cegg-price-alert-wrap">                       
                        <div class="re_title_inmodal"><i class="rhicon rhi-bell" aria-hidden="true"></i> <?php esc_html_e('Notify me, when price drops', 'rehub-theme'); ?></div>
                        <div class="rh-line mb20"></div>
                        <div class="mb20 font90">
                            <?php esc_html_e('Set Alert for Product', 'rehub-theme'); ?>: <?php echo ''.$syncitem['title'];?> - <?php echo ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($syncitem['price'], $syncitem['currencyCode']); ?>
                        </div>                     
                        <form>
                            <input type="hidden" name="module_id" value="<?php echo esc_attr($module_id); ?>">
                            <input type="hidden" name="unique_id" value="<?php echo esc_attr($unique_id); ?>">
                            <input type="hidden" name="post_id" value="<?php echo (int)$postid; ?>">
                            <div class="re-form-group mb20">                               
                                <label><?php esc_html_e('Your Email', 'rehub-theme'); ?>:</label>
                                <input type="email" name="email" class="re-form-input">
                            </div>
                            <div class="re-form-group mb20">
                                <label><?php esc_html_e('Desired price', 'rehub-theme'); ?>:</label> 
                                <input type="text" name="price" class="re-form-input">
                            </div>
                            <input value="<?php esc_html_e('Start tracking', 'rehub-theme'); ?>" type="submit" class="wpsm-button rehub_main_btn" /> 
                            <?php $privacy_url = ContentEgg\application\helpers\TemplateHelper::getPrivacyUrl(); ?>
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
        </div>
    <?php endif;?>
<?php endif;?>