import $ from 'jquery';
import _ from 'underscore';
import { ajax, ls, npm, observer, template } from 'peepso';
import { currentuserid as USER_ID, userid as PROFILE_ID, rest_url as REST_URL } from 'peepsodata';

class PsPageFiles extends PsPageAutoload {
	constructor(prefix) {
		super(prefix);

		this._search_url = `${REST_URL}files`;

		this._search_params = {
			uid: USER_ID,
			user_id: PROFILE_ID,
			sort: 'desc',
			limit: 4,
			page: 1
		};
	}

	onDocumentLoaded() {
		if (super.onDocumentLoaded() === false) {
			return false;
		}

		// Templates.
		this._search_$templates = $('.ps-js-files-templates');
		this._search_$viewmode = $('.ps-js-files-viewmode');

		// Item template.
		let itemTemplate = this._search_$templates.filter('[data-name="file-item"]').text().trim();
		this.itemTemplate = template(itemTemplate);

		// Handle toggle view mode.
		this._search_$viewmode.on('click', e => {
			e.preventDefault();
			this.toggleViewMode($(e.currentTarget).data('mode'));
		});
		this.toggleViewMode(ls.get('files_viewmode'));

		// Handle delete item.
		this._search_$ct.on('click', '.ps-js-item-delete', e => this.deleteItem(e));
	}

	/**
	 * Toggle view mode.
	 *
	 * @param {string} mode
	 */
	toggleViewMode(mode) {
		let modes = ['list', 'grid'];

		mode = modes.indexOf(mode) >= 0 ? mode : modes[0];
		this._search_$viewmode.removeClass('ps-btn--active');
		this._search_$viewmode.filter(`[data-mode="${mode}"]`).addClass('ps-btn--active');
		this._search_$ct.removeClass(modes.map(mode => `ps-files__list--${mode}`).join(' '));
		this._search_$ct.addClass(`ps-files__list--${mode}`);

		ls.set('files_viewmode', mode);
	}

	/**
	 * Delete an item.
	 *
	 * @param {Event} e
	 */
	deleteItem(e) {
		e.preventDefault();
		e.stopPropagation();

		let $item = $(e.currentTarget).closest('.ps-js-item');
		let id = $item.data('id');

		ajax.delete(this._search_url, { id }, -1).done(json => {
			if (json.success) {
				$item.remove();
			}
		});
	}

	_search_render_html(data) {
		let html = '';

		if (data instanceof Array) {
			data = data.map(item => this.itemTemplate(item));
			html = data.join('');
		}

		return html;
	}

	_search_get_items() {
		return this._search_$ct.children('.ps-js-item');
	}

	/**
	 * Fetch file contents.
	 *
	 * @param {Object} params
	 * @returns {JQueryDeferred}
	 */
	_fetch(params) {
		params = observer.applyFilters('files_get_list_files', params);

		// Multiply limit value by 2 which translate to 2 rows each call.
		params = $.extend({}, params);
		if (!_.isUndefined(params.limit)) {
			params.limit *= 2;
		}

		// TODO: abortable request.
		return $.Deferred(defer => {
			ajax.get(this._search_url, params, -1)
				.done(json => {
					if (json.files instanceof Array) {
						if (!json.files.length && 1 === params.page && json.message) {
							let errors = [`<div class="ps-alert">${json.message}</div>`];
							defer.rejectWith(this, [errors]);
						} else {
							defer.resolveWith(this, [json.files]);
						}
					} else {
						defer.rejectWith(this, [json.errors]);
					}
				})
				.fail(() => defer.rejectWith(this));
		});
	}
}

new PsPageFiles('.ps-js-files');
