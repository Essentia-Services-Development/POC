import $ from 'jquery';
import { debounce } from 'underscore';
import peepso, { observer, hooks } from 'peepso';
import { messages as messagesData } from 'peepsodata';

// Init beep sound player.
(function () {
	let playSound = function () {
		let beep = new Audio(messagesData.sound_beep);
		beep.volume = 0.7;
		playSound = debounce(function () {
			console.log('peepso.beep');
			try {
				beep.play();
			} catch (e) {}
		}, 1000);
		playSound();
	};

	// Trigger beep sound when receiving new message while browser is in the background.
	hooks.addAction('chat_unread_new', 'message', function () {
		if (!document.hasFocus()) {
			playSound();
		}
	});
})();

(function (factory) {
	window.ps_messages = peepso.messages = factory();
})(function () {
	/*
	 * PeepSo Messages class
	 * @package PeepSo
	 * @author PeepSo
	 */

	function PsMessages() {
		this.$recipient_select = null;
		this.query = '';
		this.page = { inbox: 1, sent: 1 };
		this.ajax_req = false;
		this.enter_to_send = false;
		this.unread = undefined;
	}

	var ps_messages = new PsMessages();

	// Avatars cache.
	var avatars = {};

	// Render Selectize selected item.
	function selectizeRenderItem(item, escape) {
		var name = escape(item.display_name),
			avatar = escape(item.avatar || avatars[item.id] || '');

		return '<div><img src="' + avatar + '" /><span>' + name + '</span></div>';
	}

	// Render Selectize option.
	function selectizeRenderOption(item, escape) {
		var name = escape(item.display_name),
			avatar = escape(item.avatar || avatars[item.id] || '');

		return '<div><img src="' + avatar + '" /><span>' + name + '</span></div>';
	}

	/**
	 * Runs when viewing a conversation, sets up events for adding recipients and the message postbox.
	 * @param  {int} conversation_id
	 */
	PsMessages.prototype.init_conversation_view = function (conversation_id) {
		$('#add-recipients-toggle').on('click', function (e) {
			e.preventDefault();
			$('.ps-js-recipients').slideToggle();
		});

		$('#recipients-search').selectize({
			valueField: 'id',
			labelField: 'display_name',
			searchField: 'display_name',
			plugins: ['remove_button'],
			closeAfterSelect: true,
			render: {
				option: selectizeRenderOption,
				item: selectizeRenderItem
			},
			load: function (query, callback) {
				peepso.postJson(
					'messagesajax.get_available_recipients',
					{
						parent_id: conversation_id,
						keyword: query
					},
					function (response) {
						var recipients;

						if (response.success) {
							recipients = response.data.available_participants || [];
							// Update avatars.
							$.each(recipients, function (id, user) {
								avatars[user.id] = user.avatar;
							});
						}

						callback(recipients);
					}
				);
			},
			onInitialize: function () {
				var loading = '<img src="' + this.$input.data('loading') + '" />';
				this.$wrapper.append(loading);
				this.$control_input.on('input', function (e) {
					var $input = $(this);
					setTimeout(function () {
						$input.trigger('keyup');
					}, 0);
				});
			}
		});

		this.$postbox = $('div#postbox-message').pspostbox({
			text_length: peepsomessagesdata.character_limit,
			send_button_text: peepsomessagesdata.send_button_text,
			save_url: 'messagesajax.add_message',
			postbox_req: function (req) {
				ps_messages.saving = true;
				req.parent_id = conversation_id;
				return req;
			},
			on_before_save: function () {
				var $textarea = $('.ps-postbox-input:visible textarea');
				$textarea.val('');
			},
			on_save: function (response) {
				if (!ps_messages.polling) {
					ps_messages.polling = true;
					ps_messages.load_next_in_conversation(conversation_id, 'new');
				}
			},
			on_queue_clear: function () {
				ps_messages.saving = false;
				ps_messages.$postbox.$posttabs.on_cancel();
			}
		});

		// send mark-as-read request on textarea focus
		this.$postbox.$textarea.on(
			'focus click',
			_.throttle(function () {
				peepso
					.disableAuth()
					.disableError()
					.postJson('messagesajax.mark_read_messages_in_conversation', {
						msg_id: conversation_id
					});
			}, 2000)
		);

		observer.addFilter('peepso_postbox_enter_to_send', function () {
			return ps_messages.enter_to_send;
		});

		observer.addFilter('peepso_postbox_input_changed', function (val) {
			ps_messages.currently_typing();
		});

		// cache doms
		ps_messages.$conversation = $('.ps-chat__messages');
		ps_messages.$loading = $('.ps-js-loading', ps_messages.$conversation);
		ps_messages.$typing = $('.ps-js-currently-typing', ps_messages.$conversation);
		ps_messages.$entersend = $('.ps-js-checkbox-entertosend');
		ps_messages.$doc = $(document);

		ps_messages.$conversation.data({ conversation_id: conversation_id });

		if (ps_messages.$conversation.length) {
			// Apply infinite-scroll functionality on conversation area.
			ps_messages.$conversation
				.css({ maxHeight: '60vh', overflow: 'auto' })
				.off('scroll')
				.on('scroll', ps_messages.on_conversation_scroll)
				.bind('mousewheel', function (e, d) {
					var t = $(e.currentTarget);
					if (d > 0 && t.scrollTop() === 0) {
						e.preventDefault();
					} else if (d < 0 && t.scrollTop() == t.get(0).scrollHeight - t.innerHeight()) {
						e.preventDefault();
					}
				});

			// Load initial conversation.
			ps_messages.polling = true;
			ps_messages.load_next_in_conversation(conversation_id);
			ps_messages.init_long_polling(1500);
		}

		ps_messages.enter_to_send = ps_messages.$entersend[0].checked;
		ps_messages.$entersend.on('click', ps_messages.toggle_entertosend);

		observer.addFilter('psmessages_conversation_view', function () {
			return true;
		});

		observer.addAction(
			'psmessages_conversation_mute',
			msg_id => {
				if (+msg_id === +conversation_id) {
					let $btns = $('.ps-js-btn-mute-conversation');
					$btns.attr(
						'onclick',
						'return ps_messages.unmute_conversation(' + msg_id + ', 0);'
					);
					$btns.find('span').text(peepsomessagesdata.unmute_conversation);
					let $icons = $btns.find('i');
					$icons.attr('class', 'gcis gci-bell-slash');
				}
			},
			10,
			1
		);

		observer.addAction(
			'psmessages_conversation_unmute',
			msg_id => {
				if (+msg_id === +conversation_id) {
					let $btns = $('.ps-js-btn-mute-conversation');
					$btns.attr(
						'onclick',
						'return ps_messages.mute_conversation(' + msg_id + ', 1);'
					);
					$btns.find('span').text(peepsomessagesdata.mute_conversation);

					let $icons = $btns.find('i');
					$icons.attr('class', 'gcir gci-bell');
				}
			},
			10,
			1
		);

		var $blockUser = $('.ps-js-btn-blockuser');
		$blockUser.on('click', function (e) {
			if (confirm(peepsomessagesdata.blockuser_confirm_text)) {
				var userId = $(e.currentTarget).data('user-id');
				ps_member.block_user(userId);
			}
		});

		var $conversationOptions = $('.ps-js-conversation-options');
		var $conversationDropdown = $('.ps-js-conversation-dropdown');

		$conversationOptions.on('click', function (e) {
			$conversationDropdown.toggle();
			e.stopPropagation();
		});

		$conversationDropdown.on('click', function (e) {
			e.stopPropagation();
		});

		$(document).on('click', function () {
			$conversationDropdown.hide();
		});
	};

	/**
	 * Load next/previous messages in conversation.
	 * @param {int} conversation_id First/last loaded message.
	 * @param {string} direction Method to show newer or older message.
	 * @returns {jQuery.Deferred}
	 */
	PsMessages.prototype.load_next_in_conversation = function (conversation_id, direction) {
		return $.Deferred(
			$.proxy(function (defer) {
				var req = { msg_id: conversation_id },
					now = Math.floor(new Date().getTime() / 1000),
					$from;

				if (direction === 'new') {
					$from = ps_messages.$conversation.find('.ps-js-message').last();
					req.from_id = $from.data('id');
					req.direction = direction;
					req.get_unread = 1;
				} else if (direction === 'old') {
					$from = ps_messages.$conversation.find('.ps-js-message').first();
					ps_messages.$loading.show();
					req.from_id = $from.data('id');
					req.direction = direction;
				} else {
					ps_messages.$loading.show();
				}

				// check online status every minute
				if (!this.lastOnlineCheck || this.lastOnlineCheck < now - 60) {
					this.lastOnlineCheck = now;
					req.get_participants = 1;
				}

				peepso
					.disableAuth()
					.disableError()
					.postJson(
						'messagesajax.get_messages_in_conversation',
						req,
						function (response) {
							var scroll2bottom, newChatAdded, online, $messages;

							function removeDuplicate($messages) {
								$messages.children('.ps-js-message').each(function () {
									var $message = $(this),
										msgSelector = '.ps-js-message-' + $message.data('id');
									if (ps_messages.$conversation.find(msgSelector).length) {
										$message.remove();
									}
								});

								return $messages;
							}

							if (direction === 'old') {
								setTimeout(function () {
									var $oldTop;
									ps_messages.$loading.hide();
									if (
										response.success &&
										response.data.ids &&
										response.data.ids.length
									) {
										ps_messages.$conversation.stop();
										$oldTop = ps_messages.$loading.next();
										$messages = $('<div />').append(response.data.html);
										$messages = observer.applyFilters(
											'messages_render',
											$messages
										);
										observer.doAction('peepso_external_link', $messages);
										removeDuplicate($messages).insertAfter(
											ps_messages.$loading
										);
										ps_messages.$conversation
											.stop()
											.scrollTop(Math.max(0, $oldTop.position().top - 131));
										ps_messages.$doc.trigger('peepso_messages_list_displayed');
										ps_messages.$conversation
											.off('scroll')
											.on('scroll', ps_messages.on_conversation_scroll);
									}
								}, 1000);
							} else if (direction === 'new') {
								ps_messages.polling = false;
								if (response.success) {
									ps_messages.submit_entertosend ||
										ps_messages.toggle_entertosend(
											+response.data.enter_to_send
										);
									if (response.data.ids && response.data.ids.length) {
										$messages = $('<div />').append(response.data.html);
										$messages = observer.applyFilters(
											'messages_render',
											$messages
										);
										observer.doAction('peepso_external_link', $messages);
										removeDuplicate($messages).insertBefore(
											ps_messages.$typing
										);
										ps_messages.$doc.trigger('peepso_messages_list_displayed');
										newChatAdded = true;
										scroll2bottom = true;
									}
								}
							} else {
								ps_messages.$loading.hide();
								ps_messages.polling = false;
								if (response.success) {
									ps_messages.submit_entertosend ||
										ps_messages.toggle_entertosend(
											+response.data.enter_to_send
										);
									if (response.data.ids && response.data.ids.length) {
										$messages = $('<div />').append(response.data.html);
										$messages = observer.applyFilters(
											'messages_render',
											$messages
										);
										observer.doAction('peepso_external_link', $messages);
										removeDuplicate($messages).insertBefore(
											ps_messages.$typing
										);
										ps_messages.$doc.trigger('peepso_messages_list_displayed');
										newChatAdded = true;
										scroll2bottom = true;
									}
								}
							}

							if (response.success && response.data) {
								if (!response.data.currently_typing || newChatAdded) {
									ps_messages.$typing.empty();
								} else {
									ps_messages.$typing.html(response.data.currently_typing);
									scroll2bottom = true;
								}
							}

							// update online status
							if (response.data && response.data.users) {
								online = false;
								for (var i = 0; i < response.data.users.length; i++) {
									if (+response.data.users[i].online) {
										online = true;
										break;
									}
								}
								$('.ps-conversation__status').children().get(0).className = online
									? 'gcis gci-circle'
									: 'gcir gci-clock';
							}

							// update read notification
							if (response.data && 'undefined' !== typeof response.data.receipt) {
								var receipt = +response.data.receipt;
								var unread = +response.data.unread;
								if (
									ps_messages.receipt !== receipt ||
									ps_messages.unread !== unread
								) {
									ps_messages.receipt = receipt;
									ps_messages.unread = unread;
									if (receipt) {
										ps_messages.toggle_message_checkmark(unread);
									}
								}
							}

							if (
								response.data &&
								'undefined' !== typeof response.data.send_receipt
							) {
								var send_receipt = +response.data.send_receipt;
								if (ps_messages.send_receipt !== send_receipt) {
									ps_messages.send_receipt = send_receipt;
									ps_messages.update_checkmark(conversation_id, send_receipt);
								}
							}

							if (scroll2bottom) {
								ps_messages.conversation_scroll2bottom();
							}

							defer.resolve(!!newChatAdded);
						}
					);
			}, this)
		);
	};

	/**
	 * Load older messages upon scrolling to earliest message.
	 */
	PsMessages.prototype.on_conversation_scroll = _.debounce(function () {
		if (ps_messages.$conversation[0].scrollTop <= 10) {
			ps_messages.$conversation.off('scroll');
			ps_messages.load_next_in_conversation(
				ps_messages.$conversation.data('conversation_id'),
				'old'
			);
		}
	}, 200);

	/**
	 * Scroll conversation window all the way down.
	 */
	PsMessages.prototype.conversation_scroll2bottom = function () {
		ps_messages.$conversation.stop().scrollTop(ps_messages.$conversation[0].scrollHeight);
	};

	/**
	 * Initialize long polling to check for new messages every defined interval time.
	 * @param {int} interval Polling interval in milliseconds
	 */
	PsMessages.prototype.init_long_polling = function (interval) {
		this.smart_long_polling();
	};

	/**
	 * Smart timing long polling.
	 * @param {number} delay - Initial long polling delay (in milliseconds).
	 */
	PsMessages.prototype.smart_long_polling = function (delay) {
		var msgId = this.$conversation.data('conversation_id'),
			minDelay = +peepsodata.notification_ajax_delay_min,
			maxDelay = +peepsodata.notification_ajax_delay,
			multiplier = +peepsodata.notification_ajax_delay_multiplier,
			initialDelay = Math.max(delay || 0, minDelay);

		var doPolling = $.proxy(function (delay) {
			clearTimeout(this.pollingTimer);
			this.pollingTimer = setTimeout(
				$.proxy(function () {
					this.load_next_in_conversation(msgId, 'new').done(function (hasUpdate) {
						if (hasUpdate) {
							delay = initialDelay;
						} else {
							delay = Math.min(delay * multiplier, maxDelay);
						}
						doPolling(delay);
					});
				}, this),
				delay
			);
		}, this);

		doPolling(initialDelay);

		observer.addAction('browser.inactive', function () {
			doPolling(maxDelay);
		});

		observer.addAction('browser.active', function () {
			doPolling(minDelay);
		});
	};

	/**
	 * TODO: docblock
	 */
	PsMessages.prototype.currently_typing = _.throttle(function () {
		peepso
			.disableAuth()
			.disableError()
			.postJson('messagesajax.i_am_typing', {
				msg_id: ps_messages.$conversation.data('conversation_id')
			});
	}, +peepsodata.notification_ajax_delay_min || 5000);

	/**
	 * Set state based on checkbox status
	 * @param {boolean|event} Flag or click event on checkbox
	 */
	PsMessages.prototype.toggle_entertosend = function (data) {
		var checkbox = ps_messages.$entersend[0];
		var checked = +data;
		var clicked = false;

		if (data && data.target) {
			clicked = true;
			checked = +data.target.checked;
			ps_messages.submit_entertosend = true;
			peepso
				.disableAuth()
				.disableError()
				.postJson('messagesajax.enter_to_send', { enter_to_send: checked }, function () {
					ps_messages.submit_entertosend = false;
				});
		}

		if (clicked || checked !== +checkbox.checked) {
			checkbox.checked = ps_messages.enter_to_send = checked ? true : false;
			ps_messages.$postbox.on_change();
		}
	};

	/**
	 * Show the new message dialog box
	 * @param  {int} user_id User ID
	 */
	PsMessages.prototype.new_message = function (user_id, flag, btn) {
		// open chat
		if (window.ps_chat && peepso.screenSize() === 'large' && btn) {
			btn = $(btn);
			var loading = btn.find('img').css('display', 'inline');
			peepso.postJson('chatajax.get_chat_with', { recipient: user_id }, function (response) {
				if (response.success) {
					ps_chat.open_chat(response.data.msg_id);
				}
				setTimeout(function () {
					loading.hide();
				}, 1000);
			});
			return;
		}

		var that = this;
		var $new_message_div = $('<div />').append(peepsomessagesdata.template);
		var inst = pswindow.show(
			$new_message_div.find('.dialog-title').html(),
			$new_message_div.find('.dialog-content').html()
		);
		var elem = inst.$container.find('.ps-dialog');

		var dlgBeforeClose;
		observer.addAction(
			'pswindow_before_close',
			(dlgBeforeClose = function ($dialog) {
				observer.removeAction('pswindow_before_close', dlgBeforeClose);
				observer.doAction('psmessage_new_message_close', $dialog);
			}),
			10,
			1
		);

		elem.addClass('ps-dialog-wide');
		observer.addFilter(
			'pswindow_close',
			function () {
				elem.removeClass('ps-dialog-wide');
			},
			10,
			1
		);

		var $message_postbox = (this.$message_postbox = $(
			'#cWindowContent div.ps-postbox-message'
		).pspostbox({
			autosize: true,
			text_length: peepsomessagesdata.character_limit,
			save_url: 'messagesajax.new_message',
			send_button_text: peepsomessagesdata.send_button_text,
			postbox_req: function (req) {
				var message = req.content;

				return {
					subject: '',
					message: message,
					recipients: that.$recipient_select.val()
				};
			},
			on_save: function (response) {
				pswindow.hide().show('', response.notices[0]).fade_out(pswindow.fade_time);

				observer.removeFilter('beforeunload', $message_postbox.beforeUnloadHandler);
				$(window).trigger('peepso_messages_after_send', [response]);
			},
			on_error: function (response) {
				alert(response.errors[0]);
			}
		}));

		this.$recipient_single = $('#ps-window .ps-js-recipient-single').hide();
		this.$recipient_multiple = $('#ps-window .ps-js-recipient-multiple').hide();
		this.$recipient_select = $('select[name=recipients]', this.$recipient_multiple);

		// #5285 Fix iOS issue with on-the-fly DOM tree modification.
		$message_postbox.$textarea.triggerHandler('input');

		this.get_available_recipients(
			user_id,
			$.proxy(function (available_recipients) {
				var selected, filtered;

				if (!available_recipients instanceof Array) {
					available_recipients = [];
				}

				if (flag) {
					filtered = available_recipients.filter(function (user) {
						return user[flag];
					});
					if (filtered.length) {
						available_recipients = filtered;
					}
				}

				$.each(available_recipients, function (id, user) {
					avatars[user.id] = user.avatar;

					var option = $('<option/>').val(user.id).text(user.display_name);

					that.$recipient_select.append(option);

					if (user_id === parseInt(user.id)) {
						option.attr('selected', 'selected');
						selected = user;
					}
				});

				if (undefined !== user_id) {
					$('option[value!=' + user_id + ']', this.$recipient_select).remove();
					if (selected) {
						this.$recipient_single
							.find('img')
							.attr('src', selected.avatar)
							.attr('alt', selected.display_name);
						this.$recipient_single.find('.ps-comment-user').html(selected.display_name);
						this.$recipient_single.find('a').attr('href', selected.url);
						this.$recipient_single.show();
					}
				} else {
					this.$recipient_multiple.show();
					this.$recipient_select.selectize({
						valueField: 'id',
						labelField: 'display_name',
						searchField: 'display_name',
						plugins: ['remove_button'],
						closeAfterSelect: true,
						render: {
							option: selectizeRenderOption,
							item: selectizeRenderItem
						},
						load: function (query, callback) {
							peepso.postJson(
								'messagesajax.get_available_recipients',
								{
									keyword: query
								},
								function (response) {
									var recipients;

									if (response.success) {
										recipients = response.data.available_participants || [];
										// Update avatars.
										$.each(recipients, function (id, user) {
											avatars[user.id] = user.avatar;
										});
									}

									callback(recipients);
								}
							);
						},
						onInitialize: function () {
							var loading = '<img src="' + this.$input.data('loading') + '" />';
							this.$wrapper.append(loading);
							this.$control_input.on('input', function (e) {
								var $input = $(this);
								setTimeout(function () {
									$input.trigger('keyup');
								}, 0);
							});
						},
						onChange: function (value) {
							that.toggle_send_button();
							this.$control_input.blur();
						}
					});
				}

				this.toggle_send_button();
			}, this)
		);
	};

	/**
	 *
	 */
	PsMessages.prototype.compact_map = function ($el) {
		if ($el.closest('.ps-dialog').length > 0) {
			$el.find('.ps-postbox__location').addClass('ps-postbox__location--loaded');
		}
	};

	/**
	 * Get available recipients.
	 */
	PsMessages.prototype.get_available_recipients = function (user_id, callback) {
		if (user_id && this.available_recipients && this.available_recipients[user_id]) {
			callback(this.available_recipients[user_id]);
		} else {
			var $loading = $('#ps-window .ps-js-recipient-loading').show();
			peepso.postJson(
				'messagesajax.get_available_recipients',
				{ user_id: user_id },
				$.proxy(function (response) {
					$loading.hide();
					if (response.success) {
						this.available_recipients = this.available_recipients || {};
						this.available_recipients[user_id] = response.data.available_participants;
						callback(this.available_recipients[user_id]);
					} else {
						callback([]);
					}
				}, this)
			);
		}
	};

	/**
	 * Toggle send message button.
	 */
	PsMessages.prototype.toggle_send_button = function () {
		// #4886 - Select multiple will return empty array that evaluated as true.
		var recipients = this.$recipient_select.val();
		if (recipients && !recipients.length) {
			recipients = null;
		}

		if (!recipients) {
			this.$message_postbox.$save_button._detached =
				this.$message_postbox.$save_button.parent();
			this.$message_postbox.$save_button.detach();
		} else if (this.$message_postbox.$save_button._detached) {
			this.$message_postbox.$save_button.appendTo(
				this.$message_postbox.$save_button._detached
			);
			this.$message_postbox.$save_button._detached = false;
		}
	};

	/**
	 * Add recipients to an exisiting conversation.
	 * @param {int} conversation_id A message ID
	 */
	PsMessages.prototype.add_recipients = function (conversation_id) {
		var req = {
			parent_id: conversation_id,
			participants: $('#recipients-search').val(),
			add_participant_nonce: $("input[name='add-participant-nonce']").val()
		};

		var $button = $('.ps-js-recipients .ps-btn-success');
		var $loading = $button.find('img');

		$button.attr('disabled', 'disabled');
		$loading.show();

		peepso.postJson('messagesajax.add_participants', req, function (response) {
			if (response.success) {
				if (response.data.new_conversation_redirect) {
					window.location = response.data.new_conversation_redirect;
					return;
				}

				$loading.hide();
				$button.removeAttr('disabled');

				$('.ps-js-participant-summary').html(response.data.summary);
				pswindow.show('', response.notices[0]);

				$('#recipients-search option').remove();

				$.each(response.data.available_participants, function (id, user) {
					option = $('<option/>').val(user.id).text(user.display_name);

					$('#recipients-search').append(option);
				});

				$('#recipients-search').trigger('chosen:updated');
				$('.ps-js-recipients').slideToggle();
			} else {
				$loading.hide();
				$button.removeAttr('disabled');
			}
		});
	};

	/**
	 * Checks all grouped checkboxes depending on elem state
	 * @param {object} elem Checkbox HTML object element
	 */
	PsMessages.prototype.toggle_checkboxes = function (elem) {
		var $toggler = $(elem);
		$toggler
			.parents('.ps-messages')
			.find("input[type='checkbox']")
			.prop('checked', $toggler.prop('checked'));
	};

	/**
	 * Loads messages (in HTML list format) via ajax
	 * @param {string} type [inbox | sent]
	 * @param {int} page The page number
	 * @param {string} query The SQL query
	 */
	PsMessages.prototype.load_messages = function (type, page, query) {
		var _self = this;
		var req = {
			type: type,
			page: page,
			per_page: peepsomessagesdata.per_page,
			query: query
		};

		var $container = $('#' + type);

		$('.ps-js-loading', $container).show();

		peepso.postJson('messagesajax.get_messages', req, function (response) {
			$('.ps-js-loading', $container).hide();
			if (response.success) {
				var $messages = $(response.data.html);

				// Adjust search form.
				if (query) {
					$messages.find('input[name=query]').val(query);
					$messages.find('button[type=reset]').show();
				} else {
					$messages.find('input[name=query]').val('');
					$messages.find('button[type=reset]').hide();
				}

				// Filter content.
				$messages = observer.applyFilters('messages_render', $messages);

				$messages.hide();
				$container.html($messages);
				$messages
					.fadeIn('slow')
					// Register load and prev events, only once to prevent sending multiple requests
					.one('click', '.ps-js-prev', function (e) {
						e.preventDefault();
						--page;

						if (page <= 0) {
							page++;
							return;
						}

						_self.load_messages(type, page, query);
					})
					.one('click', '.ps-js-next', function (e) {
						e.preventDefault();
						++page;
						if (page > response.data.total_pages) {
							page--;
							return;
						}
						_self.load_messages(type, page, query);
					})
					.on('click', '.ps-js-bulk-actions', function (e) {
						_self.apply_bulk_action(e.target, type, page);
					});

				$($messages, '.ps-js-messages-search-form').on('reset submit', function (e) {
					var $form = $(this),
						$query = $form.find('input[name=query]');

					if ('reset' === e.type) {
						$query.val('');
					}

					_self.load_messages(type, 1, $query.val());
				});

				$(document).trigger('peepso_messages_list_displayed');
			}
		});
	};

	/**
	 * Applies bulk actions via AJAX then reloads the current list.
	 * @param {string|object} elem Checkbox HTML object element
	 * @param {string} type [inbox | sent]
	 * @param {int} page The page number
	 */
	PsMessages.prototype.apply_bulk_action = function (elem, type, page) {
		var $form = $(elem).closest('form');
		var data = $form.serialize();

		var $checkboxes = $('.ps-js-messages-list .ps-js-messages-list-item input:checked');
		if (!$checkboxes.length) {
			alert(messagesData.text_bulk_no_items);
			return;
		}

		var action = $form.find('select[name=action]').val();
		if (!action) {
			alert(messagesData.text_bulk_action);
			return;
		}

		if (this.ajax_req) {
			return;
		}

		if ('delete' === action) {
			if (confirm(messagesData.text_bulk_delete_confirm)) {
				this._bulk_action(data, type, page);
			}
		} else {
			this._bulk_action(data, type, page);
		}
	};

	/**
	 * Applies bulk actions via AJAX then reloads the current list.
	 * @param {string} data Serialized data
	 * @param {string} type [inbox | sent]
	 * @param {int} page The page number
	 */
	PsMessages.prototype._bulk_action = function (data, type, page) {
		var _self = this;
		peepso.postJson('messagesajax.bulk_action', data, function () {
			_self.ajax_req = false;
			ps_messages.load_messages(type, page);
		});

		return true;
	};

	/**
	 * Displays the list of participants and hides the summary.
	 */
	PsMessages.prototype.show_long_participants = function () {
		$('#summary-participants').hide();
		$('#long-participants').fadeIn();
	};

	/**
	 * Displays a confirmation dialog before deleting a single message
	 * @param {int} msg_id ID from to-be-deleted message.
	 * @return boolean Returns false, to prevent the default redirect.
	 */
	PsMessages.prototype.delete_single_message = function (msg_id) {
		pswindow.confirm_delete(function () {
			pswindow.hide();
			peepso.postJson(
				'messagesajax.delete_from_conversation',
				{ msg_id: msg_id },
				function (response) {
					if (response.success) {
						$('.ps-js-message-' + msg_id).remove();
					}
				}
			);
		});

		return false;
	};

	/**
	 * Opens an image in a popup
	 * @param  {string} src The img src
	 */
	PsMessages.prototype.open_image = function (src) {
		peepso.lightbox(
			[{ content: '<div style="display:inline-block;"><img src="' + src + '" /></div>' }],
			{
				simple: true
			}
		);
	};

	/**
	 * Toggle message checkmark
	 * @param {int} msg_id ID from conversation.
	 */
	PsMessages.prototype.toggle_checkmark = function (msg_id, status) {
		ps_messages.update_checkmark(msg_id, status);

		peepso
			.disableAuth()
			.disableError()
			.postJson(
				'messagesajax.set_message_read_notification',
				{
					msg_id: msg_id,
					read_notif: +status
				},
				function (response) {
					if (response.success) {
						if (status) {
							peepso
								.disableAuth()
								.disableError()
								.postJson('messagesajax.mark_read_messages_in_conversation', {
									msg_id: msg_id
								});
						}
					}
				}
			);

		return false;
	};

	PsMessages.prototype.update_checkmark = function (msg_id, status) {
		var $chatbox = $('.ps-chat__messages'),
			$btns = $('.ps-js-btn-toggle-checkmark'),
			$icons = $btns.find('i'),
			show = +status;

		if (status) {
			$btns.attr('onclick', 'return ps_messages.toggle_checkmark(' + msg_id + ', 0);');
			$btns.find('span').text(peepsomessagesdata.hide_checkmark);
			$btns.removeClass('disabled');
		} else {
			$btns.attr('onclick', 'return ps_messages.toggle_checkmark(' + msg_id + ', 1);');
			$btns.find('span').text(peepsomessagesdata.show_checkmark);
			$btns.addClass('disabled');
		}
	};

	PsMessages.prototype.toggle_message_checkmark = _.throttle(function (unread) {
		var $checkmarks = ps_messages.$conversation.find('.gci-check-circle'),
			$unread;

		$checkmarks.addClass('read');
		if (unread > 0) {
			$unread = $checkmarks.slice(0 - unread);
			$unread.removeClass('read');
		}
	}, 1000);

	/**
	 * Mute conversation message
	 *
	 * @param {number} msg_id ID from to-be-muted conversation.
	 */
	PsMessages.prototype.mute_conversation = function (msg_id) {
		var template = peepsomessagesdata.mute_confirm.replace('{msg_id}', msg_id);
		pswindow.show(peepsomessagesdata.mute_conversation, template);

		return false;
	};

	/**
	 * Confirm mute conversation message
	 *
	 * @param {number} msg_id ID from to-be-muted conversation.
	 * @param {Element} elem Mute button inside the mute conversation confirmation dialog.
	 */
	PsMessages.prototype.confirm_mute_conversation = function (msg_id, elem) {
		pswindow.hide();

		let $elem = $(elem);
		let $form = $elem.closest('form');
		let value = $form.find('input[type=radio]:checked').val();
		let params = { parent_id: msg_id, mute: value };

		peepso.postJson('messagesajax.set_mute', params, response => {
			if (response.success) {
				observer.doAction('psmessages_conversation_mute', msg_id);
			}
		});

		return false;
	};

	/**
	 * Unmute conversation message
	 *
	 * @param {int} msg_id ID from to-be-unmuted conversation.
	 */
	PsMessages.prototype.unmute_conversation = function (msg_id) {
		let params = { parent_id: msg_id, mute: 0 };

		peepso.postJson('messagesajax.set_mute', params, response => {
			if (response.success) {
				observer.doAction('psmessages_conversation_unmute', msg_id);
			}
		});

		return false;
	};

	/**
	 * Leave the conversation message
	 * @param {string} message The message.
	 * @param {object} a The HTML object a element
	 * @return boolean Returns false, to prevent the default redirect.
	 */
	PsMessages.prototype.leave_conversation = function (message, a) {
		pswindow.confirm(message, function () {
			window.location = $(a).attr('href');
		});

		return false;
	};

	$(function () {
		var tab = 'inbox';
		$("#messages-tab a[data-toggle='tab']").on('click', function (e) {
			tab = $($(this).attr('href')).attr('id');
			ps_messages.load_messages(tab, 1);
		});

		$(window).on('peepso_messages_after_send', function (e, response) {
			window.location = response.data.url;
			return;
		});

		var elem = $('.ps-js-messages-notification');

		if (elem.psnotification) {
			elem.psnotification({
				view_all_link: peepsomessagesdata.messages_page,
				source: 'messagesajax.get_latest',
				header: peepsomessagesdata.notification_header,
				before_click: function () {
					var $unread;

					if (window.ps_chat && peepso.screenSize() === 'large') {
						return true;
					}

					$unread = elem.find('.ps-js-counter');
					if ($unread.length && +$unread.eq(0).text() > 0) {
						return true;
					}

					return false;
				}
			}).on('notifications.shown', function (e, obj) {
				var selector = '.ps-notification__wrapper > .ps-js-notification-message';

				observer.applyFilters('messages_render', obj.popover_list.find(selector));

				if (window.ps_chat && peepso.screenSize() === 'large') {
					obj.popover_list.find(selector).on('click', function () {
						ps_chat.open_chat($(this).data('id'));
						obj.popover.hide();
					});
				} else if ($(document.body).hasClass('wp-admin')) {
					obj.popover_list.find(selector).on('click', function () {
						var url = $(this).data('url');
						window.open(url);
					});
				} else {
					obj.popover_list.find(selector).one('click', function () {
						var url = $(this).data('url');
						window.location = url;
					});
				}
			});
		}
	});

	return ps_messages;
});
