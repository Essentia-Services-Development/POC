<?php

	$dateformat = mailster( 'helper' )->dateformat();

	$license_email = '';
	$license_user  = '';

if ( mailster()->is_verified() ) {
	$license_user  = mailster()->get_username( '' );
	$license_email = mailster()->get_email( '' );
}


?>
<div class="locked">
	<h2><span class="not-valid"><?php esc_html_e( 'Please Activate your License', 'mailster' ); ?></span><span class="valid"><?php esc_html_e( 'Validated!', 'mailster' ); ?></span>
	</h2>
</div>
<dl class="mailster-icon mailster-is-valid valid">
	<dt><?php esc_html_e( 'Verified License', 'mailster' ); ?></dt>
	<dd><?php printf( esc_html__( 'User: %1$s - %2$s', 'mailster' ), '<span class="mailster-username">' . esc_html( $license_user ) . '</span>', '<span class="mailster-email lighter">' . esc_html( $license_email ) . '</span>' ); ?></dd>
	<?php if ( ! mailster()->is_email_verified() ) : ?>
		<dd style="color:#D54E21"><?php esc_html_e( 'Please verify your Mailster account!', 'mailster' ); ?></dd>
	<?php endif; ?>
	<dd>
		<?php if ( current_user_can( 'mailster_manage_licenses' ) ) : ?>
		<a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster-account' ); ?>"><?php esc_html_e( 'Account', 'mailster' ); ?></a> |
		<?php endif; ?>
		<a href="<?php echo mailster_freemius()->checkout_url(); ?>" ><?php esc_html_e( 'Buy new License', 'mailster' ); ?></a>
	</dd>
</dl>
<dl class="mailster-icon mailster-not-valid not-valid">
	<dt><?php esc_html_e( 'Not Verified', 'mailster' ); ?></dt>
	<dd><?php esc_html_e( 'Your license has not been verified', 'mailster' ); ?></dd>
	<dd>
		<a href="<?php echo mailster_freemius()->checkout_url(); ?>"><?php esc_html_e( 'Buy new License', 'mailster' ); ?></a>
	</dd>
</dl>
<dl class="mailster-icon mailster-update update-not-available">
	<dt><?php printf( esc_html__( 'Installed Version %s', 'mailster' ), MAILSTER_VERSION ); ?></dt>
	<dd><?php esc_html_e( 'You have the latest version', 'mailster' ); ?></dd>
	<?php if ( ! mailster( 'update' )->is_auto_update() ) : ?>
	<dd><a href="<?php echo mailster( 'update' )->get_auto_update_url(); ?>" class="enable-auto-update"><?php esc_html_e( 'Enable Auto Update', 'mailster' ); ?></a></dd>
	<?php endif; ?>
	<!-- <dd><span class="lighter"><a href="" class="check-for-update"><?php esc_html_e( 'Check for Updates', 'mailster' ); ?></a></span>
	</dd> -->
</dl>
<?php if ( mailster()->plugin_info( 'update' ) ) : ?>
<dl class="mailster-icon mailster-update update-available">
	<dt><?php printf( esc_html__( 'Installed Version %s', 'mailster' ), MAILSTER_VERSION ); ?></dt>
	<dd><?php esc_html_e( 'A new Version is available', 'mailster' ); ?></dd>
	<dd><a href="<?php echo mailster_url( 'https://kb.mailster.co/6401de4552af714471a19027' ); ?>" data-article="6401de4552af714471a19027"><?php esc_html_e( 'view changelog', 'mailster' ); ?></a>
		<?php if ( mailster_freemius()->has_active_valid_license() ) : ?>
			<?php esc_html_e( 'or', 'mailster' ); ?> <a href="update.php?action=upgrade-plugin&plugin=<?php echo urlencode( MAILSTER_SLUG ); ?>&_wpnonce=<?php echo wp_create_nonce( 'upgrade-plugin_' . MAILSTER_SLUG ); ?>" class="update-button"><?php printf( esc_html__( 'update to %s now', 'mailster' ), '<span class="update-version">' . esc_html( mailster()->plugin_info( 'new_version' ) ) . '</span>' ); ?></a>
		<?php else : ?>
			<?php esc_html_e( 'or', 'mailster' ); ?> <strong><a href="<?php echo mailster_freemius()->checkout_url(); ?>"><?php esc_html_e( 'Renew your license to update', 'mailster' ); ?></a> <?php echo mailster()->beacon( '64074c66512c5e08fd71ac91' ); ?></strong>
		<?php endif; ?>
	</dd>
</dl>
<?php endif; ?>
<dl class="mailster-icon mailster-support">
	<dt><?php esc_html_e( 'Support', 'mailster' ); ?></dt>
	<dd>
		<a href="<?php echo mailster_url( 'https://docs.mailster.co' ); ?>" class="external"><?php esc_html_e( 'Documentation', 'mailster' ); ?></a> |
		<a href="<?php echo mailster_url( 'https://kb.mailster.co' ); ?>" class="external"><?php esc_html_e( 'Knowledge Base', 'mailster' ); ?></a> |
		<a href="<?php echo mailster_freemius()->contact_url(); ?>" class="mailster-support"><?php esc_html_e( 'Get Help', 'mailster' ); ?></a> |
		<a href="<?php echo admin_url( 'admin.php?page=mailster_tests' ); ?>"><?php esc_html_e( 'Self Test', 'mailster' ); ?></a>
	</dd>
</dl>
<?php if ( current_user_can( 'install_languages' ) && $set = mailster( 'translations' )->get_translation_set() ) : ?>
<dl class="mailster-icon mailster-translate">
	<dt><?php esc_html_e( 'Translation', 'mailster' ); ?> </dt>
	<?php if ( mailster( 'translations' )->translation_installed() ) : ?>
		<?php $name = ( esc_html_x( 'Thanks for using Mailster in %s!', 'Your language', 'mailster' ) == 'Thanks for using Mailster in %s!' ) ? $set->name : $set->native_name; ?>
	<dd><?php printf( esc_html_x( 'Thanks for using Mailster in %s!', 'Your language', 'mailster' ), '<strong>' . esc_html( $name ) . '</strong>' ); ?></dd>
		<?php if ( mailster( 'translations' )->translation_available() ) : ?>
	<dd><a href="" class="load-language"><strong><?php esc_html_e( 'Update Translation', 'mailster' ); ?></strong></a></dd>
		<?php endif; ?>
	<?php elseif ( mailster( 'translations' )->translation_available() ) : ?>
	<dd><?php printf( esc_html__( 'Mailster is available in %s!', 'mailster' ), '<strong>' . esc_html( $set->name ) . '</strong>' ); ?></dd>
	<dd><a href="" class="load-language"><strong><?php esc_html_e( 'Download Translation', 'mailster' ); ?></strong></a></dd>
	<?php endif; ?>
	<dd><span class="lighter"><?php printf( esc_html__( 'Currently %s translated.', 'mailster' ), '<strong>' . $set->percent_translated . '%</strong>' ); ?></span></dd>
</dl>
<?php endif; ?>
