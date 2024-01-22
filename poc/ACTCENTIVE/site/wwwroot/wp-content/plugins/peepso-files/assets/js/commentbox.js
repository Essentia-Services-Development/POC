import $ from 'jquery';
import { dialog, observer } from 'peepso';
import {
	currentuserid as USER_ID,
	rest_url as REST_URL,
	rest_nonce as REST_NONCE,
	file as fileData
} from 'peepsodata';

class CommentboxFile {
	/**
	 * @param {Element|JQuery} btn
	 */
	constructor(btn) {
		this.$btn = $(btn);

		this.$addon = this.$btn
			.closest('.ps-js-actions')
			.siblings('.ps-js-addons')
			.find('.ps-js-addon-files');
		this.$filename = this.$addon.find('.ps-js-filename');
		this.$loading = this.$addon.find('.ps-js-loading');
		this.$remove = this.$addon.find('.ps-js-remove').on('click', () => this._removeFile());

		let fileTypes = '';
		if (fileData.uploadFileTypes instanceof Array && fileData.uploadFileTypes.length) {
			fileTypes = `accept=".${fileData.uploadFileTypes.join(',.')}"`;
		}
		this.$file = $(`<input type="file" name="filedata[]" ${fileTypes} />`);
		this.$file.hide().appendTo(this.$addon);

		this.file = null;

		this._uploadInit();

		observer.addFilter('comment_req', (data, el) => this.commentAddData(data, el), 10, 2);

		$(document).on('ps_comment_aftersave', (e, act_id, el) => this.commentAfterSave(el));
	}

	upload() {
		this.$file.trigger('click');
	}

	commentAddData(data, el) {
		let $ct = $(el).closest('.ps-js-comment-new,.ps-comment-edit'),
			$addon = $ct.find('.ps-js-addon-files');

		if ($addon.is(this.$addon) && this.file && this.file.name) {
			data.file = this.file.name;
		}

		return data;
	}

	commentAfterSave(el) {
		let $ct = $(el).closest('.ps-js-comment-new,.ps-comment-edit'),
			$addon = $ct.find('.ps-js-addon-files');

		if ($addon.is(this.$addon)) {
			observer.doAction('file_upload_added', this.file);
			this._removeFile();
		}
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
			dropZone: null,
			pasteZone: null,
			dataType: 'json',
			url: `${REST_URL}${fileData.uploadUrl}`,
			beforeSend: xhr => {
				xhr.setRequestHeader('X-WP-Nonce', REST_NONCE);
			},
			add: (e, data) => {
				this.$file = this.$addon.find('input[type=file]');

				// Validation check.
				let file = data.files[0];
				let warning = observer.applyFilters('file_upload_warning', null, file);
				if (warning) {
					dialog(warning).error();
					return false;
				}

				// Attach upload ID to the data object.
				data._uploadId = new Date().getTime() + Math.floor(Math.random() * 1000);

				this._addFile(data);
				data.submit();
			},
			submit: (e, data) => {
				let req = { user_id: USER_ID };
				data.formData = observer.applyFilters('files_upload_req', req, this);
			},
			done: (e, data) => {
				let json = data.result;

				if (json.filename) {
					this.$loading.hide();
					this.$filename.html(json.filename);
					this.$remove.show();
					this.file.name = json.filename;
				}
			}
		});
	}

	/**
	 * Show the uploaded file.
	 *
	 * @param {Object} data
	 */
	_addFile(data) {
		this.$filename.html('&nbsp;');
		this.$loading.show();
		this.$remove.hide();
		this.$addon.show();

		this.file = { id: data._uploadId, name: '', size: data.files[0].size };

		$(document).trigger('ps_comment_addon_added', this.$addon);
	}

	/**
	 * Remove file from the uploaded file listing.
	 *
	 * @param {number} id
	 */
	_removeFile() {
		this.$filename.empty();
		this.$loading.hide();
		this.$remove.hide();
		this.$addon.hide();

		this.file = null;

		$(document).trigger('ps_comment_addon_removed', this.$addon);
	}
}

$(document).on('click', '.ps-js-comment-files', function (e) {
	let $btn = $(e.currentTarget);
	let instance = $btn.data('instance');

	if (!instance) {
		instance = new CommentboxFile($btn);
		$btn.data('instance', instance);
	}

	instance.upload();
});

$(document).on('ps_comment_aftersave', function (e, act_id, el) {
	var $ct = $(el).closest('.ps-js-comment-new, .ps-js-comment-edit'),
		$photo = $ct.find('.ps-js-addon-photo');

	$photo.find('.ps-js-remove').hide(); // hide 'remove' button
	$photo.find('.ps-js-loading').hide(); // hide loading
	$photo.find('.ps-js-img').attr('src', '').removeData('id').hide();
	$photo.hide();
	$(document).trigger('ps_comment_addon_removed', $photo);
});

/**
 * Should show post button if file available
 */
observer.addFilter('comment_show_button', obj => {
	let $ct = $(obj.el).closest('.ps-js-comment-new, .ps-js-comment-edit');
	let $files = $ct.find('.ps-js-addon-files');

	if (!obj.show && $files.is(':visible')) {
		let $filename = $files.find('.ps-js-filename');
		if ($filename.is(':visible')) {
			obj.show = true;
		}
	}

	return obj;
});
