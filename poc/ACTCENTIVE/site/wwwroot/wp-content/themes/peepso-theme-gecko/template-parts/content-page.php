<?php
$gecko_settings = GeckoConfigSettings::get_instance();

$hide_title = get_post_meta(get_proper_ID(), 'gecko-page-hide-title', true);
$hide_featuredimage = get_post_meta(get_proper_ID(), 'gecko-page-hide-featuredimage', true);
$show_content_box = get_post_meta(get_proper_ID(), 'gecko-page-enable-box-mode', true);
$post_class = '';
$post_box_class = '';

if (! has_post_thumbnail() ) {
	$post_class = 'post--noimage ';
}

if ($show_content_box) {
	$post_box_class = 'post--box';
}

if ( class_exists('PeepSo') ) {
	$PeepSoProfile=PeepSoProfile::get_instance();

	if (has_shortcode( $post->post_content, 'peepso_profile' )) {
		if (1 < $gecko_settings->get_option( 'opt_ps_profile_page_cover', 1 ) ) {
			$hide_title = true;
		}
	}
}

//
// MobiLoud
//
if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_page_titles') ) {
  $hide_title = 1;
}
// end: Mobiloud

?>

<article id="post-<?php the_ID(); ?>" <?php post_class($post_class . $post_box_class .' post--page'); ?>>
	<?php if(!$hide_featuredimage) : ?>
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-image">
			<?php echo get_the_post_thumbnail(); ?>
		</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php edit_post_link( '<i class="gcis gci-pen gc-tip" arialabel="'.__( 'Edit', 'peepso-theme-gecko' ).'"></i>', '<span class="edit-link">', '</span>' ); ?>

	<?php if(!$hide_title) : ?>
	<header class="entry-header">
		<?php
			the_title( '<h1 class="entry-title">', '</h1>' );
		?>
	</header><!-- .entry-header -->
	<?php endif; ?>

	<?php do_action('gecko_after_page_header'); ?>

	<div class="entry-content">
		<?php the_content(); ?>
		<?php
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
	<?php if ($gecko_settings->get_option( 'opt_edit_link_bottom', 0 )) : ?>
		<?php edit_post_link( '<i class="gcis gci-pen gc-tip"></i> '. __( 'Edit Page', 'peepso-theme-gecko' ) . '', '<span class="edit-link-static">', '</span>' ); ?>
	<?php endif; ?>

</article><!-- #post-## -->
