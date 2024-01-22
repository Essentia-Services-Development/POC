( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$( '#fspSaveSettings' ).on( 'click', function () {
			let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

			FSPoster.ajax( 'settings_telegram_save', data, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success');
			} );
		} );

		$( '#useReadMoreButton' ).on( 'change', function () {
			if ( $( this ).is( ':checked' ) )
			{
				$( '#fspUseCustomButton' ).slideDown();
			}
			else
			{
				$( '#fspUseCustomButton' ).slideUp();
			}
		} ).trigger( 'change' );
	} );
} )( jQuery );