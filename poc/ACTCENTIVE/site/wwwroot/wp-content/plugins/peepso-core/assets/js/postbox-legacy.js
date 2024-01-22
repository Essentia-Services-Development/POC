/*
 * Implementation of PeepSo's PostBox
 * @package PeepSo
 * @author PeepSo
 */

(function ($) {
	$.fn.pspostbox = function (options) {
		if (this.length <= 0) return;

		var _self = this;

		this.$textarea = null;
		this.$access = null;
		this.$charcount = null;
		this.$preview_button = null;
		this.$cancel_button = null;
		this.$save_button = null;
		this.$posttabs = null;
		this.$privacy_dropdown = null;

		this.can_submit = false;

		/**
		 * Initialize this postbox instance
		 * @param  {array} opts Array of options to override the defaults
		 * @return {object}      The plugin instance
		 */
		this.init = function (opts) {
			var _self = this;
			var _opts = {
				textarea: 'textarea.ps-postbox-textarea',
				mirror: '.ps-postbox-mirror',
				addons: '.ps-postbox-addons',
				access: '#postbox_acc',
				save_url: 'postbox.post',
				charcount: '.ps-js-charcount',
				preview_button: '.ps-js-btn-preview',
				cancel_button: '.ps-button-cancel',
				save_button: '.postbox-submit',
				send_button_text: undefined,
				text_length: peepsodata.postsize,
				autosize: true // allows the textarea to adjust it's height based on the length of the content
			};

			this.opts = _opts;
			$.extend(true, this.opts, opts);

			this.guid = _.uniqueId('postbox-');

			this.$posttabs = $(this).find('.ps-postbox-tab-root').ps_posttabs({
				container: this
			});

			this.$textarea = jQuery(this.opts.textarea, this);
			this.$mirror = jQuery(this.opts.mirror, this);
			this.$addons = jQuery(this.opts.addons, this);
			this.$access = $(this.opts.access, this);
			this._default_access = this.$access.val();
			this.$charcount = $(this.opts.charcount, this);
			this.$preview_button = $(this.opts.preview_button, this);
			this.$cancel_button = $(this.opts.cancel_button, this);
			this.$save_button = $(this.opts.save_button, this);

			if (!_.isUndefined(this.opts.send_button_text)) {
				this.$save_button.html(this.opts.send_button_text);
			}

			this.$preview_button.on('click', function () {
				window.postbox.check_url_preview('force');
			});

			this.$privacy = $('#privacy-tab', this);
			this.orig_height = this.$textarea.height();

			if (this.opts.autosize) this.$textarea.autosize();

			// Setup events
			this.$textarea
				.attr('maxlength', this.opts.text_length)
				.on('keydown', function (e) {
					_self.on_keydown(e);
				})
				.on('keypress', function (e) {
					_self.on_keypress(e);
				})
				.on('paste', function (e) {
					_self.on_paste(e);
				})
				.on('focus', function (e) {
					_self.on_focus();
				})
				.on('keyup', function (e) {
					_self.on_change();
				})
				.on('input', function (e) {
					_self.on_input();
				});

			this.$charcount.html(this.opts.text_length + '');

			this.$privacy_dropdown = $('.ps-privacy-dropdown', this.$privacy);

			// setup privacy control
			this.$privacy_dropdown.on(
				'click a',
				jQuery.proxy(function (e) {
					var $a = jQuery(e.target).closest('a'),
						$btn = this.$privacy.find(
							'.ps-js-interaction-wrapper .ps-js-interaction-toggle'
						),
						$input = jQuery('#postbox_acc');

					$btn.find('i').attr('class', $a.find('i').attr('class'));
					$btn.find('span').html($a.find('span').text());
					$input.val($a.attr('data-option-value'));
				}, this)
			);

			this.$privacy.on('click', function (e) {
				_self.privacy(e);
			});

			peepso.observer.addAction(
				'postbox_group_set',
				function ($postbox) {
					if ($postbox === _self) {
						_self.$privacy.hide();
					}
				},
				10,
				1
			);

			peepso.observer.addAction(
				'postbox_group_reset',
				function ($postbox) {
					if ($postbox === _self) {
						_self.$privacy.show();
					}
				},
				10,
				1
			);

			jQuery('nav.ps-postbox-tab ul li a').click(this.clear_tabs);

			jQuery('#status-post', _self).addClass('active');
			jQuery(this.$posttabs).on('peepso_posttabs_show-status', function () {
				jQuery('#status-post', _self).addClass('active');
				jQuery('.ps-postbox-status').show();
				_self.on_change();
			});

			peepso.observer.addAction(
				'postbox_type_set',
				function ($postbox, type) {
					if ($postbox === _self && type === 'status') {
						jQuery('#status-post', _self).trigger('click');
					}
				},
				10,
				2
			);

			jQuery('#status-post', _self).on('click', function () {
				jQuery(_self.$posttabs).find("[data-tab='status']").trigger('click');
			});

			this.$posttabs.on('peepso_posttabs_submit-status', function () {
				_self.save_post();
			});

			this.$posttabs.on('peepso_posttabs_cancel-status', function () {
				jQuery('#status-post', _self).removeClass('active');
			});

			this.$posttabs.on('peepso_posttabs_submit', function () {
				_self.$textarea.attr('readonly', 'readonly');
				_self.find('.ps-postbox-status').css('opacity', 0.5);
			});

			this.$posttabs.on('peepso_posttabs_cancel', function () {
				_self.$textarea.val('');
				_self.cancel_post();
			});

			this.find('.interactions > ul > li > .interaction-icon-wrapper a').on(
				'click',
				function (e, x) {
					if (x) return;

					_self
						.find('.interactions > ul > li > .ps-js-interaction-wrapper a')
						.not(this)
						.trigger('peepso.interaction-hide', [true]);
				}
			);

			// Toggle active class script.
			setTimeout(() => {
				let isEmpty = () => {
					let value = this.$textarea.val().trim(),
						flags = { hard: [], soft: ['' !== value] },
						canSubmit = peepso.observer.applyFilters(
							'peepso_postbox_can_submit',
							flags,
							this
						),
						empty = true;

					[...canSubmit.hard, ...canSubmit.soft].forEach(flag => {
						if (flag) {
							empty = false;
						}
					});

					return empty;
				};

				let toggle = active => {
					if (active) {
						this.addClass('ps-postbox--active');
						this.find('.ps-js-postbox-footer').not('.ps-postbox-tab-root').show();
						this.update_charcount();
					} else {
						this.removeClass('ps-postbox--active');
						this.find('.ps-js-postbox-footer').not('.ps-postbox-tab-root').hide();
						this.$charcount.hide();
					}
				};

				toggle(!isEmpty());

				this.$textarea.on('focus', () => this.click());
				this.on('click', e => {
					e.stopPropagation();

					toggle(true);

					$(document)
						.off('click.postbox')
						.one('click.postbox', () => toggle(!isEmpty()));
				});
			}, 100);

			this._load_addons();

			// Remove disabler.
			this.removeClass('ps-postbox--disabled');

			// Branding hook.
			if (+peepsodata.show_powered_by) {
				peepso.observer.addAction('show_branding', () => {
					let $branding = $(peepsodata.powered_by);
					if (!$(this).children(`.${$branding.attr('class')}`).length) {
						$branding.appendTo(this);
					}
				});
			}
		};

		/**
		 * Allows addons to get a reference to this postbox instance
		 */
		this._load_addons = function () {
			var addons = peepso.observer.applyFilters('peepso_postbox_addons', []);

			$(addons).each(function (index, addon) {
				addon.set_postbox(_self);
				addon.init();
			});
		};

		/**
		 * Applies filter for postbox clear tabs
		 * called when any <a> link within postbox is clicked
		 */
		this.clear_tabs = function () {
			peepso.observer.applyFilters('postbox_clear_tabs', null);
			//			jQuery(".ps-postbox div.ps-postbox-popup.active").hide();
		};

		/**
		 * Sets post privacy on mouse up
		 * @param {object} e Event triggered
		 */
		this.privacy = function (e) {
			if (this.$privacy_dropdown.is(':visible')) {
				this.$privacy_dropdown.hide();
				this.$privacy.removeClass('ps-postbox__menu-item--open');
				jQuery(document).off('mouseup.ps-postbox-privacy');
			} else {
				this.$privacy_dropdown.show();
				this.$privacy.addClass('ps-postbox__menu-item--open');

				// Add autohide on document-click.
				setTimeout(
					function () {
						jQuery(document)
							.off('mouseup.ps-postbox-privacy')
							.on(
								'mouseup.ps-postbox-privacy',
								function (e) {
									if (this.$privacy.has(e.target).length === 0) {
										this.$privacy_dropdown.hide();
										this.$privacy.removeClass('ps-postbox__menu-item--open');
										$(document).off('mouseup.ps-postbox-privacy');
									}
								}.bind(this)
							);
					}.bind(this),
					1
				);
			}
		};

		/**
		 * Saves the post
		 * Invokes when Post button is saved
		 */
		this.save_post = function () {
			var canSave = peepso.observer.applyFilters('postbox_before_save', true, this);
			if (!canSave) {
				return;
			}

			var req = {
				content: this.$textarea.val(),
				id: peepsodata.currentuserid,
				uid: peepsodata.userid,
				acc: this.$access.val(),
				type: 'activity'
			};

			if (
				!_.isUndefined(this.opts.postbox_req) &&
				typeof Function === typeof this.opts.postbox_req
			) {
				req = this.opts.postbox_req.apply(null, [req]);
			}

			// send req through filter
			req = peepso.observer.applyFilters('postbox_req', req);
			req = peepso.observer.applyFilters('postbox_req_' + this.guid, req);

			// add request parameter into queue
			this.save_post_queue || (this.save_post_queue = []);
			this.save_post_queue.push(req);

			this.on_before_save();

			if (this.save_post_progress) return;
			this.save_post_progress = true;
			this.save_post_execute();
		};

		/**
		 * TODO: docblock
		 */
		this.save_post_execute = function () {
			if (!this.save_post_queue.length) {
				if (!peepso.observer.applyFilters('peepso_postbox_enter_to_send', false)) {
					jQuery('.ps-postbox-loading', this).hide();
					jQuery('.ps-postbox-action', this).css('display', 'flex');
					this.$posttabs.on_cancel();
				}
				this.save_post_progress = false;
				this.on_queue_clear();
				postbox.remove_broken_thumbnails();
				return;
			}

			var req = this.save_post_queue.shift();

			if (!peepso.observer.applyFilters('peepso_postbox_enter_to_send', false)) {
				jQuery('.ps-postbox-action', this).css('display', 'none');
				jQuery('.ps-postbox-loading', this).show();
			}

			// Set to async so our filters run in order.
			peepso.disableAsync().postJson(this.opts.save_url, req, function (json) {
				if (json.success) {
					_self.on_save(json);
					jQuery(_self).trigger('postbox.post_saved', [req, json]);
				} else {
					_self.on_error(json);
				}
				_self.save_post_execute();
			});
		};

		/**
		 * Called before post save
		 * @param {object} json JSON object
		 */
		this.on_before_save = function () {
			if (typeof Function === typeof this.opts.on_before_save) {
				this.opts.on_before_save.apply(this);
			}
		};

		/**
		 * Called on post save
		 * @param {object} json JSON object
		 */
		this.on_save = function (json) {
			var onSaveHandler = peepso.observer.applyFilters('peepso_postbox_onsave', false, this);
			if (typeof onSaveHandler === 'function') {
				onSaveHandler.apply(this, [json]);
			} else if (typeof this.opts.on_save === 'function') {
				this.opts.on_save.apply(this, [json]);
				this.$textarea.css('height', this.orig_height);
				jQuery(this).trigger('postbox.post_saved', this);
			}
		};

		/**
		 * Called on post save
		 * @param {object} json JSON object
		 */
		this.on_queue_clear = function () {
			if (typeof Function === typeof this.opts.on_queue_clear) {
				this.opts.on_queue_clear.apply(this);
			}
		};

		/**
		 * Invoked when an error on posting has occured
		 * @param {object} json JSON object
		 */
		this.on_error = function (json) {
			if (typeof Function === typeof this.opts.on_error) {
				this.opts.on_error.apply(this, [json]);
			} else if (false === _.isUndefined(json.errors[0])) {
				// TODO: this needs translation
				psmessage.show('Error', json.errors[0]);
			}

			return;
		};

		/**
		 * Called when Cancel post button is invoked
		 */
		this.cancel_post = function () {
			//resets the privacy setting
			// this.$privacy.find("[data-option-value='" + this._default_access + "']").trigger("click");
			this.$textarea.css('height', this.orig_height);
			this.$textarea.removeAttr('readonly');
			this.find('.ps-postbox-status').css('opacity', '');
			this.on_change();
			jQuery(this).trigger('postbox.post_cancel');
		};

		/**
		 * On focus event handler
		 * Called when onfocus event is triggered
		 */
		this.on_focus = function () {
			jQuery('.ps-postbox-tab-root', _self).hide();
			jQuery('.ps-postbox-tab.interactions', _self).attr(
				'data-tab-shown',
				this.$posttabs.current_tab().data('tab')
			);
			jQuery('.ps-postbox-tab.interactions', _self).show();
		};

		/**
		 * Keydown events handler
		 * Called when keydown events occured
		 * @param {object} e Event triggered
		 */
		this.on_keydown = function (e) {
			this._go_submit = false;
			if (e.keyCode !== 13) {
				return true;
			}

			var val = this.$textarea.val(),
				trimmed = val.trim();

			if (
				!e.shiftKey &&
				peepso.observer.applyFilters('peepso_postbox_enter_to_send', false)
			) {
				if (trimmed.length || this.submitable(val)) {
					e.preventDefault();
					this._go_submit = true;
					this.$posttabs.on_submit();
					return false;
				}
			}

			if (!trimmed.length) {
				e.preventDefault();
				return false;
			}
		};

		/**
		 * Keypress events handler
		 * Called when key has been pressed
		 * @param {object} e Event triggered
		 */
		this.on_keypress = function (e) {
			if (this.$textarea.val() >= this.opts.text_length) {
				return false;
			}
		};

		/**
		 * Paste events handler
		 * Called when paste is tiggered
		 * @param {object} e Event triggered
		 */
		this.on_paste = function (e) {
			var _self = this;
			e.originalEvent.clipboardData.getData('text/plain').slice(0, this.text_length);

			setTimeout(function () {
				_self.on_change();
			}, 100);
		};

		/**
		 * Input events handler
		 * Called when input is tiggered
		 * @param {object} e Event triggered
		 */
		this.on_input = function (e) {
			if (!this._go_submit) {
				peepso.observer.applyFilters(
					'peepso_postbox_input_changed',
					this.$textarea.val(),
					this
				);

				this.update_beautifier();
			}
		};

		this.update_charcount = function () {
			var val = this.$textarea.val();
			var maxLen = this.opts.text_length;
			var len = Math.max(0, maxLen - val.length);

			this.$charcount.html(len + '');

			// Do not show the counter if it is below 50% of the characters limit.
			if (len && len / maxLen > 0.5) {
				this.$charcount.hide();
				return;
			}

			this.$charcount.show();

			// Update character counter text color.
			var color = '';
			// Above 90% of the characters limit.
			if (0 === len || len / maxLen < 0.1) {
				color = 'red';
			}
			// Above 75% of the characters limit.
			else if (len / maxLen < 0.25) {
				color = 'orange';
			}

			this.$charcount.css({ color: color });
		};

		/**
		 * Updates the character counter
		 */
		this.on_change = function () {
			this.update_charcount();

			var val = this.$textarea.val();

			if (this.submitable(val)) {
				this.$save_button.show();
			} else {
				this.$save_button.hide();
			}

			// Escape newlines and
			var mirrorText = val.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');

			this.$mirror.html(mirrorText);

			this.addons_update();
		};

		this.submitable = function (val) {
			var len = Math.max(0, this.opts.text_length - val.length);
			var can_submit = peepso.observer.applyFilters(
				'peepso_postbox_can_submit',
				{
					hard: [],
					soft: [len < this.opts.text_length && '' !== val.trim()]
				},
				this
			);

			// Notice users upon leaving the page when postbox content is not empty.
			(({ hard, soft }) => {
				let showNotice = false;
				if (hard.length) {
					showNotice = hard.indexOf(false) === -1;
				} else {
					showNotice = soft.indexOf(true) !== -1;
				}

				if (showNotice && !this.beforeUnloadHandler) {
					this.beforeUnloadHandler = () => {
						return true;
					};
					peepso.observer.addFilter('beforeunload', this.beforeUnloadHandler);
				} else if (!showNotice && this.beforeUnloadHandler) {
					peepso.observer.removeFilter('beforeunload', this.beforeUnloadHandler);
					this.beforeUnloadHandler = null;
				}

				if (showNotice) {
					this.$preview_button.show();
					this.$cancel_button.show();
				} else {
					this.$preview_button.hide();
					this.$cancel_button.hide();
				}
			})(can_submit);

			if (can_submit.hard.length) {
				can_submit = can_submit.hard.indexOf(false) > -1 ? false : true;
			} else can_submit = can_submit.soft.indexOf(true) > -1 ? true : false;

			return can_submit;
		};

		this.addons_update = jQuery.proxy(
			_.debounce(function () {
				var list = peepso.observer.applyFilters('peepso_postbox_addons_update', []);
				if (list && list.length) {
					var placeholder = this.$textarea.attr('placeholder');
					this.$textarea.data('placeholder', placeholder).removeAttr('placeholder');
					this.$addons.html('&mdash; ' + list.join(' and '));
					this.$addons.show();
				} else {
					var placeholder = this.$textarea.data('placeholder');
					this.$textarea.attr('placeholder', placeholder);
					this.$addons.hide().empty();
				}
			}, 10),
			this
		);

		this.update_beautifier = _.throttle(function () {
			_.defer(
				_.bind(function () {
					var $wrapper, html;

					if (!this.$beautifier) {
						this.$textarea.addClass('ps-tagging-textarea');
						$wrapper = this.$textarea.parent('.ps-postbox__input-tag');
						if (!$wrapper.length) {
							this.$textarea.wrap('<div class=ps-postbox__input-tag />');
							$wrapper = this.$textarea.parent('.ps-postbox__input-tag');
						}
						this.$beautifier = $wrapper.children('.ps-postbox__input-beautifier');
						if (!this.$beautifier.length) {
							this.$beautifier = $('<div class=ps-postbox__input-beautifier />');
							this.$beautifier.prependTo($wrapper);
						}
						this.$textarea.focus();
					}

					// Disable WP Emoji
					(function (settings) {
						if (settings && settings.supports) {
							settings.supports.everything = true;
						}
					})(window._wpemojiSettings || {});

					html = this.$textarea.val() || '';
					html = peepso.observer.applyFilters(
						'peepso_postbox_beautifier',
						html,
						this.$textarea
					);
					html = html
						// Replace html tags not added by peepso.
						.replace(/<(?!\/?ps_)/g, '&lt;')
						// Replace peepso tags.
						.replace(/<(\/?)ps_/g, '<$1')
						// Replace newlines.
						.replace(/\r?\n/g, '<br />');

					this.$beautifier.html(html);
				}, this)
			);
		}, 100);

		this.init(options);

		return this;
	};
})(jQuery);

// delcare class
function PsPostboxLegacy() {
	this.can_submit = false;
	this.$postbox = null;
	this.$url_preview_container = jQuery(
		"<div class='ps-postbox__url-preview-wrapper url-preview-container'></div>"
	);

	this.embedData = null;
	this.embedExceptions = [];
}

/**
 * Initializes Postbox
 */
PsPostboxLegacy.prototype.init = function () {
	var _self = this;

	peepso.observer.addFilter(
		'peepso_postbox_can_submit',
		function (can_submit) {
			if (can_submit) return can_submit;
			return _self.can_submit;
		},
		20,
		1
	);

	this.$activity_stream_recent = jQuery('#ps-activitystream-recent');
	this.$activity_stream = jQuery('#ps-activitystream');

	this.$postbox = jQuery('#postbox-main').pspostbox({
		postbox_req: function (req) {
			// Attach embed data if available.
			if (_self.embedData) {
				req.embed = _self.embedData;
			}
			return req;
		},
		on_save: function (json) {
			// Resets the postbox to the "Status" post
			jQuery(this.$posttabs).find("[data-tab='status']").trigger('click');
			return _self.append_to_stream(json);
		}
	});

	if (undefined !== this.$postbox) {
		this.$postbox.$textarea
			.on('blur', function (e) {
				_self.check_url_preview();
			})
			.on('keyup', function (e) {
				if (32 === e.keyCode) _self.check_url_preview();
			});

		this.$postbox.on('postbox.post_saved postbox.post_cancel', () => {
			_self.can_submit = false;
			_self.$url_preview_container.empty().remove();

			this.embedData = null;
			this.embedExceptions = [];
		});
	}

	return this.$postbox;
};

// Checks if there's a URL in the post and display a preview of it
PsPostboxLegacy.prototype.check_url_preview = function (force) {
	// Skip if previous fetching is not done yet.
	if (this.fetching) {
		return;
	}

	// Only trigger on simple status update.
	let currentTab = this.$postbox.$posttabs.current_tab().data('tab');
	if (currentTab !== 'status') {
		return;
	}

	// Skip if embed feature is disabled.
	let embedData = peepsodata.embed || {};
	let embedEnable = +embedData.enable;
	if (!embedEnable) {
		return;
	}

	// Do not overwrite current embed preview.
	if (!force && this.embedData) {
		return;
	}

	// Get the first matching URL.
	let postboxContent = this.$postbox.$textarea.val(),
		// Common TLDs does not need to have a scheme.
		reCommonTLD =
			/(^|\s)(https?:\/\/)?([a-z0-9-]+\.)+((com|net|org|int|edu|gov|mil|biz|info|mobi|co|io|me)(\.[a-z]{2})?)(?![a-z])(:\d+)?(\/[^\s]*)?/gi,
		// Other TLDs need to have a scheme to make sure it is a URL.
		reOtherTLD = /(^|\s)(https?:\/\/)([a-z0-9-]+\.)+([a-z]{2,24})(:\d+)?(\/[^\s]*)?/gi,
		url;

	// Skip if no url is found.
	url = postboxContent.match(reCommonTLD) || postboxContent.match(reOtherTLD);
	url = url && url[0] && url[0].trim();
	if (!url) {
		return;
	}

	// Automatically add HTTPS by default if no scheme is provided.
	if (!url.match(/^https?:\/\//i)) {
		url = `https://${url}`;
	}

	// Disable embedding insecure content if setting is disabled.
	let embedEnableNonSSL = +embedData.enable_non_ssl;
	if (!embedEnableNonSSL && !url.match(/^https:\/\//i)) {
		return;
	}

	// Do not re-fetch previously removed embed preview.
	if (this.embedExceptions && this.embedExceptions.indexOf(url) > -1) {
		return;
	}

	// Set the flag.
	this.fetching = true;

	let $postboxcontainer = this.$postbox.$textarea.closest('.ps-postbox-input');
	let $loading = this.$postbox.find('.ps-postbox-loading').show();
	let $buttons = this.$postbox.find('.ps-postbox-action').css('display', 'none');

	peepso.modules.url
		.getEmbed(url)
		.then(data => {
			this.embedData = url;

			// Apply wrapper HTML.
			let html = `<div class="ps-postbox__url-preview url-preview ps-stream-container-narrow">
				<div class="ps-postbox__url-close close">
					<a href="#" class="ps-js-remove"><i class="gcis gci-times"></i></a>
				</div>
				${data.html}
			</div>`;

			// Manually fix problem with WP Embed as described here:
			// https://core.trac.wordpress.org/ticket/34971
			html = html.replace(/\/embed\/(#\?secret=[a-zA-Z0-9]+)?"/g, '/?embed=true$1"');

			this.$url_preview_container.html(html).appendTo($postboxcontainer);
			this.$url_preview_container.on('click.ps-embed-remove', '.ps-js-remove', () => {
				this.$url_preview_container.off('click.ps-embed-remove');
				this.$url_preview_container.empty().remove();
				this.embedData = null;
				// Add to embed exception list until the next post update.
				this.embedExceptions.push(url);
			});

			// Fix Instagram embed issue.
			if (html.match(/\sdata-instgrm-permalink/)) {
				setTimeout(function () {
					try {
						window.instgrm.Embeds.process();
					} catch (e) {}
				}, 1000);
			}
		})
		.catch(() => {})
		.then(() => {
			this.fetching = false;
			$loading.hide();
			$buttons.css('display', 'flex');
		});
};

/**
 * Appends a newly added post's HTML to the activity stream
 * @param  {array} json The AJAX response from add_post
 */
PsPostboxLegacy.prototype.append_to_stream = function (json) {
	// do not proceed if post_id is not found
	if (!(json.data && +json.data.post_id)) {
		return;
	}

	if (jQuery('#ps-no-posts').length > 0) {
		// special case for stream/profile when no posts are showing
		jQuery(this.$activity_stream.css('display', 'block'));
		jQuery('#ps-no-posts').remove();
	}

	// hook up the drop-down menu within the new post
	var post_id = json.data.post_id,
		html = json.data.html;

	// Manually fix problem with WP Embed as described here:
	// https://core.trac.wordpress.org/ticket/34971
	html = html.replace(/\/embed\/(#\?secret=[a-zA-Z0-9]+)*"/g, '/?embed=true$1"');

	// #5206 Add an extra identifier for WP Embed loaded in the activity stream.
	html = html.replace(/\/\?embed=true([#"])/g, '/?embed=true&peepso=1$1');

	// Filter posts.
	var $post = peepso.observer.applyFilters('peepso_activity', jQuery(html), { inLoop: false });

	// Filter contents.
	$post
		.find('.ps-js-activity-content, .ps-js-activity-quote, .ps-comment-item')
		.each(function () {
			var $post = jQuery(this),
				html = $post.html();

			html = peepso.observer.applyFilters('peepso_activity_content', html);
			$post.html(html);
		});

	var $pinned = this.$activity_stream
		.children('.ps-js-activity-pinned')
		.not('.ps-js-activity-pinned-notop');

	// Insert new pinned posts before existing pinned posts, while new regular posts after existing pinned posts.
	if ($pinned.length) {
		let $newPinnedPosts = $post
			.filter('.ps-js-activity-pinned')
			.not('.ps-js-activity-pinned-notop');

		let $newRegularPosts = $post.not($newPinnedPosts);

		$newPinnedPosts.length && $newPinnedPosts.prependTo(this.$activity_stream);
		$newRegularPosts.length && $newRegularPosts.insertAfter($pinned.last());
	} else {
		$post.prependTo(this.$activity_stream);
	}

	// Trigger set human-readable update.
	$post.each(function () {
		if (1 === this.nodeType) {
			peepso.hooks.doAction('post_added', this);
		}
	});

	// Show the new post.
	$post.fadeIn('slow', function () {
		var $post = jQuery(this);

		$post.find('.ps-js-comment-container').hide();

		jQuery(document).trigger('ps_activitystream_append', [
			jQuery('#peepso-wrap .ps-js-activity--' + post_id + ' .ps-js-dropdown-toggle')
		]);

		// Scroll to the post if it is not currently visible.
		peepso.util.scrollIntoViewIfNeeded(this, { position: 'center' });
	});

	// Initialize save post button.
	peepso.observer.doAction('peepso_activity_actions', $post.find('[data-type=stream-action]'));

	// Fix Instagram embed issue.
	if (html.match(/\sdata-instgrm-permalink/)) {
		setTimeout(function () {
			try {
				window.instgrm.Embeds.process();
			} catch (e) {}
		}, 1000);
	}

	peepso.observer.applyFilters('peepso_posttabs_cancel-status');
};

/**
 * Scan for broken thumbnails and remove them.
 */
PsPostboxLegacy.prototype.remove_broken_thumbnails = function () {
	jQuery('.ps-media-thumbnail img').each(function () {
		var tester = new Image();
		var img = this;
		tester.onerror = function () {
			jQuery(img).closest('.ps-media-thumbnail').remove();
		};
		tester.src = img.src;
	});
};

window.postbox = new PsPostboxLegacy();

jQuery(document).ready(function () {
	postbox.init();
});

/**
 * Workaround for IE11 placeholder support.
 */
jQuery(function () {
	jQuery.support.placeholder = false;
	var webkit_type = document.createElement('input');
	if ('placeholder' in webkit_type) jQuery.support.placeholder = true;

	if (!jQuery.support.placeholder) {
		var active = document.activeElement;
		jQuery('textarea')
			.focus(function () {
				if (
					jQuery(this).attr('placeholder') &&
					jQuery(this).attr('placeholder').length > 0 &&
					'' !== jQuery(this).attr('placeholder') &&
					jQuery(this).val() === jQuery(this).attr('placeholder')
				) {
					jQuery(this).val('').removeClass('hasPlaceholder');
				}
			})
			.blur(function () {
				if (
					jQuery(this).attr('placeholder') &&
					jQuery(this).attr('placeholder').length > 0 &&
					'' !== jQuery(this).attr('placeholder') &&
					('' === jQuery(this).val() ||
						jQuery(this).val() === jQuery(this).attr('placeholder'))
				) {
					jQuery(this).val(jQuery(this).attr('placeholder')).addClass('hasPlaceholder');
				}
			});

		jQuery('textarea').blur();
		jQuery(active).focus();
		jQuery('form').submit(function () {
			jQuery(this)
				.find('.hasPlaceholder')
				.each(function () {
					jQuery(this).val('');
				});
		});
	}
});

// EOF
