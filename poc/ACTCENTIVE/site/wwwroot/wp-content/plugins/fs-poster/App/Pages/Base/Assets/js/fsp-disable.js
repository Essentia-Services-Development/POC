'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		doc.on( 'click', '#fspReactivateBtn', function () {
			let purchaseKey = $( '#fspPurchaseKey' ).val().trim();

			FSPoster.ajax( 'reactivate_app', { 'code': purchaseKey }, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success' );
				FSPoster.loading( true );

				window.location.reload();
			} );
		} );
	} );
} )( jQuery );