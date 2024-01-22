(function ($, factory) {
	var PsPageMembers = factory($);
	var ps_page_members = new PsPageMembers('.ps-js-members');
})(jQuery, function ($) {
	var HIDE_BEFORE_SEARCH = +peepsodata.members_hide_before_search;

	function PsPageMembers() {
		PsPageMembers.super_.apply(this, arguments);
		$($.proxy(this.init_page, this));
	}

	// inherit from `PsPageAutoload`
	peepso.npm.inherits(PsPageMembers, PsPageAutoload);

	peepso.npm.objectAssign(PsPageMembers.prototype, {
		onDocumentLoaded: function () {
			this._search_$ct = $(this._css_prefix).eq(0);
			this._search_$trigger = $(this._css_prefix + '-triggerscroll');
			this._search_$loading = $(this._css_prefix + '-loading');
			this._search_$noquery = $(this._css_prefix + '-noquery');
			this._search_$nomore = $(peepsodata.activity.template_no_more)
				.hide()
				.insertBefore(this._search_$trigger);

			this._search_$filters = $(this._css_prefix + '-filters');
			this._search_$filters_toggle = $(this._css_prefix + '-filters-toggle');
		},

		init_page: function () {
			this._search_$query = $('.ps-js-members-query').on(
				'input',
				$.proxy(this._filter, this)
			);
			this._search_$gender = $('.ps-js-members-gender').on(
				'change',
				$.proxy(this._filter, this)
			);
			this._search_$sortby = $('.ps-js-members-sortby').on(
				'change',
				$.proxy(this._filter, this)
			);
			this._search_$avatar = $('.ps-js-members-avatar').on(
				'click',
				$.proxy(this._filter, this)
			);
			this._search_$following = $('.ps-js-members-following').on(
				'change',
				$.proxy(this._filter, this)
			);

			// Extended profile filters.
			this._search_$extended = $('.ps-js-filter-extended').find('input[type=radio], select');
			this._search_$extended.prop('oninput', '');
			this._search_$extended
				.filter('select')
				.addClass('ps-select')
				.on('change', $.proxy(this._filter, this));
			this._search_$extended.filter('input[type=radio]').on(
				'click',
				$.proxy(function (e) {
					var $input = $(e.target);
					if ($input.data('ps-checked')) {
						$input.removeData('ps-checked');
						$input[0].checked = false;
					} else {
						$input.data('ps-checked', 1);
					}
					this._filter();
				}, this)
			);

			// Custom dropdown filters.
			this._search_$dropdown = this._search_$filters.find('.ps-js-dropdown[data-name]');
			this._search_$dropdown
				.find('.ps-js-dropdown-menu [data-option-value]')
				.on('click', e => {
					e.preventDefault();

					let $option = $(e.currentTarget);
					let $dropdown = $option.closest('.ps-js-dropdown');
					let $toggle = $dropdown.children('.ps-js-dropdown-toggle');
					let $selected = $toggle.children('[data-value]');

					if ($selected.attr('data-value') !== $option.attr('data-option-value')) {
						$selected.attr('data-value', $option.attr('data-option-value'));
						$selected.html($option.html());
						this._filter();
					}
				});

			// Toggle search filter form.
			this._search_$filters_toggle.on('click', e => {
				e.preventDefault();
				this._search_$filters.stop().slideToggle('fast');
			});

			this._filter();
		},

		_search_url: 'membersearch.search',

		_search_params: {
			uid: peepsodata.currentuserid,
			user_id: peepsodata.userid,
			query: undefined,
			order_by: undefined,
			order: undefined,
			peepso_gender: undefined,
			peepso_avatar: undefined,
			peepso_following: undefined,
			limit: 2,
			page: 1
		},

		_search_render_html: function (data) {
			if (data.members && data.members.length) {
				return data.members.join('');
			}
			return '';
		},

		_search_get_items: function () {
			return this._search_$ct.children('.ps-js-member');
		},

		/**
		 * @returns boolean
		 */
		_search_should_load_more: function () {
			var limit = +peepsodata.activity_limit_below_fold,
				$items = this._search_get_items(),
				$lastItem,
				position;

			// Handle fixed-number batch load of items.
			if (this._search_loadmore_enable && this._search_loadmore_repeat) {
				if ($items.length >= this._search_loadmore_repeat * this._search_params.page) {
					return false;
				} else {
					return true;
				}
			}

			limit = limit > 0 ? limit : 3;
			if (this._search_params.limit) {
				limit = limit * this._search_params.limit;
			}

			$lastItem = $items.slice(0 - limit).eq(0);
			if ($lastItem.length) {
				if (this._search_loadmore_enable) {
					position = $lastItem.eq(0).offset();
				} else {
					position = $lastItem.get(0).getBoundingClientRect();
				}
				if (position.top < (window.innerHeight || document.documentElement.clientHeight)) {
					return true;
				}
			}

			return false;
		},

		/**
		 * @param {object} params
		 * @returns jQuery.Deferred
		 */
		_fetch: function (params) {
			return $.Deferred(
				$.proxy(function (defer) {
					let transport = peepso.disableAuth().disableError(),
						url = this._search_url;

					params = $.extend({}, params);

					// If the "load more button" setting is enabled, limit should respect it.
					if (this._search_loadmore_enable && this._search_loadmore_repeat) {
						params.limit = this._search_loadmore_repeat;
					}
					// Otherwise, limit value is multiplied by 2 which translate to 2 items (per 1 row) each call.
					else if (!_.isUndefined(params.limit)) {
						params.limit *= 2;
					}

					this._fetch_xhr && this._fetch_xhr.abort();
					this._fetch_xhr = transport.getJson(url, params, response => {
						if (response.success) {
							defer.resolveWith(this, [response.data]);
						} else {
							defer.rejectWith(this, [response.errors]);
						}
					});
				}, this)
			);
		},

		/**
		 * Filter search based on selected elements.
		 */
		_filter: function () {
			var query = this._search_$query.val().trim(),
				sortby = this._search_$sortby.val().split('|'),
				gender = this._search_$gender.val(),
				avatar = this._search_$avatar[0].checked ? 1 : 0,
				following = this._search_$following[0].value,
				extended = {},
				dropdown = {};

			// abort current request
			this._fetch_xhr && this._fetch_xhr.abort();

			if (HIDE_BEFORE_SEARCH) {
				if (!query) {
					clearTimeout(this._search_debounced_timer);
					this._search_toggle_autoscroll('off');
					this._search_toggle_loading('hide');
					this._search_$ct.empty();
					this._search_$nomore.hide();
					this._search_$noquery.show();
					return;
				}

				this._search_$noquery.hide();
			}

			this._search_params.query = query || undefined;
			this._search_params.order_by = sortby[0] || undefined;
			this._search_params.order = sortby[1] || undefined;
			this._search_params.peepso_gender = gender || undefined;
			this._search_params.peepso_avatar = avatar || undefined;
			this._search_params.peepso_following = following || undefined;
			this._search_params.page = 1;

			// Increase the limit for recently online sort to avoid duplicate.
			if ('peepso_last_activity' === this._search_params.order_by) {
				this._search_params.limit = 25;
			} else {
				this._search_params.limit = 2;
			}

			// Add extended profile filters.
			this._search_$extended.each(function () {
				var $input = $(this);
				if ($input[0].tagName === 'SELECT') {
					extended[this.name] = this.value;
				} else if ($input.attr('type') === 'radio') {
					if (typeof extended[this.name] === 'undefined') {
						extended[this.name] = undefined;
					}
					if ($input[0].checked) {
						extended[this.name] = this.value;
					}
				}
			});
			_.extend(this._search_params, extended);

			// Add custom dropdown filters.
			this._search_$dropdown.each(function () {
				let $dropdown = $(this);
				let $selected = $dropdown.find('.ps-js-dropdown-toggle [data-value]');
				let value = $selected.attr('data-value');

				dropdown[$dropdown.attr('data-name')] = value ? value : undefined;
			});
			_.extend(this._search_params, dropdown);

			this._search();
		}
	});

	return PsPageMembers;
});
