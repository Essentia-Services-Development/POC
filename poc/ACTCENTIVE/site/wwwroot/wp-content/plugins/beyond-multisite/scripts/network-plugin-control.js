
/*
 * global networkPluginControlBulkFile:true, networkPluginControlBulkMode:true, networkPluginControlSitesOption:true, networkPluginControlSites:true,
 * networkPluginControlAbort:true, networkPluginControlDoneCount:true, networkPluginControlRunningAjax:true, networkPluginControlLoading:true, ajaxurl,
 * localizedNetworkPluginControl
 */

/*
 * networkPluginControlBulkFile: The file of the plugin we will bulk activate/deactivate
 * networkPluginControlBulkMode: The mode of the bulk action: activate or deactivate
 * networkPluginControlSitesOption: The option for the bulk action about which site to affect
 * networkPluginControlSites: The site ids to affect with the bulk action (or to exclude, depends on the previuos option)
 * networkPluginControlRunningAjax: If it is 1 it means that an ajax request is running
 * networkPluginControlAbort: If it is 1 it means we will abort the request as soon as possible
 * networkPluginControlDoneCount: How many sites are done prosessing
 * networkPluginControlLoading: The html code for the loading message in bulk plugin activation/deactivation
 */
var networkPluginControlBulkFile, networkPluginControlBulkMode, networkPluginControlSitesOption, networkPluginControlSites,
    networkPluginControlAbort, networkPluginControlDoneCount,
    networkPluginControlRunningAjax = 0,
    networkPluginControlLoading = '<div class="be-mu-p20">'
        + '<p class="be-mu-center"><img src="' + localizedNetworkPluginControl.loadingGIF + '" /></p>'
        + '<p class="be-mu-center">' + localizedNetworkPluginControl.processing + '</p>'
        + '<p class="be-mu-center" id="be-mu-plugin-processed-so-far">&nbsp;</p>'
        + '<p class="be-mu-center"><input type="button" class="button" onclick="pluginControlCloseAbort()" value="'
        + localizedNetworkPluginControl.abort + '" /></p>'
        + '</div>';

/*
 * Makes an ajax request to a php function that network enables/disables a plugin
 * @param {String} pluginFile - The file of the plugin we will network enable/disable
 * @param {String} pluginFileHash - The md5 hash of the file of the plugin we will network enable/disable
 * @param {String} enableOrDisable - Whether we will enable or disable
 */
function pluginControlNetworkEnableDisable( pluginFile, pluginFileHash, enableOrDisable ) {
    var data;

    // We show a loading message where the link that was clicked was
    document.getElementById( 'be-mu-plugin-network-enable-disable-' + pluginFileHash ).innerHTML = '<span class="be-mu-dark-text">'
        + localizedNetworkPluginControl.processing + '</span>';

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_plugin_network_enable_disable_action',
        'enable_or_disable': enableOrDisable,
        'plugin_file': pluginFile,
        'security': localizedNetworkPluginControl.ajaxNonce,
    };

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var actionSpanInner, statusSpanInner;

        response = response.trim();

        if ( '0' === response || 'no-access' === response || 'invalid-nonce' === response ) {

            if ( 'no-access' === response ) {
                alert( localizedNetworkPluginControl.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedNetworkPluginControl.errorInvalidNonce );
            } else {
                alert( localizedNetworkPluginControl.errorResponse );
            }

            // We show an error message where the link that was clicked was
            document.getElementById( 'be-mu-plugin-network-enable-disable-' + pluginFileHash ).innerHTML = '<span class="be-mu-dark-text">'
                + localizedNetworkPluginControl.error  + '</span>';
        } else {

            // If we have disabled the plugin we show an enable action link and disabled status and if we enabled the plugin - the other way around
            if ( 'disable' === enableOrDisable ) {
                actionSpanInner = '<a href="javascript:pluginControlNetworkEnableDisable( \'' + pluginFile + '\', \''
                    + pluginFileHash + '\', \'enable\' )">' + localizedNetworkPluginControl.networkEnable + '</a>';
                statusSpanInner = '<span class="be-mu-plugin-network-circle be-mu-circle-off" title="' + localizedNetworkPluginControl.networkDisabled
                    + '"></span>';
            } else {
                actionSpanInner = '<a href="javascript:pluginControlNetworkEnableDisable( \'' + pluginFile + '\', \''
                    + pluginFileHash + '\', \'disable\' )">' + localizedNetworkPluginControl.networkDisable + '</a>';
                statusSpanInner = '<span class="be-mu-plugin-network-circle be-mu-circle-on" title="' + localizedNetworkPluginControl.networkEnabled
                    + '"></span>';
            }
            document.getElementById( 'be-mu-plugin-network-enable-disable-' + pluginFileHash ).innerHTML = actionSpanInner;
            document.getElementById( 'be-mu-plugin-network-status-' + pluginFileHash ).innerHTML = statusSpanInner;
        }
    }).fail( function() {
        alert( localizedNetworkPluginControl.errorServerFail );
        document.getElementById( 'be-mu-plugin-network-enable-disable-' + pluginFileHash ).innerHTML = '<span class="be-mu-dark-text">'
            + localizedNetworkPluginControl.error  + '</span>';
    });
}

/*
 * Shows the box with the form for bulk plugin activation/deactivation
 * @param {String} pluginFile - The file of the plugin we will bulk activation/deactivation
 */
function pluginControlShowBulk( pluginFile ) {

    var data;

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === networkPluginControlRunningAjax ) {

        // This var need to be set to 0 on every new task start
        networkPluginControlAbort = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-plugin-div-results' ).innerHTML = networkPluginControlLoading;

        // We show the layer that contains everything
        document.getElementById( 'be-mu-plugin-container' ).style.display = 'inline';

        // This is the data we will send in the ajax request
        data = {
            'action': 'be_mu_plugin_network_bulk_show_action',
            'plugin_file': pluginFile,
            'security': localizedNetworkPluginControl.ajaxNonce,
        };

        // Means that an axaj request is running now
        networkPluginControlRunningAjax = 1;

        /*
         * We are making the ajax request.
         * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
         */
        jQuery.post( ajaxurl, data, function( response ) {

            response = response.trim();

            if ( '0' === response || 'no-access' === response || 'invalid-nonce' === response ) {

                // The ajax request is not running any more
                networkPluginControlRunningAjax = 0;

                // If we haven't closed the layer and aborted we will show an error
                if ( 1 !== networkPluginControlAbort ) {

                    if ( 'no-access' === response ) {
                        alert( localizedNetworkPluginControl.errorAccess );
                    } else if ( 'invalid-nonce' === response ) {
                        alert( localizedNetworkPluginControl.errorInvalidNonce );
                    } else {
                        alert( localizedNetworkPluginControl.errorResponse );
                    }

                    // Close the loading layer after closing the alert
                    pluginControlCloseAbort();

                }
            } else {

                // We put the response in the results layer
                document.getElementById( 'be-mu-plugin-div-results' ).innerHTML = response;

                // The ajax request is not running any more
                networkPluginControlRunningAjax = 0;
            }

        }).fail( function() {
            networkPluginControlRunningAjax = 0;
            alert( localizedNetworkPluginControl.errorServerFail );
            pluginControlCloseAbort();
        });

    } else {
        alert( localizedNetworkPluginControl.errorRequest );
    }
}
         
/*
 * Starts the process of bulk activating/deactivating a plugin by reading the selected settings and calling the processing function
 * @param {String} mode - Whether we are activating or deactivating
 */
function pluginControlStartBulk( mode, pluginFile, pluginName ) {
    var element, confirmResult;

    // Whether we are activating or deactivating
    networkPluginControlBulkMode = mode;

    // The file of the affected plugin
    networkPluginControlBulkFile = pluginFile;

    // The selected affected sites option
    element = document.getElementById( 'be-mu-plugin-bulk-affect-sites-id-opt' );
    networkPluginControlSitesOption = element.options[ element.selectedIndex ].value;

    // The affected site ids option
    networkPluginControlSites = document.getElementById( 'be-mu-plugin-bulk-affect-sites-ids' ).value;

    // We ask for confirmation before we begin
    if ( 'activate' === networkPluginControlBulkMode ) {
        confirmResult = confirm( localizedNetworkPluginControl.confirmActivate + " " + pluginName + "!\n\n" + localizedNetworkPluginControl.confirmContinue );
    } else {
        confirmResult = confirm( localizedNetworkPluginControl.confirmDeactivate + " " + pluginName + "!\n\n" + localizedNetworkPluginControl.confirmContinue );
    }

    // If the user didn't click ok, we stop
    if ( true !== confirmResult ) {
        return;
    }

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === networkPluginControlRunningAjax ) {

        // These vars need to be set to 0 on every new task start
        networkPluginControlAbort = networkPluginControlDoneCount = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-plugin-div-results' ).innerHTML = networkPluginControlLoading;

        // We call the function to start working on the sites (skipping 0 of them - so from the beginninig)
        pluginControlBulkProcess( 0 );
    } else {
        alert( localizedNetworkPluginControl.errorRequest );
    }
}

/*
 * Makes an ajax call to a php function that processes the sites and bulk activates/deactivates the selected plugin.
 * If needed this function will call itself untill all sites are processed.
 * @param {Number} offset - How many sites to skip
 */
function pluginControlBulkProcess( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_plugin_bulk_action',
        'mode': networkPluginControlBulkMode,
        'plugin_file': networkPluginControlBulkFile,
        'affect_sites_id_option': networkPluginControlSitesOption,
        'affect_sites_ids': networkPluginControlSites,
        'offset': offset,
        'security': localizedNetworkPluginControl.ajaxNonce,
    };

    // Means that an axaj request is running now
    networkPluginControlRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, requestProcessedSitesCount, currentOffset, isRequestLimitReached, resultTitle, theResults;

        response = response.trim();

        if ( 'no-access' === response || 'invalid-mode' === response || 'plugin-gone' === response || 'network-activated' === response
            || 'network-only' === response || 'site-ids-filled' === response || 'site-ids-empty' === response
            || 'invalid-data' === response || '0' === response || 'invalid-nonce' === response ) {

            // The ajax request is not running any more
            networkPluginControlRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( 1 !== networkPluginControlAbort ) {

                if ( 'no-access' === response ) {
                    alert( localizedNetworkPluginControl.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedNetworkPluginControl.errorInvalidNonce );
                } else if ( 'invalid-mode' === response ) {
                    alert( localizedNetworkPluginControl.errorPluginGone );
                } else if ( 'plugin-gone' === response ) {
                    alert( localizedNetworkPluginControl.errorPluginGone );
                } else if ( 'network-activated' === response ) {
                    alert( localizedNetworkPluginControl.errorNetworkActive );
                } else if ( 'network-only' === response ) {
                    alert( localizedNetworkPluginControl.errorNetworkOnly );
                } else if ( 'site-ids-filled' === response ) {
                    alert( localizedNetworkPluginControl.errorSiteFilled );
                } else if ( 'site-ids-empty' === response ) {
                    alert( localizedNetworkPluginControl.errorSiteEmpty );
                } else if ( 'invalid-data' === response ) {
                    alert( localizedNetworkPluginControl.errorData );
                } else {
                    alert( localizedNetworkPluginControl.errorResponse );
                }

                // After the error we show the bulk form again
                pluginControlShowBulk( networkPluginControlBulkFile );
            }
        } else {

            // Make sure we haven't aborted the task
            if ( 1 !== networkPluginControlAbort ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of sites processed in the last request
                requestProcessedSitesCount = parseInt( responseObject.requestProcessedSitesCount );

                // The number of sites we skipped in the last request
                currentOffset = parseInt( responseObject.currentOffset );

                // 1 means a limit was reached and the request stopped, 0 is the opposite
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // How many sites are processed so far for the current task
                networkPluginControlDoneCount += requestProcessedSitesCount;

                // If a limit was reached in the last request this means we are not done processing all the sites
                if ( 1 === isRequestLimitReached ) {

                    // We show how many sites we have processed so far
                    document.getElementById( 'be-mu-plugin-processed-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-plugin-processed-so-far' ).innerHTML = localizedNetworkPluginControl.sitesProcessed + ' '
                        + networkPluginControlDoneCount;

                    // And we call the same function we are inside now, but with a new offset, so we continue from where we left of
                    pluginControlBulkProcess( currentOffset + requestProcessedSitesCount );
                } else {

                    /*
                     * Since a limit was not reached and request ended with success this could only mean we are done processing all the sites.
                     * So we are showing a completion message and a close button.
                     */
                    if ( 'activate' === networkPluginControlBulkMode ) {
                        resultTitle = localizedNetworkPluginControl.bulkActivation;
                    } else {
                        resultTitle = localizedNetworkPluginControl.bulkDeactivation;
                    }

                    theResults = '<div class="be-mu-p20">'
                        + '<h2 class="be-mu-plugin-h2">' + resultTitle
                        + '<div class="be-mu-right">'
                        + '<input type="button" class="button" onclick="pluginControlCloseAbort()" value="' + localizedNetworkPluginControl.close + '" />'
                        + '</div>'
                        + '</h2>'
                        + '</div>';

                    document.getElementById( 'be-mu-plugin-div-results' ).innerHTML = theResults;
                }
            }

            // An ajax request is no longer running
            networkPluginControlRunningAjax = 0;
        }
    }).fail( function() {
        networkPluginControlRunningAjax = 0;
        alert( localizedNetworkPluginControl.errorServerFail );
        pluginControlShowBulk( networkPluginControlBulkFile );
    });
}

// Sets the abort var to 1, hides the results layer and puts inside it the loading code to be ready for future requests
function pluginControlCloseAbort() {
    networkPluginControlAbort = 1;
    document.getElementById( 'be-mu-plugin-container' ).style.display = 'none';
    document.getElementById( 'be-mu-plugin-div-results' ).innerHTML = networkPluginControlLoading;
}

// Reloads the network plugin control page
function pluginControlReloadPage() {
    window.location.href = localizedNetworkPluginControl.pageURL;
}

// Redirects to the URL that hides the import settings message
function pluginControlHideImport() {

    // We ask for confirmation before we begin
    confirmResult = confirm( localizedNetworkPluginControl.confirmHideImport + "!\n\n" + localizedNetworkPluginControl.confirmContinue );
           
    // If the user didn't click ok, we stop
    if ( true !== confirmResult ) {
        return;
    }

    window.location.href = localizedNetworkPluginControl.hideImportURL;
}

// Sets the abort var to 1, hides the results layer and puts inside it the loading code to be ready for future requests, and reloads the page
function pluginControlCloseAbortReload() {
    pluginControlCloseAbort();
    pluginControlReloadPage();
}

// Gets the selected plugin and calls the function to start the activated in process
function pluginControlActivatedInStart() {
    var element, pluginFile;

    element = document.getElementById( 'be-mu-plugin-bulk-plugin' );
    pluginFile = element.options[ element.selectedIndex ].value;

    // Call to a function in the activated in scripts, to start the process
    activatedInStart( pluginFile, 'plugin' );                              
}

// Starts the process of importing plugin user control settings from the plugin Multisite Plugin Manager
function pluginControlImport() {
    var confirmResult;

    // We ask for confirmation before we begin
    confirmResult = confirm( localizedNetworkPluginControl.confirmImport + "\n\n" + localizedNetworkPluginControl.confirmContinue );

    // If the user didn't click ok, we stop
    if ( true !== confirmResult ) {
        return;
    }

    // If there is no ajax request running right now, we continue, but otherwise we show an error
    if ( 0 === networkPluginControlRunningAjax ) {

        // These vars need to be set to 0 on every new task start
        networkPluginControlAbort = networkPluginControlDoneCount = 0;

        // We show a loading message for now
        document.getElementById( 'be-mu-plugin-div-results' ).innerHTML = networkPluginControlLoading;

        // And we make the layer with the loading message visible
        document.getElementById( 'be-mu-plugin-container' ).style.display = 'inline';

        // We call the function to start working on the sites (skipping 0 of them - so from the beginninig)
        pluginControlImportProcess( 0 );
    } else {
        alert( localizedNetworkPluginControl.errorRequest );
    }
}

/*
 * Makes an ajax call to a php function that processes the sites and applies any site specific plugin access settings.
 * It also imports the network plugin control settings. If needed this function will call itself untill all sites are processed.
 * @param {Number} offset - How many sites to skip
 */
function pluginControlImportProcess( offset ) {
    var data;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_plugin_import_action',
        'offset': offset,
        'security': localizedNetworkPluginControl.ajaxNonce,
    };

    // Means that an axaj request is running now
    networkPluginControlRunningAjax = 1;

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var responseObject, requestProcessedSitesCount, currentOffset, isRequestLimitReached, theResults;

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            // The ajax request is not running any more
            networkPluginControlRunningAjax = 0;

            // If we haven't closed the layer and aborted we will show an error
            if ( networkPluginControlAbort !== 1 ) {

                if ( 'no-access' === response ) {
                    alert( localizedNetworkPluginControl.errorAccess );
                } else if ( 'invalid-nonce' === response ) {
                    alert( localizedNetworkPluginControl.errorInvalidNonce );
                } else {
                    alert( localizedNetworkPluginControl.errorResponse );
                }

                // Close the loading layer after closing the alert
                pluginControlCloseAbort();
            }
        } else {

            // Make sure we haven't aborted the task
            if ( 1 !== networkPluginControlAbort ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                // The number of sites processed in the last request
                requestProcessedSitesCount = parseInt( responseObject.requestProcessedSitesCount );

                // The number of sites we skipped in the last request
                currentOffset = parseInt( responseObject.currentOffset );

                // 1 means a limit was reached and the request stopped, 0 is the opposite
                isRequestLimitReached = parseInt( responseObject.isRequestLimitReached );

                // How many sites are processed so far for the current task
                networkPluginControlDoneCount += requestProcessedSitesCount;

                // If a limit was reached in the last request this means we are not done processing all the sites
                if ( 1 === isRequestLimitReached ) {

                    // We show how many sites we have processed so far
                    document.getElementById( 'be-mu-plugin-processed-so-far' ).style.display = 'block';
                    document.getElementById( 'be-mu-plugin-processed-so-far' ).innerHTML = localizedNetworkPluginControl.sitesProcessed + ' '
                        + networkPluginControlDoneCount;

                    // And we call the same function we are inside now, but with a new offset, so we continue from where we left of
                    pluginControlImportProcess( currentOffset + requestProcessedSitesCount );
                } else {

                    /*
                     * Since a limit was not reached and request ended with success this could only mean we are done processing all the sites.
                     * So we are showing a completion message and a close button.
                     */
                    theResults = '<div class="be-mu-p20">'
                        + '<h2 class="be-mu-plugin-h2">' + localizedNetworkPluginControl.importCompleted
                        + '<div class="be-mu-right">'
                        + '<input type="button" class="button" onclick="pluginControlCloseAbortReload()" value="' + localizedNetworkPluginControl.close + '" />'
                        + '</div>'
                        + '</h2>'
                        + '</div>';

                    document.getElementById( 'be-mu-plugin-div-results' ).innerHTML = theResults;
                }
            }

            // An ajax request is no longer running
            networkPluginControlRunningAjax = 0;
        }
    }).fail( function() {
        networkPluginControlRunningAjax = 0;
        alert( localizedNetworkPluginControl.errorServerFail );
        pluginControlCloseAbort();
    });
}
