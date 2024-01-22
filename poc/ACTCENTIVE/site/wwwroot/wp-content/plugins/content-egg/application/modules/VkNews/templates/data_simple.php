<?php
defined( '\ABSPATH' ) || exit;
/*
  Name: Simple
 */
__( 'Simple', 'content-egg-tpl' );
?>
<?php \wp_enqueue_style( 'egg-bootstrap' ); ?>

<div class="egg-container egg-media">
	<?php if ( $title ): ?>
        <h3><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>

	<?php foreach ( $items as $item ): ?>
        <div class="media">
			<?php if ( $item['img'] ): ?>
                <div class="media-left">
                    <img style="max-width: 225px;" class="media-object img-thumbnail" src="<?php echo esc_url($item['img']); ?>"
                         alt="<?php echo esc_attr( $item['title'] ); ?>"/>
                </div>
			<?php endif; ?>
            <div class="media-body">
                <h4 class="media-heading"><?php echo esc_html( $item['title'] ); ?></h4>
                <p>
					<?php echo wp_kses_post($item['description']); ?>
                </p>
            </div>
        </div>

	<?php endforeach; ?>
</div>