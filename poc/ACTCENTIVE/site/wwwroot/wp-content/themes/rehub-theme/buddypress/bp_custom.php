<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

/* 
 * Default size of avatar
 */

if(!defined('BP_AVATAR_THUMB_WIDTH')){
	define ( 'BP_AVATAR_THUMB_WIDTH', 55 );
	define ( 'BP_AVATAR_THUMB_HEIGHT', 55 );
	define ( 'BP_AVATAR_FULL_WIDTH', 150 );
	define ( 'BP_AVATAR_FULL_HEIGHT', 150 );	
}

 
/*
 * BP callback for the cover image feature.
 */
 if( ! function_exists( 'rh_cover_image_callback' ) ) :
	function rh_cover_image_callback( $params = array() ) {
		if ( empty( $params ) ) {
			return;
		}

		if(!empty($params['cover_image'])){
			$cover_image = 'background-image:url(' . $params['cover_image'] . ');';
		}
		elseif(rehub_option('rehub_bpheader_image') !=''){
			$cover_image = 'background-image:url(' . esc_url(rehub_option('rehub_bpheader_image')) . ');';
		}
		else{
			$cover_image = "background: #3a6186; background: -webkit-linear-gradient(to right, #89253e, #3a6186); background: linear-gradient(to right, #89253e, #3a6186);";
		}
		return '
			/* Cover image */
			#rh-header-cover-image {'. $cover_image .'}';
	}
endif;

/* Call BP cover-image styles in head */
if( ! function_exists( 'rh_cover_image_css' ) ) :
	function rh_cover_image_css( $settings = array() ) {

		// If you are using a child theme, use bp-child-css as the theme handel

		$theme_handle = (is_rtl()) ? 'bp-parent-css-rtl' : 'bp-parent-css';
 		$settings['width']  = 1400;
        $settings['height'] = 260;	 
		$settings['theme_handle'] = $theme_handle;
		$settings['callback'] = 'rh_cover_image_callback';
	 
		return $settings;
	}
	add_filter( 'bp_before_members_cover_image_settings_parse_args', 'rh_cover_image_css', 10, 1 );
	add_filter( 'bp_before_groups_cover_image_settings_parse_args', 'rh_cover_image_css', 10, 1 );
endif;


/* Custom Tabs for User`s Profile */
if( ! function_exists( 'rh_content_setup_nav_profile' ) ) :
	function rh_content_setup_nav_profile() {
		if(rehub_option('rh_bp_user_post_name') !=''){
			global $bp;
			$userid = (!empty($bp->displayed_user->id)) ? $bp->displayed_user->id : '';
			if($userid){
				$totalposts = count_user_posts( $userid, $post_type = 'post' );	
				$class    = ( 0 === $totalposts ) ? 'no-count' : 'count';
			}	
			else {
				$class = 'hiddencount';
				$totalposts = '';
			}
			$post_name = rehub_option('rh_bp_user_post_name');
			$post_slug = rehub_option('rh_bp_user_post_slug');
			$post_position = rehub_option('rh_bp_user_post_pos');
			$addnewpage = rehub_option('rh_bp_user_post_newpage');
			$editpage = rehub_option('rh_bp_user_post_editpage');
			$member_type = rehub_option('rh_bp_user_post_type');						

			$post_slug = ($post_slug) ? trim($post_slug) : 'posts';
			$post_position = ($post_position) ? trim((int)$post_position) : 40 ;
			$member_type = ($member_type) ? trim($member_type) : '';
			
			$cssid = 'posts';
			if($member_type){
				$cssid = 'hiddenposts';
				$usertype = bp_get_member_type($userid, false);
				if(!empty($usertype) && is_array($usertype)){
					$member_type = explode(',', $member_type);
					foreach ($member_type as $type) {
						$type = trim($type);
						if (in_array($type, $usertype)){
							$cssid = 'posts';
							break;
						}
					}
				}
			}

			$post_text = $post_name .' <span class="'.$class.'">'.$totalposts.'</span>'; 

			bp_core_new_nav_item( array(
				'name' => $post_text,
				'slug' => $post_slug,
				'screen_function' => 'articles_screen_link',
				'position' => $post_position,
				'default_subnav_slug' => $post_slug,
				'item_css_id' => $cssid,
			) );
			if($addnewpage){
				bp_core_new_subnav_item( array(
					'name' => esc_html__('Add new', 'rehub-theme'),
					'slug' => 'addnew',
					'parent_url' => untrailingslashit($bp->displayed_user->domain) . '/'. $post_slug.'/',
					'parent_slug' => $post_slug,
					'screen_function' => 'articles_screen_link_addnew',
					'position' => 20,
					'user_has_access' => bp_is_my_profile(),
				) );				
			}
			if($editpage){
				bp_core_new_subnav_item( array(
					'name' => esc_html__('Edit', 'rehub-theme'),
					'slug' => 'editposts',
					'parent_url' => untrailingslashit($bp->displayed_user->domain) . '/'. $post_slug.'/',
					'parent_slug' => $post_slug,
					'screen_function' => 'articles_screen_link_edit',
					'position' => 30,
					'user_has_access' => bp_is_my_profile(),
				) );				
			}						
		}	
		if(rehub_option('rh_bp_user_product_name') !=''){
			global $bp;
			$userid = (!empty($bp->displayed_user->id)) ? $bp->displayed_user->id : '';
			if($userid){
				$totalposts = count_user_posts( $userid, $post_type = 'product' );	
				$class    = ( 0 === $totalposts ) ? 'no-count' : 'count';
			}	
			else {
				$class = 'hiddencount';
				$totalposts = '';
			}			
			$post_name = rehub_option('rh_bp_user_product_name');
			$post_slug = rehub_option('rh_bp_user_product_slug');
			$post_position = rehub_option('rh_bp_user_product_pos');
			$addnewpage = rehub_option('rh_bp_user_product_newpage');
			$editpage = rehub_option('rh_bp_user_product_editpage');
			$member_type = rehub_option('rh_bp_user_product_type');						

			$post_slug = ($post_slug) ? trim($post_slug) : 'offers';
			$post_position = ($post_position) ? trim((int)$post_position) : 41 ;
			$member_type = ($member_type) ? trim($member_type) : '';

			$cssid = 'products';	
			if($member_type){
				$cssid = 'hiddenproducts';
				$usertype = bp_get_member_type($userid, false);
				if(!empty($usertype) && is_array($usertype)){
					$member_type = explode(',', $member_type);
					foreach ($member_type as $type) {
						$type = trim($type);
						if (in_array($type, $usertype)){
							$cssid = 'products';
							break;
						}
					}
				}
			}			

			$post_text = $post_name .' <span class="'.$class.'">'.$totalposts.'</span>';

			bp_core_new_nav_item( array(
				'name' => $post_text,
				'slug' => $post_slug,
				'screen_function' => 'deals_screen_link',
				'position' => $post_position,
				'default_subnav_slug' => $post_slug,
				'item_css_id' => $cssid,
			) );
			if($addnewpage){
				bp_core_new_subnav_item( array(
					'name' => esc_html__('Add new', 'rehub-theme'),
					'slug' => 'addnew',
					'parent_url' => untrailingslashit($bp->displayed_user->domain) . '/'. $post_slug.'/',
					'parent_slug' => $post_slug,
					'screen_function' => 'deals_screen_link_addnew',
					'position' => 20,
					'user_has_access' => bp_is_my_profile(),
				) );				
			}
			if($editpage){
				bp_core_new_subnav_item( array(
					'name' => esc_html__('Edit', 'rehub-theme'),
					'slug' => 'editproducts',
					'parent_url' => untrailingslashit($bp->displayed_user->domain) . '/'. $post_slug.'/',
					'parent_slug' => $post_slug,
					'screen_function' => 'deals_screen_link_edit',
					'position' => 30,
					'user_has_access' => bp_is_my_profile(),
				) );				
			}
		}		

	do_action( 'rh_content_setup_nav_profile' );
	}
	add_action( 'bp_setup_nav', 'rh_content_setup_nav_profile' );
endif;

if(!function_exists('articles_screen_link')){
function articles_screen_link() {
	
	function articles_screen_content() {
		$displayeduser = bp_displayed_user_id();
		?>
		<div id="posts-list" class="bp-post-wrapper posts">
			<?php
			$price_meta = rehub_option('price_meta_grid');
			$disable_btn = (rehub_option('rehub_enable_btn_recash') == 1) ? 0 : 1;
			$disable_act = (rehub_option('disable_grid_actions') == 1) ? 1 : 0;
			$aff_link = (rehub_option('disable_inner_links') == 1) ? 1 : 0;
			?>
			<?php    
			    $columns = (rehub_option('width_layout') =='extended') ? '4_col' : '3_col';
				$args = array(
					'user_id' => $displayeduser,
					'show' => 12,
					'columns' => $columns,
					'enable_pagination' => '2'
					);
		        if (rehub_option('archive_layout') == 'blog') { 
		            echo wpsm_regular_blog_loop_shortcode($args);                  
		        }
		        elseif (rehub_option('archive_layout') == 'newslist') { 
		        	$args['type'] = '2';
		            echo wpsm_small_thumb_loop_shortcode($args);                  
		        }  
		        elseif (rehub_option('archive_layout') == 'communitylist') { 
		            echo wpsm_small_thumb_loop_shortcode($args);                  
		        } 
		        elseif (rehub_option('archive_layout') == 'deallist') { 
		            echo wpsm_offer_list_loop_shortcode($args);                  
		        }
				elseif (rehub_option('archive_layout') == 'grid' || rehub_option('archive_layout') == 'gridfull'){
				     echo wpsm_grid_loop_mod_shortcode($args);
				}  
				elseif (rehub_option('archive_layout') == 'columngrid' || rehub_option('archive_layout') == 'columngridfull'){
				     echo wpsm_columngrid_loop_shortcode($args);
				}
				elseif (rehub_option('archive_layout') == 'compactgrid' || rehub_option('archive_layout') == 'compactgridfull'){
				     echo wpsm_compactgrid_loop_shortcode($args);
				}
				elseif (rehub_option('archive_layout') == 'dealgrid' || rehub_option('archive_layout') == 'dealgridfull'){
				     echo wpsm_compactgrid_loop_shortcode($args);
				}
				elseif (rehub_option('archive_layout') == 'cardblog' || rehub_option('archive_layout') == 'cardblogfull'){
				     echo wpsm_colorgrid_shortcode($args);
				}          
		        else{
		            echo wpsm_compactgrid_loop_shortcode($args);           
		        }
			?>

		</div><!--/.posts-->
	<?php
	} 
	
    add_action( 'bp_template_content', 'articles_screen_content' );
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
}

if(!function_exists('articles_screen_link_addnew')){
function articles_screen_link_addnew() {
	function articles_screen_link_addnew_content() {
		$get_pageid = rehub_option('rh_bp_user_post_newpage');
		if($get_pageid){
			$get_page = get_post((int)$get_pageid);
			$content = $get_page->post_content;
			$content = apply_filters('the_content', $content);
			echo '<div class="post">'.$content.'</div>';
		}
	} 
	
    add_action( 'bp_template_content', 'articles_screen_link_addnew_content' );	
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
}

if(!function_exists('articles_screen_link_edit')){
function articles_screen_link_edit() {
	function articles_screen_link_edit_content() {
		$get_pageid = rehub_option('rh_bp_user_post_editpage');
		if($get_pageid){
			$get_page = get_post((int)$get_pageid);
			$content = $get_page->post_content;
			$content = apply_filters('the_content', $content);
			echo '<div class="post">'.$content.'</div>';
		}
	} 
	
    add_action( 'bp_template_content', 'articles_screen_link_edit_content' );	
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
}

if(!function_exists('deals_screen_link')){
function deals_screen_link() {
	function deals_screen_content() {
		if ( class_exists( 'Woocommerce' ) ) {
			$displayeduser = bp_displayed_user_id();			
		?>	
		<div id="posts-list" class="bp-post-wrapper woocommerce products">

			<?php    
			    $columns = (rehub_option('width_layout') =='extended') ? '4_col' : '3_col';
				$args = array(
					'user_id' => $displayeduser,
					'show' => 12,
					'columns' => $columns,
					'enable_pagination' => '2'
					);
				$current_design = rehub_option('woo_design');
		        if ($current_design == 'grid') { 
		            echo wpsm_woogrid_shortcode($args);                  
		        }
		        elseif ($current_design == 'gridtwo') { 
		            $args['gridtype'] = 'compact';
		            echo wpsm_woogrid_shortcode($args);                  
		        }  
		        elseif ($current_design == 'gridrev') { 
		            $args['gridtype'] = 'review';
		            echo wpsm_woogrid_shortcode($args);                  
		        } 
		        elseif ($current_design == 'griddigi') { 
		            $args['gridtype'] = 'digital';
		            echo wpsm_woogrid_shortcode($args);                  
		        }
		        elseif ($current_design == 'deallist') { 
		            echo wpsm_woolist_shortcode($args);                  
		        }
				elseif ($current_design == 'list'){
				     echo wpsm_woorows_shortcode($args);
				}             
		        else{
		            echo wpsm_woocolumns_shortcode($args);           
		        }
			?>

		</div><!--/.posts-->
		<?php
		}
	} 	
    add_action( 'bp_template_content', 'deals_screen_content' );	
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
}

if(!function_exists('deals_screen_link_addnew')){
function deals_screen_link_addnew() {
	function deals_screen_link_addnew_content() {
		$get_pageid = rehub_option('rh_bp_user_product_newpage');
		if($get_pageid){
			$get_page = get_post($get_pageid);
			$content = $get_page->post_content;
			$content = apply_filters('the_content', $content);
			echo '<div class="post">'.$content.'</div>';
		}
	} 	
    add_action( 'bp_template_content', 'deals_screen_link_addnew_content' );	
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
}

if(!function_exists('deals_screen_link_edit')){
function deals_screen_link_edit() {
	function deals_screen_link_edit_content() {
		$get_pageid = rehub_option('rh_bp_user_product_editpage');
		if($get_pageid){
			$get_page = get_post($get_pageid);
			$content = $get_page->post_content;
			$content = apply_filters('the_content', $content);
			echo '<div class="post">'.$content.'</div>';
		}
	} 
	
    add_action( 'bp_template_content', 'deals_screen_link_edit_content' );	
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
}

/**
Example of Group Extension API
if ( bp_is_active( 'groups' ) && rehub_option('rehub_bp_group_products') !='') :
class Group_Extension_Offers extends BP_Group_Extension {

    function __construct() {
        $args = array(
            'slug' => 'products',
            'name' => 'products',
            'nav_item_position' => 105,
            'nav_item_name' => rehub_option('rehub_bp_group_products'),
        );
        parent::init( $args );
    }
 
    function display( $group_id = NULL ) {
        $creator = bp_get_group_creator_id();
        $totaldeals = count_user_posts( $creator, $post_type = 'product' );
        if($totaldeals > 0){
        	include(rh_locate_template('buddypress/groups/single/offers.php'));
        }
    }
}
bp_register_group_extension( 'Group_Extension_Offers' );
endif;
**/

/* Get the Resized Cover Image URL otherwise Background styled URL */
if( ! function_exists( 'rh_cover_image_url' ) ) :
	function rh_cover_image_url( $object_dir, $height, $background = false ) {

		if( $object_dir == 'members' ) {
			$item_id = bp_get_member_user_id(); 
		} elseif( $object_dir == 'groups' ) {
			$item_id = bp_get_group_id();
		} else {
			$item_id = 0;
		}
		
		$get_cover_image_url = bp_attachments_get_attachment('url', array(
			'object_dir' => $object_dir,
			'item_id' => $item_id
		) );
		if(!empty($get_cover_image_url)){
		}
		elseif(rehub_option('rehub_bpheader_image') !=''){
			$get_cover_image_url = esc_url(rehub_option('rehub_bpheader_image'));
		}	
		$resized_cover_image_url = '';
		
		if( $get_cover_image_url ) {
			
 		$resized_cover_image = new WPSM_image_resizer();
		$resized_cover_image->src = $get_cover_image_url;
        $resized_cover_image->height = $height;

		$resized_cover_image_url = $resized_cover_image->get_resized_url(); 
		}
		
		if( $background && $resized_cover_image_url ) {
			echo 'background-image:url('. $resized_cover_image_url .')';
		} else {
			echo ''.$resized_cover_image_url;
		}
	}
endif;


if( ! function_exists( 'rh_nologedin_add_buttons' ) ) :
	function rh_nologedin_add_buttons() {
		if( ! is_user_logged_in() && rehub_option('userlogin_enable') == '1') {
		?>
			<?php if(bp_is_active( 'friends' )) :?>
			<div class="generic-button">
				<a href="#" title="Add Friend" rel="add" class="act-rehub-login-popup friendship-button"><?php esc_html_e( 'Add Friend', 'rehub-theme' ); ?></a>
			</div>
			<?php endif;?>
			<?php if(bp_is_active( 'messages' )) :?>
			<div class="generic-button">
				<a href="#" title="Send a private message to this user." class="act-rehub-login-popup send-message"><?php echo esc_html__( 'Private Message', 'rehub-theme' ); ?></a>
			</div>
			<?php endif;?>
		<?php
		}
	}
	add_action( 'bp_member_header_actions', 'rh_nologedin_add_buttons', 10, 0 );
endif;

if( ! function_exists( 'rh_nologedin_add_buttons_group' ) ) :
	function rh_nologedin_add_buttons_group() {
		if( ! is_user_logged_in() && rehub_option('userlogin_enable') == '1') {
		?>
			<div class="generic-button">
				<a href="#" title="Join Group" rel="add" class="act-rehub-login-popup"><?php echo esc_html__( 'Join Group', 'rehub-theme' ); ?></a>
			</div>
		<?php
		}
	}
	add_action( 'bp_group_header_actions', 'rh_nologedin_add_buttons_group', 10, 0 );
endif;

if (rehub_option('bp_deactivateemail_confirm') != 'bp'){
	add_filter( 'bp_registration_needs_activation', '__return_false' );	
}

add_post_type_support( 'product', 'buddypress-activity' );
function rh_customize_product_tracking_args() {
    // Check if the Activity component is active before using it.
    if ( ! bp_is_active( 'activity' ) ) {
        return;
    }
 
    bp_activity_set_post_type_tracking_args( 'product', array(
        
        'action_id'                => 'new_product',
        'bp_activity_admin_filter' => esc_html__( 'Published a new product', 'rehub-theme' ),
        'bp_activity_front_filter' => esc_html__( 'Products', 'rehub-theme' ),
        'contexts'                 => array( 'activity', 'member' ),
        'activity_comment'         => true,
        'bp_activity_new_post'     => '%1$s '.esc_html__( 'posted a new', 'rehub-theme' ).' <a href="%2$s">'.esc_html__( 'product', 'rehub-theme' ).'</a>',
        'bp_activity_new_post_ms'  => '%1$s '.esc_html__( 'posted a new', 'rehub-theme' ).' <a href="%2$s">'.esc_html__( 'product', 'rehub-theme' ).'</a>,'.esc_html__( 'on the site', 'rehub-theme' ).' %3$s',
        'position'                 => 100,
    ) );
}
add_action( 'bp_init', 'rh_customize_product_tracking_args' );

add_filter('bp_get_messages_content_value', 'rh_custom_message_placeholder_in_bp_message' );
function rh_custom_message_placeholder_in_bp_message(){
	if(!empty($_GET['ref'])){
		$content = esc_html__('I am interested in: ', 'rehub-theme').urldecode($_GET['ref']);
		$content = esc_html($content);
	}
	elseif(!empty( $_POST['content'] )){
		$content = wp_kses_post($_POST['content']);
	}
	else{
		$content = '';
	}
	return $content;	
}

if (rehub_option('rh_bp_custom_message_profile') !=''){
	function rh_bp_custom_message_profile(){
		echo do_shortcode(rehub_option('rh_bp_custom_message_profile'));
		echo '<div class="mb30 clearfix"></div>';
	}
	add_action('bp_before_profile_loop_content', 'rh_bp_custom_message_profile' );
}

function rh_bp_custom_register_membertype(){
	?>
		<?php $membertype = (!empty($_GET['membertype'])) ? esc_html($_GET['membertype']) : '';?>
		<?php if ($membertype):?>
			<input name="activate_membertype_on_reg" type="hidden" value="<?php echo esc_attr($membertype);?>">
		<?php endif;?>
	<?php
}
function rh_bp_custom_register_membertype_action($user_id){
	$activate_membertype_on_reg = (!empty($_POST['activate_membertype_on_reg'])) ? esc_html($_POST['activate_membertype_on_reg']) : '';
	if($activate_membertype_on_reg){
		$all_membertypes = bp_get_member_types( array(), 'names' );
		if(is_array($all_membertypes) and array_key_exists($activate_membertype_on_reg, $all_membertypes)){
			add_user_meta( $user_id, '_rh_activate_membertype_on_reg', $activate_membertype_on_reg);
		}
	}

}
function rh_bp_custom_register_membertype_on_approve($user_id, $key, $user){	
	$membertype = get_user_meta( $user_id, '_rh_activate_membertype_on_reg', true);
	if($membertype){
		bp_set_member_type($user_id, $membertype, true );
	}
}
add_action('bp_signup_profile_fields', 'rh_bp_custom_register_membertype' );
add_action('bp_core_activated_user', 'rh_bp_custom_register_membertype_on_approve',10 , 3);
add_action( 'bp_core_signup_user', 'rh_bp_custom_register_membertype_action', 20, 1);

/*Disable Nouveau*/
function rh_bp_get_default_options( $options ){
    $options['_bp_theme_package_id'] = 'legacy';
    return $options;
}
add_filter( 'bp_get_default_options', 'rh_bp_get_default_options' );

function rh_bp_get_theme_package_id( $theme_id ){
    update_option( '_bp_theme_package_id', 'legacy', 'yes' );
    return $theme_id = 'legacy';
}
add_filter( 'bp_get_theme_package_id', 'rh_bp_get_theme_package_id' );
add_filter('register_setting_args', 'rh_bp_change_theme_package', 10, 4);
function rh_bp_change_theme_package( $args, $defaults, $option_group, $option_name ){
    global $wp_settings_fields;
    unset( $wp_settings_fields['buddypress']['bp_main']['_bp_theme_package_id'] );
    if( $option_group == 'buddypres' && $option_name == '_bp_theme_package_id' ){
        unregister_setting( $option_group, $option_name, $args['sanitize_callback'] );
    }
    $legacy = get_option('_bp_theme_package_id');
    if( false == $legacy || 'nouveau' == $legacy ){
        update_option( '_bp_theme_package_id', 'legacy', 'yes' );
    }
    return $args;
}

if(class_exists('\CashbackTracker\application\models\OrderModel')){

	function cashback_point_bp_notification_component( $component_names = array() ) {
	 
	    // Force $component_names to be an array
	    if ( ! is_array( $component_names ) ) {
	        $component_names = array();
	    }
	 
	    // Add 'rh_cshbackcomponent' component to registered components array
	    array_push( $component_names, 'rh_cshbackcomponent' );
	 
	    // Return component's with 'cashbackcomponent' appended
	    return $component_names;
	}
	add_filter( 'bp_notifications_get_registered_components', 'cashback_point_bp_notification_component' );

	function cashback_point_format_bp_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
	    // New custom notifications
	    if ( 'rh_cshback_action' === $action ) {
	    
	        $order = \CashbackTracker\application\models\OrderModel::model()->findbyPk($item_id);
	        if(is_array($order)){
	     		list($amount, $currency) = \CashbackTracker\application\components\Commission::calculateCashback($order);
	    		$cashback = \CashbackTracker\application\helpers\CurrencyHelper::getInstance()->currencyFormat($amount, $currency);
	        }else{
	        	$cashback = '';
	        }

	    	$text = __('You got new cashback', 'rehub-theme').' - '.$cashback;

	    	$memberlink = bp_core_get_user_domain( $secondary_item_id).'notifications';
	        // WordPress Toolbar
	        if ( 'string' === $format ) {
	            $return = apply_filters( 'rh_cashback_point_format_filter', $text, $text, $memberlink, $item_id);
	        // Deprecated BuddyBar
	        } else {
	            $return = apply_filters( 'rh_cashback_point_format_filter', array(
	                'text' => $text,
	                'link' => $memberlink,
	            ), $text, $memberlink, $item_id);
	        }
	        
	        return $return;
	    }	    
	}
	add_filter( 'bp_notifications_get_notifications_for_user', 'cashback_point_format_bp_notifications', 10, 5 );


	add_action('cbtrkr_order_approve', 'rh_approvedcash_bp_notificator');
	function rh_approvedcash_bp_notificator($order)
	{
	    if(!empty($order) && function_exists('bp_notifications_add_notification')){
	    	$userid = $order['user_id'];
	    	$item_id = $order['id'];

		    bp_notifications_add_notification( array(
		        'user_id'           => $userid,
		        'item_id'           => $item_id,
		        'secondary_item_id' => $userid,
		        'component_name'    => 'rh_cshbackcomponent',
		        'component_action'  => 'rh_cshback_action',
		        'date_notified'     => bp_core_current_time(),
		        'is_new'            => 1,
		    ) );    	
	    }
	}

}