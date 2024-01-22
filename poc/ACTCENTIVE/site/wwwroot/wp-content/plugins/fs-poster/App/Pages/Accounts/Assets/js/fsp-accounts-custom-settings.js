'use strict';

( function ( $ ) {
	$( '.fsp-settings-collapser' ).on( 'click', function () {
		let _this = $( this );

		if ( ! _this.parent().hasClass( 'fsp-is-open' ) )
		{
			_this.parent().find( '.fsp-settings-collapse' ).slideToggle();
			_this.find( '.fsp-settings-collapse-state' ).toggleClass( 'fsp-is-rotated' );
		}
	} );

	$( '#fspResetToDefault' ).on( 'click', function () {
		FSPoster.confirm( fsp__( 'Are you sure to reset settings to default?' ), function () {
			let fsNodeId = $( '[name="fs_node_id"]' ).val();
			let fsNodeType = $( '[name="fs_node_type"]' ).val();
			let fsNodeDriver = $( '[name="fs_node_driver"]' ).val();

			FSPoster.ajax( 'reset_custom_settings', {
				'fs_node_id': fsNodeId,
				'fs_node_type': fsNodeType,
				'fs_node_driver': fsNodeDriver
			}, function ( res ) {
				$( '[data-modal-close=true]' ).click();

				FSPoster.toast( res[ 'msg' ], 'success' );
			} );
		}, 'far fa-save', fsp__( 'Reset to default' ) );
	} );

	$( '#fspSaveSettings' ).on( 'click', function () {
		let data = {};

		let allInputs = $( '#fspSettingsForm input, #fspSettingsForm select, #fspSettingsForm textarea' );
		$.each( allInputs, function ( _, input ) {
			input = $( input );
			if ( input.attr( 'has_changed' ) || input.attr( 'type' ) === 'hidden' )
			{
				let inputVal;
				if ( input.attr( 'type' ) === 'checkbox' )
				{
					inputVal = input.is( ':checked' ) ? 'on' : 0;
				}
				else
				{
					inputVal = input.val();
				}
				data[ input.attr( 'name' ) ] = inputVal;
			}
		} );

		FSPoster.ajax( 'save_custom_settings', data, function ( res ) {
			FSPoster.toast( res[ 'msg' ], 'success' );
		} );
	} );

	setTimeout( () => {
		$( '#fspSettingsForm input, #fspSettingsForm select, #fspSettingsForm textarea' ).on( 'input', function () {
			$( this ).attr( 'has_changed', true );
		} );
	} );

	$( '#fspURLShortener' ).on( 'change', function () {
		if ( $( this ).is( ':checked' ) )
		{
			$( '#fspShortenerRow' ).slideDown();
		}
		else
		{
			$( '#fspShortenerRow' ).slideUp();
		}
	} ).trigger( 'change' );

	$( '#fspShortenerSelector' ).on( 'change', function () {
		$( '#fspBitly, #fspYourlsApiUrl, #fspYourlsApiToken, #fspPolrApiUrl, #fspPolrApiKey, #fspShlinkApiKey, #fspShlinkApiUrl, #fsprebrandlyApiKey, #fspRebrandlyDomain' ).slideUp();
		switch ( $( this ).val() )
		{
			case 'bitly':
				$( '#fspBitly' ).slideDown();
				break;
			case 'yourls':
				$( '#fspYourlsApiUrl, #fspYourlsApiToken' ).slideDown();
				break;
			case 'polr':
				$( '#fspPolrApiUrl, #fspPolrApiKey' ).slideDown();
				break;
			case 'shlink':
				$( '#fspShlinkApiKey, #fspShlinkApiUrl' ).slideDown();
				break;
			case 'rebrandly':
				$( '#fsprebrandlyApiKey, #fspRebrandlyDomain' ).slideDown();
				break;
			default :
				break;
		}
	} ).trigger( 'change' );

	$( '#fspCustomURL' ).on( 'change', function () {
		if ( $( this ).is( ':checked' ) )
		{
			$( '#fspCustomURLRow_1' ).slideUp();
			$( '#fspCustomURLRow_2' ).slideDown();
		}
		else
		{
			$( '#fspCustomURLRow_1' ).slideDown();
			$( '#fspCustomURLRow_2' ).slideUp();
		}
	} ).trigger( 'change' );

	$( '#fspAllowCommentCustomSetting' ).on( 'change', function () {
		if ( $( this ).is( ':checked' ) )
		{
			$( '#fspFirstCommentCustomSetting' ).slideDown();
		}
		else
		{
			$( '#fspFirstCommentCustomSetting' ).slideUp();
		}
	} ).trigger( 'change' );

	$( '#fspUseGA' ).on( 'click', function () {
		$( this ).parent().parent().children( 'input' ).val( 'utm_source={network_name}&utm_medium={account_name}&utm_campaign=FS%20Poster' );
	} );
} )( jQuery );