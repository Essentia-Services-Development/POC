
/*
 * global activatedInTaskId:true, activatedInMode:true, activatedInName:true, activatedInAbort:true, activatedInDoneCount:true,
 * activatedInActivatedCount:true, activatedInDeactivatedCount:true, activatedInRunningAjax:true, activatedInLoading:true, ajaxurl, localizedActivatedIn
 */

/*
 * activatedInTaskId: The task id in the database
 * activatedInMode: This var can be either "plugin" or "theme" and shows what is the type of thing we are checking
 * activatedInName: This contains either the plugin file or the theme name that we are checking
 * activatedInAbort: This is 1 if the close button has been clicked before we finished, so need to abort and not put the results in the layer
 * activatedInRunningAjax: This is 1 if there is currently running an ajax request that is not done yet
 * activatedInDoneCount: How many sites have we checked so far for the current task
 * activatedInActivatedCount: How many sites has the thing we are checking activated for the current task
 * activatedInDeactivatedCount: How many sites has the thing we are checking deactivated for the current task
 * activatedInLoading: The html code for the loading message
 */
var activatedInTaskId, activatedInMode, activatedInName, activatedInAbort, activatedInDoneCount, activatedInActivatedCount, activatedInDeactivatedCount,
    activatedInRunningAjax = 0,
    activatedInLoading = '<div class="be-mu-p20">'
        + '<p class="be-mu-center">' + '<img src="' + localizedActivatedIn.loadingGIF + '" /></p>'
        + '<p class="be-mu-center">' + localizedActivatedIn.checking + '</p>'
        + '<p class="be-mu-center" id="be-mu-activated-in-checked-so-far">&nbsp;</p>'
        + '<p class="be-mu-center">' + localizedActivatedIn.getClose + '</p>'
        + '</div>';

/*
 * Returns a random string with chosen length
 * @param {Number} length
 * @return {String} the random string
 */
function activatedInRandomString( length ) {
    var text, possible, i;

    text = '';
    possible = 'abcdefghijklmnopqrstuvwxyz0123456789';

    for ( i = 0; i < length; i++ ) {
        text += possible.charAt( Math.floor( Math.random() * possible.length ) );
    }

    return text;
}

/*
 * This is where it all starts when the user clicks the Activated in? link.
 * The function shows the results layer and puts inside a loading message for now and it calls another function to start checking the sites.
 * @param {String} pluginFileOrThemeName
 * @param {String} pluginOrTheme
 */
function activatedInStart( pluginFileOrThemeName, pluginOrTheme ) {

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === activatedInRunningAjax ) {

        // We set the global vars with the data needed to start the request (is it a theme or a plugin and which one)
        activatedInName = pluginFileOrThemeName;
        activatedInMode = pluginOrTheme;

        // These vars need to be set to 0 on every new task start
        activatedInAbort = activatedInDoneCount = activatedInActivatedCount = activatedInDeactivatedCount = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-activated-in-div-results' ).innerHTML = activatedInLoading;

        // And we make the layer with the loading message visible
        document.getElementById( 'be-mu-activated-in-container' ).style.display = 'inline';

        // We generate a new random task id string
        activatedInTaskId = activatedInRandomString( 10 );

        // We call the function to start checking the sites (skipping 0 of them - so from the beginninig)
        activatedInCheck( 0 );
    } else {
        alert( localizedActivatedIn.errorRequest );
    }
}

/*
 * Makes an ajax request to a php function to check where a plugin ot theme is activated and on success either calls itself again
 * or calls a javascript function for the results.
 * @param {Number} offset - How many sites to skip
 */
function activatedInCheck( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
    	'action': 'be_mu_activated_in_numbers_action',
    	'task_id': activatedInTaskId,
    	'plugin_or_theme': activatedInMode,
    	'offset': offset,
        'security': localizedActivatedIn.ajaxNonce,
    	'plugin_file_or_theme_name': activatedInName
    };

    // Means that an axaj request is running
    activatedInRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, activatedCount, deactivatedCount, currentOffset, limitReached, currentDoneTo;

        response = response.trim();

        // We are checking the response for an error code
        if ( '0' === response || 'no-access' === response || 'network-activated' === response || 'invalid-nonce' === response
            || 'no-database-table' === response ) {

            // The ajax request is not running any more
            activatedInRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( 1 !== activatedInAbort ) {
                if ( 'no-access' === response ) {
                    alert( localizedActivatedIn.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedActivatedIn.errorInvalidNonce );
                } else if ( 'network-activated' === response ) {
                    alert( localizedActivatedIn.errorNetworkActivated );
                } else if ( 'no-database-table' === response ) {
                    alert( localizedActivatedIn.errorNoDatabaseTable );
                } else {
                    alert( localizedActivatedIn.errorResponse );
                }

                // We close the results layer after the error
                activatedInAbortClose();
            }
        } else {

            // If we haven't closed the layer and aborted we will show the results we continue
            if ( 1 !== activatedInAbort ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // Number of sites the plugin or theme is activated in
                activatedCount = parseInt( responseObject.activatedCount );

                // Number of sites the plugin or theme is deactivated in
                deactivatedCount = parseInt( responseObject.deactivatedCount );

                // The current offset we got to while cheking the sites
                currentOffset = parseInt( responseObject.currentOffset );

                // This is 1 if a limit (time or number of sites) has been reached for this chunk of sites
                limitReached = parseInt( responseObject.limitReached );

                // How many sites have we checked in this last request (chunk)
                currentDoneTo = activatedCount + deactivatedCount;

                // How many sites have we checked so far for this task (we add the ones from the last request)
                activatedInDoneCount += currentDoneTo;

                // How many sites the plugin or theme is activated in so far for this task
                activatedInActivatedCount += activatedCount;

                // How many sites the plugin or theme is deactivated in so far for this task
                activatedInDeactivatedCount += deactivatedCount;

                // If a limit was reached in the last request this means we are not done checking all the sites
                if ( 1 === limitReached ) {

                    // We show how many sites we have checked so far
                    document.getElementById( 'be-mu-activated-in-checked-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-activated-in-checked-so-far' ).innerHTML = localizedActivatedIn.checkedSoFar + ' ' + activatedInDoneCount;

                    // And we call the same function we are inside now, but with a new offset, so we continue from where we left of
                    activatedInCheck( currentOffset + currentDoneTo );
                } else {

                    /*
                     * Since a limit was not reached and request ended with success this could only mean we are done checking all the sites.
                     * So we are calling the function to dispay the results.
                     */
                    activatedInResults();
                }
            }

            // An ajax request is no longer running
            activatedInRunningAjax = 0;
        }
    }).fail( function() {
        activatedInRunningAjax = 0;
        alert( localizedActivatedIn.errorServerFail );
        activatedInAbortClose();
    });
}

// Makes an ajax request to a php function and displays the results (the sites where the thing is activated in)
function activatedInResults() {
    var element, pageNumber, data;

    // We get the selected option for the page num if the dropdown menu exists, otherwise we show page 1
    if ( document.getElementById( 'be-mu-activated-in-page-number' ) ) {
        element = document.getElementById( 'be-mu-activated-in-page-number' );
        pageNumber = parseInt( element.options[ element.selectedIndex ].value );

        // We show the loading image while we change pages
        document.getElementById( 'be-mu-activated-in-loading-page-number' ).style.visibility = 'visible';
    } else {
        pageNumber = 1;
    }

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_activated_in_results_action',
        'task_id': activatedInTaskId,
        'plugin_or_theme': activatedInMode,
        'page_number': pageNumber,
        'count_activated': activatedInActivatedCount,
        'count_deactivated': activatedInDeactivatedCount,
        'security': localizedActivatedIn.ajaxNonce,
        'plugin_file_or_theme_name': activatedInName
    };

    // Means that an axaj request is running
    activatedInRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( '0' === response || 'no-access' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            activatedInRunningAjax = 0;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-activated-in-loading-page-number' ) ) {
                document.getElementById( 'be-mu-activated-in-loading-page-number' ).style.visibility = 'hidden';
            }

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedActivatedIn.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedActivatedIn.errorInvalidNonce );
            } else {
                alert( localizedActivatedIn.errorResponse );
            }

            // We close the results layer after the error
            activatedInAbortClose();

        } else {

            // We show the results in the results layer
            document.getElementById( 'be-mu-activated-in-div-results' ).innerHTML = response;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-activated-in-loading-page-number' ) ) {
                document.getElementById( 'be-mu-activated-in-loading-page-number' ).style.visibility = 'hidden';
            }

            // An ajax request is no longer running
            activatedInRunningAjax = 0;

        }
	}).fail( function() {
        activatedInRunningAjax = 0;
        alert( localizedActivatedIn.errorServerFail );
        activatedInAbortClose();
    });
}

// Sets the activatedInAbort var to 1, hides the results layer and puts inside it the loading code to be ready for future requests
function activatedInAbortClose() {
    activatedInAbort = 1;
    document.getElementById( 'be-mu-activated-in-container' ).style.display = 'none';
    document.getElementById( 'be-mu-activated-in-div-results' ).innerHTML = activatedInLoading;
}

// Goes to the next or previous page of the current list of results
function activatedInNextPreviousPage( page ) {
    jQuery( "#be-mu-activated-in-page-number" ).val( page );
    activatedInResults();
}

/*
 * Exports site IDs or URLs from the activated in results box by making an ajax request.
 * If needed it calls itself multiple times to handle large data without timeouts.
 * @param {String} taskId
 * @param {String} field
 * @param {Number} offset
 * @param {Number} totalSites
 */
function activatedInExportResults( taskID, field, offset, totalSites ) {
    var data;

    if ( 0 === activatedInRunningAjax ) {

        jQuery( "#be-mu-activated-in-export-" + field + "-link" ).html( localizedActivatedIn.processing + ' ('
            + Math.round( ( parseInt( offset ) / parseInt( totalSites ) ) * 100 ) + '%)' );
        jQuery( "#be-mu-activated-in-export-" + field + "-link" ).attr( 'href', '#' );
        jQuery( ".be-mu-activated-in-preview-page-navigation" ).css( "visibility", "hidden" );

        data = {
            'action': 'be_mu_activated_in_export_results_action',
            'task_id': taskID,
            'field': field,
            'offset': offset,
            'security': localizedActivatedIn.ajaxNonce
        };

        activatedInRunningAjax = 1;

        /*
         * We are making the ajax request.
         * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
         */
        jQuery.post( ajaxurl, data, function( response ) {
            var responseObject, requestProcessedSitesCount, requestProcessedSiteChunksCount, fileURL, currentOffset, isRequestLimitReached;
            response = response.trim();
            activatedInRunningAjax = 0;
            if ( 'no-access' === response || '0' === response || 'cannot-write' === response || 'invalid-nonce' === response ) {
                if ( 'no-access' === response ) {
                    alert( localizedActivatedIn.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedActivatedIn.errorInvalidNonce );
                } else if ( 'cannot-write' === response ) {
                    alert( localizedActivatedIn.errorWriteExport );
                } else {
                    alert( localizedActivatedIn.errorResponse );
                }
                jQuery( "#be-mu-activated-in-export-" + field + "-link" ).html( localizedActivatedIn.errorError );
                jQuery( ".be-mu-activated-in-preview-page-navigation" ).css( "visibility", "visible" );
            } else {
                responseObject = jQuery.parseJSON( response );
                requestProcessedSitesCount = parseInt( responseObject.requestProcessedSitesCount );
                requestProcessedSiteChunksCount = parseInt( responseObject.requestProcessedSiteChunksCount );
                fileURL = responseObject.fileURL;
                currentOffset = parseInt( responseObject.currentOffset );
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // The export task is done
                if ( 0 === isRequestLimitReached ) {
                    jQuery( "#be-mu-activated-in-export-" + field + "-link" ).attr( 'href', fileURL );
                    jQuery( "#be-mu-activated-in-export-" + field + "-link" ).attr( 'target', '_blank' );
                    jQuery( "#be-mu-activated-in-export-" + field + "-link" ).attr( 'download', '' );
                    if ( field === 'ids' ) {
                        jQuery( "#be-mu-activated-in-export-" + field + "-link" ).html( '<span class="be-mu-green">'
                            + localizedActivatedIn.downloadIDs + '</span>' );
                    } else {
                        jQuery( "#be-mu-activated-in-export-" + field + "-link" ).html( '<span class="be-mu-green">'
                            + localizedActivatedIn.downloadURLs + '</span>' );
                    }
                    jQuery( ".be-mu-activated-in-preview-page-navigation" ).css( "visibility", "visible" );

                // We are not done, the time limit was reached on the previous request. We need to call this funciton again with a new offset.
                } else {
                    if ( field === 'ids' ) {
                        activatedInExportResults( taskID, field, currentOffset + requestProcessedSiteChunksCount, totalSites );
                    } else {
                        activatedInExportResults( taskID, field, currentOffset + requestProcessedSitesCount, totalSites );
                    }
                }

            }
        }).fail( function() {
            activatedInRunningAjax = 0;
            alert( localizedActivatedIn.errorServerFail );
            jQuery( "#be-mu-activated-in-export-" + field + "-link" ).html( localizedActivatedIn.errorError );
            jQuery( ".be-mu-activated-in-preview-page-navigation" ).css( "visibility", "visible" );
        });
    } else {
        alert( localizedActivatedIn.errorRequest );
    }
}
