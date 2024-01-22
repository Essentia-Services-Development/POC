jQuery(function ($) {

	$(document.body).on('click', '#pmp_integration_enabled', function () {
		toggle_config();
	});

	function toggle_config() {
		var $elements_to_hide = $('#field_pmp_integration_enabled').nextAll();

		if ($('#pmp_integration_enabled').is(':checked')) {
			$elements_to_hide.show();
		} else {
			$elements_to_hide.hide();
		}
	}

	toggle_config();
});