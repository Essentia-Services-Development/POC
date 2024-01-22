( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$( '#fspSaveSettings' ).on( 'click', function () {
			let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

			FSPoster.ajax( 'settings_plurk_save', data, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success');
			} );
		} );


	} );
} )( jQuery );