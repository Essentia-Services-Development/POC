<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UkYvTEVpS3VDbE1LdTYweVZSNDk0UWhZZmRvWk9DTUVZa2JyQXNtVHRJVXdkWnpVOXZNYnJpZVJ6U0hUVEkxTDZHV2J0Mm10cFlTWjNyV3lJTlpaVk5vZVVPTitYSVBrTGVQTE5RTTVDVWc3MzhoYm1QR1k3dVUxa2dtNHNXQ29BbmYyOWdzdVBPcVpLbmlVTjYzTVVB*/

$PeepSoSharePhotos = PeepSoSharePhotos::get_instance();
$is_gif = $PeepSoSharePhotos->is_gif_file($location);
$gif_autoplay = PeepSo::get_option_new('photos_gif_autoplay');

// Treat GIF image as a normal image if gif_autoplay is enabled.
if ($is_gif && $gif_autoplay) {
	$is_gif = FALSE;
	$location = preg_replace('/\/(thumbs\/)?([^\/]+?)(_[sml])*.jpg/', '/$2.gif', $location);
}

?>
<img class="<?php echo $is_gif ? 'ps-js-photo-gif' : ''; ?>" src="<?php echo $location; ?>" alt="" />
<?php if ($is_gif) { ?>
<div class="ps-lightbox__play gcis gci-play ps-lightbox-play ps-js-btn-gif" style="display:block"></div>
<?php } ?>

<?php if (intval($pho_owner_id) === get_current_user_id()) {
	$params = array('photo_id' => $pho_id);
	$set_avatar_onclick = apply_filters('peepso_photos_set_as_avatar', 'peepso.photos.set_as_avatar({ photo_id: \'' . $pho_id . '\' });', $pho_id, $params);
	$set_cover_onclick = apply_filters('peepso_photos_set_as_cover', 'peepso.photos.set_as_cover({ photo_id: \'' . $pho_id . '\' });', $pho_id, $params);
	?>
	<div class="ps-lightbox__object-actions ps-lightbox-toolbar--options">
		<div class="ps-lightbox__object-dropdown ps-dropdown ps-js-dropdown" id="picso-photo-setting">
			<a class="ps-lightbox__object-action ps-dropdown__toggle ps-js-dropdown-toggle" data-value="">
				<i class="gcis gci-cog"></i> <?php echo __('Options', 'picso'); ?>
			</a>
			<div class="ps-dropdown__menu ps-js-dropdown-menu">
				<a href="#" onclick="<?php echo $set_avatar_onclick;?>; return false;"><?php echo __('Set as my avatar', 'picso'); ?></a>
				<a href="#" onclick="<?php echo $set_cover_onclick;?>; return false;"><?php echo __('Set as my cover', 'picso'); ?></a>
			</div>
		</div>
	</div>

	<input type="hidden" name="photoid_tobe_photo_profile" id="photoid_tobe_photo_profile" value="<?php echo $pho_id ?>" />
	<?php
	wp_nonce_field('profile-set-photo-profile', '_photoprofilenonce');
	wp_nonce_field('photo-delete-album', '_delete_album_nonce');
}
?>
