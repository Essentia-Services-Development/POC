<?php
defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TemplateHelper;
?>

<div class="row">
    <div class="col-md-6 text-center cegg-image-container cegg-mb20">
        <?php if ($item['img']): ?>
            <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
                <?php TemplateHelper::displayImage($item, 350, 350); ?>
            </a>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <?php if ($item['title']): ?>
            <h3 class="cegg-item-title"><?php echo esc_html($item['title']); ?></h3>
        <?php endif; ?>
        <?php if ($item['rating']): ?>
            <div class="cegg-mb5">
                <?php TemplateHelper::printRating($item, 'default'); ?>
            </div>
        <?php endif; ?>

        <div class="cegg-price-row">

            <?php if ($item['price']): ?>
                <span class="cegg-price cegg-price-color">            
                    <?php if ($item['priceOld']): ?>
                        <small class="text-muted"><s><?php echo wp_kses(TemplateHelper::formatPriceCurrency($item['priceOld'], $item['currencyCode'], '<small>', '</small>'), array('small' => array())); ?></s></small>
                        <br>
                    <?php endif; ?>
                    <?php echo wp_kses(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'], '<span class="cegg-currency">', '</span>'), array('span' => array('class'))); ?></span>
                <?php endif; ?>


            <?php if ($stock_status = TemplateHelper::getStockStatusStr($item)): ?>
                <mark title="<?php echo \esc_attr(sprintf(TemplateHelper::__('Last updated on %s'), TemplateHelper::getLastUpdateFormatted($module_id, $post_id))); ?>"
                      class="stock-status status-<?php echo \esc_attr(TemplateHelper::getStockStatusClass($item)); ?>">
                    &nbsp;<?php echo \esc_html($stock_status); ?>
                </mark>
            <?php endif; ?>
            <?php if ($cashback_str = TemplateHelper::getCashbackStr($item)): ?>
                <div class="cegg-cashback"><?php echo esc_html(sprintf(TemplateHelper::__('Plus %s Cash Back'), $cashback_str)); ?></div>
            <?php endif; ?>
        </div>

        <?php $this->renderBlock('item_after_price_row', array('item' => $item)); ?>

        <div class="cegg-btn-row cegg-mb5">
            <div><a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"
                                                        class="btn btn-danger cegg-btn-big"><?php TemplateHelper::buyNowBtnText(true, $item, $btn_text); ?></a>
            </div>
            <span class="title-case text-muted">
                <?php TemplateHelper::getMerhantName($item, true); ?>
                <?php TemplateHelper::printShopInfo($item, array('data-placement' => 'bottom')); ?>

            </span>
        </div>
        <div class="cegg-last-update-row cegg-mb15">
            <span class="text-muted">
                <small>
                    <?php echo esc_html(sprintf(TemplateHelper::__('as of %s'), TemplateHelper::getLastUpdateFormatted($module_id, $post_id))); ?>
                    <?php
                    if ($module_id == 'Amazon' || $module_id == 'AmazonNoApi')
                    {
                        TemplateHelper::printAmazonDisclaimer();
                    }
                    ?>
                </small>
            </span>
        </div>
    </div>
</div>

