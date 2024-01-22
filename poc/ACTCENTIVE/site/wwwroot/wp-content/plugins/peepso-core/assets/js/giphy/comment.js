(function (root, factory) {
	var moduleName = 'PsGiphyComment';
	var moduleObject = factory(moduleName, root.jQuery, require('./giphy.js'));

	// export module
	if (typeof module === 'object' && module.exports) {
		module.exports = moduleObject;
	} else {
		root[moduleName] = moduleObject;
	}

	// auto-initialize class
	new moduleObject();
})(window, function (moduleName, $, PsGiphy) {
	return peepso.createClass(moduleName, {
		/**
		 * Class constructor.
		 */
		__constructor: function () {
			this.template = peepsogiphydata.dialogGiphyTemplate;

			peepso.observer.addFilter('comment_show_button', this.shouldShowButton, 10, 1);
			peepso.observer.addFilter('comment_can_submit', this.isSubmittable, 10, 1);
			peepso.observer.addFilter('comment_req', this.filterRequest, 10, 2);
			peepso.observer.addAction('comment_edit', this.onEdit, 10, 2);

			$($.proxy(this.onDocumentLoaded, this));
		},

		/**
		 *
		 */
		onDocumentLoaded: function () {
			$(document).on('click', '.ps-js-comment-giphy', $.proxy(this.onAttach, this));
			$(document).on(
				'click',
				'.ps-js-addon-giphy .ps-js-remove',
				$.proxy(this.onRemove, this)
			);
			$(document).on('ps_comment_aftersave', $.proxy(this.onSaved, this));
		},

		/**
		 * Toggle giphy image selector on comment.
		 * @param {HTMLEvent} e
		 */
		onAttach: _.throttle(function (e) {
			var dataInitialized = 'ps-giphy-initialized',
				dataLoading = 'ps-giphy-loading',
				isRTL = peepso.rtl,
				$button = $(e.currentTarget),
				$commentbox,
				$container;

			e.preventDefault();
			e.stopPropagation();

			// initialize dom
			$commentbox = $button.closest('.ps-js-comment-new, .ps-js-comment-edit');
			$container = $commentbox.prev('.ps-js-giphy-container');
			if (!$container.length) {
				$container = $(this.template);
				$container.data('act-id', $commentbox.data('id'));
				$container
					.find('.ps-js-giphy-list')
					.css('transition', `margin-${isRTL ? 'right' : 'left'} .5s`);
				$container.hide().insertBefore($commentbox);
				$container.on('input', '.ps-js-giphy-query', $.proxy(this.onSearch, this));
				$container.on('click', '.ps-js-giphy-list img', $.proxy(this.onSelectImage, this));
				$container.on('click', '.ps-js-giphy-nav-left', e => this.onScroll(e, 'left'));
				$container.on('click', '.ps-js-giphy-nav-right', e => this.onScroll(e, 'right'));
			}

			// hide container if it is currently visible
			if ($container.is(':visible')) {
				$container.hide();
				return;
			}

			// Focus and highlight the search box on show.
			function highlight($query) {
				$query.show();
				$query[0].focus();
				$query.css({ backgroundColor: peepso.getLinkColor() });
				$query.css({ transition: 'background-color 3s ease' });
				setTimeout(() => {
					$query.css({ backgroundColor: '' });
				}, 500);
			}

			$container.show();
			if ($container.data(dataInitialized) || $container.data(dataLoading)) {
				highlight($container.find('.ps-js-giphy-query'));
				return;
			}

			$container.data(dataLoading, true);
			this.search($container).done(
				$.proxy(function () {
					$container.data(dataInitialized, true);
					$container.removeData(dataLoading);
					highlight($container.find('.ps-js-giphy-query'));
				}, this)
			);
		}, 1000),

		/**
		 * Search giphy images based on keyword.
		 * @param {jQuery} $container
		 * @param {string} [keyword]
		 */
		search: function ($container, keyword) {
			return $.Deferred(
				$.proxy(function (defer) {
					var giphy = PsGiphy.getInstance(),
						$loading = $container.find('.ps-js-giphy-loading').show(),
						$list = $container.find('.ps-js-giphy-list').hide();

					giphy.search(keyword).done(
						$.proxy(function (data) {
							this.render($container, data);
							$loading.hide();
							$list.show();
							defer.resolveWith(this);
						}, this)
					);
				}, this)
			);
		},

		/**
		 * Renders gif images into specified container.
		 * @param {jQuery} $container
		 * @param {object[]} data
		 */
		render: function ($container, data) {
			var $list = $container.find('.ps-js-giphy-list'),
				$item = $container.find('.ps-js-giphy-list-item'),
				template = peepso.template($item.html()),
				rendition = peepsogiphydata.giphy_rendition_comments || 'fixed_width',
				html;

			html = _.map(data, function (item, index) {
				var images = item.images,
					src = images[rendition],
					html = '',
					preview;

				if (src) {
					preview =
						images.preview_gif ||
						images.downsized_still ||
						images.fixed_width_still ||
						images.original_still;

					if (preview) {
						$.extend(item, { src: src.url, preview: preview.url });
						html = template(item);
					}
				}

				return html;
			});

			$list.html(html.join(''));
		},

		/**
		 * Handle user search event.
		 * @param {HTMLEvent} e
		 */
		onSearch: function (e) {
			var $input = $(e.currentTarget),
				$container = $input.closest('.ps-js-giphy-container'),
				$list = $container.find('.ps-js-giphy-list'),
				$loading = $container.find('.ps-js-giphy-loading'),
				isRTL = peepso.rtl;

			$list.hide().css(isRTL ? 'marginRight' : 'marginLeft', 0);
			$loading.show();
			this._onSearch($container, $input);
		},

		_onSearch: _.debounce(function ($container, $input) {
			this.search($container, $.trim($input.val()));
		}, 1000),

		/**
		 * Handle user select image event.
		 * @param {HTMLEvent} e
		 */
		onSelectImage: function (e) {
			var $img = $(e.currentTarget),
				$container = $img.closest('.ps-js-giphy-container'),
				preview = $img.attr('src'),
				src = $img.attr('data-url');

			this.select($container, preview, src);
			$container.hide();
		},

		/**
		 * Scroll image listing to the left/right.
		 *
		 * @param {HTMLEvent} e
		 * @param {string} direction
		 */
		onScroll: function (e, direction) {
			var isRTL = peepso.rtl,
				$slider = $(e.currentTarget).closest('.ps-js-slider'),
				$list = $slider.find('.ps-js-giphy-list'),
				viewportWidth = $slider.width(),
				currentMargin = parseInt($list.css(isRTL ? 'marginRight' : 'marginLeft')) || 0,
				maxMargin;

			// Scroll left.
			if (direction === (isRTL ? 'right' : 'left')) {
				currentMargin = Math.min(currentMargin + viewportWidth, 0);
			}
			// Scroll right.
			else if (direction === (isRTL ? 'left' : 'right')) {
				var $lastItem = $list.children('.ps-js-giphy-item').last();
				if (isRTL) {
					maxMargin = Math.abs($lastItem.position().left);
				} else {
					maxMargin = $lastItem.position().left + $lastItem.width() - viewportWidth;
				}
				currentMargin -= Math.min(viewportWidth, maxMargin);
			}
			$list.css(isRTL ? 'marginRight' : 'marginLeft', currentMargin);
		},

		/**
		 * Attach gif image to the comment.
		 * @param {jQuery} $container
		 * @param {string} preview
		 * @param {string} src
		 */
		select: function ($container, preview, src) {
			var $addons = $container
					.siblings('.ps-js-comment-new, .ps-js-comment-edit')
					.find('.ps-js-addons'),
				$giphy = $addons.find('.ps-js-addon-giphy');

			$giphy.find('.ps-js-remove').show();
			$giphy.find('.ps-js-img').attr('src', preview).attr('data-url', src).show();
			$giphy.show();
			$(document).trigger('ps_comment_addon_added', $giphy);
		},

		/**
		 * Handle remove added giphy image on new comment box.
		 * @param {HTMLEvent} e
		 */
		onRemove: function (e) {
			var $giphy = $(e.currentTarget).closest('.ps-js-addon-giphy');

			$giphy.find('.ps-js-remove').hide();
			$giphy.find('.ps-js-img').attr('src', '').attr('data-url', '').hide();
			$giphy.hide();
			$(document).trigger('ps_comment_addon_removed', $giphy);
		},

		/**
		 * Should show post button if a Giphy image is attached
		 * @param {object} obj
		 * @return {object}
		 */
		shouldShowButton: function (obj) {
			var $ct = $(obj.el).closest('.ps-js-comment-new, .ps-js-comment-edit'),
				$giphy = $ct.find('.ps-js-addon-giphy'),
				$img;

			if ($giphy.is(':visible')) {
				$img = $giphy.find('.ps-js-img');
				if ($img.length && $img.attr('src')) {
					obj.show = true;
				}
			}

			return obj;
		},

		/**
		 * Check whether comment is submitable.
		 * @param {object} obj
		 * @return {object}
		 */
		isSubmittable: function (obj) {
			var $ct = $(obj.el).closest('.ps-js-comment-new, .ps-js-comment-edit'),
				$giphy = $ct.find('.ps-js-addon-giphy'),
				$img;

			if ($giphy.is(':visible')) {
				$img = $giphy.find('.ps-js-img');
				if ($img.length && $img.attr('src')) {
					obj.can_submit = true;
				}
			}

			return obj;
		},

		/**
		 * Filter request parameters on submit comment.
		 * @param {object} params
		 * @param {HTMLElement} elem
		 * @return {object}
		 */
		filterRequest: function (params, elem) {
			var $ct = $(elem).closest('.ps-js-comment-new, .ps-js-comment-edit'),
				$giphy = $ct.find('.ps-js-addon-giphy'),
				$img;

			if ($giphy.is(':visible')) {
				$img = $giphy.find('.ps-js-img');
				if ($img.length && $img.attr('data-url')) {
					params.giphy = $img.attr('data-url');
					// #3048 Remove url scheme.
					params.giphy = params.giphy.replace(/^[a-z]+:\/\//i, '');
				}
			}

			return params;
		},

		/**
		 * Handle task after comment is succesfully saved.
		 * @param {HTMLEvent} e
		 * @param {number} id
		 * @param {HTMLElement} elem
		 */
		onSaved: function (e, id, elem) {
			var $ct = $(elem).closest('.ps-js-comment-new, .ps-js-comment-edit'),
				$giphy = $ct.find('.ps-js-addon-giphy');

			$giphy.find('.ps-js-remove').hide();
			$giphy.find('.ps-js-img').attr('src', '').hide();
			$giphy.hide();
			$(document).trigger('ps_comment_addon_removed', $giphy);
		},

		/**
		 * Handle edit comment.
		 * @param {number} id
		 * @param {HTMLElement} elem
		 */
		onEdit: function (id, elem) {
			var $ct = $(elem),
				$giphy = $ct.find('.ps-js-addon-giphy'),
				$img = $giphy.find('.ps-js-img');

			if ($img.length && $img.attr('src')) {
				$giphy.find('.ps-js-remove').show();
				$giphy.find('.ps-js-img').show();
				$giphy.show();
				$(document).trigger('ps_comment_addon_added', $giphy);
			} else {
				$giphy.find('.ps-js-remove').hide();
				$giphy.find('.ps-js-img').hide();
				$giphy.hide();
				$(document).trigger('ps_comment_addon_removed', $giphy);
			}
		}
	});
});
