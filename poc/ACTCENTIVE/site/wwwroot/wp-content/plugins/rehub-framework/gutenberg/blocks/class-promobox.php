<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class PromoBox extends Basic {
	protected $name = 'promo-box';

	protected $attributes = array(
		'title'               => array(
			'type'    => 'string',
			'default' => 'Sample title',
		),
		'content'             => array(
			'type'    => 'string',
			'default' => 'Sample content',
		),
		'backgroundColor'     => array(
			'type'    => 'string',
			'default' => '#f8f8f8',
		),
		'textColor'           => array(
			'type'    => 'string',
			'default' => '#333',
		),
		'showBorder'          => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'borderSize'          => array(
			'type'    => 'number',
			'default' => 1,
		),
		'borderColor'         => array(
			'type'    => 'string',
			'default' => '#dddddd',
		),
		'showHighlightBorder' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'highlightColor'      => array(
			'type'    => 'string',
			'default' => '#fb7203',
		),
		'highlightPosition'   => array(
			'type'    => 'string',
			'default' => 'Left',
		),
		'showButton'          => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'buttonText'          => array(
			'type'    => 'string',
			'default' => 'Purchase Now',
		),
		'buttonLink'          => array(
			'type'    => 'string',
			'default' => '',
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		if ( ! function_exists( 'wpsm_promobox_shortcode' ) ) {
			return;
		}

		$attributes = array(
			'background'  => $settings['backgroundColor'],
			'title'       => $settings['title'],
			'description' => $settings['content'],
			'text_color'  => $settings['textColor'],
		);

		if ( $settings['showBorder'] ) {
			$attributes['border_size']  = $settings['borderSize'] . 'px';
			$attributes['border_color'] = $settings['borderColor'];
		}

		if ( $settings['showHighlightBorder'] ) {
			$attributes['highligh_color']     = $settings['highlightColor'];
			$attributes['highlight_position'] = strtolower( $settings['highlightPosition'] );
		}

		if ( $settings['showButton'] ) {
			$attributes['button_link'] = $settings['buttonLink'];
			$attributes['button_text'] = $settings['buttonText'];
		}

		echo wpsm_promobox_shortcode( $attributes );
	}
}