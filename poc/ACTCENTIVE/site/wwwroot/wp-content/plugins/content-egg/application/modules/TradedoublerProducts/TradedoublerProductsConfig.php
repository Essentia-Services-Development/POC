<?php

namespace ContentEgg\application\modules\TradedoublerProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * TradedoublerProductsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class TradedoublerProductsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$options = array(
			'token'                   => array(
				'title'       => 'Token <span class="cegg_required">*</span>',
				'description' => __( 'Access key for Tradedoubler Products API. You can get it <a href="https://login.tradedoubler.com/publisher/aManageTokens.action">here</a>.', 'content-egg' ),
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
			'fid'                     => array(
				'title'       => 'Feed ID <span class="cegg_required">*</span>',
				'description' => sprintf( __( 'You can find your feeds <a target="_blank" href="%s">here</a> (change {token} to your Token in the URL).', 'content-egg' ), 'http://api.tradedoubler.com/1.0/productFeeds;pretty=true?token={token}' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Feed ID" can not be empty.', 'content-egg' ),
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
				'default'     => 6,
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			'tdCategoryId'            => array(
				'title'            => __( 'Category ', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''      => __( 'All categories', 'content-egg' ),
					'899.'  => 'Art and antiques',
					'8.'    => 'Automotive',
					'4.'    => 'Books',
					'1990.' => 'Business',
					'101.'  => 'Consoles and toys',
					'361.'  => 'Education',
					'40.'   => 'Electronics',
					'68.'   => 'Fashion',
					'86.'   => 'Films and Theatre',
					'98.'   => 'Food and drink',
					'107.'  => 'Gifts',
					'14.'   => 'Hardware and software',
					'114.'  => 'Health and beauty',
					'2350.' => 'Health and safety',
					'121.'  => 'Home and garden',
					'147.'  => 'Money and finance',
					'160.'  => 'Music',
					'261.'  => 'Office',
					'838.'  => 'Photography',
					'1687.' => 'Posters',
					'401.'  => 'Special offers',
					'698.'  => 'Sports',
					'2110.' => 'Top sellers',
					'163.'  => 'Travel',
					'2.'    => 'Un-categorised',
				),
				'default'          => '',
				'section'          => 'default',
			),

			'minPrice'         => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'maxPrice'         => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'language'         => array(
				'title'       => 'Language',
				'description' => 'Matches against the language of the feed containing products.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'currency'         => array(
				'title'       => 'Currency',
				'description' => 'Matches against the currency of products.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'orderBy'          => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                     => __( 'Default', 'content-egg' ),
					'priceAsc'             => 'Price Asc',
					'priceDesc'            => 'Price Desc',
					'modificationDateAsc'  => 'Modification Date Asc',
					'modificationDateDesc' => 'Modification Date Desc',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'save_img'         => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'description_size' => array(
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
		$options = array_merge( parent::options(), $options );

		return self::moveRequiredUp( $options );

	}

}
