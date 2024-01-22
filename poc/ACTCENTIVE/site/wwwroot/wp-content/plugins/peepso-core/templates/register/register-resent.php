<div class="peepso">
	<div class="ps-page ps-page--register ps-page--register-resent">
		<h2><?php echo __('Resend Activation Code', 'peepso-core'); ?></h2>
		<p><?php echo __('Your activation code has been sent to your email.', 'peepso-core'); ?></p>
		<p>
			<?php
				$link = PeepSo::get_page('register') . '?community_activate';
				echo sprintf(__('Follow the link in the email you received, or you can enter the activation code on the <a href="%1$s"><u>activation</u></a> page.</a>', 'peepso-core'), $link);
			?>
		</p>
		<div class="ps-page__footer">
			<a class="ps-btn" href="<?php echo PeepSo::get_page('activity'); ?>"><?php echo __('Back to Community', 'peepso-core'); ?></a>
		</div>
	</div>
</div>
