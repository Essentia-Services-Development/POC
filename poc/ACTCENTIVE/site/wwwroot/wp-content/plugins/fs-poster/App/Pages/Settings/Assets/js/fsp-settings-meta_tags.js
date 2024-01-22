( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$( '#fspSaveSettings' ).on( 'click', function () {
			let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

			FSPoster.ajax( 'settings_meta_tags_save', data, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success' );
			} );
		} );
	} );

	$( '#fspEnableOEmbed, #fspEnableOpenGraph, #fspEnableTwitterTags' ).on( 'change', function () {
		if ( $( '#fspEnableOEmbed' ).is( ':checked' ) || $( '#fspEnableOpenGraph' ).is( ':checked' ) || $( '#fspEnableTwitterTags' ).is( ':checked' ) )
		{
			$( '#fspMetaTagsAllowedPostTypesRow' ).slideDown();
		}
		else
		{
			$( '#fspMetaTagsAllowedPostTypesRow' ).slideUp();
		}
	} ).trigger( 'change' );

	$( '.select2-init' ).select2();
} )( jQuery );
