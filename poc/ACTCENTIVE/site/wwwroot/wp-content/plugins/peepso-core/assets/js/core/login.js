/** @module login */

import $ from 'jquery';
import observer from './observer';
import { ajax, dialog, hooks } from 'peepso';
import peepsodata from 'peepsodata';

const LOGIN_WITH_EMAIL = +peepsodata.login_with_email;

/**
 * Initialize login form.
 *
 * @function
 * @param {HTMLFormElement|JQuery} form
 */
const initForm = function (form) {
	let $forms = $(form);

	// Login with email mode.
	if (LOGIN_WITH_EMAIL) {
		let evInput = 'input.login-with-email';

		$forms
			.find('[name=username]')
			.off(evInput)
			.on(evInput, function () {
				let username = this.value.trim(),
					$form = $(this).closest('form'),
					$submit = $form.find('[type=submit]'),
					$notice = $form.find('.ps-js-email-notice');

				// Check username against a basic email pattern.
				if (username.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
					$submit.removeAttr('disabled');
					$notice.hide();
				} else {
					$submit.attr('disabled', 'disabled');
					username ? $notice.show() : $notice.hide();
				}
			})
			.triggerHandler(evInput);
	}

	$forms.off('submit');
	$forms.on('submit', function (e) {
		e.preventDefault();
		e.stopPropagation();
		presubmit(e.target);
	});

	// Initalize password preview if available.
	hooks.doAction('init_password_preview');

	// Initialize recaptcha if available.
	if (peepso.recaptcha) {
		peepso.recaptcha.init();
	}
};

/**
 * Check if OTP is enabled by a user.
 *
 * @param {string} username
 * @returns {Promise}
 */
const tfaCheck = function (username) {
	return new Promise(resolve => {
		let tfaCodeNeeded = false;

		$.ajax({
			url: peepsodata.ajaxurl,
			type: 'POST',
			data: {
				action: 'simbatfa-init-otp',
				user: username
			},
			dataType: 'json',
			success(json) {
				try {
					if (json.status === true) {
						tfaCodeNeeded = true;
					}
				} catch (e) {}
			},
			complete() {
				resolve(tfaCodeNeeded);
			}
		});
	});
};

/**
 * Validation before submitting login form.
 *
 * @function
 * @param {*} form
 */
const presubmit = function (form) {
	let $form = $(form),
		$username = $form.find('[name=username]'),
		$password = $form.find('[name=password]'),
		$tfa = $form.find('[name=two_factor_code]'),
		$submit = $form.find('[type=submit]'),
		username = $username.val().trim(),
		password = $password.val().trim();

	if (!username && !password) {
		return false;
	}

	$submit.attr('disabled', true);
	$submit.find('img').show();

	if (!$tfa.length) {
		submit(form);
		return false;
	}

	tfaCheck(username).then(tfaCodeNeeded => {
		if (tfaCodeNeeded && !$tfa.is(':visible')) {
			$username.closest('.ps-js-username-field').hide();
			$password.closest('.ps-js-password-field').hide();
			$tfa.closest('.ps-js-tfa-field').show();
			$submit.removeAttr('disabled');
			$submit.find('img').hide();
		} else {
			submit(form);
		}
	});

	return false;
};

/**
 * Submit login form.
 *
 * @function
 * @param {HTMLFormElement} form
 */
const submit = function (form) {
	let $form = $(form),
		$submit = $form.find('[type=submit]'),
		username = $form.find('[name=username]').val(),
		password = $form.find('[name=password]').val(),
		security = $form.find('[name=security]').val(),
		remember = $form.find('[name=remember]').is(':checked') ? 1 : 0,
		redirect = $form.find('[name=redirect_to]').val(),
		$extras = $form.find('[name][data-ps-extra]'),
		data;

	// Include recaptcha response field if present.
	let $recaptcha = $form.find('[name=g-recaptcha-response]');
	if ($form.length) {
		$extras = $extras.add($recaptcha);
	}

	// Prepare login parameters.
	data = { username, password, security, remember };
	if ($extras.length) {
		$extras.each(function () {
			data[this.name] = this.value;
		});
	}

	ajax.post('auth.login', data, -1)
		.done(json => {
			if (json.success) {
				observer.doAction('login.success');
				if (redirect && window.location.href !== redirect) {
					window.location = redirect;
				} else {
					window.location.reload(true);
				}
			} else if (json.errors) {
				try {
					let title = (json.data && json.data.dialog_title) || '';
					pswindow.hide();
					if (false === pswindow.acknowledge(json.errors, title)) {
						form.find('.errlogin').html(json.errors[0]).css('display', 'block');
					}

					// Show pending activation message if needed.
					let codes = (json.data && json.data.error_code) || [];
					if (codes.indexOf('pending_approval') > -1) {
						$('.ps-js-register-activation').css('display', 'inline-block');
					}
				} catch (e) {}
				observer.doAction('login.error', form, json.errors[0]);

				// Reset recaptcha on failed login.
				if ($recaptcha.length) {
					peepso.recaptcha.reset(form);
				}
			}
		})
		.fail((xhr, status, error) => {
			if (error) {
				dialog(error).error();
			}
			observer.doAction('login.error', form, error);

			// Reset recaptcha on failed login.
			if ($recaptcha.length) {
				peepso.recaptcha.reset(form);
			}
		})
		.always(() => {
			$submit.removeAttr('disabled');
			$submit.find('img').hide();
		});

	return false;
};

observer.addAction(
	'login.error',
	form => {
		let $form = $(form),
			$username = $form.find('[name=username]'),
			$password = $form.find('[name=password]'),
			$tfa = $form.find('[name=two_factor_code]');

		// Clear out password field on failed login attempt.
		$password.val('');

		if ($tfa.length) {
			$tfa.val('');
			$tfa.closest('.ps-js-tfa-field').hide();
			$username.closest('.ps-js-username-field').show();
			$password.closest('.ps-js-password-field').show();
		}
	},
	10,
	1
);

export default {
	initForm: initForm,
	submit: presubmit
};

$(function () {
	/**
	 * Display the login dialog if a session_timeout is returned and authRequired is set to true
	 *
	 * @param {Event} e
	 * @param {boolean} reload
	 */
	$(window).on('peepso_auth_required', function (e, reload) {
		$('.login-area input').attr('disabled', true);
		// Hide any open pswindows
		if (pswindow.is_visible) {
			pswindow.hide();
		}
		// TODO: string needs to be translatable
		pswindow.show(peepsodata.login_dialog_title, peepsodata.login_dialog);
		$(document).trigger('peepso_login_shown');
		$('#ps-window').one('pswindow.hidden', function () {
			$('.login-area input').removeAttr('disabled');
		});
		if (reload) {
			$('input[name=redirect_to]').val(window.location);
		}
	});
});
