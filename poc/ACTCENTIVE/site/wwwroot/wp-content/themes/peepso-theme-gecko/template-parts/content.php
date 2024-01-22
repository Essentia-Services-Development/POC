<?php

global $post;

$hide_title = get_post_meta($post->ID, 'gecko-page-hide-title', true);
$post_class = '';
$post_date = get_the_date( 'l F j, Y' );
$post_tags = get_the_tag_list( '', __( ', ', 'peepso-theme-gecko' ) );
$gecko_settings = GeckoConfigSettings::get_instance();

// Set the image caption default visibility
$is_image_caption_vis = NULL;

if (is_single() && $gecko_settings->get_option( 'opt_blog_single_post_image_caption', 0 )) {
	$is_image_caption_vis = 1;
}

if ( !is_front_page() && is_home() && $gecko_settings->get_option( 'opt_blog_image_caption', 0 ) ) {
	$is_image_caption_vis = 1;
}

// Wire up to customizer option (Blog > General) - Controls excerpt words number if limit is enabled
$limit_words_number = $gecko_settings->get_option( 'opt_limit_blog_post_words_number', 55 );

if (! has_post_thumbnail() ) {
	$post_class = 'post--noimage';
}

?>

<article id="post-<?php the_ID(); ?>" <?php post_class($post_class); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
	<div class="entry-image" style="background-image: url(<?php echo get_the_post_thumbnail_url(); ?>">
		<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php echo get_the_post_thumbnail(); ?></a>
	</div>
	<?php if ($is_image_caption_vis) : ?><div class="wp-caption-text"><?php echo get_the_post_thumbnail_caption(); ?></div><?php endif; ?>
	<?php endif; ?>

	<?php edit_post_link( '<i class="gcis gci-pen gc-tip" arialabel="'.__( 'Edit', 'peepso-theme-gecko' ).'"></i>', '<span class="edit-link">', '</span>' ); ?>

	<?php if(!$hide_title) : ?>
	<header class="entry-header">
		<?php
			if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
			endif;
		?>
	</header><!-- .entry-header -->
	<?php endif; ?>

	<?php do_action('gecko_after_page_header'); ?>

	<?php if (is_blog() || is_home()) : ?>
	<div class="entry-date entry-date--static">
		<i class="gcir gci-calendar-alt"></i> <a href="<?php echo esc_url(get_permalink()); ?>"><?php gc_post_date() ?></a> <?php if (is_blog()) : ?><?php _e( 'Published by', 'peepso-theme-gecko' ); ?> <a class="author-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author"><?php echo get_the_author(); ?></a><?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="entry-content">
		<?php
			if ( has_excerpt( $post->ID ) && !is_single() ) {
				the_excerpt();
			} else {
				/* translators: %s: Name of current post */
				if (($gecko_settings->get_option( 'opt_limit_blog_post', 0 ) == 1) && !is_single()) {
					echo '<p>' . wp_trim_words(get_the_content(), $limit_words_number, '... <a class="more-link" href="' . esc_url( get_the_permalink() ) . '"> ' . __( 'Read more', 'peepso-theme-gecko' ) . '</a>' ) . '</p>';
				} else {
					the_content( sprintf(
						__( 'Read more %s', 'peepso-theme-gecko' ),
						the_title( '<span class="screen-reader-text" style="display:none;">', '</span>', false )
					) );
				}
			}

			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'peepso-theme-gecko' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'peepso-theme-gecko' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
			) );
		?>
	</div><!-- .entry-content -->

	<?php
		// Author bio.
		if ( is_single() && get_the_author_meta( 'description' ) ) :
			get_template_part( 'author-bio' );
		endif;
	?>

	<footer class="entry-footer">
		<?php if (is_blog()) : ?>
		<div class="entry-category"><?php _e('Category: ', 'peepso-theme-gecko'); the_category(', ') ?></div>
			<?php if ($post_tags) : ?>
				<div class="entry-tags">
					<?php _e('Tags: ', 'peepso-theme-gecko'); echo $post_tags; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if (is_home()) : ?><div class="entry-author"><i class="gcis gci-user-edit"></i><span><?php _e( 'Published by', 'peepso-theme-gecko' ); ?></span> <a class="author-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author"><?php echo get_the_author(); ?></a></div><?php endif; ?>

		<?php if (is_home() && (get_comments_number($post->ID) > 0)) : ?><div class="entry-comments" aria-label="<?php echo _e( 'Comments', 'peepso-theme-gecko' ); ?>"><i class="gcis gci-comment-alt"></i><?php echo '<a href="' . get_comments_link( $post->ID ) . '">' . get_comments_number($post->ID) . '</a>'; ?></div><?php endif; ?>
	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
