/** @module util */

import $ from 'jquery';

/* global FB:false */
export default {
	/**
	 * Checks whether an element is fully visible in the viewport.
	 *
	 * @param {HTMLElement} el
	 * @returns {boolean}
	 */
	isElementInViewport(el) {
		let rect = el.getBoundingClientRect();

		return (
			rect.top >= 0 &&
			rect.left >= 0 &&
			rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
			rect.right <= (window.innerWidth || document.documentElement.clientWidth)
		);
	},

	/**
	 * Checks whether an element is partly visible in the viewport.
	 *
	 * @param {HTMLElement} el
	 * @returns {boolean}
	 */
	isElementPartlyInViewport(el) {
		let rect = el.getBoundingClientRect();

		return (
			rect.top < (window.innerHeight || document.documentElement.clientHeight) &&
			rect.left < (window.innerWidth || document.documentElement.clientWidth) &&
			rect.bottom > 0 &&
			rect.right > 0
		);
	},

	/**
	 * Scroll element into the top of the viewport.
	 *
	 * @param {HTMLElement} el
	 * @param {Object} opts
	 */
	scrollIntoView(el, opts = {}) {
		el = $(el);
		if (el.length && !this.isElementInViewport(el[0])) {
			let duration = opts.duration || 1000,
				position = opts.position || 'top',
				scrollTop = el.offset().top;

			if ('center' === position) {
				scrollTop = Math.max(0, scrollTop - window.innerHeight / 2 + el.height() / 2);
			} else {
				scrollTop = Math.max(0, scrollTop - 10);
			}

			$('html, body').animate({ scrollTop }, duration);
		}
	},

	/**
	 * Scroll element into the top of the viewport if it is not already visible.
	 *
	 * @param {HTMLElement} el
	 * @param {Object} opts
	 */
	scrollIntoViewIfNeeded(el, opts = {}) {
		el = $(el);
		if (el.length && !this.isElementPartlyInViewport(el[0])) {
			let duration = opts.duration || 1000,
				position = opts.position || 'top',
				scrollTop = el.offset().top;

			if ('center' === position) {
				scrollTop = Math.max(0, scrollTop - window.innerHeight / 2 + el.height() / 2);
			} else {
				scrollTop = Math.max(0, scrollTop - 10);
			}

			$('html, body').animate({ scrollTop }, duration);
		}
	},

	/**
	 * Load Facebook Javascript SDK.
	 *
	 * @return {JQuery.Deferred}
	 */
	fbLoadSDK() {
		let js,
			fjs,
			timer,
			count,
			d = document,
			s = 'script',
			id = 'facebook-jssdk';

		return $.Deferred(function (defer) {
			if (d.getElementById(id)) {
				count = 0;
				timer = setInterval(function () {
					if (++count > 20 || window.FB) {
						clearInterval(timer);
						if (window.FB) {
							defer.resolve();
						}
					}
				}, 1000);
				return;
			}

			// Set callback handler.

			window.fbAsyncInit = function () {
				FB.init({
					version: 'v2.9', // https://developers.facebook.com/docs/apps/changelog/#versions
					status: false,
					xfbml: false
				});
				defer.resolve(FB);
				delete window.fbAsyncInit;
			};

			// Attach script to the document.
			fjs = d.getElementsByTagName(s)[0];
			js = d.createElement(s);
			js.id = id;
			js.src = '//connect.facebook.net/en_US/sdk.js';
			fjs.parentNode.insertBefore(js, fjs);
		});
	},

	/**
	 * Parse Facebook XFBML tags found in the document.
	 */
	fbParseXFBML() {
		let unrenderedLength = $('.fb-post, .fb-video').not('[fb-xfbml-state=rendered]').length;

		// Parse unrendered XFBML tag.
		if (unrenderedLength) {
			this.fbLoadSDK().done(function () {
				FB.XFBML.parse();
			});
		}
	},

	/**
	 * Copy string to a clipbroad.
	 *
	 * @param {string} str
	 */
	copyToClipboard(str) {
		const el = document.createElement('textarea');

		el.value = str;
		document.body.appendChild(el);
		el.select();
		document.execCommand('copy');
		document.body.removeChild(el);
	},

	/**
	 * Utility function to trim html content into a specific length.
	 *
	 * @param {string} html
	 * @param {number} length
	 * @returns {string}
	 */
	trimHtml(html, length) {
		/**
		 * Trim text content.
		 *
		 * @param {Element} element
		 * @param {number} length
		 * @returns {Object}
		 */
		function trimmer(element, length = 0) {
			[...element.childNodes].forEach(node => {
				// Skip unrelated nodetypes.
				// https://developer.mozilla.org/en-US/docs/Web/API/Node/nodeType
				if (![Node.ELEMENT_NODE, Node.TEXT_NODE].find(t => t === node.nodeType)) {
					return;
				}

				// Remove node when the maximum length is reached.
				if (length <= 0) {
					node.remove();
					return;
				}

				// On element node, recursively calls the trimmer function.
				if (Node.ELEMENT_NODE === node.nodeType) {
					// Handle empty element nodes correctly.
					// https://developer.mozilla.org/en-US/docs/Glossary/Empty_element
					if ('BR' === node.tagName) {
						length--;
						return;
					}

					let result = trimmer(node, length);

					// Update the length after trimmed.
					length = result.length;

					// Remove the element if it is empty after trimmed.
					if (!node.childNodes.length) {
						node.remove();
					}
				}

				// On text node, do text trimming if necessary.
				if (Node.TEXT_NODE === node.nodeType) {
					let text = node.textContent;

					// Just update the remaining allocated length if text's length is still shorter than that.
					if (text.length <= length) {
						length -= text.length;
					}
					// Otherwise, trim the text.
					else {
						// Safe to trim the text since the immediate character after trimmed length is a whitespace.
						if (text[length].match(/\s/)) {
							text = text.slice(0, length);
						}
						// Otherwise, we are likely to cut word during trimming. In this case, we should also remove
						// the last word being cut.
						else {
							text = text.slice(0, length);

							// Remove the last word being cut.
							let lastSpaceIndex = text.lastIndexOf(' ');
							if (lastSpaceIndex === -1) {
								text = '';
							} else {
								text = text.slice(0, Math.min(text.length, lastSpaceIndex));
							}
						}

						// Replace content with trimmed text.
						if (text.length) {
							node.textContent = text;
						} else {
							node.remove();
						}

						// Update remaining allocated length to 0 as we consider subsequent texts to be removed.
						length = 0;
					}
				}
			});

			return { element, length };
		}

		// Create a temporary HTML container.
		let element = document.createElement('div');
		element.innerHTML = html;

		let result = trimmer(element, length);

		return result.element.innerHTML;
	},

	/**
	 * Decides whether a string is in RTL.
	 * Using Unicode property escapes feature built into JavaScript's regular expression.
	 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions/Unicode_Property_Escapes
	 *
	 * @param {string} str
	 * @return {boolean}
	 */
	isRTL(str) {
		// List of RTL scripts are based on the current scripts listed here:
		// https://en.wikipedia.org/wiki/Right-to-left_script#Current_scripts
		let rtlScripts = [
			/\p{Script=Arabic}/u,
			/\p{Script=Hebrew}/u,
			/\p{Script=Thaana}/u,
			/\p{Script=Syriac}/u,
			/\p{Script=Mandaic}/u,
			/\p{Script=Samaritan}/u,
			/\p{Script=Mende_Kikakui}/u,
			/\p{Script=Nko}/u,
			/\p{Script=Adlam}/u,
			/\u10D00-\u10D3F/, // Hanifi_Rohingya
			/\u10E80-\u10EBF/ // Yezidi
		];

		let firstChar = str.trim().charAt(0);

		let rtlChar = false;
		for (let i = 0; i < rtlScripts.length; i++) {
			if (rtlScripts[i].test(firstChar)) {
				rtlChar = true;
				break;
			}
		}

		return rtlChar;
	}
};
