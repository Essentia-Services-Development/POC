<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class Itinerary extends Basic {
	protected $name = 'itinerary';

	protected $attributes = array(
		'items' => array(
			'type'    => 'object',
			'default' => array(
				array(
					'icon'    => 'rhicon rhi-circle-solid',
					'color'   => '#409cd1',
					'content' => 'Box Content',
				),
				array(
					'icon'    => 'rhicon rhi-circle-solid',
					'color'   => '#409cd1',
					'content' => 'Box Content',
				),
				array(
					'icon'    => 'rhicon rhi-circle-solid',
					'color'   => '#409cd1',
					'content' => 'Box Content',
				),
			),
		),
	);

	protected function render( $settings = array(), $content = '' ) {
		$html  = '';
		$items = $settings['items'];

		wp_enqueue_style('rhitinerary');

		if ( empty( $items ) ) {
			echo '';
			return null;
		}

		$html .= '<div class="wpsm-itinerary">';

		foreach ( $items as $item ) {
			$icon    = $item['icon'];
			$color   = $item['color'];
			$content = $item['content'];

			$html .= '<div class="wpsm-itinerary-item">';
			$html .= '	<div class="wpsm-itinerary-icon">';
			$html .= '		<span style="background-color: ' . esc_attr( $color ) . '">';
			$html .= '			<i class="' . esc_attr( $icon ) . '"></i>';
			$html .= '		</span>';
			$html .= '	</div>';
			$html .= '	<div class="wpsm-itinerary-content">' . do_shortcode( $content ) . '</div>';
			$html .= '</div>';
		}

		$html .= '</div>';
		echo $html;
	}
}