( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$( '#fspSaveSettings' ).on( 'click', function () {
			let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

			FSPoster.ajax( 'settings_reddit_save', data, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success');
			} );
		} );

		$( '#fspRedditAllowComment' ).on( 'change', function () {
			if ( $( this ).is( ':checked' ) )
			{
				$( '#fspRedditFirstComment' ).slideDown();
			}
			else
			{
				$( '#fspRedditFirstComment' ).slideUp();
			}
		} ).trigger( 'change' );
	} );
} )( jQuery );