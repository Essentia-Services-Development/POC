<?php
/**
 * PEEPSO SITE - Main index file
 *
 */
get_header();

// Get search visibility option from admin settings
$gecko_settings = GeckoConfigSettings::get_instance();

// Options page settings
$full_width_layout = $gecko_settings->get_option( 'opt_search_full_width_layout', 0 );
// $builder_friendly_layout = get_post_meta(get_proper_ID(), 'gecko-page-builder-friendly', true);

$main_class = 'both';

if($gecko_settings->get_option( 'opt_sidebar_left_search_vis', 1 ) == 1) {
  $main_class = 'main--left';
}

if($gecko_settings->get_option( 'opt_sidebar_right_search_vis', 1 ) == 1) {
  $main_class = 'main--right';
}

if($gecko_settings->get_option( 'opt_sidebar_left_search_vis', 1 ) == 1 && $gecko_settings->get_option( 'opt_sidebar_right_search_vis', 1 ) == 1) {
  $main_class = 'main--both';
}

$content_id = "";

if($gecko_settings->get_option( 'opt_search_grid', 0 ) == 1) {
  $content_id = "gecko-blog";
}

?>

<div id="main" class="main <?php echo $main_class; if ($full_width_layout === 1) : echo " main--full"; endif; ?>">
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

    <h1><?php printf( __( 'Search Results for: %s', 'peepso-theme-gecko' ), '<strong>' . get_search_query() . '</strong>' ); ?></h1>

    <?php if ( have_posts() ) : ?>
      <div class="content__posts">
      <?php
      // Start the loop.
      while ( have_posts() ) : the_post(); ?>

        <?php
        /*
         * Run the loop for the search to output the results.
         * If you want to overload this in a child theme then include a file
         * called content-search.php and that will be used instead.
         */
        get_template_part( 'template-parts/content', 'search' );

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

<?php get_footer(); ?>
