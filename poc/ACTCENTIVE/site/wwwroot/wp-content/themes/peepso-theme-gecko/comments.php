<?php
/**
 * The template for displaying comments
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
				$comments_number = get_comments_number();
			if ( '1' === $comments_number ) {
				/* translators: %s: post title */
				printf( _x( 'One thought on &ldquo;%s&rdquo;', 'comments title', 'peepso-theme-gecko' ), get_the_title() );
			} else {
				printf(
					/* translators: 1: number of comments, 2: post title */
					_nx(
						'%1$s thought on &ldquo;%2$s&rdquo;',
						'%1$s thoughts on &ldquo;%2$s&rdquo;',
						$comments_number,
						'comments title',
						'peepso-theme-gecko'
					),
					number_format_i18n( $comments_number ),
					get_the_title()
				);
			}
			?>
		</h2>


		<ol class="comment-list">
			<?php
				wp_list_comments(
					array(
						'style'       => 'ol',
						'short_ping'  => true,
						'avatar_size' => 56,
					)
				);
			?>
		</ol><!-- .comment-list -->

		<?php
		the_comments_pagination(
			array(
				'prev_text' => '<span class="screen-reader-text">' . __( 'Previous', 'peepso-theme-gecko' ) . '</span>',
				'next_text' => '<span class="screen-reader-text">' . __( 'Next', 'peepso-theme-gecko' ) . '</span>',
			)
		);
		?>

	<?php endif; // have_comments() ?>

	<?php comment_form(); ?>

</div><!-- .comments-area -->
