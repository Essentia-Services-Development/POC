<?php

namespace ContentEgg\application\modules\Coupon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * CouponConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class CouponConfig extends AffiliateParserModuleConfig {

	public function options() {
		$options = array(
			'save_img'     => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
			'hide_expired' => array(
				'title'       => __( 'Hide expired', 'content-egg' ),
				'description' => __( 'Hide expired coupons.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
			'hide_future'  => array(
				'title'       => __( 'Hide future', 'content-egg' ),
				'description' => __( 'Hide future coupons.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
		);

		$parent = parent::options();
		unset( $parent['ttl'] );
		unset( $parent['update_mode'] );

		return array_merge( $parent, $options );
	}

}
