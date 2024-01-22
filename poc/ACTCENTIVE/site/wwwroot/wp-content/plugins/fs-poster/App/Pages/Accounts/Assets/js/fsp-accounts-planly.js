'use strict';

( function ( $ ) {
    $( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', () => {
        let token = ( $( '#fspAccessToken' ).val() || '' ).trim();
        let proxy = $( '#fspProxy' ).val().trim();

        FSPoster.ajax( 'add_planly_account', { 'access_token': token, proxy }, () => accountAdded() );
    } );
} )( jQuery );