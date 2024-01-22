<?php

namespace ContentEgg\application\modules\Linkshare;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * LinkshareConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class LinkshareConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'token'                   => array(
				'title'       => 'Web Services Token <span class="cegg_required">*</span>',
				'description' => __( 'Linkshare access key. Go to your account in Linkshare and follow "LINKS -> Web Service".', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Web Services Token" can not be empty.', 'content-egg' ),
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
			'mid'                     => array(
				'title'       => 'Advertiser ID',
				'description' => __( 'Limit search by Advertiser ID. Login in account LinkShare and follow: PROGRAMS -> My Advertisers -> Choose advertiser -> Advertiser Info.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'search_logic'            => array(
				'title'            => __( 'Searching logic', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'AND'   => __( 'Search across all words - AND', 'content-egg' ),
					'OR'    => __( 'Any of word - OR', 'content-egg' ),
					'EXACT' => __( 'Exactly compliance - EXACT', 'content-egg' ),
				),
				'default'          => 'AND',
				'section'          => 'default',
			),
			'sort'                    => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''             => __( 'Default', 'content-egg' ),
					'retailprice'  => 'Price',
					'productname'  => 'Product Name',
					'categoryname' => 'Primary Category',
					'mid'          => 'Merchant ID',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'sorttype'                => array(
				'title'            => __( 'Sorting order', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'asc' => 'Ascending',
					'dsc' => 'Descending',
				),
				'default'          => 'asc',
				'section'          => 'default',
			),
			'cat'                     => array(
				'title'       => __( 'Category ', 'content-egg' ),
				'description' => __( 'Limit search by category. Each partner has own categories', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'filter_duplicate'        => array(
				'title'       => __( 'Filter duplicates', 'content-egg' ),
				'description' => __( 'Filter similar entries', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
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
