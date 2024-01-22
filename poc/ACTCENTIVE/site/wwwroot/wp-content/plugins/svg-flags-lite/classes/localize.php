<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *	Localize plugin
*/

class Localize {

	protected $module_roots;

	/* Main class constructor. */
	public function __construct($module_roots) {

		$this->module_roots = $module_roots;

		add_action( 'plugins_loaded', array( &$this, 'localize_plugin' ) );
	}

	/**
	 * Add Plugin localization support.
	 */
	public function localize_plugin() {

		load_plugin_textdomain( 'svg-flags', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

} /* End class definition */