'use strict';

( function ( $ ) {

    /* 
        Handles tab actions. Work with tab id so that multiple tabs can be handled at the same time.
        Usage: .fsp-modal-tabs must have a unique ID. Tab content must have id="{tabID}_{step}" 
        and class="{tabID}-step".
    */

	let index = 0;

	if ( $( '.fsp-metabox-tab.fsp-is-active' ).data( 'tab' ) == 'fsp' )
	{
		index = 1;
	}

	$( '.fsp-modal-tabs' ).each( function (i, el) {
		let parentModal = $(this).parents('.fsp-modal').first();
        const tabID = $( el ).attr( 'id' );

		let fspModalTab = parentModal.find( '.fsp-modal-tab' );

        fspModalTab.on( 'click', function () {
            if ($(this).hasClass( 'fsp-is-active' )) {
                return;
            }

	        parentModal.find( '.fsp-modal-tab.fsp-is-active' ).removeClass( 'fsp-is-active' );
            $(this).addClass( 'fsp-is-active' );

            const step = String(parentModal.find( '.fsp-modal-tab.fsp-is-active' ).data( 'step' ));


			parentModal.find(`.${tabID}-step`).hide();
			parentModal.find(`#${tabID}_${step}`).show();
        });

		if( $(this).find('.fsp-modal-tab.fsp-is-active').length === 0 ){
			fspModalTab.eq( index ).click();
		}
    } );
} )( jQuery );