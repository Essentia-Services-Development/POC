import $ from 'jquery';
import { hooks } from 'peepso';
import { group as groupData } from 'peepsodata';
import avatarDialog from './avatar-dialog';

const GROUP_ID = +groupData.id;

$(function () {
	let $container = $('.ps-js-focus--group .ps-js-avatar');
	if (!$container.length) {
		return;
	}

	let $image = $container.find('.ps-js-avatar-image');
	let $buttonWrapper = $container.find('.ps-js-avatar-button-wrapper');

	hooks.addAction('group_avatar_updated', 'page_group', (id, imageUrl) => {
		if (+id === GROUP_ID) {
			$image.attr('src', imageUrl);
		}

		if (imageUrl.match(/\/groups\/\d+\//)) {
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
