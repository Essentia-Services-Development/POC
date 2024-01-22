<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit('Restricted Access');
} // Exit if accessed directly

/**
 * Tax Archive Widget class.
 *
 *
 * @since 1.0.0
 */
class WPSM_Tax_Archive_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_tax_archive';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Taxonomy Archive', 'rehub-theme');
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-archive-posts';
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
        $this->start_controls_section( 'taxarchive_block_section', [
            'label' => esc_html__( 'General', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control( 'taxonomy', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Taxonomy', 'rehub-theme' ),
            'description' => esc_html__( 'Select taxonomy', 'rehub-theme' ),
            'label_block'  => true,
            'callback'  => 'wpsm_taxonomies_list'
        ]);

        $this->add_control( 'child_of', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Child of', 'rehub-theme' ),
            'description' => esc_html__( 'Set ID of parent category if you want to show only child Items', 'rehub-theme' ),
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'taxonomy',
                        'operator' => '!=',
                        'value'    => '',
                    ],
                ],
            ],
            'label_block'  => true,
            'callback'      => 'wpsm_taxonomy_terms_ids',
            'multiple'   => false,
            'linked_fields' => 'taxonomy'
        ]);

        $this->add_control( 'include', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Include', 'rehub-theme' ),
            'description' => esc_html__( 'Set Ids if you want to show only special taxonomies', 'rehub-theme' ),
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'taxonomy',
                        'operator' => '!=',
                        'value'    => '',
                    ],
                ],
            ],
            'multiple'   => true,
            'label_block'  => true,
            'callback'      => 'wpsm_taxonomy_terms_ids',
            'linked_fields' => 'taxonomy'
        ]);

        $this->add_control( 'type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type', 'rehub-theme' ),
            'description' => esc_html__( 'Logo works only for Brand, Affiliate Store and woocommerce Category taxonomy. You can add logo when you edit category. If you choose Post category archive, set "category" in Taxonomy field', 'rehub-theme' ),
            'default'     => 'storegrid',
            'options'     => [
                'compact'         => esc_html__( 'Compact small Blocks', 'rehub-theme' ),
                'compactbig'      => esc_html__( 'Compact big Blocks', 'rehub-theme' ),
                'logo'            => esc_html__( 'Logo', 'rehub-theme' ),
                'inlinelinks'     => esc_html__( 'Inline links', 'rehub-theme' ),
                'alpha'           => esc_html__( 'Alphabet', 'rehub-theme' ),
                'storegrid'       => esc_html__( 'Big grid with numbers on hover', 'rehub-theme' ),
                'woocategory'     => esc_html__( 'Woocommerce Category archive', 'rehub-theme' ),
                'postcategory'    => esc_html__( 'Post category archive', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'maintitletypo',
                'label' => esc_html__( 'Title Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .product-category h5',
            ]
        );        

        $this->add_control( 'showcount', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Show count?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'condition'  => [ 'type' => [ 'woocategory', 'postcategory' ] ],
        ]);  

        $this->add_control( 'leftimage', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Left side image?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'condition'  => [ 'type' => [ 'woocategory', 'postcategory' ] ],
        ]); 

        $this->add_control( 'originalimg', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Dsable image resizer?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'condition'  => [ 'type' => [ 'woocategory', 'postcategory' ] ],
        ]);                      

        $this->add_control( 'classcol', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Set columns', 'rehub-theme' ),
            'descriptioin'=> esc_html__( 'Choose this if you want to divide all list in Compact Blocks. This parameter is not working for Logo and Alphabet Type', 'rehub-theme' ),
            'default'     => 'col_wrap_fifth',
            'options'     => [
                'col_wrap_one'             => esc_html__( '1', 'rehub-theme' ),
                'col_wrap_two'             => esc_html__( '2', 'rehub-theme' ),
                'col_wrap_three'             => esc_html__( '3', 'rehub-theme' ),
                'col_wrap_fourth'             => esc_html__( '4', 'rehub-theme' ),
                'col_wrap_fifth'             => esc_html__( '5', 'rehub-theme' ),
                'col_wrap_six'             => esc_html__( '6', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'limit', [
            'type'        => \Elementor\Controls_Manager::NUMBER,
            'label'       => esc_html__( 'Limit (Number)', 'rehub-theme' ),
            'description' => esc_html__( 'Limit the maximum number of terms', 'rehub-theme' ),
        ]);

        $this->add_control( 'imageheight', [
            'type'        => \Elementor\Controls_Manager::NUMBER,
            'label'       => esc_html__( 'Image height', 'rehub-theme' ),
            'description' => esc_html__( 'use with Logo or Alphabet type. Default is 50', 'rehub-theme' ),
            'default'     => '50',
        ]);

        $this->add_control( 'classitem', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Custom Css class for item title', 'rehub-theme' ),
            'label_block'  => true,
        ]);

        $this->add_control( 'anchor_before', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Custom text before term name', 'rehub-theme' ),
            'label_block'  => true,
        ]);

        $this->add_control( 'anchor_after', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Custom text after term name', 'rehub-theme' ),
            'label_block'  => true,
        ]);

        $this->add_control( 'wrapclass', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Wrapper Class', 'rehub-theme' ),
            'description' => esc_html__( 'Only applied if Type is Woocommerce Category archive selected', 'rehub-theme' ),
            'default'    => 'no_padding_wrap',
            'label_block'  => true,
        ]);

        $this->add_control( 'rows', [
            'type'        => \Elementor\Controls_Manager::NUMBER,
            'label'       => esc_html__( 'Number of Rows to show?', 'rehub-theme' ),
            'default'    => '1',
        ]);

        $this->add_control( 'random', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Random order', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);
        $this->add_control( 'show_images', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Show Image ?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'default'     => '1',
        ]);        
        $this->add_control( 'hide_empty', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Hide Empty categories?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value' => '1',
            'default'     => '1',
        ]);

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( !empty( $settings['child_of'] ) ) {
            $settings['child_of'] = get_term_by( 'slug', $settings['child_of'], $settings['taxonomy'] )->term_id;
        }

        if ( !empty( $settings['include'] ) ) {
            $this->normalize_terms( $settings );
        }

        echo wpsm_tax_archive_shortcode( $settings );
    }

    public function normalize_terms( &$settings ) {
        $terms = [];
        foreach ( (array) $settings['include'] as $include ) {
            $terms[] = get_term_by( 'slug', $include, $settings['taxonomy'] )->term_id;
        }
        return $settings['include'] = implode( ',', $terms);
    }
}

Plugin::instance()->widgets_manager->register( new WPSM_Tax_Archive_Widget );
