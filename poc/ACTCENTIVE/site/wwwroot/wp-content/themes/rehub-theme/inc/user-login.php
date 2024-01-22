<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
# 	
# 	USER REGISTRATION/LOGIN MODAL
# 	========================================================================================
#   Attach this function to the footer if the user isn't logged in
# 	========================================================================================
# 		
if( !function_exists('rehub_login_register_modal') ) {
function rehub_login_register_modal() {
	// only show the registration/login form to non-logged-in members
	if(!is_user_logged_in() && rehub_option('custom_login_url') == ''){ 

		$show_terms_page = rehub_option('userlogin_term_page');
		$show_policy_page = rehub_option('userlogin_policy_page');
		?>
			<div id="rehub-login-popup-block" class="rhhidden">
				<?php if (rehub_option('custom_register_link') =='' && get_option('users_can_register')):?>
					<!-- Register form -->
					<div id="rehub-register-popup">
					<div class="rehub-register-popup">	 
						<div class="re_title_inmodal"><?php esc_html_e('Register New Account', 'rehub-theme'); ?></div>
						<?php if (rehub_option('custom_msg_popup') !='') {
							echo '<div class="mb15 mt15 rh_custom_msg_popup">';
							echo do_shortcode(rehub_option('custom_msg_popup'));
							echo '</div>';
							} ?>
						<form id="rehub_registration_form_modal" action="<?php echo home_url( '/' ); ?>" method="POST">
							<?php do_action( 'wordpress_social_login' ); ?>
							<div class="re-form-group mb20">
								<label><?php esc_html_e('Username', 'rehub-theme'); ?></label>
								<input class="re-form-input required" name="rehub_user_login" type="text"/>
							</div>
							<div class="re-form-group mb20">
								<label for="rehub_user_email"><?php esc_html_e('Email', 'rehub-theme'); ?></label>
								<input class="re-form-input required" name="rehub_user_email" id="rehub_user_email" type="email"/>
							</div>
							<div class="re-form-group mb20">
								<label for="rehub_user_signonpassword"><?php esc_html_e('Password', 'rehub-theme'); ?><span class="alignright font90"><?php esc_html_e('Minimum 6 symbols', 'rehub-theme');  ?></span></label>
								<input class="re-form-input required" name="rehub_user_signonpassword" id="rehub_user_signonpassword" type="password"/>
							</div>
							<div class="re-form-group mb20">
								<label for="rehub_user_confirmpassword"><?php esc_html_e('Confirm password', 'rehub-theme'); ?></label>
								<input class="re-form-input required" name="rehub_user_confirmpassword" id="rehub_user_confirmpassword" type="password"/>
							</div>										
							<?php

							if($show_terms_page){ ?>
								<div class="re-form-group mb20">
									<div class="checkbox">
										<label><input name="rehub_terms" type="checkbox"> 
											<?php $terms_page = (is_numeric($show_terms_page)) ? get_the_permalink($show_terms_page) : esc_url($show_terms_page);?>
											<?php esc_html_e( 'I accept the', 'rehub-theme' ); ?> <a href="<?php echo ''.$terms_page;?>" target="_blank"><?php esc_html_e( 'Terms & Conditions', 'rehub-theme' ); ?></a>
										</label>
									</div>
								</div><?php
							}

							if($show_policy_page){ ?>
								<div class="re-form-group mb20">
									<div class="checkbox">
										<label><input name="rehub_policy" type="checkbox"> 
											<?php $policy_page = (is_numeric($show_policy_page)) ? get_the_permalink($show_policy_page) : esc_url($show_policy_page);?>
											<?php esc_html_e( 'I accept the', 'rehub-theme' ); ?> <a href="<?php echo ''.$policy_page;?>" target="_blank"><?php esc_html_e( 'Privacy Policy', 'rehub-theme' ); ?></a>
										</label>
									</div>
								</div><?php
							}							

							 ?>

							<div class="re-form-group mb20">
								<input type="hidden" name="action" value="rehub_register_member_popup_function"/>
								<button class="wpsm-button rehub_main_btn" type="submit"><?php esc_html_e('Sign up', 'rehub-theme'); ?></button>
							</div>
							<?php wp_nonce_field( 'ajax-login-nonce', 'register-security' ); ?>
						</form>
						<div class="rehub-errors"></div>
						<div class="rehub-login-popup-footer"><?php esc_html_e('Already have an account?', 'rehub-theme'); ?> <span class="act-rehub-login-popup color_link" data-type="login"><?php esc_html_e('Login', 'rehub-theme'); ?></span></div>
					</div>
					</div>
				<?php endif;?>

				<!-- Login form -->
				<div id="rehub-login-popup">
			 	<div class="rehub-login-popup">
					<div class="re_title_inmodal"><?php esc_html_e('Log In', 'rehub-theme'); ?></div>
					<?php if (rehub_option('custom_msg_popup') !='') {
						echo '<div class="mb15 mt15 rh_custom_msg_popup">';
						echo do_shortcode(rehub_option('custom_msg_popup'));
						echo '</div>';
						} ?>
					<?php if(class_exists('\CashbackTracker\application\Plugin')):?>
						<div class="blue_type nonefloat_box wpsm_box rhhidden mb15" id="rh-ca-login">
							<?php echo sprintf(__( 'and get %s on %s or', 'rehub-theme' ), '<span id="rh-ca-login-n" class="rehub-main-color fontbold"></span>', '<span id="rh-ca-login-m" class="fontbold"></span>');?>
							<a href="#" target="_blank" id="rh-ca-login-a"><?php esc_html_e('go to site', 'rehub-theme');?></a> <?php esc_html_e('without cashback', 'rehub-theme');?>
						</div>
					<?php endif;?>					
					<form id="rehub_login_form_modal" action="<?php echo home_url( '/' ); ?>" method="post">
						<?php do_action( 'wordpress_social_login' ); ?>
						<div class="re-form-group mb20">
							<label><?php esc_html_e('Username', 'rehub-theme') ?></label>
							<input class="re-form-input required" name="rehub_user_login" type="text"/>
						</div>
						<div class="re-form-group mb20">
							<label for="rehub_user_pass"><?php esc_html_e('Password', 'rehub-theme')?></label>
							<input class="re-form-input required" name="rehub_user_pass" id="rehub_user_pass" type="password" autocomplete="on" />
							<?php if(class_exists('Woocommerce')) :?>
								<a href="<?php echo wc_lostpassword_url(); ?>" class="alignright"><?php esc_html_e('Lost Password?', 'rehub-theme'); ?></a>
							<?php else: ?>
								<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="alignright"><?php esc_html_e('Lost Password?', 'rehub-theme'); ?></a>
							<?php endif;?>							
						</div>
						<div class="re-form-group mb20">
							<label for="rehub_remember"><input name="rehub_remember" id="rehub_remember" type="checkbox" value="forever" />
							<?php esc_html_e('Remember me', 'rehub-theme'); ?></label>
						</div>						
						<div class="re-form-group mb20">
							<input type="hidden" name="action" value="rehub_login_member_popup_function"/>
							<button class="wpsm-button rehub_main_btn" type="submit"><?php esc_html_e('Login', 'rehub-theme'); ?></button>
						</div>
						<?php wp_nonce_field( 'ajax-login-nonce', 'loginsecurity' ); ?>
					</form>
					<div class="rehub-errors"></div>
					<?php if (get_option('users_can_register')):?>
						<div class="rehub-login-popup-footer"><?php esc_html_e('Don\'t have an account?', 'rehub-theme'); ?> 
							<?php if (rehub_option('custom_register_link') !=''):?>
								<span class="act-rehub-login-popup color_link" data-type="url" data-customurl="<?php echo esc_html(rehub_option('custom_register_link'));?>"><?php esc_html_e('Sign Up', 'rehub-theme'); ?></span>						
							<?php else:?>
								<span class="act-rehub-login-popup color_link" data-type="register"><?php esc_html_e('Sign Up', 'rehub-theme'); ?></span>
							<?php endif;?>
						</div>
					<?php endif;?>
				</div>
				</div>

			</div>
		<?php
	}else{
		if (rehub_option('custom_login_url') !=''){
			echo '<span id="rehub-custom-login-url" data-customloginurl="'.rehub_option('custom_login_url').'"></span>';			
		}
	}
}
add_action('wp_footer', 'rehub_login_register_modal');
}

# 	
# 	AJAX FUNCTION (HANDLE DATA FROM POPUP)
# 	========================================================================================	

// LOGIN
if( !function_exists('rehub_login_member_popup_function') ) {
function rehub_login_member_popup_function(){

	// Get variables
	$user_login		= sanitize_user($_POST['rehub_user_login']);	
	$user_pass		= sanitize_text_field($_POST['rehub_user_pass']);
	$remember 	= !empty($_POST['rehub_remember']) ? true : false;
	$redirect_to = rehub_option('custom_redirect_after_login');
	if($redirect_to){
		if ( stripos( $user_login, '@' ) !== false ) {
			$user = get_user_by('email', $user_login);
			if($user) {
				$user_login = $user->user_login;
			}
		}
		$redirect_to = str_replace('%%userlogin%%', $user_login, $redirect_to);
	}

	// Check CSRF token
	if( !check_ajax_referer( 'ajax-login-nonce', 'loginsecurity', false) ){
		echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30"><i></i>'.__('Session has expired, please reload the page and try again', 'rehub-theme').'</div>'));
	}
 	
 	// Check if input variables are empty
 	elseif(empty($user_login) or empty($user_pass)){
		echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30"><i></i>'.__('Please fill all form fields', 'rehub-theme').'</div>'));
 	}

 	else{
 		$secure_cookie = (!is_ssl()) ? false : '';
 		$user = wp_signon( array('user_login' => $user_login, 'user_password' => $user_pass, 'remember' => $remember ), $secure_cookie );
	    if(is_wp_error($user)){
			echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30"><i></i>'.$user->get_error_message().'</div>'));
		}
	    else{
	    	$redirect = apply_filters('rh_custom_redirect_for_login', $redirect_to, $user );
			echo json_encode(array('error' => false, 'message'=> '<div class="wpsm_box green_type mb30">'.__('Login successful, reloading page...', 'rehub-theme').'</div>', 'redirecturl' => esc_url($redirect)));
		}
 	}
 	wp_die();
}
add_action('wp_ajax_nopriv_rehub_login_member_popup_function', 'rehub_login_member_popup_function');
}

// REGISTER
if( !function_exists('rehub_register_member_popup_function') ) {
function rehub_register_member_popup_function(){

	// Get variables
	$user_login	= sanitize_user($_POST['rehub_user_login']);	
	$user_email	= sanitize_email($_POST['rehub_user_email']);
	$user_signonpassword = sanitize_text_field($_POST['rehub_user_signonpassword']);
	$user_confirmpassword	= sanitize_text_field($_POST['rehub_user_confirmpassword']);
	$wcv_apply_as_vendor = (!empty($_POST['wcv_apply_as_vendor'])) ? true : false;	
	$show_terms_page = rehub_option('userlogin_term_page');
	$show_policy_page = rehub_option('userlogin_policy_page');
	$redirect_to = rehub_option('custom_redirect_after_login');
	if($redirect_to){
		$redirect_to = str_replace('%%userlogin%%', $user_login, $redirect_to);
	}
	$bp_logic_popup = rehub_option('bp_deactivateemail_confirm');
	$usermeta = $user_error_req_fields = array();
	
	// Check CSRF token
	if( !check_ajax_referer( 'ajax-login-nonce', 'register-security', false) ){
		echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30">'.__('Session has expired, please reload the page and try again', 'rehub-theme').'</div>'));
		wp_die();
	}
 	
 	// Check if input variables are empty
 	elseif(empty($user_login) or empty($user_email) or empty($user_signonpassword) or empty($user_confirmpassword)){
		echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30">'.__('Please fill all form fields', 'rehub-theme').'</div>'));
		wp_die();
 	}

 	elseif($show_terms_page and !isset($_POST['rehub_terms'])){
		echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30">'.__('Please accept the terms and conditions before registering', 'rehub-theme').'</div>'));
		wp_die();
 	}

 	elseif($show_policy_page and !isset($_POST['rehub_policy'])){
		echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30">'.__('Please accept the Privacy Policy before registering', 'rehub-theme').'</div>'));
		wp_die();
 	} 	

 	elseif($user_signonpassword != $user_confirmpassword){
		echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30">'.__('Your passwords do not match. Set the same password in both fields', 'rehub-theme').'</div>'));
		wp_die();
 	} 

 	elseif(mb_strlen($user_signonpassword) < 6){
		echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30">'.__('Your passwords must have minimum 6 symbols.', 'rehub-theme').'</div>'));
		wp_die();
 	}  

	if (!empty($_POST['signup_profile_field_ids'])){
		$signup_profile_field_ids = explode(',', $_POST['signup_profile_field_ids']);
		foreach ((array)$signup_profile_field_ids as $field_id) {
			if ( ! isset( $_POST['field_' . $field_id] ) ) {
				if ( ! empty( $_POST['field_' . $field_id . '_day'] ) && ! empty( $_POST['field_' . $field_id . '_month'] ) && ! empty( $_POST['field_' . $field_id . '_year'] ) ) {
					// Concatenate the values.
					$date_value = $_POST['field_' . $field_id . '_day'] . ' ' . $_POST['field_' . $field_id . '_month'] . ' ' . $_POST['field_' . $field_id . '_year'];

					// Turn the concatenated value into a timestamp.
					$_POST['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $date_value ) );
					
				}
			}
			// Create errors for required fields without values.
			if ( xprofile_check_is_required_field( $field_id ) && empty( $_POST[ 'field_' . $field_id ] ) && ! bp_current_user_can( 'bp_moderate' ) ){
				$field_data = xprofile_get_field($field_id );
				if(is_object($field_data)){
					$user_error_req_fields[]= $field_data->name;
				}		
			}
		}
		if(!empty($user_error_req_fields)){
			echo json_encode(array('error' => true, 'message'=> '<div class="wpsm_box warning_type mb30">'.__('Next fields are required: ', 'rehub-theme').implode(', ',$user_error_req_fields).'</div>'));
			wp_die();				
		}
		$usermeta['profile_field_ids'] = (array)$_POST['signup_profile_field_ids'];
	} 	

	if($bp_logic_popup == 'bp' && class_exists( 'BuddyPress' )){
		$usermeta['password'] = wp_hash_password( $_POST['signup_password'] );
		$usermeta = apply_filters( 'bp_signup_usermeta', $usermeta );
		$userid = bp_core_signup_user( $user_login, $user_signonpassword, $user_email, $usermeta );
	}	
	else{
		$userid = wp_create_user( $user_login, $user_signonpassword, $user_email );
	}

	if(is_wp_error($userid)){
		$registration_error_messages = $userid->errors;
		$display_errors = '<div class="wpsm_box warning_type mb30">';
			foreach($registration_error_messages as $error){
				$display_errors .= '<p>'.$error[0].'</p>';
			}
		$display_errors .= '</div>';
		echo json_encode(array('error' => true, 'message' => $display_errors));
	}else{
		if (!empty($_POST['signup_profile_field_ids'])){
			$signup_profile_field_ids = explode(',', $_POST['signup_profile_field_ids']);
			foreach ((array)$signup_profile_field_ids as $field_id) {
				if ( ! isset( $_POST['field_' . $field_id] ) ) {
					if ( ! empty( $_POST['field_' . $field_id . '_day'] ) && ! empty( $_POST['field_' . $field_id . '_month'] ) && ! empty( $_POST['field_' . $field_id . '_year'] ) ) {
						// Concatenate the values.
						$date_value = $_POST['field_' . $field_id . '_day'] . ' ' . $_POST['field_' . $field_id . '_month'] . ' ' . $_POST['field_' . $field_id . '_year'];

						// Turn the concatenated value into a timestamp.
						$_POST['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $date_value ) );
						
					}
				}
				if(!empty($_POST['field_' . $field_id])){
					$field_val = sanitize_text_field($_POST['field_' . $field_id]);
					xprofile_set_field_data($field_id, $userid, $field_val);
					$visibility_level = ! empty( $_POST['field_' . $field_id . '_visibility'] ) ? $_POST['field_' . $field_id . '_visibility'] : 'public';
					xprofile_set_field_visibility_level( $field_id, $userid, $visibility_level );
				}	
					
			}
		}	
		do_action('rh_before_wcv_register', $userid );	
		if ($wcv_apply_as_vendor){
			$secure_cookie = (!is_ssl()) ? false : '';
			$manual = get_option( 'wcvendors_vendor_approve_registration' );
			$role   = apply_filters( 'wcvendors_pending_role', ( $manual ? 'pending_vendor' : 'vendor' ) );	
			if (class_exists('WCVendors_Pro') ) {
				if ($role == 'pending_vendor'){
					$role = 'customer';
				}
			}		
			$user_id = wp_update_user( array( 'ID' => $userid, 'role' => $role ));
			do_action( 'wcvendors_application_submited', $userid );
		    if (class_exists('WCVendors_Pro') ) {
	    		$dashboard_page_ids = (array) get_option( 'wcvendors_dashboard_page_id' );
	    		if(!empty($dashboard_page_ids)){
					$dashboard_page_id  = reset( $dashboard_page_ids );
	        		$redirect_to = get_permalink($dashboard_page_id);
	    		}
		    }
		    else {
		    	$redirect_to = get_permalink(get_option('wcvendors_vendor_dashboard_page_id'));
		    }
		    $errorshow = false;
			if ($role == 'vendor'){
				$status = 'approved';				
				$message = '<div class="wpsm_box green_type mb30">'.__( 'Congratulations! You are now a vendor. Be sure to configure your store settings before adding products.', 'rehub-theme').'</div>';
				if($bp_logic_popup == 'bp' && class_exists( 'BuddyPress' )){
					$message .= '<div class="wpsm_box blue_type mt15 mb30">'.__( 'Before login this site you will need to activate your account via the email we have just sent to your address.', 'rehub-theme').'</div>';	
					$errorshow = true;	
				}				
			}
			elseif ($role == 'customer'){
				$status = 'customer';				
				$message = '<div class="wpsm_box green_type mb30">'.__( 'Congratulations! Now you must add some settings before application', 'rehub-theme').'</div>';
				if($bp_logic_popup == 'bp' && class_exists( 'BuddyPress' )){
					$message .= '<div class="wpsm_box blue_type mt15 mb30">'.__( 'Before login this site you will need to activate your account via the email we have just sent to your address.', 'rehub-theme').'</div>';	
					$errorshow = true;	
				}				
			}			
			else{
                $status = 'pending';
				$message = '<div class="wpsm_box green_type mb30">'.__( 'Your application has been received. You will be notified by email the results of your application. Currently you can use site as Pending vendor.', 'rehub-theme').'</div>';
				if($bp_logic_popup == 'bp' && class_exists( 'BuddyPress' )){
					$message .= '<div class="wpsm_box blue_type mt15 mb30">'.__( 'Before login this site you will need to activate your account via the email we have just sent to your address.', 'rehub-theme').'</div>';	
					$errorshow = true;	
				}				
			}
			if ($status != 'customer' && $status != ''){
				global $woocommerce;
	            $mails = $woocommerce->mailer()->get_emails();
	            if (!empty( $mails ) ) {
	                $mails[ 'WC_Email_Approve_Vendor' ]->trigger($userid, $status );
	            }
			}			
			wp_signon( array('user_login' => $user_login, 'user_password' => $user_signonpassword), $secure_cookie );
			$redirect = apply_filters('rh_custom_redirect_for_reg', $redirect_to, $userid );
			echo json_encode(array('error' => $errorshow, 'message' => $message, 'redirecturl' => $redirect));			
		}else{
			do_action('rh_before_user_signon', $userid );
			$redirect = apply_filters('rh_custom_redirect_for_reg', $redirect_to, $userid );
			if($bp_logic_popup == 'bp' && class_exists( 'BuddyPress' )){
				echo json_encode(array('error' => true, 'message' => '<div class="wpsm_box green_type mb30">'.__( 'You have successfully created your account! To begin using this site you will need to activate your account via the email we have just sent to your address.', 'rehub-theme' ).'</div>', 'redirecturl' => $redirect));
			}else{
				$secure_cookie = (!is_ssl()) ? false : '';
				wp_signon( array('user_login' => $user_login, 'user_password' => $user_signonpassword), $secure_cookie );
				echo json_encode(array('error' => false, 'message' => '<div class="wpsm_box green_type mb30">'.__( 'Registration complete. Now you can use your account. Reloading page...', 'rehub-theme').'</div>', 'redirecturl' => $redirect));
			}			
		}

	}
 	wp_die();
}
add_action('wp_ajax_nopriv_rehub_register_member_popup_function', 'rehub_register_member_popup_function');
}