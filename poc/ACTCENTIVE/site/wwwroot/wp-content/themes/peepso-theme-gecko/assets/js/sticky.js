jQuery(function ($) {
	/**
	 * List of registered elements.
	 *
	 * @type {Array.<HTMLElement>}
	 */
	var elems = [];

	/**
	 * List of registered element configurations.
	 *
	 * @type {Array.<Object>}
	 */
	var elemConfigs = [];

	/**
	 * Save last scroll position to determine scrolling direction.
	 *
	 * @type {number}
	 */
	var lastScrollTop;

	/**
	 * Save current window height.
	 * @type {number}
	 */
	var lastViewportHeight;

	/**
	 * Get current element style.
	 *
	 * @param {HTMLElement} elem
	 * @returns {Object}
	 */
	function getStyle(elem) {
		return {
			position: elem.style.position,
			top: elem.style.top,
			width: elem.style.width
		};
	}

	/**
	 * Update element style.
	 *
	 * @param {HTMLElement} elem
	 * @param {Object} style
	 */
	function updateStyle(elem, style) {
		elem.style.position = style.position;
		elem.style.top = style.top;
		elem.style.width = style.width;
	}

	/**
	 * Remove element style.
	 *
	 * @param {HTMLElement} elem
	 */
	function removeStyle(elem) {
		elem.style.position = '';
		elem.style.top = '';
		elem.style.width = '';
	}

	/**
	 * Stick/unstick element to the parent based on the condition.
	 */
	function maybeStick() {
		var clientWidth = window.innerWidth;
		var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
		var scrollDelta = scrollTop - lastScrollTop;

		// Update last scroll position.
		lastScrollTop = scrollTop;

		elems.forEach(function (elem, index) {
			var opts = elemConfigs[index];
			var parent = elem.parentElement;
			var offsetHeight = elem.offsetHeight;
			var offsetTopMax = opts.offset_top;
			var offsetBottom = opts.offset_bottom;
			var offsetWidth = opts.offset_width;

			var noStick = false;

			// Do not stick if the element is almost as wide as the document,
			// meaning that the UI is likely a mobile view.
			if (offsetWidth / clientWidth >= 0.85) {
				noStick = true;
			}
			// Also, do not stick if parent height is not taller than the element itself.
			// Add 10px threshold to make sure unexpected padding doesn't get in the way.
			else if (parent.offsetHeight - offsetHeight <= 10) {
				noStick = true;
			}
			// Also, do not stick if parent top is still below the top offset.
			else if (parent.getBoundingClientRect().top > offsetTopMax) {
				noStick = true;
			}

			if (noStick) {
				if ('fixed' === getStyle(elem).position) {
					requestAnimationFrame(function () {
						removeStyle(elem);
					});
				}
				return;
			}

			// Calculate difference between element height and available scrolling area.
			var offsetTopDelta = offsetHeight - (lastViewportHeight - offsetTopMax - offsetBottom);

			// Calculate minimum offset when element height is taller than the viewport.
			var offsetTopMin = offsetTopMax - Math.max(0, offsetTopDelta);

			// Calculate the position based on the scroll delta.
			var prevStyle = getStyle(elem);
			var style = Object.assign({}, prevStyle);
			if ('fixed' !== style.position) {
				style.position = 'fixed';
				style.width = offsetWidth + 'px';
				style.top = offsetTopMax + 'px';
			} else {
				style.width = offsetWidth + 'px';
				style.top = (parseInt(style.top) || 0) - scrollDelta;
				style.top = Math.min(offsetTopMax, Math.max(offsetTopMin, style.top)) + 'px';
			}

			// Only update if the style is actually changed.
			if (JSON.stringify(style) !== JSON.stringify(prevStyle)) {
				requestAnimationFrame(function () {
					updateStyle(elem, style);
				});
			}
		});
	}

	/**
	 * Recalculate elements width.
	 */
	function recalcWidth() {
		elems.forEach(function (elem, index) {
			var currentStyle = getStyle(elem);

			// Recalculate element width.
			removeStyle(elem);
			elemConfigs[index].offset_width = elem.offsetWidth;
			updateStyle(elem, currentStyle);
		});
	}

	/**
	 * Register event listeners.
	 */
	function attachListeners() {
		attachListeners = function () {};

		// Handle browser scroll event.
		var ua = navigator.userAgent;
		var isMobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(ua);
		var scrollTimer;
		window.addEventListener('scroll', function () {
			if (!isMobile) {
				maybeStick();
				return;
			}

			// Throttle scroll handler on mobile device.
			if (!scrollTimer) {
				scrollTimer = setTimeout(function () {
					maybeStick();
					scrollTimer = undefined;
				}, 1000);
			}
		});

		// Handle browser resize event.
		var lastViewportWidth = window.innerWidth;
		var resizeTimer;
		window.addEventListener('resize', function () {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function () {
				var updateStick = false;

				if (window.innerWidth !== lastViewportWidth) {
					lastViewportWidth = window.innerWidth;
					recalcWidth();
					updateStick = true;
				}
				if (window.innerHeight !== lastViewportHeight) {
					lastViewportHeight = window.innerHeight;
					updateStick = true;
				}

				if (updateStick) {
					maybeStick();
				}
			}, 500);
		});

		// Handle document resize event (if available). Note that this is not a window resize event
		// but rather a listener to a change in the document height caused by on-the-fly content changes
		// that might come from an ajax result.
		if ('function' === typeof ResizeObserver) {
			new ResizeObserver(function () {
				window.dispatchEvent(new Event('resize'));
			}).observe(document.body);
		}

		// Save initial scroll position.
		lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;

		// Save initial viewport height.
		lastViewportHeight = window.innerHeight;
	}

	/**
	 * Initialize listener.
	 *
	 * @param {HTMLElement} elem
	 * @param {Object} opts
	 */
	function initElement(elem, opts) {
		attachListeners();

		// Add element to the list of registered elements.
		elems.push(elem);
		elemConfigs.push(opts);
	}

	/**
	 * Create jQuery plugin as the interface.
	 *
	 * @param {Object} opts
	 */
	$.fn.gc_stick_in_parent = function (opts) {
		this.each(function () {
			initElement(this, Object.assign({}, opts || {}));
		});

		recalcWidth();

		if (lastScrollTop > 0) {
			maybeStick();
		}

		return this;
	};
});
