'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		if (  typeof FSPObject.metabox_js_loaded === 'undefined' )
		{
			doc.on( 'click', '.fsp-metabox-modal-accounts > .fsp-metabox-account:not(.fsp-is-disabled)', function () {
				let _this  = $( this );
				let dataID = _this.data( 'id' );

				dataID = dataID.split( ':' );

				let nodeType = dataID[ 1 ] === 'account' ? 'account' : 'node';
				let nodeId   = dataID[ 2 ];

				FSPoster.ajax( 'add_node_to_group', {
					'node_id': nodeId,
					'node_type': nodeType,
					'group_id': $( '#fspModalGroupId' ).val(),
				}, function () {
					FSPoster.toast( fsp__( 'Added to the group!' ), 'success' );
				} );

				$( '.fsp-tab.fsp-is-active' ).click();

				_this.slideUp( 200, function () {
					$( this ).remove();
				} );
			} ).on( 'keyup', '.fsp-search-account', function () {
				   let val = $( this ).val().trim().toLowerCase();

				   if ( val !== '' )
				   {
					   $( '.fsp-metabox-modal-accounts > .fsp-metabox-account' ).filter( function () {
						   let _this = $( this );

						   if ( _this.text().toLowerCase().indexOf( val ) > -1 )
						   {
							   _this.slideDown( 200 );
						   }
						   else
						   {
							   _this.slideUp( 200 );
						   }
					   } );
				   }
				   else
				   {
					   $( '.fsp-metabox-modal-accounts > .fsp-metabox-account' ).slideDown( 200 );
				   }
			   } );

			FSPObject.metabox_js_loaded = true;
		}
	} );
} )( jQuery );
