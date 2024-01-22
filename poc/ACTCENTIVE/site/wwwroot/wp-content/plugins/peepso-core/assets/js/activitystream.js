(function (root, $, factory) {
	var moduleName = 'PsActivityStream',
		moduleObject = factory(moduleName, $, peepso.ajax, peepso.observer, peepso.modules);

	// Run on document load.
	jQuery(function ($) {
		var $container = $('#ps-activitystream'),
			config,
			inst;

		if ($container.length) {
			config = (peepsodata && peepsodata.activity) || {};
			inst = new moduleObject($container[0], config);
		}
	});
})(window, jQuery, function (moduleName, $, ajax, observer, modules) {
	var LOAD_MORE_ENABLE = 1,
		LOAD_MORE_ENABLE_MOBILE = 2,
		LOAD_MORE_ENABLE_DESKTOP = 3;

	// Constants.
	var EVT_SCROLL = 'scroll.ps-activity-stream',
		IS_ADMIN = +peepsodata.is_admin,
		MY_ID = +peepsodata.currentuserid,
		USER_ID = +peepsodata.userid,
		POST_ID = $('#peepso_post_id').val() || undefined,
		CONTEXT = $('#peepso_context').val() || undefined;

	// Handle browser resize event.
	(function (wrapper, observer) {
		if (!wrapper) {
			return;
		}

		function toggle() {
			if (wrapper.clientWidth < 700) {
				wrapper.classList.add('ps-activity--narrow');
			} else {
				wrapper.classList.remove('ps-activity--narrow');
			}
		}

		toggle();
		observer.addAction('browser.resize', toggle);
	})(document.querySelector('.ps-activity'), observer);

	return peepso.createClass(moduleName, {
		/**
		 * Class constructor.
		 * @param {Element} container
		 */
		__constructor: function (container, config) {
			this.$el = $(container);
			this.$recent = $('#ps-activitystream-recent');
			this.$loading = $('#ps-activitystream-loading');
			this.$noPosts = $('#ps-no-posts');
			this.$noPostsMatch = $('#ps-no-posts-match');
			this.$noMorePosts = $('#ps-no-more-posts');
			this.$filters = $('.ps-js-activitystream-filter');
			this.$filtersToggle = $('.ps-js-activitystream-filters-toggle');
			this.$filtersWrapper = $('.ps-js-activitystream-filters-wrapper');

			this.loading = false;
			this.loadEnd = false;
			this.loadPage = 1;
			this.loadIds = [];
			this.loadLimit = +peepsodata.activity_limit_below_fold;

			// Set loadmore button availability.
			var lmEnable = +peepsodata.loadmore_enable;
			if (peepso.isMobile()) {
				lmEnable = lmEnable === LOAD_MORE_ENABLE || lmEnable === LOAD_MORE_ENABLE_MOBILE;
			} else {
				lmEnable = lmEnable === LOAD_MORE_ENABLE || lmEnable === LOAD_MORE_ENABLE_DESKTOP;
			}

			this.loadButtonEnabled = lmEnable;
			this.loadButtonRepeat = this.loadButtonEnabled ? +peepsodata.loadmore_repeat : 0;
			this.loadButtonTemplate = config.template_load_more;

			this.isPermalink = +config.is_permalink;
			this.hideFromGuest = +config.hide_from_guest;

			// Normalize load limit.
			this.loadLimit = this.loadLimit > 0 ? this.loadLimit : 3;

			// Flag to apply activity filter.
			$.proxy(function (hashValue) {
				var $hidden, $filter, $option, $toggle;

				if (!hashValue) {
					return;
				}

				hashValue = 'core_' + hashValue;
				$hidden = $('[id=peepso_stream_id]');
				$filter = this.$filters.filter('[data-id=peepso_stream_id]');
				$option = $filter.find('[data-option-value="' + hashValue + '"]');
				$toggle = $filter.find('.ps-js-dropdown-toggle');

				if ($hidden.val() === hashValue || !$option.length) {
					return;
				}

				// Update filter data.
				$hidden.val(hashValue);

				// Update button toggle.
				$toggle.find('span').text($option.find('span').text());
				$toggle
					.find('.ps-js-icon')
					.attr('class', $option.find('.ps-js-icon').attr('class'));
			}, this)(window.location.hash.slice(1));

			// Stream notice.
			this.$filterNotice = $('#ps-stream__filters-warning');
			if (this.$filterNotice.length) {
				this.$filterNotice.data('html', this.$filterNotice.html().trim());
			}

			// Stream notice label mapper.
			this.filterNoticeMap = {};
			this.$filters
				.filter('[data-id=peepso_stream_id]')
				.find('[data-option-value]')
				.each(
					$.proxy(function (index, opt) {
						var $opt = $(opt),
							key = $opt.data('option-value'),
							label = $opt.data('option-label-warning');

						this.filterNoticeMap[key] = label;
					}, this)
				);

			// Get activitystream hidden input data.
			this.streamData = {};
			$('[id^=peepso_stream_]').each(
				$.proxy(function (index, input) {
					var name = input.id.replace(/^peepso_/, ''),
						value = input.value;

					this.streamData[name] = value;

					// Toggle "hide my posts" filter.
					if (name === 'stream_id') {
						if (!IS_ADMIN && value === 'core_scheduled') {
							this.allowHideMyPosts(false);
						} else {
							this.allowHideMyPosts(true);
						}
					}
				}, this)
			);

			// Hide loading if login popup is visible.
			$(document).on(
				'peepso_login_shown',
				function () {
					this.$loading.hide();
				}.bind(this)
			);

			// Handle activitystream filtering.
			this.$filters.on('click', '.ps-js-dropdown-toggle', $.proxy(this.onFilterToggle, this));
			this.$filters.on('click', '[data-option-value]', $.proxy(this.onFilterSelect, this));
			this.$filters.on('click', '.ps-js-cancel', $.proxy(this.onFilterCancel, this));
			this.$filters.on('click', '.ps-js-apply', $.proxy(this.onFilterApply, this));
			this.$filters.on('click', '[type=text]', $.proxy(this.onFilterFocus, this));
			this.$filters.on('keyup', '[type=text]', $.proxy(this.onFilterKeyup, this));
			this.$filters.on('click', '.ps-js-search', $.proxy(this.onFilterSearch, this));
			this.$filtersToggle.on('click', $.proxy(this.onFiltersToggle, this));

			// Flag to load everything.
			this.loadEverything = window.location.hash === '#everything';
			if (this.loadEverything) {
				if (!confirm('Are you sure want to load every post?')) {
					this.loadEverything = false;
				}
			}

			// Adds `peepso_remote_no_cache` parameter to the activitystream's request
			// if present in the URL.
			peepso.observer.addFilter('show_more_posts', params => {
				let searchParams = window.location.search || '';
				if (searchParams.match('peepso_remote_no_cache')) {
					params.peepso_remote_no_cache = 1;
				}

				return params;
			});

			// Initiate infinite load.
			var $search = $('#ps-activitystream-search');
			this.search($search.eq(0).val());

			// Listen to `peepso_stream_reset` actions.
			peepso.observer.addAction(
				'peepso_stream_reset',
				$.proxy(function () {
					this.search($search.eq(0).val());
				}, this)
			);

			// Do not show `post_saved_notice` on saved stream.
			peepso.observer.addFilter(
				'post_saved_notice',
				$.proxy(function (notice) {
					if ('core_saved' === this.streamData['stream_id']) {
						notice = false;
					}
					return notice;
				}, this),
				10,
				1
			);

			// this._token = ( new Date ).getTime();
			// this.autoload( this._token );
		},

		/**
		 * Search activities based on keyword.
		 * @param {string} [keyword]
		 */
		search: function (keyword) {
			var $toggle, label;

			keyword = (keyword || '').trim();
			this.searchKeyword = keyword;
			this.searchMode = 'exact';
			this.reset();

			// Update button.
			$toggle = $('.ps-js-activitystream-filter')
				.filter('[data-id=peepso_search]')
				.find('.ps-js-dropdown-toggle')
				.find('span');

			if ($toggle.length) {
				if (!keyword) {
					label = $toggle.data('empty');
				} else {
					label = $toggle.data('keyword');
					label +=
						keyword + '<i class="ps-posts__filter-remove gcis gci-times-circle"></i>';
					$toggle.one(
						'click',
						'i',
						$.proxy(function (e) {
							$('#ps-activitystream-search').val('');
							this.search('');
						}, this)
					);
				}

				$toggle.html(label);
			}
		},

		/**
		 * Start infinite load.
		 * @param {number} token
		 */
		autoload: function (token) {
			if (token !== this._token) {
				return;
			}

			if (this.loading) {
				return;
			}

			// Check if guest should be able to see the activities.
			if (!MY_ID && this.hideFromGuest) {
				this.$loading.hide();
				return false;
			}

			if (this.shouldLoad()) {
				this.loading = true;
				this.load(token).done(
					$.proxy(function () {
						this.loading = false;
						if (!this.isPermalink && !this.loadEnd) {
							this.autoload(token);
						}
					}, this)
				);
			} else if (this.loadButtonEnabled && !this.$loadButton) {
				this.$loadButton = $(this.loadButtonTemplate).insertAfter(this.$el);
				this.$loadButton.one(
					'click',
					$.proxy(function (e) {
						$(e.currentTarget).remove();
						this.autoload(token);
					}, this)
				);
			} else if (!this._onScrollEnabled) {
				var scrollableContainer = peepso.observer.applyFilters(
					'activitystream_scrollable_container',
					window
				);

				this._onScrollEnabled = true;
				$(scrollableContainer).on(EVT_SCROLL, token, $.proxy(this.onScroll, this));
			}
		},

		/**
		 * Check whether stream should load next activities.
		 * @return {boolean}
		 */
		shouldLoad: function () {
			var $activities = this.$el.children('.ps-js-activity'),
				$last,
				limit,
				position,
				winHeight;

			// Do not try to load next activity if all activities is already loaded.
			if (this.loadEnd) {
				return false;
			}

			// Try to load next activities on empty stream.
			if (!$activities.length) {
				return true;
			}

			// Respect loadEverything flag.
			if (this.loadEverything) {
				return true;
			}

			// Handle fixed-number batch load of activities.
			if (this.loadButtonEnabled && this.loadButtonRepeat) {
				if ($activities.length % this.loadButtonRepeat === 0) {
					if (this.$loadButton) {
						delete this.$loadButton;
						return true;
					} else {
						return false;
					}
				} else {
					return true;
				}
			}

			// Get the first of the last N activities where N is decided by limit value.
			$last = $activities.slice(0 - this.loadLimit).eq(0);
			if (!$last.length) {
				return false;
			}

			// Calculate element from viewport, or from top of the document if trigger button
			// is enabled.
			if (this.loadButtonEnabled && !this.$loadButton) {
				position = $last.eq(0).offset();
			} else {
				position = $last[0].getBoundingClientRect();
			}

			// Load next activities if `$last` is still inside the viewport.
			winHeight = window.innerHeight || document.documentElement.clientHeight;
			if (position.top < winHeight) {
				return true;
			}

			return false;
		},

		/**
		 * Load next activities in the current stream.
		 * @param {number} token
		 * @return {jQuery.Deferred}
		 */
		load: function (token) {
			var that = this;

			var params = _.extend(
				{
					uid: MY_ID,
					user_id: USER_ID,
					post_id: POST_ID,
					context: CONTEXT,
					page: this.loadPage,

					// Search query.
					search: this.searchKeyword || undefined,
					search_mode: (this.searchKeyword && this.searchMode) || undefined,

					// Also get pinned posts on first page.
					pinned: this.loadPage === 1 ? 1 : undefined
				},
				this.streamData || {}
			);

			// Execute filter hooks.
			params = peepso.observer.applyFilters('show_more_posts', params);

			return $.Deferred(function (defer) {
				that.$loading.show();

				ajax.post('activity.show_posts_per_page', params).done(function (json) {
					// Discard if token not match.
					if (token !== that._token) {
						return;
					}

					that.$loading.hide();

					if (json.data.found_posts > 0) {
						var id = +json.data.act_id;
						if (!id) {
							that.render(json.data.posts);
						}
						// Do not print duplicate post.
						else if (that.loadIds.indexOf(id) === -1) {
							that.loadIds.push(id);
							that.render(json.data.posts);
						}
						that.loadPage++;
					} else {
						that.loadEnd = true;
					}

					if (that.loadEnd) {
						if (params.page > 1) {
							var pendingHtml = peepso.observer.applyFilters(
								'activitystream_pending_html',
								''
							);
							if (pendingHtml) {
								that.render(pendingHtml);
							}
							that.$noMorePosts.show();
						} else if (that.searchKeyword) {
							that.$noPostsMatch.show();
						} else {
							that.$noPosts.show();
						}
					}

					defer.resolve();
				});
			});
		},

		/**
		 * Reset activitystream.
		 */
		reset: function () {
			this.loading = false;
			this.loadEnd = false;
			this.loadPage = 1;
			this.loadIds = [];

			// Disable scroll event.
			var scrollableContainer = peepso.observer.applyFilters(
				'activitystream_scrollable_container',
				window
			);

			this._onScrollEnabled = false;
			$(scrollableContainer).off(EVT_SCROLL);

			// Remove load more button.
			if (this.$loadButton) {
				this.$loadButton.remove();
				this.$loadButton = undefined;
			}

			// Reset view.
			this.$el.empty().hide();
			this.$recent.empty();
			this.$noMorePosts.hide();
			this.$noPosts.hide();
			this.$noPostsMatch.hide();

			// Show stream notice if needed.
			var hasNotice = this.filterNoticeMap[this.streamData['stream_id']];
			if (!hasNotice) {
				this.$filterNotice.hide();
			} else {
				this.$filterNotice.html(this.$filterNotice.data('html').replace('%s', hasNotice));
				this.$filterNotice.show();
			}

			// Restart autoload.
			this._token = new Date().getTime();
			this.autoload(this._token);

			this.updateFilterIcons();
		},

		/**
		 * Render activities into the stream.
		 * @param {string} html
		 */
		render: function (html) {
			// Manually fix problem with WP Embed as described here:
			// https://core.trac.wordpress.org/ticket/34971
			html = html.replace(/\/embed\/(#\?secret=[a-zA-Z0-9]+)*"/g, '/?embed=true$1"');

			// #5206 Add an extra identifier for WP Embed loaded in the activity stream.
			html = html.replace(/\/\?embed=true([#"])/g, '/?embed=true&peepso=1$1');

			var $posts = $(html),
				query = this.searchKeyword,
				mode = this.searchMode,
				highlight;

			// Filter posts.
			$posts = peepso.observer.applyFilters('peepso_activity', $posts);

			// Filter contents.
			$posts
				.find('.ps-js-activity-content, .ps-js-activity-quote, .ps-comment-item')
				.each(function () {
					var $post = $(this),
						html = $post.html();

					html = peepso.observer.applyFilters('peepso_activity_content', html);
					$post.html(html);
				});

			// Highlight contents if keywords are set.
			if (query) {
				highlight = '<span class="ps-text--highlight">$1</span>';
				$posts.find('.ps-js-activity-content').each(function () {
					var $post = $(this),
						html = $post.html(),
						reQuery = [query];

					if (mode === 'any') {
						reQuery = _.filter(query.split(' '), function (str) {
							return str;
						});
					}

					// Escape string to be used in regex.
					// https://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
					reQuery = _.map(reQuery, function (str) {
						return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
					});

					reQuery = RegExp('(' + reQuery.join('|') + ')(?![^<>]+>)', 'ig');
					html = html.replace(reQuery, highlight);
					$post.html(html);
				});
			}

			// Show container if its not already visible.
			if (this.$el.is(':hidden')) {
				this.$el.show();
			}

			// Safely append elements into container as some of them might raise error when added
			// to the document tree, thus breaks the autoload process.
			try {
				$posts.appendTo(this.$el);
				$posts.each(function () {
					peepso.hooks.doAction('post_added', this);
				});
			} catch (e) {}

			$posts.hide().fadeIn(1000, function () {
				$(document).trigger('ps_activitystream_loaded');

				peepso.observer.doAction('post_loaded', this);

				// Fix Instagram embed issue.
				if (html.match(/\sdata-instgrm-permalink/)) {
					setTimeout(function () {
						try {
							window.instgrm.Embeds.process();
						} catch (e) {}
					}, 1000);
				}
			});
		},

		/**
		 * Enable/disable "hide my posts" dropdown filter.
		 *
		 * @param {boolean} allow
		 */
		allowHideMyPosts: function (allow) {
			var dropdownId = 'stream_filter_show_my_posts',
				$dropdown = this.$filters.filter(`[data-id=peepso_${dropdownId}]`),
				$toggle = $dropdown.find('.ps-js-dropdown-toggle'),
				$options,
				$selected,
				optionValue;

			if (allow) {
				$toggle.show();
				return;
			}

			$toggle.hide();

			$options = $dropdown.find('[data-option-value]');
			$options.find('[type=radio][value=show_mine]').prop('checked', true);
			$options.find('[type=radio][value=hide_mine]').prop('checked', false);

			optionValue = $options.find('[type=radio]:checked').val();

			// Update hidden input's value.
			$(`#peepso_${dropdownId}`).val(optionValue);

			// Update streamData.
			this.streamData[dropdownId] = optionValue;

			// Update button toggle.
			$selected = $options.filter('[data-option-value=show_mine]');
			$toggle.find('span').text($selected.find('span').text());
			$toggle
				.find('[class*="ps-icon-"]')
				.attr('class', $selected.find('[class*="ps-icon-"]').attr('class'));
		},

		/**
		 * Update filter icons based on the selected filters.
		 */
		updateFilterIcons: function () {
			var $toggle = this.$filtersToggle.children('span').empty();
			var $inputs = this.$filtersWrapper.children('input[type=hidden]');

			$inputs.each(function () {
				var $input = $(this);
				var $div = $input.next(`[data-id="${this.id}"]`);
				var $divToggle = $div.children('.ps-js-dropdown-toggle');
				var $selected = $div.find(`[data-option-value="${this.value}"]`);
				var $selectedIcon = $selected.find('.ps-js-icon');

				$divToggle.find('.ps-js-icon').replaceWith($selectedIcon.clone());
				$toggle.append($selectedIcon.clone());
			});
		},

		/**
		 * Handle document scroll event.
		 * @param {Event} e
		 */
		onScroll: _.throttle(function (e) {
			var token = e.data;
			this.autoload(token);
		}, 1),

		/**
		 * Handle toggle filter activitystream dropdowns.
		 * @param {Event} e
		 */
		onFilterToggle: _.debounce(function (e) {
			var $toggle = $(e.currentTarget),
				$dropdown = $toggle.closest('.ps-js-activitystream-filter'),
				$menu = $dropdown.find('.ps-js-dropdown-menu'),
				data = $dropdown.data('id').replace(/^peepso_/, ''),
				value = this.streamData[data],
				$radio;

			if ($dropdown.is(':visible')) {
				$radio = $dropdown.find('[type=radio]').filter('[value="' + value + '"]');
				if ($radio.length) {
					$radio[0].checked = true;
				}
			}

			this.$filtersWrapper
				.find('.ps-js-dropdown-toggle')
				.removeClass('ps-posts__filter-toggle--active');

			if ($menu.is(':visible')) {
				$toggle.addClass('ps-posts__filter-toggle--active');
			}
		}, 1),

		/**
		 * Handle select filter activitystream dropdowns.
		 * @param {Event} e
		 */
		onFilterSelect: function (e) {
			var $option = $(e.currentTarget),
				$radio = $option.find('[type=radio]');

			e.preventDefault();
			e.stopPropagation();

			_.defer(function () {
				$radio[0].checked = true;
			});
		},

		/**
		 * Handle cancel activitystream filter.
		 * @param {Event} e
		 */
		onFilterCancel: function (e) {
			var $button = $(e.currentTarget),
				$dropdown = $button.closest('.ps-js-activitystream-filter'),
				$toggle = $dropdown.find('.ps-js-dropdown-toggle');

			$toggle.removeClass('ps-posts__filter-toggle--active');
			$toggle.focus();
		},

		/**
		 * Handle apply activitystream filter.
		 * @param {Event} e
		 */
		onFilterApply: function (e) {
			var $button = $(e.currentTarget),
				$dropdown = $button.closest('.ps-js-activitystream-filter'),
				$toggle = $dropdown.find('.ps-js-dropdown-toggle'),
				$hidden = $('#' + $dropdown.data('id')),
				$option = $dropdown.find('[type=radio]:checked').closest('[data-option-value]'),
				value = $option.data('optionValue'),
				data = $dropdown.data('id').replace(/^peepso_/, '');

			// Update filter data.
			$hidden.val(value);
			this.streamData[data] = value;

			// Update button toggle.
			$toggle.find('span').text($option.find('span').text());
			$toggle.find('.ps-js-icon').attr('class', $option.find('.ps-js-icon').attr('class'));

			$toggle.removeClass('ps-posts__filter-toggle--active');
			$toggle.focus();

			// Toggle "hide my posts" filter.
			if (data === 'stream_id') {
				if (!IS_ADMIN && value === 'core_scheduled') {
					this.allowHideMyPosts(false);
				} else {
					this.allowHideMyPosts(true);
				}
			}

			// Reset activitystream.
			this.reset();
		},

		/**
		 * Handle focus on input inside filter activitystream dropdowns.
		 * @param {Event} e
		 */
		onFilterFocus: function (e) {
			e.stopPropagation();
		},

		/**
		 * Handle keyup on input inside filter activitystream dropdowns.
		 * @param {Event} e
		 */
		onFilterKeyup: function (e) {
			var $dropdown, $search;

			e.stopPropagation();
			if (e.which === 13) {
				$dropdown = $(e.currentTarget).closest('.ps-js-activitystream-filter');
				$search = $dropdown.find('.ps-js-search');
				$search.click();
			}
		},

		/**
		 * Handle searching.
		 */
		onFilterSearch: function (e) {
			var $button = $(e.currentTarget),
				$dropdown = $button.closest('.ps-js-activitystream-filter'),
				$toggle = $dropdown.find('.ps-js-dropdown-toggle'),
				$hidden = $('#' + $dropdown.data('id')),
				$option = $dropdown.find('[type=radio]:checked').closest('[data-option-value]'),
				$input = $dropdown.find('[type=text]'),
				value = $option.data('optionValue'),
				data = $dropdown.data('id').replace(/^peepso_/, ''),
				keyword = $input.val().trim(),
				label = $toggle.find('span').data(keyword ? 'keyword' : 'empty');

			// Update filter data.
			$hidden.val(value);
			this.searchKeyword = keyword;
			this.searchMode = value;

			// Update button toggle.
			if (!keyword) {
				label = $toggle.find('span').data('empty');
			} else {
				label = $toggle.find('span').data('keyword');
				label =
					label +
					keyword +
					'<i class="ps-posts__filter-remove gcis gci-times-circle"></i>';
			}

			// Handle remove filter.
			$toggle
				.find('span')
				.html(label)
				.off('click', 'i')
				.one(
					'click',
					'i',
					$.proxy(function (e) {
						var $toggle = $(e.target).closest('.ps-js-dropdown-toggle'),
							$dropdown = $toggle.closest('.ps-js-activitystream-filter'),
							$input = $dropdown.find('[type=text]'),
							$hidden = $('#' + $dropdown.data('id'));

						e.stopPropagation();
						$input.val('');
						$hidden.val('');
						$toggle.find('span').html($toggle.find('span').data('empty'));
						this.searchKeyword = '';
						this.searchMode = '';
						this.reset();
					}, this)
				);

			$toggle.focus();

			// Reset activitystream.
			this.reset();
		},

		/**
		 * Handle filters toggle.
		 */
		onFiltersToggle: function (e) {
			if (this.$filtersWrapper.is(':visible')) {
				this.$filtersWrapper.hide();
				this.$filtersToggle.removeClass('ps-posts__filters-toggle--active');
			} else {
				this.$filtersWrapper.show();
				this.$filtersToggle.addClass('ps-posts__filters-toggle--active');
			}
		}
	});
});
