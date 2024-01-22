<?php
defined( '\ABSPATH' ) || exit;
/*
  Name: Simple
 */
__( 'Simple', 'content-egg' );
?>
<?php \wp_enqueue_style( 'egg-bootstrap' ); ?>

<?php foreach ( $items as $item ): ?>
	<?php $keywords[] = $item['title']; ?>
<?php endforeach; ?>

<div class="egg-container">
	<?php if ( $title ): ?>
        <h3><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>
	<?php echo esc_html(join( ', ', $keywords )); ?>.
</div>