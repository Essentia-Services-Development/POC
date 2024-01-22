import $ from 'jquery';
import { debounce } from 'underscore';
import { observer } from 'peepso';
import { ajaxurl_legacy as AJAXURL_LEGACY } from 'peepsodata';

/**
 * Run applicable filters to the conversation messages.
 *
 * @param {string|JQuery} $messages
 * @returns {JQuery}
 */
export function filterMessages($messages) {
	// Filter-out non-element nodes.
	$messages = $($messages).filter(function () {
		return Node.ELEMENT_NODE === this.nodeType;
	});

	let $wrapper = $('<div/>').append($messages);

	$wrapper = observer.applyFilters('messages_render', $wrapper);
	observer.doAction('peepso_external_link', $wrapper);

	return $wrapper.children();
}

/**
 * Send a currently-typing request.
 *
 * @function currentlyTyping
 * @param {number} id
 */
let currentlyTypingXhr = {};
export const currentlyTyping = debounce(function (id) {
	let ajaxParams = {
		url: `${AJAXURL_LEGACY}messagesajax.i_am_typing`,
		type: 'POST',
		data: { msg_id: id },
		dataType: 'json'
	};

	currentlyTypingXhr[id] && currentlyTypingXhr[id].abort();
	currentlyTypingXhr[id] = $.ajax(ajaxParams).always(() => {
		delete currentlyTypingXhr[id];
	});
}, 500);

/**
 * Load asynchronous content of a HTML fragment.
 *
 * @param {string} html
 * @return {Promise}
 */
export function loadAsyncContents(html) {
	return new Promise(resolve => {
		let promises = [];

		if ('string' === typeof html) {
			let wrapper = document.createElement('div');

			wrapper.innerHTML = html;
			wrapper.querySelectorAll('.ps-media__attachment img[src]').forEach(img => {
				let promise = new Promise(resolve => {
					let tmp = new Image();
					tmp.onload = resolve;
					tmp.src = img.src;
				});

				promises.push(promise);
			});
		}

		if (promises.length) {
			Promise.all(promises).then(() => {
				setTimeout(resolve, 500);
			});
		} else {
			resolve();
		}
	});
}

/**
 * Register long polling for a particular conversation.
 *
 * @param {number} id
 */
export const longPolling = {
	immediate() {},
	start() {},
	stop() {}
};
