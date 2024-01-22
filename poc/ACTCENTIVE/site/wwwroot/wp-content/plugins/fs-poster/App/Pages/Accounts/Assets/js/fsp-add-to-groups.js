'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$('body').append("<style>.select2-search__field{width:100%!important;display:block;}</style>");
		$( '.select2-init' ).select2( {
			containerCssClass: 'fsp-select2-container',
			dropdownCssClass: 'fsp-select2-dropdown',
			placeholder: fsp__( 'Click to see groups...' ),
			ajax: {
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: function ( params ) {
					return {
						action: 'get_account_groups',
						search: params.term,
						not_all: 1
					};
				},
				processResults: function ( data ) {
					return {
						results: data.result
					};
				},
				minimumInputLength: 2
			}
		} );

		$( '.fsp-modal-footer > #fspModalSaveGroupsBtn' ).on( 'click', function () {
			let id = $( '#fspModalNodeId' ).val();
			let type = $( '#fspModalNodeType' ).val();
			let groups = $( '#fspModalGroups' ).val();
			let closeBtn = $(this).siblings('[data-modal-close="true"]');
			if ( groups === '' )
			{
				groups = $( '#fspModalGroups' ).select2( 'val' );
			}

			FSPoster.ajax( 'add_to_groups', {
				'node_id': id,
				'node_type': type,
				'groups': groups
			}, function () {
				closeBtn.click();
				$( '.fsp-tab.fsp-is-active' ).click();
				FSPoster.toast('Saved successfully!', 'success');
			} );
		} );
	} );
} )( jQuery );
