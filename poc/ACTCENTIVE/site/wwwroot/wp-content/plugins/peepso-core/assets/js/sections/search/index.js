import $ from 'jquery';
import { debounce } from 'underscore';
import { modules, template } from 'peepso';
import { rest_url, site_url, sections as sectionsData } from 'peepsodata';

const _data = sectionsData.search || {};

const REST_URL = rest_url;
const SHOW_EMPTY_SECTIONS = +_data.show_empty_sections;

let uniqueId = 0;

class Search {
	constructor(elem) {
		this.uniqueId = ++uniqueId;

		this.$container = $(elem);
		this.$query = this.$container.find('input.ps-js-query');
		this.$loading = this.$container.find('.ps-js-loading').hide();
		this.$results = this.$container.find('.ps-js-result').hide();
		this.tplSection = template(this.$container.find('.ps-js-template-section').text().trim());
		this.tplItems = template(this.$container.find('.ps-js-template-items').text().trim());
		this.tplEmpty = template(this.$container.find('.ps-js-template-empty').text().trim());

		this.$query.on('input', e => {
			let query = this.$query.val().trim();

			// Do not search on empty query.
			if (query) {
				this.$results.hide();
				this.$loading.show();
				this.searchWithDelay();
			} else {
				this.$results.hide();
				this.$loading.hide();
			}
		});

		// Automatically trigger search if input is not empty.
		if (this.$query.val().trim()) {
			this.$query.triggerHandler('input');
		}
	}

	/**
	 * Calls AJAX search endpoint based on the current query string.
	 */
	search() {
		let query = this.$query.val().trim(),
			endpoint = `${REST_URL}search`,
			endpoint_id = `${endpoint}_${this.uniqueId}`,
			params = { query },
			transport;

		// Do not search on empty query.
		if (!query) {
			this.$loading.hide();
			this.$results.hide();
			return;
		}

		transport = modules.request.get(endpoint_id, endpoint, params);
		transport
			.then(json => {
				this.render(json);
				this.$loading.hide();
				this.$results.show();
			})
			.catch(() => {
				this.$loading.hide();
			});
	}

	/**
	 * Delayed search to be used with the input event.
	 */
	searchWithDelay() {
		// Replace the function with the debounced one on first call.
		this.searchWithDelay = debounce(this.search, 1000);
		this.searchWithDelay();
	}

	/**
	 * Render search result.
	 *
	 * @param {Object} data
	 */
	render(data) {
		let results = data.results,
			sections = data.meta.sections,
			has_result = !!SHOW_EMPTY_SECTIONS,
			html = '';

		if (results) {
			for (const key in results) {
				let section = $.extend({}, sections[key], { type: key });

				if (results.hasOwnProperty(key) && results[key].length) {
					section.html = this.tplItems({ results: results[key], type: key });
					has_result = true;
				} else {
					section.html = this.tplEmpty();
				}

				html += this.tplSection(section);
			}
		}

		if (!has_result) {
			html = this.tplEmpty();
		}

		this.$results.html(html);
	}
}

function init() {
	$('.ps-js-section-search').each(function () {
		new Search(this);
	});
}

export default { init };
