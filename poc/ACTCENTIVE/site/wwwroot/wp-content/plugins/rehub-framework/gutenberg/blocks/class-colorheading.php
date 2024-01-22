<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class ColorHeading extends Basic {
	protected $name = 'color-heading';

	protected $attributes = array(
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
		'backgroundColor' => array(
			'type'    => 'string',
			'default' => '#ebf2fc',
		),
		'titleColor'      => array(
			'type'    => 'string',
			'default' => '#111'
		),
		'subtitleColor'   => array(
			'type'    => 'string',
			'default' => '#111'
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		$html            = '';
		$title           = $settings['title'];
		$subtitle        = $settings['subtitle'];
		$styles          = 'background-color:' . $settings['backgroundColor'] . ';';
		$title_styles    = 'color:' . $settings['titleColor'] . ';';
		$subtitle_styles = 'color:' . $settings['subtitleColor'] . ';';
		$title_tag        = $settings['titleTag'];

		if ( empty( $title ) && empty( $subtitle ) ) {
			return;
		}
		$anchormatches = preg_match( '/id="([^"]*)"/', $inner_content, $matches );
		
		if(!empty($matches[1])){
			$anchor = $matches[1];
		}else{
			$anchor = rh_convert_cyr_symbols($subtitle);
			$anchor = str_replace(array('\'', '"'), '', $anchor); 
			$spec = preg_quote( '\'.+$*~=' );
			$anchor = preg_replace("/[^a-zA-Z0-9_$spec\-]+/", '-', $anchor );
			$anchor = strtolower( trim( $anchor, '-') );
			$anchor = substr( $anchor, 0, 70 );
		}


	    $paddingcss = (\REHub_Framework::get_option('rehub_content_shadow')) ? '' : ' pr25 pl25';

		$html .= '<div class="rh-color-heading alignfull pt30 pb30 blackcolor mb35" style="' . esc_attr( $styles ) . '">';
		$html .= '<style scoped>.main-side:not(.alignfulloutside):not(.fullgutenberg) .rh-color-heading .rh-container{width:auto;}</style>';
		$html .= '	<div class="rh-container'.$paddingcss.'">';
		$html .= '			<p id="'.$anchor.'" class="mb15 font130" style="' . esc_attr( $subtitle_styles ) . '">';
		$html .= '			' . $subtitle . '';
		$html .= '			</p>';
		$html .= '			<' . $title_tag . ' class="mt0 mb10 font200" style="' . esc_attr( $title_styles ) . '">';
		$html .= '			' . $title . '';
		$html .= '			</' . $title_tag . '>';
		$html .= '	</div>';
		$html .= '</div>';

		echo $html;
	}
}