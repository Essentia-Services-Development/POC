<?php defined( '\ABSPATH' ) || exit; ?>
<?php

use Keywordrush\AffiliateEgg\TemplateHelper;
?>
<?php if (TemplateHelper::isPriceAlertAllowed($item['id'])): ?>

    <div class="egg-container affegg-price-alert-wrap">
        <strong><?php _e('Wait For A Price Drop', 'affegg-tpl'); ?></strong>
        <form class="form-inline" style="margin-top: 10px; margin-bottom: 5px;">
            <input type="hidden" name="product_id" value="<?php echo esc_attr($item['id']); ?>">
            <input type="hidden" name="post_id" value="<?php echo esc_attr(get_the_ID()); ?>">                                
            <div class="form-group">
                <label class="sr-only" for="affegg-email-<?php echo esc_attr($item['id']); ?>"><?php _e('Your Email', 'affegg-tpl'); ?></label>
                <input type="email" class="input-sm form-control" name="email" id="affegg-email-<?php echo esc_attr($item['id']); ?>" placeholder="<?php _e('Your Email', 'affegg-tpl'); ?>" required>
            </div>     
            <div class="form-group">
                <label class="sr-only" for="affegg-price-<?php echo esc_attr($item['id']); ?>"><?php _e('Desired Price', 'affegg-tpl'); ?></label>
                <div class="input-group">
                    <?php $cur_position = TemplateHelper::getCurrencyPos($item['currency_code']); ?>
                    <?php if ($cur_position == 'left' || $cur_position == 'left_space'): ?>
                        <div class="input-group-addon"><?php echo TemplateHelper::getCurrencySymbol($item['currency_code']); ?></div>
                    <?php endif; ?>
                    <input type="number" class="input-sm form-control" name="price" id="affegg-price-<?php echo esc_attr($item['id']); ?>" placeholder="<?php _e('Desired Price', 'affegg-tpl'); ?>" step="any" required>
                    <?php if ($cur_position == 'right' || $cur_position == 'right_space'): ?>
                        <div class="input-group-addon"><?php echo TemplateHelper::getCurrencySymbol($item['currency_code']); ?></div>
                    <?php endif; ?>
                </div>                                          
            </div>     
            <button class="btn btn-warning btn-sm" type="submit"><?php _e('SET ALERT', 'affegg-tpl'); ?></button>
        </form>
        <div class="affegg-price-loading-image" style="display: none;"><img src="<?php echo Keywordrush\AffiliateEgg\PLUGIN_RES . '/img/ajax-loader.gif' ?>" /></div>
        <div class="affegg-price-alert-result-succcess text-success" style="display: none;"></div>
        <div class="affegg-price-alert-result-error text-danger" style="display: none;"></div>
        <div class="text-muted small"><?php _e('You will receive a notification when the price drops.', 'affegg-tpl'); ?></div>
    </div>
<?php endif; ?>