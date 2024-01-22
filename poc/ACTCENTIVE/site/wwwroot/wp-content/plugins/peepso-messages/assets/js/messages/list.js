import $ from 'jquery';
import { ajax, observer } from 'peepso';
import { ajaxurl_legacy as AJAXURL_LEGACY } from 'peepsodata';
import { filterMessages } from './util';
import MessageConversation from './conversation';

const per_page = window.peepsomessagesdata && peepsomessagesdata.per_page;

class MessageList {
	constructor(opts = {}) {
		this.$container = $(opts.el);
		this.$form = this.$container.find('.ps-js-messages-search-form');
		this.$query = this.$form.find('input[name=query]');
		this.$btnClear = this.$form.find('.ps-js-btn-clear').hide();
		this.$btnShowAll = $('.ps-js-messages-show-all');
		this.$btnShowUnread = $('.ps-js-messages-show-unread');
		this.$scrollable = this.$container.find('.ps-js-messages-list-scrollable');
		this.$table = this.$container.find('.ps-js-messages-list-table');
		this.$loading = this.$container.find('.ps-js-messages-list-loading');

		this.opts = opts;
		this.foundPages = 0;

		// Hold the conversation instance.
		this.currentConversationID = null;
		this.conversation = null;

		this.params = {
			type: 'inbox',
			unread_only: 0,
			query: null, // Setting as `null` is intended to response to the initial request.
			page: 1,
			per_page
		};

		// Handle input event on the search box.
		this.$query.on('input', () => {
			let val = this.$query.val().trim();

			// Toggle the reset button.
			val.length ? this.$btnClear.show() : this.$btnClear.hide();

			if (!val || val.length >= 3) {
				if (val !== this.params.query) {
					this.params.query = val;
					this.load();
				}
			}
		});

		// Trigger input event on the search box for initial data.
		this.$query.trigger('input');

		// Handle "Enter" on the search box.
		this.$query.on('keyup', e => {
			if ('Enter' === e.key) {
				e.preventDefault();
				e.stopPropagation();

				let val = this.$query.val().trim();

				// Toggle the reset button.
				val.length ? this.$btnClear.show() : this.$btnClear.hide();

				if (val !== this.params.query) {
					this.params.query = val;
					this.load();
				}
			}
		});

		// Handle clear search box button.
		this.$btnClear.on('click', () => {
			this.$btnClear.hide();
			this.$query.removeAttr('value').val('');

			if (this.params.query) {
				this.params.query = '';
				this.load();
			}
		});

		// Handle show all messages option.
		this.$btnShowAll.on('click', e => {
			e.preventDefault();

			this.params.unread_only = 0;
			this.$btnShowAll.addClass('active');
			this.$btnShowUnread.removeClass('active');
			this.load();
		});

		// Handle show unread messages option.
		this.$btnShowUnread.on('click', e => {
			e.preventDefault();

			this.params.unread_only = 1;
			this.$btnShowAll.removeClass('active');
			this.$btnShowUnread.addClass('active');
			this.load();
		});

		this.$table.on('click', '.ps-js-messages-list-item', e => {
			let id = +$(e.currentTarget).data('conversation-id');

			if (id) {
				if (this.getIDFromURL() === id) {
					this.select(id);
				} else {
					this.updateURL(id);
				}
			}
		});

		window.addEventListener('hashchange', e => {
			let oldURL = e.oldURL.match(/^([^#]+)(?:#(\d+))*/);
			let newURL = e.newURL.match(/^([^#]+)#(\d+)/);

			if (oldURL && newURL && oldURL[1] === newURL[1] && oldURL[2] !== newURL[2]) {
				this.select(newURL[2]);
			}
		});

		let evtName = 'peepso_messages_after_send.messages-list';
		$(window)
			.off(evtName)
			.on(evtName, () => {
				setTimeout(() => {
					this.currentConversationID = null;
					this.params.query = null;
					this.$query.trigger('input');
				}, 1000);
			});
	}

	fetch(params = {}) {
		return new Promise((resolve, reject) => {
			let ajaxParams = {
				url: `${AJAXURL_LEGACY}messagesajax.get_messages`,
				type: 'POST',
				data: params,
				dataType: 'json'
			};

			this.fetchXhr && this.fetchXhr.abort();
			this.fetchXhr = $.ajax(ajaxParams)
				.done(json => {
					if (json.success) {
						resolve(json.data);
					} else if (json.errors && params.page === 1) {
						reject(json.errors);
					} else {
						resolve([]);
					}
				})
				.fail(reject)
				.always(() => {
					delete this.fetchXhr;
				});
		});
	}

	load() {
		this.params.page = 1;
		this.$table.empty();
		this.$loading.show();

		this.fetch(this.params)
			.then(data => {
				this.foundPages = data.total_pages;
				this.$loading.hide();
				this.params.query ? this.$btnClear.show() : this.$btnClear.hide();

				let $messages = $(data.html);
				$messages = filterMessages($messages);
				this.$table.empty().append($messages);
				this.highlightItem(this.currentConversationID);

				if (data.total > 0) {
					let id = this.getIDFromURL();

					// Select the first conversation to be opened on page load if no ID is provided.
					if (!id) {
						// This behavior should happen on large screen only.
						if (this.$container.parent().width() - this.$container.width() > 10) {
							let $first = this.$table.find('.ps-js-messages-list-item').first();
							id = $first.data('conversation-id');
						}
					}

					if (id && !this.conversation) {
						this.select(id);
					}
				}

				if (this.foundPages > 1) {
					this.maybeLoadNext();
				}
			})
			.catch(() => {
				// Satisty reject response.
			});
	}

	loadNext() {
		this.params.page++;
		this.$loading.show();

		this.fetch(this.params)
			.then(data => {
				this.$loading.hide();
				this.$table.append(filterMessages($(data.html)));
				this.highlightItem(this.currentConversationID);

				if (this.foundPages > this.params.page) {
					this.maybeLoadNext();
				}
			})
			.catch(() => {
				// Satisty reject response.
			});
	}

	maybeLoadNext() {
		this.$table.off('scroll');

		// Always try to load next page if current conversation is not in the loaded message items.
		if (this.currentConversationID) {
			let currentSelector = `[data-conversation-id="${this.currentConversationID}"]`;
			let $current = this.$table.children(currentSelector);
			if (!$current.length) {
				this.loadNext();
				return;
			}
		}

		this.$table.on('scroll', () => {
			let scrollPosition = this.$table.innerHeight() + this.$table.scrollTop();
			if (this.$table[0].scrollHeight - scrollPosition < 30) {
				this.$table.off('scroll');
				this.loadNext();
			}
		});
		this.$table.trigger('scroll');
	}

	/**
	 * Select a specific message.
	 *
	 * @param {number} id
	 */
	select(id) {
		// Cast ID to number for better comparison.
		id = +id;

		if (this.currentConversationID !== id) {
			this.currentConversationID = id;
			this.updateURL(id);
			this.initConversation(id);
			this.highlightItem(id);
			this.markAsRead(id);
		} else if (this.conversation) {
			this.conversation.show();
		}
	}

	/**
	 * Parse URL to get the conversation ID.
	 *
	 * @returns {number|null}
	 */
	getIDFromURL() {
		let hash = window.location.hash;
		if (hash) {
			let parsed = hash.match(/^#(\d+)/);
			if (parsed) {
				return +parsed[1];
			}
		}

		return null;
	}

	/**
	 * Update URL based on a provided conversation ID.
	 *
	 * @param {number} id
	 */
	updateURL(id) {
		let currentHash = window.location.hash;
		let newHash = `#${id}`;
		if (currentHash !== newHash) {
			window.location.hash = newHash;
		}
	}

	/**
	 * Highlight message item based on a provided conversation ID.
	 */
	highlightItem(id) {
		let $items = this.$table.children();
		let $itemCurrent = $items.filter(`[data-conversation-id="${id}"]`);
		let className = 'ps-messages__list-item--selected';

		$items.not($itemCurrent).filter(`.${className}`).removeClass(className);

		// Only highlight and scroll if item is not already highlighted.
		if ($itemCurrent.length && !$itemCurrent.hasClass(className)) {
			$itemCurrent.addClass(className);
			setTimeout(function () {
				$itemCurrent[0].scrollIntoView({ block: 'nearest', inline: 'nearest' });
			}, 100);
		}
	}

	/**
	 * Initialize conversation view.
	 *
	 * @param {number} id
	 */
	initConversation(id) {
		if (this.conversation instanceof MessageConversation) {
			this.conversation.destroy();
		}

		this.conversation = new MessageConversation({
			el: document.querySelector('.ps-js-conversation'),
			id
		});
	}

	/**
	 * Mark a particular conversation as read.
	 *
	 * @param {number} id
	 */
	markAsRead(id) {
		ajax.post('messagesajax.mark_read_messages_in_conversation', { msg_id: id });

		this.$table
			.children(`[data-conversation-id="${id}"]`)
			.removeClass('ps-messages__list-item--unread');
	}
}

export default MessageList;
