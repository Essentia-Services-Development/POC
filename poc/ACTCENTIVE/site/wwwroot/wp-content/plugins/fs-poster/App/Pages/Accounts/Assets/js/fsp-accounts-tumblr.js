'use strict';

( function ( $ ) {
	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let selectedMethod = String( $( '.fsp-modal-option.fsp-is-selected' ).data( 'step' ) );
		let proxy = $( '#fspProxy' ).val().trim();

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

				if ( ! ( appID > 0 ) )
				{
					FSPoster.toast( fsp__( 'Please, select an application!' ), 'warning' );

					return;
				}

				openURL = `${ fspConfig.siteURL }/?tumblr_app_redirect=${ appID }&proxy=${ proxy }`;
			}

			window.open( openURL, 'fs-app', 'width=750, height=550' );
		}
		else if ( selectedMethod === '2' ) // cookie method
		{
			let email = $( '#fspModalStep_2 #tumblrEmail' ).val().trim();
			let password = $( '#fspModalStep_2 #tumblrPass' ).val().trim();

			FSPoster.ajax( 'add_tumblr_account', { email, password, proxy }, function () {
				accountAdded();
			} );
		}
	} );
} )( jQuery );