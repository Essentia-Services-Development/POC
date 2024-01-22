<?php
defined( '\ABSPATH' ) || exit;

/*
  Name: Add all to cart button
 */

use ContentEgg\application\helpers\TemplateHelper;

if ( ! $btn_text ) {
	$btn_text = __( 'ADD ALL TO CART', 'content-egg-tpl' );
}

$url     = '';
$item    = reset( $items );
$locales = TemplateHelper::findAmazonLocales( $items );

?>

<div class="egg-container cegg-add-to-cart">

	<?php foreach ( $locales as $locale ): ?>

		<?php $url = TemplateHelper::generateAddAllToCartUrl( $items, $locale ); ?>
        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo $url; ?>"
                                               class="btn btn-danger"><?php TemplateHelper::buyNowBtnText( true, $item, $btn_text ); ?></a>

	<?php endforeach; ?>
</div>


