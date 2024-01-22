'use strict';

( function ( $ ) {
	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let proxy = $( '#fspProxy' ).val().trim();
		let selectedMethod = String( $( '.fsp-modal-option.fsp-is-selected' ).data( 'step' ) );

		if ( selectedMethod === '1' ) // app method
		{
			let openURL;

			if ( ! $( '#fspUseCustomApp' ).is( ':checked' ) )
			{
				openURL = `${ fspConfig.standartAppURL }&proxy=${ proxy }&encode=true`;
			}
			else
			{
				let appID = $( '#fspModalAppSelector' ).val().trim();
				openURL = `${ fspConfig.siteURL }/?twitter_app_redirect=${ appID }&proxy=${ proxy }`;
			}

			window.open( openURL, 'fs-app', 'width=750, height=550' );
		}
		else if ( selectedMethod === '2' ) // cookie method
		{
			let auth_token = $( '#fspModalStep_2 #twitterCookie' ).val().trim();

			FSPoster.ajax( 'add_twitter_account', { auth_token, proxy }, function () {
				accountAdded();
			} );
		}
	} );

	$( '#fspModalUpdateCookiesButton' ).on( 'click', function () {
		let id = $( '#account_to_update' ).val().trim();
		let auth_token = $( '#fspCookie_auth_token' ).val().trim();
		let proxy 			= $( '#fspProxy' ).val().trim();
		FSPoster.ajax( 'update_twitter_account_cookie', { id, auth_token, proxy }, function () {
			accountUpdated();
		} );
	} );
} )( jQuery );