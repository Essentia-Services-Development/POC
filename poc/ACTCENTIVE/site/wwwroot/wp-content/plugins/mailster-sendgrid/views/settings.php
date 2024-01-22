<table class="form-table">
	<?php if ( ! $verified ) : ?>
	<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td><p class="description"><?php echo sprintf( __( 'You need a %s account to use this service!', 'mailster-sendgrid' ), '<a href="https://app.sendgrid.com/settings/api_keys" class="external">SendGrid</a>' ); ?></p>
		</td>
	</tr>
	<?php endif; ?>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'SendGrid API Key', 'mailster-sendgrid' ); ?></th>
		<td><input type="password" name="mailster_options[sendgrid_apikey]" value="<?php echo esc_attr( mailster_option( 'sendgrid_apikey' ) ); ?>" class="regular-text" autocomplete="new-password"></td>
	</tr>
	<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td>
			<?php if ( $verified ) : ?>
			<span style="color:#3AB61B">&#10004;</span> <?php esc_html_e( 'Your API Key is ok!', 'mailster-sparkpost' ); ?>
			<?php else : ?>
			<span style="color:#D54E21">&#10006;</span> <?php esc_html_e( 'Your API Key is WRONG!', 'mailster-sparkpost' ); ?>
			<?php endif; ?>

			<input type="hidden" name="mailster_options[sendgrid_verified]" value="<?php echo $verified; ?>">
		</td>
	</tr>
</table>
<div class="<?php echo ( ! $verified ) ? 'hidden' : ''; ?>">
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Send Emails with', 'mailster-sendgrid' ); ?></th>
		<td>
		<select name="mailster_options[sendgrid_api]">
			<option value="web" <?php selected( mailster_option( 'sendgrid_api' ), 'web' ); ?>>WEB API</option>
			<option value="smtp" <?php selected( mailster_option( 'sendgrid_api' ), 'smtp' ); ?>>SMTP API</option>
		</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Secure Connection', 'mailster-sendgrid' ); ?></th>
		<td><label><input type="hidden" name="mailster_options[sendgrid_secure]" value=""><input type="checkbox" name="mailster_options[sendgrid_secure]" value="1" <?php checked( mailster_option( 'sendgrid_secure' ), true ); ?>> <?php esc_html_e( 'use secure connection for SMTP delivery', 'mailster-sendgrid' ); ?></label></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Categories', 'mailster-sendgrid' ); ?></th>
		<td><input type="text" name="mailster_options[sendgrid_categories]" value="<?php echo esc_attr( mailster_option( 'sendgrid_categories' ) ); ?>" class="large-text">
		<p class="howto"><?php echo sprintf( __( 'Define up to 10 %s, separated with commas which get send via SendGrid X-SMTPAPI', 'mailster-sendgrid' ), '<a href="https://sendgrid.com/docs/API_Reference/SMTP_API/categories.html" class="external">' . __( 'Categories', 'mailster-sendgrid' ) . '</a>' ); ?></p>
	</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Bounce Handling via', 'mailster-sendgrid' ); ?></th>
		<td>
		<select name="mailster_options[sendgrid_bouncehandling]">
			<option value="sendgrid" <?php selected( mailster_option( 'sendgrid_bouncehandling' ), 'sendgrid' ); ?>>SendGrid (<?php esc_html_e( 'recommended', 'mailster-sendgrid' ); ?>)</option>
			<option value="mailster" <?php selected( mailster_option( 'sendgrid_bouncehandling' ), 'mailster' ); ?>>Mailster</option>
		</select> <span class="description"><?php esc_html_e( 'Mailster cannot handle bounces when the WEB API is used', 'mailster-sendgrid' ); ?></span>
		</td>
	</tr>
</table>
</div>
