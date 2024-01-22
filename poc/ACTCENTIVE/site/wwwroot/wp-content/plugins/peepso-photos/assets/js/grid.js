(function ($, factory) {
	var o = factory($);

	peepso.photoGrid = function () {
		o.update();
	};

	$(function () {
		peepso.photoGrid();

		$(window).on('load', function () {
			peepso.photoGrid();
		});

		$(document).on(
			[
				'ps_activitystream_loaded',
				'ps_activitystream_append',
				'peepso_post_edit_saved',
				'peepso_repost_shown',
				'peepso_repost_added'
			].join(' '),
			function (e) {
				if (e.type === 'ps_activitystream_append') {
					setTimeout(peepso.photoGrid, 3000);
				} else {
					peepso.photoGrid();
				}
			}
		);
	});
})(jQuery, function ($) {
	var CSS_CONFIG = [{}, {}, {}, {}, {}, {}],
		ORIENT_POTRAIT = 1,
		ORIENT_LANDSCAPE = 2,
		ORIENT_DEFAULT = 3;

	// Two images arrangement configuration.
	CSS_CONFIG[2][ORIENT_POTRAIT] = {
		float: ['left', 'left'],
		height: ['100%', '100%'],
		width: ['50%', '50%']
	};

	CSS_CONFIG[2][ORIENT_LANDSCAPE] = {
		float: ['', ''],
		height: ['50%', '50%'],
		width: ['100%', '100%']
	};

	CSS_CONFIG[2][ORIENT_DEFAULT] = {
		float: ['left', 'left'],
		height: ['50%', '50%'],
		width: ['50%', '50%']
	};

	// Three images arrangement configuration.
	CSS_CONFIG[3][ORIENT_POTRAIT] = {
		float: ['left', 'left', 'left'],
		height: ['100%', '50%', '50%'],
		width: ['66.6%', '33.3%', '33.3%']
	};

	CSS_CONFIG[3][ORIENT_LANDSCAPE] = {
		float: ['', 'left', 'left'],
		height: ['66.6%', '33.3%', '33.3%'],
		width: ['100%', '50%', '50%']
	};

	CSS_CONFIG[3][ORIENT_DEFAULT] = {
		float: ['', 'left', 'left'],
		height: ['50%', '50%', '50%'],
		width: ['100%', '50%', '50%']
	};

	// Four images arrangement configuration.
	CSS_CONFIG[4][ORIENT_POTRAIT] = {
		float: ['left', 'left', 'left', 'left'],
		height: ['100%', '33.3%', '33.3%', '33.3%'],
		width: ['66.6%', '33.3%', '33.3%', '33.4%']
	};

	CSS_CONFIG[4][ORIENT_LANDSCAPE] = {
		float: ['', 'left', 'left', 'left'],
		height: ['66.6%', '33.3%', '33.3%', '33.3%'],
		width: ['100%', '33.3%', '33.3%', '33.3%']
	};

	CSS_CONFIG[4][ORIENT_DEFAULT] = {
		float: ['left', 'left', 'left', 'left'],
		height: ['50%', '50%', '50%', '50%'],
		width: ['50%', '50%', '50%', '50%']
	};

	// Five (or more) images arrangement configuration.
	CSS_CONFIG[5][ORIENT_POTRAIT] = {
		float: ['left', 'left', 'left', 'left', 'left'],
		height: ['50%', '50%', '33.3%', '33.3%', '33.3%'],
		width: ['50%', '50%', '33.3%', '33.3%', '33.3%']
	};

	CSS_CONFIG[5][ORIENT_LANDSCAPE] = {
		float: ['left', 'left', 'left', 'left', 'left'],
		height: ['50%', '50%', '33.3%', '33.3%', '33.3%'],
		width: ['50%', '50%', '33.3%', '33.3%', '33.3%']
	};

	CSS_CONFIG[5][ORIENT_DEFAULT] = {
		float: ['left', 'left', 'left', 'left', 'left'],
		height: ['50%', '50%', '33.3%', '33.3%', '33.3%'],
		width: ['50%', '50%', '33.3%', '33.3%', '33.3%']
	};

	return {
		/**
		 * TODO: docblock
		 */
		update: function () {
			var $containers = $('[data-ps-grid=photos]');

			// Exclude already-initialized containers.
			$containers = $containers.not(function () {
				return $(this).data('ps-grid-initialized');
			});

			// Arrange photos.
			$containers.each(
				$.proxy(function (index, $item) {
					$item = $($item);
					$item.data('ps-grid-initialized', true);
					this._arrange($item);
				}, this)
			);
		},

		/**
		 * Arrange photos in a container.
		 * @param {jQuery} $container
		 */
		_arrange: function ($container) {
			var $items = $container.children('[data-ps-grid-item]'),
				$images = $items.find('img[src]'),
				maxWidth = 600,
				minWidth = 200,
				srcs;

			$container.css({
				position: 'relative',
				width: '100%',
				maxWidth: maxWidth,
				minWidth: minWidth,

				// Prevents ridiculously long image to disrupt UI.
				maxHeight: maxWidth * 2,
				overflow: 'hidden'
			});

			// Map image src.
			srcs = _.map($.makeArray($images), function (img) {
				return img.src;
			});

			this._loadImages(srcs).always(
				$.proxy(function () {
					var images, orientation, config;

					if ($items.length > 1) {
						images = $.makeArray($images);

						// Find the best orientation.
						if (this._isPotrait(images)) {
							orientation = ORIENT_POTRAIT;
						} else if (this._isLandscape(images)) {
							orientation = ORIENT_LANDSCAPE;
						} else {
							orientation = ORIENT_DEFAULT;
						}

						// Select predefined config based on images count and orientation.
						config = CSS_CONFIG[images.length][orientation];

						$items.each(function (index) {
							var $item = $(this),
								width = config.width[index],
								height = config.height[index],
								float = config.float[index];

							$item.css({ float: float, width: width, paddingTop: height });

							// Fit image based on container.
							_.defer(
								function ($item, width, height) {
									var $image = $item.find('img'),
										imgWidth,
										imgHeight;

									if (!$image.hasClass('ps-js-fitted')) {
										$image.addClass('ps-js-fitted');
										imgWidth = $image[0].naturalWidth || $image[0].width;
										imgHeight = $image[0].naturalHeight || $image[0].height;
										if (imgWidth / imgHeight > width / height) {
											$image.css({ width: 'auto', height: '100%' });
										} else {
											$image.css({ width: '100%', height: 'auto' });
										}
									}
								},
								$item,
								parseFloat(width),
								parseFloat(height)
							);
						});
					}

					// Show images.
					$items.show();
					$container.children('.ps-js-loading').remove();
				}, this)
			);
		},

		/**
		 * Determine if a photo should be marked as potrait.
		 * @param {HTMLElement|HTMLElement[]} images
		 * @return {Boolean}
		 */
		_isPotrait: function (images) {
			var potraitImages;

			images = _.isArray(images) ? images : [images];
			potraitImages = _.filter(images, function (image) {
				var width = image.naturalWidth || image.width,
					height = image.naturalHeight || image.height;

				return width / height < 0.75;
			});

			return potraitImages.length / images.length > 0.5;
		},

		/**
		 * Determine if a photo should be marked as landscape.
		 * @param {HTMLElement|HTMLElement[]} images
		 * @return {Boolean}
		 */
		_isLandscape: function (images) {
			var landscapeImages;

			images = _.isArray(images) ? images : [images];
			landscapeImages = _.filter(images, function (image) {
				var width = image.naturalWidth || image.width,
					height = image.naturalHeight || image.height;

				return height / width < 0.75;
			});

			return landscapeImages.length / images.length > 0.5;
		},

		/**
		 * Load single image.
		 * @param {String} url
		 * @return {jQuery.Deffered}
		 */
		_loadImage: function (url) {
			return $.Deferred(function (defer) {
				var image = new Image();

				image.onload = loaded;
				image.onerror = errored;
				image.onabort = errored;
				image.src = url;

				function loaded() {
					unbindEvents();
					defer.resolve(image);
				}

				function errored() {
					unbindEvents();
					defer.reject(image);
				}

				function unbindEvents() {
					image.onload = null;
					image.onerror = null;
					image.onabort = null;
				}
			}).promise();
		},

		/**
		 * Load multiple images.
		 * @param {String|String[]} urls
		 * @return {jQuery.Deffered}
		 */
		_loadImages: function (urls) {
			var defers = [];

			if (!_.isArray(urls)) {
				urls = [urls];
			}

			urls.forEach(
				$.proxy(function (url) {
					defers.push(this._loadImage(url));
				}, this)
			);

			return $.when.apply($, defers);
		}
	};
});
