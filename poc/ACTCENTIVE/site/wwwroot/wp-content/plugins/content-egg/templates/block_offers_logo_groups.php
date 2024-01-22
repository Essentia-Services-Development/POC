<?php
/*
 * Name: Sorted offers list with store logos + group pills
 * Modules:
 * Module Types: PRODUCT
 *
 */

__('Sorted offers list with product images + group pills', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;

if (!$groups = TemplateHelper::getGroupsList($data, $groups))
{
    $this->renderPartial('block_offers_logo');
    return;
}

\wp_enqueue_script('bootstrap-tab');
?>

<div class="egg-container">
    <ul class="nav nav-pills">
        <?php foreach ($groups as $i => $group): ?>
            <?php $group_ids[$i] = TemplateHelper::generateGlobalId('cegg-list-'); ?>
            <li<?php if ($i == 0): ?> class="active"<?php endif; ?>><a data-toggle="egg-pill" href="#<?php echo \esc_attr($group_ids[$i]); ?>"><?php echo \esc_html($group); ?></a></li>
        <?php endforeach; ?>
    </ul>
    <div class="tab-content">
        <?php foreach ($groups as $i => $group): ?>
            <div id="<?php echo \esc_attr($group_ids[$i]); ?>" class="tab-pane fade <?php if ($i == 0): ?> in active<?php endif; ?>">
                <?php $this->renderPartial('block_offers_logo', array('data' => TemplateHelper::filterByGroup($data, $group))); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>