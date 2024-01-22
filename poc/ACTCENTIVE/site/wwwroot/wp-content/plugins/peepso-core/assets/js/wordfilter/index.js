import { observer } from 'peepso';
import { wordfilter as config } from 'peepsodata';

const MASK_CHARACTER = config.mask;
const MASK_TYPE = +config.type;
const MASK_SHIFT = config.shift;
const FILTER_POSTS = +config.filter_posts;
const FILTER_COMMENTS = +config.filter_comments;
const FILTER_MESSAGES = +config.filter_messages;

// Build regular expression pattern.
const PATTERN = (function (keywords) {
	if (keywords instanceof Array && keywords.length) {
		keywords = keywords.map(str => shift(str.trim(), -MASK_SHIFT));
		return new RegExp(`(${keywords.join('|')})`, 'g');
	}
	return null;
})(config.keywords);

// Build selector matcher based on the config.
const SELECTOR_MATCHER = []
	.concat(FILTER_POSTS ? ['.ps-js-activity-content', '.ps-js-activity-quote'] : [])
	.concat(FILTER_COMMENTS ? ['.ps-js-comment-content'] : [])
	.concat(FILTER_MESSAGES ? ['.ps-js-conversation-content', '.ps-js-conversation-excerpt'] : [])
	.join(', ');

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

function shift(keyword = '', offset = 0) {
	if ('string' !== typeof keyword) {
		return keyword;
	}

	let newKeyword = '';

	for (let i = 0; i < keyword.length; i++) {
		let c = keyword[i];
		let islower = c >= 'a' && c <= 'z';
		let isupper = c >= 'A' && c <= 'Z';

		if (islower || isupper) {
			let code = c.charCodeAt(0) - (islower ? 97 : 65);
			// shift the character code
			code = code + offset;
			// normalize out-of-bound code
			code = code < 0 ? code + 26 : code % 26;
			// update character based on the new code
			c = String.fromCharCode(code + (islower ? 97 : 65));
		}

		newKeyword += c;
	}

	return newKeyword;
}

function filterContent(text) {
	return text.replace(PATTERN, match => {
		// Middle
		if (MASK_TYPE === 2 && match.length >= 3) {
			let mask = new Array(match.length - 1).join(MASK_CHARACTER);
			match = `${match[0]}${mask}${match[match.length - 1]}`;
		}
		// Full
		else {
			match = new Array(match.length + 1).join(MASK_CHARACTER);
		}

		return `<span class="ps-js-wordfilter">${match}</span>`;
	});
}

/**
 * Scan and replace matched text content with wordfilter character.
 *
 * @param {Element} rootElement
 */
function scanElement(rootElement) {
	let elements = [...rootElement.querySelectorAll(SELECTOR_MATCHER)],
		descendants = [];

	elements.forEach(element => {
		descendants = descendants.concat([...element.querySelectorAll('*')]);
	});

	elements = elements.concat(descendants);

	elements.forEach(element => {
		// Skip non-relevant elements.
		if (SKIP_TAGS.indexOf(element.tagName.toLowerCase()) > -1) {
			return;
		}

		[...element.childNodes].forEach(node => {
			// Skip non-text nodes.
			// https://developer.mozilla.org/en-US/docs/Web/API/Node/nodeType
			if (node.nodeType !== 3) {
				return;
			}

			// Skip empty text nodes.
			let text = node.textContent;
			if (!text.trim()) {
				return;
			}

			// Skip if it does not contain filtered keywords.
			if (!text.match(PATTERN)) {
				return;
			}

			// Generate nodes to replace the text node.
			let replacer = document.createElement('div');
			replacer.innerHTML = filterContent(text);

			// Replace original text with filtered text.
			[...replacer.childNodes].forEach(newNode => {
				node.parentNode.insertBefore(newNode, node);
			});
			node.parentNode.removeChild(node);
		});
	});
}

if (PATTERN && SELECTOR_MATCHER) {
	// Scan and replace every activity items added.
	observer.addFilter(
		'peepso_activity',
		$posts =>
			$posts.map(function () {
				if (this.nodeType === 1) {
					scanElement(this);
				}
				return this;
			}),
		20,
		1
	);

	// Scan and replace messages content.
	observer.addFilter(
		'messages_render',
		$elem =>
			$elem.map(function () {
				if (this.nodeType === 1) {
					scanElement(this);
				}
				return this;
			}),
		20,
		1
	);
}

// Fix mask character conflict with markdown parser.
const TEMP_CHARACTER = '·';

function mdPrecompile(html) {
	html = html.replace(/<span class="ps-js-wordfilter">.+?<\/span>/gi, function (match) {
		// Replace mask character with a temporary character to avoid conflict.
		match = match.replace(new RegExp('\\' + MASK_CHARACTER, 'g'), TEMP_CHARACTER);
		return match;
	});
	return html;
}

function mdPostcompile(html) {
	html = html.replace(/<span class="ps-js-wordfilter">.+?<\/span>/gi, function (match) {
		// Put the mask character back.
		match = match.replace(new RegExp('\\' + TEMP_CHARACTER, 'g'), MASK_CHARACTER);
		return match;
	});
	return html;
}

observer.addFilter('markdown_precompile', mdPrecompile, 10, 1);
observer.addFilter('markdown_postcompile', mdPostcompile, 10, 1);
