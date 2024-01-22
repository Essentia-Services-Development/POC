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
class Widget_Wpsm_Recent_Posts_List extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_recent_posts_list';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Simple list', 'rehub-theme');
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
   public function get_icon() {
        return 'eicon-bullet-list';
    }    
    protected function general_fields() {
        parent::general_fields();

        $this->add_control( 'searchtitle', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Search by Title', 'rehub-theme' ),
            'description' => esc_html__('Set name CURRENTPAGE to show posts with similar title to current page', 'rehub-theme'),
            'label_block'  => true,
        ]);
        $this->add_control( 'aff_link', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'External url instead inner?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
    }

    protected function control_fields() {
        $this->add_control( 'columns', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Show as columns', 'rehub-theme' ),
            'options'     => [
                '1'             => esc_html__( '1', 'rehub-theme' ),
                '2'             => esc_html__( '2', 'rehub-theme' ),
                '3'             => esc_html__( '3', 'rehub-theme' ),
                '4'             => esc_html__( '4', 'rehub-theme' ),
                '5'             => esc_html__( '5', 'rehub-theme' ),
                '6'             => esc_html__( '6', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'centertext', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make center text?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);

        $this->add_control( 'image', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Add image?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
        $this->add_control( 'center', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make center image?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'condition' => array(
                'image' => '1',
            ),
            'return_value'      => '1',
        ]);
        $this->add_control( 'smoothborder', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Add smooth border?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
            'condition' => array(
                'image' => '1',
            ),
            'selectors' => [
                '{{WRAPPER}} .item-small-news figure img' => 'border-radius:6px',
                '{{WRAPPER}} .item-small-news figure' => 'margin-bottom:13px !important',
            ],
        ]);
        $this->add_control( 'imgshadow', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Add shadow to image?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
            'condition' => array(
                'image' => '1',
            ),
            'selectors' => [
                '{{WRAPPER}} .item-small-news figure img' => 'box-shadow:0 2px 2px #f3f3f3',
            ],
        ]);

        $this->add_control( 'fullsizeimage', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Grid size of image?', 'rehub-theme' ),
            'description' => esc_html__( 'Maximum quality but bigger size', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
            'condition' => array(
                'image' => '1',
            ),
        ]);
        $this->add_responsive_control(
            'imageheight',
            [
                'label' => esc_html__( 'Image height (default is 80px)', 'rehub-theme' ),
                'type' => Controls_Manager::NUMBER,
                'default' => '',
                'condition' => array(
                    'image' => '1',
                ),
                'selectors' => [
                    '{{WRAPPER}} .item-small-news figure' => 'height: {{VALUE}}px !important',
                    '{{WRAPPER}} .item-small-news figure img' => 'max-height: {{VALUE}}px !important',
                ],
            ]
        );
        $this->add_responsive_control(
            'imagewidth',
            [
                'label' => esc_html__( 'Image width (default is 80px)', 'rehub-theme' ),
                'type' => Controls_Manager::NUMBER,
                'default' => '',
                'condition' => array(
                    'image' => '1',
                ),
                'selectors' => [
                    '{{WRAPPER}} .item-small-news figure' => 'width: {{VALUE}}px !important;',
                    '{{WRAPPER}} .item-small-news figure img' => 'max-width: {{VALUE}}px !important',
                    '{{WRAPPER}} .item-small-news .width-80-calc' => 'width: calc(100% - {{VALUE}}px) !important',
                ],
            ]
        );

        $this->add_control( 'nometa', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable meta', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);

        $this->add_control( 'priceenable', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable Price', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]); 

        $this->add_control( 'compareenable', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable Compare button', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);  

        $this->add_control( 'hotenable', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable Hot counter', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);                      

        $this->add_control( 'excerpt', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable excerpt?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
        $this->add_control(
            'smartscrolllist',
            array(
                'label'        => esc_html__( 'Enable smart inline scroll', 'rehub-theme' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                'return_value' => '1',
            )
        );
        $this->add_responsive_control(
            'sscrollwidth',
            [
                'label' => esc_html__( 'Width of item', 'rehub-theme' ),
                'type' => Controls_Manager::NUMBER,
                'default' => '250',
                'condition' => array(
                    'smartscrolllist' => '1',
                ),
                'selectors' => [
                    '{{WRAPPER}} .wpsm_recent_posts_list .col_item' => 'min-width: {{VALUE}}px !important; width: {{VALUE}}px !important',
                ],
            ]
        );

        $this->add_control(
            'desheading',
            [
                'label' => __( 'Design', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'listtypography',
                'label' => esc_html__( 'Heading Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .item-small-news h3',
            ]
        );
        $this->add_control( 'headcolor', [
            'label' => esc_html__( 'Heading color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .item-small-news h5 a' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_control( 'headhovercolor', [
            'label' => esc_html__( 'Heading color on Hover', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .item-small-news:hover h5 a' => 'color: {{VALUE}};',
            ],
        ]);  
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'pricetypography',
                'label' => esc_html__( 'Price Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .item-small-news .simple_price_count',
                'condition' => array(
                    'priceenable' => '1',
                ),
            ]
        );
        $this->add_control( 'pricecolor', [
            'label' => esc_html__( 'Price color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .item-small-news .simple_price_count' => 'color: {{VALUE}};',
                '{{WRAPPER}} .item-small-news .simple_price_count del' => 'opacity:0.7;',
            ],
            'condition' => array(
                'priceenable' => '1',
            ),
        ]);
        $this->add_control( 'pricehovercolor', [
            'label' => esc_html__( 'Price color on Hover', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .item-small-news:hover  .simple_price_count' => 'color: {{VALUE}};',
            ],
            'condition' => array(
                'priceenable' => '1',
            ),
        ]);                       
        $this->add_control(
            'desheading5',
            [
                'label' => __( 'Border', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control( 'border', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Add border to list items', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'listborder',
                'label' => __( 'Border', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .item-small-news',
                'condition' => array(
                    'border' => '1',
                ),
            ]
        );
        $this->add_control(
            'list_border_radius',
            [
                'label' => __( 'Border Radius', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .item-small-news' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'listshadow',
                'label' => __( 'Box Shadow', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .item-small-news',
                'condition' => array(
                    'border' => '1',
                ),
            ]
        ); 
        $this->add_control(
            'desheading2',
            [
                'label' => __( 'Borders on Hover', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => array(
                    'border' => '1',
                ),
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'listborderhover',
                'label' => __( 'Border on Hover', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .item-small-news:hover',
                'condition' => array(
                    'border' => '1',
                ),
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'listshadowhover',
                'label' => __( 'Box Shadow on Hover', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .item-small-news:hover',
                'condition' => array(
                    'border' => '1',
                ),
            ]
        );
        $this->add_control(
            'desheading3',
            [
                'label' => __( 'Background', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control( 'bgcolor', [
            'label' => esc_html__( 'Background color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .item-small-news' => 'background-color: {{VALUE}};',
            ],
        ]); 
        $this->add_control( 'bgcolorhover', [
            'label' => esc_html__( 'Background color on Hover', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .item-small-news:hover' => 'background-color: {{VALUE}};',
            ],
        ]); 
        $this->add_control(
            'desheading4',
            [
                'label' => __( 'Spacing', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'list_padding',
            [
                'label' => __( 'Padding', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .item-small-news' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        ); 
        $this->add_control(
            'mborder',
            [
                'label' => esc_html__( 'Margin in bottom', 'rehub-theme' ),
                'type' => Controls_Manager::NUMBER,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsm_recent_posts_list .col_item' => 'margin-bottom: {{VALUE}}px !important',
                ],
            ]
        );     
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
        echo recent_posts_function( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Recent_Posts_List );
