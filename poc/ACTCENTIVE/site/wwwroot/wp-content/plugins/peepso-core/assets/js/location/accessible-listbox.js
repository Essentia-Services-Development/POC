import $ from 'jquery';

export default class AccessibleListbox {
	constructor( elem ) {
		$( elem )
			.on( 'focus', '[role=listbox]', ( e ) => { this.handleFocus( e ) })
			.on( 'keydown', '[role=listbox]', ( e ) => { this.handleKeydown( e ) })
			.on( 'mousedown', '[role=option]', ( e ) => { this.handleMousedown( e ) });
	}

	handleFocus( e ) {
		let $elem = $( e.target );

		// If no option is selected, select the first option by default.
		if ( ! $elem.find( '[aria-selected=true]' ).length ) {
			$elem.find( '[role=option]:first' )
				.attr( 'aria-selected', 'true' )
				.addClass( 'ps-selected' )
				.focus();
		} else {
			$elem.find( '[aria-selected=true]' )
				.focus();
		}
	}

	handleKeydown( e ) {
		let $elem = $( e.currentTarget ),
			$current = $elem.find( '[aria-selected=true]' ),
			key = e.keyCode;

		if ( key === 38 || key === 40 ) {
			e.preventDefault();

			// Up arrow
			if ( key === 38 && $current.prev().length ) {
				$current
					.attr( 'aria-selected', 'false' )
					.removeClass( 'ps-selected' );
				$current.prev()
					.attr( 'aria-selected', 'true' )
					.addClass( 'ps-selected' )
					.focus();

			// Down arrow
			} else if ( key === 40 && $current.next().length ) {
				$current
					.attr( 'aria-selected', 'false' )
					.removeClass( 'ps-selected' );
				$current.next()
					.attr( 'aria-selected', 'true' )
					.addClass( 'ps-selected' )
					.focus();
			}
		}
	}

	handleMousedown( e ) {
		let $elem = $( e.currentTarget );

		$elem.siblings( '[aria-selected=true]' )
			.attr( 'aria-selected', 'false' )
			.removeClass( 'ps-selected' );

		$elem.attr( 'aria-selected', 'true' )
			.addClass( 'ps-selected' );
	}
}
