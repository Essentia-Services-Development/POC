<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Restricted Acces' );
}

abstract class WPSM_Widget_Base extends Widget_Base {
    /**
     * Constructor
     * @param array      $data
     * @param array|null $args
     */
    public function __construct( array $data = [], array $args = null ) {
        parent::__construct( $data, $args );

        // AJAX callbacks
        add_action( 'wp_ajax_get_wc_products_cat_list', [ &$this, 'get_wc_products_cat_list'] );
        add_action( 'wp_ajax_get_wc_products_tag_list', [ &$this, 'get_wc_products_tag_list'] );
        add_action( 'wp_ajax_get_wc_products_posts_list', [ &$this, 'get_products_title_list'] );
    }

    public function get_style_depends() {
        return [ 'rhfilterpanel' ];
    }

    /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-woocommerce';
    }

    /**
     * category name in which this widget will be shown
     * @since 1.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'rehub-category' ];
    }

    protected function register_controls() {
        $sections = $this->get_sections(); //General sections for modules
        foreach( $sections as $control => $label ) {
            $fields_method = $control . '_fields';

            if ( ! method_exists( $this, $fields_method ) ) {
                continue;
            }

            $this->start_controls_section( $fields_method, [
                'label' => $label,
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]);

            call_user_func([ $this, $fields_method ]);

            $this->end_controls_section();
        }

        $this->style_controls(); //additional for style
    }

    protected function get_sections() {
        return [
            'general'   => esc_html__('Data query', 'rehub-theme'),
            'data'      => esc_html__('Data Settings', 'rehub-theme'),
            'taxonomy'  => esc_html__('Additional Taxonomy Query', 'rehub-theme'),
            'control'   => esc_html__('Design Control', 'rehub-theme'),
            'filters'   => esc_html__('Filter Panel', 'rehub-theme'),
            'attribute'   => esc_html__('Custom Attribute Panel', 'rehub-theme'),
        ];
    }

    protected function style_controls() {
        $this->start_controls_section( 'style_content', [
            'label' => esc_html__( 'Style', 'rehub-theme' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->style_control_fields();
        $this->end_controls_section();
    }

    protected function style_control_fields() {
        $this->add_control( 'headingcolor', [
            'label' => esc_html__( 'Headings color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} h3 a' => 'color: {{VALUE}}',
            ],
        ]);
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'headingtypography',
                'label' => esc_html__( 'Heading Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .woocommerce .products h3',
            ]
        );         
        $this->add_control( 'pricecolor', [
            'label' => esc_html__( 'Price color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .woocommerce .products .price' => 'color: {{VALUE}} !important',
            ],
        ]); 
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'pricetypography',
                'label' => esc_html__( 'Price Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .woocommerce .products .price',
            ]
        );         
        $this->add_control( 'saletagcolor', [
            'label' => esc_html__( 'Sale tag color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .woocommerce .onsale' => 'background-color: {{VALUE}}',
            ],
        ]); 
        $this->add_control( 'cartbtncolor', [
            'label' => esc_html__( 'Button color', 'rehub-theme' ),
            'description' => 'For global settings, you can add button color from Customizer - Theme options - Appearance',
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .woocommerce a.woo_loop_btn' => 'background-color: {{VALUE}} !important',
            ],
        ]);  
        $this->add_control( 'cartbtncolorhover', [
            'label' => esc_html__( 'Button color hover', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .woocommerce a.woo_loop_btn:hover' => 'background-color: {{VALUE}} !important',
            ],
        ]); 
        $this->add_control( 'darkbgcolor', [
            'label' => esc_html__( 'Color for dark background under item', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .rh-cartbox' => 'background: {{VALUE}} !important',
            ],
            'condition'  => [ 'darkstyle' => 'yes' ],
        ]);                              
    }    

    protected function general_fields() {
        $this->add_control( 'data_source', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Data source', 'rehub-theme' ),
            'default'    => 'custom',
            'options'     => [
                'custom'   =>  esc_html__('Category or tag', 'rehub-theme'),
                'ids'   =>  esc_html__('Manual Select and Order', 'rehub-theme'),
                'auto'  =>  esc_html__('Auto detect archive data', 'rehub-theme'),
            ],
            'label_block'  => true,
        ]);

        $this->add_control( 'cat', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Category', 'rehub-theme' ),
            'description' => esc_html__( 'Enter names of categories', 'rehub-theme' ),
            'condition' => [
                'data_source' => 'custom',
            ],
            'label_block'  => true,
            'multiple'     => true,
            'callback'  => 'get_wc_products_cat_list'
        ]);

        $this->add_control( 'tag', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Tag', 'rehub-theme' ),
            'description' => esc_html__( 'Enter names of Tags', 'rehub-theme' ),
            'condition' => [
                'data_source' => 'custom',
            ],
            'options'     => [],
            'label_block'  => true,
            'multiple'  => true,
            'callback'  => 'get_wc_products_tag_list'
        ]);

        $this->add_control( 'ids', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Product names', 'rehub-theme' ),
            'description' => esc_html__( 'Enter the Name of Products', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'ids' ],
            'options'     => [],
            'label_block'  => true,
            'multiple'     => true,
            'callback'    => 'get_wc_products_posts_list'
        ]);

        $this->add_control( 'type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type of product', 'rehub-theme' ),
            'description' => esc_html__( 'Recent viewed products work only if you have Recent Product Widget somewhere on the site', 'rehub-theme' ),
            'condition' => [
                'data_source' => 'custom',
            ],
            'options'     => [
                'all'     => esc_html__( 'All type of products', 'rehub-theme' ),
                'recent'     => esc_html__( 'Recent viewed products', 'rehub-theme' ),
                'featured'   => esc_html__( 'Featured products', 'rehub-theme' ),
                'sale'       => esc_html__( 'Products with discount', 'rehub-theme' ),
                'saled'  => esc_html__( 'Products which have sales', 'rehub-theme' ),
                'recentviews'=> esc_html__( 'Recent viewed products', 'rehub-theme' ),
            ],
            'label_block'  => true,
            'multiple'     => true,
        ]);

        $this->add_control( 'price_range', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Price range', 'rehub-theme' ),
            'condition' => [
                'data_source' => 'custom',
            ],
            'description' => esc_html__( 'Set price range to show. Works only for posts with Main Post offer section. Example of using: 0-100. Will show products with price under 100', 'rehub-theme' ),
            'label_block'  => true,
        ]);         

        $this->add_control( 'user_id', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'User ID', 'rehub-theme' ),
            'description' => esc_html__( 'Add user ID to show only his posts', 'rehub-theme' ),
            'label_block'  => true,
            'callback'  => 'rehub_users_id_list'
        ]);               

        /*$this->add_control( 'show_coupons_only', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Deal filter', 'rehub-theme' ),
            'description' => esc_html__( 'Choose deal type if you use Posts as offers', 'rehub-theme' ),
            'options'     => [
                'all'=> esc_html__( 'Show all', 'rehub-theme' ),
                '1'  => esc_html__( 'Show discounts (not expired)', 'rehub-theme' ),
                '2'  => esc_html__( 'Show all except expired', 'rehub-theme' ),
                '3'  => esc_html__( 'Only expired offers (which have expired date)', 'rehub-theme' ),
                '4'  => esc_html__( 'Only coupons (not expired)', 'rehub-theme' ),
                '5'  => esc_html__( 'Only offers, excluding coupons (not expired)', 'rehub-theme'),
            ],
            'label_block'  => true,
        ]);*/
    }

    protected function data_fields() {

        $this->add_control( 'orderby', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Order by', 'rehub-theme' ),
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'data_source',
                        'operator' => '!=',
                        'value'    => 'ids',
                    ],
                ],
            ],
            'options'     => [
                'date'          => esc_html__( 'Date', 'rehub-theme' ),
                'ID'            => esc_html__( 'Order by post ID', 'rehub-theme' ),
                'title'         => esc_html__( 'Title', 'rehub-theme' ),
                'modified'      => esc_html__( 'Last modified date', 'rehub-theme' ),
                'comment_count' => esc_html__( 'Number of comments', 'rehub-theme' ),
                'meta_value'    => esc_html__( 'Meta value', 'rehub-theme'),
                'meta_value_num'=> esc_html__( 'Meta value number', 'rehub-theme'),
                'expirationdate'=> esc_html__( 'Expiration date', 'rehub-theme'),
                'price'         => esc_html__( 'Price', 'rehub-theme'),
                'sales'         => esc_html__( 'Sales', 'rehub-theme'),
                'rand'          => esc_html__( 'Random order', 'rehub-theme'),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'meta_key', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Meta key', 'rehub-theme' ),
            'description' => esc_html__( 'Input meta key for ordering.', 'rehub-theme' ),
            'condition'  => [ 'orderby' => [ 'meta_value', 'meta_value_num' ] ],
            'label_block'  => true,
        ]);

        $this->add_control( 'order', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Sorting', 'rehub-theme' ),
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'data_source',
                        'operator' => '!=',
                        'value'    => 'ids',
                    ],
                ],
            ],
            'options'     => [
                'DESC' => esc_html__( 'Descending', 'rehub-theme' ),
                'ASC'  => esc_html__( 'Ascending', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'show', [
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'label'       => esc_html__( 'Fetch Count', 'rehub-theme' ),
            'description' => esc_html__('Number of items to display', 'rehub-theme'),
            'default'     => '12',
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'data_source',
                        'operator' => '!=',
                        'value'    => 'ids',
                    ],
                ],
            ],
            'min'     => 1,
            'max'     => 200,
            'step'    => 1,            
        ]);

        $this->add_control( 'offset', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Offset', 'rehub-theme' ),
            'description' => esc_html__('Number of products to offset', 'rehub-theme'),
            'label_block' => true,
        ]);

        $this->add_control( 'show_date', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Show by date', 'rehub-theme' ),
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'data_source',
                        'operator' => '!=',
                        'value'    => 'ids',
                    ],
                ],
            ],
            'options'     => [
                'all'   => esc_html__( 'All', 'rehub-theme' ),
                'day'   => esc_html__( 'Published last 24 hours', 'rehub-theme' ),
                'week'  => esc_html__( 'Published last 7 days', 'rehub-theme' ),
                'month' => esc_html__( 'Published last month', 'rehub-theme' ),
                'year'  => esc_html__( 'Published last year', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'enable_pagination', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Pagination type', 'rehub-theme' ),
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'data_source',
                        'operator' => '!=',
                        'value'    => 'ids',
                    ],
                ],
            ],
            'options'     => [
                '0' => esc_html__( 'No pagination', 'rehub-theme' ),
                '1' => esc_html__( 'Simple pagination', 'rehub-theme' ),
                '2' => esc_html__( 'Infinite scroll', 'rehub-theme' ),
                '3' => esc_html__( 'New item will be added by click', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);
    }

    protected function taxonomy_fields() {
        $this->add_control( 'tax_name', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Taxonomy slug', 'rehub-theme' ),
            'description' => esc_html__( 'Enter slug of your taxonomy', 'rehub-theme' ),
            'label_block'  => true,
            'callback'  => 'wpsm_taxonomies_list'
        ]);

        $this->add_control( 'tax_slug', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Taxonomy term slug', 'rehub-theme' ),
            'description' => esc_html__( 'Enter slug of your taxonomy term if you want to show only posts from certain taxonomy term. Example, for store taxonomy - amazon, for color - black', 'rehub-theme' ),
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'tax_name',
                        'operator' => '!=',
                        'value'    => '',
                    ],
                ],
            ],
            'label_block'   => true,
            'callback'      => 'wpsm_taxonomy_terms',
            'linked_fields' => 'tax_name'
        ]);

        $this->add_control( 'tax_slug_exclude', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Taxonomy term slug exclude', 'rehub-theme' ),
            'description' => esc_html__( 'Enter slug of your taxonomy term to exclude', 'rehub-theme' ),
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'tax_name',
                        'operator' => '!=',
                        'value'    => '',
                    ],
                ],
            ],
            'label_block'  => true,
            'callback'      => 'wpsm_taxonomy_terms',
            'linked_fields' => 'tax_name'
        ]);
    }

    protected function attribute_fields() {

        $repeater = new \Elementor\Repeater();

            $repeater->add_control( 'attrkey', [
                'type'        => 'select2ajax',
                'label'       => esc_html__( 'Attribute name', 'rehub-theme' ),
                'options'     => [],
                'label_block'  => true,
                'multiple'     => false,
                'callback'    => 'rehub_wpsm_search_woo_attributes',
            ]); 
            $repeater->add_control( 'attrtype', [
                'type'        => \Elementor\Controls_Manager::SELECT,
                'label'       => esc_html__( 'Type', 'rehub-theme' ),
                'default'     => 'attribute',
                'options'     => [
                    'attribute'        =>  esc_html__('Woocommerce Attribute', 'rehub-theme'),
                    'swatch'        =>  esc_html__('Woocommerce attribute swatch', 'rehub-theme'),
                    ],
                'label_block' => true,
            ]);            
            $repeater->add_control( 'attrlabel', [
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label'       => esc_html__( 'Label', 'rehub-theme' ),
                'label_block'  => true,
                'default' => '',
            ]);
            $repeater->add_control( 'icon', [
                'label' => esc_html__( 'Icon', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::ICONS,            
            ]);             
            $repeater->add_control( 'attrposttext', [
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label'       => esc_html__( 'Text after value', 'rehub-theme' ),
                'label_block'  => true,
                'default' => '',
            ]);
            $repeater->add_control( 'attrshowempty', [
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Show Empty', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'description' => 'Show "-" if value is empty',
                'return_value'=> '1',
                'default'     => '',
            ]); 
                       

        $this->add_control( 'attrpanel', [
            'label'    => esc_html__( 'Attribute panel', 'rehub-theme' ),
            'type'     => \Elementor\Controls_Manager::REPEATER,
            'fields'   => $repeater->get_controls(),
            'title_field' => '{{{ attrkey }}}',
        ]);
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'attrflbl',
                'label' => esc_html__( 'Typography for label', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .woo_code_zone_loop .meta_v_label',
            ]
        ); 
        $this->add_control( 'attrlblclr', [
            'label' => esc_html__( 'Color for label and icon', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .woo_code_zone_loop .meta_v_label' => 'color: {{VALUE}}',
                 '{{WRAPPER}} .woo_code_zone_loop .meta_icon_label' => 'color: {{VALUE}}',
            ],
        ]);         
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'attrfntvl',
                'label' => esc_html__( 'Typography for value', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} .woo_code_zone_loop',
            ]
        );
        $this->add_control( 'attrvalclr', [
            'label' => esc_html__( 'Color for text', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .woo_code_zone_loop' => 'color: {{VALUE}}',
            ],
        ]); 
        $this->add_control( 'attrbrdadd', [
            'label' => esc_html__( 'Color for border above', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .woo_code_zone_loop' => 'border-top: 1px solid {{VALUE}}; margin-top:15px; padding-top:10px',
            ],
        ]);                                 
    }


    protected function control_fields() { //Default for grid
        $this->add_control( 'columns', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Set columns', 'rehub-theme' ),
            'default'     => '4_col',
            'options'     => [
                '3_col'             => esc_html__( '3 Columns', 'rehub-theme' ),
                '4_col'             => esc_html__( '4 Columns', 'rehub-theme' ),
                '5_col'             => esc_html__( '5 Columns', 'rehub-theme' ),
                '6_col'             => esc_html__( '6 Columns', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'gridtype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Style of design', 'rehub-theme' ),
            'options'     => [
                'regular'           => esc_html__( 'Regular', 'rehub-theme' ),
                'compact'           => esc_html__( 'Compact', 'rehub-theme' ),
                'review'            => esc_html__( 'Directory', 'rehub-theme' ),
                'digital'            => esc_html__( 'Digital', 'rehub-theme' ),
                'dealwhite'            => esc_html__( 'Deal Grid', 'rehub-theme' ),
                'gridmart' => esc_html__('Market Grid', 'rehub-framework'),
                'dealdark'            => esc_html__( 'Deal Grid Dark', 'rehub-theme' ),
                'image'            => esc_html__( 'Images', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'darkstyle', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable dark style?', 'rehub-theme' ),
            'condition'=> [ 'gridtype' => 'digital' ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'selectors' => [
                '{{WRAPPER}} .products .product a' => 'color:#fff',
                '{{WRAPPER}} .aff_tag' => 'color:#adadad',
                '{{WRAPPER}} .products .button_action' => 'background:none',
                '{{WRAPPER}} .rh-cartbox' => 'background: #111',
                '{{WRAPPER}} .lightgreybg' => 'background: #f4f2f32e',
                '{{WRAPPER}} .rehub-main-color .price' => 'color: #f1f1f1',
           ],            
        ]);

        $this->add_control( 'woolinktype', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Show link from title and image on', 'rehub-theme' ),
            'options'     => [
                'product'           => esc_html__( 'Product page', 'rehub-theme' ),
                'aff'           => esc_html__( 'Affiliate link', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'custom_col', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Custom image size?', 'rehub-theme' ),
            'description' => esc_html__( 'You can set crop ratio in Customizer - Woocommerce or enable this option to set custom image size', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);

        $this->add_control( 'custom_img_width', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Width of image in px', 'rehub-theme' ),
            'condition'=> [ 'custom_col' => 'yes' ],
        ]);

        $this->add_control( 'custom_img_height', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Height of image in px', 'rehub-theme' ),
            'condition'=> [ 'custom_col' => 'yes' ],
        ]);

        $this->add_control( 'custom_figure_height', [
            'type'        => \Elementor\Controls_Manager::NUMBER,
            'label'       => esc_html__( 'Max Height of image block in px', 'rehub-theme' ),
            'min'     => 80,
            'max'     => 500,  
            'condition'=> [ 'custom_col' => 'yes' ],          
            'selectors' => [
                 '{{WRAPPER}} figure img, {{WRAPPER}} figure.eq_figure img' => 'height: {{VALUE}}px'
            ],            
        ]);        

        $this->add_control( 'soldout', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Add fake sold counter', 'rehub-theme' ),
            'label_on'    => esc_html__( 'Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
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
                'default' => '',
                'condition' => array(
                    'smartscrolllist' => '1',
                ),
                'selectors' => [
                    '{{WRAPPER}} .product.col_item' => 'min-width: {{VALUE}}px !important; width: {{VALUE}}px !important',
                ],
            ]
        );
    }

    protected function filters_fields() {
        $this->add_control( 'filterpanelenable', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable panel?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);

        $this->add_control( 'filterheading', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Title', 'rehub-theme' ),
            'label_block'  => true,
            'condition'=> [ 'filterpanelenable' => 'yes' ],
        ]); 

        $repeater = new \Elementor\Repeater();

            $repeater->add_control( 'filtertitle', [
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label'       => esc_html__( 'Label', 'rehub-theme' ),
                'description' => esc_html__('Enter title for filter button', 'rehub-theme'),
                'default'     => esc_html__( 'Show all', 'rehub-theme' ),
                'label_block'  => true,
            ]);

            $repeater->add_control( 'filtertype', [
                'type'        => \Elementor\Controls_Manager::SELECT,
                'label'       => esc_html__( 'Type of Filter', 'rehub-theme' ),
                'description' => esc_html__('Find some important', 'rehub-theme').'<a href="http://rehubdocs.wpsoul.com/docs/rehub-theme/list-of-important-meta-fields/" target="_blank"> '.esc_html__('meta keys', 'rehub-theme').'</a>', 
                'default'     => esc_html__( 'all', 'rehub-theme' ),
                'options'     => [
                    'all'           => esc_html__( 'Show all Posts', 'rehub-theme' ),
                    'comment'       => esc_html__( 'Sort by Comments Count', 'rehub-theme' ),
                    'meta'          => esc_html__( 'Sort by Meta Field', 'rehub-theme' ),
                    'expirationdate'=> esc_html__( 'Sort by Expiration Date', 'rehub-theme' ),
                    'pricerange'    => esc_html__( 'Sort by Price Range', 'rehub-theme' ),
                    'hot'           => esc_html__( 'Show hottest sorted by date', 'rehub-theme' ),
                    'tax'           => esc_html__( 'Sort by Taxonomy', 'rehub-theme' ),
                    'coupons'       => esc_html__( 'Show only Coupons', 'rehub-theme' ),
                ],
                'label_block'  => true,
            ]);

            $repeater->add_control( 'filtermetakey', [
                'type'      => \Elementor\Controls_Manager::TEXT,
                'label'     => esc_html__('Type key for Meta', 'rehub-theme'),
                'conditions'  => [
                    'terms'   => [
                        [
                            'name'     => 'filtertype',
                            'operator' => '=',
                            'value'    => 'meta',
                        ],
                    ],
                ],
                'label_block' => true,
            ]);

            $repeater->add_control( 'filterpricerange', [
                'type'      => \Elementor\Controls_Manager::TEXT,
                'label'     => esc_html__('Price Range', 'rehub-theme'),
                'description'=> esc_html__('Set price range to show. Works only for posts with Main Post offer section. Example of using: 0-100. Will show products with price under 100', 'rehub-theme' ),
                'condition' => [ 'filtertype' => 'pricerange' ],
                'label_block'=> true,
            ]);

            $repeater->add_control( 'filterorderby', [
                'type'        => \Elementor\Controls_Manager::SELECT,
                'label'       => esc_html__( 'Order By', 'rehub-theme' ),
                'options'     => [
                    'date'          => esc_html__( 'Date', 'rehub-theme' ),
                    'ID'            => esc_html__( 'Order by Post ID', 'rehub-theme' ),
                    'title'         => esc_html__( 'Title', 'rehub-theme' ),
                    'modified'      => esc_html__( 'Last Modified Date', 'rehub-theme' ),
                    'comment_count' => esc_html__( 'Number of Comments', 'rehub-theme' ),
                    'view'          => esc_html__( 'Views', 'rehub-theme' ),
                    'thumb'         => esc_html__( 'Thumb/Hot Counter', 'rehub-theme' ),
                    'price'         => esc_html__( 'Price', 'rehub-theme' ),
                    'discount'      => esc_html__( 'Discount', 'rehub-theme' ),
                    'rand'          => esc_html__( 'Random Order', 'rehub-theme' ),
                ],
                'label_block'  => true,
            ]);

            $repeater->add_control( 'filtertaxkey', [
                'type'      => \Elementor\Controls_Manager::TEXT,
                'label'     => esc_html__('Taxonomy slug', 'rehub-theme'),
                'description'=> esc_html__('Enter slug of your taxonomy. Examples: if you want to use woocommerce product category - use product_cat, for woocommerce tags - product_tag', 'rehub-theme' ),
                'condition' => [ 'filtertype' => 'tax' ],
                'label_block'=> true,
            ]);

            $repeater->add_control( 'filtertaxtermslug', [
                'type'      => \Elementor\Controls_Manager::TEXT,
                'label'     => esc_html__('Taxonomy term slug', 'rehub-theme'),
                'description'=> esc_html__('Enter term slug of your taxonomy if you want to show only posts from this taxonomy term', 'rehub-theme' ),
                'condition' => [ 'filtertype' => 'tax' ],
                'label_block' => true,
            ]);

            $repeater->add_control( 'filtertaxcondition', [
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label'       => esc_html__( 'Use filter taxonomy within general taxonomy option?', 'rehub-theme' ),
                'label_on'    => esc_html__('Yes', 'rehub-theme'),
                'label_off'   => esc_html__('No', 'rehub-theme'),
                'condition' => [ 'filtertype' => 'tax' ],
                'label_block' => true,
            ]);

            $repeater->add_control( 'filterorder', [
                'type'        => \Elementor\Controls_Manager::SELECT,
                'label'       => esc_html__( 'Sorting', 'rehub-theme' ),
                'description' => esc_html__('Select Sorting Order', 'rehub-theme'),
                'default'     => esc_html__( 'DESC', 'rehub-theme' ),
                'options'     => [
                    'DESC'      => esc_html__( 'Descending', 'rehub-theme' ),
                    'ASC'       => esc_html__( 'Ascending', 'rehub-theme' ),
                ],
                'label_block'  => true,
            ]);

            $repeater->add_control( 'filterdate', [
                'type'        => \Elementor\Controls_Manager::SELECT,
                'label'       => esc_html__( 'Filter by date of publishing', 'rehub-theme' ),
                'description' => esc_html__('Don\'t use more than 4-5 filters!!!!! Settings for first tab must be the same as main post settings of block', 'rehub-theme'),
                'default'     => esc_html__( 'all', 'rehub-theme' ),
                'options'     => [
                    'all'           => esc_html__( 'All', 'rehub-theme' ),
                    'day'       => esc_html__( 'Published last 24 hours', 'rehub-theme' ),
                    'week'          => esc_html__( 'Published last 7 days', 'rehub-theme' ),
                    'month'=> esc_html__( 'Published last month', 'rehub-theme' ),
                    'year'  => esc_html__( 'Published last year', 'rehub-theme' ),
                ],
                'label_block'  => true,
            ]);

        $this->add_control( 'filterpanel', [
            'label'    => esc_html__( 'Filter panel', 'rehub-theme' ),
            'type'     => \Elementor\Controls_Manager::REPEATER,
            'condition'=> [ 'filterpanelenable' => 'yes' ],
            'fields'   => $repeater->get_controls(),
            'title_field' => '{{{ filtertitle }}}',
        ]);

        $this->add_control( 'taxdrop', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Taxonomy slug', 'rehub-theme' ),
            'description' => esc_html__( 'Choose taxonomy to enable category select filter', 'rehub-theme' ),
            'label_block'  => true,
            'callback'  => 'wpsm_taxonomies_list',
            'condition'=> [ 'filterpanelenable' => 'yes' ],
        ]);               

        $this->add_control( 'taxdropids', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Taxonomy ids', 'rehub-theme' ),
            'description' => esc_html__('Type here ids of taxonomy separated by comma  which you need to show. Leave empty to show all', 'rehub-theme'),
            'label_block'  => true,
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'taxdrop',
                        'operator' => '!=',
                        'value'    => '',
                    ],
                    [
                        'name'     => 'filterpanelenable',
                        'operator' => '=',
                        'value'    => 'yes',
                    ],
                ],
            ],
        ]);

        $this->add_control( 'taxdroplabel', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Taxonomy dropdown label', 'rehub-theme' ),
            'description' => esc_html__('Type here label for dropdown', 'rehub-theme'),
            'label_block'  => true,
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'taxdrop',
                        'operator' => '!=',
                        'value'    => '',
                    ],
                    [
                        'name'     => 'filterpanelenable',
                        'operator' => '=',
                        'value'    => 'yes',
                    ],
                ],
            ],
        ]);        

        $this->add_control( 'filterheadingcolor', [
            'label' => esc_html__( 'Active tab text color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} span.active.re_filtersort_btn' => 'color: {{VALUE}}',
            ],
            'condition'=> [ 'filterpanelenable' => 'yes' ],
        ]);  

        $this->add_control( 'filterheadingcolorbg', [
            'label' => esc_html__( 'Active tab background', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} span.active.re_filtersort_btn' => 'background-color: {{VALUE}}',
            ],
            'condition'=> [ 'filterpanelenable' => 'yes' ],
        ]);

        $this->add_control( 'filterpanelbg', [
            'label' => esc_html__( 'Panel background', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                 '{{WRAPPER}} .re_filter_panel' => 'background-color: {{VALUE}}; box-shadow:none',
                 '{{WRAPPER}} .re_filter_heading' => 'padding-left: 15px',
                 '.rtl {{WRAPPER}} .re_filter_heading' => 'padding-right: 15px',
            ],
            'condition'=> [ 'filterpanelenable' => 'yes' ],
        ]);                       
    }

    public function get_products_title_list() {
        global $wpdb;

        $query = [
            "select" => "SELECT SQL_CALC_FOUND_ROWS ID, post_title FROM {$wpdb->posts}",
            "where"  => "WHERE post_type = 'product'",
            "like"   => "AND post_title NOT LIKE %s",
            "offset" => "LIMIT %d, %d"
        ];

        $search_term = '';
        if ( ! empty( $_POST['search'] ) ) {
            $search_term = $wpdb->esc_like( $_POST['search'] ) . '%';
            $query['like'] = 'AND post_title LIKE %s';
        }

        $offset = 0;
        $search_limit = 100;
        if ( isset( $_POST['page'] ) && intval( $_POST['page'] ) && $_POST['page'] > 1 ) {
            $offset = $search_limit * absint( $_POST['page'] );
        }

        $final_query = $wpdb->prepare( implode(' ', $query ), $search_term, $offset, $search_limit );
        // Return saved values

        if ( ! empty( $_POST['saved'] ) && is_array( $_POST['saved'] ) ) {
            $saved_ids = array_map('intval', $_POST['saved']);
            $placeholders = array_fill(0, count( $saved_ids ), '%d');
            $format = implode(', ', $placeholders);

            $new_query = [
                "select" => $query['select'],
                "where"  => $query['where'],
                "id"     => "AND ID IN( $format )"
            ];

            $final_query = $wpdb->prepare( implode(" ", $new_query), $saved_ids );
        }

        $results = $wpdb->get_results( $final_query );
        $total_results = $wpdb->get_row("SELECT FOUND_ROWS() as total_rows;");
        $response_data = [
            'results'       => [],
            'total_count'   => $total_results->total_rows
        ];

        if ( $results ) {
            foreach ( $results as $result ) {
                $response_data['results'][] = [
                    'id'    => $result->ID,
                    'text'  => esc_html( $result->post_title )
                ];
            }
        }

        wp_send_json_success( $response_data );
    }

    public function get_wc_products_cat_list() {
        global $wpdb;

        $query = [
            "select" => "SELECT SQL_CALC_FOUND_ROWS a.term_id AS id, b.name as name, b.slug AS slug
                        FROM {$wpdb->term_taxonomy} AS a
                        INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id",
            "where"  => "WHERE a.taxonomy = 'product_cat'",
            "like"   => "AND NOT (a.term_id = '%d' OR b.slug LIKE '%s' OR b.name LIKE '%s' )",
            "offset" => "LIMIT %d, %d"
        ];

        $search_term = '';
        $cat_id     = '';
        if ( ! empty( $_POST['search'] ) ) {

            $cat_id = (int) $search_term;
            $cat_id = $cat_id > 0 ? $cat_id : - 1;

            $search_term = '%' . $wpdb->esc_like( $_POST['search'] ) . '%';
            $query["like"] = "AND (a.term_id = '%d' OR b.slug LIKE '%s' OR b.name LIKE '%s' )";
        }
        // $search_term = trim( $search_term );

        $offset = 0;
        $search_limit = 100;
        if ( isset( $_POST['page'] ) && intval( $_POST['page'] ) && $_POST['page'] > 1 ) {
            $offset = $search_limit * absint( $_POST['page'] );
        }

        $final_query = $wpdb->prepare( implode(' ', $query ), $cat_id, $search_term, $search_term, $offset, $search_limit );

        // Return saved values

        if ( ! empty( $_POST['saved'] ) && is_array( $_POST['saved'] ) ) {
            $saved_ids = array_map('intval', $_POST['saved']);
            $placeholders = array_fill(0, count( $saved_ids ), '%d');
            $format = implode(', ', $placeholders);

            $new_query = [
                "select" => $query['select'],
                "where"  => $query['where'],
                "id"     => "AND b.term_id IN( $format )",
                "order"  => "ORDER BY field(b.term_id, " . implode(",", $saved_ids) . ")"
            ];

            $final_query = $wpdb->prepare( implode( " ", $new_query), $saved_ids );
        }

        $results = $wpdb->get_results( $final_query );

        $total_results = $wpdb->get_row("SELECT FOUND_ROWS() as total_rows;");
        $response_data = [
            'results'       => [],
            'total_count'   => $total_results->total_rows
        ];

        if ( $results ) {
            foreach ( $results as $result ) {
                $response_data['results'][] = [
                    'id'    => $result->id,
                    'text'  => esc_html( $result->name ),
                    'slug'  => esc_html( $result->slug )
                ];
            }
        }

        wp_send_json_success( $response_data );
    }

    public function get_wc_products_tag_list() {
        global $wpdb;

        $query = [
            "select" => "SELECT SQL_CALC_FOUND_ROWS a.term_id AS id, b.name as name, b.slug AS slug
                        FROM {$wpdb->term_taxonomy} AS a
                        INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id",
            "where"  => "WHERE a.taxonomy = 'product_tag'",
            "like"   => "AND NOT (a.term_id = '%d' OR b.slug LIKE '%s' OR b.name LIKE '%s' )",
            "offset" => "LIMIT %d, %d"
        ];

        $search_term = '';
        $cat_id = '';

        if ( ! empty( $_POST['search'] ) ) {
            $cat_id = (int) $search_term;
            $cat_id = $cat_id > 0 ? $cat_id : - 1;

            $search_term = '%' . $wpdb->esc_like( $_POST['search'] ) . '%';
            $query["like"] = "AND (a.term_id = '%d' OR b.slug LIKE '%s' OR b.name LIKE '%s' )";
        }

        $offset = 0;
        $search_limit = 100;
        if ( isset( $_POST['page'] ) && intval( $_POST['page'] ) && $_POST['page'] > 1 ) {
            $offset = $search_limit * absint( $_POST['page'] );
        }

        $final_query = $wpdb->prepare( implode(' ', $query ), $cat_id, $search_term, $search_term, $offset, $search_limit );

        // Return saved values
        if ( ! empty( $_POST['saved'] ) && is_array( $_POST['saved'] ) ) {
            $saved_ids = array_map('intval', $_POST['saved']);
            $placeholders = array_fill(0, count( $saved_ids ), '%d');
            $format = implode(', ', $placeholders);

            $new_query = [
                "select" => $query['select'],
                "where"  => $query['where'],
                "id"     => "AND b.term_id IN( $format )",
                "order"  => "ORDER BY field(b.term_id, " . implode(",", $saved_ids) . ")"
            ];

            $final_query = $wpdb->prepare( implode( " ", $new_query), $saved_ids );
        }

        $results = $wpdb->get_results( $final_query );

        $total_results = $wpdb->get_row("SELECT FOUND_ROWS() as total_rows;");
        $response_data = [
            'results'       => [],
            'total_count'   => $total_results->total_rows
        ];

        if ( $results ) {
            foreach ( $results as $result ) {
                $response_data['results'][] = [
                    'id'    => $result->id,
                    'text'  => esc_html( $result->name ),
                    'slug'  => esc_html( $result->slug )
                ];
            }
        }

        wp_send_json_success( $response_data );
    }

    protected function normalize_arrays( &$settings, $fields = ['cat', 'tag', 'ids', 'taxdropids','field', 'attr'] ) {
        foreach( $fields as $field ) {
            if ( ! isset( $settings[ $field ] ) || ! is_array( $settings[ $field ] ) ) {
                continue;
            }

            $settings[ $field ] = implode(',', $settings[ $field ]);
        }
    }

    protected function render_custom_js() {
        if ( ! isset( $_REQUEST['action'] ) || 'elementor_ajax' != $_REQUEST['action'] ) {
            return null;
        }
    }

}
