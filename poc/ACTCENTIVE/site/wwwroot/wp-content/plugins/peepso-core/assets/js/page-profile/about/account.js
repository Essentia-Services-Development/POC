import $ from 'jquery';
import { ajax, dialog, hooks } from 'peepso';
import { profile as profileData } from 'peepsodata';

$(function () {
	let $verify = $('input[name=verify_password]');
	if (!$verify.length) {
		return;
	}

	let $form = $verify.closest('form'),
		$fields = $form.find('input[type=text], input[type=password]').not($verify),
		$save = $form.find('[type=submit]');

	// Save initial field values.
	$fields.each(function () {
		$(this).data('ps-value', this.value);
	});

	// Determine whether the form field values are changed.
	function isChanged() {
		let changed = false;
		$fields.each(function () {
			if (this.value !== $(this).data('ps-value')) {
				changed = true;
				return false;
			}
		});
		return changed;
	}

	// Enable/disable editing form field values.
	function toggleEditing() {
		let value = $verify.val().trim();
		if (value.length < 5) {
			$fields.attr('readonly', 'readonly');
			$save.attr('disabled', 'disabled');
		} else {
			$fields.removeAttr('readonly');
			if (!isChanged()) {
				$save.attr('disabled', 'disabled');
			} else {
				$save.removeAttr('disabled');
			}
		}
	}

	// Rmove invalid characters on username field.
	$fields.filter('input[name=user_nicename]').on('input', function () {
		var sanitized = this.value.replace(/[^a-z0-9-_\.@]/gi, '');
		if (this.value !== sanitized) {
			this.value = sanitized;
		}
	});

	// Handle form fields input event.
	$verify.on('input', toggleEditing);
	$fields.on('input', toggleEditing);

	// Toggle editing state on page load.
	toggleEditing();
});

/**
 * Profile deletion script.
 */
$(function () {
	let popup;

	$('.ps-js-profile-delete').on('click', e => {
		e.preventDefault();
		showDialog();
	});

	function showDialog() {
		if (!popup) {
			popup = dialog(profileData.template_profile_deletion, { destroyOnClose: false }).show();
			popup.$el.find('form').on('submit', e => e.preventDefault());
			popup.$el.find('.ps-js-cancel').on('click', () => popup.hide());
			popup.$el.find('.ps-js-submit').on('click', e => submit(e.currentTarget));
			hooks.doAction('init_password_preview');
		}

		popup.$el.find('#ps-js-profile-deletion-pass').val('');
		popup.$el.find('.ps-js-error').hide();
		popup.show();
	}

	function submit(button) {
		let $button = $(button).attr('disabled', 'disabled'),
			$loading = $button.find('.ps-js-loading').show(),
			$error = popup.$el.find('.ps-js-error').hide(),
			password = popup.$el.find('#ps-js-profile-deletion-pass').val();

		ajax.post('profile.delete_profile', { password })
			.then(json => {
				if (json.success) {
					if (json.data) {
						dialog(json.data.messages).show();
						setTimeout(() => (window.location = json.data.url), 3000);
					}
				} else if (json.errors) {
					$error.html(json.errors[0]).show();
				}
			})
			.always(() => {
				$loading.hide();
				$button.removeAttr('disabled');
			});
	}
});

/**
 * Data export request script.
 */
$(function () {
	let popup;

	$('.ps-js-export-data-request').on('click', e => {
		e.preventDefault();
		showDialog();
	});

	function showDialog() {
		if (!popup) {
			popup = dialog(profileData.template_export_data_request, {
				destroyOnClose: false
			}).show();

			popup.$el.find('.ps-js-cancel').on('click', () => popup.hide());
			popup.$el.find('.ps-js-submit').on('click', () => submit());
			popup.$el.find('form').on('submit', e => {
				e.preventDefault();
				submit();
			});
			hooks.doAction('init_password_preview');
		}

		popup.$el.find('#ps-js-export-data-request-pass').val('');
		popup.$el.find('.ps-js-error').hide();
		popup.show();
	}

	function submit() {
		let $button = popup.$el.find('.ps-js-submit').attr('disabled', 'disabled'),
			$loading = $button.find('.ps-js-loading').show(),
			$error = popup.$el.find('.ps-js-error').hide(),
			password = popup.$el.find('#ps-js-export-data-request-pass').val();

		ajax.post('profile.request_account_data', { password })
			.then(json => {
				if (json.success) {
					if (json.data) {
						let title = popup.title(),
							successPopup = dialog(json.data.messages, { title });

						popup.hide();
						successPopup.show();
						setTimeout(() => {
							window.location = json.data.url;
						}, 3000);
					}
				} else if (json.errors) {
					$error.html(json.errors[0]).show();
				}
			})
			.always(() => {
				$loading.hide();
				$button.removeAttr('disabled');
			});
	}
});

/**
 * Data export download script.
 */
$(function () {
	let popup;

	$('.ps-js-export-data-download').on('click', e => {
		e.preventDefault();
		showDialog();
	});

	function showDialog() {
		if (!popup) {
			popup = dialog(profileData.template_export_data_download, {
				destroyOnClose: false
			}).show();

			popup.$el.find('.ps-js-cancel').on('click', () => popup.hide());
			popup.$el.find('.ps-js-submit').on('click', () => submit());
			popup.$el.find('form').on('submit', e => {
				e.preventDefault();
				submit();
			});
			hooks.doAction('init_password_preview');
		}

		popup.$el.find('#ps-js-export-data-download-pass').val('');
		popup.$el.find('.ps-js-error').hide();
		popup.show();
	}

	function submit() {
		let $button = popup.$el.find('.ps-js-submit').attr('disabled', 'disabled'),
			$loading = $button.find('.ps-js-loading').show(),
			$error = popup.$el.find('.ps-js-error').hide(),
			password = popup.$el.find('#ps-js-export-data-download-pass').val();

		ajax.post('profile.download_account_data', { password })
			.then(json => {
				if (json.success) {
					if (json.data) {
						let title = popup.title(),
							successPopup = dialog(json.data.messages, { title });

						popup.hide();
						successPopup.show();
						setTimeout(() => {
							successPopup.hide();
							window.location = json.data.url;
						}, 1500);
					}
				} else if (json.errors) {
					$error.html(json.errors[0]).show();
				}
			})
			.always(() => {
				$loading.hide();
				$button.removeAttr('disabled');
			});
	}
});

/**
 * Data export delete script.
 */
$(function () {
	let popup;

	$('.ps-js-export-data-delete').on('click', e => {
		e.preventDefault();
		showDialog();
	});

	function showDialog() {
		if (!popup) {
			popup = dialog(profileData.template_export_data_delete, {
				destroyOnClose: false
			}).show();

			popup.$el.find('.ps-js-cancel').on('click', () => popup.hide());
			popup.$el.find('.ps-js-submit').on('click', () => submit());
			popup.$el.find('form').on('submit', e => {
				e.preventDefault();
				submit();
			});
			hooks.doAction('init_password_preview');
		}

		popup.$el.find('#ps-js-export-data-delete-pass').val('');
		popup.$el.find('.ps-js-error').hide();
		popup.show();
	}

	function submit() {
		let $button = popup.$el.find('.ps-js-submit').attr('disabled', 'disabled'),
			$loading = $button.find('.ps-js-loading').show(),
			$error = popup.$el.find('.ps-js-error').hide(),
			password = popup.$el.find('#ps-js-export-data-delete-pass').val();

		ajax.post('profile.delete_account_data_archive', { password })
			.then(json => {
				if (json.success) {
					if (json.data) {
						let title = popup.title(),
							successPopup = dialog(json.data.messages, { title });

						popup.hide();
						successPopup.show();
						setTimeout(() => {
							window.location = json.data.url;
						}, 3000);
					}
				} else if (json.errors) {
					$error.html(json.errors[0]).show();
				}
			})
			.always(() => {
				$loading.hide();
				$button.removeAttr('disabled');
			});
	}
});
