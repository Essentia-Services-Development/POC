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
class WPSM_Model_T_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'rh_mt_canvas';
    }

    /* Widget Title */
    public function get_title() {
        return __('3d model viewer', 'rehub-theme');
    }
    public function get_style_depends() {
        return [ 'rhmodelview' ];
    }

    public function get_script_depends() {
        return [ 'rh-modelviewer-init'];
    }
        /**
     * Get widget icon.
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-youtube';
    }

    /**
     * category name in which this widget will be shown
     * @since 1.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'rhwow-modules' ];
    }

    protected function register_controls() {
        $this->start_controls_section( 'general', [
            'label' => esc_html__( 'GLTF Model loader', 'rehub-theme' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control( 'td_url', [
            'label' => esc_html__( 'Url on gltf, glb model', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
        ]); 

        $this->add_control( 'td_image', [
            'label' => esc_html__( 'Fallback image', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::MEDIA,
        ]);

        $this->add_control(
            'td_load_iter',
            array(
                'label'        => esc_html__( 'Load only on iteraction', 'rehub-theme' ),
                'desc' => esc_html__( 'To improve google web vitals', 'rehub-theme' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                'return_value' => 'true',
                'condition' => [
                    'td_image[url]!' => '',
                ],
            )
        );

        $this->end_controls_section();

        $this->start_controls_section( 'canvassize', [
            'label' => esc_html__( 'Canvas Size', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
            $this->add_responsive_control(
                'threecanvwidth', [
                    'label' => __('Area width', 'rehub-theme'),
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'size' => '100',
                        'unit' => '%',
                    ],
                    'size_units' => [ '%', 'px'],
                    'separator' => 'before',
                    'range' => [
                        '%' => [
                            'min' => 1,
                            'max' => 200,
                        ],
                        'px' => [
                            'min' => 100,
                            'max' => 2500,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .rh-t-model' => 'width: {{SIZE}}{{UNIT}};',
                    ],
                    
                ]
            );
            $this->add_responsive_control(
                'threecanvheight', [
                    'label' => __('Area height', 'rehub-theme'),
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'size' => '250',
                        'unit' => 'px',
                    ],
                    'size_units' => [ '%', 'px'],
                    'range' => [
                        '%' => [
                            'min' => 1,
                            'max' => 200,
                        ],
                        'px' => [
                            'min' => 100,
                            'max' => 2500,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .rh-t-model' => 'height: {{SIZE}}{{UNIT}};',
                    ],
                    
                ]
            );
        $this->end_controls_section();

        $this->start_controls_section( 'aradd', [
            'label' => esc_html__( 'AR settings', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
            $this->add_control(
                'td_ar',
                array(
                    'label'        => esc_html__( 'Enable Argumented Reality option?', 'rehub-theme' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                    'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                    'return_value' => 'true',
                    'default' => 'true'
                )
            ); 
            $this->add_control(
                'td_ar_scale',
                array(
                    'label'        => esc_html__( 'Disable scale option for AR', 'rehub-theme' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                    'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                    'return_value' => 'true',
                )
            );
            $this->add_control(
                'td_ar_wall',
                array(
                    'label'        => esc_html__( 'Enable placement on Wall', 'rehub-theme' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                    'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                    'return_value' => 'true',
                )
            );
            $this->add_control( 'usdz_url', [
                'label' => esc_html__( 'Url of Usdz model for Ios', 'rehub-theme' ),
                'label_block'  => true,
                'type' => Controls_Manager::TEXT,
            ]); 

        $this->end_controls_section();

        $this->start_controls_section( 'cameraadd', [
            'label' => esc_html__( 'Camera settings', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
            $this->add_control(
                'td_rotate',
                array(
                    'label'        => esc_html__( 'Enable Auto rotate?', 'rehub-theme' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                    'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                    'return_value' => 'true',
                    'default' => 'true'
                )
            ); 
            $this->add_control(
                'td_camera',
                array(
                    'label'        => esc_html__( 'Enable Camera Control?', 'rehub-theme' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                    'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                    'return_value' => 'true',
                    'default' => 'true'
                )
            ); 
            $this->add_control(
                'td_zoom_disable',
                array(
                    'label'        => esc_html__( 'Disable zoom', 'rehub-theme' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                    'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                    'return_value' => 'true',
                )
            ); 
            $this->add_control(
                'td_cam_orbit',
                array(
                    'label'   => esc_html__( 'Custom camera orbit', 'rehub-theme' ),
                    'description' => '<a href="https://modelviewer.dev/examples/stagingandcameras/#orbitAndScroll" target="_blank">Documentation</a>',
                    'type'    => \Elementor\Controls_Manager::CODE,
                )
            );
            $this->add_control(
                'td_scale',
                array(
                    'label'   => esc_html__( 'Scale', 'rehub-theme' ),
                    'type'    => \Elementor\Controls_Manager::NUMBER,
                    'min'     => 0.01,
                    'max'     => 10,
                    'step'    => 0.01,
                )
            ); 
        $this->end_controls_section();

        $this->start_controls_section( 'lightadd', [
            'label' => esc_html__( 'Light and Environment', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
            $this->add_control( 'td_sky', [
                'label' => esc_html__( 'HDR skybox image url', 'rehub-theme' ),
                'label_block'  => true,
                'type' => \Elementor\Controls_Manager::TEXT,
            ]);
            $this->add_control( 'td_env', [
                'label' => esc_html__( 'HDR environment image url', 'rehub-theme' ),
                'label_block'  => true,
                'type' => \Elementor\Controls_Manager::TEXT,
            ]);           
            $this->add_control(
                'td_neutral',
                array(
                    'label'        => esc_html__( 'Enable Neutral Light', 'rehub-theme' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                    'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                    'return_value' => 'true',
                    'condition' => [
                        'td_env[url]' => '',
                    ],
                )
            );
            $this->add_control(
                'td_shadow_opacity',
                array(
                    'label'   => esc_html__( 'Shadow Opacity', 'rehub-theme' ),
                    'type'    => \Elementor\Controls_Manager::NUMBER,
                    'min'     => 0,
                    'max'     => 1,
                    'step'    => 0.01,
                )
            ); 
            $this->add_control(
                'td_shadow_soft',
                array(
                    'label'   => esc_html__( 'Shadow Softness', 'rehub-theme' ),
                    'type'    => \Elementor\Controls_Manager::NUMBER,
                    'min'     => 0,
                    'max'     => 1,
                    'step'    => 0.01,
                )
            ); 
        $this->end_controls_section();

        $this->start_controls_section( 'animationadd', [
            'label' => esc_html__( 'Animation settings', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);        
            $this->add_control(
                'td_play',
                array(
                    'label'        => esc_html__( 'Autoplay animation of model', 'rehub-theme' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                    'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                    'return_value' => 'true',
                )
            );
            $this->add_control( 'td_an_choose', [
                'label' => esc_html__( 'Set name of animation to play', 'rehub-theme' ),
                'label_block'  => true,
                'type' => Controls_Manager::TEXT,
            ]); 
        $this->end_controls_section();

        $this->start_controls_section( 'transformadd', [
            'label' => esc_html__( 'Transform animation settings', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]); 
        $this->add_control(
            'td_rx',
            array(
                'label'   => esc_html__( 'Roll strength', 'rehub-theme' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'min'     => 0,
                'max'     => 250,
                'step'    => 1,
            )
        ); 
        $this->add_control(
            'td_ry',
            array(
                'label'   => esc_html__( 'Pitch strength', 'rehub-theme' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'min'     => 0,
                'max'     => 250,
                'step'    => 1,
            )
        ); 
        $this->add_control(
            'td_rz',
            array(
                'label'   => esc_html__( 'Yaw strength', 'rehub-theme' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'min'     => 0,
                'max'     => 250,
                'step'    => 1,
            )
        );
        $this->add_control(
            'td_mmove',
            array(
                'label'   => esc_html__( 'React on Mouth move', 'rehub-theme' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'min'     => 0,
                'max'     => 100,
                'step'    => 0.1,
            )
        );
        $this->end_controls_section(); 

        $this->start_controls_section( 'scriptsadd', [
            'label' => esc_html__( 'Additional js scripts', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
            $this->add_control(
                'addscript',
                array(
                    'label'   => esc_html__( 'Additional script script', 'rehub-theme' ),
                    'description' => 'available variables are modelViewer, mouseX, mouseY',
                    'type'    => \Elementor\Controls_Manager::CODE,
                )
            );
            $this->add_control(
                'td_variants',
                array(
                    'label'        => esc_html__( 'Enable variant selector of model', 'rehub-theme' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                    'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                    'return_value' => 'yes',
                )
            );

        $this->end_controls_section();
        
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $addscript = $settings['addscript'];
        $variants = $variantblock = $posterblock = '';
        if ( ! empty( $settings['td_url'] )) {
            $this->add_render_attribute( 'rh_tdata', 'src', esc_url($settings['td_url']) );
        }
        if ( ! empty( $settings['td_sky'])) {
            $this->add_render_attribute( 'rh_tdata', 'skybox-image', esc_url($settings['td_sky']));
        }
        if ( ! empty( $settings['td_env'])) {
            $this->add_render_attribute( 'rh_tdata', 'environment-image', esc_url($settings['td_env']));
        }
        if ( ! empty( $settings['td_load_iter'] )) {
            $this->add_render_attribute( 'rh_tdata', 'data-loaditer', 'true' );
        }
        if ( ! empty( $settings['td_neutral'] )) {
            $this->add_render_attribute( 'rh_tdata', 'environment-image', 'neutral' );
        }
        if ( ! empty( $settings['td_shadow_opacity'] )) {
            $this->add_render_attribute( 'rh_tdata', 'shadow-intensity', $settings['td_shadow_opacity'] );
        }
        if ( ! empty( $settings['td_shadow_soft'] )) {
            $this->add_render_attribute( 'rh_tdata', 'shadow-softness', $settings['td_shadow_soft'] );
        }
        if ( ! empty( $settings['td_play'] )) {
            $this->add_render_attribute( 'rh_tdata', 'autoplay' );
        }
        if ( ! empty( $settings['td_an_choose'] )) {
            $this->add_render_attribute( 'rh_tdata', 'animation-name', $settings['td_an_choose'] );
        }
        if ( ! empty( $settings['td_rotate'] )) {
            $this->add_render_attribute( 'rh_tdata', 'auto-rotate' );
        }
        if ( ! empty( $settings['td_camera'] )) {
            $this->add_render_attribute( 'rh_tdata', 'camera-controls' );
            $this->add_render_attribute( 'rh_tdata', 'data-camera', "yes" );
        }
        if ( ! empty( $settings['td_zoom_disable'] )) {
            $this->add_render_attribute( 'rh_tdata', 'disable-zoom' );
        }
        if ( ! empty( $settings['td_cam_orbit'] )) {
            $this->add_render_attribute( 'rh_tdata', 'camera-orbit', $settings['td_cam_orbit'] );
        }
        if ( ! empty( $settings['td_ar'] )) {
            $this->add_render_attribute( 'rh_tdata', 'ar' );
        }
        if ( ! empty( $settings['td_ar_scale'] )) {
            $this->add_render_attribute( 'rh_tdata', 'ar-scale', 'fixed' );
        }
        if ( ! empty( $settings['td_ar_wall'] )) {
            $this->add_render_attribute( 'rh_tdata', 'ar-placement', 'wall' );
        }
        if ( ! empty( $settings['usdz_url'] )) {
            $this->add_render_attribute( 'rh_tdata', 'ios-src', $settings['usdz_url'] );
        }
        if ( ! empty( $settings['td_scale'] )) {
            $this->add_render_attribute( 'rh_tdata', 'data-scale', $settings['td_scale'] );
        }
        if ( ! empty( $settings['td_rx'] )) {
            $this->add_render_attribute( 'rh_tdata', 'data-rx', $settings['td_rx'] );
        }
        if ( ! empty( $settings['td_ry'] )) {
            $this->add_render_attribute( 'rh_tdata', 'data-ry', $settings['td_ry'] );
        }
        if ( ! empty( $settings['td_rz'] )) {
            $this->add_render_attribute( 'rh_tdata', 'data-rz', $settings['td_rz'] );
        }
        if ( ! empty( $settings['td_mmove'] )) {
            $this->add_render_attribute( 'rh_tdata', 'data-mousemove', $settings['td_mmove'] );
        }
        if ( ! empty( $settings['td_variants'] )) {
            $this->add_render_attribute( 'rh_tdata', 'data-variants', 'yes' );
            $variants = true;
        }
        $widgetId = $this->get_id();
        if($variants){
            $variantblock = '<div class="variantcontrols"><div>'.esc_html__('Variant: ', 'rehub-theme').'<select class="ml5 border-grey rhhidden mt5" id="rh_threevar_'.esc_attr($widgetId).'"></select></div></div>';
        }
        if ( ! empty( $settings['td_image']['url'] )) {
            $posterblock = '
            <div class="poster" slot="poster" style="background-image: url('.esc_url($settings['td_image']['url']). ');">
                <div class="pre-prompt">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="36">
                    <defs>
                        <path id="A" d="M.001.232h24.997V36H.001z" />
                    </defs>
                    <g transform="translate(-11 -4)" fill="none" fill-rule="evenodd">
                        <path fill-opacity="0" fill="#fff" d="M0 0h44v44H0z" />
                        <g transform="translate(11 3)">
                            <path d="M8.733 11.165c.04-1.108.766-2.027 1.743-2.307a2.54 2.54 0 0 1 .628-.089c.16 0 .314.017.463.044 1.088.2 1.9 1.092 1.9 2.16v8.88h1.26c2.943-1.39 5-4.45 5-8.025a9.01 9.01 0 0 0-1.9-5.56l-.43-.5c-.765-.838-1.683-1.522-2.712-2-1.057-.49-2.226-.77-3.46-.77s-2.4.278-3.46.77c-1.03.478-1.947 1.162-2.71 2l-.43.5a9.01 9.01 0 0 0-1.9 5.56 9.04 9.04 0 0 0 .094 1.305c.03.21.088.41.13.617l.136.624c.083.286.196.56.305.832l.124.333a8.78 8.78 0 0 0 .509.953l.065.122a8.69 8.69 0 0 0 3.521 3.191l1.11.537v-9.178z" fill-opacity=".5" fill="#e4e4e4" />
                            <path d="M22.94 26.218l-2.76 7.74c-.172.485-.676.8-1.253.8H12.24c-1.606 0-3.092-.68-3.98-1.82-1.592-2.048-3.647-3.822-6.11-5.27-.095-.055-.15-.137-.152-.23-.004-.1.046-.196.193-.297.56-.393 1.234-.6 1.926-.6a3.43 3.43 0 0 1 .691.069l4.922.994V10.972c0-.663.615-1.203 1.37-1.203s1.373.54 1.373 1.203v9.882h2.953c.273 0 .533.073.757.21l6.257 3.874c.027.017.045.042.07.06.41.296.586.77.426 1.22M4.1 16.614c-.024-.04-.042-.083-.065-.122a8.69 8.69 0 0 1-.509-.953c-.048-.107-.08-.223-.124-.333l-.305-.832c-.058-.202-.09-.416-.136-.624l-.13-.617a9.03 9.03 0 0 1-.094-1.305c0-2.107.714-4.04 1.9-5.56l.43-.5c.764-.84 1.682-1.523 2.71-2 1.058-.49 2.226-.77 3.46-.77s2.402.28 3.46.77c1.03.477 1.947 1.16 2.712 2l.428.5a9 9 0 0 1 1.901 5.559c0 3.577-2.056 6.636-5 8.026h-1.26v-8.882c0-1.067-.822-1.96-1.9-2.16-.15-.028-.304-.044-.463-.044-.22 0-.427.037-.628.09-.977.28-1.703 1.198-1.743 2.306v9.178l-1.11-.537C6.18 19.098 4.96 18 4.1 16.614M22.97 24.09l-6.256-3.874c-.102-.063-.218-.098-.33-.144 2.683-1.8 4.354-4.855 4.354-8.243 0-.486-.037-.964-.104-1.43a9.97 9.97 0 0 0-1.57-4.128l-.295-.408-.066-.092a10.05 10.05 0 0 0-.949-1.078c-.342-.334-.708-.643-1.094-.922-1.155-.834-2.492-1.412-3.94-1.65l-.732-.088-.748-.03a9.29 9.29 0 0 0-1.482.119c-1.447.238-2.786.816-3.94 1.65a9.33 9.33 0 0 0-.813.686 9.59 9.59 0 0 0-.845.877l-.385.437-.36.5-.288.468-.418.778-.04.09c-.593 1.28-.93 2.71-.93 4.222 0 3.832 2.182 7.342 5.56 8.938l1.437.68v4.946L5 25.64a4.44 4.44 0 0 0-.888-.086c-.017 0-.034.003-.05.003-.252.004-.503.033-.75.08a5.08 5.08 0 0 0-.237.056c-.193.046-.382.107-.568.18-.075.03-.15.057-.225.1-.25.114-.494.244-.723.405a1.31 1.31 0 0 0-.566 1.122 1.28 1.28 0 0 0 .645 1.051C4 29.925 5.96 31.614 7.473 33.563a5.06 5.06 0 0 0 .434.491c1.086 1.082 2.656 1.713 4.326 1.715h6.697c.748-.001 1.43-.333 1.858-.872.142-.18.256-.38.336-.602l2.757-7.74c.094-.26.13-.53.112-.794s-.088-.52-.203-.76a2.19 2.19 0 0 0-.821-.91" fill-opacity=".6" fill="#000" />
                            <path d="M22.444 24.94l-6.257-3.874a1.45 1.45 0 0 0-.757-.211h-2.953v-9.88c0-.663-.616-1.203-1.373-1.203s-1.37.54-1.37 1.203v16.643l-4.922-.994a3.44 3.44 0 0 0-.692-.069 3.35 3.35 0 0 0-1.925.598c-.147.102-.198.198-.194.298.004.094.058.176.153.23 2.462 1.448 4.517 3.22 6.11 5.27.887 1.14 2.373 1.82 3.98 1.82h6.686c.577 0 1.08-.326 1.253-.8l2.76-7.74c.16-.448-.017-.923-.426-1.22-.025-.02-.043-.043-.07-.06z" fill="#fff" />
                            <g transform="translate(0 .769)">
                                <mask id="B" fill="#fff">
                                    <use xlink:href="#A" />
                                </mask>
                                <path d="M23.993 24.992a1.96 1.96 0 0 1-.111.794l-2.758 7.74c-.08.22-.194.423-.336.602-.427.54-1.11.87-1.857.872h-6.698c-1.67-.002-3.24-.633-4.326-1.715-.154-.154-.3-.318-.434-.49C5.96 30.846 4 29.157 1.646 27.773c-.385-.225-.626-.618-.645-1.05a1.31 1.31 0 0 1 .566-1.122 4.56 4.56 0 0 1 .723-.405l.225-.1a4.3 4.3 0 0 1 .568-.18l.237-.056c.248-.046.5-.075.75-.08.018 0 .034-.003.05-.003.303-.001.597.027.89.086l3.722.752V20.68l-1.436-.68c-3.377-1.596-5.56-5.106-5.56-8.938 0-1.51.336-2.94.93-4.222.015-.03.025-.06.04-.09.127-.267.268-.525.418-.778.093-.16.186-.316.288-.468.063-.095.133-.186.2-.277L3.773 5c.118-.155.26-.29.385-.437.266-.3.544-.604.845-.877a9.33 9.33 0 0 1 .813-.686C6.97 2.167 8.31 1.59 9.757 1.35a9.27 9.27 0 0 1 1.481-.119 8.82 8.82 0 0 1 .748.031c.247.02.49.05.733.088 1.448.238 2.786.816 3.94 1.65.387.28.752.588 1.094.922a9.94 9.94 0 0 1 .949 1.078l.066.092c.102.133.203.268.295.408a9.97 9.97 0 0 1 1.571 4.128c.066.467.103.945.103 1.43 0 3.388-1.67 6.453-4.353 8.243.11.046.227.08.33.144l6.256 3.874c.37.23.645.55.82.9.115.24.185.498.203.76m.697-1.195c-.265-.55-.677-1.007-1.194-1.326l-5.323-3.297c2.255-2.037 3.564-4.97 3.564-8.114 0-2.19-.637-4.304-1.84-6.114-.126-.188-.26-.37-.4-.552-.645-.848-1.402-1.6-2.252-2.204C15.472.91 13.393.232 11.238.232A10.21 10.21 0 0 0 5.23 2.19c-.848.614-1.606 1.356-2.253 2.205-.136.18-.272.363-.398.55C1.374 6.756.737 8.87.737 11.06c0 4.218 2.407 8.08 6.133 9.842l.863.41v3.092l-2.525-.51c-.356-.07-.717-.106-1.076-.106a5.45 5.45 0 0 0-3.14.996c-.653.46-1.022 1.202-.99 1.983a2.28 2.28 0 0 0 1.138 1.872c2.24 1.318 4.106 2.923 5.543 4.772 1.26 1.62 3.333 2.59 5.55 2.592h6.698c1.42-.001 2.68-.86 3.134-2.138l2.76-7.74c.272-.757.224-1.584-.134-2.325" fill-opacity=".05" fill="#000" mask="url(#B)" />
                            </g>
                        </g>
                    </g>
                    </svg>
                </div>
            </div>';
        }
        $defaultblock = '
            <div class="progress-bar" slot="progress-bar">
                <div class="update-bar"></div>
            </div>
            <button slot="ar-button" class="ar-button rhhidden pb5 pl15 pr15 pt5 whitebg rh-flex-center-align rh-flex-justify-center">
            <svg height="25" viewBox="0 0 60 54" width="25" class="mr10"><g fill="none" fill-rule="evenodd"><g fill="rgb(0,0,0)" fill-rule="nonzero"><path d="m53 0h-46c-3.86416566.00440864-6.99559136 3.13583434-7 7v40c.00440864 3.8641657 3.13583434 6.9955914 7 7h46c3.8641657-.0044086 6.9955914-3.1358343 7-7v-40c-.0044086-3.86416566-3.1358343-6.99559136-7-7zm5 47c-.0033061 2.7600532-2.2399468 4.9966939-5 5h-46c-2.76005315-.0033061-4.99669388-2.2399468-5-5v-40c.00330612-2.76005315 2.23994685-4.99669388 5-5h46c2.7600532.00330612 4.9966939 2.23994685 5 5z"/><path d="m53 8h-46c-1.65685425 0-3 1.34314575-3 3v36c0 1.6568542 1.34314575 3 3 3h46c1.6568542 0 3-1.3431458 3-3v-36c0-1.65685425-1.3431458-3-3-3zm-23 19.864-10.891-5.864 10.891-5.864 10.891 5.864zm12-4.19v11.726l-11 5.926v-11.726zm-13 5.926v11.726l-11-5.926v-11.726zm-23-18.6c0-.5522847.44771525-1 1-1h22v4.4l-12.474 6.72c-.013.007-.028.01-.041.018-.3023938.1816727-.4866943.5092336-.485.862v8.382l-10 5zm48 36c0 .5522847-.4477153 1-1 1h-46c-.55228475 0-1-.4477153-1-1v-9.382l10-5v3.382c.000193.3677348.2022003.7056937.526.88l13 7c.2959236.1593002.6520764.1593002.948 0l13-7c.3237997-.1743063.525807-.5122652.526-.88v-3.382l10 5zm0-11.618-10-5v-8.382c-.0001367-.3517458-.1850653-.6775544-.487-.858-.013-.008-.028-.011-.041-.018l-12.472-6.724v-4.4h22c.5522847 0 1 .4477153 1 1z"/><circle cx="6" cy="5" r="1"/><circle cx="10" cy="5" r="1"/><circle cx="14" cy="5" r="1"/><path d="m39 6h14c.5522847 0 1-.44771525 1-1s-.4477153-1-1-1h-14c-.5522847 0-1 .44771525-1 1s.4477153 1 1 1z"/></g></g></svg> '.esc_html__("View in your space", "rehub-theme").'
            </button>
        ';
        echo '<div class="rh-t-model position-relative"><model-viewer id="rh_three_'.esc_attr($widgetId).'" style="width:100%;height:100%;--poster-color: transparent;background-color:transparent;--progress-mask:transparent;--progress-bar-color: #00ab1985" '.$this->get_render_attribute_string( 'rh_tdata' ).'>'.$variantblock.$posterblock.$defaultblock.'</model-viewer></div>';
        $script = '

        (() => {
            const modelViewer = document.querySelector("#rh_three_'.esc_attr($widgetId). '");
            modelViewer.addEventListener("progress", onRHProgress);
            const time = performance.now();
            var td_rx = modelViewer.dataset.rx;
            var td_ry = modelViewer.dataset.ry;
            var td_rz = modelViewer.dataset.rz;
            var td_scale = modelViewer.dataset.scale;
            var td_camera = modelViewer.dataset.camera;
            var td_variants = modelViewer.dataset.variants;
            var td_mousemove = modelViewer.dataset.mousemove;
            var td_loaditer = modelViewer.dataset.loaditer;

            if(td_loaditer){
            }else{
                requestIdleCallback(function(){
                    onRHInteraction();
                }, {
                    timeout:  2500
                });
            }
            var mouseX = 0;
            var mouseY = 0;
            var windowHalfX = window.innerWidth / 2;
            var windowHalfY = window.innerHeight / 2;
            document.addEventListener("mousemove", function(event){

                mouseX = ( event.clientX - windowHalfX );
                mouseY = ( event.clientY - windowHalfY );

            });
            if(td_scale){
                modelViewer.scale = `${td_scale} ${td_scale} ${td_scale}`;
            }
            if(td_variants){
                const select = document.querySelector("#rh_threevar_'.esc_attr($widgetId). '");
                modelViewer.addEventListener("load", () => {
                    const names = modelViewer.availableVariants;
                    if(typeof names !=="undefined" && names.length > 0) {
                        select.classList.remove("rhhidden");
                        for (const name of names) {
                            const option = document.createElement("option");
                            option.value = name;
                            option.textContent = name;
                            select.appendChild(option);
                        }
                    }
                });
                select.addEventListener("input", (event) => {
                  modelViewer.variantName = event.target.value;
                });
            }
            '.$addscript.'
            if(td_rx || td_ry || td_rz || td_mousemove){
                const animate = (now) => {
                    requestAnimationFrame(animate);
                    if(typeof modelViewer.orientation !=="undefined"){
                        let spaceorient = modelViewer.orientation.split(" ");
                        if(typeof td_rx === "undefined") td_rx = 0;
                        if(typeof td_ry === "undefined") td_ry = 0;
                        if(typeof td_rz === "undefined") td_rz = 0;
                        let rx = parseFloat(spaceorient[0]) + td_rx/50;
                        let ry = parseFloat(spaceorient[1]) + td_ry/50;
                        let rz = parseFloat(spaceorient[2]) + td_rz/50;
                        if(td_mousemove){
                            rz += 0.05 * ( mouseX * td_mousemove/1000 - rz );
                            ry += 0.05 * ( mouseY * td_mousemove/1000 - ry );
                        }
                        modelViewer.orientation = `${rx}deg ${ry}deg ${rz}deg`;
                        if(!td_camera){
                            modelViewer.updateFraming();
                        }
                        
                    }
                };
                animate();
            }
          })();

        ';
        if ( Plugin::$instance->editor->is_edit_mode() ) {  
            echo '<script type="module">'.$script.'</script>';
        }else{
            wp_add_inline_script('rh-modelviewer-init', $script);
        }
        
        //echo '<script type="module" src="'.get_template_directory_uri() . '/js/model-viewer.min.js"></script>';
    }
}

Plugin::instance()->widgets_manager->register( new WPSM_Model_T_Widget );
