( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$( '#fspSaveSettings' ).on( 'click', function () {
			let data = new FormData( $( '#fspSettingsForm' )[ 0 ] );

			FSPoster.ajax( 'settings_facebook_save', data, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success' );
			} );
		} );

		$( '#fs_load_groups' ).trigger( 'change' );

		$( '#fs_fetch_fb_comments' ).on( 'change', function () {
			if ( $( this ).is( ':checked' ) )
			{
				$( '#fspFetchCommentsForPostsPublishedAt' ).slideDown();
			}
			else
			{
				$( '#fspFetchCommentsForPostsPublishedAt' ).slideUp();
			}
		} ).trigger( 'change' );

		$( '.fsp-settings-fbcontrol-input > .fsp-form-input' ).on( 'change keyup', function () {
			let _this = $( this );
			let type = _this.data( 'type' );
			let val = _this.val();

			if ( _this[ 0 ].hasAttribute( 'data-jscolor' ) )
			{
				let colors = val.match( /.{1,2}/g );
				let colorText;

				if ( ( parseInt( colors[ 0 ], 16 ) * 0.299 + parseInt( colors[ 1 ], 16 ) * 0.587 + parseInt( colors[ 2 ], 16 ) * 0.114 ) > 186 )
				{
					colorText = '000000';
				}
				else
				{
					colorText = 'ffffff';
				}

				_this.attr( 'style', `background: #${ val } !important; color: #${ colorText } !important;` );
				_this.prev().attr( 'style', `color: #${ colorText } !important;` );
			}

			if ( type === 'story-background' )
			{
				$( '#fspStory' ).css( 'background', '#' + val );
			}
			else if ( type === 'title-background-color' || type === 'title-background-opacity' )
			{
				let hex = $( '.fsp-settings-fbcontrol-input > .fsp-form-input[data-type="title-background-color"]' ).val();
				let rgb = hexToRgb( hex );
				let opacity = $( '.fsp-settings-fbcontrol-input > .fsp-form-input[data-type="title-background-opacity"]' ).val();
				opacity = ( opacity > 100 ? 100 : opacity ) / 100;

				$( '#fspStoryTitle' ).css( 'background', 'rgba(' + rgb + ',' + opacity + ')' );
			}
			else
			{
				type = type.substr( 6 );

				if ( type === 'color' )
				{
					val = '#' + val;
				}
				else
				{
					val = val + 'px';
				}

				$( '#fspStoryTitle' ).css( type, val );
			}
		} ).trigger( 'change' );

		function hexToRgb ( hex )
		{
			var bigint = parseInt( hex, 16 );
			var r = ( bigint >> 16 ) & 255;
			var g = ( bigint >> 8 ) & 255;
			var b = bigint & 255;

			return r + ',' + g + ',' + b;
		}

		$( '.fsp-settings-fbstory-tab' ).on( 'click', function () {
			let tab = $( this ).data( 'tab' );

			if ( tab === 'message' )
			{
				$( `#fspSettingsFbStoryMessageRow` ).removeClass( 'fsp-hide' );
				$( `#fspSettingsFbStoryAppearanceRow` ).addClass( 'fsp-hide' );
			}
			else if ( tab === 'appearance' )
			{
				$( `#fspSettingsFbStoryMessageRow` ).addClass( 'fsp-hide' );
				$( `#fspSettingsFbStoryAppearanceRow` ).removeClass( 'fsp-hide' );
			}
		} );

		$( '#fspFbCustomFontButton' ).on( 'click', function () {
			$( '#fspFbCustomFontInput' ).trigger( 'click' );
		} );

		$( '#fspFbCustomFontReset' ).on( 'click', function () {
			FSPoster.confirm( fsp__( 'Are you sure you want to reset the custom font to default?' ), function () {
				$( '#fspFbCustomFontResetInput' ).val( 1 );
				$( '#fspSaveSettings' ).trigger( 'click' );

				$( '#fspFbCustomFontReset' ).remove();
				$( '#fspFbCustomFontResetInput' ).remove();
			}, 'fas fa-redo-alt', 'RESET' );
		} );
	} );
} )( jQuery );