import $ from 'jquery';
import _ from 'underscore';

const EVT_SUFFIX = 'ps-focus-area-footer';

/** @class */
export default class FocusAreaFooter {
	/** @type {JQuery} */ $footer;
	/** @type {JQuery} */ $interactions;
	/** @type {JQuery} */ $menu;
	/** @type {JQuery} */ $menuInner;
	/** @type {JQuery} */ $menuItems;
	/** @type {JQuery} */ $menuMore;
	/** @type {JQuery} */ $menuDropdown;

	/**
	 * Initialize focus area footer.
	 *
	 * @param {HTMLElement} footer
	 */
	constructor(footer) {
		this.$footer = $(footer);
		this.$interactions = this.$footer.find('.ps-js-focus-interactions');
		this.$menu = this.$footer.find('.ps-js-focus__menu');
		this.$menuInner = this.$menu.find('.ps-js-focus__menu-inner');
		this.$menuItems = this.$menu.find('.ps-js-item');
		this.$menuMore = this.$menu.find('.ps-js-item-more');
		this.$menuDropdown = this.$menu.find('.ps-js-focus-link-dropdown');
		this.$menuAidLeft = this.$menu.find('.ps-js-aid-left');
		this.$menuAidRight = this.$menu.find('.ps-js-aid-right');

		this.centerActiveMenu();

		// Rearrange on page load.
		_.defer(() => this.rearrange());

		// Rearrange on viewport dimension change.
		$(window).on(
			`resize.${EVT_SUFFIX}`,
			_.debounce(() => this.rearrange(), 1000)
		);
	}

	/**
	 * Check if viewport is narrow.
	 *
	 * @returns {boolean}
	 */
	isNarrow() {
		return window.innerWidth <= 740;
	}

	/**
	 * Check if the device has touch support.
	 *
	 * @returns {boolean}
	 */
	hasTouch() {
		return 'ontouchstart' in window || navigator.msMaxTouchPoints > 0;
	}

	/**
	 * Make sure the active menu is visible on a narrow view.
	 */
	centerActiveMenu() {
		if (!this.isNarrow()) {
			return;
		}

		let $active = this.$menuItems.filter('.ps-js-item-active');
		if (!$active.length) {
			return;
		}

		let $viewport = this.$menuInner,
			viewportWidth = $viewport.width(),
			activeLeft = $active.position().left,
			activeWidth = $active.width();

		$viewport.scrollLeft(activeLeft - (viewportWidth - activeWidth) / 2);
	}

	/**
	 * Reset links placement.
	 */
	reset() {
		this.$menuDropdown.hide().empty();
		this.$menuMore.detach().off('click');
		this.$menuItems.css('display', '');
		this.detachScroller();

		// Toggle scrollable aid on user scroll event.
		this.$menuInner.off('scroll').on('scroll', () => this.toggleAid());
		this.toggleAid();
	}

	/**
	 * Rearrange links placement.
	 */
	rearrange() {
		this.reset();

		// Early exit on narrow view, as it uses scrolling method instead of a dropdown.
		if (this.isNarrow()) {
			this.hasTouch() || this.attachScroller();
			return;
		}

		let maxWidth = this.$menu.width(),
			maxIterations = 20,
			iterations = 0,
			$last,
			$ref;

		// Loop until container width is below it's parent width.
		// Set to max 20 iterations to prevent potential measurement error due to external styling issue.
		while (++iterations <= maxIterations) {
			$last = this.$menuItems.filter(':visible').last();

			// Ends loop if the reference item is visible in viewport.
			$ref = iterations === 1 ? $last : this.$menuMore;
			if (peepso.rtl && $ref.position().left >= 0) {
				break;
			} else if (
				!peepso.rtl &&
				Math.floor($ref.position().left + $ref.outerWidth()) <= maxWidth
			) {
				break;
			}

			// Attach "more" dropdown on first iteration.
			if (iterations === 1) {
				this.$menuMore.insertBefore(this.$menuDropdown.parent());
				this.$menuMore.show();
				// Handle toggle dropdown.
				this.$menuMore.on('click', e => {
					e.preventDefault();
					e.stopPropagation();
					this.toggleDropdown();
				});
			}

			// Adds the last visible item into dropdown.
			$last = $last.hide().clone();
			this.$menuDropdown.prepend($last.css('display', ''));
		}
	}

	/**
	 * Toggle menu dropdown.
	 */
	toggleDropdown() {
		let $doc = $(document),
			evtName = `click.${EVT_SUFFIX}`;

		if (this.$menuDropdown.is(':visible')) {
			this.$menuDropdown.hide();
			$doc.off(evtName);
		} else {
			this.$menuDropdown.show();
			$doc.one(evtName, () => this.$menuDropdown.hide());
		}
	}

	/**
	 * Toggle scrollable aid.
	 */
	toggleAid = _.throttle(() => {
		let showAidLeft = false,
			showAidRight = false;

		if (this.isNarrow()) {
			let scrollLeft = this.$menuInner.scrollLeft(),
				scrollWidth = this.$menuInner[0].scrollWidth,
				clientWidth = this.$menuInner[0].clientWidth;

			showAidLeft = scrollLeft > 0;
			showAidRight = scrollLeft + clientWidth < scrollWidth;
		}

		showAidLeft ? this.$menuAidLeft.show() : this.$menuAidLeft.hide();
		showAidRight ? this.$menuAidRight.show() : this.$menuAidRight.hide();
	}, 250);

	/**
	 * Attach scroller on a narrow view for desktop browsers, which generally
	 * don't have touch support.
	 */
	attachScroller() {
		let scrolling = false,
			scrolled = false,
			lastPosition,
			position,
			difference;

		this.$menuInner.on('mousedown mouseup mousemove mouseleave', function (e) {
			e.preventDefault();
			e.stopPropagation();

			if (e.type === 'mousedown') {
				scrolling = true;
				scrolled = false;
				lastPosition = [e.clientX, e.clientY];
			} else if (e.type == 'mousemove' && scrolling) {
				let $inner = $(this);
				position = [e.clientX, e.clientY];
				difference = [position[0] - lastPosition[0], position[1] - lastPosition[1]];
				$inner.scrollLeft($inner.scrollLeft() - difference[0]);
				$inner.scrollTop($inner.scrollTop() - difference[1]);
				lastPosition = [e.clientX, e.clientY];
				scrolled = true;
			} else if (e.type === 'mouseup' || e.type === 'mouseleave') {
				scrolling = false;
				// Temporarily disable click event to prevent accidentally clicking the menu.
				if (scrolled) {
					let $link = $(e.target).closest('a');
					if ($link.length) {
						$link.on(`click.${EVT_SUFFIX}`, e => e.preventDefault());
						_.defer(() => $link.off(`click.${EVT_SUFFIX}`));
					}
				}
			}
		});
	}

	/**
	 * Detach scroller previously attached with `attachScroller` method.
	 */
	detachScroller() {
		this.$menuInner.off('mousedown mouseup mousemove mouseleave');
	}
}
