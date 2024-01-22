<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VEFZZjB0cmdDbHhNZW54dWtvcWxHZ2dIWWtUYVRGc0I1RjFNR0lTMEIvSmVnV2RnK1NXZEY4WkdncW9vTUsxZlhxWW5nUVNiRXJGUnFPYkZlZ1plcHlnWGgwYUE4VUwwSU1PQjgxRHpwWEFGdE9idElDa2tjTFU0djhYZXF1U2FNPQ==*/

$PeepSoSharePhotos = PeepSoSharePhotos::get_instance();
$PeepSoActivity = PeepSoActivity::get_instance();
$link = $PeepSoActivity->post_link(FALSE);
$is_gif = $PeepSoSharePhotos->is_gif_file($location);
$gif_autoplay = PeepSo::get_option_new('photos_gif_autoplay');

// Add photo hash code open current photo in the lightbox.
$link .= '#photo=' . $pho_id;

// Treat GIF image as a normal image if gif_autoplay is enabled.
if ($is_gif && $gif_autoplay) {
	$is_gif = FALSE;
	$location = preg_replace('/\/(thumbs\/)?([^\/]+?)(_[sml])*.jpg/', '/$2.gif', $location);
}

?>
<a href="<?php echo $link; ?>" class="ps-media-photo ps-media-grid-item <?php echo $is_gif ? 'ps-media--gif' : ''; ?>"
		data-ps-grid-item data-photo-id="<?php echo $pho_id ?>"
		onclick="<?php echo $onclick; ?>" style="display:none">
	<div class="ps-media-grid-padding">
		<div class="ps-media-grid-fitwidth">
			<img src="<?php echo $location; ?>"
				data-ajax-id="<?php echo $ajax_id; ?>" data-ajax-dir="<?php echo $ajax_dir; ?>" data-ajax-file="<?php echo $ajax_file; ?>" />
			<?php if ($is_gif) { ?>
			<div class="ps-media__indicator"><span><?php echo __('Gif', 'picso'); ?></span></div>
			<?php } ?>
			<?php if (isset($has_extra_photos) && $has_extra_photos > 1) { ?>
			<div class="ps-media-photo-counter" style="top:0; left:0; right:0; bottom:0;">
				<span>+<?php echo $has_extra_photos ?></span>
			</div>
			<?php } ?>
		</div>
	</div>
</a>
