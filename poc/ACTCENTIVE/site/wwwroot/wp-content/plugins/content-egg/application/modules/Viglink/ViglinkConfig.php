<?php

namespace ContentEgg\application\modules\Viglink;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AffiliatewindowConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class ViglinkConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn                             = array(
			'apiKey'                  => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'To track clicks by campaign, use your campaign-specific API Key.', 'content-egg' ) . ' ' .
				                 sprintf( __( 'You can find your API key in your <a target="_blank" href="%s">VigLink</a> account.', 'content-egg' ), 'http://www.keywordrush.com/go/viglink' ) . ' ' .
				                 __( 'When logged into your dashboard, go to Account > Sites > your domain name > "key" icon under Actions.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Password' ),
					),
				),
			),
			'secretKey'               => array(
				'title'       => 'Secret Key <span class="cegg_required">*</span>',
				'description' => __( 'When logged into your dashboard, go to Account > Sites > your domain name > "key" icon under Actions.', 'content-egg' ),
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
			),
			'deeplink'                => array(
				'title'       => 'Deeplink',
				'description' => __( 'Used only for search by URL feature! Set <a target="_blank" href="https://ce-docs.keywordrush.com/set-up-products/deeplinksettings">Deeplink</a> for one of CPA-networks.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'country'                 => array(
				'title'       => __( 'Country', 'content-egg' ),
				'description' => __( 'Filter results to only include offers from a specific country. Please use ISO Alpha-2 country codes like "us" for United States. You can specify multiple countries separated by commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
			),
			'category'                => array(
				'title'            => __( 'Category ', 'content-egg' ),
				'description'      => __( 'Filter your query by a specific category.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => self::getCategoriesList(),
				'default'          => '',
			),
			'sortBy'                  => array(
				'title'            => __( 'Sort order', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''       => __( 'Relevance', 'content-egg' ),
					'price'  => __( 'Price low to high', 'content-egg' ),
					'-price' => __( 'Price high to low', 'content-egg' ),
				),
				'default'          => '',
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
			'merchant'                => array(
				'title'       => __( 'Merchant', 'content-egg' ),
				'description' => sprintf( __( 'Filter your query by a specific merchant. Currently this filter is case-sensitive. The best way to ensure that it will return accurate results is to filter with a value <a target="_blank" href="%s">already discovered</a>. You can specify multiple merchants separated by commas.', 'content-egg' ), 'https://viglink-developer-center.readme.io/docs/product-search#section-faceting' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
			),
			'filterImages'            => array(
				'title'       => __( 'Images filter', 'content-egg' ),
				'description' => __( 'Products with images', 'content-egg' ) . '<p class="description">' . __( 'Some merchants do not supply an image in their feeds. As such, you have the option here to filter out items that do not have a merchant-supplied image, or correctly structured image URL.', 'content-egg' ) . '</p>',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
			),
			'default_currency'        => array(
				'title'       => __( 'Default currency', 'content-egg' ),
				'description' => __( 'Expects the three-letter ISO 4217 currency code. Used only for search by URL feature.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
			),
			'save_img'                => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
		);
		$optiosn                             = array_merge( parent::options(), $optiosn );
		$optiosn['ttl_items']['description'] = __( 'Please note: Price update is only available for products added by direct URL (not by keyword search).', 'content-egg' );
		$optiosn['update_mode']['default']   = 'cron';

		return $optiosn;
	}

	public static function getCategoriesList() {
		return array(
			''                                                                => __( '[ All ]', 'content-egg' ),
			'Adult and Gambling'                                              => 'Adult and Gambling',
			'Adult and Gambling > Adult'                                      => 'Adult and Gambling > Adult',
			'Adult and Gambling > Gambling'                                   => 'Adult and Gambling > Gambling',
			'Adult and Gambling > Other Adult and Gambling'                   => 'Adult and Gambling > Other Adult and Gambling',
			'Art and Entertainment'                                           => 'Art and Entertainment',
			'Art and Entertainment > DVDs and Videos'                         => 'Art and Entertainment > DVDs and Videos',
			'Art and Entertainment > Music'                                   => 'Art and Entertainment > Music',
			'Art and Entertainment > Art'                                     => 'Art and Entertainment > Art',
			'Art and Entertainment > Event Tickets'                           => 'Art and Entertainment > Event Tickets',
			'Art and Entertainment > Other Art and Entertainment'             => 'Art and Entertainment > Other Art and Entertainment',
			'Automotive'                                                      => 'Automotive',
			'Automotive > Automotive Vehicles'                                => 'Automotive > Automotive Vehicles',
			'Automotive > Auto Electronics'                                   => 'Automotive > Auto Electronics',
			'Automotive > Auto Parts and Tools'                               => 'Automotive > Auto Parts and Tools',
			'Automotive > Other Auto'                                         => 'Automotive > Other Auto',
			'Cameras and Photo'                                               => 'Cameras and Photo',
			'Cameras and Photo > Camera Hardware and Accessories'             => 'Cameras and Photo > Camera Hardware and Accessories',
			'Cameras and Photo > Video Hardware and Accessories'              => 'Cameras and Photo > Video Hardware and Accessories',
			'Cameras and Photo > Other Camera'                                => 'Cameras and Photo > Other Camera',
			'Career and Employment'                                           => 'Career and Employment',
			'Cell Phones and Mobile'                                          => 'Cell Phones and Mobile',
			'Cell Phones and Mobile > Mobile Devices'                         => 'Cell Phones and Mobile > Mobile Devices',
			'Cell Phones and Mobile > Mobile Device Accessories'              => 'Cell Phones and Mobile > Mobile Device Accessories',
			'Cell Phones and Mobile > Mobile Device Software'                 => 'Cell Phones and Mobile > Mobile Device Software',
			'Cell Phones and Mobile > Other Mobile'                           => 'Cell Phones and Mobile > Other Mobile',
			'Collectibles'                                                    => 'Collectibles',
			'Collectibles > Sports Memorabilia'                               => 'Collectibles > Sports Memorabilia',
			'Collectibles > Stamps'                                           => 'Collectibles > Stamps',
			'Collectibles > Coins and Paper Money'                            => 'Collectibles > Coins and Paper Money',
			'Collectibles > Other Collectibles'                               => 'Collectibles > Other Collectibles',
			'Computing'                                                       => 'Computing',
			'Computing > Computing, Hardware and Accessories'                 => 'Computing > Computing, Hardware and Accessories',
			'Computing > Computing Software'                                  => 'Computing > Computing Software',
			'Computing > Other Computing'                                     => 'Computing > Other Computing',
			'Consumer Electronics'                                            => 'Consumer Electronics',
			'Consumer Electronics > Home Electronics'                         => 'Consumer Electronics > Home Electronics',
			'Consumer Electronics > Portable Electronics'                     => 'Consumer Electronics > Portable Electronics',
			'Consumer Electronics > Other Electronics'                        => 'Consumer Electronics > Other Electronics',
			'Dating'                                                          => 'Dating',
			'Education'                                                       => 'Education',
			'Family and Baby'                                                 => 'Family and Baby',
			'Family and Baby > Baby and Maternity Clothing'                   => 'Family and Baby > Baby and Maternity Clothing',
			'Family and Baby > Baby Other'                                    => 'Family and Baby > Baby Other',
			'Fashion'                                                         => 'Fashion',
			'Fashion > Clothing'                                              => 'Fashion > Clothing',
			'Fashion > Clothing > Men\'s Clothing'                            => 'Fashion > Clothing > Men\'s Clothing',
			'Fashion > Clothing > Women\'s Clothing'                          => 'Fashion > Clothing > Women\'s Clothing',
			'Fashion > Clothing > Kid\'s Clothing'                            => 'Fashion > Clothing > Kid\'s Clothing',
			'Fashion > Clothing > Other Clothing'                             => 'Fashion > Clothing > Other Clothing',
			'Fashion > Shoes'                                                 => 'Fashion > Shoes',
			'Fashion > Shoes > Men\'s Shoes'                                  => 'Fashion > Shoes > Men\'s Shoes',
			'Fashion > Shoes > Women\'s Shoes'                                => 'Fashion > Shoes > Women\'s Shoes',
			'Fashion > Shoes > Kid\'s Shoes'                                  => 'Fashion > Shoes > Kid\'s Shoes',
			'Fashion > Shoes > Other Shoes'                                   => 'Fashion > Shoes > Other Shoes',
			'Fashion > Luggage and Bags'                                      => 'Fashion > Luggage and Bags',
			'Fashion > Luggage and Bags > Women\'s Luggage and Bags'          => 'Fashion > Luggage and Bags > Women\'s Luggage and Bags',
			'Fashion > Luggage and Bags > Other Luggage'                      => 'Fashion > Luggage and Bags > Other Luggage',
			'Fashion > Fashion Accessories'                                   => 'Fashion > Fashion Accessories',
			'Fashion > Fashion Accessories > Men\'s Fashion Accessories'      => 'Fashion > Fashion Accessories > Men\'s Fashion Accessories',
			'Fashion > Fashion Accessories > Women\'s Fashion Accessories'    => 'Fashion > Fashion Accessories > Women\'s Fashion Accessories',
			'Fashion > Fashion Accessories > Kid\'s Fashion Accessories'      => 'Fashion > Fashion Accessories > Kid\'s Fashion Accessories',
			'Fashion > Fashion Accessories > Other Fashion Accessories'       => 'Fashion > Fashion Accessories > Other Fashion Accessories',
			'Fashion > Other Fashion'                                         => 'Fashion > Other Fashion',
			'Financial Services'                                              => 'Financial Services',
			'Firearms and Hunting'                                            => 'Firearms and Hunting',
			'Firearms and Hunting > Firearms'                                 => 'Firearms and Hunting > Firearms',
			'Firearms and Hunting > Knives'                                   => 'Firearms and Hunting > Knives',
			'Firearms and Hunting > Fishing'                                  => 'Firearms and Hunting > Fishing',
			'Firearms and Hunting > Bow Hunting'                              => 'Firearms and Hunting > Bow Hunting',
			'Firearms and Hunting > Survival Gear'                            => 'Firearms and Hunting > Survival Gear',
			'Firearms and Hunting > Other Hunting'                            => 'Firearms and Hunting > Other Hunting',
			'Food and Drink'                                                  => 'Food and Drink',
			'Food and Drink > Standard Food'                                  => 'Food and Drink > Standard Food',
			'Food and Drink > Gourmet Food'                                   => 'Food and Drink > Gourmet Food',
			'Food and Drink > Other Food and Drink'                           => 'Food and Drink > Other Food and Drink',
			'Health and Beauty'                                               => 'Health and Beauty',
			'Health and Beauty > Health'                                      => 'Health and Beauty > Health',
			'Health and Beauty > Beauty'                                      => 'Health and Beauty > Beauty',
			'Health and Beauty > Other Health and Beauty'                     => 'Health and Beauty > Other Health and Beauty',
			'Home and Garden'                                                 => 'Home and Garden',
			'Home and Garden > Home Decor'                                    => 'Home and Garden > Home Decor',
			'Home and Garden > Appliances'                                    => 'Home and Garden > Appliances',
			'Home and Garden > Home Improvement'                              => 'Home and Garden > Home Improvement',
			'Home and Garden > Kitchen, Dining and Bar'                       => 'Home and Garden > Kitchen, Dining and Bar',
			'Home and Garden > Gardening'                                     => 'Home and Garden > Gardening',
			'Home and Garden > Gift'                                          => 'Home and Garden > Gift',
			'Home and Garden > Flowers'                                       => 'Home and Garden > Flowers',
			'Home and Garden > Cleaning Supplies'                             => 'Home and Garden > Cleaning Supplies',
			'Home and Garden > Other Home and Garden'                         => 'Home and Garden > Other Home and Garden',
			'Industrial and Supply'                                           => 'Industrial and Supply',
			'Industrial and Supply > Office Supplies'                         => 'Industrial and Supply > Office Supplies',
			'Industrial and Supply > Office Electronics'                      => 'Industrial and Supply > Office Electronics',
			'Industrial and Supply > Industrial'                              => 'Industrial and Supply > Industrial',
			'Industrial and Supply > Other Industrial and Supply'             => 'Industrial and Supply > Other Industrial and Supply',
			'Jewelry and Watches'                                             => 'Jewelry and Watches',
			'Jewelry and Watches > Jewelry'                                   => 'Jewelry and Watches > Jewelry',
			'Jewelry and Watches > Watches'                                   => 'Jewelry and Watches > Watches',
			'Jewelry and Watches > Other Jewelry'                             => 'Jewelry and Watches > Other Jewelry',
			'Lifestyle'                                                       => 'Lifestyle',
			'Motorcycles and Powersports'                                     => 'Motorcycles and Powersports',
			'Motorcycles and Powersports > Motorcycling'                      => 'Motorcycles and Powersports > Motorcycling',
			'Motorcycles and Powersports > ATVs'                              => 'Motorcycles and Powersports > ATVs',
			'Motorcycles and Powersports > Boating'                           => 'Motorcycles and Powersports > Boating',
			'Motorcycles and Powersports > Other Motorcycles and Powersports' => 'Motorcycles and Powersports > Other Motorcycles and Powersports',
			'Music and Musicians'                                             => 'Music and Musicians',
			'Music and Musicians > Musical Instruments'                       => 'Music and Musicians > Musical Instruments',
			'Music and Musicians > Musical Accessories'                       => 'Music and Musicians > Musical Accessories',
			'Music and Musicians > Other Musical'                             => 'Music and Musicians > Other Musical',
			'News, Books and Magazines'                                       => 'News, Books and Magazines',
			'News, Books and Magazines > Books and eBooks'                    => 'News, Books and Magazines > Books and eBooks',
			'News, Books and Magazines > Magazines and Newspaper'             => 'News, Books and Magazines > Magazines and Newspaper',
			'News, Books and Magazines > Other Books'                         => 'News, Books and Magazines > Other Books',
			'Online Services'                                                 => 'Online Services',
			'Other'                                                           => 'Other',
			'Pets'                                                            => 'Pets',
			'Pets > Dogs'                                                     => 'Pets > Dogs',
			'Pets > Fish'                                                     => 'Pets > Fish',
			'Pets > Cat'                                                      => 'Pets > Cat',
			'Pets > Birds'                                                    => 'Pets > Birds',
			'Pets > Other Pets'                                               => 'Pets > Other Pets',
			'Real Estate'                                                     => 'Real Estate',
			'Religious and Ceremonial'                                        => 'Religious and Ceremonial',
			'Self-Help'                                                       => 'Self-Help',
			'Shopping and Coupons'                                            => 'Shopping and Coupons',
			'Sports and Fitness'                                              => 'Sports and Fitness',
			'Sports and Fitness > Bicycling'                                  => 'Sports and Fitness > Bicycling',
			'Sports and Fitness > Water Sports'                               => 'Sports and Fitness > Water Sports',
			'Sports and Fitness > Golfing'                                    => 'Sports and Fitness > Golfing',
			'Sports and Fitness > Winter Sports'                              => 'Sports and Fitness > Winter Sports',
			'Sports and Fitness > Outdoor Apparel'                            => 'Sports and Fitness > Outdoor Apparel',
			'Sports and Fitness > Camping'                                    => 'Sports and Fitness > Camping',
			'Sports and Fitness > Other Sports and Fitness'                   => 'Sports and Fitness > Other Sports and Fitness',
			'Toys and Hobbies'                                                => 'Toys and Hobbies',
			'Toys and Hobbies > Toys'                                         => 'Toys and Hobbies > Toys',
			'Toys and Hobbies > Games'                                        => 'Toys and Hobbies > Games',
			'Toys and Hobbies > Crafts'                                       => 'Toys and Hobbies > Crafts',
			'Toys and Hobbies > Hobbies'                                      => 'Toys and Hobbies > Hobbies',
			'Toys and Hobbies > Costumes'                                     => 'Toys and Hobbies > Costumes',
			'Toys and Hobbies > Other Toys and Hobbies'                       => 'Toys and Hobbies > Other Toys and Hobbies',
			'Travel'                                                          => 'Travel',
			'Travel > Other Travel'                                           => 'Travel > Other Travel',
			'Video Gaming'                                                    => 'Video Gaming',
			'Video Gaming > Video Games'                                      => 'Video Gaming > Video Games',
			'Video Gaming > Gaming Hardware'                                  => 'Video Gaming > Gaming Hardware',
			'Video Gaming > Other Gaming'                                     => 'Video Gaming > Other Gaming'
		);
	}

}
