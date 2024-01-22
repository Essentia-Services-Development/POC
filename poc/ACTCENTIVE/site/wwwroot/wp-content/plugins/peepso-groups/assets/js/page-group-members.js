(function ($, factory) {
	var PsPageGroupMembers = factory($);
	var ps_page_group_members = new PsPageGroupMembers('.ps-js-group-members');
})(jQuery, function ($) {
	function PsPageGroupMembers() {
		PsPageGroupMembers.super_.apply(this, arguments);
		$($.proxy(this.init_page, this));
	}

	// inherit from `PsPageAutoload`
	peepso.npm.inherits(PsPageGroupMembers, PsPageAutoload);

	peepso.npm.objectAssign(PsPageGroupMembers.prototype, {
		onDocumentLoaded: function () {
			this._search_$ct = $(this._css_prefix).eq(0);
			this._search_$trigger = $(this._css_prefix + '-triggerscroll');
			this._search_$loading = $(this._css_prefix + '-loading');
			this._search_$nomore = $(peepsodata.activity.template_no_more)
				.hide()
				.insertBefore(this._search_$trigger);
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

			var url = decodeURIComponent(window.location.href),
				matches = url.match(/members\/(pending|invited|management|banned)/),
				roleMapper;

			roleMapper = {
				invited: 'pending_user',
				pending: 'pending_admin',
				management: 'management',
				banned: 'banned'
			};

			// check role
			this._search_params.role = matches ? roleMapper[matches[1]] : '';

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

			// toggle search filter form
			$('.ps-form-search-opt').on('click', $.proxy(this._toggle, this));

			this._filter();

			peepso.observer.addAction(
				'pending_admin_member_count',
				function (group_id, member_count) {
					var $label;

					if (group_id === +peepsogroupsdata.group_id) {
						$label = $('.ps-js-pending-count[data-id=' + group_id + ']');
						$label.html(member_count);
						if (!member_count) {
							$label.closest('.ps-js-pending-label').remove();
						}
					}
				},
				10,
				2
			);

			peepso.observer.addAction(
				'pending_user_member_count',
				function (group_id, member_count) {
					if (group_id === +peepsogroupsdata.group_id) {
						$label = $('.ps-js-invited-count[data-id=' + group_id + ']');
						$label.html(member_count);
					}
				},
				10,
				2
			);
		},

		_search_url: 'groupusersajax.search',

		_search_params: {
			group_id: peepsogroupsdata.group_id,
			keys: 'id,avatar,cover,fullname,profileurl,role',
			limit: 2,
			page: 1
		},

		_search_render_html: function (data) {
			var template = peepso.template(peepsogroupsdata.memberItemTemplate),
				html = '',
				i;

			if (data.members && data.members.length) {
				for (i = 0; i < data.members.length; i++) {
					html += template($.extend({}, data.members[i]));
				}
			}

			this._get_passive_actions();

			return html;
		},

		_search_get_items: function () {
			return this._search_$ct.children('.ps-js-member');
		},

		/**
		 * @param {object} params
		 * @returns jQuery.Deferred
		 */
		_fetch: function (params) {
			return $.Deferred(
				$.proxy(function (defer) {
					// Multiply limit value by 2 which translate to 2 rows each call.
					params = $.extend({}, params);
					if (!_.isUndefined(params.limit)) {
						params.limit *= 2;
					}

					this._fetch_xhr && this._fetch_xhr.abort();
					this._fetch_xhr = peepso
						.disableAuth()
						.disableError()
						.getJson(
							this._search_url,
							params,
							$.proxy(function (response) {
								if (response.success) {
									defer.resolveWith(this, [response.data]);
								} else {
									defer.rejectWith(this, [response.errors]);
								}
							}, this)
						);
				}, this)
			);
		},

		/**
		 * Filter search based on selected elements.
		 */
		_filter: function () {
			var query = $.trim(this._search_$query.val()),
				sortby = this._search_$sortby.val().split('|'),
				gender = this._search_$gender.val(),
				avatar = this._search_$avatar[0].checked ? 1 : 0,
				following = this._search_$following[0].value,
				extended = {};

			// abort current request
			this._fetch_xhr && this._fetch_xhr.abort();

			this._search_params.query = query || undefined;
			this._search_params.order_by = sortby[0] || undefined;
			this._search_params.order = sortby[1] || undefined;
			this._search_params.peepso_gender = gender || undefined;
			this._search_params.peepso_avatar = avatar || undefined;
			this._search_params.peepso_following = following || undefined;
			this._search_params.page = 1;

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

			this._search();
		},

		/**
		 * Get passive action for each group member
		 */
		_get_passive_actions: _.debounce(function () {
			var className = 'ps-js-actions-placeholder',
				$actions = this._search_$ct.find('.' + className),
				template = peepso.template(peepsogroupsdata.memberItemActionsTemplate);

			$actions.each(
				$.proxy(function (index, elem) {
					var $action = $(elem).removeClass(className),
						user_id = +$action.data('id');

					$action.show();

					this._fetch_passive_actions(user_id)
						.done(function (data) {
							data = $.extend(data, {
								id: this._search_params.group_id,
								passive_user_id: user_id
							});
							$action.html(template(data));
						})
						.fail(function () {
							$action.remove();
						});
				}, this)
			);
		}, 100),

		/**
		 * @param {number} user_id
		 * @returns jQuery.Deferred
		 */
		_fetch_passive_actions: function (user_id) {
			var params = {
				group_id: this._search_params.group_id,
				passive_user_id: user_id
			};

			return $.Deferred(
				$.proxy(function (defer) {
					peepso.getJson(
						'groupuserajax.member_passive_actions',
						params,
						$.proxy(function (response) {
							var data = response.data || {};
							if (data.member_passive_actions && data.member_passive_actions.length) {
								defer.resolveWith(this, [data]);
							} else {
								defer.rejectWith(this);
							}
						}, this)
					);
				}, this)
			);
		},

		/**
		 * Toggle search filter form.
		 */
		_toggle: function () {
			$('.ps-js-page-filters').stop().slideToggle();
		}
	});

	return PsPageGroupMembers;
});
