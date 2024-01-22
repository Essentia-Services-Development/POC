/**
 * Ajax request abstraction module.
 *
 * @module request
 * @example
 * let { get, post } = peepso.modules.request;
 *
 * // The first request will automatically be aborted since the second request has the same ID.
 * get( 'fooRequest', 'endPoint', { foo: 'bar' } )
 *     .then( data => console.log( data ) )
 *     .catch( error => console.error( error ) );
 *
 * get( 'fooRequest', 'endPoint', { foo: 'quux' } );
 *     .then( data => console.log( data ) )
 *     .catch( error => console.error( error ) );
 *
 * post( 'anotherRequest', 'endPoint', { foo: 'bar' } );
 *     .then( data => console.log( data ) )
 *     .catch( error => console.error( error ) );
 *
 * // Requests with falsy ID will not be aborted.
 * post( null, 'endPoint', { foo: 'bar' } )
 *     .then( data => console.log( data ) )
 *     .catch( error => console.error( error ) );
 *
 * post( null, 'endPoint', { foo: 'bar' } )
 *     .then( data => console.log( data ) )
 *     .catch( error => console.error( error ) );
 */

import $ from 'jquery';
import peepso from 'peepso';
import { rest_url, rest_nonce } from 'peepsodata';

/**
 * Ongoing ajax requests.
 *
 * @type {Object.<XMLHttpRequest>}
 * @private
 */
const requests = {};

/**
 * Perform a get request.
 *
 * @param {string|null} xhrId
 * @param {string} url
 * @param {Object} [data]
 * @returns {Promise}
 */
export function get(xhrId = null, url, data) {
	if (xhrId && requests[xhrId]) {
		requests[xhrId].abort();
	}

	return new Promise((resolve, reject) => {
		let xhr, json;

		// Use PeepSo ajax request method if the URL is not a valid path.
		if (-1 === url.indexOf('/')) {
			xhr = peepso.getJson(url, data);
		} else if (0 === url.indexOf(rest_url)) {
			xhr = $.ajax({
				type: 'GET',
				dataType: 'json',
				beforeSend: xhr => xhr.setRequestHeader('X-WP-Nonce', rest_nonce),
				url,
				data
			});
		} else {
			xhr = $.ajax({
				type: 'GET',
				dataType: 'json',
				url,
				data
			});
		}

		if (xhrId) {
			requests[xhrId] = xhr;
		}

		xhr.done(resp => (json = resp));
		xhr.always(() => {
			if (xhrId) {
				delete requests[xhrId];
			}

			if (json) {
				resolve(json);
			} else {
				reject();
			}
		});
	});
}

/**
 * Perform a post request.
 *
 * @param {string|null} xhrId
 * @param {string} url
 * @param {Object} [data]
 * @returns {Promise}
 */
export function post(xhrId = null, url, data) {
	if (xhrId && requests[xhrId]) {
		requests[xhrId].abort();
	}

	return new Promise((resolve, reject) => {
		let xhr, json;

		// Use PeepSo ajax request method if the URL is not a valid path.
		if (-1 === url.indexOf('/')) {
			xhr = peepso.postJson(url, data).ret;
		} else if (0 === url.indexOf(rest_url)) {
			xhr = $.ajax({
				type: 'POST',
				dataType: 'json',
				beforeSend: xhr => xhr.setRequestHeader('X-WP-Nonce', rest_nonce),
				url,
				data
			});
		} else {
			xhr = $.ajax({
				type: 'POST',
				dataType: 'json',
				url,
				data
			});
		}

		if (xhrId) {
			requests[xhrId] = xhr;
		}

		xhr.done(resp => (json = resp));
		xhr.always(() => {
			if (xhrId) {
				delete requests[xhrId];
			}

			if (json) {
				resolve(json);
			} else {
				reject();
			}
		});
	});
}

/**
 * Perform a delete request.
 *
 * @param {string|null} xhrId
 * @param {string} url
 * @param {Object} [data]
 * @returns {Promise}
 */
export function _delete(xhrId = null, url, data) {
	if (xhrId && requests[xhrId]) {
		requests[xhrId].abort();
	}

	return new Promise((resolve, reject) => {
		let xhr, json;

		if (0 === url.indexOf(rest_url)) {
			xhr = $.ajax({
				type: 'DELETE',
				dataType: 'json',
				beforeSend: xhr => xhr.setRequestHeader('X-WP-Nonce', rest_nonce),
				url,
				data
			});
		} else {
			xhr = $.ajax({
				type: 'DELETE',
				dataType: 'json',
				url,
				data
			});
		}

		if (xhrId) {
			requests[xhrId] = xhr;
		}

		xhr.done(resp => (json = resp));
		xhr.always(() => {
			if (xhrId) {
				delete requests[xhrId];
			}

			if (json) {
				resolve(json);
			} else {
				reject();
			}
		});
	});
}
