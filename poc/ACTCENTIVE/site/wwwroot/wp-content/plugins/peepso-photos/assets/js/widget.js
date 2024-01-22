jQuery(function ($) {
	let itemSelector = '.ps-js-photo';

	window.ps_widget = {
		/**
		 * Checks if navigation should be hidden.
		 *
		 * @param {Element} el
		 * @returns {boolean}
		 */
		nonav(el) {
			let $item = $(el).closest(itemSelector);

			return $item.siblings(itemSelector).length < 1;
		},

		/**
		 * Navigate to the previous item.
		 *
		 * @param {Element} el
		 */
		prev(el) {
			let $item = $(el).closest(itemSelector),
				$prev = $item.prev(itemSelector);

			if (!$prev.length) {
				$prev = $item.siblings(itemSelector).last();
			}

			if ($prev.length) {
				$prev.children('a').click();
			}
		},

		/**
		 * Navigate to the next item.
		 *
		 * @param {Element} el
		 */
		next(el) {
			let $item = $(el).closest(itemSelector),
				$next = $item.next(itemSelector);

			if (!$next.length) {
				$next = $item.siblings(itemSelector).first();
			}

			if ($next.length) {
				$next.children('a').click();
			}
		}
	};
});
