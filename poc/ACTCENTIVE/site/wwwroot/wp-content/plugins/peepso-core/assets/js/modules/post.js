/**
 * Post management module.
 *
 * @module post
 * @requires module:request
 * @example
 * let { setHumanReadable } = peepso.modules.post;
 *
 * setHumanReadable( 99, 'lorem ipsum dolor sit amet' )
 *     .then( data => console.log( data ) )
 *     .catch( error => console.error( error ) );
 */
import { ajax, Promise } from 'peepso';
import { rest_url } from 'peepsodata';

/**
 * Set raw, tags-stripped, unformatted human-readable version of a post.
 *
 * @param {number} postId
 * @param {string} content
 * @returns {Promise.<Object,?string>}
 */
export function setHumanReadable(postId, content) {
	return new Promise((resolve, reject) => {
		let params = {
			post_id: postId,
			human_friendly: encodeURIComponent(content)
		};

		ajax.post('activity.set_human_friendly', params, 20)
			.done(json => {
				if (json.success && !json.error) {
					resolve();
				} else {
					let error = (json.errors && json.errors.join('\n')) || undefined;
					reject(error);
				}
			})
			.fail(reject);
	});
}

/**
 * Get or set saved state of a specific post.
 *
 * @param {number} id
 * @param {boolean|undefined} state
 * @returns {Promise.<Object,?string>}
 */
export function save(id, state) {
	return new Promise((resolve, reject) => {
		let endpoint = `${rest_url}post_save`,
			params = { post_id: id };

		if ('undefined' === typeof state) {
			ajax.get(endpoint, params, 20)
				.done(json => resolve(json))
				.fail(reject);
		} else if (!!state) {
			ajax.post(endpoint, params, 20)
				.done(json => resolve(json))
				.fail(reject);
		} else {
			ajax.delete(`${endpoint}/${id}`, null, 20)
				.done(json => resolve(json))
				.fail(reject);
		}
	});
}

/**
 * Get or set follow state of a specific post.
 *
 * @param {number} id
 * @param {boolean|undefined} state
 * @returns {Promise.<Object,?string>}
 */
 export function follow(id, state) {
	return new Promise((resolve, reject) => {
		let endpoint = `${rest_url}post_follow`,
			params = { post_id: id, follow: state };

		if ('undefined' === typeof state) {
			ajax.get(endpoint, params, 20)
				.done(json => resolve(json))
				.fail(reject);
		} else {
			ajax.post(endpoint, params, 20)
				.done(json => resolve(json))
				.fail(reject);
		}
	});
}

/**
 * Track view of a specific post.
 *
 * @param {number} actId
 * @returns {Promise.<Object,?string>}
 */
export function trackView(actId) {
	return new Promise(resolve => {
		let params = { act_id: actId };

		ajax.get('activity.add_view_count', params, 20).done(json => {
			resolve(json);
		});
	});
}
