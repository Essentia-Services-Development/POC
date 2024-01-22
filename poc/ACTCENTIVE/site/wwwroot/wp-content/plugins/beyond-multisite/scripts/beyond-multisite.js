
/* global localizedBeyondMultisite */

/*
 * Makes the required changes to the related elements to show a chosen settings box
 * @param {String} module
 */
function beyondMultisiteShowSettings( module ) {
    var key, height;

    document.getElementById( 'be-mu-' + module + '-settings-box').style.display = 'block';
    document.getElementById( 'be-mu-' + module + '-description-box' ).style.display = 'none';
    document.getElementById( 'be-mu-show-settings-' + module ).style.display = 'none';
    document.getElementById( 'be-mu-hide-settings-' + module ).style.display = 'inline-block';

    /*
     * When the settings are shown, if there are text editors, we manually set their height again here.
     * This is needed because since WordPress 5.0 there is a bug that when the text editor is loaded in a hidden layer, it has a very small height.
     */
    if ( typeof tinyMCE != "undefined" ) {
        for ( key in tinyMCE.editors ) {
            if ( tinyMCE.editors.hasOwnProperty( key ) && key.indexOf( module ) != -1 ) {
                if ( 'be-mu-moderation-dashboard-message' == key || 'be-mu-email-unsubscribe-footer' == key ) {
                    height = 150;
                } else {
                    height = 250;
                }
                tinyMCE.editors[ key ].theme.resizeTo ( null, height );
            }
        }
    }
}

/*
 * Makes the required changes to the related elements to hide a chosen settings box
 * @param {String} module
 */
function beyondMultisiteHideSettings( module ) {
    document.getElementById( 'be-mu-' + module + '-settings-box' ).style.display = 'none';
    document.getElementById( 'be-mu-' + module + '-description-box' ).style.display = 'block';
    document.getElementById( 'be-mu-hide-settings-' + module ).style.display = 'none';
    document.getElementById( 'be-mu-show-settings-' + module ).style.display = 'inline-block';
}

// Updates the captcha preview
function beyondMultisiteUpdateCaptchaPreview() {
    var element, characters, characterSet, height, data;

    // Show the loading image
    document.getElementById( 'be-mu-loading-captcha-preview' ).style.visibility = 'visible';

    // Get and set the variables with the chosen settings
    element = document.getElementById( 'be-mu-captcha-characters' );
    characters = element.options[ element.selectedIndex ].value;
    element = document.getElementById( 'be-mu-captcha-character-set' );
    characterSet = element.options[ element.selectedIndex ].value;
    element = document.getElementById( 'be-mu-captcha-height' );
    height = element.options[ element.selectedIndex ].value;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_update_captcha_preview_action',
        'security': localizedBeyondMultisite.ajaxNonce,
        'characters': characters,
        'character_set': characterSet,
        'height': height
    };

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var imageSource;

        response = response.trim();

        if ( 'error-making-image' === response ) {
            alert( localizedBeyondMultisite.errorImage );
        } else if ( '0' === response ) {
            alert( localizedBeyondMultisite.errorResponse );
        } else if ( 'invalid-nonce' === response ) {
            alert( localizedBeyondMultisite.errorInvalidNonce );
        } else {

            // Success, now we force the image to be refreshed
            imageSource = document.getElementById( 'be-mu-captcha-preview-image' ).src;
            imageSource = imageSource.split("?")[0];
            document.getElementById( 'be-mu-captcha-preview-image' ).src = imageSource + '?' + new Date().getTime();
        }

        // When we are done, we hide the loading image
        document.getElementById( 'be-mu-loading-captcha-preview' ).style.visibility = 'hidden';
    }).fail( function() {
        alert( localizedBeyondMultisite.errorServerFail );
        document.getElementById( 'be-mu-loading-captcha-preview' ).style.visibility = 'hidden';
    });
}

// Sends a test email
function beyondMultisiteSendTestEmail( ID ) {
    var testEmail, data, ajaxAction;

    // We empty the span next to the button from a previous message and show the loading image
    document.getElementById( 'be-mu-' + ID + '-test-email-done-span' ).innerHTML = '';
    document.getElementById( 'be-mu-loading-' + ID + '-test-email' ).style.visibility = 'visible';

    // Get and set the variable with the chosen test email
    testEmail = document.getElementById( 'be-mu-' + ID + '-test-email' ).value;

    if ( 'moderation-publish' == ID || 'moderation-delete' == ID || 'moderation-pending' == ID ) {
        ajaxAction = 'be_mu_moderation_send_test_email_action';
    } else {
        ajaxAction = 'be_mu_' + ID + '_send_test_email_action';
    }

    // This is the data we will send in the ajax request
    data = {
        'action': ajaxAction,
        'security': localizedBeyondMultisite.ajaxNonce,
        'id': ID,
        'test_email': testEmail
    };

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        // Error, we alert an error
        if ( 'invalid-email' === response ) {
            alert( localizedBeyondMultisite.errorEmail );
        } else if ( 'invalid-nonce' === response ) {
            alert( localizedBeyondMultisite.errorInvalidNonce );
        } else if ( 'invalid-from-email' === response ) {
            alert( localizedBeyondMultisite.errorFromEmail );
        } else if ( 'no-access' === response ) {
            alert( localizedBeyondMultisite.errorAccess );
        } else if ( 'invalid-id' === response ) {
            alert( localizedBeyondMultisite.errorID );
        } else if ( 'failed-send' === response ) {
            alert( localizedBeyondMultisite.errorFailedSend );
        } else if ( '0' === response ) {
            alert( localizedBeyondMultisite.errorResponse );
        } else {

            // Success, we show a message next to the button
            document.getElementById( 'be-mu-' + ID + '-test-email-done-span' ).innerHTML = localizedBeyondMultisite.done;
        }

        // When we are done, we hide the loading image
        document.getElementById( 'be-mu-loading-' + ID + '-test-email' ).style.visibility = 'hidden';
    }).fail( function() {
        alert( localizedBeyondMultisite.errorServerFail );
        document.getElementById( 'be-mu-loading-' + ID + '-test-email' ).style.visibility = 'hidden';
    });
}

// Fades out and at the end removes the 'Done' message
function beyondMultisiteStartFadingMessage() {
    var element;

    element = document.getElementById( 'be-mu-message-id' ).style;
    element.opacity = 1;

    (function beyondMultisiteFadeMessage() {
        if( ( element.opacity -= 0.1 ) <= 0.1 ) {
            element.display = 'none';
        } else {
            setTimeout( beyondMultisiteFadeMessage, 40 );
        }
    })();
}

/*
 * Makes the required changes to the related elements to switch between the module menus
 * @param {String} module
 * @param {String} menu
 */
function beyondMultisiteModuleMenu( module, menu ) {

    // First we hide the content layers for both menus, and then we show only the selected one
    document.getElementById( 'be-mu-' + module + '-main-features' ).style.display = 'none';
    document.getElementById( 'be-mu-' + module + '-how-to' ).style.display = 'none';
    document.getElementById( 'be-mu-' + module + '-' + menu ).style.display = 'block';

    // First we make the bottom border of both menus white, and then we change the color of the selected menu
    document.getElementById( 'be-mu-' + module + '-main-features-link' ).style.borderBottom = '1px solid #ffffff';
    document.getElementById( 'be-mu-' + module + '-how-to-link' ).style.borderBottom = '1px solid #ffffff';
    if ( 'insert' == module || 'plugin' == module || 'activated-in' == module ) {
        document.getElementById( 'be-mu-' + module + '-' + menu + '-link' ).style.borderBottom = '1px solid #76c2af';
    }
    if ( 'pending' == module || 'captcha' == module || 'copy' == module ) {
        document.getElementById( 'be-mu-' + module + '-' + menu + '-link' ).style.borderBottom = '1px solid #c75c5c';
    }
    if ( 'ban' == module || 'clean' == module ) {
        document.getElementById( 'be-mu-' + module + '-' + menu + '-link' ).style.borderBottom = '1px solid #d0d0c1';
    }
    if ( 'improve' == module ) {
        document.getElementById( 'be-mu-' + module + '-' + menu + '-link' ).style.borderBottom = '1px solid #e0995e';
    }
    if ( 'email' == module || 'moderation' == module ) {
        document.getElementById( 'be-mu-' + module + '-' + menu + '-link' ).style.borderBottom = '1px solid #77b3d4';
    }
}

/*
 * Encodes the content of the text fields of the insert HTML module before submitting the form, so the request is not blocked by various security
 * measures when we try to add JavaScript code in there.
 */
function beyondMultisiteEncodeSubmitInsert() {
    var content;
    content = jQuery( '#be-mu-insert-head' ).val();
    content = btoa( encodeURIComponent( content ) );
    jQuery( '#be-mu-insert-head' ).val( content );
    content = jQuery( '#be-mu-insert-footer' ).val();
    content = btoa( encodeURIComponent( content ) );
    jQuery( '#be-mu-insert-footer' ).val( content );
    jQuery( 'form.be-mu-insert-settings-form' ).append( '<input type="hidden" name="be-mu-update-insert-settings" value="1" />' );
    jQuery( 'form.be-mu-insert-settings-form' ).submit();
}

// After the page loads, if the checkbox for having a template site to copy into new sites is checked, we show a message to also enter the site ID (if it isn't)
jQuery( document ).ready( function() {
    jQuery( '#be-mu-copy-template-site-enable' ).change( function() {
        if ( this.checked ) {
            if ( jQuery( '#be-mu-copy-template-site-id' ).val() === '' ) {
                jQuery( '#be-mu-copy-enter-template-site-id' ).removeClass( 'be-mu-display-none' );
            }
        } else {
            jQuery( '#be-mu-copy-enter-template-site-id' ).addClass( 'be-mu-display-none' );
        }
    });
});
