<?php

namespace ContentEgg\application\modules\Ebay;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * EbayConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class EbayConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn                  = array(
			'app_id'                  => array(
				'title'       => 'App ID (Client ID) <span class="cegg_required">*</span>',
				'description' => __( "Your application's OAuth credentials.", 'content-egg' ) . ' ' . sprintf( __( 'You can get it in <a target="_blank" href="%s">eBay Developers Program</a>.', 'content-egg' ), 'http://developer.ebay.com/join' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'App ID (Client ID)' ),
					),
				),
				'section'     => 'default',
			),
			'cert_id'                 => array(
				'title'       => 'Cert ID (Client Secret) <span class="cegg_required">*</span>',
				'description' => __( "Your application's OAuth credentials.", 'content-egg' ) . ' ' . sprintf( __( 'You can get it in <a target="_blank" href="%s">eBay Developers Program</a>.', 'content-egg' ), 'http://developer.ebay.com/join' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Cert ID (Client Secret)' ),
					),
				),
				'section'     => 'default',
			),
			'tracking_id'             => array(
				'title'       => 'EPN Campaign ID',
				'description' => __( 'This is connection with partner program EPN. Campaign ID is valid for all programs which were approved for you on EPN. If you leave this field blank - you will not get commissions from sales.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'custom_id'               => array(
				'title'       => __( 'EPN Custom ID (chanel)', 'content-egg' ),
				'description' => __( 'Any word, for example, name of domain. Custom ID will be included in sale report on EPN, so, you can additionally check your traffic. ', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'ebayin_aff_id'           => array(
				'title'       => __( 'Ebay.in Affilite ID', 'content-egg' ),
				'description' => __( 'For eBay India\'s Affiliate program only. Go to <a href="https://ebayindia.hasoffers.com/publisher/#!/account">Ebay Hasoffers Dashboard</a> and find your Affiliate ID.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'skimlinks_id'            => array(
				'title'       => __( 'Skimlinks Site ID', 'content-egg' ),
				'description' => __( 'Set this if you want to direct traffic over <a href="http://www.keywordrush.com/go/skimlinks">Skimlinks</a>. Id for domain you can find <a href="https://hub.skimlinks.com/account">here</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'viglink_id'              => array(
				'title'       => __( 'Viglink ID', 'content-egg' ),
				'description' => __( 'Set this if you want to direct traffic over <a href="http://www.keywordrush.com/go/viglink">Viglink</a>. Id for domain you can find <a href="http://www.viglink.com/install">here</a>. Id is the same for all domains', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'deeplink'                => array(
				'title'       => __( 'Deeplink', 'content-egg' ),
				'description' => __( 'Set Deeplink for one of CPA-networks. You can use parameter as <em>partner_id=12345</em>, or make link as template, for example, <em>{{url}}/partner_id-12345/</em>. Another example is   https://ad.admitad.com/g/g8f0qmlavfa/?ulp={{url_encoded}}. {{url}} and {{url_encoded}} - will be replaced by product url. If product url is after affiliate url - use {{url_encoded}}', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'global_id'               => array(
				'title'            => __( 'Locale', 'content-egg' ),
				'description'      => __( 'Local site of Ebay. For each local site you must have separate registration in affiliate program.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => self::getLocalesList(),
				'default'          => self::getDefaultLocale(),
				'section'          => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one search query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 12,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 100,
						'message' => __( 'Field "Results" can not be more than 100.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for updates and autoblogging', 'content-egg' ),
				'description' => __( 'Number of results for automatic updates and autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 9,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 100,
						'message' => __( 'Field "Results for autoupdating" can not be more than 100.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'sort_order'              => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'BidCountFewest'           => 'BidCountFewest',
					'BidCountMost'             => 'BidCountMost',
					'BestMatch'                => 'BestMatch',
					'CurrentPriceHighest'      => 'CurrentPriceHighest',
					'EndTimeSoonest'           => 'EndTimeSoonest',
					'PricePlusShippingHighest' => 'PricePlusShippingHighest',
					'PricePlusShippingLowest'  => 'PricePlusShippingLowest',
					'StartTimeNewest'          => 'StartTimeNewest',
					'WatchCountDecreaseSort'   => 'WatchCountDecreaseSort'
				),
				'default'          => 'BestMatch',
				'section'          => 'default',
			),
			'end_time_to'             => array(
				'title'       => __( 'Ending time', 'content-egg' ),
				'description' => __( 'Lifetime of lots in seconds. Only lots which will be closed not later than the specified time will be chosen.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'category_id'             => array(
				'title'       => __( 'Category ', 'content-egg' ),
				'description' => __( 'Id of category for searching. Id of categories you can find in URL of category on <a href="http://www.ebay.com/sch/allcategories/all-categories">this page</a>. You can set maximum 3 categories separated with comma. Example, "2195,2218,20094".', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'description_search'      => array(
				'title'       => __( 'Search in description', 'content-egg' ),
				'description' => __( 'Include description of product in searching. This will take more time, than searching only by title.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'search_logic'            => array(
				'title'            => __( 'Searching logic', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'AND'   => 'AND logic for multiple keywords',
					'OR'    => 'OR logic for multiple keywords',
					'EXACT' => 'Exact sequence of words'
				),
				'default'          => 'AND',
				'section'          => 'default',
			),
			'condition'               => array(
				'title'            => __( 'Product condition', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''               => 'Any',
					'1000,'          => 'New',
					'1000,2000,2500' => 'New + Refurbished',
					'3000,'          => 'Used',
					'3000,2000,2500' => 'Used + Refurbished',
					'7000,'          => 'For parts or not working',
					'2000,'          => 'Manufacturer Refurbished',
					'2500,'          => 'Seller Refurbished',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'exclude_category'        => array(
				'title'       => __( 'Exclude category', 'content-egg' ),
				'description' => __( 'Id of category, which must be excluded while searching. Id of categories you can find in URL of category on <a href="http://www.ebay.com/sch/allcategories/all-categories">this page</a>. You can set maximum 25 categories separated with comma. Example, "2195,2218,20094".', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'feedback_score_min'      => array(
				'title'            => __( 'Minimal seller rating', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''         => 'Any',
					'10.'      => 'Yellow star - 10 ratings',
					'50.'      => 'Blue star - 50 rating',
					'100.'     => 'Turquoise star - 100 rating',
					'500.'     => 'Purple star - 500 rating',
					'1000.'    => 'Red star - 1,000 rating',
					'5000.'    => 'Green star - 5,0000 ratings',
					'10000.'   => 'Yellow shooting star - 10,000',
					'25000.'   => 'Turquoise shooting star - 25000',
					'50000.'   => 'Purple shooting star - 50,000',
					'100000.'  => 'Red shooting star 100,000',
					'500000.'  => 'Green shooting star - 500,000',
					'1000000.' => 'Silver shooting star - 1,000,000'
				),
				'default'          => '',
				'section'          => 'default',
			),
			'best_offer_only'         => array(
				'title'       => __( 'Best Offer', 'content-egg' ),
				'description' => __( 'Only  "Best Offer" lots.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'featured_only'           => array(
				'title'       => __( 'Featured', 'content-egg' ),
				'description' => __( 'Only "Featured" lots.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'free_shipping_only'      => array(
				'title'       => __( 'Free Shipping', 'content-egg' ),
				'description' => __( 'Only lots with free delivery', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'local_pickup_only'       => array(
				'title'       => __( 'Local Pickup', 'content-egg' ),
				'description' => __( 'Only lots with "local pickup" option.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'get_it_fast_only'        => array(
				'title'       => __( 'Get It Fast', 'content-egg' ),
				'description' => __( 'Only "Get It Fast" lots.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'top_rated_seller_only'   => array(
				'title'       => __( 'Top-rated seller', 'content-egg' ),
				'description' => __( 'Only products from Top-rated "Top-rated" vendors.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'hide_dublicate_items'    => array(
				'title'       => __( 'Hide duplicates', 'content-egg' ),
				'description' => __( 'Filter similar lots', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
				'section'     => 'default',
			),
			'listing_type'            => array(
				'title'            => __( 'Type of auction', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                          => 'All',
					'Auction'                   => 'Auction',
					'AuctionWithBIN'            => 'Auction with BIN',
					'FixedPrice'                => 'Fixed Price',
					'FixedPrice,AuctionWithBIN' => 'Fixed Price + Auction with BIN',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'max_bids'                => array(
				'title'       => __( 'Maximum bids', 'content-egg' ),
				'description' => __( 'Example, 10', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'min_bids'                => array(
				'title'       => __( 'Minimum bids', 'content-egg' ),
				'description' => __( 'Example, 3', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'max_price'               => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => __( 'Example, 300.50', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'min_price'               => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => __( 'Example, 10.98', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'payment_method'          => array(
				'title'            => __( 'Payment options', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array( '' => 'Any', 'PayPal' => 'PayPal' ),
				'default'          => '',
				'section'          => 'default',
			),
			'available_to'            => array(
				'title'       => __( 'Available to', 'content-egg' ),
				'description' => __( 'Limits items to those available to the specified country only. Expects the two-letter ISO 3166 country code.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'located_in'              => array(
				'title'       => __( 'Located In', 'content-egg' ),
				'description' => __( 'Expects the two-letter ISO 3166 country code to indicate the country where the item is located. Item filter AvailableTo cannot be used together with item filter LocatedIn.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'seller'                  => array(
				'title'       => __( 'Seller', 'content-egg' ),
				'description' => __( 'Specify one or more seller names separated by comma. Search results will include items from the specified sellers only.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'get_description'         => array(
				'title'       => __( 'Get description', 'content-egg' ),
				'description' => __( 'Get description of product. This takes more requests for Ebay API and slow down searching. Description will be requested only for 20 first products for one searching', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'description_size'        => array(
				'title'       => __( 'Size of description', 'content-egg' ),
				'description' => __( 'The maximum size of the item description. 0 - do not cut.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 2000,
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
		$parent                   = parent::options();
		$parent['ttl']['default'] = 28800;
		$optiosn                  = array_merge( $parent, $optiosn );

		return self::moveRequiredUp( $optiosn );
	}

	public static function getLocalesList() {
		// @link: https://developer.ebay.com/DevZone/merchandising/docs/Concepts/SiteIDToGlobalID.html
		// aff programs available
		return array(
			'EBAY-US'    => 'eBay United States',
			'EBAY-IE'    => 'eBay Ireland',
			'EBAY-AT'    => 'eBay Austria',
			'EBAY-AU'    => 'eBay Australia',
			'EBAY-FRBE'  => 'eBay Belgium (French)',
			'EBAY-NLBE'  => 'eBay Belgium (Dutch)',
			'EBAY-ENCA'  => 'eBay Canada (English)',
			'EBAY-FRCA'  => 'eBay Canada (French)',
			'EBAY-FR'    => 'eBay France',
			'EBAY-DE'    => 'eBay Germany',
			'EBAY-IT'    => 'eBay Italy',
			'EBAY-ES'    => 'eBay Spain',
			'EBAY-CH'    => 'eBay Switzerland',
			'EBAY-GB'    => 'eBay UK',
			'EBAY-NL'    => 'eBay Netherlands',
			'EBAY-MOTOR' => 'eBay Motors',
			'EBAY-IN'    => 'eBay India',
			'EBAY-HK'    => 'eBay Hong Kong',
			'EBAY-MY'    => 'eBay Malaysia',
			'EBAY-PH'    => 'eBay Philippines',
			'EBAY-PL'    => 'eBay Poland',
			'EBAY-SG'    => 'eBay Singapore',
		);
	}

	public static function getDefaultLocale() {
		return 'EBAY-US';
	}

	public static function getCurrencyByGlobalId( $global_id ) {
		//@link: http://developer.ebay.com/devzone/finding/callref/Enums/currencyIdList.html
		$currency = array(
			'EBAY-US'    => 'USD',
			'EBAY-IE'    => 'EUR',
			'EBAY-AT'    => 'EUR',
			'EBAY-AU'    => 'AUD',
			'EBAY-FRBE'  => 'EUR',
			'EBAY-NLBE'  => 'EUR',
			'EBAY-ENCA'  => 'CAD',
			'EBAY-FRCA'  => 'CAD',
			'EBAY-FR'    => 'EUR',
			'EBAY-DE'    => 'EUR',
			'EBAY-IT'    => 'EUR',
			'EBAY-ES'    => 'EUR',
			'EBAY-CH'    => 'CHF',
			'EBAY-GB'    => 'GBP',
			'EBAY-NL'    => 'EUR',
			'EBAY-PL'    => 'PLN',
			'EBAY-MOTOR' => 'USD',
			'EBAY-IN'    => 'INR',
			'EBAY-HK'    => 'HKD',
			'EBAY-MY'    => 'MYR',
			'EBAY-PH'    => 'PHP',
			'EBAY-SG'    => 'SGD',
		);
		if ( isset( $currency[ $global_id ] ) ) {
			return $currency[ $global_id ];
		} else {
			return 'USD';
		}
	}

	/**
	 * Ebay возвращает время по гринвичу.
	 * Метод делаем коррекцию для текущего филиала ebay
	 *
	 * @param string $global_id
	 *
	 * @return integer
	 */
	public static function timeZoneCorrection( $global_id ) {
		switch ( $global_id ) {
			case 'EBAY-US':
			case 'EBAY-MOTOR':
				$countryhour = - 7;  // US, PDT(-7) - лето, PST(-8) зима
				break;
			case 'EBAY-ENCA':
			case 'EBAY-FRCA':
				$countryhour = - 4;  // CA, EDT(-4) - лето, EST(-5) - зима
				break;
			case 'EBAY-GB':
			case 'EBAY-IE':
				$countryhour = 1;   // UK, BST(+1) - лето, GMT(0) - зима
				break;
			case 'EBAY-FR':
			case 'EBAY-ES':
			case 'EBAY-FRBE':
			case 'EBAY-NLBE':
			case 'EBAY-DE':
			case 'EBAY-CH':
			case 'EBAY-NL':
			case 'EBAY-IT':
			case 'EBAY-AT':
			case 'EBAY-PL':
				$countryhour = 2;   // DE, MESZ(+2) - лето, MEZ(1) - зима
				break;
			case 'EBAY-AU':
				$countryhour = 10;   // AU, AEST(+10) - зима, AEDT(+11) - лето
				break;
			case 'EBAY-IN':
				$countryhour = 5.5;   // IN, IST(+5.5) - зима,лето
				break;
			case 'EBAY-HK':
			case 'EBAY-MY':
			case 'EBAY-PH':
			case 'EBAY-SG':
				$countryhour = 8;   // HK, MY GMT+8 - зима,лето
				break;

			default:
				$countryhour = 0;
		}

		if ( $global_id == 'EBAY-AU' ) {// австралия и лето
			if ( date( "I" ) ) {
				$countryhour += 1;
			}
		} elseif ( ! in_array( $global_id, array( 'EBAY-IN', 'EBAY-HK', 'EBAY-MY', 'EBAY-PH', 'EBAY-SG' ) ) ) {
			// зима? - 1
			if ( ! date( "I" ) ) {
				$countryhour -= 1;
			}
		}

		$zoneseconds = 3600 * $countryhour;

		return $zoneseconds;
	}

	/**
	 * Возвращает часовой пояс для филиала ebay
	 *
	 * @param string $global_id
	 *
	 * @return string
	 */
	public static function getTimeZone( $global_id ) {
		if ( ! date( "I" ) ) {
			$winter = true;
		} else {
			$winter = false;
		}
		switch ( $global_id ) {
			case 'EBAY-US':
				if ( $winter ) {
					return 'PST';
				} else {
					return 'PDT';
				}
			case 'EBAY-MOTOR':
				if ( $winter ) {
					return 'PST';
				} else {
					return 'PDT';
				}
			case 'EBAY-IT':
				if ( $winter ) {
					return 'CEST';
				} else {
					return 'CET';
				}
			case 'EBAY-ENCA':
				if ( $winter ) {
					return 'EST';
				} else {
					return 'EDT';
				}
			case 'EBAY-GB':
				if ( $winter ) {
					return 'GMT';
				} else {
					return 'BST';
				}
			case 'EBAY-FR':
			case 'EBAY-PL':
				if ( $winter ) {
					return 'CEST';
				} else {
					return 'CET';
				}
			case 'EBAY-ES':
				if ( $winter ) {
					return 'CEST';
				} else {
					return 'CET';
				}
			case 'EBAY-DE':
				if ( $winter ) {
					return 'MEZ';
				} else {
					return 'MESZ';
				}
			case 'EBAY-IN':
				return 'IST';
			case 'EBAY-AU':
				if ( $winter ) {
					return 'AEDT';
				} else {
					return 'AEST';
				}

			case 'EBAY-HK':
				return 'HKT';
			case 'EBAY-MY':
				return 'MYT';
			case 'EBAY-PH':
				return 'PHT';
			case 'EBAY-SG':
				return 'SGT';
		}

		return '';
	}

	public static function getDomainByGlobalId( $global_id ) {
		$domains = array(
			'EBAY-US'    => 'ebay.com',
			'EBAY-IE'    => 'ebay.ie',
			'EBAY-AT'    => 'ebay.at',
			'EBAY-AU'    => 'ebay.com.au',
			'EBAY-FRBE'  => 'ebay.au',
			'EBAY-NLBE'  => 'ebay.be',
			'EBAY-ENCA'  => 'ebay.ca',
			'EBAY-FRCA'  => 'ebay.ca',
			'EBAY-FR'    => 'ebay.fr',
			'EBAY-DE'    => 'ebay.de',
			'EBAY-IT'    => 'ebay.it',
			'EBAY-ES'    => 'ebay.es',
			'EBAY-CH'    => 'ebay.ch',
			'EBAY-GB'    => 'ebay.co.uk',
			'EBAY-NL'    => 'ebay.nl',
			'EBAY-IN'    => 'ebay.in',
			'EBAY-MOTOR' => 'ebay.com',
			'EBAY-PL'    => 'ebay.pl',
			'EBAY-HK'    => 'ebay.com.hk',
			'EBAY-MY'    => 'ebay.com.my',
			'EBAY-PH'    => 'ebay.ph',
			'EBAY-SG'    => 'ebay.com.sg',
		);
		if ( isset( $domains[ $global_id ] ) ) {
			return $domains[ $global_id ];
		} else {
			return 'ebay.com';
		}
	}

	/*
	 * @link: https://partnerhelp.ebay.com/helpcenter/knowledgebase/Tracking-Links-Overview/
	 *
	 */

	public static function getDomainByRotationId( $rotation_id ) {
		$domains = array(
			'711-53200-19255-0'   => 'ebay.com',
			'5282-53468-19255-0'  => 'ebay.ie',
			'5221-53469-19255-0'  => 'ebay.at',
			'705-53470-19255-0'   => 'ebay.com.au',
			'1553-53471-19255-0'  => 'ebay.be',
			'706-53473-19255-0'   => 'ebay.ca',
			'709-53476-19255-0'   => 'ebay.fr',
			'707-53477-19255-0'   => 'ebay.de',
			'724-53478-19255-0'   => 'ebay.it',
			'1185-53479-19255-0'  => 'ebay.es',
			'5222-53480-19255-0'  => 'ebay.ch',
			'710-53481-19255-0'   => 'ebay.co.uk',
			'1346-53482-19255-0'  => 'ebay.nl',
			'4908-226936-19255-0' => 'ebay.pl',
		);

		if ( isset( $domains[ $rotation_id ] ) ) {
			return $domains[ $rotation_id ];
		} else {
			return false;
		}
	}

}
