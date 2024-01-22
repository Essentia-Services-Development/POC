<?php
defined( '\ABSPATH' ) || exit;
/*
  Name: Simple
 */
__( 'Simple', 'content-egg' );
?>
<?php \wp_enqueue_style( 'egg-bootstrap' ); ?>

<div class="egg-container egg-image">
	<?php if ( $title ): ?>
        <h3><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>
    <div class="row">
		<?php foreach ( $items as $item ): ?>
            <div class="col-md-12" style="padding-bottom: 20px;">
                <img src="<?php echo esc_url($item['img']); ?>"<?php if ( ! empty( $item['keyword'] ) ): ?>
                     alt="<?php echo esc_attr( $item['keyword'] ); ?>" <?php endif; ?>class="img-thumbnail"/>
                <div class="text-center">
                    <p class="small"><?php printf( __( 'Source: %s', 'content-egg' ), esc_attr( $item['extra']['source'] ) ); ?></p>
                    <h4><?php echo esc_html( $item['title'] ); ?></h4>
                    <p><?php echo wp_kses_post($item['description']); ?></p>
                </div>
            </div>
		<?php endforeach; ?>
    </div>
</div>