<?php
/*
 * Name: List
 * 
 */

use ContentEgg\application\helpers\TemplateHelper;
?>


<div class="egg-container cegg-list-withlogos egg-list-wdgt">

    <div class="egg-listcontainer">

        <?php foreach ($items as $key => $item) : ?>

            <div class="cegg-list-logo-title cegg-mb5 cegg-mt10<?php if ($is_shortcode) echo ' visible-xs'; ?> text-center">
                <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"><?php echo esc_html(TemplateHelper::truncate($item['title'], 100)); ?></a>
            </div>

            <div class="row-products" <?php if (!$is_shortcode) echo ' style="display:flex;"'; ?>>
                <div class="<?php if ($is_shortcode) echo 'col-md-2 col-sm-2 '; ?>col-xs-12 cegg-image-cell">

                    <div class="cegg-position-container2">
                        <span class="cegg-position-text2"><?php echo (int) $key + 1; ?></span>
                    </div>

                    <?php if ($item['img']) : ?>
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
                            <img<?php if (!$is_shortcode) echo ' style="max-height: 70px;"'; ?> src="<?php echo esc_url($item['img']); ?>" alt="<?php echo \esc_attr($item['title']); ?>" /></a>
                        <?php endif; ?>
                </div>
                <?php if ($is_shortcode) : ?>
                    <div class="<?php if ($is_shortcode) echo 'col-md-5 col-sm-5 '; ?>col-xs-12 cegg-desc-cell hidden-xs">
                        <div class="cegg-no-top-margin cegg-list-logo-title">
                            <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"><?php echo esc_html(TemplateHelper::truncate($item['title'], 100)); ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="<?php if ($is_shortcode) echo 'col-md-3 col-sm-3 '; ?>col-xs-12 cegg-price-cell text-center">
                    <div class="cegg-price-row">

                        <?php if ($item['_price_movers']['discount_value']) : ?>
                            <div class="text-muted"><s title="<?php echo \esc_attr(TemplateHelper::getDaysAgo($item['_price_movers']['price_old_date'])); ?>"><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['_price_movers']['price_old'], $item['currencyCode'])); ?></s></div>
                        <?php endif; ?>

                        <?php if ($item['price']) : ?>

                            <div title="<?php echo \esc_attr(sprintf(TemplateHelper::__('as of %s'), TemplateHelper::dateFormatFromGmt($item['last_update']))); ?>" class="cegg-price cegg-price-color cegg-price-<?php echo \esc_attr(TemplateHelper::getStockStatusClass($item)); ?>"><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'])); ?></div>

                            <?php if ($item['_price_movers']['discount_value'] > 0) : ?>
                                <span class="text-success">
                                    &#9660;<?php echo esc_html(TemplateHelper::formatPriceCurrency($item['_price_movers']['discount_value'], $item['currencyCode'])); ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="<?php if ($is_shortcode) echo 'col-md-2 col-sm-2 '; ?>col-xs-12 cegg-btn-cell">

                    <?php if ($item['_price_movers']['discount_percent'] > 0) : ?>
                        <div class="text-center product-discount-off"><?php echo esc_html($item['_price_movers']['discount_percent']); ?>% <?php TemplateHelper::esc_html_e('OFF'); ?></div>
                    <?php endif; ?>

                    <div class="cegg-btn-row">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>" class="btn btn-danger btn-block<?php if (!$is_shortcode) echo ' btn-sm'; ?>"><span><?php TemplateHelper::buyNowBtnText(true, $item, $btn_text); ?></span></a>
                    </div>
                    <?php if ($merchant = TemplateHelper::getMerhantName($item)) : ?>
                        <div class="text-center " style="margin: 0px; padding: 0px;">
                            <small class="text-muted title-case"><?php echo \esc_html($merchant); ?></small>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        <?php endforeach; ?>

    </div>
</div>