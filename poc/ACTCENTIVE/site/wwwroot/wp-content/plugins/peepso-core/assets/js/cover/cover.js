import $ from 'jquery';
import { observer } from 'peepso';
import { cover as coverData } from 'peepsodata';

/**
 * Filter hook to get the Cover class.
 */
observer.addFilter('class_cover', () => Cover);

/**
 * General cover class.
 */
class Cover {
	/**
	 * Cover class constructor.
	 *
	 * @param {Element} el
	 * @param {Object} opts
	 * @param {Object} opts.data
	 * @param {string} opts.uploadUrl
	 * @param {Object} opts.uploadParams
	 * @param {string} opts.deleteUrl
	 * @param {Object} opts.deleteParams
	 * @param {string} opts.repositionUrl
	 * @param {Object} opts.repositionParams
	 */
	constructor(el, opts = {}) {
		this.opts = opts;
		this.dialog = null;

		this.$el = $(el);
		this.$image = this.$el.find('.ps-js-cover-image');
		this.$btnModify = this.$el.find('.ps-js-cover-modify');
		this.$btnReposition = this.$el.find('.ps-js-cover-reposition');
		this.$btnRepositionCancel = this.$el.find('.ps-js-cover-reposition-cancel');
		this.$btnRepositionSave = this.$el.find('.ps-js-cover-reposition-save');

		this.$btnModify.on('click', e => this._onModify(e));
		this.$btnReposition.on('click', e => this._onReposition(e));
		this.$btnRepositionCancel.on('click', e => this._onRepositionCancel(e));
		this.$btnRepositionSave.on('click', e => this._onRepositionSave(e));

		this.imageStyle = this.$image.attr('style');
		this.imagePositionX = this.$image.css('left');
		this.imagePositionY = this.$image.css('top');
	}

	/**
	 * Initialize cover dialog.
	 *
	 * @returns {CoverDialog|false}
	 */
	getDialog() {
		if (null !== this.dialog) {
			return this.dialog;
		}

		let CoverDialog = observer.applyFilters('class_cover_dialog', false);
		if (false === CoverDialog) {
			this.dialog = false;
			return this.dialog;
		}

		let onChange = imageSrc => this.updateCover(imageSrc);
		let opts = $.extend({}, this.opts, { onChange });
		this.dialog = new CoverDialog(opts);

		return this.dialog;
	}

	/**
	 * Show the cover dialog.
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
	 * Update cover with a new image.
	 *
	 * @param {string} cover
	 */
	updateCover(cover) {
		if (cover) {
			this.$image.css({ top: 0, left: 0 });
			this.$image.attr('src', cover);
			this._toggleReposition(true);
		} else {
			window.location.reload();
		}
	}

	/**
	 * Toggle reposition button.
	 *
	 * @private
	 * @param {boolean} enable
	 */
	_toggleReposition(enable) {
		if (enable) {
			this.$btnReposition.show();
		} else {
			this.$btnReposition.hide();
		}
	}

	/**
	 * Toggle repositioning state.
	 *
	 * @param {boolean} enable
	 */
	_toggleRepositioning(enable) {
		if (enable) {
			this.$el.find('.js-focus-gradient').hide();
			this.$el.find('.js-focus-change-cover > a').hide();
			this.$el.find('.reposition-cover-actions').show();
			this.$el.addClass('ps-focus-cover-edit');
		} else {
			this.$el.find('.js-focus-gradient').show();
			this.$el.find('.js-focus-change-cover > a').show();
			this.$el.find('.reposition-cover-actions').hide();
			this.$el.removeClass('ps-focus-cover-edit');
		}
	}

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

	/**
	 * Handle click on the reposition button.
	 *
	 * @private
	 * @param {Event} e
	 */
	_onReposition(e) {
		e.preventDefault();

		this._toggleRepositioning(true);

		let height = this.$el.height() - this.$image.height();
		let width = this.$el.width() - this.$image.width();

		this.$image.draggable({
			cursor: 'move',
			drag: function(event, ui) {
				// Impose boundary in the draggable area.
				ui.position.left = Math.max(width, Math.min(0, ui.position.left));
				ui.position.top = Math.max(height, Math.min(0, ui.position.top));
			},
			stop: (event, ui) => {
				// Calculate position in percentage.
				let posX = (100 * ui.position.left) / this.$el.width(),
					posY = (100 * ui.position.top) / this.$el.height();

				// Round the value.
				posX = Math.round(10000 * posX) / 10000;
				posY = Math.round(10000 * posY) / 10000;

				// Save and apply new value to the image.
				this.imagePositionX = posX;
				this.imagePositionY = posY;
				this.$image.css({ left: `${posX}%`, top: `${posY}%` });
			}
		});
	}

	/**
	 * Handle click on the cancel reposition button.
	 *
	 * @private
	 * @param {Event} e
	 */
	_onRepositionCancel(e) {
		e.preventDefault();
		e.stopPropagation();

		this._toggleRepositioning(false);
		this.$image.attr('style', this.imageStyle);
		this.$image.draggable('destroy');
	}

	/**
	 * Handle click on the save reposition button.
	 *
	 * @private
	 * @param {Event} e
	 */
	_onRepositionSave(e) {
		e.preventDefault();
		e.stopPropagation();

		let url = this.opts.repositionUrl;
		let params = {
			_wpnonce: coverData._wpnonce,
			// NOTE: Values are swapped in the backend but we have to follow it to make it consistent with similar endpoints.
			x: this.imagePositionY,
			y: this.imagePositionX
		};

		this._toggleRepositioning(false);
		this.$el.find('.ps-reposition-loading').show();

		params = $.extend(params, this.opts.repositionParams);
		peepso.postJson(url, params, json => {
			this.$el.find('.ps-reposition-loading').hide();
			this.imageStyle = this.$image.attr('style');
			this.$image.draggable('destroy');
		});
	}
}
