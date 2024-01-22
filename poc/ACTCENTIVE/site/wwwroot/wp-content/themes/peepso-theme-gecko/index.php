<?php
get_header();

//  Options page settings
$full_width_layout       = get_post_meta(get_proper_ID(), 'gecko-page-full-width', true);
$builder_friendly_layout = get_post_meta(get_proper_ID(), 'gecko-page-builder-friendly', true);

$main_class = layout_main_class(get_proper_ID());
$content_id = "";

if(is_home() && $gecko_settings->get_option( 'opt_blog_grid', 0 ) == 1) {
  $content_id = "gecko-blog";
}

?>

<div id="main" class="main <?php echo $main_class; if ($full_width_layout == 1) : echo " main--full"; endif; if ($builder_friendly_layout == 1) : echo " main--builder"; endif; ?>">
  <!-- ABOVE CONTENT WIDGETS -->
  <?php get_template_part( 'template-parts/widgets/above-content' ); ?>
  <!-- end: ABOVE CONTENT WIDGETS -->
  <div <?php if($content_id) : ?>id="<?php echo $content_id;?>"<?php endif; ?> class="content">
    <?php
    if ( function_exists('yoast_breadcrumb') ) {
      if ($gecko_settings->get_option( 'opt_yoastseo_breadcrumbs', 1 ) == 1) {
        yoast_breadcrumb( '<div id="breadcrumbs" class="gc-breadcrumbs">','</div>' );
      }
    }
    ?>

    <?php if ( have_posts() ) : ?>
      <div class="content__posts">
      <?php
      // Start the loop.
      while ( have_posts() ) : the_post();

        /*
         * Include the Post-Format-specific template for the content.
         * If you want to override this in a child theme, then include a file
         * called content-___.php (where ___ is the Post Format name) and that will be used instead.
         */
        get_template_part( 'template-parts/content', get_post_format() );

        // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) :
          comments_template();
        endif;

      // End the loop.
      endwhile;
      ?>
      </div>

      <?php
      // Previous/next page navigation.
      the_posts_pagination( array(
        'prev_text'          => __( 'Previous page', 'peepso-theme-gecko' ),
        'next_text'          => __( 'Next page', 'peepso-theme-gecko' ),
        'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'peepso-theme-gecko' ) . ' </span>',
      ) );

    // If no content, include the "No posts found" template.
    else :
      get_template_part( 'template-parts/content', 'none' );

    endif;
    ?>
  </div>
  <!-- UNDER CONTENT WIDGETS -->
  <?php get_template_part( 'template-parts/widgets/under-content' ); ?>
  <!-- end: UNDER CONTENT WIDGETS -->

  <?php get_sidebar('left'); ?>
  <?php get_sidebar('right'); ?>
</div>

<?php

get_footer();
