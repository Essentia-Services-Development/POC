import $ from 'jquery';
import { template } from 'peepso';
import { dialog as dialogData } from 'peepsodata';

const TEMPLATE = dialogData && dialogData.template;
const TITLE_DEFAULT = dialogData && dialogData.text_title_default;
const TITLE_ERROR = dialogData && dialogData.text_title_error;
const TITLE_CONFIRM = dialogData && dialogData.text_title_confirm;
const BUTTON_CANCEL = dialogData && dialogData.text_button_cancel;
const BUTTON_OK = dialogData && dialogData.text_button_ok;

const optsDefault = {
	title: '',
	actions: null,
	wide: false,
	focusOnShow: true,
	closeButton: true,
	closeOnEsc: true,
	destroyOnClose: true,
	onClose: false
};

class Dialog {
	constructor(html, opts = {}) {
		this.html = html;

		// Handle default error dialog title.
		/** @deprecated This option is deprecated in favor of calling ths .error() function. */
		if (opts.error && !opts.title) {
			opts.title = TITLE_ERROR;
		}

		// Try to get another opts data attached to the template.
		try {
			let attachedOpts = $('<div/>')
				.append(this.html)
				.find('script[data-name=opts]')
				.text()
				.trim();

			if (attachedOpts) {
				opts = $.extend({}, opts, JSON.parse(attachedOpts));
			}
		} catch (e) {}

		this.opts = $.extend({}, optsDefault, opts || {});
	}

	/**
	 * Render the dialog.
	 *
	 * @returns {Dialog}
	 */
	render() {
		if (!this.$el) {
			let html = this.html;
			let opts = $.extend({ title: TITLE_DEFAULT }, this.opts);

			this.$el = $(template(TEMPLATE)({ html, opts })).hide();
			this.$el.addClass('ps-js-modal'); // Add identifier for better query.
			this.$header = this.$el.find('.ps-js-header').eq(0);
			this.$title = this.$header.find('.ps-js-title');
			this.$body = this.$el.find('.ps-js-body').eq(0);
			this.$footer = this.$el.find('.ps-js-footer').eq(0);
			this.$close = this.$header.find('.ps-js-close');
			this.$el.appendTo(document.body);

			if (opts.closeButton) {
				this.$close.off('click').show();
				this.$close.on('click', e => {
					e.preventDefault();
					this.hide();
				});
			} else {
				this.$close.off('click').hide();
			}

			// Handle button mouseover effect.
			this.$footer
				.on('mouseenter', '[data-mouseover-icon]', this.onBtnMouseOver.bind(this))
				.on('mouseleave', '[data-mouseover-icon]', this.onBtnMouseOut.bind(this));
		}

		return this;
	}

	/**
	 * Show the dialog.
	 *
	 * @returns {Dialog}
	 */
	show() {
		$('html').addClass('ps-modal-is--open');

		this.render();
		this.$el.show();

		// Focus on the first input element, if enabled.
		if (this.opts.focusOnShow) {
			let $inputs = this.$body.find('input[type=text],input[type=password],select,textarea');
			$inputs.eq(0).focus();
		}

		// Close popup when Esc button pressed, if enabled.
		if (this.opts.closeOnEsc) {
			$(document).one('keyup.ps-modal', e => {
				if (27 === e.keyCode) {
					this.hide();
				}
			});
		}

		return this;
	}

	/**
	 * Hide the dialog.
	 *
	 * @returns {Dialog}
	 */
	hide() {
		if (this.$el) {
			this.$el.hide();
		}

		var $modals = $(document.body).children('.ps-js-modal');
		if (!$modals.filter(':visible').length) {
			$('html').removeClass('ps-modal-is--open');
		}

		// Execute onClose callback if available.
		if ('function' === typeof this.opts.onClose) {
			this.opts.onClose();
		}

		// Destroy popup element when the popup closed, if enabled.
		if (this.opts.destroyOnClose) {
			this.destroy();
		}

		return this;
	}

	/**
	 * Autohide the dialog.
	 *
	 * @returns {Dialog}
	 */
	autohide() {
		setTimeout(() => {
			if (this.$el && this.$el.is(':visible')) {
				this.$el.fadeOut(() => this.hide());
			}
		}, 2000);

		return this;
	}

	/**
	 * Destroy the dialog.
	 *
	 * @returns {Dialog}
	 */
	destroy() {
		if (this.$el) {
			this.$el.remove();

			delete this.$el;
			delete this.$header;
			delete this.$title;
			delete this.$body;
			delete this.$footer;
		}

		return this;
	}

	/**
	 * Show the dialog as an error popup.
	 *
	 * @returns {Dialog}
	 */
	error() {
		// Apply default value if the title is not set.
		this.opts.title = this.opts.title || TITLE_ERROR;

		return this.show();
	}

	/**
	 * Show the dialog as a confirmation popup.
	 *
	 * @param {Function} callback
	 * @returns {Dialog}
	 */
	confirm(callback) {
		// Apply default values if the title and actions are not set.
		this.opts.title = this.opts.title || TITLE_CONFIRM;
		this.opts.actions = this.opts.actions || [
			{ label: BUTTON_CANCEL, class: 'ps-js-cancel' },
			{ label: BUTTON_OK, class: 'ps-js-submit', primary: true }
		];

		if ('function' !== typeof callback) {
			callback = function () {};
		}

		this.render();

		this.$footer.on('click', '.ps-js-cancel', () => {
			this.hide();
			callback(false);
		});

		this.$footer.on('click', '.ps-js-submit', () => {
			this.hide();
			callback(true);
		});

		return this.show();
	}

	/**
	 * Dialog title getter and setter.
	 *
	 * @param {?string} text
	 * @returns {string|Dialog}
	 */
	title(text) {
		if ('undefined' === typeof text) {
			return this.opts.title;
		}

		this.opts.title = text;

		// Also update the element if it is already rendered.
		if (this.$title) {
			this.$title.html(text);
		}

		return this;
	}

	/**
	 * Handle mouseover transition on a button.
	 *
	 * @param {Event} e
	 */
	onBtnMouseOver(e) {
		let $button = $(e.currentTarget),
			$icon = $button.children('i'),
			icon = $button.data('mouseover-icon');

		if (icon) {
			$icon.data('icon', $icon.attr('class'));
			$icon.attr('class', icon);
		}
	}

	/**
	 * Handle mouseout transition on a button.
	 *
	 * @param {Event} e
	 */
	onBtnMouseOut(e) {
		let $button = $(e.currentTarget),
			$icon = $button.children('i'),
			icon = $icon.data('icon');

		if (icon) {
			$icon.attr('class', icon);
			$icon.removeData('icon');
		}
	}
}

export default function dialog(...args) {
	return new Dialog(...args);
}
