<?php
/*
 * Name: Grid without price (4 column)
 * Modules:
 * Module Types: PRODUCT
 * 
 */

__('Grid without price (4 column)', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;

if (empty($cols) || $cols > 12)
    $cols = 4;
$col_size = ceil(12 / $cols);
?>
<div class="egg-container egg-grid">
    <?php if ($title): ?>
        <h3><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="container-fluid">
        <?php $i = 0; ?>
        <div class="row">
            <?php foreach ($data as $module_id => $items): ?>

                <?php foreach ($items as $item): ?>
                    <div class="col-md-<?php echo esc_attr($col_size); ?> col-xs-6 cegg-gridbox">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo \esc_url($item['url']) ?>">

                            <div class="cegg-thumb">
                                <?php if ($item['img']): ?>
                                    <?php TemplateHelper::displayImage($item, 170, 170); ?>
                                <?php endif; ?>
                            </div>

                            <div class="producttitle small">
                                <?php echo \esc_html(TemplateHelper::truncate($item['title'], 80)); ?>
                            </div>

                        </a>
                    </div>
                    <?php
                    $i++;
                    if ($i % $cols == 0)
                        echo '<div class="clearfix hidden-xs"></div>';
                    if ($i % 2 == 0)
                        echo '<div class="clearfix visible-xs-block"></div>';
                    ?>
                <?php endforeach; ?>  
            <?php endforeach; ?>  

        </div>
    </div>


</div>
