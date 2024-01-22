import $ from 'jquery';
import { observer, template } from 'peepso';
import { cover as coverData, upload as uploadData } from 'peepsodata';

/**
 * Filter hook to get the CoverDialog class.
 */
observer.addFilter('class_cover_dialog', () => CoverDialog);

/**
 * General cover dialog class.
 */
class CoverDialog {
	/**
	 * CoverDialog class constructor.
	 *
	 * @param {Object} opts
	 * @param {Object} opts.data
	 * @param {string} opts.uploadUrl
	 * @param {Object} opts.uploadParams
	 * @param {string} opts.deleteUrl
	 * @param {Object} opts.deleteParams
	 * @param {Function} opts.onChange
	 */
	constructor(opts = {}) {
		this.opts = opts;
	}

	/**
	 * Initialize the cover dialog.
	 */
	init() {
		if (this.$el) {
			return;
		}

		let html = template(coverData.templateDialog || '')();

		this.$el = $(html).hide();
		this.$file = this.$el.find('input[type=file]');
		this.$content = this.$el.find('.ps-js-content');
		this.$loading = this.$el.find('.ps-js-loading');
		this.$error = this.$el.find('.ps-js-error');

		this.$el.on('click', '.ps-js-btn-upload', e => this._onUpload(e));
		this.$el.on('click', '.ps-js-btn-remove', e => this._onRemove(e));
		this.$el.on('click', '.ps-js-btn-close', e => this._onClose(e));

		this.$el.appendTo(document.body);

		// Toggle remove button based on the cover image availability.
		this._toggleRemove(this.opts.data && this.opts.data.cover);
		this._toggleError(false);
		this._toggleLoading(false);

		let url = this.opts.uploadUrl;
		let formData = $.extend({ _wpnonce: coverData._wpnonce }, this.opts.uploadParams);

		// Initialize uploader.
		if (this.$file.psFileupload) {
			this.$file.psFileupload({
				url: url,
				formData: formData,
				replaceFileInput: false,
				dropZone: null,
				dataType: 'json',
				add: (e, data) => {
					let file = data.files[0],
						fileTypes = /(\.|\/)(jpe?g|png|webp)$/i;

					if (!fileTypes.test(file.type)) {
						this._toggleError(coverData.textErrorFileType);
					} else if (parseInt(file.size) > uploadData.maxSize) {
						this._toggleError(uploadData.textErrorMaxSize);
					} else {
						this._toggleLoading(true);
						this._toggleError(false);
						data.submit();
					}
				},
				done: (e, data) => {
					this._toggleLoading(false);

					let json = data.result;
					if (json.success) {
						this._toggleRemove(true);
						this.hide();

						if ('function' === typeof this.opts.onChange) {
							let cover = json.data && json.data.image_url;
							if (cover) {
								this.opts.onChange(cover);
							}
						}
					}
				}
			});
		}
	}

	/**
	 * Show the dialog.
	 */
	show() {
		this.init();
		this.$el.show();
	}

	/**
	 * Hide the dialog.
	 */
	hide() {
		if (this.$el) {
			this.$el.hide();
		}
	}

	/**
	 * Toggle error message.
	 *
	 * @private
	 * @param {string} error
	 */
	_toggleError(error) {
		if (!error) {
			this.$error.hide();
			this.$error.html('');
		} else {
			this.$error.html(error);
			this.$error.show();
		}
	}

	/**
	 * Toggle loading state.
	 *
	 * @private
	 * @param {boolean} loading
	 */
	_toggleLoading(loading) {
		if (loading) {
			this.$content.hide();
			this.$loading.show();
		} else {
			this.$loading.hide();
			this.$content.show();
		}
	}

	/**
	 * Toggle remove button.
	 *
	 * @private
	 * @param {boolean} enable
	 */
	_toggleRemove(enable) {
		if (enable) {
			this.$content.addClass('ps-list-half');
			this.$content.find('.ps-js-li-remove').show();
		} else {
			this.$content.removeClass('ps-list-half');
			this.$content.find('.ps-js-li-remove').hide();
		}
	}

	/**
	 * Handle click on the upload button.
	 *
	 * @private
	 * @param {Event} e
	 */
	_onUpload(e) {
		e.preventDefault();
		e.stopPropagation();

		this.$file.click();
	}

	/**
	 * Handle click on the remove button.
	 *
	 * @private
	 * @param {Event} e
	 */
	_onRemove(e) {
		e.preventDefault();
		e.stopPropagation();

		let url = this.opts.deleteUrl;
		let params = $.extend({ _wpnonce: coverData._wpnonce }, this.opts.deleteParams);

		this._toggleLoading(true);
		peepso.postJson(url, params, json => {
			this._toggleLoading(false);

			if (json.success) {
				this._toggleRemove(false);
				this.hide();

				if ('function' === typeof this.opts.onChange) {
					this.opts.onChange(false);
				}
			}
		});
	}

	/**
	 * Handle click on the close button.
	 *
	 * @private
	 * @param {Event} e
	 */
	_onClose(e) {
		e.preventDefault();
		e.stopPropagation();

		this.hide();
	}
}
