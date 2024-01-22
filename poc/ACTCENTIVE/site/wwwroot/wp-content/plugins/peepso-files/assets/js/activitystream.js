import $ from 'jquery';
import { ajax, observer } from 'peepso';
import { rest_url as REST_URL } from 'peepsodata';

const REST_URL_FILES = `${REST_URL}files`;

observer.addFilter(
	'peepso_activity',
	$posts => {
		$posts.on('click.ps-file', '.ps-js-file-delete', function (e) {
			e.preventDefault();
			e.stopPropagation();

			let $file = $(this).closest('.ps-js-file');
			let id = $file.data('id');

			ajax.delete(REST_URL_FILES, { id }, -1).done(json => {
				if (json.success) {
					if ('delete_activity' === json.action) {
						// Delete file attachment on comment.
						let $comment = $file.closest('.ps-js-comment-item');
						if ($comment.length) {
							// Only delete file attachment if the content is not empty,
							// otherwise delete the entire comment and its replies.
							let content = $comment.find('.ps-js-comment-content').text().trim();
							if (content) {
								$file.remove();
							} else {
								$comment.next('[class*="ps-js-comment-reply"]').remove();
								$comment.remove();
							}
						} else {
							$file.closest('.ps-js-activity').remove();
						}
					} else {
						$file.remove();
					}
				}
			});
		});

		return $posts;
	},
	20
);
