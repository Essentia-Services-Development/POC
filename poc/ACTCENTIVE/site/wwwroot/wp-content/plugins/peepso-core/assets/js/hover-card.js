import $ from 'jquery';
import _ from 'underscore';
import peepso, { observer, Promise } from 'peepso';
import { hovercard as hovercardData } from 'peepsodata';

/**
 * Use single hovercard element for all instances.
 *
 * @type {JQuery}
 * @private
 */
let $card;

/**
 * Child element of `$card`.
 *
 * @type {JQuery}
 * @private
 */
let $cardInner;

/**
 * Timer to cancel toggle hovercard.
 *
 * @type {number}
 * @private
 */
let timer;

/**
 * Initialize hovercard.
 *
 * @returns {JQuery}
 * @private
 */
function create(template) {
	$cardInner = $('<div />').append(hovercardData.template);
	$cardInner.css({ position: 'absolute' });

	$card = $('<div />').css({
		display: 'none',
		height: 1,
		overflow: 'visible',
		position: 'absolute',
		width: 1,
		// Hover Card z-index must be above lightbox z-index to make it visible on the lightbox.
		zIndex: 100001
	});
	$card.on('mouseenter touchstart', function (e) {
		e.stopPropagation();
		show();
	});
	$card.on('mouseleave', function (e) {
		e.stopPropagation();
		hide();
	});

	$card.append($cardInner).appendTo(document.body);
	return $card;
}

/**
 * Show hovercard next to an element.
 *
 * @param {Object|string} [data]
 * @param {string} [data.name]
 * @param {string} [data.avatar]
 * @param {string} [data.cover]
 * @param {string} [data.views]
 * @param {string} [data.likes]
 * @param {string} [data.link]
 * @param {Event} [e]
 * @private
 */
function show(data, e) {
	clearTimeout(timer);
	$card = $card || create();

	// Show loading.
	if (data === 'loading') {
		$card.find('.ps-js-loading').show();
	}
	// Or, update content if needed.
	else if (data) {
		$card.find('.ps-js-loading').hide();
		$card.find('.ps-js-name').html(data.name);
		$card.find('.ps-js-avatar').attr('src', data.avatar);
		$card.find('.ps-js-cover').css({ backgroundImage: `url(${encodeURI(data.cover)})` });
		$card.find('.ps-js-views').html(data.views || 0);
		$card.find('.ps-js-likes').html(data.likes || 0);
		$card.find('.ps-js-link').attr('href', data.link || '#');

		// #6060 Hide element if view count === -1.
		let $views = $card.find('.ps-js-views').closest('.ps-hovercard__count--views');
		+data.views === -1 ? $views.hide() : $views.show();

		// Fire action hooks so that other plugins can update the HTML.
		observer.doAction('hovercard_update_html', $card, data);
	}

	$card.show();

	// Autohide card on click outside of the element/card.
	if (peepso.isTouch()) {
		$(document).off('touchstart.hovercard').one('touchstart.hovercard', hide);
	}

	// Reposition the hovercard if needed.
	if (e && e.currentTarget && e.clientX) {
		let $elem = $(e.currentTarget),
			offset = $elem.offset(),
			isNarrow = window.innerWidth <= 480;

		// Set initial position to top-right.
		$cardInner.css({
			top: '',
			left: 0,
			bottom: 0,
			right: isNarrow ? 0 : ''
		});

		$card.css({
			top: offset.top - $card.height(),
			left: isNarrow ? 0 : e.clientX,
			right: isNarrow ? 0 : '',
			width: isNarrow ? '' : 1
		});

		let rect = $cardInner.get(0).getBoundingClientRect();

		// Fix vertical hovercard position if it goes out of the viewport.
		if (rect.top < 0) {
			$card.css({ top: offset.top + $elem.height() });
			$cardInner.css({ bottom: '', top: 0 });
		}

		// Fix horizontal hovercard position if it goes out of the viewport.
		if (!isNarrow) {
			if (rect.right > (window.innerWidth || document.documentElement.clientWidth)) {
				$cardInner.css({ left: '', right: 0 });
			}
		}
	}
}

/**
 * Hide hovercard.
 *
 * @private
 */
function hide() {
	if ($card) {
		timer = setTimeout(function () {
			$card.hide();

			// Remove autohide listener if card is hidden.
			if (peepso.isTouch()) {
				$(document).off('touchstart.hovercard');
			}
		}, 200);
	}
}

/** @class */
class HoverCard {
	/**
	 * Store fetched user information.
	 *
	 * @type {Object}
	 * @static
	 */
	static cache = {};

	/** @type {JQuery} */ $elem;
	/** @type {number} */ id;
	/** @type {Object} */ data;

	/**
	 * Per-instance show hovercard delay timer.
	 *
	 * @type {number}
	 * @private
	 */
	timer;

	/**
	 * Initialize hovercard on an element.
	 *
	 * @param {HTMLElement} elem
	 * @param {number} id
	 */
	constructor(elem, id) {
		this.id = id;
		this.$elem = $(elem);
		this.$elem.on('mouseenter touchstart', e => {
			e.stopPropagation();
			this.show(e);
		});
		this.$elem.on('mouseleave', e => {
			e.stopPropagation();
			this.hide();
		});
		// Disable click on touch device.
		if (peepso.isTouch()) {
			this.$elem.on('click', e => {
				e.preventDefault();
				e.stopPropagation();
			});
		}
		// Listen for like counter update.
		observer.addAction(
			'profile_update_like',
			(userId, likeCount) => {
				if (+userId === +this.id) {
					let data = HoverCard.cache[this.id];
					if (data) {
						data.likes = likeCount;
					}
				}
			},
			10,
			2
		);
	}

	/**
	 * Get hovercard information of the element.
	 *
	 * @returns {Promise<Object,undefined>}
	 */
	getData() {
		return new Promise((resolve, reject) => {
			let data = HoverCard.cache[this.id];
			if (data) {
				resolve(data);
			} else {
				peepso.postJson('hovercard.info', { userid: this.id }, json => {
					if (json.success) {
						data = HoverCard.cache[this.id] = json.data;
						resolve(data);
					} else {
						reject();
					}
				});
			}
		});
	}

	/**
	 * Show hovercard.
	 *
	 * @param {Event} [e]
	 */
	show(e) {
		this.timer = setTimeout(() => {
			let timer = setTimeout(() => {
				show('loading', e);
			}, 500);

			this.getData().then(data => {
				// Cancel loading timer.
				clearTimeout(timer);

				// In case the `hide` method is called during data fetching.
				if (!this.timer) {
					return;
				}

				show(data, e);
			});
		}, 300);
	}

	/**
	 * Hide hovercard.
	 */
	hide() {
		clearTimeout(this.timer);
		this.timer = null;
		hide();
	}
}

// Export HoverCard class.
export default HoverCard;

// Lazy-initialize hovercard on mouseenter/touchstart event.
$(function () {
	// Check if hovercard is disabled.
	if (!hovercardData) {
		return;
	}

	$(document).on('mouseenter touchstart', '[data-hover-card]', function (e) {
		let $elem = $(e.currentTarget);

		// Skip if element is already initialized.
		if ($elem.data('ps-hovercard')) {
			return;
		}

		let userid = $elem.data('hover-card'),
			hovercard = new HoverCard($elem[0], userid);

		$elem.data('ps-hovercard', hovercard);
		hovercard.show(e);
	});
});
