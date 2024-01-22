<?php

namespace ContentEgg\application\modules\AvantlinkProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AvantlinkProductsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class AvantlinkProductsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$options = array(
			'affiliate_id'                  => array(
				'title'       => 'Affiliate ID <span class="cegg_required">*</span>',
				'description' => __( 'Your assigned Affiliate identifier.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Affiliate ID' ),
					),
				),
			),
			'website_id'                    => array(
				'title'       => 'Website ID <span class="cegg_required">*</span>',
				'description' => __( 'Your assigned referral website identifier.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Website ID' ),
					),
				),
			),
			'entries_per_page'              => array(
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
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 100 ),
					),
				),
			),
			'entries_per_page_update'       => array(
				'title'       => __( 'Results for updates', 'content-egg' ),
				'description' => __( 'Number of results for automatic updates and autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 6,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 100,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 100 ),
					),
				),
			),
			'app_id'                        => array(
				'title'       => __( 'Application ID', 'content-egg' ),
				'description' => __( 'The AvantLink assigned identifier for an App Market application. When specified, this will trigger the construction of customized click-through URLs that track back to the particular app for any resulting sales.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'custom_tracking_code'          => array(
				'title'       => __( 'Custom tracking code', 'content-egg' ),
				'description' => __( 'A custom string to be appended to AvantLink click-through URLs, for use with your own personal tracking mechanisms.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'datafeed_ids'                  => array(
				'title'       => __( 'Datafeed IDs', 'content-egg' ),
				'description' => __( 'A comma-delimited list of AvantLink assigned datafeed identifiers.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'merchant_category_ids'         => array(
				'title'       => __( 'Merchant category IDs', 'content-egg' ),
				'description' => __( 'A comma-delimited list of AvantLink assigned Affiliate category identifiers.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'merchant_ids'                  => array(
				'title'       => __( 'Merchant IDs', 'content-egg' ),
				'description' => __( 'A comma-delimited list of AvantLink assigned Merchant identifiers.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'search_advanced_syntax'        => array(
				'title'       => __( 'Search advanced syntax', 'content-egg' ),
				'description' => __( 'Enable search advanced syntax.', 'content-egg' ) .
				                 '<p class="description">' . __( 'Enables the use of advanced search syntax, causing special treatment in the following circumstances: preceding a word with "+" will restrict the search to ONLY products that mention that word, preceding a word with "-" will restrict the search to exclude any products that mention that word, and the keyword " OR " can be used to perform multiple simultaneous searches (in place of OR you can use the pipe character "|" to separate multiple search terms).', 'content-egg' ) . '</p>',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'search_category'               => array(
				'title'       => __( 'Search category', 'content-egg' ),
				'description' => __( 'A product category to which search results should be restricted.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'search_department'             => array(
				'title'       => __( 'Search department ', 'content-egg' ),
				'description' => __( 'A department to which search results should be restricted.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'search_on_sale_level'          => array(
				'title'            => __( 'Sale level', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'.0'   => __( 'All products', 'content-egg' ),
					'.100' => __( 'On-sale products only', 'content-egg' ),
					'.110' => __( 'Min. 10% discount', 'content-egg' ),
					'.120' => __( 'Min. 20% discount', 'content-egg' ),
					'.130' => __( 'Min. 30% discount', 'content-egg' ),
					'.140' => __( 'Min. 40% discount', 'content-egg' ),
					'.150' => __( 'Min. 50% discount', 'content-egg' ),
					'.160' => __( 'Min. 60% discount', 'content-egg' ),
					'.170' => __( 'Min. 70% discount', 'content-egg' ),
					'.180' => __( 'Min. 80% discount', 'content-egg' ),
					'.190' => __( 'Min. 90% discount', 'content-egg' ),
				),
				'default'          => '.0',
			),
			'search_price_minimum'          => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => __( 'Example, 8.99', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'search_price_maximum'          => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => __( 'Example, 98.50', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'search_results_merchant_limit' => array(
				'title'       => __( 'Results merchant limit', 'content-egg' ),
				'description' => __( 'The maximum number of results to return for each Merchant.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'search_subcategory'            => array(
				'title'       => __( 'Search subcategory ', 'content-egg' ),
				'description' => __( 'A product sub-category to which search results should be restricted.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'description_size'              => array(
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
			'save_img'                      => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
		);

		$parent                           = parent::options();
		$parent['update_mode']['default'] = 'cron';

		return array_merge( $parent, $options );
	}

}
