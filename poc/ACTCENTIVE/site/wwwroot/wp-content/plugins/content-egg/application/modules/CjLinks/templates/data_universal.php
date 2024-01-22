<?php
defined( '\ABSPATH' ) || exit;
/*
  Name: Universal
 */

__( 'Universal', 'content-egg-tpl' );

use ContentEgg\application\helpers\TemplateHelper;

?>

<?php
\wp_enqueue_style( 'egg-bootstrap' );
\wp_enqueue_style( 'egg-products' );
?>

<div class="egg-container egg-list egg-list-coupons">
	<?php if ( $title ): ?>
        <h3><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>

	<?php if ( $data = TemplateHelper::filterData( $items, 'linkType', 'Text Link', true ) ): ?>
        <div class="egg-listcontainer">
			<?php foreach ( $data as $item ): ?>
                <div class="row-products">
                    <div class="col-md-9 col-sm-9 col-xs-12 cegg-desc-cell">

                        <h4 class="cegg-no-top-margin">
                            <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
								<?php echo esc_html( $item['title'] ); ?>
                            </a>
                        </h4>

						<?php if ( $item['description'] ): ?>
                            <div class="small text-muted cegg-lineh-20"><?php echo esc_html( $item['description'] ); ?></div>
						<?php endif; ?>

						<?php if ( $item['endDate'] ): ?>
                            <span class="text-muted small"><em><?php esc_html_e( 'Ends:', 'content-egg-tpl' ); ?><?php echo esc_html(TemplateHelper::formatDate( $item['endDate'] )); ?></em></span>
						<?php endif; ?>
                    </div>
                    <div class="col-md-3 col-sm-3 col-xs-12 offer_price cegg-price-cell">
						<?php if ( $item['extra']['couponCode'] ): ?>
                            <div class="cegg-coupon-row cegg-mb10">
                                <span class="cegg-couponcode"><?php echo esc_html( $item['extra']['couponCode'] ); ?></span>
                            </div>
						<?php endif; ?>

                        <div class="cegg-btn-row cegg-mb10">
                            <a<?php TemplateHelper::printRel(); ?> target="_blank"
                                                                   title="<?php echo esc_attr( $item['extra']['advertiserSite'] ); ?>"
                                                                   href="<?php echo esc_url_raw($item['url']); ?>"
                                                                   class="btn btn-success"><?php TemplateHelper::couponBtnText( true, $item, $btn_text ); ?></a>
                        </div>
						<?php if ( $item['extra']['advertiserSite'] ): ?>
                            <div>
                                <img title="<?php echo esc_attr( $item['extra']['advertiserSite'] ); ?>"
                                     src="https://www.google.com/s2/favicons?domain=http://<?php echo esc_attr( $item['extra']['advertiserSite'] ); ?>"
                                     alt="<?php echo esc_attr( $item['extra']['advertiserName'] ); ?>"/>
                                <small><?php echo esc_html( $item['extra']['advertiserSite'] ); ?></small>
                            </div>
						<?php endif; ?>

                    </div>

                </div>
			<?php endforeach; ?>
        </div>
	<?php endif; ?>

	<?php if ( $data = TemplateHelper::filterData( $items, 'linkType', 'Banner', true ) ): ?>
        <div class="container-fluid">
			<?php $i = 0; ?>
            <div class="row">
				<?php foreach ( $data as $item ): ?>
                    <div class="col-md-6">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
                            <img src="<?php echo esc_attr( $item['img'] ); ?>"
                                 alt="<?php echo esc_attr( $item['title'] ); ?>" class="img-responsive"/>
                        </a>
                    </div>
					<?php
					$i ++;
					if ( $i % 2 == 0 ):
						?>
                        <div class="clearfix"></div>
					<?php endif; ?>
				<?php endforeach; ?>
            </div>
        </div>
	<?php endif; ?>

	<?php if ( $data = TemplateHelper::filterData( $items, 'linkType', array(
		'Text Link',
		'Banner'
	), true, true ) ): ?>
        <div class="container-fluid">
			<?php foreach ( $data as $item ): ?>
                <div class="row">
                    <div class="col-md-12">
						<?php echo wp_kses_post($item['extra']['linkHtml']); ?>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
	<?php endif; ?>

</div>