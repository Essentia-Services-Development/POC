<?php

namespace ContentEgg\application\modules\Walmart;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * WalmartConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class WalmartConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'publisherId'             => array(
				'title'       => 'Impact Publisher ID <span class="cegg_required">*</span>',
				'description' => __( 'Your Impact Radius Publisher ID.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Publisher ID' ),
					),
				),
			),
			/*
			'apiKey' => array(
				'title' => 'API Key',
				'description' => __('If you do not have your API key, leave this field empty.', 'content-egg'),
				'callback' => array($this, 'render_input'),
				'default' => '',
				'validator' => array(
					'trim',
				),
			),
			 *
			 */
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one search query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 8,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 25,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 25 ),
					),
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
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 25,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 25 ),
					),
				),
			),
			'deeplink'                => array(
				'title'       => 'Deeplink',
				'description' => __( 'Set this option if you want to send traffic through one of affiliate network with Walmart support.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'categoryId'              => array(
				'title'            => __( 'Category', 'content-egg' ),
				'description'      => __( 'Sorting criteria.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''         => __( '[ All ]', 'content-egg' ),
					'.1334134' => 'Arts, Crafts & Sewing',
					'.91083'   => 'Auto & Tires',
					'.5427'    => 'Baby',
					'.1085666' => 'Beauty',
					'.3920'    => 'Books',
					'.1105910' => 'Cell Phones',
					'.5438'    => 'Clothing',
					'.3944'    => 'Electronics',
					'.976759'  => 'Food',
					'.1094765' => 'Gifts & Registry',
					'.976760'  => 'Health',
					'.4044'    => 'Home',
					'.1072864' => 'Home Improvement',
					'.1115193' => 'Household Essentials',
					'.6197502' => 'Industrial & Scientific',
					'.3891'    => 'Jewelry',
					'.4096'    => 'Movies & TV',
					'.4104'    => 'Music on CD or Vinyl',
					'.7796869' => 'Musical Instruments',
					'.1229749' => 'Office',
					'.2637'    => 'Party & Occasions',
					'.5428'    => 'Patio & Garden',
					'.1005862' => 'Personal Care',
					'.5440'    => 'Pets',
					'.5426'    => 'Photo Center',
					'.1085632' => 'Seasonal',
					'.6163033' => 'Services',
					'.4125'    => 'Sports & Outdoors',
					'.4171'    => 'Toys',
					'.2636'    => 'Video Games',
				),
				'default'          => '',
			),
			'sort'                    => array(
				'title'            => __( 'Sort', 'content-egg' ),
				'description'      => __( 'Sorting criteria.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'relevance'      => __( 'Relevance', 'content-egg' ),
					'price'          => __( 'Price', 'content-egg' ),
					'title'          => __( 'Title', 'content-egg' ),
					'bestseller'     => __( 'Bestseller', 'content-egg' ),
					'customerRating' => __( 'Customer Rating', 'content-egg' ),
					'new'            => __( 'New', 'content-egg' ),
				),
				'default'          => 'relevance',
			),
			'order'                   => array(
				'title'            => __( 'Order', 'content-egg' ),
				'description'      => __( 'Sort ordering criteria. This parameter is needed only for the sort types: Price, Title, Customer Rating.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'asc'  => __( 'Asc', 'content-egg' ),
					'desc' => __( 'Desc', 'content-egg' ),
				),
				'default'          => 'relevance',
			),
			'price_min'               => array(
				'title'       => __( 'Price min', 'content-egg' ),
				'description' => __( 'Minimum price to include.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'price_max'               => array(
				'title'       => __( 'Price max', 'content-egg' ),
				'description' => __( 'Maximum price to include.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
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
			'customer_reviews'        => array(
				'title'       => __( 'Customer reviews', 'content-egg' ),
				'description' => __( 'Parse customer reviews. It takes more time. Don\'t check if you don\'t need it.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'reviews_as_comments'     => array(
				'title'       => __( 'Reviews as post comments', 'content-egg' ),
				'description' => __( 'Save user reviews as post comments.', 'content-egg' ),
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
		);

		return array_merge( parent::options(), $optiosn );
	}

}
