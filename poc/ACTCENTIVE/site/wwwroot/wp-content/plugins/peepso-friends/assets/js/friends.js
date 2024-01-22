import { ajax, hooks } from 'peepso';
import { currentuserid as LOGIN_USER_ID } from 'peepsodata';

/**
 * Send friend request to a community member.
 *
 * @param {number} userId
 * @returns {JQueryDeferred}
 */
function sendRequest(userId) {
	let params = { uid: LOGIN_USER_ID, user_id: userId };

	return ajax.post('friendsajax.send_request', params).then(json => {
		if (json.success) {
			hooks.doAction('friend_request_sent', userId, json.data);
		} else if (json.errors) {
			alert(json.errors);
		}
	});
}

/**
 * Cancel friend request sent to a community member.
 *
 * @param {number} userId
 * @param {number} requestId
 * @returns {JQueryDeferred}
 */
function cancelRequest(userId, requestId) {
	let params = { uid: LOGIN_USER_ID, user_id: userId, request_id: requestId, action: 'cancel' };

	return ajax.post('friendsajax.cancel_request', params).then(json => {
		if (json.success) {
			hooks.doAction('friend_request_canceled', userId, json.data);
		} else if (json.errors) {
			alert(json.errors);
		}
	});
}

/**
 * Accept friend request from a community member.
 *
 * @param {number} userId
 * @param {number} requestId
 * @returns {JQueryDeferred}
 */
function acceptRequest(userId, requestId) {
	let params = { uid: LOGIN_USER_ID, user_id: userId, request_id: requestId };

	return ajax.post('friendsajax.accept_request', params).then(json => {
		if (json.success) {
			hooks.doAction('friend_request_accepted', userId, json.data);
		} else if (json.errors) {
			alert(json.errors);
		}
	});
}

/**
 * Reject friend request from a community member.
 *
 * @param {number} userId
 * @param {number} requestId
 * @returns {JQueryDeferred}
 */
function rejectRequest(userId, requestId) {
	let params = { uid: LOGIN_USER_ID, user_id: userId, request_id: requestId, action: 'ignore' };

	return ajax.post('friendsajax.cancel_request', params).then(json => {
		if (json.success) {
			hooks.doAction('friend_request_rejected', userId, json.data);
		} else if (json.errors) {
			alert(json.errors);
		}
	});
}

/**
 * Remove friendship with a community member.
 *
 * @param {number} userId
 * @returns {JQueryDeferred}
 */
function remove(userId) {
	let params = { uid: LOGIN_USER_ID, user_id: userId };

	return ajax.post('friendsajax.remove_friend', params).then(json => {
		if (json.success) {
			hooks.doAction('friend_removed', userId, json.data);
		} else if (json.errors) {
			alert(json.errors);
		}
	});
}


export default {
	sendRequest,
	cancelRequest,
	acceptRequest,
	rejectRequest,
	remove
};
