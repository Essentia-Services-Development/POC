<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

use WP_REST_Request;
use WP_REST_Server;

class Offerbox extends Basic {
	protected $name = 'offerbox';

	protected $attributes = array(
		'name'             => array(
			'type'    => 'string',
			'default' => '',
		),
		'titleTag'        => array(
			'type'    => 'string',
			'default' => 'h3',
		),
		'description'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'disclaimer'       => array(
			'type'    => 'string',
			'default' => '',
		),
		'old_price'        => array(
			'type'    => 'string',
			'default' => '',
		),
		'sale_price'       => array(
			'type'    => 'string',
			'default' => '',
		),
		'coupon_code'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'expiration_date'  => array(
			'type'    => 'string',
			'default' => '',
		),
		'mask_coupon_code' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'mask_coupon_text' => array(
			'type'    => 'string',
			'default' => '',
		),
		'offer_is_expired' => array(
			'default' => false,
		),
		'button'           => array(
			'type'    => 'object',
			'default' => array(
				'text'     => 'Buy this item',
				'url'      => '',
				'newTab'   => false,
				'noFollow' => false,
			),
		),
		'thumbnail'        => array(
			'type'    => 'object',
			'default' => array(
				'id'     => '',
				'url'    => '',
				'width'  => '',
				'height' => '',
				'imagehtml' => ''
			),
		),
		'brand_logo_url'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'discount_tag'     => array(
			'type'    => 'number',
			'default' => 0
		),
		'rating'           => array(
			'type'    => 'number',
			'default' => 0,
		),
		'selectedPost'     => array(
			'type'    => 'string',
			'default' => '',
		),
		'borderColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'schemaenable' => array(
			'type' => 'boolean',
			'default' => false,
		),
		'schemafields'  => array(
			'type'    => 'object',
			'default' => array(
				'mpn'     => '12345',
				'sku'     => '999GC',
				'count'      => 5,
				'currency'   => 'USD',
				'brand' => 'Brand',
				'price'=> ''
			),
		),
	);

	protected function process_inline_styles( $color ) {
		$css = '';

		if ( ! empty( $color ) ) {
			$css .= "border: 2px solid {$color};";
		}

		return $css;
	}

	protected function render( $settings = array(), $inner_content = '' ) {
		$offer_post_url         = $settings['button']['url'];
		$offer_url              = $settings['button']['url'];
		$offer_price            = $settings['sale_price'];
		$offer_price_old        = $settings['old_price'];
		$offer_title            = $settings['name'];
		$offer_thumb            = $settings['thumbnail']['url'];
		$offer_thumbhtml        = (!empty($settings['thumbnail']['imagehtml'])) ? $settings['thumbnail']['imagehtml'] : '';
		$offer_btn_text         = $settings['button']['text'];
		$offer_coupon           = $settings['coupon_code'];
		$offer_coupon_date      = $settings['expiration_date'];
		$offer_coupon_mask      = $settings['mask_coupon_code'];
		$offer_desc             = $settings['description'];
		$disclaimer             = $settings['disclaimer'];
		$rating                 = $settings['rating'];
		$percentageSaved        = $settings['discount_tag'];
		$offer_coupon_mask_text = $settings['mask_coupon_text'];
		$border_color           = $settings['borderColor'];
		$title_tag        		= $settings['titleTag'];
		$schemaenable           = $settings['schemaenable'];
		$schemafields           = $settings['schemafields'];
		$inline_styles          = $this->process_inline_styles( $border_color );

		require( rh_locate_template( 'inc/parts/offerbigpart.php' ) );
	}
}
