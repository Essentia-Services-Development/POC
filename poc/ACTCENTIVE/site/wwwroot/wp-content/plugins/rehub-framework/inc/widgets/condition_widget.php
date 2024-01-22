<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: Show only in post
 */

add_action( 'widgets_init', 'rehub_conditional_widget_widget' );

function rehub_conditional_widget_widget() {
	register_widget( 'rehub_conditional_widget' );
}

class rehub_conditional_widget extends WP_Widget {

    function __construct() {
		$widget_ops = array( 'classname' => 'conditional_widget_widget', 'description' => esc_html__('Widget allows to show content only on single pages.', 'rehub-framework') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_conditional_widget' );
        parent::__construct('rehub_conditional_widget', esc_html__('ReHub: Show only in singular pages', 'rehub-framework'), $widget_ops, $control_ops  );
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {
	extract( $args );

	/* Our variables from the widget settings. */
	$title = apply_filters('widget_title', $instance['title'] );
	if( function_exists('icl_t') )  $text_code = icl_t( 'rehub_theme' , 'widget_content_'.$this->id , $instance['text_code'] ); else $text_code = $instance['text_code'] ;
	$type = !empty($instance['type']) ? $instance['type'] : 'post';
	$postid = !empty($instance['postid']) ? explode(',', $instance['postid']) : '';
	$reviewonly = (!empty($instance['reviewonly'])) ? $instance['reviewonly'] : '';

	if(is_singular($type)){
		if($postid){
			if (is_single($postid) || is_page($postid)){
				echo ''.$before_widget;
				if ( $title ){
					echo '<div class="title">' . $title . '</div>';
				}
				echo do_shortcode( $text_code );
				echo ''.$after_widget;				
			}else{
				return;
			}
		}else{
			if ($reviewonly){
				$reviewscore = get_post_meta(get_the_ID(), 'rehub_review_overall_score', true);
				if(!$reviewscore){
					return;
				}
			}
			echo ''.$before_widget;
			if ( $title ){
				echo '<div class="title">' . $title . '</div>';
			}
			echo do_shortcode( $text_code );
			echo ''.$after_widget;
		}
	}
}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['type'] = strip_tags( $new_instance['type'] );
		$instance['text_code'] = $new_instance['text_code'] ;
		$instance['postid'] = $new_instance['postid'] ;
		$instance['reviewonly'] = ( isset( $new_instance['reviewonly'] ) ) ? strip_tags( $new_instance['reviewonly'] ) : '';

		if (function_exists('icl_register_string')) {
			icl_register_string( 'rehub_theme' , 'widget_content_'.$this->id, $new_instance['text_code'] );
		}		

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'text_code' => '', 'type' => 'post', 'postid'=>'','reviewonly' => '');		
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title of widget:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'title' ); ?>" name="<?php echo ''.$this->get_field_name( 'title' ); ?>" value="<?php echo ''.$instance['title']; ?>"  />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'type' ); ?>"><?php esc_html_e('Type post type where to show content. Default is post.', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'type' ); ?>" name="<?php echo ''.$this->get_field_name( 'type' ); ?>" value="<?php echo ''.$instance['type']; ?>"  />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'postid' ); ?>"><?php esc_html_e('Type post ID or slug if you want to have widget only there. You can set several ids with comma', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'postid' ); ?>" name="<?php echo ''.$this->get_field_name( 'postid' ); ?>" value="<?php echo ''.$instance['postid']; ?>"  />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'reviewonly' ); ?>"><?php esc_html_e('Enable only in posts with review?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'reviewonly' ); ?>" name="<?php echo ''.$this->get_field_name( 'reviewonly' ); ?>" value="true" <?php if( $instance['reviewonly'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>						
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'text_code' ); ?>"><?php esc_html_e('Text or Html code :', 'rehub-framework'); ?></label>
			<textarea rows="10" id="<?php echo ''.$this->get_field_id( 'text_code' ); ?>" name="<?php echo ''.$this->get_field_name( 'text_code' ); ?>" class="widefat" ><?php echo ''.$instance['text_code']; ?></textarea>
		</p>		


	<?php
	}
}

?>