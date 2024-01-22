
/* global localizedBanUsers */

// This function will run when the dropdown menu for the page number is changed and it will redirect the super admin to the selected page url
function banUsersGoToBannedPage() {
    var element, pageNumber;

    // We get the selected option
    element = document.getElementById( 'be-mu-banned-page-number' );
    pageNumber = parseInt( element.options[ element.selectedIndex ].value );

    // We redirect the browser to the url of the selected page number
    window.location.href = localizedBanUsers.pageURL + '&page_number=' + pageNumber + localizedBanUsers.searchString;
}

// Starts a search for banned users
function banUsersSearchBanned() {
    var searchString;

    // We get the text from the search text field
    searchString = document.getElementById( 'be-mu-search-banned-string' ).value;

    // We redirect the browser to the serach page
    window.location.href = localizedBanUsers.pageURL + '&search=' + searchString;
}

/*
 * Handles keys pressed while in the search text box and intercepts the enter key to start a search
 * @param {Object} event
 */
function banUsersKeypressSearchBanned( event ) {

    // If it is enter that is pressed
    if ( 13 === event.keyCode ) {

        // Ensures that only this code will run
        event.preventDefault();

        // Start a search
        banUsersSearchBanned();
    }
}

// Goes to the next or previous page of the current list of logs
function banUsersNextPreviousPage( page ) {
    jQuery( "#be-mu-banned-page-number" ).val( page );
    banUsersGoToBannedPage();
}
