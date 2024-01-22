/**
 * Polls utility functions.
 *
 * @module polls
 */
import $ from 'jquery';
import peepso, { Promise } from 'peepso';
import { currentuserid as USER_ID } from 'peepsodata';

/**
 * Initialize uninitialized poll-type activities.
 */
export function initActivities() {
	$( '.ps-js-poll-item' ).each( function() {
		let $poll = $( this ),
			$options;

		if ( $poll.data( 'init-poll' ) ) return;
		$poll.data( 'init-poll', 1 );

		// Disable the submit button if no option is selected.
		$options = $poll.find( '.ps-js-poll-item-option' );
		$options.on( 'click', function() {
			let $poll = $( this ).closest( '.ps-js-poll-item' ),
				$options = $poll.find( '.ps-js-poll-item-option' ),
				$submit = $poll.find( '.ps-js-poll-item-submit' );

			if ( $options.filter( ':checked' ).length ) {
				$submit.removeAttr( 'disabled' );
			} else {
				$submit.attr( 'disabled', 'disabled' );
			}
		} );

		// Toggle submit button for the first time based on the options state.
		$options.eq( 0 ).triggerHandler( 'click' );
	} );
}

/**
 * Get vote editor HTML.
 *
 * @param {number} id
 * @returns {Promise.<string,?string>}
 */
export function getVoteEditorHTML( id ) {
	return new Promise( ( resolve, reject ) => {
		let endpoint = 'pollsajax.change_vote',
			params = { user_id: USER_ID, poll_id: id },
			transport,
			html,
			error;

		transport = peepso.postJson( endpoint, params ).ret;
		transport.done( json => {
			html = json.success && json.data && json.data.html;
			error = ! html && json.errors;
		} );
		transport.always( () => {
			if ( html ) {
				resolve( html );
			} else {
				reject( error );
			}
		} );
	} );
}

export function submitVote( id, polls, btn ) {
	return new Promise( ( resolve, reject ) => {} );
}

export function removeVote( id, btn ) {
	return new Promise( ( resolve, reject ) => {} );
}
