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
class WPSM_News_Ticker_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_news_ticker';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('News ticker', 'rehub-theme');
    }

    public function get_style_depends() {
        return [ 'rhnewsticker' ];
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-post-navigation';
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
        $this->start_controls_section( 'general_section', [
            'label' => esc_html__( 'General', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control( 'label', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Label', 'rehub-theme' ),
            'description' => esc_html__('Label before news ticker', 'rehub-theme'),
            'label_block'  => true,
            'default' => 'Latest News',
        ]);
        $this->add_control( 'catname', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Category name', 'rehub-theme' ),
            'description' => esc_html__('Category name to show in ticker', 'rehub-theme'),
        ]);
        $this->add_control( 'catslug', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Category taxonomy', 'rehub-theme' ),
            'description' => esc_html__('Category taxonomy name. Leave blank if you need Post category. For post tags - set as post_tag', 'rehub-theme'),
            'default' => 'category',
        ]);
        $this->add_control( 'fetch', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Number of posts to show', 'rehub-theme' ),
            'description' => esc_html__('Default is 5', 'rehub-theme'),
            'default' => '5',
        ]);

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        echo wpsm_news_ticker_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new WPSM_News_Ticker_Widget );
