<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

class Slider{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__ . '/slider', array(
			'attributes'      => $this->attributes,
			'render_callback' => array( $this, 'render_block' ),
		));
	}

	public $attributes = array(
		'slides' => array(
			'type'    => 'object',
			'default' => array(
				array(
					'image' => array(
						'id'     => '',
						'url'    => '',
						'width'  => '',
						'height' => '',
						'alt'    => '',
						'caption' => ''
					),
				),
			),
		),
	);
	public function render_block( $settings = array(), $inner_content = '' ) {
		$html       = '';
		$slides     = $settings['slides'];
		$random_key = rand( 0, 100 );

		if ( empty( $slides ) ) {
			return;
		}

		wp_enqueue_script( 'modulobox' );
		wp_enqueue_style( 'modulobox' );

		$html .= '<div class="rh-slider js-hook__slider mb25 width-100p">';
		$html .= ' <div class="rh-slider__wrapper">';
		$html .= '	<div class="rh-slider__inner modulo-lightbox">';
		foreach ( $slides as $slide ) {
			$url    = $slide['image']['url'];
			$alt    = $slide['image']['alt'];
			$caption = (!empty($slide['image']['caption'])) ? $slide['image']['caption'] : '';
			$width  = $slide['image']['width'];
			$height = $slide['image']['height'];
			if ( empty( $url ) ) {
				$url = plugin_dir_url( __DIR__ ) . '/assets/icons/noimage-placeholder.png';
			}
			$html .= '<a class="rh-slider-item" data-rel="slider_' . $random_key . '" href="' . esc_attr( $url ) . '" data-thumb="' . esc_attr( $url ) . '" target="_blank" ">';
			$html .= '<span class="rh-slider-caption">'.$caption.'</span>';
			$html .= '  <img src="' . esc_attr( $url ) . '" alt="' . esc_attr( $alt ) . '"';
			$html .= '       width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"/>';
			$html .= '</a>';
		}
		$html .= '</div>';
		$html .= '<div class="rh-slider-controls">';
		$html .= '<div class="rh-slider-arrow rh-slider-arrow--prev"><i class="rhicon rhi-chevron-left"></i></div>';
		$html .= '<div class="rh-slider-arrow rh-slider-arrow--next"><i class="rhicon rhi-chevron-right"></i></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '	<div class="rh-slider-thumbs rhscrollthin">';
		$html .= '		<div class="rh-slider-thumbs__row">';
		foreach ( $slides as $key => $slide ) {
			$url    = $slide['image']['url'];
			$alt    = $slide['image']['alt'];
			$width  = $slide['image']['width'];
			$height = $slide['image']['height'];
			if ( empty( $url ) ) {
				$url = plugin_dir_url( __DIR__ ) . '/assets/icons/noimage-placeholder.png';
			}
			$html .= '<div class="rh-slider-thumbs-item" data-slide ="' . $key . '">';
			$html .= '	<img src="' . esc_attr( $url ) . '" alt="' . esc_attr( $alt ) . '"';
			$html .= '       width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" />';
			$html .= '</div>';
		}
		$html .= '		</div>';
		$html .= '	</div>';
		$html .= '</div>';

		return $html;


	}

}