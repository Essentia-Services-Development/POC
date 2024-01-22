<?php

get_header();

//  Options page settings
$full_width_layout       = get_post_meta(get_proper_ID(), 'gecko-page-full-width', true);
$builder_friendly_layout = get_post_meta(get_proper_ID(), 'gecko-page-builder-friendly', true);
$hide_sidebars           = get_post_meta(get_proper_ID(), 'gecko-page-sidebars', true);

if (is_blog() && $gecko_settings->get_option( 'opt_blog_sidebars', 1 ) == NULL) {
  $hide_sidebars = 'both';
}

if (is_blog() && $gecko_settings->get_option( 'opt_blog_sidebars', 1 ) == NULL && get_post_meta(get_proper_ID(), 'gecko-page-sidebars', true) == 'left' ) {
  $hide_sidebars = 'left';
}

if (is_blog() && $gecko_settings->get_option( 'opt_blog_sidebars', 1 ) == NULL && get_post_meta(get_proper_ID(), 'gecko-page-sidebars', true) == 'right' ) {
  $hide_sidebars = 'right';
}

$main_class = "";

if (is_active_sidebar( 'sidebar-left' ) && ($hide_sidebars == 'right' || !$hide_sidebars)) {
  $main_class = "main--left";
}

if (is_active_sidebar( 'sidebar-right' ) && ($hide_sidebars == 'left' || !$hide_sidebars)) {
  $main_class = "main--right";
}

if (is_active_sidebar( 'sidebar-left' ) && is_active_sidebar( 'sidebar-right' ) && !$hide_sidebars) {
  $main_class = "main--both";
}

if (is_singular('sfwd-courses') && NULL == $gecko_settings->get_option( 'opt_ld_sidebars', 1 )) {
  $main_class = "";
}

?>


<?php //the_title(); ?>

<div id="main" class="main main--single <?php echo $main_class; if ($full_width_layout == 1) : echo " main--full"; endif; if ($builder_friendly_layout == 1) : echo " main--builder"; endif; ?>">
  <!-- ABOVE CONTENT WIDGETS -->
  <?php get_template_part( 'template-parts/widgets/above-content' ); ?>
  <!-- end: ABOVE CONTENT WIDGETS -->
  <?php if ((is_singular('sfwd-courses') && NULL == $gecko_settings->get_option( 'opt_ld_sidebars', 1 )) || (is_blog() && ($hide_sidebars == 'left' || $hide_sidebars == 'both'))) {
    // do nothing
  } else {
    get_sidebar('left');
  }
  ?>

  <?php if ((is_singular('sfwd-courses') && NULL == $gecko_settings->get_option( 'opt_ld_sidebars', 1 )) || (is_blog() && ($hide_sidebars == 'right' || $hide_sidebars == 'both'))) {
    // do nothing
  } else {
    get_sidebar('right');
  }
  ?>

  <div class="content">
    <?php
    if ( function_exists('yoast_breadcrumb') ) {
      if ($gecko_settings->get_option( 'opt_yoastseo_breadcrumbs', 1 ) == 1) {
        yoast_breadcrumb( '<div id="breadcrumbs" class="gc-breadcrumbs">','</div>' );
      }
    }
    ?>

    <?php
    // Start the loop.
    while ( have_posts() ) : the_post();

      /*
       * Include the post format-specific template for the content. If you want to
       * use this in a child theme, then include a file called content-___.php
       * (where ___ is the post format) and that will be used instead.
       */
      get_template_part( 'template-parts/content', get_post_format() );

      // If comments are open or we have at least one comment, load up the comment template.
      if ( comments_open() || get_comments_number() ) :
        comments_template();
      endif;

      // Previous/next post navigation.
      the_post_navigation( array(
        'next_text' => '<span class="meta-nav">' . __( 'Next', 'peepso-theme-gecko' ) . ' <i class="gcis gci-angle-right"></i></span>',
        'prev_text' => '<span class="meta-nav"><i class="gcis gci-angle-left"></i> ' . __( 'Previous', 'peepso-theme-gecko' ) . '</span>',
      ) );

      if ($gecko_settings->get_option( 'opt_edit_link_bottom', 0 )) {
        edit_post_link( '<i class="gcis gci-pen gc-tip"></i> '. __( 'Edit Page', 'peepso-theme-gecko' ) . '', '<span class="edit-link-static">', '</span>' );
      }

    // End the loop.
    endwhile;
    ?>
  </div>

  <!-- UNDER CONTENT WIDGETS -->
  <?php get_template_part( 'template-parts/widgets/under-content' ); ?>
  <!-- end: UNDER CONTENT WIDGETS -->

  <?php if ((is_singular('sfwd-courses') && NULL == $gecko_settings->get_option( 'opt_ld_sidebars', 1 )) || (is_blog() && ($hide_sidebars == 'right' || $hide_sidebars == 'both'))) {
    // do nothing
  } else {
    get_sidebar('right');
  }
  ?>
</div>

<?php

get_footer();
