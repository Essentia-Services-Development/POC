<?php require 'clean-list-notice.php'; ?>
<p class="howto"><?php esc_html_e( 'Copy and paste from your spreadsheet app. Mailster tries the guess the used formatting.', 'mailster' ); ?></p>
<form id="import_paste" method="post" class="importer-form" data-type="paste">
<textarea name="paste" class="widefat code" rows="13" placeholder="<?php echo 'justin.case@' . $_SERVER['HTTP_HOST'] . ' Justin; Case; Custom;&#10;john.doe@' . $_SERVER['HTTP_HOST'] . ' John; Doe;;&#10;jane.roe@' . $_SERVER['HTTP_HOST'] . ' Jane; Roe;;'; ?>"></textarea>
	<section class="footer alternate">
		<p>
			<?php submit_button( esc_html__( 'Next Step', 'mailster' ) . ' &#x2192;', 'primary', 'submit', false ); ?>
			<span class="status wp-ui-text-icon"></span>
		</p>
	</section>
</form>
