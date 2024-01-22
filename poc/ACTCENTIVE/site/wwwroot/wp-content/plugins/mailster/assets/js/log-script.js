mailster = (function (mailster, $, window, document) {
	'use strict';

	var nav = $('#previewtabs'),
		tabs = $('.tab');

	nav.on('click', 'a.nav-tab', function () {
		nav.find('a').removeClass('nav-tab-active');
		tabs.hide();
		var hash = $(this).addClass('nav-tab-active').attr('href');
		$('#deliverymethod').val(hash.substr(1));
		$('#tab-' + hash.substr(1)).show();
		return false;
	});

	return mailster;
})(mailster || {}, jQuery, window, document);
