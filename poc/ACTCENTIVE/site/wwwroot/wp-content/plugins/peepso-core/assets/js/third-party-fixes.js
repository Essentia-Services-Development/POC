// Fix Divi theme issue.
jQuery(( $ ) => {
	let $wrapper = $( '#peepso-wrap' );
	if ( $wrapper.length ) {
		let $row = $wrapper.closest( '.et_pb_row' );
		if ( $row.length ) {
			$row.find( '.et_pb_column' ).css({ zIndex: 'auto' });
		}
	}
});
