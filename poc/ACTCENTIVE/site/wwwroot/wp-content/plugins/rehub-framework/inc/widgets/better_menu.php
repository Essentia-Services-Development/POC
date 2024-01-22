<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: News Widget
 */

add_action( 'widgets_init', 'rehub_better_menu_load_widget' );

function rehub_better_menu_load_widget() {
	register_widget( 'rehub_better_menu_widget' );
}

class rehub_better_menu_widget extends WP_Widget {

    function __construct() {
        $widget_ops = array( 'classname' => 'better_menu', 'description' => esc_html__('Widget displays menu in good way. ', 'rehub-framework') );
        $control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_better_menu' );
        parent::__construct('rehub_better_menu', esc_html__('ReHub: Better menu', 'rehub-framework'), $widget_ops, $control_ops );
    }


/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {
	extract( $args );

	/* Our variables from the widget settings. */
	$type = $colored = '';
	$title = apply_filters('widget_title', $instance['title'] );
	$icon = $instance['icon'];
	$type = $instance['type'];
	if ($type =='red' || $type =='red' || $type =='blue' || $type =='orange' || $type =='green' || $type =='violet') {$colored=' colored_menu_widget';}
	if ($icon =='heart') {
		$title_icon = '<i class="rhicon rhi-heart"></i>';
	} 
	elseif ($icon =='life-ring') {
		$title_icon = '<i class="rhicon rhi-life-ring"></i>';
	} 
	elseif ($icon =='diamond') {
		$title_icon = '<i class="rhicon rhi-diamond"></i>';
	}
	elseif ($icon =='flash') {
		$title_icon = '<i class="rhicon rhi-bolt"></i>';
	}
	elseif ($icon =='info') {
		$title_icon = '<i class="rhicon rhi-info-circle"></i>';
	}	
	elseif ($icon =='star') {
		$title_icon = '<i class="rhicon rhi-star-full"></i>';
	}
	else {$title_icon = '';}			 
	$nav_menu = wp_get_nav_menu_object( $instance['nav_menu'] ); // Get menu
	
	/* Before widget (defined by themes). */
	echo ''.$before_widget;

	echo '<div class="'.$type.'_menu_widget'.$colored.'">';
		echo '<style scoped>
		.widget.better_menu li{font-size: 14px;margin-bottom: 14px;padding-left: 12px;position: relative;}
		.widget.better_menu a{ color: #111}
		.widget.better_menu .title i{ padding-right: 8px;color: #F90000;}
		.widget.better_menu li:last-child{margin-bottom: 0}
		.widget.better_menu ul.sub-menu{ margin: 10px 0 10px 10px}
		.widget.better_menu ul.sub-menu li{font-size: 12px;margin-bottom: 6px;}
		.widget.better_menu .bordered_menu_widget{ padding: 15px; border: 1px solid #e3e3e3}
		.widget.better_menu .red_menu_widget{ background-color: #E1193A; }
		.widget.better_menu .green_menu_widget{ background-color: #84AE28; }
		.widget.better_menu .blue_menu_widget{ background-color: #1B8AE1; }
		.widget.better_menu .orange_menu_widget{ background-color: #fb7203; }
		.widget.better_menu .violet_menu_widget{ background-color: #9728C7; }
		.widget.better_menu .colored_menu_widget{padding: 20px;color: #fff}
		.widget.better_menu .colored_menu_widget a, .widget.better_menu .colored_menu_widget .title, .widget.better_menu .colored_menu_widget i, .widget.better_menu .colored_menu_widget li:before {color: #fff !important}
		.rtl .widget.better_menu li{ padding-left: 0px; padding-right: 12px;}
		.rtl .widget.better_menu .title i{ padding-right: 0px; padding-left: 8px;}
		.rtl .widget.better_menu ul.sub-menu{ margin: 10px 10px 10px 0px}
		</style>';

	/* Display the widget title if one was input (before and after defined by themes). */
	if ( $title )
		echo '<div class="title">' . $title_icon . $title . '</div>';		
	?>

	    <?php if (!empty ($nav_menu)) :?>
	    	<?php wp_nav_menu( array( 'fallback_cb' => '', 'menu' => $nav_menu, 'container' => false  ) );?>
	    <?php endif ;?>	

			
	<?php

	echo '</div>';

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
		$instance['icon'] = $new_instance['icon'];
		$instance['type'] = $new_instance['type'];
		$instance['nav_menu'] = (int) $new_instance['nav_menu'];

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$instance['title'] = isset( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';
		$instance['icon'] = isset( $instance['icon'] ) ? $instance['icon'] : 'none';
		$instance['type'] = isset( $instance['type'] ) ? $instance['type'] : 'simple';		
		
		// Get menus
		$menus = wp_get_nav_menus();

		// If no menus exists, direct the user to create some.
		if ( !$menus ) {
			echo '<p>'. sprintf( esc_html__('No menus have been created yet. <a href="%s">Create some</a>.', 'rehub-framework'), admin_url('nav-menus.php') ) .'</p>';
			return;
		}

		?>
		
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title of widget:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'title' ); ?>" name="<?php echo ''.$this->get_field_name( 'title' ); ?>" value="<?php echo ''.$instance['title']; ?>"  />
		</p>


		<p>
		<label for="<?php echo ''.$this->get_field_id('icon'); ?>"><?php esc_html_e('Icon before title:', 'rehub-framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('icon'); ?>" name="<?php echo ''.$this->get_field_name('icon'); ?>" style="width:100%;">
			<option value="none" <?php if ( 'none' == $instance['icon'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('No icon', 'rehub-framework');?></option>
			<option value="heart" <?php if ( 'heart' == $instance['icon'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Heart', 'rehub-framework');?></option>
			<option value="life-ring" <?php if ( 'life-ring' == $instance['icon'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Life Ring', 'rehub-framework');?></option>
			<option value="diamond" <?php if ( 'diamond' == $instance['icon'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Diamond', 'rehub-framework');?></option>
			<option value="flash" <?php if ( 'flash' == $instance['icon'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Flash', 'rehub-framework');?></option>
			<option value="info" <?php if ( 'info' == $instance['icon'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Info', 'rehub-framework');?></option>
			<option value="star" <?php if ( 'star' == $instance['icon'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Star', 'rehub-framework');?></option>
		</select>
		</p>

		<p>
		<label for="<?php echo ''.$this->get_field_id('type'); ?>"><?php esc_html_e('Design of widget box:', 'rehub-framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('type'); ?>" name="<?php echo ''.$this->get_field_name('type'); ?>" style="width:100%;">
			<option value="simple" <?php if ( 'simple' == $instance['type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Simple', 'rehub-framework');?></option>
			<option value="bordered" <?php if ( 'bordered' == $instance['type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Bordered', 'rehub-framework');?></option>
			<option value="red" <?php if ( 'red' == $instance['type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Red', 'rehub-framework');?></option>
			<option value="green" <?php if ( 'green' == $instance['type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Green', 'rehub-framework');?></option>
			<option value="blue" <?php if ( 'blue' == $instance['type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Blue', 'rehub-framework');?></option>
			<option value="orange" <?php if ( 'orange' == $instance['type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Orange', 'rehub-framework');?></option>
			<option value="violet" <?php if ( 'star' == $instance['type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Violet', 'rehub-framework');?></option>
		</select>
		</p>

		<p><label for="<?php echo ''.$this->get_field_id('nav_menu'); ?>"><?php esc_html_e('Select Menu:', 'rehub-framework'); ?></label>
			<select id="<?php echo ''.$this->get_field_id('nav_menu'); ?>" name="<?php echo ''.$this->get_field_name('nav_menu'); ?>">
				<option value="0"><?php esc_html_e( '&mdash; Select &mdash;', 'rehub-framework' ) ?></option>
		<?php
			foreach ( $menus as $menu ) {
				echo '<option value="' . $menu->term_id . '"'
					. selected( $nav_menu, $menu->term_id, false )
					. '>'. esc_html( $menu->name ) . '</option>';
			}
		?>
			</select>
		</p>				

	<?php
	}
}

?>