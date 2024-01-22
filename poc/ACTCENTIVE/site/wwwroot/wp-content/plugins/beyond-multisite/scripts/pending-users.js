
/* global localizedPendingUsers */

// This function will run when the dropdown menu for the page number is changed and it will redirect the super admin to the selected page url
function pendingUsersGoToPendingPage() {
    var element, pageNumber;

    // We get the selected option
    element = document.getElementById( 'be-mu-pending-page-number' );
    pageNumber = parseInt( element.options[ element.selectedIndex ].value );

    // We redirect the browser to the url of the selected page number
    window.location.href = localizedPendingUsers.pageURL + '&page_number=' + pageNumber + localizedPendingUsers.searchString;
}

// Starts a search for pending users
function pendingUsersSearch() {
    var searchString;

    // We get the text from the search text field
    searchString = document.getElementById( 'be-mu-search-pending-string' ).value;

    // We redirect the browser to the serach page
    window.location.href = localizedPendingUsers.pageURL + '&search=' + searchString;
}

/*
 * Handles keys pressed while in the search text box and intercepts the enter key to start a search
 * @param {Object} event
 */
function pendingUsersKeyPressSeach( event ) {

    // If it is enter that is pressed
    if ( 13 === event.keyCode ) {

        // Ensures that only this code will run
        event.preventDefault();

        // Start a search
        pendingUsersSearch();
    }
}

/*
 * When the action links are clicked this function will ask for confirmation and redirect to the action url if it is given
 * @param {Number} action
 * @param {String} signupId
 */
function pendingUsersActionLink( action, signupId ) {
    var confirmMessage;

    // Based on which action link is clicked we set the confirmMessage variable
    if ( 'activate' === action ) {
        confirmMessage = localizedPendingUsers.confirmActivate;
    } else if ( 'resend' === action ) {
        confirmMessage = localizedPendingUsers.confirmResend;
    } else if ( 'delete' === action ) {
        confirmMessage = localizedPendingUsers.confirmDelete;
    } else {
        alert( localizedPendingUsers.invalidAction );
        return;
    }

    // We ask the user to confirm the action and abort if the action was canceled
    if ( ! confirm( confirmMessage ) ) {
        return;
    }

    // If the action was confirmed we redirect to the url that will perform the appropriate action
    window.location.href = localizedPendingUsers.pageURL + '&page_number=' + localizedPendingUsers.pageNumber
        + '&signup_id=' + signupId + '&action=' + action + localizedPendingUsers.searchString;
}

// Goes to the next or previous page of the current list of pending users
function pendingUsersNextPreviousPage( page ) {
    jQuery( "#be-mu-pending-page-number" ).val( page );
    pendingUsersGoToPendingPage();
}
