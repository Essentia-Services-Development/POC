<?php
/**
 * Initialize plugin.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

/**
 * Main plugin initialization class
 */
class Main {

	/**
	 * Common root paths/directories.
	 *
	 * @var $module_roots
	 */
	public static $module_roots;

	/**
	 * Initialize plugin.
	 *
	 * @param array $mr Module roots.
	 */
	public static function init( $mr ) {

		self::$module_roots = $mr;

		$root = self::$module_roots['dir'];
		include_once $root . 'classes/class-bootstrap.php';

		new BootStrap();
	}
}
