<?php
/*
 * Name: Grid
 * 
 */

__('Grid', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;

if ($is_shortcode)
    $cols = 3;
else
    $cols = 1;

$col_size = 12 / $cols;
?>

<div class="egg-container egg-grid egg-grid-wdgt">
    <div class="container-fluid">
        <div class="row">
            <?php $i = 0; ?>
            <?php foreach ($items as $key => $item): ?>

                <div class="col-md-<?php echo esc_attr($col_size); ?> cegg-gridbox<?php if (!$is_shortcode) echo ' cegg-gridbox-border'; ?>">
                    <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">

                        <div class="cegg-thumb">
                            <div class="cegg-position-container2">
                                <span class="cegg-position-text2"><?php echo (int) $key + 1; ?></span>
                            </div>

                            <?php if ($item['img']): ?>
                                <img src="<?php echo \esc_attr($item['img']) ?>" alt="<?php echo \esc_attr($item['title']); ?>" />
                            <?php endif; ?>
                        </div>

                        <div class="producttitle">
                            <?php if ($merchant = TemplateHelper::getMerhantName($item)): ?>
                                <div class="cegg-mb10">    
                                    <small class="text-muted title-case"><?php echo \esc_html($merchant); ?></small>
                                </div>
                            <?php endif; ?>

                            <?php if ($item['rating']): ?>
                                <div class="cegg-title-rating">
                                    <?php TemplateHelper::printRating($item, 'small'); ?>
                                </div>
                            <?php endif; ?>            

                            <?php echo \esc_html(TemplateHelper::truncate($item['title'], 80)); ?>                 
                        </div>


                        <div class="row cegg-mb5">
                            <?php if ($item['_price_movers']['discount_percent'] > 0): ?>
                                <div class="col-xs-2 cegg-product-discount">
                                    <span class="product-discount-value"><?php echo esc_html($item['_price_movers']['discount_percent']); ?><span class="product-discount-symbol">%</span></span>
                                    <div class="product-discount-off">
                                        <?php TemplateHelper::esc_html_e('OFF'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="col-xs-10">
                                <div class="productprice">

                                    <?php if ($item['price']): ?>

                                        <?php if ($item['_price_movers']['discount_value']): ?>
                                            <div class="text-muted"><s title="<?php echo \esc_attr(TemplateHelper::getDaysAgo($item['_price_movers']['price_old_date'])); ?>"><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['_price_movers']['price_old'], $item['currencyCode'])); ?></s></div>
                                        <?php endif; ?>
                                        <span title="<?php echo \esc_attr(sprintf(TemplateHelper::__('as of %s'), TemplateHelper::dateFormatFromGmt($item['last_update']))); ?>" class="cegg-price cegg-price-color"><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'])); ?></span>

                                    <?php endif; ?>

                                    <?php if ($item['_price_movers']['discount_value'] > 0): ?>
                                        <span class="text-success">
                                            &#9660;<?php echo esc_html(TemplateHelper::formatPriceCurrency($item['_price_movers']['discount_value'], $item['currencyCode'])); ?>
                                        </span>
                                    <?php endif; ?>                        

                                </div>
                            </div>
                        </div>                          
                    </a>
                </div>            

                <?php
                $i++;
                if ($i % $cols == 0)
                    echo '<div class="clearfix"></div>';
                ?>
            <?php endforeach; ?>
        </div>
    </div>

</div>
