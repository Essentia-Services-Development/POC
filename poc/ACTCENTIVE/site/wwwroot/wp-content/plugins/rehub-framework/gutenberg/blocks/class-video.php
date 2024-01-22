<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

class Video{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__ . '/video');
	}

}