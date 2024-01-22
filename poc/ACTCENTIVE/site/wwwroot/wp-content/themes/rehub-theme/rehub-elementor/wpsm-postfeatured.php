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
class Widget_Wpsm_Post_Featured_Section extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_featured';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Featured section', 'rehub-theme');
    }

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        wp_enqueue_style('elflexslider');
        wp_enqueue_script('elflexslider');
        wp_enqueue_script('elflexinit');
    }
    public function get_script_depends() {
        return [ 'rhyall' ];
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-gallery-group';
    } 
    protected function register_controls() {
        parent::register_controls();

        foreach ( [ 'enable_pagination', 'show', 'offset' ] as $key => $value) {
            Controls_Stack::remove_control( $value );
        }
    }
    protected function get_sections() {
        return [
            'general'   => esc_html__('Data query', 'rehub-theme'),
            'data'      => esc_html__('Data Settings', 'rehub-theme'),
            'control'   => esc_html__('Design Control', 'rehub-theme'),
        ];
    }

    protected function control_fields() {

        $this->add_control( 'show_featured_products', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Show only featured products?', 'rehub-theme' ),
            'condition'   => [ 'post_type' => 'product' ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);

        $this->add_control( 'dis_excerpt', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable exerpt?', 'rehub-theme' ),
            'condition'   => [ 'feat_type' => [ '1', '2' ] ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);

        $this->add_control( 'bottom_style', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Show text in left bottom side?', 'rehub-theme' ),
            'condition'   => [ 'feat_type' => [ '1', '2' ] ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
        $this->add_control( 'show_posts', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Number of posts to show in slider', 'rehub-theme' ),
            'condition'   => [ 'feat_type' => [ '1', '2' ] ],
            'default'     => '5',
            'label_block'  => true,
        ]);
        $this->add_control( 'custom_height', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Custom height (default is 490) in px', 'rehub-theme' ),
            'condition'   => [ 'feat_type' => [ '2' ] ],
            'default'     => '490px',
            'label_block'  => true,
        ]);

    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $settings = $this->replace_key( $settings, ['show_posts'], ['show'] );

        $this->normalize_arrays( $settings );
        $this->render_custom_js();
        echo wpsm_featured_function( $settings );
    }
    protected function replace_key( &$settings, $elementor_key, $orignal_keys) {
        $keys = array_keys( $settings );
        for ( $i = 0; $i < count( $elementor_key ); $i++ ) {
            if ( false === $index = array_search( $elementor_key[ $i ], $keys )) {
            throw new Exception(sprintf( 'Key "%s" does not exit', $elementor_key[ $i ] ) );
            }
            $keys[$index] =  $orignal_keys[ $i ];
        }
        return array_combine( $keys, array_values( $settings ) );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Post_Featured_Section );
