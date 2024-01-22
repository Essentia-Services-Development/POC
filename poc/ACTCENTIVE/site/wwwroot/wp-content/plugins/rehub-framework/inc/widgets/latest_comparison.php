<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: News Widget
 */

add_action( 'widgets_init', 'rh_latest_compare_load_widget' );

function rh_latest_compare_load_widget() {
	register_widget( 'rh_latest_compare_widget' );
}

class rh_latest_compare_widget extends WP_Widget {

    function __construct() {
		$widget_ops = array( 'classname' => 'rh_latest_compare_widget top_offers', 'description' => esc_html__('Widget displays latest comparisons for woocommerce products, you must use shortcode [wpsm_woocharts] on comparison page to count products. Use only in sidebar!', 'rehub-framework') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rh_latest_compare' );
        parent::__construct('rh_latest_compare', esc_html__('ReHub: Latest Woo Comparisons', 'rehub-framework'), $widget_ops, $control_ops );
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {
	extract( $args );

	/* Our variables from the widget settings. */
	$title = apply_filters('widget_title', $instance['title'] );
	$number = (!empty($instance['number'])) ? absint($instance['number']) : 5;
	
	/* Before widget (defined by themes). */
	echo ''.$before_widget;

	/* Display the widget title if one was input (before and after defined by themes). */
	if ( $title )
		echo '<div class="title">' . $title . '</div>';
	?>
		<?php echo rh_generate_incss('widgettopoffers');?>
		<?php 

			$comparedarray = get_transient( 'rh_latest_compared_ids' );
			if(!empty($comparedarray)){
				wp_enqueue_style('rhversus');
				$comparedarray = array_slice($comparedarray, 0, $number);
				$latest = count($comparedarray) - 1;
				$comparebtn = '';
				if (rehub_option('compare_page') != '' || rehub_option('compare_multicats_textarea') != '') {
					$comparebtn = true;
				}
				foreach ($comparedarray as $key => $value) {
					$value = explode(',', $value);
					$posttype1 = get_post_type($value[0]);
					$posttype2 = get_post_type($value[1]);
					if($posttype1 != 'product' || $posttype2 != 'product'){
						continue;
					}
					echo '<div class="wpsm-versus-item">';

						echo '<div class="vs-1-col vs-conttext rh_deal_block">';
							echo '<a href="'.get_the_permalink($value[0]).'">';
							echo '<div class="mb10">';
								$image_id = get_post_thumbnail_id($value[0]);  
		  						$image_url = wp_get_attachment_image_src($image_id,'full');
								$image_url = (!empty($image_url)) ? $image_url[0] : '';

								WPSM_image_resizer::show_static_resized_image(array('src'=> $image_url, 'crop'=> false, 'height'=> 70, 'title'=> get_the_title($value[0]), 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_70_70.png'));
							echo '</div>';
							echo '<div class="cmpr-title fontnormal mb10 flowhidden">'.get_the_title((int)$value[0]).'</div>'; 
							echo '</a>'; 
							if($comparebtn){ 
                            	echo wpsm_comparison_button(array('class' => 'minicompare', 'id' => $value[0], 'label' => esc_html__('Compare', 'rehub-framework')));
                            } 							
						echo '</div>';

						echo '<div class="vs-circle-col"><div class="vs-circle">VS</div></div>';

						echo '<div class="vs-2-col vs-conttext rh_deal_block">';
							echo '<a href="'.get_the_permalink($value[1]).'">';
							echo '<div class="mb10">';
								$image_id = get_post_thumbnail_id($value[1]);  
		  						$image_url = wp_get_attachment_image_src($image_id,'full');
								$image_url = (!empty($image_url)) ? $image_url[0] : '';

								WPSM_image_resizer::show_static_resized_image(array('src'=> $image_url, 'crop'=> false, 'height'=> 70, 'title'=> get_the_title($value[1]), 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_70_70.png'));
							echo '</div>';
							echo '<div class="fontnormal mb10 flowhidden cmpr-title">'.get_the_title((int)$value[1]).'</div>';
							echo '</a>';
							if($comparebtn){ 
                            	echo wpsm_comparison_button(array('class' => 'minicompare', 'id' => $value[1], 'label' => esc_html__('Compare', 'rehub-framework')));
                            } 							
						echo '</div>';						

					echo '</div>';

					echo '<div class="mb20"></div>';

				}
			}

		?>
			
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
		$instance['number'] = strip_tags( $new_instance['number'] );

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => esc_html__('Latest Comparisons', 'rehub-framework'), 'number' => 5);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title of widget:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'title' ); ?>" name="<?php echo ''.$this->get_field_name( 'title' ); ?>" value="<?php echo ''.$instance['title']; ?>"  />
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'number' ); ?>"><?php esc_html_e('Number of products (maximum is 10)', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'number' ); ?>" name="<?php echo ''.$this->get_field_name( 'number' ); ?>" value="<?php echo ''.$instance['number']; ?>" size="3" />
		</p>					

	<?php
	}
}

?>