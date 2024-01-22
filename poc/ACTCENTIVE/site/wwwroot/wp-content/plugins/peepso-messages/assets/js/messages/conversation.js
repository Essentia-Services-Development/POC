import $ from 'jquery';
import { throttle } from 'underscore';
import { ajax, dialog, observer, template } from 'peepso';
import {
	ajaxurl_legacy as AJAXURL_LEGACY,
	notification_ajax_delay_min as POLLING_DELAY_MIN,
	notification_ajax_delay as POLLING_DELAY_MAX,
	notification_ajax_delay_multiplier as POLLING_DELAY_MULTIPLIER
} from 'peepsodata';
import { filterMessages, currentlyTyping, loadAsyncContents } from './util';

const CHARS_LIMIT = window.peepsomessagesdata && peepsomessagesdata.character_limit;
const TEXT_BTN_SEND = window.peepsomessagesdata && peepsomessagesdata.send_button_text;
const TEMPLATE_MUTE = window.peepsomessagesdata && peepsomessagesdata.mute_confirm;

class MessageConversation {
	constructor(opts = {}) {
		this.$container = $(opts.el);
		this.$wrapper = this.$container.closest('.ps-js-conversation-wrapper');
		this.$back = this.$container.find('.ps-js-conversation-back');
		this.$recipients = this.$container.find('.ps-js-recipients');
		this.$addRecipients = this.$container.find('.ps-js-add-recipients');
		this.$participants = this.$container.find('.ps-js-conversation-participant-summary');
		this.$scrollable = this.$container.find('.ps-js-conversation-scrollable');
		this.$messages = this.$container.find('.ps-js-conversation-messages');
		this.$messagesLoading = this.$container.find('.ps-js-conversation-messages-loading');
		this.$messagesList = this.$container.find('.ps-js-conversation-messages-list');
		this.$messagesTemporary = this.$container.find('.ps-js-conversation-messages-temporary');
		this.$messagesTyping = this.$container.find('.ps-js-conversation-messages-typing');
		this.$messageTemplate = this.$container.find('[data-template=message-item]');
		this.$loading = this.$container.siblings('.ps-js-conversation-loading');
		this.$options = this.$container.find('.ps-js-conversation-options');
		this.$optionsDropdown = this.$container.find('.ps-js-conversation-dropdown');
		this.$postboxTemplate = this.$container.find('[data-template=postbox]');
		this.$postbox = null;
		this.$enterToSend = null;

		this.opts = $.extend({}, opts);

		this.params = { msg_id: this.opts.id };
		this.enterToSend = false;

		this.$back.off('click').on('click', e => {
			e.preventDefault();
			this.hide();
		});

		this.show();
		this.load();
	}

	show() {
		this.$wrapper.addClass('ps-messages__view--open');
	}

	hide() {
		this.$wrapper.removeClass('ps-messages__view--open');
	}

	/**
	 *
	 * @param {Object} params
	 * @returns {JQueryDeferred}
	 */
	fetch(params = {}) {
		let xhr = $.ajax({
			url: `${AJAXURL_LEGACY}messagesajax.get_messages_in_conversation`,
			type: 'POST',
			dataType: 'json',
			data: params
		});

		let defer = $.Deferred();

		defer.abort = xhr.abort;

		xhr.done(json => {
			if (json.success) {
				defer.resolve(json.data);
			} else if (json.errors && params.page === 1) {
				defer.reject(json.errors);
			} else {
				defer.resolve({});
			}
		});

		xhr.fail(() => defer.reject());

		return defer;
	}

	load() {
		let params = $.extend(this.params, {
			get_participants: 1,
			get_options: 1,
			get_unread: 1
		});

		this.$container.hide();
		this.$loading.show();

		this.fetch(params)
			.done(data => {
				this.render(data);

				if ('undefined' !== typeof data.enter_to_send) {
					this.enterToSend = +data.enter_to_send;
				}

				this.initPostbox();
				this.initOptions();
				this.initRecipientsForm();

				this.startLongPolling();

				// Delay loading previous messages a bit.
				setTimeout(() => this.maybeLoadPrevious(), 2000);
			})
			.always(() => {
				this.$loading.hide();
				this.$container.show();
			});
	}

	maybeLoadPrevious() {
		let evtName = 'scroll.ps-page-messages';

		this.$scrollable.off(evtName);
		this.$scrollable.on(evtName, () => {
			if (this.$scrollable[0].scrollTop < 30) {
				this.$scrollable.off(evtName);
				this.loadPrevious().then(data => {
					if (data.html) {
						this.maybeLoadPrevious();
					}
				});
			}
		});
	}

	loadPrevious() {
		return new Promise((resolve, reject) => {
			this.$messagesLoading.css('visibility', '');

			let $first = this.$messagesList.children('.ps-js-message').first();
			let params = {
				msg_id: this.params.msg_id,
				from_id: $first.data('id'),
				direction: 'old',
				get_unread: 1
			};

			this.fetch(params)
				.done(data => {
					this.render(data, 'prepend');

					setTimeout(() => resolve(data), 1000);
				})
				.fail(reject)
				.always(() => {
					this.$messagesLoading.css('visibility', 'hidden');
				});
		});
	}

	loadNext() {
		let $last = this.$messagesList.children('.ps-js-message').last();
		let params = {
			msg_id: this.params.msg_id,
			from_id: $last.data('id'),
			direction: 'new',
			get_unread: 1
		};

		this.loadNextPromise && this.loadNextPromise.abort();
		this.loadNextPromise = this.fetch(params);
		this.loadNextPromise.done(data => {
			let $temporary = this.$messagesTemporary.children('.ps-js-temporary-message');
			if (data.ids && data.ids.length) {
				$temporary = $temporary.slice(0, data.ids.length);
			}

			$temporary.remove();

			this.render(data);

			// Restart polling if necessary.
			if (!!data.currently_typing || (data.ids && data.ids.length)) {
				this.restartLongPolling();
			}
		});

		return this.loadNextPromise;
	}

	render(data, method = 'append') {
		let scrollable = this.$scrollable[0],
			scrollDistanceFromBottom,
			scrollDistanceFromTop,
			firstMessage;

		// Do NOT render on a destroyed instance.
		if (this.destroyed) {
			return;
		}

		// Calculate scroll distance before new messages are being inserted.
		if ((data.ids && data.ids.length) || !!data.currently_typing) {
			if ('append' === method) {
				scrollDistanceFromBottom = Math.abs(
					scrollable.scrollHeight - scrollable.clientHeight - scrollable.scrollTop
				);
			} else if ('prepend' === method) {
				// There is an extra padding on the scrollable children.
				let extraPadding = getComputedStyle(scrollable.firstElementChild).paddingTop;
				extraPadding = parseInt(extraPadding) || 0;

				scrollDistanceFromTop =
					this.$messagesLoading.outerHeight() + extraPadding - scrollable.scrollTop;
				firstMessage = this.$messagesList[0].firstElementChild;
			}
		}

		// Insert messages if provided.
		if (data.html) {
			let $filtered = filterMessages($(data.html));

			if ('append' === method) {
				this.$messagesList.append($filtered);
			} else if ('prepend' === method) {
				this.$messagesList.prepend($filtered);
			}
		}

		// Update participants if provided.
		if (data.html_participants) {
			this.$participants.html(data.html_participants);
		}

		// Update options if provided.
		if (data.html_options) {
			this.$optionsDropdown.html(data.html_options);
		}

		// Update read/unread checkmarks if enabled.
		if ('undefined' !== typeof data.receipt) {
			let sendReceipt = +data.receipt;
			if (sendReceipt) {
				let unreadCount = +data.unread || 0;
				this.showUnreadCheckmark(unreadCount);
			}
		}

		// Update currently typing notice if provided.
		if (!data.currently_typing || (data.ids && data.ids.length)) {
			this.$messagesTyping.empty();
		} else {
			this.$messagesTyping.html(data.currently_typing);
		}

		// Scroll to the bottom of the box if necessary.
		if ('append' === method && scrollDistanceFromBottom < 30) {
			if ((data.ids && data.ids.length) || !!data.currently_typing) {
				requestAnimationFrame(() => {
					scrollable.scrollTop = scrollable.scrollHeight;
					// Scroll again after async contents are all loaded.
					loadAsyncContents(data.html).then(() => {
						scrollable.scrollTop = scrollable.scrollHeight;
					});
				});
			}
		}
		// Or, maintain message position on scrollable area if necessary.
		else if ('prepend' === method) {
			let scrollTop = firstMessage ? $(firstMessage).position().top : 0;
			scrollable.scrollTop = Math.max(0, scrollTop - scrollDistanceFromTop);
		}
	}

	initPostbox() {
		let $postbox = this.$container
			.find('div#postbox-message')
			.html(this.$postboxTemplate.html());

		let params = null;

		this.$postbox = $postbox.pspostbox({
			text_length: CHARS_LIMIT,
			send_button_text: TEXT_BTN_SEND,
			save_url: 'messagesajax.add_message',
			postbox_req: req => {
				this.isSaving = true;
				req.parent_id = this.params.msg_id;

				// Save reference to the request parameter's object so that
				// it can be used later.
				params = req;

				return req;
			},
			on_before_save: () => {
				let attachment;

				switch (params.type) {
					case 'photo':
						attachment = { type: 'photo', count: params.files.length };
						break;
					case 'giphy':
						attachment = { type: 'giphy', count: 1 };
						break;
				}

				this.$postbox.find('.ps-postbox-input:visible textarea').val('');
				this.addTemporaryContent(params.content, attachment);
				this.$scrollable[0].scrollTop = this.$scrollable[0].scrollHeight;
			},
			on_save: () => this.loadNext(),
			on_queue_clear: () => {
				this.isSaving = false;
				this.$postbox.$posttabs.on_cancel();
			}
		});

		// Do NOT disable postbox while submitting the post.
		this.$postbox.$posttabs.off('peepso_posttabs_submit');

		this.$enterToSend = this.$postbox.find('#enter-to-send');
		this.$enterToSend[0].checked = this.enterToSend;

		this.$enterToSend.on('click', () => {
			this.$postbox.on_change();
			ajax.post('messagesajax.enter_to_send', {
				enter_to_send: this.$enterToSend.is(':checked') ? 1 : 0
			});
		});

		this.$postbox.$textarea.on(
			'focus click',
			throttle(function () {
				ajax.post('messagesajax.mark_read_messages_in_conversation', {
					msg_id: this.params.msg_id
				});
			}, 2000).bind(this)
		);

		this.$postbox.on('postbox.post_cancel postbox.post_saved', e => {
			if (this.$postbox) {
				this.$postbox.$textarea.trigger('keyup').trigger('input');
			}
		});

		observer.addFilter('peepso_postbox_enter_to_send', () => {
			return this.$enterToSend.is(':checked');
		});

		observer.addFilter(
			'peepso_postbox_input_changed',
			(val, postbox) => {
				if (postbox === this.$postbox) {
					currentlyTyping(this.params.msg_id);
				}
			},
			10,
			2
		);
	}

	initOptions() {
		let $doc = $(document);
		let evtName = 'click.conversation-options';

		// Toggle dropdown.
		this.$options.off(evtName).on(evtName, e => {
			e.stopPropagation();

			if (this.$optionsDropdown.is(':visible')) {
				this.$optionsDropdown.hide();
				$doc.off(evtName);
				return;
			}

			this.$optionsDropdown.show();
			$doc.one(evtName, () => this.$optionsDropdown.hide());
		});

		// Handle dropdown menu.
		this.$optionsDropdown.off('click').on('click', '[data-menu]', e => {
			e.preventDefault();

			let $menu = $(e.currentTarget);

			switch ($menu.data('menu')) {
				case 'block-user':
					this.blockUser($menu.data('warningText'), $menu.data('userId'));
					break;
				case 'add-recipients':
					this.addRecipients();
					break;
				case 'toggle-read-receipt':
					this.toggleReadReceipt(!+$menu.data('send'), $menu[0]);
					break;
				case 'toggle-mute':
					this.toggleMute(!+$menu.data('muted'), $menu[0]);
					break;
				case 'leave-conversation':
					this.leaveConversation($menu.data('warningText'), $menu.attr('href'));
					break;
			}
		});
	}

	initRecipientsForm() {
		let avatars = {};

		// Render Selectize selected item.
		function selectizeRenderItem(item, escape) {
			let name = escape(item.display_name),
				avatar = escape(item.avatar || avatars[item.id] || '');

			return `<div><img src="${avatar}" /><span>${name}</span></div>`;
		}

		// Render Selectize option.
		function selectizeRenderOption(item, escape) {
			let name = escape(item.display_name),
				avatar = escape(item.avatar || avatars[item.id] || '');

			return `<div><img src="${avatar}" /><span>${name}</span></div>`;
		}

		this.$recipients.find('select[name=recipients]').selectize({
			valueField: 'id',
			labelField: 'display_name',
			searchField: 'display_name',
			plugins: ['remove_button'],
			closeAfterSelect: true,
			render: {
				option: selectizeRenderOption,
				item: selectizeRenderItem
			},
			load: (query, callback) => {
				ajax.post('messagesajax.get_available_recipients', {
					parent_id: this.params.msg_id,
					keyword: query
				}).done(json => {
					let recipients;

					if (json.success) {
						recipients = json.data.available_participants || [];
						// Update avatars cache.
						$.each(recipients, function (id, user) {
							avatars[user.id] = user.avatar;
						});
					}

					callback(recipients);
				});
			},
			onInitialize: function () {
				let loading = `<img src="${this.$input.data('loading')}" />`;

				this.$wrapper.append(loading);
				this.$control_input.on('input', function (e) {
					let $input = $(this);
					setTimeout(function () {
						$input.trigger('keyup');
					}, 0);
				});
			}
		});

		this.$addRecipients.off('click').on('click', () => {
			let $select = this.$recipients.find('select[name=recipients]');
			let $nonce = this.$recipients.find('select[name=add-participant-nonce]');
			let params = {
				parent_id: this.params.msg_id,
				participants: $select.val(),
				add_participant_nonce: $nonce.val()
			};

			ajax.post('messagesajax.add_participants', params).done(json => {
				if (json.success) {
					let redirect = json.data.new_conversation_redirect;
					if (redirect) {
						window.location = redirect;
						window.location.reload();
						return;
					}

					this.$participants.html(json.data.summary);
					dialog(json.notices[0]).show().autohide();

					// Update selectbox options.
					$select.find('option').remove();
					$.each(json.data.available_participants, function (id, user) {
						let $option = $('<option/>').val(user.id).text(user.display_name);
						$select.append($option);
					});

					$select[0].selectize.clearOptions(true);

					this.$recipients.slideUp();
				}
			});
		});
	}

	showUnreadCheckmark(unreadCount) {
		let $readCheckmarks = this.$messagesList.find('.gci-check-circle'),
			$unreadCheckmarks = $();

		if (unreadCount > 0) {
			$unreadCheckmarks = $readCheckmarks.slice(0 - unreadCount);
			$readCheckmarks = $readCheckmarks.not($unreadCheckmarks);
		}

		$readCheckmarks.addClass('read');
		$unreadCheckmarks.removeClass('read');
	}

	blockUser(msg, userId) {
		dialog(msg).confirm(confirmed => {
			if (confirmed) {
				window.ps_member.block_user(userId);
			}
		});
	}

	addRecipients() {
		this.$recipients.slideDown();
	}

	addTemporaryContent(content, attachment) {
		let message = template(this.$messageTemplate.text())({ content, attachment });
		this.$messagesTemporary.append(message);
	}

	toggleReadReceipt(send, btn) {
		let params = { msg_id: this.params.msg_id, read_notif: send ? 1 : 0 };

		ajax.post('messagesajax.set_message_read_notification', params).done(json => {
			if (json.success) {
				// Update button if necessary.
				if (btn instanceof Element) {
					let $btn = $(btn);
					$btn.data('send', send ? 1 : 0);
					$btn.removeClass('disabled').addClass(send ? '' : 'disabled');
					$btn.find('span').text($btn.data(`${send ? 'dontSend' : 'send'}Text`));
				}

				if (send) {
					ajax.post('messagesajax.mark_read_messages_in_conversation', {
						msg_id: this.params.msg_id
					});
				}
			}
		});
	}

	toggleMute(mute, btn) {
		if (mute) {
			let popup = dialog(TEMPLATE_MUTE.replace('{msg_id}', this.params.msg_id)).show();
			let $radios = popup.$el.find('input[type=radio]');
			let $btn = popup.$el.find('input[type=button]');
			$btn.removeAttr('onclick');
			$btn.on('click', e => {
				e.preventDefault();
				this.toggleMuteConfirm($radios.filter(':checked').val(), btn);
				popup.hide();
			});
		} else {
			this.toggleMuteConfirm(0, btn);
		}
	}

	toggleMuteConfirm(muteHours, btn) {
		let params = { parent_id: this.params.msg_id, mute: muteHours };
		let mute = !!+muteHours;

		ajax.post('messagesajax.set_mute', params).done(json => {
			if (json.success) {
				// Update button if necessary.
				if (btn instanceof Element) {
					let $btn = $(btn);
					$btn.data('muted', mute ? 1 : 0);
					$btn.find('span').text($btn.data(`${mute ? 'muted' : 'unmuted'}Text`));
					$btn.find('i').attr('class', mute ? 'gcis gci-bell-slash' : 'gcir gci-bell');
				}

				observer.doAction(
					`psmessages_conversation_${mute ? '' : 'un'}mute`,
					params.parent_id
				);
			}
		});
	}

	leaveConversation(msg, redirect) {
		dialog(msg).confirm(confirmed => {
			if (confirmed) {
				window.location = redirect;
			}
		});
	}

	destroy() {
		this.stopLongPolling();

		this.$container.hide();

		// Reset add recipients form.
		this.$addRecipients.off('click');
		this.$recipients.hide();
		(function (selectize) {
			selectize && selectize.destroy();
		})(this.$recipients.find('select[name=recipients]')[0].selectize);

		// Reset conversation thread.
		this.hide();
		this.$messagesList.children().remove();

		// Reset postbox.
		this.$container.find('div#postbox-message').empty();
		this.$postbox = null;

		// Assign a "destroyed" flag.
		this.destroyed = true;
	}

	startLongPolling() {
		this.longPollingToken = new Date().getTime();

		let looperToken = this.longPollingToken;
		let looper = delay => {
			this.longPollingTimer = setTimeout(() => {
				this.loadNext().always(() => {
					if (this.destroyed) {
						console.log(
							`Requested conversation thread (${this.params.msg_id}) is no longer exist. ` +
								`Terminate corresponding long polling loop!`
						);
					} else if (looperToken !== this.longPollingToken) {
						console.log(`Different token. Terminate corresponding long polling loop!`);
					} else {
						// Check whether current conversation is still exist.
						let nextDelay = Math.min(
							+POLLING_DELAY_MULTIPLIER * delay,
							+POLLING_DELAY_MAX
						);
						looper(nextDelay);
					}
				});
			}, delay);
		};

		looper(+POLLING_DELAY_MIN);
	}

	stopLongPolling() {
		clearTimeout(this.longPollingTimer);
		this.longPollingToken = null;
	}

	restartLongPolling() {
		this.stopLongPolling();
		this.startLongPolling();
	}
}

export default MessageConversation;
