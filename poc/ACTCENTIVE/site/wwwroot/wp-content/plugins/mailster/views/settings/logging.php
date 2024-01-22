<p class="description"><?php esc_html_e( 'You can log all outgoing mails sent by Mailster. This is useful if you like to debug issues during sending. Disable this settings if you don\'t need logging as it uses more server resources.', 'mailster' ); ?></p>
<table class="form-table">
	<tr valign="top" class="settings-row settings-row-logging">
		<th scope="row"><?php esc_html_e( 'Enable Logging', 'mailster' ); ?></th>
		<td><label><input type="hidden" name="mailster_options[logging]" value=""><input type="checkbox" name="mailster_options[logging]" value="1" <?php checked( mailster_option( 'logging' ) ); ?>> <?php esc_html_e( 'Enable Logging for outgoing mails sent by Mailster.', 'mailster' ); ?></label>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-logging-max">
		<th scope="row"><?php esc_html_e( 'Max. Entries', 'mailster' ); ?> *</th>
		<td><input type="number" name="mailster_options[logging_max]" value="<?php echo esc_attr( mailster_option( 'logging_max' ) ); ?>" class="small-text"> <span class="description"><?php esc_html_e( 'Number of entries to keep in the database.', 'mailster' ); ?></span></td>
	</tr>
	<tr valign="top" class="settings-row settings-logging-days">
		<th scope="row"><?php esc_html_e( 'Max. Days', 'mailster' ); ?> *</th>
		<td><input type="number" name="mailster_options[logging_days]" value="<?php echo esc_attr( mailster_option( 'logging_days' ) ); ?>" class="small-text"> <span class="description"><?php esc_html_e( 'Number of days to keep entries in the database.', 'mailster' ); ?></span></td>
	</tr>
</table>
<p class="description">* <?php esc_html_e( 'Cleanup is delayed so the database can contain more entries. Keep fields empty to ignore settings.', 'mailster' ); ?></p>
