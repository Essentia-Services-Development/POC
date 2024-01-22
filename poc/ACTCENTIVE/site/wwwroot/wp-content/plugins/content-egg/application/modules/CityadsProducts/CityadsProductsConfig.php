<?php

namespace ContentEgg\application\modules\CityadsProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * CityadsProductsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class CityadsProductsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'remote_auth'             => array(
				'title'       => __( 'Remote_auth', 'content-egg' ) . ' <span class="cegg_required">*</span>',
				'description' => __( 'You access key API. Go to -> "Your account" -> "API"', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Remote_auth" can not be empty.', 'content-egg' ),
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
				'default'     => 6,
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
			'subaccount'              => array(
				'title'       => __( 'Subaccount', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'shop'                    => array(
				'title'       => __( 'Shop ID', 'content-egg' ),
				'description' => __( 'Filter for shops. Integer IDs stores listed separated by commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'categories'              => array(
				'title'            => __( 'Category ', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''      => __( 'All', 'content-egg' ),
					'698.'  => __( 'Goods for construction and repair', 'content-egg' ),
					'675.'  => __( 'Music', 'content-egg' ),
					'2.'    => __( 'Clothes', 'content-egg' ),
					'1157.' => __( 'Auto and moto technic', 'content-egg' ),
					'1722.' => __( 'Gifts and flowers', 'content-egg' ),
					'906.'  => __( 'Travel', 'content-egg' ),
					'1291.' => __( 'Books', 'content-egg' ),
					'1479.' => __( 'Plants and animals', 'content-egg' ),
					'1906.' => __( 'Health products', 'content-egg' ),
					'1064.' => __( 'Equipment', 'content-egg' ),
					'1590.' => __( 'Products for house', 'content-egg' ),
					'1785.' => __( 'Food, drinks', 'content-egg' ),
					'1956.' => __( 'Products for sex', 'content-egg' ),
					'307.'  => __( 'Photo and electronic', 'content-egg' ),
					'376.'  => __( 'household appliances', 'content-egg' ),
					'910.'  => __( 'Sport goods', 'content-egg' ),
					'461.'  => __( 'Computes', 'content-egg' ),
					'1516.' => __( 'Products for kids', 'content-egg' ),
					'1554.' => __( 'Office products', 'content-egg' ),
					'284.'  => __( 'Phones', 'content-egg' ),
					'1687.' => __( 'Furniture', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'geo'                     => array(
				'title'       => __( 'Geo', 'content-egg' ),
				'description' => __( 'Filter by region. Integer identifiers are listed separated by commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'min_price'               => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => __( 'Minimal price, for example 10', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'max_price'               => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => __( 'Maximum price, for example 100', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'currency'                => array(
				'title'            => __( 'Currency', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''    => __( 'Any', 'content-egg' ),
					'USD' => 'USD',
					'RUB' => 'RUB',
					'SGD' => 'SGD',
					'MYR' => 'MYR',
					'THB' => 'THB',
					'IDR' => 'IDR',
					'PHP' => 'PHP',
					'BRL' => 'BRL',
					'EUR' => 'EUR',
					'UAH' => 'UAH',
					'GBP' => 'GBP',
					'COP' => 'COP',
					'KZT' => 'KZT',
					'AUD' => 'AUD',
					'CAD' => 'CAD',
					'TWD' => 'TWD',
					'HKD' => 'HKD',
					'ARS' => 'ARS',
					'PLN' => 'PLN',
					'CHF' => 'CHF',
					'JPY' => 'JPY',
					'SEK' => 'SEK',
					'INR' => 'INR',
					'DKK' => 'DKK',
					'MXN' => 'MXN',
					'NOK' => 'NOK',
					'HUF' => 'HUF',
					'NZD' => 'NZD',
					'CZK' => 'CZK',
					'ILS' => 'ILS',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'sort'                    => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''          => __( 'Default', 'content-egg' ),
					'id'        => 'id',
					'name'      => 'name',
					'price'     => 'price',
					'old_price' => 'old price',
					'delivery'  => 'delivery',
					'brand'     => 'brand',
					'credit'    => 'credit',
					'rating'    => 'rating',
					'discount'  => 'discount',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'sort_type'               => array(
				'title'            => __( 'Sorting order', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'asc'  => 'Ascending',
					'desc' => 'Descending',
				),
				'default'          => 'true',
				'section'          => 'default',
			),
			'available'               => array(
				'title'       => __( 'Availability', 'content-egg' ),
				'description' => __( 'Only available products', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
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
