<?php

namespace ContentEgg\application\modules\AdmitadProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AdmitadProductsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class AdmitadProductsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'offer_id'                => array(
				'title'       => __( 'Offer ID', 'content-egg' ) . ' ' . '<span class="cegg_required">*</span>',
				'description' => __( 'You can work only with offers, which are available on <a target="_blank" href="https://www.admitadgoods.ru/offers.php">this page</a>.', 'content-egg' )
				                 . ' ' . __( 'You can find offer ID in URL, when you click on offer logo.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Offer ID" can not be empty', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'deeplink'                => array(
				'title'       => 'Deeplink' . ' ' . '<span class="cegg_required">*</span>',
				'description' => __( 'Deeplink of offer.', 'content-egg' )
				                 . ' ' . __( '<a target="_blank" href="http://www.keywordrush.com/en/docs/content-egg/DeeplinkSettings.html">Manual</a> for deeplink settings for different CPA-networks.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call' => array( '\ContentEgg\application\components\Cpa', 'deeplinkPrepare' ),
						'type' => 'filter'
					),
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Deeplink" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one search query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 20,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 20,
						'message' => __( 'Field "Results" can not be more than 20.', 'content-egg' ),
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
						'arg'     => 20,
						'message' => __( 'Field "Results for autoupdating" can not be more than 20.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'only_sale'               => array(
				'title'       => __( 'Discount', 'content-egg' ),
				'description' => __( 'Only products with discount.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'price_from'              => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'price_to'                => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
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

		$parent                           = parent::options();
		$parent['ttl_items']['validator'] = array(
			'trim',
			'absint',
			array(
				'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'greater_than' ),
				'arg'     => 86400,
				'message' => sprintf( __( 'The field "%s" can not be less than  %d.', 'content-egg' ), __( 'Update products', 'content-egg' ), 86400 ),
			),
		);
		$parent['ttl']['validator']       = array(
			'trim',
			'absint',
			array(
				'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'greater_than' ),
				'arg'     => 259200,
				'message' => sprintf( __( 'The field "%s" can not be less than  %d.', 'content-egg' ), __( 'Automatic update', 'content-egg' ), 259200 ),
			),
		);

		return array_merge( $parent, $optiosn );
	}

}
