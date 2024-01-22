import $ from 'jquery';
import { hooks, observer, modules } from 'peepso';
import { is_admin as IS_ADMIN, currentuserid as LOGIN_USER_ID } from 'peepsodata';

/**
 * Decide human readable content from a post element.
 *
 * @param {HTMLElement} post
 * @returns {string}
 */
function getPostContent(post) {
	let $post = $(post);

	let $content = $post.find('.ps-js-post-body .ps-js-activity-background-text');
	if (!$content.length) {
		$content = $post.find('.ps-js-post-body .ps-js-activity-content');
	}

	let content = $content.get(0).innerText.trim();

	// Append extra informations to the content string.
	let extras = observer.applyFilters('human_friendly_extras', [], content, post);
	if (extras.length) {
		if (content) {
			extras.unshift(content);
		}
		content = extras.join('. ');
	}

	// Fallback to stream header text if the content is empty.
	if (!content) {
		$content = $post.find('.ps-js-post-header .ps-stream-action-title');
		if ($content.length) {
			content = $content.get(0).innerText.trim();
		}
	}

	// Replace newline with space.
	content = content.replace(/\r?\n/g, ' ');
	return content;
}

/**
 * Decide human readable content from a comment element.
 *
 * @param {HTMLElement} comment
 * @returns {string}
 */
function getCommentContent(comment) {
	let $comment = $(comment),
		$content = $comment.find('.ps-js-comment-body .ps-js-comment-content'),
		content = $content.get(0).innerText.trim(),
		extras = observer.applyFilters('human_friendly_extras', [], content, comment);

	// Append extra informations to the content string.
	if (extras.length) {
		if (content) {
			extras.unshift(content);
		}
		content = extras.join('. ');
	}

	// Replace newline with space.
	content = content.replace(/\r?\n/g, ' ');
	return content;
}

/**
 * Update human readable content of a post.
 *
 * @param {HTMLElement} post
 * @param {number} postId
 */
function maybeUpdatePost(post, postId) {
	let $post = $(post);

	if (!postId) {
		let $hidden = $post.find('.ps-js-post-body input[name=peepso_set_human_friendly]');
		if ($hidden.length) {
			postId = $hidden.val();
			$hidden.remove();
		}
	}

	if (+postId) {
		let canSubmit = +IS_ADMIN || +$post.data('author') === +LOGIN_USER_ID;
		if (canSubmit) {
			modules.post.setHumanReadable(+postId, getPostContent(post));
		}
	}

	let $comments = $post.find('.ps-js-comment-item');
	if ($comments.length) {
		$comments.each(function () {
			maybeUpdateComment(this);
		});
	}
}

hooks.addAction('post_added', 'human_readable', maybeUpdatePost);
hooks.addAction('post_updated', 'human_readable', function (post) {
	let postId = $(post).data('post-id');
	if (postId) {
		maybeUpdatePost(post, postId);
	}
});

/**
 * Update human readable content of a comment.
 *
 * @param {HTMLElement} comment
 * @param {number} commentId
 */
function maybeUpdateComment(comment, commentId) {
	let $comment = $(comment);

	if (!commentId) {
		let $hidden = $comment
			.find('.ps-js-comment-body')
			.eq(0)
			.find('input[name=peepso_set_human_friendly]');

		if ($hidden.length) {
			commentId = $hidden.val();
			$hidden.remove();
		}
	}

	if (+commentId) {
		let canSubmit = +IS_ADMIN || +$comment.data('author') === +LOGIN_USER_ID;
		if (canSubmit) {
			modules.post.setHumanReadable(+commentId, getCommentContent(comment));
		}
	}
}

hooks.addAction('comment_added', 'human_readable', maybeUpdateComment);
hooks.addAction('comment_updated', 'human_readable', function (comment) {
	let commentId = $(comment).data('comment-id');
	if (commentId) {
		maybeUpdateComment(comment, commentId);
	}
});

// Unused.
function init() {}

export default { init };
