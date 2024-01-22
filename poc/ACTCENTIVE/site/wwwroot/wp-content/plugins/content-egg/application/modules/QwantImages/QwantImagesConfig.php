<?php

namespace ContentEgg\application\modules\QwantImages;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * QwantImagesConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class QwantImagesConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for a single query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 10,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 100,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), __( 'Results', 'content-egg' ), 150 ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for autoblogging ', 'content-egg' ),
				'description' => __( 'Number of results for autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 6,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 100,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), __( 'Results', 'content-egg' ), 150 ),
					),
				),
				'section'     => 'default',
			),
			'safesearch'              => array(
				'title'            => __( 'Safe search', 'content-egg' ),
				'description'      => __( 'Filter images for adult content.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''   => __( 'Return images with adult content', 'content-egg' ),
					'on' => __( 'Do not return images with adult content', 'content-egg' ),
				),
				'default'          => 'on',
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

	static public function marketCodes() {
		$codes = array(
			'es-AR',
			'en-AU',
			'de-AT',
			'nl-BE',
			'fr-BE',
			'pt-BR',
			'en-CA',
			'fr-CA',
			'es-CL',
			'da-DK',
			'fi-FI',
			'fr-FR',
			'de-DE',
			'zh-HK',
			'en-IN',
			'en-ID',
			'en-IE',
			'it-IT',
			'ja-JP',
			'ko-KR',
			'en-MY',
			'es-MX',
			'nl-NL',
			'en-NZ',
			'no-NO',
			'zh-CN',
			'pl-PL',
			'pt-PT',
			'en-PH',
			'ru-RU',
			'ar-SA',
			'en-ZA',
			'es-ES',
			'sv-SE',
			'fr-CH',
			'de-CH',
			'zh-TW',
			'tr-TR',
			'en-GB',
			'en-US',
			'es-US'
		);

		return array_combine( $codes, $codes );
	}

}
