jQuery(function ($) {
	let $pinGroupOnly = $('input[name=groups_pin_group_only]');

	// Toggle file upload configs.
	$pinGroupOnly.on('click', function () {
		let $fields = $('input[name=groups_pin_group_only_no_pinned_style]').closest('.form-group');
		this.checked ? $fields.show() : $fields.hide();
	});

	$pinGroupOnly.triggerHandler('click');
});
