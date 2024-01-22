(function ($, factory) {
	peepso = peepso || {};
	peepso.notification = new (factory($))();
})(jQuery, function ($) {
	var SSE_ENABLED = +peepsodata.sse;

	function PsNotification() {
		var that = this;

		// Delay starting notification polling to give time for more important Ajax requests.
		function start() {
			setTimeout(function () {
				that.start();
			}, 3000);
		}

		// Auto-start notification if peepso wrapper or peepso adminbar notification icon is detected.
		$(function () {
			_.defer(function () {
				var wrapperExist = $('#peepso-wrap').length,
					adminbarExist = $('#wpadminbar').find('.psnotification-toggle').length;

				if (wrapperExist || adminbarExist) {
					start();
				}
			});
		});

		// Allow widget or other scripts to manually trigger notification polling.
		peepso.observer.addAction('notification_start', start);

		// Update notification counter on titlebar manually.
		peepso.observer.addAction(
			'notification_titlebar',
			function (newCount) {
				that._update_titlebar(newCount);
			},
			10,
			1
		);
	}

	PsNotification.prototype = {
		_get_latest_interval: +peepsodata.get_latest_interval || 30000, // notification poll interval

		_get_latest_count: function () {
			peepso
				.disableAuth()
				.disableError()
				.postJson(
					'notificationsajax.get_latest_count',
					null,
					function (json) {
						var count_title;

						// Stop pooling if session is expired.
						if (json.session_timeout) {
							this.stop();
							return;
						}

						if (json.success) {
							count_title = 0;
							$.each(json.data, function (key, value) {
								var $el = $('.' + key),
									count = Math.max(0, value.count),
									prev_count,
									$counter;

								// append to titlebar counter value
								count_title += count;

								// update notification icon counter
								if ($el.length) {
									$counter = $el.find('.ps-js-counter');
									prev_count = +$counter.eq(0).text();
									if ($counter.length && prev_count !== count) {
										$counter
											.html(count)
											.css('display', count > 0 ? '' : 'none');
										if (count > 0 && $el.data('plugin_psnotification')) {
											$el.psnotification('clear_cache');
										}
									}
								}
							});

							this._update_titlebar(count_title);

							peepso.observer.doAction('notification_update', json);
						}
					}.bind(this)
				);
		},

		/**
		 * Update notification counter on titlebar.
		 *
		 * @param {number|string} count
		 */
		_update_titlebar: function (count) {
			var title = document.title || '',
				rCount = /^\((\d+)\)\s*/,
				currCount;

			// Apply increment/decrement on notification counter if parameter
			// is a relative value, e.g: `+3`, `-1`.
			if (typeof count === 'string' && count.match(/^[-+]\d+$/)) {
				count = +count;
				currCount = title.match(rCount);
				currCount = currCount ? +currCount[1] : 0;
				count = currCount + count;
			}

			// Change notification counter string on titlebar.
			title = title.replace(rCount, '');
			if (count > 0) {
				title = '(' + count + ') ' + title;
			}

			if (document.title !== title) {
				document.title = title;
			}
		},

		hide: function (note_id) {
			var $elems, fn, flag;

			if (typeof note_id === 'undefined') {
				return;
			}

			// prevent repeated call
			fn = this.hide;
			flag = '_progress_' + note_id;
			if (fn[flag]) {
				return;
			}
			fn[flag] = true;

			$elems = $('.ps-js-notifications')
				.find('.ps-js-notification--' + note_id)
				.map(function () {
					return $(this).parent('.ps-notification__wrapper').get(0);
				});

			$elems.css('opacity', 0.5);
			peepso.postJson(
				'notificationsajax.hide',
				{ note_id: note_id },
				$.proxy(function (json) {
					delete fn[flag];
					if (json.success) {
						$elems.remove();
						peepso.observer.doAction('notification_restart');
					} else {
						$elems.css('opacity', '');
					}
				}, this)
			);
		},

		/**
		 * Sends an ajax call to mark notifications as read.
		 * @param {Number} [id]
		 * @return jQuery.Deferred
		 */
		markAsRead: function (id) {
			return $.Deferred(
				$.proxy(function (defer) {
					var params = null;

					// Only marks particular notification if ID is set.
					if (id) {
						params = { note_id: id };
					}

					peepso.postJson(
						'notificationsajax.mark_as_read',
						params,
						$.proxy(function (json) {
							if (json.success) {
								defer.resolveWith(this);
							} else if (json.errors) {
								defer.rejectWith(this, [json.errors[0]]);
							}
						}, this)
					);
				}, this)
			);
		},

		/**
		 * Sends an ajax call to mark all notifications as read.
		 * @return jQuery.Deferred
		 */
		markAllAsRead: function () {
			return this.markAsRead();
		},

		start: function () {
			if (!+peepsodata.currentuserid) {
				return;
			}

			if (this._started) {
				return;
			}

			this._started = true;

			if (SSE_ENABLED) {
				var sseAction = $.proxy(function (data) {
					if (data.event === 'get_notifications') {
						this._get_latest_count();
					}
				}, this);
				peepso.observer.addAction('peepso_sse', sseAction, 10, 1);
			} else {
				clearInterval(this._get_latest_timer);
				this._get_latest_count();
				this._get_latest_timer = setInterval(
					$.proxy(this._get_latest_count, this),
					this._get_latest_interval
				);
			}

			// stop notification on login popup
			$(window).on(
				'peepso_auth_required',
				$.proxy(function () {
					clearInterval(this._get_latest_timer);
				}, this)
			);

			// restart notification on peepso-core-message's mark-as-read
			peepso.observer.addFilter('pschat_mark_as_read', this.restart, 10, 1, this);
			peepso.observer.addAction('notification_restart', this.restart, 10, 1, this);
		},

		restart: function () {
			if (!+peepsodata.currentuserid) {
				return;
			}

			if (SSE_ENABLED) {
				this._get_latest_count();
			} else {
				clearInterval(this._get_latest_timer);
				this._get_latest_count();
				this._get_latest_timer = setInterval(
					$.proxy(this._get_latest_count, this),
					this._get_latest_interval
				);
			}
		},

		stop: function () {
			clearInterval(this._get_latest_timer);
		}
	};

	return PsNotification;
});

// Available options:
// 	view_all_text, string
// 	view_all_link, string
// 	source, // string - the URL to retrieve the view
// 	request, // json - additional parameters to send to opts.source via ajax
// 	paging, // boolean - enables the scroll pagination
//

// TODO: reimplement using prototype

(function ($) {
	function PsPopoverNotification(elem, options) {
		var _self = this;
		this.popover_ct = null;
		this.popover_list = null;
		this.popover_footer = null;
		this.popover_header = null;
		this._notifications = {}; // array of HTML to be inserted to the dropdown list

		this.init = function (opts) {
			_opts = {
				view_all_text: peepsodata.view_all_text,
				view_all_link: null,
				source: null, // the URL to retrieve the view
				request: {
					// additional parameters to send to opts.source via ajax
					per_page: 10,
					page: 1
				},
				header: null, // HTML to be displayed on the top section of the notification
				paging: false, // set  this to true if you want to enable scrolling pagination
				fetch: null // Function used to modify the request data. Returning false will prevent the fetch operation
			};

			this.opts = peepso.observer.applyFilters('peepso_notification_plugin_options', _opts);

			this._content_is_fetched = false;
			$.extend(true, this.opts, opts);

			$(elem).addClass('psnotification-toggle');
			this.popover_ct = $('<div>');
			this.popover_list = $('<div>').css({ maxHeight: '40vh', overflow: 'auto' });
			this.popover_list.bind(
				'mousewheel',
				$.proxy(function (e, d) {
					var t = $(e.currentTarget);
					if (d > 0 && t.scrollTop() === 0) {
						e.preventDefault();
					} else if (d < 0 && t.scrollTop() == t.get(0).scrollHeight - t.innerHeight()) {
						e.preventDefault();
					}
				}, this)
			);

			$(elem).append(this.popover_ct);

			// Add header
			if (this.opts.header) {
				this.popover_header = $('<div class="ps-notif__box-header"/>');
				this.popover_header.append(this.opts.header);
				this.popover_ct.append(this.popover_header);
			}

			// Add list container
			this.popover_ct.append(this.popover_list);
			this.popover_list.addClass('ps-notifications ps-notifications--empty');
			this.popover_ct.addClass('ps-notif__box').hide();

			if (this.opts.paging) {
				this.init_pagination();
			}

			// Add view all link
			var footerLinks = this.opts.view_all_link,
				footerTexts = this.opts.view_all_text,
				footerBtnWidth;

			if (footerLinks) {
				footerTexts = _.isArray(footerTexts) ? footerTexts : [footerTexts];
				footerLinks = _.isArray(footerLinks) ? footerLinks : [footerLinks];

				footerLinks = _.map(footerLinks, function (value, index) {
					return ['<a href="', value, '">', footerTexts[index], '</a>'].join('');
				});

				this.popover_footer = $('<div class="ps-notif__box-footer"></div>');
				this.popover_footer.append(footerLinks.join(''));
				this.popover_footer.appendTo(this.popover_ct);
			}

			// Mark-as-read when the notification is clicked.
			this.popover_list.on(
				'mousedown.ps-notification',
				'.ps-js-notification a',
				$.proxy(function (e) {
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
					peepso.notification
						.markAsRead($item.data('id'))
						.done(
							$.proxy(function () {
								var $ct = $item.closest('.ps-js-notifications'),
									$counter = $ct.find('.ps-js-counter'),
									count = +$counter.text();

								$item.css('opacity', '');
								$item.data('unread', 0);
								if (e.which === 1 && !e.metaKey && !e.shiftKey) {
									$a.off('click');
									// https://stackoverflow.com/questions/20928915/jquery-triggerclick-not-working
									$a[0].click();
								}

								// Decrease notification counters.
								$('.ps-js-notifications')
									.find('.ps-js-counter')
									.html(count - 1)
									.css('display', count > 1 ? '' : 'none');
							}, this)
						)
						.fail(function (error) {
							$item.addClass('ps-notification--unread');
							if (error) {
								peepso.dialog(error, { error: true }).show();
							}
						});
				}, this)
			);

			// Mark-as-read when the mark-as-read icon is clicked.
			this.popover_list.on(
				'mousedown click',
				'.ps-js-mark-as-read',
				$.proxy(function (e) {
					var $icon, $item;

					e.preventDefault();
					e.stopPropagation();
					if (e.type !== 'click') {
						return;
					}

					$icon = $(e.currentTarget);
					$item = $icon.closest('.ps-js-notification');

					$icon.hide();
					$item.css('opacity', 0.5);
					$item.removeClass('ps-notification--unread');
					peepso.notification
						.markAsRead($item.data('id'))
						.done(
							$.proxy(function () {
								var $ct = $item.closest('.ps-js-notifications'),
									$counter = $ct.find('.ps-js-counter'),
									count = +$counter.text();

								$icon.remove();
								$item.css('opacity', '');
								$item.data('unread', 0);

								// Decrease notification counters.
								$('.ps-js-notifications')
									.find('.ps-js-counter')
									.html(count - 1)
									.css('display', count > 1 ? '' : 'none');
							}, this)
						)
						.fail(function (error) {
							$item.addClass('ps-notification--unread');
							$icon.show();
							if (error) {
								peepso.dialog(error, { error: true }).show();
							}
						});
				}, this)
			);

			// Mark-all-as-read button.
			if (this.popover_footer) {
				this.popover_footer.on(
					'click',
					'.ps-js-mark-all-as-read',
					$.proxy(function (e) {
						var $items = this.popover_list.find('.ps-js-notification'),
							$icons;

						$items = $items.filter('.ps-notification--unread');
						$icons = $items.find('.ps-js-mark-as-read');

						if (confirm(peepsodata.mark_all_as_read_confirm_text)) {
							$icons.hide();
							$items.css('opacity', 0.5);
							$items.removeClass('ps-notification--unread');
							peepso.notification
								.markAllAsRead()
								.done(function () {
									$icons.remove();
									$items.css('opacity', '');
									$items.data('unread', 0);

									// Empty notification counters.
									$('.ps-js-notifications')
										.find('.ps-js-counter')
										.html(0)
										.css('display', 'none');
								})
								.fail(function (error) {
									$items.addClass('ps-notification--unread');
									$icons.show();
									if (error) {
										peepso.dialog(error, { error: true }).show();
									}
								});
						}
					}, this)
				);

				// Toggle unread only.
				this.popover_footer.on(
					'click',
					'.ps-js-toggle-unread-only',
					$.proxy(function (e) {
						var $btn = $(e.currentTarget);

						this._unreadOnly = !this._unreadOnly;
						$btn.html(
							this._unreadOnly
								? peepsodata.show_all_text
								: peepsodata.show_unread_only_text
						);

						// Refresh the notification.
						this.popover_list.find('.ps-notification__wrapper').remove();
						this.opts.request.page = 1;
						this._content_is_fetched = false;
						this.load_page(function () {
							if (_self.opts.paging) {
								_self.popover_list.trigger('scroll');
							}
						});
					}, this)
				);
			}
		};

		this.fetch = function (callback) {
			var req = this.opts.request,
				method = (this.opts.method || '').toLowerCase();

			// Allow scripts to customize the request further
			if (_.isFunction(this.opts.fetch)) {
				req = this.opts.fetch.call(this, req);

				if (false === req) {
					return;
				}
			}

			this._notifications = {};
			this.fetch_stop();
			this.fetch_xhr = peepso[method === 'get' ? 'getJson' : 'postJson'](
				this.opts.source,
				req,
				function (response) {
					if (response.success) {
						_self._content_is_fetched = true;
						_self._data = response.data;
						_self._notifications = response.data.notifications;
						_self._errors = false;

						if (_self._notifications.length > 0) {
							_self.opts.request.page++;
						} // locks in to the last page that had available data, so when new data comes in we have the correct offset
					} else if (response.errors) {
						_self._content_is_fetched = true;
						_self._errors = response.errors;
					}
					if (typeof callback === 'function') {
						callback();
					}
				}
			);
		};

		this.fetch_stop = function () {
			if (this.fetch_xhr) {
				if (this.fetch_xhr.abort) {
					this.fetch_xhr.abort();
				} else if (this.fetch_xhr.ret && this.fetch_xhr.ret.abort) {
					this.fetch_xhr.ret.abort();
				}
			}
		};

		this.refresh = function () {
			this.popover_list.find('.ps-notification__wrapper').remove();
			this._content_is_fetched = false;
			this.load_page(function () {
				if (_self.opts.paging) {
					_self.popover_list.trigger('scroll');
				}
			});
		};

		this.onClick = function (e) {
			if (_.isFunction(_self.opts.before_click) && _self.opts.before_click() === false) {
				return;
			}

			if (_self.popover_ct.has($(e.target)).length > 0) {
				return;
			}

			e.preventDefault();

			var isLazy = _self.opts.lazy;
			var isVisible = _self.popover_ct.is(':visible');

			_self.show();
			!isLazy &&
				!isVisible &&
				_self.load_page(function () {
					if (_self.opts.paging) {
						_self.popover_list.trigger('scroll');
					}
				});
		};

		this.render = function () {
			$.each(this._notifications, function (i, not) {
				var notification = $("<div class='ps-notification__wrapper'></div>");
				notification.html(not).hide();
				notification.appendTo(_self.popover_list).fadeIn('slow');
			});

			$(elem).trigger('notifications.shown', [$.extend(elem, this)]);
			// open in a new tab if opened page is backend page
			if ($(document.body).hasClass('wp-admin')) {
				$(elem).find('a').attr('target', '_blank');
			}
			this.popover_list.toggleClass(
				'ps-notifications--empty',
				0 === this.popover_list.find('.ps-notification__wrapper').length
			);
		};

		this.show = function () {
			this.popover_ct.slideToggle({
				duration: 'fast',
				done: function () {
					$(document).on('mouseup.notification_click', function (e) {
						if (!$(elem).is(e.target) && 0 === $(elem).has(e.target).length) {
							_self.popover_ct.hide();
							$(document).off('mouseup.notification_click');
						}
					});
				}
			});
		};

		this.init_pagination = function () {
			this.popover_list.on('scroll', function () {
				if (
					_self._content_is_fetched &&
					$(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight
				) {
					_self._content_is_fetched = false;
					_self.load_page(function () {
						if (_self._notifications && _.isEmpty(_self._notifications)) {
							// Check empty array.
							_self.popover_list.off('scroll');
						} else {
							_self.popover_list.trigger('scroll');
						}
					});
				}
			});
		};

		this.load_page = function (callback) {
			if (false === this._content_is_fetched) {
				var error = this.popover_list.nextAll('.ps-notifs__errors'),
					loading = this.popover_list.nextAll('.ps-popover-loading');

				if (error.length) {
					error.remove();
				}

				if (!loading.length) {
					loading = $(
						"<div class='ps-popover-loading'><img src='" +
							peepsodata.loading_gif +
							"'/></div>"
					);
					this.popover_list.after(loading);
				}

				this.fetch_stop();
				setTimeout(function () {
					_self.fetch(function () {
						loading.remove();

						if (_self._errors) {
							error = $('<div class=ps-notifs__errors />');
							$.each(_self._errors, function (i, msg) {
								$('<div class=ps-notifs__error />').html(msg).appendTo(error);
							});
							_self.popover_list.after(error);
						}

						_self.render();

						if (typeof callback === typeof Function) {
							callback();
						}

						if (typeof _self.opts.after_load === 'function') {
							_self.opts.after_load.apply(_self);
						}
					});
				}, 500);
			}
		};

		this.clear_cache = function () {
			this.popover_list.find('.ps-notification__wrapper').remove();
			this.popover_ct.hide();
			this.opts.request.page = 1;
			this._content_is_fetched = false;
		};

		this.init(options);
		$(elem).on('click', this.onClick);

		return this;
	}

	$.fn.psnotification = function (methodOrOptions) {
		return this.each(function () {
			if (!$.data(this, 'plugin_psnotification')) {
				$.data(
					this,
					'plugin_psnotification',
					new PsPopoverNotification(this, methodOrOptions)
				);
			} else {
				var _self = $.data(this, 'plugin_psnotification');

				if (_.isFunction(_self[methodOrOptions])) {
					return _self[methodOrOptions].call(_self);
				}
			}
		});
	};

	peepso.observer.addAction(
		'notification_clear_cache',
		function (key) {
			key = key || 'ps-js-notifications';
			$('.' + key).psnotification('clear_cache');
		},
		10,
		1
	);
})(jQuery);

// initialise notification dropdowns
jQuery(function ($) {
	false &&
		$('.dropdown-notification').psnotification({
			view_all_link: [
				'javascript:" class="ps-js-mark-all-as-read',
				'javascript:" class="ps-js-toggle-unread-only'
			],
			view_all_text: [peepsodata.mark_all_as_read_text, peepsodata.show_unread_only_text],
			source: 'notificationsajax.get_latest',
			request: {
				per_page: 5
			},
			paging: true,
			fetch: function (req) {
				req.unread_only = this._unreadOnly ? 1 : 0;
				return req;
			}
		});
});
