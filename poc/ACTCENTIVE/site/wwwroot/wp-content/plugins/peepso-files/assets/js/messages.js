import $ from 'jquery';
import { ajax } from 'peepso';
import { rest_url as REST_URL } from 'peepsodata';

const REST_URL_FILES = `${REST_URL}files`;

function onDeleteFile(e) {
	e.preventDefault();
	e.stopPropagation();

	let $file = $(e.target).closest('.ps-js-file');
	let id = $file.data('id');

	ajax.delete(REST_URL_FILES, { id }, -1).done(json => {
		if (json.success) {
			// Only delete file attachment if the content is not empty,
			// otherwise delete the entire message.
			let $message = $file.closest('.ps-js-message');
			let content = $message.find('.ps-js-conversation-content').text().trim();
			if (content) {
				$file.remove();
			} else {
				$message.remove();
			}
		}
	});
}

$(document)
	.on('click.ps-file', '.ps-js-chat-window-messages .ps-js-file-delete', onDeleteFile)
	.on('click.ps-file', '.ps-js-conversation-wrapper .ps-js-file-delete', onDeleteFile);
