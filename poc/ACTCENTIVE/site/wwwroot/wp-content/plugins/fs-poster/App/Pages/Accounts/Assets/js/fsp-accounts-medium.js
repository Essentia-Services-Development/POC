'use strict';

( function ( $ ) {
	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let selectedMethod = String( $( '.fsp-modal-option.fsp-is-selected' ).data( 'step' ) );

		if ( selectedMethod === '1' )
		{
			let proxy = $( '#fspProxy' ).val().trim();
			let openURL;

			if ( ! $( '#fspUseCustomApp' ).is( ':checked' ) )
			{
				openURL = `${ fspConfig.standartAppURL }&proxy=${ proxy }&encode=true`;
			}
			else
			{
				let appID = $( '#fspModalStep_1 #fspModalAppSelector' ).val().trim();
				openURL   = `${ fspConfig.siteURL }/?medium_app_redirect=${ appID }&proxy=${ proxy }`;
			}

			window.open( openURL, 'fs-app', 'width=750, height=550' );
		}
		else if ( selectedMethod === '2' )
		{
			let access_token = $( '#fspModalStep_2 #fspMediumAccessToken' ).val();
			let proxy        = $( '#fspProxy' ).val().trim();

			FSPoster.ajax( 'add_new_medium_account_with_token', { access_token, proxy }, function () {
				accountAdded();
			} );
		}
	} );
} )( jQuery );