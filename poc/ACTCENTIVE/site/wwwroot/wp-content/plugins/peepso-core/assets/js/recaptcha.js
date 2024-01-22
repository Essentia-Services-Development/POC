(function ($, factory) {
	peepso.recaptcha = factory($);

	// Fail-safe initialize on document load.
	var timer = setTimeout(function () {
		peepso.recaptcha.init();
	}, 3000);
	$(function () {
		clearTimeout(timer);
		peepso.recaptcha.init();
	});
})(jQuery, function ($) {
	var config = window.peepsodata_recaptcha || {};
	var url = config.host + '/recaptcha/api.js';

	return {
		/**
		 * Initialize peepso recaptcha.
		 */
		init: function () {
			var initialized = 'ps-js-initialized',
				$btns = $('.ps-js-recaptcha');

			// Filter out already-initialized buttons.
			$btns = $btns.not('.' + initialized);

			if ($btns.length) {
				this._loadLibrary().done(
					$.proxy(function () {
						$btns.each(
							$.proxy(function (index, btn) {
								var $btn = $(btn).addClass(initialized);

								// #5357 Keep disabled state on the register form.
								var $form = $btn.closest('form');
								if (!$form.filter('[class*="register-main"]').length) {
									$btn.removeAttr('disabled');
								}

								this._initOne($btn);
							}, this)
						);
					}, this)
				);
			}
		},

		/**
		 * Reset peepso recaptcha.
		 *
		 * @param {HTMLFormElement}
		 */
		reset: function (form) {
			var evtName = 'click.ps-recaptcha',
				$form = $(form),
				$btn = $form.find('.ps-js-recaptcha'),
				recaptchaId = $form.data('ps-recaptcha-id');

			if ($btn.length && 'undefined' !== typeof recaptchaId) {
				grecaptcha.reset(recaptchaId);
				$form.find('[name=g-recaptcha-response]').val('');

				// Intercept button onclick handler.
				$btn.off(evtName).on(evtName, function (e) {
					if (!grecaptcha.getResponse(recaptchaId)) {
						e.preventDefault();
						e.stopPropagation();
						grecaptcha.execute(recaptchaId);
					}
				});
			}
		},

		/**
		 * Initialize a peepso recaptcha tag.
		 * @param {jQuery} $btn
		 */
		_initOne: function ($btn) {
			var evtName = 'click.ps-recaptcha',
				$form = $btn.closest('form'),
				$div,
				recaptchaId;

			if ($form.length) {
				// Intercept button onclick handler.
				$btn.on(evtName, function (e) {
					if (!grecaptcha.getResponse(recaptchaId)) {
						e.preventDefault();
						e.stopPropagation();
						grecaptcha.execute(recaptchaId);
					}
				});

				// Initialize recaptcha.
				$div = $('<div />').insertBefore($btn);
				recaptchaId = grecaptcha.render($div[0], {
					sitekey: config.key,
					size: 'invisible',
					callback: function () {
						$btn.off(evtName);
						$form.submit();
					}
				});

				// Save recaptcha ID.
				$form.data('ps-recaptcha-id', recaptchaId);
			}
		},

		/**
		 * Load Google Recaptcha API if it is not loaded yet.
		 * @return {jQuery.Deferred}
		 */
		_loadLibrary: function () {
			return $.Deferred(function (defer) {
				var script, timer, count;

				// Check if the script is already loaded.
				$('script').each(function (index, elem) {
					var src = $(elem).attr('src');
					if (src && src.match(url)) {
						script = elem;
						return false;
					}
				});

				// Load script if it is not loaded yet.
				if (!script) {
					script = document.createElement('script');
					script.type = 'text/javascript';
					script.src =
						config.host +
						'/recaptcha/api.js' +
						'?onload=peepsoRecaptchaCallback&render=explicit';

					window.peepsoRecaptchaCallback = function () {
						defer.resolve();
						delete window.peepsoRecaptchaCallback;
					};

					document.body.appendChild(script);

					// Or just wait for the `grecaptcha` object to be available if it is.
				} else {
					count = 0;
					timer = setInterval(function () {
						count++;

						if (window.grecaptcha) {
							clearInterval(timer);
							defer.resolve();

							// Wait for 60s (120 x 500ms) just in case on slow network.
						} else if (count > 120) {
							clearInterval(timer);
						}
					}, 500);
				}
			});
		}
	};
});
