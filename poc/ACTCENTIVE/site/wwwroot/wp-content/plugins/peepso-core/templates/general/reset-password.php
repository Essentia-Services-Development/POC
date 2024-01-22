<div class="peepso">
  <div class="ps-page ps-page--register ps-page--register-reset">
    <?php
    if (isset($error) && !empty($error)) {
        PeepSoGeneral::get_instance()->show_error($error);
    }
		?>

    <?php if(isset($error) && !in_array($error->get_error_code(), array('bad_form', 'expired_key', 'invalid_key'))) { ?>
    <h2><?php echo __('Pick a new password', 'peepso-core'); ?></h2>

    <div class="psf-register psf-register--reset">
      <form class="ps-form ps-form--register ps-form--register-resend" id="recoverpasswordform" name="recoverpasswordform" action="<?php PeepSo::get_page('recover'); ?>?submit" method="post">
        <input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $attributes['login'] ); ?>" autocomplete="off" />
        <input type="hidden" name="rp_key" value="<?php echo esc_attr( $attributes['key'] ); ?>" />
        <input type="hidden" name="task" value="-reset-password" />
        <input type="hidden" name="-form-id" value="<?php echo wp_create_nonce('peepso-reset-password-form'); ?>" />

				<div class="ps-form__grid">
					<div class="ps-form__row">
						<label for="pass1" class="ps-form__label"><?php echo __('New Password:', 'peepso-core'); ?>
							<span class="ps-form__required">&nbsp;*<span></span></span>
						</label>
						<div class="ps-form__field">
              <input class="ps-input" type="password" name="pass1" placeholder="<?php echo __('New Password', 'peepso-core'); ?>" required />
              <div class="ps-form__field-desc">
                <?php echo __('Enter your desired password', 'peepso-core'); ?>
              </div>
              <div class="ps-form__error" style="display:none"></div>
						</div>
					</div>
          <div class="ps-form__row">
						<label for="pass2" class="ps-form__label"><?php echo __('Repeat new password:', 'peepso-core'); ?>
							<span class="ps-form__required">&nbsp;*<span></span></span>
						</label>
						<div class="ps-form__field">
              <input class="ps-input" type="password" name="pass2" placeholder="<?php echo __('Repeat new password', 'peepso-core'); ?>" required />
              <div class="ps-form__field-desc">
                <?php echo __('Please re-enter your password', 'peepso-core'); ?>
              </div>
              <div class="ps-form__error" style="display:none"></div>
						</div>
					</div>
					<div class="ps-form__row ps-form__row--submit">
              <a class="ps-btn" href="<?php echo PeepSo::get_page('activity'); ?>"><?php echo __('Back to Community', 'peepso-core'); ?></a>
              <?php $recaptchaEnabled = PeepSo::get_option('site_registration_recaptcha_enable', 0); ?>
              <button type="submit" name="submit-recover"
                class="ps-btn ps-btn--action <?php echo $recaptchaEnabled ? 'ps-js-recaptcha' : ''; ?>"
                <?php echo $recaptchaEnabled ? 'disabled="disabled"' : '' ?>>
                <?php echo __('Submit', 'peepso-core'); ?>
                <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt=""
                    style="display:none" />
              </button>
					</div>
          <div class="ps-alert">
            <?php echo sprintf(__('The password should be at least %d characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ %% ^ &amp; ).','peepso-core'), PeepSo::get_option('minimum_password_length', 10)); ?>
          </div>
				</div>
			</form>
    </div>
  <?php } ?>
  </div>
</div>
