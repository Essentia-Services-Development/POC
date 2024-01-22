'use strict';

( function ( $ ) {
	$( '.fsp-modal-footer > #fspjs-save' ).on( 'click', function () {
		let groupID = $( '#fspjs-group-id' ).val().trim();
		let pageID = $( '#fspjs-page-id' ).val().trim();

		if ( ! ( groupID > 0 ) )
		{
			window.location.reload();

			return;
		}

		FSPoster.ajax( 'save_fb_group_poster', { group_id: groupID, page_id: pageID }, function ( res ) {
			FSPoster.toast( res[ 'message' ], 'success' );

			$( '.fsp-tab.fsp-is-active' ).click();
			$( '.fsp-modal-close' ).click();
		} );
	} );
} )( jQuery );