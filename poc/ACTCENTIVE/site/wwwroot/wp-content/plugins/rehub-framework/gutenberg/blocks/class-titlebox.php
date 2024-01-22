<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

use WP_REST_Request;
use WP_REST_Server;

class TitleBox extends Basic {
	protected $name = 'titlebox';

	protected $attributes = array(
		'style' => array(
			'type'    => 'string',
			'default' => '1',
		),
		'title' => array(
			'type'    => 'string',
			'default' => '',
		),
		'text'  => array(
			'type'    => 'string',
			'default' => '',
		),
	);

	protected function render($settings = array(), $inner_content = ''){
		// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		$content = do_shortcode($settings['text']);
		$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
		if($settings['style'] == 'main') {
			$themeclass = ' rehub-main-color-border';
			$colorclass = 'rehub-main-color';
		} else if($settings['style'] == 'secondary') {
			$themeclass = ' rehub-sec-color-border';
			$colorclass = 'rehub-sec-color';
		} else {
			$themeclass = $colorclass = '';
		}
		if(empty($settings['align'])){
			$settings['align'] = '';
		}

		// return the url
		return wpsm_titlebox_shortcode(array('style'=>$settings['style'], 'title'=>$settings['title'], 'align' => $settings['align']), $content);
	}
}
