<?php
/*
 * Name: Product card with price comparison popup
 * Modules:
 * Module Types: PRODUCT
 * 
 */

use ContentEgg\application\helpers\TemplateHelper;

\wp_enqueue_script('bootstrap-modal');

$all_items = TemplateHelper::sortAllByPrice($data, $order);
$cheapest = reset($all_items);
$item = TemplateHelper::selectItemByDescription($all_items);
?>

<?php if ($title): ?>
    <h3 class="cegg-shortcode-title"><?php echo \esc_html($title); ?></h3>
<?php endif; ?>

<div class="egg-container egg-item-popup">
    <div class="products">

        <div class="row">
            <div class="col-md-3 text-center cegg-image-container cegg-mb20">
                <?php if ($item['img']): ?>
                    <?php TemplateHelper::displayImage($item, 350, 350); ?>
                <?php endif; ?>
            </div>
            <div class="col-md-9">
                <?php if ($item['title']): ?>
                    <h3 class="cegg-item-title"><?php echo esc_html($item['title']); ?></h3>
                <?php endif; ?>
                <?php if ($item['description']): ?>
                    <?php echo wp_kses_post($item['description']); ?>
                <?php endif; ?>


                <?php if ($cheapest['price']): ?>
                    <div class="cegg-price-row" style="padding-top: 10px;">
                        <?php if (count($all_items) > 1): ?>
                            <?php echo esc_html(TemplateHelper::__('from')); ?>
                        <?php endif; ?>
                        <span class="cegg-price cegg-price-color"><?php echo esc_html(TemplateHelper::formatPriceCurrency($cheapest['price'], $cheapest['currencyCode'])); ?></span>


                        <?php if (count($all_items) <= 1 && ($item['module_id'] == 'Amazon' || $item['module_id'] == 'AmazonNoApi')): ?>
                            <div class="cegg-font60 cegg-lineheight15">
                                <?php echo esc_html(sprintf(TemplateHelper::__('as of %s'), TemplateHelper::dateFormatFromGmt($item['last_update']))); ?>
                                <?php TemplateHelper::printAmazonDisclaimer(); ?>
                            </div>
                        <?php endif; ?>                        

                    </div>
                <?php endif; ?>

                <div class="cegg-btn-row cegg-mb5" style="padding-top: 10px;">
                    <?php $this->renderPartial('block_popup_button', array('btn_class' => 'cegg-btn-big', 'btn_text' => $btn_text, 'title' => $title)); ?>
                </div>                    

            </div>
        </div>
    </div>

</div>


