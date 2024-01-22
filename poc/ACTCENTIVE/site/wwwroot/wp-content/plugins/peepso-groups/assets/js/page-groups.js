import $ from 'jquery';
import _ from 'underscore';
import peepso, { ls, template as compileTemplate } from 'peepso';
import GroupActions from './group-actions';

const ITEM_TEMPLATE = peepsogroupsdata.listItemTemplate;
const ACTIONS_TEMPLATE = peepsogroupsdata.listItemMemberActionsTemplate;

class PsPageGroups extends PsPageAutoload {
	constructor(prefix) {
		super(prefix);

		this._search_url = 'groupsajax.search';

		this._search_params = {
			uid: peepsodata.currentuserid,
			user_id: undefined,
			query: '',
			category: 0,
			order_by: undefined,
			order: undefined,
			admin: undefined,
			keys:
				'id,name,description,date_created_formatted,members_count,url,published,avatar_url_full,cover_url,privacy,groupuserajax.member_actions,groupfollowerajax.follower_actions',
			limit: 2,
			page: 1
		};
	}

	onDocumentLoaded() {
		if (super.onDocumentLoaded() === false) {
			return false;
		}

		this._search_$query = $('.ps-js-groups-query');
		this._search_$sortby = $('.ps-js-groups-sortby');
		this._search_$sortorder = $('.ps-js-groups-sortby-order');
		this._search_$category = $('.ps-js-groups-category');
		this._search_$searchmode = $('.ps-js-groups-search-mode');

		this._search_params.user_id = +peepsodata.userid || undefined;
		this._search_params.category = this._search_$category.val() || 0;
		this._search_params.search_mode = this._search_$searchmode.val() || undefined;

		this._search_$query.on('input', () => {
			this._filter();
		});
		this._search_$sortby.on('change', () => {
			this._filter();
		});
		this._search_$sortorder.on('change', () => {
			this._filter();
		});
		this._search_$category.on('change', e => {
			this._filter_category(e);
		});
		this._search_$searchmode.on('change', () => {
			this._filter();
		});

		$('.ps-form-search-opt').on('click', () => {
			this._toggle();
		});

		// Toggle view mode on the group listing.
		var mode = this._search_$ct.data('mode') || 'grid';
		if (+peepsodata.currentuserid) {
			var userMode = ls.get('groups_viewmode_' + peepsodata.currentuserid);
			mode = userMode || mode;
		}

		this.toggleViewMode(mode);
		$('.ps-js-groups-viewmode').on('click', e => {
			e.preventDefault();

			let mode = $(e.currentTarget).data('mode');

			if (+peepsodata.currentuserid) {
				ls.set('groups_viewmode_' + peepsodata.currentuserid, mode);
			}

			this.toggleViewMode(mode);
		});

		this._filter();
	}

	/**
	 * Toggle view mode.
	 *
	 * @param {string} mode
	 */
	toggleViewMode(mode) {
		let $buttons = $('.ps-js-groups-viewmode'),
			$active = $buttons.filter(`[data-mode="${mode}"]`),
			$lists = $('.ps-js-groups'),
			activeClass = 'ps-btn--active',
			listSingleClass = 'ps-groups__list--single';

		$active.addClass(activeClass);
		$buttons.not($active).removeClass(activeClass);

		if ('list' === mode) {
			$lists.addClass(listSingleClass);
		} else {
			$lists.removeClass(listSingleClass);
		}
	}

	/**
	 * Build html representation on some group items.
	 *
	 * @param {Object} data
	 * @returns {string}
	 */
	_search_render_html(data) {
		let itemTemplate = compileTemplate(ITEM_TEMPLATE),
			actionsTemplate = compileTemplate(ACTIONS_TEMPLATE),
			query = this._search_params.query,
			html = '',
			groupData,
			reQuery,
			highlight,
			actions,
			isMarkdown,
			i;

		if (data.groups && data.groups.length) {
			for (i = 0; i < data.groups.length; i++) {
				groupData = data.groups[i];
				groupData.nameHighlight = groupData.name || '';

				isMarkdown = groupData.description.match('peepso-markdown');
				if (isMarkdown) {
					// Parse markdown content.
					groupData.description = peepso.observer.applyFilters(
						'peepso_parse_content',
						'<div class="peepso-markdown">' + groupData.description + '</div>'
					);
				} else {
					// Decode html entities on description.
					groupData.description = $('<div/>')
						.html(groupData.description || '')
						.text();
				}

				// Highlight keyword found in title and description.
				if (query) {
					reQuery = _.filter(query.split(' '), function (str) {
						return str !== '';
					});
					reQuery = _.map(reQuery, function (str) {
						return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
					});

					reQuery = RegExp('(' + reQuery.join('|') + ')', 'ig');
					highlight =
						'<span style="background:' + peepso.getLinkColor(0.3) + '">$1</span>';
					groupData.nameHighlight = groupData.nameHighlight.replace(reQuery, highlight);

					if (!isMarkdown) {
						// Only highlight keyword if it is not markdown.
						groupData.description = (groupData.description || '').replace(
							reQuery,
							highlight
						);
					}
				}

				html += itemTemplate($.extend({}, groupData, { member_actions: '' }));

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
		}

		return html;
	}

	_search_get_items() {
		return this._search_$ct.children('.ps-js-group-item');
	}

	/**
	 * @returns boolean
	 */
	_search_should_load_more() {
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
	}

	/**
	 * Fetch group items based on provided parameters.
	 *
	 * @param {object} params
	 * @returns {jQuery.Deferred}
	 */
	_fetch(params) {
		return $.Deferred(defer => {
			let transport = peepso.disableAuth().disableError(),
				url = this._search_url;

			params = $.extend({}, params);

			// If the "load more button" setting is enabled, limit should respect it.
			if (this._search_loadmore_enable && this._search_loadmore_repeat) {
				params.limit = this._search_loadmore_repeat;
				// Make sure limit is an even number.
				// if (params.limit % 2) {
				// 	params.limit += params.page % 2 ? 1 : -1;
				// }
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
		});
	}

	/**
	 * Filter search based on selected elements.
	 */
	_filter() {
		// abort current request
		this._fetch_xhr && this._fetch_xhr.abort();

		this._search_params.query = $.trim(this._search_$query.val());
		this._search_params.category = this._search_$category.val();
		this._search_params.order_by = this._search_$sortby.val();
		this._search_params.order = this._search_$sortorder.val();
		this._search_params.search_mode = this._search_$searchmode.val();
		this._search_params.page = 1;

		this._search();
	}

	/**
	 * Filter by category.
	 *
	 * @param {Event} e
	 */
	_filter_category(e) {
		this._filter();

		let url = window.location.href,
			reg = /(category=)-?\d+/,
			val = e.target.value || 0;

		// update url
		if (url.match(reg)) {
			url = url.replace(reg, '$1' + val);
			if (window.history && history.pushState) {
				history.pushState(null, '', url);
			}
		}
	}

	/**
	 * Toggle search filter form.
	 */
	_toggle() {
		$('.ps-js-page-filters').stop().slideToggle();
	}
}

new PsPageGroups('.ps-js-groups');
