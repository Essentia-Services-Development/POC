<?php

class Marketkingpro_Admin{

	function __construct() {

		// Require WooCommerce notification
		add_action( 'admin_notices', array($this, 'marketkingpro_plugin_dependencies') );
		// Load admin notice resources (enables notification dismissal)
		add_action( 'admin_enqueue_scripts', array($this, 'load_global_admin_notice_resource') ); 
		// Allow shop manager to set plugin options
		add_filter( 'option_page_capability_marketkingpro', array($this, 'marketkingpro_options_capability' ) );
		// Require MarketKing Core with empty menu page
		add_action( 'admin_notices', array( $this, 'marketking_settings_page_core_requirement' ) ); 

		add_action( 'admin_menu', array( $this, 'marketking_license_container' ) ); 


		// How to use notices
		add_action( 'admin_notices', array($this, 'marketking_announcements_howto') );
		add_action( 'admin_notices', array($this, 'marketking_groups_howto') );
		add_action( 'admin_notices', array($this, 'marketking_grules_howto') );
		add_action( 'admin_notices', array($this, 'marketking_messages_howto') );
		add_action( 'admin_notices', array($this, 'marketking_commissionrules_howto') );
		add_action( 'admin_notices', array($this, 'marketking_abusereports_howto') );
		add_action( 'admin_notices', array($this, 'marketking_memberships_howto') );
		add_action( 'admin_notices', array($this, 'marketking_verifications_howto') );
		add_action( 'admin_notices', array($this, 'marketking_vitems_howto') );
		add_action( 'admin_notices', array($this, 'marketking_badges_howto') );
		add_action( 'admin_notices', array($this, 'marketking_refunds_howto') );
		add_action( 'admin_notices', array($this, 'marketking_roptions_howto') );
		add_action( 'admin_notices', array($this, 'marketking_rfields_howto') );
		add_action( 'admin_notices', array($this, 'marketking_sellerdocs_howto') );

		// filter to remove MarketKing in all API requests:
		require_once ( MARKETKINGPRO_DIR . 'includes/class-marketking-pro-helper.php' );
		$helper = new Marketkingpro_Helper();
		$run_in_api_requests = true;
		if (apply_filters('marketkingpro_force_cancel_api_requests', false)){
			if ($helper->marketkingpro_is_rest_api_request()){
				$run_in_api_requests = false;
			}
		}
		
		if ($run_in_api_requests){

			add_action( 'plugins_loaded', function(){
				
				// Load global admin styles
				add_action( 'admin_enqueue_scripts', array($this, 'load_global_admin_resources') ); 

				if ( class_exists( 'woocommerce' ) && defined('MARKETKINGCORE_DIR') ) {

					/* Load resources */
					// Only load scripts and styles in this specific admin page
					add_action( 'admin_enqueue_scripts', array($this, 'load_admin_resources') );

					if (intval(get_option( 'marketking_enable_registration_setting', 1 )) === 1){	
						/* Registration */
						// Register new post type, Custom Registration Options: marketking_option
						add_action( 'init', array($this, 'marketking_register_post_type_custom_option'), 0 );
						// Add metaboxes to custom options
						add_action( 'add_meta_boxes', array($this, 'marketking_option_metaboxes') );
						// Save custom options
						add_action('save_post', array($this, 'marketking_save_custom_option_metaboxes'), 10, 1);
						// Add custom columns to groups admin menu
						add_filter( 'manage_marketking_option_posts_columns', array($this, 'marketking_add_columns_custom_option_menu') );
						// Add groups custom columns data
						add_action( 'manage_marketking_option_posts_custom_column' , array($this, 'marketking_columns_custom_option_data'), 10, 2 );
						/* Registration Fields */
						// Register new post type, Custom Registration Fields: marketking_field
						add_action( 'init', array($this, 'marketking_register_post_type_custom_field'), 0 );
						// Add metaboxes to custom fields
						add_action( 'add_meta_boxes', array($this, 'marketking_field_metaboxes') );
						// Save metabox
						add_action('save_post', array($this, 'marketking_save_custom_field_metaboxes'), 10, 1);
						// Add custom columns to custom fields admin menu
						add_filter( 'manage_marketking_field_posts_columns', array($this, 'marketking_add_columns_custom_field_menu') );
						// Add custom fields custom columns data
						add_action( 'manage_marketking_field_posts_custom_column' , array($this, 'marketking_columns_custom_field_data'), 10, 2 );
					}			

					// hide non admin inquiries from admin backend by default
					if (apply_filters('hide_product_inquiries_backend', true)){
						add_filter('parse_query', array($this, 'hide_product_inquiries_backend'));
					}

					// hide non vendor approved refunds from admin backend
					if (apply_filters('hide_nonvendor_refunds_backend', true)){
						add_filter('parse_query', array($this, 'hide_nonvendor_refunds_backend'));
					}


					// b2bking integration
					if (defined('B2BKING_DIR') && intval(get_option('marketking_enable_b2bkingintegration_setting', 1)) === 1){

						// Add Settings
						add_action('b2bking_settings_panel_end_items', array($this, 'extend_b2bking_settings'));
						add_action('b2bking_settings_panel_end_items_tabs', array($this, 'extend_b2bking_settings_tabs'));

		                // Add custom columns to dynamic rules in admin menu
		                add_filter( 'manage_b2bking_rule_posts_columns', array($this, 'b2bking_add_columns_rule_menu'), 100 );

		            }

		            // Shipping tracking show backend admin that order has been received
		            if (intval(get_option('marketking_enable_shippingtracking_setting', 1)) === 1) {  
		            	if (intval(get_option( 'marketking_customers_mark_order_received_setting', 0 )) === 1){
		            		add_action('woocommerce_admin_order_data_after_order_details', array($this,'show_admin_backend_marked_received'));

		            	}
		            }

		            // hide credit product
		            add_filter('parse_query', array($this, 'marketking_hide_offer_post'));

		            

		            

				}
			});

		}
		
	}

	function marketking_hide_offer_post($query) {
		$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
		$credit_id = intval(get_option('b2bking_credit_product_id_setting', 0));
		$mkcredit_id = intval(get_option('marketking_credit_product_id_setting', 0));

		$current_exclude = $query->query_vars['post__not_in'];
		if (is_array($current_exclude)){
			$query->query_vars['post__not_in'] = array_merge(array($offer_id, $credit_id, $mkcredit_id), $current_exclude); //3225464 is deprecated
		} else {
        	$query->query_vars['post__not_in'] = array($offer_id, $credit_id, $mkcredit_id); //3225464 is deprecated
    	}
	}	

	function show_admin_backend_marked_received(){

		global $post;
		$order_id = $post->ID;
		$order = wc_get_order($order_id);
		$received = $order->get_meta('marked_received');
		if ($received === 'yes'){
			?>
			<p class="form-field form-field-wide">
				<?php
				echo '<div class="marketking_order_mark_received">'.esc_html__('The customer has marked this order as received.','marketking').'</div>';
				?>
			</p>
			<?php
		} else {
			
		}
	}

	function extend_b2bking_settings(){
	   	?>
	   	<a class="green item <?php echo $this->b2bking_isactivetab('marketkingsettings'); ?>" data-tab="marketkingsettings">
	   		<i class="shopping bag icon"></i>
	   		<div class="header"><?php esc_html_e('MarketKing Settings','marketking'); ?></div>
	   		<span class="b2bking_menu_description"><?php esc_html_e('MarketKing Integration Settings','marketking'); ?></span>
	   	</a>
	   	<?php
	   }

   function extend_b2bking_settings_tabs(){
   	?>
   	<div class="ui bottom attached tab segment <?php echo $this->b2bking_isactivetab('marketkingsettings'); ?>" data-tab="marketkingsettings">
   		<div class="b2bking_attached_content_wrapper">
   			<h2 class="ui block header">
   				<i class="shopping bag icon"></i>
   				<div class="content">
   					<?php esc_html_e('MarketKing Settings','marketking'); ?>
   					<div class="sub header">
   						<?php esc_html_e('Set B2BKing for MarketKing Settings','marketking'); ?>
   					</div>
   				</div>
   			</h2>
   			<table class="form-table">
   				<?php do_settings_fields( 'b2bking', 'b2bking_marketkingsettings_section' ); ?>
   			</table>
   		</div>
   	</div>
   	<?php
   }

   function b2bking_isactivetab($tab){
   	$gototab = get_option( 'b2bking_current_tab_setting', 'accessrestriction' );
   	if ($tab === $gototab){
   		return 'active';
   	} 
   }

   // Add custom columns to Dynamic Rules menu
   function b2bking_add_columns_rule_menu($columns) {

       $columns_initial = $columns;
   
       // rename title
       $columns = array(
           'author' => esc_html__( 'Rule Author', 'marketking' )
       );

       $columns = array_slice($columns_initial, 0, count($columns_initial), true) + $columns;

       return $columns;
   }

   	public static function hide_nonvendor_refunds_backend($query){
   		if (apply_filters('hide_nonvendor_refunds_backend', true)){

	   		if ($query->get('post_type') === 'marketking_refund'){
				if (empty($query->get('meta_query'))){
			        $meta_query[] = array(
			                'key'     => 'request_status',
			                'value'	  => 'approved',
			                'compare' => '=',
			        );    

			        // Set the meta query to the complete, altered query
			        $query->set('meta_query',$meta_query);
			    }
			}
		}
   	}

	public static function hide_product_inquiries_backend($query){

		if ($query->get('post_type') === 'marketking_message'){
			
	        $meta_query[] = array(
	                'key'     => 'customer_query_non_admin',
	                'compare' => 'NOT EXISTS',
	        );    

	        if (apply_filters('hide_product_inquiries_backend', true)){
		        // Set the meta query to the complete, altered query
		        $query->set('meta_query',$meta_query);
		    }
		}
	}

	public function marketking_license_container(){

		// Build plugin file path relative to plugins folder
		$absolutefilepath = dirname(plugins_url('', __FILE__),1);
		$pluginsurllength = strlen(plugins_url())+1;
		$relativepath = substr($absolutefilepath, $pluginsurllength);

		// plugin licensing message
		add_action( 'after_plugin_row_'.$relativepath.'/marketking-pro.php', array($this, 'marketking_licensing_message'), 10, 3 );
	}

	function marketking_licensing_message(){
		$license = get_option('marketking_license_key_setting', '');
		$email = get_option('marketking_license_email_setting', '');
		$info = parse_url(get_site_url());
		$host = $info['host'];
		$host_names = explode(".", $host);

		if (isset($host_names[count($host_names)-2])){ // e.g. if not on localhost, xampp etc

			$bottom_host_name = $host_names[count($host_names)-2] . "." . $host_names[count($host_names)-1];

			if (strlen($host_names[count($host_names)-2]) <= 3){    // likely .com.au, .co.uk, .org.uk etc
			    $bottom_host_name_new = $host_names[count($host_names)-3] . "." . $host_names[count($host_names)-2] . "." . $host_names[count($host_names)-1];
			    // legacy, do not deactivate existing sites
			    /*
			    if (get_option('pluginactivation_'.$email.'_'.$license.'_'.$bottom_host_name) === 'active' && get_option('marketking_use_legacy_activation', 'yes') === 'yes'){
			        // old activation active, proceed with old activation
			    } else {
			        $bottom_host_name = $bottom_host_name_new;
			    }
			    */

			    // new, overwrite legacy, just use new one
			    $bottom_host_name = $bottom_host_name_new;

			}


			$activation = get_option('pluginactivation_'.$email.'_'.$license.'_'.$bottom_host_name);

			if ($activation == 'active'){
				?>
			    <tr class="plugin-update-tr installer-plugin-update-tr active marketking-container-status-active">
			    	<td colspan="4" class="plugin-update colspanchange marketking-container-active">
			    		<div class="update-message notice inline notice-marketking-active">
			    			<p class="installer-q-icon"><?php 
			    				esc_html_e('Your MarketKing Pro license is valid and active. You are receiving plugin updates.','marketking');
			    				?><a class="marketking_manage_license" href="<?php echo esc_attr(admin_url('admin.php?page=marketking&tab=activate'));?>"><?php esc_html_e('Manage license.','marketking');?></a></p>
			    		</div>
			    	</td>
			    </tr>
			    <?php
			} else {
				if (empty($license)){
					// ask to enter a license key
					?>
				    <tr class="plugin-update-tr installer-plugin-update-tr active marketking-container-status-inactive">
				    	<td colspan="4" class="plugin-update colspanchange marketking-container-inactive">
				    		<div class="update-message notice inline notice-marketking-inactive">
				    			<p class="installer-q-icon"><?php 
				    				esc_html_e('You MarketKing Pro license has not been activated. You are not receiving plugin updates.','marketking');
				    				?><a href="<?php echo esc_attr(admin_url('admin.php?page=marketking&tab=activate'));?>"><?php esc_html_e('Activate license key','marketking');?></a><?php echo ' '.esc_html__('or','marketking');?><a class="marketking_notice_purchase" target="_blank" href="https://kingsplugins.com/woocommerce-multivendor/marketking/"><?php esc_html_e('purchase a new license.','marketking');?></a></p>
				    		</div>
				    	</td>
				    </tr>
				    <?php
				} else {
					?>
				    <tr class="plugin-update-tr installer-plugin-update-tr active marketking-container-status-inactive">
				    	<td colspan="4" class="plugin-update colspanchange marketking-container-inactive">
				    		<div class="update-message notice inline notice-marketking-inactive">
				    			<p class="installer-q-icon"><?php 
				    				esc_html_e('There appears to be an issue with your license key (it may be expired or inactive). You are not receiving plugin updates.','marketking');
				    				?><a href="<?php echo esc_attr(admin_url('admin.php?page=marketking&tab=activate'));?>"><?php esc_html_e('Activate license key','marketking');?></a><?php echo ' '.esc_html__('or','marketking');?><a class="marketking_notice_purchase" target="_blank" href="https://webwizards.ticksy.com/submit/#100019223"><?php esc_html_e('contact support.','marketking');?></a></p>
				    		</div>
				    	</td>
				    </tr>
				    <?php
				}
				
			}
		}
	}

	// Register new post type: Custom Registration Option (marketking_option)
	public static function marketking_register_post_type_custom_option() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Custom Registration Option', 'marketking' ),
	        'singular_name'         => esc_html__( 'Registration Option', 'marketking' ),
	        'all_items'             => esc_html__( 'Registration Options', 'marketking' ),
	        'menu_name'             => esc_html__( 'Registration Options', 'marketking' ),
	        'add_new'               => esc_html__( 'Add New', 'marketking' ),
	        'add_new_item'          => esc_html__( 'Add new registration option', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit option', 'marketking' ),
	        'new_item'              => esc_html__( 'New option', 'marketking' ),
	        'view_item'             => esc_html__( 'View option', 'marketking' ),
	        'view_items'            => esc_html__( 'View options', 'marketking' ),
	        'search_items'          => esc_html__( 'Search options', 'marketking' ),
	        'not_found'             => esc_html__( 'No options found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No options found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent option', 'marketking' ),
	        'featured_image'        => esc_html__( 'Option image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set option image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove option image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as option image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into option', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this option', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter options', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Options navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Options list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Custom Registration Option', 'marketking' ),
	        'description'           => esc_html__( 'This is where you can create new custom registration options', 'marketking' ),
	        'labels'                => $labels,
	        'supports'              => array( 'title' ),
	        'hierarchical'          => false,
	        'public'                => false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 100,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => true,
	        'has_archive'           => false,
	        'exclude_from_search'   => true,
	        'publicly_queryable'    => false,
	        'capability_type'       => 'product',
	        'map_meta_cap'          => true,
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_option',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_option', $args );
	}

	// Add Registration Option Metaboxes
	function marketking_option_metaboxes($post_type) {
	    $post_types = array('marketking_option');     //limit meta box to certain post types
       	if ( in_array( $post_type, $post_types ) ) {
       		add_meta_box(
       		    'marketking_option_settings_metabox'
       		    ,esc_html__( 'Registration Option Settings', 'marketking' )
       		    ,array( $this, 'marketking_option_settings_metabox_content' )
       		    ,$post_type
       		    ,'advanced'
       		    ,'high'
       		);
	    }
	}

	function marketking_option_settings_metabox_content(){
		global $post;
		?>
		<div class="marketking_option_settings_metabox_container">
			<div class="marketking_option_settings_metabox_container_element">
				<div class="marketking_option_settings_metabox_container_element_title">
					<svg class="marketking_option_settings_metabox_container_element_title_icon" xmlns="http://www.w3.org/2000/svg" width="33" height="35" fill="none" viewBox="0 0 33 35">
					  <path fill="#C4C4C4" fill-rule="evenodd" d="M22.211 3.395v5.07c3.495 1.914 5.729 5.664 5.729 9.88 0 6.16-5.115 11.157-11.423 11.157-6.303 0-11.417-4.994-11.417-11.156 0-4.354 2.202-8.092 5.867-9.93V3.39C4.575 5.495.314 11.36.314 18.346c0 8.745 7.256 15.831 16.201 15.831 8.948 0 16.206-7.086 16.206-15.831a15.804 15.804 0 00-10.51-14.95z" clip-rule="evenodd"/>
					  <path fill="#C4C4C4" fill-rule="evenodd" d="M16.413 16.906c1.693 0 3.073-1.036 3.073-2.32V2.32c0-1.282-1.38-2.32-3.073-2.32-1.696 0-3.073 1.038-3.073 2.32v12.266c0 1.284 1.377 2.32 3.073 2.32z" clip-rule="evenodd"/>
					</svg>
					<?php esc_html_e('Status', 'marketking'); ?>
				</div>
				<div class="marketking_option_settings_metabox_container_element_checkbox_container">
					<div class="marketking_option_settings_metabox_container_element_checkbox_name">
						<?php esc_html_e('Enabled','marketking'); ?>
					</div>
					<input type="checkbox" value="1" class="marketking_option_settings_metabox_container_element_checkbox" name="marketking_option_settings_metabox_container_element_checkbox" <?php checked(1,intval(get_post_meta($post->ID,'marketking_option_status',true)),true); ?>>
				</div>
			</div>
			<div class="marketking_option_settings_metabox_container_element">
				<div class="marketking_option_settings_metabox_container_element_title">
					<svg class="marketking_option_settings_metabox_container_element_title_icon" xmlns="http://www.w3.org/2000/svg" width="37" height="37" fill="none" viewBox="0 0 37 37">
					  <path fill="#C4C4C4" d="M36.82 19.735l-2.181-3.464 1.293-3.886a1.183 1.183 0 00-.524-1.39L31.88 8.945l-.649-4.047a1.181 1.181 0 00-.379-.692 1.167 1.167 0 00-.727-.295l-4.07-.16L23.61.47a1.164 1.164 0 00-1.434-.355L18.5 1.875 14.822.113a1.163 1.163 0 00-1.435.357l-2.442 3.281-4.07.16c-.269.011-.526.115-.727.295-.202.18-.335.424-.378.691l-.65 4.048-3.528 2.048a1.183 1.183 0 00-.525 1.39L2.36 16.27.18 19.735a1.183 1.183 0 00.18 1.477l2.939 2.837-.33 4.085c-.022.27.049.54.202.763.153.224.377.387.636.463l3.912 1.134 1.592 3.774c.105.25.293.456.531.582.239.127.513.166.777.11l3.992-.823 3.15 2.597c.213.175.476.266.739.266s.524-.091.74-.266l3.15-2.597 3.99.824a1.163 1.163 0 001.309-.693l1.592-3.774 3.912-1.134c.259-.076.484-.24.637-.463.152-.223.224-.493.202-.763l-.33-4.085 2.938-2.837a1.18 1.18 0 00.18-1.477zm-9.882-5.653l-8.171 12.323c-.309.46-.787.768-1.262.768-.474 0-1.003-.268-1.34-.609l-6-6.136a1.09 1.09 0 010-1.517l1.48-1.518a1.043 1.043 0 011.142-.23c.127.054.242.132.34.23l3.903 3.992 6.438-9.71a1.045 1.045 0 01.669-.449 1.033 1.033 0 01.786.166l1.736 1.2a1.09 1.09 0 01.279 1.49z"/>
					</svg>
					<?php esc_html_e('Vendor Approval', 'marketking'); ?>
				</div>
				<select class="marketking_option_settings_metabox_container_element_select" name="marketking_option_settings_metabox_container_element_select">
					<option value="automatic" <?php selected('automatic', get_post_meta($post->ID,'marketking_option_approval',true), true); ?>><?php esc_html_e('Automatic Approval', 'marketking'); ?></option>
					<option value="manual" <?php selected('manual', get_post_meta($post->ID,'marketking_option_approval',true), true); ?>><?php esc_html_e('Manual Approval', 'marketking'); ?></option>
				</select>
			</div>
		</div>
		<div class="marketking_option_approval_sort_container">
			<div class="marketking_option_approval_sort_container_element">
				<div class="marketking_option_approval_sort_container_element_select_container">
					<div class="marketking_user_settings_container_column_title_option">
						<svg class="marketking_user_settings_container_column_title_icon_right" xmlns="http://www.w3.org/2000/svg" width="31" height="29" fill="none" viewBox="0 0 31 29">
						  <path fill="#C4C4C4" d="M15.5 6.792V.625H.083v27.75h30.834V6.792H15.5zm-9.25 18.5H3.167v-3.084H6.25v3.084zm0-6.167H3.167v-3.083H6.25v3.083zm0-6.167H3.167V9.875H6.25v3.083zm0-6.166H3.167V3.708H6.25v3.084zm6.167 18.5H9.333v-3.084h3.084v3.084zm0-6.167H9.333v-3.083h3.084v3.083zm0-6.167H9.333V9.875h3.084v3.083zm0-6.166H9.333V3.708h3.084v3.084zm15.416 18.5H15.5v-3.084h3.083v-3.083H15.5v-3.083h3.083v-3.084H15.5V9.875h12.333v15.417zM24.75 12.958h-3.083v3.084h3.083v-3.084zm0 6.167h-3.083v3.083h3.083v-3.083z"></path>
						</svg>
						<?php esc_html_e('Default Group','marketking'); ?>	    				
					</div>
					<select class="marketking_automatic_approval_customer_group_select" name="marketking_automatic_approval_customer_group_select">
						<?php $selected = get_post_meta($post->ID,'marketking_option_automatic_approval_group',true); ?>
						<?php

						echo '<option value="" '.selected('',$selected,false).'>'.esc_html__('None (not a vendor)','marketking').'</option>';

						// display all customer groups
						$groups = get_posts( array( 'post_type' => 'marketking_group','post_status'=>'publish','numberposts' => -1) );
						foreach ($groups as $group){
							echo '<option value="group_'.esc_attr($group->ID).'" '.selected('group_'.$group->ID,$selected,false).'>'.esc_html($group->post_title).'</option>';
						}
						?>

						<?php
						// IF SALESKING ACTIVATED, INTEGRATE WITH SALESKING BY SHOWING OPTIONS
						if (defined('SALESKING_DIR')){
							// display all sales agents groups
							$groups = get_posts( array( 'post_type' => 'salesking_group','post_status'=>'publish','numberposts' => -1) );
							if (!empty($groups)){
								?>
								<optgroup label="<?php esc_html_e('Sales Agent Groups', 'marketking'); ?>">
									<?php
									foreach ($groups as $group){
										echo '<option value="saleskinggroup_'.esc_attr($group->ID).'" '.selected('saleskinggroup_'.$group->ID,$selected,false).'>'.esc_html($group->post_title).'</option>';
									}
								?>
								</optgroup>
								<?php
							}
							
						}
						?>
					</select>
				</div>
			</div>
			<div class="marketking_option_approval_sort_container_element">
				<div class="marketking_field_settings_metabox_top_column_sort_title">
					<svg class="marketking_field_settings_metabox_top_column_sort_title_icon" xmlns="http://www.w3.org/2000/svg" width="30" height="28" fill="none" viewBox="0 0 30 28">
					  <path fill="#C4C4C4" d="M6.167 27.75H0v-3.083h6.167v-1.542H3.083A3.083 3.083 0 010 20.042V18.5a3.092 3.092 0 013.083-3.083h3.084A3.083 3.083 0 019.25 18.5v6.167a3.073 3.073 0 01-3.083 3.083zm0-9.25H3.083v1.542h3.084V18.5zM3.083 0h3.084A3.083 3.083 0 019.25 3.083V9.25a3.073 3.073 0 01-3.083 3.083H3.083A3.083 3.083 0 010 9.25V3.083A3.092 3.092 0 013.083 0zm0 9.25h3.084V3.083H3.083V9.25zm10.792-6.167h15.417v3.084H13.875V3.083zm0 21.584v-3.084h15.417v3.084H13.875zm0-12.334h15.417v3.084H13.875v-3.084z"/>
					</svg>
					<?php esc_html_e('Dropdown Sort Order','marketking'); ?>
				</div>
				<input type="number" min="1" max="1000" name="marketking_option_sort_number" class="marketking_field_settings_metabox_top_column_sort_text" placeholder="<?php esc_html_e('Enter sort number here...', 'marketking'); ?>" value="<?php echo esc_attr(get_post_meta($post->ID, 'marketking_option_sort_number', true)); ?>" required>
			</div>
		</div>
		<br />

		<!-- Information panel -->
		<div class="marketking_option_settings_metabox_information_box">
			<svg class="marketking_option_settings_metabox_information_box_icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 36 36">
			  <path fill="#358BBB" d="M18 0C8.06 0 0 8.06 0 18s8.06 18 18 18 18-8.06 18-18S27.94 0 18 0zm0 28.446a1.607 1.607 0 110-3.213 1.607 1.607 0 010 3.213zm2.527-8.819a1.941 1.941 0 00-1.241 1.8v.912a.322.322 0 01-.322.322h-1.928a.322.322 0 01-.322-.322v-.864c0-.928.27-1.844.8-2.607a4.49 4.49 0 012.093-1.643c1.366-.527 2.25-1.672 2.25-2.921 0-1.772-1.732-3.215-3.857-3.215s-3.857 1.443-3.857 3.215v.305a.322.322 0 01-.322.321h-1.928a.322.322 0 01-.322-.321v-.305c0-1.58.691-3.054 1.945-4.15C14.721 9.095 16.312 8.517 18 8.517c1.688 0 3.279.582 4.484 1.635 1.253 1.097 1.945 2.572 1.945 4.15 0 2.323-1.531 4.412-3.902 5.324z"/>
			</svg>
			<?php esc_html_e('Here you can control whether vendors are manually, or automatically approved upon registration.','marketking'); ?>
		</div>		

		<?php
	}

	// Save Custom Registration Option Metabox 
	function marketking_save_custom_option_metaboxes($post_id){
		if (isset($_POST['_inline_edit'])){
			if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
			    return;
			}
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}

		$postobj = get_post($post_id);
		if ( $postobj->post_status === 'trash' ) {
	        return;
	    }
	    if (isset($_GET['action'])) {
	    	if ($_GET['action'] === 'untrash'){
	    		return;
	    	}
	    }

		if (get_post_type($post_id) === 'marketking_option'){
			$status = sanitize_text_field(filter_input(INPUT_POST, 'marketking_option_settings_metabox_container_element_checkbox'));
			$approval = sanitize_text_field(filter_input(INPUT_POST, 'marketking_option_settings_metabox_container_element_select'));
			$automatic_approval_group = sanitize_text_field(filter_input(INPUT_POST, 'marketking_automatic_approval_customer_group_select'));
			$sort_order = sanitize_text_field(filter_input(INPUT_POST, 'marketking_option_sort_number'));
			
			if ($status !== NULL){
				update_post_meta( $post_id, 'marketking_option_status', $status);
			}

			if ($approval !== NULL){
				update_post_meta( $post_id, 'marketking_option_approval', $approval);
			}

			if ($automatic_approval_group !== NULL){
				update_post_meta( $post_id, 'marketking_option_automatic_approval_group', $automatic_approval_group);
			}

			if ($sort_order !== NULL){
				update_post_meta( $post_id, 'marketking_option_sort_number', $sort_order);
			}


		}
	}
	function marketking_settings_page_core_requirement() {

		if (!defined('MARKETKINGCORE_DIR')){

			// Admin Menu Settings 
			$page_title = esc_html__('MarketKing','marketking');
			$menu_title = esc_html__('MarketKing','marketking');
			$capability = 'manage_woocommerce';
			$slug = 'marketking';
			$callback = array( $this, 'marketking_settings_page_content_core_requirement' );

			$iconurl = plugins_url('../includes/assets/images/marketking-icon-graphik.svg', __FILE__);
			$position = 54;

			add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $iconurl, $position );

		}	
	}

	function marketking_settings_page_content_core_requirement(){

		// require MarketKing CORE
		if (!defined('MARKETKINGCORE_DIR')){
			// if installed but not active
			$core_plugin_file = 'marketking-multivendor-marketplace-for-woocommerce/marketking-core.php';
			if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && is_plugin_inactive( $core_plugin_file ) ){
	    		?>

	    	    <div class="b2bking_activate_core_notice notice notice-success is-dismissible">
	    	        <h2><?php esc_html_e( 'MarketKing is almost ready!', 'marketking' ); ?></h2>
	    	        <p><?php 
	    	        esc_html_e( 'You just need to activate the ', 'marketking' ); 
	    	        echo '<strong>';
	    	        esc_html_e( 'MarketKing Core', 'marketking' ); 
	    	        echo '</strong>';
	    	        esc_html_e( ' plugin to make it functional!', 'marketking' ); 
	    	        ?></p>
	    	        <p><a class="marketking_activate_button button button-primary" href="<?php echo wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $core_plugin_file ); ?>"  title="<?php esc_html_e( 'Activate this plugin', 'marketking' ); ?>"><?php esc_html_e( 'Activate', 'marketking' ); ?></a></p>
	    	        <br>
	    	    </div>

		    	<?php

			} else if ( ! file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file )){

				// if not installed and not active
	    		?>
	    	    <div class="marketkingpro_activate_woocommerce_notice notice notice-success is-dismissible">
	    	        <h2><?php esc_html_e( 'MarketKing is almost ready!', 'marketking' ); ?></h2>
	    	        <p><?php 
	    	        esc_html_e( 'You just need to install the ', 'marketking' ); 
	    	        echo '<strong><a href="https://wordpress.org/plugins/marketking-multivendor-marketplace-for-woocommerce/">';
	    	        esc_html_e( 'MarketKing Core', 'marketking' ); 
	    	        echo '</a></strong>';
	    	        esc_html_e( ' plugin to make it functional!', 'marketking' ); 
	    	        ?></p>
	    	        <p>
	    	            <button class="marketking-core-installer button"><?php esc_html_e( 'Install Now', 'marketking' ); ?></button>
	    	        </p><br>
	    	    </div>

	    	    <script type="text/javascript">
	    	        ( function ( $ ) {
	    	            $( '.marketking-core-installer' ).on('click', function ( e ) {
	    	                e.preventDefault();
	    	                $( this ).addClass( 'install-now updating-message' );
	    	                $( this ).text( '<?php echo esc_js( 'Installing...', 'b2bking' ); ?>' );

	    	                var data = {
	    	                    action: 'marketking_core_install',
	    	                    security: '<?php echo wp_create_nonce( 'marketking-core-install-nonce' ); ?>'
	    	                };

	    	                $.post( ajaxurl, data, function ( response ) {
	    	                    if ( response.success ) {
	    	                        $( '.marketking-core-installer' ).attr( 'disabled', 'disabled' );
	    	                        $( '.marketking-core-installer' ).removeClass( 'install-now updating-message' );
	    	                        $( '.marketking-core-installer' ).text( '<?php echo esc_js( 'Installed', 'b2bking' ); ?>' );
	    	                        window.location.reload();
	    	                    }
	    	                } );
	    	            } );
	    	        } )( jQuery );
	    	    </script>
		    	<?php
			}
			
		}
	}

	// Add custom columns to custom option menu
	function marketking_add_columns_custom_option_menu($columns) {


		$columns_initial = $columns;
		// rename title
		$columns = array(
			'title' => esc_html__( 'Registration option name', 'marketking' ),
			'marketking_approval' => esc_html__( 'Approval', 'marketking' ),
			'marketking_automatic_approval_group' => esc_html__( 'Default Group', 'marketking' ),
			'marketking_status' => esc_html__( 'Status', 'marketking' ),
		);
		$columns = array_slice($columns_initial, 0, 1, true) + $columns;


	    return $columns;
	}

		function marketking_sellerdocs_howto() {
			global $current_screen;
		    if( 'marketking_docs' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_sellerdocs_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_sellerdocs_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Here you can set up documentation articles that serve as a knowledge base of useful information for your vendors. This is particularly useful for vendor onboarding or frequently asked questions.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_rfields_howto() {
			global $current_screen;
		    if( 'marketking_field' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_rfields_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_rfields_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Registration fields are custom fields that you can configure and use to collect information during registration. Create, edit, or delete fields as needed.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_roptions_howto() {
			global $current_screen;
		    if( 'marketking_option' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_roptions_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_roptions_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Registration options are the dropdown options users can choose from during registration. Create, edit, or delete options as needed.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_refunds_howto() {
			global $current_screen;
		    if( 'marketking_refund' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_refunds_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_refunds_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Here you can view the refund requests that have been approved by vendors. Once you have processed a refund via the payment gateway used, you can mark it here as completed.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_badges_howto() {
			global $current_screen;
		    if( 'marketking_badge' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_badges_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_badges_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Here you can configure badges that will be displayed on your vendor\'s profile and product pages. Badges highlight vendor achievements and can also be automatically connected to certain membership subscriptions.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_vitems_howto() {
			global $current_screen;
		    if( 'marketking_vitem' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_vitems_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_vitems_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Verification items are the documents / items you are requesting for verification from your vendors. You can configure any documents here, for example "business license" or "fire safety documentation".', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_verifications_howto() {
			global $current_screen;
		    if( 'marketking_vreq' != $current_screen->post_type ){
			    return;
		    }
		    ?>
		    <input type="hidden" id="marketking_backend_page" value="marketking_vreq">
		    <?php

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_verifications_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_verifications_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Here you can receive document verification requests from your vendors, and decide to approve or reject them. This also serves as a documents upload log for your vendors.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_memberships_howto() {
			global $current_screen;
		    if( 'marketking_mpack' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_memberships_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_memberships_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Membership Packages allow your vendors to purchase packages, addons or subscriptions, directly from their vendor dashboard. Each package is associated with a group, and you can configure features, restrictions and limitations for each group.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_abusereports_howto() {
			global $current_screen;
		    if( 'marketking_abuse' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_abusereports_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_abusereports_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Abuse reports allow your customers to report products that appear suspicious, fake, malicious, etc., helping you maintain a clean and safe marketplace. Vendors can also report misleading reviews through this module.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_commissionrules_howto() {
			global $current_screen;
		    if( 'marketking_rule' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_commissionrules_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_commissionrules_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Commission rules allow you to configure complex commission setups based on products, categories, groups, or individual vendors.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_messages_howto() {
			global $current_screen;
		    if( 'marketking_message' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_messages_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_messages_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Messages allow you to stay in touch with your vendors, ask or receive questions, clarify matters, queries, etc. Vendors can also initiate messages.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_grules_howto() {
			global $current_screen;
		    if( 'marketking_grule' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_grules_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_grules_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Through group rules, you can automatically change a vendor\'s group when they hit a particular threshold such as a certain total order value. For example, this allows you to promote vendors across ranks, increase their commission or enable additional permissions.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_groups_howto() {
			global $current_screen;
		    if( 'marketking_group' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_groups_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_groups_howto_notice notice notice-info is-dismissible">
	    	    	<p><?php esc_html_e( 'Vendor groups help you organize and manage your vendors. Create, edit, or delete groups based on your store\'s needs. To add a vendor to a group, go to their user profile and scroll down to \'Vendor Settings\'.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

		function marketking_announcements_howto() {
			global $current_screen;
		    if( 'marketking_announce' != $current_screen->post_type ){
			    return;
		    }

			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketking_dismiss_announcements_howto_notice', true)) !== 1){
	    		?>
	    	    <div class="marketking_announcements_howto_notice notice notice-info is-dismissible">
	    	        <p><?php esc_html_e( 'Announcements are notifications that are broadcast to your vendors and show up in each vendor\'s dashboard and in email notifications. Vendors cannot reply to announcements.', 'marketking' ); ?></p>
	    	    </div>
		    	<?php
		    }
		}

	// Add custom option custom columns data
	function marketking_columns_custom_option_data( $column, $post_id ) {
	    switch ( $column ) {

	        case 'marketking_approval' :
	        	$approval = get_post_meta($post_id,'marketking_option_approval',true);

	            echo '<span class="marketking_option_column_approval_'.esc_attr($approval).'">'.esc_html(ucfirst($approval)).'</span>';;
	            break;

	        case 'marketking_automatic_approval_group' :
	        	$approval = get_post_meta($post_id,'marketking_option_approval',true);
	        	$approval_group = get_post_meta($post_id,'marketking_option_automatic_approval_group',true);
	        	if ($approval_group === NULL || $approval_group === 'none' || $approval_group === ''){
	        		$approval_group = '-';
	        	} else {
	        		$approval_group = get_the_title(explode('_',$approval_group)[1]);
	        	}
	            echo '<span class="marketking_option_column_approval_'.esc_attr($approval).'">'.esc_html(ucfirst($approval_group)).'</span>';;
	            break;

	        case 'marketking_status' :
	        	$status = intval(get_post_meta($post_id,'marketking_option_status',true));
	        	if ($status === 1) {
	        		$status = 'enabled';
	        	} else {
	        		$status = 'disabled';
	        	}
	        	
	            echo '<span class="marketking_option_column_status_'.esc_attr($status).'">'.esc_html(ucfirst($status)).'</span>';
	            break;


	    }
	}


	// Register new post type: Custom Registration Field (marketking_field)
	public static function marketking_register_post_type_custom_field() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Custom Registration Field', 'marketking' ),
	        'singular_name'         => esc_html__( 'Registration Field', 'marketking' ),
	        'all_items'             => esc_html__( 'Registration Fields', 'marketking' ),
	        'menu_name'             => esc_html__( 'Registration Fields', 'marketking' ),
	        'add_new'               => esc_html__( 'Add New', 'marketking' ),
	        'add_new_item'          => esc_html__( 'Add new field', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit field', 'marketking' ),
	        'new_item'              => esc_html__( 'New field', 'marketking' ),
	        'view_item'             => esc_html__( 'View field', 'marketking' ),
	        'view_items'            => esc_html__( 'View fields', 'marketking' ),
	        'search_items'          => esc_html__( 'Search fields', 'marketking' ),
	        'not_found'             => esc_html__( 'No fields found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No fields found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent field', 'marketking' ),
	        'featured_image'        => esc_html__( 'Field image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set field image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove field image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as field image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into field', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this field', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter fields', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Fields navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Fields list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Custom Registration Field', 'marketking' ),
	        'description'           => esc_html__( 'This is where you can create new custom registration fields', 'marketking' ),
	        'labels'                => $labels,
	        'supports'              => array( 'title' ),
	        'hierarchical'          => false,
	        'public'                => false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 126,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => true,
	        'has_archive'           => false,
	        'exclude_from_search'   => true,
	        'publicly_queryable'    => false,
	        'capability_type'       => 'product',
	        'map_meta_cap'          => true,
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_field',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_field', $args );
	}

	// Add Registration Custom Field Metaboxes
	function marketking_field_metaboxes($post_type) {
	    $post_types = array('marketking_field');     //limit meta box to certain post types
       	if ( in_array( $post_type, $post_types ) ) {
       		add_meta_box(
       		    'marketking_field_settings_metabox'
       		    ,esc_html__( 'Registration Field Settings', 'marketking' )
       		    ,array( $this, 'marketking_field_settings_metabox_content' )
       		    ,$post_type
       		    ,'advanced'
       		    ,'high'
       		);
       		add_meta_box(
       		    'marketking_field_billing_connection_metabox'
       		    ,esc_html__( 'Field Options', 'marketking' )
       		    ,array( $this, 'marketking_field_billing_connection_metabox_content' )
       		    ,$post_type
       		    ,'advanced'
       		    ,'low'
       		);
	    }
	}

	function marketking_field_settings_metabox_content(){
		global $post;
		?>
		<div class="marketking_field_settings_metabox_container">
			<div class="marketking_field_settings_metabox_top">
				<div class="marketking_field_settings_metabox_top_column">
					<div class="marketking_field_settings_metabox_top_column_status">
						<div class="marketking_field_settings_metabox_top_column_status_title">
							<svg class="marketking_field_settings_metabox_top_column_status_title_icon" xmlns="http://www.w3.org/2000/svg" width="33" height="35" fill="none" viewBox="0 0 33 35">
							  <path fill="#C4C4C4" fill-rule="evenodd" d="M22.211 3.395v5.07c3.495 1.914 5.729 5.664 5.729 9.88 0 6.16-5.115 11.157-11.423 11.157-6.303 0-11.417-4.994-11.417-11.156 0-4.354 2.202-8.092 5.867-9.93V3.39C4.575 5.495.314 11.36.314 18.346c0 8.745 7.256 15.831 16.201 15.831 8.948 0 16.206-7.086 16.206-15.831a15.804 15.804 0 00-10.51-14.95z" clip-rule="evenodd"/>
							  <path fill="#C4C4C4" fill-rule="evenodd" d="M16.413 16.906c1.693 0 3.073-1.036 3.073-2.32V2.32c0-1.282-1.38-2.32-3.073-2.32-1.696 0-3.073 1.038-3.073 2.32v12.266c0 1.284 1.377 2.32 3.073 2.32z" clip-rule="evenodd"/>
							</svg>
							<?php esc_html_e('Status','marketking'); ?>
						</div>
						<div class="marketking_field_settings_metabox_top_column_status_checkbox_container">
							<div class="marketking_field_settings_metabox_top_column_status_checkbox_name">
								<?php esc_html_e('Enabled','marketking'); ?>
							</div>
							<input type="checkbox" value="1" class="marketking_field_settings_metabox_top_column_status_checkbox_input" name="marketking_field_settings_metabox_top_column_status_checkbox_input" <?php checked(1, intval(get_post_meta($post->ID, 'marketking_field_status', true)), true); ?>>
						</div>
					</div>
					<div class="marketking_field_settings_metabox_top_column_registration_option">
						<div class="marketking_field_settings_metabox_top_column_registration_option_title">
							<svg class="marketking_field_settings_metabox_top_column_registration_option_title_icon" xmlns="http://www.w3.org/2000/svg" width="28" height="31" fill="none" viewBox="0 0 28 31">
							  <path fill="#C4C4C4" d="M24.667 0H3.083A3.083 3.083 0 000 3.083v20.042a3.083 3.083 0 003.083 3.083H9.25l4.625 4.625 4.625-4.625h6.167a3.083 3.083 0 003.083-3.083V3.083A3.083 3.083 0 0024.667 0zM13.875 4.625c2.663 0 4.625 1.961 4.625 4.625 0 2.664-1.962 4.625-4.625 4.625-2.66 0-4.625-1.961-4.625-4.625 0-2.664 1.964-4.625 4.625-4.625zM6.44 21.583c.86-2.656 3.847-4.625 7.435-4.625 3.587 0 6.577 1.969 7.436 4.625H6.44z"/>
							</svg>
							<?php esc_html_e('Registration Option','marketking'); ?>
						</div>
						<select class="marketking_field_settings_metabox_top_column_registration_option_select" name="marketking_field_settings_metabox_top_column_registration_option_select">
							<option value="alloptions" <?php selected('alloptions', get_post_meta($post->ID, 'marketking_field_registration_option', true), true); ?>><?php esc_html_e('All Options','marketking'); ?></option>
							<option value="multipleoptions" <?php selected('multipleoptions', get_post_meta($post->ID, 'marketking_field_registration_option', true), true); ?>><?php esc_html_e('Select Multiple Options','marketking'); ?></option>
							<?php 
								$registration_options = get_posts([
							    		'post_type' => 'marketking_option',
							    	  	'post_status' => 'publish',
							    	  	'numberposts' => -1,
							    ]);
							    foreach ($registration_options as $option){
							    	echo '<option value="option_'.$option->ID.'" '.selected('option_'.$option->ID, get_post_meta($post->ID, 'marketking_field_registration_option', true), false).'>'.esc_html($option->post_title).'</option>'; 
							    }
							?>
						</select>

					</div>
					<div id="marketking_select_multiple_options_selector" class="marketking_field_settings_metabox_top_column_registration_option">
						<div class="marketking_field_settings_metabox_top_column_registration_option_title">
							<svg class="marketking_field_settings_metabox_top_column_registration_option_title_icon" xmlns="http://www.w3.org/2000/svg" width="28" height="31" fill="none" viewBox="0 0 28 31">
							  <path fill="#C4C4C4" d="M24.667 0H3.083A3.083 3.083 0 000 3.083v20.042a3.083 3.083 0 003.083 3.083H9.25l4.625 4.625 4.625-4.625h6.167a3.083 3.083 0 003.083-3.083V3.083A3.083 3.083 0 0024.667 0zM13.875 4.625c2.663 0 4.625 1.961 4.625 4.625 0 2.664-1.962 4.625-4.625 4.625-2.66 0-4.625-1.961-4.625-4.625 0-2.664 1.964-4.625 4.625-4.625zM6.44 21.583c.86-2.656 3.847-4.625 7.435-4.625 3.587 0 6.577 1.969 7.436 4.625H6.44z"/>
							</svg>
							<?php esc_html_e('Select Multiple Options','marketking'); ?>
						</div>
						<select class="marketking_field_settings_metabox_top_column_registration_option_select" name="marketking_field_settings_metabox_top_column_registration_option_select_multiple_options[]" multiple>
							<?php
							// if page not "Add new", get selected options
							$selected_options = array();
							if( get_current_screen()->action !== 'add'){
					        	$selected_options_string = get_post_meta($post->ID, 'marketking_field_multiple_options', true);
					        	$selected_options = explode(',', $selected_options_string);
					        }

				        	$registration_options = get_posts([
				            		'post_type' => 'marketking_option',
				            	  	'post_status' => 'publish',
				            	  	'numberposts' => -1,
				            ]);
				            foreach ($registration_options as $option){
				            	$is_selected = 'no';
				            	foreach ($selected_options as $selected_option){
										if ($selected_option === ('option_'.$option->ID )){
											$is_selected = 'yes';
										}
									}
				            	echo '<option value="option_'.$option->ID.'" '.selected('yes',$is_selected, true).'>'.esc_html($option->post_title).'</option>'; 
				            }
					        ?>
						</select>

					</div>
				</div>
				<div class="marketking_field_settings_metabox_top_column">
					<div class="marketking_field_settings_metabox_top_column_required">
						<div class="marketking_field_settings_metabox_top_column_required_title">
							<svg class="marketking_field_settings_metabox_top_column_required_title_icon" xmlns="http://www.w3.org/2000/svg" width="33" height="33" fill="none" viewBox="0 0 33 33">
							  <path fill="#C4C4C4" d="M16.188 0C7.248 0 0 7.248 0 16.188c0 8.939 7.248 16.187 16.188 16.187 8.939 0 16.187-7.248 16.187-16.188C32.375 7.248 25.127 0 16.187 0zM15.03 8.383c0-.16.13-.29.29-.29h1.734c.159 0 .289.13.289.29v9.828a.29.29 0 01-.29.289H15.32a.29.29 0 01-.289-.29V8.384zm1.156 15.898a1.734 1.734 0 010-3.468 1.735 1.735 0 010 3.468z"/>
							</svg>
							<?php esc_html_e('Required','marketking'); ?>
						</div>
						<div class="marketking_field_settings_metabox_top_column_status_checkbox_container">
							<div class="marketking_field_settings_metabox_top_column_status_checkbox_name">
								<?php esc_html_e('Required','marketking'); ?>
							</div>
							<input type="checkbox" value="1" class="marketking_field_settings_metabox_top_column_required_checkbox_input" name="marketking_field_settings_metabox_top_column_required_checkbox_input" <?php checked(1, intval(get_post_meta($post->ID, 'marketking_field_required', true)), true); ?>>
						</div>
					</div>
					<div class="marketking_field_settings_metabox_top_column_sort">
						<div class="marketking_field_settings_metabox_top_column_sort_title">
							<svg class="marketking_field_settings_metabox_top_column_sort_title_icon" xmlns="http://www.w3.org/2000/svg" width="30" height="28" fill="none" viewBox="0 0 30 28">
							  <path fill="#C4C4C4" d="M6.167 27.75H0v-3.083h6.167v-1.542H3.083A3.083 3.083 0 010 20.042V18.5a3.092 3.092 0 013.083-3.083h3.084A3.083 3.083 0 019.25 18.5v6.167a3.073 3.073 0 01-3.083 3.083zm0-9.25H3.083v1.542h3.084V18.5zM3.083 0h3.084A3.083 3.083 0 019.25 3.083V9.25a3.073 3.073 0 01-3.083 3.083H3.083A3.083 3.083 0 010 9.25V3.083A3.092 3.092 0 013.083 0zm0 9.25h3.084V3.083H3.083V9.25zm10.792-6.167h15.417v3.084H13.875V3.083zm0 21.584v-3.084h15.417v3.084H13.875zm0-12.334h15.417v3.084H13.875v-3.084z"/>
							</svg>
							<?php esc_html_e('Sort Order','marketking'); ?>
						</div>
						<input type="number" min="1" max="1000" name="marketking_field_settings_metabox_top_column_sort_input" class="marketking_field_settings_metabox_top_column_sort_text" placeholder="<?php esc_html_e('Enter sort number here...', 'marketking'); ?>" value="<?php echo esc_attr(get_post_meta($post->ID, 'marketking_field_sort_number', true)); ?>">
					</div>
					
				</div>
			</div>
			<div class="marketking_field_settings_metabox_bottom">
				<div class="marketking_field_settings_metabox_bottom_field_type">
					<div class="marketking_field_settings_metabox_bottom_field_type_title">
						<svg class="marketking_field_settings_metabox_bottom_field_type_title_icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 28 28">
						  <path fill="#C4C4C4" d="M24.667 0H3.083A3.083 3.083 0 000 3.083v21.584a3.083 3.083 0 003.083 3.083h21.584a3.083 3.083 0 003.083-3.083V3.083A3.083 3.083 0 0024.667 0zM4.625 4.625h7.708v7.708H4.625V4.625zm6.938 20.042a3.854 3.854 0 110-7.709 3.854 3.854 0 010 7.709zm4.624-9.25l4.625-7.709 4.625 7.709h-9.25z"/>
						</svg>
						<?php esc_html_e('Field Type','marketking'); ?>
					</div>
					<select class="marketking_field_settings_metabox_bottom_field_type_select" name="marketking_field_settings_metabox_bottom_field_type_select">
						<?php $field_type_post_meta = get_post_meta($post->ID, 'marketking_field_field_type', true); ?>

						<option value="text" <?php selected('text', $field_type_post_meta, true);?>><?php esc_html_e('Text','marketking'); ?></option>
						<option value="textarea" <?php selected('textarea', $field_type_post_meta, true);?>><?php esc_html_e('Textarea','marketking'); ?></option>
						<option value="number" <?php selected('number', $field_type_post_meta, true);?>><?php esc_html_e('Number','marketking'); ?></option>
						<option value="tel" <?php selected('tel', $field_type_post_meta, true);?>><?php esc_html_e('Telephone','marketking'); ?></option>
						<option value="select" <?php selected('select', $field_type_post_meta, true);?>><?php esc_html_e('Select','marketking'); ?></option>
						<option value="checkbox" <?php selected('checkbox', $field_type_post_meta, true);?>><?php esc_html_e('Checkboxes (check all that apply)','marketking'); ?></option>
						<option value="email" <?php selected('email', $field_type_post_meta, true);?>><?php esc_html_e('Email','marketking'); ?></option>
						<option value="file" <?php selected('file', $field_type_post_meta, true);?>><?php esc_html_e('File Upload (supported: jpg, jpeg, png, txt, pdf, doc, docx)','marketking'); ?></option>
						<option value="date" <?php selected('date', $field_type_post_meta, true);?>><?php esc_html_e('Date','marketking'); ?></option>
					</select>	
				</div>
				<div class="marketking_field_settings_metabox_bottom_label_and_placeholder_container">
					<div class="marketking_field_settings_metabox_bottom_field_label">
						<div class="marketking_field_settings_metabox_bottom_field_label_title">
							<svg class="marketking_field_settings_metabox_bottom_field_label_title_icon" xmlns="http://www.w3.org/2000/svg" width="31" height="30" fill="none" viewBox="0 0 31 30">
							  <path fill="#C4C4C4" d="M29.155 14.366c.61.61.915 1.327.915 2.148 0 .822-.305 1.514-.915 2.078L18.662 29.155a3.065 3.065 0 01-2.148.845c-.821 0-1.514-.282-2.077-.845L.915 15.634C.305 15.024 0 14.319 0 13.52V2.958C0 2.16.293 1.468.88.88 1.467.293 2.183 0 3.028 0h10.493c.845 0 1.55.282 2.113.845l13.52 13.521zM5.246 7.465a2.27 2.27 0 001.62-.634c.446-.423.67-.95.67-1.585 0-.633-.224-1.173-.67-1.62a2.206 2.206 0 00-1.62-.668c-.633 0-1.161.223-1.584.669a2.27 2.27 0 00-.634 1.62c0 .633.211 1.161.634 1.584.423.423.95.634 1.584.634z"/>
							</svg>
							<?php esc_html_e('Field Label','marketking'); ?>
						</div>
						<div class="marketking_field_settings_metabox_bottom_field_label_input_container">
							<input type="text" name="marketking_field_field_label_input" class="marketking_field_settings_metabox_top_column_sort_text" placeholder="<?php esc_html_e('Enter the field label here...', 'marketking'); ?>" value="<?php echo esc_attr(get_post_meta($post->ID, 'marketking_field_field_label', true)); ?>">
						</div>
					</div>
					<div class="marketking_field_settings_metabox_bottom_field_placeholder">
						<div class="marketking_field_settings_metabox_bottom_field_placeholder_title">
							<svg class="marketking_field_settings_metabox_bottom_field_placeholder_title_icon" xmlns="http://www.w3.org/2000/svg" width="37" height="37" fill="none" viewBox="0 0 37 37">
							  <path fill="#C4C4C4" d="M30.833 31.833H6.167a3.084 3.084 0 01-3.084-3.083v-18.5a3.083 3.083 0 013.084-3.083h24.666a3.084 3.084 0 013.084 3.083v18.5a3.084 3.084 0 01-3.084 3.083zM6.167 10.25v18.5h24.666v-18.5H6.167zm3.083 4.625h18.5v3.083H9.25v-3.083zm0 6.167h15.417v3.083H9.25v-3.083z"/>
							</svg>
							<?php esc_html_e('Placeholder Text','marketking'); ?>
						</div>
						<input type="text" name="marketking_field_field_placeholder_input" class="marketking_field_settings_metabox_top_column_sort_text" placeholder="<?php esc_html_e('Enter the placeholder text here...', 'marketking'); ?>" value="<?php echo esc_attr(get_post_meta($post->ID, 'marketking_field_field_placeholder', true)); ?>">
					</div>
				</div>
				<div class="marketking_field_settings_metabox_bottom_user_choices">
					<div class="marketking_field_settings_metabox_bottom_user_choices_title">
						<svg class="marketking_field_settings_metabox_bottom_user_choices_title_icon" xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="none" viewBox="0 0 34 34">
						  <path fill="#C4C4C4" d="M10.625 7.438a3.189 3.189 0 11-6.377-.003 3.189 3.189 0 016.377.003z"/>
						  <path fill="#C4C4C4" d="M7.438 0C3.4 0 0 3.4 0 7.438c0 4.037 3.4 7.437 7.438 7.437 4.037 0 7.437-3.4 7.437-7.438C14.875 3.4 11.475 0 7.437 0zm0 12.75c-2.975 0-5.313-2.338-5.313-5.313 0-2.974 2.338-5.312 5.313-5.312 2.974 0 5.312 2.338 5.312 5.313 0 2.974-2.338 5.312-5.313 5.312zM7.438 17C3.4 17 0 20.4 0 24.438c0 4.037 3.4 7.437 7.438 7.437 4.037 0 7.437-3.4 7.437-7.438 0-4.037-3.4-7.437-7.438-7.437zm0 12.75c-2.975 0-5.313-2.337-5.313-5.313 0-2.975 2.338-5.312 5.313-5.312 2.974 0 5.312 2.337 5.312 5.313 0 2.975-2.338 5.312-5.313 5.312zM17 4.25h17v6.375H17V4.25zM17 21.25h17v6.375H17V21.25z"/>
						</svg>
						<?php esc_html_e('User Choices (Options)','marketking'); ?>
					</div>
					<input type="text" class="marketking_field_settings_metabox_top_column_sort_text" name="marketking_field_user_choices_input" placeholder="<?php esc_html_e('Please enter options separated by commas. Example: “Apples, Oranges, Pears”.', 'marketking'); ?>" value="<?php echo esc_attr(get_post_meta($post->ID, 'marketking_field_user_choices', true)); ?>">
				</div>
			</div>	
		</div>
		<?php
	}

	// Billing Options Metabox
	function marketking_field_billing_connection_metabox_content(){
		?>
		<div class="marketking_field_billing_connection_metabox_container">
			<div class="marketking_field_billing_connection_metabox_title">
				<svg class="marketking_field_billing_connection_metabox_title_icon" xmlns="http://www.w3.org/2000/svg" width="28" height="37" fill="none" viewBox="0 0 28 37">
				  <g clip-path="url(#clip0)">
				    <path fill="#C4C4C4" d="M27.384 7.588L20.273.506A1.747 1.747 0 0019.038 0h-.443v9.25h9.297v-.44c0-.456-.181-.897-.508-1.222zM16.27 9.828V0H1.743C.777 0 0 .773 0 1.734v33.532C0 36.226.777 37 1.743 37H26.15c.966 0 1.743-.773 1.743-1.734V11.563h-9.878c-.959 0-1.744-.781-1.744-1.735zM4.65 5.203c0-.32.26-.578.58-.578h5.812a.58.58 0 01.58.578V6.36a.58.58 0 01-.58.579H5.23a.58.58 0 01-.581-.579V5.203zm0 5.781V9.828c0-.32.26-.578.58-.578h5.812a.58.58 0 01.58.578v1.156a.58.58 0 01-.58.579H5.23a.58.58 0 01-.581-.579zm10.46 19.07v1.743a.58.58 0 01-.582.578h-1.162a.58.58 0 01-.581-.578V30.04a4.172 4.172 0 01-2.279-.82.577.577 0 01-.041-.877l.853-.81c.202-.19.5-.2.736-.053.281.175.6.269.931.269h2.042c.472 0 .857-.428.857-.953 0-.43-.262-.809-.637-.92l-3.268-.976c-1.35-.403-2.294-1.692-2.294-3.135 0-1.772 1.384-3.212 3.1-3.257v-1.743c0-.32.26-.578.58-.578h1.162a.58.58 0 01.582.578v1.755c.82.042 1.617.326 2.278.82a.577.577 0 01.042.877l-.854.81c-.201.191-.5.2-.736.053a1.749 1.749 0 00-.93-.268h-2.043c-.472 0-.857.427-.857.953 0 .43.262.808.637.92l3.269.975c1.35.403 2.294 1.693 2.294 3.136 0 1.773-1.384 3.211-3.1 3.257z"/>
				  </g>
				  <defs>
				    <clipPath id="clip0">
				      <path fill="#fff" d="M0 0h27.892v37H0z"/>
				    </clipPath>
				  </defs>
				</svg>
				<?php esc_html_e('Special Field Type', 'marketking'); ?>
			</div>
			<select id="marketking_field_billing_connection_metabox_select" class="marketking_field_billing_connection_metabox_select" name="marketking_field_billing_connection_metabox_select">
				<?php 
				global $post;
				$selected_value = get_post_meta($post->ID, 'marketking_field_billing_connection', true);
				?>
				<option value="none" <?php selected('none', $selected_value, true); ?>><?php esc_html_e('None', 'marketking'); ?></option>
				<option value="billing_first_name" <?php selected('billing_first_name', $selected_value, true); ?>><?php esc_html_e('First Name', 'marketking'); ?></option>
				<option value="billing_last_name" <?php selected('billing_last_name', $selected_value, true); ?>><?php esc_html_e('Last Name', 'marketking'); ?></option>
				<option value="billing_countrystate" <?php selected('billing_countrystate', $selected_value, true); ?>><?php esc_html_e('Country + State', 'marketking'); ?></option>
				<option value="billing_phone" <?php selected('billing_phone', $selected_value, true); ?>><?php esc_html_e('Phone Number', 'marketking'); ?></option>
				<option value="billing_store_name" <?php selected('billing_store_name', $selected_value, true); ?>><?php esc_html_e('Store Name', 'marketking'); ?></option>
				<option value="billing_store_url" <?php selected('billing_store_url', $selected_value, true); ?>><?php esc_html_e('Store URL', 'marketking'); ?></option>
				<option value="billing_vat" <?php selected('billing_vat', $selected_value, true); ?>><?php esc_html_e('VAT ID', 'marketking'); ?></option>
				<option value="custom_mapping" <?php selected('custom_mapping', $selected_value, true); ?>><?php esc_html_e('Custom User Meta Key Mapping', 'marketking'); ?></option>
			</select>

			<div class="marketking_custom_mapping_container">
				<input class="marketking_field_mapping_input" type="text" name="marketking_field_mapping" placeholder="<?php esc_attr_e('Enter your custom user meta key here (e.g. "billing_cnpj", "vat_number", etc.)', 'marketking'); ?>" value="<?php echo esc_attr(get_post_meta($post->ID,'marketking_field_mapping', true)); ?>">
			</div>

			<div class="marketking_VAT_container">
				<div class="marketking_VAT_container_column">
					<div class="marketking_VAT_container_column_title">
						<svg class="marketking_VAT_container_column_title_icon" xmlns="http://www.w3.org/2000/svg" width="35" height="40" fill="none" viewBox="0 0 35 40">
						  <path fill="#C4C4C4" fill-rule="evenodd" d="M29.074 9.895h-4.14V7.292h3.38c.865 0 1.563-.853 1.563-1.902V1.902C29.877.849 29.179 0 28.314 0h-9.229c-.865 0-1.56.851-1.56 1.902V5.39c0 1.051.695 1.902 1.56 1.902h3.446v2.603H5.85c-.925 0-1.675.74-1.675 1.648L0 32.835v6.633h35v-6.633l-4.248-21.292a1.642 1.642 0 00-.493-1.167 1.687 1.687 0 00-1.185-.481zM10.01 29.64H7.43v-2.583h2.578v2.583zm-2.58-4.934v-2.583h2.577v2.583H7.43zm7.58 4.97h-2.577v-2.619h2.578v2.62zm0-5.01h-2.617V22.16h2.618v2.507zm5.001 5.006h-2.618v-2.617h2.618v2.617zm-2.618-5.005v-2.544h2.618v2.544h-2.618zm2.618-4.934H7.431v-4.934h12.58v4.934zm7.461 0h-5.045v-2.516h5.045v2.516z" clip-rule="evenodd"/>
						</svg>
						<?php esc_html_e('Enable Automatic VIES Validation for VAT', 'marketking'); ?>
					</div>
					<div class="marketking_VAT_container_VIES_validation_checkbox_container">
						<div class="marketking_VAT_container_VIES_validation_checkbox_name">
							<?php esc_html_e('Enabled','marketking'); ?>
						</div>
						<input type="checkbox" value="1" class="marketking_VAT_container_VIES_validation_checkbox_input" name="marketking_VAT_container_VIES_validation_checkbox_input" <?php checked(1, intval(get_post_meta($post->ID, 'marketking_field_VAT_VIES_validation', true)), true); ?>>
					</div>
				</div>
				<div class="marketking_VAT_container_column">
					<div class="marketking_VAT_container_column_title">
						<svg class="marketking_VAT_container_column_title_icon" xmlns="http://www.w3.org/2000/svg" width="37" height="37" fill="none" viewBox="0 0 37 37">
						  <path fill="#C4C4C4" d="M18.5 0C8.283 0 0 8.283 0 18.5S8.283 37 18.5 37 37 28.717 37 18.5 28.717 0 18.5 0zm-4.586 5.014c.673.363 1.47.706 2.39 1.03a8.21 8.21 0 002.74.486c.669-.001 1.337-.168 1.904-.584.55-.33 1.082-.592 1.788-.524.725.079 1.488.328 2.254.37a15.995 15.995 0 012.799 1.942c-.44.026-.908.072-1.4.137-.492.065-.971.161-1.438.29-.466.13-.894.3-1.282.507-.389.207-.674.466-.856.776-.285.467-.485.862-.602 1.186-.24.671-.183 1.743-.738 2.235a1.175 1.175 0 00-.291.272.584.584 0 00-.098.408c.013.168.098.408.253.719.078.181.142.402.194.66.492 0 1.002-.075 1.438-.388l2.566.233c.662-.746 1.434-.758 2.098 0 .207.207.427.519.66.934l-1.088.738c-.259-.078-.57-.233-.932-.467a6.565 6.565 0 01-.545-.35c-1.034-.502-3.059.083-4.197.156-.149.297-.268.588-.621.66-.061.193-.002.399-.079.584-.492.777-.66 1.593-.505 2.448.285 1.348.984 2.021 2.098 2.021h.428c.544 0 .927.026 1.147.078.22.051.33.09.33.117-.13.31-.174.556-.136.738.129.582.549 1.022.505 1.652-.04.8-.292 1.49-.018 2.293.311.779.703 1.625.99 2.429a.753.753 0 00.525.427c.467.078 1.05-.233 1.75-.932.518-.57.816-1.193.893-1.866.103-.597.523-1.126.661-1.75v-.504c.13-.26.24-.512.33-.758.13-.358.144-.814.175-1.224.407-.408.805-.771 1.088-1.283.182-.311.234-.582.156-.816-.025-.051-.09-.104-.194-.155l-.584-.233c.01-.326.61-.278.894-.234l1.399-.855a15.356 15.356 0 01-1.03 5.111 13.573 13.573 0 01-2.817 4.45 13.983 13.983 0 01-5.967 3.887c-2.319.777-4.709.932-7.17.466.424-.749.668-1.592 1.166-2.333 0-.388.058-.719.175-.99.495-1.145 1.302-1.443 2.235-2.332.942-.982.933-2.142.971-3.556-.012-.896-1.444-1.446-2.099-1.944-1.517-1.022-2.48-2.526-4.644-2.08-.773.08-.96.23-1.536-.251l-.233-.117.02-.078.098-.194c.232-.243-.098-.548-.41-.448-.064.013-.135.02-.213.02-.07-.347-.303-.67-.35-1.05.363.286.675.5.934.643.259.142.48.24.66.291.182.078.337.104.467.078.285-.052.446-.337.485-.856a9.538 9.538 0 00-.058-1.787.874.874 0 00.155-.312c.274-1.416 1-1.105 2.1-1.515.18-.103.219-.233.115-.389 0-.025-.005-.038-.018-.038-.013 0-.02-.014-.02-.04.598-.3.95-.915 1.321-1.476-.316-.501-.807-.92-1.36-1.205-.297-.366-1.461-.142-1.71-.739a1.508 1.508 0 01-.467-.117c-1.049-.68-1.492-1.869-2.623-2.35a6.621 6.621 0 00-1.34.019 13.822 13.822 0 014.314-2.371zm-9.445 10.96c.233.389.519.739.855 1.05 1.749 1.606 3.392 1.946 5.635 2.759.104.077.246.207.428.389.244.185.451.407.7.583 0 .13-.02.31-.058.544-.04.233-.046.608-.02 1.127.075 1.442 1.264 2.583 1.593 3.964-.292 1.79-.296 3.548-.505 5.324a13.939 13.939 0 01-4.644-3.109 14.199 14.199 0 01-3.09-4.702 14.826 14.826 0 01-.991-3.946 15.17 15.17 0 01.097-3.983z"/>
						</svg>
						<?php esc_html_e('What countries can see this field (multiple select)', 'marketking'); ?>
					</div>
					<select class="marketking_VAT_container_countries_select" name="marketking_VAT_container_countries_select[]" multiple>
						<?php
						// if page not "Add new", get selected options
						$selected_options = array();
						if( get_current_screen()->action !== 'add'){
				        	$selected_options_string = get_post_meta($post->ID, 'marketking_field_VAT_countries', true);
				        	$selected_options = explode(',', $selected_options_string);
				        }
				        // get countries list
				        $countries_object = new WC_Countries;
				        $countries_list = $countries_object -> get_countries();
				        $countries_list_eu = array('AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE');
						?>
						<optgroup label="<?php esc_html_e('EU Countries', 'marketking'); ?>">
							<?php
							foreach($countries_list_eu as $eu_country){
								$country_is_selected = 'no';
								foreach ($selected_options as $selected_option){
									if ($selected_option === $eu_country){
										$country_is_selected = 'yes';
									}
								}
								?>
								<option value="<?php echo esc_attr($eu_country); ?>" <?php selected('yes',$country_is_selected,true); ?>><?php echo esc_html($countries_list[$eu_country]);?></option>
								<?php
								unset ($countries_list[$eu_country]);
							}
							?>
						</optgroup>
						<optgroup label="<?php esc_html_e('All Other Countries', 'marketking'); ?>">
							<?php
							foreach($countries_list as $index => $country){
								$country_is_selected = 'no';
								foreach ($selected_options as $selected_option){
									if ($selected_option === $index){
										$country_is_selected = 'yes';
									}
								}
								?>
								<option value="<?php echo esc_attr($index); ?>" <?php selected('yes',$country_is_selected,true); ?>><?php echo esc_html($country);?></option>
								<?php
							}
							?>
						</optgroup>
					</select>
				</div>
			</div>

			<!-- Information panel -->
			<div class="marketking_billing_connection_information_box">
				<svg class="marketking_group_payment_shipping_information_box_icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 36 36">
				  <path fill="#358BBB" d="M18 0C8.06 0 0 8.06 0 18s8.06 18 18 18 18-8.06 18-18S27.94 0 18 0zm0 28.446a1.607 1.607 0 110-3.213 1.607 1.607 0 010 3.213zm2.527-8.819a1.941 1.941 0 00-1.241 1.8v.912a.322.322 0 01-.322.322h-1.928a.322.322 0 01-.322-.322v-.864c0-.928.27-1.844.8-2.607a4.49 4.49 0 012.093-1.643c1.366-.527 2.25-1.672 2.25-2.921 0-1.772-1.732-3.215-3.857-3.215s-3.857 1.443-3.857 3.215v.305a.322.322 0 01-.322.321h-1.928a.322.322 0 01-.322-.321v-.305c0-1.58.691-3.054 1.945-4.15C14.721 9.095 16.312 8.517 18 8.517c1.688 0 3.279.582 4.484 1.635 1.253 1.097 1.945 2.572 1.945 4.15 0 2.323-1.531 4.412-3.902 5.324z"/>
				</svg>
				<?php esc_html_e('Special fields are custom built for their specific purpose and should be chosen wherever they apply.','marketking'); ?>
			</div>
		</div>
		<?php
	}

	// Save Custom Registration Field Metabox 
	function marketking_save_custom_field_metaboxes($post_id){
		if (isset($_POST['_inline_edit'])){
			if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
			    return;
			}
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}


		$postobj = get_post($post_id);
		if ( $postobj->post_status === 'trash' ) {
	        return;
	    }
	    if (isset($_GET['action'])) {
	    	if ($_GET['action'] === 'untrash'){
	    		return;
	    	}
	    }

		if (get_post_type($post_id) === 'marketking_field' || get_post_type($post_id) === 'marketking_quote_field' ){

			$status = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_settings_metabox_top_column_status_checkbox_input'));
			$required = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_settings_metabox_top_column_required_checkbox_input'));
			$sort_number = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_settings_metabox_top_column_sort_input'));
			$registration_option = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_settings_metabox_top_column_registration_option_select'));
			if (isset($_POST['marketking_field_settings_metabox_top_column_registration_option_select_multiple_options'])){
				$registration_option_multiple = $_POST['marketking_field_settings_metabox_top_column_registration_option_select_multiple_options'];
			} else {
				$registration_option_multiple = NULL;
			}

			if (isset($_POST['marketking_field_settings_metabox_top_column_registration_option_select_multiple_groups'])){
				$registration_groups_multiple = $_POST['marketking_field_settings_metabox_top_column_registration_option_select_multiple_groups'];
			} else {
				$registration_groups_multiple = NULL;
			}
			$editable = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_settings_metabox_top_column_editable_checkbox_input'));
			$field_type = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_settings_metabox_bottom_field_type_select'));
			$field_label = filter_input(INPUT_POST, 'marketking_field_field_label_input'); 
			$placeholder_text = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_field_placeholder_input')); 
			$user_choices = wp_kses( filter_input(INPUT_POST, 'marketking_field_user_choices_input'), array( 'a'     => array(
		        'href' => array(),
		        'target' => array()
		    ) ) );


			$billing_connection = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_billing_connection_metabox_select'));
			$add_to_billing = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_add_to_billing_checkbox'));
			$billing_required = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_required_billing_checkbox'));
			$billing_exclusive = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_billing_exclusive_checkbox'));
			$vat_vies_validation = sanitize_text_field(filter_input(INPUT_POST, 'marketking_VAT_container_VIES_validation_checkbox_input'));
			$custom_field_mapping = sanitize_text_field(filter_input(INPUT_POST, 'marketking_field_mapping'));
			if ($custom_field_mapping !== NULL){
				update_post_meta( $post_id, 'marketking_field_mapping', $custom_field_mapping);
			}

			if (isset($_POST['marketking_VAT_container_countries_select'])){
				$vat_available_countries = $_POST['marketking_VAT_container_countries_select'];
			} else {
				$vat_available_countries = NULL;
			}

			if ($status !== NULL){
				update_post_meta( $post_id, 'marketking_field_status', $status);
			}
			if ($required !== NULL){
				update_post_meta( $post_id, 'marketking_field_required', $required);
			}
			if ($sort_number !== NULL){
				update_post_meta( $post_id, 'marketking_field_sort_number', $sort_number);
			}
			if ($registration_option !== NULL){
				update_post_meta( $post_id, 'marketking_field_registration_option', $registration_option);
			}
			if ($editable !== NULL){
				update_post_meta( $post_id, 'marketking_field_editable', $editable);
			}
			if ($field_type !== NULL){
				update_post_meta( $post_id, 'marketking_field_field_type', $field_type);
			}
			if ($field_label !== NULL){
				update_post_meta( $post_id, 'marketking_field_field_label', $field_label);
			}
			if ($placeholder_text !== NULL){
				update_post_meta( $post_id, 'marketking_field_field_placeholder', $placeholder_text);
			}
			if ($user_choices !== NULL){
				update_post_meta( $post_id, 'marketking_field_user_choices', $user_choices);
			}
			if ($billing_connection !== NULL){
				update_post_meta( $post_id, 'marketking_field_billing_connection', $billing_connection);
			}
			if ($add_to_billing !== NULL){
				update_post_meta( $post_id, 'marketking_field_add_to_billing', $add_to_billing);
			}
			if ($billing_required !== NULL){
				update_post_meta( $post_id, 'marketking_field_required_billing', $billing_required);
			}
			if ($billing_exclusive !== NULL){
				update_post_meta( $post_id, 'marketking_field_billing_exclusive', $billing_exclusive);
			}
			if ($vat_vies_validation !== NULL){
				update_post_meta( $post_id, 'marketking_field_VAT_VIES_validation', $vat_vies_validation);
			}
			if ($vat_available_countries !== NULL){
				$countries_string = '';
				foreach ($vat_available_countries as $country){
					$countries_string .= sanitize_text_field ($country).',';
				}
				// remove last comma
				$countries_string = substr($countries_string, 0, -1);
				update_post_meta( $post_id, 'marketking_field_VAT_countries', $countries_string);
			}

			if ($registration_option_multiple !== NULL){
				$options_string = '';
				foreach ($registration_option_multiple as $option){
					$options_string .= sanitize_text_field ($option).',';
				}
				// remove last comma
				$options_string = substr($options_string, 0, -1);
				update_post_meta( $post_id, 'marketking_field_multiple_options', $options_string);
			}

			// groups that can see the field in checkout
			if ($registration_groups_multiple !== NULL){
				$options_string = '';
				foreach ($registration_groups_multiple as $option){
					$options_string .= sanitize_text_field ($option).',';
				}
				// remove last comma
				$options_string = substr($options_string, 0, -1);
				update_post_meta( $post_id, 'marketking_field_multiple_groups', $options_string);
			}
		}
	}


	// Add custom columns to custom field menu
	function marketking_add_columns_custom_field_menu($columns) {

		$columns_initial = $columns;

		// rename title
		$columns = array(
			'title' => esc_html__( 'Title', 'marketking' ),
			'marketking_registration_option' => esc_html__( 'Registration Option', 'marketking' ),
			'marketking_field_label' => esc_html__( 'Field Label', 'marketking' ),
			'marketking_field_type' => esc_html__( 'Field Type', 'marketking' ),
			'marketking_required' => esc_html__( 'Required', 'marketking' ),
			'marketking_sort' => esc_html__( 'Sort Order', 'marketking' ),
			'marketking_status' => esc_html__( 'Status', 'marketking' ),
		);

		$columns = array_slice($columns_initial, 0, 1, true) + $columns;


	    return $columns;
	}

	// Add custom field custom columns data
	function marketking_columns_custom_field_data( $column, $post_id ) {
		
	    switch ( $column ) {

	        case 'marketking_registration_option' :
	        	$registration_option = get_post_meta($post_id,'marketking_field_registration_option',true);
	        	if ($registration_option === 'alloptions'){
	        		$registration_option = esc_html__('All Options','marketking');
	        	} else if ($registration_option === 'multipleoptions'){
	        		$registration_option = esc_html__('Multiple Options','marketking');
	        	} else {
	        		$regoption = explode('_',$registration_option);
	        		if(isset($regoption[1])){
	        			$registration_option = get_the_title(intval($regoption[1]));
	        		} else {
	        			$registration_option = '-';
	        		}
	        	}

	            echo '<strong>'.esc_html($registration_option).'</strong>';
	            break;

	        case 'marketking_field_label' :
	        	$field_label = get_post_meta($post_id,'marketking_field_field_label', true);

	            echo esc_html($field_label);
	            break;

	        case 'marketking_sort' :
	        	$field_type = get_post_meta($post_id,'marketking_field_sort_number', true);

	            echo ucfirst(esc_html($field_type));
	            break;

	        case 'marketking_field_type' :
	        	$field_type = get_post_meta($post_id,'marketking_field_field_type', true);

	            echo ucfirst(esc_html($field_type));
	            break;

	        case 'marketking_required' :
	        	$required = get_post_meta($post_id,'marketking_field_required', true);
	        	if (intval($required) === 1){
	        		$required = 'Yes';
	        	} else {
	        		$required = 'No';
	        	}

	            echo esc_html($required);
	            break;

	        case 'marketking_status' :
	        	$status = get_post_meta($post_id,'marketking_field_status', true);
	        	if (intval($status) === 1){
	        		$status = 'enabled';
	        	} else {
	        		$status = 'disabled';
	        	}

	            echo '<span class="marketking_option_column_status_'.esc_attr($status).'">'.esc_html(ucfirst($status)).'</span>';
	            break;
	    }
	    
	}



	function marketkingpro_options_capability( $capability ) {
	    return 'manage_woocommerce';
	}

	function load_global_admin_notice_resource(){
		wp_enqueue_script( 'marketkingpro_global_admin_notice_script', plugins_url('assets/js/adminnotice.js', __FILE__), $deps = array(), $ver = false, $in_footer =true);

		// Send data to JS
		$data_js = array(
			'security'  => wp_create_nonce( 'marketkingpro_notice_security_nonce' ),
		);
		wp_localize_script( 'marketkingpro_global_admin_notice_script', 'marketkingpro_notice', $data_js );
		
	}

	function load_global_admin_resources( $hook ){
		// compatibility with welaunch single variations plugin
		if ($hook !== 'woocommerce_page_woocommerce_single_variations_options_options'){
			wp_enqueue_style('select2', plugins_url('../includes/assets/lib/select2/select2.min.css', __FILE__) );
			wp_enqueue_script('select2', plugins_url('../includes/assets/lib/select2/select2.min.js', __FILE__), array('jquery') );
		}

		wp_enqueue_style ( 'marketkingpro_global_admin_style', plugins_url('assets/css/adminglobal.css', __FILE__));
		// Enqueue color picker
		wp_enqueue_style( 'wp-color-picker' );

		if (defined('MARKETKINGCORE_DIR')){
			wp_enqueue_script( 'marketkingpro_global_admin_script', plugins_url('assets/js/adminglobal.js', __FILE__), $deps = array('wp-color-picker'), $ver = false, $in_footer =true);
		}

		$cursym = '';
		if ( class_exists( 'woocommerce' )){
			$cursym = get_woocommerce_currency_symbol();
		}
		
		// Send data to JS
		$translation_array = array(
			'admin_url' => get_admin_url(),
			'security'  => wp_create_nonce( 'marketkingpro_security_nonce' ),
		    'currency_symbol' => $cursym,

		);

		if (isset($_GET['post'])){
			$translation_array['current_post_type'] = get_post_type(sanitize_text_field($_GET['post'] ));
		}
		if (isset($_GET['action'])){
			$translation_array['current_action'] = sanitize_text_field($_GET['action'] );
		}

		wp_localize_script( 'marketkingpro_global_admin_script', 'marketkingpro', $translation_array );

		if ($hook === 'marketkingpro_page_marketkingpro_tools'){
			wp_enqueue_script('semantic', plugins_url('../includes/assets/lib/semantic/semantic.min.js', __FILE__), $deps = array(), $ver = false, $in_footer =true);
			wp_enqueue_style( 'semantic', plugins_url('../includes/assets/lib/semantic/semantic.min.css', __FILE__));
			wp_enqueue_style ( 'marketkingpro_admin_style', plugins_url('assets/css/adminstyle.css', __FILE__));
			wp_enqueue_script( 'marketkingpro_admin_script', plugins_url('assets/js/admin.js', __FILE__), $deps = array(), $ver = false, $in_footer =true);
		}
	}
	
	function load_admin_resources($hook) {
		// Load only on this specific plugin admin
		if($hook != 'toplevel_page_marketkingpro') {
			return;
		}
		
		wp_enqueue_script('jquery');

		wp_enqueue_script('semantic', plugins_url('../includes/assets/lib/semantic/semantic.min.js', __FILE__), $deps = array(), $ver = false, $in_footer =true);
		wp_enqueue_style( 'semantic', plugins_url('../includes/assets/lib/semantic/semantic.min.css', __FILE__));

		wp_enqueue_style ( 'marketkingpro_admin_style', plugins_url('assets/css/adminstyle.css', __FILE__));
		wp_enqueue_script( 'marketkingpro_admin_script', plugins_url('assets/js/admin.js', __FILE__), $deps = array(), $ver = false, $in_footer =true);

		wp_enqueue_style( 'marketkingpro_style', plugins_url('../includes/assets/css/style.css', __FILE__)); 

	}


	function marketkingpro_plugin_dependencies() {

		if ( ! class_exists( 'woocommerce' ) ) {
			// if notice has not already been dismissed once by the current user
			if (intval(get_user_meta(get_current_user_id(),'marketkingpro_dismiss_activate_woocommerce_notice', true)) !== 1){
	    		?>
	    	    <div class="marketkingpro_activate_woocommerce_notice notice notice-warning is-dismissible">
	    	        <p><?php esc_html_e( 'Warning: The plugin "MarketKing" requires WooCommerce to be installed and activated.', 'marketking' ); ?></p>
	    	    </div>
    	    	<?php
    	    }
		}

		// require MarketKing CORE
		if (!defined('MARKETKINGCORE_DIR')){
			// if installed but not active
			$core_plugin_file = 'marketking-multivendor-marketplace-for-woocommerce/marketking-core.php';
			if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && is_plugin_inactive( $core_plugin_file ) ){
	    		?>

	    	    <div class="b2bking_activate_core_notice notice notice-success is-dismissible">
	    	        <h2><?php esc_html_e( 'MarketKing is almost ready!', 'marketking' ); ?></h2>
	    	        <p><?php 
	    	        esc_html_e( 'You just need to activate the ', 'marketking' ); 
	    	        echo '<strong>';
	    	        esc_html_e( 'MarketKing Core', 'marketking' ); 
	    	        echo '</strong>';
	    	        esc_html_e( ' plugin to make it functional!', 'marketking' ); 
	    	        ?></p>
	    	        <p><a class="marketking_activate_button button button-primary" href="<?php echo wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $core_plugin_file ); ?>"  title="<?php esc_html_e( 'Activate this plugin', 'marketking' ); ?>"><?php esc_html_e( 'Activate', 'marketking' ); ?></a></p>
	    	        <br>
	    	    </div>

		    	<?php

			} else if ( ! file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file )){

				// if not installed and not active
	    		?>
	    	    <div class="marketkingpro_activate_woocommerce_notice notice notice-success is-dismissible">
	    	        <h2><?php esc_html_e( 'MarketKing is almost ready!', 'marketking' ); ?></h2>
	    	        <p><?php 
	    	        esc_html_e( 'You just need to install the ', 'marketking' ); 
	    	        echo '<strong><a href="https://wordpress.org/plugins/marketking-multivendor-marketplace-for-woocommerce/">';
	    	        esc_html_e( 'MarketKing Core', 'marketking' ); 
	    	        echo '</a></strong>';
	    	        esc_html_e( ' plugin to make it functional!', 'marketking' ); 
	    	        ?></p>
	    	        <p>
	    	            <button class="marketking-core-installer button"><?php esc_html_e( 'Install Now', 'marketking' ); ?></button>
	    	        </p><br>
	    	    </div>

	    	    <script type="text/javascript">
	    	        ( function ( $ ) {
	    	            $( '.marketking-core-installer' ).on('click', function ( e ) {
	    	                e.preventDefault();
	    	                $( this ).addClass( 'install-now updating-message' );
	    	                $( this ).text( '<?php echo esc_js( 'Installing...', 'b2bking' ); ?>' );

	    	                var data = {
	    	                    action: 'marketking_core_install',
	    	                    security: '<?php echo wp_create_nonce( 'marketking-core-install-nonce' ); ?>'
	    	                };

	    	                $.post( ajaxurl, data, function ( response ) {
	    	                    if ( response.success ) {
	    	                        $( '.marketking-core-installer' ).attr( 'disabled', 'disabled' );
	    	                        $( '.marketking-core-installer' ).removeClass( 'install-now updating-message' );
	    	                        $( '.marketking-core-installer' ).text( '<?php echo esc_js( 'Installed', 'b2bking' ); ?>' );
	    	                        window.location.reload();
	    	                    }
	    	                } );
	    	            } );
	    	        } )( jQuery );
	    	    </script>
		    	<?php
			}
			
		}
	}


}
