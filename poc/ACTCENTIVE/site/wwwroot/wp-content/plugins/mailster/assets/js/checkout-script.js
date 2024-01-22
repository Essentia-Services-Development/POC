mailster = (function (mailster, $, window, document) {
	'use strict';

	mailster.$.document.on('click', '.mailster-checkout', function (e) {
		var _this = $(this),
			args = _this.data();

		_this.addClass('is-loading');
		$.getScript('https://checkout.freemius.com/checkout.min.js', function () {
			_this.removeClass('is-loading');
			if (typeof FS === 'object') {
				initCheckout(args);
			} else {
				alert('Error: Not able to load checkout!');
			}
		});
		e.preventDefault();
	});

	function initCheckout(args) {
		var defaults = {
			plugin_id: '12184',
			licenses: 1,
			_disable_licenses_selector: true,
			hide_licenses: true,
			hide_license_key: true,
			hide_coupon: true,
		};
		var handler = FS.Checkout.configure($.extend(defaults, args));

		handler.open({
			purchaseCompleted: function (response) {
				console.warn(response);
			},
			cancel: function (response) {
				console.warn(response);
			},
			success: function (response) {
				console.warn(response);
			},
		});
	}

	return mailster;
})(mailster || {}, jQuery, window, document);
