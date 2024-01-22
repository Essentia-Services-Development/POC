<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Restricted Acces' );
}

abstract class WPSM_Content_Widget_Base extends Widget_Base {
    public function __construct( array $data = [], array $args = null ) {
        parent::__construct( $data, $args );

        // AJAX callbacks
        add_action( 'wp_ajax_get_rehub_post_cat_list', [ &$this, 'get_rehub_post_cat_list'] );
        add_action( 'wp_ajax_get_rehub_post_tag_list', [ &$this, 'get_rehub_post_tag_list'] );
        add_action( 'wp_ajax_get_name_posts_list', [ &$this, 'get_products_title_list'] );
    }

    /**
     * category name in which this widget will be shown
     * @since 1.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'content-modules' ];
    }

    public function get_style_depends() {
        return [ 'rhfilterpanel' ];
    }

    protected function register_controls() {
        $sections = $this->get_sections();

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
    }

    protected function get_sections() {
        return [
            'general'   => esc_html__('Data query', 'rehub-theme'),
            'data'      => esc_html__('Data Settings', 'rehub-theme'),
            'control'   => esc_html__('Design Control', 'rehub-theme'),
            'filters'   => esc_html__('Filter Panel', 'rehub-theme')
        ];
    }

    protected function general_fields() {
        $this->add_control( 'data_source', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Data source', 'rehub-theme' ),
            'default'    => 'cat',
            'options'     => [
                'cat'   =>  esc_html__('Category or tag', 'rehub-theme'),
                'badge'   =>  esc_html__('Is editor choice', 'rehub-theme'),
                'ids'   =>  esc_html__('Manual Select and Order', 'rehub-theme'),
                'cpt'  =>  esc_html__('Custom post type and taxonomy', 'rehub-theme'),
                'auto'  =>  esc_html__('Auto detect archive data', 'rehub-theme'),
            ],
            'label_block'  => true,
        ]);

        $this->add_control( 'cat', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Category', 'rehub-theme' ),
            'description' => esc_html__( 'Enter names of categories. Or leave blank to show all', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'cat' ],
            'label_block'  => true,
            'multiple'     => true,
            'callback'  => 'get_rehub_post_cat_list'
        ]);

        $this->add_control( 'cat_exclude', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Category exclude', 'rehub-theme' ),
            'description' => esc_html__( 'Enter names of categories to exclude', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'cat' ],
            'label_block'  => true,
            'multiple'     => true,
            'callback'  => 'get_rehub_post_cat_list'
        ]);

        $this->add_control( 'tag', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Tag', 'rehub-theme' ),
            'description' => esc_html__( 'Enter names of tags. Or leave blank to show all', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'cat' ],
            'options'     => [],
            'label_block'  => true,
            'multiple'  => true,
            'callback'  => 'get_rehub_post_tag_list'
        ]);

        $this->add_control( 'tag_exclude', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Tags exclude', 'rehub-theme' ),
            'description' => esc_html__( 'Enter names of tags to exclude.', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'cat' ],
            'options'     => [],
            'label_block'  => true,
            'multiple'  => true,
            'callback'  => 'get_rehub_post_tag_list'
        ]);

        $this->add_control( 'ids', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Post names', 'rehub-theme' ),
            'description' => esc_html__( 'Or enter names of posts.', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'ids' ],
            'options'     => [],
            'label_block'  => true,
            'multiple'     => true,
            'callback'    => 'get_name_posts_list'
        ]);

        $this->add_control( 'badge_label', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Editor label', 'rehub-theme' ),
            'description' => esc_html__( 'Select admin label. You can customize labels in theme option - custom badges for posts', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'badge' ],
            'options'     => [
                '1'     => rehub_option('badge_label_1'),
                '2'   => rehub_option('badge_label_2'),
                '3'       => rehub_option('badge_label_3'),
                '4'  => rehub_option('badge_label_4'),
            ],
            'default'=> '1',
            'label_block'  => true,
            'multiple'     => true,
        ]);

        $this->add_control( 'post_type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Post type', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'cpt' ],
            'options'     => $this->rehub_post_type_el(),
            'label_block'  => true,
            'multiple'     => true,
        ]);

        $this->add_control( 'tax_name', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Taxonomy', 'rehub-theme' ),
            'description' => esc_html__( 'Choose taxonomy', 'rehub-theme' ),
            'label_block'  => true,
            'condition'  => [ 'data_source' => 'cpt' ],
            'callback'  => 'wpsm_taxonomies_list'
        ]);

        $this->add_control( 'tax_slug', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Taxonomy term slug', 'rehub-theme' ),
            'description' => esc_html__( 'Choose terms of taxonomy which you enabled in field above', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'cpt' ],
            'label_block'   => true,
            'callback'      => 'wpsm_taxonomy_terms',
            'linked_fields' => 'tax_name'
        ]);

        $this->add_control( 'tax_slug_exclude', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Taxonomy term slug exclude', 'rehub-theme' ),
            'description' => esc_html__( 'Choose which terms of taxonomy which you enabled in field above you want to exclude', 'rehub-theme' ),
            'condition'  => [ 'data_source' => 'cpt' ],
            'label_block'  => true,
            'callback'      => 'wpsm_taxonomy_terms',
            'linked_fields' => 'tax_name'
        ]);

        $this->add_control( 'price_range', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Price range', 'rehub-theme' ),
            'description' => esc_html__( 'Set price range to show. Works only for posts with Main Post offer section. Example of using: 0-100. Will show products with price under 100', 'rehub-theme' ),
            'label_block'  => true,
        ]);                 

        $this->add_control( 'show_coupons_only', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Deal filter', 'rehub-theme' ),
            'description' => esc_html__( 'Choose deal type if you use Posts as offers', 'rehub-theme' ),
            'options'     => [
                'all'=> esc_html__( 'Show all', 'rehub-theme' ),
                '1'  => esc_html__( 'Show discounts (not expired)', 'rehub-theme' ),
                '2'  => esc_html__( 'Only coupons (not expired)', 'rehub-theme' ),
                '3'  => esc_html__( 'Show all except expired', 'rehub-theme' ),
                '4'  => esc_html__( 'Only expired offers (which have expired date)', 'rehub-theme' ),
                '5'  => esc_html__( 'Only offers, excluding coupons (not expired)', 'rehub-theme'),
                '6'  => esc_html__( 'Only with reviews', 'rehub-theme'),
            ],
            'label_block'  => true,
        ]);
    }

    protected function data_fields() {

        $this->add_control( 'orderby', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Order by', 'rehub-theme' ),
            'description' => esc_html__( 'Select order type. If "Meta value" or "Meta value Number" is chosen then meta key is required.', 'rehub-theme' ),
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
                'view'          => esc_html__( 'Views', 'rehub-theme'),
                'thumb'          => esc_html__( 'Thumb/Hot counter', 'rehub-theme'),
                'hot'          => esc_html__( 'Show hottest sorted by date', 'rehub-theme'),
                'expirationdate'          => esc_html__( 'Expiration date', 'rehub-theme'),
                'price'          => esc_html__( 'Price', 'rehub-theme'),
                'discount'          => esc_html__( 'Discount', 'rehub-theme'),
                'rand'          => esc_html__( 'Random order', 'rehub-theme'),
            ],
            'label_block' => true,
        ]);

        $this->add_control( 'order', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Sorting', 'rehub-theme' ),
            'description' => esc_html__( 'Select Sorting Order', 'rehub-theme' ),
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

        $this->add_control( 'meta_key', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Meta key', 'rehub-theme' ),
            'description' => esc_html__( 'Input meta key for ordering.', 'rehub-theme' ),
            'condition'  => [ 'orderby' => [ 'meta_value', 'meta_value_num' ] ],
            'label_block'  => true,
        ]);

        $this->add_control( 'offset', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Offset', 'rehub-theme' ),
            'description' => esc_html__('Number of products to offset', 'rehub-theme'),
            'label_block' => true,
        ]);

        $this->add_control( 'show_date', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Filter by date', 'rehub-theme' ),
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
                'no' => esc_html__( 'No pagination', 'rehub-theme' ),
                '1' => esc_html__( 'Simple pagination', 'rehub-theme' ),
                '2' => esc_html__( 'Infinite scroll', 'rehub-theme' ),
                '3' => esc_html__( 'New item will be added by click', 'rehub-theme' ),
            ],
            'label_block' => true,
        ]);
    }

    protected function control_fields() {
        $this->add_control( 'exerpt_count', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Symbols in exerpt', 'rehub-theme' ),
            'description' => esc_html__('Set 0 to disable exerpt', 'rehub-theme'),
            'default'     => '0',
            'label_block'  => true,
        ]);

         $this->add_control( 'disable_meta', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable post meta?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);

         $this->add_control( 'disable_price', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable price meta?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);

         $this->add_control( 'image_padding', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable padding in images?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);

        $this->add_control( 'disablecard', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable Boxed Layout?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);

         $this->add_control( 'enable_btn', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable affiliate button?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);

        $this->add_control( 'columns', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Set columns', 'rehub-theme' ),
            'options'     => [
                '3_col'             => esc_html__( '3 Columns', 'rehub-theme' ),
                '2_col'             => esc_html__( '2 Columns', 'rehub-theme' ),
                '4_col'             => esc_html__( '4 Columns', 'rehub-theme' ),
                '5_col'             => esc_html__( '5 Columns', 'rehub-theme' ),
                '6_col'             => esc_html__( '6 Columns', 'rehub-theme' ),
            ],
            'label_block' => true,
            'default' => '4_col',
        ]);

        $this->add_control( 'aff_link', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Make link as affiliate?', 'rehub-theme' ),
            'description' => esc_html__( 'This will change all inner post links to affiliate link of post offer', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
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
                'default'     => 'all',
                'options'     => [
                    'all'           => esc_html__( 'Show all Posts', 'rehub-theme' ),
                    'comment'       => esc_html__( 'Sort by Comments Count', 'rehub-theme' ),
                    'meta'          => esc_html__( 'Sort by Meta Field', 'rehub-theme' ),
                    'expirationdate'=> esc_html__( 'Sort by Expiration Date', 'rehub-theme' ),
                    'pricerange'    => esc_html__( 'Sort by Price Range', 'rehub-theme' ),
                    'hot'           => esc_html__( 'Show hottest sorted by date', 'rehub-theme' ),
                    'tax'           => esc_html__( 'Sort by Taxonomy', 'rehub-theme' ),
                    'deals'         => esc_html__( 'Show only Deals', 'rehub-theme' ),
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

            $repeater->add_control( 'filtermetakeydesc', [
                'type'      => \Elementor\Controls_Manager::RAW_HTML,
                'conditions'  => [
                    'terms'   => [
                        [
                            'name'     => 'filtertype',
                            'operator' => '=',
                            'value'    => 'meta',
                        ],
                    ],
                ],
                'raw' => '<div style="background-color: #dcf0f3; padding:10px; line-height:16px">Some important meta keys: <br /><br /><strong>rehub_main_product_price</strong> - key where stored price of main offer, <br /><strong>rehub_review_overall_score</strong> - key for overall review score, <br /><strong>post_hot_count</strong> - thumbs counter, <br /><strong>post_wish_count</strong> - wishlist counter, <br /><strong>post_user_average</strong> - user rating score(based on full review criterias), <br /><strong>rehub_views</strong> - post view counter, <br /><strong>rehub_views_mon, rehub_views_day, rehub_views_year</strong> - post view counter by day, month, year <br /><strong>affegg_product_price</strong> - price of main offer for Affiliate Egg plugin, <br /><strong>_price</strong> - key for price of woocommerce products, <br /><strong>total_sales</strong> - key for sales of woocommerce products</div>',
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
                'condition'    => [ 'filtertype' => 'pricerange' ],
            ]);

            $repeater->add_control( 'filtertaxkey', [
                'type'      => \Elementor\Controls_Manager::TEXT,
                'label'     => esc_html__('Additional Taxonomy slug', 'rehub-theme'),
                'description'=> 'Enter slug of your taxonomy. Examples: if you want to use post categories - use category. If you want to use woocommerce product category - use product_cat, woocommerce tags - product_tag',
                'condition' => [ 'filtertype' => 'tax' ],
                'label_block'=> true,
            ]);

            $repeater->add_control( 'filtertaxtermslug', [
                'type'      => \Elementor\Controls_Manager::TEXT,
                'label'     => esc_html__('Additional Taxonomy term slug', 'rehub-theme'),
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
            ],
            'condition'=> [ 'filterpanelenable' => 'yes' ],
        ]);         
    }

    public function get_products_title_list() {
        global $wpdb;

        //$post_types = get_post_types( array('public'   => true) );
        //$placeholdersformat = array_fill(0, count( $post_types ), '%s');
        //$postformat = implode(", ", $placeholdersformat);

        $query = [
            "select" => "SELECT SQL_CALC_FOUND_ROWS ID, post_title FROM {$wpdb->posts}",
            "where"  => "WHERE post_type IN ('post', 'product', 'blog', 'page')",
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
            $saved_ids = $_POST['saved'];
            $placeholders = array_fill(0, count( $saved_ids ), '%d');
            $format = implode(', ', $placeholders);

            $new_query = [
                "select" => $query['select'],
                "where"  => $query['where'],
                "id"     => "AND ID IN( $format )",
                "order"  => "ORDER BY field(ID, " . implode(",", $saved_ids) . ")"
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

    public static function get_rehub_post_cat_list($tax_name = '') {
        global $wpdb;
        $taxonomy = $tax_name ? $tax_name : 'category'; // changed
        $query = [
            "select" => "SELECT SQL_CALC_FOUND_ROWS a.term_id AS id, b.name as name, b.slug AS slug
                        FROM {$wpdb->term_taxonomy} AS a
                        INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id",
            "where"  => "WHERE a.taxonomy = '{$taxonomy}'",
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
                    'id'    => $tax_name ? esc_html( $result->slug ) : (int)$result->id,
                    'text'  => esc_html( $result->name ),
                    'slug'  => esc_html( $result->slug )
                ];
            }
        }

        wp_send_json_success( $response_data );
    }

    public function get_rehub_post_tag_list() {
        global $wpdb;

        $query = [
            "select" => "SELECT SQL_CALC_FOUND_ROWS a.term_id AS id, b.name as name, b.slug AS slug
                        FROM {$wpdb->term_taxonomy} AS a
                        INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id",
            "where"  => "WHERE a.taxonomy = 'post_tag'",
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

    protected function normalize_arrays( &$settings, $fields = ['cat', 'tag', 'ids', 'taxdropids','field', 'cat_exclude', 'tag_exclude', 'postid'] ) {
        foreach( $fields as $field ) {
            if ( ! isset( $settings[ $field ] ) || ! is_array( $settings[ $field ] ) ) {
                continue;
            }

            $settings[ $field ] = implode(',', $settings[ $field ]);
        }
    }
    protected function rehub_post_formats() {
        return [
                'all'   => esc_html__( 'All', 'rehub-theme' ),
                'regular'   => esc_html__( 'regular', 'rehub-theme' ),
                'video'  => esc_html__( 'video', 'rehub-theme' ),
                'gallery' => esc_html__( 'gallery', 'rehub-theme' ),
                'review'  => esc_html__( 'review', 'rehub-theme' ),
                'music'  => esc_html__( 'music', 'rehub-theme' ),
            ];
    }

    protected function render_custom_js() {
        if ( ! isset( $_REQUEST['action'] ) || 'elementor_ajax' != $_REQUEST['action'] ) {
            return null;
        }
    }

    protected function rehub_post_type_el() {
        $post_types = get_post_types( array('public'   => true) );
        $post_types_list = array();
        foreach ( $post_types as $post_type ) {
            if ( $post_type !== 'revision' && $post_type !== 'nav_menu_item' && $post_type !== 'attachment') {
                $label = $post_type;
                $post_types_list[$post_type] = $label;
            }
        }
        return $post_types_list;
    }     

}
