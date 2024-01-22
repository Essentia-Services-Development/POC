<?php

namespace ContentEgg\application\modules\Impactradius;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * ImpactradiusConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 */
class ImpactradiusConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'AccountSid'              => array(
				'title'       => 'Account SID <span class="cegg_required">*</span>',
				'description' => __( 'Special key for Impactradius API. Get it <a href="https://member.impactradius.com/secure/mediapartner/accountSettings/mp-wsapi-flow.ihtml">here</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Account SID' ),
					),
				),
			),
			'AuthToken'               => array(
				'title'       => 'Auth Token <span class="cegg_required">*</span>',
				'description' => __( 'Your password for API is your Auth Token. Get it <a href="https://member.impactradius.com/secure/mediapartner/accountSettings/mp-wsapi-flow.ihtml">here</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Auth Token' ),
					),
				),
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
						'arg'     => 200,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 200 ),
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
						'arg'     => 200,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 200 ),
					),
				),
			),
			'minprice'                => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'maxprice'                => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'in_stock'                => array(
				'title'       => __( 'In stock', 'content-egg' ),
				'description' => __( 'Search only products in stock.', 'content-egg' ) .
				                 '<p class="description">' . __( 'Note, some advertisers do not have stock status for their products. For this case, try to disable this filter to find products.', 'content-egg' ) . '</p>',
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
