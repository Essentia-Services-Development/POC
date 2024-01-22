jQuery(function ($) {
	var $default = $('select[name=stream_id_default]'),
		$sticky = $('input[name=stream_id_sticky]'),
		$following = $('input[name=peepso_navigation_following]'),
		$saved = $('input[name=peepso_navigation_saved]'),
		$notice = $('#field_extra_activity_items');

	$default.on('change', function () {
		var $fields = $following.add($saved).closest('.form-group');

		if (this.value === 'core_community' && !$sticky[0].checked) {
			$notice.hide();
			$fields.show();
		} else {
			$fields.hide();
			$notice.show();
		}
	});

	$sticky.on('click', function () {
		var $fields = $following.add($saved).closest('.form-group');

		if (!this.checked && $default.val() === 'core_community') {
			$notice.hide();
			$fields.show();
		} else {
			$fields.hide();
			$notice.show();
		}
	});

	$default.triggerHandler('change');
});
