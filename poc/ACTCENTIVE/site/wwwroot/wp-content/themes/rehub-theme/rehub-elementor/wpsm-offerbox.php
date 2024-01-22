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
class Widget_Offerbox extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm-offerbox';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Offerbox', 'rehub-theme');
    }

    public function get_script_depends() {
        return [ 'affegg_coupons' ];
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
            'query'   => esc_html__('Data query', 'rehub-theme'),
            'general'   => esc_html__('Manual Fields', 'rehub-theme'),
        ];
    }

    protected function general_fields() {
        $this->add_control(
            'offer_url',
            [
                'label' => esc_html__('Offer url', 'rehub-theme'),
                'type' => Controls_Manager::TEXT,
            ]
        );         
        $this->add_control(
            'offer_title',
            [
                'label' => esc_html__('Title', 'rehub-theme'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Offer title',
            ]
        );    
        $this->add_control(
            'offer_desc',
            [
                'label' => esc_html__('Description', 'rehub-theme'),
                'type' => Controls_Manager::WYSIWYG,
            ]
        ); 
        $this->add_control(
            'disclaimer',
            [
                'label' => esc_html__('Disclaimer or additional information', 'rehub-theme'),
                'type' => Controls_Manager::TEXTAREA,
            ]
        );         
        $this->add_control(
            'offer_price_old',
            [
                'label' => esc_html__( 'Regular price', 'rehub-theme' ),
                'type' => Controls_Manager::TEXT,
            ]
        ); 
        $this->add_control(
            'offer_price',
            [
                'label' => esc_html__( 'Sale price', 'rehub-theme' ),
                'type' => Controls_Manager::TEXT,
            ]
        );
        $this->add_control(
            'percentageSaved',
            [
                'label' => esc_html__( 'Discount (%)', 'rehub-theme' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'step' => 1,
            ]
        ); 
        $this->add_control(
            'rating',
            [
                'label' => esc_html__( 'Star rating', 'rehub-theme' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 5,
                'step' => 1,
            ]
        );        
        $this->add_control(
            'offer_coupon',
            [
                'label' => esc_html__( 'Coupon code', 'rehub-theme' ),
                'type' => Controls_Manager::TEXT,
            ]
        ); 
        $this->add_control( 'offer_coupon_date', [
            'type'        => \Elementor\Controls_Manager::DATE_TIME,
            'label'       => esc_html__( 'Choose date of finish', 'rehub-theme' ),
            'label_block'  => true,
        ]);
        $this->add_control( 'offer_coupon_mask', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Mask coupon code?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
        ]);
        $this->add_control(
            'offer_coupon_mask_text',
            [
                'label' => esc_html__( 'Mask Text', 'rehub-theme' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Reveal coupon', 'rehub-theme'),
                'condition' => [ 'offer_coupon_mask' => '1' ]
            ]
        );        
        $this->add_control(
            'offer_btn_text',
            [
                'label' => esc_html__( 'Button Text', 'rehub-theme' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Buy this item', 'rehub-theme'),
            ]
        );                         

        $this->add_control( 'image_id', [
            'label' => esc_html__( 'Image', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'default' => [
                'url' => \Elementor\Utils::get_placeholder_image_src(),
            ],
            'label_block'  => true,
        ]);
        $this->add_control( 'bordercolor', [
            'label' => esc_html__( 'Border color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .bigofferblock' => 'border: 2px solid {{VALUE}}',
            ],
        ]);                       
                     
    }

    protected function query_fields() {
        $this->add_control( 'postid', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Post names', 'rehub-theme' ),
            'description' => esc_html__( 'Choose post to import Offer or add details in Manual Fields', 'rehub-theme' ),
            'options'     => [],
            'label_block'  => true,
            'multiple'     => false,
            'callback'    => 'get_name_posts_list',           
        ]);  
        /*$this->add_control( 'compact', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Compact view', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'id',
                        'operator' => '!=',
                        'value'    => '',
                    ],
                ],
            ],            
        ]);*/                   
    } 
    

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $id = '';
        if(!empty($settings['postid'])){
            $id = $settings['postid'];
        }
        else if(!empty($settings['id'])){
            $id = $settings['id'];
        }
        $btnwoo = '';
        if(!empty($id)){
            if('product' == get_post_type($id)){

                $product = wc_get_product($id);

                $image_id  = get_post_thumbnail_id($id);  
                $image_url = wp_get_attachment_image_src($image_id,'full');
                $offer_thumb = $image_url[0]; 

                $offer_price_old = $product->get_regular_price();
                $offer_price = $product->get_price();             
                $percentageSaved='';
                if ($offer_price_old && $offer_price_old !=0) {
                    $percentageSaved = round( ( ( $offer_price_old - $offer_price ) / $offer_price_old ) * 100 );
                }  
                $offer_title = get_the_title($id); 
                $offer_desc = $product->get_short_description();    
                $offer_coupon = get_post_meta( $id, 'rehub_woo_coupon_code', true );
                $rating = $product->get_average_rating();   
                $btnwoo = apply_filters( 'woocommerce_loop_add_to_cart_link',
                    sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn  btn_offer_block %s %s product_type_%s"%s %s>%s</a>',
                    esc_url( $product->add_to_cart_url() ),
                    esc_attr( $product->get_id() ),
                    esc_attr( $product->get_sku() ),
                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                    $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
                    esc_attr( $product->get_type() ),
                    $product->get_type() =='external' ? ' target="_blank"' : '',
                    $product->get_type() =='external' ? ' rel="nofollow"' : '',
                    esc_html( $product->add_to_cart_text() )
                    ),
                $product );   
                $offer_coupon_mask_text = ''; 
                $disclaimer = get_post_meta($id, 'rehub_offer_disclaimer', true);

            }else{
                $offer_post_url = get_post_meta( $id, 'rehub_offer_product_url', true );
                $offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
                $offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url );
                $offer_price = get_post_meta( $id, 'rehub_offer_product_price', true );
                $offer_price_old = get_post_meta( $id, 'rehub_offer_product_price_old', true );
                $offer_title = get_post_meta( $id, 'rehub_offer_name', true );
                $offer_thumb = get_post_meta( $id, 'rehub_offer_product_thumb', true );
                $offer_btn_text = get_post_meta( $id, 'rehub_offer_btn_text', true );
                $offer_coupon = get_post_meta( $id, 'rehub_offer_product_coupon', true );
                $offer_coupon_date = get_post_meta( $id, 'rehub_offer_coupon_date', true );
                $offer_coupon_mask = get_post_meta( $id, 'rehub_offer_coupon_mask', true );
                $offer_desc = get_post_meta( $id, 'rehub_offer_product_desc', true );
                $disclaimer = get_post_meta($id, 'rehub_offer_disclaimer', true);
                $rating = get_post_meta($id, 'rehub_review_overall_score', true);
                if($rating) $rating = $rating / 2;
                $offer_coupon_mask_text = '';                
            }

        }else{
            $offer_post_url = $settings['offer_url'];
            $offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
            $offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url ); 
            $offer_price = $settings['offer_price'];  
            $offer_price_old = $settings['offer_price_old'];
            $offer_title = $settings['offer_title'];
            $offer_desc = $settings['offer_desc'];
            $rating = $settings['rating'];
            $disclaimer = $settings['disclaimer'];
            $image_url = wp_get_attachment_image_src($settings['image_id']['id'], 'full');
            if(!empty($image_url)) $image_url = $image_url[0];
            $offer_thumb = $image_url;
            $offer_coupon = $settings['offer_coupon'];
            $offer_coupon_date = $settings['offer_coupon_date'];
            $offer_coupon_mask = $settings['offer_coupon_mask'];
            $offer_btn_text = $settings['offer_btn_text'];   
            $offer_coupon_mask_text = $settings['offer_coupon_mask_text']; 
            $percentageSaved = $settings['percentageSaved'];    
        }
        $this->render_custom_js();
        include(rh_locate_template('inc/parts/offerbigpart.php'));
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Offerbox );
