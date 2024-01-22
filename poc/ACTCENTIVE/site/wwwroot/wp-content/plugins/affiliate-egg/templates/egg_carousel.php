<?php defined( '\ABSPATH' ) || exit;
/*
  Name: Slider
 */
__('Slider', 'affegg-tpl');
?>

<?php wp_enqueue_style('affegg-bootstrap'); ?>
<?php wp_enqueue_style('affegg-bootstrap-glyphicons'); ?>
<?php wp_enqueue_script('affegg-bootstrap'); ?>

<div class="egg-container">

    <div class="container-fluid">
        <div class="row">
            <div id="affegg-carousel-<?php echo $egg['id'] ?>-<?php echo $egg_counter ?>" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators">
                    <?php foreach ($items as $i => $item): ?>          
                        <li data-target="#affegg-carousel-<?php echo $egg['id'] ?>-<?php echo $egg_counter ?>" data-slide-to="<?php echo $i ?>"<?php if ($i == 0): ?> class="active"<?php endif; ?>></li>
                    <?php endforeach; ?>
                </ol>
                <div class="carousel-inner">
                    <?php foreach ($items as $i => $item): ?>          
                        <div class="item<?php if ($i == 0) echo ' active'; ?>"> 
                            <a rel="nofollow" target="_blank" href="<?php echo esc_url($item['url']) ?>"<?php echo $item['ga_event'] ?>>
                                <img src="<?php echo esc_attr($item['img']) ?>" alt="<?php echo esc_attr($item['title']); ?>">
                                <div class="carousel-caption">
                                    <strong><?php echo esc_html($item['title']); ?><?php if ($item['manufacturer']): ?>, <?php echo esc_html($item['manufacturer']); ?><?php endif; ?></strong>
                                    <?php if ($item['price']): ?>
                                        <p><?php echo $item['price_formatted']; ?></p>
                                    <?php endif; ?>
                                </div>                    
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a class="left carousel-control" href="#affegg-carousel-<?php echo $egg['id'] ?>-<?php echo $egg_counter ?>" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <a class="right carousel-control" href="#affegg-carousel-<?php echo $egg['id'] ?>-<?php echo $egg_counter ?>" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                </a>
            </div>
        </div>
    </div>
</div>
