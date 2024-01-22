import $ from 'jquery';
import {
	ajaxurl_legacy as AJAXURL_LEGACY,
	ajax_exception_text as AJAX_EXCEPTION_TEXT,
	rest_url as REST_URL,
	rest_nonce as REST_NONCE
} from 'peepsodata';

const MAX_CONCURRENT_REQUESTS = 8;

class Ajax {
	constructor() {
		this.counter = 0;
		this.priority = 0;
		this.requests = [];
		this.queue = [];
	}

	/**
	 * Adds an AJAX request data into a queue.
	 *
	 * @param {Object} requestData
	 * @return {Promise}
	 */
	addToQueue(requestData) {
		this.queue.push(requestData);
		this.queue.sort((a, b) => +a.priority - b.priority);

		// Gives time for queue sorting when multiple requests are added simultaneously.
		setTimeout(() => this.execQueue(), 1);
	}

	/**
	 * Executes AJAX request queue.
	 */
	execQueue() {
		if (!this.queue.length) {
			return;
		}

		let data = this.queue[0];

		if (this.requests.length >= MAX_CONCURRENT_REQUESTS) {
			// Allow queue bypass-ing by setting the priority to -1, which results
			// in ajax being executed immediately.
			if (-1 !== data.priority) {
				return;
			}
		}

		// Delay higher priority value requests to give time for the same
		// or lower priority value requests to run first.
		if (this.requests.length && data.priority > this.priority) {
			return;
		}

		this.queue.shift();
		this.priority = data.priority;

		let id = ++this.counter;

		let params = {
			url: data.url,
			type: data.method.toUpperCase(),
			data: data.data,
			dataType: 'json'
		};

		// Add required X-WP-Nonce header for REST requests.
		if (0 === params.url.indexOf(REST_URL)) {
			params.beforeSend = xhr => xhr.setRequestHeader('X-WP-Nonce', REST_NONCE);
		}

		let xhr = $.ajax(params)
			.done((json, status, xhr) => {
				// Handles session timeout.
				if (!data.url.match('auth.login') && json.session_timeout) {
					if (json.login_dialog) {
						peepsodata.login_dialog = json.login_dialog;
					}
					
					data.defer.reject(xhr, status);
					$(window).trigger('peepso_auth_required');
					return;
				}

				data.defer.resolve(json, status, xhr);
			})
			.fail((xhr, status, error) => {
				if (xhr.responseJSON) {
					// Handles non-200 response code.
					data.defer.resolve(xhr.responseJSON, status, xhr);
				} else if (xhr.responseText) {
					// Handles non-JSON response.
					data.defer.reject(xhr, status, AJAX_EXCEPTION_TEXT);
				} else {
					data.defer.reject(xhr, status);
				}
			})
			.always(() => {
				// Remove completed request from the request table.
				for (let i = this.requests.length - 1; i >= 0; i--) {
					if (this.requests[i].id === id) {
						this.requests.splice(i, 1);
						break;
					}
				}

				// Recursively calls current method when request is done to run the next item in queue
				// until it is empty.
				this.execQueue();
			});

		this.requests.push({ id, xhr });
	}

	/**
	 * Performs a GET request.
	 *
	 * @param {string} url
	 * @param {Object} data
	 * @param {number} priority
	 * @returns {JQueryDeferred}
	 */
	get(url, data = {}, priority = 10) {
		return $.Deferred(defer => {
			if (-1 === url.indexOf('/')) {
				url = `${AJAXURL_LEGACY}${url}`;
			}

			this.addToQueue({ method: 'get', url, data, priority, defer });
		});
	}

	/**
	 * Performs a POST request.
	 *
	 * @param {string} url
	 * @param {Object} data
	 * @param {number} priority
	 * @returns {JQueryDeferred}
	 */
	post(url, data = {}, priority = 10) {
		return $.Deferred(defer => {
			if (-1 === url.indexOf('/')) {
				url = `${AJAXURL_LEGACY}${url}`;
			}

			this.addToQueue({ method: 'post', url, data, priority, defer });
		});
	}

	/**
	 * Performs a DELETE request.
	 *
	 * @param {string} url
	 * @param {Object} data
	 * @param {number} priority
	 * @returns {JQueryDeferred}
	 */
	delete(url, data = {}, priority = 10) {
		return $.Deferred(defer => {
			if (-1 === url.indexOf('/')) {
				url = `${AJAXURL_LEGACY}${url}`;
			}

			this.addToQueue({ method: 'delete', url, data, priority, defer });
		});
	}
}

export default Ajax;

// Test case:
// $(() => {
// 	let ajax = new Ajax();
// 	ajax.post('https://www.kompas.com/', {});
// 	ajax.post('https://www.tempo.co/', {});
// 	ajax.get('https://www.detik.com/', {}, 20);
// 	ajax.post('https://www.kompasian.com/', {});
// 	ajax.get('https://www.cnnindonesia.com/', {}, 5)
// 		.done(json => console.log('Done!', json))
// 		.fail(error => console.log('Fail!', error))
// 		.always(() => console.log('Always!'));
// 	ajax.get('https://www.google.com/', {});
// });
