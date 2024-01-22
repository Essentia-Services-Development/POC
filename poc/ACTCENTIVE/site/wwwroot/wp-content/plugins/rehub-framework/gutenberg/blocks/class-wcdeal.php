<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class WCDeal extends Basic {
	protected $name = 'wc-deal';

	public function __construct() {
		parent::__construct();
	}

	static $fonts = array();

	protected $attributes = array(
		'select_type' => array(
			'type' => 'string',
			'default' => 'custom',
		),
		'designtype' => array(
			'type' => 'string',
			'default' => 'deal',
		),
		'cat' => array(
			'type' => 'array',
			'default' => null
		),
		'tag' => array(
			'type' => 'array',
			'default' => null
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
		'user_id' => array(
			'type' => 'array',
			'default' => null
		),
		'type' => array(
			'type' => 'string',
			'default' => 'recent',
		),
		'ids' => array(
			'type' => 'array',
			'default' => null
		),
		'order' => array(
			'type' => 'string',
			'default' => 'desc',
		),
		'orderby' => array(
			'type' => 'string',
			'default' => 'date',
		),
		'meta_key' => array(
			'type' => 'string',
			'default' => '',
		),
		'show' => array(
			'type' => 'number',
			'default' => 12,
		),
		'offset' => array(
			'type' => 'string',
			'default' => '',
		),
		'enable_pagination' => array(
			'type' => 'string',
			'default' => '0',
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		$this->normalize_arrays( $settings );
		if ( !empty( $settings['attrpanel'] ) ) {
            $settings['attrelpanel'] = rawurlencode( json_encode( $settings['attrpanel'] ) );
        }

		if ( !empty( $settings['filterpanel'] ) ) {
            $settings['filterpanel'] = $this->filter_values( $settings['filterpanel'] );
            $settings['filterpanel'] = rawurlencode( json_encode( $settings['filterpanel'] ) );
        }

		if($settings['designtype'] == 'row' || $settings['designtype'] == 'compact'){
			$output = str_replace( "{{ content }}", wpsm_woorows_shortcode( $settings ), $inner_content );
		}else{
			$output = str_replace( "{{ content }}", wpsm_woolist_shortcode( $settings ), $inner_content );
		}
		
		echo $output;
	}
}