import $ from 'jquery';
import _ from 'underscore';
import { observer } from 'peepso';
import { markdown as markdownData } from 'peepsodata';
import marked from 'marked';
import loadScript from 'load-script';
import { loadCSS as loadStyle } from 'fg-loadcss';
import 'truncate/jquery.truncate.js';

const NO_PARAGRAPH = +markdownData.no_paragraph;
const ENABLE_HEADING = +markdownData.enable_heading;

// https://marked.js.org/#/USING_ADVANCED.md
marked.setOptions({
	renderer: new marked.Renderer(),
	headerIds: false,
	gfm: true,
	breaks: true,
	smartLists: true
});

let initHighlight = () => {
	initHighlight = () => {};

	doHighlight();

	// Trigger highlight on every activity content.
	let filter = html => {
		doHighlight();
		return html;
	};
	observer.addFilter('peepso_activity_content', filter, 10, 1);
};

let loadHighlight = cb => {
	let noConflict = window.hljs,
		hljs;

	loadStyle(markdownData['highlight-css']);
	loadScript(markdownData['highlight-js'], function (err, script) {
		hljs = window.hljs;
		window.hljs = noConflict;
		cb(hljs);
	});

	// Short-circuit subsequent calls.
	loadHighlight = cb => {
		let loops = 0,
			maxLoops = 20,
			timer;

		if (hljs) {
			cb(hljs);
			return;
		}

		timer = setInterval(() => {
			if (hljs || ++loops > maxLoops) {
				clearInterval(timer);
				if (hljs) {
					cb(hljs);
				}
			}
		}, 1000);
	};
};

let doHighlight = _.debounce(() => {
	loadHighlight(hljs => {
		$('[data-ps-hljs]').each((i, code) => {
			$(code).removeAttr('data-ps-hljs');
			hljs.highlightBlock(code);
		});
	});
}, 2000);

const compileMarkdown = html => {
	let $wrapper = $('<div />').html(html),
		$markdown = $wrapper.find('.peepso-markdown');

	// Return original content if no markdown wrapper found.
	if (!$markdown.length) {
		return html;
	}

	$markdown.each(function () {
		let $html = $(this),
			$excerpt = $html.parent('.ps-js-content-excerpt'),
			$nested,
			html,
			excerptLength;

		// Skip if the content is already parsed before.
		if ($html.attr('data-ps-markdown') === 'parsed') {
			return true;
		}

		// Add attribute to flag content as parsed.
		$html.attr('data-ps-markdown', 'parsed');

		// Parse nested markdown separately.
		$nested = $html.find('.peepso-markdown');
		$nested.each(function (i, elem) {
			let text = document.createTextNode(`[NESTED_MD_${i}]`);
			elem.replaceWith(text);
		});

		// If an excerpt content is detected, replace the content with a full content version
		// so that it does not break markdown tags.
		if ($excerpt.length) {
			let $full = $excerpt.next('.ps-js-content-full');
			excerptLength = $html.html().length;
			$html.html($full.children('.peepso-markdown').html());
		}

		try {
			html = $html.html();
			// Trigger pre-compile hooks.
			html = observer.applyFilters('markdown_precompile', html);
			// Replace line-break tags with newline characters.
			html = html.replace(/<br(\s*\/)?>/gi, '\n');
			// Fix markdown link tag.
			html = html.replace(
				/\[([^\[\]]+)\]\((<a[^\(\)]+<\/a>)\)/gi,
				function (match, title, link) {
					link = link.replace(/>http[^<]+<\/a>/, `>${title}</a>`);
					return link;
				}
			);
			// Fix markdown quoted text.
			html = html.replace(/\n&gt; /gi, '\n> ');
			// Preserve multiple linebreaks. This one deviates from the standard markdown.
			html = html.replace(NO_PARAGRAPH ? /\n(?=\n)/g : /\n(?=\n\n)/g, '[PRESERVE_BR]');
			if (NO_PARAGRAPH) {
				// #5827 Fix listing rendering issue when using regular linebreak option.
				html = html.replace(
					/((?:^|\n)\s*(?:\*|\+|-|\d+\.)\s[\s\S]*?)\[PRESERVE_BR\]\n/gi,
					function (match, content) {
						return `${content}\n\n`;
					}
				);
				// #5988 Fix quote rendering issue when using regular linebreak option.
				html = html.replace(
					/((?:^|\n)>\s[\s\S]*?)\[PRESERVE_BR\]\n/gi,
					function (match, content) {
						return `${content}\n\n`;
					}
				);
			}
			// Fix text inside the code.
			html = html.replace(/`[^`]+`/gi, function (match) {
				// Revert preserved linebreak tags inside the code.
				match = match.replace(/\[PRESERVE_BR\]/g, '\n');
				// Remove link tags inside code as naturally HTML code is not working inside pre/code tag.
				match = match.replace(/<a[^>]+?>(?!<\/a>)(.+?)<\/a>/gi, '$1');
				match = match.replace(/<span.+?ps-stream-hashtag[^>]+>(.+?)<\/span>/gi, '$1');
				return match;
			});
			// Replace preserved linebreaks with BR tags.
			html = html.replace(/\[PRESERVE_BR\]/g, '\n&nbsp;');
			// Convert markdown tags to html.
			html = marked(html);
			// Fix "&" converted into "&amp;";
			html = html.replace(/&amp;/gi, '&');
			// Fix "&nbsp;<br>"
			html = html.replace(/&nbsp;<br>/g, '<br>');
			// Add highlight marker attribute.
			if (html.match(/<code/i)) {
				// Only highlight language-defined codes.
				let rCode = /(<code)([^>]+class="[^>]*lang(uage|))/gi;
				if (html.match(rCode)) {
					html = html.replace(rCode, '$1 data-ps-hljs="1"$2');
					initHighlight();
				}
			}
			// Respect enable heading config.
			if (!ENABLE_HEADING) {
				html = html.replace(/<(\/?)h[1-6]/gi, '<$1p');
			}
			// Trigger post-compile hooks.
			html = observer.applyFilters('markdown_postcompile', html);
		} catch (e) {}

		// Parse nested markdown separately.
		$nested.each(function (i, elem) {
			let nested = compileMarkdown(elem.outerHTML);
			html = html.replace(`[NESTED_MD_${i}]`, nested);
		});

		if (html) {
			// If an excerpt content is detected, truncate the content
			// to match with the original excerpt content length.
			if ($excerpt.length) {
				html = $.truncate(html, { length: excerptLength, ellipsis: '' });
			}

			// Apply markdown conversion.
			$html.html(html);
		}
	});

	return $wrapper.html();
};

// Activity content parsing filter.
observer.addFilter('peepso_activity_content', compileMarkdown, 10, 1);

// General content parsing filter.
observer.addFilter('peepso_parse_content', compileMarkdown, 10, 1);

// Try to find and parse unparsed markdown on page load.
$(() => {
	let $markdown = $('.peepso-markdown');
	$markdown.each(function () {
		let $elem = $(this),
			$wrapper = $('<div />').html($elem.clone());

		$wrapper.html(compileMarkdown($wrapper.html()));
		$elem.replaceWith($wrapper.find('.peepso-markdown'));
	});
});
