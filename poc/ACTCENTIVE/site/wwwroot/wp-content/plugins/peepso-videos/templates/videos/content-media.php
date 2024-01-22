<?php
$PeepSoActivity = PeepSoActivity::get_instance();

if( isset($force_oembed) && true == $force_oembed && isset($content)) {
	echo $content;
	unset($content);
}

if(!isset($oembed_type) || (isset($oembed_type) && 'video' === $oembed_type) ) {
?>
<div class="ps-media ps-media--video ps-media-video">
	<?php if (isset($content) && !empty($content)) { ?>
	<div class="ps-media__thumbnail ps-media-thumbnail video-avatar">
		<div class="<?php $PeepSoActivity->content_media_class('media-object'); ?>">
			<?php echo ($content); ?>
		</div>
	</div>
	<?php } ?>
	<div class="ps-media__body ps-media-body video-description">
		<div class="ps-media__title ps-media-title">
			<?php echo ($title); ?>
		</div>
		<div class="ps-media__subtitle">
			<?php echo ($host); ?>
		</div>
		<div class="ps-media__desc ps-media-desc"><?php if (isset($description)) echo substr(strip_tags(apply_filters('peepso_remove_shortcodes', PeepSoSecurity::strip_content($description))), 0, 250); ?></div>
	</div>
</div>
<?php }
