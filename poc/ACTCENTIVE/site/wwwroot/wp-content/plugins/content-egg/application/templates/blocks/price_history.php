<?php
defined( '\ABSPATH' ) || exit;

use ContentEgg\application\helpers\TemplateHelper;

?>

<?php if ( $title ): ?>
    <h4><?php echo \esc_html( $title ); ?></h4>
<?php else: ?>
    <h4><?php esc_html_e( 'Price History', 'content-egg-tpl' ); ?></h4>
<?php endif; ?>

<?php TemplateHelper::priceHistoryMorrisChart( $item['unique_id'], $module_id, 180, array( 'lineWidth'   => 2,
                                                                                           'postUnits'   => ' ' . $item['currencyCode'],
                                                                                           'goals'       => array( (int) $item['price'] ),
                                                                                           'fillOpacity' => 0.5
), array( 'style' => 'height: 220px;' ) ); ?>

<?php $prices = TemplateHelper::priceHistoryPrices( $item['unique_id'], $module_id, $limit = 5 ); ?>
<?php if ( ! $prices ) {
	return;
} ?>
<div class="row">
    <div class='col-md-7'>
        <h4><?php TemplateHelper::esc_html_e( 'Statistics' ); ?></h4>
        <table class="table table-hover">
            <tr>
                <td><?php TemplateHelper::esc_html_e( 'Current Price' ); ?></td>
                <td>
					<?php if ( $item['price'] ): ?>
						<?php echo esc_html(TemplateHelper::formatPriceCurrency( $item['price'], $item['currencyCode'] )); ?>
					<?php else: ?>
                        -
					<?php endif; ?>
                </td>
                <td><?php echo esc_html(TemplateHelper::getLastUpdateFormatted( $module_id, $post_id, false )); ?></td>
            </tr>
			<?php $price = TemplateHelper::priceHistoryMax( $item['unique_id'], $module_id ); ?>
            <tr>
                <td class="text-danger"><?php TemplateHelper::esc_html_e( 'Highest Price' ); ?></td>
                <td><?php echo esc_html(TemplateHelper::formatPriceCurrency( $price['price'], $item['currencyCode'] )); ?></td>
                <td><?php echo esc_html(TemplateHelper::formatDate( $price['date'] )); ?></td>
            </tr>
			<?php $price = TemplateHelper::priceHistoryMin( $item['unique_id'], $module_id ); ?>
            <tr>
                <td class="text-success"><?php TemplateHelper::esc_html_e( 'Lowest Price' ); ?></td>
                <td><?php echo esc_html(TemplateHelper::formatPriceCurrency( $price['price'], $item['currencyCode'] )); ?></td>
                <td><?php echo esc_html(TemplateHelper::formatDate( $price['date'] )); ?></td>
            </tr>
        </table>
		<?php $since = TemplateHelper::priceHistorySinceDate( $item['unique_id'], $module_id ); ?>
        <div class='text-right text-muted'><?php echo esc_html(sprintf( TemplateHelper::__( 'Since %s' ), TemplateHelper::formatDate( $since ) )); ?></div>
    </div>
    <div class='col-md-5'>
        <h4><?php TemplateHelper::esc_html_e( 'Last price changes' ); ?></h4>
        <table class="table table-hover table-condensed">
			<?php foreach ( $prices as $price ): ?>
                <tr>
                    <td><?php echo esc_html(TemplateHelper::formatPriceCurrency( $price['price'], $item['currencyCode'] )); ?></td>
                    <td><?php echo esc_html(TemplateHelper::formatDate( $price['date'] )); ?></td>
                </tr>
			<?php endforeach; ?>
        </table>

    </div>
</div>