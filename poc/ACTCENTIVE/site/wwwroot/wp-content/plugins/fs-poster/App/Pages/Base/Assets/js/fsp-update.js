'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		doc.on( 'click', '#fspUpdateBtn', function () {
			let purchaseKey = $( '#fspPurchaseKey' ).val().trim();

			FSPoster.ajax( 'update_app', { 'code': purchaseKey }, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success' );
				FSPoster.loading( true );

				window.location.reload();
			} );
		} );
	} );
} )( jQuery );
