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
class Widget_Wpsm_Regular_Blog_Post_List extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'regular_blog_loop';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Regular blog posts', 'rehub-theme');
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-featured-image';
    }

    protected function get_sections() {
        return [
            'general'   => esc_html__('General', 'rehub-theme'),
            'data'      => esc_html__('Data Settings', 'rehub-theme'),
            'filters'   => esc_html__('Filter Panel', 'rehub-theme')
        ];
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
        $this->normalize_arrays( $settings );
        $this->render_custom_js();
        echo wpsm_regular_blog_loop_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Regular_Blog_Post_List );
