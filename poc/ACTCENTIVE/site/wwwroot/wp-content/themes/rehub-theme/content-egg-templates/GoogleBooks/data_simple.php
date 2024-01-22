<?php
/*
  Name: Simple
 */
?>
<?php if (isset($title) && $title): ?>
    <h3 class="cegg-shortcode-title"><?php echo esc_html($title); ?></h3>
<?php endif; ?>
<div class="egg-wrap">

    <?php foreach ($items as $item): ?>
        <div class="media border-grey-bottom pb10 mb20 flowhidden">
            <?php if ($item['img']): ?>
                <div class="media-left pr20 pb10 celldisplay verttop">
                    <img style="max-width: 225px;" class="thumbnail pt5 pl5 pr5 pb5 mb20 whitebg border-lightgrey blockstyle" src="<?php echo esc_url($item['img']); ?>" alt="<?php echo esc_attr($item['title']); ?>" />
                </div>
            <?php endif; ?>
            <div class="media-body flowhidden celldisplay verttop">
                <h4 class="media-heading mt0 mb10">
                    <?php echo esc_html($item['title']); ?>
                </h4>
                <small class="text-meta font85 greycolor blockstyle mb10">
                    <?php if ($item['extra']['publisher']): ?>
                        <?php echo esc_attr($item['extra']['publisher']); ?>.
                    <?php endif; ?>
                    <?php if ($item['extra']['publisher']): ?>
                        <?php echo date('Y', $item['extra']['date']); ?>
                    <?php endif; ?>
                    <a target="_blank" <?php echo ce_printRel();?> href="<?php echo esc_url($item['url']); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/gbs_preview.gif" /></a>

                </small>
                <p><?php echo esc_attr($item['description']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>