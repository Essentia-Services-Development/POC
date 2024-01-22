<?php
/*
  Name: Price alert form
 */

use ContentEgg\application\helpers\TemplateHelper;
?>

<?php foreach ($items as $item): ?>
    <?php if (TemplateHelper::isPriceAlertAllowed($item['unique_id'], $module_id)): ?>
        <div class="price-alert-form-ce">
            <div class="alert-form-ce-wrap">
                <h4 id="<?php echo esc_attr($item['unique_id']); ?>"><i class="rhicon rhi-bell bigbellalert rehub-main-color" aria-hidden="true"></i><?php esc_html_e('Didn\'t find the right price? Set price alert below', 'rehub-theme');?></h4>          
                <div class="cegg-price-alert-wrap">
                    <div class="mb10 font90">
                        <?php esc_html_e('Lowest price Product', 'rehub-theme'); ?>: <?php echo esc_attr($item['title']);?> - <?php echo TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode']); ?>
                    </div>              
                    <form>
                        <?php $postid = (isset($post_id)) ? $post_id : get_the_ID();?>
                        <input type="hidden" name="module_id" value="<?php echo esc_attr($module_id); ?>">
                        <input type="hidden" name="unique_id" value="<?php echo esc_attr($item['unique_id']); ?>">
                        <input type="hidden" name="post_id" value="<?php echo (int)$postid; ?>">        
                        <div class="tabledisplay mobileblockdisplay mobilecenterdisplay flowhidden">
                            <div class="celldisplay pr15 rtlpl15">                    
                                <input type="text" name="email" placeholder="<?php esc_html_e('Your Email', 'rehub-theme'); ?>:" class="mb10">
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
<?php endforeach; ?>