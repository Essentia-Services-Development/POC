import $ from 'jquery';
import peepso, { Promise } from 'peepso';
import { vip as vipData, ajaxurl as ajaxUrl } from 'peepsodata';

/**
 * Use single popover element for all instances.
 *
 * @type {JQuery}
 * @private
 */
let $popover;

/**
 * Timer to cancel toggle popover.
 *
 * @type {number}
 * @private
 */
let timer;

/**
 * Reusable popover template.
 *
 * @type {Function}
 * @return {string}
 * @private
 */
let template = peepso.template( vipData.popoverTemplate );

/**
 * Initialize hovercard.
 *
 * @returns {JQuery}
 * @private
 */
function create() {
	$popover = $( '<div />' );
	$popover.addClass( 'ps-vip-dropdown__wrapper' );
	$popover.css( { display: 'none', position: 'absolute' } );
	$popover.appendTo( document.body );
	return $popover;
}

/**
 * Show popover next to an element.
 *
 * @param {Object|string} data
 * @param {HTMLElement} [el]
 * @private
 */
function show( data, el ) {
	clearTimeout( timer );
	$popover = $popover || create();
	$popover.html( template( data ) ).show();

	if ( el ) {
		let $el = $( el ),
			offset = $el.offset();

		$popover.css( {
			left: offset.left,
			top: offset.top + $el.height()
		} );
	}
}

/**
 * Hide popover.
 *
 * @private
 */
function hide() {
	if ( $popover ) {
		timer = setTimeout( function() {
			$popover.hide();
		}, 200 );
	}
}

/** @class */
class VIPPopover {
	/**
	 * Store fetched user information.
	 *
	 * @type {Object}
	 * @static
	 */
	static cache = {};

	/**
	 * Per-instance show popover delay timer.
	 *
	 * @type {number}
	 * @private
	 */
	timer;

	/**
	 * Initialize popover on an element.
	 *
	 * @param {HTMLElement} elem
	 * @param {number} id
	 */
	constructor( elem, id ) {
		this.id = id;
		this.$elem = $( elem );
		this.$elem.on( 'mouseenter', e => {
			e.stopPropagation();
			this.show( e );
		} );
		this.$elem.on( 'mouseleave', e => {
			e.stopPropagation();
			this.hide();
		} );

		// Remove title attribute to prevent showing default title.
		this.$elem.removeAttr( 'title' );
	}

	/**
	 * Get popover information of the element.
	 *
	 * @returns {Promise<Object,undefined>}
	 */
	getData() {
		return new Promise( ( resolve, reject ) => {
			let data = VIPPopover.cache[ this.id ];
			if ( data ) {
				resolve( data );
				return;
			}

			$.get( {
				url: ajaxUrl,
				dataType: 'json',
				data: {
					action: 'peepso_vip_user_icons',
					user_id: this.id
				}
			} ).done( json => {
				if ( json ) {
					data = VIPPopover.cache[ this.id ] = json;
					resolve( data );
				} else {
					reject();
				}
			} );
		} );
	}

	/**
	 * Show popover.
	 *
	 * @param {Event} [e]
	 */
	show( e ) {
		this.timer = setTimeout( () => {
			let timer = setTimeout( () => {
				show( 'loading', this.$elem.get( 0 ) );
			}, 500 );

			this.getData().then( data => {
				// Cancel loading timer.
				clearTimeout( timer );

				// In case the `hide` method is called during data fetching.
				if ( ! this.timer ) {
					return;
				}

				show( data, this.$elem.get( 0 ) );
			} );
		}, 300 );
	}

	hide() {
		clearTimeout( this.timer );
		this.timer = null;
		hide();
	}
}

export default VIPPopover;

/**


jQuery( function( $ ) {
	if ( ! vipData.popoverEnable ) {
		return;
	}

	var template = peepso.template( vipData.popoverTemplate ),
		cache = {},
		$popover,
		timer;

	function getData( user_id ) {

	}

/**
function show( e ) {
	var $el = $( e.target ),
		user_id = $el.data( 'id' );

	
	$el.removeAttr( 'title' );

	clearTimeout( timer );
	timer = setTimeout( function() {
		if ( ! $popover ) {
			$popover = $( '<div />' );
			$popover.addClass( 'ps-vip-dropdown__wrapper' );
			$popover.css( { display: 'none', position: 'absolute' } );
			$popover.appendTo( document.body );
		}

		getData( user_id ).done( function( data ) {
			var html = template( data ),
				offset = $el.offset();

			$popover
				.html( html )
				.show()
				.css( {
					left: offset.left + $el.width(),
					top: offset.top
				} );
		} );
	}, 500 );
}
*/

// 	function hide() {
// 		clearTimeout( timer );
// 		if ( $popover ) {
// 			$popover.hide();
// 		}
// 	}

// } );
