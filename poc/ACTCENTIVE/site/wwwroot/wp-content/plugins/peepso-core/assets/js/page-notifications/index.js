import $ from 'jquery';
import { ajax, observer } from 'peepso';
import { currentuserid } from 'peepsodata';

const USER_ID = +currentuserid;

let params = {
	page: 1,
	per_page: 10,
	unread_only: 0
};

let $btnShowAll;
let $btnShowUnread;
let $btnMarkAllAsRead;
let $list;
let $loading;
let $alert;

function fetch(params) {
	return new Promise((resolve, reject) => {
		ajax.post('notificationsajax.get_latest', params)
			.done(json => {
				if (json.success) {
					let data = json.data || {};
					resolve(data.notifications || []);
				} else if (json.errors && params.page === 1) {
					reject(json.errors);
				} else {
					resolve([]);
				}
			})
			.fail(reject);
	});
}

function load(unreadOnly = 0) {
	params.page = 1;
	params.unread_only = !!unreadOnly ? 1 : 0;

	$list.hide();
	$alert.hide();
	$loading.show();
	fetch(params)
		.then(function (items) {
			if (items instanceof Array && items.length) {
				$list.html(items.join('')).show();

				if (items.length >= params.per_page) {
					maybeLoadNext();
				}
			}
		})
		.catch(function (error) {
			$alert.html(error).show();
		})
		.finally(() => $loading.hide());
}

function loadNext() {
	params.page++;

	$loading.show();
	fetch(params)
		.then(function (items) {
			if (items instanceof Array && items.length) {
				$list.append(items.join(''));

				if (items.length >= params.per_page) {
					maybeLoadNext();
				}
			}
		})
		.finally(() => $loading.hide());
}

function maybeLoadNext() {
	let evtName = 'scroll.ps-page-notifications';
	let $win = $(observer.applyFilters('autoload_scrollable_container', window));

	$win.off(evtName);
	$win.on(evtName, () => {
		let $lastItem = $list.find('.ps-js-notification').last();
		let position = $lastItem.get(0).getBoundingClientRect();

		if (position.top < (window.innerHeight || document.documentElement.clientHeight)) {
			$win.off(evtName);
			loadNext();
		}
	}).trigger(evtName);
}

function markAllAsRead() {
	return new Promise((resolve, reject) => {
		ajax.post('notificationsajax.mark_as_read')
			.done(json => {
				if (json.success) {
					resolve();
				} else if (json.errors) {
					reject(json.errors[0]);
				} else {
					reject();
				}
			})
			.fail(reject);
	});
}

function markAsRead(note_id) {
	return new Promise((resolve, reject) => {
		ajax.post('notificationsajax.mark_as_read', { note_id })
			.done(json => {
				if (json.success) {
					resolve();
				} else if (json.errors) {
					reject(json.errors[0]);
				} else {
					reject();
				}
			})
			.fail(reject);
	});
}

$(function () {
	let $container = $('.ps-js-page-notifications');
	if (!$container.length) {
		return;
	}

	if (!USER_ID) {
		return;
	}

	$btnShowAll = $container.find('.ps-js-notification-show-all');
	$btnShowUnread = $container.find('.ps-js-notification-show-unread');
	$btnMarkAllAsRead = $container.find('.ps-js-notification-mark-all-as-read');
	$list = $container.find('.ps-js-page-notifications-list');
	$loading = $container.find('.ps-js-page-notifications-loading');
	$alert = $container.find('.ps-js-page-notifications-alert').hide();

	$btnShowAll.on('click', e => {
		e.preventDefault();

		$btnShowAll.addClass('active');
		$btnShowUnread.removeClass('active');
		load(0);
	});

	$btnShowUnread.on('click', e => {
		e.preventDefault();

		$btnShowAll.removeClass('active');
		$btnShowUnread.addClass('active');
		load(1);
	});

	$btnMarkAllAsRead.on('click', e => {
		e.preventDefault();

		let $button = $(e.currentTarget);
		if ($button.data('loading')) {
			return;
		}

		$button.data('loading', 1);

		markAllAsRead().then(() => {
			$button.removeData('loading');
			$list.find('.ps-js-notification[data-unread]').each(function () {
				$(this)
					.removeClass('ps-notification--unread')
					.removeAttr('data-unread')
					.find('.ps-js-mark-as-read')
					.remove();
			});

			// Empty notification counters.
			$('.ps-js-notifications').find('.ps-js-counter').html(0).css('display', 'none');
		});
	});

	$list.on('mousedown click', '.ps-js-mark-as-read', e => {
		e.preventDefault();
		e.stopPropagation();

		let $button = $(e.currentTarget);
		let $item = $button.closest('.ps-js-notification');
		let id = $item.data('id');

		if ($item.data('loading')) {
			return;
		}

		$item.css('opacity', 0.5);
		$item.data('loading', 1);
		markAsRead(id).then(() => {
			$button.remove();
			$item.removeClass('ps-notification--unread');
			$item.removeAttr('data-unread');
			$item.css('opacity', '');
			$item.removeData('loading');

			// Decrease notification counter.
			let $notifs = $('.ps-js-notifications');
			if ($notifs.length) {
				let count = +$notifs.eq(0).find('.ps-js-counter').text();
				$notifs.each(function () {
					$(this)
						.find('.ps-js-counter')
						.html(count - 1)
						.css('display', count > 1 ? '' : 'none');
				});
			}
		});
	});

	// Mark-as-read when the notification is clicked.
	$list.on('mousedown.ps-notification', '.ps-js-notification a', e => {
		var $a = $(e.currentTarget),
			$item = $a.closest('.ps-js-notification'),
			isUnread = +$item.data('unread');

		// Do not proceed if notification item is already read.
		if (!isUnread) {
			return;
		}

		// Assume right-click or ctrl-key will open context menu.
		// Assume alt-key will download link.
		if (e.which === 3 || e.ctrlKey || e.altKey) {
			return;
		}

		// Assume middle-click or meta-key and shift-key will open link in new tab.
		// Assume shift-key will open link in new window.
		if (!(e.which === 2 || e.metaKey || e.shiftKey)) {
			// Temporarily disable default click action.
			$a.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
			});
		}

		$item.css('opacity', 0.5);
		$item.removeClass('ps-notification--unread');
		markAsRead($item.data('id'))
			.then(() => {
				$item.css('opacity', '');
				$item.data('unread', 0);
				if (e.which === 1 && !e.metaKey && !e.shiftKey) {
					$a.off('click');
					// https://stackoverflow.com/questions/20928915/jquery-triggerclick-not-working
					$a[0].click();
				}

				// Decrease notification counter.
				let $notifs = $('.ps-js-notifications');
				if ($notifs.length) {
					let count = +$notifs.eq(0).find('.ps-js-counter').text();
					$notifs.each(function () {
						$(this)
							.find('.ps-js-counter')
							.html(count - 1)
							.css('display', count > 1 ? '' : 'none');
					});
				}
			})
			.fail(function (error) {
				$item.addClass('ps-notification--unread');
				if (error) {
					peepso.dialog(error, { error: true }).show();
				}
			});
	});

	// Start with all notifications.
	$btnShowAll.triggerHandler('click');
});
