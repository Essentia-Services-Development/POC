<?php
/*
 * Name: Buttons row
 * Modules:
 * Module Types: PRODUCT
 * 
 */

use ContentEgg\application\helpers\TemplateHelper;

if (empty($cols) || $cols > 12)
    $cols = 3;
$col_size = ceil(12 / $cols);

if (!$btn_text)
    $btn_text = sprintf(__('%s at %s', 'content-egg-tpl'), '%PRICE%', '%MERCHANT%');

$all_items = TemplateHelper::sortAllByPrice($data, $order);
?>

<div class="egg-container egg-btns-row">

    <?php if ($title): ?>
        <h3><?php echo \esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row">
            <?php $i = 0; ?>
            <?php foreach ($all_items as $item): ?>

                <div class="col-md-<?php echo esc_attr($col_size); ?> col-xs-12 cegg-btn-cell">
                    <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>" class="btn btn-danger btn-block"><?php TemplateHelper::buyNowBtnText(true, $item, $btn_text); ?></a> 
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
