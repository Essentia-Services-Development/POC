<?php
defined( '\ABSPATH' ) || exit;

use ContentEgg\application\helpers\TemplateHelper;

if ( TemplateHelper::isModuleDataExist( $items, 'Amazon', 'AmazonNoApi' ) ) {
	\wp_enqueue_script( 'cegg-frontend', \ContentEgg\PLUGIN_RES . '/js/frontend.js', array( 'jquery' ) );
}
?>

<?php if ( $title ): ?>
    <h3 class="cegg-shortcode-title"><?php echo \esc_html( $title ); ?></h3>
<?php endif; ?>
<?php foreach ( $items as $item ): ?>

    <div class="egg-container egg-item">
        <div class="products">

			<?php $this->renderBlock( 'item_row', array( 'item' => $item ) ); ?>

        </div>
    </div>
<?php endforeach; ?>