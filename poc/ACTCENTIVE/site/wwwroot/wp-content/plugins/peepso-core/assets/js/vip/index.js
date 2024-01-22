import $ from 'jquery';
import peepso, { observer } from 'peepso';
import { vip as vipData } from 'peepsodata';
import VIPPopover from './popover';

$( function( $ ) {
	let template = peepso.template( vipData.hovercardTemplate );

	// Handle VIP data injection into hovercard.
	observer.addAction(
		'hovercard_update_html',
		function( $ct, data ) {
			let $card = $ct.find( '.ps-hovercard' ).removeClass( 'ps-hovercard--full' ),
				$cover = $card.find( '.ps-js-cover' ),
				$vip = $cover.siblings( '.ps-js-vip' );

			if ( $vip.length ) {
				$vip.remove();
			}

			if ( _.isObject( data.vip ) && ! _.isArray( data.vip ) ) {
				$card.addClass( 'ps-hovercard--full' );
				$vip = $( template( data ) ).addClass( 'ps-js-vip' );
				$vip.insertAfter( $cover );
			}
		},
		10,
		2
	);

	// Handle toggle VIP popover.
	if ( vipData.popoverEnable ) {
		// Lazy-initialize popover on mouseenter event.
		$( document ).on( 'mouseenter', '.ps-js-vip-badge', function( e ) {
			let $elem = $( e.currentTarget );

			// Skip if element is already initialized.
			if ( $elem.data( 'ps-vip' ) ) {
				return;
			}

			let userid = $elem.data( 'id' ),
				popover = new VIPPopover( $elem[ 0 ], userid );

			$elem.data( 'ps-vip', popover );
			popover.show( e );
		} );
	}
} );
