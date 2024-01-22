<?php
/**
 * Enqueue scripts for PDF Viewer by ThemeNcode
 *
 * Enqueue both frontend and backend scripts.
 *
 * @since 1.0
 *
 * @package pdf-viewer-by-themencode
 */

if ( ! function_exists( 'tnc_pvfw_enqueue_script' ) ) {
	/**
	 * Enqueue jquery as some themes may have jquery disabled.
	 */
	function tnc_pvfw_enqueue_script() {
		if ( is_singular( 'pdfviewer' ) || is_page_template( 'tnc-pdf-viewer-shortcode.php' ) || is_page_template( 'tnc-pdf-viewer.php' ) ) {
			global $post;
			$get_pvfw_global_settings_for_js = get_option( 'pvfw_csf_options' );
			$tnc_pvfw_custom_js              = $get_pvfw_global_settings_for_js['custom-js'];

			wp_enqueue_script( 'themencode-pdf-viewer-jquery', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/tnc-resources/jquery.min.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_enqueue_script( 'themencode-pdf-viewer-compatibility-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/compatibility.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_enqueue_script( 'themencode-pdf-viewer-pdf-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/build/pdf.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_enqueue_script( 'themencode-pdf-viewer-debugger-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/debugger.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_enqueue_script( 'themencode-pdf-viewer-pinch-zoom-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/tnc-resources/pinch-zoom.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_enqueue_script( 'themencode-pdf-viewer-modal-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/tnc-resources/jquery.modal.min.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_enqueue_script( 'themencode-pdf-viewer-viewer-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/viewer.js', array(), PVFW_PLUGIN_VERSION, false );
			

			
			
			// Make overflow hidden or inherit by depending on select page zoom scale and scroll mode start.
			$tnc_pvfw_pdf_viewer_fields_for_custom_js = get_post_meta( get_the_ID(), 'tnc_pvfw_pdf_viewer_fields', true );
			$get_pvfw_global_settings_for_custom_js = get_option( 'pvfw_csf_options' );

			if ( is_singular( 'pdfviewer' ) ) {

				if( 
					$tnc_pvfw_pdf_viewer_fields_for_custom_js['default_scroll'] == '3' ||
					( empty( $get_pvfw_global_settings_for_custom_js['toolbar-default-scroll'] ) && $tnc_pvfw_pdf_viewer_fields_for_custom_js['default_scroll'] == '3')
				) {
					$tnc_pvfw_custom_js_for_control_page_shaking = "$(document).ready(function() {	
						let tncPvfwScaleSelectValue = $('#scaleSelect').val();
						let tncPvfwScaleAllValue = ['auto', 'page-actual', 'page-fit', 'page-width', '0.5', '0.75', '1'];
						if ($.inArray(tncPvfwScaleSelectValue, tncPvfwScaleAllValue) !== -1 ) {
							$('#viewer').css({'overflow' : 'hidden'})
						} else {
							$('#viewer').css({'overflow' : 'unset'})
						}});";
					$tnc_pvfw_custom_js_for_control_page_shaking .= "$(document).on('scalechanged', function(){
							let tncPvfwScaleSelectValue = $('#scaleSelect').val();
							let tncPvfwScaleAllValue = ['auto', 'page-actual', 'page-fit', 'page-width', '0.5', '0.75', '1'];
							
							var scroll = PDFViewerApplication.pdfViewer.scrollMode;
							
							if ($.inArray(tncPvfwScaleSelectValue, tncPvfwScaleAllValue) !== -1 && scroll == 3 ) {
								$('#viewer').css({'overflow' : 'hidden'})
							} else {
								$('#viewer').css({'overflow' : 'unset'})
							}
						});";

					$tnc_pvfw_custom_js_for_control_page_shaking .= "$(document).on('scrollmodechanged', function(){
						var scroll = PDFViewerApplication.pdfViewer.scrollMode;
						if( scroll != 3 ){
							$('#viewer').css({'overflow' : 'unset'})
						}
					});";

					wp_add_inline_script( 'themencode-pdf-viewer-viewer-js', $tnc_pvfw_custom_js_for_control_page_shaking );
				}
			}
			
			// Make overflow hidden or inherit by depending on select page zoom scale and scroll mode end.


			if ( is_page_template( 'tnc-pdf-viewer-shortcode.php' ) ) {
				if ( isset( $_GET['file'] ) && ! empty( $_GET['file'] ) ) {
					$file     = esc_html( $_GET['file'] );
					$settings = esc_html( $_GET['settings'] );
				} elseif ( isset( $_GET['view'] ) && ! empty( $_GET['view'] ) ) {
					$get_data    = esc_html( $_GET['view'] );
					$decode_data = esc_html( base64_decode( $get_data ) );

					list($file_full, $settings_full, $lang_full) = explode( '&', $decode_data );
					list($file_par, $file)                       = explode( '=', $file_full );
					list($settings_par, $settings)               = explode( '=', $settings_full );
					list($lang_par, $viewer_language)            = explode( '=', $lang_full );

					$encode_file = base64_encode( $file );
				} elseif ( isset( $_GET['tnc_pvfw'] ) && ! empty( $_GET['tnc_pvfw'] ) ) {
					$get_data    = esc_html( $_GET['tnc_pvfw'] );
					$decode_data = esc_html( base64_decode( $get_data ) );

					list( $file_full, $settings_full, $lang_full ) = explode( '&', $decode_data );
					list( $file_par, $file )                       = explode( '=', $file_full );
					list( $settings_par, $settings )               = explode( '=', $settings_full );
					list( $lang_par, $viewer_language )            = explode( '=', $lang_full );

					$encode_file = base64_encode( $file );
				}

				$settings_arr = str_split( $settings );
				$get_language = $viewer_language;
				$fto          = esc_html( $encode_file );
				$download     = $settings_arr[0];
				$print        = $settings_arr[1];

				if ( isset( $settings_arr[17] ) ) {
					$scroll_default = $settings_arr[17];
				} else {
					$scroll_default = '0';
				}

				if ( isset( $settings_arr[18] ) ) {
					$spread_default = $settings_arr[18];
				} else {
					$spread_default = '0';
				}

				wp_add_inline_script(
					'themencode-pdf-viewer-pdf-js',
					'var tnc_locale = "' . esc_html( $get_language ) . '";
					var tnc_imageResourcesPath = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/images/";
					var tnc_workerSrc = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/build/pdf.worker.js?ver='. PVFW_PLUGIN_VERSION .'";
					var tnc_sandboxSrc = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/build/pdf.sandbox.js?ver='. PVFW_PLUGIN_VERSION .'";
					var tnc_cMapUrl = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/cmaps/";
					var tnc_stdfonts = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/standard_fonts/";
					var tnc_cMapPacked = true;

					var fto = "' . $fto . '";
					var tnc_print = "' . $print . '";
					var tnc_dl = "' . $download . '";
					var tnc_scroll_default = ' . ( empty( $scroll_default ) ? 0 : $scroll_default ) . ';
					var tnc_spread_default = ' . ( empty( $spread_default ) ? 0 : $spread_default ) . ';',
					$position = 'after'
				);
			}

			if ( is_page_template( 'tnc-pdf-viewer.php' ) ) {
				$get_pvfw_global_settings_for_js = get_option( 'pvfw_csf_options' );
				$get_language                    = $get_pvfw_global_settings_for_js['toolbar-viewer-language'];
				$fto                             = base64_encode( esc_url( $_REQUEST['file'] ) );
				$print                           = $get_pvfw_global_settings_for_js['toolbar-print'];
				$download                        = $get_pvfw_global_settings_for_js['toolbar-download'];
				$scroll_default                  = $get_pvfw_global_settings_for_js['toolbar-default-scroll'];
				$spread_default                  = $get_pvfw_global_settings_for_js['toolbar-default-spread'];

				wp_add_inline_script(
					'themencode-pdf-viewer-pdf-js',
					'var tnc_locale = "' . $get_language . '";
					var tnc_imageResourcesPath = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/images/";
					var tnc_workerSrc = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/build/pdf.worker.js?ver='. PVFW_PLUGIN_VERSION .'";
					var tnc_sandboxSrc = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/build/pdf.sandbox.js?ver='. PVFW_PLUGIN_VERSION .'";
					var tnc_cMapUrl = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/cmaps/";
					var tnc_stdfonts = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/standard_fonts/";
					var tnc_cMapPacked = true;

					var fto = "' . $fto . '";
					var tnc_print = "' . $print . '";
					var tnc_dl = "' . $download . '";
					var tnc_scroll_default = ' . ( empty( $scroll_default ) ? 0 : $scroll_default ) . ';
					var tnc_spread_default = ' . ( empty( $spread_default ) ? 0 : $spread_default ) . ';',
					$position = 'after'
				);
			}

			if ( is_singular( 'pdfviewer' ) ) {
				$get_pvfw_single_settings_for_js = get_post_meta( $post->ID, 'tnc_pvfw_pdf_viewer_fields', true );
				$get_pvfw_global_settings_for_js = get_option( 'pvfw_csf_options' );
				$get_language                    = $get_pvfw_single_settings_for_js['language'];
				$fto                             = apply_filters('tnc_pvfw_single_open_file_url', base64_encode( $get_pvfw_single_settings_for_js['file'] ) );
				if( isset($get_pvfw_global_settings_for_js['toolbar-elements-use-global-settings']) && $get_pvfw_global_settings_for_js['toolbar-elements-use-global-settings'] == '1' ) {
					$print 							= $get_pvfw_global_settings_for_js['toolbar-print'];
					$download                       = $get_pvfw_global_settings_for_js['toolbar-download'];
				} else {
					$print = $get_pvfw_single_settings_for_js['print'];
					$download = $get_pvfw_single_settings_for_js['download'];
				}

				$scroll_default                  = $get_pvfw_single_settings_for_js['default_scroll'];
				$spread_default                  = $get_pvfw_single_settings_for_js['default_spread'];

				wp_add_inline_script(
					'themencode-pdf-viewer-pdf-js',
					'var tnc_locale = "' . $get_language . '";
					var tnc_imageResourcesPath = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/images/";
					var tnc_workerSrc = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/build/pdf.worker.js?ver='. PVFW_PLUGIN_VERSION .'";
					var tnc_sandboxSrc = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/build/pdf.sandbox.js?ver='. PVFW_PLUGIN_VERSION .'";
					var tnc_cMapUrl = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/cmaps/";
					var tnc_stdfonts = "' . plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/standard_fonts/";
					var tnc_cMapPacked = true;

					var fto = "' . $fto . '";
					var tnc_print = "' . $print . '";
					var tnc_dl = "' . $download . '";
					var tnc_scroll_default = ' . ( empty( $scroll_default ) ? 0 : $scroll_default ) . ';
					var tnc_spread_default = ' . ( empty( $spread_default ) ? 0 : $spread_default ) . ';',
					$position = 'after'
				);
			}
			wp_enqueue_script( 'themencode-pdf-viewer-send-to-friend-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/tnc-resources/send-to-friend.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_enqueue_script( 'themencode-pdf-viewer-turn-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/tnc-resources/turn.min.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_enqueue_script( 'themencode-pdf-viewer-pdf-turn-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/pdf-turn/pdf-turn.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_enqueue_script( 'themencode-pdf-viewer-frontend-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/tnc-resources/frontend.js', array(), PVFW_PLUGIN_VERSION, false );
			wp_add_inline_script(
				'themencode-pdf-viewer-pdf-turn-js',
				$tnc_pvfw_custom_js,
				$position = 'after'
			);
		}
	}
	add_action( 'wp_enqueue_scripts', 'tnc_pvfw_enqueue_script' );
}


if ( ! function_exists( 'tnc_pvfw_enqueue_admin_css' ) ) {
	add_action( 'admin_enqueue_scripts', 'tnc_pvfw_enqueue_admin_css' );

	/**
	 * Enqueue Scripts in the admin
	 *
	 * @param  [type] $hook_suffix [description].
	 */
	function tnc_pvfw_enqueue_admin_css( $hook_suffix ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'pvfw-admin-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/tnc-resources/admin-css.css', array(), PVFW_PLUGIN_VERSION, $media = 'all' );
		
		wp_enqueue_script( 'pvfw-admin-js', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/tnc-resources/admin-js.js', array(), PVFW_PLUGIN_VERSION, true );
	}
}

if ( ! function_exists( 'tnc_pvfw_remove_all_scripts' ) ) {
	/**
	 * Remove all other scripts except required ones from viewer pages
	 *
	 * @return void
	 */
	function tnc_pvfw_remove_all_scripts() {
		if ( is_singular( 'pdfviewer' ) || is_page_template( 'tnc-pdf-viewer-shortcode.php' ) || is_page_template( 'tnc-pdf-viewer.php' ) ) {
			global $wp_scripts;
			$tnc_pvfw_script_handles = array( 'themencode-pdf-viewer-jquery', 'themencode-pdf-viewer-compatibility-js', 'themencode-pdf-viewer-pdf-js', 'themencode-pdf-viewer-debugger-js', 'themencode-pdf-viewer-pinch-zoom-js', 'themencode-pdf-viewer-modal-js', 'themencode-pdf-viewer-viewer-js', 'themencode-pdf-viewer-send-to-friend-js', 'themencode-pdf-viewer-turn-js', 'themencode-pdf-viewer-pdf-turn-js','themencode-pdf-viewer-frontend-js');
			foreach ( $wp_scripts->registered as $single_key => $single_script ) {
				if ( ! in_array( $single_script->handle, $tnc_pvfw_script_handles ) ) {
					wp_dequeue_script( $single_script->handle );
				}
			}


		}
	}
	add_action( 'wp_print_scripts', 'tnc_pvfw_remove_all_scripts', 199 );
}

if ( ! function_exists( 'tnc_pvfw_remove_all_styles' ) ) {
	function tnc_pvfw_remove_all_styles() {
		if ( is_singular( 'pdfviewer' ) || is_page_template( 'tnc-pdf-viewer-shortcode.php' ) || is_page_template( 'tnc-pdf-viewer.php' ) ) {
			global $wp_styles;
			$tnc_pvfw_style_handles = array( 'themencode-pdf-viewer-css', 'themencode-pdf-viewer-theme-midnight-calm', 'themencode-pdf-viewer-theme-material-blue', 'themencode-pdf-viewer-theme-aqua-white', 'themencode-pdf-viewer-modal-css', 'themencode-pdf-viewer-pdf-turn-css', 'themencode-pdf-viewer-custom-color', 'themencode-pdf-viewer-theme-common-css', 'themencode-pdf-viewer-theme-smart-red', 'themencode-pdf-viewer-theme-louis-purple', 'themencode-pdf-viewer-theme-sea-green','themencode-pdf-viewer-small-css','themencode-pdf-viewer-large-css','themencode-pdf-viewer-medium-css','themencode-pdf-viewer-toolbar-bottom-center-css','themencode-pdf-viewer-toolbar-bottom-full-width-css','themencode-pdf-viewer-toolbar-top-center-css','themencode-pdf-viewer-toolbar-top-full-width-css');
			foreach ( $wp_styles->registered as $single_key => $single_style ) {
				if ( ! in_array( $single_style->handle, $tnc_pvfw_style_handles ) ) {
					wp_dequeue_style( $single_style->handle );
				}
			}
		}
	}
	add_action( 'wp_print_styles', 'tnc_pvfw_remove_all_styles', 199 );
}
    // Add viewer toolbar class
	function add_toolbar_body_class( $classes ) {
			if ( is_singular('pdfviewer') ) {
				global $post;
				$get_pvfw_single_settings = get_post_meta( $post->ID, 'tnc_pvfw_pdf_viewer_fields', true );
				$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );

				if( array_key_exists( 'select-toolbar-style', $get_pvfw_single_settings ) ){
					$get_pvfw_toolbar_style   = $get_pvfw_single_settings['select-toolbar-style'];
					if( $get_pvfw_toolbar_style == 'global'){
						$get_pvfw_toolbar_style = $get_pvfw_global_settings['appearance-select-toolbar-style'];
					}
				} else {
					$get_pvfw_toolbar_style   = 'toolbar-top-full-width';
				}

				if( 'bottom-center' ==  $get_pvfw_toolbar_style ) {
					$classes[] = 'toolbar-bottom-center';
				}
				if( 'bottom-full-width' ==  $get_pvfw_toolbar_style ) {
					$classes[] = 'toolbar-bottom-full-width';
				}
				if( 'top-center' ==  $get_pvfw_toolbar_style ) {
					$classes[] = 'toolbar-top-center';
				}
				if( 'top-full-width' ==  $get_pvfw_toolbar_style ) {
					$classes[] = 'toolbar-top-full-width';
				}
			}
			return $classes;
		}

    add_filter( 'body_class', 'add_toolbar_body_class');

	// Add viewer toolbar class
		function add_icon_body_class( $classes ) {
			if ( is_singular('pdfviewer') ) {
				global $post;
				$get_pvfw_single_settings = get_post_meta( $post->ID, 'tnc_pvfw_pdf_viewer_fields', true );
				$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );
				if( array_key_exists( 'icon-size', $get_pvfw_single_settings ) ){
					$get_pvfw_icon_size   = $get_pvfw_single_settings['icon-size'];
					if( $get_pvfw_icon_size == 'global'){
					  	$get_pvfw_icon_size = $get_pvfw_global_settings['appearance-icon-size'];
					}
					} else {
						$get_pvfw_icon_size   = 'icon-medium';
					}

					if( 'small' ==  $get_pvfw_icon_size ) {
						$classes[] = 'icon-small';
					}
					if( 'large' ==  $get_pvfw_icon_size ) {
						$classes[] = 'icon-large';
					}
					if( 'medium' ==  $get_pvfw_icon_size ) {
						$classes[] = 'icon-medium';
					}
				} 
				return $classes;
			}
			
		add_filter( 'body_class', 'add_icon_body_class');


if ( ! function_exists( 'tnc_pvfw_add_viewer_styles' ) ) {
	add_action( 'wp_enqueue_scripts', 'tnc_pvfw_add_viewer_styles' );

	function tnc_pvfw_add_viewer_styles() {
		if ( is_singular( 'pdfviewer' ) || is_page_template( 'tnc-pdf-viewer-shortcode.php' ) || is_page_template( 'tnc-pdf-viewer.php' ) ) {
			wp_enqueue_style( 'themencode-pdf-viewer-theme-common-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/common.css', array(), PVFW_PLUGIN_VERSION, 'all' );
			// Load selected theme only.
			global $post;
			if ( is_singular( 'pdfviewer' ) ) {
				$get_pvfw_single_settings = get_post_meta( $post->ID, 'tnc_pvfw_pdf_viewer_fields', true );
				$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );

				$get_pvfw_single_type = $get_pvfw_single_settings['appearance-select-type'];
				$get_pvfw_global_type = $get_pvfw_global_settings['appearance-select-type'];

				if( array_key_exists( 'icon-size', $get_pvfw_single_settings ) ){
					$get_pvfw_icon_size   = $get_pvfw_single_settings['icon-size'];
					if( $get_pvfw_icon_size == 'global'){
					  	$get_pvfw_icon_size = $get_pvfw_global_settings['appearance-icon-size'];
					}
				} else {
					$get_pvfw_icon_size   = 'medium';
				}

				if( array_key_exists( 'select-toolbar-style', $get_pvfw_single_settings ) ){
					$get_pvfw_toolbar_style   = $get_pvfw_single_settings['select-toolbar-style'];
					if( $get_pvfw_toolbar_style == 'global'){
						$get_pvfw_toolbar_style = $get_pvfw_global_settings['appearance-select-toolbar-style'];
					}
				} else {
					$get_pvfw_toolbar_style   = 'top-full-width';
				}
			
				//Load icon size only.
				
				if( 'small' ==  $get_pvfw_icon_size) {
					wp_enqueue_style( 'themencode-pdf-viewer-small-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/icon-size/small.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} elseif ( 'large' == $get_pvfw_icon_size) {
					wp_enqueue_style('themencode-pdf-viewer-large-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/icon-size/large.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} else {
					wp_enqueue_style('themencode-pdf-viewer-medium-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/icon-size/medium.css', array(), PVFW_PLUGIN_VERSION, 'all');
				}
        
				//Load toolbar style only. 
			    
				if( 'bottom-center' ==  $get_pvfw_toolbar_style ){
					wp_enqueue_style( 'themencode-pdf-viewer-toolbar-bottom-center-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/bottom-center.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} elseif ('bottom-full-width' ==  $get_pvfw_toolbar_style ) {
					wp_enqueue_style( 'themencode-pdf-viewer-toolbar-bottom-full-width-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/bottom-full-width.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} elseif ('top-center' ==  $get_pvfw_toolbar_style ) {
					wp_enqueue_style( 'themencode-pdf-viewer-toolbar-top-center-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/top-center.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} else {
					wp_enqueue_style( 'themencode-pdf-viewer-toolbar-top-full-width-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/top-full-width.css', array(), PVFW_PLUGIN_VERSION, 'all');
				}
				 
				
				if ( $get_pvfw_single_settings['appearance-use-global-settings'] == '0' ) {
					if ( $get_pvfw_single_type == 'select-theme' ) {
						$get_pvfw_single_theme = $get_pvfw_single_settings['appearance-select-theme'];
						wp_enqueue_style( 'themencode-pdf-viewer-theme-' . $get_pvfw_single_theme, plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/' . $get_pvfw_single_theme . '.css', array(), PVFW_PLUGIN_VERSION, 'all' );
						if( $get_pvfw_single_theme == "" ){
							wp_enqueue_style( 'themencode-pdf-viewer-theme-midnight-calm', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/midnight-calm.css', array(), PVFW_PLUGIN_VERSION, 'all' );
						}
					} elseif ( $get_pvfw_single_type = 'custom-color' ) {

						$get_pvfw_single_primary_color   = str_replace( '#', '', $get_pvfw_single_settings['appearance-select-colors']['primary-color'] );
						$get_pvfw_single_secondary_color = str_replace( '#', '', $get_pvfw_single_settings['appearance-select-colors']['secondary-color'] );
						$get_pvfw_single_text_color      = str_replace( '#', '', $get_pvfw_single_settings['appearance-select-colors']['text-color'] );
						$get_pvfw_single_icon_color      = $get_pvfw_single_settings['appearance-select-icon'];

						if(empty($get_pvfw_single_primary_color)){
							$get_pvfw_single_primary_color = 'cccccc';
						}
						if(empty($get_pvfw_single_secondary_color)){
							$get_pvfw_single_secondary_color = 'DEDEDE';
						}
						if(empty($get_pvfw_single_text_color)){
							$get_pvfw_single_text_color = '232323';
						}
						if(empty($get_pvfw_single_icon_color)){
							$get_pvfw_single_icon_color = 'dark-icons';
						}

						//Add icon & toolbar style
						
						$get_pvfw_single_settings = get_post_meta( $post->ID, 'tnc_pvfw_pdf_viewer_fields', true );

						if( array_key_exists( 'icon-size', $get_pvfw_single_settings ) ){
							$get_pvfw_icon_size   = $get_pvfw_single_settings['icon-size'];
						} else {
							$get_pvfw_icon_size   = 'medium';
						}
		
						if( array_key_exists( 'select-toolbar-style', $get_pvfw_single_settings ) ){
							$get_pvfw_toolbar_style   = $get_pvfw_single_settings['select-toolbar-style'];
						} else {
							$get_pvfw_toolbar_style   = 'top-full-width';
						}
					
						//Load icon size only.
						
						if( 'small' ==  $get_pvfw_icon_size) {
							wp_enqueue_style( 'themencode-pdf-viewer-small-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/icon-size/small.css', array(), PVFW_PLUGIN_VERSION, 'all');
						} elseif ( 'large' == $get_pvfw_icon_size) {
							wp_enqueue_style('themencode-pdf-viewer-large-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/icon-size/large.css', array(), PVFW_PLUGIN_VERSION, 'all');
						} else {
							wp_enqueue_style('themencode-pdf-viewer-medium-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/icon-size/medium.css', array(), PVFW_PLUGIN_VERSION, 'all');
						}
		
						//Load toolbar style only. 
						
						if( 'bottom-center' ==  $get_pvfw_toolbar_style ){
							wp_enqueue_style( 'themencode-pdf-viewer-toolbar-bottom-center-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/bottom-center.css', array(), PVFW_PLUGIN_VERSION, 'all');
						} elseif ('bottom-full-width' ==  $get_pvfw_toolbar_style ) {
							wp_enqueue_style( 'themencode-pdf-viewer-toolbar-bottom-full-width-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/bottom-full-width.css', array(), PVFW_PLUGIN_VERSION, 'all');
						} elseif ('top-center' ==  $get_pvfw_toolbar_style ) {
							wp_enqueue_style( 'themencode-pdf-viewer-toolbar-top-center-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/top-center.css', array(), PVFW_PLUGIN_VERSION, 'all');
						} else {
							wp_enqueue_style( 'themencode-pdf-viewer-toolbar-top-full-width-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/top-full-width.css', array(), PVFW_PLUGIN_VERSION, 'all');
						}

						wp_enqueue_style( 'themencode-pdf-viewer-custom-color', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/custom.css', array(), PVFW_PLUGIN_VERSION, 'all' );

						$get_pvfw_icons_folder_dir =  plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/';
						$themencode_inline_css_single = '								
							:root {
								--primary: #' . $get_pvfw_single_primary_color . ';
								--secondary: #' . $get_pvfw_single_secondary_color . ';
								--textc: #' . $get_pvfw_single_text_color . ';
								--icon_color: ' . $get_pvfw_single_icon_color . ';
							}

							:root {
								--loading-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/loading.svg);
								--treeitem-expanded-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/treeitem-expanded.svg);
								--treeitem-collapsed-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/treeitem-collapsed.svg);
								--toolbarButton-menuArrow-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-menuArrow.svg);
								--toolbarButton-sidebarToggle-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-sidebarToggle.svg);
								--toolbarButton-secondaryToolbarToggle-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-secondaryToolbarToggle.svg);
								--toolbarButton-pageUp-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-pageUp.svg);
								--toolbarButton-pageDown-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-pageDown.svg);
								--toolbarButton-zoomOut-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-zoomOut.svg);
								--toolbarButton-zoomIn-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-zoomIn.svg);
								--toolbarButton-presentationMode-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-presentationMode.svg);
								--toolbarButton-print-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-print.svg);
								--toolbarButton-openFile-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-openFile.svg);
								--toolbarButton-download-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-download.svg);
								--toolbarButton-bookmark-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-bookmark.svg);
								--toolbarButton-viewThumbnail-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-viewThumbnail.svg);
								--toolbarButton-viewOutline-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-viewOutline.svg);
								--toolbarButton-viewAttachments-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-viewAttachments.svg);
								--toolbarButton-viewLayers-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-viewLayers.svg);
								--toolbarButton-currentOutlineItem-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-currentOutlineItem.svg);
								--toolbarButton-search-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-search.svg);
								--findbarButton-previous-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/findbarButton-previous.svg);
								--findbarButton-next-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/findbarButton-next.svg);
								--secondaryToolbarButton-firstPage-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-firstPage.svg);
								--secondaryToolbarButton-lastPage-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-lastPage.svg);
								--secondaryToolbarButton-rotateCcw-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-rotateCcw.svg);
								--secondaryToolbarButton-rotateCw-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-rotateCw.svg);
								--secondaryToolbarButton-selectTool-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-selectTool.svg);
								--secondaryToolbarButton-handTool-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-handTool.svg);
								--secondaryToolbarButton-scrollVertical-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-scrollVertical.svg);
								--secondaryToolbarButton-scrollHorizontal-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-scrollHorizontal.svg);
								--secondaryToolbarButton-scrollWrapped-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-scrollWrapped.svg);
								--secondaryToolbarButton-spreadNone-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-spreadNone.svg);
								--secondaryToolbarButton-spreadOdd-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-spreadOdd.svg);
								--secondaryToolbarButton-spreadEven-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-spreadEven.svg);
								--secondaryToolbarButton-documentProperties-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-documentProperties.svg);
							}

							/* Retina */
							@media screen and (-webkit-min-device-pixel-ratio: 2), screen and (min-resolution: 2dppx){

								/* Rules for Retina screens */
								.toolbarButton::before { -webkit-transform: scale(0.5); transform: scale(0.5);}
								.secondaryToolbarButton::before { -webkit-transform: scale(0.5); transform: scale(0.5);}
								.toolbarButton::before,
								html[dir="rtl"] .toolbarButton::before {  }
								.secondaryToolbarButton::before {  }
								html[dir="rtl"] .secondaryToolbarButton::before {  }
								.toolbarField.pageNumber.visiblePageIsLoading,


								.toolbarButton#sidebarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-sidebarToggle.svg); }
								html[dir="rtl"] .toolbarButton#sidebarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-sidebarToggle-rtl.svg);}
								.toolbarButton#secondaryToolbarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-secondaryToolbarToggle.svg);}
								html[dir="rtl"] .toolbarButton#secondaryToolbarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-secondaryToolbarToggle-rtl.svg);}
								.toolbarButton.findPrevious::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/findbarButton-previous.svg);}
								html[dir="rtl"] .toolbarButton.findPrevious::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/findbarButton-previous-rtl.svg);}
								.toolbarButton.findNext::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/findbarButton-next.svg);}
								html[dir="rtl"] .toolbarButton.findNext::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/findbarButton-next-rtl.svg);}
								.toolbarButton.pageUp::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-pageUp.svg);}
								html[dir="rtl"] .toolbarButton.pageUp::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-pageUp-rtl.svg);}
								.toolbarButton.pageDown::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-pageDown.svg);}
								html[dir="rtl"] .toolbarButton.pageDown::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-pageDown-rtl.svg);}
								.toolbarButton.zoomIn::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-zoomIn.svg);}
								.toolbarButton.zoomOut::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-zoomOut.svg);}
								.toolbarButton.presentationMode::before,
								.secondaryToolbarButton.presentationMode::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-presentationMode.svg);}
								.toolbarButton.print::before,
								.secondaryToolbarButton.print::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-print.svg);}
								.toolbarButton.openFile::before,
								.secondaryToolbarButton.openFile::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-openFile.svg);}
								.toolbarButton.download::before,
								.secondaryToolbarButton.download::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-download.svg);}
								.toolbarButton.bookmark::before,
								.secondaryToolbarButton.bookmark::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-bookmark.svg);}
								#viewThumbnail.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-viewThumbnail.svg);}
								#viewOutline.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-viewOutline.svg);}
								html[dir="rtl"] #viewOutline.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-viewOutline-rtl.svg);}
								#viewAttachments.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-viewAttachments.svg);}
								#viewFind.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/toolbarButton-search.svg);}
								.secondaryToolbarButton.firstPage::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-firstPage.svg);}
								.secondaryToolbarButton.lastPage::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-lastPage.svg);}
								.secondaryToolbarButton.rotateCcw::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-rotateCcw.svg);}
								.secondaryToolbarButton.rotateCw::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-rotateCw.svg);}
								.secondaryToolbarButton.handTool::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-handTool.svg);}
								.secondaryToolbarButton.documentProperties::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/secondaryToolbarButton-documentProperties.svg);}



								.outlineItemToggler::before { -webkit-transform: scale(0.5); transform: scale(0.5); top: -1px; content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/treeitem-expanded.svg); }

								.outlineItemToggler.outlineItemsHidden::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/treeitem-collapsed.svg);}
								html[dir="rtl"] .outlineItemToggler.outlineItemsHidden::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/treeitem-collapsed-rtl.svg);}



								.outlineItemToggler::before { right: 0; }
								html[dir="rtl"] .outlineItemToggler::before { left: 0; }
								.social_icon_d { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/share.svg) no-repeat;}
								.tnc_fb { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/facebook.svg) no-repeat left; text-indent: -999em; }
								.tnc_tw { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/twitter.svg) no-repeat left;  text-indent: -999em; }
								.tnc_lin { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/linkedin.svg) no-repeat left; text-indent: -999em; }
								.tnc_whatsapp { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/whatsapp.svg) no-repeat left; text-indent: -999em; }
								.tnc_email { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/email.svg) no-repeat left;text-indent: -999em; }
							}

							.pdfViewer .page .loadingIcon {
								position: absolute;
								display: block;
								left: 0;
								top: 0;
								right: 0;
								bottom: 0;
								background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/loading-icon.gif) center no-repeat;
							}

							.grab-to-pan-grab {
								cursor: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/grab.cur), move !important;
								cursor: -webkit-grab !important;
								cursor: grab !important;
							}

							.grab-to-pan-grab:active,
							.grab-to-pan-grabbing {
								cursor: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/grabbing.cur), move !important;
								cursor: -webkit-grabbing !important;
								cursor: grabbing !important;
								position: fixed;
								background: rgba(0, 0, 0, 0);
								display: block;
								top: 0;
								left: 0;
								right: 0;
								bottom: 0;
								overflow: hidden;
								z-index: 50000; /* should be higher than anything else in PDF.js! */
							}

							/* TNC FlipBook - PDF viewer for WordPress Stylesheet
							Developed by ThemeNcode 
							*/
							.tnc_social_share { display: table; background: var(--secondary); }
							.tnc_social_share ul { padding: 0; }
							.tnc_social_share ul li { float: left;  list-style: none; color: #999; }
							.tnc_social_share ul li a { display: block; color: #999;}
							.tnc_social_share ul li a.tnc_share { font-weight: bold;  text-decoration: none; color: #2C3E50; }
							.social_icon_d { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/share.svg) no-repeat;}
							.tnc_fb { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/facebook.svg) no-repeat left; text-indent: -999em; }
							.tnc_tw { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/twitter.svg) no-repeat left;  text-indent: -999em; }
							.tnc_lin { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/linkedin.svg) no-repeat left; text-indent: -999em; }
							.tnc_whatsapp { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/whatsapp.svg) no-repeat left; text-indent: -999em; }
							.tnc_email { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_single_icon_color .'/email.svg) no-repeat left;text-indent: -999em; }
							.logo_text a { color: var(--textc); font-weight: bold; text-decoration: none; }
		

						';
						wp_add_inline_style('themencode-pdf-viewer-custom-color', $themencode_inline_css_single );

						if( $get_pvfw_single_icon_color == "light-icons" ){
							$themencode_inline_css_single_condition = '
								.tnc-pdf-back-to-btn a{
									color: #fff;
									text-decoration: none;
								}

								.toolbarButton::before, 
								.secondaryToolbarButton::before, 
								.dropdownToolbarButton::after, 
								.treeItemToggler::before {
									background-color: #fff;
								}
							';
							wp_add_inline_style('themencode-pdf-viewer-custom-color', $themencode_inline_css_single_condition );
						} else {
							$themencode_inline_css_single_condition = '
								.tnc-pdf-back-to-btn a{
									color: #000;
									text-decoration: none;
								}

								.toolbarButton::before, 
								.secondaryToolbarButton::before, 
								.dropdownToolbarButton::after, 
								.treeItemToggler::before {
									background-color: #000;
								}
							';
							wp_add_inline_style('themencode-pdf-viewer-custom-color', $themencode_inline_css_single_condition );
						}
					}
				} else {
					if ( $get_pvfw_global_type == 'select-theme' ) {
						$get_pvfw_global_theme = $get_pvfw_global_settings['appearance-select-theme'];
						wp_enqueue_style( 'themencode-pdf-viewer-theme-' . $get_pvfw_global_theme, plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/' . $get_pvfw_global_theme . '.css', array(), PVFW_PLUGIN_VERSION, 'all' );
						if( $get_pvfw_global_theme == "" ){
							wp_enqueue_style( 'themencode-pdf-viewer-theme-midnight-calm', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/midnight-calm.css', array(), PVFW_PLUGIN_VERSION, 'all' );
						}
					} elseif ( $get_pvfw_global_type = 'custom-color' ) {

						$get_pvfw_global_primary_color   = str_replace( '#', '', $get_pvfw_global_settings['appearance-select-colors']['primary-color'] );
						$get_pvfw_global_secondary_color = str_replace( '#', '', $get_pvfw_global_settings['appearance-select-colors']['secondary-color'] );
						$get_pvfw_global_text_color      = str_replace( '#', '', $get_pvfw_global_settings['appearance-select-colors']['text-color'] );
						$get_pvfw_global_icon_color      = $get_pvfw_global_settings['appearance-select-icon'];

						if(empty($get_pvfw_global_primary_color)){
							$get_pvfw_global_primary_color = 'cccccc';
						}
						if(empty($get_pvfw_global_secondary_color)){
							$get_pvfw_global_secondary_color = 'DEDEDE';
						}
						if(empty($get_pvfw_global_text_color)){
							$get_pvfw_global_text_color = '232323';
						}
						if(empty($get_pvfw_global_icon_color)){
							$get_pvfw_global_icon_color = 'dark-icons';
						}

						wp_enqueue_style( 'themencode-pdf-viewer-custom-color', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/custom.css', array(), PVFW_PLUGIN_VERSION, 'all' );

						$get_pvfw_icons_folder_dir =  plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/';
						$themencode_inline_css_global = '								
							:root {
								--primary: #' . $get_pvfw_global_primary_color . ';
								--secondary: #' . $get_pvfw_global_secondary_color . ';
								--textc: #' . $get_pvfw_global_text_color . ';
								--icon_color: ' . $get_pvfw_global_icon_color . ';
							}

							:root {
								--loading-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/loading.svg);
								--treeitem-expanded-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-expanded.svg);
								--treeitem-collapsed-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-collapsed.svg);
								--toolbarButton-menuArrow-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-menuArrow.svg);
								--toolbarButton-sidebarToggle-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-sidebarToggle.svg);
								--toolbarButton-secondaryToolbarToggle-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-secondaryToolbarToggle.svg);
								--toolbarButton-pageUp-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageUp.svg);
								--toolbarButton-pageDown-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageDown.svg);
								--toolbarButton-zoomOut-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-zoomOut.svg);
								--toolbarButton-zoomIn-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-zoomIn.svg);
								--toolbarButton-presentationMode-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-presentationMode.svg);
								--toolbarButton-print-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-print.svg);
								--toolbarButton-openFile-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-openFile.svg);
								--toolbarButton-download-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-download.svg);
								--toolbarButton-bookmark-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-bookmark.svg);
								--toolbarButton-viewThumbnail-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewThumbnail.svg);
								--toolbarButton-viewOutline-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewOutline.svg);
								--toolbarButton-viewAttachments-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewAttachments.svg);
								--toolbarButton-viewLayers-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewLayers.svg);
								--toolbarButton-currentOutlineItem-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-currentOutlineItem.svg);
								--toolbarButton-search-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-search.svg);
								--findbarButton-previous-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-previous.svg);
								--findbarButton-next-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-next.svg);
								--secondaryToolbarButton-firstPage-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-firstPage.svg);
								--secondaryToolbarButton-lastPage-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-lastPage.svg);
								--secondaryToolbarButton-rotateCcw-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-rotateCcw.svg);
								--secondaryToolbarButton-rotateCw-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-rotateCw.svg);
								--secondaryToolbarButton-selectTool-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-selectTool.svg);
								--secondaryToolbarButton-handTool-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-handTool.svg);
								--secondaryToolbarButton-scrollVertical-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-scrollVertical.svg);
								--secondaryToolbarButton-scrollHorizontal-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-scrollHorizontal.svg);
								--secondaryToolbarButton-scrollWrapped-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-scrollWrapped.svg);
								--secondaryToolbarButton-spreadNone-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-spreadNone.svg);
								--secondaryToolbarButton-spreadOdd-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-spreadOdd.svg);
								--secondaryToolbarButton-spreadEven-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-spreadEven.svg);
								--secondaryToolbarButton-documentProperties-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-documentProperties.svg);
							}

							/* Retina */
							@media screen and (-webkit-min-device-pixel-ratio: 2), screen and (min-resolution: 2dppx){

								/* Rules for Retina screens */
								.toolbarButton::before { -webkit-transform: scale(0.5); transform: scale(0.5); top: -10px; }
								.secondaryToolbarButton::before { -webkit-transform: scale(0.5); transform: scale(0.5); top: -10px; }
								.toolbarButton::before,
								html[dir="rtl"] .toolbarButton::before { left: -7px; }
								.secondaryToolbarButton::before { left: -2px; }
								html[dir="rtl"] .secondaryToolbarButton::before { left: 186px; }
								.toolbarField.pageNumber.visiblePageIsLoading,


								.toolbarButton#sidebarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-sidebarToggle.svg); width: 46px; height: 46px; }
								html[dir="rtl"] .toolbarButton#sidebarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-sidebarToggle-rtl.svg); width: 46px; height: 46px; }
								.toolbarButton#secondaryToolbarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-secondaryToolbarToggle.svg); width: 46px; height: 46px; }
								html[dir="rtl"] .toolbarButton#secondaryToolbarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-secondaryToolbarToggle-rtl.svg); width: 46px; height: 46px; }
								.toolbarButton.findPrevious::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-previous.svg); width: 46px; height: 46px; }
								html[dir="rtl"] .toolbarButton.findPrevious::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-previous-rtl.svg); width: 46px; height: 46px; }
								.toolbarButton.findNext::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-next.svg); width: 46px; height: 46px; }
								html[dir="rtl"] .toolbarButton.findNext::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-next-rtl.svg); width: 46px; height: 46px; }
								.toolbarButton.pageUp::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageUp.svg); width: 46px; height: 46px; }
								html[dir="rtl"] .toolbarButton.pageUp::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageUp-rtl.svg); width: 46px; height: 46px; }
								.toolbarButton.pageDown::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageDown.svg); width: 46px; height: 46px; }
								html[dir="rtl"] .toolbarButton.pageDown::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageDown-rtl.svg); width: 46px; height: 46px; }
								.toolbarButton.zoomIn::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-zoomIn.svg); width: 46px; height: 46px; }
								.toolbarButton.zoomOut::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-zoomOut.svg); width: 46px; height: 46px; }
								.toolbarButton.presentationMode::before,
								.secondaryToolbarButton.presentationMode::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-presentationMode.svg); width: 46px; height: 46px; }
								.toolbarButton.print::before,
								.secondaryToolbarButton.print::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-print.svg); width: 46px; height: 46px; }
								.toolbarButton.openFile::before,
								.secondaryToolbarButton.openFile::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-openFile.svg); width: 46px; height: 46px; }
								.toolbarButton.download::before,
								.secondaryToolbarButton.download::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-download.svg); width: 46px; height: 46px; }
								.toolbarButton.bookmark::before,
								.secondaryToolbarButton.bookmark::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-bookmark.svg); width: 46px; height: 46px; }
								#viewThumbnail.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewThumbnail.svg); width: 46px; height: 46px; }
								#viewOutline.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewOutline.svg); width: 46px; height: 46px; }
								html[dir="rtl"] #viewOutline.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewOutline-rtl.svg); width: 46px; height: 46px; }
								#viewAttachments.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewAttachments.svg); width: 46px; height: 46px; }
								#viewFind.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-search.svg); width: 46px; height: 46px; }
								.secondaryToolbarButton.firstPage::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-firstPage.svg); width: 46px; height: 46px; }
								.secondaryToolbarButton.lastPage::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-lastPage.svg); width: 46px; height: 46px; }
								.secondaryToolbarButton.rotateCcw::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-rotateCcw.svg); width: 46px; height: 46px; }
								.secondaryToolbarButton.rotateCw::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-rotateCw.svg); width: 46px; height: 46px; }
								.secondaryToolbarButton.handTool::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-handTool.svg); width: 46px; height: 46px; }
								.secondaryToolbarButton.documentProperties::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-documentProperties.svg); width: 46px; height: 46px; }



								.outlineItemToggler::before { -webkit-transform: scale(0.5); transform: scale(0.5); top: -1px; content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-expanded.svg); width: 46px; height: 46px; }

								.outlineItemToggler.outlineItemsHidden::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-collapsed.svg); width: 46px; height: 46px; }
								html[dir="rtl"] .outlineItemToggler.outlineItemsHidden::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-collapsed-rtl.svg); width: 46px; height: 46px; }



								.outlineItemToggler::before { right: 0; }
								html[dir="rtl"] .outlineItemToggler::before { left: 0; }
								.social_icon_d { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/share.svg) no-repeat; background-size: 23px 23px; margin: 5px 0; width: 23px; height: 23px; }
								.tnc_fb { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/facebook.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
								.tnc_tw { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/twitter.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
								.tnc_lin { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/linkedin.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
								.tnc_whatsapp { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/whatsapp.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
								.tnc_email { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/email.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							}

							.pdfViewer .page .loadingIcon {
								position: absolute;
								display: block;
								left: 0;
								top: 0;
								right: 0;
								bottom: 0;
								background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/loading-icon.gif) center no-repeat;
							}

							.grab-to-pan-grab {
								cursor: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/grab.cur), move !important;
								cursor: -webkit-grab !important;
								cursor: grab !important;
							}

							.grab-to-pan-grab:active,
							.grab-to-pan-grabbing {
								cursor: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/grabbing.cur), move !important;
								cursor: -webkit-grabbing !important;
								cursor: grabbing !important;
								position: fixed;
								background: rgba(0, 0, 0, 0);
								display: block;
								top: 0;
								left: 0;
								right: 0;
								bottom: 0;
								overflow: hidden;
								z-index: 50000; /* should be higher than anything else in PDF.js! */
							}

							/* TNC FlipBook - PDF viewer for WordPress Stylesheet
							Developed by ThemeNcode 
							*/
							.tnc_social_share { display: table; margin: 5px 10px; background: var(--secondary); }
							.tnc_social_share ul { padding: 0; }
							.tnc_social_share ul li { float: left; margin: 0 5px; list-style: none; color: #999; }
							.tnc_social_share ul li a { display: block; color: #999; width: 24px; height: 24px; }
							.tnc_social_share ul li a.tnc_share { width: 42px; padding-top: 4px; font-weight: bold; font-size: 14px; text-decoration: none; color: #2C3E50; }
							.social_icon_d { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/share.svg) no-repeat; background-size: 23px 23px; margin: 5px 0; width: 23px; height: 23px; }
							.tnc_fb { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/facebook.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							.tnc_tw { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/twitter.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							.tnc_lin { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/linkedin.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							.tnc_whatsapp { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/whatsapp.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							.tnc_email { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/email.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							.logo_text a { font-size: 18px; color: var(--textc); padding: 8px 10px 0 0; font-weight: bold; text-decoration: none; }
							.logo_block { margin-right: 20px; margin-top: 7px; }

						';
						wp_add_inline_style('themencode-pdf-viewer-custom-color', $themencode_inline_css_global );

						if( $get_pvfw_global_icon_color == "light-icons" ){
							$themencode_inline_css_single_condition = '
								.tnc-pdf-back-to-btn a{
									color: #fff;
									text-decoration: none;
								}

								.toolbarButton::before, 
								.secondaryToolbarButton::before, 
								.dropdownToolbarButton::after, 
								.treeItemToggler::before {
									background-color: #fff;
								}
							';
							wp_add_inline_style('themencode-pdf-viewer-custom-color', $themencode_inline_css_single_condition );
						} else {
							$themencode_inline_css_single_condition = '
								.tnc-pdf-back-to-btn a{
									color: #000;
									text-decoration: none;
								}

								.toolbarButton::before, 
								.secondaryToolbarButton::before, 
								.dropdownToolbarButton::after, 
								.treeItemToggler::before {
									background-color: #000;
								}
							';
							wp_add_inline_style('themencode-pdf-viewer-custom-color', $themencode_inline_css_single_condition );
						}
					}
				}
			} else {
				$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );
				$get_pvfw_global_type     = $get_pvfw_global_settings['appearance-select-type'];
				$get_pvfw_toolbar_style = $get_pvfw_global_settings['appearance-select-toolbar-style'];
				$get_pvfw_icon_size = $get_pvfw_global_settings['appearance-icon-size'];

				if( empty( $get_pvfw_toolbar_style ) ){
					$get_pvfw_toolbar_style   = 'toolbar-top-full-width';
				}

				if( empty( $get_pvfw_icon_size ) ){
					$get_pvfw_toolbar_style   = 'medium';
				}

				//Load icon size only.
				
				if( 'small' ==  $get_pvfw_icon_size) {
					wp_enqueue_style( 'themencode-pdf-viewer-small-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/icon-size/small.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} elseif ( 'large' == $get_pvfw_icon_size) {
					wp_enqueue_style('themencode-pdf-viewer-large-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/icon-size/large.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} else {
					wp_enqueue_style('themencode-pdf-viewer-medium-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/icon-size/medium.css', array(), PVFW_PLUGIN_VERSION, 'all');
				}

				//Load toolbar style only. 
			    
				if( 'bottom-center' ==  $get_pvfw_toolbar_style ){
					wp_enqueue_style( 'themencode-pdf-viewer-toolbar-bottom-center-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/bottom-center.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} elseif ('bottom-full-width' ==  $get_pvfw_toolbar_style ) {
					wp_enqueue_style( 'themencode-pdf-viewer-toolbar-bottom-full-width-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/bottom-full-width.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} elseif ('top-center' ==  $get_pvfw_toolbar_style ) {
					wp_enqueue_style( 'themencode-pdf-viewer-toolbar-top-center-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/top-center.css', array(), PVFW_PLUGIN_VERSION, 'all');
				} else {
					wp_enqueue_style( 'themencode-pdf-viewer-toolbar-top-full-width-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/toolbar-style/top-full-width.css', array(), PVFW_PLUGIN_VERSION, 'all');
				}

				wp_enqueue_style( 'themencode-pdf-viewer-theme-common-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/common.css', array(), PVFW_PLUGIN_VERSION, 'all' );

				if ( $get_pvfw_global_type == 'select-theme' ) {
					$get_pvfw_global_theme = $get_pvfw_global_settings['appearance-select-theme'];
					wp_enqueue_style( 'themencode-pdf-viewer-theme-' . $get_pvfw_global_theme, plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/' . $get_pvfw_global_theme . '.css', array(), PVFW_PLUGIN_VERSION, 'all' );
				} elseif ( $get_pvfw_global_type = 'custom-color' ) {

					$get_pvfw_global_primary_color   = str_replace( '#', '', $get_pvfw_global_settings['appearance-select-colors']['primary-color'] );
					$get_pvfw_global_secondary_color = str_replace( '#', '', $get_pvfw_global_settings['appearance-select-colors']['secondary-color'] );
					$get_pvfw_global_text_color      = str_replace( '#', '', $get_pvfw_global_settings['appearance-select-colors']['text-color'] );
					$get_pvfw_global_icon_color      = $get_pvfw_global_settings['appearance-select-icon'];

					if(empty($get_pvfw_global_primary_color)){
						$get_pvfw_global_primary_color = 'cccccc';
					}
					if(empty($get_pvfw_global_secondary_color)){
						$get_pvfw_global_secondary_color = 'DEDEDE';
					}
					if(empty($get_pvfw_global_text_color)){
						$get_pvfw_global_text_color = '232323';
					}
					if(empty($get_pvfw_global_icon_color)){
						$get_pvfw_global_icon_color = 'dark-icons';
					}

					wp_enqueue_style( 'themencode-pdf-viewer-custom-color', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/custom.css', array(), PVFW_PLUGIN_VERSION, 'all' );

					$get_pvfw_icons_folder_dir =  plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/schemes/';
					$themencode_inline_css_global = '								
						:root {
							--primary: #' . $get_pvfw_global_primary_color . ';
							--secondary: #' . $get_pvfw_global_secondary_color . ';
							--textc: #' . $get_pvfw_global_text_color . ';
							--icon_color: ' . $get_pvfw_global_icon_color . ';
						}

						:root {
							--loading-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/loading.svg);
							--treeitem-expanded-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-expanded.svg);
							--treeitem-collapsed-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-collapsed.svg);
							--toolbarButton-menuArrow-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-menuArrow.svg);
							--toolbarButton-sidebarToggle-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-sidebarToggle.svg);
							--toolbarButton-secondaryToolbarToggle-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-secondaryToolbarToggle.svg);
							--toolbarButton-pageUp-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageUp.svg);
							--toolbarButton-pageDown-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageDown.svg);
							--toolbarButton-zoomOut-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-zoomOut.svg);
							--toolbarButton-zoomIn-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-zoomIn.svg);
							--toolbarButton-presentationMode-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-presentationMode.svg);
							--toolbarButton-print-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-print.svg);
							--toolbarButton-openFile-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-openFile.svg);
							--toolbarButton-download-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-download.svg);
							--toolbarButton-bookmark-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-bookmark.svg);
							--toolbarButton-viewThumbnail-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewThumbnail.svg);
							--toolbarButton-viewOutline-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewOutline.svg);
							--toolbarButton-viewAttachments-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewAttachments.svg);
							--toolbarButton-viewLayers-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewLayers.svg);
							--toolbarButton-currentOutlineItem-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-currentOutlineItem.svg);
							--toolbarButton-search-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-search.svg);
							--findbarButton-previous-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-previous.svg);
							--findbarButton-next-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-next.svg);
							--secondaryToolbarButton-firstPage-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-firstPage.svg);
							--secondaryToolbarButton-lastPage-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-lastPage.svg);
							--secondaryToolbarButton-rotateCcw-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-rotateCcw.svg);
							--secondaryToolbarButton-rotateCw-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-rotateCw.svg);
							--secondaryToolbarButton-selectTool-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-selectTool.svg);
							--secondaryToolbarButton-handTool-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-handTool.svg);
							--secondaryToolbarButton-scrollVertical-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-scrollVertical.svg);
							--secondaryToolbarButton-scrollHorizontal-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-scrollHorizontal.svg);
							--secondaryToolbarButton-scrollWrapped-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-scrollWrapped.svg);
							--secondaryToolbarButton-spreadNone-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-spreadNone.svg);
							--secondaryToolbarButton-spreadOdd-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-spreadOdd.svg);
							--secondaryToolbarButton-spreadEven-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-spreadEven.svg);
							--secondaryToolbarButton-documentProperties-icon: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-documentProperties.svg);
						}

						/* Retina */
						@media screen and (-webkit-min-device-pixel-ratio: 2), screen and (min-resolution: 2dppx){

							/* Rules for Retina screens */
							.toolbarButton::before { -webkit-transform: scale(0.5); transform: scale(0.5); top: -10px; }
							.secondaryToolbarButton::before { -webkit-transform: scale(0.5); transform: scale(0.5); top: -10px; }
							.toolbarButton::before,
							html[dir="rtl"] .toolbarButton::before { left: -7px; }
							.secondaryToolbarButton::before { left: -2px; }
							html[dir="rtl"] .secondaryToolbarButton::before { left: 186px; }
							.toolbarField.pageNumber.visiblePageIsLoading,


							.toolbarButton#sidebarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-sidebarToggle.svg); width: 46px; height: 46px; }
							html[dir="rtl"] .toolbarButton#sidebarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-sidebarToggle-rtl.svg); width: 46px; height: 46px; }
							.toolbarButton#secondaryToolbarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-secondaryToolbarToggle.svg); width: 46px; height: 46px; }
							html[dir="rtl"] .toolbarButton#secondaryToolbarToggle::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-secondaryToolbarToggle-rtl.svg); width: 46px; height: 46px; }
							.toolbarButton.findPrevious::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-previous.svg); width: 46px; height: 46px; }
							html[dir="rtl"] .toolbarButton.findPrevious::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-previous-rtl.svg); width: 46px; height: 46px; }
							.toolbarButton.findNext::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-next.svg); width: 46px; height: 46px; }
							html[dir="rtl"] .toolbarButton.findNext::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/findbarButton-next-rtl.svg); width: 46px; height: 46px; }
							.toolbarButton.pageUp::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageUp.svg); width: 46px; height: 46px; }
							html[dir="rtl"] .toolbarButton.pageUp::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageUp-rtl.svg); width: 46px; height: 46px; }
							.toolbarButton.pageDown::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageDown.svg); width: 46px; height: 46px; }
							html[dir="rtl"] .toolbarButton.pageDown::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-pageDown-rtl.svg); width: 46px; height: 46px; }
							.toolbarButton.zoomIn::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-zoomIn.svg); width: 46px; height: 46px; }
							.toolbarButton.zoomOut::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-zoomOut.svg); width: 46px; height: 46px; }
							.toolbarButton.presentationMode::before,
							.secondaryToolbarButton.presentationMode::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-presentationMode.svg); width: 46px; height: 46px; }
							.toolbarButton.print::before,
							.secondaryToolbarButton.print::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-print.svg); width: 46px; height: 46px; }
							.toolbarButton.openFile::before,
							.secondaryToolbarButton.openFile::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-openFile.svg); width: 46px; height: 46px; }
							.toolbarButton.download::before,
							.secondaryToolbarButton.download::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-download.svg); width: 46px; height: 46px; }
							.toolbarButton.bookmark::before,
							.secondaryToolbarButton.bookmark::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-bookmark.svg); width: 46px; height: 46px; }
							#viewThumbnail.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewThumbnail.svg); width: 46px; height: 46px; }
							#viewOutline.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewOutline.svg); width: 46px; height: 46px; }
							html[dir="rtl"] #viewOutline.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewOutline-rtl.svg); width: 46px; height: 46px; }
							#viewAttachments.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-viewAttachments.svg); width: 46px; height: 46px; }
							#viewFind.toolbarButton::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/toolbarButton-search.svg); width: 46px; height: 46px; }
							.secondaryToolbarButton.firstPage::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-firstPage.svg); width: 46px; height: 46px; }
							.secondaryToolbarButton.lastPage::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-lastPage.svg); width: 46px; height: 46px; }
							.secondaryToolbarButton.rotateCcw::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-rotateCcw.svg); width: 46px; height: 46px; }
							.secondaryToolbarButton.rotateCw::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-rotateCw.svg); width: 46px; height: 46px; }
							.secondaryToolbarButton.handTool::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-handTool.svg); width: 46px; height: 46px; }
							.secondaryToolbarButton.documentProperties::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/secondaryToolbarButton-documentProperties.svg); width: 46px; height: 46px; }



							.outlineItemToggler::before { -webkit-transform: scale(0.5); transform: scale(0.5); top: -1px; content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-expanded.svg); width: 46px; height: 46px; }

							.outlineItemToggler.outlineItemsHidden::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-collapsed.svg); width: 46px; height: 46px; }
							html[dir="rtl"] .outlineItemToggler.outlineItemsHidden::before { content: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/treeitem-collapsed-rtl.svg); width: 46px; height: 46px; }



							.outlineItemToggler::before { right: 0; }
							html[dir="rtl"] .outlineItemToggler::before { left: 0; }
							.social_icon_d { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/share.svg) no-repeat; background-size: 23px 23px; margin: 5px 0; width: 23px; height: 23px; }
							.tnc_fb { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/facebook.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							.tnc_tw { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/twitter.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							.tnc_lin { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/linkedin.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							.tnc_whatsapp { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/whatsapp.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
							.tnc_email { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/email.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
						}

						.pdfViewer .page .loadingIcon {
							position: absolute;
							display: block;
							left: 0;
							top: 0;
							right: 0;
							bottom: 0;
							background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/loading-icon.gif) center no-repeat;
						}

						.grab-to-pan-grab {
							cursor: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/grab.cur), move !important;
							cursor: -webkit-grab !important;
							cursor: grab !important;
						}

						.grab-to-pan-grab:active,
						.grab-to-pan-grabbing {
							cursor: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/grabbing.cur), move !important;
							cursor: -webkit-grabbing !important;
							cursor: grabbing !important;
							position: fixed;
							background: rgba(0, 0, 0, 0);
							display: block;
							top: 0;
							left: 0;
							right: 0;
							bottom: 0;
							overflow: hidden;
							z-index: 50000; /* should be higher than anything else in PDF.js! */
						}

						/* TNC FlipBook - PDF viewer for WordPress Stylesheet
						Developed by ThemeNcode 
						*/
						.tnc_social_share { display: table; margin: 5px 10px; background: var(--secondary); }
						.tnc_social_share ul { padding: 0; }
						.tnc_social_share ul li { float: left; margin: 0 5px; list-style: none; color: #999; }
						.tnc_social_share ul li a { display: block; color: #999; width: 24px; height: 24px; }
						.tnc_social_share ul li a.tnc_share { width: 42px; padding-top: 4px; font-weight: bold; font-size: 14px; text-decoration: none; color: #2C3E50; }
						.social_icon_d { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/share.svg) no-repeat; background-size: 23px 23px; margin: 5px 0; width: 23px; height: 23px; }
						.tnc_fb { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/facebook.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
						.tnc_tw { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/twitter.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
						.tnc_lin { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/linkedin.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
						.tnc_whatsapp { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/whatsapp.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
						.tnc_email { background: url('.$get_pvfw_icons_folder_dir . $get_pvfw_global_icon_color .'/email.svg) no-repeat left; background-size: 23px 23px; text-indent: -999em; }
						.logo_text a { font-size: 18px; color: var(--textc); padding: 8px 10px 0 0; font-weight: bold; text-decoration: none; }
						.logo_block { margin-right: 20px; margin-top: 7px; }

					';
					wp_add_inline_style('themencode-pdf-viewer-custom-color', $themencode_inline_css_global );

					if( $get_pvfw_global_icon_color == "light-icons" ){
						$themencode_inline_css_single_condition = '
							.tnc-pdf-back-to-btn a{
								color: #fff;
								text-decoration: none;
							}
						';
						wp_add_inline_style('themencode-pdf-viewer-custom-color', $themencode_inline_css_single_condition );
					} else {
						$themencode_inline_css_single_condition = '
							.tnc-pdf-back-to-btn a{
								color: #000;
								text-decoration: none;
							}
						';
						wp_add_inline_style('themencode-pdf-viewer-custom-color', $themencode_inline_css_single_condition );
					}
				}
			}
			wp_enqueue_style( 'themencode-pdf-viewer-modal-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/tnc-resources/jquery.modal.min.css', array(), PVFW_PLUGIN_VERSION, 'all' );

			wp_enqueue_style( 'themencode-pdf-viewer-pdf-turn-css', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/web/pdf-turn/pdf-turn.css', array(), PVFW_PLUGIN_VERSION, 'all' );
			$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );
			$get_pvfw_custom_css      = $get_pvfw_global_settings['custom-css'];
			wp_add_inline_style(
				'themencode-pdf-viewer-pdf-turn-css',
				$get_pvfw_custom_css
			);
		}
	}
}

