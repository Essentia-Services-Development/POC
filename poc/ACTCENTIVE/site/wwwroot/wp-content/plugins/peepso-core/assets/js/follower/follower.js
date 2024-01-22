import { ajax, hooks } from 'peepso';
import { currentuserid as LOGIN_USER_ID } from 'peepsodata';

/**
 * Follow a community member.
 *
 * @param {number} userId
 * @returns {JQueryDeferred}
 */
function follow(userId) {
	let params = { uid: LOGIN_USER_ID, user_id: userId, follow: 1 };

	return ajax.post('followerajax.set_follow_status', params).then(json => {
		if (json.success) {
			hooks.doAction('user_followed', userId, json.data);
		} else if (json.errors) {
			alert(json.errors);
		}
	});
}

/**
 * Unfollow a community member.
 *
 * @param {number} userId
 * @returns {JQueryDeferred}
 */
function unfollow(userId) {
	let params = { uid: LOGIN_USER_ID, user_id: userId, follow: 0 };

	return ajax.post('followerajax.set_follow_status', params).then(json => {
		if (json.success) {
			hooks.doAction('user_unfollowed', userId, json.data);
		} else if (json.errors) {
			alert(json.errors);
		}
	});
}

export { follow, unfollow };
