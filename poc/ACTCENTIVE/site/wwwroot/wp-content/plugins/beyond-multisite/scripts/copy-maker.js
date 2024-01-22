
/*
 * global copySitesAbort:true, copySitesFromID:true, copySitesToID:true, copySitesTaskID:true, copyMakerRunningAjax:true, ajaxurl, localizedCopyMaker
 */

/*
 * copySitesAbort: Tells us if we need to abort the copy process (1 means to abort as soon as possible)
 * copyMakerRunningAjax: If this is 1, it means that currently is running an ajax request, 0 means the opposite
 * copySitesFromID: The ID of the site we are copying from
 * copySitesToID: The ID of the site we are pasting into
 * copySitesTaskID: The ID of current copy maker task
 */
var copySitesAbort, copySitesFromID, copySitesToID, copySitesTaskID,
    copyMakerRunningAjax = 0;

/*
 * Returns a random string with chosen length
 * @param {Number} length
 * @return {String} the random string
 */
function copyMakerRandomString( length ) {
    var text, possible, i;

    text = '';
    possible = 'abcdefghijklmnopqrstuvwxyz0123456789';

    for ( i = 0; i < length; i++ ) {
        text += possible.charAt( Math.floor( Math.random() * possible.length ) );
    }
    return text;
}

/*
 * Shows the form for copying a site
 * @param {Number} fromSiteID
 */
function copyMakerCopySiteForm( fromSiteID ) {

    // The ID of the site we are copying from
    copySitesFromID = fromSiteID;

    // Shows the container layer and hides the loading data layer and the done message layer
    document.getElementById( 'be-mu-copy-sites-container' ).style.display = 'block';
    document.getElementById( 'be-mu-copy-sites-loading' ).style.display = 'none';
    document.getElementById( 'be-mu-copy-sites-done' ).style.display = 'none';

    // Puts the ID of the site we are copying from into the span in the form
    document.getElementById( 'be-mu-copy-from-site-id-span' ).innerHTML = copySitesFromID;

    jQuery( '#be-mu-copy-paste-into-new-link' ).attr( 'href', localizedCopyMaker.urlCopySite + copySitesFromID );

    // Resets to an empty string the text form element for the ID of the site to paste into
    document.getElementById( 'be-mu-copy-to-site-id' ).value = '';
}

// Builds and shows the confirmation message before the copy process starts and starts the process if the user confirms
function copyMakerConfirmCopySite() {

    var data, confirmResult, confirmMessage;

    // Shows the loading image and disables the button
    document.getElementById( 'be-mu-copy-loading-confirm' ).style.visibility = 'visible';
    document.getElementById( 'be-mu-copy-site-button' ).disabled = 'disabled';

    // The ID of the site we are pasting into
    copySitesToID = document.getElementById( 'be-mu-copy-to-site-id' ).value;

    // This is the data we will send in the ajax request
    data = {
    	'action': 'be_mu_copy_sites_confirm_action',
    	'from_site_id': copySitesFromID,
    	'to_site_id': copySitesToID,
        'security': localizedCopyMaker.ajaxNonce
    };

    // Means that an axaj request is running
    copyMakerRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        var responseObject, fromURL, toURL, toFolder, toPrefix;

        // The ajax request is not running any more
        copyMakerRunningAjax = 0;

        response = response.trim();

        // We are checking the response for an error code
        if ( '0' === response || 'no-access' === response || 'main-site' === response || 'from-site-not-exist' === response
            || 'to-site-not-exist' === response || 'invalid-from-id' === response || 'invalid-to-id' === response || 'same-ids' === response
            || 'no-site-url-copy-from' === response || 'no-site-url-copy-to' === response || 'upload-path-not-reliable' === response
            || 'invalid-nonce' === response ) {

            // We hide the loading image and make the button usable again
            document.getElementById( 'be-mu-copy-loading-confirm' ).style.visibility = 'hidden';
            document.getElementById( 'be-mu-copy-site-button' ).disabled = '';

            // We alert the error message based on the error code
            if ( 'no-access' === response ) {
                alert( localizedCopyMaker.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedCopyMaker.errorInvalidNonce );
            } else if ( 'main-site' === response ) {
                alert( localizedCopyMaker.errorMainSite );
            } else if ( 'from-site-not-exist' === response ) {
                alert( localizedCopyMaker.errorFromSiteNotExist );
            } else if ( 'to-site-not-exist' === response ) {
                alert( localizedCopyMaker.errorToSiteNotExist );
            } else if ( 'invalid-from-id' === response ) {
                alert( localizedCopyMaker.errorInvalidFromID );
            } else if ( 'invalid-to-id' === response ) {
                alert( localizedCopyMaker.errorInvalidToID );
            } else if ( 'same-ids' === response ) {
                alert( localizedCopyMaker.errorSameIDs );
            } else if ( 'no-site-url-copy-from' === response ) {
                alert( localizedCopyMaker.errorFromNoSiteURL );
            } else if ( 'no-site-url-copy-to' === response ) {
                alert( localizedCopyMaker.errorToNoSiteURL );
            } else if ( 'upload-path-not-reliable' === response ) {
                alert( localizedCopyMaker.errorUploadPathNotReliable );
            } else {
                alert( localizedCopyMaker.errorResponse );
            }

        // There is no error code
        } else {

            // We parse the response into an object
            responseObject = jQuery.parseJSON( response );

            // The URL of the site we will copy form
            fromURL = responseObject.fromURL;

            // The URL of the site we will paste into
            toURL = responseObject.toURL;

            // The uploads folder path of the site we will paste into
            toFolder = responseObject.toFolder;

            // The database table prefix of the site we will paste into
            toPrefix = responseObject.toPrefix;

            // We place the URLs in the message text (replacing the %1$s and %2$s strings)
            confirmMessage = localizedCopyMaker.warningCopySite.replace( '%1$s', '\n' + toURL + '\n' );
            confirmMessage = confirmMessage.replace( '%2$s', '\n' + fromURL );
            confirmMessage = confirmMessage.replace( '%3$s', '"' + toPrefix + '"' );
            confirmMessage = confirmMessage.replace( '%4$s', '"' + toFolder + '"' );

            // We hide the loading image and make the button usable again
            document.getElementById( 'be-mu-copy-loading-confirm' ).style.visibility = 'hidden';
            document.getElementById( 'be-mu-copy-site-button' ).disabled = '';

            // We ask the user for confirmation before we let him continue
            confirmResult = confirm( confirmMessage );

            // If the user clicked cancel, we stop everything
            if ( true !== confirmResult ) {
                return;
            }

            // We generate a new random task id string
            copySitesTaskID = copyMakerRandomString( 10 );

            // We start the copy process with 0 stages done and 0 parts of next stage done
            copyMakerProcessCopySite( 0, 0 );
        }
    }).fail( function() {
        copyMakerRunningAjax = 0;
        alert( localizedCopyMaker.errorServerFail );
        document.getElementById( 'be-mu-copy-loading-confirm' ).style.visibility = 'hidden';
        document.getElementById( 'be-mu-copy-site-button' ).disabled = '';
    });
}

/*
 * This function makes an ajax request to a php function that will do the actual copying of the site.tables
 * After the ajax request this function will call it self multiple times if needed untill all stages of the process are completed.
 * At the end it will show a message with links to the new site home page, dashboard, and edit site page.
 * @param {Number} stagesDone
 * @param {Number} nextStagePartsDone
 */
function copyMakerProcessCopySite( stagesDone, nextStagePartsDone ) {

    var data, confirmResult, confirmMessage;

    // If this is the first time we call this function for the current site copying, we se the abort variable to 0, we hide the form and show the loading image
    if ( 0 === parseInt( stagesDone ) && 0 === parseInt( nextStagePartsDone ) ) {
        copySitesAbort = 0;
        document.getElementById( 'be-mu-copy-sites-form' ).style.display = 'none';
        document.getElementById( 'be-mu-copy-sites-loading' ).style.display = 'block';
    }

    // This is the data we will send in the ajax request
    data = {
    	'action': 'be_mu_copy_sites_process_action',
    	'task_id': copySitesTaskID,
    	'stages_done': stagesDone,
    	'next_stage_parts_done': nextStagePartsDone,
    	'from_site_id': copySitesFromID,
    	'to_site_id': copySitesToID,
        'security': localizedCopyMaker.ajaxNonce
    };

    // Means that an axaj request is running
    copyMakerRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        var responseObject, newStagesDone, newNextStagePartsDone, limitReached, statusMessage, logLinkText;

        response = response.trim();

        // We are checking the response for an error code
        if ( '0' === response || 'no-access' === response || 'missing-post-data' === response || 'main-site' === response || 'from-site-not-exist' === response
            || 'to-site-not-exist' === response || 'empty-from-prefix' === response || 'empty-to-prefix' === response || 'empty-to-db-name' === response
            || 'invalid-from-id' === response || 'invalid-to-id' === response || 'invalid-stage' === response || 'same-upload-folders' === response
            || 'cannot-read-from-folder' === response || 'cannot-write-to-folder' === response || 'same-ids' === response || 'same-prefixes' === response
            || 'same-prefix-as-main' === response || 'invalid-to-prefix' === response || 'invalid-nonce' === response ) {

            // The ajax request is not running any more
            copyMakerRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( 1 !== copySitesAbort ) {
                if ( 'no-access' === response ) {
                    alert( localizedCopyMaker.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedCopyMaker.errorInvalidNonce );
                } else if ( 'missing-post-data' === response ) {
                    alert( localizedCopyMaker.errorMissingPOST );
                } else if ( 'main-site' === response ) {
                    alert( localizedCopyMaker.errorMainSite );
                } else if ( 'from-site-not-exist' === response ) {
                    alert( localizedCopyMaker.errorFromSiteNotExist );
                } else if ( 'to-site-not-exist' === response ) {
                    alert( localizedCopyMaker.errorToSiteNotExist );
                } else if ( 'empty-from-prefix' === response ) {
                    alert( localizedCopyMaker.errorEmptyFromPrefix );
                } else if ( 'empty-to-prefix' === response ) {
                    alert( localizedCopyMaker.errorEmptyToPrefix );
                } else if ( 'empty-to-db-name' === response ) {
                    alert( localizedCopyMaker.errorEmptyToDbName );
                } else if ( 'invalid-from-id' === response ) {
                    alert( localizedCopyMaker.errorInvalidFromID );
                } else if ( 'invalid-to-id' === response ) {
                    alert( localizedCopyMaker.errorInvalidToID );
                } else if ( 'same-ids' === response ) {
                    alert( localizedCopyMaker.errorSameIDs );
                } else if ( 'invalid-stage' === response ) {
                    alert( localizedCopyMaker.errorInvalidStage );
                } else if ( 'same-prefixes' === response ) {
                    alert( localizedCopyMaker.errorSamePrefixes );
                } else if ( 'same-prefix-as-main' === response ) {
                    alert( localizedCopyMaker.errorSamePrefixAsMain );
                } else if ( 'invalid-to-prefix' === response ) {
                    alert( localizedCopyMaker.errorInvalidToPrefix );
                } else if ( 'same-upload-folders' === response ) {
                    alert( localizedCopyMaker.errorSameUploadFolders );
                } else if ( 'cannot-read-from-folder' === response ) {
                    alert( localizedCopyMaker.errorCannotReadFromFolder );
                } else if ( 'cannot-write-to-folder' === response ) {
                    alert( localizedCopyMaker.errorCannotWriteToFolder );
                } else {
                    alert( localizedCopyMaker.errorResponse );
                }

                // We close the results layer after the error
                copySitesAbortClose();
            }
        } else {

            // If we haven't closed the layer and aborted we will show the results or continue the process in the new request
            if ( 1 !== copySitesAbort ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of tables processed in the last request
                limitReached = parseInt( responseObject.limitReached );

                // If a limit was reached in the previous request
                if ( 1 === limitReached ) {

                    // How many stages are done so far for the task
                    newStagesDone = parseInt( responseObject.newStagesDone );

                    // How many parts of the next stage are done so far
                    newNextStagePartsDone = parseInt( responseObject.newNextStagePartsDone );

                    // We build the progress message to show ar this time
                    statusMessage = localizedCopyMaker.statusSoFarMessage.replace( '%1$d', ( newStagesDone + 1 ) );
                    statusMessage = statusMessage.replace( '%2$d', newNextStagePartsDone );

                    // We display the progress message
                    document.getElementById( 'be-mu-copy-sites-processed-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-copy-sites-processed-so-far' ).innerHTML = statusMessage;

                    // And we call the same function we are inside now, but with a new done stages and parts counts, so we continue from where we left of
                    copyMakerProcessCopySite( newStagesDone, newNextStagePartsDone );

                // A limit was not reached, so we have to be done
                } else {

                    // We hide the loading image and show the done layer
                    document.getElementById( 'be-mu-copy-sites-loading' ).style.display = 'none';
                    document.getElementById( 'be-mu-copy-sites-done' ).style.display = 'block';

                    // We put the URLs to the action links for the new site inside the href attributes of the a tags
                    document.getElementById( 'be-mu-copy-sites-edit-action' ).href = responseObject.editURL;
                    document.getElementById( 'be-mu-copy-sites-dashboard-action' ).href = responseObject.dashboardURL;
                    document.getElementById( 'be-mu-copy-sites-visit-action' ).href = responseObject.visitURL;
                    document.getElementById( 'be-mu-copy-sites-log-action' ).href = responseObject.viewLogURL;

                    // We put the error count in the link anchor
                    logLinkText = document.getElementById( 'be-mu-copy-sites-log-action' ).innerHTML;
                    document.getElementById( 'be-mu-copy-sites-log-action' ).innerHTML = logLinkText.replace( '%d', responseObject.errorCount );
                }
            }

            // An ajax request is no longer running
            copyMakerRunningAjax = 0;
        }
    }).fail( function() {
        copyMakerRunningAjax = 0;
        alert( localizedCopyMaker.errorServerFail );
        copySitesAbortClose();
    });
}

// Sets the copySitesAbort var to 1, hides the results layer and shows the form layer to be ready for future use
function copySitesAbortClose() {
    copySitesAbort = 1;
    document.getElementById( 'be-mu-copy-sites-container' ).style.display = 'none';
    document.getElementById( 'be-mu-copy-sites-form' ).style.display = 'block';
}

/*
 * This function will run when the dropdown menu for the page number is changed and it will redirect the super admin to
 * the selected page url in the copy sites logs page
 */
function copyMakerGoToCopySitesLogsPage() {
    var element, pageNumber;

    // We get the selected option
    element = document.getElementById( 'be-mu-copy-sites-logs-page-number' );
    pageNumber = parseInt( element.options[ element.selectedIndex ].value );

    // We redirect the browser to the url of the selected page number
    window.location.href = localizedCopyMaker.pageURL + '&page_number=' + pageNumber;
}

/*
 * When the action links are clicked this function will ask for confirmation and redirect to the action url if it is given
 * @param {Number} action
 * @param {String} taskID
 */
function copyMakerCopySitesLogsActionLink( action, taskID ) {
    var confirmMessage;

    // Based on which action link is clicked we set the confirmMessage variable
    if ( 'delete' === action ) {
        confirmMessage = localizedCopyMaker.confirmDeleteLog;
    } else {
        alert( localizedCopyMaker.invalidAction );
        return;
    }

    // We ask the user to confirm the action and abort if the action was canceled
    if ( ! confirm( confirmMessage ) ) {
        return;
    }

    // If the action was confirmed we redirect to the url that will perform the appropriate action
    window.location.href = localizedCopyMaker.pageURL + '&page_number=' + localizedCopyMaker.pageNumber
        + '&task_id=' + taskID + '&action=' + action;
}

// Goes to the next or previous page of the current list of logs
function copyMakerNextPreviousPage( page ) {
    jQuery( "#be-mu-copy-sites-logs-page-number" ).val( page );
    copyMakerGoToCopySitesLogsPage();
}
