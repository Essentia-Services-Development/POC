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
class Widget_Wpsm_Woo_Products_Columns extends WPSM_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_woocolumns';
    }

    public function get_icon() {
        return 'eicon-posts-grid';
    }   

    /* Widget Title */
    public function get_title() {
        return esc_html__('Columns of woocommerce products', 'rehub-theme');
    }
    protected function control_fields() {
        $this->add_control( 'columns', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Set columns', 'rehub-theme' ),
            'options'     => [
                '3_col'             => esc_html__( '3 Columns', 'rehub-theme' ),
                '4_col'             => esc_html__( '4 Columns', 'rehub-theme' ),
                '5_col'             => esc_html__( '5 Columns', 'rehub-theme' ),
                '6_col'             => esc_html__( '6 Columns', 'rehub-theme' ),
            ],
            'default' => '4_col',
            'label_block' => true,
        ]);

        $this->add_control( 'woolinktype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Show link from title and image on', 'rehub-theme' ),
            'options'     => [
                'product'           => esc_html__( 'Product page', 'rehub-theme' ),
                'aff'           => esc_html__( 'Affiliate link', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'custom_col', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Custom image size?', 'rehub-theme' ),
            'description' => esc_html__( 'Use only if your image is blured', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);

        $this->add_control( 'custom_img_width', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Width of image in px', 'rehub-theme' ),
            'label_block'  => true,
            'condition'=> [ 'custom_col' => 'yes' ],
        ]);

        $this->add_control( 'custom_img_height', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Height of image in px', 'rehub-theme' ),
            'label_block'  => true,
            'condition'=> [ 'custom_col' => 'yes' ],
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
        if ( !empty( $settings['attrpanel'] ) ) {
            $settings['attrelpanel'] = rawurlencode( json_encode( $settings['attrpanel'] ) );
        }
        // Convert arrays to strings
        $this->normalize_arrays( $settings );

        $this->render_custom_js();
        echo wpsm_woocolumns_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Woo_Products_Columns );
