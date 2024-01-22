(function ($, peepso, factory) {
	peepso.PhotoDropzone = factory($, peepso);
})(jQuery, peepso, function ($, peepso) {
	/**
	 * Create photo dropzone element.
	 * @class PhotoDropzone
	 */
	function PhotoDropzone(container) {
		this.create(container);
	}

	peepso.npm.objectAssign(
		PhotoDropzone.prototype,
		peepso.npm.EventEmitter.prototype,
		/** @lends PhotoDropzone.prototype */ {
			/**
			 * Photo dropzone template.
			 * @type {string}
			 */
			template: psdata_photos_dropzone.template,

			/**
			 * Photo preview template.
			 * @type {string}
			 */
			templatePreview: psdata_photos_dropzone.template_preview,

			/**
			 * Initialize photo dropzone.
			 */
			create: function (container) {
				this.uploading = 0;
				this.$el = $(this.template).appendTo(container);
				this.$photos = this.$el.find('.ps-js-photos');
				this.$file = this.$el.find('input[type=file]');
				this.$upload = this.$el.find('.ps-js-upload');
				this.$upload.on('click', $.proxy(this.onUpload, this));

				// auto-initialize file upload if run on webdriver
				if (peepso.isWebdriver()) {
					this.uploadInit();
					this.uploadInitialized = true;
				}
			},

			/**
			 * Reset and empty dropzone content.
			 * @param {string} [action]
			 */
			empty: function (action) {
				if (action !== 'save') {
					this.removeTempFiles(this.getPhotos());
				}
				this.$photos.find('.ps-js-preview').remove();
				this.emit('photo_empty');
			},

			/**
			 * Reset and empty dropzone content.
			 * @param {string} [action]
			 */
			movePhotos: function (oldGroupId, groupId) {
				photos = this.getPhotos();
				if (_.isArray(photos) && photos.length >= 1) {
					var params = {
						user_id: peepsodata.currentuserid,
						old_group_id: oldGroupId,
						group_id: groupId,
						photo: photos,
						_wpnonce: this.$el.find('[name=_wpnonce_remove_temp_files]').val(),
						_wp_http_referer: this.$el.find('[name=_wp_http_referer]').val()
					};

					// send req through filter
					params = peepso.observer.applyFilters('photos_move_temp_files', params);

					peepso.postJson('photosajax.move_temp_files', params);
				}
			},

			/**
			 * Get IDs of uploaded photos.
			 */
			getPhotos: function () {
				var ids = [];
				this.$photos.find('.ps-js-preview').each(function () {
					var id = $(this).data('id');
					ids.push(id);
				});
				return ids;
			},

			/**
			 * TODO: docblock
			 */
			upload: function () {
				if (!this.uploadInitialized) {
					this.uploadInit();
					this.uploadInitialized = true;
				}
				this.$file.trigger('click');
			},

			/**
			 * Initialize photo upload.
			 */
			uploadInit: function () {
				var USER_ID = peepsodata.currentuserid;

				this.$file.psFileupload({
					singleFileUploads: true,
					sequentialUploads: false,
					// This option needs to be explicitly set as TRUE to fix bug on Safari browser
					// which caches previously selected files.
					replaceFileInput: true,
					dropZone: this.$el,
					pasteZone: null,
					dataType: 'json',
					url: peepsodata.ajaxurl_legacy + 'photosajax.upload_photo',
					add: $.proxy(this.uploadAdd, this),
					submit: $.proxy(function (e, data) {
						var req = { user_id: USER_ID };
						data.formData = peepso.observer.applyFilters(
							'photos_upload_req',
							req,
							this
						);
					}, this),
					progress: $.proxy(this.uploadProgress, this),
					done: $.proxy(this.uploadDone, this)
				});

				this.on('photo_added', this.uploadAdded);
				this.on('photo_removed', this.uploadRemoved);
			},

			/**
			 * TODO: docblock
			 */
			uploadAdd: function (e, data) {
				// Update reference to this variable, as the file uploader library
				// always replace file input after every upload.
				this.$file = this.$el.find('input[type=file]');
				this.validate(data);
			},

			/**
			 * TODO: docblock
			 */
			uploadAdded: function (data, id) {
				var $pview = $(this.templatePreview);
				$pview.find('.ps-js-progress').css({ width: 1 });
				$pview.on('click', '.ps-js-rotate-l', { id, dir: 'ccw' }, this.onRotate.bind(this));
				$pview.on('click', '.ps-js-rotate-r', { id, dir: 'cw' }, this.onRotate.bind(this));
				$pview.on('click', '.ps-js-remove', { id }, $.proxy(this.onRemove, this));
				$pview.addClass('ps-js-preview-' + id);

				// Remove rotate feature on GIF image.
				var isGIF = data.files[0].name.match(/\.gif$/i);
				if (isGIF) {
					$pview.find('.ps-js-rotate-l, .ps-js-rotate-r').remove();
				}

				this.$upload.before($pview);
				data.preview = $pview;
				this.resetSortable();
			},

			/**
			 * TODO: docblock
			 */
			uploadRemoved: function (id) {
				var $preview = this.$photos.find('.ps-js-preview-' + id);
				$preview.fadeOut(
					500,
					$.proxy(function () {
						$preview.remove();
						if (this.$photos.find('.ps-js-preview').length) {
							this.resetSortable();
						} else {
							this.emit('photo_empty');
						}
					}, this)
				);
			},

			/**
			 * TODO: docblock
			 */
			uploadProgress: function (e, data) {
				var $preview = data.preview,
					progress;

				progress = (data.loaded * 100) / data.total;
				progress = Math.max(0, Math.min(98, progress));
				progress = progress + '%';

				$preview.find('.ps-js-progress').stop().animate({ width: progress }, 2000);

				// Notice users upon leaving the page when photo is uploading.
				if (!this.beforeUnloadHandler) {
					this.beforeUnloadHandler = function () {
						return true;
					};
					peepso.observer.addFilter('beforeunload', this.beforeUnloadHandler);
				}
			},

			/**
			 * TODO: docblock
			 */
			uploadDone: function (e, data) {
				var $preview = data.preview,
					response = data.result,
					errorMessage,
					$error;

				if (this.beforeUnloadHandler) {
					peepso.observer.removeFilter('beforeunload', this.beforeUnloadHandler);
					this.beforeUnloadHandler = null;
				}

				if (response.success) {
					$preview.data('id', response.data.files[0]);
					$preview
						.find('.ps-js-img')
						.html('<img src="' + response.data.thumbs[0] + '" />');
					$preview.find('.ps-js-rotate-l, .ps-js-rotate-r').show();
					$preview.find('.ps-js-remove').show();
					$preview
						.find('.ps-js-progress')
						.stop()
						.animate({ width: '100%' }, 1000, function () {
							setTimeout(function () {
								$preview.find('.ps-js-progressbar').fadeOut();
							}, 1000);
						});
				} else if (response.errors && response.errors.length) {
					errorMessage = response.errors.join('<br/>');
					$error = $('<div/>').append(errorMessage).attr('title', errorMessage);

					$error.css({
						color: 'red',
						fontSize: '.8em',
						height: '100%',
						overflow: 'hidden',
						padding: 4
					});

					$preview.find('.ps-js-progress').stop().hide();
					$preview.find('.ps-js-rotate-l, .ps-js-rotate-r').hide();
					$preview.find('.ps-js-remove').show();
					$preview.find('.ps-js-img').html($error);
				}

				this.emit('photo_upload_done', --this.uploading);
			},

			/**
			 * TODO: docblock
			 */
			validate: function (data) {
				// add placeholder
				var previewId = new Date().getTime() + Math.floor(Math.random() * 1000);
				this.emit('photo_added', data, previewId);
				this.emit('photo_upload_start', ++this.uploading);
				// add file data into queue
				this.validateQueue || (this.validateQueue = []);
				this.validateQueue.push([data, previewId]);
				this._validate();
			},

			/**
			 * TODO: docblock
			 */
			_validate: _.throttle(function () {
				if (this._validateProgress) {
					return;
				}

				var queue = this.validateQueue.shift();
				if (!queue) {
					return;
				}

				// set validation request flag
				this._validateProgress = true;

				var data = queue[0],
					previewId = queue[1],
					file = data.files[0];

				// skip validation on unsupported file format
				if (!/\.(gif|jpg|jpeg|png|tif|tiff|webp)$/i.test(file.name)) {
					this.emit('photo_upload_done', --this.uploading);
					this.validateError(previewId, peepsophotosdata.error_unsupported_format);
					this._validateProgress = false;
					this._validate();
					return;
				}

				var photos = _.filter(this.getPhotos(), function (id) {
					return id;
				});

				// send req through filter
				var req = peepso.observer.applyFilters(
					'photos_validate_req',
					{
						size: parseInt(file.size),
						filesize: parseInt(file.size),
						photos: photos.length + 1
					},
					this
				);

				$.ajax({
					type: 'POST',
					url: peepsodata.ajaxurl_legacy + 'photosajax.validate_photo_upload',
					data: req,
					dataType: 'json'
				})
					.done(
						$.proxy(function (response) {
							// trying to upload on validation success
							if (response.success) {
								data.submit()
									.done(
										$.proxy(function () {
											// chain to next validation on upload success
											setTimeout(
												$.proxy(function () {
													this._validateProgress = false;
													this._validate();
												}, this),
												1000 /* adjust delay between requests here */
											);
										}, this)
									)

									// show error message on upload failed
									.fail(
										$.proxy(function (xhr) {
											this.emit('photo_upload_done', --this.uploading);
											this.validateFail(previewId);
											this._validateProgress = false;
											this._validate();
										}, this)
									);

								// show error message on error response
							} else if (response.errors && response.errors.length) {
								this.emit('photo_upload_done', --this.uploading);
								this.validateError(previewId, response.errors.join('<br/>'));
								this._validateProgress = false;
								this._validate();
							}

							// show error message on on validation failed
						}, this)
					)
					.fail(
						$.proxy(function (xhr) {
							this.emit('photo_upload_done', --this.uploading);
							this.validateFail(previewId);
							this._validateProgress = false;
							this._validate();
						}, this)
					);
			}, 1000),

			/**
			 * Validation handler in case of server returning error.
			 */
			validateError: function (previewId, errorMessage) {
				var $preview = this.$photos.find('.ps-js-preview-' + previewId),
					$error = $('<div/>')
						.append('<span>' + errorMessage + '</span>')
						.attr('title', errorMessage);

				$error
					.css({
						color: 'red',
						display: 'table',
						fontSize: '.8em',
						height: '100%',
						overflow: 'hidden',
						padding: 4
					})
					.find('span')
					.css({
						display: 'table-cell',
						verticalAlign: 'middle'
					});

				$preview.find('.ps-js-progress').stop().hide();
				$preview.find('.ps-js-rotate-l, .ps-js-rotate-r').hide();
				$preview.find('.ps-js-remove').show();
				$preview.find('.ps-js-img').html($error);
			},

			/**
			 * Validation handler in case of network issue (404, 500, etc)
			 */
			validateFail: function (previewId) {
				var message = psdata_photos_dropzone.text_upload_failed_notice,
					$preview = this.$photos.find('.ps-js-preview-' + previewId),
					$error = $('<div/>')
						.append('<span>' + message + '</span>')
						.attr('title', message);

				$error
					.css({
						color: 'red',
						display: 'table',
						fontSize: '.8em',
						height: '100%',
						overflow: 'hidden',
						padding: 4
					})
					.find('span')
					.css({
						display: 'table-cell',
						verticalAlign: 'middle'
					});

				$preview.find('.ps-js-progress').stop().hide();
				$preview.find('.ps-js-rotate-l, .ps-js-rotate-r').hide();
				$preview.find('.ps-js-remove').show();
				$preview.find('.ps-js-img').html($error);
			},

			/**
			 * TODO: docblock
			 */
			resetSortable: function () {
				this.$photos.sortable({
					items: '.ps-js-preview',
					// #6811 Fix dragging issue as described in the following forum link:
					// https://forum.jquery.com/topic/jquery-ui-1-8rc1-sortable-list-doesn-t-allow-drop-at-first-and-last-position-depending-where-the-mouse-gets-clicked-to-drag-on
					tolerance: 'pointer',
					// #6811 Adds dummy class to prevent placeholder's incorrect height issue.
					placeholder: 'ps-js-dummy-class'
				});
			},

			/**
			 * TODO: docblock
			 */
			destroy: function () {
				this.removeTempFiles(this.getPhotos());
				this.$el.remove();
			},

			/**
			 * Notify server to delete related temporary files if user removes photo(s) from dropzone.
			 * @param {string[]} photos
			 */
			removeTempFiles: function (photos) {
				if (_.isArray(photos) && photos.length >= 1) {
					var params = {
						user_id: peepsodata.currentuserid,
						photo: photos,
						_wpnonce: this.$el.find('[name=_wpnonce_remove_temp_files]').val(),
						_wp_http_referer: this.$el.find('[name=_wp_http_referer]').val()
					};

					// send req through filter
					params = peepso.observer.applyFilters('photos_remove_temp_files', params);

					peepso.postJson('photosajax.remove_temp_files', params);
				}
			},

			/**
			 * TODO: docblock
			 */
			onUpload: function () {
				this.upload();
			},

			/**
			 * TODO: docblock
			 */
			onRotate: function (e) {
				var previewId = e.data.id,
					direction = e.data.dir,
					$preview = this.$photos.find('.ps-js-preview-' + previewId),
					photo = $preview.data('id');

				var $disabler = $('<div />').css({
					background: 'rgba(255, 255, 255, .5)',
					position: 'absolute',
					top: 0,
					left: 0,
					right: 0,
					bottom: 0
				});

				$disabler.appendTo($preview);

				var params = { photo, direction };
				params = peepso.observer.applyFilters('photos_rotate_req', params, this);

				peepso.ajax
					.post('photosajax.rotate_photo', params)
					.done(json => {
						if (json.success) {
							$preview.data('id', json.data.file);
							$preview.find('.ps-js-img').html(`<img src="${json.data.thumb}" />`);
						}
					})
					.always(() => $disabler.remove());
			},

			/**
			 * TODO: docblock
			 */
			onRemove: function (e) {
				var previewId = e.data.id,
					$preview = this.$photos.find('.ps-js-preview-' + previewId);

				this.removeTempFiles([$preview.data('id')]);
				this.emit('photo_removed', previewId);
			},

			/**
			 * TODO: docblock
			 */
			triggerUpload: function () {
				this.upload();
			},

			/**
			 * Register additional droppable element.
			 *
			 * @param {HTMLElement} elem
			 */
			addDroppable: function (elem) {
				// Add additional drop target.
				var parentLookupTimer = setInterval(
					$.proxy(function () {
						var $parent = this.$el.parent();
						if ($parent) {
							clearInterval(parentLookupTimer);

							// Initialize uploader if it is not initialized yet.
							if (!this.uploadInitialized) {
								this.uploadInit();
								this.uploadInitialized = true;
							}

							// Add the element to the set of drop targets.
							var $dropzones = this.$file.psFileupload('option', 'dropZone');
							$dropzones = $dropzones.add(elem);
							this.$file.psFileupload('option', 'dropZone', $dropzones);
						}
					}, this),
					1000
				);
			}
		}
	);

	return PhotoDropzone;
});
