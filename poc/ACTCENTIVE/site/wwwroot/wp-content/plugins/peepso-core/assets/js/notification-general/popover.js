import $ from 'jquery';
import peepso, { observer } from 'peepso';
import NotificationGeneral from './singleton';
import NotificationPopover from '../notification-popover';

const TEXT_MARK_AS_READ = peepsodata.mark_all_as_read_text;
const TEXT_MARK_AS_READ_CONFIRM = peepsodata.mark_all_as_read_confirm_text;
const TEXT_SHOW_UNREAD_ONLY = peepsodata.show_unread_only_text;
const TEXT_SHOW_ALL = peepsodata.show_all_text;
const TEXT_VIEW_ALL = peepsodata.view_all_text;
const LINK_PAGE_NOTIFICATIONS = peepsodata.notification_url;
const TEMPLATE_HEADER = peepsodata.notification_header;

let instance = new NotificationGeneral();

export default class NotificationPopoverGeneral extends NotificationPopover {
	/**
	 * Create general notification popover.
	 * @param {Element} elem
	 */
	constructor(elem) {
		super(elem);

		this.loading = false;
		this.loadEnd = false;

		instance.on('counter:updated', count => {
			this.updateCounter(count);
		});
		instance.on('html:updated', html => {
			this.updateHtml(html);
		});
		instance.on('unread_only:updated', state => {
			this.updateButton(state);
		});
	}

	/**
	 * Create popover header.
	 * @returns {jQuery}
	 */
	createHeader() {
		if (!TEMPLATE_HEADER) {
			return $();
		}

		return $('<div class="ps-notif__box-header"></div>')
			.html(TEMPLATE_HEADER)
			.on('click', e => e.stopPropagation());
	}

	/**
	 * Create popover body.
	 * @returns {jQuery}
	 */
	createBody() {
		return (
			super
				.createBody()
				.on('mousedown', '.ps-js-notification', e => {
					this.onItemClick(e);
				})
				.on('click', '.ps-js-mark-as-read', e => {
					this.onMarkRead(e);
				})
				// Do not propagate these events which might triggers unwanted ancestor's event handlers.
				.on('click', '.ps-js-notification a', e => {
					e.stopPropagation();
				})
				.on('mousedown', '.ps-js-mark-as-read', e => {
					e.stopPropagation();
				})
		);
	}

	/**
	 * Create popover footer.
	 * @returns {jQuery}
	 */
	createFooter() {
		// Mark all as read button.
		this.$btnMarkAllRead = $('<a href="#">' + TEXT_MARK_AS_READ + '</a>').on('click', e => {
			this.onMarkAllRead(e);
		});

		// Toggle unread only button.
		let unreadOnly = observer.applyFilters('notification_unread_only', 0);
		this.$btnToggleUnreadOnly = $(
			'<a href="#">' + (unreadOnly ? TEXT_SHOW_ALL : TEXT_SHOW_UNREAD_ONLY) + '</a>'
		).on('click', e => {
			this.onToggleUnreadOnly(e);
		});

		let $viewAll = $(
			'<a href="' + LINK_PAGE_NOTIFICATIONS + '">' + TEXT_VIEW_ALL + '</a></div>'
		).on('click', e => {
			e.stopPropagation();
		});

		return $('<div class="ps-notif__box-footer"></div>')
			.append(this.$btnMarkAllRead)
			.append(this.$btnToggleUnreadOnly)
			.add($('<div class="ps-notif__box-footer"></div>').append($viewAll));
	}

	/**
	 * Update notification counter.
	 * @param {number} count
	 */
	updateCounter(count) {
		let $counter = this.$counter;
		if (count > 0) {
			$counter.text(count).show();
		} else {
			$counter.hide();
		}
	}

	/**
	 * Update notification items.
	 * @param {string} html
	 */
	updateHtml(html) {
		if (this.html !== html) {
			this.html = html;
			this.$popoverBody.html(html);

			// Open link in a new tab if its in administrator page.
			if ($(document.body).hasClass('wp-admin')) {
				this.$popoverBody.find('a[href]').attr('target', '_blank');
			}
		}
	}

	/**
	 * Update toggle unread only button state.
	 * @param {number} state
	 */
	updateButton(state) {
		let $btn = this.$btnToggleUnreadOnly,
			label = state ? TEXT_SHOW_ALL : TEXT_SHOW_UNREAD_ONLY;

		// Reset loadEnd state.
		this.loadEnd = false;

		$btn.html(label);
	}

	/**
	 * Toggle popover visibility.
	 * @param {Event} [e]
	 */
	toggle(e) {
		super.toggle(e);

		if (!this.html) {
			let unreadCount = +this.$counter.text() || 0;
			instance.unreadCount = unreadCount;
			instance.fetch(1);
		}
	}

	/**
	 * Load next notification items.
	 */
	loadNext() {
		let $loading, $body;

		if (this.loading || this.loadEnd) {
			return;
		}

		this.loading = true;

		// Show loading.
		$loading = this.$popoverLoading;
		$body = this.$popoverBody.append($loading);
		$body[0].scrollTop = $body[0].scrollHeight;

		instance
			.next()
			.catch(() => {
				this.loadEnd = true;
			})
			.then(() => {
				this.loading = false;
				$loading.detach();
				if (!this.loadEnd) {
					this.tryLoadNext();
				}
			});
	}

	onItemClick(e) {
		let $item = $(e.currentTarget),
			id = $item.data('id'),
			isUnread = $item.data('unread'),
			cssUnread = 'ps-notification--unread',
			$link,
			$btn,
			openedInNewWindow;

		// Do not propagate event to make sure default link action is not prevented
		// by parent event listeners.
		e.stopPropagation();

		// Exit on certain conditions to fire default actions.
		if (
			e.which === 3 || // Assume right-click will open context menu.
			e.ctrlKey || // Assume Ctrl+click will also open context menu.
			e.altKey // Assume Alt+click will download the link URL.
		) {
			return;
		}

		$link = $item.find('a').eq(0);

		if (
			$link.attr('target') === '_blank' || // Link will be opened in a new tab.
			e.which === 2 || // Assume middle-click will open link in a new tab.
			e.metaKey || // Assume Meta+click will also open link in a new tab.
			e.shiftKey // Assume Shift+click will open link in a new window.
		) {
			openedInNewWindow = true;
		}

		// Handle behavior for already read notification.
		if (!isUnread) {
			if (!openedInNewWindow) {
				this.openUrl($link.attr('href'));
			}
			return;
		}

		// Intercept default action if link is not opened in a new window.
		if (!openedInNewWindow) {
			$link.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
			});
		}

		if ($item.data('progress')) {
			return;
		}

		$item.data('progress', true).css('opacity', 0.5);
		$item.removeClass(cssUnread);
		$btn = $item.find('.ps-js-mark-as-read').hide();

		instance
			.markRead(id)
			.then(() => {
				$item.data('unread', false);
				$btn.remove();

				// Follow  URL if link is not opened in a new window.
				if (!openedInNewWindow) {
					$link.off('click');
					// https://stackoverflow.com/questions/20928915/jquery-triggerclick-not-working
					$link[0].click();
					this.openUrl($link.attr('href'));
				}
			})
			.catch(() => {
				$item.addClass(cssUnread);
				$btn.show();
			})
			.then(() => {
				$item.data('progress', false).css('opacity', '');
			});
	}

	/**
	 * Handle mark a notification item as read.
	 * @param {Event} e
	 */
	onMarkRead(e) {
		let $btn = $(e.currentTarget),
			$item = $btn.closest('.ps-js-notification'),
			id = $item.data('id'),
			cssUnread = 'ps-notification--unread';

		e.preventDefault();
		e.stopPropagation();

		if ($item.data('progress')) {
			return;
		}

		$item.data('progress', true).css('opacity', 0.5);
		$item.removeClass(cssUnread);
		$btn.hide();

		instance
			.markRead(id)
			.then(() => {
				$item.data('unread', false);
				$btn.remove();
			})
			.catch(() => {
				$item.addClass(cssUnread);
				$btn.show();
			})
			.then(() => {
				$item.data('progress', false).css('opacity', '');
			});
	}

	/**
	 * Handle mark all notification items as read.
	 * @param {Event} e
	 */
	onMarkAllRead(e) {
		let $btn = this.$btnMarkAllRead;

		e.preventDefault();
		e.stopPropagation();

		if ($btn.data('progress')) {
			return;
		}

		if (confirm(TEXT_MARK_AS_READ_CONFIRM)) {
			$btn.data('progress', true).css('opacity', 0.5);
			instance
				.markRead()
				.then(() => {
					this.toggle();
				})
				.catch($.noop)
				.then(() => {
					$btn.data('progress', false).css('opacity', '');
				});
		}
	}

	/**
	 * Navigate to a notification url. Reload the page if the new url is same with the current url.
	 *
	 * @param {string} url
	 */
	openUrl(url) {
		let oldHref = window.location.href,
			newHref = url,
			sameHref = newHref.replace(/#.*$/, '') === oldHref.replace(/#.*$/, '');

		setTimeout(function () {
			window.location = newHref;
			// Fix same URL not reloading the page.
			if (sameHref) {
				window.location.reload();
			}
		}, 1);
	}

	/**
	 * Handle toggle showing only unread notification items.
	 * @param {Event} e
	 */
	onToggleUnreadOnly(e) {
		let $btn = this.$btnToggleUnreadOnly,
			timer;

		e.preventDefault();
		e.stopPropagation();

		if ($btn.data('progress')) {
			return;
		}

		// Show loading progress.
		this.$popoverBody.empty().append(this.$popoverLoading);

		$btn.data('progress', true).css('opacity', 0.5);
		instance
			.toggleUnreadOnly()
			.catch($.noop)
			.then(() => {
				$btn.data('progress', false).css('opacity', '');
				this.tryLoadNext();
			});
	}
}
