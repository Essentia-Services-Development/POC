
/* global ajaxurl, localizedSitePluginControl */

/*
 * Makes an ajax request to a php function that site enables/disables a selected plugin
 * @param {String} pluginFile - The file of the plugin we will enable/disable
 * @param {String} pluginFileHash - The md5 hash of the file of the plugin we will enable/disable
 * @param {String} enableOrDisable - Enable or disable
 */
function pluginControlSiteEnableDisable( pluginFile, pluginFileHash, enableOrDisable ) {
    var data;

    // Show a loading message where the enable/disable action link was
    document.getElementById( 'be-mu-plugin-site-enable-disable-' + pluginFileHash ).innerHTML = localizedSitePluginControl.loading;

    // This is the data we will send in the ajax request
    data = {
        'action': 'be_mu_plugin_site_enable_disable_action',
        'enable_or_disable': enableOrDisable,
        'plugin_file': pluginFile,
        'blog_id': localizedSitePluginControl.siteID,
        'security': localizedSitePluginControl.ajaxNonce,
    };

    /*
     * We are making the ajax request.
     * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
     */
    jQuery.post( ajaxurl, data, function( response ) {
        var actionSpanInner, statusSpanInner;

        response = response.trim();

        if ( 'no-access' === response || '0' === response || 'invalid-nonce' === response ) {

            if ( 'no-access' === response ) {
                alert( localizedSitePluginControl.errorAccess );
            } else if ( 'invalid-nonce' === response ) {
                alert( localizedSitePluginControl.errorInvalidNonce );
            } else {
                alert( localizedSitePluginControl.errorResponse );
            }

            // Show an error message where the enable/disable action link was
            document.getElementById( 'be-mu-plugin-site-enable-disable-' + pluginFileHash ).innerHTML = localizedSitePluginControl.error;
        } else {

            // If we have disabled the plugin we show an enable action link and disabled status and if we enabled the plugin - the other way around
            if ( 'disable' === enableOrDisable ) {
                actionSpanInner = '<a href="javascript:pluginControlSiteEnableDisable(\'' + pluginFile + '\', \''
                    + pluginFileHash + '\', \'enable\')">' + localizedSitePluginControl.enable + '</a>';
                statusSpanInner = '<div class="be-mu-plugin-circle be-mu-circle-off" title="' + localizedSitePluginControl.disabled + '"></div>';
            } else {
                actionSpanInner = '<a href="javascript:pluginControlSiteEnableDisable(\'' + pluginFile + '\', \''
                    + pluginFileHash + '\', \'disable\')">' + localizedSitePluginControl.disable + '</a>';
                statusSpanInner = '<div class="be-mu-plugin-circle be-mu-circle-on" title="' + localizedSitePluginControl.enabled + '"></div>';
            }
            document.getElementById( 'be-mu-plugin-site-enable-disable-' + pluginFileHash ).innerHTML = actionSpanInner;
            document.getElementById( 'be-mu-plugin-site-status-' + pluginFileHash ).innerHTML = statusSpanInner;
        }
    }).fail( function() {
        alert( localizedSitePluginControl.errorServerFail );
        document.getElementById( 'be-mu-plugin-site-enable-disable-' + pluginFileHash ).innerHTML = localizedSitePluginControl.error;
    });
}
