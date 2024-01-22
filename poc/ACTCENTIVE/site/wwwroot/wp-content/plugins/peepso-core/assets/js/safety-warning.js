import $ from 'jquery';

$.fn.psSafetyWarning = function() {
	return this.each(( index, elem ) => {
		$( elem ).find( '.ps-js-close' ).on( 'click', ( e ) => {
			$( e.target ).closest( '.ps-js-safety-warning' ).remove();
		});
	});
};

$(() => {
	$( '.ps-js-safety-warning' ).psSafetyWarning();
});
