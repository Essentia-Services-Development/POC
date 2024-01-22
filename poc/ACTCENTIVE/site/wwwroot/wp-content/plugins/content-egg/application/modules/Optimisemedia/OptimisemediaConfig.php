<?php

namespace ContentEgg\application\modules\Optimisemedia;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * OptimisemediaConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class OptimisemediaConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'api_key'                 => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'Follow: My Details -> Accout Details -> API Key', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "API Key" can not be empty', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'private_key'             => array(
				'title'       => 'Private Key <span class="cegg_required">*</span>',
				'description' => __( 'Follow: My Details -> Accout Details -> API Key', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Private Key" can not be empty', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'AffiliateID'             => array(
				'title'       => 'Affiliate ID <span class="cegg_required">*</span>',
				'description' => 'Take a look at any of your Optimise Tracking links - there will be a parameter called &AID= - this is your Affiliate ID',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Affiliate ID" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'AgencyID'                => array(
				'title'            => __( 'Agency', 'content-egg' ),
				'description'      => 'The OMG Agency you are accessing.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'1.'   => 'Optimise UK',
					'95.'  => 'Optimise India',
					'118.' => 'Optimise SE Asia',
					'12.'  => 'Optimise Poland',
					'142.' => 'Optimise Brazil',
				),
				'default'          => '95.',
				'section'          => 'default',
			),
			'Currency'                => array(
				'title'       => 'Currency <span class="cegg_required">*</span>',
				'description' => 'The 3 digit ISO standard currency code. eg. GBP, USD, INR, SGD, AUD, etc.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => 'INR',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Currency" can not be empty', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one search query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 15,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 500,
						'message' => __( 'The field "Results" can not be more than 500.', 'content-egg' ),
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
						'arg'     => 500,
						'message' => __( 'The field "Results" can not be more than 500.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'MID'                     => array(
				'title'       => 'Merchant ID',
				'description' => 'A MerchantID you want to filter the results by.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'MinPrice'                => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'MaxPrice'                => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'DiscountedOnly'          => array(
				'title'       => 'Discounted',
				'description' => 'Indicates whether to return Discounted products only',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
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
