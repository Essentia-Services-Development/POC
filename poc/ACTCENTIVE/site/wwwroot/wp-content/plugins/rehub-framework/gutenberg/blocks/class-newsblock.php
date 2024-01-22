<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class NewsBlock extends Basic {
	protected $name = 'news-block';

	public function __construct() {
		parent::__construct();
	}

	static $fonts = array();

	protected $attributes = array(
		'data_source' => array(
			'type' => 'string',
			'default' => 'cat',
		),
		'cat' => array(
			'type' => 'array',
			'default' => null
		),
        'cat_exclude' => array(
			'type' => 'array',
			'default' => null
		),
		'tag' => array(
			'type' => 'array',
			'default' => null
		),
        'tag_exclude' => array(
			'type' => 'array',
			'default' => null
		),
        'badge_label' => array(
			'type' => 'string',
			'default' => '1',
		),
        'post_type' => array(
			'type' => 'string',
			'default' => 'post',
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
		'ids' => array(
			'type' => 'array',
			'default' => null
		),
        'price_range' => array(
			'type' => 'string',
			'default' => '',
		),
        'show_coupons_only' => array(
			'type' => 'string',
			'default' => 'all',
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
			'default' => 1,
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

		if ( !empty( $settings['filterpanel'] ) ) {
            $settings['filterpanel'] = $this->filter_values( $settings['filterpanel'] );
            $settings['filterpanel'] = rawurlencode( json_encode( $settings['filterpanel'] ) );
        }

		$output = str_replace( "{{ content }}", wpsm_news_with_thumbs_mod_shortcode( $settings ), $inner_content );
		
		echo $output;
	}
}