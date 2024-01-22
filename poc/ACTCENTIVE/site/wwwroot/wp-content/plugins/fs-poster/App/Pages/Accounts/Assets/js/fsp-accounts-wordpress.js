'use strict';

( function ( $ ) {
	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let site_url = $( '#fspWP_site_url' ).val().trim();
		let username = $( '#fspWP_username' ).val().trim();
		let password = $( '#fspWP_password' ).val().trim();
		let proxy = $( '#fspProxy' ).val().trim();

		FSPoster.ajax( 'add_wordpress_site', { site_url, username, password, proxy }, function () {
			accountAdded();
		} );
	} );
} )( jQuery );