import $ from 'jquery';
import peepso, { observer } from 'peepso';
import Uploader from './uploader';

var UPLOAD_ID;

(function(factory) {
	window.ps_videos = peepso.videos = factory();

	// remove video tab switch, unused for now
	observer.addAction(
		'postbox_init',
		function(postbox) {
			postbox.$tabContext.find('#audio-post').remove();
			postbox.$tabContext.find('#video-post').remove();
		},
		10,
		1
	);

	// Remove video item on widgets after delete post action.
	observer.addAction(
		'peepso_delete_post',
		function(postId) {
			var $item = $('.ps-widget--videos').find('.ps-js-video');
			$item = $item.filter('[data-post-id="' + postId + '"]');
			$item.remove();
		},
		10,
		1
	);
})(function() {
	/*
	 * @package PeepSovideos
	 * @author PeepSo
	 */

	window.PsVideos = function PsVideos() {
		this._fileList = [];
		this.show_progress = null;
		this.queue_filesize = 0;
		this.$url_textarea = null;
		this.$uploader = null;
		this.has_valid_url = false;
		this.preview_request = false;
		this.save_post = null;
		this.last_error = null;
	};

	var ps_videos = new PsVideos();

	/**
	 * Initializes this instance's container and selector reference to a postbox instance.
	 * Called on postbox.js _load_addons()
	 */
	PsVideos.prototype.init = function() {
		var that = this;

		observer.addFilter(
			'peepso_postbox_can_submit',
			function(can_submit) {
				if (that.$postbox.$posttabs.current_tab_id === 'videos') {
					can_submit.hard.push(UPLOAD_ID || !!that.has_valid_url);
				}

				return can_submit;
			},
			30,
			1
		);

		var orig_placeholder = this.$postbox.$textarea.attr('placeholder');

		jQuery(this.$postbox.$posttabs).on('peepso_posttabs_show-videos', function(
			e,
			tab,
			posttabs
		) {
			jQuery('#video-post', that.$postbox).hide();
			tab.show();
			if ('undefined' !== typeof postbox.$textarea) {
				postbox.$textarea.attr('placeholder', 'Say something about this video...');
			}
			jQuery('.ps-postbox-status', posttabs.options.container).show();
		});

		this.$url_textarea = jQuery('.ps-videos-url', this.$postbox);
		this.$uploader = jQuery('[data-tab-id=videos]')
			.find('#ps-upload-container > div')
			.eq(0);

		this.$postbox.$posttabs.on('peepso_posttabs_submit-videos', function() {
			// TODO: use if (!that.has_valid_url) -- comparisons take more time
			if (!UPLOAD_ID && !that.has_valid_url) {
				if (null !== that.last_error) {
					alert(that.last_error);
				}
				return;
			}
			observer.addFilter(
				'postbox_req_' + that.$postbox.guid,
				function(req) {
					return that.set_post_filter(req);
				},
				10,
				1
			);
			that.$postbox.save_post();
			observer.removeFilter(
				'postbox_req_' + that.$postbox.guid,
				function(req) {
					return that.set_post_filter(req);
				},
				10,
				1
			);
		});

		this.$postbox.$posttabs.on('peepso_posttabs_cancel-videos', function() {
			jQuery('#video-post', that.$postbox).show();
			that.$postbox.$textarea.attr('placeholder', orig_placeholder);
			that.on_cancel();
		});

		jQuery('#video-post', this.$postbox).on('click', function() {
			// Do nothing when we're on the videos tab
			if ('videos' === that.$postbox.$posttabs.current_tab().data('tab')) return;
			jQuery(that.$postbox.$posttabs)
				.find("[data-tab='videos']")
				.trigger('click');
			//		that.$postbox.$posttabs.on_cancel();
			that.$postbox.$textarea.focus();
		});

		this.$url_textarea.on('change', function() {
			that.on_url_input(that.$url_textarea.val());
		});

		this.$uploader.on('click', function() {
			that.browseFile();
		});

		observer.addFilter(
			'postbox_req_edit',
			function(req, sel) {
				var url = jQuery('.ps-videos-edit-url', this.$postbox);
				if (url.length > 0) {
					req.url = url.val();
					req.type = 'video';
				}
				return req;
			},
			10,
			2
		);

		this.save_post = jQuery.proxy(activity.option_savepost, activity);
		activity.option_savepost = function(post_id) {
			that.option_savepost(post_id);
		};
	};

	/**
	 * Load video preview
	 * @param {string} url address
	 */
	PsVideos.prototype.on_url_input = function(url) {
		var $loading = this.$postbox.find(
				'.ps-postbox-videos .ps-postbox-input .ps-postbox-loading'
			),
			$preview = this.$postbox.find('.ps-postbox-videos .ps-postbox-preview'),
			$submit = this.$postbox.find('.ps-postbox-action > .ps-button-action.postbox-submit');

		// Abort previous preview request.
		if (this.preview_request && this.preview_request.ret) {
			this.preview_request.ret.abort();
		}

		$loading.show();

		this.preview_request = peepso.postJson(
			'videosajax.get_preview',
			{ url: url },
			$.proxy(function(response) {
				var html,
					reFbRoot = /<div[^<]+fb-root[^<]+<[^>]+>/i,
					isFbVideo = false;

				$loading.hide();

				if (response.success) {
					this.has_valid_url = true;

					// Remove #fb-root tag if present.
					html = response.data.html;
					if ((isFbVideo = html.match(reFbRoot))) {
						html = html.replace(reFbRoot, '');
						html = html.replace(/<script[^<]+<\/script>/i, '');
					}

					$preview
						.empty()
						.show()
						.html(html);

					// Manually trigger parse XFBML for Facebook video preview.
					if (isFbVideo) {
						peepso.util.fbParseXFBML();
					}
				} else {
					this.has_valid_url = false;
					$preview.hide().empty();
					$submit.hide();

					// Show error if supplied.
					if (response.errors) {
						this.last_error = response.errors[0];
						alert(this.last_error);
					}
				}

				this.$postbox.on_change();
				this.preview_request = false;
			}, this)
		);
	};

	/**
	 * Defines the postbox this instance is running on.
	 * Called on postbox.js _load_addons()
	 * @param {object} postbox pspostbox
	 */
	PsVideos.prototype.set_postbox = function(postbox) {
		this.$postbox = postbox;
	};

	/**
	 * Aborts preview request and hides view input fields
	 */
	PsVideos.prototype.on_cancel = function() {
		if (this.preview_request) {
			this.preview_request.ret.abort();
			jQuery(
				'.ps-postbox-videos .ps-postbox-input .ps-postbox-loading',
				this.$postbox
			).hide();
		}

		this.has_valid_url = false;
		jQuery('#ps-videos-input', this.$postbox).hide();
		this.$url_textarea.val('');
		UPLOAD_ID = false;
		jQuery('.ps-postbox-videos .ps-postbox-preview', this.$postbox)
			.hide()
			.html('');
		this.$postbox.on_change();
	};

	/**
	 * Set request url and set request type to "video"
	 * @param {object} req postbox request
	 */
	PsVideos.prototype.set_post_filter = function(req) {
		if (UPLOAD_ID) {
			req.video_id = UPLOAD_ID;
		} else {
			req.url = this.$url_textarea.val();
		}

		req.type = 'video';

		console.log(req);

		return req;
	};

	/**
	 * Play video
	 * @param {object} e HTML element object
	 * @param {object} params Video parameters
	 */
	PsVideos.prototype.play_video = function(e, params) {
		params = params || {};

		if (params.type === 'vimeo') {
			this.play_vimeo_video(e, params);
			return false;
		}

		$player = jQuery('#peepso-video-player-' + jQuery(e).data('post-id')).find('iframe');
		jQuery('#peepso-video-player-' + jQuery(e).data('post-id')).fadeIn();

		var $thumbnail_div = jQuery(e).parents('.video-avatar');

		$thumbnail_div.siblings('.video-description').css('display', 'block');

		var video_player = jQuery('#peepso-video-player-' + jQuery(e).data('post-id')).parent();
		video_player.show();
		$thumbnail_div.parent().prepend(video_player);

		$thumbnail_div.fadeOut().remove();

		var css;
		var orig_width = $player.width();
		var ratio = $player.width() / $player.height();
		var pratio = $player.parent().width() / $player.parent().height();

		if (ratio < pratio) {
			css = { width: '100%' };
			//		height: $player.height() * ratio
			$player.css(css);
			new_width = $player.width();
			$player.css('height', $player.height() * (new_width / orig_width));
		} else {
			css = { width: '100%', height: '390' }; // same dimension with youtube
		}

		$player.css(css);

		return false;
	};

	/**
	 * Play vimeo video.
	 * @param {object} el Clicked DOM object
	 * @param {object} params Video parameters
	 */
	PsVideos.prototype.play_vimeo_video = function(el, params) {
		el = $(el);

		var $ct = el.closest('.ps-media-video');
		if (!$ct.length) {
			return;
		}

		var $player = $ct.prev('.cstream-attachment');
		if (!$player.length) {
			return;
		}

		var $iframe = $player.find('iframe');
		if (!$iframe.length) {
			$iframe = $(
				'<iframe src="https://player.vimeo.com/video/' +
					params.id +
					'?autoplay=1" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen style="width:100%"></iframe>'
			);
			$iframe.appendTo($player);
		}

		var $thumbnail = el.closest('.video-avatar');
		$thumbnail.siblings('.video-description').css('display', 'block');
		$thumbnail.remove();

		$player.show();
	};

	/**
	 * Overrides activity.save_post()
	 * @param {string} post_id Post ID
	 */
	PsVideos.prototype.option_savepost = function(post_id) {
		var that = this;
		var $ai = jQuery('.ps-js-activity--' + post_id);
		if ($ai.length > 0) {
			var req = observer.applyFilters(
				'postbox_req_edit',
				{},
				jQuery('.cstream-edit textarea', $ai)
			);
			if (
				'undefined' !== typeof req.type &&
				'undefined' !== typeof req.url &&
				'video' === req.type
			) {
				jQuery('.cstream-edit textarea, .cstream-edit input', $ai).attr(
					'disabled',
					'disabled'
				);
				jQuery('.ps-edit-loading', $ai).show();
				jQuery('.cstream-edit button', $ai).hide();
				// check if url is valid
				peepso.postJson('videosajax.get_preview', { url: req.url }, function(response) {
					if ('undefined' !== typeof response.errors) {
						alert(response.errors[0]);
						jQuery('.cstream-edit textarea, .cstream-edit input', $ai).removeAttr(
							'disabled'
						);
						jQuery('.ps-edit-loading', $ai).hide();
						jQuery('.cstream-edit button', $ai).show();
					} else that.save_post(post_id);
				});
			} else this.save_post(post_id);
		} else this.save_post(post_id);
	};

	PsVideos.prototype.browseFile = function() {
		var that = this,
			$div;

		if (!this.$file) {
			this.$file = $(
				'<input type="file" name="filedata[]" accept="video/mp4,video/x-m4v,video/*" />'
			);
			$div = $('<div />').append(this.$file);
			$div.css({
				position: 'absolute',
				top: 0,
				right: 0,
				width: 1,
				height: 1,
				overflow: 'hidden'
			});
			$div.insertAfter(this.$uploader);

			this.$file.on('change', function(e) {
				var elem = e.target,
					file = elem.files[0],
					uploader;

				e.preventDefault();

				that.validate(file)
					.then(function() {
						uploader = new Uploader(elem);
						uploader.upload().then(function(data) {
							UPLOAD_ID = data.id;
						});
					})
					.fail(function(errors) {
						alert(errors[0]);
					});
			});
		}

		this.$file.click();
	};

	/**
	 * Validate video file to be uploaded.
	 * @param {Object} file
	 * @param {string} file.type
	 * @param {number} file.size
	 * @returns {jQuery.Deferred}
	 */
	PsVideos.prototype.validate = function(file) {
		return $.Deferred(function(defer) {
			var params = {
				type: file.type,
				size: parseInt(file.size)
			};

			peepso.postJson('videosajax.validate_video_upload', params, function(json) {
				if (json && json.success) {
					defer.resolve();
				} else {
					defer.reject(json && json.errors);
				}
			});
		});
	};

	/**
	 * Adds a new PsVideos object to a postbox instance.
	 * @param {array} addons An array of addons to plug into the postbox.
	 */
	// observer.addFilter("peepso_postbox_addons", function(addons) {
	// 	addons.push(new PsVideos);
	// 	return (addons);
	// }, 10, 1);

	return ps_videos;
});
