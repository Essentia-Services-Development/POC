jQuery(function ($) {
	var $enable = $('input[name=messages_chat_enable]'),
		$longPoll = $('input[name=messages_get_chats_longpoll]'),
		$mode = $('select[name=messages_chat_restriction_mode]'),
		$disablePages = $('textarea[name=messages_chat_disable_on_pages]'),
		$enablePages = $('textarea[name=messages_chat_enable_on_pages]');

	$enable.on('change', function () {
		if (!this.checked) {
			$longPoll.closest('.form-group').hide();
			$mode.closest('.postbox').hide();
		} else {
			$longPoll.closest('.form-group').show();
			$mode.closest('.postbox').show();
			$mode.triggerHandler('change');
		}
	});

	// Toggle video configs.
	$mode.on('change', function () {
		var mode = +this.value,
			modeEnable = 1;

		if (mode === modeEnable) {
			$disablePages.closest('.form-group').hide();
			$enablePages.closest('.form-group').show();
		} else {
			$enablePages.closest('.form-group').hide();
			$disablePages.closest('.form-group').show();
		}
	});

	$enable.triggerHandler('change');
});
