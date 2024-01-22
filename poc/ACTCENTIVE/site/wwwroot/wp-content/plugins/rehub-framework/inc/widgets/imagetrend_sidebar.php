<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: Trending image grid Widget
 */

add_action( 'widgets_init', 'rehub_postimagetrend_load_widget' );

function rehub_postimagetrend_load_widget() {
	register_widget( 'rehub_postimagetrend_widget' );
}

class rehub_postimagetrend_widget extends WP_Widget {

    function __construct() {
		$widget_ops = array( 'classname' => 'postimagetrend', 'description' => esc_html__('Widget that displays image grid posts. Use only in sidebar!', 'rehub-framework') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_postimagetrend' );
        parent::__construct('rehub_postimagetrend', esc_html__('ReHub: Image grid posts', 'rehub-framework'), $widget_ops, $control_ops );
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {
	extract( $args );

	/* Our variables from the widget settings. */
	$title = apply_filters('widget_title', $instance['title'] );
	$tags = $instance['tags'];
	$number = $instance['number'];
	if( !empty($instance['nohead']) ) $nohead = ' nohead';
	else $nohead = '';	
	if( !empty($instance['two']) ) $two = ' two_column';
	else $two = '';	
	global $post;
	
	if(!empty($tags)) :
		$query = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1, 'tag' => $tags);
	else :
		$query = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1);
	endif;	
	$loop = new WP_Query($query);
	
	/* Before widget (defined by themes). */
	echo ''.$before_widget;

	if ($loop->have_posts()) :

	/* Display the widget title if one was input (before and after defined by themes). */
	if ( $title )
		echo '<div class="title">' . $title . '</div>';
	?>
		<div class="postimagetrend<?php echo esc_attr($nohead); echo esc_attr($two);?>">
		<style scoped>
			.postimagetrend .title{padding: 8px 15px}
			.postimagetrend .wrap{ height: 220px; overflow: hidden; position: relative; margin: 0 0 15px 0}
			.postimagetrend .wrap img{ min-height: 220px; width: 100%}
			.postimagetrend .wrap h4{   text-shadow: 0 1px 1px #333; position: absolute; bottom: 0; left: 0; right: 0; color: #fff; padding: 5px 12px; z-index: 9}
			.postimagetrend .wrap a:after{position: absolute; z-index: 8; bottom: 0; left: 0; height: 66px; background-color:rgba(0,0,0,0.3);color:#FFFFFF!important;width:100%;background:linear-gradient(to bottom,rgba(0,0,0,0) 0%,rgba(0,0,0,0.15) 40%,rgba(0,0,0,0.4) 100%); content: ""}
			.postimagetrend .wrap:hover a:before { opacity: 0}
			.postimagetrend.nohead .wrap{border-bottom: 1px solid #ccc; margin: 0}
			.postimagetrend.two_column .wrap{ width: 50%; float: left; height: 150px;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc; margin: 0}
			.postimagetrend.two_column .wrap img{ min-height: 150px; width: 100%}
			.postimagetrend.two_column .wrap h4{ font-size: 13px; line-height: 15px; text-transform: none;}
			.postimagetrend.two_column{  border: 1px solid #ccc;overflow: hidden;border-right: none;border-bottom: none;}
			.postimagetrend.nohead .wrap h4, .postimagetrend.nohead .wrap a:after{ display: none;}
			.rtl .postimagetrend .wrap a:after{ right: 0; left: inherit; }
			.rtl .postimagetrend.two_column .wrap{ float: right; border-right: 0; border-left: 1px solid #ccc;}
			.rtl .postimagetrend.two_column{  border-left: 0; }
		</style>		
		<?php  while ($loop->have_posts()) : $loop->the_post(); ?>	
		<div class="wrap">
			<a href="<?php the_permalink();?>" class="view-link">
                <?php if(!empty($instance['two'])) : ?>
                    <?php $width_img = 166 ;?>
                <?php else : ?>
                    <?php $width_img = 336 ;?>
                <?php endif ; ?>
                <?php WPSM_image_resizer::show_static_resized_image(array('thumb'=> true, 'crop'=> true, 'width'=> $width_img, 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_336_220.png'));?>

				<h4><?php the_title();?></h4>
			</a>	
		</div>	
		<?php endwhile; ?>
		</div>
		<?php wp_reset_query(); ?>
		<?php endif; ?>
			
	<?php

	/* After widget (defined by themes). */
	echo ''.$after_widget;
}


	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['tags'] = strip_tags($new_instance['tags']);
		$instance['number'] = strip_tags( $new_instance['number'] );
		$instance['nohead'] = (!empty($new_instance['nohead'])) ? $new_instance['nohead'] : '';
		$instance['two'] =  (!empty($new_instance['two'])) ? $new_instance['two'] : '';
		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => esc_html__('Trending', 'rehub-framework'), 'number' => 6, 'tags' => '', 'nohead' => true, 'two' => false);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title of widget:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'title' ); ?>" name="<?php echo ''.$this->get_field_name( 'title' ); ?>" value="<?php echo ''.$instance['title']; ?>"  />
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'number' ); ?>"><?php esc_html_e('Number of posts to show:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'number' ); ?>" name="<?php echo ''.$this->get_field_name( 'number' ); ?>" value="<?php echo ''.$instance['number']; ?>" size="3" />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'tags' ); ?>"><?php esc_html_e('Enter tag slug:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'tags' ); ?>" name="<?php echo ''.$this->get_field_name( 'tags' ); ?>" value="<?php echo ''.$instance['tags']; ?>"  />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'nohead' ); ?>"><?php esc_html_e('Disable headings  ?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'nohead' ); ?>" name="<?php echo ''.$this->get_field_name( 'nohead' ); ?>" value="true" <?php if( $instance['nohead'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'two' ); ?>"><?php esc_html_e('Two column?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'two' ); ?>" name="<?php echo ''.$this->get_field_name( 'two' ); ?>" value="false" <?php if( $instance['two'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>				

	<?php
	}
}

?>