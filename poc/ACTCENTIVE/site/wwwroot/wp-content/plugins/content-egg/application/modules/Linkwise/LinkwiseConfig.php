<?php

namespace ContentEgg\application\modules\Linkwise;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * LinkwiseConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class LinkwiseConfig extends AffiliateParserModuleConfig {

	public function options() {
		$options = array(
			'username'                => array(
				'title'       => 'API Username <span class="cegg_required">*</span>',
				'description' => __( 'Please ask Linkwi.se support for your API credentials.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'API Username' ),
					),
				),
			),
			'password'                => array(
				'title'       => 'API Password <span class="cegg_required">*</span>',
				'description' => __( 'Please ask Linkwi.se support for your API credentials.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'API Password' ),
					),
				),
			),
			'subids'                  => array(
				'title'       => 'Subids',
				'description' => __( 'Add subids to tracking ulrs. Please specify comma seperated pairs subid_name-subid_value. e.g. subids=subid1-value1,subid2-value2.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
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
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 100,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 100 ),
					),
				),
			),
			'entries_per_page_update' => array(
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
			'prod_categories'         => array(
				'title'       => __( 'Product categories', 'content-egg' ),
				'description' => __( 'Only show products belonging to specific categories. Please specify a list of product category names, separated by commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'programs'                => array(
				'title'       => __( 'Programs', 'content-egg' ),
				'description' => __( 'Only show programs with these names. Please specify a list of program names, separated by commas. ', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'program_ids'             => array(
				'title'       => __( 'Programs IDs', 'content-egg' ),
				'description' => __( 'Only show programs with these ids. Please specify a list of program ids, separated by commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'feed_ids'                => array(
				'title'       => __( 'Feed IDs', 'content-egg' ),
				'description' => __( 'Only show products from feeds with these ids. Please specify a list of feed ids, separated by commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'categories'              => array(
				'title'       => __( 'Categories', 'content-egg' ),
				'description' => __( 'Only show programs belonging to specific categories. Please specify a list of category names (or IDs), separated by commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'countries'               => array(
				'title'       => __( 'Countries', 'content-egg' ),
				'description' => __( 'Filter programs according to their country. Please specify a list of country names (e.g. greece) or country_codes (e.g. gr), separated by commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'min_price'               => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => __( 'Example, 8.99', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'max_price'               => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => __( 'Example, 98.50', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			/*
			  'in_stock' => array(
			  'title' => __('Stock status', 'content-egg'),
			  'description' => __('Filter results according to the program\'s status.', 'content-egg'),
			  'callback' => array($this, 'render_dropdown'),
			  'dropdown_options' => array(
			  'yes' => __('In stock', 'content-egg'),
			  'no' => __('Out of stock', 'content-egg'),
			  ),
			  'default' => 'yes',
			  ),
			 *
			 */
			'has_images'              => array(
				'title'       => __( 'Has images', 'content-egg' ),
				'description' => __( 'Show only products with images.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
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
			'joined'                  => array(
				'title'            => __( 'Program status', 'content-egg' ),
				'description'      => __( 'Filter results according to the program\'s status.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'yes' => __( 'Joined', 'content-egg' ),
					'no'  => __( 'Not joined', 'content-egg' ),
					'all' => __( 'All', 'content-egg' ),
				),
				'default'          => 'yes',
			),
			'save_img'                => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
		);

		$parent = parent::options();

		//$parent['update_mode']['default'] = 'cron';
		return array_merge( $parent, $options );
	}

}
