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
class Widget_Wpsm_Colored_Grid_Loop extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'colored_grid_loop';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Colored Post grid', 'rehub-theme');
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    protected function control_fields() {
        $this->add_control( 'columns', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Set columns', 'rehub-theme' ),
            'options'     => [
                '3_col'             => esc_html__( '3 Columns', 'rehub-theme' ),
                '2_col'             => esc_html__( '2 Columns', 'rehub-theme' ),
                '4_col'             => esc_html__( '4 Columns', 'rehub-theme' ),
                '5_col'             => esc_html__( '5 Columns', 'rehub-theme' ),
                '6_col'             => esc_html__( '6 Columns', 'rehub-theme' ),
            ],
            'label_block' => true,
            'default' => '3_col',
        ]); 
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'headingtypography',
                'label' => esc_html__( 'Heading Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .coloredgrid h3',
            ]
        );  
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'texttypography',
                'label' => esc_html__( 'Text Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .coloredgrid .excerptforgrid',
            ]
        );
        $this->add_control( 'disabletext', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable excerpt?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'selectors' => [
                '{{WRAPPER}} .coloredgrid .excerptforcgrid' => 'display:none',                                
             ],            
        ]);
        $this->add_control( 'disablecategory', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable category?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'selectors' => [
                '{{WRAPPER}} .coloredgrid .catforcgrid' => 'display:none',                                
             ],            
        ]); 
        $this->add_control( 'disablehover', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable hover border?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'selectors' => [
                '{{WRAPPER}} .coloredgrid .rh-borderinside' => 'display:none',                                
             ],            
        ]);                                            
        $this->add_control( 'color1', [
            'label' => esc_html__( 'Color for each N + 1', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+1) .rehub-main-color' => 'color: {{VALUE}}',   
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+1).rh-main-bg-hover:hover' => 'background-color: {{VALUE}}',                              
             ],
        ]);
        $this->add_control( 'color2', [
            'label' => esc_html__( 'Color for each N + 2', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+2) .rehub-main-color' => 'color: {{VALUE}}',   
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+2).rh-main-bg-hover:hover' => 'background-color: {{VALUE}}',                              
             ],
        ]); 
        $this->add_control( 'color3', [
            'label' => esc_html__( 'Color for each N + 3', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+3) .rehub-main-color' => 'color: {{VALUE}}',   
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+3).rh-main-bg-hover:hover' => 'background-color: {{VALUE}}',                              
             ],
        ]); 
        $this->add_control( 'color4', [
            'label' => esc_html__( 'Color for each N + 4', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+4) .rehub-main-color' => 'color: {{VALUE}}',   
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+4).rh-main-bg-hover:hover' => 'background-color: {{VALUE}}',                              
             ],
        ]);
        $this->add_control( 'color5', [
            'label' => esc_html__( 'Color for each N + 5', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+5) .rehub-main-color' => 'color: {{VALUE}}',   
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+5).rh-main-bg-hover:hover' => 'background-color: {{VALUE}}',                              
             ],
        ]); 
        $this->add_control( 'color6', [
            'label' => esc_html__( 'Color for each N + 6', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+6) .rehub-main-color' => 'color: {{VALUE}}',   
                '{{WRAPPER}} .coloredgrid .col_item:nth-child(6n+6).rh-main-bg-hover:hover' => 'background-color: {{VALUE}}',                              
             ],
        ]);                                     
    }    

    protected function rehub_filter_values( $haystack ) {
        foreach ( $haystack as $key => $value ) {
            if ( is_array( $value ) ) {
                $haystack[ $key ] = $this->rehub_filter_values( $haystack[ $key ]);
            }

            if ( empty( $haystack[ $key ] ) ) {
                unset( $haystack[ $key ] );
            }
        }

        return $haystack;
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( is_array( $settings['filterpanel'] ) ) {
            $settings['filterpanel'] = $this->rehub_filter_values( $settings['filterpanel'] );
            $settings['filterpanel'] = rawurlencode( json_encode( $settings['filterpanel'] ) );
        }
        // print_r($settings);
        $this->normalize_arrays( $settings );
        echo wpsm_colorgrid_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Colored_Grid_Loop );
