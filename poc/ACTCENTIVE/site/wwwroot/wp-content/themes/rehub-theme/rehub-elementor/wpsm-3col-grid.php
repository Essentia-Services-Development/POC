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
class Widget_Wpsm_three_colgrid_Mod extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'three_colgrid_mod';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('3 column grid', 'rehub-theme');
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-slider-push';
    } 

    public function get_categories() {
        return [ 'content-modules' ];
    }

    protected function get_sections() {
        return [
            'general'   => esc_html__('Data query', 'rehub-theme'),
            'control'   => esc_html__('Design Control', 'rehub-theme'),
        ];
    }    

    protected function control_fields() {

        $this->add_control( 'dis_meta', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable meta?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
            'selectors' => [
                 '{{WRAPPER}} .post-meta' => 'display:none',
            ],            
        ]);
    }


    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $this->normalize_arrays( $settings );
        $this->render_custom_js();
        echo wpsm_three_col_posts_function( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_three_colgrid_Mod );
