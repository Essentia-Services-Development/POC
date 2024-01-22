<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: News Widget
 */

add_action( 'widgets_init', 'rehub_top_offers_load_widget' );

function rehub_top_offers_load_widget() {
	register_widget( 'rehub_top_offers_widget' );
}

class rehub_top_offers_widget extends WP_Widget {

    function __construct() {
		$widget_ops = array( 'classname' => 'top_offers', 'description' => esc_html__('Widget displays top offers. Use only in sidebar!', 'rehub-framework') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_top_offers' );
        parent::__construct('rehub_top_offers', esc_html__('ReHub: Top Offers/Products', 'rehub-framework'), $widget_ops, $control_ops );
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {

	extract( $args );

	/* Our variables from the widget settings. */
	$title = apply_filters('widget_title', $instance['title'] );
	$tags = (!empty($instance['tags'])) ? $instance['tags'] : '';
	$order = (!empty($instance['order'])) ? $instance['order'] : '';
	$number = (!empty($instance['number'])) ? $instance['number'] : '';
	$post_type = (!empty($instance['post_type'])) ? $instance['post_type'] : '';
	$random = (!empty($instance['random'])) ? $instance['random'] : '';
	$notexpired = (!empty($instance['notexpired'])) ? $instance['notexpired'] : '';	
	$comparebtn = (!empty($instance['comparebtn'])) ? $instance['comparebtn'] : '';
	$orderby = (!empty($instance['orderby'])) ? $instance['orderby'] : '';
	
	/* Before widget (defined by themes). */
	echo ''.$before_widget;

	/* Display the widget title if one was input (before and after defined by themes). */
	if ( $title )
		echo '<div class="title mb25">' . $title . '</div>';
	?>
		<?php echo rh_generate_incss('widgettopoffers');?>
	    <?php if ($post_type == 'post') :?>
	    	<?php rehub_top_offers_widget_block_post($tags, $number, $order, $random, $orderby, $notexpired, $comparebtn);?>
	    <?php elseif ($post_type == 'woo' && class_exists('Woocommerce')):?>
	    	<?php rehub_top_offers_widget_block_woo($tags, $number, $order, $random, $orderby, $notexpired, $comparebtn);?>
	    <?php else : ?> 	              
	    	<?php rehub_top_offers_widget_block_post($tags, $number, $order, $random, $orderby, $notexpired, $comparebtn);?>
	    <?php endif ;?>	

			
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
		$instance['order'] = strip_tags($new_instance['order']);
		$instance['number'] = strip_tags( $new_instance['number'] );
		$instance['post_type'] = $new_instance['post_type'];
		$instance['random'] =  (!empty($new_instance['random'])) ? $new_instance['random'] : '';
		$instance['notexpired'] =  (!empty($new_instance['notexpired'])) ? $new_instance['notexpired'] : '';
		$instance['comparebtn'] =  (!empty($new_instance['comparebtn'])) ? $new_instance['comparebtn'] : '';
		$instance['orderby'] =  $new_instance['orderby'];

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => esc_html__('Top offers', 'rehub-framework'), 'number' => 5, 'tag' => '', 'post_type' => 'post', 'order' => '', 'tags' =>'', 'random' =>'', 'orderby'=> 'DESC', 'notexpired'=> '', 'comparebtn'=> '');
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
		<label for="<?php echo ''.$this->get_field_id('post_type'); ?>"><?php esc_html_e('Widget is based on:', 'rehub-framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('post_type'); ?>" name="<?php echo ''.$this->get_field_name('post_type'); ?>" style="width:100%;">
			<option value="post" <?php if ( 'post' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Posts', 'rehub-framework');?></option>
			<option value="woo" <?php if ( 'woo' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Woocommerce', 'rehub-framework');?></option>			
		</select>
		</p>

		<p><em><?php esc_html_e('If you select Widget base on posts or woocommerce, enter tag slug in field below. Also, you can set name of meta key for ordering or show random products', 'rehub-framework');?></em></p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'tags' ); ?>"><?php esc_html_e('Enter tag slug:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'tags' ); ?>" name="<?php echo ''.$this->get_field_name( 'tags' ); ?>" value="<?php echo ''.$instance['tags']; ?>"  />
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'order' ); ?>"><?php esc_html_e('Meta key name for ordering:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'order' ); ?>" name="<?php echo ''.$this->get_field_name( 'order' ); ?>" value="<?php echo ''.$instance['order']; ?>"  />
		</p>

		<p>
		<label for="<?php echo ''.$this->get_field_id('orderby'); ?>"><?php esc_html_e('Order for field above:', 'rehub-framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('orderby'); ?>" name="<?php echo ''.$this->get_field_name('orderby'); ?>" style="width:100%;">
			<option value="DESC" <?php if ( 'DESC' == $instance['orderby'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('DESC', 'rehub-framework');?></option>		
			<option value="ASC" <?php if ( 'ASC' == $instance['orderby'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('ASC', 'rehub-framework');?></option>		
		</select>
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'comparebtn' ); ?>"><?php esc_html_e('Show compare button instead offer button?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'comparebtn' ); ?>" name="<?php echo ''.$this->get_field_name( 'comparebtn' ); ?>" value="true" <?php if( $instance['comparebtn'] ) echo 'checked="checked"'; ?> type="checkbox" />
			<small>This works only if you enabled comparison page in theme option - dynamic comparison</small>
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'random' ); ?>"><?php esc_html_e('Show random?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'random' ); ?>" name="<?php echo ''.$this->get_field_name( 'random' ); ?>" value="true" <?php if( $instance['random'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'notexpired' ); ?>"><?php esc_html_e('Exclude expired?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'notexpired' ); ?>" name="<?php echo ''.$this->get_field_name( 'notexpired' ); ?>" value="true" <?php if( $instance['notexpired'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>						

	<?php
	}
}

?>