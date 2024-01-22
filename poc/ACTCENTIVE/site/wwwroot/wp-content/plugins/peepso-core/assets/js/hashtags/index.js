import $ from 'jquery';
import { browser, observer } from 'peepso';
import { hashtags as hashtagsData } from 'peepsodata';

const HASHTAG_URL = hashtagsData.url;
const HASHTAG_EVERYTHING = +hashtagsData.everything || 0;
const HASHTAG_MIN_LENGTH = HASHTAG_EVERYTHING ? 0 : +hashtagsData.min_length || 0;
const HASHTAG_MAX_LENGTH = HASHTAG_EVERYTHING ? 10000 : +hashtagsData.max_length || 10000;
const HASHTAG_MUST_START_WITH_LETTER = HASHTAG_EVERYTHING
	? 0
	: +hashtagsData.must_start_with_letter || 0;

// Build hashtag pattern based on above configuration.
const HASHTAG_PATTERN = (() => {
	let startWithLetter, minLength, maxLength, pattern;

	if (HASHTAG_EVERYTHING) {
		pattern = '(^|>|\\s)(#([^#\\s<]+))';
	} else {
		startWithLetter = HASHTAG_MUST_START_WITH_LETTER;
		minLength = Math.max(0, HASHTAG_MIN_LENGTH - (startWithLetter ? 1 : 0));
		maxLength = Math.max(0, HASHTAG_MAX_LENGTH - (startWithLetter ? 1 : 0));

		pattern =
			'(^|>|\\s)(#(' +
			(startWithLetter ? '[a-z]' : '') +
			'[a-z0-9]{' +
			minLength +
			',' +
			maxLength +
			'}' +
			'))';
	}

	return new RegExp(pattern, 'ig');
})();

let instance;

export default class Hashtags {
	constructor() {
		if (!instance) {
			instance = this;

			this.hashtag = undefined;
			this.$filter = null;
			this.$toggle = null;
			this.$input = null;
			this.$apply = null;

			this.assignHooks();

			// Initialize activity stream filter.
			$(() => {
				this.initActivityFilter();
			});
		}

		return instance;
	}

	/**
	 * Filter activity stream.
	 * @param {string} hashtag
	 */
	filter(hashtag) {
		let prevHashtag = this.hashtag;

		this.hashtag = hashtag;

		// Update filter toggle button.
		if (!hashtag) {
			let label = this.$toggle.data('empty');
			this.$toggle.html(label);

			// Update postbox content.
			let $postboxText = $('#postbox-main textarea.ps-postbox-textarea').eq(0);
			if ($postboxText.length && $postboxText.val() === `#${prevHashtag} `) {
				$postboxText.val('').trigger('input');
			}
		} else {
			let label =
				this.$toggle.data('keyword') +
				hashtag +
				' <i class="ps-posts__filter-remove gcis gci-times-circle"></i>';
			this.$toggle.html(label);
			this.$toggle.one('click', 'i', () => {
				this.filter('');
			});

			// Update postbox content.
			let $postboxText = $('#postbox-main textarea.ps-postbox-textarea').eq(0);
			if ($postboxText.length && !$postboxText.val()) {
				$postboxText.val(`#${hashtag} `).trigger('input');
			}
		}

		// Update input element.
		this.$input.val(hashtag);

		observer.doAction('peepso_stream_reset');
	}

	assignHooks() {
		// Filter postbox beautifier.
		observer.addFilter(
			'peepso_postbox_beautifier',
			html => {
				return html.replace(HASHTAG_PATTERN, ($0, $1, $2, $3) => {
					return $1 + '<ps_span class="ps-tag">' + $2 + '</ps_span>';
				});
			},
			20,
			1
		);

		// Filter-out URL hash mistakenly marked as hashtag.
		observer.addFilter(
			'peepso_activity',
			$posts => {
				if (this.hashtag) {
					$posts = $posts.map(function () {
						let $post = $(this),
							html = $post.html();

						// Only filter-out post element.
						if ($post.closest('.ps-js-activity').length) {
							if (typeof html === 'string' && !html.match(HASHTAG_PATTERN)) {
								return null;
							}
						}

						return this;
					});
				}

				return $posts;
			},
			20,
			1
		);

		// Filter activity stream search parameters.
		observer.addFilter(
			'show_more_posts',
			params => {
				if (this.hashtag) {
					params.search_hashtag = this.hashtag;
				}
				return params;
			},
			10,
			1
		);
	}

	initActivityFilter() {
		this.$filter = $('.ps-js-activitystream-filter').filter('[data-id=peepso_search_hashtag]');
		this.$toggle = this.$filter.find('.ps-js-dropdown-toggle span');
		this.$input = this.$filter.find('input[type=text]');
		this.$apply = this.$filter.find('.ps-js-search-hashtag');

		this.$input
			.on('input', e => {
				let el = e.target,
					keyword = el.value,
					filtered;

				if (HASHTAG_EVERYTHING) {
					// #5929 When using non-latin keyboard layout (e.g., Chinese Simplified), iOS prints
					// temporary text in alphabet which contains spaces.
					filtered = keyword.replace(browser.isIOS() ? /[#<]/g : /[#\s<]/g, '');
				} else {
					filtered = keyword.replace(/[^a-z0-9]/gi, '');
				}

				// Filter-out punctuations.
				if (keyword !== filtered) {
					el.value = filtered;
				}

				// Disable apply button if hashtag string length is invalid.
				let length = filtered.length;
				if (length < HASHTAG_MIN_LENGTH || length > HASHTAG_MAX_LENGTH) {
					this.$apply.attr('disabled', 'disabled');
				} else {
					this.$apply.removeAttr('disabled');
				}
			})
			.triggerHandler('input');

		// Apply search when Enter key is pressed.
		this.$input.on('keyup', e => {
			if (e.keyCode === 13) {
				e.preventDefault();
				e.stopPropagation();
				this.$apply.click();
			}
		});

		this.$apply.on('click', () => {
			let val = this.$input.val().trim(),
				length = val.length;

			if (length >= HASHTAG_MIN_LENGTH && length <= HASHTAG_MAX_LENGTH) {
				this.filter(val);
			}
		});

		// Automatically fires filter hashtag on page load if url match.
		let url = decodeURIComponent(window.location.href),
			pattern = HASHTAG_EVERYTHING
				? /\/\??hashtag\/([^#\s<\/]+)/
				: /\/\??hashtag\/([a-z0-9]+)/i,
			matches = url.match(pattern);

		if (matches) {
			this.filter(matches[1]);
		}
	}
}

// Auto-initialize hashtags class.
new Hashtags();
