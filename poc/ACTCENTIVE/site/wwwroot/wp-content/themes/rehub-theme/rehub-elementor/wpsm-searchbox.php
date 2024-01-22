<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit('Restricted Access');
} // Exit if accessed directly

/**
 * Search Box Widget class.
 *
 *
 * @since 1.0.0
 */
class WPSM_Search_Box_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'wpsm_searchbox';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Search Box', 'rehub-theme');
    }

    public function get_style_depends() {
        return [ 'rhajaxsearch' ];
    }

        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-search';
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
        $this->start_controls_section( 'searchbox_block_section', [
            'label' => esc_html__( 'Search Box Block', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control( 'search_type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Type of Search', 'rehub-theme' ),
            'default'     => 'post',
            'options'     => [
                'post'       =>  esc_html__('Post types', 'rehub-theme'),
                'tax'        =>  esc_html__('Taxonomy', 'rehub-theme'),
                ],
            'label_block' => true,
        ]);
        $this->add_control( 'by', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Choose post type', 'rehub-theme' ),
            'condition'   => [ 'search_type'  => 'post' ],
            'options'     => $this->rehub_post_type_el(),
            'label_block' => true,
        ]);
        $this->add_control( 'tax', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Taxonomy slug', 'rehub-theme' ),
            'description' => esc_html__( 'You can set several with commas. Be aware of taxonomies with too much items.', 'rehub-theme' ),
            'condition'   => [ 'search_type'  => 'tax' ],
            'label_block' => true,
        ]);
        $this->add_control( 'catid', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Only inside category', 'rehub-theme' ),
            'description' => esc_html__( 'You can search items only in category, use category slugs separated by comma', 'rehub-theme' ),
            'condition'   => [ 'search_type'  => 'post' ],
            'label_block'  => true,
        ]);
        $this->add_control( 'enable_ajax', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable ajax search?', 'rehub-theme' ),
            'condition'   => [ 'search_type' => 'post' ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'=> '1',
        ]);
        $this->add_control( 'enable_compare', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Enable compare button in results?', 'rehub-theme' ),
            'description' => esc_html__( 'You must set also dynamic comparison in theme option - dynamic comparison, separate by comma', 'rehub-theme' ),            
            'condition'   => [ 'search_type' => 'post' ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'=> '1',
        ]);        
        $this->add_control( 'placeholder', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Placeholder', 'rehub-theme' ),
            'label_block'  => true,
        ]);
        $this->add_control( 'label', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Text on button', 'rehub-theme' ),
            'description' => esc_html__( 'Or leave blank to show search icon only', 'rehub-theme' ),
            'label_block'  => true,
        ]);
        $this->add_control( 'color', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Color of button', 'rehub-theme' ),
            'default'     => 'btncolor',
            'options'     => [
                'btncolor'       =>  esc_html__('Main Button Color', 'rehub-theme'),
                'main'        =>  esc_html__('Main Theme Color', 'rehub-theme'),
                'secondary'        =>  esc_html__('Secondary Theme Color', 'rehub-theme'),
                'orange'        =>  esc_html__('orange', 'rehub-theme'),
                'gold'        =>  esc_html__('gold', 'rehub-theme'),
                'black'        =>  esc_html__('black', 'rehub-theme'),
                'blue'        =>  esc_html__('blue', 'rehub-theme'),
                'red'        =>  esc_html__('red', 'rehub-theme'),
                'green'        =>  esc_html__('green', 'rehub-theme'),
                'rosy'        =>  esc_html__('rosy', 'rehub-theme'),
                'brown'        =>  esc_html__('brown', 'rehub-theme'),
                'pink'        =>  esc_html__('pink', 'rehub-theme'),
                'purple'        =>  esc_html__('purple', 'rehub-theme'),
                'teal'        =>  esc_html__('teal', 'rehub-theme'),
                ],
            'label_block' => true,
        ]);
        $this->add_control( 'aff_link', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'External url instead inner?', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return_value'      => '1',
        ]);
        $this->end_controls_section();
    }

    protected function rehub_post_type_el() {
        $post_types = get_post_types( array('public'   => true) );
        $post_types_list = array();
        foreach ( $post_types as $post_type ) {
            if ( $post_type !== 'revision' && $post_type !== 'nav_menu_item' && $post_type !== 'attachment') {
                $label = $post_type;
                $post_types_list[$label] = $post_type;
            }
        }
        return $post_types_list;
    }        

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        echo wpsm_searchbox_function( $settings );
    }

}

Plugin::instance()->widgets_manager->register( new WPSM_Search_Box_Widget );
