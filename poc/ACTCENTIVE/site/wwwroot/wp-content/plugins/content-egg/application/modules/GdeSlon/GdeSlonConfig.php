<?php

namespace ContentEgg\application\modules\GdeSlon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * GdeSlonConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class GdeSlonConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'api_key'                 => array(
				'title'       => __( 'API key', 'content-egg' ) . ' <span class="cegg_required">*</span>',
				'description' => __( 'You access key API. Go to -> "Tools" -> "XML API"', 'content-egg' ),
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
			'subid'                   => array(
				'title'       => 'SubID',
				'description' => __( 'Numeric or alphabet identificator for segment data about traffic. ', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
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
						'arg'     => 100,
						'message' => __( 'Field "Results" can not be more than 100.', 'content-egg' ),
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
						'arg'     => 100,
						'message' => __( 'Field "Results for autoupdating" can not be more than 100.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'order'                   => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'default'         => __( 'Default', 'content-egg' ),
					'price'           => __( 'Price', 'content-egg' ),
					'partner_benefit' => __( 'Partner benefit', 'content-egg' ),
					'newest'          => __( 'Newests', 'content-egg' ),
				),
				'default'          => 'default',
				'section'          => 'default',
			),
			'search_category'         => array(
				'title'       => __( 'Categories for search', 'content-egg' ),
				'description' => __( 'Limit search by categories. You can find Category ID <a target="_blank" href="http://api.gdeslon.ru/categories">here</a>. You can set multiple ID with comma.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'merchant_id'             => array(
				'title'       => __( 'Shop ID', 'content-egg' ),
				'description' => __( 'Limit search by definite shop. You can find shop ID <a target="_blank" href="http://api.gdeslon.ru/merchants">here</a>. You can set multiple ID with comma.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'no_m'                    => array(
				'title'       => __( 'Exclude Shop ID', 'content-egg' ),
				'description' => __( 'Exclude merchant from search. You can find shop ID <a target="_blank" href="http://api.gdeslon.ru/merchants">here</a>. You can set multiple ID with comma.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
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
			'parked_domain_name'      => array(
				'title'       => __( 'Parked domain', 'content-egg' ),
				'description' => __( 'Parked domain name (eg, http://example.com)', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'section'     => 'default',
			),
		);

		return array_merge( parent::options(), $optiosn );
	}

}
