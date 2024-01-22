<?php

namespace ContentEgg\application\modules\BingImages;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * BingImagesConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class BingImagesConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'subscription_key'        => array(
				'title'       => 'Subscription Key <span class="cegg_required">*</span>',
				'description' => __( 'Key access to Bing Search API. You can get <a href="https://azure.microsoft.com/en-us/try/cognitive-services/?api=bing-image-search-api">here</a>.', 'content-egg' )
				                 . ' ' . __( 'You can set several Subscription Keys with commas.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Subscription Key' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for a single query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 16,
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
				'default'     => 6,
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
			'safeSearch'              => array(
				'title'            => __( 'Safe search', 'content-egg' ),
				'description'      => __( 'Filter images for adult content.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'Off'      => __( 'Return images with adult content', 'content-egg' ),
					'Moderate' => __( 'Do not return images with adult content', 'content-egg' ),
					//'Strict' => __('Do not return images with adult content', 'content-egg'),
				),
				'default'          => 'Moderate',
			),
			'license'                 => array(
				'title'            => __( 'License', 'content-egg' ),
				'description'      => __( 'Filter images by the type of license applied to the image. <a target="_blank" href="http://help.bing.microsoft.com/#apex/18/en-us/10006/0">Read more</a>.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                   => __( 'All', 'content-egg' ),
					'Any'                => __( 'Any', 'content-egg' ),
					'Public'             => __( 'Public', 'content-egg' ),
					'Share'              => __( 'Share', 'content-egg' ),
					'ShareCommercially'  => __( 'Share commercially ', 'content-egg' ),
					'Modify'             => __( 'Modify', 'content-egg' ),
					'ModifyCommercially' => __( 'Modify commercially', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'aspect'                  => array(
				'title'            => __( 'Aspect', 'content-egg' ),
				'description'      => __( 'Filter images by aspect ratio.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''       => __( 'All', 'content-egg' ),
					'Square' => __( 'Square', 'content-egg' ),
					'Wide'   => __( 'Wide', 'content-egg' ),
					'Tall'   => __( 'Tall', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'color'                   => array(
				'title'            => __( 'Color', 'content-egg' ),
				'description'      => __( 'Filter images by color.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''           => __( 'All', 'content-egg' ),
					'ColorOnly'  => __( 'Color images', 'content-egg' ),
					'Monochrome' => __( 'Black and white', 'content-egg' ),
					'Black'      => __( 'Black', 'content-egg' ),
					'Blue'       => __( 'Blue', 'content-egg' ),
					'Brown'      => __( 'Brown', 'content-egg' ),
					'Gray'       => __( 'Gray', 'content-egg' ),
					'Green'      => __( 'Green', 'content-egg' ),
					'Orange'     => __( 'Orange', 'content-egg' ),
					'Pink'       => __( 'Pink', 'content-egg' ),
					'Purple'     => __( 'Purple', 'content-egg' ),
					'Red'        => __( 'Red', 'content-egg' ),
					'Teal'       => __( 'Teal', 'content-egg' ),
					'White'      => __( 'White', 'content-egg' ),
					'Yellow'     => __( 'Yellow', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'freshness'               => array(
				'title'            => __( 'Freshness', 'content-egg' ),
				'description'      => __( 'Filter images by when Bing discovered the image.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''      => __( 'All', 'content-egg' ),
					'Day'   => __( 'Last 24 hours', 'content-egg' ),
					'Week'  => __( 'Last 7 days', 'content-egg' ),
					'Month' => __( 'Last 30 days', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'imageContent'            => array(
				'title'            => __( 'Image content', 'content-egg' ),
				'description'      => __( 'Filter images by content.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''         => __( 'All', 'content-egg' ),
					'Face'     => __( "Person's face", 'content-egg' ),
					'Portrait' => __( "Person's head and shoulders", 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'imageType'               => array(
				'title'            => __( 'Image type', 'content-egg' ),
				'description'      => __( 'Filter images by image type.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''            => __( 'All', 'content-egg' ),
					'AnimatedGif' => __( 'Animated GIFs', 'content-egg' ),
					'Clipart'     => __( 'Clip art images', 'content-egg' ),
					'Line'        => __( 'Line drawings', 'content-egg' ),
					'Photo'       => __( 'Photographs', 'content-egg' ),
					'Shopping'    => __( 'Shopping', 'content-egg' ),
					'Transparent' => __( 'Transparent', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'size'                    => array(
				'title'            => __( 'Size', 'content-egg' ),
				'description'      => __( 'Filter images by size.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                   => __( 'All', 'content-egg' ),
					'Small'              => __( 'Less than 200x200 pixels', 'content-egg' ),
					'Medium'             => __( 'Greater than or equal to 200x200 but less than 500x500', 'content-egg' ),
					'Large'              => __( '500x500 pixels or larger', 'content-egg' ),
					'Wallpaper'          => __( 'Wallpaper images', 'content-egg' ),
					'ModifyCommercially' => __( 'Modify commercially', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'domain_name'             => array(
				'title'       => __( 'Domain', 'content-egg' ),
				'description' => __( 'Limit the search to only that domain. For example ask: wikimedia.org', 'content-egg' ),
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
