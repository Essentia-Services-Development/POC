/*
 * global emailUsersTaskId:true, emailUsersMode:true, emailUsersAbort:true, emailUsersDoneCount:true, emailUsersAffected:true,
 * emailUsersForm:true, emailUsersRunningAjax:true, emailContainerVisible:true, emailLoadingAbort:true, ajaxurl, localizedEmail
 */

/*
 * emailUsersTaskId: The task id in the database
 * emailUsersMode: This var can be either "preview" or "send" and shows what is the mode we are in
 * emailUsersAbort: This is 1 if the abort button has been clicked before we finished, so need to abort and not put the results in the layer
 * emailUsersDoneCount: How many users have we processed so far for the current task
 * emailUsersAffected: How many users are/would be affected by the email task
 * emailUsersForm: An object with the chosen options in the email users form
 * emailUsersRunningAjax: This is 1 if there is currently running an ajax request that is not done yet
 * emailContainerVisible: If it is 1, the container layer for the loading message and results is vissible (if 0, it is not)
 * emailLoadingAbort: The html code for the loading message
 */
var emailUsersTaskId, emailUsersMode, emailUsersAbort, emailUsersDoneCount, emailUsersAffected,
    emailUsersForm = {},
    emailUsersRunningAjax = 0,
    emailContainerVisible = 0,
    emailLoadingAbort = '<div class="be-mu-p20">'
        + '<p class="be-mu-center be-mu-email-loading">' + '<img src="' + localizedEmail.loadingGIF + '" /></p>'
        + '<p class="be-mu-center be-mu-email-processing">' + localizedEmail.processing + '</p>'
        + '<p class="be-mu-center" id="be-mu-email-processed-so-far">&nbsp;</p>'
        + '<p class="be-mu-center">' + localizedEmail.getAbort + '</p>'
        + '<h2 class="be-mu-email-h2 be-mu-display-none">' + localizedEmail.exportTitle + '<div class="be-mu-right">'
        + '<input type="button" class="button be-mu-mleft10imp" onclick="emailCloseAbort( \'no-reload\' )" value="' + localizedEmail.close + '"></div></h2>'
        + '<p id="be-mu-email-exported-affected" class="be-mu-1-15-em be-mu-display-none"><b>' + localizedEmail.affectedUsers + '</b></p>'
        + '<p id="be-mu-email-exported" class="be-mu-display-none"><textarea></textarea></p>'
        + '</div>';

/*
 * Returns a random string with chosen length
 * @param {Number} length
 * @return {String} the random string
 */
function emailRandomString( length ) {
    var text, possible, i;

    text = '';
    possible = 'abcdefghijklmnopqrstuvwxyz0123456789';

    for ( i = 0; i < length; i++ ) {
        text += possible.charAt( Math.floor( Math.random() * possible.length ) );
    }

    return text;
}

/*
 * This function starts the process of sending emails or previewing the user selection, depending on the mode var
 * @param {String} mode
 */
function emailStart( mode ) {
    var confirmResult;

    emailUsersMode = mode;

    // If sending the emails is clicked, we ask the user for confirmation before we let him continue
    if ( 'send' === emailUsersMode ) {
        confirmResult = confirm( localizedEmail.confirmStart );
        if ( true !== confirmResult ) {
            return;
        }
    }

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === emailUsersRunningAjax ) {

        // We get the selected settings from the form and set the global object with the data needed to start the request
        element = document.getElementById( 'be-mu-email-role' );
        emailUsersForm.role = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-email-role-sites-attribute' );
        emailUsersForm.roleSitesAttribute = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-email-role-sites-id-option' );
        emailUsersForm.roleSitesIdOption = element.options[ element.selectedIndex ].value;
        emailUsersForm.roleSitesIds = document.getElementById( 'be-mu-email-role-sites-ids' ).value;
        element = document.getElementById( 'be-mu-email-users-id-option' );
        emailUsersForm.usersIdOption = element.options[ element.selectedIndex ].value;
        emailUsersForm.usersIds = document.getElementById( 'be-mu-email-users-ids' ).value;
        element = document.getElementById( 'be-mu-email-ban-status' );
        emailUsersForm.banStatus = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-email-spam-status' );
        emailUsersForm.spamStatus = element.options[ element.selectedIndex ].value;
        element = document.getElementById( 'be-mu-email-unsubscribe-status' );
        emailUsersForm.unsubscribeStatus = element.options[ element.selectedIndex ].value;
        emailUsersForm.listRoles = jQuery( '#be-mu-email-roles-list' ).val();


        // If we are in "send" mode we also get the fields for the email message
        if ( 'send' === emailUsersMode ) {
            emailUsersForm.fromEmail = document.getElementById( 'be-mu-email-from-email' ).value;
            emailUsersForm.fromName = document.getElementById( 'be-mu-email-from-name' ).value;
            emailUsersForm.subject = document.getElementById( 'be-mu-email-subject' ).value;

            /*
             * Getting the content of the message in the WordPress editor is a little more complex. This approach seems to work well
             * regardless of whether we are in visual or text mode and whether we have changed the mode and added more content.
             */
            if ( tinyMCE.editors['be-mu-email-message'] ) {
                tinyMCE.editors['be-mu-email-message'].save();
                tinyMCE.editors['be-mu-email-message'].load();
                emailUsersForm.message = tinyMCE.editors['be-mu-email-message'].getContent();
            } else if ( document.getElementById( 'be-mu-email-message' ) ) {
                emailUsersForm.message = document.getElementById( 'be-mu-email-message' ).value;
            } else {

                // If we could not get the content, we show an error and return
                alert ( localizedEmail.errorMessage );
                return;
            }
        }

        // These vars need to be set to 0 on every new task start
        emailUsersAbort = emailUsersDoneCount = emailUsersAffected = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-email-div-results' ).innerHTML = emailLoadingAbort;

        // And we make the layer with the loading message visible
        document.getElementById( 'be-mu-email-container' ).style.display = 'inline';
        emailContainerVisible = 1;

        // We generate a new random task id string
        emailUsersTaskId = emailRandomString( 10 );

        // Start processing the users and scheduling emails or selecting users, based on the mode. We are skipping zero of the users for the first request.
        emailProcess( 0 );

    } else {
        alert( localizedEmail.errorRequest );
    }
}

/*
 * Makes an ajax request to a php function that will go through the users and schedule the email we need to send or only preview selected
 * users, based on the mode. After the ajax request this function will call it self multiple times if needed untill all users are processed and
 * at the end will display the results with the affected users.
 * @param {Number} offset - How many users to skip
 */
function emailProcess( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
    	'action': 'be_mu_email_schedule_action',
    	'mode': emailUsersMode,
    	'task_id': emailUsersTaskId,
        'role': emailUsersForm.role,
        'role_sites_attribute': emailUsersForm.roleSitesAttribute,
        'role_sites_id_option': emailUsersForm.roleSitesIdOption,
        'role_sites_ids': emailUsersForm.roleSitesIds,
        'users_id_option': emailUsersForm.usersIdOption,
        'users_ids': emailUsersForm.usersIds,
        'ban_status': emailUsersForm.banStatus,
        'spam_status': emailUsersForm.spamStatus,
        'unsubscribe_status': emailUsersForm.unsubscribeStatus,
        'list_roles': emailUsersForm.listRoles,
    	'offset': offset,
        'security': localizedEmail.ajaxNonce
    };

    // If the mode is "send" we also add the data for the email message to the request
    if ( 'send' === emailUsersMode ) {
        data.from_email = emailUsersForm.fromEmail;
        data.from_name = emailUsersForm.fromName;
        data.subject = emailUsersForm.subject;
        data.message = emailUsersForm.message;
    }

    // Means that an axaj request is running
    emailUsersRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, currentOffset, limitReached, requestProcessedUsersCount, requestAffectedUsersCount;

        response = response.trim();

        // We are checking the response for an error code
        if ( '0' === response || 'no-access' === response || 'invalid-data' === response || 'ids-invalid' === response || 'ids-not-needed' === response
            || 'invalid-from-email' === response || 'empty-fields' === response || 'another-task' === response || 'invalid-nonce' === response ) {

            // The ajax request is not running any more
            emailUsersRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( 1 !== emailUsersAbort ) {
                if ( 'no-access' === response ) {
                    alert( localizedEmail.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedEmail.errorInvalidNonce );
                } else if ( 'invalid-data' === response ) {
                    alert( localizedEmail.errorData );
                } else if ( 'ids-invalid' === response ) {
                    alert( localizedEmail.errorIdsInvalid );
                } else if ( 'ids-not-needed' === response ) {
                    alert( localizedEmail.errorIdsNotNeeded );
                } else if ( 'invalid-from-email' === response ) {
                    alert( localizedEmail.errorFromEmail );
                } else if ( 'empty-fields' === response ) {
                    alert( localizedEmail.errorEmpty );
                } else if ( 'another-task' === response ) {
                    alert( localizedEmail.errorAnotherTask );
                } else {
                    alert( localizedEmail.errorResponse );
                }

                // We close the results layer after the error
                emailCloseAbort( 'no-reload' );
            }
        } else {

            // If we haven't closed the layer and aborted we will show the results we continue
            if ( 1 !== emailUsersAbort ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of users processed in the last request
                requestProcessedUsersCount = parseInt( responseObject.requestProcessedUsersCount );

                // How many users are/would be affected by email sending for the last request
                requestAffectedUsersCount = parseInt( responseObject.requestAffectedUsersCount );

                // The current offset we got to processing the users
                currentOffset = parseInt( responseObject.currentOffset );

                // This is 1 if a limit (time or number of users) has been reached for this chunk of users
                limitReached = parseInt( responseObject.limitReached );

                // How many users have we processed so far for this task (we add the ones from the last request)
                emailUsersDoneCount += requestProcessedUsersCount;

                // How many users are/would be affected by email sending so far for the current task
                emailUsersAffected += requestAffectedUsersCount;

                // If a limit was reached in the last request this means we are not done processing all the users
                if ( 1 === limitReached ) {

                    // We show how many users we have processed so far
                    document.getElementById( 'be-mu-email-processed-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-email-processed-so-far' ).innerHTML = localizedEmail.processedSoFar + ' ' + emailUsersDoneCount;

                    if ( 'export' === emailUsersMode ) {
                        jQuery( '#be-mu-email-exported textarea' ).val( jQuery( '#be-mu-email-exported textarea' ).val()
                            + responseObject.exportEmails.replaceAll( ";;", "\n" ) );
                    }

                    // And we call the same function we are inside now, but with a new offset, so we continue from where we left of
                    emailProcess( currentOffset + requestProcessedUsersCount );
                } else {

                    /*
                     * Since a limit was not reached and request ended with success this could only mean we are done processing all the users.
                     * So we are calling the function to dispay the results.
                     */
                    if ( 'export' !== emailUsersMode ) {
                        emailResults();
                    } else {
                        jQuery( '#be-mu-email-exported, .be-mu-email-h2, #be-mu-email-exported-affected' ).removeClass( 'be-mu-display-none' );
                        jQuery( '#be-mu-email-div-results .be-mu-center' ).hide();
                        jQuery( '#be-mu-email-exported textarea' ).val( jQuery( '#be-mu-email-exported textarea' ).val()
                            + responseObject.exportEmails.replaceAll( ";;", "\n" ) );
                        jQuery( '#be-mu-email-exported-affected b' ).append( ' ' + emailUsersAffected );
                    }
                }
            }

            // An ajax request is no longer running
            emailUsersRunningAjax = 0;
        }
    }).fail( function() {
        emailUsersRunningAjax = 0;
        alert( localizedEmail.errorServerFail );
        emailCloseAbort( 'no-reload' );
    });
}

// Makes an ajax request to a php function and displays the results (which users were/would be affected by the email task)
function emailResults() {
    var element, pageNumber, data;

    // Get the selected page num if the dropdown menu exists, otherwise we show page 1
    if ( document.getElementById( 'be-mu-email-page-number' ) ) {

        element = document.getElementById( 'be-mu-email-page-number' );
        pageNumber = parseInt( element.options[ element.selectedIndex ].value );

        // We show the loading image while we change pages
        document.getElementById('be-mu-email-loading-page-number').style.visibility='visible';
    } else {
        pageNumber = 1;
    }

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_email_results_action',
    	'mode': emailUsersMode,
    	'task_id': emailUsersTaskId,
        'page_number': pageNumber,
        'count_affected_users': emailUsersAffected,
        'security': localizedEmail.ajaxNonce
    };

    // Means that an axaj request is running
    emailUsersRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            emailUsersRunningAjax = 0;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-email-loading-page-number' ) ) {
                document.getElementById( 'be-mu-email-loading-page-number' ).style.visibility = 'hidden';
            }

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedEmail.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedEmail.errorInvalidNonce );
            } else {
                alert( localizedEmail.errorResponse );
            }
        } else {

            // We show the results in the results layer
            document.getElementById( 'be-mu-email-div-results' ).innerHTML = response;

            // We hide the loading image, since the request is done
            if ( document.getElementById( 'be-mu-email-loading-page-number' ) ) {
                document.getElementById( 'be-mu-email-loading-page-number' ).style.visibility = 'hidden';
            }

            // An ajax request is no longer running
            emailUsersRunningAjax = 0;
        }
    }).fail( function() {
        emailUsersRunningAjax = 0;
        alert( localizedEmail.errorServerFail );
        if ( document.getElementById( 'be-mu-email-loading-page-number' ) ) {
            document.getElementById( 'be-mu-email-loading-page-number' ).style.visibility = 'hidden';
        }
    });
}

/*
 * Switches the email sending mode for a task
 * @param {String} sendingMode
 * @param {String} taskID
 */
function emailSwitchSendingMode( sendingMode, taskID ) {
    var data;

    jQuery( '#be-mu-email-current-task-data' ).html( '<p>' + localizedEmail.processing + '</p>' );

    clearInterval( myEmailInterval );
    clearTimeout( myEmailTimeout );

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_email_sending_mode_action',
        'task_id': taskID,
        'sending_mode': sendingMode,
        'security': localizedEmail.ajaxNonce
    };

    // Means that an axaj request is running
    emailUsersRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        // We alert an error
        if ( 'no-access' === response ) {
            alert( localizedEmail.errorAccess );
        } else if ( 'invalid-nonce' === response ) {
            alert( localizedEmail.errorInvalidNonce );
        } else if ( 'done' === response ) {

            emailGetTaskData();

            if ( "real-time" === sendingMode ) {
                setTimeout( emailWorkAndGetTaskData, 15000 );
            } else {
                myEmailInterval = setInterval( emailGetTaskData, 60000 );
            }
        } else {
            alert( localizedEmail.errorResponse );
        }

        // An ajax request is no longer running
        emailUsersRunningAjax = 0;
    }).fail( function() {
        emailUsersRunningAjax = 0;
        alert( localizedEmail.errorServerFail );
    });
}

// Makes an ajax request to a php function that shows the current email task data
function emailGetTaskData() {
    var data;

    clearTimeout( myEmailTimeout );

    jQuery( '#be-mu-email-current-task-data' ).html( '<p>' + localizedEmail.processing + '</p>' );

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_email_task_data_action',
        'work': 'no',
        'security': localizedEmail.ajaxNonce
    };

    // Means that an axaj request is running
    emailUsersRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        jQuery( '#be-mu-email-current-task-data' ).html( response );

        if ( response.indexOf( 'be_mu_email_task_completed' ) != -1 || response.indexOf( 'be_mu_email_task_error' ) != -1 ) {
            clearInterval( myEmailInterval );
            clearTimeout( myEmailTimeout );
        }

        // An ajax request is no longer running
        emailUsersRunningAjax = 0;
    }).fail( function() {
        emailUsersRunningAjax = 0;
        alert( localizedEmail.errorServerFail );
    });
}

// Makes an ajax request to a php function that shows the current email task data and does email sending work too
function emailWorkAndGetTaskData() {
    var data;

    clearInterval( myEmailInterval );

    jQuery( '#be-mu-email-current-task-data' ).html( '<p>' + localizedEmail.processing + '</p>' );

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_email_task_data_action',
        'work': 'yes',
        'security': localizedEmail.ajaxNonce
    };

    // Means that an axaj request is running
    emailUsersRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        jQuery( '#be-mu-email-current-task-data' ).html( response );

        if ( response.indexOf( 'be_mu_email_task_completed' ) === -1 && response.indexOf( 'be_mu_email_task_error' ) === -1 ) {
            setTimeout( emailWorkAndGetTaskData, 15000 );
        }

        // An ajax request is no longer running
        emailUsersRunningAjax = 0;
    }).fail( function() {
        emailUsersRunningAjax = 0;
        alert( localizedEmail.errorServerFail );
    });
}

/*
 * This function runs when an abort or close button is clicked regarding the email task process or results.
 * It sets the global abort var to 1, hides the results layer and puts inside it the loading code to be ready for future requests.
 * Also it reloads the page if emails have been scheduled for sending.
 * @param {Number} toReload
 */
function emailCloseAbort( toReload ) {
    emailUsersAbort = 1;
    document.getElementById( 'be-mu-email-container' ).style.display = 'none';
    emailContainerVisible = 0;
    document.getElementById( 'be-mu-email-div-results' ).innerHTML = emailLoadingAbort;
    if ( 'reload' === toReload ) {
        emailReloadPage();
    /*
     * If the abort button is clicked while scheduling emails is running we show a message that suggests to the user to reload
     * the page, since some emails are probably already scheduled. We do not just reload it because the user might want to see what he did chose
     * in the form before he reloads the page.
     */
    } else if ( 'send' === emailUsersMode && 1 === emailUsersRunningAjax ) {
        alert( localizedEmail.suggestReload );
    }
}

/*
 * Runs when the cancel/complete email task button is clicked
 * and makes an ajax request to a php function that deletes all data for the site deletion task.
 * @param {String} taskId
 * @param {String} mode
 */
function emailCancelOrCompleteEmailTask( taskId, mode ) {
    var confirmResult, data;

    // Based on the mode we show a confirmation message
    if ( 'cancel' === mode ) {
        confirmResult = confirm( localizedEmail.cancelTask );
        if ( true !== confirmResult ) {
            return;
        }
    } else {
        confirmResult = confirm( localizedEmail.completeTask );
        if ( true !== confirmResult ) {
            return;
        }
    }

    // We show the loading image
    document.getElementById( 'be-mu-email-loading-cancel-email-task' ).style.visibility = 'visible';

    // The data for the ajax request
    data = {
        'action': 'be_mu_email_cancel_or_complete_email_task_action',
        'task_id': taskId,
        'security': localizedEmail.ajaxNonce
    };

    // Means that an axaj request is running
    emailUsersRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // An ajax request is no longer running
            emailUsersRunningAjax = 0;

            // We hide the loading image, since the request is done
            document.getElementById( 'be-mu-email-loading-cancel-email-task' ).style.visibility = 'hidden';

            // We alert an error
            if ( 'no-access' === response ) {
                alert( localizedEmail.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedEmail.errorInvalidNonce );
            } else {
                alert( localizedEmail.errorResponse );
            }
        } else {

            // We hide the loading image, since the request is done
            document.getElementById( 'be-mu-email-loading-cancel-email-task' ).style.visibility = 'hidden';

            // An ajax request is no longer running
            emailUsersRunningAjax = 0;

            // We reload the page to show the email users form
            emailReloadPage();
        }
    }).fail( function() {
        emailUsersRunningAjax = 0;
        alert( localizedEmail.errorServerFail );
        document.getElementById( 'be-mu-email-loading-cancel-email-task' ).style.visibility = 'hidden';
    });
}

// Reloads the email users page
function emailReloadPage() {
    window.location.href = localizedEmail.pageURL;
}

// Sends a test email to a chosen email address
function emailSendTestEmail() {
    var fromEmail, fromName, toTestUser, subject, message, data, roleSitesIdOption, roleSitesIds;

    // We empty the span next to the button from a previous message and show the loading image
    document.getElementById( 'be-mu-email-test-email-done-span' ).innerHTML = '';
    document.getElementById( 'be-mu-loading-email-test-email' ).style.visibility = 'visible';

    // Get and set the variables with the chosen values for the test email
    fromEmail = document.getElementById( 'be-mu-email-from-email' ).value;
    fromName = document.getElementById( 'be-mu-email-from-name' ).value;
    toTestUser = document.getElementById( 'be-mu-email-test-user' ).value;
    subject = document.getElementById( 'be-mu-email-subject' ).value;

    // These are needed for the shortocode to list only sites where user is admin but only selected sites in user selection
    element = document.getElementById( 'be-mu-email-role-sites-id-option' );
    roleSitesIdOption = element.options[ element.selectedIndex ].value;
    roleSitesIds = document.getElementById( 'be-mu-email-role-sites-ids' ).value;

    /*
     * Getting the content of the message in the WordPress editor is a little more complex. This approach seems to work well
     * regardless of whether we are in visual or text mode and whether we have changed the mode and added more content.
     */
    if ( tinyMCE.editors['be-mu-email-message'] ) {
        tinyMCE.editors['be-mu-email-message'].save();
        tinyMCE.editors['be-mu-email-message'].load();
        message = tinyMCE.editors['be-mu-email-message'].getContent();
    } else if ( document.getElementById( 'be-mu-email-message' ) ) {
        message = document.getElementById( 'be-mu-email-message' ).value;
    } else {

        // If we could not get the content, we show an error, hide the loading image and return
        alert ( localizedEmail.errorMessage );
        document.getElementById( 'be-mu-loading-email-test-email' ).style.visibility = 'hidden';
        return;
    }

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_email_send_test_email_action',
        'security': localizedEmail.ajaxNonce,
        'from_email': fromEmail,
        'from_name': fromName,
        'to_test_user': toTestUser,
        'subject': subject,
        'message': message,
        'role_sites_id_option': roleSitesIdOption,
        'role_sites_ids': roleSitesIds,
    };

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {

        response = response.trim();

        // Error, we alert an error
        if ( 'invalid-from-email' === response ) {
            alert( localizedEmail.errorFromEmail );
        } else if ( 'invalid-to-user' === response ) {
            alert( localizedEmail.errorToUser );
        } else if ( 'empty-fields' === response ) {
            alert( localizedEmail.errorEmpty );
        } else if ( 'could-not-send' === response ) {
            alert( localizedEmail.errorSend );
        } else if ( 'invalid-data' === response ) {
            alert( localizedEmail.errorData );
        } else if ( 'ids-invalid' === response ) {
            alert( localizedEmail.errorIdsInvalid );
        } else if ( 'no-access' === response ) {
            alert( localizedEmail.errorAccess );
        } else if ( 'invalid-nonce' === response ) {
            alert( localizedEmail.errorInvalidNonce );
        } else if ( '0' === response ) {
            alert( localizedEmail.errorResponse );
        } else {

            // Success, we show a message next to the button
            document.getElementById( 'be-mu-email-test-email-done-span' ).innerHTML = localizedEmail.done;
        }

        // When we are done, we hide the loading image
        document.getElementById( 'be-mu-loading-email-test-email' ).style.visibility = 'hidden';
    }).fail( function() {
        alert( localizedEmail.errorServerFail );
        document.getElementById( 'be-mu-loading-email-test-email' ).style.visibility = 'hidden';
    });
}

// Goes to the next or previous page of the current list of results
function emailUsersNextPreviousPage( page ) {
    jQuery( "#be-mu-email-page-number" ).val( page );
    emailResults();
}

// Shows and hides the field for list of roles based on the selected option
jQuery(function(){
    jQuery( '#be-mu-email-role' ).change( function() {
        if ( jQuery( this ).val() === 'Any role from a list' ) {
            jQuery( '#be-mu-email-list-roles-show' ).removeClass( 'be-mu-display-none' );
        } else {
            jQuery( '#be-mu-email-list-roles-show' ).addClass( 'be-mu-display-none' );
        }
    });
});
