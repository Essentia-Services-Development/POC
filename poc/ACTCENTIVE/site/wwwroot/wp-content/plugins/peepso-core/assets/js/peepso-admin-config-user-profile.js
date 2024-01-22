(function($) {

	// Toggle verbose mode.
	$( 'button[name=peepso_unsub_email_notification]' ).on( 'click', function(e) {
		console.log('save preferences');
		e.preventDefault();

		var $el = jQuery( e && e.target ? e.target : e ),
		$loading = $el.closest('#ps-js-unsub-email').find('.ps-js-loading'),
		$message = $el.closest('#ps-js-unsub-email').find('#ps-js-unsub-message'),
		params = {};
		console.log($loading);

		params.user_id = $el.closest('#ps-js-unsub-email').find('input[name=peepso_unsub_user_id]').val();
		params._wpnonce = $el.closest('#ps-js-unsub-email').find('input[name=peepso_unsub_nonce]').val();
		params.action = 'peepso_user_unsubscribe_emails';
		params.all = 1;

		$loading.find('i').stop().hide();
		$loading.find('img').show();

		$.post(peepsoconfiguserdata.ajax_url, params, function(response) {
			data = $.parseJSON( response );
			
			$.each( data.messages, function( key, value ) {
				msg = value + "<br>";
				$message.html( $message.html() + msg);
			});
			$loading.find('img').hide();
			$loading.find('i').show().delay( 800 ).fadeOut();
		});
	});

})(jQuery);
