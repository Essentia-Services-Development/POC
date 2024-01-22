import $ from 'jquery';
import { hooks } from 'peepso';

function initToggleNsfw(post) {
	let $post = $(post);

	$post.off('click.nsfw').on('click.nsfw', '.ps-js-post-nsfw span', function () {
		let $nsfw = $(this).closest('.ps-js-post-nsfw');
		let $body = $nsfw.closest('.ps-js-post-body');
		let $content = $body.children('.ps-js-activity-content');
		let $attachments = $body.children('.ps-js-activity-attachments');

		$content.removeClass('ps-post__content--nsfw');
		$attachments.removeClass('ps-post__attachments--nsfw');
		$nsfw.remove();

		hooks.doAction('nsfw_reveal', $attachments.get(0));
	});
}

hooks.addAction('post_added', 'nsfw', initToggleNsfw);
hooks.addAction('post_updated', 'nsfw', initToggleNsfw);
hooks.addAction('post_reload', 'nsfw', id => {
	$(`.ps-js-activity[data-post-id="${id}"]`).each(function () {
		initToggleNsfw(this);
	});
});

function init() {}

export default { init };
