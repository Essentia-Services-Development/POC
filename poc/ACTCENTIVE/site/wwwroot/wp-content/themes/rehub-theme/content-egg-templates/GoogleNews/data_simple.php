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
        <?php 
            $title = (!empty($item['title'])) ? esc_html($item['title']) : '';
            $url = (!empty($item['url'])) ? esc_html($item['url']) : '';
            $img = (!empty($item['img'])) ? esc_html($item['img']) : '';
        ?>
        <div class="media border-grey-bottom pb10 mb20 flowhidden">
            <?php if ($item['img']): ?>
                <div class="media-left pr20 pb10 celldisplay verttop">
                    <img style="max-width: 225px;" class="media-object blockstyle thumbnail pt5 pl5 pr5 pb5 mb20 whitebg border-lightgrey" src="<?php echo esc_url($item['img']); ?>" alt="<?php echo esc_attr($item['title']); ?>" />
                </div>
            <?php endif; ?>
            <div class="media-body flowhidden celldisplay verttop">
                <h4 class="media-heading mt0 mb10">
                    <?php echo wpsm_hidelink_shortcode(array('link'=>$url, 'text'=>$title));?>
                </h4>
                <small class="text-meta font85 greycolor blockstyle mb10">
                    <?php echo date(get_option('date_format'), $item['extra']['date']); ?> -
                    <?php echo wpsm_hidelink_shortcode(array('link'=>$url, 'text'=>$item['extra']['source']));?>
                </small>
                <p><?php echo esc_attr($item['description']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>