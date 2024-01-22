'use strict';

( function ( $ ) {
    /**
     * For the moment standard app is not available.
     * Customers will have to use custom apps in order to add their discord accounts to the FS Poster
    * */

    let appId;
    let proxy;

    $( '#fspUseCustomApp' ).trigger( 'click' ).attr( 'disabled', 'disabled' );
    $( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
        proxy   = $( '#fspProxy' ).val().trim();
        appId   = $( '#fspModalAppSelector' ).val().trim();

        let openURL = `${ fspConfig.siteURL }/?discord_app_redirect=${ appId }&proxy=${ proxy }`;

        window.open( openURL, 'fs-app', 'width=750, height=550' );
    } );
} )( jQuery );