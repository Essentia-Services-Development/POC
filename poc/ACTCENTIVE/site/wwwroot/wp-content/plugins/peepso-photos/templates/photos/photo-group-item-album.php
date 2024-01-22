<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UUNHSFlGRkI0dFJoVjF3dUN5dWM1aWtWMjhmejdoWVNtc3p5ZDg3TDBubE4yUU4yMThManYyb1VsUitFTUNCejFORnBnQkFucy82bFlpT1JQOWpCdzMwRUFpNTNPOHZ6R05neGRFeDIyZGt6VktBRThKMHk3MGltWHI2c3h5emZxamEwMTlZZ2Q3OUwzZS94NHdIWUxR*/
// album title
$title = (0 === intval($album->pho_system_album)) ? $album->pho_album_name : __($album->pho_album_name, 'groupso');

// default thumbnail
$pho_thumb = PeepSo::get_asset('images/album/default.png');

// if a custom thumb exists
if(isset($album->cover_photo->pho_thumbs['m_s'])) {
	$pho_thumb = $album->cover_photo->pho_thumbs['m_s'];
}

?>
<div class="ps-photos__list-item ps-photos__list-item--album ps-js-album">
	<div class="ps-photos__list-item-inner">
		<a title="<?php echo $title;?>" href="<?php echo $profile_url . 'photos/album/' . $album->pho_album_id;?>" data-id="<?php echo $album->pho_album_id; ?>">
			<img class="" src="<?php echo $pho_thumb;?>" title="" alt="<?php echo $title;?>" />
			<div class="ps-photos__list-item-overlay">
				<div class="ps-photos__list-item-title"><?php echo $title; ?></div>
				<div class="ps-photos__list-item-details"><?php

					// @todo:num photo album
					echo sprintf(_n( '%s photo', '%s photos', $album->num_photo, 'picso' ), $album->num_photo);

				?></div>
			</div>
		</a>
	</div>
</div>
