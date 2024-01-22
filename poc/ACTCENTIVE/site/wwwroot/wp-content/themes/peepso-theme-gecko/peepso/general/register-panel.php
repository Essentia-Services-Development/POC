<?php if ( ! is_user_logged_in()) {
$activated = FALSE;

if(isset($_COOKIE['peepso_last_visited_page']) && stristr($_COOKIE['peepso_last_visited_page'], 'community_activate')) {
    $activated = TRUE;
}

// since 1.11.3 - fallback for peepso_activate renamed into community_activate #3180
if(isset($_COOKIE['peepso_last_visited_page']) && stristr($_COOKIE['peepso_last_visited_page'], 'peepso_activate')) {
    $activated = TRUE;
}

$is_mobiloud =  GeckoAppHelper::is_app('mobiloud');

$no_cover = $no_cover ?? FALSE;
?>

<!-- PeepSo Login Panel -->
<div class="ps-landing">
  <?php
  $default = PeepSo::get_option('landing_page_image', PeepSo::get_asset('images/landing/register-bg.jpg'));
  $disable_registration = intval(PeepSo::get_option('site_registration_disabled', 0));
  $landing_page = !empty($default) ? $default : PeepSo::get_asset('images/landing/register-bg.jpg');
  ?>

  <div class="ps-lading__inner">
    <?php if(!$no_cover) { ?>
    <div class="ps-landing__cover" style="background-image:url('<?php echo $landing_page;?>')">
      <div class="ps-landing__cover-inner">
        <div class="ps-landing__content">
          <div class="ps-landing__title">
            <?php if($activated) : ?>
              <?php echo __('Thank you', 'peepso-theme-gecko');?>
            <?php else : ?>
              <?php echo PeepSo::get_option('site_registration_header', __('Get Connected!', 'peepso-theme-gecko')); ?>
            <?php endif; ?>
          </div>
          <div class="ps-landing__text">
            <?php if($activated) : ?>
              <?php echo __('Your e-mail address was confirmed. You can now log in.','peepso-theme-gecko');?>
            <?php else : ?>
              <?php echo PeepSo::get_option('site_registration_callout', __('Come and join our community. Expand your network and get to know new people!', 'peepso-theme-gecko')); ?>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!$is_mobiloud) { ?>
          <?php if(!$activated && 0 === $disable_registration) { ?>
          <div class="ps-landing__actions">
            <a class="ps-btn ps-btn--sm ps-btn--cp ps-btn--join" href="<?php echo PeepSo::get_page('register'); ?>">
              <?php echo PeepSo::get_option('site_registration_buttontext', __('Join us now, it\'s free!', 'peepso-theme-gecko')); ?>
            </a>
          </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
    <?php } ?>

    <?php PeepSoTemplate::exec_template('general', 'login');?>
  </div>
</div>
<!-- end: PeepSo Login Panel -->

<?php
} // is_user_logged_in() ?>
