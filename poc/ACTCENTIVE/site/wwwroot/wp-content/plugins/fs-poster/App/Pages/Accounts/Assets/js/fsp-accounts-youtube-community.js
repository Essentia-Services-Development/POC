'use strict';

( function ( $ ) {
    let doc = $( document );

    doc.ready( () => {
        doc.on( 'click', '.fsp-modal-footer > #fspModalAddButtonYoutubeCommunity', () => {
            FSPoster.ajax( 'add_youtube_community_account', {
                login_info: ( $( '#fspCookie_loginInfo' ).val() || '' ).trim(),
                api_sid: ( $( '#fspCookie_secure3ApiSid' ).val() || '' ).trim(),
                p_sid: ( $( '#fspCookie_secure3pSid' ).val() || '' ).trim(),
                proxy: ( $( '#fspProxy' ).val() || '' ).trim()
            }, () => accountAdded() );
        } ).on('click', '.fsp-modal-footer > #fspModalUpdateCookiesButtonYoutubeCommunity', () => {
            FSPoster.ajax( 'update_youtube_community_account', {
                login_info: ( $( '#fspCookie_loginInfo' ).val() || '' ).trim(),
                api_sid: ( $( '#fspCookie_secure3ApiSid' ).val() || '' ).trim(),
                p_sid: ( $( '#fspCookie_secure3pSid' ).val() || '' ).trim(),
                account_id: ( $( '#account_to_update' ).val() || '' ).trim(),
                proxy: ( $( '#fspProxy' ).val() || '' ).trim()
            }, () => accountUpdated() );
        } );
    } );
} )( jQuery );