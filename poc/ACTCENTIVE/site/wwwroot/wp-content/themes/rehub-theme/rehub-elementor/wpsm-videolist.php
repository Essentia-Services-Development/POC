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
class WPSM_Video_Playlist_Block_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'video_mod';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Video playlist block', 'rehub-theme');
    }

    public function get_script_depends() {
        return [ 'video_playlist', 'flexslider' ];
    }

    public function get_style_depends() {
        return [ 'video-pl' ];
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-thumbnails-down';
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
        $this->start_controls_section( 'general_section', [
            'label' => esc_html__( 'General', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control( 'videolinks', [
            'type'        => \Elementor\Controls_Manager::TEXTAREA,
            'label'       => esc_html__( 'Links on videos', 'rehub-theme' ),
            'description'   => esc_html__( 'Each link must be divided by COMMA. Works with youtube and vimeo. Example for youtube: https://www.youtube.com/watch?v=ZZZZZZZZZZZ, https://www.youtube.com/watch?v=YYYYYY', 'rehub-theme' ),
            'label_block'  => true,
        ]);
        $this->add_control( 'playlist_type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Playlist type', 'rehub-theme' ),
            'description' => esc_html__('Video gallery works only with youtube or vimeo, but not at once. Also, playlist type can be only one on page. Slider type can have multiple instances', 'rehub-theme'),
            'options'     => array(
                'playlist'  => esc_html__('Playlist', 'rehub-theme'),
                'slider'    => esc_html__('Slider', 'rehub-theme'),
            ),
            'default' => 'playlist'
        ]);
        $this->add_control( 'playlist_auto_play', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Autoplay ON / OFF:', 'rehub-theme' ),
            'description' => esc_html__('Autoplay does not work on mobile devices (android, windows phone, iOS)', 'rehub-theme'),
            'options'     => array(
                '0'  => esc_html__('OFF', 'rehub-theme'),
                '1'    => esc_html__('ON', 'rehub-theme'),
            ),
            'default' => '0',
            'condition' => [ 'playlist_type' => 'playlist' ]
        ]);
        $this->add_control( 'playlist_width', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Column style', 'rehub-theme' ),
            'options'     => array(
                'full'  => esc_html__('Full width', 'rehub-theme'),
                'stack'    => esc_html__('Stack', 'rehub-theme'),
            ),
            'default' => 'full',
            'condition' => [ 'playlist_type' => 'playlist' ]
        ]);
        $this->add_control( 'playlist_host', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Video host', 'rehub-theme' ),
            'options'     => array(
                'youtube'  => esc_html__('Youtube', 'rehub-theme'),
                'vimeo'    => esc_html__('Vimeo', 'rehub-theme'),
            ),
            'default' => 'youtube',
        ]);
        $this->add_control( 'key', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Youtube API key', 'rehub-theme' ),
            'description' => esc_html__('Place here your own API key for youtube if default is not working', 'rehub-theme').' <a href="https://developers.google.com/youtube/v3/getting-started" target="_blank">API Youtube</a>',
            'label_block'  => true,
        ]);

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        echo video_mod_function( $settings );      
    }

}

Plugin::instance()->widgets_manager->register( new WPSM_Video_Playlist_Block_Widget );
