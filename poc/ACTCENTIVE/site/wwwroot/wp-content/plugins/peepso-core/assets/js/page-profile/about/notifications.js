import $ from 'jquery';
import { currentuserid as user_id, userid as view_user_id } from 'peepsodata';

let queue = [];
let saving = false;

const saveNotification = (fieldname, value, priority = false) => {
	return new Promise(resolve => {
		let item = [fieldname, value, resolve, priority];
		priority ? queue.unshift(item) : queue.push(item);
		setTimeout(saveExecQueue, 500);
	});
};

const saveExecQueue = () => {
	if (!queue.length) {
		return;
	}

	if (saving) {
		return;
	}

	let item = queue.shift();
	let fieldname = item[0];
	let value = item[1];
	let resolves = [item[2]];
	let priority = item[3];
	let params = { user_id, view_user_id, fieldname, value };

	if (!priority && queue.length) {
		item = queue.shift();
		params.fieldname_extra = item[0];
		params.value_extra = item[1];
		resolves.push(item[2]);
	}

	saving = true;
	peepso.postJson('profilepreferencesajax.save_notifications', params, json => {
		saving = false;
		resolves.forEach(resolve => resolve(json));
		saveExecQueue();
	});
};

const toggleEmailOpts = (show = true) => {
	let $emailHeader = $('.ps-js-preferences-header').find('span[data-type=email]');
	let $emailBtns = $('.ps-js-preferences-button').filter('[data-type=email]');
	let $emailChks = $('.ps-js-notification-option').find('span[data-type=email]');

	if (show) {
		$emailHeader.show();
		$emailBtns.show();
		$emailChks.show();
	} else {
		$emailHeader.hide();
		$emailBtns.hide();
		$emailChks.hide();
	}
};

const togglePairedOpt = checkbox => {
	let $self = $(checkbox);
	let selfType = $self.closest('span[data-type]').data('type');

	let $pairs = $self.closest('.ps-preferences__checkbox').find('[type=checkbox]').not($self);
	if (!$pairs.length) {
		return;
	}

	$pairs.each(function () {
		let $pair = $(this);
		let pairType = $pair.closest('span[data-type]').data('type');

		if ('onsite' === selfType && !$self[0].checked && $pair[0].checked) {
			$pair.trigger('click');
		} else if ('onsite' === pairType && !$pair[0].checked && $self[0].checked) {
			$pair.trigger('click');
		}
	});
};

$(function () {
	// Handle change on nofification config.
	$('.ps-js-profile-list').on('change', '[type=checkbox]', function () {
		let $el = $(this);
		let $loading = $el.closest('.ps-js-notification-option').find('.ps-js-loading');
		let $loadingProgress = $loading.find('img');
		let $loadingComplete = $loading.find('i');

		// Show loading.
		$loadingComplete.stop().hide();
		$loadingProgress.show();

		saveNotification($(this).attr('name'), this.checked ? 1 : 0).then(function () {
			// Hide loading.
			$loadingProgress.hide();
			$loadingComplete.show().delay(800).fadeOut();
		});

		togglePairedOpt(this);
	});

	let $eni = $('select[name=email_intensity]');

	// Fix "profilepreferencesajax.savepreference" endpoint is accidentally triggered.
	$eni.off('change.savepref');

	$eni.on('change', function () {
		let value = this.value;
		let $loading = $eni.next('.ps-js-loading');
		let $loadingProgress = $loading.find('img');
		let $loadingComplete = $loading.find('i');

		// Show loading.
		$loadingComplete.stop().hide();
		$loadingProgress.show();

		saveNotification('email_intensity', value, true).then(json => {
			// Hide loading.
			$loadingProgress.hide();
			$loadingComplete.show().delay(800).fadeOut();

			if (json.success) {
				// Update description for the selected option.
				let $descs = $('#peepso_email_intensity_descriptions').children();
				let $desc = $descs.filter('#peepso_email_intensity_' + value);
				if ($desc.length) {
					$descs.not($desc).hide();
					$desc.show();
				}

				// Toggle email notification options.
				toggleEmailOpts(0 === +value);
			}
		});
	});

	// Also toggle email notification options on page load.
	if ($eni.length) {
		toggleEmailOpts(0 === +$eni.val());
	}

	// Handle Web Push option.
	let $browserpush = $('input[name=web_push]');
	$browserpush.off('change.savepref'); // Fix accidently triggers "profilepreferencesajax.savepreference" endpoint.
	$browserpush.on('change', function () {
		let value = this.checked ? 1 : 0;
		let $loading = $browserpush.closest('.ps-preferences__notification').find('.ps-js-loading');
		let $loadingProgress = $loading.find('img');
		let $loadingComplete = $loading.find('i');

		// Show loading.
		$loadingComplete.stop().hide();
		$loadingProgress.show();

		saveNotification('web_push', value, true).then(json => {
			// Hide loading.
			$loadingProgress.hide();
			$loadingComplete.show().delay(800).fadeOut();

			if (json.success) {
				setTimeout(function () {
					window.location.reload();
				}, 500);
			}
		});
	});

	// Make sure email notification checkbox states is consistent with onsite nofification checkbox states.
	$('.ps-preferences__checkbox')
		.children('[data-type=onsite]')
		.find('[type=checkbox]')
		.each(function () {
			togglePairedOpt(this);
		});
});
