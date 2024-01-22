'use strict';

( function ( $ ) {
    let doc = $( document );

    doc.ready( function () {
        $( '.fsp-modal-footer > #fspModalAddButton' ).on( 'click', function () {
            let username = $( '#fspUsername' ).val().trim();
            let password = $( '#fspPassword' ).val().trim();
            let proxy = $( '#fspProxy' ).val().trim();

            FSPoster.ajax( 'add_threads_account', { username, password, proxy }, function ( response ) {
                if( typeof response.id !== 'undefined' ){
                    accountAdded();
                }
                else{
                    requireAction( response.options, proxy );
                }
            } );
        } );
    } );
} )( jQuery );

function requireAction ( options, proxy )
{
    if ( typeof jQuery !== 'undefined' )
    {
        $ = jQuery;
    }

    let title = options[ 'obfuscated_phone_number' ] === '3' ? '' : `<p class="fsp-modal-p">${ fsp__( 'Two factor authentication required! Activation code was sent to ' + FSPoster.htmlspecialchars( options[ 'obfuscated_phone_number' ] ) ) }.</p>`;
    $( '.fsp-modal-body' ).html( `
        ${title}
		<div class="fsp-modal-step">
			<div class="fsp-form-group">
				<label>${ fsp__( 'Activation code' ) }</label>
				<div class="fsp-form-input-has-icon">
					<i class="far fa-copy"></i>
					<input id="fspActivationCode" class="fsp-form-input" autocomplete="off" placeholder="${ fsp__( 'Enter the activation code' ) }">
				</div>
			</div>
		</div>` );

    $( '#fspModalAddButton' ).off( 'click' ).on( 'click', function () {
        let code = $( '#fspActivationCode' ).val().trim();

        FSPoster.ajax( 'do_threads_challenge', {
            options,
            proxy,
            code
        }, function () {
            accountAdded();
        } );
    } );
}