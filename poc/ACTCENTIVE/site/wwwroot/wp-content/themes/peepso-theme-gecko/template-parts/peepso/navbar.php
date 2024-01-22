<?php
$gecko_settings = GeckoConfigSettings::get_instance();

$navbar_class = "ps-navbar--gecko";
$navbar_sticky = "";

if(1 == $gecko_settings->get_option('opt_ps_navbar_sticky', 0 ) ) {
  $navbar_sticky = "gc-navbar--sticky";
}

if (class_exists('PeepSo')) {

if(0==PeepSo::get_option('disable_navbar', 0)) {
    PeepSoTemplate::exec_template('general', 'js-unavailable');
    $PeepSoGeneral = PeepSoGeneral::get_instance();
    ?>

    <?php if (is_user_logged_in()) { ?>
        <!-- PeepSo Navbar -->
        <div class="ps-navbar <?php echo $navbar_class; ?> <?php echo $navbar_sticky; ?> js-toolbar">
          <div class="ps-navbar__inner">
            <div class="ps-navbar__menu"><?php echo $PeepSoGeneral->render_navigation('primary'); ?></div>

            <div class="ps-navbar__menu ps-navbar__menu--mobile"><?php echo $PeepSoGeneral->render_navigation('mobile-secondary'); ?></div>

            <div class="ps-navbar__notifications"><?php echo $PeepSoGeneral->render_navigation('secondary'); ?></div>

            <div class="ps-navbar__toggle">
              <span class="ps-navbar__menu-item">
                  <a href="#" class="ps-navbar__menu-link ps-js-navbar-toggle" onclick="return false;">
                      <i class="gcis gci-bars"></i>
                  </a>
              </span>
            </div>
          </div>

          <div id="ps-mobile-navbar" class="ps-navbar__submenu"><?php echo $PeepSoGeneral->render_navigation('mobile-primary'); ?></div>
        </div>
        <!-- end: PeepSo Navbar -->
    <?php }
}

// #5350 - Commented out as it duplicates UserLimits message
// since: 3.4.0.5
// do_action('peepso_action_render_navbar_after');

}
?>
