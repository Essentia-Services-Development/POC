<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class ReviewBox extends Basic {
	protected $name = 'reviewbox';

	public function __construct() {
		$this->action();
		parent::__construct();
	}

	private function action(){
		add_action( 'wp_ajax_update_review_meta', array( $this, 'update_review_meta' ) );
	}

	public function update_review_meta(){
		$attr = $_POST['attr'];
		$id = $attr['postId'];
		$flag = true;

		$score = 0; $total_counter = 0;
		if(!empty($attr['criterias'])){
			foreach ($attr['criterias'] as $criteria) {
				$score += (float) $criteria['value']; $total_counter ++;
			}
		}
		if (!empty($attr['scoreManual']))  {
			$total_score = $attr['scoreManual'];
		}
		else {
			if( !empty( $score ) && !empty( $total_counter ) ) $total_score =  $score / $total_counter ;
			if( empty($total_score) ) $total_score = 0;
			$total_score = round($total_score,1);
		}

		if(!empty($total_score)){
			update_post_meta( $id, 'rehub_review_overall_score', $total_score );
			update_post_meta( $id, 'rehub_review_editor_score', $total_score );
		} else {
			delete_post_meta( $id, 'rehub_review_overall_score' );
			delete_post_meta( $id, 'rehub_review_editor_score' );
		}
		if(!empty($attr['title'])){
			update_post_meta( $id, '_review_heading', $attr['title'] );
		} else {
			delete_post_meta( $id, '_review_heading' );
		}
		if(!empty($attr['description'])){
			update_post_meta( $id, '_review_post_summary_text', $attr['description']);
		} else {
			delete_post_meta( $id, '_review_post_summary_text' );
		}
		if(!empty($attr['positives'])){
			$pros = array_map(array($this, 'get_string_from_array'), $attr['positives']);
			update_post_meta( $id, '_review_post_pros_text', implode("\n", $pros));
		} else {
			delete_post_meta( $id, '_review_post_pros_text' );
		}
		if(!empty($attr['negatives'])){
			$cons = array_map(array($this, 'get_string_from_array'), $attr['negatives']);
			update_post_meta( $id, '_review_post_cons_text', implode("\n", $cons));
		} else {
			delete_post_meta( $id, '_review_post_cons_text' );
		}
		if(!empty($attr['criterias'])){
			$criterias = array();
			foreach($attr['criterias'] as $key => $item){
				$criterias[$key]['review_post_name'] = $item['title'];
				$criterias[$key]['review_post_score'] = $item['value'];
				$keycriteria = $key +1;
				update_post_meta( $id, '_review_score_criteria_'.$keycriteria, $item['value']);
			}
			update_post_meta( $id, '_review_post_criteria', $criterias);
		} else {
			delete_post_meta( $id, '_review_post_criteria' );
			$criterias = 10;
			for($i=1; $i<$criterias; $i++){
				delete_post_meta( $id, '_review_score_criteria_'.$i );
			}
		}

		if(empty($total_score)){
			delete_post_meta( $id, 'rehub_review_overall_score' );
			delete_post_meta( $id, 'rehub_review_editor_score' );
			delete_post_meta( $id, '_review_heading' );
			delete_post_meta( $id, '_review_post_summary_text' );
			delete_post_meta( $id, '_review_post_pros_text' );
			delete_post_meta( $id, '_review_post_cons_text' );
			delete_post_meta( $id, '__review_post_criteria' );
		}

		wp_send_json_success( $flag );
	}

	protected function get_string_from_array($item){
		return $item['title'];
	}

	protected $attributes = array(
		'title'       => array(
			'type'    => 'string',
			'default' => 'Awesome'
		),
		'description' => array(
			'type'    => 'string',
			'default' => 'Place here Description for your reviewbox',
		),
		'score'       => array(
			'type'    => 'number',
			'default' => 0,
		),
		'scoreManual' => array(
			'type'    => 'number',
			'default' => 0,
		),
		'mainColor'   => array(
			'type'    => 'string',
			'default' => '#E43917',
		),
		'criterias'   => array(
			'type'    => 'object',
			'default' => array(),
		),
		'prosTitle'   => array(
			'type'    => 'string',
			'default' => 'Positive',
		),
		'positives'   => array(
			'type'    => 'object',
			'default' => array(),
		),
		'consTitle'   => array(
			'type'    => 'string',
			'default' => 'Negatives',
		),
		'negatives'   => array(
			'type'    => 'object',
			'default' => array(),
		),
		'uniqueClass' => array(
			'type'    => 'string',
			'default' => ''
		),
	);

	protected function inject_styles( $class_name, $color ) {
		$css = '.' . $class_name . ' .overall-score, .' . $class_name . ' .rate-bar-bar {';
		$css .= '   background:' . $color;
		$css .= '}';

		wp_register_style( 'reviewbox-inline-style', false, array( 'rhstyle' ) );
		wp_enqueue_style( 'reviewbox-inline-style' );
		wp_add_inline_style( 'reviewbox-inline-style', $css );
	}

	protected function render( $settings = array(), $inner_content = '' ) {
		$params                     = array();
		$criterias                  = '';
		$positives                  = '';
		$negatives                  = '';
		$params['title']            = $settings['title'];
		$params['description']      = $settings['description'];
		$params['prostitle']        = $settings['prosTitle'];
		$params['constitle']        = $settings['consTitle'];
		$params['additional_class'] = 'revbox'.mt_rand();
		
		if ( ! empty( $settings['criterias'] ) ) {
			foreach ( $settings['criterias'] as $item ) {
				$criterias .= $item['title'] . ':' . (float) $item['value'] . ';';
			}
			if(!empty($settings['scoreManual'])){
				$params['score'] = $settings['scoreManual'];
			}

			$params['criterias'] = $criterias;
			/*if($params['score'] == 10){
				$params['score'] = 0;
			}*/
		} else {
			if($settings['scoreManual'] !== 0 && $settings['scoreManual'] !== null){
				$params['score'] = $settings['scoreManual'];
			}else if(!empty($settings['score'])){
				$params['score'] = $settings['score'];
			}else{
				$params['score'] = 0;
			}
		}
		if ( ! empty( $settings['positives'] ) ) {
			foreach ( $settings['positives'] as $item ) {
				$positives .= $item['title'] . ';';
			}

			$params['pros'] = $positives;
		}

		if ( ! empty( $settings['negatives'] ) ) {
			foreach ( $settings['negatives'] as $item ) {
				$negatives .= $item['title'] . ';';
			}

			$params['cons'] = $negatives;
		}

		if ( ! is_admin() ) {
			$this->inject_styles( $params['additional_class'], $settings['mainColor'] );
		}
		echo wpsm_reviewbox( $params );
	}
}