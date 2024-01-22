<?php

namespace ContentEgg\application\modules\Flipkart;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * FlipkartConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class FlipkartConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'tracking_id'             => array(
				'title'       => __( 'Affiliate Tracking ID', 'content-egg' ) . ' <span class="cegg_required">*</span>',
				'description' => __( 'Go to account  -> "API" -> "API Token"', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Affiliate Tracking ID" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'token'                   => array(
				'title'       => __( 'Token', 'content-egg' ) . ' <span class="cegg_required">*</span>',
				'description' => __( 'You access key API. Go to flipkart account -> "API" -> "API Token"', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Token" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'trackingParameters'      => array(
				'title'       => __( 'Tracking parameters', 'content-egg' ),
				'description' => __( 'Affiliate tracking parameters affExtParam1 and affExtParam2. For example: <em>affExtParam1=ABC&affExtParam2=123</em>', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'deeplink'                => array(
				'title'       => 'Deeplink',
				'description' => __( 'Set this option, if you want to send traffic to one of CPA-network with support of Flipkart and deeplink.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one search query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 10,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 10,
						'message' => __( 'The "Results" can not be more than 10.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for updates and autoblogging', 'content-egg' ),
				'description' => __( 'Number of results for automatic updates and autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 6,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 10,
						'message' => __( 'Field "Results for autoupdating" can not be more than 10.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'save_img'                => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'description_size'        => array(
				'title'       => __( 'Trim description', 'content-egg' ),
				'description' => __( 'Description size in characters (0 - do not cut)', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '300',
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
		);

		return array_merge( parent::options(), $optiosn );
	}

}
