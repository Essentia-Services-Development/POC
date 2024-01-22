( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$( '#fspSaveSettings' ).on( 'click', function () {
			let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

			FSPoster.ajax( 'settings_twitter_save', data, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success');
			} );
		} );

		$( '#fspTwitterAllowComment' ).on( 'change', function () {
			if ( $( this ).is( ':checked' ) )
			{
				$( '#fspTwitterFirstComment' ).slideDown();
			}
			else
			{
				$( '#fspTwitterFirstComment' ).slideUp();
			}
		} ).trigger( 'change' );
	} );
} )( jQuery );