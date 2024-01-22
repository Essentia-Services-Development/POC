(function ($, factory) {
	var PsPostboxPhoto = factory('PsPostboxPhoto', $);

	peepso.observer.addFilter(
		'peepso_postbox_addons',
		function (addons) {
			addons.push(new PsPostboxPhoto());

			return addons;
		},
		10,
		1
	);

	// remove photo tab switch, unused for now
	peepso.observer.addAction(
		'postbox_init',
		function (postbox) {
			postbox.$tabContext.find('#photo-post').remove();
		},
		10,
		1
	);
})(jQuery, function (name, $) {
	return peepso.createClass(name, {
		__constructor: function () {
			this.canSubmitFlag = false;
			this.dropzone = null;
		},

		/**
		 * This function called from main postbox code.
		 * TODO: Should be deprecated, this class should be able to decide its own initialisation process.
		 */
		set_postbox: function ($postbox) {
			this.$postbox = $postbox;
			this.$posttab = $postbox.$posttabs;
		},

		/**
		 * This function called from main postbox code.
		 * TODO: Should be deprecated, this class should be able to decide its own initialisation process.
		 */
		init: function () {
			this.initialize();
		},

		initialize: function () {
			this.$text = this.$postbox.$textarea;
			this.$textContainer = this.$text.closest('.ps-postbox-status');

			var $tab = this.$postbox.find('.ps-postbox-tabs').children('[data-tab-id=photos]');
			this.$uploadButton = $tab.find('.ps-postbox-photo-upload');
			this.$uploadPreview = $tab.find('.ps-postbox-preview');

			this.$posttab.on('peepso_posttabs_show-photos', $.proxy(this.show, this));
			this.$posttab.on('peepso_posttabs_cancel-photos', $.proxy(this.cancel, this));
			this.$posttab.on('peepso_posttabs_submit-photos', $.proxy(this.post, this));

			peepso.observer.addAction(
				'postbox_type_set',
				$.proxy(function ($postbox, type) {
					if ($postbox === this.$postbox && type === 'photos') {
						this.$phototab.trigger('click');
					}
				}, this),
				10,
				2
			);

			this.$uploadButton.on('click', $.proxy(this.upload, this));

			// camera icon
			this.$phototab = this.$postbox.find('#photo-post');
			this.$phototab.on('click', $.proxy(this.onCameraTabClick, this));

			// textarea placeholders
			this.placeholderDefault = this.$text.attr('placeholder');
			this.placeholderPhoto = $('#photo-comment-label').text();

			// observers
			peepso.observer.addFilter(
				'peepso_postbox_can_submit',
				$.proxy(this.canSubmit, this),
				20,
				1
			);

			peepso.observer.addAction(
				'psmessage_new_message_close',
				$.proxy(function ($dialog) {
					this.dropzone.empty();
				}, this),
				10,
				1
			);

			peepso.observer.addAction(
				'postbox_group_set',
				$.proxy(function ($postbox, groupId) {
					this.oldGroupId = this.groupId;
					this.groupId = groupId;

					if (this.$postbox === $postbox) {
						this.dropzone.movePhotos(this.oldGroupId, this.groupId);
					}
				}, this),
				10,
				2
			);

			peepso.observer.addAction(
				'postbox_group_reset',
				$.proxy(function ($postbox, groupId) {
					if (this.$postbox === $postbox) {
						this.dropzone.movePhotos(this.groupId, undefined);
						this.groupId = undefined;
					}
				}, this),
				10,
				2
			);

			peepso.observer.addFilter(
				'photos_validate_req',
				$.proxy(function (req, dropzone) {
					if (this.dropzone === dropzone && this.groupId) {
						req.group_id = this.groupId;
						req.module_id = window.peepsogroupsdata && peepsogroupsdata.module_id;
					}
					return req;
				}, this),
				10,
				2
			);

			peepso.observer.addFilter(
				'photos_upload_req',
				$.proxy(function (req, dropzone) {
					if (this.dropzone === dropzone && this.groupId) {
						req.group_id = this.groupId;
						req.module_id = window.peepsogroupsdata && peepsogroupsdata.module_id;
					}
					return req;
				}, this),
				10,
				2
			);

			peepso.observer.addFilter(
				'photos_rotate_req',
				$.proxy(function (req, dropzone) {
					if (this.dropzone === dropzone && this.groupId) {
						req.group_id = this.groupId;
						req.module_id = window.peepsogroupsdata && peepsogroupsdata.module_id;
					}
					return req;
				}, this),
				10,
				2
			);
		},

		show: function (e, tab) {
			tab.show();
			this.showButton();
			this.$phototab.addClass('active');
			this.$text.attr('placeholder', this.placeholderPhoto);
			this.dropzoneInit();
		},

		showButton: function () {
			this.$uploadPreview.hide();
			this.$textContainer.show();
			this.$uploadButton.show();
			this.$postbox.on_change();
		},

		showPreview: function () {
			this.$uploadButton.hide();
			this.$uploadPreview.show();
			this.$textContainer.show();
			this.$text.focus();
		},

		upload: function () {
			this.dropzone.triggerUpload();
		},

		cancel: function () {
			this.dropzone.empty();
			this.showButton();
			this.$phototab.removeClass('active');
			this.$text.attr('placeholder', this.placeholderDefault);
		},

		post: function () {
			var filterName = 'postbox_req_' + this.$postbox.guid;

			peepso.observer.addFilter(filterName, this.postSetRequest, 10, 1, this);
			this.$postbox.save_post();
			peepso.observer.removeFilter(filterName, this.postSetRequest, 10);
			this.dropzone.empty('save');
		},

		postSetRequest: function (req) {
			return $.extend(req, {
				files: this.dropzone.getPhotos(),
				type: 'photo'
			});
		},

		canSubmit: function (flags) {
			if (this.$posttab.current_tab_id === 'photos') {
				flags.hard.push(this.canSubmitFlag);
			}
			return flags;
		},

		dropzoneInit: function () {
			if (!this.dropzone) {
				this.dropzone = new peepso.PhotoDropzone(this.$uploadPreview);
				this.dropzone.on('photo_added', $.proxy(this.dropzonePhotoAdded, this));
				this.dropzone.on(
					'photo_upload_start',
					$.proxy(this.dropzonePhotoUploadStart, this)
				);
				this.dropzone.on('photo_upload_done', $.proxy(this.dropzonePhotoUploadDone, this));
				this.dropzone.on('photo_empty', $.proxy(this.dropzonePhotoEmpty, this));

				// Add the upload button as a droppable element.
				this.dropzone.addDroppable(this.$uploadButton[0]);
			}
		},

		dropzonePhotoAdded: function () {
			this.showPreview();
		},

		dropzonePhotoUploadStart: function () {
			this.canSubmitFlag = false;
		},

		dropzonePhotoUploadDone: function (remaining) {
			if (remaining <= 0) {
				this.canSubmitFlag = true;
				this.$postbox.on_change();
			}
		},

		dropzonePhotoEmpty: function () {
			this.canSubmitFlag = false;

			if ('photos' === this.$posttab.current_tab_id) {
				this.showButton();
			}
		},

		onCameraTabClick: function () {
			if (this.$posttab.current_tab_id !== 'photos') {
				this.$postbox.find('[data-tab=photos]').trigger('click');
				// this.$postbox.find('.ps-postbox-tab.interactions').hide();
				// this.$postbox.find('.ps-postbox-tab-root').show();
			}
		}
	});
});
