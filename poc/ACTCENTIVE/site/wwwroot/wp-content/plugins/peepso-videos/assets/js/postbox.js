import $ from 'jquery';
import _ from 'underscore';
import peepso, { browser, dialog, observer } from 'peepso';
import Video from './video';

export default class PostboxVideo {
	constructor($postbox) {
		this.config = window.peepsovideosdata || {};

		this.$postbox = $postbox;
		this.$postboxTab = this.$postbox.$posttabs;
		this.$postboxStatusTextarea = this.$postbox.$textarea;
		this.$postboxStatus = this.$postboxStatusTextarea.closest('.ps-postbox-status');
		this.$postboxVideo = this.$postbox.find('.ps-postbox-tabs [data-tab-id=videos]');
		this.$postboxVideoShortcut = this.$postbox.find('#video-post');

		// Handle toggle postbox video.
		this.$postboxTab.on('peepso_posttabs_show-videos', () => {
			this.show();
		});
		this.$postboxTab.on('peepso_posttabs_cancel-videos', () => {
			this.cancel();
		});
		this.$postboxTab.on('peepso_posttabs_submit-videos', () => {
			this.post();
		});
		this.$postboxVideoShortcut.on('click', () => {
			this.$postboxTab.find('[data-tab=videos]').click();
		});

		observer.addAction(
			'postbox_type_set',
			$.proxy(function ($postbox, type) {
				if ($postbox === this.$postbox && type === 'videos') {
					this.$postboxVideoShortcut.trigger('click');
				}
			}, this),
			10,
			2
		);

		// Embed.
		this.$embed = this.$postbox.find('.ps-js-video-embed');
		this.$embedUrl = this.$embed.find('.ps-js-url');
		this.$embedLoading = this.$embed.find('.ps-js-loading');
		this.$embedUrl.on(
			'input',
			_.debounce(e => this.embed(e.target.value), 2000)
		);
		this.$embedUrl.on('paste', e => _.defer(() => this.embed(e.target.value)));
		this.$embedUrl.on('blur', e => {
			// Prevent accidental click on upload button.
			this.$uploadBtn.off('click');
			_.defer(() => this.$uploadBtn.on('click', () => this.onUploadClick()));

			this.embed(e.target.value);
		});

		// Uploader.
		this.$upload = this.$postbox.find('.ps-js-video-upload');
		this.$uploadBtn = this.$upload.find('.ps-js-btn');
		this.$uploadForm = this.$upload.find('.ps-js-form');
		this.$uploadTitle = this.$upload.find('.ps-js-title');
		this.$uploadProgress = this.$upload.find('.ps-js-progress');
		this.$uploadDone = this.$upload.find('.ps-js-done');
		this.$uploadFailed = this.$upload.find('.ps-js-failed');
		this.$uploadSuccess = this.$upload.find('.ps-js-success');
		this.$uploadBtn.on('click', () => this.onUploadClick());

		// Separator.
		this.$separator = this.$postbox.find('.ps-js-video-separator');
		this.$preview = this.$postbox.find('.ps-js-video-preview');

		// Postbox video mode and data state.
		this.mode = null;
		this.data = null;

		// Handle input title
		this.$uploadTitle.on(
			'input',
			_.throttle(() => {
				this.$postbox.on_change();
			}, 500)
		);

		// Handle toggle submit button.
		observer.addFilter(
			'peepso_postbox_can_submit',
			(flags, $postbox) => {
				if (this.$postbox === $postbox && this.$postboxTab.current_tab_id === 'videos') {
					if ('embed' === this.mode) {
						flags.hard.push(this.data ? true : false);
					} else if ('upload' === this.mode) {
						let title = this.$uploadTitle.val().trim();
						flags.hard.push(this.data && title ? true : false);
					} else {
						flags.hard.push(false);
					}
				}
				return flags;
			},
			10,
			2
		);

		// Handle onsave
		observer.addFilter(
			'peepso_postbox_onsave',
			(fn, $postbox) => {
				if (this.$postbox === $postbox && this.$postboxTab.current_tab_id === 'videos') {
					if ('upload' === this.mode && !(this.data && this.data.is_audio)) {
						this.uploadShowNotice = true;
					}
				}
				return fn;
			},
			10,
			2
		);

		this.$postbox.on('postbox.post_saved', () => {
			if (this.uploadShowNotice) {
				delete this.uploadShowNotice;

				// Briefly show the success notice below the postbox after successfully uploading a video.
				let $notice = this.$uploadSuccess.clone();
				$notice.show().insertAfter(this.$postbox);
				$notice.delay(3000).fadeOut('slow', function () {
					$notice.remove();
				});
			}
		});

		if (true /* peepso.isWebdriver() */) {
			this.uploadInit();
			this.uploadInitialized = true;
		}
	}

	/**
	 * Switch UI to show postbox video form.
	 */
	show() {
		this.mode = null;
		this.$postboxVideoShortcut.addClass('active');
		this.$postboxVideo.show();
		this.$embed.show();
		this.$separator.show();
		this.$upload.show();
		this.$uploadBtn.show();
		this.$uploadForm.hide();
		this.$uploadSuccess.hide();
		this.$preview.hide();

		if (+this.config.upload_enable) {
			this.$postboxStatus.show();
		} else {
			this.$postboxStatus.show();
			this.mode = 'embed';
		}

		this.$postbox.on_change();
	}

	/**
	 * Switch UI to show video embed form.
	 */
	showEmbed() {
		this.mode = 'embed';
		this.$postboxStatus.show();
		this.$embed.show();
		this.$separator.hide();
		this.$upload.hide();
		this.$preview.show();
	}

	/**
	 * Swith UI to show video upload form.
	 */
	showUpload() {
		this.mode = 'upload';
		this.$postboxStatus.show();
		this.$embed.hide();
		this.$separator.hide();
		this.$upload.show();
		this.$uploadTitle.val('');
		this.$uploadBtn.hide();
		this.$uploadForm.show();
		this.$uploadProgress.show();
		this.$uploadDone.hide();
		this.$uploadFailed.hide();
		this.$preview.show();
	}

	/**
	 * Cancel creating video post.
	 */
	cancel() {
		this.mode = null;
		this.data = null;
		this.$postboxVideoShortcut.removeClass('active');
		this.$embedUrl.val((this._embedUrl = ''));
		this.$preview.empty();
		this.$postbox.on_change();
	}

	/**
	 * Finalize creating video post.
	 */
	post() {
		let filterName = 'postbox_req_' + this.$postbox.guid;

		observer.addFilter(filterName, this.postSetRequest, 10, 1, this);
		this.$postbox.save_post();
		observer.removeFilter(filterName, this.postSetRequest, 10);
	}

	postSetRequest(req) {
		if ('embed' === this.mode) {
			_.extend(req, {
				type: 'video',
				url: this.data.url
			});
		} else if ('upload' === this.mode) {
			_.extend(req, {
				type: 'video',
				video: this.data.file,
				video_title: this.$uploadTitle.val().trim()
			});
		}

		return req;
	}

	/**
	 * Handle embed url.
	 *
	 * @param {string} url
	 */
	embed(url = '') {
		url = url.trim();

		// Skip if url is not valid.
		if (!url.match(/^https?:\/\//i)) {
			return;
		}

		// Skip if url is not changed.
		if (url === this._embedUrl) {
			return;
		}
		this._embedUrl = url;

		// Abort previous video fetching.
		if (this.video) {
			try {
				this.video.fetchAbort();
			} catch (e) {}
		}

		// Fetch embed video information.
		this.showEmbed();
		this.$embedLoading.show();
		this.video = new Video(url);
		this.video
			.getHTML()
			.then(html => {
				this.$preview.html(html);
				this.video.getData().then(data => {
					this.data = data;
					this.$postbox.on_change();
				});
			})
			.catch(error => {
				this.$embedUrl.val((this._embedUrl = ''));
				this.show();
				dialog(error, { error: true }).show();
			})
			.finally(() => {
				this.$embedLoading.hide();
			});
	}

	onUploadClick(e) {
		if (!this.uploadInitialized) {
			this.uploadInit();
			this.uploadInitialized = true;
		}

		// Try to clear input value first if possible, to make sure
		// onchange event is triggered even if the same file is selected.
		try {
			this.$uploadFile[0].value = null;
		} catch (ex) {}

		this.$uploadFile.click();
	}

	/**
	 * Initialize video upload.
	 */
	uploadInit() {
		if (!this.$uploadFile) {
			this.$uploadFile = this.$upload.find('.ps-js-file');
			this.$uploadFile.psFileupload({
				replaceFileInput: false,
				dropZone: this.$uploadBtn,
				add: (e, data) => {
					let file = data,
						$progressbar = this.$uploadProgress.find('.ps-js-percent-bar'),
						$percent = this.$uploadProgress.find('.ps-js-percent');

					this.showUpload();
					$percent.html('');
					$progressbar.css({ transition: '', width: 1 });

					if (!this.beforeUnloadHandler) {
						this.beforeUnloadHandler = () => {
							return true;
						};
						observer.addFilter('beforeunload', this.beforeUnloadHandler);
					}

					this.video = new Video(file);
					this.video.on(
						'progress',
						_.throttle(
							percent => {
								let label = +percent,
									width = Math.min(100, +percent);

								// Consistently use 2 fractional digits.
								if (label > 0 && label < 100) {
									label = label.toFixed(2);
								}

								$percent.html(`${label}%`);
								$progressbar.css({
									transition: 'width 1s',
									width: `${width}%`
								});

								if (width === 100) {
									setTimeout(() => {
										this.$uploadProgress.hide();
									}, 1500);
								}
							},
							browser.isIOS() ? 1500 : 250
						)
					);

					this.video
						.getHTML()
						.then(() => {
							this.$uploadDone.show();
							this.video.getData().then(data => {
								if (this.beforeUnloadHandler) {
									observer.removeFilter('beforeunload', this.beforeUnloadHandler);
									this.beforeUnloadHandler = null;
								}

								this.data = data;
								this.$postbox.on_focus();
								this.$postbox.on_change();
							});
						})
						.catch(error => {
							this.show();

							if (!error) {
								error = 'Undefined error.';
							} else if (error.errors) {
								error = error.errors;
							}

							dialog(error).error();
						});
				}
			});
		}
	}
}
