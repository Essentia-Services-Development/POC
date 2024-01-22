<?php

namespace ContentEgg\application\modules\Amazon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;
use ContentEgg\application\libs\amazon\AmazonLocales;

/**
 * AmazonConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AmazonConfig extends AffiliateParserModuleConfig {

	public function options() {
		$options = array(
			'access_key_id'           => array(
				'title'       => 'Access Key <span class="cegg_required">*</span>',
				'description' => sprintf( __( 'For information about getting an Access Key, see <a target="_blank" href="%s">Register for Product Advertising API</a>.', 'content-egg' ), 'https://webservices.amazon.com/paapi5/documentation/register-for-pa-api.html' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Access Key" can not be empty', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'secret_access_key'       => array(
				'title'       => 'Secret Key <span class="cegg_required">*</span>',
				'description' => sprintf( __( 'A key that is used in conjunction with the Access Key ID to cryptographically sign an API request. To retrieve your Secret Access Key, refer to <a target="_blank" href="%s">Register for Product Advertising API</a>.', 'content-egg' ), 'https://webservices.amazon.com/paapi5/documentation/register-for-pa-api.html' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Secret Key" can not be empty.', 'content-egg' ),
					),
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'exact_length' ),
						'arg'     => 40,
						'message' => sprintf( __( 'The field "%s" must have an exact length of value: %d.', 'content-egg' ), 'Secret Key', 40 ),
					),
				),
				'section'     => 'default',
			),
			'associate_tag'           => array(
				'title'       => __( 'Default Associate Tag', 'content-egg' ) . ' <span class="cegg_required">*</span>',
				'description' => __( 'An alphanumeric token that uniquely identifies you as an Associate. To obtain an Associate Tag, refer to <a target="_blank" href="https://webservices.amazon.com/paapi5/documentation/troubleshooting/sign-up-as-an-associate.html">Becoming an Associate</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Tracking ID" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'locale'                  => array(
				'title'            => __( 'Default locale', 'content-egg' ) . '<span class="cegg_required">*</span>',
				'description'      => __( 'Your Amazon Associates tag works only in the locale in which you register. If you want to be an Amazon Associate in more than one locale, you must register separately for each locale.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => self::getLocalesList(),
				'default'          => self::getDefaultLocale(),
				'section'          => 'default',
			),
			'forced_urls_update'      => array(
				'title'       => __( 'Forced links update', 'content-egg' ),
				'description' => __( 'Override/update existing links with new Tracking ID.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one search query.', 'content-egg' ) . ' ' .
				                 __( 'It needs a bit more time to get more than 10 results in one request', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 10,
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
				'title'       => __( 'Results for updates', 'content-egg' ),
				'description' => __( 'Number of results for automatic updates and autoblogging.', 'content-egg' ) . ' ' .
				                 __( 'It needs a bit more time to get more than 10 results in one request.', 'content-egg' ),
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
			'link_type'               => array(
				'title'            => __( 'Link type', 'content-egg' ),
				'description'      => __( 'Type of partner links. Know more about amazon <a target="_blank" href="https://affiliate-program.amazon.com/gp/associates/help/t2/a11">90 day cookie</a>.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'product'     => 'Product page',
					'add_to_cart' => 'Add to cart',
				),
				'default'          => 'product',
				'section'          => 'default',
			),
			'search_index'            => array(
				'title'       => __( 'Search Index', 'content-egg' ),
				'description' => sprintf( __( 'Indicates the product category to search. SearchIndex values differ by marketplace. For list of search index values, refer <a target="_blank" href="%s">Locale Reference</a>.', 'content-egg' ), 'https://webservices.amazon.com/paapi5/documentation/locale-reference.html' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'SortBy'                  => array(
				'title'            => __( 'Sort', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                   => __( 'Default (recommended)', 'content-egg' ),
					'AvgCustomerReviews' => 'Avg Customer Reviews',
					'Featured'           => 'Featured',
					'NewestArrivals'     => 'Newest Arrivals',
					'Price:HighToLow'    => 'Price: High To Low',
					'Price:LowToHigh'    => 'Price: Low To High',
					'Relevance'          => 'Relevance',
				),
				'default'          => '',
			),
			'brouse_node'             => array(
				'title'       => __( 'Browse Node ID', 'content-egg' ),
				'description' => __( 'A unique ID assigned by Amazon that identifies a product category/sub-category.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'title'                   => array(
				'title'       => __( 'Search in Title', 'content-egg' ),
				'description' => __( 'Search for product titles only.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'Merchant'                => array(
				'title'            => __( 'Merchant', 'content-egg' ),
				'description'      => __( 'Filters search results to return items having at least one offer sold by target merchant.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'All'    => __( 'List Offers from all Merchants', 'content-egg' ),
					'Amazon' => __( 'List Offers only from Amazon', 'content-egg' ),
				),
				'default'          => '',
			),
			'minimum_price'           => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => __( 'Filters search results to items with at least one offer price above the specified value.', 'content-egg' ) . ' ' .
				                 __( 'For example, 31.41', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				//'metaboxInit' => true,
			),
			'maximum_price'           => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => __( 'Filters search results to items with at least one offer price below the specified value.', 'content-egg' ) . ' ' .
				                 __( 'For example, 32.41', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				//'metaboxInit' => true,
			),
			'min_percentage_off'      => array(
				'title'            => __( 'Minimum saving percent', 'content-egg' ),
				'description'      => __( 'Filters search results to items with at least one offer having saving percentage above the specified value.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''    => __( 'Any', 'content-egg' ),
					'5%'  => '5%',
					'10%' => '10%',
					'15%' => '15%',
					'20%' => '20%',
					'25%' => '25%',
					'30%' => '30%',
					'35%' => '35%',
					'40%' => '40%',
					'45%' => '45%',
					'50%' => '50%',
					'60%' => '60%',
					'70%' => '70%',
					'80%' => '80%',
					'90%' => '90%',
					'95%' => '95%',
				),
				'default'          => '',
				//'metaboxInit' => true,
			),
			'Condition'               => array(
				'title'            => __( 'Condition', 'content-egg' ),
				'description'      => __( 'The condition parameter filters offers by condition type. For example, Condition:Used will return items having atleast one offer of Used condition type.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''            => __( 'Any', 'content-egg' ),
					'New'         => __( 'New', 'content-egg' ),
					'Used'        => __( 'Used', 'content-egg' ),
					'Collectible' => __( 'Collectible', 'content-egg' ),
					'Refurbished' => __( 'Refurbished', 'content-egg' ),
				),
				'default'          => '',
			),
			'CurrencyOfPreference'    => array(
				'title'       => __( 'Currency of preference', 'content-egg' ),
				'description' => sprintf( __( 'Currency of preference in which the prices information should be returned in response. By default the prices are returned in the default currency of the marketplace. Expected currency code format is the ISO 4217 currency code (i.e. USD, EUR etc.). For information on default currency and valid currencies for a marketplace, refer <a target="_blank" href="%s">Locale Reference</a>.', 'content-egg' ), 'https://webservices.amazon.com/paapi5/documentation/locale-reference.html#topics' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'default'     => '',
			),
			'LanguagesOfPreference'   => array(
				'title'       => __( 'Languages of preference', 'content-egg' ),
				'description' => sprintf( __( 'Languages in order of preference in which the item information should be returned in response. By default the item information is returned in the default language of the marketplace. Expected locale format is the ISO 639 language code followed by underscore followed by the ISO 3166 country code (i.e. en_US, fr_CA etc.). For information on default language and valid languages for a marketplace, refer <a target="_blank" href="%s">Locale Reference</a>.', 'content-egg' ), 'https://webservices.amazon.com/paapi5/documentation/locale-reference.html#topics' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'default'     => '',
			),
			'AmazonGlobal'            => array(
				'title'       => __( 'Amazon Global', 'content-egg' ),
				'description' => __( 'Amazon Global products', 'content-egg' )
				                 . ' <p class="description">' . __( 'A delivery program featuring international shipping to certain Exportable Countries.', 'content-egg' ) . '</p>',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
			'FreeShipping'            => array(
				'title'       => __( 'Free Shipping', 'content-egg' ),
				'description' => __( 'Free Shipping products', 'content-egg' )
				                 . ' <p class="description">' . __( 'A delivery program featuring free shipping of an item.', 'content-egg' ) . '</p>',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
			'FulfilledByAmazon'       => array(
				'title'       => __( 'Fulfilled by Amazon', 'content-egg' ),
				'description' => __( 'Fulfilled by Amazon', 'content-egg' )
				                 . ' <p class="description">' . __( 'Fulfilled by Amazon indicates that products are stored, packed and dispatched by Amazon.', 'content-egg' ) . '</p>',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
			'Prime'                   => array(
				'title'       => __( 'Prime', 'content-egg' ),
				'description' => __( 'Prime products', 'content-egg' )
				                 . ' <p class="description">' . __( 'An offer for an item which is eligible for Prime Program.', 'content-egg' ) . '</p>',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
			'MinReviewsRating'        => array(
				'title'            => __( 'Minimum rating', 'content-egg' ),
				'description'      => __( 'Filters search results to items with customer review ratings above specified value.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''   => __( 'Any', 'content-egg' ),
					'4.' => 4,
					'3.' => 3,
					'2.' => 2,
					'1.' => 1,
				),
				'default'          => '',
			),
			'save_img'                => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images to local server', 'content-egg' )
				                 . ' <p class="description">' . __( 'Enabling this option may violate API rules.', 'content-egg' ) . '</p>',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'show_small_logos'        => array(
				'title'            => __( 'Small logos', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'description'      => __( 'Enabling this option may violate API rules.', 'content-egg' ) . ' '
				                      . sprintf( __( 'Read more: <a target="_blank" href="%s">Amazon brand usage guidelines</a>.', 'content-egg' ), 'https://advertising.amazon.com/ad-specs/en/policy/brand-usage' ),
				'dropdown_options' => array(
					'true'  => __( 'Show small logos', 'content-egg' ),
					'false' => __( 'Hide small logos', 'content-egg' ),
				),
				'default'          => 'false',
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

		foreach ( self::getLocalesList() as $locale_id => $locale_name ) {
			$options[ 'associate_tag_' . $locale_id ] = array(
				'title'       => sprintf( __( 'Associate Tag for %s locale', 'content-egg' ), $locale_name ),
				'description' => __( 'Type here your tracking ID for this locale if you need multiple locale parsing', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			);
		}

		$parent                         = parent::options();
		$parent['ttl_items']['default'] = 86400;
		$options                        = array_merge( $parent, $options );

		$options['forced_tag'] = array(
			'title'       => __( 'Forced Associate Tag', 'content-egg' ),
			'description' => __( 'Forced replacement of tag parameter in all links. Usually you should leave this field blank!', 'content-egg' ),
			'callback'    => array( $this, 'render_input' ),
			'default'     => '',
			'validator'   => array(
				'trim',
			),
		);

		return self::moveRequiredUp( $options );
	}

	public static function getLocalesList() {
		$locales = array_keys( self::locales() );
		sort( $locales );

		return array_combine( $locales, array_map( 'strtoupper', $locales ) );
	}

	public static function getDefaultLocale() {
		return 'us';
	}

	public static function getActiveLocalesList() {
		$locales = self::getLocalesList();
		$active  = array();

		$default            = self::getInstance()->option( 'locale' );
		$active[ $default ] = $locales[ $default ];

		foreach ( $locales as $locale => $name ) {
			if ( $locale == $default ) {
				continue;
			}
			if ( self::getInstance()->option( 'associate_tag_' . $locale ) ) {
				$active[ $locale ] = $name;
			}
		}

		return $active;
	}

	public static function getDomainByLocale( $locale ) {
		return AmazonLocales::getDomain( $locale );
	}

	public static function locales() {
		return AmazonLocales::locales();
	}

}
