import { dialog } from 'peepso';

const TEMPLATE = window.psdata_photos_albumdialog && window.psdata_photos_albumdialog.template;

(function ($, peepso, factory) {
	peepso.PhotoAlbumDialog = factory($, peepso);
})(jQuery, peepso, function ($, peepso) {
	/**
	 * Create photo album dialog.
	 * @class PhotoAlbumDialog
	 */
	function PhotoAlbumDialog(user_id) {
		this.create(user_id);
		this.popup.show();
	}

	peepso.npm.objectAssign(
		PhotoAlbumDialog.prototype,
		peepso.npm.EventEmitter.prototype,
		/** @lends PhotoAlbumDialog.prototype */ {
			/**
			 * Photo album dialog template.
			 * @type {string}
			 */
			template: psdata_photos_albumdialog.template,

			/**
			 * Photo album URL
			 * @type {string}
			 */
			album_url: psdata_photos_albumdialog.album_url,

			/**
			 * Initialize album dialog.
			 */
			create: function (user_id) {
				var className = 'ps-js-dialog-create-album',
					id = _.uniqueId(className + '-');

				// save user id
				this.user_id = user_id || peepsodata.userid;

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

				// run extra functionalities
				peepso.observer.applyFilters('photo_create_album', { popup: this.$el });
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
				var name = $.trim(this.$el.find('[name=album_name]').val()),
					desc = $.trim(this.$el.find('[name=album_desc]').val()),
					photo = this.dropzone.getPhotos(),
					error = false;

				// check name
				if (name.length) {
					this.$el.find('.ps-js-error-name').hide();
				} else {
					this.$el.find('.ps-js-error-name').show();
					error = true;
				}

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
					name: name,
					description: desc,
					privacy: this.$el.find('[name=album_privacy]').val(),
					type: 'album',
					photo: photo,
					_wpnonce: this.$el.find('[name=_wpnonce]').val(),
					_wp_http_referer: this.$el.find('[name=_wp_http_referer]').val()
				};

				// send req through filter
				req = peepso.observer.applyFilters('photos_create_album_req', req);

				var $location = this.$el.find('[name=album_location]'),
					locname = $location.data('location'),
					loclat = $location.data('latitude'),
					loclng = $location.data('longitude');

				if (locname && loclat && loclng) {
					req['location[name]'] = locname;
					req['location[latitude]'] = loclat;
					req['location[longitude]'] = loclng;
				}

				this.$submit.attr('disabled', 'disabled');
				this.$loading.show();

				peepso.postJson(
					'photosajax.create_album',
					req,
					$.proxy(function (json) {
						this.$loading.hide();
						this.$submit.removeAttr('disabled');

						if (json.success) {
							this.$el.remove();
							var path = this.album_url + '/' + json.data.album_id;
							window.location.href = path;
						}
					}, this)
				);
			}
		}
	);

	return PhotoAlbumDialog;
});
