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
class WPSM_TabsEvery_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_TabsEvery';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Tabs for everything', 'rehub-theme');
    }

    public function get_script_depends() {
        return ['rhtabs'];
    }
        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-tabs';
    }

    public function get_keywords() {
        return [ 'tabs', 'accordion', 'toggle' ];
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
            'label' => esc_html__( 'Content', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);        

        $repeater = new \Elementor\Repeater();

            $repeater->add_control( 'title', [
                'label' => esc_html__( 'Tab title', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Tab title',
                'label_block' => true,
            ]);
            $repeater->add_control(
                'content_template',
                [
                    'label'       => esc_html__( 'Elementor Templates', 'rehub-theme' ),
                    'type'        => Controls_Manager::SELECT,
                    'default'     => '0',
                    'options'     => rh_get_local_el_templates(),
                    'label_block' => 'true',
                ]
            );
            $repeater->add_control( 'tabajax', [
                'type'        => Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Ajax loading?', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'return_value' => '1',
            ]); 
            $repeater->add_control(
                'tabicon',
                [
                    'label' => __( 'Icon', 'rehub-theme' ),
                    'type' => \Elementor\Controls_Manager::ICONS,
                ]
            );
            $repeater->add_control(
                'tabiconmargin', [
                    'label' => __('Icon margins', 'rehub-theme'),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 1,
                            'max' => 50,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} {{CURRENT_ITEM}} .tabiconwrapper' => 'margin-left: {{SIZE}}{{UNIT}};margin-right: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => array(
                        'tabicon!' => '',
                    ),
                ]
            );
            $repeater->add_control( 'tabiconright', [
                'type'        => Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Right aligh for icon', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'return_value' => '1',
            ]);
            $repeater->add_control(
                'content',
                [
                    'label' => esc_html__( 'Additional Content', 'rehub-theme' ),
                    'type' => Controls_Manager::WYSIWYG,
                    'show_label' => false,
                ]
            );                        

        $this->add_control( 'TabsEvery', [
            'label'    => esc_html__( 'TabsEvery', 'rehub-theme' ),
            'type'     => \Elementor\Controls_Manager::REPEATER,
            'fields'   => $repeater->get_controls(),
            'title_field' => '{{{ title }}}',
        ]);

        $this->end_controls_section();

        $this->start_controls_section( 'tabstyle_section', [
            'label' => esc_html__( 'Style', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]); 
        $this->add_control( 'tabstack', [
            'type'        => Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable stack for tabs', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'selectors' => [
                '{{WRAPPER}} .tabs-menu li' => 'float:none',
            ],
        ]); 

        $this->add_control( 'tabstackblock', [
            'type'        => Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable Inline block', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'selectors' => [
                '{{WRAPPER}} .tabs-menu' => 'display:inline-block',
            ],
            'condition' => array(
                'tabstack' => '1',
            ),
        ]); 
        $this->add_responsive_control(
            'rhtabwidth', [
                'label' => __('Tab section width', 'rehub-theme'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => '100',
                    'unit' => '%',
                ],
                'size_units' => [ '%', 'px'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 10,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tabs-menu' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => array(
                    'tabstack' => '1',
                ),
            ]
        );
        $this->add_control( 'tabmobilescroll', [
            'type'        => Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable tab scroll on mobile?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'condition' => array(
                'tabstack!' => '1',
            ),
            'selectors' => [
                '{{WRAPPER}} .tabs-menu li' => 'float:none; display:inline-block',
            ],
        ]);

        $this->add_control(
            'tabpadding',
            [
                'label' => __( 'Padding', 'rehub-theme' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .tabs-menu li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'tabmargin',
            [
                'label' => __( 'Margin', 'rehub-theme' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .tabs-menu li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        ); 
        $this->add_control(
            'tabborderradius',
            [
                'label' => __( 'Border radius', 'rehub-theme' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .tabs-menu li' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        ); 

        $this->start_controls_tabs( 'tabs_a_style' );
        $this->start_controls_tab(
            'tab_reg_sec',
            [
                'label' => esc_html__( 'Regular tab', 'rehub-theme' ),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tabtypography',
                'label' => esc_html__( 'Title Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .tabs-menu li',
            ]
        );

        $this->add_control( 'tabcolor', [
            'label' => esc_html__( 'Set text color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tabs-menu li' => 'color: {{VALUE}}',
            ],
        ]);

       $this->add_control(
            'tabrhhr1',
            [
                'label' => __( 'Background control', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tabbgcolor',
                'label' => esc_html__( 'Set background', 'rehub-theme' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .tabs-menu li',
            ]
        ); 
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'tabborder',
                'label' => __( 'Border', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .tabs-menu li',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tabbox_shadow',
                'label' => __( 'Box Shadow', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .tabs-menu li',
            ]
        );  

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_act_sec',
            [
                'label' => esc_html__( 'Active tab', 'rehub-theme' ),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tabtypographyhover',
                'label' => esc_html__( 'Title Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .tabs-menu li.current, {{WRAPPER}} .tabs-menu li:hover',
            ]
        );

        $this->add_control( 'tabactivecolor', [
            'label' => esc_html__( 'Set text color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tabs-menu li.current' => 'color: {{VALUE}}',
                '{{WRAPPER}} .tabs-menu li:hover' => 'color: {{VALUE}}',
            ],
        ]);

        $this->add_control(
            'tabrhhr2',
            [
                'label' => __( 'Background control', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tabactivebgcolor',
                'label' => esc_html__( 'Set background', 'rehub-theme' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .tabs-menu li.current, {{WRAPPER}} .tabs-menu li:hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'tabborderhover',
                'label' => __( 'Border', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .tabs-menu li.current, {{WRAPPER}} .tabs-menu li:hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tabboxhover_shadow',
                'label' => __( 'Box Shadow', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .tabs-menu li.current, {{WRAPPER}} .tabs-menu li:hover',
            ]
        );  

        $this->end_controls_tab();
        $this->end_controls_tabs();
              

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        ?> 
        <?php if ( $settings['TabsEvery'] ) :?>
            <?php echo rh_generate_incss('tabs');?>
            <ul class="tabs-menu clearfix<?php echo (!empty($settings['tabmobilescroll'])) ? ' smart-scroll-desktop' : '';?>">
                <?php foreach (  $settings['TabsEvery'] as $index => $item ):?>
                    <?php 
                        $tab_content_setting_key = $this->get_repeater_setting_key( 'tab_item', 'TabsEvery', $index );
                        $this->add_render_attribute( $tab_content_setting_key, [
                            'data-tab' => intval($index + 1),
                            'class' => array(
                                'elementor-tab-title',
                                ($index == 0) ? 'current elementor-active' : '',
                                'elementor-repeater-item-' . $item['_id'],
                                (!empty($item['tabajax']) && !empty($item["content_template"])) ? 'rh-el-onclick load-block-'.$item["content_template"].'' : '',
                            ),
                        ] );
                    ?>
                    <li <?php echo ''.$this->get_render_attribute_string( $tab_content_setting_key ); ?>>
                        <?php if (empty($item['tabiconright'])):?>
                            <span class="tabiconwrapper"><?php \Elementor\Icons_Manager::render_icon( $item['tabicon'], [ 'aria-hidden' => 'true' ] ); ?></span>
                        <?php endif;?>                        
                        <?php echo esc_attr($item['title']);?>
                        <?php if (!empty($item['tabiconright'])):?>
                            <span class="tabiconwrapper"><?php \Elementor\Icons_Manager::render_icon( $item['tabicon'], [ 'aria-hidden' => 'true' ] ); ?></span>
                        <?php endif;?>
                    </li>
                <?php endforeach;?>
            </ul>
                <?php foreach (  $settings['TabsEvery'] as $index => $item ):?>
                    <?php if(!empty($item['content_template']) || !empty($item['content'])):?>
                    <div class="tabs-item<?php if ($index != 0):?> rhhidden<?php endif;?>">
                        <?php if(!empty($item['content_template'])):?>
                            <?php if(!empty($item['tabajax'])):?>
                                <div class="el-ajax-load-block el-ajax-load-block-<?php echo intval($item['content_template']);?>"></div>
                            <?php else:?>
                                <?php echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $item['content_template'] );?>
                            <?php endif;?> 
                        <?php endif;?>
                        <?php if(!empty($item['content'])):?> 
                            <div class="pt20 pb20"><?php echo rehub_kses($item['content']);?></div>
                        <?php endif;?> 
                    </div>
                    <?php endif;?>
                <?php endforeach;?>
        <?php endif;?>
        <?php
    }


}

Plugin::instance()->widgets_manager->register( new WPSM_TabsEvery_Widget );