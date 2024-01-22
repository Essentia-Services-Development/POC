import $ from 'jquery';
import { observer } from 'peepso';
import FocusAreaFooter from './focus-area-footer';

/** @class */
export default class FocusArea {
	/** @type {JQuery} */ $container;
	/** @type {JQuery} */ $avatar;
	/** @type {JQuery} */ $footer;
	/** @type {FocusAreaFooter} */ footer;

	/**
	 * Initialize focus area.
	 *
	 * @param {HTMLElement} container
	 */
	constructor( container ) {
		this.$container = $( container );
		this.$avatar = this.$container.find( '.ps-js-focus-avatar-button' );
		this.$footer = this.$container.find( '.ps-focus__footer' );

		if ( this.$avatar.length ) {
			this.$avatar.on( 'click', e => this.onClickAvatar( e ) );
		}

		if ( this.$footer.length ) {
			this.footer = new FocusAreaFooter( this.$footer[ 0 ] );
		}

		// TODO Listen to `avatar_updated` action.
		observer.addAction( 'avatar_updated', data => this.onAvatarUpdated( data ), 10, 1 );
	}

	/**
	 * Handle the click event on change avatar button.
	 *
	 * @param {Event} e
	 */
	onClickAvatar( e ) {
		e.preventDefault();
		e.stopPropagation();
		observer.doAction( 'avatar_update_dialog' );
	}

	/**
	 * Handle updated avatar event.
	 *
	 * @param {*} data
	 */
	onAvatarUpdated( data ) {
		// TODO
	}
}
