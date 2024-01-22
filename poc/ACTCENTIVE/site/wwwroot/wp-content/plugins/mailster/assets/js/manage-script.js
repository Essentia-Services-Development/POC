mailster = (function (mailster, $, window, document) {
	'use strict';

	mailster.$.document.on('change', '.list-toggle', function () {
		$(this)
			.parent()
			.parent()
			.parent()
			.find('ul input')
			.prop('checked', $(this).prop('checked'));
	});

	return mailster;
})(mailster || {}, jQuery, window, document);
