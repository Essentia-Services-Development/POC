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
class WPSM_Hover_Banner_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_hover_banner';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Hover Banner', 'rehub-theme');
    }

    public function get_style_depends() {
        return [ 'rhbanner' ];
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-image-rollover';
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
        $this->style_controls();
    }
    protected function general_controls() {
        $this->start_controls_section( 'general_section', [
            'label' => esc_html__( 'General', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control( 'title', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Title', 'rehub-theme' ),
            'label_block'  => true,
            'default' => 'Main title',
        ]);      

        $this->add_control( 'subtitle', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Subtitle', 'rehub-theme' ),
            'label_block'  => true,
            'default' => 'Sub title',
        ]);

        $this->add_control( 'image_id', [
            'label' => esc_html__( 'Upload background', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'default' => [
                'url' => \Elementor\Utils::get_placeholder_image_src(),
            ],
            'label_block'  => true,
        ]);

        $this->add_control( 'enable_icon', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable Icon?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);

        $this->add_control( 'icon', [
            'label' => esc_html__( 'Icon', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::ICONS,
            'default' => [
                'value' => 'rhicon rhi-gift',
                'library' => 'rhicons',
            ],            
            'condition'=> [ 'enable_icon' => 'yes' ],
        ]);        

        $this->add_control( 'height', [
            'type' => \Elementor\Controls_Manager::NUMBER,
            'label'       => esc_html__( 'Height, px', 'rehub-theme' ),
        ]);       

        $this->add_control( 'padding', [
            'type' => \Elementor\Controls_Manager::NUMBER,
            'label'       => esc_html__( 'Padding, px', 'rehub-theme' ),
            'default'     => '40',
        ]);

        $this->add_control( 'align', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Text Position', 'rehub-theme' ),
            'default'    => 'center',
            'options'     => [
                'left'   =>  esc_html__('Left', 'rehub-theme'),
                'right'   =>  esc_html__('Right', 'rehub-theme'),
                'center'   =>  esc_html__('Center', 'rehub-theme')
            ],
            'label_block'  => true,
        ]);

        $this->add_control( 'vertical', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Vertical align', 'rehub-theme' ),
            'default'    => 'middle',
            'options'     => [
                'middle'   =>  esc_html__('Middle', 'rehub-theme'),
                'top'   =>  esc_html__('Top', 'rehub-theme'),
                'bottom'   =>  esc_html__('Bottom', 'rehub-theme')
            ],
            'label_block'  => true,
        ]);

        $this->add_control( 'url', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Banner URL', 'rehub-theme' ),
            'label_block'  => true,
        ]);

        $this->add_control( 'targetself', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Open in the same window?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'default'     => 'yes',
        ]);
        $this->add_control( 'btn', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable button', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return'      => 'yes',
        ]);
        $this->add_control( 'btn_label', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Button Label', 'rehub-theme' ),
            'label_block'  => true,
            'default' => 'Buy this',
            'condition'=> [ 'btn' => 'yes' ]
        ]);
        $this->add_control( 'overlay', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable Overlay?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'default'     => '1',
            'return_value' => '1',
        ]);

        $this->end_controls_section();
    }
    protected function style_controls() {
        $this->start_controls_section( 'style_content', [
            'label' => esc_html__( 'Style', 'rehub-theme' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'maintitletypo',
                'label' => esc_html__( 'Title Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .wpsm-banner-wrapper h4',
            ]
        );   

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subtitletypo',
                'label' => esc_html__( 'SubTitle Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .wpsm-banner-wrapper h6',
            ]
        );                

        $this->add_control( 'bg', [
            'label' => esc_html__( 'Set background color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-banner-image' => 'background-color: {{VALUE}}',
            ],
            'default'     => '#555555',              
        ]);

        $this->add_control( 'bghover', [
            'label' => esc_html__( 'Set background color on hover', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpsm-banner-wrapper:hover .wpsm-banner-image' => 'background-color: {{VALUE}}!important',
            ],             
        ]);

        $this->add_control( 'color', [
            'label' => esc_html__( 'Icon and Hover border Color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-banner-text i:before' => 'color: {{VALUE}} !important',
                '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-banner-text:after' => 'border-color: {{VALUE}} !important',
                '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-banner-text:before' => 'border-color: {{VALUE}} !important',
            ],
            'default'     => '#ffffff',            
        ]);

        $this->add_control( 'colortext', [
            'label' => esc_html__( 'Title Color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpsm-banner-wrapper h4' => 'color: {{VALUE}} !important',
            ],
            'default'     => '#ffffff', 
        ]);

        $this->add_control( 'colorsubtext', [
            'label' => esc_html__( 'Subtitle Color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpsm-banner-wrapper h6' => 'color: {{VALUE}} !important',
            ],
            'default'     => '#ffffff', 
        ]); 
        $this->add_control(
            'bannerradius',
            [
                'label' => __( 'Border radius', 'rehub-theme' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .wpsm-banner-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

       $this->add_control(
            'hvbnrrhhr1',
            [
                'label' => __( 'Button control', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition'=> [ 'btn' => 'yes' ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'btnbg',
                'label' => esc_html__( 'Set background', 'rehub-theme' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-button',
                'condition'=> [ 'btn' => 'yes' ]
            ]
        );

        $this->add_control( 'btncolor', [
            'label' => esc_html__( 'Button text color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-button' => 'color: {{VALUE}} !important',
                'condition'=> [ 'btn' => 'yes' ]
            ], 
        ]); 

        $this->add_control(
            'btnpadding',
            [
                'label' => __( 'Button padding', 'rehub-theme' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition'=> [ 'btn' => 'yes' ]
            ]
        );
        $this->add_control(
            'btnmargin',
            [
                'label' => __( 'Button margin', 'rehub-theme' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition'=> [ 'btn' => 'yes' ]
            ]
        );
        $this->add_control(
            'tabborderradius',
            [
                'label' => __( 'Border radius', 'rehub-theme' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'btntypo',
                'label' => esc_html__( 'Button Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .wpsm-banner-wrapper .wpsm-button',
                'condition'=> [ 'btn' => 'yes' ]
            ]
        );        

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $settings['image_id'] = $settings['image_id']['id'];
        if(!empty($settings['icon']) && is_array($settings['icon'])){
            $settings['icon'] = $settings['icon']['value'];
        }
        echo wpsm_banner_shortcode( $settings );
    }

}

Plugin::instance()->widgets_manager->register( new WPSM_Hover_Banner_Widget );
