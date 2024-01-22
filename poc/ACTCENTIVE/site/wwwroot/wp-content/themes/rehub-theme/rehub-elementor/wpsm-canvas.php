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
class WPSM_Canvas_A_Widget extends Widget_Base {

    /* Widget Name */
    public function get_name() {
        return 'rh_a_canvas';
    }

    /* Widget Title */
    public function get_title() {
        return __('Animated canvas', 'rehub-theme');
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
    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        wp_enqueue_script('elrhvideocanvas');
        wp_enqueue_script('elrhblobcanvas');
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
        $this->start_controls_section( 'general_section', [
            'label' => esc_html__( 'General', 'rehub-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control( 'rh_canvas_type', [
            'type'        => \Elementor\Controls_Manager::SELECT,
            'label'       => esc_html__( 'Enable Type of canvas', 'rehub-theme' ),
            'default' => 'video',
            'options'     => [
                'video'   =>  esc_html__('Lazy Load Video', 'rehub-theme'),
                'masksvg'   =>  esc_html__('Animated SVG', 'rehub-theme'),
            ],
        ]);
        $this->add_control( 'rh_vid_mp4', [
            'label' => esc_html__( 'Mp4 video link', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => array(
                'rh_canvas_type' => 'video',
            ),
        ]);
        $this->add_control(
            'rh_vid_mp4_inner',
            [
                'label' => __( 'Or upload File', 'rehub-theme' ). ' mp4',
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_type' => 'video',
                'condition' => array(
                    'rh_canvas_type' => 'video',
                ),
            ]
        );
        $this->add_control( 'rh_vid_webm', [
            'label' => esc_html__( 'Webm video link', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => array(
                'rh_canvas_type' => 'video',
            ),
        ]);
        $this->add_control(
            'rh_vid_webm_inner',
            [
                'label' => __( 'Or upload File', 'rehub-theme' ). ' webm',
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_type' => 'video',
                'condition' => array(
                    'rh_canvas_type' => 'video',
                ),
            ]
        );
        $this->add_control( 'rh_vid_ogv', [
            'label' => esc_html__( 'Ogv video link', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => array(
                'rh_canvas_type' => 'video',
            ),
        ]);
        $this->add_control(
            'rh_vid_ogv_inner',
            [
                'label' => __( 'Or upload File', 'rehub-theme' ). ' ogv',
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_type' => 'video',
                'condition' => array(
                    'rh_canvas_type' => 'video',
                ),
            ]
        );
        $this->add_control(
            'rh_load_iter',
            array(
                'label'        => esc_html__( 'Load only on iteraction', 'rehub-theme' ),
                'desc' => esc_html__( 'To improve google web vitals', 'rehub-theme' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                'return_value' => 'true',
                'condition' => array(
                    'rh_canvas_type' => 'video',
                ),
            )
        );
        $this->add_control( 'rh_vid_poster', [
            'label' => esc_html__( 'Upload poster', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'condition' => array(
                'rh_canvas_type' => 'video',
            ),
            'label_block'  => true,
        ]);
        $this->add_control(
            'rh_vid_breakpoint',
            array(
                'label'   => esc_html__( 'Breakpoint', 'rehub-theme' ),
                'description' => esc_html__( 'Video will be replaced by Fallback image if window width less than this breakpoint', 'rehub-theme' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'min'     => 200,
                'max'     => 2500,
                'step'    => 1,
                'default' => 300,
                'condition' => array(
                    'rh_canvas_type' => 'video',
                ),
            )
        ); 
        $this->add_responsive_control( 'rh_vid_fallback', [
            'label' => esc_html__( 'Upload fallback image', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'condition' => array(
                'rh_canvas_type' => 'video',
            ),
            'label_block'  => true,
        ]);
        $this->add_control( 'disableloop', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable loop in video?', 'rehub-theme' ),
            'condition'   => [ 'rh_canvas_type' => 'video' ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return'      => '1',
        ]);
        $this->add_control( 'disableautoplay', [
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'label'       => esc_html__( 'Disable autoplay in video?', 'rehub-theme' ),
            'condition'   => [ 'rh_canvas_type' => 'video' ],
            'label_on'    => esc_html__('Yes', 'rehub-theme'),
            'label_off'   => esc_html__('No', 'rehub-theme'),
            'return'      => '1',
        ]);
        $this->add_control(
            'tensionPoints',
            [
                'label' => __( 'Curve Tension', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 2,
                ],
                'label_block' => true,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                ],
            ]
        );
        $this->add_control(
            'numPoints',
            [
                'label' => __( 'Num Points', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 5,
                ],
                'label_block' => true,
                'range' => [
                    'px' => [
                        'min' => 3,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                ],
            ]
        );
        $this->add_control(
            'minmaxRadius',
            [
                'label' => __( 'Min Max Radius', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'sizes' => [
                        'start' => 140,
                        'end' => 160,
                    ],
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 600,
                        'step' => 1,
                    ],
                ],
                'labels' => [
                    __( 'Min', 'rehub-theme' ),
                    __( 'Max', 'rehub-theme' ),
                ],
                'scales' => 0,
                'handles' => 'range',
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                ],
            ]
        );
        $this->add_control(
            'minmaxDuration',
            [
                'label' => __( 'Min Max Duration', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'sizes' => [
                        'start' => 5,
                        'end' => 6,
                    ],
                    'unit' => 's',
                ],
                'range' => [
                    's' => [
                        'min' => 0.1,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'labels' => [
                    __( 'Min', 'rehub-theme' ),
                    __( 'Max', 'rehub-theme' ),
                ],
                'scales' => 0,
                'handles' => 'range',
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                ],
            ]
        );
        $this->add_responsive_control(
            'svgarea_size', [
                'label' => __('Svg Size', 'rehub-theme'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => '100',
                    'unit' => '%',
                ],
                'size_units' => [ '%', 'px'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 2000,
                    ],
                ],
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                ],
                'selectors' => [
                    '{{WRAPPER}} .rh-svg-blob' => 'width: {{SIZE}}{{UNIT}};',
                ],
                
            ]
        );
        $this->add_responsive_control(
            'svg_size', [
                'label' => __('Image Size', 'rehub-theme'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => '100',
                    'unit' => '%',
                ],
                'size_units' => [ '%', 'px'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 2000,
                    ],
                ],
                'condition' => [
                    'svg_image[id]!' => '',
                    'rh_canvas_type' => 'masksvg',
                ],
                
            ]
        );
        $this->add_control(
            'svgfilltype',
            [
                'label' => __( 'Fill with', 'rehub-theme' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'image',
                'options' => [
                    'color' => __( 'Color', 'rehub-theme' ),
                    'image' => __( 'Image', 'rehub-theme' ),
                    'gradient' => __( 'Gradient', 'rehub-theme' ),
                ],
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                ],
                'separator' => 'before'
            ]
        );
        $this->add_control(
            'fill_color',
            [
                'label' => __( 'Default Color', 'rehub-theme' ),
                'type' => Controls_Manager::COLOR,
                'default' => '#FF0000',
                'alpha' => false,
                'condition' => [
                    'svgfilltype' => 'color',
                    'rh_canvas_type' => 'masksvg',
                ],
              
            ]
        );
        $this->add_control(
            'svg_image',
                [
                 'label' => __( 'Image', 'rehub-theme' ),
                 'type' => Controls_Manager::MEDIA,
                 'default' => [
                    'url' => '',
                 ],
                 
                 'show_label' => false,
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                    'svgfilltype' => 'image'
                ],
              ]
        );
        $this->add_control(
            'svgimage_x',
            [
                'label' => __( 'Translate X', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => '0',
                ],
                'size_units' => [ '%', 'px'],
                'range' => [
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => -500,
                        'max' => 500,
                        'step' => 1,
                    ],
                ],
                //'render_type' => 'ui',
                'label_block' => false,
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                    'svgfilltype' => 'image'
                ],
            ]
        );
        $this->add_control(
            'svgimage_y',
            [
                'label' => __( 'Translate Y', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => '0',
                ],
                'size_units' => [ '%', 'px'],
                'range' => [
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => -500,
                        'max' => 500,
                        'step' => 1,
                    ],
                ],
                //'render_type' => 'ui',
                'label_block' => false,
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                    'svgfilltype' => 'image'
                ],
            ]
        );
        $this->add_control(
            'gradientx1',
            [
                'label' => __( 'X1 position', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                    'unit' => '%',
                ],
                'label_block' => true,
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                    'svgfilltype' => 'gradient'
                ],
            ]
        );
        $this->add_control(
            'gradientx2',
            [
                'label' => __( 'X2 position', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 100,
                    'unit' => '%',
                ],
                'label_block' => true,
                'size_units' => [ '%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                    'svgfilltype' => 'gradient'
                ],
            ]
        );
        $this->add_control(
            'gradienty1',
            [
                'label' => __( 'Y1 position', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                    'unit' => '%',
                ],
                'label_block' => true,
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                    'svgfilltype' => 'gradient'
                ],
            ]
        );
        $this->add_control(
            'gradienty2',
            [
                'label' => __( 'Y2 position', 'rehub-theme' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 100,
                    'unit' => '%',
                ],
                'label_block' => true,
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                    'svgfilltype' => 'gradient'
                ],
            ]
        );
        $this->add_control(
            'gradientcolor1', [
                'label' => __('Color 1', 'rehub-theme'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff0000',
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                    'svgfilltype' => 'gradient'
                ],
            ]
        ); 
        $this->add_control(
            'gradientcolor2', [
                'label' => __('Color 2', 'rehub-theme'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0000ff',
                'condition' => [
                    'rh_canvas_type' => 'masksvg',
                    'svgfilltype' => 'gradient'
                ],
            ]
        );

        $this->add_responsive_control(
            'rhandwidth', [
                'label' => __('Area width', 'rehub-theme'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => '100',
                    'unit' => '%',
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
                    '{{WRAPPER}} .rh_and_canvas' => 'width: {{SIZE}}{{UNIT}};',
                ],
                
            ]
        );
        $this->add_responsive_control(
            'rhandheight', [
                'label' => __('Area height', 'rehub-theme'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => '100',
                    'unit' => '%',
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
                    '{{WRAPPER}} .rh_and_canvas' => 'height: {{SIZE}}{{UNIT}};',
                ],
                
            ]
        );

        $this->end_controls_section();
    }

    /* Widget output Rendering */
    protected function render() {
        $settings = $this->get_settings_for_display();
        if(!empty($settings['rh_canvas_type']) && $settings['rh_canvas_type'] == 'video') {
            wp_enqueue_script('rhvideocanvas');
            if ( ! empty( $settings['rh_vid_mp4'] )) {
                $this->add_render_attribute( 'rh_vid_data', 'data-mp4', $settings['rh_vid_mp4'] );
            }else if(!empty($settings['rh_vid_mp4_inner']['url'])){
                $this->add_render_attribute( 'rh_vid_data', 'data-mp4', $settings['rh_vid_mp4_inner']['url'] );
            }
            if ( ! empty( $settings['rh_vid_webm'] )) {
                $this->add_render_attribute( 'rh_vid_data', 'data-webm', $settings['rh_vid_webm'] );
            }else if(!empty($settings['rh_vid_webm_inner']['url'])){
                $this->add_render_attribute( 'rh_vid_data', 'data-webm', $settings['rh_vid_webm_inner']['url'] );
            }
            if ( ! empty( $settings['rh_vid_ogv'] )) {
                $this->add_render_attribute( 'rh_vid_data', 'data-ogv', $settings['rh_vid_ogv'] );
            }else if(!empty($settings['rh_vid_ogv_inner']['url'])){
                $this->add_render_attribute( 'rh_vid_data', 'data-ogv', $settings['rh_vid_ogv_inner']['url'] );
            }
            if ( ! empty( $settings['rh_vid_poster'] )) {
                $this->add_render_attribute( 'rh_vid_data', 'poster', $settings['rh_vid_poster']['url'] );
            }
            if ( ! empty( $settings['rh_vid_breakpoint'] )) {
                $this->add_render_attribute( 'rh_vid_data', 'data-breakpoint', $settings['rh_vid_breakpoint'] );
            }
            if ( ! empty( $settings['rh_vid_fallback'] )) {
                $this->add_render_attribute( 'rh_vid_data', 'data-fallback', $settings['rh_vid_fallback']['url'] );
            }
            if ( ! empty( $settings['rh_vid_fallback_tablet'] )) {
                $this->add_render_attribute( 'rh_vid_data', 'data-fallback-tablet', $settings['rh_vid_fallback_tablet']['url'] );
            }
            if ( ! empty( $settings['rh_vid_fallback_mobile'] )) {
                $this->add_render_attribute( 'rh_vid_data', 'data-fallback-mobile', $settings['rh_vid_fallback_mobile']['url'] );
            }
            if ( ! empty( $settings['rh_load_iter'] )) {
                $this->add_render_attribute( 'rh_vid_data', 'data-loaditer', 'true' );
            }
            $loop = (empty( $settings['disableloop'] )) ? ' loop' : '';
            $autoplay = (empty( $settings['disableautoplay'] )) ? ' autoplay' : '';
            echo '<video'.$loop.$autoplay.' playsinline muted class="rh-video-canvas rh_and_canvas" '.$this->get_render_attribute_string( 'rh_vid_data' ).'></video>';
        }else if(!empty($settings['rh_canvas_type']) && $settings['rh_canvas_type'] == 'masksvg') {
            wp_enqueue_script('rhblobcanvas');
            $widgetId = $this->get_id();
            
            if(!empty($settings['svg_image']['id'])){
                $image_url = Group_Control_Image_Size::get_attachment_image_src($settings['svg_image']['id'], 'image', $settings);
                $imageData = wp_get_attachment_image_src($settings['svg_image']['id'],'full');
                $h = $imageData[2];
                $w = $imageData[1];
                $imageProportion = $h/$w;
                $realHeight = $settings['svg_size']['size'] * $imageProportion;
                $this->add_render_attribute('_svgrapper', 'data-resize', $realHeight);
            }
            $this->add_render_attribute('_svgrapper', 'data-numpoints', $settings['numPoints']['size']);
            $this->add_render_attribute('_svgrapper', 'data-minradius', $settings['minmaxRadius']['sizes']['start']);
            $this->add_render_attribute('_svgrapper', 'data-maxradius', $settings['minmaxRadius']['sizes']['end']);
            $this->add_render_attribute('_svgrapper', 'data-minduration', $settings['minmaxDuration']['sizes']['start']);
            $this->add_render_attribute('_svgrapper', 'data-maxduration', $settings['minmaxDuration']['sizes']['end']);
            $this->add_render_attribute('_svgrapper', 'data-tensionpoints', $settings['tensionPoints']['size']);

            if(empty($settings['svgimage_x']['size'])){
                $posX = 0;
            }else{
                $posX = $settings['svgimage_x']['size'];
            }
            if(empty($settings['svgimage_y']['size'])){
                $posY = 0;
            }else{
                $posY = $settings['svgimage_y']['size'];
            }
            ?>
            <div data-id="<?php echo esc_attr($widgetId); ?>" class="rh-svgblob-wrapper rh_and_canvas" <?php echo ''.$this->get_render_attribute_string( '_svgrapper' )?>>
                <svg class="rh-svg-blob" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" preserveAspectRatio="xMidYMid meet" xml:space="preserve">
                      
                    <?php  if(!empty($settings['svg_image']['id']) && $settings['svgfilltype'] == 'image'){ ?>
                    <defs>
                        <pattern id="pattern-<?php echo esc_attr($widgetId); ?>" patternUnits="userSpaceOnUse" patternContentUnits="userSpaceOnUse" width="<?php echo ''.$settings['svg_size']['size'].$settings['svg_size']['unit']; ?>" height="<?php echo ''.$realHeight.$settings['svg_size']['unit']; ?>" x="<?php echo ''.$posX.$settings['svgimage_x']['unit']; ?>" y="<?php echo ''.$posY.$settings['svgimage_y']['unit']; ?>">
                            
                                <image id="img-pattern" xlink:href="<?php echo ''.$image_url; ?>" width="<?php echo ''.$settings['svg_size']['size'].$settings['svg_size']['unit']; ?>" height="<?php echo ''.$realHeight.$settings['svg_size']['unit']; ?>"> </image>
                        </pattern>
                    </defs>
                    <?php } ?>
                    <?php  if($settings['svgfilltype'] == 'gradient'){ ?>
                    <defs>
                        <linearGradient id="pattern-<?php echo esc_attr($widgetId); ?>" x1="<?php echo ''.$settings['gradientx1']['size'].$settings['gradientx1']['unit']; ?>" x2="<?php echo ''.$settings['gradientx2']['size'].$settings['gradientx2']['unit']; ?>" y1="<?php echo ''.$settings['gradienty1']['size'].$settings['gradienty1']['unit']; ?>" y2="<?php echo ''.$settings['gradienty2']['size'].$settings['gradienty2']['unit']; ?>">
                            <stop style="stop-color: <?php echo ''.$settings['gradientcolor1'];?>" offset="0"/>
                            <stop style="stop-color: <?php echo ''.$settings['gradientcolor2'];?>" offset="1"/>
                        </linearGradient>
                    </defs>
                    <?php } ?>


                    <path id="rhblobpath-<?php echo esc_attr($widgetId); ?>"></path>
                    
                    <?php if(!empty($settings['svg_image']['id']) || $settings['gradientcolor1'] != ''):?>
                        <style>
                            #rhblobpath-<?php echo esc_attr($widgetId); ?>{
                                fill: url(#pattern-<?php echo ''.$this->get_id(); ?>);
                            }
                        </style>
                    <?php else:?>
                        <style>
                            #rhblobpath-<?php echo esc_attr($widgetId); ?>{
                                fill: <?php echo ''.$settings['fill_color'];?>;
                            }
                        </style>                    
                    <?php endif;?>


                </svg>
            </div>
            <?php
            wp_enqueue_script('gsap');
        }  
    }

  

}

Plugin::instance()->widgets_manager->register( new WPSM_Canvas_A_Widget );