/**
 * User management module.
 *
 * @module user
 * @example
 * let { updateField } = peepso.modules.user;
 *
 * updateField( 99, 99, 'foobar' )
 *     .then( data => console.log( data ) )
 *     .catch( error => console.error( error ) );
 */
import { Promise } from 'peepso';
import { currentuserid as LOGIN_USER_ID } from 'peepsodata';

/**
 * Update the value of a user information field.
 *
 * @param {number} userId
 * @param {number} fieldId
 * @param {*} fieldValue
 * @returns {Promise.<Object,?string>}
 */
export function updateField( userId, fieldId, fieldValue ) {
	return new Promise( ( resolve, reject ) => {
		let endpoint = 'profilefieldsajax.savefield',
			transport,
			params,
			json;

		params = {
			user_id: LOGIN_USER_ID,
			view_user_id: userId,
			id: fieldId,
			value: fieldValue
		};

		transport = peepso.postJson( endpoint, params ).ret;
		transport
			.done( resp => ( json = resp ) )
			.always( () => {
				if ( json && json.success && ! json.errors ) {
					resolve( json.data );
					return;
				}

				let error = ( json && json.errors && json.errors[ 0 ] ) || undefined;
				reject( error );
			} );
	} );
}

/**
 * Update the visibility of a user information field.
 *
 * @param {number} userId
 * @param {number} fieldId
 * @param {number} visibility
 * @returns {Promise.<undefined,?string>}
 */
export function updateFieldVisibility( userId, fieldId, visibility ) {
	return new Promise( ( resolve, reject ) => {
		let endpoint = 'profilefieldsajax.save_acc',
			transport,
			params,
			json;

		params = {
			user_id: LOGIN_USER_ID,
			view_user_id: userId,
			id: fieldId,
			acc: visibility
		};

		transport = peepso.postJson( endpoint, params ).ret;
		transport
			.done( resp => ( json = resp ) )
			.always( () => {
				if ( json && json.success && ! json.errors ) {
					resolve();
					return;
				}

				let error = ( json && json.errors && json.errors.join( '\n' ) ) || undefined;
				reject( error );
			} );
	} );
}
