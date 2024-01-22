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
class Widget_Wpsm_Woo_Products_Carousel extends WPSM_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'woo_mod';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Woo commerce product carousel', 'rehub-theme');
    }

    public function get_script_depends() {
        return [ 'owlcarousel', 'owlinit' ];
    }

    public function get_style_depends() {
        return [ 'rhcarousel' ];
    }

    protected function register_controls() {
        parent::register_controls();
        Controls_Stack::remove_control( 'enable_pagination' );
    }
    protected function get_sections() {
        return [
            'general'   => esc_html__('Data query', 'rehub-theme'),
            'data'      => esc_html__('Data Settings', 'rehub-theme'),
            'taxonomy'  => esc_html__('Additional Taxonomy Query', 'rehub-theme'),
            'control'   => esc_html__('Design Control', 'rehub-theme')
        ];
    }
    public function get_icon() {
        return 'eicon-posts-carousel';
    }      
    protected function control_fields() {
        $this->add_control( 'aff_link', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make link as affiliate?', 'rehub-theme' ),
            'description' => esc_html__( 'This will change all inner post links to affiliate link of post offer', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'default'     => 'no',
        ]);

        $this->add_control( 'autorotate', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make autorotate?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'default'     => 'yes',
        ]);

        $this->add_control( 'carouseltype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type', 'rehub-theme' ),
            'default'     => 'columned',
            'options'     => [
                'columned'             => esc_html__( 'Columned grid', 'rehub-theme' ),
                'simple'             => esc_html__( 'Simple grid', 'rehub-theme' ),
                'compact'             => esc_html__( 'Compact grid', 'rehub-theme' ),
                'review'             => esc_html__( 'Review grid', 'rehub-theme' ),
                'dealwhite'            => esc_html__( 'Deal Grid', 'rehub-theme' ),
                'dealdark'            => esc_html__( 'Deal Grid Dark', 'rehub-theme' ),
                'digital'             => esc_html__( 'Digital grid', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'soldout', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Add fake sold counter', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'default'     => '',
        ]);

        $this->add_control( 'showrow', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Number of items in row', 'rehub-theme' ),
            'options'     => [
                '5'             => esc_html__( '5', 'rehub-theme' ),
                '4'             => esc_html__( '4', 'rehub-theme' ),
                '3'             => esc_html__( '3', 'rehub-theme' ),
                '6'             => esc_html__( '6', 'rehub-theme' ),
            ],
            'label_block' => true,
            'default' => '5'
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
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        // Convert arrays to strings
        $this->normalize_arrays( $settings );
        echo woo_mod_shortcode( $settings );
    }

}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Woo_Products_Carousel );
