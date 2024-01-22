<?php
$gecko_settings = GeckoConfigSettings::get_instance();
$top_widgets_vis = 1;

//
// Mobile App
//
if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_top-widgets') ) {
  $top_widgets_vis = 0;
}
?>

<?php
if ( is_active_sidebar( 'slider-widgets' )) : ?>
<div class="gc-widgets gc-widgets--slider">
  <?php dynamic_sidebar( 'slider-widgets' ); ?>
</div>
<?php endif; ?>

<?php

if ( GeckoAppHelper::is_app() && is_active_sidebar( 'mobi-top-widgets' ) && $gecko_settings->get_option( 'opt_app_widget_positions', 0 ) ) {
echo '<div class="gc-widgets gc-widgets--app gc-widgets--app-top">
    <div class="gc-widgets__inner">
      <div class="gc-widgets__grid">';
        dynamic_sidebar( 'mobi-top-widgets' );
echo '</div>
    </div>
</div>';
}

// end: Mobile App
?>

<?php
if ( is_active_sidebar( 'top-widgets' ) && $top_widgets_vis === 1) : ?>
<div class="gc-widgets gc-widgets--top">
    <div class="gc-widgets__inner">
      <div class="gc-widgets__grid">
        <?php dynamic_sidebar( 'top-widgets' ); ?>
      </div>
    </div>
</div>
<?php endif; ?>
