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
class Widget_Wpsm_Deal_Coupon_List extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_offer_list';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Deal/Coupon List', 'rehub-theme');
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-post-list';
    }  

    public function get_categories() {
        return [ 'deal-helper' ];
    }    

    protected function control_fields() {
        $this->add_control( 'aff_link', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make link as affiliate?', 'rehub-theme' ),
            'description' => esc_html__( 'This will change all inner post links to affiliate link of post offer', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
    }    

    protected function rehub_filter_values( $haystack ) {
        foreach ( $haystack as $key => $value ) {
            if ( is_array( $value ) ) {
                $haystack[ $key ] = $this->rehub_filter_values( $haystack[ $key ]);
            }

            if ( empty( $haystack[ $key ] ) ) {
                unset( $haystack[ $key ] );
            }
        }

        return $haystack;
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( is_array( $settings['filterpanel'] ) ) {
            $settings['filterpanel'] = $this->rehub_filter_values( $settings['filterpanel'] );
            $settings['filterpanel'] = rawurlencode( json_encode( $settings['filterpanel'] ) );
        }
        // print_r($settings);
        $this->normalize_arrays( $settings );
        $this->render_custom_js();
        echo wpsm_offer_list_loop_shortcode( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Deal_Coupon_List );
