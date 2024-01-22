import $ from 'jquery';
import { ajax, isWebdriver, observer, template } from 'peepso';
import {
	currentuserid as USER_ID,
	rest_url as REST_URL,
	rest_nonce as REST_NONCE
} from 'peepsodata';

class Dropzone {
	constructor(el, opts = {}) {
		this.$el = $(el);
		this.opts = opts;

		this.$previews = this.$el.find('.ps-js-previews');
		this.$fileItems = this.$previews.find('.ps-js-items');
		this.$previews.on('click', '.ps-js-item .ps-js-remove', e => this._removePreview(e));

		this.$add = this.$previews.find('.ps-js-btn-add');
		this.$add.on('click', () => this.upload());

		let fileTypes = '';
		if (opts.uploadFileTypes instanceof Array && opts.uploadFileTypes.length) {
			fileTypes = `accept=".${opts.uploadFileTypes.join(',.')}"`;
		}
		this.$file = $(`<input type="file" name="filedata[]" ${fileTypes} multiple />`);
		this.$file.hide().appendTo(el);

		this.files = [];

		let previewTemplate = this.$el.find('script[data-name="preview-item"]').text().trim();
		this.previewTemplate = template(previewTemplate);

		// auto-initialize file upload if run on webdriver
		if (true || isWebdriver()) {
			this._uploadInit();
			this._uploadInitialized = true;
		}
	}

	/**
	 * Perform file upload.
	 */
	upload() {
		if (!this._uploadInitialized) {
			this._uploadInit();
			this._uploadInitialized = true;
		}

		this.$file.trigger('click');
	}

	/**
	 * Check if all files are already uploaded completely.
	 */
	uploadComplete() {
		// TODO: fix upload complete state monitor.
		return true;
	}

	/**
	 * Reset dropzone content.
	 */
	reset() {
		this.files = [];
		this.$el.find('.ps-js-item').remove();
	}

	/**
	 * Get IDs of uploaded photos.
	 *
	 * @return {Array}
	 */
	getFiles() {
		let files = this.files
			.filter(file => !!file.name)
			.map(function (file) {
				return { name: file.name, size: file.size };
			});

		return files;
	}

	/**
	 * Initialize file upload functionality.
	 *
	 * @private
	 */
	_uploadInit() {
		this.$file.psFileupload({
			singleFileUploads: true,
			sequentialUploads: false,
			// This option needs to be explicitly set as TRUE to fix bug on Safari browser
			// which caches previously selected files.
			replaceFileInput: true,
			dropZone: this.$el,
			pasteZone: null,
			dataType: 'json',
			url: this.opts.uploadUrl,
			beforeSend: xhr => {
				if (0 === this.opts.uploadUrl.indexOf(REST_URL)) {
					xhr.setRequestHeader('X-WP-Nonce', REST_NONCE);
				}
			},
			add: (e, data) => {
				this.$file = this.$el.find('input[type=file]');
				this._uploadSingle(data);
			},
			submit: (e, data) => {
				let req = { user_id: USER_ID };
				data.formData = observer.applyFilters('files_upload_req', req, this);
			},
			progress: (e, data) => {
				this._updatePreview(data);
			},
			done: (e, data) => {
				data.loaded = data.total;
				this._updatePreview(data);

				let uploadId = data._uploadId,
					json = data.result;

				if (json.filename) {
					let index = this.files.findIndex(file => file.id === uploadId);
					if (index >= 0) {
						this.files[index].name = json.filename;
						this.emit('uploaded');
					}
				}
			}
		});
	}

	/**
	 * Upload single file.
	 *
	 * @param {Object} data
	 */
	_uploadSingle(data) {
		// Validation check.
		let file = data.files[0];
		let warning = observer.applyFilters('file_upload_warning', null, this.files.concat([file]));
		if (warning) {
			this._addPreview(file, data._uploadId, warning);
			this.emit('added');
			return false;
		}

		// Attach upload ID to the data object.
		data._uploadId = new Date().getTime() + Math.floor(Math.random() * 1000);

		this._addFile(data);
		data.submit();
	}

	/**
	 * Add file to the uploaded file listing.
	 *
	 * @param {Object} data
	 */
	_addFile(data) {
		this.files.push({ id: data._uploadId, name: '', size: data.files[0].size });
		this._addPreview(data.files[0], data._uploadId);

		this.emit('added');
	}

	/**
	 * Remove file from the uploaded file listing.
	 *
	 * @param {number} id
	 */
	_removeFile(id) {
		for (let i = this.files.length - 1; i >= 0; i--) {
			if (this.files[i].id === id) {
				let deletedFile = this.files.splice(i, 1);
				let filename = deletedFile[0].name;

				ajax.delete(this.opts.uploadUrl, { filename }, -1)
					.done(() => {
						/*TODO*/
					})
					.fail(() => {
						/*TODO*/
					});
			}
		}

		if (!this.files.length) {
			// Remove invalid file items.
			this.$fileItems.find('.ps-js-item').remove();

			this.emit('empty');
		}
	}

	/**
	 * Add preview to the uploaded file listing.
	 *
	 * @param {Object} file
	 */
	_addPreview(file, id, error = null) {
		let previewData = { id, name: file.name, error };
		let previewHtml = this.previewTemplate(previewData);
		let $preview = $(previewHtml).attr('data-preview-id', id);

		$preview.appendTo(this.$fileItems);
	}

	/**
	 * Remove preview (and associated file) from the uploaded file listing.
	 *
	 * @param {Event} e
	 */
	_removePreview(e) {
		e.preventDefault();
		e.stopPropagation();

		let $preview = $(e.target).closest('.ps-js-item');

		this._removeFile($preview.data('id'));
		$preview.remove();
	}

	/**
	 * Update preview state.
	 *
	 * @param {Object} data
	 */
	_updatePreview(data) {
		let progress = (data.loaded * 100) / data.total;
		progress = Math.max(0, Math.min(100, progress));
		progress = progress + '%';

		let $preview = this.$fileItems.find(`[data-preview-id="${data._uploadId}"]`);
		let $progressbar = $preview.find('.ps-js-progress');
		$progressbar.stop().animate({ width: progress }, 2000, function () {
			if ('100%' === progress) {
				let $item = $(this).closest('.ps-js-item');
				$item.addClass('uploaded');
			}
		});
	}

	/**
	 * Simple event hooks management for dropdown state.
	 *
	 * @param {string} evt
	 * @param {Function} callback
	 */
	on(evt, callback) {
		this._events = this._events || {};
		this._events[evt] = this._events[evt] || [];
		this._events[evt].push(callback);
	}
	emit(evt, data) {
		if (this._events && this._events[evt] instanceof Array) {
			for (let i = 0; i < this._events[evt].length; i++) {
				const callback = this._events[evt][i];
				callback(data);
			}
		}
	}
}

export default Dropzone;
