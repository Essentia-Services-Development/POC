jQuery(function ($) {
	// Toggle WPJM Profile Integration.
	$('input[name=wpjm_enable]')
		.on('click', function () {
			let $field = $(this).closest('.form-group');
			let $children = $field.nextAll();

			this.checked ? $children.show() : $children.hide();
		})
		.triggerHandler('click');

	// Toggle WPJM Activity Stream Integration.
	$('input[name=wpjm_stream_enable]')
		.on('click', function () {
			let $field = $(this).closest('.form-group');
			let $children = $field.nextAll();

			this.checked ? $children.show() : $children.hide();
		})
		.triggerHandler('click');
});
