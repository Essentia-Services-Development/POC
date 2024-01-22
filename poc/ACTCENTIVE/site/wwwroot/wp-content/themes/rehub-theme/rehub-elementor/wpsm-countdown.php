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
class WPSM_Countdown_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_countdown';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Countdown', 'rehub-theme');
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-countdown';
    }

    /**
     * category name in which this widget will be shown
     * @since 1.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'rhwow-modules' ];
    }
    protected function register_controls() {
        $this->general_controls();
    }
    protected function general_controls() {
        $this->start_controls_section( 'general_section', [
            'label' => esc_html__( 'General', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control( 'date', [
            'type'        => \Elementor\Controls_Manager::DATE_TIME,
            'label'       => esc_html__( 'Choose date of finish', 'rehub-theme' ),
            'label_block'  => true,
        ]);


        $this->add_control( 'enable_light', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable light compact style?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $date = date_create_from_format( 'Y-m-d H:i', $this->get_settings( 'date' ) );
        $new_date = date_format($date, 'Y-m-d');
        $new_time = date_format($date, 'H:i');
        $countdown = explode('-', $new_date);
        $timedown = explode(':', $new_time);
        $year = $countdown[0];
        $month = $countdown[1];
        $day  = $countdown[2];
        $hour = $timedown[0];
        $min = $timedown[1];
        $light = $this->get_settings( 'enable_light' );
        $lightclass = $light ? 'gridcountdown' : '';
        echo '<div class="'.$lightclass.'">';
        echo wpsm_countdown(array('year'=> $year, 'month'=>$month, 'day'=>$day, 'hour'=>$hour, 'minute' => $min));
        echo '</div>';
    }

}

Plugin::instance()->widgets_manager->register( new WPSM_Countdown_Widget );