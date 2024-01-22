import { hooks, observer } from 'peepso';
import peepsodata from 'peepsodata';

const hashtagsData = peepsodata.hashtags || {};

const HASHTAG_URL = hashtagsData.url;
const HASHTAG_EVERYTHING = +hashtagsData.everything || 0;
const HASHTAG_MIN_LENGTH = HASHTAG_EVERYTHING ? 0 : +hashtagsData.min_length || 0;
const HASHTAG_MAX_LENGTH = HASHTAG_EVERYTHING ? 10000 : +hashtagsData.max_length || 10000;
const HASHTAG_MUST_START_WITH_LETTER = HASHTAG_EVERYTHING
	? 0
	: +hashtagsData.must_start_with_letter || 0;

// Build hashtag pattern based on above configuration.
const HASHTAG_PATTERN = (() => {
	let startWithLetter, minLength, maxLength, pattern;

	if (HASHTAG_EVERYTHING) {
		pattern = '(^|>|\\s)(#([^#\\s<]+))';
	} else {
		startWithLetter = HASHTAG_MUST_START_WITH_LETTER;
		minLength = Math.max(0, HASHTAG_MIN_LENGTH - (startWithLetter ? 1 : 0));
		maxLength = Math.max(0, HASHTAG_MAX_LENGTH - (startWithLetter ? 1 : 0));

		pattern =
			'(^|>|\\s)(#(' +
			(startWithLetter ? '[a-z]' : '') +
			'[a-z0-9]{' +
			minLength +
			',' +
			maxLength +
			'}' +
			'))';
	}

	return new RegExp(pattern, 'ig');
})();

const SKIP_TAGS = [
	'a',
	'area',
	'audio',
	'base',
	'br',
	'button',
	'code',
	'col',
	'embed',
	'frame',
	'hr',
	'iframe',
	'img',
	'input',
	'keygen',
	'link',
	'meta',
	'param',
	'script',
	'select',
	'source',
	'style',
	'textarea',
	'track',
	'video',
	'wbr'
];

function filterContent(html) {
	if (html.match(HASHTAG_PATTERN)) {
		html = html.replace(HASHTAG_PATTERN, function (match, before, hashtag, label) {
			let newHtml = `${before}<a href="${HASHTAG_URL}${label}/"><span class="ps-stream-hashtag">${hashtag}</span></a>`;
			return newHtml;
		});
	}
	return html;
}

function scanElement(rootElement) {
	let elements = rootElement.querySelectorAll('*');

	// Add the root element to the element list.
	elements = [rootElement, ...elements];

	// Iterate through all elements.
	elements.forEach(element => {
		// Skip non-relevant elements.
		if (SKIP_TAGS.indexOf(element.tagName.toLowerCase()) > -1) {
			return;
		}

		let childNodes = [...element.childNodes];

		// Iterate through the element's childNodes.
		childNodes.forEach(node => {
			let text, replacer;

			// Skip non-text nodes.
			// https://developer.mozilla.org/en-US/docs/Web/API/Node/nodeType
			if (node.nodeType !== 3) {
				return;
			}

			// Skip empty text nodes.
			text = node.textContent;
			if (!text.trim()) {
				return;
			}

			// Skip if it does not contain mention tags.
			if (!text.match(HASHTAG_PATTERN)) {
				return;
			}

			// Generate nodes to replace the text node.
			replacer = document.createElement('div');
			replacer.innerHTML = filterContent(text);

			// Replace text node with new nodes.
			// https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/replaceWith
			node.replaceWith.apply(node, replacer.childNodes);

			// Update childNodes list.
			childNodes = childNodes.concat([...replacer.childNodes]);
		});
	});
}

function init() {
	// Do not run if hashtag feature is disabled.
	if (!hashtagsData.url) {
		return;
	}

	// Scan and replace every activity items added.
	observer.addFilter(
		'peepso_activity',
		$posts =>
			$posts.map(function () {
				if (this.nodeType === 1) {
					let contents = this.querySelectorAll(
						[
							'.ps-js-activity-content',
							'.ps-js-activity-extras',
							'.ps-js-activity-quote',
							'.ps-js-activity-background-text',
							'.ps-comment__content'
						].join(', ')
					);

					// Empty contents means that the whole element is the content
					// which usually comes from edit post/comment action.
					if (!contents.length) {
						contents = [this];
					}

					contents.forEach(scanElement);
				}

				return this;
			}),
		11,
		1
	);

	// Replace mention.
	hooks.addAction('post_updated', 'hashtag', function (post) {
		let postbg = post.querySelector('.ps-js-activity-background-text');
		if (postbg) {
			scanElement(postbg);
		}
	});

	// Build hashtag pattern for postbox.
	const HASHTAG_PATTERN_POSTBOX = (() => {
		let caret = '(?:\\u200D\\u200D)';
		// Safari still doesn't support lookbehind pattern, unfortunately.
		// https://caniuse.com/js-regexp-lookbehind
		let startSeparator = `((?:^|\\s|&nbsp;)${caret}?)`;
		let pattern;

		if (HASHTAG_EVERYTHING) {
			pattern = `${startSeparator}(#${caret}?(?:[^#\\s\\u200D]${caret}?)+)`;
		} else {
			let startWithLetter = HASHTAG_MUST_START_WITH_LETTER;
			let minLength = Math.max(0, HASHTAG_MIN_LENGTH - (startWithLetter ? 1 : 0));
			let maxLength = Math.max(0, HASHTAG_MAX_LENGTH - (startWithLetter ? 1 : 0));

			pattern = `${startSeparator}(#${caret}?(?:${
				startWithLetter ? `[a-z]${caret}?` : ''
			}(?:[a-z0-9]${caret}?){${minLength},${maxLength}})+)`;
		}

		return new RegExp(pattern, 'ig');
	})();

	/**
	 * In-place content transform hook.
	 *
	 * @param {Element} elem
	 */
	observer.addAction(
		'postbox_content_transform',
		(elem, editor) => {
			let pattern = HASHTAG_PATTERN_POSTBOX;

			// Reset existing highlight.
			[...elem.querySelectorAll('[data-hashtag]')].forEach(span => {
				let textNode = [...span.childNodes].find(node => node.nodeType === Node.TEXT_NODE);
				let prevNode = span.previousSibling;
				let nextNode = span.nextSibling;

				if (prevNode && prevNode.nodeType === Node.TEXT_NODE) {
					prevNode.textContent += textNode.textContent;
					span.remove();
					if (nextNode && nextNode.nodeType === Node.TEXT_NODE) {
						prevNode.textContent += nextNode.textContent;
						nextNode.remove();
					}
				} else if (nextNode && nextNode.nodeType === Node.TEXT_NODE) {
					nextNode.textContent = textNode.textContent + nextNode.textContent;
					span.remove();
				} else {
					span.replaceWith.apply(span, [textNode]);
				}
			});

			(function highlight(elem) {
				[...elem.childNodes].forEach(node => {
					if (Node.TEXT_NODE === node.nodeType) {
						let text = node.textContent;

						if (text.match(pattern)) {
							let replacement = document.createElement('div');

							replacement.innerHTML = text.replace(pattern, ($0, $1, $2) => {
								let separator = $1;
								let content = $2;
								let value = content.replace(/\u200D/gi, '');

								return `${separator}<span data-highlight data-hashtag data-value="${value}">${content}</span>`;
							});

							node.replaceWith.apply(node, replacement.childNodes);
						}
					} else if (Node.ELEMENT_NODE === node.nodeType) {
						highlight(node);
					}
				});
			})(elem);
		},
		10,
		2
	);

	// Render hashtags in blogpost.
	jQuery('.peepso-wp-post-hashtags').each(function () {
		scanElement(this);
	});
}

export default { init };
