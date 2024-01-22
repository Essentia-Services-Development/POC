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
				let appID = $( '#fspModalAppSelector' ).val().trim();
				openURL   = `${ fspConfig.siteURL }/?google_b_app_redirect=${ appID }&proxy=${ proxy }`;
			}

			window.open( openURL, 'fs-app', 'width=750, height=550' );
		}
		else
		{
			let cookie_sid = $( '#fspCookie_sid' ).val().trim();
			let cookie_hsid = $( '#fspCookie_hsid' ).val().trim();
			let cookie_ssid = $( '#fspCookie_ssid' ).val().trim();
			let cookie_sapisid = $( '#fspCookie_sapisid' ).val().trim();
			let proxy = $( '#fspProxy' ).val().trim();

			if ( cookie_sid === '' || cookie_hsid === '' || cookie_ssid === '' || cookie_sapisid === '' )
			{
				FSPoster.toast( fsp__( 'Please, enter your cookies!' ), 'warning' );

				return;
			}

			FSPoster.ajax( 'add_google_b_account', {cookie_sid, cookie_hsid, cookie_ssid, cookie_sapisid, proxy }, function () {
				accountAdded();
			} );
		}
	} );

	$( '.fsp-modal-footer > #fspModalUpdateCookiesButton' ).on('click', function (  ){
		let account_id = $( '#account_to_update' ).val().trim();
		let cookie_sid = $( '#fspCookie_sid' ).val().trim();
		let cookie_hsid = $( '#fspCookie_hsid' ).val().trim();
		let cookie_ssid = $( '#fspCookie_ssid' ).val().trim();
		let cookie_sapisid = $( '#fspCookie_sapisid' ).val().trim();
		let proxy = $( '#fspProxy' ).val().trim();

		if ( cookie_sid === '' || cookie_hsid === '' || cookie_ssid === '' )
		{
			FSPoster.toast( fsp__( 'Please, enter your cookies!' ), 'warning' );

			return;
		}

		FSPoster.ajax( 'update_google_b_cookie', {account_id, cookie_sid, cookie_hsid, cookie_ssid, cookie_sapisid, proxy }, function () {
			accountUpdated();
		} );
	});
} )( jQuery );