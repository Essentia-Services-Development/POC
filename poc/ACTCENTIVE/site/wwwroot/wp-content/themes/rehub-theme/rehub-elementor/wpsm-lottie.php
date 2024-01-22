<?php 

namespace Elementor;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WPSM_Lottie extends Widget_Base {
	
	public function get_name() {
		return 'rh_lottie';
	}

    public function get_title() {
        return esc_html__('LottieFiles Canvas', 'rehub-theme');
    }

    public function get_icon() {
        return 'eicon-youtube';
    }

    public function get_categories() {
        return [ 'rhwow-modules' ];
    }
	
	public function get_keywords() {
		return [ 'canvas', 'animations'];
	}

    public function get_script_depends() {
        return ['lottie', 'lottie-init'];
    }
	
    protected function register_controls() {
		
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Lottie Content', 'rehub-theme' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'json_code_url',
			[
				'label' => esc_html__( 'JSON Input', 'rehub-theme' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'code',
				'description' => 'Download JSON file <a href="https://lottiefiles.com/14288-surfing-waveboard" target="_blank">(example link)</a> and import It’s code/url at space below.',
				'options' => [
					'code'  => esc_html__( 'Code', 'rehub-theme' ),
					'url' => esc_html__( 'URL', 'rehub-theme' ),					
				],
			]
		);
		$this->add_control(
			'content_parse_json_url',
			[
				'label' => esc_html__( 'JSON URL', 'rehub-theme' ),
				'type' => Controls_Manager::URL,				
				'placeholder' => esc_html__( 'https://www.demo-link.com', 'rehub-theme' ),
				'condition' => [
					'json_code_url' => 'url',
				],
			]
		);
		$this->add_control(
			'content_parse_json',
			[
				'label' => esc_html__( 'JSON Code', 'rehub-theme' ),
				'type' => Controls_Manager::TEXTAREA,
				'condition' => [
					'json_code_url' => 'code',
				],
			]
		);
		
		$this->end_controls_section();
		/*extra options start*/
		$this->start_controls_section(
			'section_bm_extra_option',
			[
				'label' => esc_html__( 'Main Settings', 'rehub-theme' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);		
		$this->add_control(
			'play_action_on',
			[
				'label' => __( 'Play on', 'rehub-theme' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'autoplay',
				'options' => [
					''         => __( 'Default', 'rehub-theme' ),
					'autoplay' => __( 'Auto Play', 'rehub-theme' ),
					'hover'    => __( 'On Hover', 'rehub-theme' ),
					'click'    => __( 'On Click', 'rehub-theme' ),
					'column'   => __( 'Column Hover', 'rehub-theme' ),
					'section'  => __( 'Section Hover', 'rehub-theme' ),					
					'mouseinout'  => __( 'Mouse In-Out Effect', 'rehub-theme' ),
					'mousescroll'  => __( 'Scroll Parallax', 'rehub-theme' ),
					'viewport'  => __( 'View Port Based', 'rehub-theme' ),
				],				
			]
		);
		$this->add_control(
			'loop',
			[
				'label' => esc_html__( 'Loop Animation', 'rehub-theme' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'ON', 'rehub-theme' ),
				'label_off' => esc_html__( 'OFF', 'rehub-theme' ),
				'return_value' => 'true',
				'default' => 'true',
				'separator' => 'before',
				'condition' => [
					'play_action_on!' => '',
				],
			]
		);
		$this->add_control(
			'speed',
			[
				'label' => esc_html__( 'Animation Play Speed', 'rehub-theme' ),
				'type'  => Controls_Manager::SLIDER,
                'type'    => Controls_Manager::NUMBER,
                'min'     => 0,
                'max'     => 100,
                'step'    => 0.1,
                'default' => 1,
				'condition' => [
					'play_action_on!' => ['','mousescroll','mouseinout','hover','click','column','section'],
				],
				'separator' => 'before',
			]
		);
		$this->add_control(
			'bm_scrollbased',
			[
				'label' => __( 'On Scroll Animation Height', 'rehub-theme' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'bm_custom',
				'options' => [
					'bm_custom' => __( 'Custom Height', 'rehub-theme' ),
					'bm_document'  => __( 'Document Height', 'rehub-theme' ),
				],
				'description' => __( 'Note : If you select "Document height", Animation will start and end based on whole page\'s height. In Custom height, You will be able to select offset and total height for animation.', 'rehub-theme' ),
				'separator' => 'before',
				'condition' => [
					'play_action_on' => 'mousescroll',
				],
			]
		);
		
		$this->add_control(
			'bm_section_duration',
			[
				'label' => __( 'Duration', 'rehub-theme' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 50,
						'max' => 2000,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 500,
				],
				'condition' => [
					'play_action_on' => 'mousescroll',
					'bm_scrollbased' => 'bm_custom',
				],
			]
		);
		$this->add_control(
			'bm_section_offset',
			[
				'label' => __( 'Offset', 'rehub-theme' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -1000,
						'max' => 1000,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'condition' => [
					'play_action_on' => 'mousescroll',
					'bm_scrollbased' => 'bm_custom',
				],
			]
		);
		$this->add_control(
			'bm_start_custom',
			[
				'label' => esc_html__( 'Custom Animation Start Time', 'rehub-theme' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'ON', 'rehub-theme' ),
				'label_off' => esc_html__( 'OFF', 'rehub-theme' ),
				'condition' => [
					'play_action_on' => ['autoplay','hover','click','column','section','mouseinout','mousescroll','viewport'],
				],
			]
		);
		$this->add_control(
			'bm_start_time',
			[
				'label' => esc_html__( 'Animation Start Time', 'rehub-theme' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 5000,
				'step' => 1,
				'condition' => [
					'play_action_on' => ['autoplay','hover','click','column','section','mouseinout','mousescroll','viewport'],
					'bm_start_custom' => 'yes',
				],
			]
		);
		$this->add_control(
			'bm_end_custom',
			[
				'label' => esc_html__( 'Custom Animation End Time', 'rehub-theme' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'ON', 'rehub-theme' ),
				'label_off' => esc_html__( 'OFF', 'rehub-theme' ),
				'condition' => [
					'play_action_on' => ['autoplay','hover','click','column','section','mouseinout','mousescroll','viewport'],
				],				
			]
		);
		$this->add_control(
			'bm_end_time',
			[
				'label' => esc_html__( 'Animation End Time', 'rehub-theme' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 5000,
				'step' => 1,
				'condition' => [
					'play_action_on' => ['autoplay','hover','click','column','section','mouseinout','mousescroll','viewport'],
					'bm_end_custom' => 'yes',
				],
			]
		);
		$this->add_control(
			'bm_start_end_note',
			[
				'label' => ( 'Note : You need to enter Custom Start Time and End Time from Lottiefiles Web Player. You need to use same format e.g. 30,239, 699 etc.'),
				'type' => Controls_Manager::HEADING,
				'conditions'   => [
					'terms' => [
						[
							'relation' => 'or',
							'terms'    => [																
								[
									'name'     => 'play_action_on','operator' => '==','value'    => 'mouseinout',
									'name'     => 'bm_start_custom','operator' => '==','value'    => 'yes',
								],
								[
									'name'     => 'play_action_on','operator' => '==','value'    => 'mousescroll',
									'name'     => 'bm_start_custom','operator' => '==','value'    => 'yes',
								],
								[
									'name'     => 'play_action_on','operator' => '==','value'    => 'mouseinout',
									'name'     => 'bm_end_custom','operator' => '==','value'    => 'yes',
								],
								[
									'name'     => 'play_action_on','operator' => '==','value'    => 'mousescroll',
									'name'     => 'bm_end_custom','operator' => '==','value'    => 'yes',
								],
							],
						],
					],
				],
			]
		);
		$this->add_control(
			'tp_bm_link',
			[
				'label' => esc_html__( 'URL', 'rehub-theme' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Enable', 'rehub-theme' ),
				'label_off' => esc_html__( 'Disable', 'rehub-theme' ),				
				'default' => 'false',
				'separator' => 'before',
			]
		);
		$this->add_control(
			'tp_bm_link_url',
			[
				'label' => esc_html__( 'URL', 'rehub-theme' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://www.demo-link.com', 'rehub-theme' ),
				'default' => '#',
				'condition' => [
					'tp_bm_link' => 'yes',					
				],
			]
		);
		$this->add_control(
			'tp_bm_link_delay',
			[
				'label' => esc_html__( 'Click Delay', 'rehub-theme' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 10000,
                        'step' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 1000,
				],
				'condition' => [
					'tp_bm_link' => 'yes',					
				],
				'separator' => 'before',
			]
		);
		$this->add_control(
			'tp_bm_link_delay_note',
			[
				'label' => ( 'Note : We have added option of Delay in Click for Style “On Click”, You can add delay to finish your animation and after that link will be open.'),
				'type' => Controls_Manager::HEADING,				
			]
		);
		$this->add_control(
			'anim_renderer',
			[
				'label' => esc_html__( 'Animation Renderer', 'rehub-theme' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'svg',
				'options' => [
					'svg'  => esc_html__( 'SVG', 'rehub-theme' ),
					'canvas' => esc_html__( 'Canvas', 'rehub-theme' ),
					'html' => esc_html__( 'HTML', 'rehub-theme' ),
				],
				'separator' => 'before',
			]
		);
		$this->end_controls_section();
		/*extra options end*/
		
		$this->start_controls_section(
			'section_layout_option',
			[
				'label' => esc_html__( 'Layout Options', 'rehub-theme' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'max_width',
			[
				'label' => esc_html__( 'Width', 'rehub-theme' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'separator' => 'before',
				'render_type' => 'ui',
				'selectors' => [
					'{{WRAPPER}} .rh-lottie-canvas' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'minimum_height',
			[
				'label' => esc_html__( 'Height', 'rehub-theme' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
                'default' => [
                    'size' => '100',
                    'unit' => '%',
                ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'render_type' => 'ui',
				'selectors' => [
					'{{WRAPPER}} .rh-lottie-canvas' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->end_controls_section();
			
	}
	
	 protected function render() {
        $settings = $this->get_settings_for_display();
		$style_atts = $classes = '';
		
		
		$bm_start_time=$bm_end_time='';
		if(!empty($settings['bm_start_custom']) && $settings['bm_start_custom']=='yes'){
			$bm_start_time = ($settings['bm_start_time']!='') ? $settings['bm_start_time'] : 1;
		}
		if(!empty($settings['bm_end_custom']) && $settings['bm_end_custom']=='yes'){
			$bm_end_time = ($settings['bm_end_time']!='') ? $settings['bm_end_time'] : 100;
		}
		$bm_scrollbased = (!empty($settings['bm_scrollbased'])) ? $settings['bm_scrollbased'] : 'bm_custom';
		$bm_section_duration=500;
		if(!empty($settings['bm_section_duration']['size'])){
			$bm_section_duration = $settings['bm_section_duration']['size'];
		}
		$bm_section_offset=0;
		if(!empty($settings['bm_section_offset']['size'])){
			$bm_section_offset = $settings['bm_section_offset']['size'];
		}
		
		$options=array();
		
		$anim_renderer=$settings["anim_renderer"];
		$loop =(!empty($settings['loop']) && $settings['loop']=='true') ? true : false;		
		$max_width =(!empty($settings['max_width']["size"])) ? $settings['max_width']["size"].$settings['max_width']["unit"] : '100%';		
		$minimum_height =(!empty($settings['minimum_height']["size"])) ? $settings['minimum_height']["size"].$settings['minimum_height']["unit"] : '';
		$speed =(!empty($settings['speed'])) ? $settings['speed'] : '0.5';
		
		$autoplay_viewport=$autostop_viewport=false;
		if(!empty($settings['play_action_on']) && $settings['play_action_on']=='viewport'){
			$autoplay_viewport =true;
			$autostop_viewport =true;
		}
		$play_action_on ='';
		if(!empty($settings['play_action_on'])){
			$play_action_on =$settings['play_action_on'];
		}
		
		
		$id=uniqid("movin", true);
		$uid=uniqid('', true);
		
		$options = array(			
			'id'      => $uid,
			'container_id'      => $id,
			'autoplay_viewport' => $autoplay_viewport,
			'autostop_viewport' => $autostop_viewport,
			'loop'              => $loop,
			'width'             => $max_width,
			'height'            => $minimum_height,
			'lazyload'          => false,
			'playSpeed'          => $speed,
			'play_action' => $play_action_on,
			'bm_scrollbased' => $bm_scrollbased,
			'bm_section_duration' => $bm_section_duration,
			'bm_section_offset' => $bm_section_offset,
			'bm_start_time' => $bm_start_time,
			'bm_end_time' => $bm_end_time,
		);
		if ( !empty($settings['content_parse_json']) ) {
			$options['animation_data'] = $settings['content_parse_json'];
		}
		
		if ( !empty($settings['content_parse_json_url']['url']) ) {
			$ext = pathinfo($settings['content_parse_json_url']['url'], PATHINFO_EXTENSION);			
			if($ext!='json'){
				echo '<h3 class="theplus-posts-not-found">'.esc_html__("Opps!! Please Enter Only JSON File Extension.",'rehub-theme').'</h3>';
				return false;
			}else{
				$get_json = rh_filesystem('get_content', $settings['content_parse_json_url']['url']);
				$options['animation_data'] = $get_json;
			}
		}
		
		if ( !isset( $options['autoplay_onload'] ) ) {
			$options['autoplay_onload'] = true;
		}
		if ( $settings["anim_renderer"] ) {
			$options['renderer'] = esc_attr($settings["anim_renderer"]);
		}
		
		if ( !empty( $anim_renderer ) ) {
			$classes .= ' renderer-' . $anim_renderer;
		}
		
		if ( !empty( $anim_renderer ) && $anim_renderer == 'html' ) {
			$style_atts .= 'position: relative;';
		}
		$settings_opt = '';
		if(!empty($settings['content_parse_json']) || !empty($settings['content_parse_json_url']['url'])){
			if(\Elementor\Plugin::$instance->editor->is_edit_mode()){
				$settings_opt =  'data-settings=\''.htmlspecialchars(json_encode($options), ENT_QUOTES, 'UTF-8').'\'';
				$settings_opt .= 'data-editor-load="yes"';
			}else{
				wp_enqueue_script( 'lottie' );wp_enqueue_script( 'lottie-init' );
			}
			$this->render_text( $options );
			$output ='';			
			if((!empty($settings['tp_bm_link']) && $settings['tp_bm_link'] == 'yes') && !empty($settings['tp_bm_link_url']) && !empty($settings["tp_bm_link_delay"])){
				$output .='<script>
					(function($){
						"use strict";
							$( document ).ready(function() {
								$("a.rh-lottie-link").click(function (e) {
								e.preventDefault();
								var storeurl = this.getAttribute("href");
								setTimeout(function(){
									 window.location = storeurl;
								}, '.$settings["tp_bm_link_delay"]["size"].');
							}); 
						});
					})(jQuery);
					</script>';
					
				
				$output .='<a class="rh-lottie-link" href="'.$settings['tp_bm_link_url'].'">';
				
			}
			
			$output .='<div id="' . esc_attr( $id ) . '" class="rh-lottie-canvas '.$classes.'" style="'.$style_atts.'" '.$settings_opt.'>';
			$output .='</div>';
				
			if(!empty($settings['tp_bm_link']) && $settings['tp_bm_link'] == 'yes'){
				$output .='</a>';
			}
			
		}else{
			$output ='<h3 class="posts-not-found">'.esc_html__( "JSON Parse Not Working", "rehub-theme" ).'</h3>';
		}
		echo ''.$output;
	}
	
	protected function render_text($options = array()) {
		$settings = $this->get_settings_for_display();
		
		if($options){	
			$Rehub_Lottie_Class = new \Rehub_Lottie_Class;
			\Rehub_Lottie_Class::plus_addAnimation($options);
		}else{
			return;
		}
	}
	
}

Plugin::instance()->widgets_manager->register( new WPSM_Lottie );