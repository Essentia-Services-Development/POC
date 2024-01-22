<?php

$login_with_email = 2 === (int) PeepSo::get_option('login_with_email', 0);
$disable_registration = intval(PeepSo::get_option('site_registration_disabled', 0));

?>
<div class="ps-landing__form psf-login psf-login--inline">
  <form class="ps-form ps-form--login ps-js-form-login" action="" onsubmit="return false;" method="post" name="login" id="ps-form-login-main">
    <div class="ps-form__row">
      <!-- Login -->
      <div class="ps-form__field ps-form__field--icon ps-js-username-field">
        <div class="ps-input__wrapper--icon">
            <input class="ps-input ps-input--sm ps-input--icon" type="text" name="username" placeholder="<?php echo PeepSoGeneral::get_login_input_label(); ?>" mouseev="true"
              autocomplete="off" keyev="true" clickev="true" />
            <?php if ($login_with_email) { ?>
            <i class="gcis gci-envelope"></i>
            <?php } else { ?>
            <i class="gcis gci-user"></i>
            <?php } ?>
        </div>
        <?php if ($login_with_email) { ?>
        <div class="ps-form__field-notice ps-form__field-notice--important ps-js-email-notice" style="display:none"><?php echo __('Please use a valid email address.', 'peepso-core'); ?></div>
        <?php } ?>
      </div>

      <!-- Password -->
      <div class="ps-form__field ps-form__field--icon ps-js-password-field">
        <input class="ps-input ps-input--sm ps-input--icon <?php echo PeepSo::get_option_new('password_preview_enable') ? 'ps-js-password-preview' : '' ?>"
            type="password" name="password" placeholder="<?php echo __('Password', 'peepso-core'); ?>" mouseev="true"
            autocomplete="off" keyev="true" clickev="true" />
        <i class="gcis gci-key"></i>
      </div>

      <?php include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); ?>
      <?php if( PeepSo::two_factor_plugin_enabled() /* is_plugin_active('two-factor-authentication/two-factor-login.php') */ ) { ?>
      <!-- Two Factor authentication -->
      <div class="ps-form__field ps-form__field--icon ps-js-tfa-field" style="display:none">
        <input class="ps-input ps-input--sm ps-input--icon" type="password" name="two_factor_code" placeholder="<?php echo __('TFA code', 'peepso-core'); ?>" mouseev="true"
           autocomplete="off" keyev="true" clickev="true" data-ps-extra="1" />
        <i class="gcis gci-key"></i>
      </div>
      <?php } ?>

      <!-- Submit form -->
      <div class="ps-form__field ps-form__field--submit ps-js-password-field">
        <?php $recaptchaEnabled = PeepSo::get_option('recaptcha_login_enable', 0); ?>
        <button type="submit"
            class="ps-btn ps-btn--sm ps-btn--action ps-btn--login ps-btn--loading <?php echo $recaptchaEnabled ? 'ps-js-recaptcha' : ''; ?>"
            <?php echo $recaptchaEnabled ? 'disabled="disabled"' : '' ?>>
          <span><?php echo __('Login', 'peepso-core'); ?></span>
          <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
        </button>
      </div>
    </div>

    <!-- Remember password -->
    <div class="ps-checkbox ps-checkbox--login">
      <input class="ps-checkbox__input" type="checkbox" alt="<?php echo __('Remember Me', 'peepso-core'); ?>" value="yes" name="remember" id="ps-form-login-main-remember" <?php echo PeepSo::get_option('site_frontpage_rememberme_default', 0) ? ' checked':'';?>>
      <label class="ps-checkbox__label" for="ps-form-login-main-remember"><?php echo __('Remember Me', 'peepso-core'); ?></label>
    </div>

    <div class="psf-login__links">
      <?php
      $disable_registration = intval(PeepSo::get_option('site_registration_disabled', 0));

      // PeepSo/peepso#2906 hide "resend activation" until really necessary
      $hide_resend_activation = TRUE;
      ?>

      <?php if(0 === $disable_registration) { ?>
      <a class="psf-login__link psf-login__link--register" href="<?php echo PeepSo::get_page('register'); ?>"><?php echo __('Register', 'peepso-core'); ?></a>
      <?php } ?>

      <a class="psf-login__link psf-login__link--recover" href="<?php echo PeepSo::get_page('recover'); ?>"><?php echo __('Forgot Password', 'peepso-core'); ?></a>

      <?php if(0 === $disable_registration) { ?>
      <a class="psf-login__link psf-login__link--activation ps-js-register-activation" href="<?php echo PeepSo::get_page('register'); ?>?resend"><?php echo __('Resend activation code', 'peepso-core'); ?></a>
      <?php } ?>
    </div>

    <div class="ps-alert errlogin calert clear alert-error" style="display:none"></div>
    <input type="hidden" name="option" value="ps_users" />
    <input type="hidden" name="task" value="-user-login" />
    <input type="hidden" name="redirect_to" value="<?php echo PeepSo::get_page('redirectlogin'); ?>" />
    <?php
    // Remove ID attribute from nonce field.
    $nonce = wp_nonce_field('ajax-login-nonce', 'security', true, false);
    $nonce = preg_replace( '/\sid="[^"]+"/', '', $nonce );
    echo $nonce;
    ?>

    <?php do_action('peepso_action_render_login_form_after'); ?>
  </form>
  <?php do_action('peepso_after_login_form'); ?>
</div>

<script>
    (function() {
        function initLoginForm( $ ) {
            peepso.login.initForm( $('.ps-js-form-login') );

            $(function() {

                var $nav = $('.wp-social-login-widget');
                var $wrap = $('.ps-js--wsl');
                var $btn = $('.ps-js--wsl .ps-btn');
                var $vlinks = $('.ps-js--wsl .wp-social-login-provider-list');
                var $hlinks = $('.ps-js--wsl .hidden-links');
                var $hdrop = $('.ps-js--wsl .ps-widget--wsl-dropdown');

                var numOfItems = 0;
                var totalSpace = 0;
                var breakWidths = [];

                // Get initial state
                $vlinks.children().outerWidth(function(i, w) {
                    totalSpace += w;
                    numOfItems += 1;
                    breakWidths.push(totalSpace);
                });

                var availableSpace, numOfVisibleItems, requiredSpace;

                function check() {
                    // Get instant state
                    availableSpace = $vlinks.width() - 40;
                    numOfVisibleItems = $vlinks.children().length;
                    requiredSpace = breakWidths[numOfVisibleItems - 1];

                    // There is not enought space
                    if (requiredSpace > availableSpace) {
                        $vlinks.children().last().prependTo($hlinks);
                        numOfVisibleItems -= 1;
                        check();
                        // There is more than enough space
                    } else if (availableSpace > breakWidths[numOfVisibleItems]) {
                        $hlinks.children().first().appendTo($vlinks);
                        numOfVisibleItems += 1;
                    }

                    // Update the button accordingly
                    $btn.attr("count", numOfItems - numOfVisibleItems);
                    if (numOfVisibleItems === numOfItems) {
                        $btn.addClass('hidden');
                        $wrap.removeClass('has-more');
                    } else $btn.removeClass('hidden'), $wrap.addClass('has-more');
                }

                // Window listeners
                $(window).resize(function() {
                    check();
                });

                $btn.on('click', function() {
                    $hlinks.toggleClass('hidden');
                    $hdrop.toggleClass('hidden');
                });

                check();

            });
        }

        // naively check if jQuery exist to prevent error
        var timer = setInterval(function() {
            if ( window.jQuery && window.peepso ) {
                clearInterval( timer );
                initLoginForm( window.jQuery );
            }
        }, 1000 );

    })();
</script>
