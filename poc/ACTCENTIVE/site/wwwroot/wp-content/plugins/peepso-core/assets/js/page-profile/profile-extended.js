jQuery(function( $ ) {

	var sepClass = '.ps-js-profile-separator',
		$list = $('.ps-js-profile-list'),
		$seps;

	// remove separator with no children
	if ( $list.length ) {
		$seps = $list.find( sepClass ).closest('.ps-js-profile-item');
		$seps.each(function() {
			var $sep = $( this ),
				$next = $sep.next();

			if ( ! $next.length ) {
				$sep.remove();
				return true;
			}

			if ( $next.find( sepClass ).length ) {
				$sep.remove();
				return true;
			}
		});
	}
});
