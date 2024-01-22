import $ from 'jquery';
import { observer, template } from 'peepso';
import { avatar as avatarData, upload as uploadData } from 'peepsodata';

/**
 * Filter hook to get the AvatarDialog class.
 */
observer.addFilter('class_avatar_dialog', () => AvatarDialog);

/**
 * General avatar dialog class.
 */
class AvatarDialog {
	/**
	 * AvatarDialog class constructor.
	 *
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
	 * @param {Function} opts.onChange
	 */
	constructor(opts = {}) {
		this.opts = opts;
	}

	/**
	 * Initialize the avatar dialog.
	 */
	init() {
		if (this.$el) {
			return;
		}

		let html = template(avatarData.templateDialog2 || '')(this.opts.data);

		this.$el = $(html).hide();
		this.$file = this.$el.find('input[type=file]');
		this.$hasAvatar = this.$el.find('.ps-js-has-avatar');
		this.$noAvatar = this.$el.find('.ps-js-no-avatar');
		this.$preview = this.$el.find('.ps-js-preview');
		this.$avatar = this.$el.find('.ps-js-avatar');
		this.$loading = this.$el.find('.ps-js-loading');
		this.$error = this.$el.find('.ps-js-error');
		this.$btnRemove = this.$el.find('.ps-js-btn-remove');
		this.$btnCrop = this.$el.find('.ps-js-btn-crop');
		this.$btnCropCancel = this.$el.find('.ps-js-btn-crop-cancel');
		this.$btnCropConfirm = this.$el.find('.ps-js-btn-crop-save');
		this.$btnFinalize = this.$el.find('.ps-js-btn-finalize').attr('disabled', 'disabled');

		this.$el.on('click', '.ps-js-btn-upload', e => this._onUpload(e));
		this.$el.on('click', '.ps-js-btn-remove', e => this._onRemove(e));
		this.$el.on('click', '.ps-js-btn-crop', e => this._onCrop(e));
		this.$el.on('click', '.ps-js-btn-crop-cancel', e => this._onCropCancel(e));
		this.$el.on('click', '.ps-js-btn-crop-save', e => this._onCropConfirm(e));
		this.$el.on('click', '.ps-js-btn-finalize', e => this._onFinalize(e));
		this.$el.on('click', '.ps-js-btn-close', e => this._onClose(e));

		this.$el.appendTo(document.body);

		// Toggle UI based on the upload image status.
		let avatar = this.opts.data.avatar;
		let avatarSource = this.opts.data.avatarSource;
		if (avatar && avatarSource) {
			this._updateAvatar(avatar, avatarSource);
		} else {
			this._updateAvatar(false);
		}

		this._toggleError(false);
		this._toggleLoading(false);
		this._toggleFinalize(false);

		let url = this.opts.uploadUrl;
		let formData = $.extend({ _wpnonce: avatarData._wpnonce }, this.opts.uploadParams);

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

					this._toggleFinalize(false);

					if (!fileTypes.test(file.type)) {
						this._toggleError(avatarData.textErrorFileType);
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
						let avatar = json.data && json.data.image_url;
						let avatarSource = json.data && json.data.orig_image_url;
						this._updateAvatar(avatar, avatarSource);

						this._toggleFinalize(true);
					} else if (json.errors) {
						this._toggleError(json.errors);
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
			this._toggleError(false);
			this.$el.hide();
		}
	}

	/**
	 * Update shown avatar images. No second parameter means that the first parameter
	 * is a default avatar, and the dialog UI will be adjusted accordingly.
	 *
	 * @param {string} avatar
	 * @param {string} avatarSource
	 */
	_updateAvatar(avatar, avatarSource) {
		let cacheBust = '?_t=' + new Date().getTime();

		if (avatar) {
			if (avatarSource) {
				this.$preview.find('img').attr('src', avatarSource + cacheBust);
			}

			this.$avatar.find('img').attr('src', avatar + cacheBust);
			this.$btnRemove.show();
			this.$noAvatar.hide();
			this.$hasAvatar.show();
		} else {
			this.$preview.find('img').removeAttr('src');
			this.$avatar.find('img').attr('src', this.opts.data.avatarDefault + cacheBust);
			this.$btnRemove.hide();
			this.$hasAvatar.hide();
			this.$noAvatar.show();
		}
	}

	/**
	 * Get crop measurements.
	 *
	 * @returns {Object|false}
	 */
	_getCropCoord() {
		let coords = this._cropCoords;
		if (!coords) {
			return false;
		}

		let $img = this.$preview.find('img'),
			ratio = 1,
			maxWH = 800,
			resize = false,
			width,
			height,
			params;

		// Calculate ratio of resized image on this dialog relative to its actual dimension.
		if ($img[0].naturalWidth) {
			width = $img[0].naturalWidth || $img.width();
			height = $img[0].naturalHeight || $img.height();

			// Reduce large dimension images.
			if (width > maxWH || height > maxWH) {
				ratio = maxWH / Math.max(width, height);
				width = width * ratio;
				height = height * ratio;
				resize = true;
			}

			ratio = width / $img.width();
		}

		params = {
			x1: Math.floor(ratio * coords.x),
			y1: Math.floor(ratio * coords.y),
			x2: Math.floor(ratio * (coords.x + coords.width)),
			y2: Math.floor(ratio * (coords.y + coords.height))
		};

		if (resize) {
			params.width = width;
			params.height = height;
		}

		return params;
	}

	/**
	 * Toggle error message.
	 *
	 * @private
	 * @param {string} error
	 */
	_toggleError(error) {
		if (error instanceof Array) {
			error = error.join('<br>');
		}

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
			this.$loading.stop().show();
		} else {
			this.$loading.stop().fadeOut();
		}
	}

	/**
	 * Toggle the 'Done' button fo finalize action.
	 *
	 * @private
	 * @param {boolean} enable
	 */
	_toggleFinalize(enable) {
		if (enable) {
			this.$btnFinalize.removeAttr('disabled');
		} else {
			this.$btnFinalize.attr('disabled', 'disabled');
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
	 * Handle click on the crop button.
	 *
	 * @param {Event} e
	 */
	_onCrop(e) {
		e.preventDefault();
		e.stopPropagation();

		let $img = this.$preview.find('img');

		// Attach cropping layer.
		ps_crop.init({
			elem: $img,
			change: coords => {
				// Save crop coordinates for later use.
				this._cropCoords = coords;
			}
		});

		// Toggle cropping buttons.
		this.$btnCrop.hide();
		this.$btnCropCancel.show();
		this.$btnCropConfirm.show();

		// Disable finalize button on cropping mode.
		this._toggleFinalize(false);
	}

	/**
	 * Handle click on the cancel crop button.
	 *
	 * @param {Event} e
	 */
	_onCropCancel(e) {
		e.preventDefault();
		e.stopPropagation();

		let $img = this.$preview.find('img');

		// Detach cropping layer.
		ps_crop.detach($img);

		// Reset cropping buttons.
		this.$btnCrop.show();
		this.$btnCropCancel.hide();
		this.$btnCropConfirm.hide();

		// Reset finalize button on cropping mode.
		// this._toggleFinalize(true);
	}

	/**
	 * Handle click on the confirm crop button.
	 *
	 * @param {Event} e
	 */
	_onCropConfirm(e) {
		e.preventDefault();
		e.stopPropagation();

		let $img = this.$preview.find('img');

		// Detach cropping layer.
		ps_crop.detach($img);

		// Reset cropping buttons.
		this.$btnCrop.show();
		this.$btnCropCancel.hide();
		this.$btnCropConfirm.hide();

		let coords = this._getCropCoord();
		let cropData = {
			x: coords.x1,
			y: coords.y1,
			x2: coords.x2,
			y2: coords.y2,
			width: coords.width,
			height: coords.height,
			tmp: 1
		};

		let url = this.opts.cropUrl;
		let params = $.extend({ _wpnonce: avatarData._wpnonce }, cropData, this.opts.cropParams);

		this._toggleLoading(true);
		peepso.postJson(url, params, json => {
			this._toggleLoading(false);

			if (json.success) {
				let avatar = json.data.image_url;
				this._updateAvatar(avatar);
				this._toggleFinalize(true);
			}
		});
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
		let params = $.extend({ _wpnonce: avatarData._wpnonce }, this.opts.deleteParams);

		this._toggleLoading(true);
		peepso.postJson(url, params, json => {
			this._toggleLoading(false);

			if (json.success) {
				window.loading.reload();
			}
		});
	}

	/**
	 * Handle click on the finalize button.
	 *
	 * @private
	 * @param {Event} e
	 */
	_onFinalize(e) {
		e.preventDefault();
		e.stopPropagation();

		let url = this.opts.confirmUrl;
		let params = $.extend({ _wpnonce: avatarData._wpnonce }, this.opts.confirmParams);

		this._toggleLoading(true);
		peepso.postJson(url, params, json => {
			this._toggleLoading(false);

			if (json.success) {
				window.location.reload();
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
