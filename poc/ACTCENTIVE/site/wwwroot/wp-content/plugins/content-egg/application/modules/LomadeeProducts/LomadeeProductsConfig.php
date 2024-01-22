<?php

namespace ContentEgg\application\modules\LomadeeProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * LomadeeProductsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class LomadeeProductsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'sourceId'                => array(
				'title'       => 'Source ID <span class="cegg_required">*</span>',
				'description' => __( 'You can find your Source ID <a target="_blank" href="https://www.lomadee.com/dashboard/#/toolkit">here</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Source ID' ),
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
			'categoryId'              => array(
				'title'       => __( 'Category ID', 'content-egg' ),
				'description' => $this->getCategoryIdDesc(),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'storeId'                 => array(
				'title'       => __( 'Store ID', 'content-egg' ),
				'description' => $this->getStoreIdDesc(),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'sort'                    => array(
				'title'            => __( 'Sort', 'content-egg' ),
				'description'      => __( 'The way the offers are sorted.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'rating' => __( 'Rating', 'content-egg' ),
					'price'  => __( 'Price', 'content-egg' ),
				),
				'default'          => 'rating',
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

	protected function getCategoryIdDesc() {
		if ( ! $this->option( 'sourceId' ) ) {
			return;
		}
		$url = 'https://api.lomadee.com/v3/149919724709334e079c5/category/_all?sourceId=' . urlencode( $this->option( 'sourceId' ) ) . '&hasOffer=true';

		return sprintf( __( 'You can find it <a target="_blank" href="%s">here</a>.', 'content-egg' ), $url );
	}

	protected function getStoreIdDesc() {
		if ( ! $this->option( 'sourceId' ) ) {
			return;
		}
		$url = 'https://api.lomadee.com/v3/149919724709334e079c5/store/_all?sourceId=' . urlencode( $this->option( 'sourceId' ) ) . '&hasOffer=true';

		return sprintf( __( 'You can find it <a target="_blank" href="%s">here</a>.', 'content-egg' ), $url );
	}

}
