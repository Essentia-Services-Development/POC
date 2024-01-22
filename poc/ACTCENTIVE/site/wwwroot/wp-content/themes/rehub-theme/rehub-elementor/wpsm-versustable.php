<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit('Restricted Access');
} // Exit if accessed directly

/**
 *Button With Popup Widget class.
 *
 *
 * @since 1.0.0
 */
class WPSM_Versus_Line_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_versus';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Versus Table', 'rehub-theme');
    }

    public function get_style_depends() {
        return [ 'rhversus' ];
    }
        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-table';
    }

    /**
     * category name in which this widget will be shown
     * @since 1.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'helpler-modules' ];
    }
    protected function register_controls() {
        $this->wpsm_versus_shortcode_fields();
    }

    protected function wpsm_versus_shortcode_fields() {
        $this->start_controls_section( 'versus_block_section', [
            'label' => esc_html__( 'Versus Line Block', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control( 'heading', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Heading', 'rehub-theme' ),
            'label_block'  => true,
            'default' => 'Versus Title'
        ]);
        $this->add_control( 'subheading', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Subheading', 'rehub-theme' ),
            'label_block'  => true,
            'default' => 'Versus subline',
        ]);
        $this->add_control( 'type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type', 'rehub-theme' ),
            'default'     => 'two',
            'options'     => [
                'two'        =>  esc_html__('Two Column', 'rehub-theme'),
                'three'        =>  esc_html__('Three Column', 'rehub-theme'),
                ],
            'label_block' => true,
        ]);
        $this->end_controls_section();

        $this->start_controls_section( 'first_column_section', [
            'label' => esc_html__( 'First Column', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control( 'firstcolumntype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'First Column Type', 'rehub-theme' ),
            'default'     => 'text',
            'options'     => [
                'text'        =>  esc_html__('Text', 'rehub-theme'),
                'image'        =>  esc_html__('Image', 'rehub-theme'),
                'tick'        =>  esc_html__('Check Icon', 'rehub-theme'),
                'times'        =>  esc_html__('Cross Icon', 'rehub-theme'),
                ],
            'label_block' => true,
        ]);
        $this->add_control( 'firstcolumncont', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Place text', 'rehub-theme' ),
            'condition'   => [ 'firstcolumntype' => [ 'text' ] ],
            'label_block'  => true,
            'default' => 'Value 1'
        ]);
        $this->add_control( 'firstcolumnimg', [
            'type'        => \Elementor\Controls_Manager::MEDIA,
            'label'       => esc_html__( 'Upload Image', 'rehub-theme' ),
            'condition'   => [ 'firstcolumntype' => [ 'image' ] ],
            'label_block'  => true,
        ]);
        $this->add_control( 'firstcolumngrey', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make first column unhighlighted?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);
        $this->end_controls_section();

        $this->start_controls_section( 'second_column_section', [
            'label' => esc_html__( 'Second Column', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control( 'secondcolumntype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Second Column Type', 'rehub-theme' ),
            'default'     => 'text',
            'options'     => [
                'text'        =>  esc_html__('Text', 'rehub-theme'),
                'image'        =>  esc_html__('Image', 'rehub-theme'),
                'tick'        =>  esc_html__('Check Icon', 'rehub-theme'),
                'times'        =>  esc_html__('Cross Icon', 'rehub-theme'),
                ],
            'label_block' => true,
        ]);
        $this->add_control( 'secondcolumncont', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Place text', 'rehub-theme' ),
            'condition' => [ 'secondcolumntype' => 'text' ],
            'label_block'  => true,
            'default' => 'Value 2',
        ]);
        $this->add_control( 'secondcolumnimg', [
            'type'        => \Elementor\Controls_Manager::MEDIA,
            'label'       => esc_html__( 'Upload Image', 'rehub-theme' ),
            'condition'   => [ 'secondcolumntype' => [ 'image' ] ],
            'label_block'  => true,
        ]);
        $this->add_control( 'secondcolumngrey', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make second column unhighlighted?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);
        $this->end_controls_section();

        $this->start_controls_section( 'third_column_section', [
            'label' => esc_html__( 'Third Column', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            'condition' => [ 'type' => 'three' ],
        ]);

        $this->add_control( 'thirdcolumntype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Third Column Type', 'rehub-theme' ),
            'default'     => 'text',
            'options'     => [
                'text'        =>  esc_html__('Text', 'rehub-theme'),
                'image'        =>  esc_html__('Image', 'rehub-theme'),
                'tick'        =>  esc_html__('Check Icon', 'rehub-theme'),
                'times'        =>  esc_html__('Cross Icon', 'rehub-theme'),
                ],
            'condition' => [ 'type' => 'three' ],
            'label_block' => true,
        ]);
        $this->add_control( 'thirdcolumncont', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Place text', 'rehub-theme' ),
            'condition' => [ 'thirdcolumntype' => 'text', 'type' => 'three' ],
            'label_block'  => true,
            'default' => 'Value 3',
        ]);
        $this->add_control( 'thirdcolumnimg', [
            'type'        => \Elementor\Controls_Manager::MEDIA,
            'label'       => esc_html__( 'Upload Image', 'rehub-theme' ),
            'condition' => [ 'thirdcolumntype' => 'image' ],
            'label_block'  => true,
        ]);
        $this->add_control( 'thirdcolumngrey', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make third column unhighlighted?', 'rehub-theme' ),
            'condition' => [ 'type' => 'three' ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);

        $this->end_controls_section();

        $this->start_controls_section( 'versus_style_section', [
            'label' => esc_html__( 'Versus Line Style', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);
        $this->add_control( 'bg', [
            'label' => esc_html__( 'Background color (optional)', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
        ]);
        $this->add_control( 'color', [
            'label' => esc_html__( 'Text color (optional)', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
        ]);

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $settings['firstcolumnimg'] = (!empty($settings['firstcolumnimg']['id'])) ? $settings['firstcolumnimg']['id'] : '';
        $settings['secondcolumnimg'] = (!empty($settings['secondcolumnimg']['id'])) ? $settings['secondcolumnimg']['id'] : '';
        $settings['thirdcolumnimg'] = (!empty($settings['thirdcolumnimg']['id'])) ? $settings['thirdcolumnimg']['id'] : '';
        echo wpsm_versus_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new WPSM_Versus_Line_Widget );
