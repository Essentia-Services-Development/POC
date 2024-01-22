<?php
	
defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );

if (isset($user_login) && !empty($user_login)){
	$user = get_user_by('login', $user_login);
	$user_email = $user->user_email;
} else {
	// dummy data
	$users = get_users(array(
	    'number' => 1,
	));
	$user = $users[0];
	$user_email = $user->user_email;
}


 ?>

<p>
	<?php 

	if (b2bking()->has_b2b_application_pending($user->ID)){
		esc_html_e( 'An existing B2C customer has submitted the B2B registration / account upgrade form.', 'b2bking');	
	} else {
		esc_html_e( 'You have a new customer registration that requires approval.', 'b2bking');	
	}

	?>
	<br /><br />
 	<?php esc_html_e( 'Username: ','b2bking'); echo esc_html($user_login); ?>
 	<br />
 	<?php esc_html_e( 'Email: ','b2bking'); echo esc_html($user_email); ?>
 	<br /><br />
 	<a href="<?php echo esc_attr(admin_url('/user-edit.php?user_id='.$user->ID.'#b2bking_registration_data_container')); ?> "><?php esc_html_e( 'Click to Review User', 'b2bking' ); ?> </a>
</p>
<?php

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
