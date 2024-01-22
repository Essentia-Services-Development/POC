
/* global localizedFlags */

// When the document loads, we start getting the country codes of the IP addresses that do not still have a country code in the database and are visible on the page
jQuery( document ).ready( function( $ ) {
    flagsGetCountryCode();
});

// Gets the country code for an IP address visible on the page and puts the country flag next to it. It calls itself untill all IP addresses on the page are done.
function flagsGetCountryCode() {

    var currentID, idPartsArray, userID, userIP;

    // If elements with this class exist, then there are still IP addresses for which we have not checked the country code
    if ( jQuery( '.be-mu-ban-pending-flag' ).length ) {

        // The ID of the current element that we are checking
        currentID = jQuery( '.be-mu-ban-pending-flag' ).first().attr( 'id' );

        // We split the ID to get the user ID
        idPartsArray = currentID.split( '-' );

        // The IP address is for this user ID
        userID = parseInt( idPartsArray[5] );

        // This is the IP address we are checking
        userIP = document.getElementById( 'be-mu-ban-ip-value-' + userID ).innerHTML;

        // This is the data we will send in the ajax request
        data = {
            'action': 'be_mu_ban_check_ip_country_action',
            'ip': userIP,
            'user_id': userID,
            'be_mu_flags_nonce': localizedFlags.ajaxNonce
        };

        /*
         * We are making the ajax request.
         * Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
         */
        jQuery.post( ajaxurl, data, function( response ) {

            var responseObject, countryCode, country;

            response = response.trim();

            // If we actually got some real data (and not an error) we show the flag next to the IP address
            if ( '' != response ) {

                // We parse the response into an object
                responseObject = jQuery.parseJSON( response );

                countryCode = responseObject.countryCode;
                country = responseObject.country;

                // We set the country string in the tooltip span element
                document.getElementById( 'be-mu-ban-ip-country-tooltip-' + userID ).innerHTML = country;

                // We add the classes that will show the correct flag in the flag span element
                jQuery( '#' + currentID ).addClass( 'flag flag-' + countryCode.toLowerCase() );
            }

            // We remove the pending flag class so we do not check this element twice and we know it is done
            jQuery( '#be-mu-ban-ip-flag-' + userID ).removeClass( 'be-mu-ban-pending-flag' );

            // After half a second, so we do not make too many requests too fast, we check the next IP address (if any)
            setTimeout( 'flagsGetCountryCode()', 500 );
        });
    }
}
