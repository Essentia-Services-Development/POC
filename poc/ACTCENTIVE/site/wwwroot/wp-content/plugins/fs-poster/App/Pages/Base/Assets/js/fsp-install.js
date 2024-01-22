'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		doc.on( 'click', '#fspInstallBtn', function () {
			let purchaseKey = $( '#fspPurchaseKey' ).val().trim();
			let email = $( '#fspEmail' ).val().trim();
			let marketingStatistics = $( '#fspMarketingStatistics' ).val();

			FSPoster.ajax( 'activate_app', {
				'code': purchaseKey,
				'statistic': marketingStatistics,
				'email': email
			}, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success' );
				FSPoster.loading( true );

				window.location.reload();
			} );
		} );
	} );
} )( jQuery );
