<?php
defined('\ABSPATH') || exit;
/*
  Name: Simple
 */
__('Simple', 'content-egg-tpl');
?>

<?php \wp_enqueue_style('egg-bootstrap'); ?>

<div class="egg-container egg-video">
    <?php if ($title): ?>
        <h3><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <?php foreach ($items as $item): ?>
        <div class="row">
            <div class="col-md-12">
                <iframe loading="lazy" width="560" height="315"
                        src="https://www.youtube.com/embed/<?php echo esc_attr($item['extra']['guid']); ?>" frameborder="0"
                        allowfullscreen></iframe>
                <h4><?php echo esc_html($item['title']); ?></h4>
                <?php if ($item['description']): ?>
                    <p><?php echo wp_kses_post($item['description']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>