<?php
defined( '\ABSPATH' ) || exit;

use ContentEgg\application\helpers\TemplateHelper;

?>

<div class="egg-container egg-list egg-list-coupons">
	<?php if ( $title ): ?>
        <h3><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>

    <div class="egg-listcontainer">
		<?php foreach ( $items as $item ): ?>
            <div class="row-products">
                <div class="col-md-9 col-sm-9 col-xs-12 cegg-desc-cell cegg-pl5">
                    <h4 class="cegg-no-top-margin">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
							<?php echo esc_html( $item['title'] ); ?>
                        </a>
                    </h4>
					<?php if ( $item['description'] ): ?>
                        <div class="small text-muted cegg-lineh-20"><?php echo wp_kses_post($item['description']); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $item['extra']['discount'] ) ): ?>
                        <span class="label label-danger">
                            <?php echo esc_html( $item['extra']['discount'] ); ?><?php TemplateHelper::esc_html_e( 'OFF' ) ?>
                        </span>&nbsp;
					<?php endif; ?>

					<?php if ( $module_id == 'TradedoublerCoupons' && $item['extra']['discountAmount'] ): ?>
                        <span class="label label-danger">
                            <?php if ( ! (bool) $item['extra']['isPercentage'] ) {
	                            echo esc_html(TemplateHelper::currencyTyping( $item['extra']['currencyId'] ));
                            } ?><?php echo esc_html( $item['extra']['discountAmount'] ); ?><?php if ( (bool) $item['extra']['isPercentage'] ) {
	                            echo '%';
                            } ?>
                            <?php TemplateHelper::esc_html_e( 'OFF' ) ?>
                        </span>
					<?php endif; ?>

					<?php if ( $item['startDate'] ): ?>
                        <span class="text-muted small text-center"><em><?php echo esc_html(sprintf( TemplateHelper::__( 'Start date: %s' ), TemplateHelper::formatDate( $item['startDate'] )) ); ?></em></span>
					<?php endif; ?>
					<?php if ( $item['endDate'] ): ?>
                        <span class="text-muted small text-center"><em><?php echo esc_html(sprintf( TemplateHelper::__( 'End date: %s' ), TemplateHelper::formatDate( $item['endDate'] ) )); ?></em></span>
					<?php endif; ?>
                </div>
                <div class="col-md-3 col-sm-3 col-xs-12 offer_price cegg-price-cell">
					<?php if ( $item['img'] ): ?>
						<?php $item['img'] = str_replace( 'http://', '//', $item['img'] ); ?>
                        <div class="cegg-thumb">
                            <img src="<?php echo esc_url($item['img']); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>"/>
                        </div>
					<?php endif; ?>
					<?php if ( $item['code'] ): ?>
                        <div class="cegg-coupon-row cegg-mb10">
                            <span class="cegg-couponcode"><?php echo esc_html( $item['code'] ); ?></span>
                        </div>
					<?php endif; ?>
                    <div class="cegg-btn-row cegg-mb10">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"
                                                               class="btn btn-danger"><?php TemplateHelper::couponBtnText( true, $item, $btn_text ); ?></a>

						<?php if ( $merchant = TemplateHelper::getMerhantName( $item ) ): ?>
                            <div class="text-center">
                                <small class="text-muted title-case"><?php echo \esc_html( $merchant ); ?></small>
                            </div>
						<?php endif; ?>

                    </div>
                </div>
            </div>
		<?php endforeach; ?>
    </div>
</div>



