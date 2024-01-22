<?php

/**
 * @Author: Abdul Awal
 * @Date:   2021-05-11 00:04:08
 * @Last Modified by:   Abdul Awal
 * @Last Modified time: 2021-05-13 14:48:57
 */

if ( ! function_exists( 'pvfw_get_page_by_name' ) ) {
	/**
	 * Get Page by name
	 *
	 * @param  [type] $pagename [description].
	 * @return [type]           [description]
	 */
	function pvfw_get_page_by_name( $pagename ) {
		$list_all_pages = get_pages();
		foreach ( $list_all_pages as $page ) {
			if ( $page->post_name == $pagename ) {
				return $page;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'tnc_mail_to_friend' ) ) {
	add_action( 'wp_ajax_tnc_mail_to_friend', 'tnc_mail_to_friend' );
	add_action( 'wp_ajax_nopriv_tnc_mail_to_friend', 'tnc_mail_to_friend' );

	function tnc_mail_to_friend() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'tnc_mail_to_friend_nonce' ) ) {
			exit( 'Invalid Request' );
		}

		$uname    = sanitize_text_field( $_POST['yourname'] );
		$fname    = sanitize_text_field( $_POST['friendsname'] );
		$sname    = $_SERVER['SERVER_NAME'];
		$uemail   = sanitize_email( $_POST['youremailaddress'] );
		$femail   = sanitize_email( $_POST['friendsemailaddress'] );
		$message  = nl2br( sanitize_textarea_field( $_POST['message'] ) );
		$link     = $share_url;
		$to       = $femail;
		$subject  = sanitize_text_field( $_POST['email_subject'] );
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
		$headers .= 'From: ' . $uname . ' <webmaster@' . $sname . '>' . "\r\n";
		$headers .= 'Reply-To:' . $uemail . "\r\n";
		$sendmail = mail( $to, $subject, $message, $headers );

		if ( $sendmail ) {
			$result['type'] = 'success';
		} else {
			$result['type'] = 'error';
		}

		if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
			$result = json_encode( $result );
			echo $result;
		}

		die();
	}
}


// Display register link if site is not registered to get updates. Added in version 8.0.
function tnc_pvfw_update_message( $plugin_data, $response ) {
	$get_site_key = get_option( 'tnc_pvfw_sitekey' );
	if ( empty( $get_site_key ) ) {
		echo " Please <a href='" . admin_url( 'admin.php?page=themencode-pdf-viewer-updates' ) . "'>register your copy</a> to get automatic updates.";
	} else {
		if ( empty( $response->package ) ) {
			echo " <a href='" . admin_url( 'update-core.php?force-check=1' ) . "'>click here</a> to refresh license";
		}
	}
}
add_action( 'in_plugin_update_message-pdf-viewer-for-wordpress/pdf-viewer-for-wordpress.php', 'tnc_pvfw_update_message', $priority = 10, $accepted_args = 2 );


// Site registration message.
function tnc_pvfw_site_registered_status( $recheck ) {

	if( $recheck ){
		delete_transient( 'themencode-pdf-viewer-for-wordpress-registration' );
	}
	
	$get_site_key    = get_option( 'tnc_pvfw_sitekey' );
	$site_url_parse  = parse_url( site_url() );
	$tnc_pvfw_domain = $site_url_parse['host'];
	if ( empty( $get_site_key ) ) {
		return false;
	} else {
		$get_reg_status = get_transient( 'themencode-pdf-viewer-for-wordpress-registration' );
		if ( $get_reg_status === false ) {
			$get_registration_response = wp_remote_get( 'https://updates.themencode.com/pvfw/verify.php?sitekey=' . $get_site_key . '&site_domain=' . $tnc_pvfw_domain, array(
				'sslverify' => false
			) );
			set_transient( 'themencode-pdf-viewer-for-wordpress-registration', $get_registration_response['body'], 86400 );
			$get_reg_status = $get_registration_response['body'];
			if ( $get_reg_status == 'active' ) {
				return true;
			} else {
				return false;
			}
		} else {
			if ( $get_reg_status == 'active' ) {
				return true;
			} else {
				return false;
			}
		}
	}
}


// Site registration message.
function tnc_pvfw_site_registered_message() {
	$get_site_key = get_option( 'tnc_pvfw_sitekey' );
	$get_reg_status = get_transient( 'themencode-pdf-viewer-for-wordpress-registration' );

	if( $get_reg_status == 'inactive' ) {
		_e('<div class="inactive-pvfw" id="inactive-pvfw"><div style="border: 2px solid red; color: red; padding: 10px 20px;margin: 10px 0;"><p>You are using a non-registered url. Please re-register your copy of TNC FlipBook - PDF viewer for WordPress. <a href="' . admin_url( '/admin.php?page=themencode-pdf-viewer-updates', $scheme = 'admin' ) . '">Click Here</a> to go to registration page.</p></div></div>', 'pdf-viewer-for-wordpress');
	} elseif ( $get_reg_status == 'active' ) {
		return '';
	} else {
		return '<div style="border: 2px solid red; color: red; padding: 10px 20px;margin: 10px 0;">You are using a non-registered version of TNC FlipBook - PDF viewer for WordPress. Please register your copy of <a href="https://codecanyon.net/item/pdf-viewer-for-wordpress/8182815/" target="_blank"> TNC FlipBook - PDF viewer for WordPress </a> to receive updates & keep using without issues.<a href="' . admin_url( '/admin.php?page=themencode-pdf-viewer-updates', $scheme = 'admin' ) . '">Click Here</a> to go to registration page.<br /></div>';
	}
}

function tnc_pvfw_registration_invalid_notice_in_admin_panel() {
	$get_reg_status = get_transient( 'themencode-pdf-viewer-for-wordpress-registration' );
	if($get_reg_status == 'inactive') {
		_e('<div class="notice notice-warning is-dismissible"><p>You are using a non-registered url. Please re-register your copy of TNC FlipBook - PDF viewer for WordPress. <a href="' . admin_url( '/admin.php?page=themencode-pdf-viewer-updates', $scheme = 'admin' ) . '">Click Here</a> to go to registration page.</p></div>', 'pdf-viewer-for-wordpress');
	}
}
add_action( 'admin_notices', 'tnc_pvfw_registration_invalid_notice_in_admin_panel' );

if ( ! function_exists( 'themencode_news_updates' ) ) {
	// ThemeNcode news updates on admin pages.
	// added on 31 october 2019.
	function themencode_news_updates() {
		$news = get_transient( 'themencode-news-updates' );
		if ( empty( $news ) ) {
			$get_news = wp_remote_get( 'https://updates.themencode.com/', array(
				'sslverify' => false
			) );
			set_transient( 'themencode-news-updates', $get_news['body'], 86400 );
			$news = $get_news['body'];
		}

		return $news;
	}
}

if ( ! function_exists( 'themencode_news_updates_callback' ) ) {
	/**
	 * themencode_news_updates_callback for CSF
	 *
	 * @return [type] [description]
	 */
	function themencode_news_updates_callback() {
		$news = get_transient( 'themencode-news-updates' );
		if ( empty( $news ) ) {
			$get_news = wp_remote_get( 'https://updates.themencode.com/', array(
				'sslverify' => false
			) );
			set_transient( 'themencode-news-updates', $get_news['body'], 86400 );
			$news = $get_news['body'];
		}

		echo $news;
	}
}


/* 
* Add a place for display message to leave rating in the admin area.
*/

if ( ! function_exists( 'themencode_pvfw_leave_rating_in_admin_area' ) ) {
	function themencode_pvfw_leave_rating_in_admin_area() {
		global $pagenow;
		$admin_pages = [ 'edit.php', 'post-new.php' ];

		if( isset( $_GET['post_type'] ) ) {
			$post_type_pdfviewer = $_GET['post_type'];
		}

		if ( isset( $_POST['review_notice_on_pdf_viewer_admin_page_form_button'] ) ) {
			add_option( 'review_notice_on_pdf_viewer_admin_page_form_button', true );
		}
		$review_notice_on_pdf_viewer_admin_page_form_button = get_option('review_notice_on_pdf_viewer_admin_page_form_button');

		if( in_array( $pagenow, $admin_pages ) && ( !empty($post_type_pdfviewer) && $post_type_pdfviewer == 'pdfviewer')  && $review_notice_on_pdf_viewer_admin_page_form_button == false) {
			?>
				<div class="<?php echo esc_attr( 'notice notice-info is-dismissible review_notice_on_pdf_viewer_admin_page' ) ?>" style="margin-bottom: 5px;">
					<p>
						<?php _e( 'Enjoying TNC FlipBook - PDF viewer for WordPress? It\'s time to <a style="text-decoration:underline; font-weight: bold;" target="_blank" href="https://codecanyon.net/item/pdf-viewer-for-wordpress/reviews/8182815">Let others know</a> by leaving a review.', 'pdf-viewer-for-wordpress' ); ?>
					</p>
					<span class="<?php echo esc_attr( 'review_notice_on_pdf_viewer_admin_page_form_button' ) ?>">
						<form action="#" method="POST">
							<input type="<?php echo esc_attr( 'submit' ) ?>" id="<?php echo esc_attr( 'review_notice_on_pdf_viewer_admin_page_already_did' ) ?>" name="<?php echo esc_attr( 'review_notice_on_pdf_viewer_admin_page_form_button' ) ?>" class="button button-primary" value="<?php echo esc_attr( 'Already Did' ) ?>">
						</form>
						<form action="#" method="POST">
							<input type="<?php echo esc_attr( 'submit' ) ?>" id="<?php echo esc_attr( 'review_notice_on_pdf_viewer_admin_page_not_interest' ) ?>" name="<?php echo esc_attr( 'review_notice_on_pdf_viewer_admin_page_form_button' ) ?>" class="button button-default" value="<?php echo esc_attr( 'Not Interested' ) ?>">
						</form>
						</span>
				</div>
			<?php
		}
	}
	add_action( 'admin_notices', 'themencode_pvfw_leave_rating_in_admin_area', 1 );
}



/* 
* Add a place for display advertise in admin panel
*/
if ( ! function_exists( 'themencode_advertisement_update' ) ) {
    function themencode_advertisement_update() {
        global $pagenow;
        $admin_pages = [ 'edit.php', 'post-new.php' ];

        // Check if $_GET['post_type'] is set
        $post_type_pdfviewer = isset($_GET['post_type']) ? $_GET['post_type'] : '';

        if( in_array( $pagenow, $admin_pages ) && ( !empty($post_type_pdfviewer) && $post_type_pdfviewer == 'pdfviewer' ) ) {

            $advertisement = get_transient( 'themencode-advertisement-update' );
            if( empty( $advertisement ) ) {
                // Handle wp_remote_get errors
                $get_advertisement = wp_remote_get( 'https://updates.themencode.com/pvfw/promo.php', array(
                    'sslverify' => false
                ) );

                // Check if the response is a WP_Error
                if( is_wp_error( $get_advertisement ) ) {
                    // Handle the error appropriately
                    error_log( $get_advertisement->get_error_message() );
                    return;
                }

                // Check if the body of the response is set and valid
                if( isset($get_advertisement['body']) && !empty($get_advertisement['body']) ) {
                    set_transient( 'themencode-advertisement-update', $get_advertisement['body'], 86400 );
                    $advertisement = $get_advertisement['body'];
                } else {
                    // Handle the case where the body is not set or invalid
                    error_log( 'Invalid response body from the advertisement server.' );
                    return;
                }
            }

            // Sanitize and validate data before output
            echo wp_kses_post( $advertisement );
        }
    }
    add_action( 'admin_notices', 'themencode_advertisement_update' );
}



if ( ! function_exists( 'tnc_pvfw_create_viewer_url_callback' ) ) {
	/**
	 * [tnc_pvfw_create_viewer_url_callback description]
	 *
	 * @return [type] [description]
	 */
	function tnc_pvfw_create_viewer_url_callback() {
		echo sprintf( esc_html__( 'Please create FlipBooks using %s (TNC FlipBook > Add New) before creating a shortcode.', 'pdf-viewer-for-wordpress' ), '<a href="' . admin_url( '/post-new.php?post_type=pdfviewer', $scheme = 'admin' ) . '">this link</a>' );
	}
}

if ( ! function_exists( 'tnc_num_to_text' ) ) {
	// convert 0 or 1 to Show or Hide
	function tnc_num_to_text( $value ) {
		if ( $value == '1' || $value == 'true' ) {
			return 'Show';
		} else {
			return 'Hide';
		}
	}
}

if ( ! function_exists( 'tnc_pvfw_generate_file_array' ) ) {
	/**
	 * Take requested file url and return array with all the required fields to verify if the user has access.
	 *
	 * @param  $get_requested_file the requested file
	 * @return array
	 */
		function tnc_pvfw_generate_file_array( $get_requested_file ) {

			global $wpdb;

			$posts_table = $wpdb->prefix . 'posts';

			$uploadDir      = wp_upload_dir();
			$full_url       = $uploadDir['baseurl'] . $get_requested_file;
			$full_path      = $uploadDir['basedir'] . $get_requested_file;
			$fileInfo       = pathinfo( $full_path );
			$isResizedImage = false;

			$file_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$posts_table} WHERE guid = %s ", $full_url ) );

			if ( empty( $file_id ) ) {

				// Convert resized thumb url's to main file url
				$query_url = preg_replace( '/(-\d+x\d+)/', '', $full_url );

				$file_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$posts_table} WHERE guid = %s ", $query_url ) );

				if ( $file_id ) {
					$isResizedImage = true;
				}
			}

			$file_array = array(
				'id'               => $file_id,
				'file_url'         => $full_url,
				'file_path'        => $full_path,
				'is_resized_image' => $isResizedImage,
			);

			return $file_array;
		}
	}

// Registration notice
function tnc_pvfw_register_pvfw_notice() {
	$get_site_key = get_option( 'tnc_pvfw_sitekey' );
	if ( empty( $get_site_key ) ) { ?>
	
	<div class="notice notice-warning is-dismissible">
		<p>
		<?php

		if ( current_user_can( 'manage_options' ) ) {
			_e( sprintf( 'Please <a href="%s">register</a> your copy of TNC FlipBook - PDF viewer for WordPress to have it working properly & get updates.', admin_url( '/edit.php?post_type=pdfviewer&page=themencode-pdf-viewer-updates' ) ), 'pdf-viewer-for-wordpress' );
		}
		?>
		</p>
	</div>
	
		<?php
	} }
add_action( 'admin_notices', 'tnc_pvfw_register_pvfw_notice' );

if ( ! function_exists( 'tnc_pvfw_single_pdf_viewer_template' ) ) {
	/* Filter the single_template with our custom function*/
	add_filter( 'single_template', 'tnc_pvfw_single_pdf_viewer_template' );

	function tnc_pvfw_single_pdf_viewer_template( $single ) {

		global $post;

		if ( $post->post_type == 'pdfviewer' ) {
			$viewer_template_file = dirname( __FILE__ ) . '/../tnc-pdf-viewer-single.php';
			if ( file_exists( $viewer_template_file ) ) {
				return $viewer_template_file;
			}
		}

		return $single;

	}
}

/**
 * Add a column for displaying url with toolbar options in All PDF Viewers page. 
 *
 * @package pdf-viewer-for-wordpress
 */

function tnc_pvfw_post_header_for_display_url( $columns ) {
    $columns["tnc_pvfw_post_header_for_display_url"] = esc_html__( "URL", "pdf-viewer-for-wordpress" );
    return $columns;
}
add_filter('manage_edit-pdfviewer_columns', 'tnc_pvfw_post_header_for_display_url');


function tnc_pvfw_table_url_cell_content( $colname ) {
	if ( $colname == 'tnc_pvfw_post_header_for_display_url') {

		$get_pvfw_single_settings = get_post_meta( get_the_ID(), 'tnc_pvfw_pdf_viewer_fields', true );

		$url = get_post_permalink();
		$url_id = url_to_postid( $url );
		if( isset( $get_pvfw_single_settings['default-page-number'] ) && !empty( $get_pvfw_single_settings['default-page-number'] ) ){
			$page = $get_pvfw_single_settings['default-page-number'];
		} else {
			$page = '';
		}

		if( isset( $get_pvfw_single_settings['default-zoom'] ) && !empty( $get_pvfw_single_settings['default-zoom'] ) ){
			$zoom = $get_pvfw_single_settings['default-zoom'];
		} else {
			$zoom = 'auto';
		}

		if( isset( $get_pvfw_single_settings['toolbar-default-page-mode'] ) && !empty( $get_pvfw_single_settings['toolbar-default-page-mode'] ) ){
			$pagemode = $get_pvfw_single_settings['toolbar-default-page-mode'];
		} else {
			$pagemode = 'none';
		}

		$full_url = $url.'?auto_viewer=true#page=' . $page . '&zoom=' . $zoom . '&pagemode=' . $pagemode	;

		?>
			<input type="text" class="pvfw_tnc_copy_link_url attachment-details-copy-link tnc-pvfw-copy-share-url" id="<?php echo 'pvfw_tnc_copy_link_url_'.$url_id ?>" value="<?php echo esc_url( $full_url ) ?>" readonly="">
			<span class="copy-to-clipboard-container">
				<button class="button button-small" id="<?php echo 'pvfw_tnc_copy_link_url_'.$url_id ?>" onClick="copy_url_clipboard_target(event,this.id, 'pvfw-url-copied-message-<?php echo $url_id; ?>')">Copy URL to clipboard</button>
			</span>
			<span class="pvfw-url-copied-message" id="pvfw-url-copied-message-<?php echo $url_id ?>"></span>
		<?php

	}
}
add_action('manage_pdfviewer_posts_custom_column', 'tnc_pvfw_table_url_cell_content', 10, 2);




function tnc_pvfw_auto_add_hash_params_to_viewer_url(){
	$get_post_status = get_post_status();
	if( is_singular( 'pdfviewer' ) && $get_post_status == 'publish' ){
		$get_url = esc_url( $_SERVER['REQUEST_URI'] );
		if( isset( $_REQUEST['auto_viewer'] ) ){
			$get_auto_viewer = esc_html( $_REQUEST['auto_viewer'] );
		}
		$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );
		$get_pvfw_single_settings =  get_post_meta( get_the_ID(), 'tnc_pvfw_pdf_viewer_fields', true );
		$toolbar_use_global       = $get_pvfw_single_settings['toolbar-elements-use-global-settings'];

		if ( $toolbar_use_global == '0' ) {
			$find         = ( $get_pvfw_single_settings['find'] == '0' ) ? 0 : 1;
			$pagenav      = ( $get_pvfw_single_settings['pagenav'] == '0' ) ? 0 : 1 ;
			$share        = ( $get_pvfw_single_settings['share'] == '0' ) ? 0 : 1 ;
			$zoom         = ( $get_pvfw_single_settings['zoom'] == '0' ) ? 0 : 1 ;
			$logo         = ( $get_pvfw_single_settings['logo'] == '0' ) ? 0 : 1 ;
			$print        = ( $get_pvfw_single_settings['print'] == '0' ) ? 0 : 1 ; 
			$open         = ( $get_pvfw_single_settings['open'] == '0' ) ? 0 : 1 ;
			$download     = ( $get_pvfw_single_settings['download'] == '0' ) ? 0 : 1 ;
			$fullscreen   = ( $get_pvfw_single_settings['fullscreen'] == '0' ) ? 0 : 1 ;
			$current_view = ( $get_pvfw_single_settings['current_view'] == '0' ) ? 0 : 1 ;
			$rotate       = ( $get_pvfw_single_settings['rotate'] == '0' ) ? 0 : 1;
			$handtool     = ( $get_pvfw_single_settings['handtool'] == '0' ) ? 0 : 1 ;
			$doc_prop     = ( $get_pvfw_single_settings['doc_prop'] == '0' ) ? 0 : 1 ;
			$toggle_menu  = ( $get_pvfw_single_settings['toggle_menu'] == '0' ) ? 0 : 1 ;
			$toggle_left  = ( $get_pvfw_single_settings['toggle_left'] == '0' ) ? 0 : 1 ;
			$scroll       = ( $get_pvfw_single_settings['scroll'] == '0' ) ? 0 : 1 ;
			$spread       = ( $get_pvfw_single_settings['spread'] == '0' ) ? 0 : 1 ;
		} else {
			$find         = ( $get_pvfw_global_settings['toolbar-find'] == '0' ) ? 0 : 1 ;
			$pagenav      = ( $get_pvfw_global_settings['toolbar-pagenav'] == '0' ) ?  0 : 1 ;
			$share        = ( $get_pvfw_global_settings['toolbar-share'] == '0' ) ?  0 : 1 ;
			$zoom         = ( $get_pvfw_global_settings['toolbar-zoom'] == '0' ) ?  0 : 1 ;
			$logo         = ( $get_pvfw_global_settings['toolbar-logo'] == '0' ) ?  0 : 1 ;
			$print        = ( $get_pvfw_global_settings['toolbar-print'] == '0' ) ? 0 : 1 ;
			$open         = ( $get_pvfw_global_settings['toolbar-open'] == '0' ) ?  0 : 1 ;
			$download     = ( $get_pvfw_global_settings['toolbar-download'] == '0' ) ? 0 : 1 ;
			$fullscreen   = ( $get_pvfw_global_settings['toolbar-fullscreen'] == '0' ) ?  0 : 1 ;
			$current_view = ( $get_pvfw_global_settings['toolbar-current-view'] == '0' ) ?  0 : 1 ;
			$rotate       = ( $get_pvfw_global_settings['toolbar-rotate'] == '0' ) ?  0 : 1 ;
			$handtool     = ( $get_pvfw_global_settings['toolbar-handtool'] == '0' ) ?  0 : 1 ;
			$doc_prop     = ( $get_pvfw_global_settings['toolbar-doc-prop'] == '0' ) ?  0 : 1 ;
			$toggle_menu  = ( $get_pvfw_global_settings['toolbar-right-toggle'] == '0' ) ?  0 : 1 ;
			$toggle_left  = ( $get_pvfw_global_settings['toolbar-left-toggle'] == '0' ) ?  0 : 1 ;
			$scroll       = ( $get_pvfw_global_settings['toolbar-scroll'] == '0' ) ?  0 : 1 ;
			$spread       = ( $get_pvfw_global_settings['toolbar-spread'] == '0' ) ?  0 : 1 ;
		}

		$oxygen_redirect = $get_pvfw_global_settings['advanced-oxygen-integration'];

		if( $oxygen_redirect == '1' ){
			$shortcode_viewer_url = plugins_url()."/".TNC_PVFW_WEB_DIR."/pdf-viewer-x.php";
			$sc_page_one          = parse_url( $shortcode_viewer_url, PHP_URL_QUERY );
			if ( $sc_page_one ) {
				$shortcode_viewer_url_par = '&tnc_pvfw=';
			} else {
				$shortcode_viewer_url_par = '?tnc_pvfw=';
			}
			$generated_url  = 'file=' . $get_pvfw_single_settings['file'] . '&settings=' . $download . $print . $zoom  . $fullscreen . $share . $open . $logo . $pagenav . $find  . $current_view . $rotate . $handtool . $doc_prop . $toggle_menu . $toggle_left . $scroll . $spread . $get_pvfw_single_settings['default_scroll'] . $get_pvfw_single_settings['default_spread'] . '&lang=' . $get_pvfw_single_settings['language'];
			$additional_url = '#page=' .$get_pvfw_single_settings['default-page-number'] . '&zoom=' . $get_pvfw_single_settings['default-zoom'] . '&pagemode=' . $get_pvfw_single_settings['toolbar-default-page-mode'];
			$encoded_url    = base64_encode( $generated_url );
			$full_url      = $shortcode_viewer_url . $shortcode_viewer_url_par . $encoded_url . $additional_url;
		} else {
			if( isset( $get_auto_viewer ) && ! empty( $get_auto_viewer ) && $get_auto_viewer == 'true' ){
				return;
			}
	
			$url = get_permalink( get_the_ID() );
	
			if( isset( $get_pvfw_single_settings['default-page-number'] ) && !empty( $get_pvfw_single_settings['default-page-number'] ) ){
				$page = $get_pvfw_single_settings['default-page-number'];
			} else {
				$page = '';
			}
	
			if( isset( $get_pvfw_single_settings['default-zoom'] ) && !empty( $get_pvfw_single_settings['default-zoom'] ) ){
				$zoom = $get_pvfw_single_settings['default-zoom'];
			} else {
				$zoom = 'auto';
			}
	
			if( isset( $get_pvfw_single_settings['toolbar-default-page-mode'] ) && !empty( $get_pvfw_single_settings['toolbar-default-page-mode'] ) ){
				$pagemode = $get_pvfw_single_settings['toolbar-default-page-mode'];
			} else {
				$pagemode = 'none';
			}

			// Validate the URL
			if (filter_var($url, FILTER_VALIDATE_URL)) {
				
				// Check if the URL already has query parameters
				if (strpos($url, '?') !== false) {
					$full_url = $url . '&auto_viewer=true#page=' . $page . '&zoom=' . $zoom . '&pagemode=' . $pagemode;
				} else {
					$full_url = $url . '?auto_viewer=true#page=' . $page . '&zoom=' . $zoom . '&pagemode=' . $pagemode;
				}
				
			} else {
				$full_url = $url . '?auto_viewer=true#page=' . $page . '&zoom=' . $zoom . '&pagemode=' . $pagemode;
			}
		}
		
		wp_safe_redirect( $full_url );
	}
}
add_action( 'template_redirect',  'tnc_pvfw_auto_add_hash_params_to_viewer_url' );

