<div class="ps-post__background ps-js-activity-background" style="background-image: url(<?php echo $background; ?>);">
  <div class="ps-post__background-inner">
    <div>
      <div class="ps-post__background-text post-backgrounds-content ps-js-activity-background-text"
        data-placeholder="<?php echo __('Say what is on your mind...', 'peepso-core'); ?>"
        style="color: <?php echo $text_color ?>;"><?php echo $content; ?></div>
    </div>
  </div>
  <div class="ps-js-activity-background-warning" style="display:none; position:absolute; bottom:70px; left:0; right:0; text-align:center">
    <span style="display:inline-block; background:white; color:red; font-size:70%; padding:5px; margin:5px; border-radius:5px"><?php echo __('Please shorten the text or change the post type', 'peepso-core') ?></span>
  </div>
</div>
