<?php


namespace Rehub\Gutenberg\Blocks;
defined('ABSPATH') OR exit;


class Toc extends Basic{

	protected $name = 'toc';

	protected $attributes = array(
		'textColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'numberColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'fontSize'      => array(
			'type'    => 'number',
			'default' => 14,
		),
		'lineHeight'      => array(
			'type'    => 'number',
			'default' => 20,
		),
		'margin'      => array(
			'type'    => 'number',
			'default' => 10,
		),
		'blockId'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'items' => array(
			'type'    => 'array',
			'default' => [],
		),
	);

	protected function render($settings = array(), $inner_content = ''){
		extract($settings);
		$links = '';
		if(!empty($items)){
			$links .= '<ul class="autocontents">';
			foreach($items as $item){
				$linkcontent = '';
				if(!empty($item['content'])){
					$linkcontent = $item['content'];
				}else if(!empty($item['subtitle'])){
					$linkcontent = $item['subtitle'];
				}
				if($linkcontent){
					$links .= '<li class="top"><a class="rehub_scroll" href="#'.esc_html($item['anchor']).'">'.wp_kses_post($linkcontent).'</a></li>';
				}
			}
			$links .= '</ul>';
		}else{
			$links .= wpsm_contents_shortcode(array("headers"=>"h2"));
		}
		
		$id = 'rh-gut-'.mt_rand();
		$out = '';
		$out .= '<div id="'.$id.'">
		<style scoped>
		#'.$id.' .autocontents li{margin: 0 0 '.$margin.'px 0; font-size: '.$fontSize.'px; line-height:'.$lineHeight.'px}
		#'.$id.' .autocontents li a{
			'.(($textColor) ? "color:".$textColor.";" : "").'
		}
		#'.$id.' .autocontents li:before{
			'.(($numberColor) ? "color:".$numberColor.";" : "").'
		}
		</style>
		'.$links.'
		</div>';

		return $out;
	}
}