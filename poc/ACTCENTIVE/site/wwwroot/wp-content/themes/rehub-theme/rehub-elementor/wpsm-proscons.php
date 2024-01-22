<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit('Restricted Access');
} // Exit if accessed directly

/**
 * Prons And Cons Widget class.
 *
 * 'pros_cons_block' shortcode
 *
 * @since 1.0.0
 */
class WPSM_Pros_Cons_Block_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'pros_cons_block';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Prons And Cons Block', 'rehub-theme');
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-check-circle';
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
        $this->start_controls_section( 'select_layout_block', [
            'label' => esc_html__( 'Blocks', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control( 'select_block', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Select Block', 'rehub-theme' ),
            'default'    => 'wpsm_pros_shortcode',
            'options'     => [
                'wpsm_pros_shortcode'       =>  esc_html__('Pros Block', 'rehub-theme'),
                'wpsm_cons_shortcode'       =>  esc_html__('Cons Block', 'rehub-theme'),
            ],
            'label_block'  => true,
        ]);
        $this->add_control( 'title', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Positives', 'rehub-theme' ),
            'default' => 'Positives:',
            'label_block'  => true,
            'condition' => [ 'select_block' => 'wpsm_pros_shortcode' ]
        ]);  
        $this->add_control( 'content', [
            'type'        => \Elementor\Controls_Manager::WYSIWYG,
            'label'       => esc_html__( 'Content', 'rehub-theme' ),
            'label_block'  => true,
            'default' => '<ul><li>Positive one</li><li>Positive two</li><li>Positive three</li></ul>',
            'condition' => [ 'select_block' => 'wpsm_pros_shortcode' ]
        ]);   
        $this->add_control( 'cons_title', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Negatives', 'rehub-theme' ),
            'label_block'  => true,
            'default' => 'Negatives:',
            'condition' => [ 'select_block' => 'wpsm_cons_shortcode' ],
        ]);
        $this->add_control( 'cons_content', [
            'type'        => \Elementor\Controls_Manager::WYSIWYG,
            'label'       => esc_html__( 'Content', 'rehub-theme' ),
            'label_block'  => true,
            'default' => '<ul><li>Negative one</li><li>Negative two</li><li>Negative three</li></ul>',
            'condition' => [ 'select_block' => 'wpsm_cons_shortcode' ]
        ]);                          
        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        if( $settings['select_block'] == 'wpsm_cons_shortcode' ) {
            $cons_title = rh_check_empty_index($settings, 'cons_title');
            if($cons_title){
                $settings['title'] = $settings['cons_title'];
            }
            $cons_content = rh_check_empty_index($settings, 'cons_content');
            if($cons_content){
                $settings['content'] = $settings['cons_content'];
            }            
            echo wpsm_cons_shortcode($settings, $settings['content']);
        }else{
            echo wpsm_pros_shortcode($settings, $settings['content']);
        }
    }
}

Plugin::instance()->widgets_manager->register( new WPSM_Pros_Cons_Block_Widget );
