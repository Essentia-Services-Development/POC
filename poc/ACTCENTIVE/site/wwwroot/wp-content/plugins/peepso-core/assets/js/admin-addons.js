jQuery(function ($) {
	const _label = (window.peepsoadminaddonsdata && peepsoadminaddonsdata.label) || {};
	const ACTIVATE_THEME_TITLE = _label.activate_theme_warning_title;
	const ACTIVATE_THEME_MESSAGE = _label.activate_theme_warning_message;
	const ACTIVATE_THEME_BTN_CANCEL = _label.activate_theme_warning_btn_cancel;
	const ACTIVATE_THEME_BTN_CONFIRM = _label.activate_theme_warning_btn_confirm;
	const ACTIVATE_THEME_ACTIONS = [
		{
			label: ACTIVATE_THEME_BTN_CANCEL,
			class: 'pa-btn--cancel ps-js-cancel',
			icon: 'gcir gci-times-circle',
			iconHover: 'gcis gci-times-circle'
		},
		{
			label: ACTIVATE_THEME_BTN_CONFIRM,
			class: 'pa-btn--active ps-js-submit',
			icon: 'gcir gci-check-circle',
			iconHover: 'gcis gci-check-circle',
			primary: true
		}
	];

	const LICENSE_CHECK_ERROR_MESSAGE = _label.license_check_error_message;
	const LICENSE_CHECK_ERROR_DESCRIPTION = _label.license_check_error_description;

	function PageAddons() {
		this.init();
	}

	PageAddons.prototype = {
		init() {
			this.data = window.peepsoadminaddonsdata || {};
			this.installQueue = [];

			this.$tutorial = $('.pa-addons-tutorial');
			this.$container = $('.pa-page--addons');
			this.$disabler = this.$container.find('.ps-js-disabler');
			this.$actionDisabler = this.$container.find('.ps-js-action-disabler');
			this.$license = this.$container.find('.pa-addons__license');
			this.$licenseName = this.$license.find('.ps-js-license-name');
			this.$licenseKey = this.$license.find('#license');
			this.$error = this.$container.find('.ps-js-addons-message');
			this.$list = this.$container.find('.ps-js-list');
			this.$btnBulkShow = this.$container.find('.ps-js-bulk-show').hide();
			this.$btnBulkHide = this.$container.find('.ps-js-bulk-hide').hide();
			this.$btnBulkInstall = this.$container.find('.ps-js-bulk-install').hide();
			this.$btnBulkActivate = this.$container.find('.ps-js-bulk-activate').hide();
			this.$divCheckAll = this.$container.find('.ps-js-bulk-checkall-wrapper').hide();
			this.$chkAll = this.$container.find('.ps-js-bulk-checkall');
			this.$chkCategories = null;
			this.$chkAddons = null;
			this.$descriptions = null;
			this.$descToggles = null;

			this.$tutorial.on(
				'click',
				'.pa-addons-tutorial__close',
				this.onDismissTutorial.bind(this)
			);
			this.$license.on('click', 'button', this.getLicenseInfo.bind(this));
			this.$licenseKey.on('input', () => this.licenseChanged(true));
			this.$chkAll.on('click', this.onSelectAll.bind(this));
			this.$list.on(
				'click',
				'.ps-js-category [type=checkbox]',
				this.onSelectCategory.bind(this)
			);
			this.$list.on('click', '.ps-js-addon [type=checkbox]', this.onSelectAddon.bind(this));
			this.$list.on('click', '.ps-js-show-addon-desc', this.toggleDescription.bind(this));
			this.$list.on('mouseenter', '[data-mouseover-text]', this.onBtnMouseOver.bind(this));
			this.$list.on('mouseleave', '[data-mouseover-text]', this.onBtnMouseOut.bind(this));
			this.$list.on('click', '.ps-js-addon-install', this.install.bind(this));
			this.$list.on('click', '.ps-js-addon-inactive', this.activate.bind(this));
			this.$btnBulkShow.on('click', this.showActions.bind(this));
			this.$btnBulkHide.on('click', this.hideActions.bind(this));
			this.$btnBulkInstall.on('click', this.installSelected.bind(this));
			this.$btnBulkActivate.on('click', this.activateSelected.bind(this));

			this.getLicenseInfo(true);
		},

		/**
		 * Get the license information from the provided key.
		 *
		 * @param {*} firstCall
		 * @param {*} noDelay
		 */
		getLicenseInfo(firstCall = false, noDelay = false) {
			let $input_license = $('#license'),
				$input_license_changed = $('#license_changed'),
				$button = this.$license.find('button'),
				license = $input_license.val().trim(),
				license_changed = $input_license_changed.val().trim(),
				data = { license, license_changed };

			if ($button.data('ajax')) {
				return;
			}

			this.$error.empty();
			this.$actionDisabler.show();

			// Separate callback since the process is forced to have a minimum 5s window to avoid blinking.
			let callbackData;
			let callback = () => {
				let json = callbackData;

				if (json.success) {
					if (json.data.addons) {
						this.$licenseName.html(json.data.bundle_name);
						this.updateList(json.data.addons);
						this.$error.empty();
						this.$actionDisabler.hide();

						if (firstCall) {
							this.restoreState();
						}

						this.updateActions();
					}

					if (true !== firstCall && json.data.message) {
						this.$error.html(json.data.message);
					}
				} else {
					let html = [LICENSE_CHECK_ERROR_MESSAGE];
					if (json.xhr && 404 === json.xhr.status) {
						html.push(LICENSE_CHECK_ERROR_DESCRIPTION);
					}

					this.$error.html(html.join('<br>'));
				}
			};

			// Force a minimum 5s request time.
			$button.data('ajax', 1);
			this.setButtonProgress($button, true);

			let loaded = null;
			setTimeout(
				() => {
					if (null === loaded) {
						loaded = false;
					} else {
						$button.removeData('ajax');
						this.setButtonProgress($button, false);
						callback();
					}
				},
				noDelay ? 0 : 1500
			);

			peepso.ajax
				.post('adminAddons.check_license', data, -1)
				.done(json => {
					callbackData = json;

					if (null === loaded) {
						loaded = true;
					} else {
						$button.removeData('ajax');
						this.setButtonProgress($button, false);
						this.licenseChanged(false);
						callback();
					}
				})
				.fail((xhr, status) => {
					callbackData = { success: 0, xhr };
					$button.removeData('ajax');
					this.setButtonProgress($button, false);
					callback();
				});
		},

		/**
		 * Update/reset license_changed flag.
		 *
		 * @param {boolean} changed
		 */
		licenseChanged(changed = true) {
			$('#license_changed').val(!!changed ? 1 : 0);
		},

		/**
		 * Update addon listing.
		 *
		 * @param {string} html
		 */
		updateList(html) {
			let $checkboxes = this.$chkAddons || $();
			let checkedStates = {};

			// Save checked state before the content is being overwritten.
			$checkboxes.not(':disabled').each(function () {
				let $checkbox = $(this),
					id = $checkbox.data('id');

				if (id) {
					checkedStates[id] = $checkbox.is(':checked');
				}
			});

			this.$list.html(html);

			this.$chkCategories = this.$list.find('.ps-js-category [type=checkbox]');
			this.$chkAddons = this.$list.find('.ps-js-addon [type=checkbox]');
			this.$descriptions = this.$list.find('.ps-js-addon-desc');
			this.$descToggles = this.$list.find('.ps-js-show-addon-desc');

			// Restore checked state.
			$checkboxes = this.$chkAddons;
			$checkboxes.not(':disabled').each((i, checkbox) => {
				let $checkbox = $(checkbox),
					$row = $checkbox.closest('.ps-js-addon'),
					id = $checkbox.data('id'),
					checked = checkedStates[id];

				if ('undefined' !== typeof checked) {
					this.setRowSelected($row, checked);
				}
			});
		},

		/**
		 * Update bulk action state after content update.
		 */
		updateActions() {
			// Update disabled and checked state on the category checkboxes.
			this.$chkCategories.each((i, checkbox) => {
				let $checkbox = $(checkbox),
					$category = $checkbox.closest('.ps-js-category'),
					$addons = $category.nextUntil('.ps-js-category'),
					$chkAddons = $addons.find('[type=checkbox]'),
					$chkAddonsDisabled = $chkAddons.filter(':disabled'),
					$row = $checkbox.closest('.ps-js-category');

				if ($chkAddons.length === $chkAddonsDisabled.length) {
					$checkbox.prop('disabled', true);
					if ($chkAddonsDisabled.eq(0).is(':checked')) {
						this.setRowSelected($row, true);
					}
				} else {
					$chkAddons = $chkAddons.not($chkAddonsDisabled).not(':checked');
					if (!$chkAddons.length) {
						this.setRowSelected($row, true);
					}
				}
			});

			// Update disabled and checked state on the checkall checkboxes.
			let $chkCategories = this.$chkCategories,
				$chkCategoriesDisabled = $chkCategories.filter(':disabled'),
				$chkCategoriesUnchecked = $chkCategories
					.not($chkCategoriesDisabled)
					.not(':checked');

			this.$chkAll.each((i, checkbox) => {
				let $checkbox = $(checkbox),
					$row = $checkbox.closest('.ps-js-bulk-checkall-wrapper');

				if ($chkCategories.length === $chkCategoriesDisabled.length) {
					$checkbox.prop('disabled', true);
					this.setRowSelected($row, false);
				} else {
					$checkbox.prop('disabled', false);
					this.setRowSelected($row, !$chkCategoriesUnchecked.length);
				}
			});

			// Update bulk action visibilities.
			this.$btnBulkShow.hide();
			if (this.$chkAddons.length) {
				this.$btnBulkHide.is(':hidden') ? this.hideActions() : this.showActions();
			}
		},

		/**
		 * Update bulk install and bulk activete button state after content update.
		 */
		updateBulkInstallAndActivate() {
			let $chkAddonsChecked = this.$chkAddons.not(':disabled').filter(':checked'),
				$chkAddonsInstalled = $chkAddonsChecked.filter('[data-is-installed=1]'),
				$chkAddonsActive = $chkAddonsInstalled.filter('[data-is-active=1]');

			if ($chkAddonsChecked.length - $chkAddonsInstalled.length) {
				this.$btnBulkInstall.removeAttr('disabled');
			} else {
				this.$btnBulkInstall.attr('disabled', 'disabled');
			}

			if ($chkAddonsInstalled.length - $chkAddonsActive.length) {
				this.$btnBulkActivate.removeAttr('disabled');
			} else {
				this.$btnBulkActivate.attr('disabled', 'disabled');
			}
		},

		showActions() {
			this.$btnBulkShow.hide();
			this.$btnBulkHide.show();
			this.$btnBulkInstall.show();
			this.$btnBulkActivate.show();
			this.$divCheckAll.show();
			this.$chkCategories.show();
			this.$chkAddons.show();

			// Forcefully hide the description.
			this.$descriptions.add(this.$descToggles).attr('style', 'display: none !important');

			this.$list.addClass('pa-addons__list--bulk');

			this.updateBulkInstallAndActivate();
		},

		hideActions() {
			this.$btnBulkShow.show();
			this.$btnBulkHide.hide();
			this.$btnBulkInstall.hide();
			this.$btnBulkActivate.hide();
			this.$divCheckAll.hide();
			this.$chkCategories.hide();
			this.$chkAddons.hide();

			this.$descriptions.add(this.$descToggles).removeAttr('style');

			this.$list.removeClass('pa-addons__list--bulk');
		},

		/**
		 * Handle select all checkboxes.
		 *
		 * @param {Event} e
		 */
		onSelectAll(e) {
			let checked = e.currentTarget.checked,
				$rows = $();

			$rows = $rows.add(this.$chkAll.closest('.ps-js-bulk-checkall-wrapper'));
			$rows = $rows.add(this.$chkCategories.closest('.ps-js-category'));
			$rows = $rows.add(this.$chkAddons.closest('.ps-js-addon'));
			this.setRowSelected($rows, checked);

			this.updateBulkInstallAndActivate();
		},

		/**
		 * Handle select category checkboxes.
		 *
		 * @param {Event} e
		 */
		onSelectCategory(e) {
			let checked = e.currentTarget.checked,
				$category = $(e.currentTarget).closest('.ps-js-category'),
				$addons = $category.nextUntil('.ps-js-category'),
				$rows = $category.add($addons);

			this.setRowSelected($rows, checked);

			// Decide checkall checkbox states.
			let values = $.makeArray(this.$chkCategories.not(':disabled')).map(chk => chk.checked);
			checked = -1 === values.indexOf(false);
			$rows = this.$chkAll.closest('.ps-js-bulk-checkall-wrapper');
			this.setRowSelected($rows, checked);

			this.updateBulkInstallAndActivate();
		},

		/**
		 * Handle select addon checkboxes.
		 *
		 * @param {Event} e
		 */
		onSelectAddon(e) {
			let checked = e.currentTarget.checked,
				$checkbox = $(e.currentTarget),
				$addon = $checkbox.closest('.ps-js-addon');

			this.setRowSelected($addon, checked);

			// Decide category checkbox state.
			let $category = $addon.prevAll('.ps-js-category').eq(0);
			let $addons = $category.nextUntil('.ps-js-category');
			let $checkboxes = $addons.find('[type=checkbox]').not(':disabled');
			let values = $.makeArray($checkboxes).map(chk => chk.checked);
			checked = -1 === values.indexOf(false);
			this.setRowSelected($category, checked);

			// Decide checkall checkbox states.
			values = $.makeArray(this.$chkCategories.not(':disabled')).map(chk => chk.checked);
			checked = -1 === values.indexOf(false);
			$rows = this.$chkAll.closest('.ps-js-bulk-checkall-wrapper');
			this.setRowSelected($rows, checked);

			this.updateBulkInstallAndActivate();
		},

		/**
		 * Add a plugin to the installation queue.
		 *
		 * @param {number} id
		 * @returns {JQueryDeferred}
		 */
		installQueueAdd(id) {
			let deferred = $.Deferred();

			this.installQueue.push({ id, deferred });
			this.installQueueExec();

			return deferred;
		},

		/**
		 * Execute plugin installation queue.
		 */
		installQueueExec() {
			if (!this.installQueue.length) {
				this.__installQueueRunning = false;
				return;
			}

			if (this.__installQueueRunning) {
				return;
			}

			this.__installQueueRunning = true;

			// Disable navigation.
			this.disableNavigate();
			this.setButtonProgress(this.$btnBulkInstall, true);
			this.$disabler.show();

			let item = this.installQueue.shift();

			peepso.ajax
				.post('adminAddons.install', { item_id: item.id }, -1)
				.done(json => item.deferred.resolve(json))
				.fail(() => item.deferred.reject())
				.always(() => {
					setTimeout(() => {
						this.__installQueueRunning = false;

						// Enable navigation and  when all queue items are already executed.
						if (!this.installQueue.length) {
							this.enableNavigate();
							this.setButtonProgress(this.$btnBulkInstall, false);
							this.$disabler.hide();

							this.getLicenseInfo(false, true);

							this.updateBulkInstallAndActivate();
						}

						this.installQueueExec();
					}, 1000);
				});
		},

		/**
		 * Toggle addon description.
		 *
		 * @param {Event} e
		 */
		install(e) {
			e.preventDefault();

			let $button = $(e.currentTarget);
			if ($button.data('installing')) {
				return;
			}

			let id = $button.data('id');
			if (!id) {
				return;
			}

			// Set addon button state.
			$button.data('installing', 1);
			$button.addClass('pa-btn--disabled');
			$button.triggerHandler('mouseleave');
			this.setButtonProgress($button, true);

			let deferred = this.installQueueAdd(id);

			deferred.done(json => {
				if (json.success) {
					if ('object' === typeof json.data && true == json.data.result) {
						// Update button.
						this.setButtonProgress($button, false, this.data.label.installed);

						$button
							.removeClass('pa-btn--addon-install ps-js-addon-install')
							.addClass('pa-btn--addon-inactive ps-js-addon-inactive')
							.attr('data-mouseover-text', this.data.label.activate);

						// Update checkbox.
						$button
							.closest('.ps-js-addon')
							.find('[type=checkbox]')
							.attr('data-is-installed', '1');
					} else {
						alert(this.data.label.install_failed + ' ' + $button.attr('title'));
					}
				}
			});

			// Reset addon button state.
			deferred.always(() => {
				$button.removeData('installing');
				$button.removeClass('pa-btn--disabled');
				this.setButtonProgress($button, false);
			});
		},

		/**
		 * Bulk plugin installation.
		 */
		installSelected() {
			this.$chkAddons.filter(':checked').each((i, chk) => {
				let $addon = $(chk).closest('.ps-js-addon'),
					$button = $addon.find('.ps-js-addon-install'),
					id = $button.data('id');

				if (id) {
					// Set addon button state.
					$button.data('installing', 1);
					$button.addClass('pa-btn--disabled');
					$button.triggerHandler('mouseleave');
					this.setButtonProgress($button, true);

					let deferred = this.installQueueAdd(id);

					deferred.done(json => {
						if (json.success) {
							if ('object' === typeof json.data && true == json.data.result) {
								// Update button.
								this.setButtonProgress($button, false, this.data.label.installed);

								$button
									.removeClass('pa-btn--addon-install ps-js-addon-install')
									.addClass('pa-btn--addon-inactive ps-js-addon-inactive')
									.attr('data-mouseover-text', this.data.label.activate);

								// Update checkbox.
								$button
									.closest('.ps-js-addon')
									.find('[type=checkbox]')
									.attr('data-is-installed', '1');
							} else {
								alert(this.data.label.install_failed + ' ' + $button.attr('title'));
							}
						}
					});

					// Reset addon button state.
					deferred.always(() => {
						$button.removeData('installing');
						$button.removeClass('pa-btn--disabled');
						this.setButtonProgress($button, false);
					});
				}
			});
		},

		/**
		 * Toggle addon description.
		 *
		 * @param {Event} e
		 */
		activate(e) {
			e.preventDefault();

			let $button = $(e.currentTarget);
			if ($button.data('installing')) {
				return;
			}

			let keyword = $button.data('activation-keyword');
			if (!this.activateThemeDialog && 'activate_themes' === keyword) {
				let title = ACTIVATE_THEME_TITLE;
				let html = ACTIVATE_THEME_MESSAGE;
				let actions = ACTIVATE_THEME_ACTIONS;

				let opts = { title, actions, closeButton: false, closeOnEsc: false };
				peepso.dialog(html, opts).confirm(ok => {
					if (ok) {
						this.activateThemeDialog = true;
						$button.trigger('click');
					} else {
						// Uncheck selected theme.
						let $addon = $button.closest('.ps-js-addon');
						let $checkbox = $addon.find('[type=checkbox]');
						if ($checkbox.is(':checked')) {
							$checkbox.trigger('click');
						}
					}
				});
				return;
			}

			// Reset activate theme dialog flag.
			delete this.activateThemeDialog;

			// Make sure user does not do any action that could interrupt loading.
			this.setButtonProgress($button, true);
			this.setButtonProgress(this.$btnBulkActivate, true);
			this.$disabler.show();

			// Save current state before page load.
			this.saveState();

			$(`<form action="admin.php?page=peepso-installer" method="POST">
				<input type="hidden" name="${$button.data('activation-keyword')}[]"
					value="${$button.data('activation-key')}" />
			</form>`)
				.appendTo(document.body)[0]
				.submit();
		},

		activateSelected() {
			let $buttons = $(),
				query = [];

			// Build query parameters.
			this.$chkAddons.filter(':checked').each((i, chk) => {
				let $addon = $(chk).closest('.ps-js-addon'),
					$button = $addon.find('.ps-js-addon-inactive');

				if ($button.length) {
					let keyword = $button.data('activation-keyword'),
						key = $button.data('activation-key');

					if (keyword && key) {
						$buttons = $buttons.add($button);
						query.push([keyword, key]);
					}
				}
			});

			if (!this.activateThemeDialog && query.find(item => 'activate_themes' === item[0])) {
				let title = ACTIVATE_THEME_TITLE;
				let html = ACTIVATE_THEME_MESSAGE;
				let actions = ACTIVATE_THEME_ACTIONS;

				let opts = { title, actions, closeButton: false, closeOnEsc: false };
				peepso.dialog(html, opts).confirm(ok => {
					if (!ok) {
						// Uncheck selected themes.
						this.$chkAddons.filter(':checked').each(function (i, chk) {
							let $checkbox = $(chk),
								$addon = $checkbox.closest('.ps-js-addon'),
								$button = $addon.find('.ps-js-addon-inactive');

							if ($button.length) {
								let keyword = $button.data('activation-keyword');
								if ('activate_themes' === keyword) {
									$checkbox.trigger('click');
								}
							}
						});
					}

					this.activateThemeDialog = true;
					this.activateSelected();
				});
				return;
			}

			// Reset activate theme dialog flag.
			delete this.activateThemeDialog;

			if (!query.length) {
				return;
			}

			query = query
				.map(function (param) {
					return `<input type="hidden" name="${param[0]}[]"
					value="${param[1]}" />`;
				})
				.join('');

			// Make sure user does not do any action that could interrupt loading.
			this.setButtonProgress($buttons, true);
			this.setButtonProgress(this.$btnBulkActivate, true);
			this.$disabler.show();

			// Save current state before page load.
			this.saveState();

			$(`<form action="admin.php?page=peepso-installer" method="POST">${query}</form>`)
				.appendTo(document.body)[0]
				.submit();
		},

		disableNavigate() {
			let $menubar = $('#wpadminbar');
			let $sidebar = $('#adminmenuwrap');
			let disablerHtml =
				'<div class="ps-js-disabler" style="position:absolute; top:0; left:0; right:0; bottom:0; z-index:100000"></div>';

			$menubar.children('.ps-js-disabler').length || $menubar.append(disablerHtml);
			$sidebar.children('.ps-js-disabler').length || $sidebar.append(disablerHtml);

			// Save original onbeforeunload handler, and attach a new onbeforeunload handler.
			if ('undefined' === typeof this.__originalBeforeOnload) {
				this.__originalBeforeOnload = window.onbeforeunload || function () {};
				window.onbeforeunload = function (e) {
					e.preventDefault();
					e.returnValue = '';
				};
			}
		},

		enableNavigate() {
			$('#wpadminbar').children('.ps-js-disabler').remove();
			$('#adminmenuwrap').children('.ps-js-disabler').remove();

			// Detach new onbeforeunload handler, and restore original onbeforeunload handler.
			if ('undefined' !== typeof this.__originalBeforeOnload) {
				window.onbeforeunload = this.__originalBeforeOnload;
				this.__originalBeforeOnload = undefined;
			}
		},

		/**
		 * Toggle addon description.
		 *
		 * @param {Event} e
		 */
		toggleDescription(e) {
			let $button = $(e.currentTarget),
				$label = $button.find('[data-label-show]'),
				$desc = $button.siblings('.pa-addons__addon-desc-text'),
				hidden = $desc.hasClass('slide-up');

			e.preventDefault();
			e.stopPropagation();

			if (hidden) {
				$desc.removeClass('slide-up').addClass('slide-down');
				$label.html($label.data('label-hide'));
			} else if (!hidden) {
				$desc.removeClass('slide-down').addClass('slide-up');
				$label.html($label.data('label-show'));
			}
		},

		/**
		 * Handle mouseover transition on a button.
		 *
		 * @param {Event} e
		 */
		onBtnMouseOver(e) {
			let $button = $(e.currentTarget),
				$text = $button.children('span'),
				$icon = $button.children('i'),
				text = $button.data('mouseover-text'),
				icon = $button.data('mouseover-icon');

			if (text) {
				$text.data('text', $text.text());
				$text.text(text);
			}

			if (icon) {
				$icon.data('icon', $icon.attr('class'));
				$icon.attr('class', icon);
			}
		},

		/**
		 * Handle mouseout transition on a button.
		 *
		 * @param {Event} e
		 */
		onBtnMouseOut(e) {
			let $button = $(e.currentTarget),
				$text = $button.children('span'),
				$icon = $button.children('i'),
				text = $text.data('text'),
				icon = $icon.data('icon');

			if (text) {
				$text.text(text);
				$text.removeData('text');
			}

			if (icon) {
				$icon.attr('class', icon);
				$icon.removeData('icon');
			}
		},

		/**
		 * Set or unset button(s) as in progress.
		 *
		 * @param {JQuery|Element|Element[]} buttons
		 * @param {boolean} progress
		 * @param {string} text
		 */
		setButtonProgress(buttons, progress = true, text) {
			$(buttons).each(function () {
				let $button = $(this),
					$icon = $button.children('i'),
					$text = $button.children('span'),
					already = $button.data('progress');

				if (progress && !already) {
					$button.data('progress', 1);
					$button.attr('class', 'pa-btn pa-btn--loading pa-addons_license-button');

					$icon.data('icon', $icon.attr('class'));
					$icon.attr('class', 'gcis gci-sync-alt pa-addons__spinner');

					text = text || $button.data('running-text');
					if (text) {
						$text.data('text', $text.text());
						$text.text(text);
					}

					// Temporarily disabled mouseover effect.
					if ($button.attr('data-mouseover-text')) {
						$button.attr('data-mouseover-off', $button.attr('data-mouseover-text'));
						$button.removeAttr('data-mouseover-text');
					}
				} else if (!progress) {
					$button.removeData('progress');
					$button.attr('class', 'pa-btn pa-addons_license-button');

					let icon = $icon.data('icon');
					if (icon) {
						$icon.attr('class', icon);
					}

					// Override default text if necessary.
					if (text) {
						$text.data('text', text);
					}

					text = $text.data('text');
					if (text) {
						$text.text(text);
					}

					// Put back mouseover effect.
					if ($button.attr('data-mouseover-off')) {
						$button.attr('data-mouseover-text', $button.attr('data-mouseover-off'));
						$button.removeAttr('data-mouseover-off');
					}
				}
			});
		},

		/**
		 * Set or unset addon row(s) as selected.
		 *
		 * @param {JQuery|Element|Element[]} rows
		 * @param {boolean} selected
		 */
		setRowSelected(rows, selected = true) {
			$(rows).each(function () {
				let $row = $(this),
					$checkbox = $row.find('[type=checkbox]'),
					selectedClass = 'pa-addons__addon--selected';

				if (!selected) {
					$checkbox.prop('checked', false);
					$row.removeClass(selectedClass);
				} else {
					$checkbox.prop('checked', true);
					if (!$checkbox.is(':disabled')) {
						$row.addClass(selectedClass);
					}
				}
			});
		},

		/**
		 * Save checked addons and bulk action state.
		 */
		saveState() {
			let $checkboxes = this.$chkAddons || $(),
				checkedStates = {},
				showActions = this.$btnBulkHide.is(':visible');

			$checkboxes.not(':disabled').each(function () {
				let $checkbox = $(this),
					id = $checkbox.data('id');

				if (id) {
					checkedStates[id] = $checkbox.is(':checked');
				}
			});

			peepso.ls.set('addons_checked', JSON.stringify(checkedStates));
			peepso.ls.set('addons_show_actions', showActions ? 1 : 0);
		},

		/**
		 * Restore checked addons and bulk action state.
		 */
		restoreState() {
			let checkedStates = peepso.ls.get('addons_checked'),
				showActions = +peepso.ls.get('addons_show_actions');

			try {
				checkedStates = JSON.parse(checkedStates);
				if ('object' === typeof checkedStates) {
					let $checkboxes = this.$chkAddons || $();
					$checkboxes.not(':disabled').each((i, checkbox) => {
						let $checkbox = $(checkbox),
							$row = $checkbox.closest('.ps-js-addon'),
							id = $checkbox.data('id'),
							checked = checkedStates[id];

						if ('undefined' !== typeof checked) {
							this.setRowSelected($row, checked);
						}
					});
				}
			} catch (e) {}

			if (showActions) {
				this.showActions();
			}

			peepso.ls.remove('addons_checked');
			peepso.ls.remove('addons_show_actions');
		},

		/**
		 * Hide installer tutorial.
		 *
		 * @param {Event} e
		 */
		onDismissTutorial(e) {
			e.preventDefault();

			this.$tutorial.slideUp();
			peepso.ajax.post('adminAddons.hide_tutorial', null, -1);
		}
	};

	new PageAddons();
});
