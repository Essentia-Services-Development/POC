
/*
 * global localizedCopyToNew
 */

// On page load we change the form action attribute to contain the site id we will copy from
jQuery( function() {
    jQuery( '.wrap form' ).each( function() {
        var actionForm = jQuery( this ).attr( 'action' );
        if ( actionForm.indexOf( 'action=add-site' ) != -1 ) {
            jQuery( this ).attr( 'action', actionForm + "&be-mu-copy-from-post=" + localizedCopyToNew.copyFrom );
        }
    });
});
