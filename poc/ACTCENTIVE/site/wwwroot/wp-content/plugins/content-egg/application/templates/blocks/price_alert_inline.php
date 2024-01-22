<?php
defined( '\ABSPATH' ) || exit;

use ContentEgg\application\helpers\TemplateHelper;

?>
<?php if ( ! TemplateHelper::isPriceAlertAllowed( $item['unique_id'], $module_id ) ) {
	return;
} ?>

<?php
$desired_price = '';
//if ($item['price'])
//$desired_price = ceil($item['price'] * 90 / 100); // -10%

if ( empty( $input_class ) ) {
	$input_class = '';
}
if ( empty( $btn_class ) ) {
	$btn_class = '';
}

$privacy_url = TemplateHelper::getPrivacyUrl();
?>

<div class="cegg-price-alert-wrap">
	<?php if ( $title ): ?>
        <div class="price-alert-title cegg-mb10"><?php echo \esc_html( $title ); ?></div>
	<?php else: ?>
        <div class="price-alert-title cegg-mb5"><?php TemplateHelper::esc_html_e( 'Wait For A Price Drop' ); ?></div>
	<?php endif; ?>
    <div class="row cegg-no-bottom-margin">
        <form class="navbar-form">
            <input type="hidden" name="module_id" value="<?php echo \esc_attr( $module_id ); ?>">
            <input type="hidden" name="unique_id" value="<?php echo \esc_attr( $item['unique_id'] ); ?>">
            <input type="hidden" name="post_id" value="<?php echo \esc_attr( get_the_ID() ); ?>">
            <div class="col-md-6">
                <label class="sr-only"
                       for="cegg-email-<?php echo \esc_attr( $item['unique_id'] ); ?>"><?php TemplateHelper::esc_html_e( 'Your Email' ); ?></label>
                <input value="<?php echo \esc_attr( TemplateHelper::getCurrentUserEmail() ); ?>" type="email"
                       class="<?php echo esc_attr( $input_class ); ?> form-control" name="email"
                       id="cegg-email-<?php echo \esc_attr( $item['unique_id'] ); ?>"
                       placeholder="<?php TemplateHelper::esc_html_e( 'Your Email' ); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="sr-only"
                       for="cegg-price-<?php echo \esc_attr( $item['unique_id'] ); ?>"><?php TemplateHelper::esc_html_e( 'Desired Price' ); ?></label>
                <div class="input-group">
					<?php $cur_position = TemplateHelper::getCurrencyPos( $item['currencyCode'] ); ?>
					<?php if ( $cur_position == 'left' || $cur_position == 'left_space' ): ?>
                        <div class="input-group-addon"><?php echo esc_html(TemplateHelper::getCurrencySymbol( $item['currencyCode'] )); ?></div>
					<?php endif; ?>
                    <input value="<?php echo esc_attr($desired_price); ?>" type="number"
                           class="<?php echo \esc_attr( $input_class ); ?> form-control" name="price"
                           id="cegg-price-<?php echo \esc_attr( $item['unique_id'] ); ?>"
                           placeholder="<?php TemplateHelper::esc_html_e( 'Desired Price' ); ?>" step="any" required>
					<?php if ( $cur_position == 'right' || $cur_position == 'right_space' ): ?>
                        <div class="input-group-addon"><?php echo esc_html(TemplateHelper::getCurrencySymbol( $item['currencyCode'] )); ?></div>
					<?php endif; ?>
                    <span class="input-group-btn">
                        <button class="btn btn-warning <?php echo \esc_attr( $btn_class ); ?>"
                                type="submit"><?php TemplateHelper::esc_html_e( 'SET ALERT' ); ?></button>
                    </span>

                </div>
            </div>
            <div class="col-md-12">
				<?php if ( $privacy_url ): ?>
                    <div style="display: none;" class="price-alert-agree-wrap">
                        <label class="price-alert-agree-label">
                            <input type="checkbox" name="accepted" value="1" id="cegg_alert_accepted" required/>
							<?php $privacy_link = '<a target="_blank" href="' . \esc_attr( $privacy_url ) . '">' . TemplateHelper::__( 'Privacy Policy' ) . '</a>'; ?>
							<?php echo wp_kses_post(sprintf( TemplateHelper::__( 'I agree to the %s.' ), $privacy_link )); ?>
                        </label>
                    </div>
				<?php endif; ?>
                <div class="text-muted small cegg-mt5"><?php TemplateHelper::esc_html_e( 'You will receive a notification when the price drops.' ); ?></div>
            </div>
        </form>
    </div>

    <div class="cegg-price-loading-image" style="display: none;"><img
                src="<?php echo esc_url_raw(\ContentEgg\PLUGIN_RES) . '/img/ajax-loader.gif' ?>"/></div>
    <div class="cegg-price-alert-result-succcess text-success" style="display: none;"></div>
    <div class="cegg-price-alert-result-error text-danger" style="display: none;"></div>
</div>