<?php
$top_widgets_vis = 1;

//
// MobiLoud
//
if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_above-content-widgets') ) {
  $top_widgets_vis = 0;
}
// end: Mobiloud

if ( is_active_sidebar( 'above-content-widgets' ) && $top_widgets_vis === 1) : ?>
<div class="gc-widgets gc-widgets--above-content">
  <?php dynamic_sidebar( 'above-content-widgets' ); ?>
</div>
<?php endif; ?>
