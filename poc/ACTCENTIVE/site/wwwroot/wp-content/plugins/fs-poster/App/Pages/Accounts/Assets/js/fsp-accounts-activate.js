'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$('body').append("<style>.select2-search__field{width:100%!important;display:block;}</style>");
		$( '.select2-init' ).select2( {
			containerCssClass: 'fsp-select2-container',
			dropdownCssClass: 'fsp-select2-dropdown',
			placeholder: fsp__( 'Click to search categories, tags... ( min. 2 character )' ),
			//width: 'style',
			ajax: {
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: function ( params ) {
					return {
						action: 'get_tags_and_cats',
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

		$( '.fsp-modal-footer > #fspModalActivateBtn' ).on( 'click', function () {
			let id = $( '#fspActivateID' ).val();
			let ids = $( '#fspActivateIDS' ).val();
			let ajaxType = $( '#fspActivateURL' ).val();
			let cats = $( '#fspCategories' ).val();
			let for_all = $( '#fspActivateConditionallyForAll' ).is( ':checked' ) ? 1 : 0;
			let filterType = String( $( '.fsp-modal-option.fsp-is-selected' ).data( 'name' ) );
			let accountDiv = $( `.fsp-account-item[data-id=${ id }][data-type="${ ajaxType === 'account_activity_change' ? 'account' : 'community' }"]` );

			if ( cats === '' )
			{
				cats = $( '#fspCategories' ).select2( 'val' );
			}

			FSPoster.ajax( ajaxType, {
				id,
				ids,
				checked: 1,
				categories: cats,
				filter_type: filterType,
				for_all
			}, function () {
				accountDiv.find( `.fsp-account-checkbox > i` ).removeClass( 'far' ).addClass( 'fas fsp-is-checked-conditionally' );

				$( '[data-modal-close=true]' ).click();

				if ( ids.length )
				{
					$( '.fsp-tab.fsp-is-active' ).click();
				}
			} );
		} );
	} );
} )( jQuery );
