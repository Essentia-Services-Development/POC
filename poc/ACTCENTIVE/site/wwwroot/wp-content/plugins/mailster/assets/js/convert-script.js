mailster = (function (mailster, $, window, document) {
	'use strict';

	var strings = [
		'Validating license',
		'Sending to Provider',
		'Checking for Updates',
		'Clean up',
	];

	$('.convert_form_wrap')
		.on('submit', '.convert_form', function () {
			var form = $(this),
				wrap = form.parent(),
				email = wrap.find('input.email').val(),
				license = wrap.find('input.license').val(),
				error;

			form.removeClass('has-error').prop('disabled', true);
			wrap.addClass('loading');

			info();

			mailster.util.ajax(
				'convert',
				{
					email: email,
					license: license,
				},
				function (response) {
					form.prop('disabled', false);
					wrap.removeClass('loading');

					if (response.success) {
						wrap.addClass('step-2').removeClass('step-1');

						$.each(response.data.data.texts, function (i, text) {
							$('.result').append('<li>' + text + '</li>');
						});

						$('.convert-plan').html(response.data.data.plan);
					} else {
						error = response.data.error;
						form.addClass('has-error')
							.find('.error-msg')
							.html(error);
					}
				},
				function (jqXHR, textStatus, errorThrown) {
					var response =
						mailster.util.trim(jqXHR.responseText) ||
						'Unknown error';
					form.addClass('has-error')
						.find('.error-msg')
						.html(response);
					form.prop('disabled', false);
					wrap.removeClass('loading');
				}
			);

			return false;
		})
		.removeClass('loading');

	function info() {
		var el = $('.convert-form-info');
		var i = 0;
		var l = strings.length;

		var t = setInterval(function () {
			el.html(strings[i]);
			i++;
			if (i > l) {
				i = 0;
				clearInterval(t);
			}
		}, 3000);
	}

	return mailster;
})(mailster || {}, jQuery, window, document);
