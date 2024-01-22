( function ( $ ) {
    $( document ).ready( function () {
        $( '#fspSaveSettings' ).on( 'click', function () {
            let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

            FSPoster.ajax( 'settings_xing_save', data, function ( res ) {
                FSPoster.toast( res[ 'msg' ], 'success');
            } );
        } );
    } );
} )( jQuery );