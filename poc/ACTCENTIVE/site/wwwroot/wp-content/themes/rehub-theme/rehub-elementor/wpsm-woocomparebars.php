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
class Widget_Wpsm_Woo_Products_Compare extends WPSM_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_woocomparebars';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Woo Compare Bars', 'rehub-theme');
    }

    protected function get_sections() {
        return [
            'general'   => esc_html__('General', 'rehub-theme')
        ];
    }

    public function get_icon() {
        return 'eicon-skill-bar';
    }    

    protected function general_fields() {
        

        $this->add_control( 'ids', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Product names', 'rehub-theme' ),
            'description' => esc_html__( 'Enter the Name of Products', 'rehub-theme' ),
            'options'     => [],
            'label_block'  => true,
            'multiple'     => true,
            'callback'    => 'get_wc_products_posts_list'
        ]);

        $this->add_control( 'attr', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Attribute names', 'rehub-theme' ),
            'description' => 'Choose attributes which have numeric values, other will have errors',
            'options'     => [],
            'label_block'  => true,
            'multiple'     => true,
            'callback'    => 'rehub_wpsm_search_woo_attributes'
        ]);        

        $this->add_control( 'min', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Attribute for minimum priority', 'rehub-theme' ),
            'description' => 'By default, bar with maximum value will be highlighted. You can set here number of attribute which will be highlighted with minimum value. For example, if you choosed 5 attributes above, set number 3 if you want to highlight minimum in third attribute. For multiple, use comma divider. For example: 3,5',
        ]);
    }

    protected function style_control_fields() {
        $this->add_control( 'color', [
            'type'        => \Elementor\Controls_Manager::COLOR,
            'label'       => esc_html__( 'Color', 'rehub-theme' ),
            'description' => 'Set default color or leave empty to leave default color as grey',
        ]);

        $this->add_control( 'markcolor', [
            'type'        => \Elementor\Controls_Manager::COLOR,
            'label'       => esc_html__( 'Highlight Color', 'rehub-theme' ),
            'description' => 'Set highlighted color or leave empty to leave default color as orange',
        ]);                            
    }   

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        // Convert arrays to strings
        $this->normalize_arrays( $settings );
        echo wpsm_woo_versus_function( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Woo_Products_Compare );