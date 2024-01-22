<?php
$PeepSoActivity = PeepSoActivity::get_instance();

if( isset($force_oembed) && true == $force_oembed && isset($content)) {
	// Add specific class for WP_Embed content.
	$css_wpembed = '';
	if ( strpos($content, 'wp-embedded-content') && strpos($content, 'ps-media ps-media--iframe ps-media-iframe') ) {
		if ( FALSE === strpos($content, 'ps-media-iframe--wpembed') ) {
			$content = str_replace( 'ps-media ps-media--iframe ps-media-iframe', 'ps-media ps-media--iframe ps-media-iframe ps-media-iframe--wpembed', $content);
		}
	}

	echo $content;
	if (strpos($content, 'blockquote') !== FALSE) {
		return;
	}
	unset($content);
}

if(!isset($oembed_type) || (isset($oembed_type) && in_array($oembed_type, array('video', 'rich'))) ) {
?>
<div class="ps-media ps-media--embed ps-media-video">
	<div class="ps-media__inner">
		<?php if (isset($content) && !empty($content)) { ?>
		<div class="ps-media__thumbnail ps-media-thumbnail video-avatar">
			<div class="ps-media__cover <?php $PeepSoActivity->content_media_class('media-object'); ?>">
				<a class="ps-media__cover-inner" href="<?php echo $url; ?>" rel="nofollow" <?php echo $target; ?> style="background-image: url('<?php echo $og_image_url;?>');">
					<?php echo ($content); ?>
				</a>
			</div>
		</div>
		<?php } ?>
		<div class="ps-media__body ps-media-body video-description">
			<div class="ps-media__title ps-media-title">
				<a href="<?php echo $url; ?>" rel="nofollow" <?php echo $target; ?>><?php echo ($title); ?></a>
			</div>
			<div class="ps-media__subtitle">
				<a href="<?php echo $url; ?>" rel="nofollow" <?php echo $target; ?>><?php echo ($host); ?></a>
			</div>
			<div class="ps-media__desc ps-media-desc"><?php
				if (isset($description)) {
					echo wp_trim_words($description, 55);
				}
			?></div>
		</div>
	</div>
</div>
<?php }
