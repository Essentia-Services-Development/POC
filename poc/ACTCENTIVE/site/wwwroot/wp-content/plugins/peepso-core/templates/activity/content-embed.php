<?php

if ( isset( $html ) ) {
	// Wrap embed iframe with `ps-media-iframe` class.
	if ( preg_match( '/<iframe/i', $html ) ) {
		// Add specific class for WP_Embed content.
		$css_wpembed = '';
		if ( preg_match( '/wp-embedded-content/i', $html ) ) {
			$css_wpembed = ' ps-media--wp ps-media-iframe--wpembed';
		}

		$html = apply_filters('the_content', $html);
		$html = '<div class="ps-media ps-media--iframe ps-media-iframe' . $css_wpembed . '">' . $html . '</div>';
	}
	echo $html;

} else {
	$linkAttr = 'rel="nofollow"';
	$linkTarget = (int) PeepSo::get_option('site_activity_open_links_in_new_tab', 1);
	if (1 === $linkTarget) {
		$linkAttr = 'rel="nofollow noreferrer noopener" target="_blank"';
	} else if (2 === $linkTarget && 0 !== strpos($url, site_url())) {
		$linkAttr = 'rel="nofollow noreferrer noopener" target="_blank"';
	}

	$small_thumbnail = PeepSo::get_option('small_url_preview_thumbnail', 0 );
?>
<div class="ps-media <?php echo $small_thumbnail ? '' : 'ps-media--vertical' ?> ps-media--embed ps-media-video" data-mime-type="<?php echo $mime_type; ?>" data-potential-thumbnails="<?php echo isset($potential_thumbnails) ? $potential_thumbnails : ""; ?>">
	<div class="ps-media__inner">
		<?php if ( ! isset( $html ) && isset( $thumbnail ) ) { ?>
		<div class="ps-media__thumbnail ps-media-thumbnail video-avatar">
	        <div class="ps-media__cover ps-media-cover-wrapper media-object <?php echo $thumbnail['type'];?>">
			<?php if ($thumbnail['type'] === 'audio') { ?>
				<audio preload="metadata" controls
						src="<?php echo $thumbnail['value']; ?>">
					<?php echo __('Sorry, your browser does not support embedded audio.', 'peepso-core') ?>
				</audio>
			<?php } else if ($thumbnail['type'] === 'video') { ?>
				<video preload="metadata" controls>
					<source src="<?php echo $thumbnail['value']; ?>"
						type="<?php echo $mime_type; ?>">
					<?php echo __('Sorry, your browser does not support embedded video.', 'peepso-core') ?>
				</video>
			<?php } else if ($thumbnail['type'] === 'image') { ?>
				<a class="ps-media__cover-inner ps-media-cover" href="<?php echo $url; ?>" <?php echo $linkAttr; ?> style="background-image:url('<?php echo $thumbnail['value']; ?>');">
					<img class="ps-media__cover-image" src="<?php echo $thumbnail['value']; ?>"
						alt="<?php echo __('preview thumbnail', 'peepso-core') ?>" />
				</a>
			<?php } else {
				echo $thumbnail['value'];
			} ?>
			</div>
		</div>
		<?php } ?>
		<div class="ps-media__body ps-media-body video-description">
			<div class="ps-media__title ps-media-title">
				<a href="<?php echo $url; ?>" <?php echo $linkAttr; ?>><?php echo $title; ?></a>
			</div>
			<div class="ps-media__subtitle">
				<a href="<?php echo $url; ?>" <?php echo $linkAttr; ?>><?php echo $site_name; ?></a>
			</div>
			<div class="ps-media__desc ps-media-desc"><?php
				if (isset($description)) {
					echo wp_trim_words($description, 55);
				}
			?></div>
		</div>
	</div>
</div><?php

}
