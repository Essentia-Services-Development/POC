import $ from 'jquery';
import * as browser from './browser';
import observer from './observer';
import peepsodata from 'peepsodata';

const SSE_ENABLED = +peepsodata.sse;
const SSE_USER_ID = +peepsodata.currentuserid;
const SSE_URL = peepsodata.sse_url;
const SSE_DOMAINS = peepsodata.sse_domains;
const SSE_DELAY = +peepsodata.sse_backend_delay;
const SSE_TIMEOUT = +peepsodata.sse_backend_timeout;
const SSE_KEEPALIVE = +peepsodata.sse_backend_keepalive;
const SSE_WPNONCE = +peepsodata.sse_wpnonce;

let ls = window.localStorage,
	on = window.addEventListener,
	channel,
	token,
	dataQueue;

/**
 * Get current timestamp.
 *
 * @returns {number}
 */
function now() {
	return new Date().getTime();
}

/**
 * Get SSE token.
 *
 * @param {*} force
 * @returns {Promise}
 */
function getToken(force) {
	return new Promise((resolve, reject) => {
		if (!force) {
			token = token || ls.getItem('peepso_sse_token');
			if (token) {
				resolve(token);
				return;
			}
		}

		$.post({
			url: peepsodata.ajaxurl,
			data: { action: 'peepso_sse_token', _wpnonce: SSE_WPNONCE },
			dataType: 'json',
			error: e => {
				reject(e);
			},
			success: data => {
				if (data && data.sse_token) {
					token = data.sse_token;
					ls.setItem('peepso_sse_token', token);
					resolve(token);
				} else {
					reject();
				}
			}
		});
	});
}

/**
 * Establish SSE connection.
 */
function connect() {
	let url =
		SSE_URL +
		`?user_id=${SSE_USER_ID}` +
		`&delay=${SSE_DELAY}` +
		`&timeout=${SSE_TIMEOUT}` +
		`&keepalive=${SSE_KEEPALIVE}` +
		`&token=${token}`;

	let errorCount = 0,
		maxErrorCount = 10;

	let listener = function (e) {
		// Ensure the origin of messages is from the accepted domains.
		if (SSE_DOMAINS && SSE_DOMAINS.indexOf(e.origin) === -1) {
			return;
		}

		// Reset error counter when a message is received.
		errorCount = 0;

		try {
			let data = JSON.parse(e.data),
				event = data.event;

			switch (event) {
				case 'error_path_not_found':
				case 'error_invalid_user_id':
					disconnect();
					break;
				case 'error_invalid_token':
					disconnect();
					getToken('force').then(connect);
					break;
				case 'debug_start':
				case 'keepalive':
				case 'timeout':
					break;
				default:
					broadcastData(data);
					ls.setItem('peepso_sse', e.data);
					setTimeout(() => {
						ls.removeItem('peepso_sse');
					}, 100);
					break;
			}
		} catch (x) {}
	};

	channel = new EventSource(url, { withCredentials: true });
	channel.addEventListener('message', listener, false);

	// Stop reconnecting if it gets repeated error.
	channel.addEventListener(
		'error',
		e => {
			if (++errorCount >= maxErrorCount) {
				disconnect();
			}
		},
		false
	);
}

/**
 * Disconnect established SSE connection.
 */
function disconnect() {
	if (channel) {
		channel.close();
		channel = null;
	}
}

/**
 * Broadcast received SSE data efficiently.
 *
 * @param {*} data
 * @param {boolean} force
 */
function broadcastData(data, force = false) {
	if (!force && browser.isHidden()) {
		dataQueue = dataQueue || [];
		dataQueue.push(data);
		return;
	}

	observer.doAction('peepso_sse', data);
}

/**
 * Setup SSE connection relay.
 *
 * @returns {Promise}
 */
function setupConnectionRelay() {
	return new Promise(resolve => {
		on('storage', data => {
			let { key, newValue } = data;

			// Main tab dropped connection.
			if (key === 'peepso_sse_is_on' && !newValue) {
				setTimeout(setupConnection, 1000);
			}
			// Main tab relaying data.
			else if (key === 'peepso_sse' && newValue) {
				try {
					broadcastData(JSON.parse(newValue));
				} catch (x) {}
			}
		});

		// Check if SSE connection is already established.
		resolve(ls.getItem('peepso_sse_is_on') ? 'relay' : 'no_relay');
	});
}

/**
 * Setup SSE connection.
 */
function setupConnection() {
	setupConnectionRelay().then(status => {
		if (status !== 'relay') {
			getToken().then(() => {
				ls.setItem('peepso_sse_is_on', now());
				on('beforeunload', () => {
					disconnect();
					ls.removeItem('peepso_sse_is_on');
				});
				connect();
			});
		}

		// Flush queued data if the browser gets focus.
		observer.addAction('browser.active', () => {
			if (dataQueue && dataQueue.length) {
				while (dataQueue.length) {
					broadcastData(dataQueue.shift(), true);
				}
			}
		});
	});
}

if (SSE_ENABLED && SSE_USER_ID && typeof EventSource !== 'undefined') {
	setupConnection();
}

export default channel;
