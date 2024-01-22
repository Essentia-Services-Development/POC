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
class WPSM_News_With_Thumbs_Widget extends WPSM_Content_Widget_Base{

    /* Widget Name */
    public function get_name() {
        return 'news_with_thumbs_mod';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('News Block', 'rehub-theme');
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-posts-group';
    }

    /**
     * category name in which this widget will be shown
     * @since 1.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'content-modules' ];
    }


    protected function register_controls() {
        parent::register_controls();

        foreach ( [ 'enable_pagination', 'show' ] as $key => $value) {
            Controls_Stack::remove_control( $value );
        }
    }

    protected function control_fields() {
        $this->add_control( 'secondtype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type for second column', 'rehub-theme' ),
            'default'     => '1',
            'options'     => [
                '1'             => esc_html__( 'News with thumbnails', 'rehub-theme' ),
                '2'             => esc_html__( 'News without thumbnails', 'rehub-theme' ),
                '3'             => esc_html__( 'Big thumbnails', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'thirdtype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type for third column', 'rehub-theme' ),
            'default'     => 'no',
            'options'     => [
                'no'            => esc_html__( 'No', 'rehub-theme' ),
                '1'             => esc_html__( 'News with thumbnails', 'rehub-theme' ),
                '2'             => esc_html__( 'News without thumbnails', 'rehub-theme' ),
                '3'             => esc_html__( 'Big thumbnails', 'rehub-theme' ),
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
        echo wpsm_news_with_thumbs_mod_shortcode( $settings );
    }

}

Plugin::instance()->widgets_manager->register( new WPSM_News_With_Thumbs_Widget );
