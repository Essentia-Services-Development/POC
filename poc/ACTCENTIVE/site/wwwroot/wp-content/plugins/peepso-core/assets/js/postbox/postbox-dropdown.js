import $ from 'jquery';
import { observer } from 'peepso';

let dropdownCounter = 0;

/**
 * Filter hook to get the PostboxDropdown class.
 */
observer.addFilter('class_postbox_dropdown', () => PostboxDropdown);

/**
 * General postbox dropdown class.
 */
class PostboxDropdown {
	/**
	 * PostboxDropdown class constructor.
	 *
	 * @param {Element} elem
	 * @param {Object} opts
	 */
	constructor(elem, opts = {}) {
		this.id = `ps-postbox-dropdown--${++dropdownCounter}`;

		this.opts = opts;

		this.$postbox = $(elem).closest('.ps-js-postbox');
		this.$container = $(elem);
		this.$toggle = this.$container.find('.ps-js-postbox-toggle').first();
		this.$dropdown = this.$container.find('.ps-js-postbox-dropdown').first();

		this.$toggle.on('click', () => this.toggle());
	}

	/**
	 * Toggle dropdown.
	 */
	toggle() {
		this.$dropdown.is(':visible') ? this.hide() : this.show();
	}

	/**
	 * Show the dropdown.
	 */
	show() {
		this.$dropdown.show();
		this.$container.addClass('ps-postbox__menu-item--open');

		// Add autohide on document-click.
		setTimeout(() => {
			$(document)
				.off(`mouseup.${this.id}`)
				.on(`mouseup.${this.id}`, e => {
					if (this.$container.has(e.target).length === 0) {
						this.hide();
					}
				});
		}, 1);
	}

	/**
	 * Hide the dropdown.
	 */
	hide() {
		this.$dropdown.hide();
		this.$container.removeClass('ps-postbox__menu-item--open');
		$(document).off(`mouseup.${this.id}`);
	}
}

export default PostboxDropdown;
