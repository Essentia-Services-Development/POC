<?php
/**
 * BuddyPress - Members Activate
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 * @version 3.0.0
 */

?>

<div id="buddypress">
	<?php

	/**
	 * Fires before the display of the member activation page.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_before_activation_page' ); ?>

	<div class="page" id="activate-page">
		<div id="template-notices" role="alert" aria-atomic="true">
			<?php

			/** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
			do_action( 'template_notices' ); ?>
		</div>

		<?php

		/**
		 * Fires before the display of the member activation page content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_before_activate_content' ); ?>

		<?php if ( bp_account_was_activated() ) : ?>

			<?php if ( isset( $_GET['e'] ) ) : ?>
				<p><?php esc_html_e( 'Your account was activated successfully! Your account details have been sent to you in a separate email.', 'rehub-theme' ); ?></p>
			<?php else : ?>
				<?php $link_class = (!is_user_logged_in() && rehub_option('userlogin_enable') == '1') ? 'act-rehub-login-popup' : ''; ?>
				<p><?php esc_html_e( 'Your account was activated successfully! You can now', 'rehub-theme' ); ?> <a href="<?php echo wp_login_url( bp_get_root_domain() );?>" class="<?php echo esc_attr($link_class);?>"><?php esc_html_e( 'log in', 'rehub-theme' ); ?></a> <?php esc_html_e( 'with the username and password you provided when you signed up.', 'rehub-theme' ); ?></p>
			<?php endif; ?>

		<?php else : ?>

			<p><?php esc_html_e( 'Please provide a valid activation key.', 'rehub-theme' ); ?></p>
			<form action="" method="post" class="standard-form" id="activation-form">
				<label for="key"><?php esc_html_e( 'Activation Key:', 'rehub-theme' ); ?></label>
				<input type="text" name="key" id="key" value="<?php echo esc_attr( bp_get_current_activation_key() ); ?>" />
				<p class="submit">
					<input type="submit" name="submit" value="<?php esc_attr_e( 'Activate', 'rehub-theme' ); ?>" />
				</p>
			</form>

		<?php endif; ?>

		<?php

		/**
		 * Fires after the display of the member activation page content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_after_activate_content' ); ?>
	</div><!-- .page -->

	<?php

	/**
	 * Fires after the display of the member activation page.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_after_activation_page' ); ?>
</div><!-- #buddypress -->