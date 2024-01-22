jQuery(function ($) {
	var $enable = $('input[name=limitusers_roles_show]'),
		$fields = $enable.closest('.form-group').nextAll('.form-group');

	$enable.on('click', function () {
		this.checked ? $fields.show() : $fields.hide();
	});

	$enable.triggerHandler('click');
});
