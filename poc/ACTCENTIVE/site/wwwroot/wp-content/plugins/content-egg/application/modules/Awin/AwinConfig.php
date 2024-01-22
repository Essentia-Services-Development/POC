<?php

namespace ContentEgg\application\modules\Awin;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateFeedParserModuleConfig;

/**
 * AwinConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AwinConfig extends AffiliateFeedParserModuleConfig {

	public function options() {
		$optiosn = array(
			'datafeed_url'            => array(
				'title'       => 'Datafeed Download URL <span class="cegg_required">*</span>',
				'description' => sprintf( __( 'Go to ToolBox -> <a target="_blank" href="%s">Create-a-Feed</a>. Read more <a href="%s">here</a>.', 'content-egg' ), 'http://wiki.awin.com/index.php/Downloading_feeds_using_Create-a-Feed', 'https://ce-docs.keywordrush.com/modules/affiliate/awin' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Datafeed Download URL' ),
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
			'in_stock'                => array(
				'title'       => __( 'In stock', 'content-egg' ),
				'description' => __( 'Search only products in stock.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
				'section'     => 'default',
			),
			'partial_url_match'       => array(
				'title'       => __( 'Search partial URL', 'content-egg' ),
				'description' => __( 'Partial URL match', 'content-egg' )
				                 . '<p class="description">' . __( 'You can use part of a URL to search for products by URL.', 'content-egg' ) . '</p>',
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
		);

		return array_merge( parent::options(), $optiosn );
	}

}
