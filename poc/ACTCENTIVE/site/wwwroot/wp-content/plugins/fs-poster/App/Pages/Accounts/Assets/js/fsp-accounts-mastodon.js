'use strict';

( function ( $ ) {
	let appId;
	let proxy;

	$( '#fspUseCustomApp' ).trigger( 'click' ).attr( 'disabled', 'disabled' );
	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		proxy   = $( '#fspProxy' ).val().trim();
		appId   = $( '#fspModalAppSelector' ).val().trim();

		let openURL = `${ fspConfig.siteURL }/?mastodon_app_redirect=${ appId }&proxy=${ proxy }`;

		window.open( openURL, 'fs-app', 'width=750, height=550' );
	} );
} )( jQuery );