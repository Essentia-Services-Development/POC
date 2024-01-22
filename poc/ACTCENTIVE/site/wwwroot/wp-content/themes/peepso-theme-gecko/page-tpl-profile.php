<?php
get_header();

//
//
//
//  THIS TEMPLATE IS NOT USED ANYMORE SINCE 3.0.0.0
//
//
//

//  Options page settings
$full_width_layout       = get_post_meta(get_proper_ID(), 'gecko-page-full-width', true);
$builder_friendly_layout = get_post_meta(get_proper_ID(), 'gecko-page-builder-friendly', true);

$main_class = layout_main_class(get_proper_ID());

?>

<div id="main" class="main <?php echo $main_class; if ($full_width_layout == 1) : echo " main--full"; endif; if ($builder_friendly_layout == 1) : echo " main--builder"; endif; ?>">
  <div class="content">
    <?php if ( have_posts() ) : ?>

      <?php
      // Start the loop.
      while ( have_posts() ) : the_post();

        /*
         * Include the Post-Format-specific template for the content.
         * If you want to override this in a child theme, then include a file
         * called content-___.php (where ___ is the Post Format name) and that will be used instead.
         */
        get_template_part( 'template-parts/content', 'page' );

        // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) :
          comments_template();
        endif;

      // End the loop.
      endwhile;

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

  <?php get_sidebar('left'); ?>
  <?php get_sidebar('right'); ?>
</div>

<?php

get_footer();
