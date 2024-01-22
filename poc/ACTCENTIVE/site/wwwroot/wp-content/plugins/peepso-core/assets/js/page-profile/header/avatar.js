import $ from 'jquery';
import { hooks } from 'peepso';
import { profile as profileData } from 'peepsodata';
import avatarDialog from './avatar-dialog';

const PROFILE_ID = +profileData.id;

$(function () {
	let $container = $('.ps-js-focus--profile .ps-js-avatar');
	if (!$container.length) {
		return;
	}

	let $image = $container.find('.ps-js-avatar-image');
	let $buttonWrapper = $container.find('.ps-js-avatar-button-wrapper');

	hooks.addAction('profile_avatar_updated', 'page_profile', (id, imageUrl) => {
		if (+id === PROFILE_ID) {
			$image.attr('src', imageUrl);
		}

		if (imageUrl.match(/\/users\/\d+\//)) {
			// Custom avatar.
			$buttonWrapper.css('cursor', '');
			$buttonWrapper.attr(
				'onclick',
				`peepso.simple_lightbox('${imageUrl.replace('-full.', '-orig.')}'); return false`
			);
		} else {
			// Default avatar.
			$buttonWrapper.css('cursor', 'default');
			$buttonWrapper.removeAttr('onclick');
		}
	});

	let $button = $container.find('.ps-js-avatar-button');
	$button.on('click', e => {
		e.preventDefault();
		e.stopPropagation();

		avatarDialog().show();
	});
});
