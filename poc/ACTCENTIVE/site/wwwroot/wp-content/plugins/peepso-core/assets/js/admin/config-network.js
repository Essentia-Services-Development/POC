jQuery( function( $ ) {
	var $sse = $( 'input[name=sse]' ),
		$fields = $sse.closest( '.form-group' ).nextAll( '.form-group' );

	$sse.on( 'click', function() {
		this.checked ? $fields.show() : $fields.hide();
	} );
	$sse.triggerHandler( 'click' );
} );
