<div class="ps-comments__input-addon ps-comments__input-addon--photo ps-js-addon-photo">
	<img class="ps-js-img" alt="photo"
		src="<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UzRUTEVHcVJoQ2NsRzIvN3lGbXN0L1J5VWxSNkpoN21lNU1xRVlVSGpuK0l6MnM4d3VEWlRJdis2ZmtOeXNhYzRJMXVEZm1RQXl3d28rc2JoVEZxbWJYY3pBTS9oMlJ1SHQxbEdVbGZwaWV2T2pQRFFWNmNoS2R4bzlrZTlhdHV0cUxCVTJLWkFUVC9mVkFGNFJ0U0h1*/ echo isset($thumb) ? $thumb : ''; ?>"
		data-id="<?php echo isset($id) ? $id : ''; ?>" />

	<div class="ps-loading ps-js-loading">
		<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" />
	</div>

	<div class="ps-comments__input-addon-remove ps-js-remove">
		<?php wp_nonce_field('remove-temp-files', '_wpnonce_remove_temp_comment_photos'); ?>
		<i class="gcis gci-times"></i>
	</div>
</div>
