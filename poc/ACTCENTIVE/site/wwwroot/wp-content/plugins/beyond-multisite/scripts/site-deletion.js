
/* global localizedSiteDeletion */

// This function makes an ajax request to a php funciton that cancels the site deletion
function cleanupCancelSiteDeletion() {
    var data;

    // We show the loading message
    document.getElementById( 'be-mu-clean-loading-cancel-deletion' ).innerHTML = localizedSiteDeletion.loading;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_cancel_site_deletion_action',
        'security': localizedSiteDeletion.ajaxNonce
    };

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( '0' === response || 'no-access' === response || 'error-updating' === response ) {
            document.getElementById( 'be-mu-clean-loading-cancel-deletion' ).innerHTML = localizedSiteDeletion.error;
        } else {

            // The cancellation was successful so we show a little message that it is done
            document.getElementById( 'be-mu-clean-loading-cancel-deletion' ).innerHTML = localizedSiteDeletion.done;

            // After a while we fade out the red deletion message and hide it
            setTimeout( cleanupStartFadingRedMessage, 1500 );
        }
	}).fail( function() {
        alert( localizedSiteDeletion.errorUserServerFail );
        document.getElementById( 'be-mu-clean-loading-cancel-deletion' ).innerHTML = localizedSiteDeletion.error;
    });
}

// Fades out and at the end removes the red site deletion message
function cleanupStartFadingRedMessage() {
    var element;

    element = document.getElementById( 'be-mu-clean-red-deletion-message-id' ).style;
    element.opacity = 1;

    (function cleanFadeRedMessage() {
        if( ( element.opacity -= 0.1 ) <= 0.1 ) {
            element.display = 'none';
        } else {
            setTimeout( cleanFadeRedMessage, 40 );
        }
    })();
}
