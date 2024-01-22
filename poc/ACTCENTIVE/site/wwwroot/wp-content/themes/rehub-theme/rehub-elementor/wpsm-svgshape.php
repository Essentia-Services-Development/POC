<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Info box Widget class.
 *
 * 'SvgShape' shortcode
 *
 * @since 1.0.0
 */
class Widget_SvgShape extends Widget_Base {

	/* Widget Name */
	public function get_name() {
		return 'wpsm-svgshape';
	}

	/* Widget Title */
	public function get_title() {
		return esc_html__('SVG Shapes', 'rehub-theme');
	}

	/* Widget Icon */
	public function get_icon() {
		return 'eicon-shortcode';
	}

	/* Theme Category */
	public function get_categories() {
		return [ 'rhwow-modules' ];
	}

	/* Widget Keywords */
	public function get_keywords() {
		return [ 'shape' ];
	}

	/* Widget Controls */
	protected function register_controls() {

		$this->start_controls_section(
			'section_control_SvgShape',
			[
				'label' => esc_html__('Control', 'rehub-theme'),
			]
		);		
		$this->add_control(
			'shape',
			[
				'label' => esc_html__('Shape', 'rehub-theme'),
				'type' => Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'1' => esc_html__('Circle', 'rehub-theme'),
					'2' => esc_html__('Rectangle', 'rehub-theme'),
					'3' => esc_html__('Triangle', 'rehub-theme'), 
					'4' => esc_html__('Line', 'rehub-theme'),
                    'cali1' => esc_html__('Caligraphic divider 1', 'rehub-theme'),
                    'cali2' => esc_html__('Caligraphic divider 2', 'rehub-theme'),
                    'circledots' => esc_html__('Circle dots', 'rehub-theme'),
                    'blob1' => esc_html__('Blob 1', 'rehub-theme'),
                    'blob2' => esc_html__('Blob 2', 'rehub-theme'),
                    'blob3' => esc_html__('Blob 4', 'rehub-theme'),
                    'checkmark' => esc_html__('Check icon', 'rehub-theme'),
                    'calidoodle1' => esc_html__('Caligraphic doodle 1', 'rehub-theme'),
                    'calidoodle2' => esc_html__('Caligraphic doodle 2', 'rehub-theme'),
                    'calidoodle3' => esc_html__('Caligraphic doodle 3', 'rehub-theme'),
				]
			]
		);
        $this->add_control(
            'borderwidth',
            array(
                'label'   => esc_html__( 'Border width', 'rehub-theme' ),
                'type'    => Controls_Manager::NUMBER,
                'separator' => 'before',
                'min'     => 0,
                'max'     => 100,
                'step'    => 1,
            )
        );
        $this->add_control( 'bordercolor', [
            'label' => esc_html__( 'Color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
        ]);
        $this->add_control(
            'borderdash',
            array(
                'label'        => esc_html__( 'Enable dashed border?', 'rehub-theme' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                'return_value' => 'true',
            )
        );
        $this->add_control(
            'dashone',
            array(
                'label'   => esc_html__( 'Dash line', 'rehub-theme' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 1000,
                'step'    => 1,
                'default' => 5,
                'condition' => array(
                	'borderdash' => ['true'],
            	),
            )
        );
        $this->add_control(
            'dashtwo',
            array(
                'label'   => esc_html__( 'Dash offset', 'rehub-theme' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 1000,
                'step'    => 1,
                'default' => 5,
                'condition' => array(
                	'borderdash' => ['true'],
            	),
            )
        );	
		$this->add_control(
			'filltype',
			[
				'label' => esc_html__('Fill type', 'rehub-theme'),
				'type' => Controls_Manager::SELECT,
				'default' => '1',
                'separator' => 'before',
				'options' => [
					'1' => esc_html__('Color', 'rehub-theme'),
					'2' => esc_html__('Gradient', 'rehub-theme'),
					'3' => esc_html__('No fill', 'rehub-theme'),
				]
			]
		);
        $this->add_control( 'fill', [
            'label' => esc_html__( 'Color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#cc0000',
            'condition' => array(
                'filltype' => ['1'],
            ),
        ]);
        $this->add_control( 'gradcolorone', [
            'label' => esc_html__( 'Color One', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'condition' => array(
                'filltype' => ['2'],
            ),
        ]);
        $this->add_control(
            'offsetone',
            [
                'label' => __( 'Offset', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'label_block' => true,
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'filltype' => '2',
                ],
            ]
        );
        $this->add_control( 'gradcolortwo', [
            'label' => esc_html__( 'Color two', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'condition' => array(
                'filltype' => ['2'],
            ),
        ]);
        $this->add_control(
            'offsettwo',
            [
                'label' => __( 'Offset 2', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 100,
                ],
                'label_block' => true,
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'filltype' => '2',
                ],
            ]
        );
        $this->add_control(
            'gradrotate',
            [
                'label' => __( 'Rotation for gradient', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'label_block' => true,
                'separator' => 'before',
                'default' => [
                    'size' => 100,
                ],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 360,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'filltype' => '2',
                ],
            ]
        );
        $this->add_control(
            'svgshadow',
            array(
                'label'        => esc_html__( 'Enable shadow?', 'rehub-theme' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                'return_value' => 'true',
                'separator' => 'before',
            )
        );
        $this->add_control(
            'svgshadowcontrol',
            array(
                'label'        => esc_html__( 'Box shadow', 'rehub-theme' ),
                'type'         => \Elementor\Controls_Manager::BOX_SHADOW,
                'condition' => [
                    'svgshadow!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} svg' => 'filter: drop-shadow({{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{COLOR}});',
                ],
            )
        );
        $this->add_control(
            'svgwidth', [
                'label' => __('SVG width', 'rehub-theme'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px'],
                'separator' => 'before',
                'default' => [
                    'size' => 100,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 2500,
                    ]
                ],
                'condition' => [
                    'shape' => ['1','2','3','4'],
                ],
                
            ]
        );
        $this->add_control(
            'svgheight', [
                'label' => __('SVG height', 'rehub-theme'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px'],
                'separator' => 'before',
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 2500,
                    ]
                ],
                'default' => [
                    'size' => 100,
                    'unit' => 'px',
                ],
                'condition' => [
                    'shape' => ['2','3'],
                ],
            ]
        );
        $this->add_responsive_control(
            'areawidth', [
                'label' => __('Area width', 'rehub-theme'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px'],
                'separator' => 'before',
                'default' => [
                    'size' => 100,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 2500,
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} svg' => 'width: {{SIZE}}{{UNIT}};',
                ],
                
            ]
        );
        $this->add_responsive_control(
            'areaheight', [
                'label' => __('Area height', 'rehub-theme'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px'],
                'separator' => 'before',
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 2500,
                    ]
                ],
                'default' => [
                    'size' => 100,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} svg' => 'height: {{SIZE}}{{UNIT}};',
                ],
                
            ]
        );
	    $this->add_control(
	        'svg_rotate',
	        array(
	            'label'   => esc_html__( 'Rotation (deg)', 'rehub-theme' ),
	            'type'    => Controls_Manager::NUMBER,
	            'min'     => 1,
	            'max'     => 360,
	            'step'    => 1,
	            'selectors' => [
	                '{{WRAPPER}} svg' => 'transform: rotate({{VALUE}}deg);',
	            ],
	        )
	    );  

		$this->end_controls_section();

	}
	
	/* Widget output Rendering */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$widgetId = $this->get_id();
		if($settings['filltype'] == '1'){
			$fill = $settings['fill'];
		}
		elseif($settings['filltype'] == '2'){
			$fill = 'url(#grad'.$widgetId.')';
		}else{
			$fill = 'transparent';
		}
        if ( ! empty( $settings['borderwidth'] )) {
            $this->add_render_attribute( 'svgdata', 'stroke-width', $settings['borderwidth']  );
            $borderwidth = $settings['borderwidth'];
        }else{
            $borderwidth = 0;
        }
        if ( ! empty( $settings['bordercolor'] )) {
            $this->add_render_attribute( 'svgdata', 'stroke', $settings['bordercolor']  );
        }
        if($fill){
        	$this->add_render_attribute( 'svgdata', 'fill', $fill  );
        }
        if ( ! empty( $settings['borderdash'] )) {
            $this->add_render_attribute( 'svgdata', 'stroke-dasharray', $settings['dashone'].','.$settings['dashtwo']  );
        }
		$width = (!empty($settings['svgwidth']['size'])) ? $settings['svgwidth']['size'] : '';
		$height = (!empty($settings['svgheight']['size'])) ? $settings['svgheight']['size'] : '';
		if($settings['shape'] == '1'){
			$this->add_render_attribute( 'svgdatainner', 'cx', $width/2  );
			$this->add_render_attribute( 'svgdatainner', 'cy', $width/2  );
			$r = $width/2;
			if(!empty($settings['borderwidth'])){
				$r = $r - intval($settings['borderwidth']);
			}			
			$this->add_render_attribute( 'svgdatainner', 'r', $r  );
		}
		elseif($settings['shape'] == '2'){
			$x = $y = $borderwidth;
			$this->add_render_attribute( 'svgdatainner', 'x', $x  );
			$this->add_render_attribute( 'svgdatainner', 'y', $y  );
			$this->add_render_attribute( 'svgdatainner', 'width', $width  );
			$this->add_render_attribute( 'svgdatainner', 'height', $height  );			
		}
		elseif($settings['shape'] == '3'){
			$w2 = $width/2 - $borderwidth/2;
            $w = $width - $borderwidth;
            $h = $height - $borderwidth;
            $zero = $borderwidth;
			$points = $zero.','.$zero.' '.$w.','.$zero.' '.$w2.','.$h;
			$this->add_render_attribute( 'svgdatainner', 'points', $points );			
		}
		elseif($settings['shape'] == '4'){
			$this->add_render_attribute( 'svgdatainner', 'x2', $width );			
		}
        elseif($settings['shape'] == 'cali1'){
            $this->add_render_attribute( 'svgdata', 'viewBox', '0 0 397.88 41.1'  );
        } 
        elseif($settings['shape'] == 'checkmark'){
            $this->add_render_attribute( 'svgdata', 'viewBox', '0 0 79 79'  );
        } 
        elseif($settings['shape'] == 'cali2'){
            $this->add_render_attribute( 'svgdata', 'viewBox', '0 0 105.52 27.74'  );
        }
        elseif($settings['shape'] == 'calidoodle1'){
            $this->add_render_attribute( 'svgdata', 'viewBox', '0 0 212.42 298.08'  );
        } 
        elseif($settings['shape'] == 'calidoodle2'){
            $this->add_render_attribute( 'svgdata', 'viewBox', '0 0 331.64 190.04'  );
        }
        elseif($settings['shape'] == 'calidoodle3'){
            $this->add_render_attribute( 'svgdata', 'viewBox', '0 0 99.67 73.98'  );
        }
        elseif($settings['shape'] == 'circledots'){
            $this->add_render_attribute( 'svgdata', 'viewBox', '0 0 192.44 9.35'  );
        }
        elseif($settings['shape'] == 'blob1' || $settings['shape'] == 'blob2' || $settings['shape'] == 'blob3' || $settings['shape'] == 'blob4'){
            $this->add_render_attribute( 'svgdata', 'viewBox', '0 0 600 600'  );
        }    
		?> 


			<svg class="rh-svg-shape" <?php echo ''.$this->get_render_attribute_string( 'svgdata' );?>>
				<?php if($settings['filltype'] == '2'):?>
					<defs>
				    <linearGradient id="grad<?php echo ''.$widgetId;?>" gradientTransform="rotate(<?php echo''.$settings['gradrotate']['size'];?>)">
				        <stop offset="<?php echo''.$settings['offsetone']['size'];?>%" stop-color="<?php echo''.$settings['gradcolorone'];?>"/>
				        <stop offset="<?php echo''.$settings['offsettwo']['size'];?>%" stop-color="<?php echo''.$settings['gradcolortwo'];?>"/>
				    </linearGradient>
				    </defs>					
				<?php endif;?>
				<?php if($settings['shape'] == '1'):?>
					<circle <?php echo ''.$this->get_render_attribute_string( 'svgdatainner' );?> />
				<?php elseif($settings['shape'] == '2'):?>
					<rect <?php echo ''.$this->get_render_attribute_string( 'svgdatainner' );?> />
				<?php elseif($settings['shape'] == '3'):?>
					<polygon <?php echo ''.$this->get_render_attribute_string( 'svgdatainner' );?> />
				<?php elseif($settings['shape'] == '4'):?>
					<line x1="0" y1="0" y2="0" <?php echo ''.$this->get_render_attribute_string( 'svgdatainner' );?> />
                <?php elseif($settings['shape'] == 'checkmark'):?>
                    <path d="M24.000,39.000 L36.000,49.000 L56.000,30.000 "/>
                    <path d="M38.000,6.000 C55.673,6.000 70.000,20.327 70.000,38.000 C70.000,55.673 55.673,70.000 38.000,70.000 C20.327,70.000 6.000,55.673 6.000,38.000 C6.000,20.327 20.327,6.000 38.000,6.000 Z"/>
                <?php elseif($settings['shape'] == 'cali1'):?>
                    <g><g><path d="M133.63,27.13c.25-.2,2-1.48,2.26-1.68,3.54-2.81,6.86-6.8,6.45-11.6a8.37,8.37,0,0,0-7.41-8,1,1,0,0,0-.86.5c-3.57,6.19-2.8,15-.44,20.77"/><path d="M169.68,28.62a21.71,21.71,0,0,0-1.91-24.73,1,1,0,0,0-1.42,0c-6.89,7.8-3,18.28,3.33,24.73"/><path d="M199.9,22.29a26.41,26.41,0,0,0,.44-21.13,1.93,1.93,0,0,0-2.8,0c-2.89,7-.61,14.79,2.36,21.13"/><path d="M230.85,29.17c6.3-6.45,7.86-17.13,1-24.92a1,1,0,0,0-1.42,0c-6.1,7.49-3.86,17.6.45,24.92"/><path d="M265.29,25.93A21.38,21.38,0,0,0,264,7a1,1,0,0,0-.87-.49c-4.21.47-11.32,8.34,2.16,19.4"/><path d="M.15,40.63c32-10,65.19,1.37,97.8-1.56,12.2-1.1,25.27-4.14,35.5-11.28A20.35,20.35,0,0,0,154,38.85c6.34-.68,12.26-5.42,15.42-9.87,3,2.52,5.92,5.66,10.31,5.82,8.83.31,16.46-6,20.2-12.51,3.74,6.47,9.46,12.82,18.28,12.51,4.4-.16,9.66-3.1,12.67-5.63,3.17,4.46,6.72,9,13.06,9.68,9.29,1,17.69-5.83,21.38-12.92,10.23,7.14,22.44,12,34.64,13.14,32.61,2.93,65.78-8.43,97.8,1.56"/></g></g>
                <?php elseif($settings['shape'] == 'cali2'):?>
                    <g><g><path d="M14.67,18.6c-4.62,3.16-9.07-4.08-4.43-7.1,5.76-3.75,10.92,2.84,9.58,8.37A9.37,9.37,0,0,1,6.62,26C1.1,23.48-.94,15.53,1.55,10.31,4.66,3.77,11.34.8,18.31.52c8.88-.37,17,4.89,24.31,9.24A166.23,166.23,0,0,0,73.47,24.32c10.14,3.51,24.2,5.92,30.34-5.42,4-7.34-2.53-16.29-10.26-16.7A8.4,8.4,0,0,0,85,9.92c-.39,4.87,6,7.56,9.6,5.11"/></g></g>
                <?php elseif($settings['shape'] == 'circledots'):?>
                    <g><g><path d="M6,2.87A3.35,3.35,0,0,0,3,1.77C1,2.12.12,4.8.66,6.57c.84,2.79,4.92,2.13,6-.27A3.19,3.19,0,0,0,6,2.87Z"/><path d="M20.93,1.25a4.55,4.55,0,0,0-5.25,2.21c-1,2.18,1.33,4.22,3.4,4.22,2.31,0,4.3-3.27,3-5.48A2.19,2.19,0,0,0,20.93,1.25Z"/><path d="M36.45,3.33c-1.17-2.18-5-2.51-6.33-.05C26.67,9.72,39.89,9.7,36.45,3.33Z"/><path d="M44.73,2.59a3.53,3.53,0,0,0-.61,4.12c2.33,4.51,10.3,1.26,7.94-3.89C50.51-.55,46.9.09,44.73,2.59Z"/><path d="M67.85,2.53c-3.11-4-10.35-.11-6.59,4.39,2.59,3.1,9.63.2,6.8-4.1A1.94,1.94,0,0,0,67.85,2.53Z"/><path d="M79.78.65C78,.88,76.3,1.79,75.84,3.34,75.2,5.47,77.4,7.77,79.41,8a4.68,4.68,0,0,0,5.33-3.75C85.17,1.48,82.37.33,79.78.65Z"/><path d="M101.62,3c-.85-3.13-4.81-2.89-6.91-1.19-3.43,2.78.15,6.91,3.87,6.38C100.91,7.89,102.2,5.19,101.62,3Z"/><path d="M113.19,2.87a3.36,3.36,0,0,0-3.07-1.1c-2,.35-2.84,3-2.31,4.8.85,2.79,4.92,2.13,6-.27A3.19,3.19,0,0,0,113.19,2.87Z"/><path d="M128.09,1.25a4.57,4.57,0,0,0-5.26,2.21c-1,2.18,1.33,4.22,3.41,4.22,2.3,0,4.3-3.27,3-5.48A2.17,2.17,0,0,0,128.09,1.25Z"/><path d="M143.6,3.33c-1.17-2.18-5-2.51-6.32-.05C133.83,9.72,147,9.7,143.6,3.33Z"/><path d="M151.88,2.59a3.52,3.52,0,0,0-.6,4.12c2.32,4.51,10.3,1.26,7.93-3.89C157.67-.55,154.06.09,151.88,2.59Z"/><path d="M175,2.53c-3.12-4-10.35-.11-6.59,4.39,2.59,3.1,9.62.2,6.8-4.1Z"/><path d="M186.93.65c-1.78.23-3.47,1.14-3.94,2.69-.64,2.13,1.57,4.43,3.58,4.68a4.69,4.69,0,0,0,5.33-3.75C192.33,1.48,189.53.33,186.93.65Z"/></g></g>
                <?php elseif($settings['shape'] == 'blob1'):?>
                        <path d="M124.4,-189.8C163.1,-168.8,197.5,-137.4,220.1,-97.7C242.7,-58,253.5,-10,242.2,31.4C230.9,72.7,197.4,107.4,167.1,147.9C136.8,188.4,109.7,234.8,72,246.4C34.3,258.1,-14,235.1,-64.7,220.2C-115.3,205.3,-168.4,198.6,-206.7,170.3C-245,141.9,-268.5,91.9,-266.9,43.8C-265.3,-4.3,-238.6,-50.5,-214.6,-95.8C-190.5,-141.1,-169,-185.4,-133.7,-207.9C-98.4,-230.4,-49.2,-231.2,-3.2,-226.3C42.9,-221.4,85.8,-210.8,124.4,-189.8Z" transform="translate(300 300)" />
                <?php elseif($settings['shape'] == 'blob2'):?>
                        <path d="M94.7,-171.8C116.2,-133.5,122.7,-96.3,150.3,-61.2C177.8,-26.1,226.5,6.7,240,46.9C253.5,87.1,231.8,134.6,194.5,157.2C157.3,179.9,104.4,177.7,61.6,173.9C18.8,170.2,-14,164.7,-57.7,165.9C-101.5,167.1,-156.2,174.9,-195.5,155.1C-234.9,135.4,-259,88,-249.9,45.7C-240.9,3.3,-198.7,-34.1,-168.6,-67.5C-138.5,-100.9,-120.6,-130.4,-94.6,-166.6C-68.5,-202.9,-34.2,-245.9,1.2,-247.8C36.6,-249.6,73.2,-210.2,94.7,-171.8Z" transform="translate(300 300)" />
                <?php elseif($settings['shape'] == 'blob3'):?>
                        <path d="M134.5,-196.4C171.6,-185.4,197.2,-143.6,202.9,-101.4C208.6,-59.2,194.4,-16.7,192.1,30.5C189.9,77.7,199.6,129.6,180.7,163.1C161.8,196.7,114.3,212.1,66.4,226C18.6,239.9,-29.6,252.3,-64.3,234.9C-98.9,217.4,-120.1,169.9,-151.6,132.2C-183,94.4,-224.6,66.3,-237,29.5C-249.4,-7.3,-232.5,-52.9,-205.2,-85.9C-178,-118.9,-140.3,-139.4,-104.4,-151C-68.5,-162.6,-34.2,-165.3,7.2,-176.5C48.7,-187.7,97.3,-207.4,134.5,-196.4Z" transform="translate(300 300)" />
                <?php elseif($settings['shape'] == 'blob4'):?>
                        <path d="m169.7-35.8c25.8 60 5.3 154.3-41.7 181.2-47.1 26.9-120.8-13.5-162.7-54.2-41.9-40.8-52.1-81.7-41-121.5 11-39.7 43.4-78.2 95.5-84.6 52.2-6.5 124.1 19.1 149.9 79.1z" transform="translate(300 300)" /> 
                <?php elseif($settings['shape'] == 'calidoodle2'):?>
                        <path d="M.17,189.57c5.94-2.12,78.22-28.64,76.08-60.77-.58-8.73-6.77-19.52-12.25-19.4-8,.18-18.24,23.69-13.28,43.92,5.15,21,26.84,39.2,42.38,35.74,22.42-5,33-55.29,26.81-92.54-.73-4.34-1.13-10.57-2.29-10.61-1.49,0-4.35,9.91-3.07,18.38,2.32,15.34,19,31.1,32.68,30.13,29.18-2.07,51.68-80.6,38.81-89.36-2.18-1.49-5.8-1.25-7.15.51-5.9,7.69,25.49,52.3,42.9,48,20.45-5,22.67-77.91,15-79.95C234.9,13.12,232,16.69,231,20c-5.21,16.66,28,49.59,43.4,43.4,16.72-6.71,13.91-60,4.09-62.81-2.31-.66-5.57,1.28-5.62,3.07-.1,3.9,15.09,8.74,58.72,14.81" /> 
                <?php elseif($settings['shape'] == 'calidoodle1'):?>
                        <path d="M19.67.16c2.42,7-7.84,14.32-11.66,18.89C1.76,26.52-2.55,36.45,3.19,45.63s17.47,10.8,27.31,9.45c6-.82,24.37-8.36,27.79.14,1.15,2.88-.9,6.17-2.28,8.57-2.6,4.51-5.83,8.62-8.61,13-5.71,9-9,19.47-4.5,29.73,4.4,10,14.22,17.25,24.89,19.2,13.2,2.41,25.74-1.73,37.87-6.55,6.89-2.73,20.35-8.48,27.37-3.35,5.85,4.27,1.52,10.65-2.15,15-5.59,6.69-12.12,12.48-17.08,19.7s-7.58,16.37-4.47,24.94c3.6,9.92,13.66,16,23.7,17.67,12.36,2.08,23.71-2.3,35.19-6.33,9.31-3.27,20-6.56,29.15-.9,12.34,7.65-.8,20.33-6.58,27.68-7.82,10-12.22,21.22-11.92,34a52.93,52.93,0,0,0,12.32,32.78c4.21,5,9.37,8.28,14.7,11.92,2.16,1.47,4.74,3.16,6.1,5.45" /> 
                <?php elseif($settings['shape'] == 'calidoodle3'):?>
                        <path d="M99.63,70.75,69.57,73.4,84.81,47.67l-40.19,6V28.6S18.38,38.43,18.38,34s-3.83-23.6-3.83-23.6L.29.41" /> 
				<?php endif;?>
			</svg>
	   	<?php	
	}	

	protected function content_template() {
		?>
		<#
			var widgetId = view.getIDInt().toString().substr( 0, 3 );
			if(settings.filltype == '1'){
				 var fill = settings.fill;
			}
			else if(settings.filltype == '2'){
				var fill = 'url(#grad' + widgetId + ')';
			}else{
				var fill = 'transparent';
			}
	        if(fill){
	        	view.addRenderAttribute( 'svgdata', 'fill', fill  );
	        }
	        if ( settings.borderwidth ) {
	            view.addRenderAttribute( 'svgdata', 'stroke-width', settings.borderwidth  );
	        }
	        if ( settings.bordercolor ) {
	            view.addRenderAttribute( 'svgdata', 'stroke', settings.bordercolor  );
	        }
	        if ( settings.borderdash ) {
	            view.addRenderAttribute( 'svgdata', 'stroke-dasharray', settings.dashone+','+settings.dashtwo  );
	        }
			var width = settings.svgwidth.size;
			var height = settings.svgheight.size;
            var borderwidth = settings.borderwidth;
            if(borderwidth == ''){
                var borderwidth = 0;
            }

			if(settings.shape == '1'){
				view.addRenderAttribute( 'svgdatainner', 'cx', width/2  );
				view.addRenderAttribute( 'svgdatainner', 'cy', width/2  );
				var r = width/2;
				if(settings.borderwidth){
					r = r - settings.borderwidth;
				}			
				view.addRenderAttribute( 'svgdatainner', 'r', r  );
			}
			else if(settings.shape == '2'){
				var x = borderwidth;
				var y = borderwidth;
				view.addRenderAttribute( 'svgdatainner', 'x', x  );
				view.addRenderAttribute( 'svgdatainner', 'y', y  );
				view.addRenderAttribute( 'svgdatainner', 'width', width  );
				view.addRenderAttribute( 'svgdatainner', 'height', height  );			
			}
			else if(settings.shape == '3'){
				var borderwidth = settings.borderwidth;
				if(borderwidth == ''){
					var borderwidth = 0;
				}
				var w2 = width/2 - borderwidth/2;
				var w = width - borderwidth;
				var h = height - borderwidth;
				var zero = borderwidth;
				view.addRenderAttribute( 'svgdatainner', 'points', zero+','+zero+' '+w+','+zero+' '+w2+','+h );			
			}
			else if(settings.shape == '4'){
				view.addRenderAttribute( 'svgdatainner', 'x2', width );			
			}
            else if(settings.shape == 'cali1'){
                view.addRenderAttribute( 'svgdata', 'viewBox', '0 0 397.88 41.1'  );
            } 
            else if(settings.shape == 'cali2'){
                view.addRenderAttribute( 'svgdata', 'viewBox', '0 0 105.52 27.74'  );
            }
            else if(settings.shape == 'calidoodle1'){
                view.addRenderAttribute( 'svgdata', 'viewBox', '0 0 212.42 298.08'  );
            } 
            else if(settings.shape == 'calidoodle2'){
                view.addRenderAttribute( 'svgdata', 'viewBox', '0 0 331.64 190.04'  );
            }
            else if(settings.shape == 'calidoodle3'){
                view.addRenderAttribute( 'svgdata', 'viewBox', '0 0 99.67 73.98'  );
            }
            else if(settings.shape == 'circledots'){
                view.addRenderAttribute( 'svgdata', 'viewBox', '0 0 192.44 9.35'  );
            }
            else if(settings.shape == 'checkmark'){
                view.addRenderAttribute( 'svgdata', 'viewBox', '0 0 79 79'  );
            }
            else if(settings.shape == 'blob1' || settings.shape == 'blob2' || settings.shape == 'blob3' || settings.shape == 'blob4'){
                view.addRenderAttribute( 'svgdata', 'viewBox', '0 0 600 600'  );
            }  		
		#>
		<svg class="rh-svg-shape" {{{ view.getRenderAttributeString( 'svgdata' ) }}}>
			<# if(settings.filltype == '2'){ #>
				<defs>
			    <linearGradient id="grad{{{widgetId}}}" gradientTransform="rotate({{{settings.gradrotate.size}}})">
			        <stop offset="{{{settings.offsetone.size}}}%" stop-color="{{{settings.gradcolorone}}}"/>
			        <stop offset="{{{settings.offsettwo.size}}}%" stop-color="{{{settings.gradcolortwo}}}"/>
			    </linearGradient>
			    </defs>			
			<# } #>	
			<# if(settings.shape == '1'){ #>
				<circle {{{ view.getRenderAttributeString( 'svgdatainner' )}}} />
			<# } else if(settings.shape == '2'){ #>
				<rect {{{ view.getRenderAttributeString( 'svgdatainner' )}}} />
            <# } else if(settings.shape == 'checkmark'){ #>
                <path d="M24.000,39.000 L36.000,49.000 L56.000,30.000 "/>
                <path d="M38.000,6.000 C55.673,6.000 70.000,20.327 70.000,38.000 C70.000,55.673 55.673,70.000 38.000,70.000 C20.327,70.000 6.000,55.673 6.000,38.000 C6.000,20.327 20.327,6.000 38.000,6.000 Z"/>
			<# } else if(settings.shape == '3'){ #>
				<polygon {{{ view.getRenderAttributeString( 'svgdatainner' )}}} />
			<# } else if(settings.shape == '4'){ #>
				<line x1="0" y1="0" y2="0" {{{ view.getRenderAttributeString( 'svgdatainner' )}}} />
            <# } else if(settings.shape == 'cali1'){ #>
                <path d="M133.63,27.13c.25-.2,2-1.48,2.26-1.68,3.54-2.81,6.86-6.8,6.45-11.6a8.37,8.37,0,0,0-7.41-8,1,1,0,0,0-.86.5c-3.57,6.19-2.8,15-.44,20.77"/><path d="M169.68,28.62a21.71,21.71,0,0,0-1.91-24.73,1,1,0,0,0-1.42,0c-6.89,7.8-3,18.28,3.33,24.73"/><path d="M199.9,22.29a26.41,26.41,0,0,0,.44-21.13,1.93,1.93,0,0,0-2.8,0c-2.89,7-.61,14.79,2.36,21.13"/><path d="M230.85,29.17c6.3-6.45,7.86-17.13,1-24.92a1,1,0,0,0-1.42,0c-6.1,7.49-3.86,17.6.45,24.92"/><path d="M265.29,25.93A21.38,21.38,0,0,0,264,7a1,1,0,0,0-.87-.49c-4.21.47-11.32,8.34,2.16,19.4"/><path d="M.15,40.63c32-10,65.19,1.37,97.8-1.56,12.2-1.1,25.27-4.14,35.5-11.28A20.35,20.35,0,0,0,154,38.85c6.34-.68,12.26-5.42,15.42-9.87,3,2.52,5.92,5.66,10.31,5.82,8.83.31,16.46-6,20.2-12.51,3.74,6.47,9.46,12.82,18.28,12.51,4.4-.16,9.66-3.1,12.67-5.63,3.17,4.46,6.72,9,13.06,9.68,9.29,1,17.69-5.83,21.38-12.92,10.23,7.14,22.44,12,34.64,13.14,32.61,2.93,65.78-8.43,97.8,1.56"/>
            <# } else if(settings.shape == 'cali2'){ #>
                <path d="M14.67,18.6c-4.62,3.16-9.07-4.08-4.43-7.1,5.76-3.75,10.92,2.84,9.58,8.37A9.37,9.37,0,0,1,6.62,26C1.1,23.48-.94,15.53,1.55,10.31,4.66,3.77,11.34.8,18.31.52c8.88-.37,17,4.89,24.31,9.24A166.23,166.23,0,0,0,73.47,24.32c10.14,3.51,24.2,5.92,30.34-5.42,4-7.34-2.53-16.29-10.26-16.7A8.4,8.4,0,0,0,85,9.92c-.39,4.87,6,7.56,9.6,5.11"/>
            <# } else if(settings.shape == 'calidoodle1'){ #>
                <path d="M19.67.16c2.42,7-7.84,14.32-11.66,18.89C1.76,26.52-2.55,36.45,3.19,45.63s17.47,10.8,27.31,9.45c6-.82,24.37-8.36,27.79.14,1.15,2.88-.9,6.17-2.28,8.57-2.6,4.51-5.83,8.62-8.61,13-5.71,9-9,19.47-4.5,29.73,4.4,10,14.22,17.25,24.89,19.2,13.2,2.41,25.74-1.73,37.87-6.55,6.89-2.73,20.35-8.48,27.37-3.35,5.85,4.27,1.52,10.65-2.15,15-5.59,6.69-12.12,12.48-17.08,19.7s-7.58,16.37-4.47,24.94c3.6,9.92,13.66,16,23.7,17.67,12.36,2.08,23.71-2.3,35.19-6.33,9.31-3.27,20-6.56,29.15-.9,12.34,7.65-.8,20.33-6.58,27.68-7.82,10-12.22,21.22-11.92,34a52.93,52.93,0,0,0,12.32,32.78c4.21,5,9.37,8.28,14.7,11.92,2.16,1.47,4.74,3.16,6.1,5.45" />
            <# } else if(settings.shape == 'calidoodle3'){ #>
                <path d="M99.63,70.75,69.57,73.4,84.81,47.67l-40.19,6V28.6S18.38,38.43,18.38,34s-3.83-23.6-3.83-23.6L.29.41" />  
            <# } else if(settings.shape == 'calidoodle2'){ #>
                <path d="M.17,189.57c5.94-2.12,78.22-28.64,76.08-60.77-.58-8.73-6.77-19.52-12.25-19.4-8,.18-18.24,23.69-13.28,43.92,5.15,21,26.84,39.2,42.38,35.74,22.42-5,33-55.29,26.81-92.54-.73-4.34-1.13-10.57-2.29-10.61-1.49,0-4.35,9.91-3.07,18.38,2.32,15.34,19,31.1,32.68,30.13,29.18-2.07,51.68-80.6,38.81-89.36-2.18-1.49-5.8-1.25-7.15.51-5.9,7.69,25.49,52.3,42.9,48,20.45-5,22.67-77.91,15-79.95C234.9,13.12,232,16.69,231,20c-5.21,16.66,28,49.59,43.4,43.4,16.72-6.71,13.91-60,4.09-62.81-2.31-.66-5.57,1.28-5.62,3.07-.1,3.9,15.09,8.74,58.72,14.81" /> 
            <# } else if(settings.shape == 'circledots'){ #>
               <path d="M6,2.87A3.35,3.35,0,0,0,3,1.77C1,2.12.12,4.8.66,6.57c.84,2.79,4.92,2.13,6-.27A3.19,3.19,0,0,0,6,2.87Z"/><path d="M20.93,1.25a4.55,4.55,0,0,0-5.25,2.21c-1,2.18,1.33,4.22,3.4,4.22,2.31,0,4.3-3.27,3-5.48A2.19,2.19,0,0,0,20.93,1.25Z"/><path d="M36.45,3.33c-1.17-2.18-5-2.51-6.33-.05C26.67,9.72,39.89,9.7,36.45,3.33Z"/><path d="M44.73,2.59a3.53,3.53,0,0,0-.61,4.12c2.33,4.51,10.3,1.26,7.94-3.89C50.51-.55,46.9.09,44.73,2.59Z"/><path d="M67.85,2.53c-3.11-4-10.35-.11-6.59,4.39,2.59,3.1,9.63.2,6.8-4.1A1.94,1.94,0,0,0,67.85,2.53Z"/><path d="M79.78.65C78,.88,76.3,1.79,75.84,3.34,75.2,5.47,77.4,7.77,79.41,8a4.68,4.68,0,0,0,5.33-3.75C85.17,1.48,82.37.33,79.78.65Z"/><path d="M101.62,3c-.85-3.13-4.81-2.89-6.91-1.19-3.43,2.78.15,6.91,3.87,6.38C100.91,7.89,102.2,5.19,101.62,3Z"/><path d="M113.19,2.87a3.36,3.36,0,0,0-3.07-1.1c-2,.35-2.84,3-2.31,4.8.85,2.79,4.92,2.13,6-.27A3.19,3.19,0,0,0,113.19,2.87Z"/><path d="M128.09,1.25a4.57,4.57,0,0,0-5.26,2.21c-1,2.18,1.33,4.22,3.41,4.22,2.3,0,4.3-3.27,3-5.48A2.17,2.17,0,0,0,128.09,1.25Z"/><path d="M143.6,3.33c-1.17-2.18-5-2.51-6.32-.05C133.83,9.72,147,9.7,143.6,3.33Z"/><path d="M151.88,2.59a3.52,3.52,0,0,0-.6,4.12c2.32,4.51,10.3,1.26,7.93-3.89C157.67-.55,154.06.09,151.88,2.59Z"/><path d="M175,2.53c-3.12-4-10.35-.11-6.59,4.39,2.59,3.1,9.62.2,6.8-4.1Z"/><path d="M186.93.65c-1.78.23-3.47,1.14-3.94,2.69-.64,2.13,1.57,4.43,3.58,4.68a4.69,4.69,0,0,0,5.33-3.75C192.33,1.48,189.53.33,186.93.65Z"/>
            <# } else if(settings.shape == 'blob1'){ #>
                    <path d="M124.4,-189.8C163.1,-168.8,197.5,-137.4,220.1,-97.7C242.7,-58,253.5,-10,242.2,31.4C230.9,72.7,197.4,107.4,167.1,147.9C136.8,188.4,109.7,234.8,72,246.4C34.3,258.1,-14,235.1,-64.7,220.2C-115.3,205.3,-168.4,198.6,-206.7,170.3C-245,141.9,-268.5,91.9,-266.9,43.8C-265.3,-4.3,-238.6,-50.5,-214.6,-95.8C-190.5,-141.1,-169,-185.4,-133.7,-207.9C-98.4,-230.4,-49.2,-231.2,-3.2,-226.3C42.9,-221.4,85.8,-210.8,124.4,-189.8Z" transform="translate(300 300)" />
            <# } else if(settings.shape == 'blob2'){ #>
                    <path d="M94.7,-171.8C116.2,-133.5,122.7,-96.3,150.3,-61.2C177.8,-26.1,226.5,6.7,240,46.9C253.5,87.1,231.8,134.6,194.5,157.2C157.3,179.9,104.4,177.7,61.6,173.9C18.8,170.2,-14,164.7,-57.7,165.9C-101.5,167.1,-156.2,174.9,-195.5,155.1C-234.9,135.4,-259,88,-249.9,45.7C-240.9,3.3,-198.7,-34.1,-168.6,-67.5C-138.5,-100.9,-120.6,-130.4,-94.6,-166.6C-68.5,-202.9,-34.2,-245.9,1.2,-247.8C36.6,-249.6,73.2,-210.2,94.7,-171.8Z" transform="translate(300 300)" />
            <# } else if(settings.shape == 'blob3'){ #>
                    <path d="M134.5,-196.4C171.6,-185.4,197.2,-143.6,202.9,-101.4C208.6,-59.2,194.4,-16.7,192.1,30.5C189.9,77.7,199.6,129.6,180.7,163.1C161.8,196.7,114.3,212.1,66.4,226C18.6,239.9,-29.6,252.3,-64.3,234.9C-98.9,217.4,-120.1,169.9,-151.6,132.2C-183,94.4,-224.6,66.3,-237,29.5C-249.4,-7.3,-232.5,-52.9,-205.2,-85.9C-178,-118.9,-140.3,-139.4,-104.4,-151C-68.5,-162.6,-34.2,-165.3,7.2,-176.5C48.7,-187.7,97.3,-207.4,134.5,-196.4Z" transform="translate(300 300)" />
            <# } else if(settings.shape == 'blob4'){ #>
                    <path d="m169.7-35.8c25.8 60 5.3 154.3-41.7 181.2-47.1 26.9-120.8-13.5-162.7-54.2-41.9-40.8-52.1-81.7-41-121.5 11-39.7 43.4-78.2 95.5-84.6 52.2-6.5 124.1 19.1 149.9 79.1z" transform="translate(300 300)" />  
			<# } #>						
		</svg>		
		<?php
	}


}
Plugin::instance()->widgets_manager->register( new Widget_SvgShape );