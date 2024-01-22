import $ from 'jquery';
import { hooks, observer, modules } from 'peepso';

const MENTION_PATTERN = new RegExp(
	'@peepso_([a-z]+)_(\\d+)(?:\\((' +
		'[^\\(\\)]+' +
		'(?:\\([^\\(\\)]+\\)[^\\(\\)]*?)*?' +
		')\\))?',
	'g'
);

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

let replaceCount = 0;

function filterContent(html) {
	if (html.match(MENTION_PATTERN)) {
		html = html.replace(MENTION_PATTERN, function (mention, type, id, name) {
			let placeholderClass = `ps-tag__link ps-js-mention-${++replaceCount}`,
				placeholderHtml = `<a class="${placeholderClass}">${name || mention}</a>`;

			maybeDelayGetHtml(placeholderClass, [type, id, name]);

			return placeholderHtml;
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
			if (!text.match(MENTION_PATTERN)) {
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

function getHtml(elements, params) {
	let [type, id, name] = params;

	modules.mention.getHtml(type, id, name).then(html => {
		elements = [...document.getElementsByClassName(elements)];

		// Iterate through all elements.
		elements.forEach(element => {
			// Generate nodes to replace the text node.
			let replacer = document.createElement('div');
			replacer.innerHTML = html;
			// Replace link placeholder with an actual one.
			[...replacer.childNodes].forEach(node => {
				element.parentNode.insertBefore(node, element);
			});
			element.parentNode.removeChild(element);
		});
	});
}

let requestDelay = true;
let requestQueue = [];

function maybeDelayGetHtml(elements, params) {
	if (requestDelay) {
		requestQueue.push({ elements, params });
	} else {
		getHtml(elements, params);
	}
}

function init() {
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
		10,
		1
	);

	// Replace mention.
	hooks.addAction('post_updated', 'mention', function (post) {
		let postbg = post.querySelector('.ps-js-activity-background-text');
		if (postbg) {
			scanElement(postbg);
		}
	});

	$(() => {
		scanElement(document.body);

		// Delay fetching mention information to give time for more important Ajax requests.
		setTimeout(() => {
			requestDelay = false;

			// Execute queues.
			while (requestQueue.length) {
				let { elements, params } = requestQueue.shift();
				getHtml(elements, params);
			}
		}, 3000);
	});
}

let $dropdown;
function showMentionSelector(elem, editor, keyword) {
	if (!$dropdown) {
		$dropdown = $('<div class="ps-post__background-selector" />');
		$dropdown.appendTo(document.body);
	}

	let selection = window.getSelection();
	if (!selection.rangeCount) {
		return false;
	}

	let range = selection.getRangeAt(0),
		rangeTextNode = range.startContainer,
		rangeTextOffset = range.startOffset,
		rect = range.getClientRects()[0],
		scrollTop = document.documentElement.scrollTop || document.body.scrollTop,
		scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft,
		top = rect.top + scrollTop + 25,
		left = rect.left + scrollLeft;

	editor.disableInput = true;

	$dropdown.css({ top, left });
	$dropdown.show();
	$dropdown.off('click.ps-mention');
	$dropdown.on('click.ps-mention', function (e) {
		let $item = $(e.target).closest('[data-item]');
		if ($item.length) {
			e.preventDefault();
			e.stopPropagation();
			$item.addClass('active').siblings('[data-item]').removeClass('active');
			selectActive();
			setTimeout(() => (editor.disableInput = false), 400);
		}
	});

	$(elem).off('keydown.ps-mention');
	$(elem).on('keydown.ps-mention', function (e) {
		let key = e.keyCode;

		if (13 === key) {
			e.preventDefault();
			e.stopPropagation();
			selectActive();
			setTimeout(() => (editor.disableInput = false), 400);
		} else if (27 === key) {
			hideMentionSelector();
			setTimeout(() => (editor.disableInput = false), 400);
		} else if (38 === key) {
			e.preventDefault();
			e.stopPropagation();
			move('up');
		} else if (40 === key) {
			e.preventDefault();
			e.stopPropagation();
			move('down');
		}
	});

	function selectActive() {
		if ($dropdown && $dropdown.is(':visible')) {
			let $active = $dropdown.find('.active');
			if ($active.length) {
				let text = rangeTextNode.textContent,
					textBefore = text.substr(0, rangeTextOffset),
					textAfter = text.substr(rangeTextOffset);

				// Removes tag trigger string.
				textBefore = textBefore.replace(/@[^@]*$/, '');
				textAfter = textAfter.replace(/^[^\s]+/, '');

				let replacer = document.createElement('div');
				let mentionId = $active.data('id');
				let mentionName = $active.data('name');
				let mentionValue = `@peepso_user_${mentionId}(${mentionName})`;

				replacer.innerHTML = `${textBefore}<span data-highlight data-mention data-id="${mentionId}" data-name="${mentionName}" data-value="${mentionValue}">${mentionName}</span> \u200D${textAfter}`;

				let caretAtNode = replacer.childNodes[textBefore.length ? 2 : 1];

				// Replace text node with new nodes.
				// https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/replaceWith
				rangeTextNode.replaceWith.apply(rangeTextNode, replacer.childNodes);

				// Update caret position.
				let selection = window.getSelection();
				let range = document.createRange();
				range.setStart(caretAtNode, 1);
				range.collapse(true);
				selection.removeAllRanges();
				selection.addRange(range);

				hideMentionSelector();
			}
		}
	}

	function move(direction = 'down') {
		if ($dropdown) {
			let $active = $dropdown.find('.active');
			let $next = 'up' === direction ? $active.prev() : $active.next();
			if (!$next.length) {
				$next = 'up' === direction ? $active.siblings().last() : $active.siblings().first();
			}
			if ($next.length) {
				$active.removeClass('active');
				$next.addClass('active');
			}
		}
	}

	function fetchList() {
		modules.mention.getTargets().then(data => {
			let html = '';
			let index = 0;

			let excludes = [...elem.querySelectorAll('[data-mention]')];
			excludes = excludes.map(span => +span.getAttribute('data-id'));

			for (const id in data.users) {
				// Excludes users already mentioned.
				if (excludes.indexOf(+id) >= 0) {
					continue;
				}

				if (Object.hasOwnProperty.call(data.users, id)) {
					const user = data.users[id];
					let name = user.name;

					// Attempt to remove accents/diacritics from the name for better comparison.
					// https://stackoverflow.com/questions/990904/remove-accents-diacritics-in-a-string-in-javascript
					if (String.prototype.normalize) {
						name = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
					}

					if (name.toLowerCase().indexOf(keyword) >= 0) {
						html += `<a href="#" class="${
							index++ ? '' : 'active'
						}" data-item data-id="${user.id}" data-name="${
							user.name
						}" style="display: block; white-space: nowrap"><div class="ps-avatar"><img src="${
							user.avatar
						}"></div> <span>${user.name}</span></a>`;
					}
				}
			}

			html ? $dropdown.html(html) : $dropdown.hide();
		});
	}

	fetchList();
}

function hideMentionSelector(elem) {
	if ($dropdown) {
		$dropdown.hide();
	}
}

/**
 * Update mention element.
 *
 * @param {Element} span
 */
function updateMention(span) {
	let nameFull = span.getAttribute('data-name'); //.split(/\s+/);
	let namePartial = span.innerText; //.split(/\s+/);
	if (namePartial === nameFull) {
		return;
	}

	let nameFullParts = nameFull.split(/\s+/);
	let namePartialParts = namePartial.split(/\s+/);
	namePartialParts = namePartialParts.filter(str => nameFullParts.indexOf(str) >= 0);

	// Remove mention element if the all content is removed.
	if (!namePartialParts.length) {
		let prevText = span.previousSibling;
		let nextText = span.nextSibling;
		let caretText;
		let caretTextPos;

		if (prevText) {
			caretText = prevText;
			caretTextPos = prevText.textContent.length;
			if (nextText) {
				prevText.textContent += nextText.textContent;
				nextText.remove();
			}
		} else if (nextText) {
			caretText = nextText;
			caretTextPos = 0;
		}

		let container = span.parentElement;
		span.remove();

		// Update caret position if necessary.
		if (caretText) {
			let selection = window.getSelection();
			let range = document.createRange();
			range.setStart(caretText, caretTextPos);
			range.collapse(true);
			selection.removeAllRanges();
			selection.addRange(range);
		}

		if (!container.innerText.trim().length) {
			container.innerHTML = '';
			container.focus();
		}

		// Otherwise, just update the text.
	} else {
		let currText = span.childNodes[0];

		namePartial = namePartialParts.join(' ');
		currText.textContent = namePartial;

		// Update content.
		span.setAttribute(
			'data-value',
			`@peepso_user_${span.getAttribute('data-id')}(${namePartial})`
		);

		// Update caret position.
		let selection = window.getSelection();
		let range = document.createRange();
		range.setStart(currText, currText.textContent.length);
		range.collapse(true);
		selection.removeAllRanges();
		selection.addRange(range);
	}
}

/**
 * Content change hook.
 *
 * @param {Element} elem
 * @param {ContentEditable} editor
 */
observer.addAction(
	'postbox_content_change',
	(elem, editor) => {
		let selection = window.getSelection();
		if (!selection.rangeCount) {
			return false;
		}

		let range = selection.getRangeAt(0);
		let container = range.startContainer;

		if (container.nodeType === Node.TEXT_NODE) {
			// Update content if the caret is currently inside a mention element.
			let parent = container.parentElement;
			if (parent.hasAttribute('data-mention')) {
				updateMention(parent);
				return;
			}

			let text = container.textContent.substr(0, range.startOffset);
			let pattern = /@([a-z]+)$/i;
			let matches = text.match(pattern);

			if (matches) {
				showMentionSelector(elem, editor, matches.slice(1));
			} else {
				hideMentionSelector();
			}
		}
	},
	10,
	2
);

/**
 * Full content update hook.
 *
 * @param {Element} elem
 * @param {ContentEditable} editor
 */
observer.addAction(
	'postbox_content_update',
	(elem, editor) => {
		let textNodes = [...elem.childNodes].filter(node => node.nodeType === Node.TEXT_NODE);

		[...textNodes].forEach(node => {
			let html = node.textContent.replace(
				/@peepso_user_(\d+)\(([\s\S]*?)\)/g,
				function (match, p1, p2) {
					return `<span data-highlight data-mention data-id="${p1}" data-name="${p2}" data-value="${match}">${p2}</span>`;
				}
			);

			if (html !== node.textContent) {
				// Generate nodes to replace the text node.
				let replacer = document.createElement('div');
				replacer.innerHTML = html;

				// Replace text node with new nodes.
				// https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/replaceWith
				node.replaceWith.apply(node, replacer.childNodes);
			}
		});
	},
	10,
	2
);

export default { init };
