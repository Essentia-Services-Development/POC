<?php

namespace ContentEgg\application\modules\AE;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AEConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AEConfig extends AffiliateParserModuleConfig {

	public function options() {
		$options = array(
			'deeplink'                => array(
				'title'       => __( 'Affiliate link', 'content-egg' ),
				'description' => __( 'Set Deeplink for one of CPA-networks. For direct affiliate programs you can use parameter as <em>partner_id=12345</em>, or make link as template, for example, <em>{{url}}/partner_id-12345/</em>. Another example is   https://ad.admitad.com/g/g8f0qmlavfa/?ulp={{url_encoded}}. {{url}} and {{url_encoded}} - will be replaced by product url. If product url is after affiliate url - use {{url_encoded}}', 'content-egg' ),
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
				'default'     => 3,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 50,
						'message' => __( 'The field "Results" can not be more than 50.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for updates and autoblogging', 'content-egg' ),
				'description' => __( 'Number of results for automatic updates and autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 3,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 50,
						'message' => __( 'The field "Results" can not be more than 50.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'reviews_as_comments'     => array(
				'title'       => __( 'Reviews as post comments', 'content-egg' ),
				'description' => __( 'Save user reviews as post comments.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'save_img'                => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on local server.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'show_small_logos'        => array(
				'title'            => __( 'Small logos', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'true'  => __( 'Show small logos', 'content-egg' ),
					'false' => __( 'Hide small logos', 'content-egg' ),
				),
				'default'          => 'true',
			),
			'show_large_logos'        => array(
				'title'            => __( 'Large logos', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'true'  => __( 'Show large logos', 'content-egg' ),
					'false' => __( 'Hide large logos', 'content-egg' ),
				),
				'default'          => 'true',
			),
		);

		$parent                         = parent::options();
		$parent['ttl']['default']       = 4320000;
		$parent['ttl_items']['default'] = 2592000;

		return array_merge( $parent, $options );
	}

}
