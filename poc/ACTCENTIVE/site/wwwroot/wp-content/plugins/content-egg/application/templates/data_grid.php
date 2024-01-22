<?php
defined( '\ABSPATH' ) || exit;

use ContentEgg\application\helpers\TemplateHelper;

if ( TemplateHelper::isModuleDataExist( $items, 'Amazon', 'AmazonNoApi' ) ) {
	\wp_enqueue_script( 'cegg-frontend', \ContentEgg\PLUGIN_RES . '/js/frontend.js', array( 'jquery' ) );
}


if ( empty( $cols ) || $cols > 12 ) {
	$cols = 4;
}
$col_size = ceil( 12 / $cols );
?>

<div class="egg-container egg-grid">
	<?php if ( $title ): ?>
        <h3><?php echo \esc_html( $title ); ?></h3>
	<?php endif; ?>

    <div class="container-fluid">
        <div class="row">

			<?php
			$i = 0;
			foreach ( $items as $item ) {
				$this->renderBlock( 'grid_row', array( 'item' => $item, 'col_size' => $col_size, 'i' => $i ) );
				$i ++;
				if ( $i % $cols == 0 ) {
					echo '<div class="clearfix hidden-xs"></div>';
				}
				if ( $i % 2 == 0 ) {
					echo '<div class="clearfix visible-xs-block"></div>';
				}
			}
			?>

        </div>
    </div>

	<?php if ( $module_id == 'Amazon' || $module_id == 'AmazonNoApi' ): ?>
        <div class="row cegg-no-top-margin">
            <div class="col-md-12 text-right text-muted">
                <small>
					<?php echo esc_html(sprintf( TemplateHelper::__( 'Last updated on %s' ), TemplateHelper::getLastUpdateFormatted( $module_id, $post_id ) )); ?>
					<?php TemplateHelper::printAmazonDisclaimer(); ?>
                </small>
            </div>
        </div>
	<?php endif; ?>
</div>
