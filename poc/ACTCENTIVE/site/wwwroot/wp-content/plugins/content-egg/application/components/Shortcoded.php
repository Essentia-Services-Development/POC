<?php

namespace ContentEgg\application\components;

defined( '\ABSPATH' ) || exit;

/**
 * Shortcoded class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class Shortcoded {

	private static $instances = array();
	private static $shortcoded_module_ids = array();
	private $post_id;

	public static function getInstance( $post_id ) {
		if ( ! isset( self::$instances[ $post_id ] ) ) {
			self::$instances[ $post_id ]             = new self( $post_id );
			self::$shortcoded_module_ids[ $post_id ] = array();
		}

		return self::$instances[ $post_id ];
	}

	private function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	public function getShortcodedModuleIds() {
		return self::$shortcoded_module_ids[ $this->post_id ];
	}

	public function setShortcodedModule( $module_id ) {
		self::$shortcoded_module_ids[ $this->post_id ][ $module_id ] = $module_id;
	}

	public function isShortcoded( $module_id ) {
		if ( isset( self::$shortcoded_module_ids[ $this->post_id ][ $module_id ] ) ) {
			return true;
		} else {
			return false;
		}
	}

}
