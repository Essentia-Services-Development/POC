<div class="peepso">
  <div class="ps-page ps-page--register ps-page--register-resend">
    <h2><?php echo __('Resend Activation Code', 'peepso-core'); ?></h2>
    <p><?php echo __('Please enter your registered email address here so that we can resend you the activation link.', 'peepso-core'); ?></p>
    <?php
		if (isset($error)) {
			PeepSoGeneral::get_instance()->show_error($error);
		}
		?>

    <div class="psf-register psf-register--resend">
      <form class="ps-form ps-form--register ps-form--register-resend" name="resend-activation" action="<?php PeepSo::get_page('register'); ?>?resend" method="post">
				<input type="hidden" name="task" value="-resend-activation" />
				<input type="hidden" name="-form-id" value="<?php echo wp_create_nonce('resent-activation-form'); ?>" />
				<div class="ps-form__grid">
					<div class="ps-form__row">
						<label for="email" class="ps-form__label"><?php echo __('Email Address', 'peepso-core'); ?>
							<span class="ps-form__required">&nbsp;*<span></span></span>
						</label>
						<div class="ps-form__field">
							<input class="ps-input" type="email" name="email" id="email" placeholder="<?php echo __('Email address', 'peepso-core'); ?>" />
						</div>
					</div>
					<div class="ps-form__row ps-form__row--submit">
						<a class="ps-btn" href="<?php echo PeepSo::get_page('activity'); ?>"><?php echo __('Back to Community', 'peepso-core'); ?></a>
						<?php $recaptchaEnabled = PeepSo::get_option('site_registration_recaptcha_enable', 0); ?>
						<input type="submit" name="submit-resend"
							class="ps-btn ps-btn--action <?php echo $recaptchaEnabled ? 'ps-js-recaptcha' : ''; ?>"
							value="<?php echo __('Submit', 'peepso-core'); ?>"
							<?php echo $recaptchaEnabled ? 'disabled="disabled"' : '' ?> />
					</div>
				</div>
			</form>
    </div>
  </div>
</div>
