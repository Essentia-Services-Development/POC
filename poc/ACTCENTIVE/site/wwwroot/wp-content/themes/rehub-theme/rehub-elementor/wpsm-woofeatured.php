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
class Widget_Wpsm_Woo_Products_Featured extends WPSM_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_woofeaturedgrid';
    }
    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        wp_enqueue_style('elflexslider');
        wp_enqueue_script('elflexslider');
        wp_enqueue_script('elflexinit');
    }
    /* Widget Title */
    public function get_title() {
        return esc_html__('Woo Featured section', 'rehub-theme');
    }
    public function get_icon() {
        return 'eicon-gallery-group';
    } 

    public function get_script_depends() {
        return [ 'rhyall' ];
    } 
    protected function get_sections() {
        return [
            'general'   => esc_html__('Data query', 'rehub-theme'),
            'taxonomy'  => esc_html__('Additional Taxonomy Query', 'rehub-theme'),
            'control'   => esc_html__('Design Control', 'rehub-theme')
        ];
    }
    protected function control_fields() {
        $this->add_control( 'feat_type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type of area', 'rehub-theme' ),
            'description' => esc_html__( 'Featured area works only in full width row', 'rehub-theme' ),
            'default'     => '2',
            'options'     => [
                '1'             => esc_html__( 'Featured full width slider', 'rehub-theme' ),
                '2'             => esc_html__( 'Featured grid', 'rehub-theme' )
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'dis_excerpt', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable exerpt?', 'rehub-theme' ),
            'condition'   => [ 'feat_type' => [ '1' ] ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
        ]);

        $this->add_control( 'bottom_style', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Show text in left bottom side?', 'rehub-theme' ),
            'description' => esc_html__( 'Use only if your image is blured', 'rehub-theme' ),
            'condition'   => [ 'feat_type' => [ '1' ] ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
        ]);

        $this->add_control( 'show', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Number of posts to show in slider', 'rehub-theme' ),
            'default'     => '5',
            'condition'   => [ 'feat_type' => [ '1' ] ],
            'label_block' => true,
        ]);

        $this->add_control( 'custom_height', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Custom height (default is 490) in px', 'rehub-theme' ),
            'condition'   => [ 'feat_type' => [ '1' ] ],
            'label_block' => true,
        ]);
    }
    protected function style_control_fields() {
        $this->add_control( 'headingcolor', [
            'label' => esc_html__( 'Headings color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .col-feat-grid.item-1 h2 a, {{WRAPPER}} .flex-overlay h2 a' => 'color: {{VALUE}}',
            ],
        ]);
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'headingtypography',
                'label' => esc_html__( 'Heading Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .col-feat-grid.item-1 h2, {{WRAPPER}} .flex-overlay h2',
            ]
        );         
        $this->add_control( 'pricecolor', [
            'label' => esc_html__( 'Price color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .blacklabelprice' => 'color: {{VALUE}}',
            ],
        ]); 
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'pricetypography',
                'label' => esc_html__( 'Price Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .blacklabelprice',
            ]
        );         
        $this->add_control( 'saletagcolor', [
            'label' => esc_html__( 'Sale tag color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .blacklabelprice' => 'background-color: {{VALUE}}',
            ],
        ]); 
        $this->add_control( 'cartbtncolor', [
            'label' => esc_html__( 'Button color', 'rehub-theme' ),
            'description' => 'For global settings, you can add button color from Customizer - Theme options - Appearance',
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .main_slider.flexslider a.woo_loop_btn' => 'background-color: {{VALUE}} !important',
            ],
        ]);  
        $this->add_control( 'cartbtncolorhover', [
            'label' => esc_html__( 'Button color hover', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .main_slider.flexslider a.woo_loop_btn:hover' => 'background-color: {{VALUE}} !important',
            ],
        ]);                              
    }  

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        // Convert arrays to strings
        $this->normalize_arrays( $settings );
        // wp_enqueue_script('flexslider');
        $this->render_custom_js();
        echo wpsm_woofeatured_function( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Woo_Products_Featured );
