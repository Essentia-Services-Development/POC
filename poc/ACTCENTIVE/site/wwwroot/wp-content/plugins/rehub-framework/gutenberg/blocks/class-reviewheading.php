<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

class ReviewHeading{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__ . '/reviewheading', array(
			'attributes'      => $this->attributes,
			'render_callback' => array( $this, 'render_block' ),
		));
	}

	public $attributes = array(
		'includePosition' => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'position'        => array(
			'type'    => 'string',
			'default' => '',
		),
		'title'           => array(
			'type'    => 'string',
			'default' => '',
		),
		'titleTag'        => array(
			'type'    => 'string',
			'default' => 'h2',
		),
		'subtitle'        => array(
			'type'    => 'string',
			'default' => '',
		),
		'includeImage'    => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'image'           => array(
			'type'    => 'object',
			'default' => array(
				'id'     => '',
				'url'    => '',
				'width'  => '',
				'height' => '',
				'alt'    => '',
			),
		),
		'link'            => array(
			'type'    => 'string',
			'default' => '',
		),
	);

	public function render_block( $settings = array(), $inner_content = '' ) {
		$html             = '';
		$include_position = $settings['includePosition'];
		$position         = $settings['position'];
		$title            = $settings['title'];
		$title_tag        = $settings['titleTag'];
		$subtitle         = $settings['subtitle'];
		$include_image    = $settings['includeImage'];
		$image            = $settings['image'];
		$link             = $settings['link'];

		if(!$position) $position = '1';

		$html .= '<div class="rh-review-heading rh-flex-center-align mb25">';

		if ( $include_position ) {
			$html .= '	<div class="rh-review-heading__position mr15 font150">';
			$html .= '		<span class="fontbold lightgreycolor font250">' . esc_html( $position ) . '</span>';
			$html .= '	</div>';
		}
		
		$anchormatches = preg_match( '/id="([^"]*)"/', $inner_content, $matches );
		
		if(!empty($matches[1])){
			$anchor = $matches[1];
		}else{
			$subtitleclean = strip_tags($subtitle);
			$anchor = rh_convert_cyr_symbols($subtitleclean);
			$anchor = str_replace(array('\'', '"'), '', $anchor); 
			$spec = preg_quote( '\'.+$*~=' );
			$anchor = preg_replace("/[^a-zA-Z0-9_$spec\-]+/", '-', $anchor );
			$anchor = strtolower( trim( $anchor, '-') );
			$anchor = substr( $anchor, 0, 70 );
		}

		$html .= '	<div id="'.$anchor.'">';
		$html .= '		<' . $title_tag . ' class="mt0 mb0">' . do_shortcode( $title ) . '</' . $title_tag . '>';
		$html .= '		<div class="mt5 lineheight20 greycolor">' . do_shortcode( $subtitle ) . '</div>';
		$html .= '	</div>';

		if ( $include_image && ! empty( $image['url'] ) ) {
			$html .= '	<a class="rh-review-heading__logo rh-flex-right-align blockstyle" href="' . esc_url( $link ) . '">';
			$html .= '		<div class="rh-review-heading__logo-container">';
			$html .= '			<img src="' . esc_url( $image['url'] ) . '" alt="' . esc_attr( $image['alt'] ) . '"';
			$html .= '              width="' . esc_attr( $image['width'] ) . '" height="' . esc_attr( $image['height'] ) . '"/>';
			$html .= '		</div>';
			$html .= '	</a>';
		}

		$html .= '</div>';

		return $html;
	}

}