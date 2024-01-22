<?php
defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TemplateHelper;

if (TemplateHelper::isModuleDataExist($items, 'Amazon', 'AmazonNoApi'))
{
    \wp_enqueue_script('cegg-frontend', \ContentEgg\PLUGIN_RES . '/js/frontend.js', array('jquery'));
}

\wp_enqueue_script('bootstrap-popover');
?>

<div class="egg-container egg-list">

    <?php if ($title): ?>
        <h3><?php echo \esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="egg-listcontainer">
        <?php foreach ($items as $item): ?>
            <?php $this->renderBlock('list_row', array('item' => $item)); ?>
        <?php endforeach; ?>
    </div>

    <?php if ($module_id == 'Amazon' || $module_id == 'AmazonNoApi'): ?>
        <div class="row cegg-no-top-margin">
            <div class="col-md-12 text-right text-muted">
                <small>
                    <?php echo esc_html(sprintf(TemplateHelper::__('Last updated on %s'), TemplateHelper::getLastUpdateFormatted($module_id, $post_id))); ?>
                    <?php TemplateHelper::printAmazonDisclaimer(); ?>
                </small>
            </div>
        </div>
    <?php endif; ?>

</div>
