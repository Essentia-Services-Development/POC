<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class WCBox extends Basic {
	protected $name = 'wc-box';

	protected $attributes = array(
		'productId' => array(
			'type'    => 'string',
			'default' => '',
		),
		'titleTag'        => array(
			'type'    => 'string',
			'default' => 'h3',
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		$id = $settings['productId'];
		$title_tag        = $settings['titleTag'];

		if ( empty( $id ) ) {
			return;
		}

		if ( function_exists( 'rehub_get_woo_offer' ) ) {
			echo rehub_get_woo_offer( $id, $title_tag );
		}
	}
}