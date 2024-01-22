<?php
/*
  Name: Price tracker & alert
 */
__('Price tracker & alert', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;

defined('\ABSPATH') || exit;
?>

<div class="egg-container cegg-price-tracker-item">

    <?php if ($title): ?>
        <h3><?php echo \esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="products">

        <?php foreach ($items as $item): ?>
            <div class="row">
                <div class="col-md-8">

                    <?php if ($item['rating']): ?>
                        <div>
                            <?php TemplateHelper::printRating($item, 'default'); ?>
                        </div>
                    <?php endif; ?>
                    <h3 class="media-heading"
                        id="<?php echo \esc_attr($item['unique_id']); ?>"><?php echo esc_html($item['title']); ?><?php if ($item['manufacturer']): ?>, <?php echo \esc_html($item['manufacturer']); ?><?php endif; ?></h3>


                    <div class="panel panel-default cegg-price-tracker-panel">
                        <div class="panel-body">
                            <div class="row cegg-no-bottom-margin">
                                <div class="col-md-7 col-sm-7 col-xs-12 cegg-mb15">

                                    <?php if ($item['price']): ?>
                                        <span class="cegg-price">
                                            <small><?php TemplateHelper::esc_html_e('Price'); ?>:</small> <span
                                                class="cegg-price-color"><?php echo wp_kses_data(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'], '<span class="cegg-currency">', '</span>')); ?></span>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($item['stock_status']) && $item['stock_status'] == \ContentEgg\application\components\ContentProduct::STOCK_STATUS_OUT_OF_STOCK): ?>
                                        <mark title="<?php echo \esc_attr(sprintf(TemplateHelper::__('Last updated on %s'), TemplateHelper::getLastUpdateFormatted($module_id, $post_id))); ?>"
                                              class="stock-status status-<?php echo \esc_attr(TemplateHelper::getStockStatusClass($item)); ?>">
                                                  <?php echo \esc_html(TemplateHelper::getStockStatusStr($item)); ?>
                                        </mark>
                                    <?php endif; ?>
                                    <?php if ($item['price']): ?>
                                        <br><small
                                            class="text-muted"><?php echo esc_html(sprintf(TemplateHelper::__('as of %s'), TemplateHelper::getLastUpdateFormatted($module_id, $post_id))); ?></small>
                                        <?php endif; ?>
                                </div>
                                <div class="col-md-5 col-sm-5 col-xs-12 text-muted">
                                    <a<?php TemplateHelper::printRel(); ?> target="_blank"
                                                                           href="<?php echo esc_url_raw($item['url']); ?>"
                                                                           class="btn btn-danger"><?php TemplateHelper::buyNowBtnText(true, $item, $btn_text); ?></a>

                                    <?php if ($merchant = TemplateHelper::getMerhantName($item)): ?>
                                        <div>
                                            <small class="text-muted title-case"><?php echo \esc_html($merchant); ?></small>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>

                        </div>
                    </div>

                    <?php
                    $this->renderBlock('price_alert_inline', array('item' => $item,
                        'input_class' => 'input-sm',
                        'btn_class' => 'btn-sm'
                    ));
                    ?>

                </div>
                <div class="col-md-4">
                    <?php if ($item['img']): ?>
                        <div class="cegg-thumb">
                            <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
                                <img src="<?php echo esc_url($item['img']); ?>"
                                     alt="<?php echo \esc_attr($item['title']); ?>"/>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
            <?php $this->renderBlock('price_history', array('item' => $item)); ?>
        <?php endforeach; ?>
    </div>
</div>
