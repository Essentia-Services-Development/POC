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
class Widget_Wpsm_Review_List extends WPSM_Content_Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_reviewlist';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Advanced Listing Builder', 'rehub-theme');
    }

    public function get_style_depends() {
        return [ 'rhtipsy' ];
    }

    public function get_script_depends() {
        return ['tipsy'];
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-favorite';
    }  

    public function get_categories() {
        return [ 'deal-helper' ];
    }    

    protected function get_sections() {
        return [
            'general'   => esc_html__('Data query', 'rehub-theme'),
            'data'      => esc_html__('Data Settings', 'rehub-theme'),
            'builder'      => esc_html__('List Builder', 'rehub-theme'),
            'filters'   => esc_html__('Filter Panel', 'rehub-theme')
        ];
    }

    protected function builder_fields() {

        $this->add_control( 'image', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable image?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'default' => '1',
        ]); 
        $this->add_control(
            'imageheight',
            [
                'label' => esc_html__( 'Custom image height', 'rehub-theme' ) . '',
                'type' =>  \Elementor\Controls_Manager::NUMBER,
                'min' => 126,
                'max' => 300,
                'step' => 1,
                'selectors' => [
                    '{{WRAPPER}} .top_rating_item figure' => 'height: {{VALUE}}px;width: {{VALUE}}px;', 
                    '{{WRAPPER}} .top_rating_item figure > a' => 'height: {{VALUE}}px;width: {{VALUE}}px;border:none',
                    '{{WRAPPER}} .top_rating_item figure > a img' => 'max-height: {{VALUE}}px;max-width: {{VALUE}}px;',
                    '{{WRAPPER}} .listitem_column.listbuild_image' => 'max-width: 1000px;'
                ],
                'condition' => [ 'image' => '1' ]
            ]
        ); 

        $this->add_control( 'review', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable Review score?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'default' => '1',
        ]);

        $this->add_control( 'button', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable Button?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'default' => '1',
        ]);

        $this->add_control( 'readmore', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable read more link?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'condition'=> [ 'button' => '1' ],
            'selectors' => [
                 '{{WRAPPER}} .read_full' => 'display:none',
            ],            
        ]);   

        $this->add_control( 'numberdisable', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable numbers?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'selectors' => [
                 '{{WRAPPER}} .top_rating_item .rank_count' => 'display:none',
            ],            
        ]);             

        $this->add_control( 'contentpos', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Show content', 'rehub-theme' ),
            'options'     => [
                'no' =>  esc_html__('No', 'rehub-theme'),
                'titlerow' =>  esc_html__('As title on separate row', 'rehub-theme'),                    
                'titleexc'  =>  esc_html__('As title and excerpt on column', 'rehub-theme'),
                ],
            'default' => 'titleexc',
            'label_block' => true,
        ]);

        $this->add_control(
            'contshortcode',
            [
                'label' => esc_html__('Shortcode Area Under content', 'rehub-theme'),
                'type' => Controls_Manager::TEXTAREA,
                'condition'=> [ 'contentpos' => 'titleexc' ],
            ]
        ); 

        $this->add_control( 'headingtag', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Heading Tag', 'rehub-theme' ),
            'options'     => [
                'h2' =>  'H2',
                'h3' =>  'H3',
                'h4' =>  'H4',
                'div' =>  'div',
                ],
            'default' => 'h2',
            'label_block' => true,
            'condition'=> [ 'contentpos' => ['titleexc', 'titlerow'] ],
        ]);         

        $this->add_control( 'togglelink', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Add toggle block link', 'rehub-theme' ),
            'options'     => [
                'no' =>  esc_html__('No', 'rehub-theme'),
                'title' =>  esc_html__('Near title', 'rehub-theme'),                    
                'image'  =>  esc_html__('Near image', 'rehub-theme'),
                'button'  =>  esc_html__('Near button', 'rehub-theme'),
                'disclaimer'  =>  esc_html__('Disclaimer', 'rehub-theme'),
                ],
            'label_block' => true,
        ]); 

        $this->add_control( 'togglecontent', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Content Of toggle', 'rehub-theme' ),
            'options'     => [
                'review' =>  esc_html__('Review score and criterias', 'rehub-theme'),
                'content' =>  esc_html__('Post content (can slow down page)', 'rehub-theme'),                    
                'field'  =>  esc_html__('Custom field value', 'rehub-theme'),
                ],
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'togglelink',
                        'operator' => '!=',
                        'value'    => 'no',
                    ],
                ],
            ],
            'label_block' => true,
        ]);
        $this->add_control( 'togglefield', [
            'label' => esc_html__( 'Field key', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition'=> [ 'togglecontent' => 'field' ],
        ]);    
        $this->add_control( 'disclaimer', [
            'label' => esc_html__( 'Disclaimer meta key', 'rehub-theme' ),
            'description' => esc_html__( 'Place here custom field key where you store disclaimer. If you use Post offer disclaimer, you can use key rehub_offer_disclaimer', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
        ]);  

        $this->add_control( 'afflink', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make all links as external?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',            
        ]);                                  

        $this->add_control( 'stacktablet', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Stack elements on tablet?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',            
        ]);    
        $this->add_control( 'stackmobile', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Stack meta elements on mobiles?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',            
        ]);     

        $this->add_control(
            'hr',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );                                      

        $section = new \Elementor\Repeater();

            $section->add_control( 'sectiontype', [
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default' => 'empty',
                'label'       => esc_html__( 'Type', 'rehub-theme' ),
                'options'     => [
                    'empty' =>  esc_html__('Empty', 'rehub-theme'),
                    'attribute' =>  esc_html__('Woocommerce Attribute value', 'rehub-theme'),
                    'swatch'  =>  esc_html__('Woocommerce attribute swatch', 'rehub-theme'),                    
                    'custom'  =>  esc_html__('Meta value', 'rehub-theme'),
                    'shortcode' => esc_html__('Shortcode', 'rehub-theme'),
                    ],
                'label_block' => true,
            ]);
            $section->add_control( 'field', [
                'label' => esc_html__( 'Field key', 'rehub-theme' ),
                'label_block'  => true,
                'type' => \Elementor\Controls_Manager::TEXT,
                'condition'=> [ 'sectiontype' => 'custom' ],
            ]);
            $section->add_control( 'shortcodefield', [
                'label' => esc_html__( 'Shortcode', 'rehub-theme' ),
                'label_block'  => true,
                'type' => \Elementor\Controls_Manager::TEXT,
                'condition'=> [ 'sectiontype' => 'shortcode' ],
            ]);            
            $section->add_control( 'attrfield', [
                'type'        => 'select2ajax',
                'label'       => esc_html__( 'Attribute name', 'rehub-theme' ),
                'options'     => [],
                'label_block'  => true,
                'multiple'     => false,
                'callback'    => 'rehub_wpsm_search_woo_attributes',
                'condition'  => [ 'sectiontype' => [ 'attribute', 'swatch' ] ],
            ]);             
            $section->add_control( 'unit', [
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label'       => esc_html__( 'Units', 'rehub-theme' ),
                'label_block'  => true,
                'default' => '',
            ]);
            $section->add_control( 'unitbefore', [
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Unit before value?', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'return_value' => '1',
            ]);            
            $section->add_control( 'posttext', [
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label'       => esc_html__( 'Text after value', 'rehub-theme' ),
                'label_block'  => true,
                'default' => '',
            ]);
            $section->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'valuetypo',
                    'label' => esc_html__( 'Typography for value', 'rehub-theme' ),
                    'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .listitem_custom_val',
                ]
            );
            $section->add_control( 'valuecolor', [
                'label' => esc_html__( 'Color for value', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'render_type' => 'none',
                'selectors' => [
                     '{{WRAPPER}} {{CURRENT_ITEM}} .listitem_custom_val' => 'color: {{VALUE}}',
                ],
            ]);                                                
            $section->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'texttypo',
                    'label' => esc_html__( 'Typography for text', 'rehub-theme' ),
                    'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .meta_posttext',                    
                ]
            ); 
            $section->add_control( 'textcolor', [
                'label' => esc_html__( 'Color for text', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                     '{{WRAPPER}} {{CURRENT_ITEM}} .meta_posttext' => 'color: {{VALUE}}',
                ],
            ]);         
            $section->add_control( 'tooltip', [
                'label' => esc_html__( 'Tooltip text', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::TEXT,                
            ]);                                                         
        $this->add_control( 'section', [
            'label'    => esc_html__( 'Meta Value section', 'rehub-theme' ),
            'type'     => \Elementor\Controls_Manager::REPEATER,
            'fields'   => $section->get_controls(),
            'title_field' => '{{{ sectiontype }}}',
        ]);
        $this->add_control( 'bgitemcolor', [
            'label' => esc_html__( 'Background for meta items', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .listitem_meta_index' => 'background-color: {{VALUE}}',
            ],
        ]);
        $this->add_control(
            'itemheight',
            [
                'label' => esc_html__( 'Equal height for meta items', 'rehub-theme' ) . '',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 30,
                'max' => 300,
                'step' => 1,
                'selectors' => [
                    '{{WRAPPER}} .listitem_meta_index' => 'height: {{VALUE}}px;', 
                ],
            ]
        );    
        $this->add_control(
            'itemmargin',
            [
                'label' => esc_html__( 'Margin between meta items', 'rehub-theme' ) . '',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .listitem_meta_index' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};', 
                ],
            ]
        );    
        $this->add_control(
            'itembradius',
            [
                'label' => esc_html__( 'Border radius', 'rehub-theme' ) . '',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'step' => 1,
                'max' => 100,
                'selectors' => [
                    '{{WRAPPER}} .listitem_meta_index' => 'margin: 0 {{VALUE}}px;', 
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
        $settings['listargs'] = array();
        foreach ($settings['section'] as $index => $item) {
            $settings['section'][$index] = array_filter($item);
            $argssettings = array('tooltip', 'posttext', 'unitbefore', 'unit', 'attrfield', 'field', 'shortcodefield', 'sectiontype', '_id');
            $listargsvalues = wp_array_slice_assoc( $settings['section'][$index], $argssettings );
            array_walk_recursive($listargsvalues, function(&$item) {
                $item = htmlspecialchars($item, ENT_QUOTES);
            });
            $settings['listargs']['section'][$index] = $listargsvalues;
            
        }       
        $argstoadd = array('image', 'imageheight', 'button', 'review', 'contentpos', 'headingtag', 'stacktablet', 'togglefield', 'disclaimer', 'togglecontent', 'togglelink', 'afflink', 'stackmobile');
        foreach ($argstoadd as $add) {
            if(!empty($settings[$add])){
                $settings['listargs'][$add] = $settings[$add];
            }
        }
        if(!empty($settings['contshortcode'])){
            $rhshortcontent = str_replace('"', '\'', $settings['contshortcode']);
            $settings['listargs']['contshortcode'] = urlencode($rhshortcontent);
        }
        if(!empty($settings['listargs']['section'])){
            foreach ($settings['listargs']['section'] as $index => $item) {
                if ($settings['listargs']['section'][$index]['sectiontype'] == 'shortcode' && !empty($settings['listargs']['section'][$index]['shortcodefield'])){
                    $sectionshortcode = str_replace('"', '\'', $settings['listargs']['section'][$index]['shortcodefield']);
                    $settings['listargs']['section'][$index]['shortcodefield'] = urlencode($sectionshortcode);
                }
            }
        }
        $settings['listargs'] = json_encode( $settings['listargs']);
        $this->normalize_arrays( $settings );

        echo wpsm_list_constructor( $settings );
    }
}

Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Review_List );
