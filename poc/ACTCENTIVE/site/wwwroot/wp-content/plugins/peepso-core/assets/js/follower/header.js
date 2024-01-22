import $ from 'jquery';
import { hooks } from 'peepso';
import { profile as profileData } from 'peepsodata';
import { follow, unfollow } from './follower';

const PROFILE_ID = profileData && +profileData.id;

const $header = $('.ps-js-focus--profile');
const btnSelector = '.ps-js-friend-follow, .ps-js-friend-unfollow';

// Handle follow/unfollow button.
$header.on('click', btnSelector, function (e) {
	let $btn = $(e.currentTarget),
		$loading = $btn.find('img'),
		userId = $btn.data('user-id'),
		following = !$btn.hasClass('ps-js-friend-follow');

	e.preventDefault();
	e.stopPropagation();

	if (!following) {
		$loading.show();
		follow(userId).then(function () {
			$loading.hide();
		});
	} else {
		$loading.show();
		unfollow(userId).then(function () {
			$loading.hide();
		});
	}
});

$header.on('mouseenter', btnSelector, function (e) {
	let $btn = $(e.currentTarget),
		$label = $btn.find('span').last();

	$label.data('html', $label.html());
	$label.html($btn.data('text-hover'));
});

$header.on('mouseleave', btnSelector, function (e) {
	let $btn = $(e.currentTarget),
		$label = $btn.find('span').last();

	$label.html($label.data('html'));
});

hooks.addAction('user_followed', 'header', updateActions);
hooks.addAction('user_unfollowed', 'header', updateActions);

function updateActions(userId, data = {}) {
	if (+userId !== PROFILE_ID) {
		return;
	}

	let actions = data.actions;

	if ('string' === typeof actions) {
		$header.find('.ps-js-profile-actions-extra').html(actions);
	} else if ('object' === typeof actions) {
		if (actions.primary_profile) {
			$header.find('.ps-js-focus-actions').html(actions.primary_profile);
		}
		if (actions.secondary_profile) {
			$header.find('.ps-js-profile-actions-extra').html(actions.secondary_profile);
		}
	}
}
