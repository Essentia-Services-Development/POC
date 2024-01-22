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
class Widget_Wpsm_Compactgrid_loop_Mod extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'compactgrid_loop_mod';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Deal/Coupon grid', 'rehub-theme');
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

    public function get_categories() {
        return [ 'deal-helper' ];
    }

    protected function control_fields() {
        $this->add_control( 'gridtype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type', 'rehub-theme' ),
            'default'     => 'full',
            'options'     => [
                'full'           => esc_html__( 'Full Deal Grid', 'rehub-theme' ),
                'compact'           => esc_html__( 'Compact Deal Grid (Coupon)', 'rehub-theme' ),
                'mobile'           => esc_html__( 'Mobile Optimized Grid', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);
        $this->add_control( 'aff_link', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make link as affiliate?', 'rehub-theme' ),
            'description' => esc_html__( 'This will change all inner post links to affiliate link of post offer', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
        $this->add_control( 'disable_btn', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable button?', 'rehub-theme' ),
            'description' => esc_html__( 'This will disable button in grid', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
        $this->add_control( 'disable_act', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable actions?', 'rehub-theme' ),
            'description' => esc_html__( 'This will disable thumbs and comment count in bottom', 'rehub-theme' ),
            'condition'   => [ 'gridtype' => [ 'full', 'mobile' ] ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
        $this->add_control( 'price_meta', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Show Price meta as', 'rehub-theme' ),
            'condition'   => [ 'gridtype' => [ 'full' ] ],
            'default' => '1',
            'options'     => [
                '1'             => esc_html__( 'User logo + Price', 'rehub-theme' ),
                '2'             => esc_html__( 'Brand logo + Price', 'rehub-theme' ),
                '3'             => esc_html__( 'Only Price', 'rehub-theme' ),
                '4'             => esc_html__( 'Nothing', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'columns', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Set columns', 'rehub-theme' ),
            'default'     => '4_col',
            'options'     => [
                '3_col'             => esc_html__( '3 Columns', 'rehub-theme' ),
                '2_col'             => esc_html__( '2 Columns', 'rehub-theme' ),
                '4_col'             => esc_html__( '4 Columns', 'rehub-theme' ),
                '5_col'             => esc_html__( '5 Columns', 'rehub-theme' ),
                '6_col'             => esc_html__( '6 Columns', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control(
            'smartscrolllist',
            array(
                'label'        => esc_html__( 'Enable smart inline scroll', 'rehub-theme' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                'return_value' => '1',
            )
        );
        $this->add_responsive_control(
            'sscrollwidth',
            [
                'label' => esc_html__( 'Width of item', 'rehub-theme' ),
                'type' => Controls_Manager::NUMBER,
                'default' => '',
                'condition' => array(
                    'smartscrolllist' => '1',
                ),
                'selectors' => [
                    '{{WRAPPER}} .col_item' => 'min-width: {{VALUE}}px !important; width: {{VALUE}}px !important',
                ],
            ]
        );

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
        $this->render_custom_js();
        echo wpsm_compactgrid_loop_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Compactgrid_loop_Mod );
