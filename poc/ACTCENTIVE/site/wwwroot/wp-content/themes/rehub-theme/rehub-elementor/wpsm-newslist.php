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
class Widget_Wpsm_Small_Thumb_Loop_Widget extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'small_thumb_loop';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('News/Directory list', 'rehub-theme');
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-post-list';
    }

    protected function get_sections() {

        return [
            'general'   => esc_html__('General', 'rehub-theme'),
            'data'      => esc_html__('Data Settings', 'rehub-theme'),
            'type'      => esc_html__('Type', 'rehub-theme'),
            'filters'   => esc_html__('Filter Panel', 'rehub-theme')
        ];
    }
    protected function type_fields() {
        $this->add_control( 'type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Set type', 'rehub-theme' ),
            'description' => esc_html__('Select style type', 'rehub-theme'),
            'default'     => '1',
            'options'     => [
                '1'             => esc_html__( 'Directory/Community Style', 'rehub-theme' ),
                '2'             => esc_html__( 'News Magazine style', 'rehub-theme' ),
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

        if ( is_array( $settings['filterpanel'] ) ) {
            $settings['filterpanel'] = $this->rehub_filter_values( $settings['filterpanel'] );
            $settings['filterpanel'] = rawurlencode( json_encode( $settings['filterpanel'] ) );
        }
        // print_r($settings);
        $this->normalize_arrays( $settings );
        $this->render_custom_js();
        echo wpsm_small_thumb_loop_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Small_Thumb_Loop_Widget );
