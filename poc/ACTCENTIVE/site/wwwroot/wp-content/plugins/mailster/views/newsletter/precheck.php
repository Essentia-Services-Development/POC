<?php

$now = time();

$sent         = $this->get_sent( $post->ID );
$current_user = wp_get_current_user();

$terms_agreed = get_user_meta( $current_user->ID, '_mailster_precheck_agreed', true );

if ( $subscriber = mailster( 'subscribers' )->get_by_mail( $current_user->user_email, true ) ) {

	$fullname      = $subscriber->fullname;
	$email         = $subscriber->email;
	$subscriber_id = $subscriber->ID;

} else {

	$firstname     = $current_user->user_firstname ? $current_user->user_firstname : $current_user->display_name;
	$fullname      = mailster_option( 'name_order' ) ? trim( $current_user->user_lastname . ' ' . $firstname ) : trim( $firstname . ' ' . $current_user->user_lastname );
	$email         = $current_user->user_email;
	$subscriber_id = 0;

}

$to = $fullname ? $fullname . ' <' . $email . '>' : $email;

?>
<div id="mailster_precheck_wrap" style="display:none;">
	<div class="mailster-precheck<?php echo ( $terms_agreed ) ? ' precheck-terms-agreed' : ''; ?>">
		<div class="precheck-bar">			
			<ul class="precheck-emailheader">
				<li><label><?php esc_html_e( 'From', 'mailster' ); ?>:</label><span class="precheck-from"></span></li>
				<li><label><?php esc_html_e( 'Subject', 'mailster' ); ?>:</label><span class="precheck-subject"></span></li>
				<li><label><?php esc_html_e( 'To', 'mailster' ); ?>:</label><span class="precheck-to"></span><a class="change-receiver mailster-icon" title="<?php esc_attr_e( 'Change the user in the preview.', 'mailster' ); ?>"></a><span class="precheck-to-input" title="<?php esc_attr_e( 'Search for subscribers...', 'mailster' ); ?>"><input type="hidden" value="<?php echo (int) $subscriber_id; ?>" id="subscriber_id"><input type="text" class="precheck-subscriber" value="" placeholder="<?php echo esc_attr( $to ); ?>"></span></li>
			</ul>
			<div class="precheck-images button-group">
				<a class="button precheck-toggle-images mailster-icon active" title="<?php esc_attr_e( 'Toggle Images', 'mailster' ); ?>"></a>
				<a class="button precheck-toggle-structure mailster-icon" title="<?php esc_attr_e( 'Toggle Structure', 'mailster' ); ?>"></a>
			</div>
			<div class="precheck-resize button-group">
				<a class="button precheck-switch mailster-icon precheck-switch-desktop active" data-dimensions='{"w":"100%","h":"100%"}'></a>
				<a class="button precheck-switch mailster-icon precheck-switch-mobile" data-dimensions='{"w":319,"h":639}'></a>
				<a class="button precheck-switch mailster-icon precheck-switch-landscape" data-dimensions='{"w":639,"h":319}'></a>


			</div>
			<ul class="precheck-run">
				<li class="alignright"><span class="spinner" id="precheck-ajax-loading"></span><button class="button button-primary precheck-run-btn"><?php esc_html_e( 'Precheck Campaign', 'mailster' ); ?></button></li>
			</ul>
		</div>
		<div class="device-wrap">
			<div class="device desktop">
				<div class="desktop-body">
					<div class="preview-body">
						<iframe class="mailster-preview-iframe desktop" src="" width="100%" scrolling="auto" frameborder="0" data-no-lazy=""></iframe>
					</div>
				</div>
			</div>
			<div class="device-notice"><?php esc_html_e( 'Your email may look different on mobile devices.', 'mailster' ); ?></div>
		</div>
		<div class="score-wrap">
			<div class="score-message"></div>
			<div class="precheck-tos-box">

				<?php if ( mailster()->is_verified() ) : ?>
					<?php echo mailster()->beacon( array( '63fa7367e6d6615225473a9b' ) ); ?>
					<h3><?php esc_html_e( 'Precheck Terms of Service.', 'mailster' ); ?></h3>
					<?php $terms = file_get_contents( MAILSTER_DIR . 'licensing/Precheck.txt' ); ?>
					<?php echo wpautop( $terms, false ); ?>
					<p><label><input type="checkbox" id="precheck-agree-checkbox"><?php esc_html_e( 'I\'ve read the Terms of Service and agree.', 'mailster' ); ?></label></p>
					<?php submit_button( esc_html__( 'Submit', 'mailster' ), 'primary', 'precheck-agree' ); ?>

				<?php else : ?>

					<h3><?php esc_html_e( 'Please register the plugin first!', 'mailster' ); ?></h3>
					<p><?php esc_html_e( 'To use the precheck service you have to register the Mailster plugin on the dashboard', 'mailster' ); ?></p>
					<a href="<?php echo admin_url( 'admin.php?page=mailster_dashboard' ); ?>" class="button button-primary"><?php esc_html_e( 'Go to Dashboard', 'mailster' ); ?></a>

				<?php endif; ?>

			</div>
			<div class="precheck-score">
				<?php echo mailster()->beacon( array( '63fa7367e6d6615225473a9b' ) ); ?>
				<div class="precheck-status-icon"></div>
				<h3 class="precheck-status"><?php esc_html_e( 'Ready for Precheck!', 'mailster' ); ?></h3>
			</div>
			<div class="precheck-results-wrap">
				<div class="precheck-results">
					<details id="precheck-message">
						<summary><?php esc_html_e( 'Message', 'mailster' ); ?><span class="precheck-penality"></span></summary>
						<div class="precheck-body">
							<details id="precheck-subject">
								<summary><?php esc_html_e( 'Subject', 'mailster' ); ?><span class="precheck-penality"></span></summary>
								<div class="precheck-result"></div>
							</details>
							<details id="precheck-email">
								<summary><?php esc_html_e( 'Email', 'mailster' ); ?><span class="precheck-penality"></span></summary>
								<div class="precheck-result"></div>
							</details>
						</div>
					</details>
					<details id="precheck-links">
						<summary><?php esc_html_e( 'Links', 'mailster' ); ?><span class="precheck-penality"></span></summary>
						<div class="precheck-result"></div>
					</details>
					<details id="precheck-images">
						<summary><?php esc_html_e( 'Images', 'mailster' ); ?><span class="precheck-penality"></span></summary>
						<div class="precheck-result"></div>
					</details>
					<details id="precheck-spam_report">
						<summary><?php esc_html_e( 'Spam Report', 'mailster' ); ?><span class="precheck-penality"></span></summary>
						<div class="precheck-result"></div>
					</details>
					<details id="precheck-authentication">
						<summary><?php esc_html_e( 'Authentication', 'mailster' ); ?><span class="precheck-penality"></span></summary>
						<div class="precheck-body">
							<details id="precheck-spf">
								<summary><acronym title="Sender Policy Framework">SPF</acronym><span class="precheck-penality"></span></summary>
								<div class="precheck-result"></div>
							</details>
							<details id="precheck-dkim">
								<summary><acronym title="DomainKeys Identified Mail">DKIM</acronym><span class="precheck-penality"></span></summary>
								<div class="precheck-result"></div>
							</details>
							<details id="precheck-dmarc">
								<summary><acronym title="Domain-based Message Authentication, Reporting & Conformance">DMARC</acronym><span class="precheck-penality"></span></summary>
								<div class="precheck-result"></div>
							</details>
							<details id="precheck-rdns">
								<summary><acronym title="Reverse Domain Name Server lookup">rDNS</acronym><span class="precheck-penality"></span></summary>
								<div class="precheck-result"></div>
							</details>
							<details id="precheck-mx">
								<summary><acronym title="Mail Exchanger Record">MX</acronym><span class="precheck-penality"></span></summary>
								<div class="precheck-result"></div>
							</details>
							<details id="precheck-a">
								<summary><acronym title="Address record">A</acronym><span class="precheck-penality"></span></summary>
								<div class="precheck-result"></div>
							</details>
						</div>
					</details>
					<details id="precheck-blocklist">
						<summary>Blocklist<span class="precheck-penality"></span></summary>
						<div class="precheck-result"></div>
					</details>

				</div>
			</div>
		</div>
	</div>

</div>
