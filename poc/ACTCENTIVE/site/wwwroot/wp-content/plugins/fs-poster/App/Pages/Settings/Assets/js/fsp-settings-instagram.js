'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$( '#fspSaveSettings' ).on( 'click', function () {
			let data = new FormData( $( '#fspSettingsForm' )[ 0 ] );

			FSPoster.ajax( 'settings_instagram_save', data, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success');
			} );
		} );

		$( '#fspPostingTypeSelector' ).on( 'change', function () {
			if ( $( this ).val() != 2   ) {
				$( '#fspPostingTypeRow' ).slideDown();
			} else {
				$( '#fspPostingTypeRow' ).slideUp();
			}
		} ).trigger( 'change' );

		$( '.fsp-settings-igcontrol-input > .fsp-form-input.iginput' ).on( 'change keyup', function () {
			let _this = $( this );
			let type = _this.data( 'type' );
			let val = _this.val();

			if ( _this[ 0 ].hasAttribute( 'data-jscolor' ) )
			{
				let colors = val.match( /.{1,2}/g );
				let colorText;

				if ( ( parseInt(colors[ 0 ], 16 ) * 0.299 + parseInt(colors[ 1 ], 16 ) * 0.587 + parseInt(colors[ 2 ], 16 ) * 0.114 ) > 186 )
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
				$( '#fspStory' ).css('background', '#' + val);
			}
			else if( type === 'title-background-color' || type === 'title-background-opacity' )
			{
				let hex = $('.fsp-settings-igcontrol-input > .fsp-form-input[data-type="title-background-color"]').val();
				let rgb = hexToRgb( hex );
				let opacity = $('.fsp-settings-igcontrol-input > .fsp-form-input[data-type="title-background-opacity"]').val();
				opacity = (opacity > 100 ? 100 : opacity) / 100;

				$( '#fspStoryTitle' ).css( 'background', 'rgba(' + rgb + ',' + opacity + ')' );
			}
			else if( type === 'link-background-color' || type === 'link-background-opacity' )
			{
				let hex = $('.fsp-settings-igcontrol-input > .fsp-form-input[data-type="link-background-color"]').val();
				let rgb = hexToRgb( hex );
				let opacity = $('.fsp-settings-igcontrol-input > .fsp-form-input[data-type="link-background-opacity"]').val();
				opacity = (opacity > 100 ? 100 : opacity) / 100;

				$( '#fspStoryLinkText' ).css( 'background', 'rgba(' + rgb + ',' + opacity + ')' );
			}
			else if( type === 'hashtag-background-color' || type === 'hashtag-background-opacity' )
			{
				let hex = $('.fsp-settings-igcontrol-input > .fsp-form-input[data-type="hashtag-background-color"]').val();
				let rgb = hexToRgb( hex );
				let opacity = $('.fsp-settings-igcontrol-input > .fsp-form-input[data-type="hashtag-background-opacity"]').val();
				opacity = (opacity > 100 ? 100 : opacity) / 100;

				$( '#fspStoryHashtagText' ).css( 'background', 'rgba(' + rgb + ',' + opacity + ')' );
			}
			else
			{
				let selector = '#fspStoryLinkText';
				let cutLen   = 5;

				if( type.search('title') !== -1 )
				{
					selector = '#fspStoryTitle';
					cutLen = 6;
				}
				else if( type.search('hashtag') !== -1 )
				{
					selector = '#fspStoryHashtagText';
					cutLen = 8;
				}

				type = type.substr( cutLen );

				if( type === 'color' )
				{
					val = '#' + val;
				}
				else
				{
					val = val + 'px';
				}

				$( selector ).css( type, val );
			}
		} ).trigger( 'change' );

		$( '#fspStoryHashtagInput' ).on( 'change keyup', function (){
			if( $( this ).val() === '' ){
				$('#fspStoryHashtagText').hide();
			}
			else{
				$('#fspStoryHashtagText').show().html( '#' + $( this ).val() );
			}
		} ).trigger( 'change' );

		function hexToRgb(hex)
		{
			var bigint = parseInt(hex, 16);
			var r = (bigint >> 16) & 255;
			var g = (bigint >> 8) & 255;
			var b = bigint & 255;

			return r + "," + g + "," + b;
		}

		$( '.fsp-settings-igstory-tab' ).on( 'click', function () {
			let tab = $( this ).data( 'tab' );

			if ( tab === 'message' )
			{
				$( `#fspSettingsIgStoryMessageRow` ).removeClass( 'fsp-hide' );
				$( `#fspSettingsIgStoryAppearanceRow` ).addClass( 'fsp-hide' );
			}
			else if ( tab === 'appearance' )
			{
				$( `#fspSettingsIgStoryMessageRow` ).addClass( 'fsp-hide' );
				$( `#fspSettingsIgStoryAppearanceRow` ).removeClass( 'fsp-hide' );
			}
		} );

		$( '.fsp-settings-igstory-sub-tab' ).on( 'click', function () {
			let tab = $( this ).data( 'sub-tab' );

			$( '.fsp-settings-igstory-sub-tab' ).removeClass( 'fsp-is-active' );
			$( this ).addClass( 'fsp-is-active' );

			if ( tab === 'title' )
			{
				$( `#fspInstagramStoryTitleTab` ).removeClass( 'fsp-hide' );
				$( `#fspInstagramStoryLinkTab, #fspInstagramStoryHashtagTab` ).addClass( 'fsp-hide' );
			}
			else if ( tab === 'link' )
			{
				$( `#fspInstagramStoryLinkTab` ).removeClass( 'fsp-hide' );
				$( `#fspInstagramStoryTitleTab, #fspInstagramStoryHashtagTab` ).addClass( 'fsp-hide' );
			}
			else if ( tab === 'hashtag' )
			{
				$( `#fspInstagramStoryHashtagTab` ).removeClass( 'fsp-hide' );
				$( `#fspInstagramStoryLinkTab, #fspInstagramStoryTitleTab` ).addClass( 'fsp-hide' );
			}
		} );

		$( '#fspIgCustomFontButton' ).on( 'click', function () {
			$( '#fspIgCustomFontInput' ).trigger( 'click' );
		} );

		$( '#fspIgCustomFontReset' ).on( 'click', function () {
			FSPoster.confirm( fsp__( 'Are you sure you want to reset the custom font to default?' ), function () {
				$( '#fspIgCustomFontResetInput' ).val( 1 );
				$( '#fspSaveSettings' ).trigger( 'click' );

				$( '#fspIgCustomFontReset' ).remove();
				$( '#fspIgCustomFontResetInput' ).remove();
			}, 'fas fa-redo-alt', 'RESET' );
		} );

		$( '#fspIgAllowComment' ).on( 'change', function () {
			if ( $( this ).is( ':checked' ) )
			{
				$( '#fspIgFirstComment' ).slideDown();
			}
			else
			{
				$( '#fspIgFirstComment' ).slideUp();
			}
		} ).trigger( 'change' );
	} );
} )( jQuery );