<?php

namespace ContentEgg\application\modules\Affiliatewindow;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AffiliatewindowConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class AffiliatewindowConfig extends AffiliateParserModuleConfig {

	//e2ca1aa65eed1cfd8d6297450f3558fd

	public function options() {
		$optiosn = array(
			'api_key'                 => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'Access key for ProductServe API (ShopWindow Client) V3. You can get it <a href="https://www.affiliatewindow.com/affiliates/accountdetails.php">here</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "API Key" can not be empty', 'content-egg' ),
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
			'sSort'                   => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''           => __( 'Default', 'content-egg' ),
					'az'         => 'Alphabetical increasing',
					'za'         => 'Alphabetical decreasing',
					'hi'         => 'By price, decreasing',
					'lo'         => 'By price, increasing',
					'popularity' => 'By popularity, decreasing',
					'random'     => 'Randomly',
					'relevancy'  => 'More relevant products to the specified query will appear first',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'merchantID'              => array(
				'title'       => 'Merchant ID',
				'description' => __( 'You can set several Merchant IDs with commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			/*
			  'categoryID' => array(
			  'title' => 'Category ID',
			  'description' => __('Список категорий можно найти <a href="http://wiki.affiliatewindow.com/index.php/ShopWindow_Appendix_2_Category_IDs">здесь</a>.', 'content-egg') . ' ' . __('Вы можете задать несколько Category IDs через запятую.', 'content-egg'),
			  'callback' => array($this, 'render_input'),
			  'default' => '',
			  'validator' => array(
			  'trim',
			  ),
			  'section' => 'default',
			  ),
			 *
			 */
			'sMode'                   => array(
				'title'            => __( 'Search mode', 'content-egg' ),
				'description'      => __( 'Details about different search modes <a href="http://wiki.affiliatewindow.com/index.php/ShopWindow_Search_Modes_v3">here</a>.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''         => __( 'Default', 'content-egg' ),
					'phrase'   => 'phrase',
					'all'      => 'all',
					'any'      => 'any',
					'boolean'  => 'boolean',
					'extended' => 'extended',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'bHotPick'                => array(
				'title'       => 'Top products',
				'description' => 'Include only the advertisers top products.',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'iAdult'                  => array(
				'title'       => 'Adult content',
				'description' => 'Allow adult content.',
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
