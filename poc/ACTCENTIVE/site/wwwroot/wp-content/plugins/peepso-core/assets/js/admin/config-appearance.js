jQuery(function ($) {
	var $dateFormatNoYear = $('select[name=date_format_no_year]'),
		$dateFormatNoYearCustom = $('input[name=date_format_no_year_custom]').closest(
			'.form-group'
		);

	$dateFormatNoYear.on('change', function () {
		'custom' == this.value ? $dateFormatNoYearCustom.show() : $dateFormatNoYearCustom.hide();
	});
	$dateFormatNoYear.triggerHandler('change');

	// Toggle name-base avatar configs.
	var $nameBasedAvatars = $('input[name=avatars_name_based]'),
		$nameBasedBackground = $('select[name=avatars_name_based_background_color]'),
		$nameBasedGrayscale = $('input[name=avatars_name_based_background_grayscale]'),
		$nameBasedFont = $('select[name=avatars_name_based_font_color]'),
		$nameBasedPreview = $('#field_avatars_name_based_preview').children('.form-field');

	$nameBasedAvatars.on('click', function () {
		var $fields = $nameBasedBackground
			.add($nameBasedGrayscale)
			.add($nameBasedFont)
			.add($nameBasedPreview)
			.closest('.form-group');

		if (this.checked) {
			$fields.show();
			nameBasedPreview();
		} else {
			$fields.hide();
		}
	});
	$nameBasedAvatars.triggerHandler('click');

	$nameBasedBackground.on('change', nameBasedPreview);
	$nameBasedGrayscale.on('click', nameBasedPreview);
	$nameBasedFont.on('change', nameBasedPreview);

	var prevConfigHash, xhr;
	function nameBasedPreview() {
		var config = {
			bg: $nameBasedBackground.val(),
			grayscale: $nameBasedGrayscale[0].checked ? 1 : '',
			font: $nameBasedFont.val()
		};

		var configHash = JSON.stringify(config);

		if (prevConfigHash !== configHash) {
			prevConfigHash = configHash;

			// Show loading indicator.
			var $loading = $('<div>Loading...</div>').css('paddingTop', 4);
			if (window.peepsodata && peepsodata.loading_gif) {
				$loading.html('<img src="' + peepsodata.loading_gif + '" />');
			}

			$nameBasedPreview.html($loading);

			xhr && xhr.abort();
			xhr = $.ajax({
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: 'peepso_name_based_avatars_preview',
					background_color: config.bg,
					background_grayscale: config.grayscale,
					font_color: config.font
				}
			});
			xhr.then(function (json) {
				var images = json && json.url;
				if (images instanceof Array) {
					$nameBasedPreview.empty();
					images.forEach(function (url) {
						var $img = $('<img src="' + url + '" width="50" />').css('marginRight', 10);
						$nameBasedPreview.append($img);
					});
				}
			});
			xhr.always(function () {
				xhr = undefined;
				$loading.remove();
			});
		}
	}
});
