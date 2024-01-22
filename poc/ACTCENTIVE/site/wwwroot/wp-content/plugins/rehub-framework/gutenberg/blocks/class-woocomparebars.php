<?php


namespace Rehub\Gutenberg\Blocks;
defined('ABSPATH') OR exit;


class WooCompareBars extends Basic{

	protected $name = 'woocomparebars';

	protected $attributes = array(
		'type' => array(
			'type'    => 'string',
			'default' => 'ceoffer',
		),
		'ids' => array(
			'type'    => 'array',
			'default' => [],
		),	
        'attr' => array(
			'type'    => 'array',
			'default' => [],
		),	
        'min' => array(
			'type'    => 'array',
			'default' => [],
		),
        'color' => array(
			'type'    => 'string',
			'default' => '',
		),
        'markcolor' => array(
			'type'    => 'string',
			'default' => '',
		),

	);

	protected function render($settings = array(), $inner_content = ''){
		extract($settings);
		$id = 'rh-woocomparebars-'.mt_rand();
        $ids = wp_list_pluck( $ids, 'id' );
        $attr = wp_list_pluck( $attr, 'slug' );
        $min = wp_list_pluck( $min, 'value' );
        $value = wpsm_woo_versus_function(array('ids'=> $ids, 'attr'=> $attr, 'min'=> $min, 'color'=> $color, 'markcolor'=> $markcolor)); 
		$out = '<div id="'.$id.'">';
		$out .= $value;
		$out .= '</div>';

		return $out;
	}
}