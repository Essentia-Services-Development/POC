<?php
defined( '\ABSPATH' ) || exit;

use ContentEgg\application\helpers\TemplateHelper;

?>

<div class="row-products row" id="my-comparison-block">
    <div class="col-md-2 col-sm-2 col-xs-3 cegg-image-cell">

        <div class="cegg-position-container2">
            <span class="cegg-position-text2"><?php echo (int) $i + 1; ?></span>
        </div>

		<?php if ( $item['img'] ): ?>
            <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
				<?php TemplateHelper::displayImage( $item, 130, 100 ); ?>
            </a>
		<?php endif; ?>
    </div>
    <div class="col-md-8 col-sm-8 col-xs-9 cegg-desc-cell">
        <div class="cegg-no-top-margin cegg-list-logo-title">
            <a<?php TemplateHelper::printRel(); ?> target="_blank"
                                                   href="<?php echo esc_url_raw($item['url']); ?>"><?php echo \esc_html( TemplateHelper::truncate( $item['title'], 100 ) ); ?></a>
        </div>
		<?php if ( $item['description'] && strlen( strip_tags( $item['description'] ) ) < 100 ): ?>
            <div class="cegg-no-prices-desc cegg-mb5"><?php echo wp_kses_post(TemplateHelper::truncate( $item['description'], 300 )); ?></div>
		<?php endif; ?>

        <div class="text-center cegg-mt10 visible-xs">
            <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"
                                                   class="btn btn-danger btn-block"><span><?php TemplateHelper::buyNowBtnText( true, $item, $btn_text ); ?></span></a>
			<?php if ( $merchant = TemplateHelper::getMerhantName( $item ) ): ?>
                <small class="text-muted title-case">
					<?php echo \esc_html( $merchant ); ?>
					<?php TemplateHelper::printShopInfo( $item ); ?>
                </small>
			<?php endif; ?>
        </div>
    </div>
    <div class="col-md-2 col-sm-2 col-xs-12 cegg-btn-cell hidden-xs">
        <div class="cegg-btn-row">
            <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"
                                                   class="btn btn-danger btn-block"><span><?php TemplateHelper::buyNowBtnText( true, $item, $btn_text ); ?></span></a>
        </div>
		<?php if ( $merchant = TemplateHelper::getMerhantName( $item ) ): ?>
            <div class="text-center">
                <small class="text-muted title-case">
					<?php echo \esc_html( $merchant ); ?>
					<?php TemplateHelper::printShopInfo( $item ); ?>
                </small>
            </div>
		<?php endif; ?>

    </div>
</div>
