<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

use WP_REST_Request;
use WP_REST_Server;

class PostOfferbox extends Basic {
	protected $name = 'post-offerbox';

	protected $attributes = array(
		'selectedPost' => array(
			'type'    => 'string',
			'default' => '',
		),
		'titleTag'        => array(
			'type'    => 'string',
			'default' => 'h3',
		),
	);


	protected function render( $settings = array(), $inner_content = '' ) {
		$id = $settings['selectedPost'];

		if ( empty( $id ) ) {
			return '';
		}

		$offer_post_url         = get_post_meta( $id, 'rehub_offer_product_url', true );
		$offer_post_url         = apply_filters( 'rehub_create_btn_url', $offer_post_url );
		$offer_url              = apply_filters( 'rh_post_offer_url_filter', $offer_post_url );
		$offer_price            = get_post_meta( $id, 'rehub_offer_product_price', true );
		$offer_price_old        = get_post_meta( $id, 'rehub_offer_product_price_old', true );
		$offer_title            = get_post_meta( $id, 'rehub_offer_name', true );
		$offer_thumb            = get_post_meta( $id, 'rehub_offer_product_thumb', true );
		$offer_btn_text         = get_post_meta( $id, 'rehub_offer_btn_text', true );
		$offer_coupon           = get_post_meta( $id, 'rehub_offer_product_coupon', true );
		$offer_coupon_date      = get_post_meta( $id, 'rehub_offer_coupon_date', true );
		$offer_coupon_mask      = get_post_meta( $id, 'rehub_offer_coupon_mask', true );
		$offer_desc             = get_post_meta( $id, 'rehub_offer_product_desc', true );
		$disclaimer             = get_post_meta( $id, 'rehub_offer_disclaimer', true );
		$rating                 = get_post_meta( $id, 'rehub_review_overall_score', true );
		$title_tag        		= $settings['titleTag'];
		$offer_coupon_mask_text = '';

		if ( $rating ) {
			$rating = $rating / 2;
		}

		if ( empty( $offer_title ) ) {
			$offer_title = get_the_title( $id );
		}

		if ( empty( $offer_thumb ) ) {
			$offer_thumb = get_the_post_thumbnail_url( $id );
		}

		if ( empty( $offer_btn_text ) ) {
			if ( ! empty( \REHub_Framework::get_option( 'rehub_btn_text' ) ) ) {
				$offer_btn_text = \REHub_Framework::get_option( 'rehub_btn_text' );
			} else {
				$offer_btn_text = 'Buy this item';
			}
		}

		require( rh_locate_template( 'inc/parts/offerbigpart.php' ) );
	}
}
