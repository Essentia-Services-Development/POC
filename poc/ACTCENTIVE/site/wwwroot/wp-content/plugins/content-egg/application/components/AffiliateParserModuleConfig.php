<?php

namespace ContentEgg\application\components;

defined( '\ABSPATH' ) || exit;

/**
 * ParserModuleConfig abstract class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
abstract class AffiliateParserModuleConfig extends ParserModuleConfig {

	public function options() {
		$options = array(
			'ttl' => array(
				'title'       => __( 'Update by keyword', 'content-egg' ),
				'description' => __( 'Lifetime of cache in seconds, after this period products will be updated if you set keyword for updating. 0 - never update', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 2592000,
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
		);

		if ( $this->getModuleInstance()->isItemsUpdateAvailable() ) {
			$options['ttl_items'] = array(
				'title'       => __( 'Price update', 'content-egg' ),
				'description' => __( 'Time in seconds for updating prices, availability, etc. 0 - never update', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 604800,
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			);
		}
		$options['update_mode'] = array(
			'title'            => __( 'Update mode', 'content-egg' ),
			'description'      => '',
			'callback'         => array( $this, 'render_dropdown' ),
			'dropdown_options' => array(
				'visit'      => __( 'Page view', 'content-egg' ),
				'cron'       => __( 'Cron', 'content-egg' ),
				'visit_cron' => __( 'Page view + Cron', 'content-egg' ),
			),
			'default'          => 'visit',
		);

		return
			array_merge(
				parent::options(), $options
			);
	}


}
