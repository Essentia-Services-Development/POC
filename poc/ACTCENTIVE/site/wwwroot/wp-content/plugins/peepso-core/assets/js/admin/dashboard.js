jQuery( function( $ ) {
	var $mostRecent = $( '#peepso_dashboard_most_recent' );

	$mostRecent.on( 'click', '[data-toggle=tab]', function( e ) {
		var $a = $( this ),
			$tab = $a.closest( 'li' ),
			$div = $mostRecent.find( $a.attr( 'href' ) );

		e.preventDefault();
		e.stopPropagation();

		if ( $div.length ) {
			$tab.siblings().removeClass( 'active' );
			$tab.addClass( 'active' );
			$div.siblings().removeClass( 'active' );
			$div.addClass( 'active' );
		}
	} );
} );
