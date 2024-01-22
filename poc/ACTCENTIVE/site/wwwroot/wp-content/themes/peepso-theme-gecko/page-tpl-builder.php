<?php
/*
  Template Name: Builder friendly
  Template Post Type: page, download
*/

get_header();

?>

<?php if ( have_posts() ) : ?>
  <?php
    // Start the loop.
    while ( have_posts() ) : the_post();

      // Load content
      the_content();

    // End the loop.
    endwhile;
  endif;
?>

<?php

get_footer();
