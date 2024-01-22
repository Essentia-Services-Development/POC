import $ from 'jquery';
import { observer } from 'peepso';

/**
 * Filter hook to get the Avatar class.
 */
observer.addFilter('class_avatar', () => Avatar);

/**
 * General Avatar class.
 */
class Avatar {
	/**
	 * Avatar class constructor.
	 *
	 * @param {Element} el
	 * @param {Object} opts
	 * @param {Object} opts.data
	 * @param {string} opts.uploadUrl
	 * @param {Object} opts.uploadParams
	 * @param {string} opts.confirmUrl
	 * @param {Object} opts.confirmParams
	 * @param {string} opts.deleteUrl
	 * @param {Object} opts.deleteParams
	 * @param {string} opts.cropUrl
	 * @param {Object} opts.cropParams
	 */
	constructor(el, opts = {}) {
		this.opts = opts;
		this.dialog = null;

		this.$el = $(el);
		this.$button = this.$el.find('.ps-js-focus-avatar-button');

		this.$button.on('click', e => this._onModify(e));
	}

	/**
	 * Initialize avatar dialog.
	 *
	 * @returns {AvatarDialog|false}
	 */
	getDialog() {
		if (null !== this.dialog) {
			return this.dialog;
		}

		let AvatarDialog = observer.applyFilters('class_avatar_dialog', false);
		if (false === AvatarDialog) {
			this.dialog = false;
			return this.dialog;
		}

		let onChange = imageSrc => this.updateAvatar(imageSrc);
		let opts = $.extend({}, this.opts, { onChange });
		this.dialog = new AvatarDialog(opts);

		return this.dialog;
	}

	/**
	 * Show the avatar dialog.
	 *
	 * @param {Event} e
	 */
	showDialog() {
		let dialog = this.getDialog();
		if (dialog) {
			dialog.show();
		}
	}

	/**
	 * Update avatar with a new image.
	 *
	 * @param {string} avatar
	 */
	updateAvatar(avatar) {}

	/**
	 * Handle click on the modify button.
	 *
	 * @private
	 * @param {Event} e
	 */
	_onModify(e) {
		e.preventDefault();

		this.showDialog();
	}
}
