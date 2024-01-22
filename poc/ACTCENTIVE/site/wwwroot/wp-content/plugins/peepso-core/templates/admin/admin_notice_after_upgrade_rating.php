<div class="notice notice-warning peepso">
	<p>
	<?php echo sprintf(__('Thank you for upgrading PeepSo to the latest version. Please consider leaving us a <a href="%s" aria-label="Leave us a review!">★★★★★ review.</a> Thank you in advance!', 'peepso-core'), 'https://peep.so/review'); ?>
    </p>
    <p>
		<a id="ps-gs-notice-dismiss" href="#" class="button ps-js-gs-notice-dimiss">
			<?php echo __('Dismiss','peepso-core') ?>
		</a>
	</p>
</div>
<script>
setTimeout(function() {
	jQuery(function( $ ) {
		$( '.ps-js-gs-notice-dimiss' ).on( 'click', function( e ) {
			e.preventDefault();
			e.stopPropagation();
			$( this ).closest( '.notice' ).remove();
			$.get( window.location.href, { peepso_hide_bundle: 1 } );
		})
	});
}, 100 );
</script>