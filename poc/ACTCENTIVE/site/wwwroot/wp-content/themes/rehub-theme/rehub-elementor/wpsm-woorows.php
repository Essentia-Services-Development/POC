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
class Widget_Wpsm_Woo_Products_Rows extends WPSM_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_woorows';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Woocommerce List', 'rehub-theme');
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    protected function get_sections() {
        return [
            'general'   => esc_html__('Data query', 'rehub-theme'),
            'data'      => esc_html__('Data Settings', 'rehub-theme'),
            'taxonomy'  => esc_html__('Additional Taxonomy Query', 'rehub-theme'),
            'filters'   => esc_html__('Filter Panel', 'rehub-theme'),
            'attribute'   => esc_html__('Custom Attribute Panel', 'rehub-theme'),  
            'control'   => esc_html__('Design Control', 'rehub-theme')          
        ];
    }
    protected function control_fields() {
        $this->add_control( 'designtype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type of area', 'rehub-theme' ),
            'options'     => [
                '1'             => esc_html__( 'Regular', 'rehub-theme' ),
                'compact'       => esc_html__( 'Wholesale Compact List', 'rehub-theme' )
            ],
            'label_block' => true,
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
        if ( !empty( $settings['filterpanel'] ) ) {
            $settings['filterpanel'] = $this->rehub_filter_values( $settings['filterpanel'] );
            $settings['filterpanel'] = rawurlencode( json_encode( $settings['filterpanel'] ) );
        }
        if ( !empty( $settings['attrpanel'] ) ) {
            $settings['attrelpanel'] = rawurlencode( json_encode( $settings['attrpanel'] ) );
        }          
        // Convert arrays to strings
        $this->normalize_arrays( $settings );
        $this->render_custom_js();
        echo wpsm_woorows_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Woo_Products_Rows );
