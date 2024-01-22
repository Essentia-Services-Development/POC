jQuery(function ($) {
	let $enable = $('input[name=fileuploads_enable]');

	// Toggle file upload configs.
	$enable.on('click', function () {
		let $field = $(this).closest('.form-group');
		let $box = $field.closest('.postbox');
		let $targetFields = $field.nextAll();
		let $targetBoxes = $box.closest('#peepso').find('.postbox').not($box);

		if (this.checked) {
			$targetFields.show();
			$targetBoxes.show();
		} else {
			$targetFields.hide();
			$targetBoxes.hide();
		}
	});

	$enable.triggerHandler('click');
});
