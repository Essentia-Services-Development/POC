(function ($) {
	let doc = $( document );

	doc.ready( function () {
		$( '#fspSaveSettings' ).on( 'click', function () {
			let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

			FSPoster.ajax( 'settings_general_save', data, function (res) {
				if ( $( '#fspHideNotifications' ).is( ':checked' ) )
				{
					$( '.fsp-notification-container' ).hide();
				}
				else
				{
					$( '.fsp-notification-container' ).show();
				}

				FSPoster.toast( res[ 'msg' ], 'success' );
			} );
		} );
	} );

	$( '#fspCheckAccounts' ).on( 'change', function () {
		if ( $( this ).is( ':checked' ) )
		{
			$( '#fspDisableAccountsRow' ).slideDown();
		}
		else
		{
			$( '#fspDisableAccountsRow' ).slideUp();
		}
	} ).trigger( 'change' );

	$( ".select2-init" ).select2();

	$( '[data-open-img]' ).on( 'click', function () {
		let img = $( this ).data( 'open-img' );

		FSPoster.modal( `<div class="fsp-modal-body"><img src="${ img }" style="width: 100%;"></div><div class="fsp-modal-footer"><button class="fsp-button" data-modal-close="true">CLOSE</button></div>`, true, true );
	} );

	$( '#fspLicenseStatus' ).on( 'change', function () {
		FSPoster.confirm( fsp__( 'Are you sure to disable the plugin license?' ), function () {
			FSPoster.ajax( 'settings_general_save', { 'fsp_license_status': 0 }, function (result) {
				if ( result[ 'redirect' ] )
				{
					FSPoster.loading( true );

					window.location.href = result[ 'redirect' ];
				}
				else
				{
					FSPoster.loading( true );

					window.location.reload();
				}
			} );
		}, 'fas fa-exclamation-triangle', fsp__( 'YES, SAVE CHANGES' ), function () {
			$( '#fspLicenseStatus' )[ 0 ].checked = true;
		} );
	} );
})( jQuery );
