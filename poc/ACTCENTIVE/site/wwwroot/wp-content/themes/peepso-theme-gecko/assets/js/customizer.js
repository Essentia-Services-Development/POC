jQuery(function ($) {
	var _data = window.gecko_customizer || {};

	var PRESETS = _data.presets || {};
	var TEXT_SAVE = _data.text_save;
	var TEXT_SAVED = _data.text_saved;
	var TEXT_PUBLISH = _data.text_publish;
	var TEXT_PUBLISHED = _data.text_published;
	var TEXT_NOTICE_PUBLISH = _data.text_notice_publish || '';
	var TEXT_NOTICE_UNSAVED = _data.text_notice_unsaved || '';
	var TEXT_NOTICE_RELOAD = _data.text_notice_reload || '';
	var TEXT_NOTICE_RESET = _data.text_notice_reset || '';
	var TEXT_NOTICE_DELETE = _data.text_notice_delete || '';

	function Customizer(element) {
		this.init(element);
	}

	$.extend(Customizer.prototype, {
		/**
		 * Published preset.
		 *
		 * @type {string}
		 */
		publishedPreset: null,

		/**
		 * Selected preset.
		 *
		 * @type {string}
		 */
		selectedPreset: null,

		/**
		 * Flag to determine if the selected preset is read-only.
		 *
		 * @type {boolean}
		 */
		readonly: true,

		/**
		 * Flag to determine if the selected preset is modified.
		 *
		 * @type {boolean}
		 */
		modified: false,

		/**
		 * Backend settings.
		 *
		 * @type {Object}
		 */
		settings: {},

		/**
		 * CSS variable settings.
		 *
		 * @type {Object}
		 */
		cssVars: {},

		/**
		 * Initialize customizer.
		 *
		 * @param {Element} el
		 */
		init: function (el) {
			this.$el = $(el);

			this.$optionsLabel = this.$el.find('.gc-js-options-label');
			this.$optionsLess = this.$el.find('.gc-js-options-less');
			this.$optionsMore = this.$el.find('.gc-js-options-more');
			this.$preset = this.$el.find('.gc-js-preset');
			this.$settings = this.$el.find('.gc-js-settings');
			this.$preview = this.$el.find('.gc-js-preview');
			this.$iframe = this.$preview.find('iframe[name=gecko-customizer-preview]');
			this.$actions = this.$el.find('.gc-js-actions');
			this.$actionWarningDesc = this.$el.find('.gc-js-action-warning-desc');
			this.$saveAsForm = this.$el.find('.gc-js-save-as-form');
			this.$renameForm = this.$el.find('.gc-js-rename-form');

			// Buttons.
			this.$btnMain = this.$el.find('.gc-js-btn-main');
			this.$btnMainMore = this.$el.find('.gc-js-btn-main-more');
			this.$btnSave = this.$el.find('.gc-js-btn-save');
			this.$btnPublish = this.$el.find('.gc-js-btn-publish');
			this.$btnReset = this.$el.find('.gc-js-btn-reset');
			this.$btnSaveAs = this.$el.find('.gc-js-btn-save-as');
			this.$btnRename = this.$el.find('.gc-js-btn-rename');
			this.$btnDelete = this.$el.find('.gc-js-btn-delete');
			this.$btnMore = this.$el.find('.gc-js-btn-more');
			this.$btnBack = this.$el.find('.gc-js-btn-back');
			this.$btnViewports = this.$el.find('.gc-js-btn-viewport');

			// Event handlers.
			this.$btnMain.on('click', $.proxy(this.onMain, this));
			this.$btnMainMore.on('click', $.proxy(this.onMainMore, this));
			// this.$optionsLabel.on('click', $.proxy(this.toggleOptions, this));
			this.$preset.on('change', $.proxy(this.onPreset, this));
			this.$btnReset.on('click', $.proxy(this.onReset, this));
			this.$btnSave.on('click', $.proxy(this.onSave, this));
			this.$btnPublish.on('click', $.proxy(this.onPublish, this));
			this.$btnSaveAs.on('click', $.proxy(this.onSaveAs, this));
			this.$btnRename.on('click', $.proxy(this.onRename, this));
			this.$btnDelete.on('click', $.proxy(this.onDelete, this));
			this.$btnMore.on('click', $.proxy(this.onMore, this));
			this.$btnBack.on('click', $.proxy(this.onBack, this));
			this.$btnViewports.on('click', $.proxy(this.onViewport, this));

			this.$tabs = this.$el.find('.gc-js-customizer-tabs');
			this.$tabs.on('click', '.gc-js-customizer-tab', $.proxy(this.onTab, this));

			this.$el.on(
				'click',
				'.gc-js-optgroup-title, .gc-js-optgroup-desc',
				this.onOptGroup.bind(this)
			);

			// Set initial preset value.
			var preset = this.$preset.val();
			var presetConfig = PRESETS[preset] || {};
			this.publishedPreset = preset;
			this.selectedPreset = preset;
			this.modified = false;
			this.readonly = +presetConfig.readonly;

			// Populate initial backend settings values.
			$.proxy(function (preset) {
				for (var key in preset) {
					var input = this.$settings.find('[data-setting="' + key + '"]').get(0);
					this.settings[key] = input ? getInputValue(input) : preset[key];
				}
			}, this)(presetConfig.settings);

			// Populate initial CSS variable values.
			$.proxy(function (preset) {
				for (var key in preset) {
					var input = this.$settings.find('[data-var="' + key + '"]').get(0);
					this.cssVars[key] = input ? getInputValue(input) : preset[key];
				}
			}, this)(presetConfig.css_vars);

			// Initialize inputs.
			this.$settings.find('[data-setting], [data-var]').each(function () {
				initInput(this);
			});

			// Make sure input does not contain dynamic CSS variable.
			this.updateInputs();

			// Toggle action buttons accordingly based on the selected preset state.
			this.updateButtons();

			// // Set default toggle.
			// if (localStorage.get('customizer_hide_form')) {
			// 	this.toggleOptions('hide');
			// } else {
			// 	this.toggleOptions('show');
			// }

			// Handle value change.
			this.$settings.find('[data-setting], [data-var]').on(
				'gc-customizer-update',
				$.proxy(function (e) {
					var $input = $(e.currentTarget),
						data = $input.data(),
						value = getInputValue(e.currentTarget);

					if (!data.var) {
						this.settings[data.setting] = value;
						this.apply('temp').always($.proxy(this.reload, this));
					} else {
						this.cssVars[data.var] = value;
						this.updatePreview(data.var, value);
					}

					this.modified = true;

					this.updateInputs();
					this.updateButtons();
				}, this)
			);

			// Smooth transition on iframe load and reload events.
			this.$iframe.on('load', () => {
				// Re-apply unsaved CSS variable changes after iframe is loaded.
				for (var key in this.cssVars) {
					this.updatePreview(key, this.cssVars[key]);
				}

				// Re-apply the onbeforeunload event on every page load since it will be lost
				// when loading the new content.
				this.$iframe[0].contentWindow.onbeforeunload = () => {
					// Hide iframe before loading the next resource to reduce the flickering effect.
					this.$iframe.css('transition', 'none');
					this.$iframe.css('opacity', 0);
				};

				// Fade-in effect on load event.
				this.$iframe.css('transition', 'opacity 0.5s ease-in-out');
				this.$iframe.css('opacity', '');
			});

			// Handles initial config state.
			if (window.location.hash) {
				this.onTab(window.location.hash);
			}
		},

		/**
		 * Apply configuration.
		 *
		 * @param {string} mode
		 * @returns {JQueryDeferred}
		 */
		apply: function (mode) {
			var params = {
				action: 'gecko_customizer_apply',
				id: this.$preset.val(),
				settings: this.settings,
				css_vars: this.cssVars
			};

			if ('temp' === mode) {
				params.action = 'gecko_customizer_apply_temp';
			}

			return $.post(ajaxurl, params, null, 'json').done(json => {
				if (json.success) {
					if ('temp' !== mode) {
						$.post(ajaxurl, { action: 'gecko_customizer_clear_temp' });
					}

					if (json.data) {
						if (json.data.site_icon) {
							this.updateSiteIcon(json.data.site_icon);
						}
					}
				}
			});
		},

		/**
		 * Save preset.
		 *
		 * @param {string} name
		 * @param {boolean} publish
		 * @returns {JQueryDeferred}
		 */
		save: function (name, publish) {
			var params = {
				action: 'gecko_customizer_save_preset',
				id: this.$preset.val(),
				settings: this.settings,
				css_vars: this.cssVars,
				publish: publish ? 1 : 0
			};

			// Save as a custom name if parameter is provided.
			if ('string' === typeof name && name.trim()) {
				params.name = name.trim();
				delete params.id;
			}

			return $.post(ajaxurl, params, null, 'json').done(json => {
				if (json.success) {
					if (json.data) {
						if (json.data.site_icon) {
							this.updateSiteIcon(json.data.site_icon);
						}
					}
				}
			});
		},

		/**
		 * Rename preset.
		 *
		 * @param {string} id
		 * @returns {JQueryDeferred}
		 */
		rename: function (id, name) {
			var params = {
				action: 'gecko_customizer_rename_preset',
				id: id,
				name: name
			};

			return $.post(ajaxurl, params, null, 'json');
		},

		/**
		 * Delete preset.
		 *
		 * @param {string} id
		 * @returns {JQueryDeferred}
		 */
		delete: function (id) {
			var params = {
				action: 'gecko_customizer_delete_preset',
				id: id
			};

			return $.post(ajaxurl, params, null, 'json');
		},

		/**
		 * Smooth iframe reload transition.
		 */
		reload: function () {
			this.$iframe.css('transition', 'opacity 0.5s ease-in-out');
			this.$iframe.css('opacity', 0);
			setTimeout(() => {
				this.$iframe[0].contentWindow.location.reload(true);
			}, 700);
		},

		/**
		 * Sync input values based on the current settings.
		 */
		updateInputs: _.debounce(function () {
			var settings = this.settings;
			this.$settings.find('[data-setting]').each(function () {
				var key = this.getAttribute('data-setting');
				if ('undefined' !== typeof settings[key]) {
					setInputValue(this, settings[key]);
				}
			});

			var cssVars = resolveDynamicStyle(this.cssVars);
			this.$settings.find('[data-var]').each(function () {
				var key = this.getAttribute('data-var');
				if ('undefined' !== typeof cssVars[key]) {
					setInputValue(this, cssVars[key]);
				}
			});
		}, 1000),

		/**
		 * Update button states based on the current settings.
		 */
		updateButtons: _.debounce(function () {
			if (this.modified) {
				this.$btnReset.removeAttr('disabled');
				this.$btnSaveAs.attr('disabled', 'disabled');
			} else {
				this.$btnReset.attr('disabled', 'disabled');
				this.$btnSaveAs.removeAttr('disabled');
			}

			if (this.readonly) {
				this.$btnRename.attr('disabled', 'disabled');
				this.$actionWarningDesc.show();

				// Change the duplicate button color for readonly presets.
				this.$btnSaveAs.addClass('gcu-presets__action--pulse');
			} else {
				this.$btnRename.removeAttr('disabled');
				this.$actionWarningDesc.hide();

				// Reset the duplicate button color for custom presets.
				this.$btnSaveAs.removeClass('gcu-presets__action--pulse');
			}

			if (this.readonly || this.publishedPreset === this.selectedPreset) {
				this.$btnDelete.attr('disabled', 'disabled');
			} else {
				this.$btnDelete.removeAttr('disabled');
			}

			if (this.readonly) {
				this.$btnMainMore.hide();
				this.$btnMain.data('publish', 1);
				if (this.publishedPreset !== this.selectedPreset) {
					this.$btnMain.find('span').html(TEXT_PUBLISH);
					this.$btnMain.removeAttr('disabled');
				} else {
					this.$btnMain.find('span').html(TEXT_PUBLISHED);
					this.$btnMain.attr('disabled', 'disabled');
				}
			} else {
				if (this.publishedPreset === this.selectedPreset) {
					this.$btnMainMore.hide();
					this.$btnMain.data('publish', 1);
					if (this.modified) {
						this.$btnMain.find('span').html(TEXT_PUBLISH);
						this.$btnMain.removeAttr('disabled');
						this.$btnSave.removeAttr('disabled');
						this.$btnPublish.removeAttr('disabled');
					} else {
						this.$btnMain.find('span').html(TEXT_PUBLISHED);
						this.$btnMain.attr('disabled', 'disabled');
						this.$btnSave.attr('disabled', 'disabled');
						this.$btnPublish.attr('disabled', 'disabled');
					}
				} else {
					this.$btnMainMore.show();
					if (this.modified) {
						this.$btnMain.data('publish', 0);
						this.$btnMain.find('span').html(TEXT_SAVE);
						this.$btnMain.removeAttr('disabled');
						this.$btnSave.removeAttr('disabled');
						this.$btnPublish.removeAttr('disabled');
					} else {
						this.$btnMain.data('publish', 0);
						this.$btnMain.find('span').html(TEXT_SAVED);
						this.$btnMain.attr('disabled', 'disabled');
						this.$btnSave.attr('disabled', 'disabled');
						this.$btnPublish.removeAttr('disabled');
					}
				}
			}

			if ((this.modified && !this.readonly) || this.publishedPreset !== this.selectedPreset) {
				// Save original onbeforeunload handler, and attach a new onbeforeunload handler.
				if ('undefined' === typeof this.onbeforeunload) {
					this.onbeforeunload = window.onbeforeunload || function () {};
					window.onbeforeunload = function () {
						return TEXT_NOTICE_RELOAD;
					};
				}
			} else {
				// Detach new onbeforeunload handler, and restore original onbeforeunload handler.
				if ('undefined' !== typeof this.onbeforeunload) {
					window.onbeforeunload = this.onbeforeunload;
					delete this.onbeforeunload;
				}
			}
		}, 100),

		/**
		 * Notify preview iframe with CSS update.
		 *
		 * @param {string} key
		 * @param {string} value
		 */
		updatePreview: function (key, value) {
			var message = { type: 'css', key: key, value: value };
			this.$iframe[0].contentWindow.postMessage(message, '*');
		},

		/**
		 * Update site icon with provided URL.
		 *
		 * @param {string} url
		 */
		updateSiteIcon: function (url) {
			let links = document.querySelectorAll('link[rel*="icon"]');

			if (!url) {
				links.forEach(link => link.remove());
				return;
			}

			if (!links.length) {
				let link = document.createElement('link');
				link.rel = 'icon';
				document.getElementsByTagName('head')[0].appendChild(link);
				links = [link];
			}

			links.forEach(link => {
				link.href = url;
			});
		},

		/**
		 * Apply a preset.
		 *
		 * @param {string} preset
		 */
		applyPreset: function (preset) {
			var presetConfigs = PRESETS[preset] || {},
				presetCssVars = presetConfigs.css_vars || {};

			// Update active preset value.
			this.selectedPreset = preset;
			this.modified = false;
			this.readonly = +presetConfigs.readonly;

			// Update values.
			for (var key in presetCssVars) {
				if ('undefined' !== typeof this.cssVars[key]) {
					this.cssVars[key] = presetCssVars[key];
					this.updatePreview(key, presetCssVars[key]);
				}
			}

			// Update inputs.
			this.updateInputs();
			this.updateButtons();
		},

		// /**
		//  * Toggle options.
		//  *
		//  * @param {string} toggle
		//  */
		// toggleOptions: function (toggle) {
		// 	var $icon = this.$optionsLabel.children('i');
		//
		// 	if (['show', 'hide'].indexOf(toggle) === -1) {
		// 		toggle = this.$optionsLess.is(':visible') ? 'show' : 'hide';
		// 	}
		//
		// 	if ('show' === toggle) {
		// 		this.$optionsLess.hide();
		// 		this.$optionsMore.show();
		// 		$icon.attr('class', $icon.data('class-expand'));
		// 	} else {
		// 		this.$optionsLess.find('strong').html(this.$preset.find('option:selected').html());
		// 		this.$optionsMore.hide();
		// 		this.$optionsLess.show();
		// 		$icon.attr('class', $icon.data('class-collapse'));
		// 	}
		//
		// 	if ('hide' === toggle) {
		// 		localStorage.set('customizer_hide_form', 1);
		// 	} else {
		// 		localStorage.remove('customizer_hide_form');
		// 	}
		// },

		/**
		 * Main button handler.
		 *
		 * @param {Event} e
		 */
		onMain: function (e) {
			if (this.$btnMain.data('publish')) {
				this.onPublish(e);
			} else {
				this.onSave(e);
			}
		},

		/**
		 * Toggle main more options.
		 */
		onMainMore: function (e) {
			var $btn = this.$btnMainMore,
				$dropdown = $btn.closest('.gcu-js-dropdown').find('.gcu-js-dropdown-menu');

			$(document).off('click.gcu-js-dropdown-togg-more');

			if ($dropdown.is(':visible')) {
				$dropdown.hide();
			} else {
				$dropdown.show();
				setTimeout(function () {
					$(document).on('click.gcu-js-dropdown-togg-more', function () {
						$(document).off('click.gcu-js-dropdown-togg-more');
						$dropdown.hide();
					});
				}, 100);
			}
		},

		/**
		 * Preset selection handler.
		 *
		 * @param {Event} e
		 */
		onPreset: function (e) {
			var preset = this.$preset.val();

			if (this.selectedPreset !== preset) {
				if (!this.modified) {
					this.applyPreset(preset);
				} else {
					if (confirm(TEXT_NOTICE_UNSAVED)) {
						this.applyPreset(preset);
					} else {
						// Cancel change preset.
						this.$preset.val(this.selectedPreset);
						return;
					}
				}

				if (e) {
					if (+PRESETS[preset].readonly) {
						// Back after change preset for readonly preset.
						this.onBack();
					}
				}
			}
		},

		/**
		 * Reset button handler.
		 */
		onReset: function () {
			if (confirm(TEXT_NOTICE_RESET)) {
				this.applyPreset(this.$preset.val());
			}
		},

		/**
		 * Save button handler.
		 *
		 * @param {Event} e
		 */
		onSave: function (e) {
			var $btn = this.$btnSave,
				$icon = $btn.find('.gc-js-loading');

			$btn.attr('disabled', 'disabled');

			// Perform optimistic AJAX request.
			$icon.data('class', $icon.attr('class')).attr('class', $icon.data('class-loading'));
			this.save()
				.done(
					function (json) {
						if (json.success) {
							var preset = json.data && json.data.preset;
							if (preset && PRESETS[preset.id]) {
								PRESETS[preset.id] = preset;
							}
						}
					}.bind(this)
				)
				.always(
					function () {
						$icon.attr('class', $icon.data('class'));
						this.modified = false;
						this.updateButtons();
					}.bind(this)
				);
		},

		/**
		 * Publish button handler.
		 *
		 * @param {Event} e
		 */
		onPublish: function (e) {
			if (this.selectedPreset !== this.publishedPreset) {
				if (!confirm(TEXT_NOTICE_PUBLISH)) {
					return;
				}
			}

			var $btn = this.$btnPublish,
				$icon = $btn.find('.gc-js-loading');

			$btn.attr('disabled', 'disabled');

			// Perform optimistic AJAX request.
			$icon.data('class', $icon.attr('class')).attr('class', $icon.data('class-loading'));
			this.save(null, true)
				.done(
					function (json) {
						if (json.success) {
							var preset = json.data && json.data.preset;
							if (preset && PRESETS[preset.id]) {
								PRESETS[preset.id] = preset;
							}
							this.publishedPreset = this.selectedPreset;

							// Update default preset marker on the preset options.
							setTimeout(
								function () {
									var active = this.publishedPreset;
									this.$preset.children('option').each(function () {
										var value = this.value,
											label = this.textContent,
											marker = ' (Default)';

										if (value === active && label.indexOf(marker) === -1) {
											this.textContent = label + marker;
										} else if (value !== active && label.indexOf(marker) > -1) {
											this.textContent = label.replace(marker, '');
										}
									});
								}.bind(this),
								500
							);
						}
					}.bind(this)
				)
				.always(
					function () {
						$icon.attr('class', $icon.data('class'));
						this.modified = false;
						this.updateButtons();
					}.bind(this)
				);
		},

		/**
		 * Save-as button handler.
		 *
		 * @param {Event} e
		 */
		onSaveAs: function (e) {
			var $input = this.$saveAsForm.find('input[type=text]'),
				$cancel = this.$saveAsForm.find('.gc-js-cancel'),
				$save = this.$saveAsForm.find('.gc-js-save');

			// Just hide the form if it is already visible for easy toggle.
			if (this.$saveAsForm.is(':visible')) {
				this.$saveAsForm.hide();
				return;
			}

			var onCancel = $.proxy(function () {
				this.$saveAsForm.hide();
				$input.val('');
			}, this);

			var onSave = $.proxy(function () {
				var $btn = $save,
					$loading = $save.find('.gc-js-loading'),
					presetName = $input.val().trim();

				if (!presetName) {
					return;
				}

				$loading.show();
				$btn.attr('disabled', 'disabled');
				this.save(presetName)
					.done(onSaveSuccess)
					.always(function () {
						$loading.hide();
						$btn.removeAttr('disabled');
					});
			}, this);

			var onSaveSuccess = $.proxy(function (json) {
				if (json.success) {
					this.$saveAsForm.hide();

					// Add new preset option.
					if (json.data && json.data.preset) {
						var preset = json.data.preset || {};
						if (preset.id) {
							this.$preset.append(
								'<option value="' + preset.id + '">' + preset.label + '</option>'
							);

							// Append the new preset into the preset list.
							PRESETS[preset.id] = preset;

							// Select the new preset.
							this.$preset.val(preset.id);
							this.onPreset();
						}
					}
				}
			}, this);

			$cancel.off('click').on('click', onCancel);
			$save.off('click').on('click', onSave);
			this.$saveAsForm.show();

			// Set default preset name.
			var presetName = PRESETS[this.selectedPreset].label + ' copy';
			$input.val(presetName);

			$input.select();
		},

		/**
		 * Rename button handler.
		 *
		 * @param {Event} e
		 */
		onRename: function (e) {
			var $input = this.$renameForm.find('input[type=text]'),
				$cancel = this.$renameForm.find('.gc-js-cancel'),
				$save = this.$renameForm.find('.gc-js-save');

			// Just hide the form if it is already visible for easy toggle.
			if (this.$renameForm.is(':visible')) {
				this.$renameForm.hide();
				return;
			}

			var onCancel = $.proxy(function () {
				this.$renameForm.hide();
				$input.val('');
			}, this);

			var onSave = $.proxy(function () {
				var $btn = $save,
					$loading = $save.find('.gc-js-loading'),
					presetName = $input.val().trim();

				if (!presetName) {
					return;
				}

				$loading.show();
				$btn.attr('disabled', 'disabled');
				this.rename(this.selectedPreset, presetName)
					.done(onSaveSuccess)
					.always(function () {
						$loading.hide();
						$btn.removeAttr('disabled');
					});
			}, this);

			var onSaveSuccess = $.proxy(function (json) {
				if (json.success) {
					this.$renameForm.hide();

					// Update preset label.
					if (json.data && json.data.preset) {
						var preset = json.data.preset || {};
						if (preset.id) {
							var $opt = this.$preset.find('option[value="' + preset.id + '"]');
							if ($opt.length) {
								$opt.html(
									preset.label +
										(preset.id === this.publishedPreset ? ' (Default)' : '')
								);
							}

							if (PRESETS[preset.id]) {
								PRESETS[preset.id].label = preset.label;
							}
						}
					}
				}
			}, this);

			$cancel.off('click').on('click', onCancel);
			$save.off('click').on('click', onSave);
			this.$renameForm.show();

			// Set original preset name.
			var presetName = PRESETS[this.selectedPreset].label;
			$input.val(presetName);

			$input.select();
		},

		/**
		 * Delete button handler.
		 */
		onDelete: function () {
			if (!confirm(TEXT_NOTICE_DELETE)) {
				return;
			}

			var $btn = this.$btnDelete,
				$icon = $btn.find('.gc-js-loading'),
				$opt = this.$preset.find('option:selected'),
				id = this.$preset.val();

			var onSuccess = $.proxy(function () {
				$opt.remove();

				delete PRESETS[id];
				this.onPreset();
			}, this);

			// Perform AJAX request.
			$icon.data('class', $icon.attr('class')).attr('class', $icon.data('class-loading'));
			$btn.attr('disabled', 'disabled');
			this.delete(id).always(function () {
				$icon.attr('class', $icon.data('class'));
				$btn.removeAttr('disabled');
				onSuccess();
			});
		},

		/**
		 * Toggle more options.
		 */
		onMore: function (e) {
			var $btn = this.$btnMore,
				$dropdown = $btn.closest('.gcu-js-dropdown').find('.gcu-js-dropdown-menu');

			$(document).off('click.gcu-js-dropdown-togg');

			if ($dropdown.is(':visible')) {
				$dropdown.hide();
			} else {
				$dropdown.show();
				setTimeout(function () {
					$(document).on('click.gcu-js-dropdown-togg', function () {
						$(document).off('click.gcu-js-dropdown-togg');
						$dropdown.hide();
					});
				}, 100);
			}
		},

		/**
		 * Tab selection handler.
		 *
		 * @param {Event} e
		 * @param {string} tab
		 */
		onTab: function (e, tab) {
			if ('string' === typeof e) {
				tab = e;
			} else {
				e.preventDefault();
				tab = $(e.currentTarget).data('tab');
			}

			this.$tabs.addClass('hide');
			this.$btnBack.show();

			var $settings = this.$settings.filter(tab),
				$groups = $settings.find('.gc-js-optgroup');

			$groups.each(function (index) {
				var $group = $(this);
				$group.removeClass('open');
			});
			
			$settings.addClass('open');

			// Update URL hash.
			if (history && history.replaceState) {
				history.replaceState(undefined, undefined, tab);
			}
		},

		onCategory: function (e, tab) {
			this.$settings.filter(tab)
		},

		/**
		 * Optgroup selection handler.
		 *
		 * @param {Event} e
		 */
		onOptGroup: function (e) {
			var $group = $(e.currentTarget).closest('.gc-js-optgroup'),
				$siblings = $group.siblings('.gc-js-optgroup');

			$siblings.removeClass('open');
			$group.toggleClass('open');
		},

		/**
		 * Back button handler.
		 */
		onBack: function () {
			this.$tabs.removeClass('hide');
			this.$settings.removeClass('open');
			this.$btnBack.hide();

			// Remove URL hash.
			if (history && history.replaceState) {
				var url = window.location.href.replace(window.location.hash, '');
				history.replaceState(undefined, undefined, url);
			}
		},

		/**
		 * Viewport selection handler.
		 *
		 * @param {Event} e
		 */
		onViewport: function (e) {
			var $button = $(e.currentTarget),
				data = $button.data();

			this.$iframe.attr('width', data.width);
			this.$iframe.attr('height', data.height);

			this.$btnViewports.each(function () {
				var $item = $(this);
				if ($item.is($button)) {
					$item.addClass('active');
				} else {
					$item.removeClass('active');
				}
			});
		}
	});

	/**
	 * Initialize input.
	 *
	 * @param {Element} input
	 */
	function initInput(input) {
		var $input = $(input),
			data = $input.data();

		if ('color' === data.optionType) {
			initInputColor(input);
		} else if ('range' === data.optionType) {
			initInputRange(input);
		} else if ('image' === data.optionType) {
			initInputMedia(input);
		} else {
			$input.on(
				'checkbox' === input.type || 'SELECT' === input.tagName ? 'change' : 'input',
				function () {
					if (!this.__stopEvent) {
						$(this).trigger('gc-customizer-update');
					}
				}
			);
		}
	}

	/**
	 * Initialize color picker input.
	 *
	 * @see {@link http://automattic.github.io/Iris/}
	 *
	 * @param {Element} input
	 */
	function initInputColor(input) {
		var $input = $(input).hide(),
			$button = $input.next('button'),
			$label = $button.next('label'),
			pickr;

		pickr = Pickr.create({
			el: $button[0],
			theme: 'classic',
			default: null,
			appClass: 'gca-color-picker',
			swatches: [
				'rgba(244, 67, 54, 1)',
				'rgba(233, 30, 99, 0.95)',
				'rgba(156, 39, 176, 0.9)',
				'rgba(103, 58, 183, 0.85)',
				'rgba(63, 81, 181, 0.8)',
				'rgba(33, 150, 243, 0.75)',
				'rgba(3, 169, 244, 0.7)',
				'rgba(0, 188, 212, 0.7)',
				'rgba(0, 150, 136, 0.75)',
				'rgba(76, 175, 80, 0.8)',
				'rgba(139, 195, 74, 0.85)',
				'rgba(205, 220, 57, 0.9)',
				'rgba(255, 235, 59, 0.95)',
				'rgba(255, 193, 7, 1)'
			],
			components: {
				preview: true,
				opacity: true,
				hue: true,
				interaction: {
					hex: false,
					rgba: false,
					hsva: false,
					input: true,
					save: true,
					cancel: false,
					clear: false
				}
			},
			i18n: {
				'btn:save': 'Close',
				'aria:btn:save': 'close'
			}
		});

		pickr.on(
			'change',
			$.proxy(function (color, pickr) {
				// Pickr's `toRGB()` method result in an undesired long-precision floating values,
				// so we will just use `toHEXA()` method instead.
				color = color.toHEXA().toString();
				color = maybeHexaToRgba(color);

				// Automatically save color on `change` event.
				pickr.setColor(color);
			}, this)
		);

		pickr.on(
			'save',
			$.proxy(function (color, pickr) {
				if (null !== color) {
					// Pickr's `toRGB()` method result in an undesired long-precision floating values,
					// so we will just use `toHEXA()` method instead.
					color = color.toHEXA().toString();
					color = maybeHexaToRgba(color);
				} else {
					color = '';
				}

				if ($input.val() !== color) {
					$input.val(color);
					if (!$input[0].__stopEvent) {
						$input.trigger('gc-customizer-update');
					}
				}
			}, this)
		);

		$input.data('pickr', pickr);

		$label.on('mousedown', function (e) {
			e.preventDefault();
			e.stopPropagation();

			// We check the CSS visibility value since it is used to toggle the popup.
			var $app = $(pickr.getRoot().app),
				visible = +$app.css('opacity');

			visible ? pickr.hide() : pickr.show();
		});

		// Re-use the Save button as a Close button.
		var $close = $(pickr.getRoot().interaction.save);
		$close.on('click', function () {
			pickr.hide();
		});
	}

	/**
	 * Initialize range slider input.
	 *
	 * @see {@link http://ionden.com/a/plugins/ion.rangeSlider/}
	 *
	 * @param {Element} input
	 */
	function initInputRange(input) {
		var $input = $(input),
			$buttons = $input.nextAll('.js-option-controls').find('button');

		$input.ionRangeSlider({ skin: 'round' });
		$input.on('input', function () {
			if (!this.__stopEvent) {
				$(this).trigger('gc-customizer-update');
			}
		});

		// Handles plus/minus buttons.
		$buttons.on('click', function () {
			var direction = +$(this).data('dir'),
				inst = $input.data('ionRangeSlider'),
				from = inst.options.from,
				min = inst.options.min,
				max = inst.options.max,
				step = inst.options.step,
				value = Math.max(min, Math.min(max, from + direction * step));

			inst.update({ from: value });
		});
	}

	/**
	 * Initialize media selector input.
	 *
	 * @see {@link https://codex.wordpress.org/Javascript_Reference/wp.media}
	 *
	 * @param {Element} input
	 */
	function initInputMedia(input) {
		var $input = $(input),
			$image = $input.next('img'),
			$upload = $input.nextAll('button[data-action=upload]'),
			$delete = $input.nextAll('button[data-action=delete]'),
			$change = $input.nextAll('button[data-action=change]'),
			title = $input.attr('title'),
			frame;

		$input.on('input', function () {
			if (!this.__stopEvent) {
				$(this).trigger('gc-customizer-update');
			}
		});

		$upload.add($change).on('click', function (e) {
			e.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: title,
				button: {
					text: 'Use this media'
				},
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();

				$input.val(attachment.id).trigger('input');
				$image.attr('src', attachment.url).show();
				$upload.hide();
				$delete.show();
				$change.show();
			});

			frame.open();
		});

		$delete.on('click', function (e) {
			e.preventDefault();

			$input.val('').trigger('input');
			$image.removeAttr('src').hide();
			$upload.show();
			$delete.hide();
			$change.hide();
		});
	}

	/**
	 * Get input value.
	 *
	 * @param {Element} input
	 * @returns {string}
	 */
	function getInputValue(input) {
		var $input = $(input),
			data = $input.data(),
			value;

		if ('checkbox' === input.type) {
			if (data.var) {
				value = input.checked ? data.varOn : data.varOff;
			} else {
				value = input.checked ? data.settingOn : data.settingOff;
			}
		} else {
			value = input.value;
			if (value && data.unit) {
				value = value + data.unit;
			}
		}

		return value;
	}

	/**
	 * Set input value.
	 *
	 * @param {Element} input
	 * @param {string} value
	 */
	function setInputValue(input, value) {
		var $input = $(input),
			data = $input.data();

		if (data.unit) {
			value = value.replace(data.unit, '');
		}

		input.__stopEvent = true;

		if ('color' === data.optionType) {
			$input.data('pickr').setColor(value);
		} else if ('range' === data.optionType) {
			// Handle range input with custom values.
			// https://github.com/IonDen/ion.rangeSlider/issues/107#issuecomment-165453776
			if (data.values) {
				var values = data.values.split(', '),
					valueIndex = values.indexOf(value);

				if (valueIndex > -1) {
					value = valueIndex;
				}
			}

			$input.data('ionRangeSlider').update({ from: value });
		} else if ('checkbox' === input.type) {
			input.checked = value == (!!data.var ? data.varOn : data.settingOn);
		} else {
			input.value = value;
		}

		delete input.__stopEvent;
	}

	/**
	 * Resolve and normalize dynamic CSS into static CSS values.
	 *
	 * @param {Object} css
	 * @returns {Object}
	 */
	function resolveDynamicStyle(css) {
		var doc = document.body,
			div = document.createElement('div'),
			divStyle = div.style,
			normalized = {},
			key;

		divStyle.height = divStyle.width = divStyle.left = divStyle.top = '1px';
		divStyle.position = 'absolute';
		for (key in css) {
			// Only resolve CSS variables, indicated by the "--" prefix.
			if (0 === key.indexOf('--')) {
				divStyle.setProperty(key, css[key]);
			}
		}

		doc.appendChild(div);
		for (key in css) {
			// Only resolve CSS variables, indicated by the "--" prefix.
			if (0 === key.indexOf('--')) {
				normalized[key] = getComputedStyle(div).getPropertyValue(key);
			} else {
				normalized[key] = css[key];
			}
		}
		doc.removeChild(div);

		return normalized;
	}

	/**
	 * Convert HEXA color with aplha value to RGBA representation.
	 *
	 * @param {string} hexa
	 * @param {string}
	 */
	function maybeHexaToRgba(hexa) {
		return hexa.replace(/#(.{2})(.{2})(.{2})(.{2})/, function (hexa, r, g, b, a) {
			r = parseInt(r, 16);
			g = parseInt(g, 16);
			b = parseInt(b, 16);
			a = parseInt(a, 16) / 255;

			// Round opacity value.
			a = Math.floor(a * 10000) / 10000;

			return 'rgba(' + r + ',' + g + ',' + b + ',' + a + ')';
		});
	}

	/**
	 * Local storage management with an in-memory storage if feature is not available.
	 */
	const ls = window.localStorage;
	const inMemoryStorage = {};
	const localStorage = {
		/**
		 * Adds or updates a localStorage data.
		 *
		 * @param {string} key
		 * @param {string} value
		 */
		set(key, value = null) {
			try {
				// Save to the in-memory storage cache for faster access next time.
				inMemoryStorage[key] = value;

				ls.setItem(`gecko_${key}`, value);
			} catch (e) {}
		},

		/**
		 * Gets a localStorage data.
		 *
		 * @param {string} key
		 * @returns {string|null}
		 */
		get(key) {
			let value = null;

			try {
				if (inMemoryStorage[key]) {
					value = inMemoryStorage[key];
				} else {
					value = ls.getItem(`gecko_${key}`);
					inMemoryStorage[key] = value;
				}
			} catch (e) {}

			return value;
		},

		/**
		 * Removes a localStorage data.
		 *
		 * @param {string} key
		 */
		remove(key) {
			try {
				delete inMemoryStorage[key];

				ls.removeItem(`gecko_${key}`);
			} catch (e) {}
		}
	};

	// Initialize customizer.
	new Customizer(document.querySelector('.gc-js-customizer'));
});

// Remove the admin menu sidebar as soon as possible.
setTimeout(function () {
	var adminmenu = document.getElementById('adminmenumain');
	adminmenu.parentNode.removeChild(adminmenu);
}, 1);
