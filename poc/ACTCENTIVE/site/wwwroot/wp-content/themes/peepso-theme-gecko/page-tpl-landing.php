<?php

/*
  Template Name: Landing
*/

add_filter( 'gecko_html_class', function( $classes ) {
    return array_merge( $classes, array( 'is-landing' ) );
} );

get_header();

$landing_footer_full_width = "";

if(1 == $gecko_settings->get_option('opt_landing_footer_full_width', 0 ) ) {
  $landing_footer_full_width = "landing--footer-full-width";
}

$template_dir = get_stylesheet_directory_uri();
$full_width_layout = get_post_meta($post->ID, 'gecko-page-full-width', true);
$current_user = wp_get_current_user();

$landing_btn_url = get_home_url();
$landing_btn_label = __( 'Go to Homepage', 'peepso-theme-gecko' );

$hide_footer = get_post_meta(get_proper_ID(), 'gecko-page-hide-footer', true);
$widgets_vis = 1;
$widget_social_vis = 1;

if (get_post_meta($post->ID, 'gecko-landing-btn-url', true)) {
  $landing_btn_url = get_post_meta($post->ID, 'gecko-landing-btn-url', true);
}

if (get_post_meta($post->ID, 'gecko-landing-btn-label', true)) {
  $landing_btn_label = get_post_meta($post->ID, 'gecko-landing-btn-label', true);
}

#5392 Automatically show PeepSo registration form if necessary.
$landing_peepso_register = false;
if ( class_exists('PeepSo') ) {
  $page_peepso_register = PeepSo::get_page('register');
  $page_current = get_permalink();
  $landing_peepso_register = 0 === strpos($page_current, $page_peepso_register);
}

?>

<div class="landing<?php if ($full_width_layout == 1) echo ' landing--full'; ?><?php if ($hide_footer == 1) echo ' no-footer'; ?><?php if ($landing_peepso_register) echo ' landing--peepso-register'; ?>  <?php echo $landing_footer_full_width; ?>">
  <div class="landing__bg">
    <div class="landing__row landing__row--bg" style="<?php if ( has_post_thumbnail() ) { echo 'background-image: url(' . get_the_post_thumbnail_url() . ');'; } ?>"></div>
    <div class="landing__row landing__row--bg"></div>
  </div>

  <div class="landing__wrapper">
    <div class="landing__grid">
      <div class="landing__row landing__row--grid" style="<?php if ( has_post_thumbnail() ) { echo 'background-image: url(' . get_the_post_thumbnail_url() . ');'; } ?>">
        <div class="landing__title">
          <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?> <!-- Left column content -->

          <?php if (!get_the_content()) { ?>
            <h2><?php esc_html_e( 'Welcome back!', 'peepso-theme-gecko' ); ?></h2>
            <p><?php esc_html_e( 'Register or login to see the Community activities.', 'peepso-theme-gecko' ); ?></p>
          <?php } ?>
        </div>
      </div>

      <div class="landing__row landing__row--grid">
        <div class="landing__content">
          <div class="landing__form landing__form--login">
            <?php if ( !is_active_sidebar( 'landing' )) { ?>
              <?php if ( class_exists('PeepSo') ) { the_widget( 'PeepSoWidgetLogin', 'view_option=vertical' ); } else { ?>
                <?php if ( !is_user_logged_in() ) { wp_login_form(); } ?>
              <?php } ?>

              <?php if ( is_user_logged_in() ) : ?>
              <div class="landing__welcome">
                <?php esc_html_e( 'You are logged in as', 'peepso-theme-gecko' ); ?> <strong><?php echo $current_user->user_firstname; ?></strong>
                <div class="landing__welcome-action">
                  <a href="<?php echo $landing_btn_url; ?>" class="ps-btn ps-btn-action"><?php echo $landing_btn_label; ?></a>
                </div>
              </div>
            <?php endif; ?>
            <?php } else { ?>
              <div class="gc-widgets">
                <?php dynamic_sidebar( 'landing' ); ?>
              </div>
            <?php } ?>
          </div>
        </div>
        <?php if(!$hide_footer && !$landing_footer_full_width) : ?>
<!-- FOOTER -->
<!-- TODO: make separate template for landing footer  -->
<div class="landing__footer">
  <div class="landing__footer-widget">
    <?php if ( is_active_sidebar( 'footer-widgets') && $widgets_vis === 1 ) : ?>
    <div class="gc-footer__grid">
      <!-- Include widgets -->
      <?php dynamic_sidebar( 'footer-widgets' ); ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="landing__footer-bottom">
    <div class="footer__copyrights">
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
    <ul class="footer__menu">
      <?php wp_nav_menu( array( 'theme_location' => 'footer-menu', 'items_wrap' => '%3$s', 'container' => false, 'fallback_cb' => false ) ); ?>
    </ul>
    <?php if ( is_active_sidebar( 'footer-social' ) && $widget_social_vis === 1) : ?>
    <div class="gc-footer__social">
      <?php dynamic_sidebar( 'footer-social' ); ?>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if(!$hide_footer && $landing_footer_full_width) : ?>
<!-- FOOTER -->
<!-- TODO: make separate template for landing footer  -->
<div class="landing__footer">
  <div class="landing__footer-widget">
    <?php if ( is_active_sidebar( 'footer-widgets') && $widgets_vis === 1 ) : ?>
    <div class="gc-footer__grid">
      <!-- Include widgets -->
      <?php dynamic_sidebar( 'footer-widgets' ); ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="landing__footer-bottom">
    <div class="footer__copyrights">
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
    <ul class="footer__menu">
      <?php wp_nav_menu( array( 'theme_location' => 'footer-menu', 'items_wrap' => '%3$s', 'container' => false, 'fallback_cb' => false ) ); ?>
    </ul>
    <?php if ( is_active_sidebar( 'footer-social' ) && $widget_social_vis === 1) : ?>
    <div class="gc-footer__social">
      <?php dynamic_sidebar( 'footer-social' ); ?>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php
get_footer();
