import FocusArea from './focus-area';

// Initalize focus area on document ready.
document.addEventListener( 'DOMContentLoaded', function() {
	let container = document.querySelector( '.ps-js-focus' );
	if ( container ) {
		new FocusArea( container );
	}
} );
