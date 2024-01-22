import $ from 'jquery';
import { hooks, util } from 'peepso';
import { readmore_min, readmore_min_single, activity, read_more_text } from 'peepsodata';
import linkTarget from './link-target';

const IS_PERMALINK = activity && +activity.is_permalink;
const READMORE_MIN = +readmore_min;
const READMORE_MIN_SINGLE = +readmore_min_single;
const READMORE_TEXT = read_more_text;

/**
 * Truncate post content and add readmore button if necessary.
 *
 * @param {HTMLElement} post
 */
function maybeReadMorePost(post) {
	let $post = $(post);
	let $content = $post.find('.ps-js-post-body .ps-js-activity-content');
	let contentLength = $content.text().trim().length;

	if (contentLength > READMORE_MIN) {
		let moreHtml = '<a href="#" class="ps-stream-post-more ps-js-content-excerpt-toggle"/>';
		let $more = $(moreHtml).html(READMORE_TEXT);
		if (contentLength > READMORE_MIN_SINGLE) {
			let moreHref = $post.find('.ps-js-post-header .ps-js-timestamp').attr('href');

			// Respects "open in new tab" setting.
			moreHtml = linkTarget.setLinkTarget($more.attr('href', moreHref).prop('outerHTML'));
			$more = $(moreHtml).attr('data-single-act', 1);
		}

		let $trimmed = $('<div class="ps-js-content-excerpt"/>')
			.html(util.trimHtml($content.html(), READMORE_MIN) + ' &hellip; ')
			.append($more);

		let $full = $('<div class="ps-js-content-full"/>')
			.html($content.html())
			.css('display', 'none');

		$content.empty().append($trimmed).append($full);
	}

	let $comments = $post.find('.ps-js-comment-item');
	if ($comments.length) {
		$comments.each(function () {
			maybeReadMoreComment(this);
		});
	}
}

/**
 * Truncate comment content and add readmore button if necessary.
 *
 * @param {HTMLElement} comment
 */
function maybeReadMoreComment(comment) {
	let $comment = $(comment);
	let $content = $comment.find('.ps-js-comment-body .ps-js-comment-content');
	let contentLength = $content.text().trim().length;

	if (contentLength > READMORE_MIN) {
		let $more = $('<a class="ps-stream-post-more ps-js-content-excerpt-toggle"/>')
			.attr('href', '#')
			.html(READMORE_TEXT);

		let $trimmed = $('<div class="ps-js-content-excerpt"/>')
			.html(util.trimHtml($content.html(), READMORE_MIN) + ' &hellip; ')
			.append($more);

		let $full = $('<div class="ps-js-content-full"/>')
			.html($content.html())
			.css('display', 'none');

		$content.empty().append($trimmed).append($full);
	}
}

// Do not truncate on single post view.
if (!IS_PERMALINK) {
	hooks.addAction('post_added', 'add_readmore', maybeReadMorePost);
	hooks.addAction('post_updated', 'add_readmore', function (post) {
		let postId = $(post).data('post-id');
		if (postId) {
			maybeReadMorePost(post, postId);
		}
	});

	hooks.addAction('comment_added', 'add_readmore', maybeReadMoreComment);
	hooks.addAction('comment_updated', 'add_readmore', function (comment) {
		let commentId = $(comment).data('comment-id');
		if (commentId) {
			maybeReadMoreComment(comment, commentId);
		}
	});
}

function init() {}

export default { init };
