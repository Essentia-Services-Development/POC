jQuery(function ($) {

	var $yesno = $('input[type=checkbox]');

	$yesno.on('click', function () {

		var clicked_id = this.id;

		var do_action = false;

		var replace_from = '_onsite_';
		var replace_to = '_email_';
		var replace_state = false;

		if (!this.checked && clicked_id.indexOf('_onsite_') >= 0) {
			do_action = true;
		}

		if (this.checked && clicked_id.indexOf('_email_') >= 0) {
			do_action = true;
			replace_from = '_email_';
			replace_to = '_onsite_';
			replace_state = true;
		}

		if (do_action) {
			var partner_id = '#' + clicked_id.replace(replace_from, replace_to);
			$(partner_id).prop('checked', replace_state);
		}

		//var $field = $allowNonSSL.closest( '.form-group' );
		//this.checked ? $field.show() : $field.hide();
	});

	$yesno.triggerHandler('click');


	var $preview = $('input[name=notification_previews]'),
		$previewLength = $('select[name=notification_preview_length]'),
		$previewEllipsis = $('input[name=notification_preview_ellipsis]'),
		$previewFields = $previewLength.add($previewEllipsis).closest('.form-group');

	$preview.on('click', function () {
		this.checked ? $previewFields.show() : $previewFields.hide();
	});
	$preview.triggerHandler('click');

	// Click-to-copy.
	$('.ps-js-copy-trigger').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();

		var $button = $(this),
			sourceText = $('.ps-js-copy-source').text();

		peepso.util.copyToClipboard(sourceText);
		$button.html($button.data('copy-success'));
	});
});

