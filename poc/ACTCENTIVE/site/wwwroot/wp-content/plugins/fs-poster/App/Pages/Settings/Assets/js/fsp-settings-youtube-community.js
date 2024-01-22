( function ( $ ) {
    $( document ).ready( () => {
        $( '#fspSaveSettings' ).on( 'click', () => {
            let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

            FSPoster.ajax( 'settings_youtube_community_save', data, ( res ) => {
                FSPoster.toast( res[ 'msg' ], 'success');
            } );
        } );
    } );
} )( jQuery );