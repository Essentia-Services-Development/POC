<div class="ps-media__page-list-item <?php if (!$vid_thumbnail) { echo "ps-media__page-list-item--audio"; } ?> ps-js-video" data-post-id="<?php echo $vid_post_id; ?>">
  <div class="ps-media__page-list-item-inner">
    <a href="#" onclick="ps_comments.open('<?php echo $vid_post_id; ?>', 'video'); return false;">
      <?php if ($vid_thumbnail) { ?>
        <img src="<?php echo $vid_thumbnail;?>" />
      <?php
      } else { 
        $attachment_type = get_post_meta($vid_post_id, PeepSoVideos::POST_META_KEY_MEDIA_TYPE, TRUE);
        if ($attachment_type == PeepSoVideos::ATTACHMENT_TYPE_AUDIO) {
        ?>
          <img src="<?php echo PeepSoVideos::get_cover_art($vid_artist, $vid_album, FALSE); ?>">
        <?php } else if ($attachment_type == PeepSoVideos::ATTACHMENT_TYPE_VIDEO) { ?>
          <img src="<?php echo PeepSo::get_asset('images/video/default.png'); ?>">
        <?php } ?>
      <?php } ?>
      <i class="gcis gci-play"></i>
      <div class="ps-media__page-list-item-title">
        <span><?php echo $vid_title;?></span>
      </div>
    </a>
  </div>
</div>
