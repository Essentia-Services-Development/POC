<?php
defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TemplateHelper;
?>

<?php if ($item['module_id'] == 'Amazon' && (!empty($item['extra']['totalNew']) && $item['extra']['totalNew'] > 1 ) || !empty($item['extra']['totalUsed'])): ?>

    <div class="after-price-row cegg-lineh-20 cegg-mb15">
        <small class="text-muted">
            <?php if (!empty($item['extra']['totalNew']) && $item['extra']['totalNew'] > 1): ?>
                <?php echo esc_html(sprintf(TemplateHelper::__('%d new from %s'), $item['extra']['totalNew'], TemplateHelper::formatPriceCurrency($item['extra']['lowestNewPrice'], $item['currencyCode']))); ?>
                <br/>
            <?php endif; ?>
            <?php if (!empty($item['extra']['totalUsed'])): ?>
                <?php echo esc_html(sprintf(TemplateHelper::__('%d used from %s'), $item['extra']['totalUsed'], TemplateHelper::formatPriceCurrency($item['extra']['lowestUsedPrice'], $item['currencyCode']))); ?>
                <br/>
            <?php endif; ?>
            <?php if (!empty($item['extra']['IsEligibleForSuperSaverShipping'])): ?>
                <div class="text-success"><?php TemplateHelper::esc_html_e('Free shipping'); ?></div>
            <?php endif; ?>
        </small>
    </div>
<?php endif; ?>


<?php if ($item['module_id'] == 'Ebay'): ?>
    <?php $time_left = TemplateHelper::getTimeLeft($item['extra']['listingInfo']['endTimeGmt']); ?>

    <div class="after-price-row cegg-lineh-20 cegg-mb15">
        <small class="text-muted">

            <?php if ($item['extra']['sellingStatus']['bidCount'] !== ''): ?>
                <div><?php esc_html_e('Bids:', 'content-egg-tpl'); ?><?php echo esc_html($item['extra']['sellingStatus']['bidCount']); ?></div>
            <?php endif; ?>

            <?php if ($item['extra']['conditionDisplayName']): ?>
                <div>
                    <?php esc_html_e('Item condition:', 'content-egg-tpl'); ?>
                    <mark><?php echo esc_html($item['extra']['conditionDisplayName']); ?></mark>
                </div>
            <?php endif; ?>

            <?php if ($time_left): ?>
                <div>
                    <?php esc_html_e('Time left:', 'content-egg-tpl'); ?>
                    <span <?php
                    if (strstr($time_left, __('m', 'content-egg-tpl')))
                    {
                        echo 'class="text-danger"';
                    }
                    ?>><?php echo esc_html($time_left); ?></span>
                </div>
                <?php else: ?>
                <div style="color: red;">
                <?php esc_html_e('Ended:', 'content-egg-tpl'); ?>
                <?php echo esc_html(date('M j, H:i', strtotime($item['extra']['listingInfo']['endTime']))); ?><?php echo esc_html($item['extra']['listingInfo']['timeZone']); ?>
                </div>
            <?php endif; ?>

            <?php if ($item['extra']['shippingInfo']['shippingType'] == 'Free'): ?>
                <div class="text-success"><?php TemplateHelper::esc_html_e('Free shipping'); ?></div>
            <?php endif; ?>

            <?php if ($item['extra']['eekStatus']): ?>
                <div class="muted"><?php esc_html_e('EEK:', 'content-egg-tpl'); ?><?php _p($item['extra']['eekStatus']); ?></div>
    <?php endif; ?>
        </small>
    </div>
<?php endif; ?>    