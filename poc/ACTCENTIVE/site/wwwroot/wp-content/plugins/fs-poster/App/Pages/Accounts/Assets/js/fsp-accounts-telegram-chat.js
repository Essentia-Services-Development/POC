'use strict';

( function ( $ ) {
	$( '#fspReloadChats' ).on( 'click', function () {
		let accountID = $( '#fspAccountID' ).val().trim();

		FSPoster.ajax( 'telegram_last_active_chats', { account: accountID }, function ( result ) {
			if(result[ 'list' ] === undefined || result[ 'list' ].length === 0){
				return;
			}

			let chatSelector = $( '#fspModalChatSelector' );

			chatSelector.html( `<option disabled selected>${ fsp__( 'Select chat' ) }</option>` );

			for ( let i in result[ 'list' ] )
			{
				chatSelector.append( `<option value="${ result[ 'list' ][ i ][ 'id' ] }">${ result[ 'list' ][ i ][ 'name' ] }</option>` );
			}
		} );
	} ).trigger( 'click' );

	$( '#fspModalChatSelector' ).on( 'change', function () {
		let chatID = $( this ).val().trim();

		$( '#fspChatID' ).val( chatID );
	} );

	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let accountID = $( '#fspAccountID' ).val().trim();
		let chatID = $( '#fspChatID' ).val().trim();

		FSPoster.ajax( 'telegram_chat_save', { 'account_id': accountID, 'chat_id': chatID }, function () {
			accountAdded();
		} );
	} );
} )( jQuery );