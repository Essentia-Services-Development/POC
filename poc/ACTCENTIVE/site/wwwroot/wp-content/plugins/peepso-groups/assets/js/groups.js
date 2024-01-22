var GroupActions = require('./group-actions').default;

(function ($, peepso, factory) {
	/**
	 * PsGroups global instance.
	 * @name ps_groups
	 * @type {PsGroups}
	 */
	window.ps_groups = new (factory($, peepso))();
})(jQuery, peepso, function ($, peepso) {
	/**
	 * PsGroups class.
	 * @class PsGroups
	 */
	function PsGroups() {
		peepso.observer.addFilter(
			'group_update_member_count',
			function (group_id, member_count) {
				var $item = $('.ps-js-group-item--' + group_id),
					$count = $item.find('.ps-js-member-count'),
					label,
					html;
				if ($count.length) {
					label =
						+member_count > 1
							? peepsogroupsdata.lang.members
							: peepsogroupsdata.lang.member;
					$count.html(member_count + ' ' + label);
				}
			},
			10,
			2
		);

		$(document.body).on(
			'click',
			'.ps-js-groups .ps-js-member-action',
			$.proxy(function (e) {
				var $btn = $(e.currentTarget),
					$loading,
					data,
					method,
					confirm;

				e.preventDefault();
				e.stopPropagation();

				if ($btn.data('ps-loading')) {
					return;
				}

				data = $.extend({}, $btn.data());
				if (!data.method || !data.id) {
					return;
				}

				$loading = $btn.find('img');
				if (!$loading.length && $btn.parent().hasClass('ps-js-dropdown-menu')) {
					$loading = $btn.parent().siblings('.ps-js-dropdown-toggle');
					$loading = $loading.find('img');

					// Hide dropdown automatically if loading is on the trigger button.
					$btn.parent().hide();
				}

				method = data.method;
				confirm = data.confirm;
				data.group_id = data.id;
				delete data.method;
				delete data.confirm;
				delete data.id;

				this._member_action_confirmation(confirm).done(function () {
					$btn.data('ps-loading', true);
					$loading.show();

					this.member_action(method, data).done(function (json) {
						var $actions = $btn.closest('.ps-js-member-actions'),
							actionsTemplate = peepso.template(
								peepsogroupsdata.listItemMemberActionsTemplate
							),
							html = actionsTemplate($.extend({ id: data.group_id }, json));

						$actions.html(html);
						if (json.member_count) {
							peepso.observer.applyFilters(
								'group_update_member_count',
								data.group_id,
								json.member_count
							);
						}
					});
				});
			}, this)
		);

		$(document.body).on(
			'click',
			'.ps-js-groups .ps-js-more',
			$.proxy(function (e) {
				var itemSelector = '.ps-js-group-item',
					expandedClassName = 'ps-group--expanded',
					$wrapper = $(e.currentTarget).closest(itemSelector);

				e.preventDefault();
				e.stopPropagation();

				$('.ps-groups')
					.find(itemSelector)
					.each(function () {
						var $item = $(this);
						if ($item.is($wrapper) && !$item.hasClass(expandedClassName)) {
							$item.addClass(expandedClassName);
							$item.find('.ps-js-more span').html(peepsogroupsdata.lang.less);
						} else if ($item.hasClass(expandedClassName)) {
							$item.removeClass(expandedClassName);
							$item.find('.ps-js-more span').html(peepsogroupsdata.lang.more);
						}
					});
			}, this)
		);
	}

	/**
	 * @memberof PsGroups
	 * @param {object} params
	 */
	PsGroups.prototype._fetch = function (params) {
		return $.Deferred(
			$.proxy(function (defer) {
				var category = (params.category || 0) + '';
				this._fetch_xhr = this._fetch_xhr || {};
				this._fetch_xhr[category] && this._fetch_xhr[category].abort();
				this._fetch_xhr[category] = peepso.disableAuth().getJson(
					'groupsajax.search',
					params,
					$.proxy(function (response) {
						var itemTemplate = peepso.template(peepsogroupsdata.listItemTemplate),
							actionsTemplate = peepso.template(
								peepsogroupsdata.listItemMemberActionsTemplate
							),
							data = response.data || {},
							html = '',
							groupData,
							reQuery,
							highlight,
							actions,
							isMarkdown,
							i;

						if (data.groups && data.groups.length) {
							// group listing found
							for (i = 0; i < data.groups.length; i++) {
								groupData = data.groups[i];
								groupData.nameHighlight = groupData.name || '';

								// Parse markdown content.
								isMarkdown = groupData.description.match('peepso-markdown');
								if (isMarkdown) {
									// Parse markdown content.
									groupData.description = peepso.observer.applyFilters(
										'peepso_parse_content',
										'<div class="peepso-markdown">' +
											groupData.description +
											'</div>'
									);
								} else {
									// Decode html entities on description.
									groupData.description = $('<div/>')
										.html(groupData.description || '')
										.text();
								}

								if (params.query) {
									reQuery = params.query.replace(
										/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g,
										'\\$&'
									);
									reQuery = RegExp('(' + reQuery + ')', 'ig');
									highlight =
										'<span style="background:' +
										peepso.getLinkColor(0.3) +
										'">$1</span>';
									groupData.nameHighlight = groupData.nameHighlight.replace(
										reQuery,
										highlight
									);

									if (!isMarkdown) {
										// Only highlight keyword if it is not markdown.
										groupData.description = (
											groupData.description || ''
										).replace(reQuery, highlight);
									}
								}

								html += itemTemplate(
									$.extend({}, groupData, { member_actions: '' })
								);

								// Render group actions.
								_.defer(function (data) {
									var $card = $('.ps-js-group-item--' + data.id),
										$actions = $card.find('.ps-js-member-actions'),
										actions;

									actions = new GroupActions({
										id: data.id,
										member_actions: data.groupuserajax.member_actions,
										follower_actions: data.groupfollowerajax.follower_actions
									});

									$actions.html(actions.$el);
								}, groupData);
							}
							defer.resolveWith(this, [html]);
						} else if (params.page > 1) {
							// empty list
							defer.resolveWith(this, [null]);
						} else {
							// error
							defer.rejectWith(this, [response.errors]);
						}
					}, this)
				);
			}, this)
		);
	};

	/**
	 * TODO: should be part of `PsGroup` class
	 * @memberof PsGroups
	 * @param {string} method
	 * @param {object} data
	 */
	PsGroups.prototype._member_action_confirmation = function (confirm) {
		return $.Deferred(
			$.proxy(function (defer) {
				if (confirm) {
					pswindow.confirm(
						confirm,
						$.proxy(function () {
							pswindow.hide();
							defer.resolveWith(this);
						}, this),
						$.proxy(function () {
							defer.rejectWith(this);
						}, this)
					);
				} else {
					defer.resolveWith(this);
				}
			}, this)
		);
	};

	/**
	 * TODO: should be part of `PsGroup` class
	 * @memberof PsGroups
	 * @param {string} method
	 * @param {object} data
	 */
	PsGroups.prototype.member_action = function (method, data) {
		return $.Deferred(
			$.proxy(function (defer) {
				if ((method || '').indexOf('.') < 0) {
					method = 'groupuserajax.' + method;
				}
				peepso.postJson(
					method,
					data,
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
	};

	return PsGroups;
});
