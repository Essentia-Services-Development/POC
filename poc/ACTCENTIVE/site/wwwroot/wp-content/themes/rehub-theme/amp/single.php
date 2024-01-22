<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Single view template.
 *
 * @package AMP
 */

/**
 * Context.
 *
 * @var AMP_Post_Template $this
 */

$this->load_parts( array( 'html-start' ) );
?>
<?php $this->load_parts( array( 'header' ) ); ?>

<article class="amp-wp-article">

	<?php do_action('ampforwp_post_before_design_elements') ?>
	<?php include(rh_locate_template('amp/title-section.php')); ?>

	<?php
		if ( function_exists( 'ampforwp_is_amp_endpoint' ) ){
			$this->load_parts( array( 'elements/featured-image' ) );
		}else{
			$this->load_parts( array( 'featured-image' ) );
	} ?>
	<div class="clearfix"></div>

	<div class="amp-wp-article-content">
		<?php do_action('ampforwp_before_post_content') ?>
		<?php echo ''.$this->get( "post_amp_content" ).''; // amphtml content; no kses ?>
		<?php do_action('ampforwp_after_post_content') ?>
	</div>

	<footer class="amp-wp-article-footer">
		<?php $this->load_parts( apply_filters( 'amp_post_article_footer_meta', array('rehub-amp-social', 'meta-comments-link' ) ) ); ?>	
	</footer>

</article>

<?php $this->load_parts( array( 'footer' ) ); ?>

<?php
$this->load_parts( array( 'html-end' ) );
