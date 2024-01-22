'use strict';

( function ( $ ) {
	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let selectedMethod = String( $( '.fsp-modal-option.fsp-is-selected' ).data( 'step' ) );

		if ( selectedMethod === '1' ) // app method
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
				openURL = `${ fspConfig.siteURL }/?fb_app_redirect=${ appID }&proxy=${ proxy }`;
			}

			window.open( openURL, 'fs-app', 'width=750, height=550' );
		}
		else if ( selectedMethod === '2' ) // cookie method
		{
			let cookie_c_user = $( '#fspModalStep_2 #fspCookie_c_user' ).val().trim();
			let cookie_xs = $( '#fspModalStep_2 #fspCookie_xs' ).val().trim();
			let proxy = $( '#fspProxy' ).val().trim();

			FSPoster.ajax( 'add_new_fb_account_with_cookie', { cookie_c_user, cookie_xs, proxy }, function () {
				accountAdded();
			} );
		}
	} );

	$( '.fsp-modal-footer > #fspModalUpdateCookiesButton' ).on('click', function (  ){
		let account_id = $( '#account_to_update' ).val().trim();
		let cookie_xs = $( '#fspCookie_xs' ).val().trim();
		let proxy = $( '#fspProxy' ).val().trim();

		FSPoster.ajax( 'update_fb_account_cookie', { account_id, cookie_xs, proxy }, function () {
			accountUpdated();
		} );
	});
} )( jQuery );