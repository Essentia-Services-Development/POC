<?php

namespace WPGO_Plugins\SVG_Flags;

/**
 *  Bootstrap class for the free shortcodes
 */
class Shortcodes {

	protected $module_roots;

	/* Main class constructor. */
	public function __construct( $module_roots, $custom_plugin_data ) {

		$this->module_roots       = $module_roots;
		$this->custom_plugin_data = $custom_plugin_data;
		$this->country_codes      = $this->custom_plugin_data->country_codes;
		$this->load_shortcodes();

		// Allow shortcodes to be used in widgets (the callbacks are WordPress core functions)
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode' );
	}

	/* Load shortcodes. */
	public function load_shortcodes() {

		// plugin root path
		$root = $this->module_roots['dir'];

		// [svg-flag] shortcode
		require_once $root . 'classes/shortcodes/svg-flag-shortcode.php';
		SVG_Flag_Shortcode::create_instance( $this->module_roots, $this->custom_plugin_data );

		// [svg-flag-image] shortcode
		require_once $root . 'classes/shortcodes/svg-flag-image-shortcode.php';
		SVG_Flag_Image_Shortcode::create_instance( $this->module_roots, $this->custom_plugin_data );

		// [svg-flag-grid] shortcode
		// require_once( $root . 'classes/shortcodes/svg-flag-grid-shortcode.php' );
		// SVG_Flag_Grid_Shortcode::create_instance($this->module_roots, $this->custom_plugin_data);
	}

} /* End class definition */
