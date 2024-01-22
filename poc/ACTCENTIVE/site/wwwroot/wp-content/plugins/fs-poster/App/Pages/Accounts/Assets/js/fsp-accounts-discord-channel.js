'use strict';

( function ( $ ) {
    let accountID = $( '#fspAccountID' ).val().trim();

    FSPoster.ajax( 'discord_get_available_channels', { account_id: accountID }, function ( result ) {
        let channelSelector = $( '#fspModalChannelSelector' );

        channelSelector.html( `<option disabled selected>${ fsp__( 'Select channel' ) }</option>` );

        for ( let i in result[ 'list' ] )
        {
            channelSelector.append( `<option value="${ result[ 'list' ][ i ][ 'id' ] }|${ result[ 'list' ][ i ][ 'name' ] }">${ result[ 'list' ][ i ][ 'name' ] }</option>` );
        }

        channelSelector.attr( 'disabled', false );
    } );

    
    $( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
        let channel = $( '#fspModalChannelSelector' ).val();

        if ( channel === undefined )
        {
            FSPoster.toast( fsp__( 'Error!' ), 'warning' );
        }

        FSPoster.ajax( 'discord_save_channels', {
            'account_id': accountID,
            'channel': channel.trim(),
        }, function () {
            accountAdded();
        } );
    } );
} )( jQuery );