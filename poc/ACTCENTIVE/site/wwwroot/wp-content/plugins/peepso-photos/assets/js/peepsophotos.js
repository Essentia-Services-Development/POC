(function ($, peepso, factory) {
	factory($, peepso);

	// Remove photo item on widgets after delete post action.
	peepso.observer.addAction(
		'peepso_delete_post',
		function (postId) {
			var $item = $('.ps-widget--photos').find('.ps-js-photo');
			$item = $item.filter('[data-post-id="' + postId + '"]');
			$item.remove();
		},
		10,
		1
	);

	// Handle file drop.
	peepso.observer.addAction(
		'commentbox_drop_files',
		function (textarea, files) {
			peepso.photos.comment_attach_photo(textarea, files);
		},
		10,
		2
	);

	peepso.observer.addFilter(
		'human_friendly_extras',
		function (extras, content, root) {
			if (!content && root && !root.querySelector('.ps-js-post-header')) {
				var $photo = $(root).find('.ps-comment-media .ps-media-photos [data-preview]');
				if ($photo.length) {
					extras.push($photo.data('preview'));
				}
			}
			return extras;
		},
		20,
		3
	);

	// Direct photo link handler.
	$(function () {
		let href = window.location.href;
		let photoId = href.match(/#(.+&)?photo=(\d+)/);
		if (photoId) {
			if (window.ps_comments) {
				ps_comments.open(photoId[2], 'photo');
			}
		}
	});
})(jQuery || $, peepso, function ($, peepso) {
	/**
	 * PsPhotos class.
	 * @class PsPhotos
	 */
	function PsPhotos() {
		$(window).on('peepso_activity_deleted', function (e, json, act_id) {
			if (json.success) {
				$('.photo-item[data-id=' + act_id + ']').remove();
				if (undefined !== json.data.photo_total && json.data.photo_total <= 0) {
					$('.ps-js-activity--' + json.data.post_act_id).remove();
				}
			}
		});
	}

	var ps_photos = (peepso.photos = new PsPhotos());

	/**
	 * avatar upload
	 */
	PsPhotos.prototype.set_as_avatar = function (extra) {
		var req = jQuery.extend(
			{
				user_id: peepsodata.userid,
				photo_id: jQuery('#photoid_tobe_photo_profile').val(),
				_wpnonce: jQuery('#_photoprofilenonce').val()
			},
			extra || {}
		);

		peepso.postJson('photosajax.set_photo_as_avatar', req, function (json) {
			if (json.success) {
				window.location.reload();
			}
		});
	};

	/**
	 * avatar upload
	 */
	PsPhotos.prototype.set_as_cover = function (extra) {
		var req = jQuery.extend(
			{
				user_id: peepsodata.userid,
				photo_id: jQuery('#photoid_tobe_photo_profile').val(),
				_wpnonce: jQuery('#_photoprofilenonce').val()
			},
			extra || {}
		);

		peepso.postJson('photosajax.set_photo_as_cover', req, function (json) {
			if (json.success) {
				window.location.reload();
			}
		});
	};

	/**
	 * Arrange pho$o streams.
	 */
	PsPhotos.prototype.arrange_images = _.debounce(function () {
		var placeholderClass = 'photo-container-placeholder',
			initializedClass = 'ps-js-initialized';

		$('.ps-js-photos')
			.not('.' + initializedClass)
			.each(
				$.proxy(function (index, item) {
					var $ct = $(item),
						$children = $ct.children('.ps-js-photo'),
						$loading = $ct.children('.ps-js-loading'),
						havePlaceholder = $ct.hasClass(placeholderClass),
						urls,
						timer;

					urls = $children.map(function () {
						var $img = $(this).find('img');
						return $img.data('src');
					});

					// load images
					this.load_images(urls).always(
						$.proxy(function () {
							$loading.remove();

							$children.each(function () {
								var $img = $(this).find('img');
								$img.attr('src', $img.data('src'));
								$img.removeData('src').removeAttr('data-src');
							});

							if (havePlaceholder) {
								$children.each(function (index, item) {
									var $el = $(item),
										$img = $el.find('img');

									$el.data({
										width: $img[0].naturalWidth,
										height: $img[0].naturalHeight
									});
								});
							}

							// fade-in
							if (havePlaceholder) {
								$ct.css({ opacity: 0 }).removeClass(placeholderClass);
								$ct.animate({ opacity: 1 });
							}

							$ct.addClass(initializedClass);
						}, this)
					);
				}, this)
			);
	}, 100);

	/**
	 * Re-arrange all photo streams.
	 */
	PsPhotos.prototype.rearrange_images = function () {
		var initializedClass = 'ps-js-initialized';
		$('.photo-container')
			.filter('.' + initializedClass)
			.removeClass(initializedClass);
		this.arrange_images();
	};

	/**
	 * Attach photo to comment
	 *
	 * @param {HTMLElement} elem
	 * @param {FileList} files
	 */
	PsPhotos.prototype.comment_attach_photo = function (elem, files) {
		var $elem, $addons, $addon, $div, $file, unique_id;

		$elem = $(elem);
		$addons = $elem.closest('.ps-js-comment-new,.ps-comment-edit').find('.ps-js-addons');
		$addon = $addons.find('.ps-js-addon-photo');

		if (!(unique_id = $addon.data('initialized'))) {
			$file = $(
				'<input type="file" name="filedata[]" accept=".gif,.jpg,.jpeg,.png,.tif,.tiff,.webp" />'
			).uniqueId();
			$div = $('<div />').append($file);
			$div.css({
				position: 'absolute',
				top: 0,
				right: 0,
				width: 1,
				height: 1,
				overflow: 'hidden'
			});
			$div.insertAfter($addon);

			// initialize file upload
			if ($file.psFileupload) {
				$file.psFileupload({
					formData: {
						user_id: peepsodata.currentuserid
					},
					singleFileUploads: false,
					sequentialUploads: false,
					replaceFileInput: /^((?!chrome|android).)*safari/i.test(navigator.userAgent),
					dropZone: $addons.closest('.ps-textarea-wrapper'),
					pasteZone: null,
					dataType: 'json',
					url: peepsodata.ajaxurl_legacy + 'photosajax.upload_photo',
					add: $.proxy(function (e, data) {
						this.validate_photo(data);
					}, this),
					submit: function () {
						$addon.find('.ps-js-remove').hide(); // hide 'remove' button
						$addon.find('.ps-js-loading').show(); // show loading
						$addon.find('.ps-js-img').hide();
						$addon.show();
						$(document).trigger('ps_comment_addon_added', $addon);
						return true;
					},
					done: $.proxy(function (e, data) {
						var response = data.result;
						if (response.success) {
							$addon.find('.ps-js-remove').show(); // show 'remove' button
							$addon.find('.ps-js-loading').hide(); // hide loading
							$addon
								.find('.ps-js-img')
								.attr('src', response.data.thumbs[0])
								.data('id', response.data.files[0])
								.show();
							$(document).trigger('ps_comment_addon_added', $addon);
						}
					}, this)
				});
			}

			unique_id = $file.attr('id');
			$addon.data('initialized', unique_id);
		}

		if (files instanceof FileList) {
			return;
		}

		if (!peepso.isWebdriver()) {
			$('#' + unique_id).trigger('click');
		}
	};

	/**
	 * Validate photo before it is uploaded
	 * @param {object} data File object from file upload library
	 */
	PsPhotos.prototype.validate_photo = function (data) {
		var reImage = /\.(gif|jpg|jpeg|png|tif|tiff|webp)$/i,
			size = 0,
			file;

		// check file extension
		for (var i = 0; i < data.files.length; i++) {
			file = data.files[i];
			if (!reImage.test(file.name)) {
				peepso
					.dialog(peepsophotosdata.error_unsupported_format, { error: true })
					.show()
					.autohide();
				return false;
			}
			size += parseInt(file.size);
		}

		var req = {
			size: size,
			filesize: size,
			photos: data.files.length
		};

		// send req through filter
		req = peepso.observer.applyFilters('photos_validate_req', req);

		var that = this;
		peepso.postJson('photosajax.validate_photo_upload', req, function (response) {
			if (response.success) {
				data.submit();
			} else {
				peepso.dialog(response.errors[0], { error: true }).show().autohide();
			}
		});
	};

	/**
	 * Form dialog create album user.
	 * @param {number} user_id User ID.
	 */
	PsPhotos.prototype.show_dialog_album = function (user_id) {
		new peepso.PhotoAlbumDialog(user_id);
	};

	/**
	 * Form dialog add photos to album.
	 * @param {number} user_id User ID.
	 */
	PsPhotos.prototype.show_dialog_add_photos = function (user_id, album_id) {
		new peepso.PhotoAlbumUploadDialog(user_id, album_id);
	};

	/**
	 * Deletes an album via ajax
	 * @param  {int} album_id The album ID to delete
	 */
	PsPhotos.prototype.show_dialog_delete_album = function (user_id, album_id, extra) {
		var req = jQuery.extend(
			{
				album_id: album_id,
				uid: user_id,
				_wpnonce: jQuery('#_delete_album_nonce').val()
			},
			extra || {}
		);

		var $act_delete_div_msg = jQuery('[data-album-delete-id=' + album_id + ']');
		var confirm_delete_message = '';
		if ($act_delete_div_msg.length) {
			confirm_delete_message = $act_delete_div_msg.text();
		}

		pswindow.confirm_delete(function () {
			// send req through filter
			req = peepso.observer.applyFilters('photos_delete_album', req);

			peepso.postJson('photosajax.delete_album', req, function (json) {
				if (json.success) {
					window.location.reload();
				} else {
					peepso.dialog(json.errors[0], { error: true }).show().autohide();
				}
			});
		}, confirm_delete_message);

		return false;
	};

	/**
	 * Deletes stream post album type
	 * @param  {int} post_id The post ID to delete
	 */
	PsPhotos.prototype.delete_stream_album = function (post_id, act_id) {
		var req = {
			post_id: post_id,
			uid: peepsodata.currentuserid
		};

		var $act_delete_div_msg = jQuery('[data-act-delete-id=' + act_id + ']');
		var confirm_delete_message = '';
		if ($act_delete_div_msg.length) {
			confirm_delete_message = $act_delete_div_msg.text();
		}

		pswindow.confirm_delete(function () {
			// send req through filter
			req = peepso.observer.applyFilters('photos_delete_stream_album', req);

			peepso.postJson('photosajax.delete_stream_album', req, function (json) {
				if (json.success) {
					window.location.reload();
				} else {
					peepso.dialog(json.errors[0], { error: true }).show().autohide();
				}
			});
		}, confirm_delete_message);

		return false;
	};

	/**
	 * Menu selector
	 */
	PsPhotos.prototype.select_menu = function (select) {
		var $option = $(select.options[select.selectedIndex]),
			value = $option.val(),
			url = $option.data('url'),
			loc = window.location + '',
			samePage = loc.match(/\/requests/) && url.match(/\/requests/);

		if (samePage) {
			$('.ps-js-photos-submenu')
				.siblings('.tab-content')
				.find(value === 'album' ? '#album' : '#latest')
				.addClass('active')
				.siblings()
				.removeClass('active');
		} else {
			window.location = $option.data('url');
		}
	};

	/**
	 * Load single image.
	 * @memberof PsPhotos
	 * @param {string} url Image URL to be loaded.
	 * @return {jQuery.Promise}
	 */
	PsPhotos.prototype.load_image = function (url) {
		return $.Deferred(function (deferred) {
			var image = new Image();
			image.onload = loaded;
			image.onerror = errored;
			image.onabort = errored;
			image.src = url;

			function loaded() {
				unbindEvents();
				deferred.resolve(image);
			}

			function errored() {
				unbindEvents();
				deferred.reject(image);
			}

			function unbindEvents() {
				image.onload = null;
				image.onerror = null;
				image.onabort = null;
			}
		}).promise();
	};

	/**
	 * Load multiple images.
	 * @memberof PsPhotos
	 * @param {string[]} urls Image URLs to be loaded.
	 * @return {jQuery.Promise}
	 */
	PsPhotos.prototype.load_images = function (urls) {
		var deferreds = [],
			i;

		if (typeof urls !== 'object' || typeof urls.length !== 'number') {
			urls = [urls];
		}

		for (i = 0; i < urls.length; i++) {
			deferreds.push(this.load_image(urls[i]));
		}

		return $.when.apply($, deferreds);
	};

	/**
	 * Show image in a popup.
	 * @param {string} src
	 */
	PsPhotos.prototype.show_image = function (src) {
		var cssPopup = 'ps-js-photo-popup',
			rImage = /\.(gif|jpe?g|png|webp)/i,
			rGif = /\.gif/i,
			rJpg = /\.jpe?g/i;

		if (!rImage.test(src)) {
			return;
		}

		var $popup = $('.' + cssPopup).eq(0);

		// initialize popup
		if (!$popup.length) {
			$popup = $(peepsophotosdata.template_popup);
			$popup.addClass(cssPopup).appendTo(document.body);
			$popup.on('click', '.ps-lightbox-padding, .ps-lightbox-close', function (e) {
				var $elem = $(e.currentTarget);
				$elem.closest('.ps-lightbox').hide();
			});
			$popup.on('click', '.ps-lightbox-wrapper', function (e) {
				e.stopPropagation();
			});
			$popup.on('click', '.ps-lightbox-play', function (e) {
				var $btn = $(e.currentTarget),
					$img = $btn.siblings('.ps-js-img').find('img'),
					iconPlay = 'gcis gci-play',
					iconStop = 'gcis gci-stop',
					src = $img.attr('src'),
					isPlayed = $btn.data('played'),
					timerHideStop = $btn.data('timer-stop');

				if (isPlayed) {
					$btn.removeData('played');
					$btn.removeClass(iconStop).addClass(iconPlay);
					$img.attr('src', src.replace(rGif, '.jpg'));
					$btn.removeData('timer-stop');
					clearTimeout(timerHideStop);
				} else {
					$btn.data('played', 1);
					$btn.removeClass(iconPlay).addClass(iconStop);
					$img.attr('src', src.replace(rJpg, '.gif'));

					// Hide the icon on the touch device after a while since
					// it does not support the mouseover event.
					if (peepso.browser.isTouch()) {
						clearTimeout(timerHideStop);
						timerHideStop = setTimeout(function () {
							$btn.removeClass(iconStop);
						}, 1000);
						$btn.data('timer-stop', timerHideStop);
						$img.one('click', function () {
							$btn.click();
						});
					}
				}
			});
		}

		var $object = $popup.find('.ps-js-img');

		// GIF autoplay setting.
		var gifAutoplay = +peepsophotosdata.gif_autoplay;

		// Show non-animated image placeholder if GIF autoplay setting is disabled.
		if (rGif.test(src) && !gifAutoplay) {
			src = src.replace(rGif, '.jpg');
			$object.html('<img src="' + src + '" />');
			$object
				.next('.ps-lightbox-play')
				.removeClass('gcis gci-stop')
				.addClass('gcis gci-play')
				.show();
			$popup.show();
		}
		// Treat other image types. Also treat GIF image if autoplay setting is enabled.
		else {
			$object.html('<img src="' + src + '" />');
			$object.next('.ps-lightbox-play').hide();
			$popup.show();
		}
	};

	/**
	 * Performe page initialization on document ready action
	 */
	$(document).ready(function () {
		$(window).on('load', function () {
			ps_photos.arrange_images();
		});

		peepso.observer.addAction('browser.resize', function () {
			ps_photos.rearrange_images();
		});

		$(document).on(
			'ps_activitystream_loaded ps_activitystream_append peepso_repost_shown peepso_report_shown peepso_repost_added peepso_post_edit_saved peepso_messages_list_displayed ps_comment_added',
			function (e) {
				if (e.type === 'ps_activitystream_append') {
					setTimeout(function () {
						ps_photos.arrange_images();
					}, 3000);
				} else {
					ps_photos.arrange_images();
				}

				if (pswindow.is_visible) {
					pswindow.refresh();
				}
			}
		);

		$(document).on('ps_comment_aftersave', function (e, act_id, sel) {
			var $ct = $(sel).closest('.ps-js-comment-new, .ps-js-comment-edit'),
				$photo = $ct.find('.ps-js-addon-photo');

			$photo.find('.ps-js-remove').hide(); // hide 'remove' button
			$photo.find('.ps-js-loading').hide(); // hide loading
			$photo.find('.ps-js-img').attr('src', '').removeData('id').hide();
			$photo.hide();
			$(document).trigger('ps_comment_addon_removed', $photo);
		});

		$(document).on('ps_comment_save ps_lightbox_navigate', function () {
			ps_photos.arrange_images();
		});

		$(document).on('click', '.ps-js-addon-photo .ps-js-remove', function () {
			var $addon = $(this).closest('.ps-js-addon-photo'),
				$img = $addon.find('.ps-js-img'),
				photo = $img.data('id');

			$addon.find('.ps-js-remove').hide(); // hide 'remove' button
			$addon.find('.ps-js-loading').hide(); // hide loading
			$img.attr('src', '').removeData('id').removeAttr('data-id').hide(); // hide image
			$addon.hide();

			// notice server to remove the temp image
			var params = {
				user_id: peepsodata.currentuserid,
				photo: [photo],
				_wpnonce: $addon.find('[name=_wpnonce_remove_temp_comment_photos]').val(),
				_wp_http_referer: $addon.find('[name=_wp_http_referer]').val()
			};

			params = peepso.observer.applyFilters('photos_remove_temp_files', params);
			peepso.postJson('photosajax.remove_temp_files', params);

			$(document).trigger('ps_comment_addon_removed', $addon);
		});

		peepso.observer.addAction(
			'comment_edit',
			function (id, elem) {
				var $ct = $(elem),
					$photo = $ct.find('.ps-js-addon-photo'),
					$img = $photo.find('.ps-js-img');

				if ($img.length && $img.data('id')) {
					$photo.find('.ps-js-remove').show(); // show 'remove' button
					$photo.find('.ps-js-loading').hide(); // hide loading
					$photo.find('.ps-js-img').show();
					$photo.show();
					$(document).trigger('ps_comment_addon_added', $photo);
				} else {
					$photo.find('.ps-js-remove').hide(); // hide 'remove' button
					$photo.find('.ps-js-loading').hide(); // hide loading
					$photo.find('.ps-js-img').hide();
					$photo.hide();
					$(document).trigger('ps_comment_addon_removed', $photo);
				}
			},
			10,
			2
		);

		$(document).on('click', '.ps-lightbox .ps-js-btn-gif', function (e) {
			var $btn = $(e.currentTarget),
				$img = $btn.siblings('.ps-js-photo-gif'),
				iconPlay = 'gcis gci-play',
				iconStop = 'gcis gci-stop',
				iconSpinner = 'ps-lightbox__spinner',
				src = $img.attr('src'),
				isPlayed = $btn.data('played'),
				timerHideStop = $btn.data('timer-stop'),
				timerShowSpinner,
				imgLoader;

			e.preventDefault();
			e.stopPropagation();

			if (isPlayed) {
				$btn.removeData('played');
				$btn.removeClass(iconStop).addClass(iconPlay);
				$img.attr('src', src.replace('.gif', '.jpg'));
				$btn.removeData('timer-stop');
				clearTimeout(timerHideStop);
				return;
			}

			timerShowSpinner = setTimeout(function () {
				$btn.addClass(iconSpinner).removeClass(iconPlay);
			}, 500);

			src = src.replace('.jpg', '.gif');
			imgLoader = new Image();
			imgLoader.onload = function () {
				$btn.data('played', 1);
				$btn.removeClass(iconPlay).removeClass(iconSpinner);
				$btn.addClass(iconStop);

				$img.attr('src', src);
				clearTimeout(timerShowSpinner);

				// Hide the icon on the touch device after a while since it does not
				// support the mouseover event.
				if (peepso.browser.isTouch()) {
					clearTimeout(timerHideStop);
					timerHideStop = setTimeout(function () {
						$btn.removeClass(iconStop);
					}, 1000);
					$btn.data('timer-stop', timerHideStop);
					$img.one('click', function () {
						$btn.click();
					});
				}
			};

			imgLoader.src = src;
		});
	});

	/**
	 * Informs that this plugin is loaded.
	 * @param {booloean} flag.
	 */
	peepso.observer.addFilter(
		'ps_photos_available',
		function (flag) {
			return true;
		},
		10,
		1
	);

	/**
	 * Add photo attachment on save comment
	 */
	peepso.observer.addFilter(
		'comment_req',
		function (req, sel) {
			var $ct = $(sel).closest('.ps-js-comment-new,.ps-comment-edit'),
				$photo = $ct.find('.ps-js-addon-photo'),
				$img;

			if ($photo.is(':visible')) {
				$img = $photo.find('.ps-js-img');
				if ($img.length && $img.data('id')) {
					req.photo = $img.data('id');
				}
			}

			return req;
		},
		10,
		2
	);

	/**
	 * Should be able to post empty comment if photo available
	 */
	peepso.observer.addFilter(
		'comment_can_submit',
		function (obj) {
			var $ct = $(obj.el).closest('.ps-js-comment-new, .ps-js-comment-edit'),
				$photo = $ct.find('.ps-js-addon-photo'),
				$img;

			if ($photo.is(':visible')) {
				$img = $photo.find('.ps-js-img');
				if ($img.length && $img.data('id')) {
					obj.can_submit = true;
				}
			}

			return obj;
		},
		10,
		1
	);

	/**
	 * Should show post button if photo available
	 */
	peepso.observer.addFilter(
		'comment_show_button',
		function (obj) {
			var $ct = $(obj.el).closest('.ps-js-comment-new, .ps-js-comment-edit'),
				$photo = $ct.find('.ps-js-addon-photo'),
				$img;

			if ($photo.is(':visible')) {
				$img = $photo.find('.ps-js-img');
				if ($img.length && $img.data('id')) {
					obj.show = true;
				}
			}

			return obj;
		},
		10,
		1
	);
});
