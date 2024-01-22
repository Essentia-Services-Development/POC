<?php
defined('\ABSPATH') || exit;
/*
  Name: Large responsive
 */
?>

<?php \wp_enqueue_style('egg-bootstrap'); ?>

<div class="egg-container egg-video">
    <?php if ($title): ?>
        <h3><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <?php foreach ($items as $item): ?>
        <h4><?php echo esc_html($item['title']); ?></h4>
        <div class="embed-responsive embed-responsive-16by9">
            <iframe loading="lazy" width="560" height="315"
                    src="https://www.youtube.com/embed/<?php echo esc_attr($item['extra']['guid']); ?>?rel=0" frameborder="0"
                    allowfullscreen></iframe>
        </div>
        <?php if ($item['description']): ?>
            <p><?php echo wp_kses_post($item['description']); ?></p>
        <?php endif; ?>
    <?php endforeach; ?>
</div>