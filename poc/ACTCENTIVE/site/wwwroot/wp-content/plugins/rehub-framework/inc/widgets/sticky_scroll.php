<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: News Widget
 */

add_action( 'widgets_init', 'rehub_sticky_on_scroll_widget' );

function rehub_sticky_on_scroll_widget() {
	register_widget( 'rehub_sticky_on_scroll' );
}

class rehub_sticky_on_scroll extends WP_Widget {

    function __construct() {
		$widget_ops = array( 'classname' => 'stickyscroll_widget pb0', 'description' => esc_html__('Widget that sticks after sidebar scroll. Use only in sidebar!', 'rehub-framework') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_sticky_on_scroll' );
        parent::__construct('rehub_sticky_on_scroll', esc_html__('ReHub: Sticky on scroll', 'rehub-framework'), $widget_ops, $control_ops  );
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {
	extract( $args );

	/* Our variables from the widget settings. */
	$title = apply_filters('widget_title', $instance['title'] );
	if( function_exists('icl_t') )  $text_code = icl_t( 'rehub_theme' , 'widget_content_'.$this->id , $instance['text_code'] ); else $text_code = $instance['text_code'] ;
	$type = !empty($instance['type']) ? $instance['type'] : '';
	$postid = !empty($instance['postid']) ? explode(',', $instance['postid']) : '';

	if($type){
		if(is_singular($type)){
			/* Before widget (defined by themes). */
			echo ''.$before_widget;

			/* Display the widget title if one was input (before and after defined by themes). */
			if ( $title )
				echo '<div class="title">' . $title . '</div>';
			?>
			<?php echo do_shortcode( $text_code ); wp_enqueue_script('custom_scroll'); ?>
			<?php if( !empty($instance['autocontent']) ) {
				echo '<div class="border-lightgrey-double pb15 pl5 pr15 pt15 rehub-main-color-border whitebg">'.wpsm_contents_shortcode(array()).'</div>';
			}?>

				
			<?php

			/* After widget (defined by themes). */
			echo ''.$after_widget;
		}else{
			return false;
		}
	}
	elseif($postid){
		if (is_single($postid) || is_page($postid)){
			echo ''.$before_widget;
			if ( $title ){
				echo '<div class="title">' . $title . '</div>';
			}
			echo do_shortcode( $text_code ); wp_enqueue_script('custom_scroll');
			if( !empty($instance['autocontent']) ) {echo '<div class="border-lightgrey-double pb15 pl5 pr15 pt15 rehub-main-color-border whitebg">'.wpsm_contents_shortcode(array()).'</div>';}
			echo ''.$after_widget;				
		}else{
			return false;
		}
	}
	else{
		/* Before widget (defined by themes). */
		echo ''.$before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo '<div class="title">' . $title . '</div>';
		?>
		<?php echo do_shortcode( $text_code ); wp_enqueue_script('custom_scroll'); ?>
		<?php if( !empty($instance['autocontent']) ) {echo '<div class="border-lightgrey-double pb15 pl5 pr15 pt15 rehub-main-color-border whitebg">'.wpsm_contents_shortcode(array()).'</div>';}?>		

			
		<?php

		/* After widget (defined by themes). */
		echo ''.$after_widget;
	}
}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text_code'] = $new_instance['text_code'] ;

		$instance['type'] = ( isset( $new_instance['type'] ) ) ? strip_tags( $new_instance['type'] ) : '';
		$instance['postid'] = ( isset( $new_instance['postid'] ) ) ? strip_tags( $new_instance['postid'] ) : '';

		$instance['autocontent'] = (!empty($new_instance['autocontent'])) ? strip_tags( $new_instance['autocontent'] ) : '';

		if (function_exists('icl_register_string')) {
			icl_register_string( 'rehub_theme' , 'widget_content_'.$this->id, $new_instance['text_code'] );
		}		

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'text_code' => '', 'type' => '', 'postid'=>'', 'autocontent'=>'');		
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p><em style="color:red;"><?php esc_html_e('Use this widget only once and only in sidebar area!', 'rehub-framework');?></em></p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title of widget:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'title' ); ?>" name="<?php echo ''.$this->get_field_name( 'title' ); ?>" value="<?php echo ''.$instance['title']; ?>"  />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'type' ); ?>"><?php esc_html_e('Type post type where to show content. This option shows content only on inner (singular) pages. For example, if you need to show only in Posts, use: post. For blog - type: blog', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'type' ); ?>" name="<?php echo ''.$this->get_field_name( 'type' ); ?>" value="<?php echo ''.$instance['type']; ?>"  />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'postid' ); ?>"><?php esc_html_e('Type post ID or slug if you want to have widget only there. You can set several ids with comma', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'postid' ); ?>" name="<?php echo ''.$this->get_field_name( 'postid' ); ?>" value="<?php echo ''.$instance['postid']; ?>"  />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'text_code' ); ?>"><?php esc_html_e('Text or Html code :', 'rehub-framework'); ?></label>
			<textarea rows="10" id="<?php echo ''.$this->get_field_id( 'text_code' ); ?>" name="<?php echo ''.$this->get_field_name( 'text_code' ); ?>" class="widefat" ><?php echo ''.$instance['text_code']; ?></textarea>
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'autocontent' ); ?>"><?php esc_html_e('Show Table of content for posts?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'autocontent' ); ?>" name="<?php echo ''.$this->get_field_name( 'autocontent' ); ?>" value="true" <?php if( $instance['autocontent'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>		


	<?php
	}
}

?>