<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' );

$gecko_settings = GeckoConfigSettings::get_instance();

//  Options page settings
$full_width_layout       = get_post_meta(get_proper_ID(), 'gecko-page-full-width', true);
$builder_friendly_layout = get_post_meta(get_proper_ID(), 'gecko-page-builder-friendly', true);
$hide_sidebars           = get_post_meta(get_proper_ID(), 'gecko-page-sidebars', true);
$hide_sidebars_woo       = $gecko_settings->get_option( 'opt_woo_sidebars', 1 );
$builder_friendly_woo    = $gecko_settings->get_option( 'opt_woo_builder', 0 );
$main_class              = "";

if (is_active_sidebar( 'sidebar-left' ) && ($hide_sidebars == 'right' || !$hide_sidebars)) {
  $main_class = "main--left";
}

if (is_active_sidebar( 'sidebar-right' ) && ($hide_sidebars == 'left' || !$hide_sidebars)) {
  $main_class = "main--right";
}

if (is_active_sidebar( 'sidebar-left' ) && is_active_sidebar( 'sidebar-right' ) && !$hide_sidebars) {
  $main_class = "main--both";
}

if ($hide_sidebars_woo == 0) {
	$hide_sidebars = 1;
	$main_class = "";
}

if ($builder_friendly_woo == 1) {
	$builder_friendly_layout = 1;
}

?>
<div id="main" class="main main--single <?php echo $main_class; if ($full_width_layout == 1) : echo " main--full"; endif; if ($builder_friendly_layout == 1) : echo " main--builder"; endif; ?>">
  	<!-- ABOVE CONTENT WIDGETS -->
  	<?php get_template_part( 'template-parts/widgets/above-content' ); ?>
	<!-- end: ABOVE CONTENT WIDGETS -->
	<?php if (($hide_sidebars == 'left' || $hide_sidebars == 'both') || $hide_sidebars == 1) {
		// do nothing
	} else {
		get_sidebar('left');
	}
	?>

	<?php if (($hide_sidebars == 'right' || $hide_sidebars == 'both' || $hide_sidebars == 1)) {
		// do nothing
	} else {
		get_sidebar('right');
	}
	?>
	<div class="content">
	<?php
		/**
		 * woocommerce_before_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action( 'woocommerce_before_main_content' );
	?>

		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; // end of the loop. ?>

	<?php
		/**
		 * woocommerce_after_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>
	</div>

	<!-- UNDER CONTENT WIDGETS -->
	<?php get_template_part( 'template-parts/widgets/under-content' ); ?>
	<!-- end: UNDER CONTENT WIDGETS -->

	<?php if (($hide_sidebars == 'right' || $hide_sidebars == 'both' || $hide_sidebars == 1)) {
    // do nothing
	} else {
		get_sidebar('right');
	}
  ?>
</div>
<?php
get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
