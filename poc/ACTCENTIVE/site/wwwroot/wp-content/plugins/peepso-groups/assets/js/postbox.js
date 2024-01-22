import $ from 'jquery';
import _ from 'underscore';
import peepso, { hooks, observer } from 'peepso';
import { currentuserid as LOGIN_USER_ID, groups as groupsData } from 'peepsodata';

const MODULE_ID = window.peepsogroupsdata.module_id;
const FORCE_POSTS_IN_GROUPS = +window.peepsogroupsdata.force_posts_in_groups;

const PostboxDropdown = observer.applyFilters('class_postbox_dropdown', 10, 1);

/**
 * Postbox group selector dropdown.
 */
class PostboxGroup extends PostboxDropdown {
	/**
	 * @param {JQuery} $postbox
	 */
	constructor($postbox) {
		super($postbox.find('#group-tab')[0]);

		// Override this.$postbox value for now because of missing properties and methods required for various actions.
		// TODO: Do not override.
		this.$postbox = $postbox;

		this.$selected = this.$container.find('.ps-postbox__menu-item-label').hide();
		this.$radio = this.$container.find('[type=radio]');
		this.$finder = this.$container.find('.ps-js-group-finder');
		this.$search = this.$finder.find('input[type=text]');
		this.$loading = this.$finder.find('.ps-js-loading');
		this.$result = this.$finder.find('.ps-js-result');

		// Save category ID if it is currently on the group category page.
		this.CATEGORY_ID = undefined;
		if (window.peepsoGroupCategory) {
			this.CATEGORY_ID = +window.peepsoGroupCategory.id;
		}

		this.resultTemplate = peepso.template(
			this.$result.find('.ps-js-result-item-template').text()
		);

		this.$dropdown.on('click', '[data-option-value]', e => {
			let value = $(e.currentTarget).data('optionValue');

			e.stopPropagation();
			this.select(value);
			if (value !== 'group') {
				this.hide();
			}
		});

		this.$postbox.on('postbox.post_cancel postbox.post_saved', () => this.remove());

		// Filters and actions.
		observer.addFilter('postbox_req', $.proxy(this.filterPostboxReq, this), 10, 1);

		hooks.addFilter(
			'postbox_can_submit',
			'postbox_group',
			$.proxy(this.filterPostboxCanSubmit, this)
		);
	}

	/**
	 * Show dropdown.
	 */
	show() {
		super.show();

		// Hide non-group option on group category page.
		if (FORCE_POSTS_IN_GROUPS || this.CATEGORY_ID) {
			this.$radio.eq(0).closest('[data-option-value]').hide();
			this.$dropdown.find('[data-option-value] .ps-checkbox').hide();
			this.select('group');
			this.$finder.show();
		}
	}

	/**
	 * Hide dropdown.
	 */
	hide() {
		super.hide();

		// Reset view if post to group checkbox is checked, but the user
		// is not selecting a group yet.
		if (this.$radio.get(1).checked && !this.value()) {
			this.$radio.get(0).checked = true;
			this.$finder.hide();
			this.$container.removeClass('active');
			this.$selected.hide();
		}
	}

	/**
	 * Get the selected group.
	 *
	 * @returns {string|undefined}
	 */
	value() {
		let value;

		if (this.$radio.get(1).checked) {
			if (this.selectedGroup) {
				value = this.selectedGroup;
			}
		}

		return value;
	}

	/**
	 * Select the option to post it straight to a group or not.
	 *
	 * @param {string} value
	 */
	select(value) {
		if (!FORCE_POSTS_IN_GROUPS && value !== 'group') {
			this.remove();
			this.hide();
		} else if (FORCE_POSTS_IN_GROUPS || value === 'group') {
			this.$radio.get(1).checked = true;
			this.$finder.show();
			this.initFinder();
		}
	}

	/**
	 * Remove selected group.
	 */
	remove() {
		let needReset = !!this.selectedGroup;

		this.selectedGroup = undefined;

		this.$radio.get(0).checked = true;
		this.$finder.hide();
		this.$container.removeClass('active');
		this.$selected.hide();

		if (needReset) {
			observer.doAction('postbox_group_reset', this.$postbox);
		}
	}

	/**
	 * Search for available groups.
	 *
	 * @param {string} query
	 * @param {number} page
	 * @returns {JQueryXHR}
	 */
	search(query, page) {
		let endpoint = 'groupsajax.search',
			params,
			handler;

		query = (query || '').trim();
		page = parseInt(page) || 1;

		params = {
			uid: LOGIN_USER_ID,
			user_id: LOGIN_USER_ID,
			category: this.CATEGORY_ID,
			query: query,
			writable_only: 1,
			keys: 'id,name,description,avatar_url_full,privacy,groupuserajax.can_pin_posts',
			order_by: 'post_title',
			order: 'ASC',
			limit: 10,
			page: page
		};

		handler = json => {
			if (json && json.data) {
				this.query = query;
				this.page = page;
				this.render(json.data, page);
				this.searchEnd = !(json.data && json.data.groups && json.data.groups.length);
			}
		};

		this.xhr && this.xhr.abort();
		this.xhr = peepso.disableAuth().getJson(endpoint, params, handler);
		return this.xhr;
	}

	/**
	 * Render result.
	 *
	 * @param {Object} data
	 * @param {number} page
	 */
	render(data, page) {
		let $list = this.$result.children('.ps-js-result-list');

		data = data || {};
		page = page || 1;

		if (page === 1) {
			$list.empty();
		}

		if (data && data.groups && data.groups.length) {
			for (let i = 0; i < data.groups.length; i++) {
				$list.append(this.resultTemplate(data.groups[i]));
			}
		} else if (page === 1) {
			$list.append(`<li><em>${groupsData.textNoResult}</em></li>`);
		}
	}

	/**
	 * Initialize group finder form.
	 */
	initFinder() {
		if (this._initialSearch) {
			return;
		}

		this.$search.on('keydown', $.proxy(this.onQueryEnter, this));
		this.$search.on('input', $.proxy(this.onQueryInput, this));
		this.$search.on('click', e => e.stopPropagation());
		this.$result.on('click', '[data-id]', $.proxy(this.onResultSelect, this));
		this.$result.on('scroll', $.proxy(this.onResultScroll, this));

		this.$result.hide();
		this.$loading.show();
		this.search().always(() => {
			this.$loading.hide();
			this.$result.show();
		});
		this._initialSearch = true;
	}

	/**
	 * Handle input event on the search query.
	 */
	onQueryInput() {
		let value = this.$search.val(),
			trimmed = value.replace(/^\s+/, '');

		if (value !== trimmed) {
			this.$search.val(trimmed);
		}

		this.$result.hide();
		this.$loading.show();

		// Delay searching for 1s.
		clearTimeout(this._onQueryTimer);
		this._onQueryTimer = setTimeout(
			$.proxy(function () {
				this.search(trimmed, 1).always(
					$.proxy(function () {
						this.$loading.hide();
						this.$result.show();
					}, this)
				);
			}, this),
			1000
		);
	}

	/**
	 * Handle submitting search query via pressing the enter key.
	 *
	 * @param {Event} e
	 */
	onQueryEnter(e) {
		let value, trimmed;

		if (e.keyCode === 13) {
			e.preventDefault();

			value = this.$search.val();
			trimmed = value.replace(/(^\s+|\s+$)/g, '');

			// Remove searching delay currently in-progress.
			clearTimeout(this._onQueryTimer);

			this.$result.hide();
			this.$loading.show();
			this.search(trimmed, 1).always(
				$.proxy(function () {
					this.$loading.hide();
					this.$result.show();
				}, this)
			);
		}
	}

	/**
	 * Handle selecting the item on result container.
	 *
	 * @param {Event} e
	 */
	onResultSelect(e) {
		let $item = $(e.currentTarget),
			id = $item.data('id'),
			name = $item.data('name'),
			can_pin_posts = $item.data('can-pin-posts'),
			data = { can_pin_posts };

		this.selectedGroup = id;
		this.$container.addClass('active');
		this.$selected.html(name).show();
		this.hide();

		observer.doAction('postbox_group_set', this.$postbox, this.selectedGroup, data);
	}

	/**
	 * Handle autoload upon scrolling on result container.
	 *
	 * @param {Event} e
	 */
	onResultScroll(e) {
		let scrollHeight, scrollLength;

		e.stopPropagation();

		if (!this.searchEnd && this.$loading.is(':hidden')) {
			scrollHeight = this.$result[0].scrollHeight;
			scrollLength = this.$result.scrollTop() + this.$result.innerHeight();
			if (scrollLength >= scrollHeight) {
				this.$loading.show();
				this.search(this.query, this.page + 1).always(() => this.$loading.hide());
			}
		}
	}

	/**
	 * Filter hook for "postbox_req".
	 *
	 * @param {Object} params
	 * @returns {Object}
	 */
	filterPostboxReq(params) {
		let value = this.value();

		if (value) {
			params.module_id = MODULE_ID;
			params.group_id = value;
			params.force_as_group_post = 1;
			params.acc = undefined; // Unset privacy parameter.
		}

		return params;
	}

	/**
	 * Filter hook for "postbox_can_submit".
	 *
	 * @param {boolean} canSubmit
	 * @param {JQuery} $postbox
	 * @returns {boolean}
	 */
	filterPostboxCanSubmit(canSubmit, $postbox) {
		// Do not allow to proceed if group is not selected when in the group category postbox.
		if ($postbox === this.$postbox) {
			if ((FORCE_POSTS_IN_GROUPS || this.CATEGORY_ID) && !this.value()) {
				canSubmit = false;
				this.show();
			}
		}

		return canSubmit;
	}
}

// Initialize class on main postbox initialization.
observer.addAction(
	'peepso_postbox_addons',
	addons => {
		let wrapper = {
			init() {},
			set_postbox($postbox) {
				if ($postbox.find('#group-tab').length) {
					new PostboxGroup($postbox);
				}
			}
		};

		addons.push(wrapper);
		return addons;
	},
	10,
	1
);

// Hide dropdown trigger button on edit postbox initialization.
observer.addAction(
	'postbox_init',
	postbox => {
		postbox.$el.find('#group-tab').remove();
	},
	10,
	1
);
