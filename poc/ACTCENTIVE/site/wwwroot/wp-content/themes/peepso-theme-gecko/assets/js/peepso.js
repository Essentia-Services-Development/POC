(function ($, peepso, geckopeepsodata) {
	const { observer } = peepso;
	const { text } = geckopeepsodata;

	const TRIM_LONG_PHOTO = +geckopeepsodata.trim_long_photo;
	const TRIM_LONG_PHOTO_HEIGHT = +geckopeepsodata.trim_long_photo_height;
	const TEXT_CLICK_TO_EXPAND = text.click_to_expand;

	/**
	 * Initialize necessary behavior on a post.
	 *
	 * @param {Element} post
	 */
	function initPost(post) {
		if (TRIM_LONG_PHOTO) {
			trimLongImage(post);
		}
	}

	/**
	 * Trim long vertical image.
	 *
	 * @param {Element} post
	 */
	function trimLongImage(post) {
		let $post = $(post);

		// Find unprocessed grid containers.
		let $containers = $post.find('[data-ps-grid=photos]').not(function () {
			return $(this).data('gc-processed');
		});

		$containers.each(function () {
			let $container = $(this).data('gc-processed', true);
			let $items = $container.find('[data-ps-grid-item]');

			// Only trim grid containers with single photo.
			if ($items.length !== 1) {
				return;
			}

			let $image = $items.find('img');
			if (!$image.length) {
				return;
			}

			_whenLoaded($image[0]).then(function () {
				let imgHeight = $image.height();
				let maxHeight = TRIM_LONG_PHOTO_HEIGHT;

				// Trim long vertical image if necessary.
				if (imgHeight > maxHeight) {
					$container.addClass('gc-post__gallery--single-trim');
					$container.css({ maxHeight: maxHeight });

					// Attach expander.
					$('<span class="gc-post__gallery--single-expand" />')
						.html(TEXT_CLICK_TO_EXPAND)
						.prependTo($container)
						.on('click', function () {
							$(this).parent().css({ maxHeight: '' });
							$(this).remove();
						});
				}
			});
		});
	}

	/**
	 * Wait until the image is fully loaded.
	 *
	 * @param {Element} img
	 * @returns {Promise}
	 */
	function _whenLoaded(img) {
		return new Promise(function (resolve) {
			let loops = 0;
			let maxLoops = 30;
			let timer = setInterval(function () {
				if (img.naturalHeight) {
					clearInterval(timer);
					setTimeout(resolve, 1000);
				} else if (++loops > maxLoops) {
					clearInterval(timer);
				}
			}, 1000);
		});
	}

	// Initialize on each activity item added.
	observer.addFilter('peepso_activity', $posts =>
		$posts.each(function () {
			if (this.nodeType === 1) {
				initPost(this);
			}

			return this;
		})
	);
})(jQuery, peepso, geckopeepsodata);
