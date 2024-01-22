import $ from 'jquery';
import { observer } from 'peepso';

function permalinkInit() {
	$( '.ps-js-permalink' ).each( function() {
		let $btn = $( this );
		$btn.removeClass( 'ps-js-permalink' );
		$btn.on( 'click', function( e ) {
			e.preventDefault();
			e.stopPropagation();

			let $btn = $( this );
			let $link = $btn.closest( 'a[href]' );
			if ( $link.length ) {
				peepso.util.copyToClipboard( $link.attr( 'href' ) );
				$btn.attr( 'data-tooltip', $btn.data( 'tooltip-success' ) );
			}
		} );
		$btn.on( 'mouseleave', function() {
			let $btn = $( this ),
				tooltip = $btn.data( 'tooltip-initial' );

			if ( tooltip !== $btn.attr( 'data-tooltip' ) ) {
				$btn.attr( 'data-tooltip', tooltip );
			}
		} );
	} );
}

function init() {
	// Initialize permalink on page load.
	$(function() {
		setTimeout( permalinkInit, 1000 );
	});

	// Initialize permalink everytime `peepso_activity` filter is called.
	observer.addFilter(
		'peepso_activity',
		$posts => {
			setTimeout( permalinkInit, 1000 );
			return $posts;
		},
		10,
		1
	);
}

export default { init };
