  <?php if (! is_page_template( 'page-tpl-landing.php' ) ) : ?>
    <?php get_template_part( 'template-parts/widgets/bottom' ); ?>
  <?php endif; ?>

  <?php
  // Get search visibility option from admin settings
  $gecko_settings = GeckoConfigSettings::get_instance();

  $hide_widgets = get_post_meta(get_proper_ID(), 'gecko-page-footer-mobile', true);
  $widgets_vis = 1; // Enabled visibility
  $widget_social_vis = 1; // Enabled visibility
  $hide_sidebar_left = get_post_meta(get_proper_ID(), 'gecko-page-left-sidebar-mobile', true);
  $hide_sidebar_right = get_post_meta(get_proper_ID(), 'gecko-page-right-sidebar-mobile', true);
  $hide_footer = get_post_meta(get_proper_ID(), 'gecko-page-hide-footer', true);
  $show_scroll_to_top = $gecko_settings->get_option('opt_scroll_to_top', '1');

  if (is_search() || is_archive()) {
    $hide_widgets = NULL; // WIRE UP in 3.1.1.0
    $hide_sidebar_left = NULL; // WIRE UP in 3.1.1.0
    $hide_sidebar_right = NULL; // WIRE UP in 3.1.1.0

    if($gecko_settings->get_option( 'opt_search_footer_vis', 1 ) == NULL) {
      $hide_footer = 1; // show
    } else {
      $hide_footer = 0; // hide
    }
  }

  //
  // MobiLoud
  //
  // Footer:
  if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_footer') ) {
    $hide_footer = 1;
  }
  // Footer widgets:
  if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_footer-widgets') ) {
    $widgets_vis = 0;
  }
  // Footer social widget:
  if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_footer-social') ) {
    $widget_social_vis = 0;
  }
  // Scroll to top visibility
  if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_scroll_to_top') ) {
    $show_scroll_to_top = NULL;
  }
  // end: Mobiloud

  // Get search visibility option from admin settings
  $gecko_settings = GeckoConfigSettings::get_instance();

  if($gecko_settings->get_option('opt_sidebar_left_mobile_vis', '1') == NULL) {
    $hide_sidebar_left = TRUE;
  }

  if($gecko_settings->get_option('opt_sidebar_right_mobile_vis', '1') == NULL) {
    $hide_sidebar_right = TRUE;
  }

  if ($hide_widgets == 1) :
  ?>
  <style>
  @media screen and (max-width: 980px) {
    .gc-footer__grid {
      display: none;
    }
  }
  </style>
  <?php endif; ?>

  <?php if ($hide_sidebar_left == 1) : ?>
    <style>
    @media screen and (max-width: 980px) {
      .sidebar--left {
        display: none;
      }
    }
    </style>
  <?php endif; ?>

  <?php if ($hide_sidebar_right == 1) : ?>
    <style>
    @media screen and (max-width: 980px) {
      .sidebar--right {
        display: none;
      }
    }
    </style>
  <?php endif; ?>

  <?php if($show_scroll_to_top == 1) : ?>
  <a href="#body" class="gc-scroll__to-top js-scroll-top"><i class="gcis gci-angle-up"></i></a>
  <?php endif; ?>

  <?php if (! is_page_template( 'page-tpl-landing.php' ) ) : ?>
    <?php if ($gecko_settings->get_option( 'opt_footer_vis', 1 ) === 1) : ?>
    <?php if(! $hide_footer) : ?>
    <footer class="gc-footer">
      <?php if (! is_page_template( 'page-tpl-landing.php' ) ) : ?>
        <?php if ( is_active_sidebar( 'footer-widgets') && $widgets_vis === 1 ) : ?>
        <div class="gc-footer__grid">
          <!-- Include widgets -->
          <?php dynamic_sidebar( 'footer-widgets' ); ?>
        </div>
        <?php endif; ?>
      <?php endif; ?>
      <div class="gc-footer__bottom">
        <div class="gc-footer__bottom-inner">
          <div class="gc-footer__copyrights">
            <?php
            $line_1 = $gecko_settings->get_option( 'opt_footer_text_line_1', FALSE);
            if(FALSE === $line_1) {
                $line_1 = get_bloginfo('name');
            }
            if (strlen($line_1) ) : ?>
              <?php echo $line_1; ?>
            <?php endif; ?>
            <?php
            $line_2 = $gecko_settings->get_option( 'opt_footer_text_line_2', FALSE);
            if(FALSE === $line_2) {
                $line_2 = 'All rights reserved';
            }
            if (strlen($line_2)) : ?>
            <div class="gc-footer__rights">
              <?php echo $line_2; ?>
            </div>
            <?php endif; ?>
          </div>

          <ul class="gc-footer__menu"><?php wp_nav_menu( array( 'theme_location' => 'footer-menu', 'items_wrap' => '%3$s', 'container' => false, 'fallback_cb' => false ) ); ?></ul>

          <?php if ( is_active_sidebar( 'footer-social' ) && $widget_social_vis === 1) : ?>
          <div class="gc-footer__social">
            <!-- <a href="javascript:" class="gc-footer__social-item gc-footer__social-item--facebook">
              <i class="gcib gci-facebook-f"></i>
            </a>
            <a href="javascript:" class="gc-footer__social-item gc-footer__social-item--twitter">
              <i class="gcib gci-twitter"></i>
            </a>
            <a href="javascript:" class="gc-footer__social-item gc-footer__social-item--instagram">
              <i class="gcib gci-instagram"></i>
            </a> -->
            <?php dynamic_sidebar( 'footer-social' ); ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </footer>
    <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php
  $blog_grid_columns = $gecko_settings->get_option('opt_blog_grid_col', '2');
  ?>
  <script type="text/javascript">
    var blogGridColumns = <?php echo $blog_grid_columns; ?>;
  </script>
  <?php wp_footer(); ?>
  <?php if (apply_filters('peepso_free_bundle_should_brand', FALSE)) { ?>
    <script type="text/javascript">
      if (window.peepso) {
        peepso.observer.addFilter('get_footer_container', () => jQuery('.gc-footer__copyrights'));
        setTimeout(() => peepso.observer.doAction('show_branding'), 1000);
      }
    </script>
  <?php } ?>
  </body>
</html>
