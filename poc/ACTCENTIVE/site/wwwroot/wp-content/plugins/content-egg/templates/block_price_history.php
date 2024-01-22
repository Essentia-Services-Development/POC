<?php
/*
 * Name: Price history for lowest price product
 * Modules:
 * Module Types: PRODUCT
 * 
 */

__('Price history for lowest price product', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\helpers\TextHelper;
?>

<?php
$all_items = TemplateHelper::sortAllByPrice($data);
$item = $all_items[0];
$module_id = $item['module_id'];
if (!$title)
    $title = TemplateHelper::__('Price History for') . ' ' . TextHelper::truncate($item['title'], 100);
?>

<div class="egg-container">
    <?php $this->renderBlock('price_history', array('item' => $item, 'module_id' => $module_id, 'title' => $title)); ?>

</div>