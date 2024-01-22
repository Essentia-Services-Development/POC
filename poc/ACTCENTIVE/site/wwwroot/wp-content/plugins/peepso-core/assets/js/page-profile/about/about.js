import $ from 'jquery';
import { hooks } from 'peepso';
import { profile as profileData } from 'peepsodata';

const PROFILE_ID = +profileData.id;

$(function() {
	let $completeness = $('.ps-js-profile-completeness');
	if (!$completeness.length) {
		return;
	}

	let $status = $completeness.find('.ps-js-status'),
		$progressbar = $completeness.find('.ps-js-progressbar'),
		$required = $completeness.find('.ps-js-required');

	hooks.addAction('profile_completeness_updated', 'page_profile_about', (id, data = {}) => {
		if (+id === PROFILE_ID) {
			let completed = 0;

			if ('undefined' !== typeof data.profile_completeness) {
				if (+data.profile_completeness >= 100) {
					completed++;
					$status.hide();
					$progressbar.hide();
				} else {
					$status.html(data.profile_completeness_message).show();
					$progressbar.show();
					$progressbar.children('span').css({ width: `${+data.profile_completeness}%` });
				}
			}

			if ('undefined' !== data.missing_required) {
				if (+data.missing_required <= 0) {
					completed++;
					$required.hide();
				} else {
					$required.html(data.missing_required_message).show();
				}
			}

			if (2 === completed) {
				$completeness.hide();
			} else {
				$completeness.show();
			}
		}
	});
});
