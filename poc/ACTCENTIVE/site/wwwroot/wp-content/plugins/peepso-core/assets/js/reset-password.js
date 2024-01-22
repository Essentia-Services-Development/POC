(function($) {
	function validate(ct) {
		var $ct = $(ct),
			$input = $ct.find('input[type=password]'),
			name,
			value,
			url,
			req;

		if (!$input.length) {
			return;
		}

		name = $input.eq(0).attr('name') || '';
		value = peepso.observer.applyFilters('profile_field_save', $input.val(), $input);

		// match name with the names used in register fields
		if (name === 'pass1') {
			name = 'password';
		} else if (name === 'pass2') {
			name = 'password2';
		}

		// validate core fields
		if (['password', 'password2'].indexOf(name) >= 0) {
			url = peepsodata.ajaxurl_legacy + 'profilefieldsajax.validate_register';
			req = {};
			req['name'] = name;
			req[name] = value;

			// verify password
			if (name === 'password2') {
				$input = $input.closest('form').find('[name=pass1]');
				req['password'] = $input.val();
			}
		}

		return $.ajax({
			url: url,
			type: 'post',
			dataType: 'json',
			data: req
		}).done(function(json) {
			var $err = $ct.find('.ps-form__error'),
				errors;

			if (json.errors && json.errors.length) {
				$err.empty();
				errors = json.errors;
				_.each(errors, function(error) {
					$err.append('<div class="ps-form__error-item">' + error + '</div>');
				});
				$err.show();
			} else {
				$err.hide();
			}
		});
	}

	function doSubmit(form) {
		var $form = $(form),
			$fields = $form.find('.ps-form__field'),
			$submit = $form.find('button[type=submit]'),
			deferreds = [];
		errors = [];

		// prevent repeated click
		if ($form.data('ps-submitting')) {
			return;
		}

		$form.data('ps-submitting', true);

		// validate all fields
		$fields.each(function() {
			var xhr = validate(this);
			if (xhr) {
				deferreds.push(xhr);
			}
		});

		// submit when all validation done
		$submit.find('img').show();
		$.when.apply($, deferreds).done(function() {
			var json, i;

			$submit.find('img').hide();

			// prevent submit if errors detected
			for (i in arguments) {
				json = arguments[i][0];
				if (json.errors) {
					$form.removeData('ps-submitting');
					return;
				}
			}

			// submit form
			$form.off('submit.ps-resetpassword');
			setTimeout(function() {
				$submit.click();
			}, 100);
		});
	}

	var $form = $('#recoverpasswordform');

	$form
		// on change input text
		.on(
			'input',
			'input[type=password]',
			_.debounce(function(e) {
				validate($(e.target).closest('.ps-form__field'));
			}, 500)
		)
		// on form submit
		.on('submit.ps-resetpassword', function(e) {
			e.preventDefault();
			e.stopPropagation();
			doSubmit(this);
			return false;
		});
})(jQuery);
