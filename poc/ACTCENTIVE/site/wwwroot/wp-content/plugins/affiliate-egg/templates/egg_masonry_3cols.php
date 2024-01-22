<?php defined( '\ABSPATH' ) || exit;
/*
  Name: Masonry (3 colums)
 */
__('Masonry (3 colums)', 'affegg');

use Keywordrush\AffiliateEgg\TemplateHelper;
?>

<?php $this->enqueueStyle(); ?>
<?php wp_enqueue_style('affegg-masonry', plugins_url('style/masonry.css', __FILE__)); ?>
<?php wp_enqueue_script('affegg-jquery-masonry', plugins_url('js/jquery.masonry.min.js', __FILE__), array('jquery', 'affegg-bootstrap')); ?>
<?php wp_enqueue_script('affegg-masonry-scrypt', plugins_url('js/masonry-script.js', __FILE__)); ?>

<div class="egg-container">

    <div class="affegg-masonry row-fluid">
        <?php foreach ($items as $i => $item): ?>          
            <a rel="nofollow" target="_blank" class="image" title="<?php echo esc_attr($item['title']); ?>" href="<?php echo esc_url($item['url']) ?>"<?php echo $item['ga_event'] ?>>

                <div class="item masonry-brick">
                    <div class="picture">
                        <img class="img-responsive" src="<?php echo esc_attr($item['img']) ?>" alt="<?php echo esc_attr($item['title']); ?>" />
                        <div class="item-content">
                            <div class="description">

                                <strong><?php echo esc_html(TemplateHelper::truncate($item['title'], 80)); ?><?php if ($item['manufacturer'] && mb_strlen($item['title'], 'utf-8') < 80): ?>, <?php echo esc_html($item['manufacturer']); ?><?php endif; ?></strong>
                                <?php if ($item['description']): ?>
                                    <p><?php echo $item['description']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="meta">
                                <?php if ($item['price']): ?>
                                    <?php if ($item['old_price']): ?>
                                        <strike><?php echo TemplateHelper::formatPriceCurrency($item['old_price_raw'], $item['currency_code']); ?></strike>
                                    <?php endif; ?>
                                    <?php if ($item['price']): ?>
                                        <span class="cegg-price"><?php echo TemplateHelper::formatPriceCurrency($item['price_raw'], $item['currency_code'], '<span class="cegg-currency">', '</span>'); ?></span>
                                    <?php endif; ?> 
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

        <?php endforeach; ?>
    </div>
    <?php if ($see_more_uri): ?>
        <div class="row">
            <div class="col-md-12 text-center"> 
                <a class="btn btn-info" rel="nofollow" target="_blank" href="<?php echo $see_more_uri; ?>"><?php _e('See more...', 'affegg'); ?></a>
            </div>
        </div>
    <?php endif; ?>
</div>