<?php

namespace ContentEgg\application\modules\Ozon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * OzonConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class OzonConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn                  = array(
			'partner_id'              => array(
				'title'       => __( 'Partner ID', 'content-egg' ),
				'description' => __( 'Укажите, если хотите посылать трафик на "родную" партнерскую программу ozon. Найти идентификатор можно <a href="https://www.ozon.ru/?context=partner_data">здесь</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'deeplink'                => array(
				'title'       => __( 'Deeplink', 'content-egg' ),
				'description' => __( 'Deeplink одной из CPA-сетей с поддержкой ozon. Укажите, если хотите посылать трафик через CPA-сеть.', 'content-egg' ),
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
			'items_sort_tag'          => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'ListRelevance'     => __( 'Relevance', 'content-egg' ),
					'ListRRelevance'    => __( 'Return relevance', 'content-egg' ),
					'ListOzonSpecial_1' => __( 'Ozon Special Default', 'content-egg' ),
					'ListOzonSpecial_2' => __( 'Ozon Special Имя', 'content-egg' ),
					'ListOzonSpecial_3' => __( 'Ozon Special Бестселлеры', 'content-egg' ),
					'ListOzonSpecial_4' => __( 'Ozon Special Rating', 'content-egg' ),
					'ListOzonSpecial_5' => __( 'Ozon Special Год выхода', 'content-egg' ),
					'ListOzonSpecial_6' => __( 'Ozon Special Цена', 'content-egg' ),
					'ListAvail'         => __( 'Availability', 'content-egg' ),
					'ListRAvail'        => __( 'Revert availability', 'content-egg' ),
					'ListYear'          => __( 'Year', 'content-egg' ),
					'ListRYear'         => __( 'Year in backward sorting', 'content-egg' ),
					'ListType'          => __( 'Type', 'content-egg' ),
					'ListRType'         => __( 'Type in backward sorting', 'content-egg' ),
					'ListPrice'         => __( 'Price ascending', 'content-egg' ),
					'ListRPrice'        => __( 'Price descending', 'content-egg' ),
					'ListName'          => __( 'Name', 'content-egg' ),
					'ListRName'         => __( 'Name in backward sorting', 'content-egg' ),
					'ListAuthor'        => __( 'Author', 'content-egg' ),
					'ListRAuthor'       => __( 'Author in backward sorting', 'content-egg' ),
				),
				'default'          => 'ListRelevance',
				'section'          => 'default',
			),
			'search_category'         => array(
				'title'            => __( 'Category ', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''              => __( 'All', 'content-egg' ),
					'div_kid'       => __( 'For kids', 'content-egg' ),
					'div_book'      => __( 'Books', 'content-egg' ),
					'div_tech'      => __( 'Electronic', 'content-egg' ),
					'div_appliance' => __( 'household appliances', 'content-egg' ),
					'div_soft'      => __( 'Games and soft', 'content-egg' ),
					'div_dvd'       => __( 'DVD and Blu-ray', 'content-egg' ),
					'div_music'     => __( 'Music', 'content-egg' ),
					'div_home'      => __( 'House, garden, zoo products', 'content-egg' ),
					'div_bs'        => __( 'Sport', 'content-egg' ),
					'div_beauty'    => __( 'Beauty and health', 'content-egg' ),
					'div_fashion'   => __( 'Clothes', 'content-egg' ),
					'div_gifts'     => __( 'Gifts', 'content-egg' ),
					'div_rar'       => __( 'Antiques, vintage, art', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'features'                => array(
				'title'       => __( 'Features', 'content-egg' ),
				'description' => __( 'Parse features of products (maximum for 3 products)', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'customer_reviews'        => array(
				'title'       => __( 'Customer reviews', 'content-egg' ),
				'description' => __( 'Parse Customer reviews', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'review_products_number'  => array(
				'title'       => __( 'Number of products with reviews', 'content-egg' ),
				'description' => __( 'Parse reviews only for a certain amount of products. Max - 3.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '1',
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 3,
						'message' => __( 'The "Number of products with reviews" can not be more than 3.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'review_number'           => array(
				'title'       => __( 'Number of reviews', 'content-egg' ),
				'description' => __( 'Number of reviews for a single product.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '5',
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			'truncate_reviews'        => array(
				'title'       => __( 'Cut reviews', 'content-egg' ),
				'description' => __( 'Size of reviews in characters (0 - do not cut)', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '500',
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			'review_sort'             => array(
				'title'            => __( 'Sorting of reviews', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''     => __( 'Useful', 'content-egg' ),
					'date' => __( 'Date of publication', 'content-egg' ),
					'rate' => __( 'Rating', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
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
		$parent                   = parent::options();
		$parent['ttl']['default'] = 2592000;

		return array_merge( $parent, $optiosn );
	}

}
