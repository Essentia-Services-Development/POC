<?php
defined( '\ABSPATH' ) || exit;
/*
  Name: Simple
 */

__( 'Simple', 'content-egg-tpl' );

use ContentEgg\application\helpers\TemplateHelper;

?>

<?php
\wp_enqueue_style( 'egg-bootstrap' );
\wp_enqueue_style( 'egg-products' );
?>

<div class="egg-container">
	<?php if ( $title ): ?>
        <h3><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>

    <div class="egg-simplelistcontainer">

		<?php foreach ( $items as $item ): ?>
            <div class="row">
                <div class="col-md-12">
                    <h3>
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
							<?php echo $item['title']; ?>
                        </a>
                    </h3>
					<?php if ( $item['description'] ): ?>
                        <p><?php echo wp_kses_post($item['description']); ?></p>
					<?php endif; ?>
                </div>

            </div>
		<?php endforeach; ?>
    </div>
</div>