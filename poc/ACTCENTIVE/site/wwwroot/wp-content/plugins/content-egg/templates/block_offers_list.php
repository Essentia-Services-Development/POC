<?php
/*
 * Name: Sorted offers list with product images
 * Modules:
 * Module Types: PRODUCT
 * 
 */

__('Sorted offers list with product images', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;

if (isset($data['Amazon']) || isset($data['AmazonNoApi']))
    \wp_enqueue_script('cegg-frontend', \ContentEgg\PLUGIN_RES . '/js/frontend.js', array('jquery'));

$all_items = TemplateHelper::sortAllByPrice($data, $order, $sort);
$amazon_last_updated = TemplateHelper::getLastUpdateFormattedAmazon($data);
?>

<div class="egg-container cegg-list-withlogos">
    <?php if ($title): ?>
        <h3><?php echo \esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="egg-listcontainer">

        <?php foreach ($all_items as $key => $item): ?>    
            <?php $this->renderBlock('list_row', array('item' => $item, 'amazon_last_updated' => $amazon_last_updated));?>
        <?php endforeach; ?>
        
    </div>
</div>


