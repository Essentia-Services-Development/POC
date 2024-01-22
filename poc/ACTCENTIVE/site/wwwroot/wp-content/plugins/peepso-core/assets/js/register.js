(function ($) {
	/**
	 * Flag if user needs to verify entered email address information.
	 *
	 * @type {number}
	 */
	var VERIFY_EMAIL = peepsodata.register && +peepsodata.register.confirm_email_field;

	function validate(ct) {
		var $ct = $(ct),
			$input = $ct.find(
				'input[type=text],input[type=email],input[type=password],input[type=checkbox],input[type=radio],textarea,select'
			),
			id,
			name,
			type,
			value,
			core,
			url,
			req;

		if (!$input.length) {
			return;
		}

		id = $input.eq(0).data('id');
		name = $input.eq(0).attr('name') || '';
		type = ($input.eq(0).attr('type') || '').toLowerCase();

		if (type === 'checkbox') {
			value = $input.filter(':checked').map(function () {
				return this.value;
			});
			value = $.makeArray(value);
			value = JSON.stringify(value);
		} else if (type === 'radio') {
			value = $input.filter(':checked').val();
		} else if ($input.hasClass('datepicker')) {
			value = $input.data('value');
		} else {
			value = peepso.observer.applyFilters('profile_field_save', $input.val(), $input);
		}

		// validate core fields
		if (
			[
				'username',
				'email',
				'email_verify',
				'password',
				'password2',
				'terms',
				'privacy',
				'g-recaptcha-response'
			].indexOf(name) >= 0
		) {
			core = true;
			url = peepsodata.ajaxurl_legacy + 'profilefieldsajax.validate_register';
			req = {};
			req['name'] = name;
			req[name] = value;

			// verify email
			if (name === 'email_verify') {
				$input = $input.closest('form.ps-js-form-register').find('[name=email]');
				req['email'] = $input.val();
			}
			// verify password
			else if (name === 'password2') {
				$input = $input.closest('form.ps-js-form-register').find('[name=password]');
				req['password'] = $input.val();
			}

			// validate extra fields
		} else {
			core = false;
			url = peepsodata.ajaxurl_legacy + 'profilefieldsajax.validate';
			req = {
				user_id: peepsodata.currentuserid,
				view_user_id: peepsodata.userid,
				id: id,
				value: value
			};
		}

		return $.Deferred(function (defer) {
			$.ajax({
				url: url,
				type: 'post',
				dataType: 'json',
				data: req
			}).always(function (json) {
				var $err = $ct.find('.ps-form__error'),
					errors;

				// Handle non-200 response code.
				if (json.responseJSON) {
					json = json.responseJSON;
				}

				if (json.errors && json.errors.length) {
					$err.empty();
					errors = core ? json.errors : json.errors[0];
					_.each(errors, function (error) {
						$err.append('<div class="ps-form__error-item">' + error + '</div>');
					});
					$err.show();
				} else {
					$err.hide();
				}

				defer.resolve.apply(null, arguments);
			});
		});
	}

	function doSubmit(form) {
		var $form = $(form),
			$fields = $form.find('.ps-form__field'),
			$dps = $fields.find('input.datepicker'),
			$submit = $form.find('button[type=submit]'),
			deferreds = [];

		// prevent repeated click
		if ($form.data('ps-submitting')) {
			return;
		}

		$form.data('ps-submitting', true);
		$submit.attr('disabled', 'disabled');

		// validate all fields
		$fields.each(function () {
			var xhr = validate(this);
			if (xhr) {
				deferreds.push(xhr);
			}
		});

		// submit when all validation done
		$submit.find('img').show();
		$.when.apply($, deferreds).done(function () {
			var json, i;

			$submit.find('img').hide();

			// prevent submit if errors detected
			for (i in arguments) {
				json = arguments[i][0];
				if (json.errors) {
					$form.removeData('ps-submitting');
					$submit.removeAttr('disabled');
					return;
				}
			}

			// convert datepicker values before submitting
			if ($dps.length) {
				$dps.each(function () {
					var $dp = $(this),
						val = $dp.data('value');
					if (val) {
						$hidden = $('<input type="hidden" name="' + $dp.attr('name') + '" />');
						$dp.removeAttr('name');
						$hidden.insertAfter($dp);
						$hidden.val(val);
					}
				});
			}

			// let plugins convert field input values before submitting
			if ($fields.length) {
				$fields.each(function () {
					$input = $(this).find(
						'input[type=text],input[type=email],input[type=password],input[type=checkbox],input[type=radio],textarea,select'
					);
					peepso.observer.doAction('profile_field_save_register', $input);
				});
			}

			// submit form
			setTimeout(function () {
				$form.off('submit.ps-register');
				$submit.removeAttr('disabled');
				$submit.trigger('click');
				$submit.attr('disabled', 'disabled');
			}, 100);
		});
	}

	var $form = $(document).find('form.ps-js-form-register');

	$form
		// on change input text
		.on(
			'input',
			'input[type=text]:not(.ps-js-field-location),input[type=email],input[type=password],textarea',
			_.debounce(function (e) {
				validate($(e.target).closest('.ps-form__field'));
			}, 500)
		)
		// on change checkbox/radio input
		.on(
			'click',
			'input[type=checkbox],input[type=radio]',
			_.debounce(function (e) {
				validate($(e.target).closest('.ps-form__field'));
			}, 100)
		)
		// on change selectbox
		.on(
			'change',
			'select',
			_.debounce(function (e) {
				validate($(e.target).closest('.ps-form__field'));
			}, 100)
		)
		// on form submit
		.on('submit.ps-register', function (e) {
			e.preventDefault();
			e.stopPropagation();
			doSubmit(this);
			return false;
		});

	var $username = $form.find('input[name=username]'),
		$email = $form.find('input[name=email]');

	if ('hidden' === $username.attr('type')) {
		// Copy value from email if username field is hidden.
		$email.on('input', function () {
			$username.val($email.val());
		});
	} else {
		// Remove invaid characters on the username field.
		$username.on('input', function () {
			var sanitized = this.value.replace(/[^a-z0-9-_\.@]/gi, '');
			if (this.value !== sanitized) {
				this.value = sanitized;
			}
		});
	}

	// disable submit button if terms & privacy checkboxes are not checked
	var $consents = $form.find('input[name=terms], input[name=privacy]'),
		$submit = $form.find('[type=submit]');

	if ($consents.length) {
		$consents
			.on('click.ps-register-consent', function (e) {
				var checked = [];
				$consents.each(function () {
					checked.push(!!this.checked);
				});
				if (checked.indexOf(false) >= 0) {
					e.stopPropagation();
					$submit.attr('disabled', 'disabled');
				} else {
					$submit.removeAttr('disabled');
				}
			})
			.triggerHandler('click.ps-register-consent');
	}

	// wait for the location element to be available
	$(function () {
		setTimeout(function () {
			$form.find('.ps-js-location-wrapper .ps-btn').on(
				'mousedown',
				_.debounce(function (e) {
					validate($(e.target).closest('.ps-form__field'));
				}, 100)
			);
		}, 1000);
	});
})(jQuery);
