jQuery( function( $ ) {
	var $mycred = $( 'input[name=mycred_enabled]' )

	// Toggle mycred points configs.
	$mycred.on( 'click', function() {
		var $enable = $( this ).closest( '.form-group' );
		var $target = $enable.next().nextAll();

		if ( this.checked ) {
			$target.show();
		} else {
			$target.hide();

		}
	} );

	$mycred.triggerHandler( 'click' );
} );

(function( $ ) {

	var $mycred = $('#mycred_enabled');
	var $mycredHistory = $('#field_mycred_point_history_enabled');

	$mycred.on('click', function() {
		var $enable = $( '#field_mycred_enabled' ).closest( '.form-group' );
		var $target = $enable.next().nextAll();
		if ( this.checked ) {
			$mycredHistory.show();

			$target.hide();
		} else {
			$mycredHistory.hide();

			$target.show();
		}
	});
	$mycred.triggerHandler('click');

})( jQuery );
