<?php
defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TemplateHelper;

if (TemplateHelper::isModuleDataExist($items, 'Amazon', 'AmazonNoApi'))
{
    \wp_enqueue_script('cegg-frontend', \ContentEgg\PLUGIN_RES . '/js/frontend.js', array('jquery'));
}
?>

<?php if ($title): ?>
    <h3 class="cegg-shortcode-title"><?php echo \esc_html($title); ?></h3>
<?php endif; ?>
<?php foreach ($items as $item): ?>

    <div class="egg-container egg-item">
        <div class="products">

            <?php $this->renderBlock('item_row', array('item' => $item)); ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="cegg-mb25">
                        <?php $this->renderPartialModule('_item_details_top', array('Flipkart'), array('item' => $item)); ?>
                        <?php $this->renderBlock('item_features', array('item' => $item)); ?>
                        <?php if ($item['description']): ?>
                            <p><?php echo wp_kses_post($item['description']); ?></p>
                        <?php endif; ?>
                        <?php
                        $this->renderPartialModule('_item_details_bottom', array(
                            'Envato',
                            'Udemy'
                                ), array('item' => $item));
                        ?>
                        <?php $this->renderBlock('item_reviews', array('item' => $item)); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>