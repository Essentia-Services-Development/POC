<?php


namespace Rehub\Gutenberg\Blocks;
defined('ABSPATH') OR exit;


class Metaget extends Basic{

	protected $name = 'metaget';

	protected $attributes = array(
		'align'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'valueColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'prefixColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'postfixColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'iconColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'iconBg'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'iconHeight'      => array(
			'type'    => 'number',
			'default' => 35
		),
		'valueSize'      => array(
			'type'    => 'number',
		),
		'prefixSize'      => array(
			'type'    => 'number',
		),
		'postId'      => array(
			'type'    => 'number',
		),
		'postType'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'postfixSize'      => array(
			'type'    => 'number',
			'default' => 13
		),
		'iconSize'      => array(
			'type'    => 'number',
		),
		'blockId'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'field' => array(
			'type'    => 'string',
			'default' => '',
		),
		'prefix' => array(
			'type'    => 'string',
			'default' => '',
		),
		'postfix' => array(
			'type'    => 'string',
			'default' => '',
		),
		'icon' => array(
			'type'    => 'string',
			'default' => '',
		),
		'type' => array(
			'type'    => 'string',
			'default' => 'custom',
		),
		'loading' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'showtoggle' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'show_empty' => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'labelblock' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'lineHeight'      => array(
			'type'    => 'number',
			'default' => 25,
		),
		'margin'      => array(
			'type'    => 'number',
			'default' => 8,
		),
		'marginpost'      => array(
			'type'    => 'number',
			'default' => 3,
		),

	);

	protected function render($settings = array(), $inner_content = ''){
		extract($settings);
		global $post;
		$postId = $post->ID;
		$id = 'rh-metaget-'.mt_rand();
		$alignflex = '';
		if($align === 'left'){
			$alignflex = 'start';
		}
		else if($align === 'right'){
			$alignflex = 'end';
		}
		else if($align === 'center'){
			$alignflex = 'center';
		}
		$out = $class=$textalign='';
		if(!$labelblock){
			$class = 'rh-flex-center-align rh-flex-justify-'.$alignflex;
		}else{
			$textalign = ' style="text-align:'.$align.'"';
		}
		$out .= '<div id="'.$id.'" class="'.$class.'"'.$textalign.'>
		<style scoped>
			#'.$id.' .meta_v_value{
				'.((isset($valueSize)) ? "font-size:".$valueSize."px;" : "").'
				'.(($valueColor) ? "color:".$valueColor.";" : "").'
				'.((isset($lineHeight)) ? "line-height:".$lineHeight."px;" : "").'
			}
			#'.$id.' i.rhicon{
				'.((isset($iconSize)) ? "font-size:".$iconSize."px;" : "").'
				'.(($iconColor) ? "color:".$iconColor.";" : "").'
				'.((isset($iconHeight)) ? "line-height:".$iconHeight."px;" : (isset( $lineHeight) ? $lineHeight.'px;' : '')).'
				'.((isset($margin)) ? "margin-right:".$margin."px;" : "").'
				'.((isset($iconHeight)) ? "height:".$iconHeight."px;width:".$iconHeight."px;border-radius:50%; text-align:center;" : "").'
				'.(($iconBg) ? "background-color:".$iconBg.";" : "").'
			}
			#'.$id.' .meta_v_label{
				'.((isset($prefixSize)) ? "font-size:".$prefixSize."px;" : "").'
				'.(($prefixColor) ? "color:".$prefixColor.";" : "").'
				'.((isset($lineHeight)) ? "line-height:".$lineHeight."px;" : "").'
				'.((isset($margin)) ? "margin-right:".$margin."px;" : "").'
			}
			#'.$id.' .meta_v_posttext{
				'.((isset($postfixSize)) ? "font-size:".$postfixSize."px;" : "").'
				'.(($postfixColor) ? "color:".$postfixColor.";" : "").'
				'.((isset($lineHeight)) ? "line-height:".$lineHeight."px;" : "").'
				'.((isset($marginpost)) ? "margin-left:".$marginpost."px;" : "").'
			}
		</style>
		'.wpsm_get_custom_value(array('field'=>$field, 'post_id'=>$postId, 'type'=>$type, 'show_empty'=>$show_empty, 'label'=>$prefix, 'posttext'=>$postfix, 'icon'=>$icon, 'labelblock'=>$labelblock, 'showtoggle'=>$showtoggle)).'
		</div>';

		return $out;
	}
}