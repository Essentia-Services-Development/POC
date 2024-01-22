<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Info box Widget class.
 *
 * 'wpsm_box' shortcode
 *
 * @since 1.0.0
 */
class Widget_Wpsm_Box extends Widget_Base {

	/* Widget Name */
	public function get_name() {
		return 'wpsm-box';
	}

	/* Widget Title */
	public function get_title() {
		return esc_html__('Info box', 'rehub-theme');
	}

	/* Widget Icon */
	public function get_icon() {
		return 'eicon-alert';
	}

	/* Theme Category */
	public function get_categories() {
		return [ 'helpler-modules' ];
	}

	/* Widget Keywords */
	public function get_keywords() {
		return [ 'box', 'info', 'warning' ];
	}

	/* Widget Controls */
	protected function register_controls() {

		$this->start_controls_section(
			'section_control_wpsm_box',
			[
				'label' => esc_html__('Control', 'rehub-theme'),
			]
		);
		$this->add_control(
			'type',
			[
				'label' => esc_html__('Box type', 'rehub-theme'),
				'type' => Controls_Manager::SELECT,
				'default' => 'green',
				'options' => [
					'title' => esc_html__('Titlebox', 'rehub-theme'),
					'info' => esc_html__('Info', 'rehub-theme'),
					'warning' => esc_html__('Warning', 'rehub-theme'), 
					'error' => esc_html__('Error', 'rehub-theme'),
					'download' => esc_html__('Download', 'rehub-theme'),
					'green' => esc_html__('Green color box', 'rehub-theme'),
					'gray' => esc_html__('Gray color box', 'rehub-theme'),
					'blue' => esc_html__('Blue color box', 'rehub-theme'),
					'red' => esc_html__('Red color box', 'rehub-theme'),
					'yellow' => esc_html__('Yellow color box', 'rehub-theme'),
					'dashed_border' => esc_html__('Dashed', 'rehub-theme'),
					'solid_border' => esc_html__('Solid border', 'rehub-theme'),
				]
			]
		);
		$this->add_control(
			'title',
			[
				'label' => esc_html__('Title', 'rehub-theme'),
				'type' => Controls_Manager::TEXT,
				'default' => 'Title',
				'condition'   => [ 'type' => 'title' ],
			]
		);	
        $this->add_control( 'color', [
            'label' => esc_html__( 'Color', 'rehub-theme' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#fb7203',
            'condition'   => [ 'type' => 'title' ],
            'selectors' => [
                '{{WRAPPER}} .wpsm-titlebox' => 'border-color: {{VALUE}}',
                '{{WRAPPER}} .wpsm-titlebox > strong:first-child' => 'color: {{VALUE}}',
            ],
        ]);			
		$this->add_control(
			'float',
			[
				'label' => esc_html__('Box float', 'rehub-theme'),
				'type' => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none' =>  esc_html__('None', 'rehub-theme'),
					'left' => esc_html__('Left', 'rehub-theme'),
					'right' => esc_html__('Right', 'rehub-theme'),
				]
			]
		);
		$this->add_control(
			'textalign',
			[
				'label' => esc_html__('Text align', 'rehub-theme'),
				'type' => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left' => esc_html__('Left', 'rehub-theme'),
					'right' => esc_html__('Right', 'rehub-theme'),
					'justify' =>  esc_html__('Justify', 'rehub-theme'),
					'center' =>  esc_html__('Center', 'rehub-theme'),					
				]
			]
		);		
		$this->add_control(
			'content',
			[
				'label' => esc_html__( 'Content', 'rehub-theme' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Box Content', 'rehub-theme' ),
				'show_label' => false,
			]
		);

		$this->end_controls_section();

	}
	
	/* Widget output Rendering */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?> 	
			<?php $class = ($settings['type'] == 'title') ? 'wpsm-titlebox wpsm_style_main' : 'wpsm_box';?>
			<div class="<?php echo ''.$class;?> <?php echo ''.$settings['type'];?>_type <?php echo ''.$settings['float'];?>float_box" style="text-align:<?php echo ''.$settings['textalign'];?>;">				
				<?php if ($settings['type'] == 'title'):?>
					<strong><?php echo ''.$settings['title'];?></strong>
				<?php endif;?>
				<i></i>
				<div>
					<?php $mycontent = '<div '.$this->get_render_attribute_string( "content" ).'>'.$settings['content'].'</div>';?>
					<?php echo do_shortcode($mycontent);?>
				</div>
			</div>
	   	<?php	
	}

}
Plugin::instance()->widgets_manager->register( new Widget_Wpsm_Box );