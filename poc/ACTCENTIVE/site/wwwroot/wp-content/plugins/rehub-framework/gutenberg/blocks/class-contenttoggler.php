<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

class ContentToggler{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__ . '/contenttoggler', array(
			'attributes'      => $this->attributes,
		));
	}

	protected $attributes = array(
		'background'      => array(
			'type'    => 'string',
			'default' => '#ffffff',
		),
		'openlabel'     => array(
			'type'    => 'string',
			'default' => 'Show more +',
		),
		'closelabel' => array(
			'type'    => 'string',
			'default' => 'Show less -',
		),
		'textColor'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'height'     => array(
			'type'    => 'string',
			'default' => '100px',
		),
		'textalign'     => array(
			'type'    => 'string',
			'default' => 'center',
		),
	);

}