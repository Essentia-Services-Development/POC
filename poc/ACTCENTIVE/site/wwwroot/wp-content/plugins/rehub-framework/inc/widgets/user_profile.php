<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: News Widget
 */

add_action( 'widgets_init', 'rh_user_profile_load_widget' );

function rh_user_profile_load_widget() {
	register_widget( 'rh_user_profile_widget' );
}

class rh_user_profile_widget extends WP_Widget {

    function __construct() {
		$widget_ops = array( 'classname' => 'user-profile-div', 'description' => esc_html__('Widget that displays User Profile on Vendor Store page. Use only in vendor sidebar area!', 'rehub-framework') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rh_user_profile' );
        parent::__construct('rh_user_profile', esc_html__('ReHub: User Profile', 'rehub-framework'), $widget_ops, $control_ops );
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {
	global $WCFM, $WCFMmp;

	if ( ! wcfmmp_is_store_page() ) {
			return;
	}	
	extract( $args );

	/* Our variables from the widget settings. */
	$title = (!empty($instance['title'])) ? apply_filters('widget_title', $instance['title'] ) : '';
	$store_user   = wcfmmp_get_store( get_query_var( 'author' ) );
	$vendor = get_userdata( $vendor_id );
	$vendor_id = $store_user->get_id();
	$name = get_the_author_meta( 'display_name', $vendor_id );
	

	/* Display the widget title if one was input (before and after defined by themes). */

	?>	
		<div class="rh-cartbox widget">
			<div>
				<div class="widget-inner-title rehub-main-font"><?php echo ''.$title;?></div>
				<div class="profile-avatar text-center">
				<?php if ( function_exists('bp_displayed_user_avatar') ) : ?>
					<?php bp_displayed_user_avatar( 'type=full&width=110&height=110&&item_id='.$vendor_id ); ?>
				<?php else : ?>
					<?php echo get_avatar( $comment, 110, '', $name ); ?>
				<?php endif; ?>
				</div>
				<div class="profile-usertitle text-center mt20">
					<div class="profile-usertitle-name font110 fontbold mb20">
					<?php if ( function_exists('bp_core_get_user_domain') ) : ?>
						<a href="<?php echo bp_core_get_user_domain( $vendor_id ); ?>">
					<?php endif;?>
						<?php the_author_meta( 'nickname',$vendor_id); ?> 						
						<?php if ( function_exists('bp_core_get_user_domain') ) : ?></a><?php endif;?>
					</div>
				</div>
				<div class="lineheight25 margincenter mb10 profile-stats">
					<div class="pt5 pb5 pl10 pr10"><i class="rhicon rhi-user mr5 rtlml5"></i> <?php esc_html_e( 'Registration', 'rehub-framework' );  echo ': ' .date_i18n( get_option( "date_format" ), strtotime( $vendor->user_registered )); ?></div>
	                <div class="pt5 pb5 pl10 pr10"><i class="rhicon rhi-heartbeat mr5 rtlml5"></i><?php esc_html_e( 'Product Votes', 'rehub-framework' ); echo ': ' . $count_p_votes; ?></div>
	                <div class="pt5 pb5 pl10 pr10"><i class="rhicon rhi-briefcase mr5 rtlml5"></i><?php esc_html_e( 'Total submitted', 'rehub-framework' ); echo ': ' . $totaldeals; ?></div>
				</div>
				<?php if ( function_exists( 'mycred_get_users_badges' ) ) : ?>
	                <div class="profile-achievements mb15 text-center">
	                    <div class="pt5 pb5 pl10 pr10">
	                        <?php rh_mycred_display_users_badges( $vendor_id ) ?>
	                    </div>
	                </div>
            	<?php endif; ?>
	            <?php if ( function_exists('bp_core_get_user_domain') ) : ?>
	            	<?php if ( bp_is_active( 'xprofile' ) ) : ?>
						<?php if ( bp_has_profile( array( 'profile_group_id' => 1, 'fetch_field_data' => true, 'user_id'=>$vendor_id ) ) ) : while ( bp_profile_groups() ) : bp_the_profile_group(); ?>
							<?php $numberfields = explode(',', bp_get_the_profile_field_ids());?>
							<?php $count = (!empty($numberfields)) ? count($numberfields) : '';?>
							<?php $bp_profile_description = rehub_option('rh_bp_seo_description');?>
							<?php $bp_profile_phone = rehub_option('rh_bp_phone');	?>

							<?php if ($count > 1) :?>
								<ul id="xprofile-in-wcmstore" class="flowhidden">
									<?php $fieldid = 0; while ( bp_profile_fields() ) : bp_the_profile_field(); $fieldid++; ?>
										<?php if ($fieldid == 1) continue;?>
										<?php $fieldname = bp_get_the_profile_field_name();?>
										<?php if($fieldname == $bp_profile_phone) continue;?>
										<?php if($fieldname == $bp_profile_description) continue;?>
										<?php if ( bp_field_has_data() ) : ?>
											<li>
												<div class="floatleft mr5"><?php echo esc_attr($fieldname) ?>: </div>
												<div class="floatleft"><?php bp_the_profile_field_value() ?></div>	
											</li>
										<?php endif; ?>
									<?php endwhile; ?>
								</ul>
							<?php endif; ?>
						<?php endwhile; endif; ?>
	            	<?php endif;?>
				
	                <div class="profile-usermenu mt20">
	                    <ul class="user-menu-tab pt5 pb5 pl10 pr10" role="tablist">
	                        <li class="text-center">
	                            <a href="<?php echo bp_core_get_user_domain( $vendor_id ); ?>" class="position-relative blockstyle pt10 pb10 pl15 pr15"><i class="rhicon rhi-folder-open mr5 rtlml5"></i><?php esc_html_e( 'Show full profile', 'rehub-framework' ); ?></a>
	                        </li>
	                    </ul>
	                </div>
				<?php endif; ?>
            </div>	    		
		</div>	
	<?php

}


	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => esc_html__('Shop owner:', 'rehub-framework'));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p><em style="color:red;"><?php esc_html_e('Use only for vendor store widget area', 'rehub-framework');?></em></p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title of widget:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'title' ); ?>" name="<?php echo ''.$this->get_field_name( 'title' ); ?>" value="<?php echo ''.$instance['title']; ?>"  />
		</p>


	<?php
	}
}

?>