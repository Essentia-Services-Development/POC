<?php
defined('\ABSPATH') || exit;
/*
  Name: Tile
 */
__('Tile', 'content-egg-tpl');
?>

<?php \wp_enqueue_style('egg-bootstrap'); ?>

<div class="egg-container egg-video">
    <?php if ($title): ?>
        <h3><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($items as $item): ?>
            <div class="col-md-6">
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe loading="lazy" width="560" height="315"
                            src="https://www.youtube.com/embed/<?php echo esc_attr($item['extra']['guid']); ?>" frameborder="0"
                            allowfullscreen></iframe>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>