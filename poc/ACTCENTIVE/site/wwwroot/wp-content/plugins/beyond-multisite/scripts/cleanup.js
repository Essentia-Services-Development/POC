
/*
 * global cleanupCommentForm:true, cleanupRevisionForm:true, cleanupSiteForm:true, cleanupMode:true, cleanupTask:true,
 * cleanupRunningAjax:true, cleanupDone:true, cleanupAffected:true, cleanupAbort:true, cleanupContainerVisible:true, cleanupCommentLoading:true,
 * cleanupRevisionLoading:true, cleanupSiteLoading:true, cleanupTableDone:true, cleanupTableLoading:true, cleanupUserDone:true, cleanupUserLoading:true,
 * ajaxurl, localizedCleanup
 */

/*
 * cleanupCommentForm: An object with the chosen options in the comment deletion form
 * cleanupRevisionForm: An object with the chosen options in the revision deletion form
 * cleanupSiteForm: An object with the chosen options in the site deletion form
 * cleanupUserForm: An object with the chosen options in the user deletion form
 * cleanupMode: An object with the modes for each form. If the mode is "preview" we are doing a preview deletion, if it is "delete" we are executing deleting
 * cleanupSiteExtraFieldsNames: An array with names of extra fields added with a hook.
 * cleanupSiteExtraFieldsValues: An array with values of extra fields added with a hook.
 * cleanupTask: An object with the ID of the current deletion task
 * cleanupDone: An object that holds the number of sites (or database tables in the case of leftover database table deletion) are processed for the current task
 * cleanupAffected: An object that holds the number of affected data points (sites, comments, etc.) by deletion for the current task
 * cleanupAbort: An object that tells us if we need to abort a given deletion process (1 means to abort as soon as possible)
 * cleanupRunningAjax: If this is 1, it means that currently is running an ajax request, 0 means the opposite
 * cleanupContainerVisible: If it is 1, the container layer for the loading message and results is vissible (if 0, it is not)
 * cleanupCommentLoading: The html code for the loading message in comment deletion
 * cleanupRevisionLoading: The html code for the loading message in revision deletion
 * cleanupSiteLoading: The html code for the loading message in site deletion
 * cleanupTableLoading: The html code for the loading message in leftover database tables deletion
 * cleanupUserLoading: The html code for the loading message in user without a role deletion
 */
var cleanupTask = {},
    cleanupDone = {},
    cleanupAffected = {},
    cleanupAbort = {},
    cleanupCommentForm = {},
    cleanupRevisionForm = {},
    cleanupSiteForm = {},
    cleanupUserForm = {},
    cleanupMode = {
        comment: 'preview',
        revision: 'preview',
        site: 'preview',
        table: 'preview',
        user: 'preview',
    },
    cleanupSiteExtraFieldsNames = [],
    cleanupSiteExtraFieldsValues = [],
    cleanupRunningAjax = 0,
    cleanupContainerVisible = 0,
    cleanupCommentLoading = '<div class="be-mu-p20">'
        + '<p class="be-mu-center"><img src="' + localizedCleanup.loadingGIF + '" /></p>'
        + '<p class="be-mu-center">' + localizedCleanup.processing + '</p>'
        + '<p class="be-mu-center" id="be-mu-clean-processed-so-far">&nbsp;</p>'
        + '<p class="be-mu-center">' + localizedCleanup.abortComments + '</p>'
        + '</div>',
    cleanupRevisionLoading = '<div class="be-mu-p20">'
        + '<p class="be-mu-center"><img src="' + localizedCleanup.loadingGIF + '" /></p>'
        + '<p class="be-mu-center">' + localizedCleanup.processing + '</p>'
        + '<p class="be-mu-center" id="be-mu-clean-processed-so-far">&nbsp;</p>'
        + '<p class="be-mu-center">' + localizedCleanup.abortRevisions + '</p>'
        + '</div>',
    cleanupSiteLoading = '<div class="be-mu-p20">'
        + '<p class="be-mu-center"><img src="' + localizedCleanup.loadingGIF + '" /></p>'
        + '<p class="be-mu-center">' + localizedCleanup.processing + '</p>'
        + '<p class="be-mu-center" id="be-mu-clean-processed-so-far">&nbsp;</p>'
        + '<p class="be-mu-center">' + localizedCleanup.abortSites + '</p>'
        + '</div>',
    cleanupTableLoading = '<div class="be-mu-p20">'
        + '<p class="be-mu-center"><img src="' + localizedCleanup.loadingGIF + '" /></p>'
        + '<p class="be-mu-center">' + localizedCleanup.processing + '</p>'
        + '<p class="be-mu-center" id="be-mu-clean-processed-so-far">&nbsp;</p>'
        + '<p class="be-mu-center">' + localizedCleanup.abortTables + '</p>'
        + '</div>',
    cleanupUserLoading = '<div class="be-mu-p20">'
        + '<p class="be-mu-center"><img src="' + localizedCleanup.loadingGIF + '" /></p>'
        + '<p class="be-mu-center">' + localizedCleanup.processing + '</p>'
        + '<p class="be-mu-center" id="be-mu-clean-processed-so-far">&nbsp;</p>'
        + '<p class="be-mu-center">' + localizedCleanup.abortUsers + '</p>'
        + '</div>';

/*
 * Returns a random string with chosen length
 * @param {Number} length
 * @return {String} the random string
 */
function cleanupRandomString( length ) {
    var text, possible, i;

    text = '';
    possible = 'abcdefghijklmnopqrstuvwxyz0123456789';

    for ( i = 0; i < length; i++ ) {
        text += possible.charAt( Math.floor( Math.random() * possible.length ) );
    }
    return text;
}

/*
 * This function starts the process of deleting comments or previewing the deletion, depending on the mode var
 * @param {String} mode
 */
function cleanupStartComments( mode ) {
    var confirmResult, element;

    // If executing the deletion is selected we ask the user for confirmation before we let him continue
    if ( 'delete' === mode ) {
        confirmResult = confirm( localizedCleanup.warningComments );
        if ( true !== confirmResult ) {
            return;
        }
    }

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === cleanupRunningAjax ) {

        cleanupMode.comment = mode;

        // We get the selected settings from the form and set the global object with the data needed to start the request
        element = document.getElementById( 'be-mu-clean-comment-status' );
        cleanupCommentForm.status = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-clean-comment-url-count' );
        cleanupCommentForm.url = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-clean-comment-datetime' );
        cleanupCommentForm.dateTime = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-clean-comment-affect-sites-comment-amount' );
        cleanupCommentForm.affectAmount = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-clean-comment-affect-sites-comment-status' );
        cleanupCommentForm.affectStatus = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-clean-comment-affect-sites-id-option' );
        cleanupCommentForm.sitesOption = element.options[ element.selectedIndex ].value;
        cleanupCommentForm.sites = document.getElementById( 'be-mu-clean-comment-affect-sites-ids' ).value;

         // These vars need to be set to 0 on every new task start
        cleanupAbort.comment = cleanupDone.comment = cleanupAffected.commentSites = cleanupAffected.commentComments = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupCommentLoading;

        // And we make the layer with the loading message visible
        document.getElementById( 'be-mu-clean-container' ).style.display = 'inline';
        cleanupContainerVisible = 1;

        // We generate a new random task id string
        cleanupTask.comment = cleanupRandomString( 10 );

        // We call the function to start working on the sites (skipping 0 of them - so from the beginninig)
        cleanupCommentPrimaryProcess( 0 );
    } else {
        alert( localizedCleanup.errorRequest );
    }
}

/*
 * This function starts the process of deleting revisions or previewing the deletion, depending on the mode var
 * @param {String} mode
 */
function cleanupStartRevisions( mode ) {
    var confirmResult, element;

    // If executing the deletion is selected we ask the user for confirmation before we let him continue
    if ( 'delete' === mode ) {
        confirmResult = confirm( localizedCleanup.warningRevisions );
        if ( true !== confirmResult ) {
            return;
        }
    }

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === cleanupRunningAjax ) {

        cleanupMode.revision = mode;

        // We get the selected settings from the form and set the global object with the data needed to start the request
        element = document.getElementById( 'be-mu-clean-revision-datetime' );
        cleanupRevisionForm.dateTime = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-clean-revision-exclude' );
        cleanupRevisionForm.exclude = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-clean-revision-affect-sites-id-option' );
        cleanupRevisionForm.sitesOption = element.options[ element.selectedIndex ].value;
        cleanupRevisionForm.sites = document.getElementById( 'be-mu-clean-revision-affect-sites-ids' ).value;

        // These vars need to be set to 0 on every new task start
        cleanupAbort.revision = cleanupDone.revision = cleanupAffected.revisionSites = cleanupAffected.revisionRevisions = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupRevisionLoading;

        // And we make the layer with the loading message visible
        document.getElementById( 'be-mu-clean-container' ).style.display = 'inline';
        cleanupContainerVisible = 1;

        // We generate a new random task id string
        cleanupTask.revision = cleanupRandomString( 10 );

        // We call the function to start working on the sites (skipping 0 of them - so from the beginninig)
        cleanupRevisionProcess( 0 );
    } else {
        alert( localizedCleanup.errorRequest );
    }
}

/*
 * This function starts the process of deleting sites or previewing the deletion, depending on the mode var
 * @param {String} mode
 */
function cleanupStartSite( mode ) {
    var element1, element2, element3, element4, element5, element6, element7, element8, element9, element10, confirmResult,
        deletionType, deletionTime;

    // We get the selected settings from the drop down menus in the form
    element1 = document.getElementById( 'be-mu-clean-site-attributes' );
    element2 = document.getElementById( 'be-mu-clean-site-registered' );
    element3 = document.getElementById( 'be-mu-clean-site-updated' );
    element4 = document.getElementById( 'be-mu-clean-site-posts' );
    element5 = document.getElementById( 'be-mu-clean-site-pages' );
    element6 = document.getElementById( 'be-mu-clean-site-comments' );
    element7 = document.getElementById( 'be-mu-clean-site-affect-sites-id-option' );
    element8 = document.getElementById( 'be-mu-clean-site-deletion-type' );
    element9 = document.getElementById( 'be-mu-clean-site-deletion-time' );
    element10 = document.getElementById( 'be-mu-clean-site-skip-cancelled' );

    // If executing the deletion is selected we ask the user for confirmation before we let him continue
    if ( 'delete' === mode ) {
        deletionType = element8.options[ element8.selectedIndex ].value;
        deletionTime = element9.options[ element9.selectedIndex ].value;

        // If it is permanent deletion we show a more shocking message
        if ( 'Permanent deletion' === deletionType ) {
            if ( 'No cancellation. Execute now!' === deletionTime ) {
                confirmResult = confirm( localizedCleanup.warningSites );
            } else {
                confirmResult = confirm( localizedCleanup.warningScheduleSites );
            }

        // If it is just marking as deleted/archived we show a more regular message
        } else {
            if ( 'No cancellation. Execute now!' === deletionTime ) {
                if ( 'Mark as archived (change last updated time)' === deletionType || 'Mark as archived (keep last updated time)' === deletionType ) {
                    confirmResult = confirm( localizedCleanup.confirmMarkArchived );
                } else {
                    confirmResult = confirm( localizedCleanup.confirmMarkDeleted );
                }
            } else {
                if ( 'Mark as archived (change last updated time)' === deletionType || 'Mark as archived (keep last updated time)' === deletionType ) {
                    confirmResult = confirm( localizedCleanup.confirmScheduleArchived );
                } else {
                    confirmResult = confirm( localizedCleanup.confirmScheduleDeleted );
                }
            }
        }

        // If the user didn't click ok, we stop
        if ( true !== confirmResult ) {
            return;
        }
    }

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === cleanupRunningAjax ) {

        cleanupMode.site = mode;

        // We set the global object with the data needed to start the request
        cleanupSiteForm.attributes = element1.options[ element1.selectedIndex ].value;
        cleanupSiteForm.registered = element2.options[ element2.selectedIndex ].value;
        cleanupSiteForm.updated = element3.options[ element3.selectedIndex ].value;
        cleanupSiteForm.posts = element4.options[ element4.selectedIndex ].value;
        cleanupSiteForm.pages = element5.options[ element5.selectedIndex ].value;
        cleanupSiteForm.comments = element6.options[ element6.selectedIndex ].value;
        cleanupSiteForm.sitesOption = element7.options[ element7.selectedIndex ].value;
        cleanupSiteForm.sites = document.getElementById( 'be-mu-clean-site-affect-sites-ids' ).value;
        cleanupSiteForm.deletionType = element8.options[ element8.selectedIndex ].value;
        cleanupSiteForm.deletionTime = element9.options[ element9.selectedIndex ].value;
        cleanupSiteForm.skipCancelled = element10.options[ element10.selectedIndex ].value;

        // We get the data for extra fields added via hooks
        if ( localizedCleanup.extraFieldsSiteDeletion.length > 0 ) {
            localizedCleanup.extraFieldsSiteDeletion.forEach(function(entry) {
                if ( jQuery( '#' + entry ).length > 0 ) {
                    cleanupSiteExtraFieldsValues.push( jQuery( '#' + entry ).val() );
                    cleanupSiteExtraFieldsNames.push( entry );
                }
            });
            cleanupSiteForm.extraFieldsValues = cleanupSiteExtraFieldsValues.join("[be-mu-separator]");
            cleanupSiteForm.extraFieldsNames = cleanupSiteExtraFieldsNames.join("[be-mu-separator]");
        } else {
            cleanupSiteForm.extraFieldsValues = '';
            cleanupSiteForm.extraFieldsNames = '';
        }

        // These vars need to be set to 0 on every new task start
        cleanupAbort.site = cleanupDone.site = cleanupAffected.site = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupSiteLoading;

        // And we make the layer with the loading message visible
        document.getElementById( 'be-mu-clean-container' ).style.display = 'inline';
        cleanupContainerVisible = 1;

        // We generate a new random task id string
        cleanupTask.site = cleanupRandomString( 10 );

        // We call the function to start working on the sites (skipping 0 of them - so from the beginninig)
        cleanupSiteProcess( 0 );
    } else {
        alert( localizedCleanup.errorRequest );
    }
}

/*
 * This function starts the process of deleting leftover database tables or previewing the deletion, depending on the mode var
 * @param {String} mode
 */
function cleanupStartTable( mode ) {
    var confirmResult;

    // If executing the deletion is selected we ask the user for confirmation before we let him continue
    if ( 'delete' === mode ) {

        confirmResult = confirm( localizedCleanup.confirmTableDeletion );

        // If the user didn't click ok, we stop
        if ( true !== confirmResult ) {
            return;
        }
    }

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === cleanupRunningAjax ) {

        cleanupMode.table = mode;

        // These vars need to be set to 0 on every new task start
        cleanupAbort.table = cleanupDone.table = cleanupAffected.tableTables = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupTableLoading;

        // And we make the layer with the loading message visible
        document.getElementById( 'be-mu-clean-container' ).style.display = 'inline';
        cleanupContainerVisible = 1;

        // We generate a new random task id string
        cleanupTask.table = cleanupRandomString( 10 );

        // We call the function to start working on the database tables (skipping 0 of them - so from the beginninig)
        cleanupTableProcess( 0 );
    } else {
        alert( localizedCleanup.errorRequest );
    }
}

/*
 * This function starts the process of deleting users without a role or previewing the deletion, depending on the mode var
 * @param {String} mode
 */
function cleanupStartUsers( mode ) {
    var confirmResult;

    // If executing the deletion is selected we ask the user for confirmation before we let him continue
    if ( 'delete' === mode ) {

        confirmResult = confirm( localizedCleanup.confirmUserDeletion );

        // If the user didn't click ok, we stop
        if ( true !== confirmResult ) {
            return;
        }
    }

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === cleanupRunningAjax ) {

        cleanupMode.user = mode;

        cleanupUserForm.role = jQuery( '#be-mu-cleanup-users-role' ).val();
        cleanupUserForm.roleList = jQuery( '#be-mu-cleanup-users-roles-list' ).val();

        // These vars need to be set to 0 on every new task start
        cleanupAbort.user = cleanupDone.user = cleanupAffected.userUsers = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupUserLoading;

        // And we make the layer with the loading message visible
        document.getElementById( 'be-mu-clean-container' ).style.display = 'inline';
        cleanupContainerVisible = 1;

        // We generate a new random task id string
        cleanupTask.user = cleanupRandomString( 10 );

        // We call the function to start working on the database users (skipping 0 of them - so from the beginninig)
        cleanupUserProcess( 0 );
    } else {
        alert( localizedCleanup.errorRequest );
    }
}

/*
 * This function makes an ajax request to a php function that will go through the database tables and apply the leftover database tables
 * deletion action if needed.
 * After the ajax request this function will call it self multiple times if needed untill all tables are processed and
 * at the end will call cleanupTableResults() to display the results.
 * @param {Number} offset - How many tables to skip
 */
function cleanupTableProcess( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_table_action',
        'mode': cleanupMode.table,
        'task_id': cleanupTask.table,
        'offset': offset,
        'security': localizedCleanup.ajaxNonce,
    };

    // Means that an axaj request is running now
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, requestProcessedTablesCount, requestAffectedTablesCount, currentOffset, newOffset, isRequestLimitReached;

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // The ajax request is not running any more
            cleanupRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( 1 !== cleanupAbort.table ) {

                if ( 'no-access' === response ) {
                    alert( localizedCleanup.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedCleanup.errorInvalidNonce );
                } else {
                    alert( localizedCleanup.errorResponse );
                }

                // Close the loading layer after closing the alert
                cleanupCloseAbortTables();
            }
        } else {

            // Make sure we haven't aborted the task
            if ( 1 !== cleanupAbort.table ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of tables processed in the last request
                requestProcessedTablesCount = parseInt( responseObject.requestProcessedTablesCount );

                // The number of tables that are/would be affected by deletion in the last request
                requestAffectedTablesCount = parseInt( responseObject.requestAffectedTablesCount );

                // The number of tables we skipped in the last request
                currentOffset = parseInt( responseObject.currentOffset );

                // 1 means a limit was reached and the request stopped, 0 is the opposite
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // How many tables are processed so far for the current task
                cleanupDone.table += requestProcessedTablesCount;

                // How many tables are/would be affected by deletion for the current task
                cleanupAffected.tableTables += requestAffectedTablesCount;

                // If a limit was reached in the last request this means we are not done processing all the tables
                if ( 1 === isRequestLimitReached ) {

                    // We show how many tables we have processed so far
                    document.getElementById( 'be-mu-clean-processed-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-clean-processed-so-far' ).innerHTML = localizedCleanup.tablesProcessed + ' ' + cleanupDone.table;

                    // We calculate the new offset to use in the next request based on how many tables we processed last time and whether we deleted some or not
                    if ( 'delete' === cleanupMode.table ) {
                        newOffset = currentOffset + requestProcessedTablesCount - requestAffectedTablesCount;
                    } else {
                        newOffset = currentOffset + requestProcessedTablesCount;
                    }

                    // And we call the same function we are inside now, but with a new offset, so we continue from where we left of
                    cleanupTableProcess( newOffset );
                } else {

                    /*
                     * Since a limit was not reached and request ended with success this could only mean we are done processing all the tables.
                     * So we are calling the function to display the results.
                     */
                    cleanupTableResults();
                }
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        cleanupCloseAbortTables();
    });
}

/*
 * This function makes an ajax request to a php function that will go through the users and apply the no role user deletion action if needed.
 * After the ajax request this function will call it self multiple times if needed untill all users are processed and
 * at the end will call cleanupUserResults() to display the results.
 * @param {Number} offset - How many users to skip
 */
function cleanupUserProcess( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_user_action',
        'mode': cleanupMode.user,
        'task_id': cleanupTask.user,
        'role': cleanupUserForm.role,
        'role_list': cleanupUserForm.roleList,
        'offset': offset,
        'security': localizedCleanup.ajaxNonce,
    };

    // Means that an axaj request is running now
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, requestProcessedUsersCount, requestAffectedUsersCount, currentOffset, newOffset, isRequestLimitReached;

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response || 'invalid-role-data' === response ) {

            // The ajax request is not running any more
            cleanupRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( 1 !== cleanupAbort.user ) {

                if ( 'no-access' === response ) {
                    alert( localizedCleanup.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedCleanup.errorInvalidNonce );
                }  else if ( 'invalid-role-data' === response ) {
                    alert( localizedCleanup.errorInvalidUserRoleData );
                } else {
                    alert( localizedCleanup.errorResponse );
                }

                // Close the loading layer after closing the alert
                cleanupCloseAbortUsers();
            }
        } else {

            // Make sure we haven't aborted the task
            if ( 1 !== cleanupAbort.user ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of users processed in the last request
                requestProcessedUsersCount = parseInt( responseObject.requestProcessedUsersCount );

                // The number of users that are/would be affected by deletion in the last request
                requestAffectedUsersCount = parseInt( responseObject.requestAffectedUsersCount );

                // The number of users we skipped in the last request
                currentOffset = parseInt( responseObject.currentOffset );

                // 1 means a limit was reached and the request stopped, 0 is the opposite
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // How many users are processed so far for the current task
                cleanupDone.user += requestProcessedUsersCount;

                // How many users are/would be affected by deletion for the current task
                cleanupAffected.userUsers += requestAffectedUsersCount;

                // If a limit was reached in the last request this means we are not done processing all the users
                if ( 1 === isRequestLimitReached ) {

                    // We show how many users we have processed so far
                    document.getElementById( 'be-mu-clean-processed-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-clean-processed-so-far' ).innerHTML = localizedCleanup.usersProcessed + ' ' + cleanupDone.user;

                    // We calculate the new offset to use in the next request based on how many users we processed last time and whether we deleted some or not
                    if ( 'delete' === cleanupMode.user ) {
                        newOffset = currentOffset + requestProcessedUsersCount - requestAffectedUsersCount;
                    } else {
                        newOffset = currentOffset + requestProcessedUsersCount;
                    }

                    // And we call the same function we are inside now, but with a new offset, so we continue from where we left of
                    cleanupUserProcess( newOffset );
                } else {

                    /*
                     * Since a limit was not reached and request ended with success this could only mean we are done processing all the users.
                     * So we are calling the function to display the results.
                     */
                    cleanupUserResults();
                }
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        cleanupCloseAbortUsers();
    });
}

// Makes an ajax request to a php function and displays the results (which tables were/would be affected by the leftover database tables deletion)
function cleanupTableResults() {
    var element, pageNumber, data;

    // Get the selected page num if the dropdown menu exists, otherwise we show page 1
    if ( document.getElementById( 'be-mu-clean-page-number' ) ) {

        element = document.getElementById( 'be-mu-clean-page-number' );
        pageNumber = parseInt( element.options[ element.selectedIndex ].value );

        // We show the loading image while we change pages
        document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'visible';
    } else {
        pageNumber = 1;
    }

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_table_results_action',
        'mode': cleanupMode.table,
        'task_id': cleanupTask.table,
        'page_number': pageNumber,
        'count_affected_tables': cleanupAffected.tableTables,
        'security': localizedCleanup.ajaxNonce
    };

    // Means that an axaj request is running
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            cleanupRunningAjax = 0;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedCleanup.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedCleanup.errorInvalidNonce );
            } else {
                alert( localizedCleanup.errorResponse );
            }
        } else {

            // We show the results in the results layer
            document.getElementById( 'be-mu-clean-div-results' ).innerHTML = response;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
            document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
        }
    });
}

// Makes an ajax request to a php function and displays the results (which users were/would be affected by the no role users deletion)
function cleanupUserResults() {
    var element, pageNumber, data;

    // Get the selected page num if the dropdown menu exists, otherwise we show page 1
    if ( document.getElementById( 'be-mu-clean-page-number' ) ) {

        element = document.getElementById( 'be-mu-clean-page-number' );
        pageNumber = parseInt( element.options[ element.selectedIndex ].value );

        // We show the loading image while we change pages
        document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'visible';
    } else {
        pageNumber = 1;
    }

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_user_results_action',
        'mode': cleanupMode.user,
        'task_id': cleanupTask.user,
        'page_number': pageNumber,
        'count_affected_users': cleanupAffected.userUsers,
        'security': localizedCleanup.ajaxNonce
    };

    // Means that an axaj request is running
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            cleanupRunningAjax = 0;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedCleanup.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedCleanup.errorInvalidNonce );
            } else {
                alert( localizedCleanup.errorResponse );
            }
        } else {

            // We show the results in the results layer
            document.getElementById( 'be-mu-clean-div-results' ).innerHTML = response;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
            document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
        }
    });
}

/*
 * This function makes an ajax request to a php function that will go through the sites and apply the primary comment deletion action if needed.
 * After the ajax request this function will call it self multiple times if needed untill all sites are processed and
 * at the end will either call cleanupCommentResults() to display the results if this is a preview deletion,
 * or call cleanupCommentSecondaryProcess() in order to start the secondary deletion process if it is an actual deletion process.
 * @param {Number} offset - How many sites to skip
 */
function cleanupCommentPrimaryProcess( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_comment_primary_action',
        'mode': cleanupMode.comment,
        'task_id': cleanupTask.comment,
        'comment_status': cleanupCommentForm.status,
        'comment_url_count': cleanupCommentForm.url,
        'comment_datetime': cleanupCommentForm.dateTime,
        'affect_sites_comment_amount': cleanupCommentForm.affectAmount,
        'affect_sites_comment_status': cleanupCommentForm.affectStatus,
        'affect_sites_id_option': cleanupCommentForm.sitesOption,
        'affect_sites_ids': cleanupCommentForm.sites,
        'offset': offset,
        'security': localizedCleanup.ajaxNonce,
    };

    // Means that an axaj request is running now
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, requestProcessedSitesCount, requestAffectedSitesCount, requestAffectedCommentsCount, currentOffset, isRequestLimitReached;

        response = response.trim();

        if ( 'no-access' === response || 'invalid-data' === response || 'site-ids-filled' === response || 'site-ids-empty' === response
            || '0' === response || 'invalid-nonce' === response ) {

            // The ajax request is not running any more
            cleanupRunningAjax = 0;

            if ( cleanupAbort.comment !== 1 ) {
                if ( 'no-access' === response ) {
                    alert( localizedCleanup.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedCleanup.errorInvalidNonce );
                } else if ( 'invalid-data' === response ) {
                    alert( localizedCleanup.errorData );
                } else if ( 'site-ids-filled' === response ) {
                    alert( localizedCleanup.errorSiteFilled );
                } else if ( 'site-ids-empty' === response ) {
                    alert( localizedCleanup.errorSiteEmpty );
                } else {
                    alert( localizedCleanup.errorResponse );
                }

                // Close the loading layer after closing the alert
                cleanupCloseAbortComments();
            }
        } else {

            // Make sure we haven't aborted the task
            if ( 1 !== cleanupAbort.comment ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of sites processed in the last request
                requestProcessedSitesCount = parseInt( responseObject.requestProcessedSitesCount );

                // How many sites are/would be affected by deletion for the last request
                requestAffectedSitesCount = parseInt( responseObject.requestAffectedSitesCount );

                // How many comments are/would be affected by deletion for the last request
                requestAffectedCommentsCount = parseInt( responseObject.requestAffectedCommentsCount );

                // The number of sites we skipped in the last request
                currentOffset = parseInt( responseObject.currentOffset );

                // 1 means a limit was reached and the request stopped, 0 is the opposite
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // How many sites are processed so far for the current task
                cleanupDone.comment += requestProcessedSitesCount;

                // How many sites are/would be affected by deletion so far for the current task
                cleanupAffected.commentSites += requestAffectedSitesCount;

                // How many sites are/would be affected by deletion so far for the current task
                cleanupAffected.commentComments += requestAffectedCommentsCount;

                // If a limit was reached in the last request this means we are not done processing all the sites
                if ( 1 === isRequestLimitReached ) {

                    // We show how many sites we have processed so far with different message for deletion and preview
                    document.getElementById( 'be-mu-clean-processed-so-far' ).style.display = 'block';
                    if ( 'delete' === cleanupMode.comment ) {
                        document.getElementById( 'be-mu-clean-processed-so-far' ).innerHTML = localizedCleanup.sitesProcessedPrimary + ' ' + cleanupDone.comment;
                    } else {
                        document.getElementById( 'be-mu-clean-processed-so-far' ).innerHTML = localizedCleanup.sitesProcessed + ' ' + cleanupDone.comment;
                    }

                    // And we call the same function we are inside now, but with a new offset, so we continue from where we left of
                    cleanupCommentPrimaryProcess( currentOffset + requestProcessedSitesCount );
                } else {

                    // If it is execute deletion and there were affected sites
                    if ( 'delete' === cleanupMode.comment && cleanupAffected.commentSites > 0 ) {

                        // We reset the counter for the sites processed in the secondary comment cleanup
                        cleanupDone.commentSecondary = 0;

                        // We start the secondary comment cleanup
                        cleanupCommentSecondaryProcess( 0 );
                    } else {

                        // Since a limit was not reached and request ended with success this could only mean we are done processing all the sites
                        // So we are calling the function to display the results
                        cleanupCommentResults();
                    }
                }
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        cleanupCloseAbortComments();
    });
}

/*
 * This function makes an ajax request to a php function that will go through the sites and apply the secondary comment deletion action if needed.
 * After the ajax request this function will call it self multiple times if needed untill all sites are processed and
 * at the end will call cleanupCommentResults() to display the results.
 * @param {Number} offset - How many sites to skip
 */
function cleanupCommentSecondaryProcess( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_comment_secondary_action',
        'mode': cleanupMode.comment,
        'task_id': cleanupTask.comment,
        'offset': offset,
        'comment_status': cleanupCommentForm.status,
        'security': localizedCleanup.ajaxNonce,
    };

    // Means that an axaj request is running now
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, requestProcessedSitesCount, currentOffset, isRequestLimitReached;

        response = response.trim();

        if ( 'no-access' === response || 'no-sites' === response || '0' === response || 'invalid-nonce' === response ) {

            // The ajax request is not running any more
            cleanupRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( 1 !== cleanupAbort.comment ) {
                if ( 'no-access' === response ) {
                    alert( localizedCleanup.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedCleanup.errorInvalidNonce );
                } else if ( 'no-sites' === response ) {
                    alert( localizedCleanup.errorSiteFilled );
                } else {
                    alert( localizedCleanup.errorResponse );
                }

                // Close the loading layer after closing the alert
                cleanupCloseAbortComments();
            }
        } else {

            // Make sure we haven't aborted the task
            if ( 1 !== cleanupAbort.comment ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of sites processed in the last request
                requestProcessedSitesCount = parseInt( responseObject.requestProcessedSitesCount );

                // The number of sites we skipped in the last request
                currentOffset = parseInt( responseObject.currentOffset );

                // 1 means a limit was reached and the request stopped, 0 is the opposite
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // How many sites are processed so far for the current task
                cleanupDone.commentSecondary += requestProcessedSitesCount;

                // If a limit was reached in the last request this means we are not done processing all the sites
                if ( 1 === isRequestLimitReached ) {

                    // We show how many sites we have processed so far by secondary deletion
                    document.getElementById( 'be-mu-clean-processed-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-clean-processed-so-far' ).innerHTML = localizedCleanup.sitesProcessedSecondary + ' '
                        + cleanupDone.commentSecondary;

                    // And we call the same function we are inside now, but with a new offset, so we continue from where we left of
                    cleanupCommentSecondaryProcess( currentOffset + requestProcessedSitesCount );
                } else {

                    /*
                     * Since a limit was not reached and request ended with success this could only mean we are done processing all the sites.
                     * So we are calling the function to display the results.
                     */
                    cleanupCommentResults();
                }
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        cleanupCloseAbortComments();
    });
}

/*
 * This function makes an ajax request to a php function that will go through the sites and apply the revision deletion action if needed.
 * After the ajax request this function will call it self multiple times if needed untill all sites are processed and
 * at the end will call cleanupRevisionResults() to display the results.
 * @param {Number} offset - How many sites to skip
 */
function cleanupRevisionProcess( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_revision_action',
        'mode': cleanupMode.revision,
        'task_id': cleanupTask.revision,
        'revision_datetime': cleanupRevisionForm.dateTime,
        'revision_exclude': cleanupRevisionForm.exclude,
        'affect_sites_id_option': cleanupRevisionForm.sitesOption,
        'affect_sites_ids': cleanupRevisionForm.sites,
        'offset': offset,
        'security': localizedCleanup.ajaxNonce,
    };

    // Means that an axaj request is running now
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, requestProcessedSitesCount, requestAffectedSitesCount, requestAffectedRevisionsCount, currentOffset, isRequestLimitReached;

        response = response.trim();

        if ( 'no-access' === response || 'invalid-data' === response || 'site-ids-filled' === response || 'site-ids-empty' === response
            || '0' === response || 'invalid-nonce' === response ) {

            // The ajax request is not running any more
            cleanupRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( cleanupAbort.revision !== 1 ) {

                if ( 'no-access' === response ) {
                    alert( localizedCleanup.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedCleanup.errorInvalidNonce );
                } else if ( 'invalid-data' === response ) {
                    alert( localizedCleanup.errorData );
                } else if ( 'site-ids-filled' === response ) {
                    alert( localizedCleanup.errorSiteFilled );
                } else if ( 'site-ids-empty' === response ) {
                    alert( localizedCleanup.errorSiteEmpty );
                } else {
                    alert( localizedCleanup.errorResponse );
                }

                // Close the loading layer after closing the alert
                cleanupCloseAbortRevisions();
            }
        } else {

            // Make sure we haven't aborted the task
            if( 1 !== cleanupAbort.revision ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of sites processed in the last request
                requestProcessedSitesCount = parseInt( responseObject.requestProcessedSitesCount );

                // The number of sites that are/would be affected by deletion
                requestAffectedSitesCount = parseInt( responseObject.requestAffectedSitesCount );

                // The number of revisions that are/would be affected by deletion
                requestAffectedRevisionsCount = parseInt( responseObject.requestAffectedRevisionsCount );

                // The number of sites we skipped in the last request
                currentOffset = parseInt( responseObject.currentOffset );

                // 1 means a limit was reached and the request stopped, 0 is the opposite
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // How many sites are processed so far for the current task
                cleanupDone.revision += requestProcessedSitesCount;

                // How many sites are/would be affected by deletion for the current task
                cleanupAffected.revisionSites += requestAffectedSitesCount;

                // How many revisions are/would be affected by deletion for the current task
                cleanupAffected.revisionRevisions += requestAffectedRevisionsCount;

                // If a limit was reached in the last request this means we are not done processing all the sites
                if ( 1 === isRequestLimitReached ) {

                    // We show how many sites we have processed so far
                    document.getElementById( 'be-mu-clean-processed-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-clean-processed-so-far' ).innerHTML = localizedCleanup.sitesProcessed + ' ' + cleanupDone.revision;

                    // And we call the same function we are inside now, but with a new offset, so we continue from where we left of
                    cleanupRevisionProcess( currentOffset + requestProcessedSitesCount );
                } else {

                    /*
                     * Since a limit was not reached and request ended with success this could only mean we are done processing all the sites.
                     * So we are calling the function to display the results.
                     */
                    cleanupRevisionResults();
                }
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        cleanupCloseAbortRevisions();
    });
}

/*
 * This function makes an ajax request to a php function that will go through the sites and apply the site deletion action if needed.
 * After the ajax request this function will call it self multiple times if needed untill all sites are processed and
 * at the end will call cleanupSiteResults() to display the results.
 * @param {Number} offset - How many sites to skip
 */
function cleanupSiteProcess( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_site_action',
        'mode': cleanupMode.site,
        'task_id': cleanupTask.site,
        'site_attributes': cleanupSiteForm.attributes,
        'site_registered': cleanupSiteForm.registered,
        'site_updated': cleanupSiteForm.updated,
        'site_posts': cleanupSiteForm.posts,
        'site_pages': cleanupSiteForm.pages,
        'site_comments': cleanupSiteForm.comments,
        'affect_sites_id_option': cleanupSiteForm.sitesOption,
        'affect_sites_ids': cleanupSiteForm.sites,
        'site_delete_type': cleanupSiteForm.deletionType,
        'site_delete_time': cleanupSiteForm.deletionTime,
        'site_skip_cancelled': cleanupSiteForm.skipCancelled,
        'extra_field_values': cleanupSiteForm.extraFieldsValues,
        'extra_field_names': cleanupSiteForm.extraFieldsNames,
        'offset': offset,
        'security': localizedCleanup.ajaxNonce,
    };

    // Means that an axaj request is running now
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, requestProcessedSitesCount, requestAffectedSitesCount, currentOffset, isRequestLimitReached;

        response = response.trim();

        if ( 'no-access' === response || 'invalid-data' === response || 'already-deleted' === response || 'already-archived' === response
            || 'no-schedule' === response || 'another-task' === response || 'site-ids-filled' === response || 'invalid-nonce' === response
            || 'main-site' === response || 'site-ids-empty' === response || '0' === response || response in localizedCleanup.extraErrorsSiteDeletionSkip ) {

            // The ajax request is not running any more
            cleanupRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( 1 !== cleanupAbort.site ) {

                if ( 'no-access' === response ) {
                    alert( localizedCleanup.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedCleanup.errorInvalidNonce );
                } else if ( 'invalid-data' === response ) {
                    alert( localizedCleanup.errorData );
                } else if ( 'already-deleted' === response ) {
                    alert( localizedCleanup.errorDeleted );
                } else if ( 'already-archived' === response ) {
                    alert( localizedCleanup.errorArchived );
                } else if ( 'no-schedule' === response ) {
                    alert( localizedCleanup.errorNoSchedule );
                } else if ( 'another-task' === response ) {
                    alert( localizedCleanup.errorAnotherTask );
                } else if ( 'site-ids-filled' === response ) {
                    alert( localizedCleanup.errorSiteFilled);
                } else if ( 'main-site' === response ) {
                    alert( localizedCleanup.errorMainSite );
                } else if ( 'site-ids-empty' === response ) {
                    alert( localizedCleanup.errorSiteEmpty );
                } else {
                    if ( response in localizedCleanup.extraErrorsSiteDeletionSkip ) {
                        alert( localizedCleanup.extraErrorsSiteDeletionSkip[ response ] );
                    } else {
                        alert( localizedCleanup.errorResponse );
                    }
                }

                // Close the loading layer after closing the alert
                cleanupCloseAbortSites( 'no-reload' );
            }
        } else {

            // Make sure we haven't aborted the task
            if ( 1 !== cleanupAbort.site ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of sites processed in the last request
                requestProcessedSitesCount = parseInt( responseObject.requestProcessedSitesCount );

                // The number of sites that are/would be affected by deletion
                requestAffectedSitesCount = parseInt( responseObject.requestAffectedSitesCount );

                // The number of sites we skipped in the last request
                currentOffset = parseInt( responseObject.currentOffset );

                // 1 means a limit was reached and the request stopped, 0 is the opposite
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // How many sites are processed so far for the current task
                cleanupDone.site += requestProcessedSitesCount;

                // How many sites are/would be affected by deletion for the current task
                cleanupAffected.site += requestAffectedSitesCount;

                // If a limit was reached in the last request this means we are not done processing all the sites
                if ( 1 === isRequestLimitReached ) {

                    // We show how many sites we have processed so far
                    document.getElementById( 'be-mu-clean-processed-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-clean-processed-so-far' ).innerHTML = localizedCleanup.sitesProcessed + ' ' + cleanupDone.site;

                    /*
                     * And we call the same function we are inside now, but with a new offset, so we continue from where we left of.
                     * If the chosen settings indicate that the previous request has deleted sites in a way that affects the number of
                     * sites to skip the next request, we skip less sites.
                     */
                    if ( 'delete' === cleanupMode.site && 'No cancellation. Execute now!' === cleanupSiteForm.deletionTime
                        && ( 'Permanent deletion' === cleanupSiteForm.deletionType || ( 'Not deleted' === cleanupSiteForm.attributes
                        && ( 'Mark as deleted (change last updated time)' === cleanupSiteForm.deletionType
                        || 'Mark as deleted (keep last updated time)' === cleanupSiteForm.deletionType ) )
                        || ( 'Not archived' === cleanupSiteForm.attributes
                        && ( 'Mark as archived (change last updated time)' === cleanupSiteForm.deletionType
                        || 'Mark as archived (keep last updated time)' === cleanupSiteForm.deletionType ) ) ) ) {

                        cleanupSiteProcess( currentOffset + requestProcessedSitesCount - requestAffectedSitesCount );

                    /*
                     * If the chosen settings indicate that the affected sites do not change the number of sites to skip, we do not reduce the number of
                     * skipped sites.
                     */
                    } else {
                        cleanupSiteProcess( currentOffset + requestProcessedSitesCount );
                    }

                } else {

                    /*
                     * Since a limit was not reached and request ended with success this could only mean we are done processing all the sites.
                     * So we are calling the function to display the results.
                     */
                    cleanupSiteResults();
                }
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        cleanupCloseAbortSites( 'no-reload' );
    });
}


// Makes an ajax request to a php function and displays the results (which sites were/would be affected by the comment deletion and how)
function cleanupCommentResults() {
    var element, pageNumber, data, saveBottomActions;

    // We save the bottom actions code so the download link of the exported file remains after we change the
    if ( jQuery( "#be-mu-clean-comment-preview-bottom-actions" ).length ) {
        saveBottomActions = jQuery( "#be-mu-clean-comment-preview-bottom-actions" ).html();
    }

    // get the selected page num if the dropdown menu exists, otherwise we show page 1
    if ( document.getElementById( 'be-mu-clean-page-number' ) ) {

        element = document.getElementById( 'be-mu-clean-page-number' );
        pageNumber = parseInt( element.options[ element.selectedIndex ].value );

        // We show the loading image while we change pages
        document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'visible';
    } else {
        pageNumber = 1;
    }

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_comment_results_action',
        'mode': cleanupMode.comment,
        'task_id': cleanupTask.comment,
        'page_number': pageNumber,
        'count_affected_sites': cleanupAffected.commentSites,
        'count_affected_comments': cleanupAffected.commentComments,
        'security': localizedCleanup.ajaxNonce
    };

    // Means that an axaj request is running
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            cleanupRunningAjax = 0;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedCleanup.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedCleanup.errorInvalidNonce );
            } else {
                alert( localizedCleanup.errorResponse );
            }
        } else {

            // We show the results in the results layer
            document.getElementById( 'be-mu-clean-div-results' ).innerHTML = response;

            if ( jQuery( "#be-mu-clean-comment-preview-bottom-actions" ).length ) {
                jQuery( "#be-mu-clean-comment-preview-bottom-actions" ).html( saveBottomActions );
            }

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
	}).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
            document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
        }
    });
}

// Makes an ajax request to a php function and displays the results (which sites were/would be affected by the revision deletion and how)
function cleanupRevisionResults() {
    var element, pageNumber, data, saveBottomActions;

    // We save the bottom actions code so the download link of the exported file remains after we change the page
    if ( jQuery( "#be-mu-clean-revision-preview-bottom-actions" ).length ) {
        saveBottomActions = jQuery( "#be-mu-clean-revision-preview-bottom-actions" ).html();
    }

    // get the selected page num if the dropdown menu exists, otherwise we show page 1
    if ( document.getElementById( 'be-mu-clean-page-number' ) ) {

        element = document.getElementById( 'be-mu-clean-page-number' );
        pageNumber = parseInt( element.options[ element.selectedIndex ].value );

        // We show the loading image while we change pages
        document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'visible';
    } else {
        pageNumber = 1;
    }

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_revision_results_action',
        'mode': cleanupMode.revision,
        'task_id': cleanupTask.revision,
        'page_number': pageNumber,
        'count_affected_sites': cleanupAffected.revisionSites,
        'count_affected_revisions': cleanupAffected.revisionRevisions,
        'security': localizedCleanup.ajaxNonce
    };

    // Means that an axaj request is running
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            cleanupRunningAjax = 0;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedCleanup.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedCleanup.errorInvalidNonce );
            } else {
                alert( localizedCleanup.errorResponse );
            }
        } else {

            // We show the results in the results layer
            document.getElementById( 'be-mu-clean-div-results' ).innerHTML = response;

            if ( jQuery( "#be-mu-clean-revision-preview-bottom-actions" ).length ) {
                jQuery( "#be-mu-clean-revision-preview-bottom-actions" ).html( saveBottomActions );
            }

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
	}).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
            document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
        }
    });
}

// Makes an ajax request to a php function and displays the results (which sites were/would be affected by the site deletion)
function cleanupSiteResults() {
    var element, pageNumber, data, saveBottomActions;

    // We save the bottom actions code so the download link of the exported file remains after we change the page
    if ( jQuery( "#be-mu-clean-site-preview-bottom-actions" ).length ) {
        saveBottomActions = jQuery( "#be-mu-clean-site-preview-bottom-actions" ).html();
    }

    // Get the selected page num if the dropdown menu exists, otherwise we show page 1
    if ( document.getElementById( 'be-mu-clean-page-number' ) ) {

        element = document.getElementById( 'be-mu-clean-page-number' );
        pageNumber = parseInt( element.options[ element.selectedIndex ].value );

        // We show the loading image while we change pages
        document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'visible';
    } else {
        pageNumber = 1;
    }

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_site_results_action',
        'mode': cleanupMode.site,
        'task_id': cleanupTask.site,
        'page_number': pageNumber,
        'count_affected_sites': cleanupAffected.site,
        'site_delete_time': cleanupSiteForm.deletionTime,
        'site_delete_type': cleanupSiteForm.deletionType,
        'security': localizedCleanup.ajaxNonce
    };

    // Means that an axaj request is running
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            cleanupRunningAjax = 0;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedCleanup.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedCleanup.errorInvalidNonce );
            } else {
                alert( localizedCleanup.errorResponse );
            }
        } else {

            // We show the results in the results layer
            document.getElementById( 'be-mu-clean-div-results' ).innerHTML = response;

            if ( jQuery( "#be-mu-clean-site-preview-bottom-actions" ).length ) {
                jQuery( "#be-mu-clean-site-preview-bottom-actions" ).html( saveBottomActions );
            }

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
            document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
        }
    });
}

/*
 * Makes an ajax request to a php function that displays a list of sites with a specific status in a given site deletion task
 * @param {String} taskID
 * @param {String} status
 * @param {String} start - If "yes" we are starting the process, and if "no" we are just changing the page number
 */
function cleanupTaskViewSites( taskID, status, start ) {
    var element, pageNumber, data;

    // If we are starting the process we will aslo show the results layer and a loaging message
    if ( 'yes' === start ) {

        // We show a loading message for now
        document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupSiteLoading;

        // And we make the layer with the loading message visible
        document.getElementById( 'be-mu-clean-container' ).style.display = 'inline';
        cleanupContainerVisible = 1;
    }

    // Get the selected page num if the dropdown menu exists, otherwise we show page 1
    if ( document.getElementById( 'be-mu-clean-page-number' ) ) {

        element = document.getElementById( 'be-mu-clean-page-number' );
        pageNumber = parseInt( element.options[ element.selectedIndex ].value );

        // We show the loading image while we change pages
        document.getElementById('be-mu-clean-loading-page-number').style.visibility='visible';
    } else {
        pageNumber = 1;
    }

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_clean_site_results_action',
        'mode': 'view',
        'task_id': taskID,
        'status': status,
        'page_number': pageNumber,
        'security': localizedCleanup.ajaxNonce
    };

    // Means that an axaj request is running
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            cleanupRunningAjax = 0;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedCleanup.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedCleanup.errorInvalidNonce );
            } else {
                alert( localizedCleanup.errorResponse );
            }
        } else {

            // We show the results in the results layer
            document.getElementById( 'be-mu-clean-div-results' ).innerHTML = response;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
                document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
            }

            // An ajax request is no longer running
            cleanupRunningAjax = 0;
        }
    }).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        if ( document.getElementById( 'be-mu-clean-loading-page-number' ) ) {
            document.getElementById( 'be-mu-clean-loading-page-number' ).style.visibility = 'hidden';
        }
    });
}

/*
 * This function runs when an abort or close button is clicked regarding comment deletion process or results.
 * It sets the global abort var to 1, hides the results layer and puts inside it the loading code to be ready for future requests.
 */
function cleanupCloseAbortComments() {
    cleanupAbort.comment = 1;
    document.getElementById( 'be-mu-clean-container' ).style.display = 'none';
    cleanupContainerVisible = 0;
    document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupCommentLoading;
}

/*
 * This function runs when an abort or close button is clicked regarding revision deletion process or results.
 * It sets the global abort var to 1, hides the results layer and puts inside it the loading code to be ready for future requests.
 */
function cleanupCloseAbortRevisions() {
    cleanupAbort.revision = 1;
    document.getElementById( 'be-mu-clean-container' ).style.display = 'none';
    cleanupContainerVisible = 0;
    document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupRevisionLoading;
}

/*
 * This function runs when an abort or close button is clicked regarding site deletion process or results.
 * It sets the global abort var to 1, hides the results layer and puts inside it the loading code to be ready for future requests.
 * Also it reloads the page if a site deletion has been scheduled.
 * @param {Number} toReload
 */
function cleanupCloseAbortSites( toReload ) {
    cleanupAbort.site = 1;
    document.getElementById( 'be-mu-clean-container' ).style.display = 'none';
    cleanupContainerVisible = 0;
    document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupSiteLoading;
    if ( 'reload' === toReload ) {
        cleanupReloadPage();
    /*
     * If the abort button is clicked while an execution of scheduling site deletions is running we show a message that suggests to the user to reload
     * the page, since some deletions are probably already scheduled. We do not just reload it because the user might want to see what he did chose
     * in the form before he reloads the page.
     */
    } else if ( 'No cancellation. Execute now!' != cleanupSiteForm.deletionTime && 'delete' === cleanupMode.site && 1 === cleanupRunningAjax ) {
        alert( localizedCleanup.suggestReload );
    }
}

/*
 * This function runs when an abort or close button is clicked regarding leftover database tables deletion process or results.
 * It sets the global abort var to 1, hides the results layer and puts inside it the loading code to be ready for future requests.
 */
function cleanupCloseAbortTables() {
    cleanupAbort.table = 1;
    document.getElementById( 'be-mu-clean-container' ).style.display = 'none';
    cleanupContainerVisible = 0;
    document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupTableLoading;
}

/*
 * This function runs when an abort or close button is clicked regarding no role user deletion process or results.
 * It sets the global abort var to 1, hides the results layer and puts inside it the loading code to be ready for future requests.
 */
function cleanupCloseAbortUsers() {
    cleanupAbort.user = 1;
    document.getElementById( 'be-mu-clean-container' ).style.display = 'none';
    cleanupContainerVisible = 0;
    document.getElementById( 'be-mu-clean-div-results' ).innerHTML = cleanupUserLoading;
}

// Asks for confirmation when the user tries to leave or reload the page during a running task, since this will abort the task
function cleanupConfirmExitDuringLoading() {
    if ( 1 === cleanupContainerVisible && 1 === cleanupRunningAjax ) {
        return localizedCleanup.abortTask;
    }
}

// Runs when the user tries to leave or reload the page
window.onbeforeunload = cleanupConfirmExitDuringLoading;

/*
 * Runs when the cancel/complete site deletion task button is clicked
 * and makes an ajax request to a php function that deletes all data for the site deletion task.
 * @param {String} taskId
 * @param {String} mode
 */
function cleanupCancelOrCompleteSiteDeletionTask( taskId, mode ) {
    var confirmResult, data;

    // Based on the mode we show a confirmation message
    if ( 'cancel' === mode ) {
        confirmResult = confirm( localizedCleanup.cancelTask );
        if ( true !== confirmResult ) {
            return;
        }
    } else {
        confirmResult = confirm( localizedCleanup.completeTask );
        if ( true !== confirmResult ) {
            return;
        }
    }

    // We show the loading image
    document.getElementById( 'be-mu-clean-loading-cancel-deletion-task' ).style.visibility = 'visible';

    // The data for the ajax request
    data = {
        'action': 'be_mu_clean_cancel_or_complete_site_deletion_task_action',
        'task_id': taskId,
        'security': localizedCleanup.ajaxNonce
    };

    // Means that an axaj request is running
    cleanupRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            cleanupRunningAjax = 0;

            // We hide the loading image, since the request is done
            document.getElementById( 'be-mu-clean-loading-cancel-deletion-task' ).style.visibility = 'hidden';

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedCleanup.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedCleanup.errorInvalidNonce );
            } else {
                alert( localizedCleanup.errorResponse );
            }
        } else {

            // We hide the loading image, since the request is done
            document.getElementById( 'be-mu-clean-loading-cancel-deletion-task' ).style.visibility = 'hidden';

            // An ajax request is no longer running
            cleanupRunningAjax = 0;

            // We reload the page to show the site deletion form
            cleanupReloadPage();
        }
	}).fail( function() {
        cleanupRunningAjax = 0;
        alert( localizedCleanup.errorServerFail );
        document.getElementById( 'be-mu-clean-loading-cancel-deletion-task' ).style.visibility = 'hidden';
    });
}

// Reloads the network cleanup page and scrolls to the Delete sites section. The force_reload variable and its value ensure a new URL, and therefor a reload.
function cleanupReloadPage() {
    window.location.href = localizedCleanup.pageURL + '&force_reload=' + cleanupRandomString( 3 ) + '#site';
}

// Goes to the next or previous page of the current list of results for Leftover Database Table Deletion
function cleanupTableNextPreviousPage( page ) {
    jQuery( "#be-mu-clean-page-number" ).val( page );
    cleanupTableResults();
}

// Goes to the next or previous page of the current list of results for No Role User Deletion
function cleanupUserNextPreviousPage( page ) {
    jQuery( "#be-mu-clean-page-number" ).val( page );
    cleanupUserResults();
}

// Goes to the next or previous page of the current list of results for Comment Deletion
function cleanupCommentNextPreviousPage( page ) {
    jQuery( "#be-mu-clean-page-number" ).val( page );
    cleanupCommentResults();
}

// Goes to the next or previous page of the current list of results for Revision Deletion
function cleanupRevisionNextPreviousPage( page ) {
    jQuery( "#be-mu-clean-page-number" ).val( page );
    cleanupRevisionResults();
}

// Goes to the next or previous page of the current list of results for Site Deletion
function cleanupSiteNextPreviousPage( page ) {
    jQuery( "#be-mu-clean-page-number" ).val( page );
    cleanupSiteResults();
}

// Goes to the next or previous page of the current list of results for viewing scheduled or canceled site deletions
function cleanupSiteViewNextPreviousPage( page, task_id, status, start ) {
    jQuery( "#be-mu-clean-page-number" ).val( page );
    cleanupTaskViewSites( task_id, status, start );
}

/*
 * Exports site IDs or URLs from the preview deletion results box by making an ajax request.
 * If needed it calls itself multiple times to handle large data without timeouts.
 * @param {String} taskId
 * @param {String} field
 * @param {Number} offset
 * @param {Number} totalSites
 * @param {String} taskType
 */
function cleanupExportResults( taskID, field, offset, totalSites, taskType ) {
    var data;

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === cleanupRunningAjax ) {

        // Show a loading message
        jQuery( "#be-mu-cleanup-" + taskType + "-export-" + field + "-link" ).html( localizedCleanup.processing + ' ('
            + Math.round( ( parseInt( offset ) / parseInt( totalSites ) ) * 100 ) + '%)' );

        jQuery( "#be-mu-cleanup-" + taskType + "-export-" + field + "-link" ).attr( 'href', '#' );

        // Hide the page navigation
        jQuery( ".be-mu-clean-preview-page-navigation" ).css( "visibility", "hidden" );

        data = {
            'action': 'be_mu_clean_export_results_action',
            'task_id': taskID,
            'field': field,
            'offset': offset,
            'security': localizedCleanup.ajaxNonce
        };

        // Means that an axaj request is running
        cleanupRunningAjax = 1;

        /*
         * We are making the ajax request.
         * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
         */
        jQuery.post( ajaxurl, data, function( response ) {

            var responseObject, requestProcessedSitesCount, requestProcessedSiteChunksCount, fileURL, currentOffset, isRequestLimitReached;

            response = response.trim();

            // An ajax request is no longer running
            cleanupRunningAjax = 0;

            if ( 'no-access' === response || '0' === response || 'cannot-write' === response || 'invalid-nonce' === response ) {

                // We alert an error
                if ( 'no-access' === response ) {
                    alert( localizedCleanup.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedCleanup.errorInvalidNonce );
                } else if ( 'cannot-write' === response ) {
                    alert( localizedCleanup.errorWriteExport );
                } else {
                    alert( localizedCleanup.errorResponse );
                }

                jQuery( "#be-mu-cleanup-" + taskType + "-export-" + field + "-link" ).html( localizedCleanup.errorError );

                // Show the page navigation
                jQuery( ".be-mu-clean-preview-page-navigation" ).css( "visibility", "visible" );

            } else {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of sites processed in the last request for exporting URLs
                requestProcessedSitesCount = parseInt( responseObject.requestProcessedSitesCount );

                // The number of chunks processed in the last request for exporting IDs
                requestProcessedSiteChunksCount = parseInt( responseObject.requestProcessedSiteChunksCount );

                // The URL of the txt file with the exported data
                fileURL = responseObject.fileURL;

                // The number of chunks with sites IDs or the number of site URLs (not chunks) that were skipped on the previous request
                currentOffset = parseInt( responseObject.currentOffset );

                // It is 1 if the previous request reached the time limit.
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // The export task is done
                if ( 0 === isRequestLimitReached ) {

                    jQuery( "#be-mu-cleanup-" + taskType + "-export-" + field + "-link" ).attr( 'href', fileURL );
                    jQuery( "#be-mu-cleanup-" + taskType + "-export-" + field + "-link" ).attr( 'target', '_blank' );
                    jQuery( "#be-mu-cleanup-" + taskType + "-export-" + field + "-link" ).attr( 'download', '' );

                    if ( field === 'ids' ) {
                        jQuery( "#be-mu-cleanup-" + taskType + "-export-" + field + "-link" ).html( '<span class="be-mu-green">'
                            + localizedCleanup.downloadIDs + '</span>' );
                    } else {
                        jQuery( "#be-mu-cleanup-" + taskType + "-export-" + field + "-link" ).html( '<span class="be-mu-green">'
                            + localizedCleanup.downloadURLs + '</span>' );
                    }

                    // Show the page navigation
                    jQuery( ".be-mu-clean-preview-page-navigation" ).css( "visibility", "visible" );

                // We are not done, the time limit was reached on the previous request. We need to call this funciton again with a new offset.
                } else {
                    if ( field === 'ids' ) {
                        cleanupExportResults( taskID, field, currentOffset + requestProcessedSiteChunksCount, totalSites, taskType );
                    } else {
                        cleanupExportResults( taskID, field, currentOffset + requestProcessedSitesCount, totalSites, taskType );
                    }
                }

            }
        }).fail( function() {
            cleanupRunningAjax = 0;
            alert( localizedCleanup.errorServerFail );
            jQuery( "#be-mu-cleanup-" + taskType + "-export-" + field + "-link" ).html( localizedCleanup.errorError );
            jQuery( ".be-mu-clean-preview-page-navigation" ).css( "visibility", "visible" );
        });
    } else {
        alert( localizedCleanup.errorRequest );
    }
}

// When the page loads, we start monitoring the changes in the dropdown menus, so we can ask for a custom number for some options
jQuery( function() {
    jQuery( 'select' ).on( "change", function() {
        var optionValue, optionIndex, id, optionText, number, newValue, newText, maxNumber;

        // The value of the selected option
        optionValue = jQuery( this ).val();

        // The ID of the dropdown menu that has changed
        id = jQuery( this ).attr( 'id' );

        // If the ID indicates that the menu is from my plugin and if the value contains "X", we proceed
        if ( id.substr( 0, 6 ) == 'be-mu-' && optionValue.indexOf( '[X]' ) != -1 ) {

            /*
             * If this option is related to time, usually days, we do not allow higher numbers than 9999 because if it is too high we can go back in time beyond
             * the unix time creation. If it is not about time, we need higher limit, for example when we are deleting comments in sites with at least X.
             */
            if ( optionValue.indexOf( 'days' ) != -1 || optionValue.indexOf( 'hours' ) != -1 || optionValue.indexOf( 'min' ) != -1 ) {
                maxNumber = 9999;
            } else {
                maxNumber = 999999999;
            }

            // The index of the selected option
            optionIndex = jQuery( this ).prop( 'selectedIndex' );

            // The text of the selected option
            optionText = jQuery( '#' + id + ' option:selected' ).html();

            // We ask for a number to replace X with
            number = prompt( optionText + ". " + localizedCleanup.promptNumberCustomOption );

            // If the user cancels the prompt, we change the selected option to the one above this one
            if ( null === number ) {
                optionIndex--;
                jQuery( '#' + id ).prop( "selectedIndex", optionIndex );

            // If the value they entered is a whole number and not too low or high, we create and add the new option, and select it
            } else if ( ! isNaN( number ) && number >= 1 && number <= maxNumber && number % 1 === 0 && number.toString()[0] != '0' ) {

                // We replace the X with the entered number
                newValue = optionValue.replace( '[X]', number );

                // If the option does not exist we create it
                if ( jQuery( "#" + id + " option[value='" + newValue + "']").length <= 0 ) {
                    newText = optionText.replace( '[X]', number );
                    jQuery( '#' + id ).append( jQuery( '<option>', {
                        value: newValue,
                        text: newText
                    }));
                }

                // We select the new option
                jQuery( '#' + id ).val( newValue );

            // If the entered value is not valid we show an error and switch the selected option to the one above this one
            } else {
                if ( 9999 == maxNumber ) {
                    alert( localizedCleanup.errorNumberCustomOptionLow );
                } else {
                    alert( localizedCleanup.errorNumberCustomOptionHigh );
                }
                optionIndex--;
                jQuery( '#' + id ).prop( "selectedIndex", optionIndex );
            }
        }

        if ( id === 'be-mu-clean-site-updated' && optionValue.indexOf( 'after registration' ) != -1 ) {
            jQuery( '#be-mu-clean-gmt-bug-message' ).css( 'display', 'block' );
        }

        if ( id === 'be-mu-clean-site-updated' && optionValue.indexOf( 'after registration' ) == -1 ) {
            jQuery( '#be-mu-clean-gmt-bug-message' ).css( 'display', 'none' );
        }
    });

    // Show roles list on user selection in the delete users section
    jQuery( '#be-mu-cleanup-users-role' ).change( function() {
        if ( jQuery( this ).val() === 'Any role from a list' ) {
            jQuery( '#be-mu-cleanup-users-list-roles-show' ).removeClass( 'be-mu-display-none' );
        } else {
            jQuery( '#be-mu-cleanup-users-list-roles-show' ).addClass( 'be-mu-display-none' );
        }
    });
});
