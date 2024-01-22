<?php

namespace ContentEgg\application\modules\Aliexpress;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AliexpressConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class AliexpressConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'api_key'                 => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'Special key to access Aliexpress API. You can get it <a target="_blank" href="http://portals.aliexpress.com/adcenter/api_setting.htm">here</a>.', 'content-egg' ),
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
			'tracking_id'             => array(
				'title'       => 'Tracking ID',
				'description' => __( 'Specify if you want to send traffic through the original affiliate program Aliexpress. You can find it <a target="_blank" href="http://portals.aliexpress.com/track_id_manage.htm">here</a>. This option must be set before saving products in database.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'deeplink'                => array(
				'title'       => 'Deeplink',
				'description' => __( 'Set this option, if you want to send traffic to one of CPA-network with support of aliexpress and deeplink.', 'content-egg' ),
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
						'arg'     => 40,
						'message' => __( 'The "Results" can not be more than 40.', 'content-egg' ),
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
						'arg'     => 40,
						'message' => __( 'The "Results" can not be more than 40.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'category_id'             => array(
				'title'            => __( 'Category ', 'content-egg' ),
				'description'      => __( 'Limit the search of goods by this category.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					0         => __( 'All categories', 'content-egg' ),
					3         => 'Apparel & Accessories',
					34        => 'Automobiles & Motorcycles',
					1501      => 'Baby Products',
					66        => 'Beauty & Health',
					7         => 'Computer & Networking',
					13        => 'Construction & Real Estate',
					44        => 'Consumer Electronics',
					100008578 => 'Customized Products',
					5         => 'Electrical Equipment & Supplies',
					502       => 'Electronic Components & Supplies',
					2         => 'Food',
					1503      => 'Furniture',
					200003655 => 'Hair & Accessories',
					42        => 'Hardware',
					15        => 'Home & Garden',
					6         => 'Home Appliances',
					200003590 => 'Industry & Business',
					36        => 'Jewelry & Watch',
					39        => 'Lights & Lighting',
					1524      => 'Luggage & Bags',
					21        => 'Office & School Supplies',
					509       => 'Phones & Telecommunications',
					30        => 'Security & Protection',
					322       => 'Shoes',
					200001075 => 'Special Category',
					18        => 'Sports & Entertainment',
					1420      => 'Tools',
					26        => 'Toys & Hobbies',
					1511      => 'Watches',
				),
				'default'          => 0,
				'section'          => 'default',
			),
			'high_quality_items'      => array(
				'title'       => __( 'Best quality products', 'content-egg' ),
				'description' => __( 'Only products with high sales, good user feedbacks', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'local_currency'          => array(
				'title'            => __( 'Currency', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'USD' => 'USD',
					'RUB' => 'RUB',
					'GBP' => 'GBP',
					'BRL' => 'BRL',
					'CAD' => 'CAD',
					'AUD' => 'AUD',
					'EUR' => 'EUR',
					'INR' => 'INR',
					'UAH' => 'UAH',
					'JPY' => 'JPY',
					'MXN' => 'MXN',
					'IDR' => 'IDR',
					'TRY' => 'TRY',
					'SEK' => 'SEK',
				),
				'default'          => 'USD',
			),
			'language'                => array(
				'title'            => __( 'Language', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'en' => 'en',
					'pt' => 'pt',
					'ru' => 'ru',
					'es' => 'es',
					'fr' => 'fr',
					'id' => 'id',
					'it' => 'it',
					'nl' => 'nl',
					'tr' => 'tr',
					'vi' => 'vi',
					'th' => 'th',
					'de' => 'de',
					'ko' => 'ko',
					'ja' => 'ja',
					'ar' => 'ar',
					'pl' => 'pl',
					'he' => 'he',
				),
				'default'          => 'en',
			),
			'commission_rate_from'    => array(
				'title'       => __( 'Minimal commission', 'content-egg' ),
				'description' => __( 'Minimal commission (without %). Example, 3', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'original_price_from'     => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => __( 'Must be set in USD. Example, 12.34', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'original_price_to'       => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => __( 'Must be set in USD. Example, 56.78', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'volume_from'             => array(
				'title'       => __( 'Minimal sales', 'content-egg' ),
				'description' => __( 'Minimal number of partner sales for last month. Example, 123', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'volume_to'               => array(
				'title'       => __( 'Maximal sales', 'content-egg' ),
				'description' => __( 'Max number of partner sales for last month. Example, 456', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'sort'                    => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                   => __( 'Default', 'content-egg' ),
					'orignalPriceUp'     => __( 'Price low to high', 'content-egg' ),
					'orignalPriceDown'   => __( 'Price high to low', 'content-egg' ),
					'sellerRateDown'     => __( 'Seller rating', 'content-egg' ),
					'commissionRateUp'   => __( 'Commission from low to high', 'content-egg' ),
					'commissionRateDown' => __( 'Commission from high to low', 'content-egg' ),
					'volumeDown'         => __( 'Sales', 'content-egg' ),
					'validTimeUp'        => __( 'Lifetime from low to high', 'content-egg' ),
					'validTimeDown'      => __( 'Lifetime from high to low', 'content-egg' ),
				),
				'default'          => '',
			),
			'start_credit_score'      => array(
				'title'       => __( 'Seller rating', 'content-egg' ),
				'description' => __( 'Minimal seller rating, for example 12', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					'absint',
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
		);

		return array_merge( parent::options(), $optiosn );
	}

}
