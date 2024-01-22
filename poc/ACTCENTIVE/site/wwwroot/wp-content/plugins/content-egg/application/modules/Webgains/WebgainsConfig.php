<?php

namespace ContentEgg\application\modules\Webgains;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateFeedParserModuleConfig;

/**
 * WebgainsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class WebgainsConfig extends AffiliateFeedParserModuleConfig {

	public function options() {
		$options = array(
			'datafeed_url' => array(
				'title'       => 'Product Feed URL <span class="cegg_required">*</span>',
				'description' => sprintf( __( 'Check <a target="_blank" href="%s">how to create</a> a Product Feed.', 'content-egg' ), 'https://ce-docs.keywordrush.com/modules/affiliate/webgains' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Product Feed URL' ),
					),
				),
			),
		);
		$options = ( array_merge( parent::options(), $options ) );

		return self::moveRequiredUp( $options );
	}

}
