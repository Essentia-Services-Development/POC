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
class WPSM_CatBox_Widget extends Widget_Base {

    public function __construct( array $data = [], array $args = null ) {
        parent::__construct( $data, $args );
        // ajax callback
        // add_action( 'wp_ajax_get_all_taxonomies_list', [ &$this, 'get_taxonomies_list'] );
    }
    /* Widget Name */
    public function get_name() {
        return 'wpsm_catbox';
    }

    /* Widget Title */
    public function get_title() {
        return esc_html__('Category box', 'rehub-theme');
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
        return 'eicon-info-box';
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
        $this->start_controls_section( 'general_section', [
            'label' => esc_html__( 'General', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control( 'title', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Title', 'rehub-theme' ),
            'label_block'  => true,
        ]);
        $this->add_control( 'image', [
            'label' => esc_html__( 'Image', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'default' => [
                'url' => \Elementor\Utils::get_placeholder_image_src(),
            ],
            'label_block'  => true,
        ]);
        $this->add_control( 'size_img', [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label'       => esc_html__( 'Image size', 'rehub-theme' ),
            'description' => esc_html__('Leave blank or try to change size to better fit for image. Example, 170px or 50%', 'rehub-theme'),
            'label_block'  => true,
        ]);
        $this->add_control( 'tax_name', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Choose taxonomy', 'rehub-theme' ),
            'label_block'  => true,
            'multiple'     => false,
            'callback'  => 'wpsm_taxonomies_list'
        ]);        
        $this->add_control( 'category', [
            'type'        => 'select2ajax',
            'label'       => esc_html__( 'Category taxonomy term:', 'rehub-theme' ),
            'label_block'  => true,
            'conditions'  => [
                'terms'   => [
                    [
                        'name'     => 'tax_name',
                        'operator' => '!=',
                        'value'    => '',
                    ],
                ],
            ], 
            'callback'      => 'wpsm_taxonomy_terms',
            'linked_fields' => 'tax_name'                       
        ]);        
        $this->add_control( 'disablelink', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable link from title', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'default'     => 'yes',
        ]);
        $this->add_control( 'disablechild', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable child elements', 'rehub-theme' ),
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
        ]);

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $settings['image'] = $settings['image']['id'];
        echo wpsm_catbox_shortcode( $settings );
    }
    protected function get_taxonomy_list() {
        $args = [
            '_builtin'  => false,
            'public'    => true
        ];
        $terms = [];
        $taxonomies = get_taxonomies( $args, 'names' );
        foreach ($taxonomies as $slug => $name) {
            $terms[$slug] = $name;
        }
        return $terms;
    }
}

Plugin::instance()->widgets_manager->register( new WPSM_CatBox_Widget );
