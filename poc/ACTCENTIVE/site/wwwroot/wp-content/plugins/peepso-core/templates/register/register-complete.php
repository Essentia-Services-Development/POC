<div class="peepso">
	<div class="ps-page ps-page--register ps-page--register-complete">
		<h2><?php echo __('User Registered', 'peepso-core'); ?></h2>
		<p>
			<?php
				if (PeepSo::get_option('registration_disable_email_verification', '0')) {
					if (PeepSo::get_option('site_registration_enableverification', '0'))
						echo __('Administrator will be notified that your account has been created and is awaiting approval. Until the site administrator approves your account, you will not be able to login. Once your account has been approved, you will receive a notification email.', 'peepso-core');
					else
						echo __('Your account has been created.', 'peepso-core');
				} else {
					if (PeepSo::get_option('site_registration_enableverification', '0'))
						echo __('Please check your email account and confirm your registration. Once that\'s done, Administrator will be notified that your account has been created and is awaiting approval. Until the site administrator approves your account, you will not be able to login. Once your account has been approved, you will receive a notification email.', 'peepso-core');
					else
						echo __('Your account has been created. An activation link has been sent to the email address you provided, click on the link to logon to your account.', 'peepso-core');
				}
			?>
		</p>
		<div class="ps-page__footer">
			<a href="<?php  echo PeepSo::get_page('activity'); ?>" class="ps-btn"><?php echo __('Back to Community', 'peepso-core'); ?></a>
		</div>
	</div>
</div>
