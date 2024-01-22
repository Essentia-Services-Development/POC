<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

class Popupbutton{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__ . '/popupbutton', array(
			'attributes'      => $this->attributes,
		));
	}

	protected $attributes = array(
		'max_width'      => array(
			'type'    => 'number',
			'default' => 500,
		),
		'pTop'      => array(
			'type'    => 'number',
			'default' => 5,
		),
		'mBottom'      => array(
			'type'    => 'number',
			'default' => 25,
		),
		'pSide'      => array(
			'type'    => 'number',
			'default' => 12,
		),
		'textSize'      => array(
			'type'    => 'number',
			'default' => 14,
		),
		'borderradius'      => array(
			'type'    => 'number',
			'default' => 0,
		),
		'btn_text'     => array(
			'type'    => 'string',
			'default' => 'Show popup',
		),
		'textColor'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'bgColor'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'bgGradient'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'blockId'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'textalign'     => array(
			'type'    => 'string',
			'default' => 'left',
		),
	);

}