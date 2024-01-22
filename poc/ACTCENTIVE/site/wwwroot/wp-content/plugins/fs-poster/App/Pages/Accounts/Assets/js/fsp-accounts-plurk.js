'use strict';

( function ( $ ) {
	$( '#fspGetAccessToken' ).on( 'click', function () {
		let appID = $( '#fspModalAppSelector' ).val().trim();
		FSPoster.ajax( 'get_plurk_authorization_link', { 'app': appID }, function (result) {
			window.open( result.link , '', 'width=750, height=550' );
			$('#plurkRequestToken').val(result.request_token.token);
			$('#plurkRequestTokenSecret').val(result.request_token.secret);
		} );

	} );

	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let requestToken = $('#plurkRequestToken').val().trim();
		let requestTokenSecret = $('#plurkRequestTokenSecret').val().trim();
		let appID = $( '#fspModalAppSelector' ).val().trim();
		let verifier = $( '#plurkVerifier' ).val().trim();
		let proxy = $( '#fspProxy' ).val().trim();

		FSPoster.ajax( 'add_new_plurk_account', { 'requestToken': requestToken, 'requestTokenSecret':requestTokenSecret, 'verifier':verifier , 'app': appID, proxy }, function (result) {
			accountAdded();
		} );
	} );
} )( jQuery );