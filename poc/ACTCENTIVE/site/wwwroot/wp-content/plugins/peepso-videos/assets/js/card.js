import $ from 'jquery';
import peepso, { Promise } from 'peepso';
import peepsodata, { media as mediaData } from 'peepsodata';

class Card {
	/** @type {jQuery} */ static $outer;
	/** @type {jQuery} */ static $inner;

	/**
	 * Initialize media card.
	 *
	 * @returns {jQuery}
	 * @private
	 */
	static create() {
		let $outer = Card.$outer,
			$inner = Card.$inner;

		if ( ! $outer ) {
			$inner = Card.$inner = $( '<div />' ).append( mediaData.templateCard );
			$inner.css( { position: 'absolute' } );

			$outer = Card.$outer = $( '<div />' ).css( {
				display: 'none',
				height: 1,
				overflow: 'visible',
				position: 'absolute',
				width: 1,
				// Card z-index must be above lightbox z-index to make it visible on the lightbox.
				zIndex: 100001
			} );
			$outer.on( 'mouseenter', function( e ) {
				e.stopPropagation();
				Card.show();
			} );
			$outer.on( 'mouseleave', function( e ) {
				e.stopPropagation();
				Card.hide();
			} );
			$outer.append( $inner ).appendTo( document.body );
		}

		return $outer;
	}

	/**
	 * Show media card next to an element.
	 *
	 * @param {Object|string} [data]
	 * @param {string} [data.cover]
	 * @param {Event} e
	 * @private
	 */
	static show( data, e ) {
		clearTimeout( Card.hideTimer );

		let $card = Card.create(),
			$inner = Card.$inner;

		// Show loading.
		if ( data === 'loading' ) {
			$card.find( '.ps-js-loading' ).show();
		}
		// Or, update content if needed.
		else if ( data ) {
			$card.find( '.ps-js-loading' ).hide();
			$card.find( '.ps-js-cover' ).css( { backgroundImage: `url(${ encodeURI( data.cover ) })` } );
			if ( data.artist ) {
				$card.find( '.ps-js-artist' ).html( data.artist ).show();
			} else {
				$card.find( '.ps-js-artist' ).hide();
			}
			if ( data.album ) {
				$card.find( '.ps-js-album' ).html( data.album ).show();
			} else {
				$card.find( '.ps-js-album' ).hide();
			}
		}

		$card.show();

		// Reposition the card if needed.
		if ( e && e.currentTarget && e.clientX ) {
			let $elem = $( e.currentTarget ),
				offset = $elem.offset(),
				isNarrow = window.innerWidth <= 480;

			// Set initial position to top-right.
			$inner.css( {
				top: '',
				left: 0,
				bottom: 0,
				right: isNarrow ? 0 : ''
			} );

			$card.css( {
				top: offset.top - $card.height(),
				left: isNarrow ? 0 : e.clientX,
				right: isNarrow ? 0 : '',
				width: isNarrow ? '' : 1
			} );

			let rect = $inner.get( 0 ).getBoundingClientRect();

			// Fix vertical hovercard position if it goes out of the viewport.
			if ( rect.top < 0 ) {
				$card.css( { top: offset.top + $elem.height() } );
				$inner.css( { bottom: '', top: 0 } );
			}

			// Fix horizontal hovercard position if it goes out of the viewport.
			if ( ! isNarrow ) {
				if ( rect.right > ( window.innerWidth || document.documentElement.clientWidth ) ) {
					$inner.css( { left: '', right: 0 } );
				}
			}
		}
	}

	/**
	 * Timer to hide media card.
	 *
	 * @type {number}
	 * @private
	 */
	static hideTimer;

	/**
	 * Hide media card.
	 *
	 * @private
	 */
	static hide() {
		let $card = Card.$outer;
		if ( $card ) {
			Card.hideTimer = setTimeout( function() {
				$card.hide();
			}, 200 );
		}
	}
}

/** @class */
class MediaCard {
	/**
	 * Store fetched information.
	 *
	 * @type {Object}
	 * @static
	 */
	static cache = {};

	/** @type {jQuery} */ $elem;
	/** @type {Object} */ opts;

	/**
	 * Initialize card on an element.
	 *
	 * @param {HTMLElement} elem
	 * @param {Object} data
	 */
	constructor( elem, opts ) {
		this.$elem = $( elem );
		this.opts = opts;

		this.$elem.on( 'mouseenter', e => {
			e.stopPropagation();
			this.show( e );
		} );

		this.$elem.on( 'mouseleave', e => {
			e.stopPropagation();
			this.hide();
		} );

		// Disable click on touch device.
		if ( peepso.isTouch() ) {
			this.$elem.on( 'click', e => {
				e.preventDefault();
				e.stopPropagation();
			} );
		}
	}

	/**
	 * Get card information.
	 *
	 * @returns {Promise<Object,undefined>}
	 */
	getData() {
		return new Promise( resolve => {
			let id = this.opts.artist + ':' + this.opts.album,
				data = MediaCard.cache[ id ];

			if ( data ) {
				resolve( data );
			} else {
				$.post(
					peepsodata.ajaxurl,
					{
						action: 'peepso_audio_album_info',
						artist: this.opts.artist,
						album: this.opts.album
					},
					json => {
						data = MediaCard.cache[ id ] = json;
						resolve( data );
					},
					'json'
				);
			}
		} );
	}

	/**
	 * Timer to show media card.
	 *
	 * @type {number}
	 */
	showTimer;

	/**
	 * Show media card.
	 *
	 * @param {Event} e
	 */
	show( e ) {
		this.showTimer = setTimeout( () => {
			let loadingTimer = setTimeout( () => {
				Card.show( 'loading', e );
			}, 500 );
			this.getData().then( data => {
				// Cancel loading timer.
				clearTimeout( loadingTimer );
				// Exit if the `hide` method is called during data fetching.
				if ( ! this.showTimer ) {
					return;
				}
				Card.show( data, e );
			} );
		}, 300 );
	}

	/**
	 * Hide media card.
	 */
	hide() {
		clearTimeout( this.showTimer );
		this.showTimer = null;
		Card.hide();
	}
}

export default MediaCard;

$( function() {
	// On a touch device, toggle card can be triggered with a tap.
	let evtName = peepso.isTouch() ? 'touchstart' : 'mouseenter';

	// Lazy-initialize media card on hover or tap event.
	$( document ).on( evtName, '[data-artist][data-album]', function( e ) {
		let $elem = $( e.currentTarget );

		// Skip if element is already initialized.
		if ( $elem.data( 'ps-mediacard' ) ) {
			return;
		}

		let artist = $elem.data( 'artist' ),
			album = $elem.data( 'album' ),
			card;

		if ( artist && album ) {
			card = new MediaCard( $elem[ 0 ], { artist, album } );
			$elem.data( 'ps-mediacard', card );
			card.show( e );
		} else {
			$elem.data( 'ps-mediacard', 'do_not_trigger' );
		}
	} );
} );
