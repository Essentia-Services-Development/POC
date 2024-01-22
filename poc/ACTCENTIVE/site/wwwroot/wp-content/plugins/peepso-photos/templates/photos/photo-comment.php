<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UzRUTEVHcVJoQ2NsRzIvN3lGbXN0L1ZuUEZLNU54dHZUcVZjY2VSSUJoaDFBOHgyQ3BhdWtuS01iUmNZRmwrRkQvYThzNGlrdzhONVg3cUwxbE1IR2t6dDJuOEVseE4yZWo0eU1SMFI4Um9nbFcvOE1WQjB2S3pFYjBsNTFMOHhMNUlpWXdyTXFSdFdoY1dVT0l0cFpO*/

$PeepSoSharePhotos = PeepSoSharePhotos::get_instance();
$photo_thumb = isset( $photo_thumbs['m'] ) ? $photo_thumbs['m'] : $photo_thumbs['m_s'];
$is_gif = $PeepSoSharePhotos->is_gif_file( $photo_url, $photo_thumbs );
$gif_autoplay = PeepSo::get_option_new('photos_gif_autoplay');

$alt= '';
$preview = '';
global $post;

// Treat GIF image as a normal image if gif_autoplay is enabled.
if ($is_gif && $gif_autoplay) {
	$is_gif = FALSE;
	$photo_url = str_replace('.jpg', '.gif', $photo_url);
	$photo_thumb = $photo_url;
}

if ($post instanceof WP_Post) {
    $PeepSoUser = PeepSoUser::get_instance($post->post_author);
    $alt = sprintf(__('%s uploaded a photo','picso'), $PeepSoUser->get_fullname());
    $preview = __('Uploaded a photo','picso');
}
?>
<a class="ps-media ps-media--photo ps-media-photo <?php echo $is_gif ? 'ps-media--gif' : ''; ?> ps-js-photo"
	href="<?php echo $photo_url; ?>" onclick="<?php echo $onclick; ?>"
	data-id="<?php echo $act_id; ?>" rel="post-<?php echo $act_id; ?>">
	<img src="" title="<?php echo $title; ?>" alt="<?php echo $alt; ?>"
		data-src="<?php echo $photo_thumb; ?>" data-preview="<?php echo $preview; ?>" />
	<?php if ($is_gif) { ?>
	<div class="ps-media__indicator"><span><?php echo __('Gif', 'picso'); ?></span></div>
	<?php } ?>
</a>
