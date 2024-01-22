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
class WPSM_itinerary_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_itinerary';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Itinerary', 'rehub-theme');
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-checkbox';
    }

    public function get_style_depends() {
        return [ 'rhitinerary' ];
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
        $this->general_controls();
    }
    protected function general_controls() {
        $this->start_controls_section( 'general_section', [
            'label' => esc_html__( 'General', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $repeater = new \Elementor\Repeater();

            $repeater->add_control( 'icon', [
                'label' => esc_html__( 'Icon', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::ICON,
                'options'   => \Elementor\Control_Icon::get_icons(),
                'default' => 'rhicon rhi-circle-solid',
            ]);

            $repeater->add_control( 'color', [
                'label' => esc_html__( 'Set background color', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#409cd1',
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} .wpsm-itinerary-icon span' => 'background-color: {{VALUE}}',
                ],
            ]);

            $repeater->add_control(
                'content',
                [
                    'label' => esc_html__( 'Content', 'rehub-theme' ),
                    'type' => Controls_Manager::WYSIWYG,
                    'default' => esc_html__( 'Box Content', 'rehub-theme' ),
                    'show_label' => false,
                ]
            ); 

        $this->add_control( 'itinerary', [
            'label'    => esc_html__( 'Itinerary', 'rehub-theme' ),
            'type'     => \Elementor\Controls_Manager::REPEATER,
            'fields'   => $repeater->get_controls(),
            'title_field' => '{{{ icon }}}',
        ]);

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        wp_enqueue_style('rhitinerary');
        ?> 
            <div class="wpsm-itinerary">

                <?php if ( $settings['itinerary'] ) :?>
                    <?php foreach (  $settings['itinerary'] as $index => $item ):?>
                        <?php 
                            $tab_content_setting_key = $this->get_repeater_setting_key( 'content', 'itinerary', $index );
                        ?>
                        <div class="wpsm-itinerary-item">
                            <div class="wpsm-itinerary-icon">
                                <span style="background-color: <?php echo esc_attr($item['color']);?>"><i class="<?php echo esc_attr($item['icon']);?>"></i></span>
                            </div>
                            <div class="wpsm-itinerary-content">
                                <?php 
                                $mycontent = '<div '.$this->get_render_attribute_string( $tab_content_setting_key).'>'.$item['content'].'</div>';
                                ?>
                                <?php echo do_shortcode($mycontent);?>
                            </div>
                        </div>
                    <?php endforeach;?>
                <?php endif;?>

            </div>
        <?php
    }


}

Plugin::instance()->widgets_manager->register( new WPSM_itinerary_Widget );