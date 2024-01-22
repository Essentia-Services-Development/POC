(function ($, peepso, factory) {
	factory($, peepso, peepso.observer);
})(jQuery || $, peepso, function ($, peepso, observer) {
	function PsTags() {}

	PsTags.prototype.init = function () {
		var _self = this;

		this.taggable_inputs = observer.applyFilters('peepsotags_taggable_inputs', [
			'#postbox-main textarea.ps-postbox-textarea'
		]);

		this.init_tags(this.taggable_inputs.join(','));

		// Separate comments, we need to add post ID to the request, to get comment participants
		this.init_tags_comments();
		$(document).on(
			'peepso_tags_init_comments ps_activitystream_append ps_activitystream_loaded peepso_repost_added',
			function () {
				_self.init_tags_comments();
			}
		);

		// Handle newly-added comment.
		$(document).on('ps_comment_added ps_comment_save', function () {
			_self.init_tags_comments();
		});

		observer.addFilter(
			'postbox_req_edit',
			function (req, sel) {
				sel.ps_tagging('val', function (val) {
					req.post = val;
				});
				return req;
			},
			10,
			2
		);

		observer.addFilter(
			'comment_req',
			function (req, sel) {
				$(sel).ps_tagging('val', function (val) {
					req.content = val;
					req.post = val;
				});
				return req;
			},
			10,
			2
		);

		observer.addFilter(
			'comment_cancel',
			function (sel) {
				$(sel).ps_tagging('reset');
			},
			10,
			2
		);

		observer.addFilter(
			'modalcomments.afterchange',
			function (lightbox) {
				if (lightbox && lightbox.$attachment) {
					lightbox.$attachment.find('.ps-comment-reply textarea').ps_tagging();
				}
			},
			10,
			2
		);

		observer.addFilter(
			'caption_req',
			function (req, sel) {
				$(sel).ps_tagging('val', function (val) {
					req.description = val;
				});
				return req;
			},
			10,
			2
		);

		observer.addFilter(
			'comment.reply',
			(textarea, data) => {
				if (data.id != peepsodata.currentuserid) {
					textarea.ps_tagging('val', value => {
						if (data.id && data.name) {
							let template = _.template(peepsotagsdata.template);
							let newValue = template({ id: data.id, title: data.name });

							value = value.trim();
							if (!value) {
								value = `${newValue} `;
							} else if (value.indexOf(newValue) === -1) {
								value = `${value} ${newValue} `;
							}

							textarea.val(value);
							textarea.removeData('ps_tagging');
							this.init_tags_comments(textarea);
						}
					});
				}
			},
			10,
			2
		);

		// Re-initialize tagging on postbox.
		observer.addAction('postbox_reinit', $textarea => {
			$textarea.removeData('ps_tagging');
			this.init_tags($textarea);
			setTimeout(() => $textarea.trigger('keyup').trigger('input'), 1000);
		});

		$('#peepso-wrap').on('comment.saved', function (e, post_id, sel, req) {
			$(sel).ps_tagging('reset');
			return;
		});

		$('#peepso-wrap').on('post_edit.shown', function (e, post_id, html) {
			var textarea = html.find('textarea');
			_self.init_tags(textarea);
		});

		observer.addAction(
			'comment_edit',
			$.proxy(function (post_id, elem) {
				var textarea = $(elem).find('textarea');
				this.init_tags(textarea);
			}, this),
			10,
			2
		);

		observer.addAction(
			'postbox_update',
			$.proxy(function (postbox) {
				this.init_tags(postbox.$text);
			}, this),
			10,
			1
		);

		observer.addFilter(
			'postbox_data',
			function (data, postbox) {
				postbox.$text.ps_tagging('val', function (value) {
					data.content = value;
				});
				return data;
			},
			10,
			2
		);
	};

	PsTags.prototype.init_tags = function (selector) {
		var focusFetch = false,
			focusAfter = false,
			taggable;

		// do when element get focus
		$(selector).one('focus.get_taggable', function () {
			var req = observer.applyFilters('tags_get_taggable_params', {});
			focusFetch = true;
			peepso.postJson('tagsajax.get_taggable', req, function (response) {
				if (response.success) {
					taggable = response.data.users;
				}
				if (typeof focusAfter === 'function') {
					focusAfter(taggable || []);
				}
				focusFetch = false;
			});
		});

		$(selector).ps_tagging({
			syntax: _.template(peepsotagsdata.template, { interpolate: /<%=([\s\S]+?)%>/g }),
			parser: new RegExp(peepsotagsdata.parser, 'gi'),
			parser_groups: { id: 1, title: 2 },
			fetcher: function (query, callback) {
				if (taggable) {
					callback(taggable, 'cache');
					return;
				}
				if (focusFetch) {
					focusAfter = callback;
					return;
				}
				var req = observer.applyFilters('tags_get_taggable_params', {});
				peepso.postJson('tagsajax.get_taggable', req, function (response) {
					if (response.success) {
						taggable = response.data.users;
					}
					callback(taggable || []);
				});
			}
		});
	};

	PsTags.prototype.init_tags_comments = function (selector) {
		if (!selector) {
			selector = '[data-type="stream-newcomment"] textarea[name="comment"]';
		}

		$(selector).each(function (index, elem) {
			var focusFetch = false,
				focusAfter = false,
				taggable;

			// do when element get focus
			$(elem).off('focus.get_taggable');
			$(elem).one('focus.get_taggable', function () {
				var req = observer.applyFilters('tags_get_taggable_params', {
					act_id: $(elem).data('act-id')
				});
				focusFetch = true;
				peepso.postJson('tagsajax.get_taggable', req, function (response) {
					if (response.success) {
						taggable = response.data.users;
					}
					if (typeof focusAfter === 'function') {
						focusAfter(taggable || []);
					}
					focusFetch = false;
				});
			});

			$(elem).ps_tagging({
				syntax: _.template(peepsotagsdata.template, { interpolate: /<%=([\s\S]+?)%>/g }),
				parser: new RegExp(peepsotagsdata.parser, 'gi'),
				parser_groups: { id: 1, title: 2 },
				fetcher: function (query, callback) {
					if (taggable) {
						callback(taggable, 'cache');
						return;
					}
					if (focusFetch) {
						focusAfter = callback;
						return;
					}
					var req = observer.applyFilters('tags_get_taggable_params', {
						act_id: $(elem).data('act-id')
					});
					peepso.postJson('tagsajax.get_taggable', req, function (response) {
						if (response.success) {
							taggable = response.data.users;
						}
						callback(taggable || []);
					});
				}
			});

			observer.addFilter(
				'comment_can_submit',
				function (obj) {
					var inst = $(obj.el).data('ps_tagging');
					if (inst.dropdown_is_visible) {
						obj.can_submit = false;
					}
					return obj;
				},
				20,
				1
			);

			$(elem).ps_autosize();
		});
	};

	/**
 * Force line-breaks on word-wrapped textarea value
 * http://stackoverflow.com/a/19743610/2526639
 // TODO: document the {sel} parameter
 * @param {object} The DOM element
 */
	PsTags.prototype.apply_line_breaks = function (sel) {
		var oTextarea = sel;

		if (oTextarea.wrap) {
			oTextarea.setAttribute('wrap', 'off');
		} else {
			oTextarea.setAttribute('wrap', 'off');
			/*		var newArea = oTextarea.cloneNode(true);
		newArea.value = oTextarea.value;
		oTextarea.parentNode.replaceChild(newArea, oTextarea);
		oTextarea = newArea; */
		}

		var strRawValue = oTextarea.value;
		oTextarea.value = '';
		var nEmptyWidth = oTextarea.scrollWidth;
		var nLastWrappingIndex = -1;

		// TODO: docblock
		function testBreak(strTest) {
			oTextarea.value = strTest;
			return oTextarea.scrollWidth > nEmptyWidth;
		}

		// TODO: docblock
		function findNextBreakLength(strSource, nLeft, nRight) {
			var nCurrent;
			if ('undefined' === typeof nLeft) {
				nLeft = 0;
				nRight = -1;
				nCurrent = 64;
			} else {
				if (-1 === nRight) {
					nCurrent = nLeft * 2;
				} else if (nRight - nLeft <= 1) return Math.max(2, nRight);
				else nCurrent = nLeft + (nRight - nLeft) / 2;
			}
			var strTest = strSource.substr(0, nCurrent);
			var bLonger = testBreak(strTest);
			if (bLonger) {
				nRight = nCurrent;
			} else {
				if (nCurrent >= strSource.length) return null;
				nLeft = nCurrent;
			}
			return findNextBreakLength(strSource, nLeft, nRight);
		}

		var i = 0,
			j;
		var strNewValue = '';
		while (i < strRawValue.length) {
			var breakOffset = findNextBreakLength(strRawValue.substr(i));
			if (null === breakOffset) {
				strNewValue += strRawValue.substr(i);
				break;
			}
			nLastWrappingIndex = -1;
			var nLineLength = breakOffset - 1;
			for (j = nLineLength - 1; j >= 0; j--) {
				var curChar = strRawValue.charAt(i + j);
				if (' ' === curChar || '-' === curChar || '+' === curChar) {
					nLineLength = j + 1;
					break;
				}
			}
			strNewValue += strRawValue.substr(i, nLineLength) + '\n';
			i += nLineLength;
		}

		oTextarea.value = strNewValue;
		oTextarea.setAttribute('wrap', '');
	};

	var ps_tags = new PsTags();

	$(function () {
		ps_tags.init();

		$('.ps-postbox-tab.interactions .ps-button-cancel').on('click', function () {
			$('#postbox-main textarea.ps-postbox-textarea').ps_tagging('reset');
		});

		$('#postbox-main').on('postbox.post_cancel', function () {
			$('#postbox-main textarea.ps-postbox-textarea').ps_tagging('reset');
		});
	});

	/**
	 * Initialize tagging on postbox initialization.
	 * @param {Array} addons An array of addons that are being plugged in to the Postbox.
	 */
	observer.addFilter(
		'peepso_postbox_addons',
		function (addons) {
			addons.push({
				init: $.noop,
				set_postbox: function (postbox) {
					var isMain = postbox.attr('id') === 'postbox-main';
					var isMessage = postbox.hasClass('ps-postbox-message');

					if (!(isMain || isMessage)) {
						return;
					}

					if (!postbox.$textarea.hasClass('ps-tagging-textarea')) {
						ps_tags.init_tags(postbox.$textarea);
					}

					// Handle main postbox only.
					observer.addFilter(
						'postbox_req_' + postbox.guid,
						function (req) {
							$('#postbox-main textarea.ps-postbox-textarea')
								.eq(0)
								.ps_tagging('val', function (val) {
									req.content = val;
								});
							return req;
						},
						10,
						1
					);
				}
			});

			return addons;
		},
		10,
		1
	);
});
