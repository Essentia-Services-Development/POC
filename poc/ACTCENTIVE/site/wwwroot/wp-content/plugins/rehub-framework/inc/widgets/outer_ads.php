<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: ADS Widget
 */

add_action( 'widgets_init', 'rehub_outer_mediad_widget' );

function rehub_outer_mediad_widget() {
	register_widget( 'rehub_outer_mediad' );
}

class rehub_outer_mediad extends WP_Widget {

    function __construct() {
		$widget_ops = array( 'classname' => 'outer_widget mt0 mb0 ml0 mr0 pt0 pb0 pl0 pr0', 'description' => esc_html__('Outcontent side ads widget.', 'rehub-framework') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_outer_mediad' );
        parent::__construct( 'rehub_outer_mediad', esc_html__('ReHub: Side Out Ads widget', 'rehub-framework'), $widget_ops, $control_ops);
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {
	extract( $args );

	/* Our variables from the widget settings. */
	$side = 'left';
	$fixed = 'fixed';
	if( !empty($instance['side']) ) $side = 'right';
	if( !empty($instance['fixed']) ) $fixed = 'absolute';
	$margin = $instance['margin'];	
	$width = $instance['width'];
	if ($side =='left') {
		if (rehub_option ('width_layout') == 'compact') {
			$position = - 560 - $width;
		}
		elseif (rehub_option ('width_layout') == 'mini') {
			$position = - 530 - $width;
		}
		else {
			$position = - 614 - $width;
		}	
	}
	if ($side =='right') {
		if (rehub_option ('width_layout') == 'compact') {
			$position = 560;
		}
		elseif (rehub_option ('width_layout') == 'mini') {
			$position = 530;
		}
		else {
			$position = 614;
		}
	}
	if( function_exists('icl_t') )  $text_code = icl_t( 'Widget content code' , 'widget_content_'.$this->id , $instance['text_code'] ); else $text_code = $instance['text_code'] ;

	/* Before widget (defined by themes). */
	echo ''.$before_widget;

	?>
	<div class="mediad outer_mediad_<?php echo esc_attr($side);?>" style="margin-left: <?php echo (int)$position;?>px; top: <?php echo (int)$margin;?>px; width: <?php echo (int)$width;?>px; position: <?php echo esc_attr($fixed);?>; left: 50%">
		<style scoped>
			@media screen and (max-width: 1279px) {
				.outer_mediad_left, .outer_mediad_right{display:none;}
			}			
		</style>
			<?php echo do_shortcode($text_code); ?>
	</div>

		
	<?php

	/* After widget (defined by themes). */
	echo ''.$after_widget;
}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['width'] = strip_tags( $new_instance['width'] );
		$instance['side'] = strip_tags( $new_instance['side'] );
		$instance['fixed'] = strip_tags( $new_instance['fixed'] );
		$instance['margin'] = strip_tags( $new_instance['margin'] );
		$instance['text_code'] = $new_instance['text_code'] ;

		if (function_exists('icl_register_string')) {
			icl_register_string( 'Widget content code' , 'widget_content_'.$this->id, $new_instance['text_code'] );
		}		

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'width' => 120, 'margin' => 250, 'side' => '', 'fixed' => '', 'text_code' => '');
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p><em style="color:red;"><?php esc_html_e('Use this widget only in sidebar area!', 'rehub-framework');?></em></p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'width' ); ?>"><?php esc_html_e('Width of ads (without px):', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'width' ); ?>" name="<?php echo ''.$this->get_field_name( 'width' ); ?>" value="<?php echo ''.$instance['width']; ?>"  />
		</p>		
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'side' ); ?>"><?php esc_html_e('Right Side ?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'side' ); ?>" name="<?php echo ''.$this->get_field_name( 'side' ); ?>" value="true" <?php if( $instance['side'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'fixed' ); ?>"><?php esc_html_e('Disable fixed position?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'fixed' ); ?>" name="<?php echo ''.$this->get_field_name( 'fixed' ); ?>" value="true" <?php if( $instance['fixed'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>		
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'margin' ); ?>"><?php esc_html_e('Margin from top of page (without px):', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'margin' ); ?>" name="<?php echo ''.$this->get_field_name( 'margin' ); ?>" value="<?php echo ''.$instance['margin']; ?>"  />
		</p>
		<p><em><?php esc_html_e('Note, if you disable fixed position, margin will be calculated from top of content area', 'rehub-framework');?></em></p>				
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'text_code' ); ?>"><?php esc_html_e('Text or Html code :', 'rehub-framework'); ?></label>
			<textarea rows="10" id="<?php echo ''.$this->get_field_id( 'text_code' ); ?>" name="<?php echo ''.$this->get_field_name( 'text_code' ); ?>" class="widefat" ><?php echo ''.$instance['text_code']; ?></textarea>
		</p>				


	<?php
	}
}

?>