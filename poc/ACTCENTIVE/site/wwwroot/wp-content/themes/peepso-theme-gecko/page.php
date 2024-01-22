<?php
get_header();

$gecko_settings = GeckoConfigSettings::get_instance();

//  Options page settings
$hide_title = get_post_meta(get_proper_ID(), 'gecko-page-hide-title', true);
$full_width_layout = get_post_meta(get_proper_ID(), 'gecko-page-full-width', true);
$builder_friendly_layout = get_post_meta(get_proper_ID(), 'gecko-page-builder-friendly', true);
$main_class = layout_main_class(get_proper_ID());

?>

<?php if ( class_exists('PeepSo') ) : ?>
  <?php
  $PeepSoProfile=PeepSoProfile::get_instance();

  if (has_shortcode( $post->post_content, 'peepso_profile' )) :
  ?>
    <?php if (2 == $gecko_settings->get_option( 'opt_ps_profile_page_cover', 1 ) ) : ?>
      <div class="gc-profile__cover gc-profile__cover--wide">
        <?php if(!$hide_title) : ?>
      	<header class="entry-header">
      		<?php
      			the_title( '<h1 class="entry-title">', '</h1>' );
      		?>
      		<?php edit_post_link( '<i class="gcis gci-pen gc-tip" arialabel="'.__( 'Edit', 'peepso-theme-gecko' ).'"></i>', '<span class="edit-link">', '</span>' ); ?>
      	</header><!-- .entry-header -->
      	<?php endif; ?>

        <?php get_template_part( 'template-parts/peepso/navbar' ); ?>
        <?php get_template_part( 'template-parts/peepso/focus' ); ?>
      </div>
    <?php elseif (3 == $gecko_settings->get_option( 'opt_ps_profile_page_cover', 1 ) ) : ?>
      <div class="gc-profile__cover gc-profile__cover--full">
        <?php if(!$hide_title) : ?>
      	<header class="entry-header">
      		<?php
      			the_title( '<h1 class="entry-title">', '</h1>' );
      		?>
      		<?php edit_post_link( '<i class="gcis gci-pen gc-tip" arialabel="'.__( 'Edit', 'peepso-theme-gecko' ).'"></i>', '<span class="edit-link">', '</span>' ); ?>
      	</header><!-- .entry-header -->
      	<?php endif; ?>

        <?php get_template_part( 'template-parts/peepso/navbar' ); ?>
        <?php get_template_part( 'template-parts/peepso/focus' ); ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
<?php endif; ?>

<div id="main" class="main <?php echo $main_class; if ($full_width_layout == 1) : echo " main--full"; endif; if ($builder_friendly_layout == 1) : echo " main--builder"; endif; ?>">
  <!-- ABOVE CONTENT WIDGETS -->
  <?php get_template_part( 'template-parts/widgets/above-content' ); ?>
  <!-- end: ABOVE CONTENT WIDGETS -->
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
  <!-- UNDER CONTENT WIDGETS -->
  <?php get_template_part( 'template-parts/widgets/under-content' ); ?>
  <!-- end: UNDER CONTENT WIDGETS -->
  <?php get_sidebar('left'); ?>
  <?php get_sidebar('right'); ?>
</div>

<?php

get_footer();
