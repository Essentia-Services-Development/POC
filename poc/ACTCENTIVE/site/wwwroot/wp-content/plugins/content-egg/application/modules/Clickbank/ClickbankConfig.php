<?php

namespace ContentEgg\application\modules\Clickbank;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * ClickbankConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ClickbankConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'nickname'                => array(
				'title'       => 'ClickBank nickname <span class="cegg_required">*</span>',
				'description' => __( 'Your nickname on ClickBank.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "ClickBank nickname" can not be empty.', 'content-egg' ),
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
						'arg'     => 50,
						'message' => __( 'The field "Results" can not be more than 50.', 'content-egg' ),
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
						'arg'     => 50,
						'message' => __( 'The field "Results" can not be more than 50.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'mainCategoryId'          => array(
				'title'            => __( 'Category ', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''      => 'All categories',
					'1253.' => 'Arts &amp; Entertainment',
					'1510.' => 'Betting Systems',
					'1266.' => 'Business / Investing',
					'1283.' => 'Computers / Internet',
					'1297.' => 'Cooking, Food &amp; Wine',
					'1308.' => 'E-business &amp; E-marketing',
					'1362.' => 'Education',
					'1332.' => 'Employment &amp; Jobs',
					'1338.' => 'Fiction',
					'1340.' => 'Games',
					'1344.' => 'Green Products',
					'1347.' => 'Health &amp; Fitness',
					'1366.' => 'Home &amp; Garden',
					'1377.' => 'Languages',
					'1392.' => 'Mobile',
					'1400.' => 'Parenting &amp; Families',
					'1408.' => 'Politics / Current Events',
					'1410.' => 'Reference',
					'1419.' => 'Self-Help',
					'1432.' => 'Software &amp; Services',
					'1461.' => 'Spirituality, New Age &amp; Alternative Beliefs',
					'1472.' => 'Sports',
					'1494.' => 'Travel',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'sortField'               => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                          => 'Keyword Relevance',
					'POPULARITY'                => 'Popularity',
					'AVERAGE_EARNINGS_PER_SALE' => 'Avg $/sale',
					'INITIAL_EARNINGS_PER_SALE' => 'Initial $/sale',
					'PCT_EARNINGS_PER_SALE'     => 'Avg %/sale',
					'TOTAL_REBILL'              => 'Avg Rebill Total',
					'PCT_EARNINGS_PER_REBILL'   => 'Avg %/rebill',
					'GRAVITY'                   => 'Gravity',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'gravityV1'               => array(
				'title'       => __( 'Minimum Gravity', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'productLanguages'        => array(
				'title'            => __( 'Language', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''   => __( 'Any', 'content-egg' ),
					'EN' => 'English',
					'DE' => 'German',
					'ES' => 'Spanish',
					'FR' => 'French',
					'IT' => 'Italian',
					'PT' => 'Portuguese',
				),
				'default'          => '',
				'section'          => 'default',
			),
			/*
			  'productTypes' => array(
			  'title' => __('Billing Type', 'content-egg'),
			  'description' => '',
			  'callback' => array($this, 'render_dropdown'),
			  'dropdown_options' => array(
			  '' => __('Any', 'content-egg'),
			  'standard' => 'One-time',
			  'rebill' => 'Recurring',
			  ),
			  'default' => '',
			  'section' => 'default',
			  ),
			 *
			 */
			'description_size'        => array(
				'title'       => __( 'Trim description', 'content-egg' ),
				'description' => __( 'Description size in characters (0 - do not cut)', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '0',
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
		);
		$parent  = parent::options();
		unset( $parent['featured_image'] );

		return array_merge( $parent, $optiosn );
	}

}
