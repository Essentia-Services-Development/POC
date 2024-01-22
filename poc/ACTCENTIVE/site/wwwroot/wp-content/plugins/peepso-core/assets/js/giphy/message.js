(function (root, factory) {
	var moduleName = 'PsGiphyMessage';
	var moduleObject = factory(moduleName, root.jQuery, require('./giphy.js'));

	// export module
	if (typeof module === 'object' && module.exports) {
		module.exports = moduleObject;
	} else {
		root[moduleName] = moduleObject;
	}

	// auto-initialize class
	new moduleObject();

	peepso.observer.addFilter(
		'giphy_rendition_posts',
		function (rendition, $postbox) {
			if ('messagesajax.add_message' === $postbox.opts.save_url) {
				rendition = window.peepsogiphydata.giphy_rendition_messages || 'fixed_width';
			}

			return rendition;
		},
		10,
		2
	);
})(window, function (moduleName, $, PsGiphy) {
	return peepso.createClass(moduleName, {
		/**
		 * Class constructor.
		 */
		__constructor: function () {
			$($.proxy(this.onDocumentLoaded, this));
		},

		/**
		 *
		 */
		onDocumentLoaded: function () {
			$(document).on(
				'click',
				'.ps-js-chat-window .ps-js-giphy-trigger',
				$.proxy(this.onToggle, this)
			);
		},

		/**
		 * Toggle giphy image selector chat input.
		 * @param {HTMLEvent} e
		 */
		onToggle: function (e) {
			var $btn = $(e.currentTarget),
				$container = $btn.siblings('.ps-js-giphy-container'),
				dataInitialized = 'ps-giphy-initialized',
				dataLoading = 'ps-giphy-loading';

			e.preventDefault();
			e.stopPropagation();

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
			$container.on('input', '.ps-js-giphy-query', $.proxy(this.onSearch, this));
			$container.on('click', '.ps-js-giphy-list img', $.proxy(this.onSelectImage, this));
			$container.on('click', '.ps-js-giphy-nav-left', e => this.onScroll(e, 'left'));
			$container.on('click', '.ps-js-giphy-nav-right', e => this.onScroll(e, 'right'));

			this.search($container).done(
				$.proxy(function () {
					$container.data(dataInitialized, true);
					$container.removeData(dataLoading);
					highlight($container.find('.ps-js-giphy-query'));
				}, this)
			);
		},

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
				rendition = peepsogiphydata.giphy_rendition_messages || 'fixed_width',
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
				$loading = $container.find('.ps-js-giphy-loading');

			$list.hide().css({ marginLeft: '', marginRight: '' });
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
				id = $container.closest('.ps-js-chat-window').data('id'),
				src = $img.attr('data-url');

			$container.hide();
			this.post(id, src);
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
		 * Post Giphy image as message once selected.
		 * @param {number} id
		 * @param {string} src
		 */
		post: function (id, src) {
			peepso.observer.doAction('msgso_send_message', id, '', {
				type: 'giphy',
				giphy: src
			});
		}
	});
});
