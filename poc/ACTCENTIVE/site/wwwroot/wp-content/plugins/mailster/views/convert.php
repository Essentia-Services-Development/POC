<div class="wrap" id="mailster-convert">
<?php
$user_email = mailster()->get_email();
$license    = mailster()->get_license();
$dateformat = mailster( 'helper' )->dateformat();

if ( empty( $user_email ) ) {
	$user       = wp_get_current_user();
	$user_email = $user->user_email;
}

?>

	<div class="convert_form_wrap step-1 loading">
		<div class="convert-form-info">Start converting your license</div>
		<form class="convert_form" action="" method="POST">
			<h1>Mailster License Migration <?php echo mailster()->beacon( '63fe029de6d6615225474599' ); ?></h1>
			<h2>You're about to convert your Mailster license to our new license provider.</h2>
			<p class="howto">We have partnered with Freemius to provide updates for the Mailster Newsletter Plugin. By submitting the form, you are consenting to the transfer of your data to Freemius for the purpose of processing future updates.</p>
			<p class="howto">Kindly provide the email address you would like to use for your account. If you already have a Freemius account, please use the same email address.</p>
			<p class="error-msg">&nbsp;</p>
			<p>
				<input type="text" class="widefat license align-center" name="license" value="<?php echo esc_attr( $license ); ?>" placeholder="XXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX">
				<input type="email" class="widefat email align-center" name="email" value="<?php echo esc_attr( $user_email ); ?>" placeholder="<?php echo esc_attr( $user_email ); ?>">
			</p>
			<input type="submit" class="button button-hero button-primary" value="Convert License now">
			<p class="howto">
				<a href="<?php echo mailster_url( 'https://kb.mailster.co/63fe029de6d6615225474599' ); ?>" data-article="63fe029de6d6615225474599">Why is this step required?</a>
			</p>
		</form>
		<form class="registration_complete">
			<div class="registration_complete_wrap">
				<div class="registration_complete_check"></div>
				<div class="registration_complete_text"><?php esc_html_e( 'All Set!', 'mailster' ); ?></div>
			</div>
			<h1>Mailster License Migration completed!</h1>
			<p class="howto">Your license has been successfully converted.</p>
			<p class="howto">Congratulation you know have a <code class="convert-plan"></code> Plan!</p>
			<ul class="result"></ul>
			<p><a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster-account' ); ?>" class="button button-primary">Your Freemius Account</a> <a href="<?php echo admin_url( 'admin.php?page=mailster_dashboard' ); ?>" class="button button-secondary">Mailster Dashboard</a></p>

		</form>
	</div>

</div>
