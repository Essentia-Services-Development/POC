'use strict';

( function ( $ ) {
	$( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
		let name = $( '#fspModalGroupName' ).val().trim();

		FSPoster.ajax( 'create_account_group', { 'name': name }, function (res) {
			groupCreated(res.id, name);
		} );
	} );

	$( '.fsp-modal-footer > #fspModalSaveButton' ).on( 'click', function () {
		let name = $( '#fspModalGroupName' ).val().trim();
		let id = $( '#fspModalGroupId' ).val().trim();

		if( name === '' )
		{
			FSPoster.alert( fsp__( 'Name can\'t be empty!' ) );
		}
		else
		{
			FSPoster.ajax( 'edit_account_group', { 'group_id': id, 'name': name }, function () {
				$('.fsp-tab[data-id="' + id + '"] > .fsp-tab-title > .fsp-tab-title-text').html( name );
				FSPoster.toast('Saved successfully', 'success');
				$( '.fsp-modal-close' ).click();
			} );
		}
	} );
} )( jQuery );