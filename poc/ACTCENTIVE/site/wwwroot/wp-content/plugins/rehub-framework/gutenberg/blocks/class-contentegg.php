<?php


namespace Rehub\Gutenberg\Blocks;
defined('ABSPATH') OR exit;


class ContentEgg extends Basic{

	protected $name = 'contentegg';

	protected $attributes = array(
		'type' => array(
			'type'    => 'string',
			'default' => 'ceoffer',
		),
		'ids' => array(
			'type'    => 'object',
			'default' => [],
		),		

	);

	protected function render($settings = array(), $inner_content = ''){
		extract($settings);
		$id = 'rh-contentegg-'.mt_rand();
		$post_id = (!empty($ids['id'])) ? $ids['id'] : get_the_ID();
        if($type == 'ceoffer'){
            $value = wpsm_get_bigoffer(array('post_id'=> $post_id));
        }else{
            if($type == 'cemerchant'){
                $template = 'custom/all_merchant_widget_group';
            }
            else if($type == 'cewidget'){
                $template = 'custom/all_logolist_widget';
            }   
            else if($type == 'cegrid'){
                $template = 'custom/all_offers_grid';
            }

            else if($type == 'celist'){
                $template = 'custom/all_offers_list';
            }               

            else if($type == 'celistlogo'){
                $template = 'custom/all_offers_logo_group';
            }

            else if($type == 'celistdef'){
                $template = 'offers_list';
            }               

            else if($type == 'celistdeflogo'){
                $template = 'offers_logo';
            }               

            else if($type == 'cestat'){
                $template = 'price_statistics';
            }   

            else if($type == 'cehistory'){
                $template = 'custom/all_pricehistory_full';
            }   

            else if($type == 'cealert'){
                $template = 'custom/all_pricealert_full';
            } 
            $atts = array();
            $atts['post_id'] = $post_id;
            $atts['template'] = $template;
            if(defined('\ContentEgg\PLUGIN_PATH')) {
                $value = \ContentEgg\application\BlockShortcode::getInstance()->viewData($atts);
            }
        }  
		$out = '<div id="'.$id.'">';
		$out .= $value;
		$out .= '</div>';

		return $out;
	}
}