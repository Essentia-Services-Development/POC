<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class WooFeaturedSection extends Basic {
	protected $name = 'wc-featured-section';

	public function __construct() {
		parent::__construct();
	}

	static $fonts = array();

	protected $attributes = array(
		'select_type' => array(
			'type' => 'string',
			'default' => 'custom',
		),
		'cat' => array(
			'type' => 'array',
			'default' => null
		),
		'tag' => array(
			'type' => 'array',
			'default' => null
		),
		'tax_name' => array(
			'type' => 'string',
			'default' => '',
		),
		'tax_slug' => array(
			'type' => 'array',
			'default' => null
		),
		'tax_slug_exclude' => array(
			'type' => 'array',
			'default' => null
		),
		'user_id' => array(
			'type' => 'array',
			'default' => null
		),
		'type' => array(
			'type' => 'string',
			'default' => 'recent',
		),
		'ids' => array(
			'type' => 'array',
			'default' => null
		),
		'show' => array(
			'type' => 'number',
			'default' => 5,
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		$this->normalize_arrays( $settings );
		$output = str_replace( "{{ content }}", wpsm_woofeatured_function( $settings ), $inner_content );
		
		echo $output;
	}
}