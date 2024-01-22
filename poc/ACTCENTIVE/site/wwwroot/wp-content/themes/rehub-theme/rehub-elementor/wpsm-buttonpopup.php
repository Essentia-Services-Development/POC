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
class WPSM_Button_Popup_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_button_popup';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Button with Popup', 'rehub-theme');
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-archive-title';
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
        $this->start_controls_section( 'button_popup_block_section', [
            'label' => esc_html__( 'Button with Popup Block', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control( 'color', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Color of button', 'rehub-theme' ),
            'default'     => 'btncolor',
            'options'     => [
                'main'        =>  esc_html__('Main Theme Color', 'rehub-theme'),
                'secondary'        =>  esc_html__('Secondary Theme Color', 'rehub-theme'),
                'btncolor'        =>  esc_html__('Main Color of Buttons', 'rehub-theme'),
                'orange'        =>  esc_html__('orange', 'rehub-theme'),
                'gold'        =>  esc_html__('gold', 'rehub-theme'),
                'black'        =>  esc_html__('black', 'rehub-theme'),
                'blue'        =>  esc_html__('blue', 'rehub-theme'),
                'red'        =>  esc_html__('red', 'rehub-theme'),
                'green'        =>  esc_html__('green', 'rehub-theme'),
                'rosy'        =>  esc_html__('rosy', 'rehub-theme'),
                'brown'        =>  esc_html__('brown', 'rehub-theme'),
                'pink'        =>  esc_html__('pink', 'rehub-theme'),
                'purple'        =>  esc_html__('purple', 'rehub-theme'),
                'teal'        =>  esc_html__('teal', 'rehub-theme'),
                ],
            'label_block' => true,
        ]);
        $this->add_control( 'size', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Button Size', 'rehub-theme' ),
            'default'     => 'medium',
            'options'     => [
                'medium'        =>  esc_html__('Medium', 'rehub-theme'),
                'small'        =>  esc_html__('Small', 'rehub-theme'),
                'big'        =>  esc_html__('Big', 'rehub-theme'),
                ],
            'label_block' => true,
        ]);
        $this->add_control( 'enable_icon', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable icon in button?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);
        $this->add_control( 'icon', [
            'label' => esc_html__( 'Icon', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::ICON,
            'options'   => \Elementor\Control_Icon::get_icons(),
            'default' => '',
            'condition'=> [ 'enable_icon' => 'yes' ],
        ]);
        $this->add_control( 'btn_text', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Button text', 'rehub-theme' ),
            'description' => esc_html__( 'Enter Text for Button', 'rehub-theme' ),
            'label_block'  => true,
            'default' => 'Click on me'
        ]);
        $this->add_control( 'max_width', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Max width of popup', 'rehub-theme' ),
            'default'     => '500',
            'label_block'  => true,
        ]);
        $this->add_control( 'content', [
            'type'        => \Elementor\Controls_Manager::TEXTAREA,
            'label'       => esc_html__( 'Content', 'rehub-theme' ),
            'label_block'  => true,
            'default' => 'Content of popup'
        ]);

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        echo wpsm_button_popup_funtion( $settings, $settings['content'] );
    }

}

Plugin::instance()->widgets_manager->register( new WPSM_Button_Popup_Widget );