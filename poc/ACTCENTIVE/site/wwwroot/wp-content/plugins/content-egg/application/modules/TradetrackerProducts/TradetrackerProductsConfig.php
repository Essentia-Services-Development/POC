<?php

namespace ContentEgg\application\modules\TradetrackerProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AffiliatewindowConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class TradetrackerProductsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$options = array(
			'customerID'              => array(
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
			'passphrase'              => array(
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
			'affiliateSiteID'         => array(
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
			'subId'                   => array(
				'title'       => 'Reference',
				'description' => __( 'If you would like to have all transactions to be available in a custom report, you can add your own reference.', 'content-egg' ) . ' ' .
				                 __( 'Note that the maximum length of a reference is 255, and characters are limited to: a-z, A-Z, 0-9, tilde (~), dash (-), colon (:), period (.), comma (,) vertical bar (|) and asterisk (*).', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					'strip_tags',
				),
			),
			'locale'                  => array(
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
					'ar_AE' => 'ar_AE',
					'es_MX' => 'es_MX',
				),
				'default'          => 'en_GB',
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
				'default'     => 6,
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			'feedID'                  => array(
				'title'     => __( 'Feed ID', 'content-egg' ),
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
				),
				'section'   => 'default',
			),
			'feedCategoryName'        => array(
				'title'     => __( 'Feed Category Name', 'content-egg' ),
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
				),
				'section'   => 'default',
			),
			'campaignID'              => array(
				'title'     => __( 'Campaign ID', 'content-egg' ),
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
				),
				'section'   => 'default',
			),
			'campaignCategoryID'      => array(
				'title'     => __( 'Campaign Category ID', 'content-egg' ),
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
				),
				'section'   => 'default',
			),
			'priceFrom'               => array(
				'title'       => __( 'Price From', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'priceTo'                 => array(
				'title'       => __( 'Price To', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
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
			'stock_status'            => array(
				'title'            => __( 'Stock status', 'content-egg' ) . ' (beta)',
				'description'      => __( 'Set this status if the product is not found.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'unknown'      => 'Unknown',
					'out_of_stock' => 'Out of stock',
				),
				'default'          => 'unknown',
			),
		);

		$parent                             = parent::options();
		$parent['ttl_items']['default']     = 0;
		$parent['ttl_items']['description'] = __( 'Time in seconds for updating prices. 0 - never update.', 'content-egg' )
		                                      . ' ' . __( 'Experimental feature for this module.', 'content-egg' );

		return array_merge( $parent, $options );
	}

	public static function getCurrencyByLocale( $locale ) {
		$locales = array(
			'nl_BE' => 'EUR',
			'fr_BE' => 'EUR',
			'cs_CZ' => 'CZK',
			'da_DK' => 'DKK',
			'de_DE' => 'EUR',
			'et_EE' => 'EUR',
			'en_GB' => 'GBP',
			'es_ES' => 'EUR',
			'fr_FR' => 'EUR',
			'it_IT' => 'EUR',
			'hu_HU' => 'HUF',
			'nl_NL' => 'EUR',
			'nb_NO' => 'NOK',
			'de_AT' => 'EUR',
			'pl_PL' => 'PLN',
			'fi_FI' => 'EUR',
			'sv_SE' => 'SEK',
			'ru_RU' => 'RUB',
			'ar_AE' => 'AED',
			'es_MX' => 'MXN',
		);
		if ( isset( $locales[ $locale ] ) ) {
			return $locales[ $locale ];
		} else {
			return 'EUR';
		}
	}

	public function clearSubId( $id ) {
		return preg_replace( '/[^a-zA-Z0-9~-:\.\,\|\*]/', '', $id );

	}

}
