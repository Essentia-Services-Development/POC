(function ($) {
	var $btns = $('.gc-admin__tabs a').click(function () {
		var $el = $('.' + this.id).fadeIn(450);
		$('.gc-admin__tab').not($el).hide();

		$btns.removeClass('active');
		$(this).addClass('active');

		var url = location.href;
		url = url.replace(window.location.origin, '');
		url = url.split('#')[0] + '#' + $(this).attr('id').replace('gc-tab-', '');
		window.history.replaceState(null, null, url);
	});

	if (location.href.split('#').length > 1) {
		var id = 'gc-tab-' + location.href.split('#')[1];

		$btns.removeClass('active');
		$('#' + id).addClass('active');
		var $el = $('.' + id).fadeIn(450);
		$('.gc-admin__tab').not($el).hide();
	}

	var dropdownToggle = '.gc-dropdown__toggle';
	var dropdownBox = '.gc-dropdown__box';
	var dropdownIcons = '.gc-dropdown__icons';
	var dropdownIcon = '.gc-dropdown__icons > a';
	var dropdownSelect = '.gc-dropdown__icons-select';
	var currentValue = $(dropdownSelect).val();
	var currentIcon = $(dropdownIcons)
		.find('#' + currentValue)
		.attr('class');

	$(dropdownToggle)
		.children('a')
		.click(function () {
			$(this).parent().siblings(dropdownBox).fadeToggle();
		});

	$(document).click(function (e) {
		e.stopPropagation();
		var container = $('.gc-dropdown');

		//check if the clicked area is dropDown or not
		if (container.has(e.target).length === 0) {
			$(dropdownBox).hide();
		}
	});

	$(dropdownToggle).children('a').removeClass().addClass(currentIcon);

	$(dropdownIcon).click(function () {
		var newClass = $(this).attr('class');
		var parent = $(this).parent().parent().siblings(dropdownToggle);
		var iconID = $(this).attr('id');

		$(parent).children('a').removeClass().addClass(newClass);
		$(dropdownSelect).val(iconID);
		$(dropdownBox).hide();
	});

	// Filter-out weird characters on the license key.
	$('input[name="gecko_options[gecko_license]"]').on('input', function () {
		var filtered = this.value.replace(/[^a-zA-Z0-9]/g, '');
		if (this.value !== filtered) {
			this.value = filtered;
		}
	});

	// Toggle selected custom presets.
	$('select[name="gecko_options[opt_user_preset]"]').on('change', function () {
		let $presets = $('input[name^="gecko_options[opt_user_preset_list]"]');
		let $container = $presets.eq(0).closest('.gca-dash__option');
		this.value == 2 ? $container.show(): $container.hide();
	}).triggerHandler('change');
})(jQuery);
