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
class Widget_Wpsm_CEBox extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_scorebox';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Content Egg Box', 'rehub-theme');
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-price-list';
    }  

    public function get_categories() {
        return [ 'deal-helper' ];
    }    

    protected function get_sections() {
        return [
            'general'   => esc_html__('Data query', 'rehub-theme'),
        ];
    }

    protected function general_fields() {
        $this->add_control( 'postid', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Post names', 'rehub-theme' ),
            'description' => esc_html__( 'Choose post from which you want to import Content Egg box', 'rehub-theme' ),
            'options'     => [],
            'label_block'  => true,
            'multiple'     => false,
            'callback'    => 'get_name_posts_list',            
        ]);
        $this->add_control( 'type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Choose Type', 'rehub-theme' ),
            'default'     => 'ceoffer',
            'options'     => [
                'ceoffer'=> esc_html__('Post box with CE comparison widget', 'rehub-theme'),
                'cemerchant'=>  esc_html__('Content Egg merchants table', 'rehub-theme'),
                'cewidget'=>  esc_html__('Content Egg logo widget', 'rehub-theme'),
                'cegrid'=>  esc_html__('Content Egg grid', 'rehub-theme'),
                'celist'=>  esc_html__('Content Egg list with offer images', 'rehub-theme'),
                'celistlogo'=>  esc_html__('Content Egg list with logo images', 'rehub-theme'),
                'cestat'=>  esc_html__('Content Egg Price statistic', 'rehub-theme'),
                'cehistory'=>  esc_html__('Content Egg price history', 'rehub-theme'),
                'cealert'=>  esc_html__('Content Egg price alert', 'rehub-theme'),
            ],
            'label_block' => true,
        ]);        

    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $offertype = $settings['type'];
        $post_id = $settings['postid'];
        if(!$post_id){
            $post_id = get_the_ID();
        }
        if($offertype == 'ceoffer'){
            echo wpsm_get_bigoffer(array('post_id'=> $post_id));
        }else{
            if($offertype == 'cemerchant'){
                $template = 'custom/all_merchant_widget_group';
            }

            else if($offertype == 'cewidget'){
                $template = 'custom/all_logolist_widget';
            }   

            else if($offertype == 'cegrid'){
                $template = 'custom/all_offers_grid';
            }

            else if($offertype == 'celist'){
                $template = 'custom/all_offers_list';
            }               

            else if($offertype == 'celistlogo'){
                $template = 'custom/all_offers_logo_group';
            }

            else if($offertype == 'celistdef'){
                $template = 'offers_list';
            }               

            else if($offertype == 'celistdeflogo'){
                $template = 'offers_logo';
            }               

            else if($offertype == 'cestat'){
                $template = 'price_statistics';
            }   

            else if($offertype == 'cehistory'){
                $template = 'custom/all_pricehistory_full';
            }   

            else if($offertype == 'cealert'){
                $template = 'custom/all_pricealert_full';
            } 
            $atts = array();
            $atts['post_id'] = $post_id;
            $atts['template'] = $template;
            if(defined('\ContentEgg\PLUGIN_PATH')) {
                echo \ContentEgg\application\BlockShortcode::getInstance()->viewData($atts);
            }
        }   
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_CEBox );
