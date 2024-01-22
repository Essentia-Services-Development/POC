<?php

namespace ContentEgg\application\modules\TradetrackerCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * TradetrackerCouponsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class TradetrackerCouponsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'customerID'                   => array(
				'title'       => 'Customer ID <span class="cegg_required">*</span>',
				'description' => __( 'You can find your Customer ID and Passphrase by logging onto your TradeTracker account and navagating to "Creatives -> <a href="https://affiliate.tradetracker.com/webService/index">Web Services</a>". You may need to request access first by clicking the "request access" link.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Username' ),
					),
				),
				'section'     => 'default',
			),
			'passphrase'                   => array(
				'title'     => 'Passphrase <span class="cegg_required">*</span>',
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Password' ),
					),
				),
				'section'   => 'default',
			),
			'affiliateSiteID'              => array(
				'title'       => 'Affiliate Site ID <span class="cegg_required">*</span>',
				'description' => __( 'Login into your TradeTracker control panel. Click on "<a href="https://affiliate.tradetracker.com/customerSite/list">My Sites</a>" in the Account menu. The ID (without #) that is assigned to your website is your Affiliate Site ID.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Affiliate Site ID' ),
					),
				),
				'section'     => 'default',
			),
			//http://ws.tradetracker.com/soap/affiliate?wsdl
			'locale'                       => array(
				'title'            => __( 'Locale', 'content-egg' ),
				'description'      => __( 'Your TradeTracker locale.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'nl_BE' => 'nl_BE',
					'fr_BE' => 'fr_BE',
					'cs_CZ' => 'cs_CZ',
					'da_DK' => 'da_DK',
					'de_DE' => 'de_DE',
					'et_EE' => 'et_EE',
					'en_GB' => 'en_GB',
					'es_ES' => 'es_ES',
					'fr_FR' => 'fr_FR',
					'it_IT' => 'it_IT',
					'hu_HU' => 'hu_HU',
					'nl_NL' => 'nl_NL',
					'nb_NO' => 'nb_NO',
					'de_AT' => 'de_AT',
					'pl_PL' => 'pl_PL',
					'fi_FI' => 'fi_FI',
					'sv_SE' => 'sv_SE',
					'ru_RU' => 'ru_RU',
					'pt_BR' => 'pt_BR',
					'es_MX' => 'es_MX',
				),
				'default'          => 'en_GB',
			),
			'entries_per_page'             => array(
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
			'entries_per_page_update'      => array(
				'title'       => __( 'Results for updates and autoblogging', 'content-egg' ),
				'description' => __( 'Number of results for automatic updates and autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 6,
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			'itemsType'                    => array(
				'title'            => __( 'Items type', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'voucher' => __( 'Vouchers', 'content-egg' ),
					'offer'   => __( 'Offers', 'content-egg' ),
					'text'    => __( 'Text Links', 'content-egg' ),
				),
				'default'          => 'voucher',
				'metaboxInit'      => true,
			),
			'materialBannerDimensionID'    => array(
				'title'     => __( 'Banner Dimension ID', 'content-egg' ),
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
				),
				'section'   => 'default',
			),
			'campaignID'                   => array(
				'title'     => __( 'Campaign ID', 'content-egg' ),
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
				),
				'section'   => 'default',
			),
			'campaignCategoryID'           => array(
				'title'     => __( 'Campaign Category ID', 'content-egg' ),
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
				),
				'section'   => 'default',
			),
			'includeUnsubscribedCampaigns' => array(
				'title'       => __( 'Unsubscribed campaigns', 'content-egg' ),
				'description' => __( 'Include unsubscribed campaigns.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'materialBannerDimensionID'    => array(
				'title'     => __( 'Banner Dimension ID', 'content-egg' ),
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
				),
				'section'   => 'default',
			),
			'save_img'                     => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
		);

		return array_merge( parent::options(), $optiosn );
	}

}
