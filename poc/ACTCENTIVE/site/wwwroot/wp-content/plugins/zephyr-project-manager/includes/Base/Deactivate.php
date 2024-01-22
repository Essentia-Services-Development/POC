<?php

/**
* @package ZephyrProjectManager
*/

namespace ZephyrProjectManager\Base;

if ( !defined( 'ABSPATH' ) ) {
	die;
}

class Deactivate {
	public static function deactivate(){
		flush_rewrite_rules();
	}
}