<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class FeaturedSection extends Basic {
	protected $name = 'featured-section';

	public function __construct() {
		parent::__construct();
	}

	static $fonts = array();

	protected $attributes = array(
		'data_source' => array(
			'type' => 'string',
			'default' => 'cat',
		),
		'cat' => array(
			'type' => 'array',
			'default' => null
		),
        'cat_exclude' => array(
			'type' => 'array',
			'default' => null
		),
		'tag' => array(
			'type' => 'array',
			'default' => null
		),
        'tag_exclude' => array(
			'type' => 'array',
			'default' => null
		),
        'badge_label' => array(
			'type' => 'string',
			'default' => '1',
		),
        'post_type' => array(
			'type' => 'string',
			'default' => 'post',
		),
		'tax_name' => array(
			'type' => 'string',
			'default' => '',
		),
		'tax_slug' => array(
			'type' => 'array',
			'default' => null,
		),
		'tax_slug_exclude' => array(
			'type' => 'array',
			'default' => null,
		),
		'ids' => array(
			'type' => 'array',
			'default' => null
		),
        'price_range' => array(
			'type' => 'string',
			'default' => '',
		),
        'show_coupons_only' => array(
			'type' => 'string',
			'default' => 'all',
		),
		'show' => array(
			'type' => 'number',
			'default' => 5,
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		$this->normalize_arrays( $settings );
		$output = str_replace( "{{ content }}", wpsm_featured_function( $settings ), $inner_content );
		
		echo $output;
	}
}