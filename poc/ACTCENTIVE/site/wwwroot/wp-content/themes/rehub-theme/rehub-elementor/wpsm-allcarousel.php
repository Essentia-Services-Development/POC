<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit('Restricted Access');
} // Exit if accessed directly

/**
 * Info box Widget class.
 *
 * 'wpsm_box' shortcode
 *
 * @since 1.0.0
 */
class Widget_Wpsm_All_Carousel extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'all_carousel_mod';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Template Carousel', 'rehub-theme');
    }

    public function get_script_depends() {
        return [ 'owlcarousel', 'owlinit'];
    }
    public function get_style_depends() {
        return [ 'rhcarousel' ];
    }

    public function get_icon() {
        return 'eicon-slider-push';
    }
    public function get_categories() {
        return [ 'helpler-modules' ];
    }
    protected function register_controls() {
        $this->start_controls_section( 'content', [
            'label' => esc_html__( 'Content', 'rehub-theme' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'tab_title',
            [
                'label' => esc_html__( 'Title', 'rehub-theme' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Slide 1' , 'rehub-theme' ),
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => true,
            ]
        );
        
        $repeater->add_control(
            'content_template',
            [
                'label'       => esc_html__( 'Elementor Templates', 'rehub-theme' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '0',
                'options'     => rh_get_local_el_templates(),
                'label_block' => 'true',
            ]
        );

        
        $this->add_control(
            'carousel_content',
            [
                'label' => '',
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'tab_title' => esc_html__( 'Slide #1', 'rehub-theme' ),                     
                    ],
                ],
                'title_field' => '{{{ tab_title }}}',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section( 'general', [
            'label' => esc_html__( 'General settings', 'rehub-theme' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);
            $this->add_control(
                'carousel_unique_id',
                [
                    'label' => esc_html__( 'Connected widget ID', 'rehub-theme' ),
                    'type' => Controls_Manager::TEXT,
                    'default' => '',
                    'separator' => 'after',
                    'description' => esc_html__('Keep this blank or Setup connected id of tabs, accordion widget','rehub-theme'),
                ]
            );
            $this->add_control( 'enable_dots', [
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Enable dots?', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'return_value'      => '1',
            ]);
            $this->add_control( 'disable_arrows', [
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Disable arrows?', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'return_value'      => '1',
            ]);
            $this->add_control( 'disable_loop', [
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Disable loop?', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'return_value'      => '1',
            ]);
            $this->add_control( 'autorotate', [
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Make autorotate?', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'return_value'      => '1',
            ]);
        $this->end_controls_section();

        $this->start_controls_section( 'style', [
            'label' => esc_html__( 'Style settings', 'rehub-theme' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);
            $this->add_control( 'enable_overflow', [
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Enable visible overflow?', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'return_value'      => '1',
                'selectors' => [
                    '{{WRAPPER}} .re_carousel .owl-stage-outer' => 'overflow:visible;',
                ], 
            ]);
        $this->end_controls_section();

        $this->start_controls_section( 'arrowstyle', [
            'label' => esc_html__( 'Arrow styles', 'rehub-theme' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);
            $this->add_control( 'arrow_bg', [
                'label' => esc_html__( 'Arrow background Color', 'rehub-theme' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'disable_arrows!' => '1',
                ],  
                'selectors' => [
                    '{{WRAPPER}} .re_carousel .controls' => 'background-color: {{VALUE}};',
                ],         
            ]);
            $this->add_control( 'arrow_hover_bg', [
                'label' => esc_html__( 'Arrow hover background Color', 'rehub-theme' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'disable_arrows!' => '1',
                ],  
                'selectors' => [
                    '{{WRAPPER}} .re_carousel .controls:hover' => 'background-color: {{VALUE}};',
                ],          
            ]);
            $this->add_control( 'arrow_color', [
                'label' => esc_html__( 'Arrow icon Color', 'rehub-theme' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'disable_arrows!' => '1',
                ], 
                'selectors' => [
                    '{{WRAPPER}} .re_carousel .controls:after' => 'color: {{VALUE}};',
                ],          
            ]);
            $this->add_control( 'arrow_colorhover', [
                'label' => esc_html__( 'Arrow icon Color on Hover', 'rehub-theme' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'disable_arrows!' => '1',
                ], 
                'selectors' => [
                    '{{WRAPPER}} .re_carousel .controls:hover:after' => 'color: {{VALUE}};',
                ],          
            ]);
            $this->add_control(
                'arrow_size',
                array(
                    'label'   => esc_html__( 'Size of arrow background', 'rehub-theme' ),
                    'type'    => Controls_Manager::NUMBER,
                    'min'     => 30,
                    'max'     => 120,
                    'step'    => 1,
                    'condition' => [
                        'disable_arrows!' => '1',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .re_carousel .controls' => 'width: {{VALUE}}px;height: {{VALUE}}px;line-height: {{VALUE}}px;',
                        '{{WRAPPER}} .re_carousel .controls:after' => 'line-height: {{VALUE}}px;',
                    ], 
                )
            );
            $this->add_control(
                'arrow_iconsize',
                array(
                    'label'   => esc_html__( 'Size of arrow icon', 'rehub-theme' ),
                    'type'    => Controls_Manager::NUMBER,
                    'min'     => 15,
                    'max'     => 120,
                    'step'    => 1,
                    'condition' => [
                        'disable_arrows!' => '1',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .re_carousel .controls:after' => 'font-size: {{VALUE}}px;',
                    ], 
                )
            );

            $this->add_control(
                'carborderradius',
                [
                    'label' => __( 'Border radius', 'rehub-theme' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .re_carousel .controls' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            ); 
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'carousel_shadow',
                    'label' => __( 'Box Shadow', 'rehub-theme' ),
                    'selector' => '{{WRAPPER}} .re_carousel .controls',
                ]
            );
            $this->add_control(
                'arrow_left',
                array(
                    'label'   => esc_html__( 'Left position', 'rehub-theme' ),
                    'type'    => Controls_Manager::NUMBER,
                    'min'     => -200,
                    'max'     => 200,
                    'step'    => 1,
                    'condition' => [
                        'disable_arrows!' => '1',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .re_carousel .controls.prev' => 'left: {{VALUE}}px;',
                    ], 
                )
            );
            $this->add_control(
                'arrow_right',
                array(
                    'label'   => esc_html__( 'Left position', 'rehub-theme' ),
                    'type'    => Controls_Manager::NUMBER,
                    'min'     => -200,
                    'max'     => 200,
                    'step'    => 1,
                    'condition' => [
                        'disable_arrows!' => '1',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .re_carousel .controls.next' => 'right: {{VALUE}}px;',
                    ], 
                )
            ); 
        $this->end_controls_section();

        $this->start_controls_section( 'dotstyle', [
            'label' => esc_html__( 'Dot styles', 'rehub-theme' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);
            $this->add_control( 'dot_bg', [
                'label' => esc_html__( 'Dot background Color', 'rehub-theme' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'enable_dots' => '1',
                ],  
                'selectors' => [
                    '{{WRAPPER}} .re_carousel .owl-dots .owl-dot span' => 'background-color: {{VALUE}};',
                ],         
            ]);
            $this->add_control( 'dot_hover_bg', [
                'label' => esc_html__( 'Dot hover background Color', 'rehub-theme' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'enable_dots' => '1',
                ],  
                'selectors' => [
                    '{{WRAPPER}} .re_carousel .owl-dots .owl-dot.active span' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .re_carousel .owl-dots .owl-dot:hover span' => 'background-color: {{VALUE}};',
                ],          
            ]);
        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $tab_id=$idconnect='';
        $settings = $this->get_settings_for_display();
        $id_int = substr( $this->get_id_int(), 0, 3 );
        if ( ! empty( $settings['enable_dots'] )) {
            $this->add_render_attribute( 'rh_allcaro', 'data-dotenable', $settings['enable_dots'] );
        }
        if ( ! empty( $settings['disable_arrows'] )) {
            $this->add_render_attribute( 'rh_allcaro', 'data-navdisable', $settings['disable_arrows'] );
        }
        if ( ! empty( $settings['disable_loop'] )) {
            $this->add_render_attribute( 'rh_allcaro', 'data-loopdisable', $settings['disable_loop'] );
        }
        if ( ! empty( $settings['autorotate'] )) {
            $this->add_render_attribute( 'rh_allcaro', 'data-auto', $settings['autorotate'] );
        }
        if(!empty($settings["carousel_unique_id"])){
            $tab_id=esc_attr($settings["carousel_unique_id"]);
            $this->add_render_attribute( 'rh_allcaro', 'data-connected', $tab_id );
            $idconnect = 'rh-ca-connected ';
        }
        $this->add_render_attribute( 'rh_allcaro', 'data-lazy', '0' );
        wp_enqueue_style('rhcarousel'); wp_enqueue_script('owlcarousel'); wp_enqueue_script('owlinit'); 
        echo '<div class="rh-car-anything loading"><div class="'.$idconnect.'carousel-style-2 re_carousel flowvisible" data-fullrow="3" data-showrow="1" '.$this->get_render_attribute_string( 'rh_allcaro' ).'>';
            if(!empty($settings['carousel_content'])){
                foreach ( $settings['carousel_content'] as $index => $item ) :
                    $tab_count = $index + 1;
                    
                    $tab_content_setting_key = $this->get_repeater_setting_key( 'tab_content', 'carousel_content', $index );

                    $this->add_render_attribute( $tab_content_setting_key, [
                        'id' => 'slide-content-' . $id_int . $tab_count,
                        'class' => [ 'rh-slide-content' ],
                    ] );

                    ?>
                    <div <?php echo ''.$this->get_render_attribute_string( $tab_content_setting_key ); ?>>
                            <?php
                            if(!empty($item['content_template'])){
                                echo '<div class="rh-inner-full-carousel">'.\Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $item['content_template'] ).'</div>';
                            }
                            ?>                      
                    </div>
                <?php 
                endforeach;
            }

        echo '</div></div>';
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_All_Carousel );
