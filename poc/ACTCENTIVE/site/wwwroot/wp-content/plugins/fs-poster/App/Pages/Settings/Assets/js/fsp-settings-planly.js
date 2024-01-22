( function ( $ ) {
    $( document ).ready( () => {
        $( '#fspSaveSettings' ).on( 'click', () => {
            let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

            FSPoster.ajax( 'settings_planly_save', data, ( res ) => {
                FSPoster.toast( res[ 'msg' ], 'success');
            } );
        } );
    } );
} )( jQuery );