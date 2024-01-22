<div class="ps-post__background ps-js-post-background">
  <div class="ps-post__background-inner">
    <div>
      <div class="ps-post__background-text ps-js-post-background-text" contenteditable="true" data-placeholder="<?php echo __('Say what is on your mind...', 'peepso-core'); ?>"></div>
    </div>
  </div>
  <div class="ps-js-activity-background-warning" style="display:none; position:absolute; bottom:70px; left:0; right:0; text-align:center">
    <span style="display:inline-block; background:white; color:red; font-size:70%; padding:5px; margin:5px; border-radius:5px"><?php echo __('Please shorten the text or change the post type', 'peepso-core') ?></span>
  </div>
</div>
<div class="ps-postbox__backgrounds peepso-backgrounds">
  <?php foreach ($post_backgrounds as $post_background) : ?>
    <?php if ($post_background->image != '0.jpg') : ?>
      <div class="ps-postbox__backgrounds-item peepso-background-item ps-tip ps-tip--inline" aria-label="<?php echo $post_background->title; ?>" style="background-image:url(<?php echo $post_background->image_url; ?>)" data-preset-id="<?php echo $post_background->post_id; ?>" data-background="<?php echo $post_background->image_url; ?>" data-text-color="<?php echo $post_background->content->text_color; ?>"></div>
    <?php endif; ?>
  <?php endforeach; ?>
</div>
<style>
  /* @todo: Matthew/Nurul, please improve and move this styling to SCSS. */
  .ps-post__background-text [data-highlight] {
    background: lightblue;
    border-radius: 5px;
    box-shadow: 0 0 0 1px lightblue;
    padding: 0 2px;
  }
  .ps-post__background-selector {
    background: white;
    padding: 5px;
    position: absolute;
    transform: translateX(-50%);
    z-index: 999;
  }
  .ps-post__background-selector [data-item] {
    display: block;
    color: inherit;
    text-decoration: none;
  }
  .ps-post__background-selector [data-item].active {
    background: lightblue;
  }
</style>
