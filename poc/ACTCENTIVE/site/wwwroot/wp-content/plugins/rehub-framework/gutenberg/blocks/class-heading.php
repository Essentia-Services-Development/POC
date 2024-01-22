<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

class Heading extends Basic {
	protected $name = 'heading';

	protected $attributes = array(
		'level'          => array(
			'type'    => 'number',
			'default' => 2,
		),
		'content'        => array(
			'type'    => 'string',
			'default' => 'Heading',
		),
		'backgroundText' => array(
			'type'    => 'string',
			'default' => '01.',
		),
		'textAlign'      => array(
			'type'    => 'string',
			'default' => 'left',
		),
	);

	protected function render($settings = array(), $inner_content = ''){

		$level = $settings['level'];
		if(!is_numeric($level) || $level < 1 || $level > 6) {
			$level = 2;
		}

		$level = 'h'.$level;

		$wrapperClassAlign = [
			'center' => 'rh-flex-justify-center',
			'left'   => 'rh-flex-justify-start',
			'right'  => 'rh-flex-justify-end',
		];

		$numberClassAlign = [
			'center' => 'text-center',
			'left'   => 'text-left-align',
			'right'  => 'text-right-align',
		];

		$this->add_render_attribute('wrapper', 'class', array(
			'wpsm_heading_number',
			'position-relative',
			'rh-flex-center-align',
			'mb30',
			$wrapperClassAlign[$settings['textAlign']],
		));

		$this->add_render_attribute('number', 'class', array(
			'number',
			'abdfullwidth',
			'width-100p',
			$numberClassAlign[$settings['textAlign']],
		));

		$out = '<div '.$this->get_render_attribute_string('wrapper').'>
			<style scoped>.wpsm_heading_number{min-height: 6em;}.wpsm_heading_number .number{color:#f0f0f0;font-size:6em;font-weight:600;line-height:1; z-index: 1}.wpsm_heading_number .wpsm_heading_context{ z-index: 2;}.rtl .wpsm_heading_number .number{left:auto;right:0;text-align:right}</style>
            <div '.$this->get_render_attribute_string('number').'>'.$settings['backgroundText'].'</div>
            <div class="wpsm_heading_context position-relative">
            <'.$level.' class="mt0 mb0 ml15 mr15">
			'.$settings['content'].'
			</'.$level.'>
            </div>
			</div>';

		return $out;
	}
}
