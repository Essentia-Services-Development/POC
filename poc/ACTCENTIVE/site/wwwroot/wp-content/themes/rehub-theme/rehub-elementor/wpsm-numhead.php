<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Info box Widget class.
 *
 * 'NumHead' shortcode
 *
 * @since 1.0.0
 */
class Widget_NumHead extends Widget_Base {

	/* Widget Name */
	public function get_name() {
		return 'wpsm-numhead';
	}

	/* Widget Title */
	public function get_title() {
		return esc_html__('Numbered Heading', 'rehub-theme');
	}

    public function get_style_depends() {
        return [ 'rhnumbox' ];
    }

	/* Widget Icon */
	public function get_icon() {
		return 'eicon-counter-circle';
	}

	/* Theme Category */
	public function get_categories() {
		return [ 'helpler-modules' ];
	}

	/* Widget Keywords */
	public function get_keywords() {
		return [ 'heading' ];
	}

	/* Widget Controls */
	protected function register_controls() {

		$this->start_controls_section(
			'section_control_NumHead',
			[
				'label' => esc_html__('Control', 'rehub-theme'),
			]
		);
		$this->add_control(
			'num',
			[
				'label' => esc_html__( 'Number', 'rehub-theme' ),
				'type' => Controls_Manager::NUMBER,
				'default' => '1',
			]
		);		
		$this->add_control(
			'heading',
			[
				'label' => esc_html__('Heading', 'rehub-theme'),
				'type' => Controls_Manager::SELECT,
				'default' => '2',
				'options' => [
					'1' => esc_html__('H1', 'rehub-theme'),
					'2' => esc_html__('H2', 'rehub-theme'),
					'3' => esc_html__('H3', 'rehub-theme'), 
					'4' => esc_html__('H4', 'rehub-theme'),
					'5' => esc_html__('H5', 'rehub-theme'),
					'6' => esc_html__('H6', 'rehub-theme'),
				]
			]
		);
        $this->add_control( 'color', [
            'label' => esc_html__( 'Color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#fb7203',
            'selectors' => [
                '{{WRAPPER}} .wpsm-numhead.wpsm-style1 span' => 'border-color: {{VALUE}}; color: {{VALUE}}',
            ],
        ]);	
        $this->add_control( 'colortext', [
            'label' => esc_html__( 'Color of text', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpsm-numhead h1, {{WRAPPER}} .wpsm-numhead h2, {{WRAPPER}} .wpsm-numhead h3, {{WRAPPER}} .wpsm-numhead h4, {{WRAPPER}} .wpsm-numhead h5, {{WRAPPER}} .wpsm-numhead h6' => 'color: {{VALUE}}',
            ],
        ]);	        			
		$this->add_control(
			'content',
			[
				'label' => esc_html__( 'Content', 'rehub-theme' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Heading text', 'rehub-theme' ),
			]
		);

		$this->end_controls_section();

	}
	
	/* Widget output Rendering */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?> 	
			<div class="wpsm-numhead wpsm-style1">
				<span><?php echo intval($settings['num']);?></span>				
				<h<?php echo esc_attr($settings['heading']);?> <?php echo ''.$this->get_render_attribute_string( "content" );?>><?php echo ''.$settings['content'];?></h<?php echo esc_attr($settings['heading']);?>>
			</div>
	   	<?php	
	}	

}
Plugin::instance()->widgets_manager->register( new Widget_NumHead );