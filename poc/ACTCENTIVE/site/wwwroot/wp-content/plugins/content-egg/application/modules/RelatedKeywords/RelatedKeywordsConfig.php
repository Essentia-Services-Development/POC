<?php

namespace ContentEgg\application\modules\RelatedKeywords;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * RelatedKeywordsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class RelatedKeywordsConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'subscription_key'        => array(
				'title'       => 'Subscription Key <span class="cegg_required">*</span>',
				'description' => __( 'Key access to Bing Autosuggest API. You can get <a href="https://azure.microsoft.com/en-us/try/cognitive-services/?api=autosuggest-api">here</a>.', 'content-egg' ) .
				                 __( 'You can set several Subscription Keys with commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The "%s" can not be empty.', 'content-egg' ), 'Subscription Key' ),
					),
				),
				'section'     => 'default',
			),
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
						'arg'     => 150,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), __( 'Results', 'content-egg' ), 150 ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for autoblogging ', 'content-egg' ),
				'description' => __( 'Number of results for autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 5,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 150,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), __( 'Results', 'content-egg' ), 150 ),
					),
				),
				'section'     => 'default',
			),
			'mkt'                     => array(
				'title'            => __( 'Market code', 'content-egg' ),
				'description'      => __( 'The market where the results come from. The market must be in the form [language code]-[country code].', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array_merge( array( '' => __( '(unspecified)', 'content-egg' ) ), self::marketCodes() ),
				'default'          => '',
			),
		);
		$parent  = parent::options();
		unset( $parent['featured_image'] );

		return array_merge( $parent, $optiosn );
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
