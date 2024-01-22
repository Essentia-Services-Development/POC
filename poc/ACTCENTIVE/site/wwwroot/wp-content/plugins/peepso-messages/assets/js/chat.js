import { observer } from 'peepso';
import './chat-window';
import PsChatSidebar from './chat-sidebar';

(function ($, _, peepso, factory) {
	window.PsChat = null;
	window.ps_chat = {};
	window.ps_chat.open_chat = function (id) {
		var url = window.peepsochatdata.messageUrl.replace('{id}', id);
		if ($(document.body).hasClass('wp-admin')) {
			window.open(url);
		} else {
			window.location = url;
		}
	};

	// disable chat on mobile
	var mobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i;
	if (mobile.test(navigator.userAgent)) {
		return;
	}

	// initialize chat plugin
	setTimeout(function () {
		// Add hooks to disable the chat feature completely.
		if (!observer.applyFilters('chat_enabled', true)) {
			return;
		}

		PsChat = factory($, _, peepso);
		var inst = new PsChat();

		// Override `open_chat` function if chat is enabled on the current page.
		if (!inst.isDisabled()) {
			ps_chat.open_chat = function (id) {
				inst.openChat(id);
			};
		}
	}, 100);
})(jQuery || $, _, peepso, function ($, _, peepso) {
	var SSE_ENABLED = +peepsodata.sse;
	var MAX_VISIBLE_WINDOWS = 4;
	var DEFAULT_POLLING_INTERVAL = +peepsodata.notification_ajax_delay_min;
	var INACTIVE_POLLING_INTERVAL = +peepsodata.notification_ajax_delay;

	// Disable and enable pages value sanitizer.
	function sanitizePages(pages) {
		pages = pages.split(/\r?\n/);
		pages = _.map(pages, function (str) {
			return str.trim();
		});
		pages = _.filter(pages, function (str) {
			return str.length;
		});
		return pages;
	}

	var _messagesdata = window.peepsomessagesdata || {};
	var RESTRICT_MODE = +_messagesdata.chat_restriction_mode || 0;
	var DISABLE_ON_PAGES = sanitizePages(_messagesdata.chat_disable_on_pages || '');
	var ENABLE_ON_PAGES = sanitizePages(_messagesdata.chat_enable_on_pages || '');

	/**
	 * Chat stack class.
	 * @class PsChat
	 */
	function PsChat() {
		var path = window.location.pathname,
			isLogin = +peepsodata.currentuserid,
			mode = RESTRICT_MODE,
			pages = 1 === mode ? ENABLE_ON_PAGES : DISABLE_ON_PAGES,
			i;

		// Disable chat for guest.
		if (!isLogin) {
			this.disabled = true;
			return false;
		}

		// Set default based on mode.
		this.disabled = 1 === mode ? true : false;

		// Disable/enable on current pages based on the config.
		if (pages.length) {
			for (i = 0; i < pages.length; i++) {
				try {
					// Negate default based on mode if the page match with the config.
					if (path.match(new RegExp('^' + pages[i] + '(#|\\?|\\/|$)'))) {
						this.disabled = !this.disabled;
						break;
					}
				} catch (e) {}
			}
		}

		// Skip initialization if disabled.
		if (this.disabled) {
			return false;
		}

		this.state = {};
		this.beforeDestroyState = {};
		this.windows = {};
		this.windowsOrder = [];
		this.windowsUpdate = {};
		this.sidebar = new PsChatSidebar();
		this.create();
	}

	peepso.npm.objectAssign(
		PsChat.prototype,
		/** @lends PsChat.prototype */ {
			/**
			 * Chat stack template.
			 * @type {string}
			 */
			template: peepsochatdata.containerTemplate,

			/**
			 * Initialize chat stack.
			 */
			create: function () {
				this.$root = $(this.template);
				this.$wrapper = this.$root.children('.ps-js-chat');
				this.$windows = this.$wrapper.children('.ps-js-chat-windows');
				this.$wrapper.append(this.sidebar.$el);
				this.$root.appendTo(document.body);

				this.sidebar.on('select', $.proxy(this.onSidebarSelect, this));
				this.sidebar.on('remove', $.proxy(this.onSidebarRemove, this));

				$(window)
					.off('resize.ps-js-chat')
					.on('resize.ps-js-chat', $.proxy(this.onDocumentResize, this));

				// Delay starting chat polling requests to give time for more important Ajax requests.
				setTimeout(
					$.proxy(function () {
						if (SSE_ENABLED) {
							this.fetchChatState();
							return;
						}

						this.onDocumentResize();

						// Restart long-polling every 30s if config is set.
						if (+peepsomessagesdata.get_chats_longpoll) {
							setInterval(
								$.proxy(function () {
									this.startLongPolling();
								}, this),
								30000
							);
						}
					}, this),
					3000
				);

				peepso.observer.addAction(
					'peepso_sse',
					$.proxy(function (data) {
						if (data.event === 'get_chats') {
							this.fetchChatState();
						}
					}, this),
					10,
					1
				);

				peepso.observer.addAction(
					'browser.inactive',
					$.proxy(function () {
						this.browserInactive = true;
					}, this)
				);

				peepso.observer.addAction(
					'browser.active',
					$.proxy(function () {
						this.browserInactive = false;
						this.checkChatDelay = DEFAULT_POLLING_INTERVAL;
						this.stopLongPolling();
						this.startLongPolling();
					}, this)
				);
			},

			/**
			 * Starts long-polling to get chat stack state.
			 */
			startLongPolling: function () {
				if (!SSE_ENABLED && !this.sessionExpired) {
					this.stopLongPolling();
					this.fetchChatState().done(
						$.proxy(function () {
							this.checkChatState();
						}, this)
					);
				}
			},

			/**
			 * Stops long-polling to get chat stack state.
			 */
			stopLongPolling: function () {
				if (!SSE_ENABLED) {
					clearTimeout(this.checkChatTimer);
					this.checkChatXHR && this.checkChatXHR.abort();
					this.fetchChatXHR && this.fetchChatXHR.ret && this.fetchChatXHR.ret.abort();
					this.checkChatXHR = false;
					this.fetchChatXHR = false;
				}
			},

			/**
			 * Check if there is any changes on chat state for current user.
			 * @returns jQuery.Deferred
			 */
			checkChatState: function () {
				this.checkChatXHR = $.post({
					url: peepsodata.ajaxurl,
					dataType: 'json',
					data: {
						action: 'peepso_should_get_chats',
						delay: this.checkChatDelay
					}
				});

				this.checkChatXHR.always(
					function (data) {
						var delay, isStateChanged;

						data = data || [];

						// Stop pooling if session is expired.
						if (data.session_timeout) {
							this.stopLongPolling();
							this.sessionExpired = true;
							return;
						}

						delay = +data[1];
						isStateChanged = +data[0];

						// update delay
						this.checkChatDelay = delay || this.checkChatDelay;

						if (isStateChanged) {
							this.fetchChatState().always(
								$.proxy(function () {
									this.checkChatDelayed(delay);
								}, this)
							);
						} else {
							this.checkChatDelayed(delay);
						}
					}.bind(this)
				);
			},

			/**
			 * Delayed call for `checkChatState` function.
			 * @param {number} delay Delay time in millisecond
			 */
			checkChatDelayed: function (delay) {
				delay = delay || DEFAULT_POLLING_INTERVAL;
				if (this.browserInactive) {
					delay = INACTIVE_POLLING_INTERVAL;
				}

				this.checkChatTimer = setTimeout($.proxy(this.checkChatState, this), delay);
			},

			/**
			 * Fetch chat state for current user.
			 * @returns jQuery.Deferred
			 */
			fetchChatState: function () {
				if (this.fetchChatXHR) {
					return $.Deferred(
						$.proxy(function (defer) {
							defer.resolveWith(this);
						}, this)
					);
				}

				this.fetchChatXHR = peepso
					.disableAuth()
					.disableError()
					.postJson(
						'chatajax.get_chats',
						{},
						$.proxy(function (response) {
							var chat, i;
							if (response.success) {
								// reset windows order
								this.windowsOrder = [];
								for (i = 0; i < response.data.chats.length; i++) {
									chat = response.data.chats[i];
									this.state[chat.id] = this.state[chat.id] || {};
									// update windows order
									this.windowsOrder.push(+chat.id);
									// check if we need to update chat window and sidebar based on `last_activity` or `muted` value change
									if (
										this.state[chat.id].last_activity !== chat.last_activity ||
										this.state[chat.id].muted !== +chat.muted ||
										this.state[chat.id].disabled !== +chat.disabled ||
										this.state[chat.id].send_receipt !== +chat.send_receipt ||
										this.state[chat.id].receipt !== +chat.receipt ||
										this.state[chat.id].receipt_unread !==
											+chat.receipt_unread ||
										this.state[chat.id].unread !== +chat.unread
									) {
										// set to false for sidebar on first call to fix false positive flag
										if (
											!this.state[chat.id].last_activity &&
											i >= MAX_VISIBLE_WINDOWS
										) {
											this.windowsUpdate[chat.id] = false;
										} else {
											this.windowsUpdate[chat.id] = true;
										}
									}
									// update window state
									$.extend(this.state[chat.id], {
										state: chat.state,
										unread: chat.unread,
										last_activity: chat.last_activity,
										disabled: +chat.disabled,
										muted: +chat.muted,
										send_receipt: +chat.send_receipt,
										receipt: +chat.receipt,
										receipt_unread: +chat.receipt_unread,
										unread: +chat.unread
									});
								}
								this.renderWindows();
							}

							this.fetchChatXHR = false;
						}, this)
					);

				return this.fetchChatXHR.ret; // return $.Deferred
			},

			/**
			 * Generate local copy of chat state.
			 */
			getChatState: function () {
				var states = [],
					data,
					id,
					i;

				for (i = 0; i < this.windowsOrder.length; i++) {
					id = this.windowsOrder[i];
					data = this.state[id] || {};
					states.push({
						id: id,
						state: data.state || undefined,
						unread: data.unread || undefined,
						last_activity: data.last_activity || undefined
					});
				}
				return states;
			},

			/**
			 * Sends ajax request to update chat state for particular chat window.
			 * @param {number} id Conversation ID.
			 * @param {number} state New chat state value (0: closed, 1: expand, 2: collapse).
			 */
			setChatState: function (id, state) {
				var states;
				this.stopLongPolling();
				this.state[id] = $.extend(this.state[id] || {}, state);
				states = JSON.stringify(this.getChatState());
				this.setChatStateXHR && this.setChatStateXHR.ret.abort();
				this.setChatStateXHR = peepso
					.disableAuth()
					.disableError()
					.postJson(
						'chatajax.set_chats',
						{ chats: states },
						$.proxy(function () {
							this.setChatStateXHR = false;
							this.startLongPolling();
						}, this)
					);
			},

			/**
			 * Render all chat windows based on current state.
			 */
			renderWindows: function () {
				var sidebarOrder = this.windowsOrder.slice(MAX_VISIBLE_WINDOWS),
					stackOrder = this.windowsOrder.slice(0, MAX_VISIBLE_WINDOWS),
					id,
					state,
					$el,
					i;

				// remove windows not listed on stack order
				for (id in this.windows) {
					if (stackOrder.indexOf(+this.windows[id].id) === -1) {
						this.windows[id].destroy();
						delete this.windows[id];
					}
				}
				// add missing windows
				for (i = 0; i < stackOrder.length; i++) {
					id = stackOrder[i];
					if (!this.windows[id]) {
						this.windows[id] = this.createWindow(id);
						this.windows[id].$el.appendTo(this.$windows);
					}
				}

				// update and re-order windows
				for (i = 0; i < stackOrder.length; i++) {
					id = stackOrder[i];
					state = +this.state[id].state;
					$el = this.windows[id].$el[0];

					if (i !== this.getWindowIndex($el)) {
						if (i === 0) {
							$($el).prependTo(this.$windows);
						} else {
							$($el).insertBefore(this.$windows.children().eq(i));
						}
					}

					// update state
					if (state === 1) {
						this.windows[id].expand();
					} else {
						this.windows[id].collapse();
					}

					// update window if necessary
					if (this.windowsUpdate[id]) {
						this.windows[id].update(this.state[id]);
						this.windowsUpdate[id] = false;
					}
				}
				// reset sidebar
				this.sidebar.reset(sidebarOrder, this.windowsUpdate);
				if (this.sidebar.visible) {
					this.$wrapper.addClass('ps-chat--more');
				} else {
					this.$wrapper.removeClass('ps-chat--more');
				}

				// prevent backspace from navigating back if there is active chat window
				if (this.windowsOrder.length) {
					if (!this.backspacePrevented) {
						this.backspacePrevented = true;
						$(document).on('keydown.ps-js-chat', function (e) {
							if (e.which === 8 && !$(e.target).is('input, textarea')) {
								e.preventDefault();
							}
						});
					}
					// bring back default backspace behavior if no active chat window
				} else if (this.backspacePrevented) {
					this.backspacePrevented = false;
					$(document).off('keydown.ps-js-chat');
				}
			},

			/**
			 * Creates a new conversation window.
			 * @param {number} id Conversation ID.
			 * @return {PsChatWindow} Chat window instance.
			 */
			createWindow: function (id) {
				var wnd = new PsChatWindow(id, this.state[id]);
				wnd.on('expand', $.proxy(this.onWindowExpand, this));
				wnd.on('collapse', $.proxy(this.onWindowCollapse, this));
				wnd.on('destroy', $.proxy(this.onWindowDestroy, this));
				return wnd;
			},

			/**
			 * Get window index
			 * @param {element} node Window element.
			 * @return {number} Window index in chat stack.
			 */
			getWindowIndex: function (node) {
				var index;
				if ($.contains(document.documentElement, node)) {
					for (index = 0; (node = node.previousSibling); index++) {}
				}
				return index;
			},

			/**
			 * Opens a new chat specified with conversation ID.
			 * @param {number} id Conversation ID.
			 */
			openChat: function (id) {
				var order;
				id = +id;
				order = this.windowsOrder.indexOf(id);
				if (order === -1) {
					this.windows[id] = this.createWindow(id);
					this.windows[id].$el.show();
					this.windows[id].$el.appendTo(this.$windows);
					this.windowsOrder.splice(MAX_VISIBLE_WINDOWS - 1, 0, id);
				} else if (order < MAX_VISIBLE_WINDOWS) {
					this.windows[id].$el.show();
					this.windows[id].expand();
				} else {
					this.windows[id] || (this.windows[id] = this.createWindow(id));
					this.windows[id].$el.show();
					this.windows[id].$el.appendTo(this.$windows);
					this.windowsOrder.splice(order, 1);
					this.windowsOrder.splice(MAX_VISIBLE_WINDOWS - 1, 0, id);
				}
				this.windows[id].focus();
				this.windows[id].markAsRead();
				this.setChatState(id, { state: 1 });
				if (this.beforeDestroyState[id]) {
					this.state[id].disabled = this.beforeDestroyState[id].disabled;
					this.state[id].muted = this.beforeDestroyState[id].muted;
					this.state[id].send_receipt = this.beforeDestroyState[id].send_receipt;
				}
				this.windowsUpdate[id] = true;
				this.renderWindows();
			},

			closeChat: function (id) {
				var order;
				this.beforeDestroyState[id] = this.state[id];
				this.windows[id] && this.windows[id].destroy();
				delete this.windows[id];
				delete this.state[id];
				order = this.windowsOrder.indexOf(+id);
				order > -1 && this.windowsOrder.splice(order, 1);
			},

			/**
			 * Check if chat is disabled on the currect page.
			 *
			 * @return {boolean}
			 */
			isDisabled: function () {
				return this.disabled;
			},

			/**
			 * Event handler when one of chat window is being expanded by user.
			 * @private
			 * @param {number} id Conversation ID being expanded.
			 */
			onWindowExpand: function (id) {
				this.setChatState(id, { state: 1 });
			},

			/**
			 * Event handler when one of chat window is being collapsed by user.
			 * @private
			 * @param {number} id Conversation ID being collapsed.
			 */
			onWindowCollapse: function (id) {
				this.setChatState(id, { state: 2 });
			},

			/**
			 * Event handler when one of chat window is being closed by user.
			 * @private
			 * @param {number} id Conversation ID being closed.
			 */
			onWindowDestroy: function (id) {
				this.beforeDestroyState[id] = this.state[id];
				this.setChatState(id, { state: 0 });
				delete this.windows[id];
				delete this.state[id];
				var order = this.windowsOrder.indexOf(+id);
				order > -1 && this.windowsOrder.splice(order, 1);
				id = this.sidebar.removeFirst();
				if (id) {
					this.windows[id] || (this.windows[id] = this.createWindow(id));
					this.windows[id].$el.show();
					this.windows[id].$el.appendTo(this.$windows);
					this.windows[id].expand();
				}
			},

			/**
			 * Event handler when one of sidebar item is being selected by user.
			 * @private
			 * @param {number} id Conversation ID being selected.
			 */
			onSidebarSelect: function (id) {
				this.openChat(id);
			},

			/**
			 * Event handler when one of sidebar item is being selected by user.
			 * @private
			 * @param {number} id List of conversation ID being removed.
			 */
			onSidebarRemove: function (ids) {
				if (ids && ids.length) {
					for (var i = 0; i < ids.length; i++) {
						this.setChatState(ids[i], { state: 0 });
					}
				}
			},

			/**
			 * Event handler when window is being resized.
			 * @function
			 * @private
			 */
			onDocumentResize: _.debounce(function () {
				var ctWidth = this.$windows.width(),
					chatWidth = 246,
					chatCount = Math.floor(ctWidth / chatWidth);

				// Some themes are deliberately triggers browser's resize event every few seconds for their theme behavior need,
				// thus making our onresize event handler to be run repeatedly. As a workaround, we should compare previous
				// and current window size, and do not run event handler if window size is not changed.
				if (this._prevWidth !== ctWidth) {
					this._prevWidth = ctWidth;
					this.stopLongPolling();

					if (chatCount >= 1) {
						MAX_VISIBLE_WINDOWS = chatCount;
						this.renderWindows();
						this.startLongPolling();
						this.$root.show();
					} else {
						this.$root.hide();
					}
				}
			}, 500)
		}
	);

	return PsChat;
});
