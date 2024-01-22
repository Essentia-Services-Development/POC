<?php
/*
  Name: Simple
 */

?>
<?php if (isset($title) && $title): ?>
    <h3 class="cegg-shortcode-title"><?php echo esc_html($title); ?></h3>
<?php endif; ?>
<div class="egg-wrap twi-wrap">
    <?php foreach ($items as $item): ?>
        <div class="twi_profile">
            <?php if ($item['profileImage']) :?>
                <img style="max-width: 30px;" class="twi-avatar" src="<?php echo esc_url($item['profileImage']); ?>" alt="<?php echo esc_html($item['extra']['author']); ?>" />
            <?php endif;?>
            <?php if ($item['extra']['author']) :?>
                <a <?php echo ce_printRel();?> target="_blank" href="<?php echo esc_url($item['url']); ?>">@<?php echo esc_html($item['extra']['author']); ?></a>
            <?php endif;?>
            <?php if ($item['extra']['followersCount']) :?>
                <a <?php echo ce_printRel();?> target="_blank" href="<?php echo esc_url($item['url']); ?>" class="twi-follow-btn" title="<?php echo esc_html($item['extra']['followersCount']); ?>"><i class="rhicon rhi-twitter"></i> <?php esc_html_e('Follow', 'rehub-theme') ;?></a>
            <?php endif;?>                        
        </div>
        <div class="media border-grey-bottom pb10 mb20 flowhidden">
            <div class="media-body flowhidden celldisplay verttop">
                <p><?php echo esc_attr($item['description']); ?></p>
            </div>        
            <?php if ($item['img']): ?>
                <div class="media-right celldisplay verttop">
                    <img style="max-width: 100px;" class="thumbnail pt5 pl5 pr5 pb5 mb20 whitebg border-lightgrey blockstyle" src="<?php echo esc_url($item['img']); ?>" alt="<?php echo esc_attr($item['title']); ?>" />
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>