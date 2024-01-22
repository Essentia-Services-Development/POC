(function ($, peepso, peepsodata) {
	const { observer, util, ContentEditable } = peepso;
	const { backgrounds: backgroundsData = {} } = peepsodata;

	// Configurations.
	const POST_MAX_LENGTH = +backgroundsData.post_max_length || 0;
	const POST_MAX_LINEBREAKS = +backgroundsData.post_max_linebreaks || 0;

	class PostboxBackground {
		constructor($postbox) {
			this.$postbox = $postbox;
			this.$postboxTab = this.$postbox.$posttabs;
			this.$postboxBtnPreview = this.$postbox.find('.ps-js-btn-preview');
			this.$postboxStatusTextarea = this.$postbox.$textarea;
			this.$postboxStatus = this.$postboxStatusTextarea.closest('.ps-postbox-status');
			this.$postboxBackground = this.$postbox.find(
				'.ps-postbox-tabs [data-tab-id=post_backgrounds]'
			);
			this.$postboxBackgroundShortcut = this.$postbox.find('#post_backgrounds');

			this.$presets = this.$postboxBackground.find('.peepso-background-item');
			this.$background = this.$postboxBackground.find('.ps-js-post-background');
			this.$text = this.$postboxBackground.find('.ps-js-post-background-text');
			this.$warning = this.$postboxBackground.find('.ps-js-activity-background-warning');

			this.$postboxTab.on('peepso_posttabs_show-post_backgrounds', () => this.show());
			this.$postboxTab.on('peepso_posttabs_cancel-post_backgrounds', () => this.cancel());
			this.$postboxTab.on('peepso_posttabs_submit-post_backgrounds', () => this.post());
			this.$postboxBackgroundShortcut.on('click', () => {
				this.$postboxTab.find('[data-tab=post_backgrounds]').click();
			});

			this.$presets.on('click', e => this.select(e.currentTarget));

			this.editor = new ContentEditable(this.$text[0], {
				onChange: this.onChange.bind(this),
				transform: this.contentTransform.bind(this)
			});

			observer.addAction(
				'postbox_type_set',
				($postbox, type) => {
					if ($postbox === this.$postbox && type === 'post_backgrounds') {
						this.$postboxBackgroundShortcut.trigger('click');
					}
				},
				10,
				2
			);

			observer.addFilter(
				'peepso_postbox_can_submit',
				(flags, $postbox) => {
					if ($postbox === this.$postbox) {
						let type = this.$postbox.$posttabs.current_tab_id;
						if ('post_backgrounds' === type) {
							let text = this.editor.value();
							text = text.replace(/@peepso_user_(\d+)\(([\s\S]*?)\)/g, '$2');

							if (!text) {
								flags.hard.push(false);
								this.$warning.hide();
							} else if (POST_MAX_LENGTH && text.length > POST_MAX_LENGTH) {
								flags.hard.push(false);
								this.$warning.show();
							} else {
								flags.hard.push(true);
								this.$warning.hide();
							}
						}
					}

					return flags;
				},
				10,
				2
			);
		}

		/**
		 * Show the postbox background type.
		 */
		show() {
			this.$postboxBtnPreview.hide();
			this.$postboxStatus.hide();
			this.$postboxBackground.show();

			// Update text content from the original textarea.
			let contentObj = { content: this.$postboxStatusTextarea.val() };
			contentObj = observer.applyFilters(`postbox_req_${this.$postbox.guid}`, contentObj);
			this.editor.value(contentObj.content);

			// Call in-place content transform hook.
			observer.doAction('postbox_content_update', this.$text[0], this.editor);

			// Select the first preset if nothing is selected.
			let $selected = this.$presets.filter('.active');
			if (!$selected.length) {
				this.select(this.$presets.eq(0));
			}

			this.$postbox.on_change();
		}

		/**
		 * Select a background preset.
		 *
		 * @param {Element|JQuery} preset
		 */
		select(preset) {
			let $preset = $(preset),
				bgImage = $preset.css('background-image'),
				bgColor = $preset.attr('data-background'),
				textColor = $preset.attr('data-text-color'),
				id = $preset.attr('data-preset-id');

			// Update background and text.
			this.$background.css('background-image', bgImage).attr('data-background', bgColor);
			this.$text
				.css('color', textColor)
				.attr('data-text-color', textColor)
				.attr('data-preset-id', id);

			// Update selected preset.
			$preset.addClass('active');
			this.$presets.not($preset).removeClass('active');

			// Sync placeholder text color.
			if (!this.__styleOverride) {
				this.__styleOverride = document.createElement('style');
				this.__styleOverride.id = 'peepso-post-background';
				document.head.appendChild(this.__styleOverride);
			}
			this.__styleOverride.innerHTML = `.ps-post__background-text:before { color: ${textColor} !important }`;

			// Focus on the "textarea".
			this.focus();
		}

		cancel() {
			this.select(this.$presets.eq(0));
			this.editor.value('');

			observer.doAction('postbox_reinit', this.$postboxStatusTextarea);
		}

		post() {
			let filterName = 'postbox_req_' + this.$postbox.guid;

			observer.addFilter(filterName, this.postSetRequest, 10, 1, this);
			this.$postbox.save_post();
			observer.removeFilter(filterName, this.postSetRequest, 10);
		}

		/**
		 * Update the post data.
		 *
		 * @param {Object} data
		 * @returns {Object}
		 */
		postSetRequest(data) {
			data.type = 'post_backgrounds';
			data.content = this.editor.value();
			data.preset_id = this.$text.attr('data-preset-id');
			data.text_color = this.$text.attr('data-text-color');
			data.background = this.$background.attr('data-background');

			return data;
		}

		/**
		 * Handle focus.
		 */
		focus() {
			setTimeout(() => {
				let value = this.editor.value();

				if (value) {
					let selection = window.getSelection();
					let children = this.$text[0].children;
					let lastChild = children[children.length - 1];

					// TODO
				} else {
					// Should just focus on the element if it is empty.
					this.$text[0].focus();
				}
			}, 0);
		}

		contentTransform(elem, editor) {
			let maxLinebreaks = POST_MAX_LINEBREAKS;
			let linebreaks = [...elem.querySelectorAll('br')];

			// Skip ending linebreak.
			linebreaks.pop();

			// Respect max_linebreaks setting.
			linebreaks.forEach(node => {
				if (--maxLinebreaks < 0) {
					// Concatenate sibling texts if necessary.
					let prev = node.previousSibling;
					if (prev && prev.nodeType === Node.TEXT_NODE) {
						let next = node.nextSibling;
						if (next && next.nodeType === Node.TEXT_NODE) {
							let printable = next.textContent.replace(/\u200D/g, '').length;
							prev.textContent += `${printable ? ' ' : ''}` + next.textContent;
							next.remove();
						}
					}

					node.remove();
				}
			});

			// Call in-place content transform hook.
			observer.doAction('postbox_content_transform', elem, editor);
		}

		onChange() {
			this.$postboxStatusTextarea.val(this.editor.value());
			this.$postboxStatusTextarea.trigger('keyup');
			this.$postboxBtnPreview.hide();

			// Call in-place content transform hook.
			observer.doAction('postbox_content_change', this.$text[0], this.editor);
		}
	}

	// Initialize class on main postbox initialization.
	observer.addAction(
		'peepso_postbox_addons',
		addons => {
			let wrapper = {
				init() {},
				set_postbox($postbox) {
					if ($postbox.find('#post_backgrounds').length) {
						new PostboxBackground($postbox);
					}
				}
			};

			addons.push(wrapper);
			return addons;
		},
		10,
		1
	);

	class PsPostBackgroundActivity {
		editPost(post_id, act_id) {
			let $activity_html = $('.ps-post[data-id="' + act_id + '"]');

			// cancel if already edit mode
			if ($activity_html.find('.ps-postbox-textarea').length) {
				return;
			}

			// move postbox below the attachment
			$activity_html
				.find('.ps-js-activity-edit')
				.detach()
				.appendTo('.ps-post[data-id="' + act_id + '"] .ps-post__body');

			// trigger original edit event
			activity.option_edit(post_id, act_id).then(() => {
				let $postbox = $activity_html.find('.ps-postbox-content');
				let $textarea = $postbox.find('textarea');

				let $hidden = $postbox.find('[type=hidden]');
				let $editor = $activity_html.find('.post-backgrounds-content');
				let $warning = $activity_html.find('.ps-js-activity-background-warning');
				let $submit = $postbox.parent().find('.postbox-submit');
				let originalHTML = $editor[0].innerHTML;

				// Hide original postbox and show new editor.
				$postbox.hide();
				$editor.attr('contenteditable', 'true');

				let editor = new ContentEditable($editor[0], {
					onChange: function () {
						let text = editor.value();

						$textarea.add($hidden).val(text);
						$textarea.trigger('keyup');

						if (!text) {
							$submit.hide();
							$warning.hide();
						} else if (POST_MAX_LENGTH && text.length > POST_MAX_LENGTH) {
							$submit.hide();
							$warning.show();
						} else {
							$submit.show();
							$warning.hide();
						}

						// Call in-place content transform hook.
						$textarea.val(editor.value());
						observer.doAction('postbox_content_change', $editor[0], editor);
					},
					transform: function (elem, editor) {
						let maxLinebreaks = POST_MAX_LINEBREAKS;
						let linebreaks = [...elem.querySelectorAll('br')];

						// Skip ending linebreak.
						linebreaks.pop();

						// Respect max_linebreaks setting.
						linebreaks.forEach(node => {
							if (--maxLinebreaks < 0) {
								// Concatenate sibling texts if necessary.
								let prev = node.previousSibling;
								if (prev && prev.nodeType === Node.TEXT_NODE) {
									let next = node.nextSibling;
									if (next && next.nodeType === Node.TEXT_NODE) {
										let printable = next.textContent.replace(
											/\u200D/g,
											''
										).length;
										prev.textContent +=
											`${printable ? ' ' : ''}` + next.textContent;
										next.remove();
									}
								}

								node.remove();
							}
						});

						// Call in-place content transform hook.
						observer.doAction('postbox_content_transform', elem, editor);
					}
				});

				// Update text content from the original textarea.
				setTimeout(function () {
					let postbox = $postbox.closest('.ps-js-activity-edit').data('ps-postbox');
					let $textarea = postbox.$text;
					let originalValue = $textarea.val();

					$textarea.ps_tagging('val', function (value) {
						if (value !== originalValue) {
							$textarea.val(value);
							editor.value(value);
							observer.doAction('postbox_content_update', $editor[0], editor);
						}
					});
				}, 100);

				function filterData(data, postbox) {
					if (postbox === $postbox.closest('.ps-js-activity-edit').data('ps-postbox')) {
						data.content = editor.value();
					}

					return data;
				}

				observer.addFilter('postbox_data', filterData, 20, 2);

				// on cancel button
				$activity_html.find('.ps-button-cancel').on('click', () => {
					$editor.removeAttr('contenteditable').html(originalHTML);
					observer.removeFilter('postbox_data', filterData, 20, 2);
				});
			});
		}
	}

	window.PsPostBackground = new PsPostBackgroundActivity();
})(jQuery, window.peepso, window.peepsodata);
