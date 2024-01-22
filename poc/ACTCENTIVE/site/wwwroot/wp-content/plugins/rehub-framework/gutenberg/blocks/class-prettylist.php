<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class PrettyList extends Basic {
	protected $name = 'pretty-list';

	protected $attributes = array(
		'type'  => array(
			'type'    => 'string',
			'default' => 'arrow',
		),
		'items' => array(
			'type'    => 'object',
			'default' => array(
				array(
					'text' => 'Sample Item #1',
				),
				array(
					'text' => 'Sample Item #2',
				),
				array(
					'text' => 'Sample Item #3',
				),
			),
		),
	);

	private function generate_list( $items = array() ) {
		$list = '<ul>';

		foreach ( $items as $item ) {
			$list .= '<li>' . do_shortcode( $item['text'] ) . '</li>';
		}

		$list .= '</ul>';

		return $list;
	}

	protected function render( $settings = array(), $inner_content = '' ) {
		$type  = $settings['type'];
		$items = $settings['items'];

		if ( ! function_exists( 'wpsm_list_shortcode' ) || empty( $items ) ) {
			return;
		}

		echo wpsm_list_shortcode( array( 'type' => $type ), $this->generate_list( $items ) );
	}
}