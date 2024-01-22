<?php

namespace ContentEgg\application\modules\TradedoublerCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * TradedoublerCouponsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class TradedoublerCouponsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'token'                   => array(
				'title'       => 'Token <span class="cegg_required">*</span>',
				'description' => __( 'Access key for Tradedoubler Coupons API. You can get it <a href="https://login.tradedoubler.com/publisher/aManageTokens.action">here</a>.', 'content-egg' ),
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
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one search query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 10,
				'validator'   => array(
					'trim',
					'absint',
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
				),
				'section'     => 'default',
			),
			'programId'               => array(
				'title'       => 'Program ID',
				'description' => 'Primary key of the program the voucher corresponds to.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'voucherTypeId'           => array(
				'title'            => 'Voucher Type',
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'' => __( 'Any', 'content-egg' ),
					1  => 'Voucher',
					2  => 'Discount',
					3  => 'Free article',
					4  => 'Free shipping',
					5  => 'Raffle',
				),
				'default'          => '  ',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'siteSpecific'            => array(
				'title'       => 'Exclusive vouchers',
				'description' => 'Set to True if you only want to get your exclusive voucher codes.',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'languageId'              => array(
				'title'       => 'Language',
				'description' => 'Enter an <a href="http://www.loc.gov/standards/iso639-2/php/code_list.php">ISO 639-1</a> code to filter on language.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
		);
		$parent  = parent::options();
		unset( $parent['featured_image'] );

		return array_merge( $parent, $optiosn );
	}

}
