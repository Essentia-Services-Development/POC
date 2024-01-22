'use strict';

( function ( $ ) {
	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let token = $( '#fspBotToken' ).val().trim();
		let proxy = $( '#fspProxy' ).val().trim();

		FSPoster.ajax( 'add_telegram_bot', { 'bot_token': token, proxy }, function () {
			accountAdded();
		} );
	} );
} )( jQuery );