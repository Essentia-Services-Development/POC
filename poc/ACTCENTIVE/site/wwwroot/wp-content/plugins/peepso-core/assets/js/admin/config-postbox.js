// Handle toggle link preview.
jQuery(function ($) {
	var $enable = $('input[name=allow_embed]'),
		$smallThumbnail = $('input[name=small_url_preview_thumbnail]'),
		$allowNonSSL = $('input[name=allow_non_ssl_embed]'),
		$preferImg = $('input[name=prefer_img_embeds]'),
		$refresh = $('select[name=refresh_embeds]'),
		$wrapper = $smallThumbnail
			.add($allowNonSSL)
			.add($preferImg)
			.add($refresh)
			.closest('.form-group');

	$enable
		.on('click', function () {
			this.checked ? $wrapper.show() : $wrapper.hide();
		})
		.triggerHandler('click');
});

// Handle toggle location.
jQuery(function ($) {
	$('input[name=location_enable]')
		.on('click', function () {
			var $key = $('input[name=location_gmap_api_key]'),
				$wrapper = $key.closest('.form-group');

			this.checked ? $wrapper.show() : $wrapper.hide();
		})
		.triggerHandler('click');
});

// Handle toggle allow non-alphanumeric hashtags.
jQuery(function ($) {
	var $enable = $('input[name=hashtags_enable]'),
		$everything = $('input[name=hashtags_everything]'),
		$min = $('select[name=hashtags_min_length]'),
		$max = $('select[name=hashtags_max_length]'),
		$letter = $('input[name=hashtags_must_start_with_letter]');

	$enable.on('click', function () {
		var $field = $(this).closest('.form-group'),
			$childFields = $field.nextAll('.form-group');

		if (this.checked) {
			$childFields.show();
			$everything.triggerHandler('click');
		} else {
			$childFields.hide();
		}
	});

	$everything.on('click', function () {
		var $minField = $min.closest('.form-group'),
			$maxField = $max.closest('.form-group'),
			$letterField = $letter.closest('.form-group'),
			$fields = $minField.add($maxField).add($letterField);

		if (this.checked) {
			$fields.hide();
		} else {
			$fields.show();
		}
	});

	$enable.triggerHandler('click');
});

// Handle toggle post backgrounds.
jQuery(function ($) {
	$('input[name=post_backgrounds_enable]')
		.on('click', function () {
			var $field = $(this).closest('.form-group'),
				$childFields = $field.nextAll('.form-group'),
				$desc = $('.ps-js-link-manage-backgrounds');

			if (this.checked) {
				$childFields.show();
				$desc.show();
			} else {
				$childFields.hide();
				$desc.hide();
			}
		})
		.triggerHandler('click');
});
