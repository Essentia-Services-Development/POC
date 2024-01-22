import { dialog } from 'peepso';

const TEMPLATE =
	window.psdata_photos_albumuploaddialog && window.psdata_photos_albumuploaddialog.template;

(function ($, peepso, factory) {
	peepso.PhotoAlbumUploadDialog = factory($, peepso);
})(jQuery, peepso, function ($, peepso) {
	/**
	 * Create photo album dialog.
	 * @class PhotoAlbumUploadDialog
	 */
	function PhotoAlbumUploadDialog(user_id, album_id) {
		this.create(user_id, album_id);
		this.popup.show();
	}

	peepso.npm.objectAssign(
		PhotoAlbumUploadDialog.prototype,
		peepso.npm.EventEmitter.prototype,
		/** @lends PhotoAlbumUploadDialog.prototype */ {
			/**
			 * Photo album dialog template.
			 * @type {string}
			 */
			template: psdata_photos_albumuploaddialog.template,

			/**
			 * Initialize album dialog.
			 */
			create: function (user_id, album_id) {
				var className = 'ps-js-dialog-create-album',
					id = _.uniqueId(className + '-');

				// save user id
				this.user_id = user_id || peepsodata.userid;
				this.album_id = album_id;

				this.popup = dialog(TEMPLATE, { wide: true }).show();

				this.$el = this.popup.$el;
				this.$el.addClass(className).attr('id', id);
				this.$submit = this.$el.find('.ps-js-submit');
				this.$loading = this.$el.find('.ps-js-loading');
				this.$container = this.$el.find('.ps-js-photos-container');
				this.$upload = this.$el.find('.ps-js-photos-upload');

				this.$el.on('click', '.ps-js-photos-upload-button', $.proxy(this.onUpload, this));
				this.$el.on('click', '.ps-js-cancel', $.proxy(this.onClose, this));
				this.$el.on('click', '.ps-js-submit', $.proxy(this.onSubmit, this));
				this.$el.appendTo(document.body);

				// initialize photo upload
				this.dropzone = new peepso.PhotoDropzone(this.$el.find('.ps-js-photos-container'));
				this.dropzone.on('photo_added', $.proxy(this.onPhotoAdded, this));
				this.dropzone.on('photo_upload_start', $.proxy(this.onPhotoUploadStart, this));
				this.dropzone.on('photo_upload_done', $.proxy(this.onPhotoUploadDone, this));
				this.dropzone.on('photo_empty', $.proxy(this.onPhotoEmpty, this));
			},

			onUpload: function () {
				this.dropzone.triggerUpload();
			},

			onPhotoAdded: function () {
				this.$upload.hide();
				this.$container.show();
			},

			onPhotoUploadStart: function () {
				this.$submit.attr('disabled', 'disabled');
			},

			onPhotoUploadDone: function (remaining) {
				if (remaining <= 0) {
					this.$submit.removeAttr('disabled');
				}
			},

			onPhotoEmpty: function () {
				this.$container.hide();
				this.$upload.show();
			},

			onClose: function (e) {
				e.preventDefault();
				e.stopPropagation();
				this.dropzone.destroy();
				this.$el.remove();
			},

			onSubmit: function () {
				var photo = this.dropzone.getPhotos(),
					error = false;

				// check photo
				if (photo.length >= 1) {
					this.$el.find('.ps-js-error-photo').hide();
				} else {
					this.$el.find('.ps-js-error-photo').show();
					error = true;
				}

				if (error) {
					return;
				}

				var req = {
					user_id: this.user_id,
					album_id: this.album_id,
					type: 'photo',
					photo: photo,
					_wpnonce: this.$el.find('[name=_wpnonce]').val(),
					_wp_http_referer: this.$el.find('[name=_wp_http_referer]').val()
				};

				// send req through filter
				req = peepso.observer.applyFilters('photos_add_photos_to_album_req', req);

				this.$submit.attr('disabled', 'disabled');
				this.$loading.show();

				peepso.postJson(
					'photosajax.add_photos_to_album',
					req,
					$.proxy(function (json) {
						this.$loading.hide();
						this.$submit.removeAttr('disabled');

						if (json.success) {
							this.$el.remove();
							window.location.reload();
						}
					}, this)
				);
			}
		}
	);

	return PhotoAlbumUploadDialog;
});
