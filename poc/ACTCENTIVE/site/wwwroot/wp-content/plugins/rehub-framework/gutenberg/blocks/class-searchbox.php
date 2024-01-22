<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class Searchbox extends Basic {
	protected $name = 'searchbox';

	public function __construct() {
		parent::__construct();
	}

	protected $attributes = array(
		'search_type' => array(
			'type' => 'string',
			'default' => 'post',
		),
		'by' => array(
			'type' => 'array',
			'default' => 'post'
		),
		'tax' => array(
			'type' => 'array',
			'default' => null
		),
		'catid' => array(
			'type' => 'string',
			'default' => '',
		),
		'enable_ajax' => array(
			'type' => 'string',
			'default' => ''
		),
		'enable_compare' => array(
			'type' => 'string',
			'default' => '',
		),
		'placeholder' => array(
			'type' => 'string',
			'default' => 'Search',
		),
		'label' => array(
			'type' => 'string',
			'default' => '',
		),
		'color' => array(
			'type' => 'string',
			'default' => '#7635f3',
		),
		'aff_link' => array(
			'type' => 'string',
			'default' => '',
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		$output = str_replace( "{{ content }}", wpsm_searchbox_function( $settings ), $inner_content );
		
		echo $output;
	}
}