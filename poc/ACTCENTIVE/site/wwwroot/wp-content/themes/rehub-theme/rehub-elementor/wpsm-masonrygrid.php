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
class Widget_Wpsm_Masonry_Grid extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'grid_loop_mod';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Masonry grid', 'rehub-theme');
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-posts-masonry';
    }

    protected function register_controls() {
        parent::register_controls();
        foreach ( [ 'exerpt_count', 'disable_meta', 'disable_price', 'image_padding', 'enable_btn', ] as $key => $value) {
            Controls_Stack::remove_control( $value );
        }
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
        echo wpsm_grid_loop_mod_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Masonry_Grid );
