<p class="description"><?php esc_html_e( 'Some of these settings may affect your website. In normal circumstance it is not required to change anything on this page.', 'mailster' ); ?></p>
<table class="form-table">
	<tr valign="top" class="settings-row settings-row-usage-mailster-logo">
		<th scope="row"><?php esc_html_e( 'Mailster Branding', 'mailster' ); ?></th>
		<td>
			<label><input type="hidden" name="mailster_options[mailster_branding]" value=""><input type="checkbox" name="mailster_options[mailster_branding]" value="1" <?php checked( mailster_option( 'mailster_branding' ) ); ?>> <?php esc_html_e( 'Show Mailster Branding in your campaigns.', 'mailster' ); ?></label> <p class="description"><?php esc_html_e( 'If you enable this option your campaigns contain a small logo of Mailster at the very bottom of the email.', 'mailster' ); ?>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-cache">
		<th scope="row"><?php esc_html_e( 'Cache', 'mailster' ); ?></th>
		<td>
			<label><input type="hidden" name="mailster_options[disable_cache_frontpage]" value=""><input type="checkbox" name="mailster_options[disable_cache_frontpage]" value="1" <?php checked( mailster_option( 'disable_cache_frontpage' ) ); ?>> <?php esc_html_e( 'Disable Form Caching', 'mailster' ); ?></label> <p class="description"><?php esc_html_e( 'Enable this option if you have issue with the security nonce on Mailster forms.', 'mailster' ); ?></p>
			<br><label><input type="hidden" name="mailster_options[disable_cache]" value=""><input type="checkbox" name="mailster_options[disable_cache]" value="1" <?php checked( mailster_option( 'disable_cache' ) ); ?>> <?php esc_html_e( 'Disable Object Cache for Mailster', 'mailster' ); ?></label> <p class="description"><?php esc_html_e( 'If enabled Mailster doesn\'t use cache anymore. This causes an increase in page load time! This option is not recommended!', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-short-codes">
		<th scope="row"><?php esc_html_e( 'Short Codes', 'mailster' ); ?><?php echo mailster()->beacon( '611bba3321ef206e5592c322' ); ?></th>
		<td><label><input type="hidden" name="mailster_options[shortcodes]" value=""><input type="checkbox" name="mailster_options[shortcodes]" value="1" <?php checked( mailster_option( 'shortcodes' ) ); ?>> <?php esc_html_e( 'Process short codes in emails.', 'mailster' ); ?></label> <p class="description"><?php esc_html_e( 'Check this option to process short codes. This may cause unexpected results.', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-remove-data">
		<th scope="row"><?php esc_html_e( 'Remove Data', 'mailster' ); ?></th>
		<td><label><input type="hidden" name="mailster_options[remove_data]" value=""><input type="checkbox" name="mailster_options[remove_data]" value="1" <?php checked( mailster_option( 'remove_data' ) ); ?>> <?php esc_html_e( 'Remove all data on plugin deletion', 'mailster' ); ?></label> <p class="description"><?php esc_html_e( 'Mailster will remove all it\'s data if you delete the plugin via the plugin page.', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-url-rewrite">
		<th scope="row"><?php esc_html_e( 'URL Rewrite', 'mailster' ); ?></th>
		<td><label><input type="hidden" name="mailster_options[got_url_rewrite]" value=""><input type="checkbox" name="mailster_options[got_url_rewrite]" value="1" <?php checked( mailster_option( 'got_url_rewrite' ) ); ?>> <?php esc_html_e( 'Website supports URL rewrite', 'mailster' ); ?></label> <p class="description"><?php esc_html_e( 'Mailster detects this setting by default so change only if detection fails.', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-form-post-protection">
		<th scope="row"><?php esc_html_e( 'Form POST protection', 'mailster' ); ?></th>
		<td><input type="text" name="mailster_options[post_nonce]" value="<?php echo esc_attr( mailster_option( 'post_nonce' ) ); ?>" class="regular-text" style="width: 100px;"> <label><input type="hidden" name="mailster_options[use_post_nonce]" value=""><input type="checkbox" name="mailster_options[use_post_nonce]" value="1" <?php checked( mailster_option( 'use_post_nonce' ) ); ?>> <?php esc_html_e( 'Use on internal forms.', 'mailster' ); ?></label> <span class="description"><?php esc_html_e( 'Check if you have a heavy cached page and problems with invalid Security Nonce.', 'mailster' ); ?></span>
			<p class="description"><?php esc_html_e( 'A unique string to prevent form submissions via POST. Pass this value in a \'_nonce\' variable. Keep empty to disable test.', 'mailster' ); ?></p></td>
	</tr>
	<tr valign="top" class="settings-row settings-row-legacy-hooks">
		<th scope="row"><?php esc_html_e( 'Legacy Hooks', 'mailster' ); ?></th>
		<td><label><input type="checkbox" name="mailster_options[legacy_hooks]" value="1" <?php checked( mailster_option( 'legacy_hooks' ) ); ?>> <?php esc_html_e( 'Enable legacy hooks', 'mailster' ); ?></label> <p class="description"><?php esc_html_e( 'If you still use deprecated MyMail hooks and filters you can keep them working by enabling this option.', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-send-test">
		<th scope="row"><?php esc_html_e( 'Send Test', 'mailster' ); ?></th>
		<td>
		<div class="mailster-testmail">
			<input type="text" value="<?php echo esc_attr( $test_email ); ?>" autocomplete="off" class="form-input-tip mailster-testmail-email">
			<input type="button" value="<?php esc_attr_e( 'Send Test', 'mailster' ); ?>" class="button mailster_sendtest" data-role="basic">
			<div class="loading test-ajax-loading"></div>
		</div>
		</td>
	</tr>
</table>
