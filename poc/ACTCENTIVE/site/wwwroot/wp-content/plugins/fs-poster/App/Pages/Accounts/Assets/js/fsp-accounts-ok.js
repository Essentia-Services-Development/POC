'use strict';

( function ( $ ) {
	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let proxy = $( '#fspProxy' ).val().trim();
		let openURL;

		if ( ! $( '#fspUseCustomApp' ).is( ':checked' ) )
		{
			openURL = `${ fspConfig.standartAppURL }&proxy=${ proxy }&encode=true`;
		}
		else
		{
			let appID = $( '#fspModalAppSelector' ).val().trim();
			openURL = `${ fspConfig.siteURL }/?ok_app_redirect=${ appID }&proxy=${ proxy }`;
		}

		window.open( openURL, 'fs-app', 'width=750, height=550' );
	} );
} )( jQuery );