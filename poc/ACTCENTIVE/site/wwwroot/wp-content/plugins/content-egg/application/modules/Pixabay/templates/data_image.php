<?php
defined( '\ABSPATH' ) || exit;
/*
  Name: Image
 */
__( 'Image', 'content-egg-tpl' );
?>
<?php \wp_enqueue_style( 'egg-bootstrap' ); ?>

<div class="egg-container egg-image">
	<?php if ( $title ): ?>
        <h3><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>
    <div class="row">
		<?php foreach ( $items as $item ): ?>
            <div class="col-md-12" style="padding-bottom: 20px;">
                <img src="<?php echo esc_url($item['img']); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>"
                     class="img-thumbnail"/>
            </div>
		<?php endforeach; ?>
    </div>
</div>