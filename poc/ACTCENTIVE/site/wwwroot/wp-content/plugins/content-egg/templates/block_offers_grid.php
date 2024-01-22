<?php
/*
 * Name: Grid with prices (3 column)
 * Modules:
 * Module Types: PRODUCT
 * 
 */

__('Grid with prices (3 column)', 'content-egg-tpl');

if (empty($cols) || $cols > 12)
    $cols = 3;
$col_size = ceil(12 / $cols);
?>

<div class="egg-container egg-grid">

    <?php if ($title): ?>
        <h3><?php echo \esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row">
            <?php
            $i = 0;
            foreach ($data as $module_id => $items)
            {
                foreach ($items as $item)
                {
                    $this->renderBlock('grid_row', array('item' => $item, 'col_size' => $col_size, 'i' => $i));
                    $i++;
                    if ($i % $cols == 0)
                        echo '<div class="clearfix hidden-xs"></div>';
                    if ($i % 2 == 0)
                        echo '<div class="clearfix visible-xs-block"></div>';
                }
            }
            ?>
        </div>
    </div>

</div>
