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
class Widget_Wpsm_Woo_Products_Grid extends WPSM_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_woogrid';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Grid of woocommerce products', 'rehub-theme');
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
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

        $this->normalize_arrays( $settings );
        $this->render_custom_js();
        echo wpsm_woogrid_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Woo_Products_Grid );
