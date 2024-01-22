<?php
/*
 * Name: Sorted offers list with no prices
 * Modules:
 * Module Types: PRODUCT
 * 
 */


use ContentEgg\application\helpers\TemplateHelper;

$all_items = TemplateHelper::sortAllByPrice($data, $order);
?>

<div class="egg-container cegg-list-no-prices">
    <?php if ($title): ?>
        <h3><?php echo \esc_html($title); ?></h3>
    <?php endif; ?>
        
    <div class="egg-listcontainer">

        <?php foreach ($all_items as $i => $item): ?>    
            <?php $this->renderBlock('list_row_no_price', array('i' => $i, 'item' => $item));?>
        <?php endforeach; ?>
        
    </div>
</div>


