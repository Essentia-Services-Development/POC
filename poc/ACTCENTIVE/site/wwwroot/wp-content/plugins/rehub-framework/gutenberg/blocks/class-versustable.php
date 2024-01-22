<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class VersusTable extends Basic {
	protected $name = 'versus-table';

	protected $attributes = array(
		'heading'      => array(
			'type'    => 'string',
			'default' => 'Versus Title',
		),
		'subheading'   => array(
			'type'    => 'string',
			'default' => 'Versus subline',
		),
		'type'         => array(
			'type'    => 'string',
			'default' => 'two',
		),
		'bg'           => array(
			'type'    => 'string',
			'default' => '',
		),
		'color'        => array(
			'type'    => 'string',
			'default' => '',
		),
		'firstColumn'  => array(
			'type'    => 'object',
			'default' => array(
				'type'    => 'text',
				'isGrey'  => false,
				'content' => 'Value 1',
				'image'   => '',
				'imageId' => '',
			),
		),
		'secondColumn' => array(
			'type'    => 'object',
			'default' => array(
				'type'    => 'text',
				'isGrey'  => false,
				'content' => 'Value 2',
				'image'   => '',
				'imageId' => '',
			),
		),
		'thirdColumn'  => array(
			'type'    => 'object',
			'default' => array(
				'type'    => 'text',
				'isGrey'  => false,
				'content' => 'Value 3',
				'image'   => '',
				'imageId' => '',
			),
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		$attrs = array(
			'heading'          => $settings['heading'],
			'subheading'       => $settings['subheading'],
			'type'             => $settings['type'],
			'bg'               => $settings['bg'],
			'color'            => $settings['color'],
			'firstcolumntype'  => $settings['firstColumn']['type'],
			'secondcolumntype' => $settings['secondColumn']['type'],
			'thirdcolumntype'  => $settings['thirdColumn']['type'],
			'firstcolumngrey'  => $settings['firstColumn']['isGrey'],
			'secondcolumngrey' => $settings['secondColumn']['isGrey'],
			'thirdcolumngrey'  => $settings['thirdColumn']['isGrey'],
			'firstcolumncont'  => $settings['firstColumn']['content'],
			'secondcolumncont' => $settings['secondColumn']['content'],
			'thirdcolumncont'  => $settings['thirdColumn']['content'],
			'firstcolumnimg'   => $settings['firstColumn']['imageId'],
			'secondcolumnimg'  => $settings['secondColumn']['imageId'],
			'thirdcolumnimg'   => $settings['thirdColumn']['imageId'],
		);

		if ( function_exists( 'wpsm_versus_shortcode' ) ) {
			echo wpsm_versus_shortcode( $attrs );
		}
	}
}