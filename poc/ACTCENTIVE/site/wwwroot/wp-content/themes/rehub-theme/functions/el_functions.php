<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
// Enqueue Scripts
add_action( 'elementor/preview/enqueue_scripts', function () {
    //wp_enqueue_script('modulobox');
    wp_enqueue_script('gsap');
    wp_enqueue_script('scrolltrigger');
    wp_enqueue_script('gsapsplittext');
    wp_enqueue_script('gsapsvgdraw');
    wp_enqueue_script('gsapsvgpath');
    wp_enqueue_script('gsapsvgpathhelper');
    wp_enqueue_script('zeroclipboard');
    wp_enqueue_script('rehub-elementor', get_template_directory_uri() . '/rehub-elementor/js/custom-elementor.js', array('jquery'), '2.2', true);
});   

add_action( 'elementor/elements/categories_registered', function( $elements_manager ) {
     $elements_manager->add_category( 'rehub-category', [ 'title' => esc_html__( 'Rehub Woocommerce Modules', 'rehub-theme' ), 'icon' => 'eicon-woocommerce' ] );
     $elements_manager->add_category( 'content-modules', [ 'title' => esc_html__( 'Rehub Post Modules', 'rehub-theme' ) ] );
     $elements_manager->add_category( 'deal-helper', [ 'title' => esc_html__( 'Rehub Deal/Coupon Modules', 'rehub-theme' ) ] );
     $elements_manager->add_category( 'helpler-modules', [ 'title' => esc_html__( 'Rehub Helper Modules', 'rehub-theme' ) ] );  
    $elements_manager->add_category( 'rhwow-modules', [ 'title' => esc_html__( 'Rehub WOW Animations', 'rehub-theme' ) ] );     
});

// Ajax general callback methods  and control
require_once (locate_template('rehub-elementor/controls/ajax-callbacks.php'));
function register_rehub_selectajax_control( $controls_manager ) {
    require_once (locate_template('rehub-elementor/controls/select2ajax-control.php'));
    $controls_manager->register( new Select2Ajax_Control );
}
add_action( 'elementor/controls/register', 'register_rehub_selectajax_control' );

add_action( 'init', function () {

    // Abstracts
    require_once (rh_locate_template('rehub-elementor/abstracts/content-base-widget.php'));

    // Widgets
    if(class_exists('Woocommerce')){
        // Abstracts
        require_once (rh_locate_template('rehub-elementor/abstracts/woo-base-widget.php')); 

        require_once (rh_locate_template('rehub-elementor/wpsm-woogrid.php'));
        require_once (rh_locate_template('rehub-elementor/wpsm-woocolumns.php'));       
        require_once (rh_locate_template('rehub-elementor/wpsm-woorows.php'));
        require_once (rh_locate_template('rehub-elementor/wpsm-woolist.php'));
        require_once (rh_locate_template('rehub-elementor/wpsm-woofeatured.php'));
        require_once (rh_locate_template('rehub-elementor/wpsm-woocarousel.php'));
        require_once (rh_locate_template('rehub-elementor/wpsm-woocomparebars.php'));
        require_once (rh_locate_template('rehub-elementor/wpsm-wooday.php'));
    }

    require_once (rh_locate_template('rehub-elementor/wpsm_columngrid.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-newslist.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-regularblog.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-masonrygrid.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-simplelist.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-postfeatured.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-news-with-thumbs.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-news-ticker.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm_coloredgrid.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-3col-grid.php'));        
    
    require_once (rh_locate_template('rehub-elementor/wpsm-deallist.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-dealgrid.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-dealcarousel.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-reviewlist.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-offerbox.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-CEbox.php'));

    require_once (rh_locate_template('rehub-elementor/wpsm-hover-banner.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-theme.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-taxarchive.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-videolist.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-catbox.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-searchbox.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-cardbox.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-getter.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-buttonpopup.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-versustable.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-countdown.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-itinerary.php'));    
    require_once (rh_locate_template('rehub-elementor/wpsm-reviewbox.php')); 
    require_once (rh_locate_template('rehub-elementor/wpsm-tabevery.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-allcarousel.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-svgshape.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-canvas.php'));  
    require_once (rh_locate_template('rehub-elementor/wpsm-particle.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-3dmodel.php'));
    require_once (rh_locate_template('rehub-elementor/wpsm-lottie.php'));
},11); 

// Header and footer locations
function reh_prefix_register_elementor_locations( $elementor_theme_manager ) {
    $elementor_theme_manager->register_location( 'header' );
    $elementor_theme_manager->register_location( 'footer' );
}
add_action( 'elementor/theme/register_locations', 'reh_prefix_register_elementor_locations' );


/*add_action('elementor/widgets/widgets_registered', function($widgets_manager){
    $elementor_widget_blacklist = array('star-rating');
    foreach($elementor_widget_blacklist as $widget_name){
        $widgets_manager->unregister_widget_type($widget_name);
    }
}, 15);*/

// Register icons and styles for Elementor
add_action( 'elementor/editor/before_enqueue_scripts', function() {
    if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
        wp_enqueue_style('elprostyle', get_template_directory_uri() . '/css/elpro.css', array(), '1.1');
    }
    wp_enqueue_style( 'rehubicons', get_template_directory_uri() . '/iconstyle.css', array(), RH_MAIN_THEME_VERSION);
    wp_register_script('elflexslider', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array('jquery'), '2.7.4', true);
    wp_register_script('elflexinit', get_template_directory_uri() . '/js/flexinit.js', array('jquery', 'flexslider'), '2.2.2', true);
    wp_register_style('elflexslider', get_template_directory_uri() . '/css/flexslider.css', array('rhstyle'), '2.2');
    wp_register_script('elrhvideocanvas', get_template_directory_uri() . '/js/videocanvas.js', array('jquery', 'rhinview'), '1.0.0', true );
    wp_register_script('elrhblobcanvas', get_template_directory_uri() . '/js/blobcanvas.js', array('jquery', 'gsap'), '1.0.0', true );

} );   

add_filter('elementor/icons_manager/native', 'rh_change_native_fa', 99);
function rh_change_native_fa($tabs){
    $newicons = [
        'rhicons' => [
            'name' => 'rhicons',
            'label' => 'Rehub Icons',
            'url' => '',
            'enqueue' => '',
            'prefix' => 'rhi-',
            'displayPrefix' => 'rhicon',
            'labelIcon' => 'rhicon rhi-font',
            'ver' => '5.9.0',
            'fetchJson' => get_template_directory_uri() . '/rehub-elementor/solid.js',
            'native' => true,
        ]
    ];
    $tabs += $newicons;
    return $tabs;
}

add_action( 'elementor/frontend/widget/before_render', 'RH_el_elementor_frontend' );
add_action( 'elementor/frontend/section/before_render', 'RH_el_elementor_frontend_section' );
add_action( 'elementor/element/section/section_advanced/after_section_end', 'RH_custom_section_elementor', 10, 2 );
add_action( 'elementor/element/common/_section_responsive/after_section_end', 'RH_parallax_el_elementor', 10, 2 );
add_action( 'elementor/element/html/section_title/after_section_end', 'RH_el_html_add_custom', 10, 2 );
add_action( 'elementor/widget/render_content', 'RH_el_custom_widget_render', 10, 2 );
add_filter('elementor/controls/animations/additional_animations', 'RH_additional_el_annimation');
add_filter( 'elementor/widget/print_template', 'rh_el_custom_print_template', 10, 2 );
add_filter('elementor/image_size/get_attachment_image_html', 'rh_el_add_lazy_load_images',10,4);


function RH_custom_section_elementor( $obj, $args ) {

    $obj->start_controls_section(
        'section_rh_stickyel',
        array(
            'label' => esc_html__( 'RH Smart Section and Background', 'rehub-theme' ),
            'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
        )
    );

    $obj->add_control(
        'rh_stickyel_section_sticky',
        array(
            'label'        => esc_html__( 'Enable smart scroll', 'rehub-theme' ),
            'description' => esc_html__( 'You must have minimum two columns. Smart scroll is visible only on frontend site and not visible in Editor mode of Elementor', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'true',
            'prefix_class' => 'rh-elementor-sticky-',
        )
    );

    $obj->add_control(
        'rh_stickyel_top_spacing',
        array(
            'label'   => esc_html__( 'Top Spacing', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 500,
            'step'    => 1,
            'condition' => array(
                'rh_stickyel_section_sticky' => 'true',
            ),
        )
    );

    $obj->add_control(
        'rh_stickyel_bottom_spacing',
        array(
            'label'   => esc_html__( 'Bottom Spacing', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 500,
            'step'    => 1,
            'condition' => array(
                'rh_stickyel_section_sticky' => 'true',
            ),
        )
    );

    $obj->add_control(
        'rh_parallax_bg',
        array(
            'label'        => esc_html__( 'Enable parallax for background image', 'rehub-theme' ),
            'description' => esc_html__( 'Add background in Style section', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'true',
            'prefix_class' => 'rh-parallax-bg-',
        )
    );

    $obj->add_control(
        'rh_parallax_bg_speed',
        array(
            'label'   => esc_html__( 'Parallax speed', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 200,
            'step'    => 1,
            'default' => 10,
            'condition' => array(
                'rh_parallax_bg' => 'true',
            ),
            'prefix_class' => 'rh-parallax-bg-speed-',
        )
    );  

    $obj->add_control(
        'rh_lazy_bg',[
            'label' => esc_html__( 'Lazy load background', 'rehub-theme' ),
            'type' => Elementor\Controls_Manager::MEDIA,
            'selectors' => [
                '.elementor-editor-active {{WRAPPER}}' => 'background-image: url("{{URL}}");',
                '{{WRAPPER}}.lazy-bg-loaded' => 'background-image: url("{{URL}}");',
            ],

        ]
    );

    $obj->add_control(
        'rh_lazy_bg_c',[
        'label' => esc_html__( 'Color', 'rehub-theme' ),
        'type' => Elementor\Controls_Manager::COLOR,
        'default' => '',
        'selectors' => [
            '{{WRAPPER}}' => 'background-color: {{VALUE}};',
        ],
        'condition' => [
            'rh_lazy_bg[url]!' => '',
        ],
    ]);

    $obj->add_control(
        'rh_lazy_bg_pos',[
            'label' => esc_html__( 'Position', 'rehub-theme' ),
            'type' => Elementor\Controls_Manager::SELECT,
            'default' => '',
            'options' => [
                '' => esc_html__( 'Default', 'rehub-theme' ),
                'top left' => esc_html__( 'Top Left', 'rehub-theme' ),
                'top center' => esc_html__( 'Top Center', 'rehub-theme' ),
                'top right' => esc_html__( 'Top Right', 'rehub-theme' ),
                'center left' => esc_html__( 'Center Left', 'rehub-theme' ),
                'center center' => esc_html__( 'Center Center', 'rehub-theme' ),
                'center right' => esc_html__( 'Center Right', 'rehub-theme' ),
                'bottom left' => esc_html__( 'Bottom Left', 'rehub-theme' ),
                'bottom center' => esc_html__( 'Bottom Center', 'rehub-theme' ),
                'bottom right' => esc_html__( 'Bottom Right', 'rehub-theme' ),
            ],
            'selectors' => [
                '{{WRAPPER}}' => 'background-position: {{VALUE}};',
            ],
            'condition' => [
                'rh_lazy_bg[url]!' => '',
            ],
        ]
    ); 

    $obj->add_control(
        'rh_lazy_bg_att',[
        'label' => esc_html__( 'Attachment', 'rehub-theme' ),
        'type' => Elementor\Controls_Manager::SELECT,
        'default' => '',
        'options' => [
            '' => esc_html__( 'Default', 'rehub-theme' ),
            'scroll' => esc_html__( 'Scroll', 'rehub-theme' ),
            'fixed' => esc_html__( 'Fixed', 'rehub-theme' ),
        ],
        'selectors' => [
            '{{WRAPPER}}' => 'background-attachment: {{VALUE}};',
        ],
        'condition' => [
            'rh_lazy_bg[url]!' => '',
        ],
    ]);   

    $obj->add_control(
        'rh_lazy_bg_repeat',[
        'label' => esc_html__( 'Repeat', 'rehub-theme' ),
        'type' => Elementor\Controls_Manager::SELECT,
        'default' => '',
        'options' => [
            '' => esc_html__( 'Default', 'rehub-theme' ),
            'no-repeat' => esc_html__( 'No-repeat', 'rehub-theme' ),
            'repeat' => esc_html__( 'Repeat', 'rehub-theme' ),
            'repeat-x' => esc_html__( 'Repeat-x', 'rehub-theme' ),
            'repeat-y' => esc_html__( 'Repeat-y', 'rehub-theme' ),
        ],
        'selectors' => [
            '{{WRAPPER}}' => 'background-repeat: {{VALUE}};',
        ],
        'condition' => [
            'rh_lazy_bg[url]!' => '',
        ],
    ]);

    $obj->add_control(
        'rh_lazy_bg_size',[
        'label' => esc_html__( 'Size', 'rehub-theme' ),
        'type' => Elementor\Controls_Manager::SELECT,
        'default' => '',
        'options' => [
            '' => esc_html__( 'Default', 'rehub-theme' ),
            'auto' => esc_html__( 'Auto', 'rehub-theme' ),
            'cover' => esc_html__( 'Cover', 'rehub-theme' ),
            'contain' => esc_html__( 'Contain', 'rehub-theme' ),
        ],
        'selectors' => [
            '{{WRAPPER}}' => 'background-size: {{VALUE}};',
        ],
        'condition' => [
            'rh_lazy_bg[url]!' => '',
        ],
    ]);

    $obj->end_controls_section();
} 
function RH_parallax_el_elementor( $obj, $args ) {

    $obj->start_controls_section(
        'rh_parallax_el_section',
        array(
            'label' => esc_html__( 'Re:Hub Quick Effects', 'rehub-theme' ),
            'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
        )
    );

    $obj->add_control(
        'rh_infinite_rotate',
        array(
            'label'        => esc_html__( 'Enable Infinite rotating', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'infinite',               
            'prefix_class' => 'rotate',
        )
    ); 
    $obj->add_control(
        'rh_infinite_leftright',
        array(
            'label'        => esc_html__( 'Enable Infinite Left to right', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'infinite',               
            'prefix_class' => 'leftright',
        )
    ); 
    $obj->add_control(
        'rh_infinite_updownright',
        array(
            'label'        => esc_html__( 'Enable Infinite Up and Down', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'infinite',               
            'prefix_class' => 'upanddown',
        )
    );
    $obj->add_control(
        'rh_infinite_fastshake',
        array(
            'label'        => esc_html__( 'Enable Infinite Shake', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'Shake',               
            'prefix_class' => 'fast',
        )
    );
    
    $obj->add_control( 'rh_infinite_speed', [
        'type'        => \Elementor\Controls_Manager::SELECT,
        'label'       => esc_html__( 'Animation Speed', 'rehub-theme' ),
        'options'     => [
            '5'   => '5s',
            '10'   =>  '10s',
            '15'   =>  '15s',
            '20'   =>  '20s',
            '25'   =>  '25s',
            '50'   =>  '50s',
            '100'   =>  '100s',                        
            '0'   =>  '0s',
        ],               
        'prefix_class' => 'animationspeed',
    ]); 
    $obj->add_control(
        'rh_perspective_boxshadow',
        array(
            'label'        => esc_html__( 'Enable Perspective Box shadow', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => '1',
            'selectors' => [
                '{{WRAPPER}} > .elementor-widget-container' => 'box-shadow: 0 1px 0 #ccc, 0 2px 0 #ccc, 0 3px 0 #ccc, 0 4px 0 #ccc, 0 5px 0 #ccc, 0 6px 0 #ccc, 0 7px 0 #ccc, 0 8px 0 #ccc, 0 9px 0 #ccc, 0 50px 30px rgba(0,0,0,.25)',
            ],                
        )
    ); 
    $obj->add_control(
        'rh_perspective_textshadow',
        array(
            'label'        => esc_html__( 'Enable Perspective Text shadow', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => '1',
            'selectors' => [
                '{{WRAPPER}} > .elementor-widget-container' => 'text-shadow: 0 1px 0 #ccc, 0 2px 0 #ccc, 0 3px 0 #ccc, 0 4px 0 #ccc, 0 5px 0 #ccc, 0 6px 0 #ccc, 0 7px 0 #ccc, 0 8px 0 #ccc, 0 9px 0 #ccc, 0 50px 30px rgba(0,0,0,.25)',
            ],                
        )
    );                
    $obj->add_control(
        'rh_parallax_circle',
        array(
            'label'   => esc_html__( 'Make shape', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 3000,
            'step'    => 1,
            'selectors' => [
                '{{WRAPPER}} > .elementor-widget-container' => 'width: {{VALUE}}px;height: {{VALUE}}px;display: flex; align-items: center;justify-content: center;',
            ],
        )
    );  
    $obj->add_control(
        'rh_make_rotate',
        array(
            'label'   => esc_html__( 'Rotation (deg)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 360,
            'step'    => 1,
            'selectors' => [
                '{{WRAPPER}} > .elementor-widget-container' => 'transform: rotate({{VALUE}}deg);',
            ],
        )
    ); 

    $obj->end_controls_section(); 

    $obj->start_controls_section(
        'rh_gsap_section',
        array(
            'label' => esc_html__( 'Re:Hub WOW Animations', 'rehub-theme' ),
            'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
        )
    ); 

    $obj->add_control(
        'rh_gsap',
        array(
            'label'        => esc_html__( 'Enable Advanced animations', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'true',
        )
    );
    $obj->start_controls_tabs( 'gsapintabs', ['condition'=> ['rh_gsap' => 'true' ]] );
    $obj->start_controls_tab(
        'gsapftab',
        [
            'label' => esc_html__( 'Init Transform', 'rehub-theme' ),
        ]
    );
    $obj->add_control(
        'rh_gsap_x',
        array(
            'label'   => esc_html__( 'Translate X', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -5000,
            'max'     => 5000,
            'step'    => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
            
        )
    ); 

    $obj->add_control(
        'rh_gsap_y',
        array(
            'label'   => esc_html__( 'Translate Y', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -5000,
            'max'     => 5000,
            'step'    => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    );  

    $obj->add_control(
        'rh_gsap_xo',
        array(
            'label'   => esc_html__( 'Translate X (%)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -1000,
            'max'     => 1000,
            'step'    => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_yo',
        array(
            'label'   => esc_html__( 'Translate Y (%)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -1000,
            'max'     => 1000,
            'step'    => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_z',
        array(
            'label'   => esc_html__( 'Translate Z', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -5000,
            'max'     => 5000,
            'step'    => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_r',
        array(
            'label'   => esc_html__( 'Rotation', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -3600,
            'max'     => 3600,
            'step'    => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_rx',
        array(
            'label'   => esc_html__( 'Rotation X', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -3600,
            'max'     => 3600,
            'step'    => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_ry',
        array(
            'label'   => esc_html__( 'Rotation Y', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -3600,
            'max'     => 3600,
            'step'    => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 
 

    $obj->add_control(
        'rh_gsap_scale',
        array(
            'label'   => esc_html__( 'Scale', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 30,
            'step'    => 0.1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_scale_x',
        array(
            'label'   => esc_html__( 'Scale X', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 30,
            'step'    => 0.1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_scale_y',
        array(
            'label'   => esc_html__( 'Scale Y', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 30,
            'step'    => 0.1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    );

    $obj->add_control(
        'rh_gsap_width',
        array(
            'label'   => esc_html__( 'Width', 'rehub-theme' ),
            'description' => 'set with px, %, em',
            'type'    => Elementor\Controls_Manager::TEXT,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_height',
        array(
            'label'   => esc_html__( 'Height', 'rehub-theme' ),
            'description' => 'set with px, %, em',
            'type'    => Elementor\Controls_Manager::TEXT,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_boxshadow',
        array(
            'label'   => esc_html__( 'Css Box Shadow value', 'rehub-theme' ),
            'description' => 'example: inset 100px 0 0 0 #cc0000',
            'type'    => Elementor\Controls_Manager::TEXT,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    );

    $obj->add_control(
        'rh_gsap_opacity',
        array(
            'label'   => esc_html__( 'Opacity', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 100,
            'step'    => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    );
    $obj->add_control(
        'rh_gsap_bg', [
            'label' => __('Background Color', 'rehub-theme'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'condition' => [
                'rh_gsap' => 'true'
            ]
        ]
    );
    $obj->add_control( 'rh_gsap_origin', [
        'label' => esc_html__( 'Transform Origin', 'rehub-theme' ),
        'label_block'  => true,
        'description' => 'left, right, top, bottom...',
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition' => [
            'rh_gsap' => 'true'
        ],
    ]);
    $obj->end_controls_tab();
    $obj->start_controls_tab(
        'gsapstab',
        [
            'label' => esc_html__( 'SVG MotionPath', 'rehub-theme' ),
        ]
    );
    $obj->add_control( 'rh_gsap_path', [
        'label' => esc_html__( 'Set path', 'rehub-theme' ),
        'description' => esc_html__('can be ID (place with #), svg path coordinates. Also, type here word "custom" to enable Path draw helper. Alt click will add new point. Del will delete it. Then, click on Copy Motion Path button in bottom of page and insert path parameter here.', 'rehub-theme'),
        'label_block'  => true,
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition'=> ['rh_gsap' => 'true' ],
    ]); 
    $obj->add_control( 'rh_gsap_path_align', [
        'label' => esc_html__( 'Align ID', 'rehub-theme' ),
        'description' => esc_html__('By default, element is alighned by itself, but you can set id of path or another element', 'rehub-theme'),
        'label_block'  => true,
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition'=> ['rh_gsap' => 'true', 'rh_gsap_path!' => '' ],
    ]); 
    $obj->add_control(
        'rh_gsap_path_orient',
        array(
            'label'        => esc_html__( 'Orient along path', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'condition'=> ['rh_gsap' => 'true', 'rh_gsap_path!' => '' ],
        )
    );
    $obj->add_control(
        'rh_gsap_path_align_x',
        [
            'label' => __( 'Align origin point X', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'default' => [
                'size' => 0.5,
            ],
            'label_block' => true,
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.1,
                ],
            ],
            'condition'=> ['rh_gsap' => 'true', 'rh_gsap_path!' => '' ],
        ]
    );
    $obj->add_control(
        'rh_gsap_path_align_y',
        [
            'label' => __( 'Align origin point Y', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'default' => [
                'size' => 0.5,
            ],
            'label_block' => true,
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.1,
                ],
            ],
            'condition'=> ['rh_gsap' => 'true', 'rh_gsap_path!' => '' ],
        ]
    );
    $obj->end_controls_tab();
    $obj->end_controls_tabs();

    $obj->add_control(
        'rhhrtabsone',
        [
            'type' => \Elementor\Controls_Manager::DIVIDER,
            'condition'=> [ 'rh_gsap' => 'true'],
        ]
    ); 

    $obj->start_controls_tabs( 'gsapopttabs', ['condition'=> ['rh_gsap' => 'true' ]] );
    $obj->start_controls_tab(
        'gsapttab',
        [
            'label' => esc_html__( 'Animation Option', 'rehub-theme' ),
        ]
    );
    $obj->add_control(
        'rh_gsap_duration',
        array(
            'label'   => esc_html__( 'Duration (s)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0.1,
            'max'     => 60,
            'step'    => 0.1,
            'default' => 1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 

    $obj->add_control(
        'rh_gsap_delay',
        array(
            'label'   => esc_html__( 'Delay (s)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0.1,
            'max'     => 20,
            'step'    => 0.1,
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    );

    $obj->add_control( 'rh_gsap_ease', [
        'type'        => \Elementor\Controls_Manager::SELECT,
        'label'       => esc_html__( 'Ease type', 'rehub-theme' ),
        'options'     => [
            'power0-none'   =>  esc_html__('Linear', 'rehub-theme'),
            'power1-in'   =>  esc_html__('Power 1 in', 'rehub-theme'),
            'power1-out'   =>  esc_html__('Power 1 out', 'rehub-theme'),
            'power1-inOut'   =>  esc_html__('Power 1 inOut', 'rehub-theme'),
            'power2-in'   =>  esc_html__('Power 2 in', 'rehub-theme'),
            'power2-out'   =>  esc_html__('Power 2 out', 'rehub-theme'),
            'power2-inOut'   =>  esc_html__('Power 2 inOut', 'rehub-theme'),
            'power3-in'   =>  esc_html__('Power 3 in', 'rehub-theme'),
            'power3-out'   =>  esc_html__('Power 3 out', 'rehub-theme'),
            'power3-inOut'   =>  esc_html__('Power 3 inOut', 'rehub-theme'),
            'power4-in'   =>  esc_html__('Power 4 in', 'rehub-theme'),
            'power4-out'   =>  esc_html__('Power 4 out', 'rehub-theme'),
            'power4-inOut'   =>  esc_html__('Power 4 inOut', 'rehub-theme'),
            'back-in'   =>  esc_html__('Back in', 'rehub-theme'),
            'back-out'   =>  esc_html__('Back out', 'rehub-theme'),
            'back-inOut'   =>  esc_html__('Back inOut', 'rehub-theme'),
            'elastic-in'   =>  esc_html__('elastic in', 'rehub-theme'),
            'elastic-out'   =>  esc_html__('elastic out', 'rehub-theme'),
            'elastic-inOut'   =>  esc_html__('elastic inOut', 'rehub-theme'),
            'circ-in'   =>  esc_html__('circ in', 'rehub-theme'),
            'circ-out'   =>  esc_html__('circ out', 'rehub-theme'),
            'circ-inOut'   =>  esc_html__('circ inOut', 'rehub-theme'),
            'expo-in'   =>  esc_html__('expo in', 'rehub-theme'),
            'expo-out'   =>  esc_html__('expo out', 'rehub-theme'),
            'expo-inOut'   =>  esc_html__('expo inOut', 'rehub-theme'),
            'cine-in'   =>  esc_html__('cine in', 'rehub-theme'),
            'cine-out'   =>  esc_html__('cine out', 'rehub-theme'),
            'cine-inOut'   =>  esc_html__('cine inOut', 'rehub-theme'),
        ],
        'condition' => array(
            'rh_gsap' => 'true',
        ),
    ]);

    $obj->add_control(
        'rh_gsap_infinite',
        array(
            'label'        => esc_html__( 'Enable infinite', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    );
    $obj->add_control(
        'rh_gsap_yoyo',
        array(
            'label'        => esc_html__( 'Enable Yoyo style', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'default' => 'yes',
            'condition'=> [ 'rh_gsap_infinite' => 'yes', 'rh_gsap' => 'true' ],
        )
    );
    $obj->add_control(
        'rh_gsap_repeatdelay',
        array(
            'label'        => esc_html__( 'Enable delay between animations', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'default' => 'yes',
            'condition'=> [ 'rh_gsap_infinite' => 'yes', 'rh_gsap' => 'true' ],
        )
    );

    $obj->add_control(
        'rh_gsap_from',
        array(
            'label'        => esc_html__( 'Set direction as FROM', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'default' => 'yes',
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        )
    ); 
    $obj->end_controls_tab();
    $obj->start_controls_tab(
        'gsapfotab',
        [
            'label' => esc_html__( 'Trigger Option', 'rehub-theme' ),
        ]
    );
    $obj->add_control( 'rh_gsap_trigger_type', [
        'type'        => \Elementor\Controls_Manager::SELECT,
        'label'       => esc_html__( 'Trigger type', 'rehub-theme' ),
        'description' => esc_html__('Check documentation for available values - ', 'rehub-theme').'<a href="https://greensock.com/docs/v3/Plugins/ScrollTrigger" target="_blank">ScrollTrigger Docs</a>',
        'options'     => [
            'custom'   =>  esc_html__('Scroll trigger', 'rehub-theme'),
            'load'   =>  esc_html__('On load', 'rehub-theme'),
            'batch'   =>  esc_html__('Batch Scroll', 'rehub-theme'),
            'hover'   =>  esc_html__('On Hover', 'rehub-theme'),
            'click'   =>  esc_html__('On Click', 'rehub-theme'),
        ],
        'default' => 'custom',
        'condition' => array(
            'rh_gsap' => 'true',
        ),
    ]);

    $obj->add_control( 'rh_gsap_trigger_field', [
        'label' => esc_html__( 'Css ID of custom trigger or custom class for Batch scroll', 'rehub-theme' ),
        'description' => esc_html__('By default, animation will start when you scroll to element. You can place here custom ID for trigger or custom css class if you use Batch Scroll', 'rehub-theme'),
        'label_block'  => true,
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition' => array(
            'rh_gsap_trigger_type' => ['custom', 'batch', 'hover', 'click'], 'rh_gsap' => 'true',
        ),
    ]);
    $obj->add_control( 'rh_gsap_obj_field', [
        'label' => esc_html__( 'Css ID or class where to add animation', 'rehub-theme' ),
        'description' => esc_html__('By default, will be applied to current object but you can apply to custom object', 'rehub-theme'),
        'label_block'  => true,
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition' => array(
            'rh_gsap_trigger_type' => ['custom', 'load', 'hover', 'click'], 'rh_gsap' => 'true',
        ),
    ]);

    $obj->add_control(
    'rh_gsap_sc_start',
    array(
        'label'   => esc_html__( 'Trigger start', 'rehub-theme' ),
        'description' => esc_html__('By default, trigger is set to top point of element, but you can change this. Example: top center', 'rehub-theme'),
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition' => array(
            'rh_gsap_trigger_type' => ['custom', 'batch'], 'rh_gsap' => 'true',
        ),
    )
    );

    $obj->add_control(
    'rh_gsap_sc_end',
    array(
        'label'   => esc_html__( 'Trigger end', 'rehub-theme' ),
        'description' => esc_html__('By default, trigger scroll end is set to bottom point of element, but you can change this. Example: +=300 will set end of trigger as 300px after start', 'rehub-theme'),
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition' => array(
            'rh_gsap_trigger_type' => ['custom', 'batch'], 'rh_gsap' => 'true',
        ),
    )
    ); 

    $obj->add_control(
    'rh_gsap_batch_interval',
    array(
        'label'   => esc_html__( 'Interval between items', 'rehub-theme' ),
        'description' => esc_html__('By default, interval is 0.15, but you can set other. Use float number.', 'rehub-theme'),
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition' => array(
            'rh_gsap_trigger_type' => ['batch'], 'rh_gsap' => 'true',
        ),
    )
    ); 
    $obj->add_control(
        'rh_gsap_batchrandom',
        array(
            'label'        => esc_html__( 'Enable random order', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'condition' => array(
                'rh_gsap_trigger_type' => ['batch'], 'rh_gsap' => 'true',
            ),
        )
    );    

    $obj->add_control(
    'rh_gsap_sc_dur',
    array(
        'label'   => esc_html__( 'Interpolate animation by Scroll', 'rehub-theme' ),
        'description' => esc_html__('By default, scroll will trigger full animation. If you want to play animation by scrolling, place here number of seconds for feedback. Recommended value is 1.', 'rehub-theme'),
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition' => array(
            'rh_gsap_trigger_type' => 'custom', 'rh_gsap' => 'true',
        ),
    )
    ); 

    $obj->add_control( 'rh_gsap_pinned', [
        'label' => esc_html__( 'Pin while scroll', 'rehub-theme' ),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'label_on' => __('Yes', 'rehub-theme'),
        'label_off' => __('No', 'rehub-theme'),
        'return_value' => 'yes',
        'condition' => array(
            'rh_gsap_trigger_type' => 'custom', 'rh_gsap' => 'true',
        ),
    ]); 
    $obj->add_control( 'rh_gsap_pinspace', [
        'label' => esc_html__( 'Enable overflow on pinned item', 'rehub-theme' ),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'label_on' => __('Yes', 'rehub-theme'),
        'label_off' => __('No', 'rehub-theme'),
        'return_value' => 'yes',
        'condition' => array(
            'rh_gsap_trigger_type' => 'custom', 'rh_gsap' => 'true',
        ),
    ]);
    $obj->add_control(
        'rh_gsap_sc_act', [
            'label' => __('Trigger actions', 'rehub-theme'),
            'description' => esc_html__('Default is: play pause resume reset', 'rehub-theme'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '',
            'condition' => array(
                'rh_gsap_trigger_type' => 'custom', 'rh_gsap' => 'true',
            ),
        //            
        ]
    );
    $obj->add_control(
        'rh_gsap_sc_snap',
        array(
            'label'   => esc_html__( 'Scroll snap', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0.01,
            'max'     => 1,
            'step'    => 0.01,
            'condition' => array(
                'rh_gsap_trigger_type' => 'custom', 'rh_gsap' => 'true',
            ),
        )
    );
    $obj->add_control(
        'rh_videoplay',
        [
            'label' => __( 'Play video on this trigger', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'condition' => array(
                'rh_gsap_trigger_type' => ['custom', 'load', 'hover', 'click'], 'rh_gsap' => 'true',
            ),
        ]
    );
    $obj->end_controls_tab();
    $obj->end_controls_tabs();

    $obj->add_control(
        'rhhr2',
        [
            'label' => __( 'Text, SVG, Stagger', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
            'condition' => array(
                'rh_gsap' => 'true',
            ),
        ]
    );

    $obj->add_control( 'rh_gsap_st_type', [
        'type'        => \Elementor\Controls_Manager::SELECT,
        'label'       => esc_html__( 'Enable Advanced Animation', 'rehub-theme' ),
        'options'     => [
            'no'   =>  esc_html__('No', 'rehub-theme'),
            'text'   =>  esc_html__('On Text', 'rehub-theme'),
            'class'   =>  esc_html__('Stagger', 'rehub-theme'),
            'svg'   =>  esc_html__('SVG lines', 'rehub-theme'),
        ],
        'condition' => array(
            'rh_gsap' => 'true',
        ),
    ]);

    $obj->add_control( 'rh_gsap_text', [
        'type'        => \Elementor\Controls_Manager::SELECT,
        'label'       => esc_html__( 'Break type for text', 'rehub-theme' ),
        'options'     => [
            'lines'   =>  esc_html__('Lines', 'rehub-theme'),
            'chars'   =>  esc_html__('Chars', 'rehub-theme'),
            'words'   =>  esc_html__('Words', 'rehub-theme'),
        ],
        'condition'=> [ 'rh_gsap_st_type' => 'text', 'rh_gsap' => 'true' ],
    ]);

    $obj->add_control( 'rh_gsap_stagger', [
        'label' => esc_html__( 'Set stagger class', 'rehub-theme' ),
        'description' => esc_html__('this will trigger animation on all elements with this class with some delay between each item', 'rehub-theme'),
        'label_block'  => true,
        'type' => \Elementor\Controls_Manager::TEXT,
        'condition'=> [ 'rh_gsap_st_type' => 'class', 'rh_gsap' => 'true' ],
    ]); 

    $obj->add_control(
        'rh_gsap_stdelay',
        array(
            'label'   => esc_html__( 'Stagger delay', 'rehub-theme' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 10,
            'step'    => 0.1,
            'condition' => array(
                'rh_gsap' => 'true', 'rh_gsap_st_type!' => ''
            ),
        )
    );
    $obj->add_control(
        'rh_gsap_strandom',
        array(
            'label'        => esc_html__( 'Enable random order', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'condition' => array(
                'rh_gsap' => 'true', 'rh_gsap_st_type!' => ''
            ),
        )
    );
    if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
    $gsaprepeater = new \Elementor\Repeater();
    $gsaprepeater->add_control(
        'multi_x',
        array(
            'label'   => esc_html__( 'Translate X', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -5000,
            'max'     => 5000,
            'step'    => 1,
            
        )
    ); 

    $gsaprepeater->add_control(
        'multi_y',
        array(
            'label'   => esc_html__( 'Translate Y', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -5000,
            'max'     => 5000,
            'step'    => 1,
        )
    );  

    $gsaprepeater->add_control(
        'multi_xo',
        array(
            'label'   => esc_html__( 'Translate X (%)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -1000,
            'max'     => 1000,
            'step'    => 1,
        )
    ); 

    $gsaprepeater->add_control(
        'multi_yo',
        array(
            'label'   => esc_html__( 'Translate Y (%)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -1000,
            'max'     => 1000,
            'step'    => 1,
        )
    ); 

    $gsaprepeater->add_control(
        'multi_r',
        array(
            'label'   => esc_html__( 'Rotation', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -3600,
            'max'     => 3600,
            'step'    => 1,
        )
    ); 

    $gsaprepeater->add_control(
        'multi_rx',
        array(
            'label'   => esc_html__( 'Rotation X', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -3600,
            'max'     => 3600,
            'step'    => 1,
        )
    ); 

    $gsaprepeater->add_control(
        'multi_ry',
        array(
            'label'   => esc_html__( 'Rotation Y', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => -3600,
            'max'     => 3600,
            'step'    => 1,
        )
    ); 
 

    $gsaprepeater->add_control(
        'multi_scale',
        array(
            'label'   => esc_html__( 'Scale', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 30,
            'step'    => 0.1,
        )
    ); 

    $gsaprepeater->add_control(
        'multi_scale_x',
        array(
            'label'   => esc_html__( 'Scale X', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 30,
            'step'    => 0.1,
        )
    ); 

    $gsaprepeater->add_control(
        'multi_scale_y',
        array(
            'label'   => esc_html__( 'Scale Y', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 30,
            'step'    => 0.1,
        )
    );

    $gsaprepeater->add_control(
        'multi_width',
        array(
            'label'   => esc_html__( 'Width', 'rehub-theme' ),
            'description' => 'set with px, %, em',
            'type'    => Elementor\Controls_Manager::TEXT,
        )
    ); 

    $gsaprepeater->add_control(
        'multi_height',
        array(
            'label'   => esc_html__( 'Height', 'rehub-theme' ),
            'description' => 'set with px, %, em',
            'type'    => Elementor\Controls_Manager::TEXT,
        )
    ); 

    $gsaprepeater->add_control(
        'multi_opacity',
        array(
            'label'   => esc_html__( 'Opacity', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 100,
            'step'    => 1,
        )
    );
    $gsaprepeater->add_control(
        'multi_bg', [
            'label' => __('Background Color', 'rehub-theme'),
            'type' => \Elementor\Controls_Manager::COLOR,
        ]
    );
    $gsaprepeater->add_control( 'multi_origin', [
        'label' => esc_html__( 'Transform Origin', 'rehub-theme' ),
        'label_block'  => true,
        'description' => 'left, right, top, bottom...',
        'type' => \Elementor\Controls_Manager::TEXT,
    ]);
    $gsaprepeater->add_control(
        'multi_from',
        array(
            'label'        => esc_html__( 'Set direction as FROM', 'rehub-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
        )
    ); 
    $gsaprepeater->add_control(
        'multi_duration',
        array(
            'label'   => esc_html__( 'Duration (s)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0.1,
            'max'     => 60,
            'step'    => 0.1,
            'default' => 1,
        )
    ); 
    $gsaprepeater->add_control(
        'multi_delay',
        array(
            'label'   => esc_html__( 'Delay (s)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 0.1,
            'max'     => 20,
            'step'    => 0.1,
        )
    ); 
    $gsaprepeater->add_control( 'multi_time', [
        'label' => esc_html__( 'Custom start time', 'rehub-theme' ),
        'label_block'  => true,
        'description' => '<a href="https://greensock.com/docs/v3/GSAP/Timeline">Documentation</a>',
        'type' => \Elementor\Controls_Manager::TEXT,
    ]);
    $gsaprepeater->add_control(
        'multi_hover',
        array(
            'label'        => esc_html__( 'Enable On Hover Action', 'rehub-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
        )
    );
    $gsaprepeater->add_control( 'multi_ease', [
        'type'        => \Elementor\Controls_Manager::SELECT,
        'label'       => esc_html__( 'Ease type', 'rehub-theme' ),
        'options'     => [
            'power0-none'   =>  esc_html__('Linear', 'rehub-theme'),
            'power1-in'   =>  esc_html__('Power 1 in', 'rehub-theme'),
            'power1-out'   =>  esc_html__('Power 1 out', 'rehub-theme'),
            'power1-inOut'   =>  esc_html__('Power 1 inOut', 'rehub-theme'),
            'power2-in'   =>  esc_html__('Power 2 in', 'rehub-theme'),
            'power2-out'   =>  esc_html__('Power 2 out', 'rehub-theme'),
            'power2-inOut'   =>  esc_html__('Power 2 inOut', 'rehub-theme'),
            'power3-in'   =>  esc_html__('Power 3 in', 'rehub-theme'),
            'power3-out'   =>  esc_html__('Power 3 out', 'rehub-theme'),
            'power3-inOut'   =>  esc_html__('Power 3 inOut', 'rehub-theme'),
            'power4-in'   =>  esc_html__('Power 4 in', 'rehub-theme'),
            'power4-out'   =>  esc_html__('Power 4 out', 'rehub-theme'),
            'power4-inOut'   =>  esc_html__('Power 4 inOut', 'rehub-theme'),
            'back-in'   =>  esc_html__('Back in', 'rehub-theme'),
            'back-out'   =>  esc_html__('Back out', 'rehub-theme'),
            'back-inOut'   =>  esc_html__('Back inOut', 'rehub-theme'),
            'elastic-in'   =>  esc_html__('elastic in', 'rehub-theme'),
            'elastic-out'   =>  esc_html__('elastic out', 'rehub-theme'),
            'elastic-inOut'   =>  esc_html__('elastic inOut', 'rehub-theme'),
            'circ-in'   =>  esc_html__('circ in', 'rehub-theme'),
            'circ-out'   =>  esc_html__('circ out', 'rehub-theme'),
            'circ-inOut'   =>  esc_html__('circ inOut', 'rehub-theme'),
            'expo-in'   =>  esc_html__('expo in', 'rehub-theme'),
            'expo-out'   =>  esc_html__('expo out', 'rehub-theme'),
            'expo-inOut'   =>  esc_html__('expo inOut', 'rehub-theme'),
            'cine-in'   =>  esc_html__('cine in', 'rehub-theme'),
            'cine-out'   =>  esc_html__('cine out', 'rehub-theme'),
            'cine-inOut'   =>  esc_html__('cine inOut', 'rehub-theme'),
        ],
    ]); 
    $gsaprepeater->add_control( 'multi_obj', [
        'label' => esc_html__( 'Custom object', 'rehub-theme' ),
        'label_block'  => true,
        'description' => 'By default, animation will be applied to current object, but you can set custom class or id of object',
        'type' => \Elementor\Controls_Manager::TEXT,
    ]);                                       
    $obj->add_control( 'rh_gsap_multi', [
        'label'    => esc_html__( 'Multiple Animations', 'rehub-theme' ),
        'type'     => \Elementor\Controls_Manager::REPEATER,
        'fields'   => $gsaprepeater->get_controls(),
        'title_field' => 'Duration - {{{ multi_duration }}}',
        'separator' => 'before',
        'prevent_empty' => false,
        'condition' => array(
            'rh_gsap' => 'true',
        ),
    ]);
}

    $obj->add_control(
        'rhhr5',
        [
            'type' => \Elementor\Controls_Manager::DIVIDER,
            'condition'=> [ 'rh_gsap' => 'true' ],
        ]
    );   

    $obj->add_control(
        'rh_reveal', [
            'label' => __('Enabled Reveal', 'rehub-theme'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => '',
            'label_on' => __('Yes', 'rehub-theme'),
            'label_off' => __('No', 'rehub-theme'),
            'return_value' => 'yes',
        //            
        ]
    );
    $obj->add_control(
        'rh_reveal_dir',
        [
            'label' => __( 'Direction', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'lr',
            'options' => [
                'lr' => __( 'Left to Right', 'rehub-theme' ),
                'rl' => __( 'Right to Left', 'rehub-theme' ),
                'tb' => __( 'Top to Bottom', 'rehub-theme' ),
                'bt' => __( 'Bottom to top', 'rehub-theme' ),
            ],
            'condition' => [
                'rh_reveal' => 'yes'
            ]
        ]
    );
    $obj->add_control(
        'rh_reveal_speed', [
            'label' => __('Speed', 'rehub-theme'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 10,
            'step'    => 0.1,
            'default' => 1,
            'condition' => [
                'rh_reveal' => 'yes'
            ]
        ]
    );
    $obj->add_control(
        'rh_reveal_delay', [
            'label' => __('Delay', 'rehub-theme'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 10,
            'step'    => 0.1,
            'condition' => [
                'rh_reveal' => 'yes'
            ]
        ]
    );
    $obj->add_control(
        'rh_reveal_bgcolor', [
            'label' => __('Color', 'rehub-theme'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#ccc',
            'selectors' => [
                '{{WRAPPER}} .rh-reveal-block' => 'background-color: {{VALUE}};',
            ],
            'condition' => [
                'rh_reveal' => 'yes'
            ]
        ]
    );
    $obj->add_control(
        'rhhrrev',
        [
            'type' => \Elementor\Controls_Manager::DIVIDER,
            'condition'=> [ 'rh_reveal' => 'yes' ],
        ]
    ); 

    $obj->add_control(
        'rh_parallax_el',
        array(
            'label'        => esc_html__( 'Enable scroll parallax effect', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'true',
        )
    );

    $obj->add_control(
        'rh_parallax_el_speed',
        array(
            'label'   => esc_html__( 'Time (ms)', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 200,
            'step'    => 1,
            'default' => 10,
            'condition' => array(
                'rh_parallax_el' => 'true',
            ),
        )
    );
    $obj->add_control(
        'rh_parallax_el_strength',
        array(
            'label'   => esc_html__( 'Strength', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 1500,
            'step'    => 1,
            'default' => 100,
            'condition' => array(
                'rh_parallax_el' => 'true',
            ),
        )
    );
    $obj->add_control(
        'rh_parallax_el_dir',
        array(
            'label'        => esc_html__( 'Enable reverse direction', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'default' => 'yes',
            'condition' => array(
                'rh_parallax_el' => 'true',
            ),                
        )
    ); 
    $obj->add_control(
        'rhhrelpar',
        [
            'type' => \Elementor\Controls_Manager::DIVIDER,
            'condition'=> [ 'rh_parallax_el' => 'true' ],
        ]
    ); 

    $obj->add_control(
        'rh_parlx_m_el',
        array(
            'label'        => esc_html__( 'Enable mouse move effect', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'true',
        )
    );

    $obj->add_control(
        'rh_parlx_m_el_cur',
        array(
            'label'        => esc_html__( 'Bounds by Object', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'condition' => array(
                'rh_parlx_m_el' => 'true',
            ),
        )
    );
    $obj->add_control(
        'rh_parlx_restore',
        array(
            'label'        => esc_html__( 'Restore on Mouse Leave', 'rehub-theme' ),
            'type'         => Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
            'label_off'    => esc_html__( 'No', 'rehub-theme' ),
            'return_value' => 'yes',
            'condition' => array(
                'rh_parlx_m_el' => 'true',
                'rh_parlx_m_el_cur' => 'yes'
            ),
        )
    );

    $obj->add_control(
        'rh_parlx_m_el_speed',
        array(
            'label'   => esc_html__( 'Strength for x and y', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 200,
            'step'    => 1,
            'default' => 20,
            'condition' => array(
                'rh_parlx_m_el' => 'true',
            ),
        )
    );
    $obj->add_control(
        'rh_parlx_m_el_tilt',
        array(
            'label'   => esc_html__( 'Strength for tilt', 'rehub-theme' ),
            'type'    => Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 200,
            'step'    => 1,
            'condition' => array(
                'rh_parlx_m_el' => 'true',
            ),
        )
    );                                       

    $obj->end_controls_section();
}
function RH_el_elementor_frontend( $element) {
    if ( $element->get_settings( 'rh_gsap' ) == 'true' || $element->get_settings( 'rh_parallax_el' ) == 'true' || $element->get_settings( 'rh_reveal' ) == 'true' ) {
        wp_enqueue_script('gsap');
        wp_enqueue_script('scrolltrigger');
        wp_enqueue_script('gsapinit');
        if ( $element->get_settings( 'rh_gsap_st_type' ) == 'text' ) {
            wp_enqueue_script('gsapsplittext');
        }
        if ( $element->get_settings( 'rh_gsap_st_type' ) == 'svg') {
            wp_enqueue_script('gsapsvgdraw');
        } 
        if ( $element->get_settings( 'rh_gsap_path' ) !='') {
            wp_enqueue_script('gsapsvgpath');
        }       
    } 
 
    if ( $element->get_settings( 'rh_parlx_m_el' ) == 'true') {
        wp_enqueue_script('gsap');wp_enqueue_script('gsapinit');
    }           
    return;        
}
function RH_el_elementor_frontend_section( $element) {
    if('section' === $element->get_name()){
        if ( $element->get_settings( 'rh_stickyel_section_sticky' ) == 'true' ) {
            wp_enqueue_script('stickysidebar');
            $element->add_render_attribute( '_wrapper', array(
                'data-sticky-top-offset' => ($element->get_settings('rh_stickyel_top_spacing')  != '') ? $element->get_settings('rh_stickyel_top_spacing') : '',
                'data-sticky-bottom-offset' => ($element->get_settings('rh_stickyel_bottom_spacing')  != '') ? $element->get_settings('rh_stickyel_bottom_spacing') : '',            
            ) );
        }
        if ( $element->get_settings( 'rh_parallax_bg' ) == 'true' ) {
            wp_enqueue_script('rh_elparallax');
        }  
        $lazybg = $element->get_settings('rh_lazy_bg');
        if ( !empty($lazybg['url'])) {
            wp_enqueue_script('rhyall');
            $element->add_render_attribute( '_wrapper', 'class', array('lazy-bg') );
        }
    }       
    return;        
}
function RH_el_html_add_custom( $obj, $args ) {
    $obj->start_controls_section(
        'section_rh_custom_html',
        array(
            'label' => esc_html__( 'Custom JS, CSS', 'rehub-theme' ),
            'tab'   => Elementor\Controls_Manager::TAB_CONTENT,
        )
    );    
    $obj->add_control(
        'rh_js',
        [
            'label' => __( 'Enter your JS code', 'rehub-theme' ),
            'type' => Elementor\Controls_Manager::CODE,
            'default' => '',
            'language' => 'javascript',           
        ]
    );
    $obj->add_control(
        'rh_css',
        [
            'label' => __( 'Enter your CSS code', 'rehub-theme' ),
            'type' => Elementor\Controls_Manager::CODE,
            'default' => '',
            'language' => 'css',          
        ]
    );
    $obj->end_controls_section();    
} 
function RH_el_custom_widget_render( $content, $widget ) {
    $settings = $widget->get_settings_for_display();
    if ( 'html' === $widget->get_name() ) {  
        if ( ! empty( $settings['rh_js'] ) ) {
            $customjs = $settings['rh_js'];
            wp_add_inline_script('elementor-frontend', $customjs);
        }
        if ( ! empty( $settings['rh_css'] ) ) {
            $customcss = $settings['rh_css'];
            $cssid = 'el_rh_css_html'.uniqid('', true);
            wp_register_style( $cssid, false );
            wp_enqueue_style( $cssid );
            wp_add_inline_style($cssid, $customcss);            
        }  
    }
    if (!empty($settings['rh_gsap'])) {
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            
        }
        $hideclass = '';
        if ( ! empty( $settings['rh_gsap_from'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-from', $settings['rh_gsap_from'] );
        }
        if ( ! empty( $settings['rh_gsap_trigger_type'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-triggertype', $settings['rh_gsap_trigger_type'] );
            if($settings['rh_gsap_trigger_type'] == 'custom'){
                if ( ! empty( $settings['rh_gsap_sc_start'] ) ) {
                    $widget->add_render_attribute( 'ann-wrapper', 'data-triggerstart', $settings['rh_gsap_sc_start'] );
                }
                if ( ! empty( $settings['rh_gsap_sc_end'] ) ) {
                    $widget->add_render_attribute( 'ann-wrapper', 'data-triggerend', $settings['rh_gsap_sc_end'] );
                }
                if ( ! empty( $settings['rh_gsap_sc_dur'] ) ) {
                    $widget->add_render_attribute( 'ann-wrapper', 'data-triggerscrub', $settings['rh_gsap_sc_dur'] );
                }
                if ( ! empty( $settings['rh_gsap_pinned'] ) ) {
                    $widget->add_render_attribute( 'ann-wrapper', 'data-pinned', $settings['rh_gsap_pinned'] );
                }
                if ( ! empty( $settings['rh_gsap_pinspace'] ) ) {
                    $widget->add_render_attribute( 'ann-wrapper', 'data-pinspace', $settings['rh_gsap_pinspace'] );
                }
                if ( ! empty( $settings['rh_gsap_sc_act'] ) ) {
                    $widget->add_render_attribute( 'ann-wrapper', 'data-triggeraction', $settings['rh_gsap_sc_act'] );
                } 
                if ( ! empty( $settings['rh_gsap_sc_snap'] ) ) {
                    $widget->add_render_attribute( 'ann-wrapper', 'data-triggersnap', $settings['rh_gsap_sc_snap'] );
                }              
            }
            if ( ! empty( $settings['rh_gsap_trigger_field'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-customtrigger', $settings['rh_gsap_trigger_field'] );
            }
            if ( ! empty( $settings['rh_gsap_obj_field'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-customobject', $settings['rh_gsap_obj_field'] );
            }
            if ( ! empty( $settings['rh_gsap_batch_interval'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-batchint', $settings['rh_gsap_batch_interval'] );
            } 
            if ( ! empty( $settings['rh_gsap_batchrandom'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-batchrandom', $settings['rh_gsap_batchrandom'] );
            }           
        }
        if ( ! empty( $settings['rh_videoplay'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-videoplay', $settings['rh_videoplay'] );
        }
        if ( ! empty( $settings['rh_gsap_x'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-x', $settings['rh_gsap_x'] );
        }
        if ( ! empty( $settings['rh_gsap_y'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-y', $settings['rh_gsap_y'] );
        }
        if ( ! empty( $settings['rh_gsap_z'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-z', $settings['rh_gsap_z'] );
        }
        if ( ! empty( $settings['rh_gsap_xo'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-xo', $settings['rh_gsap_xo'] );
        }
        if ( ! empty( $settings['rh_gsap_yo'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-yo', $settings['rh_gsap_yo'] );
        }
        if ( ! empty( $settings['rh_gsap_r'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-r', $settings['rh_gsap_r'] );
        }
        if ( ! empty( $settings['rh_gsap_rx'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-rx', $settings['rh_gsap_rx'] );
        }
        if ( ! empty( $settings['rh_gsap_ry'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-ry', $settings['rh_gsap_ry'] );
        }
        if ( ! empty( $settings['rh_gsap_scale'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-s', $settings['rh_gsap_scale'] );
        }
        if ( ! empty( $settings['rh_gsap_scale_x'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-sx', $settings['rh_gsap_scale_x'] );
        }
        if ( ! empty( $settings['rh_gsap_scale_y'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-sy', $settings['rh_gsap_scale_y'] );
        }
        if ( ! empty( $settings['rh_gsap_width'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-width', $settings['rh_gsap_width'] );
        }
        if ( ! empty( $settings['rh_gsap_height'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-height', $settings['rh_gsap_height'] );
        }
        if ( ! empty( $settings['rh_gsap_boxshadow'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-boxshadow', $settings['rh_gsap_boxshadow'] );
        }
        if ( ! empty( $settings['rh_gsap_opacity'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-o', $settings['rh_gsap_opacity'] );
            if( ! empty( $settings['rh_gsap_from'] ) && $settings['rh_gsap_from'] == 'yes' ){
                if($settings['rh_gsap_opacity'] == 0 || $settings['rh_gsap_opacity'] == 1){
                    $hideclass = ' prehidden';
                }
            }
        }
        if ( ! empty( $settings['rh_gsap_infinite'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-loop', $settings['rh_gsap_infinite'] );
            if ( ! empty( $settings['rh_gsap_yoyo'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-yoyo', $settings['rh_gsap_yoyo'] );
            }
            if ( ! empty( $settings['rh_gsap_repeatdelay'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-repeatdelay', $settings['rh_gsap_repeatdelay'] );
            }
        }
        if ( ! empty( $settings['rh_gsap_delay'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-delay', $settings['rh_gsap_delay'] );
        }
        if ( ! empty( $settings['rh_gsap_ease'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-ease', $settings['rh_gsap_ease'] );
        }
        if ( ! empty( $settings['rh_gsap_stdelay'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-stdelay', $settings['rh_gsap_stdelay'] );
        }
        if ( ! empty( $settings['rh_gsap_strandom'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-strandom', $settings['rh_gsap_strandom'] );
        }
        if ( ! empty( $settings['rh_gsap_bg'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-bg', $settings['rh_gsap_bg'] );
        }
        if ( ! empty( $settings['rh_gsap_stagger'] ) && $settings['rh_gsap_st_type'] == 'class' ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-stagger', $settings['rh_gsap_stagger'] );
        }
        if ( ! empty( $settings['rh_gsap_text'] ) && $settings['rh_gsap_st_type'] == 'text' ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-text', $settings['rh_gsap_text'] );
        }
        if ($settings['rh_gsap_st_type'] == 'svg' ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-svgdraw', 'yes' );
        }
        if ( ! empty( $settings['rh_gsap_origin'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-origin', $settings['rh_gsap_origin'] );
        }
        if ( ! empty( $settings['rh_gsap_path'] ) ) {
            $widget->add_render_attribute( 'ann-wrapper', 'data-path', $settings['rh_gsap_path'] );
            if ( ! empty( $settings['rh_gsap_path_align'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-path-align', $settings['rh_gsap_path_align'] );
            }
            if ( ! empty( $settings['rh_gsap_path_align_x'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-path-alignx', $settings['rh_gsap_path_align_x']['size'] );
            }
            if ( ! empty( $settings['rh_gsap_path_align_y'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-path-aligny', $settings['rh_gsap_path_align_y']['size'] );
            }
            if ( ! empty( $settings['rh_gsap_path_orient'] ) ) {
                $widget->add_render_attribute( 'ann-wrapper', 'data-path-orient', $settings['rh_gsap_path_orient'] );
            }
        }
        $settings['multianimations'] = array();
        if ( ! empty( $settings['rh_gsap_multi'] )) {
            foreach ($settings['rh_gsap_multi'] as $index => $item) {
                foreach ($item as $key => $value) {
                    if(!empty($value)){
                        if(is_array($value)) $value = $value['size'];
                        if($value) $settings['multianimations'][$index][$key] = $value;
                    }
                }        
            }
            $widget->add_render_attribute( 'ann-wrapper', 'data-multianimations', json_encode($settings['multianimations']) );
        }
        $content = '<div '.$widget->get_render_attribute_string( 'ann-wrapper' ).' data-duration="'.$settings['rh_gsap_duration'].'" class="rh-gsap-wrap'.$hideclass.'">'.$content. '</div>';
        if(!empty($settings['rh_gsap_z'])) {
            $content = '<div class="rhforce3d">'.$content. '</div>';
        }
    }
    if ( ! empty( $settings['rh_reveal'] )) {
        if ( ! empty( $settings['rh_reveal_dir'] )) {
            $widget->add_render_attribute( 'reveal-wrapper', 'data-reveal-dir', $settings['rh_reveal_dir'] );
        }
        if ( ! empty( $settings['rh_reveal_speed'] )) {
            $widget->add_render_attribute( 'reveal-wrapper', 'data-reveal-speed', $settings['rh_reveal_speed'] );
        }
        if ( ! empty( $settings['rh_reveal_delay'] )) {
            $widget->add_render_attribute( 'reveal-wrapper', 'data-reveal-delay', $settings['rh_reveal_delay'] );
        }
        if ( ! empty( $settings['rh_reveal_bgcolor'] )) {
            $widget->add_render_attribute( 'reveal-wrapper', 'data-reveal-bg', $settings['rh_reveal_bgcolor'] );
        }
        $content = '<div class="rh-reveal-wrap prehidden position-relative"><div class="rh-reveal-cont">'.$content. '</div><div '.$widget->get_render_attribute_string( 'reveal-wrapper' ).' class="rh-reveal-block abdfullwidth pointernone"></div></div>';
    }
    if ( ! empty( $settings['rh_parallax_el'] )) {
        if ( ! empty( $settings['rh_parallax_el_dir'] )) {
            $widget->add_render_attribute( 'rhpar-wrapper', 'data-from', $settings['rh_parallax_el_dir'] );
        }
        if ( ! empty( $settings['rh_parallax_el_strength'] )) {
            $widget->add_render_attribute( 'rhpar-wrapper', 'data-y', $settings['rh_parallax_el_strength'] );
        }
        if ( ! empty( $settings['rh_parallax_el_speed'] )) {
            $widget->add_render_attribute( 'rhpar-wrapper', 'data-duration', $settings['rh_parallax_el_speed']/10 );
        }
        $content = '<div data-triggerscrub="1" data-triggertype="custom" class="prehidden rh-gsap-wrap" '.$widget->get_render_attribute_string( 'rhpar-wrapper' ).'>'.$content. '</div>';
    }
    if ( ! empty( $settings['rh_parlx_m_el'] )) {
        if ( ! empty( $settings['rh_parlx_m_el_speed'] )) {
            $widget->add_render_attribute( 'rhmprlx-wrapper', 'data-prlx-xy', $settings['rh_parlx_m_el_speed'] );
        }
        if ( ! empty( $settings['rh_parlx_m_el_cur'] )) {
            $widget->add_render_attribute( 'rhmprlx-wrapper', 'data-prlx-cur', $settings['rh_parlx_m_el_cur'] );
        }
        if ( ! empty( $settings['rh_parlx_restore'] )) {
            $widget->add_render_attribute( 'rhmprlx-wrapper', 'data-prlx-rest', $settings['rh_parlx_restore'] );
        }
        if ( ! empty( $settings['rh_parlx_m_el_tilt'] )) {
            $widget->add_render_attribute( 'rhmprlx-wrapper', 'data-prlx-tilt', $settings['rh_parlx_m_el_tilt'] );
        }
        $content = '<div class="rh-prlx-mouse" '.$widget->get_render_attribute_string( 'rhmprlx-wrapper' ).'>'.$content. '</div>';
    }
    return $content;
}

function rh_el_custom_print_template($content, $widget){
    if (!$content) return '';
    if ( 'html' === $widget->get_name() ) {
        ob_start();
        ?>
        {{{ settings.html }}}
        <# if (settings.rh_css ) { #>
        <style>{{{ settings.rh_css }}}</style>
        <# } #>       
        <?php
        $content = ob_get_clean();
    }
    //check gsap
    $content = "<# if ( settings.rh_gsap ) { 
        if ( settings.rh_gsap_from ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-from', settings.rh_gsap_from );
        }
        if ( settings.rh_gsap_trigger_type ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-triggertype', settings.rh_gsap_trigger_type );
            if(settings.rh_gsap_trigger_type == 'custom'){
                if ( settings.rh_gsap_sc_start ) {
                    view.addRenderAttribute( 'ann-wrapper', 'data-triggerstart', settings.rh_gsap_sc_start );
                }
                if ( settings.rh_gsap_sc_end ) {
                    view.addRenderAttribute( 'ann-wrapper', 'data-triggerend', settings.rh_gsap_sc_end );
                }
                if ( settings.rh_gsap_sc_dur ) {
                    view.addRenderAttribute( 'ann-wrapper', 'data-triggerscrub', settings.rh_gsap_sc_dur );
                }
                if ( settings.rh_gsap_pinned ) {
                    view.addRenderAttribute( 'ann-wrapper', 'data-pinned', settings.rh_gsap_pinned );
                }
                if ( settings.rh_gsap_pinspace ) {
                    view.addRenderAttribute( 'ann-wrapper', 'data-pinspace', settings.rh_gsap_pinspace);
                }
                if ( settings.rh_gsap_sc_act ) {
                    view.addRenderAttribute( 'ann-wrapper', 'data-triggeraction', settings.rh_gsap_sc_act );
                }
                if ( settings.rh_gsap_sc_snap ) {
                    view.addRenderAttribute( 'ann-wrapper', 'data-triggersnap', settings.rh_gsap_sc_snap );
                }
            }
            if ( settings.rh_gsap_trigger_field ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-customtrigger', settings.rh_gsap_trigger_field );
            }
            if ( settings.rh_gsap_obj_field ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-customobject', settings.rh_gsap_obj_field );
            }
            if ( settings.rh_gsap_batch_interval ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-batchint', settings.rh_gsap_batch_interval );
            } 
            if ( settings.rh_gsap_batchrandom ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-batchrandom', settings.rh_gsap_batchrandom );
            }           
        }
        if ( settings.rh_videoplay ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-videoplay', settings.rh_videoplay );
        }
        if ( settings.rh_gsap_x ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-x', settings.rh_gsap_x );
        }
        if ( settings.rh_gsap_y ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-y', settings.rh_gsap_y );
        }
        if ( settings.rh_gsap_z ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-z', settings.rh_gsap_z );
        }
        if ( settings.rh_gsap_xo ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-xo', settings.rh_gsap_xo );
        }
        if ( settings.rh_gsap_yo ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-yo', settings.rh_gsap_yo );
        }
        if ( settings.rh_gsap_r ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-r', settings.rh_gsap_r );
        }
        if ( settings.rh_gsap_rx ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-rx', settings.rh_gsap_rx );
        }
        if ( settings.rh_gsap_ry ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-ry', settings.rh_gsap_ry );
        }
        if ( settings.rh_gsap_scale ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-s', settings.rh_gsap_scale );
        }
        if ( settings.rh_gsap_scale_x ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-sx', settings.rh_gsap_scale_x );
        }
        if ( settings.rh_gsap_scale_y ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-sy', settings.rh_gsap_scale_y );
        }
        if ( settings.rh_gsap_width ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-width', settings.rh_gsap_width );
        }
        if ( settings.rh_gsap_height ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-height', settings.rh_gsap_height );
        }
        if ( settings.rh_gsap_boxshadow ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-boxshadow', settings.rh_gsap_boxshadow );
        }
        if ( settings.rh_gsap_opacity ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-o', settings.rh_gsap_opacity );
        }
        if ( settings.rh_gsap_infinite ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-loop', settings.rh_gsap_infinite );
            if ( settings.rh_gsap_yoyo ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-yoyo', settings.rh_gsap_yoyo );
            }
            if ( settings.rh_gsap_repeatdelay ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-repeatdelay', settings.rh_gsap_repeatdelay );
            }
        }
        if ( settings.rh_gsap_delay ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-delay', settings.rh_gsap_delay );
        }
        if ( settings.rh_gsap_ease ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-ease', settings.rh_gsap_ease );
        }
        if ( settings.rh_gsap_stdelay ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-stdelay', settings.rh_gsap_stdelay );
        }
        if ( settings.rh_gsap_strandom ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-strandom', settings.rh_gsap_strandom );
        }
        if ( settings.rh_gsap_stagger && settings.rh_gsap_st_type == 'class') {
            view.addRenderAttribute( 'ann-wrapper', 'data-stagger', settings.rh_gsap_stagger );
        }
        if (settings.rh_gsap_st_type == 'svg') {
            view.addRenderAttribute( 'ann-wrapper', 'data-svgdraw', 'yes' );
        }
        if ( settings.rh_gsap_text && settings.rh_gsap_st_type == 'text' ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-text', settings.rh_gsap_text );
        }
        if ( settings.rh_gsap_bg ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-bg', settings.rh_gsap_bg );
        }
        if ( settings.rh_gsap_origin ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-origin', settings.rh_gsap_origin );
        }
        if ( settings.rh_gsap_path ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-path', settings.rh_gsap_path );
            if ( settings.rh_gsap_path_align ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-path-align', settings.rh_gsap_path_align );
            }
            if ( settings.rh_gsap_path_align_x ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-path-alignx', settings.rh_gsap_path_align_x.size );
            }
            if ( settings.rh_gsap_path_align_y ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-path-aligny', settings.rh_gsap_path_align_y.size );
            }
            if ( settings.rh_gsap_path_orient ) {
                view.addRenderAttribute( 'ann-wrapper', 'data-path-orient', settings.rh_gsap_path_orient );
            }
        }
        if ( settings.rh_gsap_multi ) {
            view.addRenderAttribute( 'ann-wrapper', 'data-multianimations', JSON.stringify(settings.rh_gsap_multi) );
        }
        if ( settings.rh_gsap_z || settings.rh_gsap_rx || settings.rh_gsap_ry ) {
            #>
            <div class=\"rhforce3d\">
            <#
        }

        #>
        <div {{{ view.getRenderAttributeString( 'ann-wrapper' ) }}} class=\"rh-gsap-wrap\" data-duration=\"{{{settings.rh_gsap_duration}}}\">" . $content . "</div>
        <#
        if ( settings.rh_gsap_z || settings.rh_gsap_rx || settings.rh_gsap_ry ) {
            #>
            </div>
            <#
        } 
    }
    else {
        #>" . $content . "<# 
    } #>";


    //Check reveal
    $content = "<# if ( settings.rh_reveal ) {
        if ( settings.rh_reveal_dir ) {
            view.addRenderAttribute( 'reveal-wrapper', 'data-reveal-dir', settings.rh_reveal_dir );
        }
        if ( settings.rh_reveal_speed ) {
            view.addRenderAttribute( 'reveal-wrapper', 'data-reveal-speed', settings.rh_reveal_speed );
        }
        if ( settings.rh_reveal_delay ) {
            view.addRenderAttribute( 'reveal-wrapper', 'data-reveal-delay', settings.rh_reveal_delay );
        }
        if ( settings.rh_reveal_bgcolor ) {
            view.addRenderAttribute( 'reveal-wrapper', 'data-reveal-bg', settings.rh_reveal_bgcolor );
        }
        #>
        <div class=\"rh-reveal-wrap prehidden position-relative\"><div class=\"rh-reveal-cont\">" . $content . "</div><div {{{ view.getRenderAttributeString( 'reveal-wrapper' ) }}} class=\"rh-reveal-block abdfullwidth pointernone\"></div></div></div>
        <# 
    }
    else {
        #>" . $content . "<# 
    } #>";

    //Check parallax
    $content = "<# if ( settings.rh_parallax_el ) {
        if ( settings.rh_parallax_el_dir ) {
            view.addRenderAttribute( 'rhpar-wrapper', 'data-from', settings.rh_parallax_el_dir );
        }
        if ( settings.rh_parallax_el_strength ) {
            view.addRenderAttribute( 'rhpar-wrapper', 'data-y', settings.rh_parallax_el_strength );
        }
        if ( settings.rh_parallax_el_speed ) {
            view.addRenderAttribute( 'rhpar-wrapper', 'data-duration', settings.rh_parallax_el_speed/10 );
        }
        #>
        <div data-triggerscrub=\"1\" data-triggertype=\"custom\" class=\"rh-gsap-wrap\" {{{ view.getRenderAttributeString( 'rhpar-wrapper' ) }}}>" . $content . "</div>
        <# 
    }
    else {
        #>" . $content . "<# 
    } #>";

    //Check parallax mouse
    $content = "<# if ( settings.rh_parlx_m_el ) {
        if ( settings.rh_parlx_m_el_speed ) {
            view.addRenderAttribute( 'rhmprlx-wrapper', 'data-prlx-xy', settings.rh_parlx_m_el_speed );
        }
        if ( settings.rh_parlx_m_el_cur ) {
            view.addRenderAttribute( 'rhmprlx-wrapper', 'data-prlx-cur', settings.rh_parlx_m_el_cur );
        }
        if ( settings.rh_parlx_restore ) {
            view.addRenderAttribute( 'rhmprlx-wrapper', 'data-prlx-rest', settings.rh_parlx_restore );
        }
        if ( settings.rh_parlx_m_el_tilt ) {
            view.addRenderAttribute( 'rhmprlx-wrapper', 'data-prlx-tilt', settings.rh_parlx_m_el_tilt );
        }
        #>
        <div class=\"rh-prlx-mouse\" {{{ view.getRenderAttributeString( 'rhmprlx-wrapper' ) }}}>" . $content . "</div>
        <# 
    }
    else {
        #>" . $content . "<# 
    } #>";

    return $content;
}
function RH_additional_el_annimation($array){
    $array['Rehub Effects'] = [
        'stuckMoveUpOpacity' => 'Stuck Up with Fade',
        'stuckFlipUpOpacity' => 'Stuck Up with Flip and Fade',
        'tracking-in-expand' => 'Text tracking and expand',

    ];
    return $array; 
}
function rh_el_add_lazy_load_images($html, $settings, $image_size_key, $image_key){

    if (rehub_option('enable_lazy_images') == '1'){
        if($html){
            if(stripos($html, 'class=') !== false){
                $html = str_replace('class="', 'class="lazyload ', $html);                
            }else{
                $html = str_replace('img', 'img class="lazyload"', $html);
            }

            $new_src_url = get_template_directory_uri() . '/images/default/blank.gif';
            $html = preg_replace('(src="(.*?)")', 'src="'.$new_src_url.'" data-src="$1"', $html);
        }
    }
    return $html;

}

if ( !class_exists( 'Rehub_Lottie_Class' ) ) {
    class Rehub_Lottie_Class {
        public static $animations = array();

        function __construct() {
            add_action( 'wp_footer', array( $this, 'plus_animation_data' ), 5 );            
        }

        public static function plus_addAnimation( $animation = array() ) {
            
            if ( empty( $animation ) || empty( $animation['id'] ) ) {
                return false;
            }
            
            self::$animations[$animation['container_id']] = $animation;
        }
        public static function plus_getAnimations() {
            return apply_filters( 'wpbdmv-animations', self::$animations );
        }

        public static function plus_hasAnimations() {
            $animations = self::plus_getAnimations();
            return empty( $animations ) ? false : true;
        }

        function plus_animation_data() {
            if ( !self::plus_hasAnimations() ) {
                return;
            }
            wp_localize_script( 'lottie-init', 'wpbodymovin', array(
                'animations' => self::plus_getAnimations()
            ) );
        }

    }
}
function rh_get_local_el_templates() {
    $templates = \Elementor\Plugin::$instance->templates_manager->get_source( 'local' )->get_items();
    $types     = [];

    if ( empty( $templates ) ) {
        $options = [ '0' => esc_html__( 'You Haven’t Saved Templates Yet.', 'rehub-theme' ) ];
    } else {
        $options = [ '0' => esc_html__( 'Select Template', 'rehub-theme' ) ];
        
        foreach ( $templates as $template ) {
            $options[ $template['template_id'] ] = $template['title'] . ' (' . $template['type'] . ')';
            $types[ $template['template_id'] ] = $template['type'];
        }
    }

    return $options;
}
//add_action( 'elementor/element/parse_css', 'rh_add_post_css', 10, 2 );
/*function rh_add_post_css( $post_css, $element ) {
    if ( $post_css instanceof Dynamic_CSS ) {
        return;
    }
    $element_settings = $element->get_settings();
    if ( empty( $element_settings['rh_css'] ) ) {
        return;
    }
    $css = trim( $element_settings['rh_css'] );
    if ( empty( $css ) ) {
        return;
    }
    $post_css->get_stylesheet()->add_raw_css( $css );
}*/