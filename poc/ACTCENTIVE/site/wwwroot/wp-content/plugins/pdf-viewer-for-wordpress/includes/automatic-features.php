<?php
/**
 * Automatic Features
 * with PDF Vuewer for WordPress
 *
 * @package pdf-viewer-for-wordpress
 */

// get_settings.
$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );

if( isset( $get_pvfw_global_settings['select-automatic-display'] )){
	$auto_add = $get_pvfw_global_settings['select-automatic-display'];
}


if( isset( $auto_add ) && ! empty( $auto_add ) ) {

	/**
	 * Automatic Link
	 */
	function tnc_pdf_autolink() {
		$get_pvfw_global_settings  = get_option( 'pvfw_csf_options' );
		$tnc_al_get_viewer_page_id = $get_pvfw_global_settings['advanced-pdf-viewer-page'];

		if ( empty( $tnc_al_get_viewer_page_id ) ) {
			$tnc_al_get_viewer_page_id = get_option( 'tnc_pdf_viewer_page_id', false );
		}
		$tnc_pvfw_default_zoom = $get_pvfw_global_settings['toolbar-default-zoom'];
		$tnc_pvfw_default_pagemode = $get_pvfw_global_settings['toolbar-default-page-mode'];
		$viewer_url = get_permalink( $tnc_al_get_viewer_page_id );
		$pdf_al     = wp_parse_url( $viewer_url, PHP_URL_QUERY );
		if ( $pdf_al ) {
			$viewer_url .= '&file=';
		} else {
			$viewer_url .= '?file=';
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {		
				var gethost = new RegExp(location.host);
				jQuery("a[href$='.pdf']").each(function() {
					if(gethost.test(jQuery(this).attr('href'))){
						var _href = jQuery(this).attr("href");
						jQuery(this).attr("href", '<?php echo esc_url( $viewer_url ); ?>' + _href + '#zoom=<?php echo esc_html( $tnc_pvfw_default_zoom ); ?>&pagemode=<?php echo esc_html( $tnc_pvfw_default_pagemode ); ?>');
					} else {
						// Do Nothing
					}
				});
			});
		</script>
		<?php
	}
	/**
	 * Automatic iFrame
	 */
	function tnc_pdf_autoiframe() {
		$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );
		$get_fullscreen_text      = $get_pvfw_global_settings['general-fullscreen-text'];

		if ( ! empty( $get_fullscreen_text ) ) {
			$fullscreen_text = $get_fullscreen_text;
		} else {
			$fullscreen_text = esc_html__( 'Fullscreen Mode', 'pdf-viewer-for-wordpress' );
		}
		$tnc_ai_get_viewer_page_id = $get_pvfw_global_settings['advanced-pdf-viewer-page'];

		$tnc_pvfw_default_zoom = $get_pvfw_global_settings['toolbar-default-zoom'];

		if( isset( $get_pvfw_global_settings['toolbar-default-page-mode'] ) ) {
			$tnc_pvfw_default_pagemode = $get_pvfw_global_settings['toolbar-default-page-mode'];
		} else {
			$tnc_pvfw_default_pagemode = "none";
		}

		if ( empty( $tnc_ai_get_viewer_page_id ) ) {
			$tnc_ai_get_viewer_page_id = get_option( 'tnc_pdf_viewer_page_id', false );
		}

		$viewer_url = get_permalink( $tnc_ai_get_viewer_page_id );
		$pdf_al     = wp_parse_url( $viewer_url, PHP_URL_QUERY );

		if ( $pdf_al ) {
			$viewer_url .= '&file=';
		} else {
			$viewer_url .= '?file=';
		}

		$auto_iframe_width  = $get_pvfw_global_settings['select-automatic-iframe-width'];
		$auto_iframe_height = $get_pvfw_global_settings['select-automatic-iframe-height'];
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {		
				var gethost = new RegExp(location.host);
				jQuery("a[href$='.pdf']").each(function() {
					if(gethost.test(jQuery(this).attr('href'))){
						var _href = jQuery(this).attr("href");
						jQuery(this).replaceWith("<a class='fullscreen-mode' href='<?php echo esc_url( $viewer_url ); ?>" + _href +"#zoom=<?php echo esc_html( $tnc_pvfw_default_zoom ); ?>' ><?php echo esc_html( $fullscreen_text ); ?></a><br><iframe class='pvfw-pdf-viewer-frame' width='<?php echo esc_attr( $auto_iframe_width ); ?>' height='<?php echo esc_attr( $auto_iframe_height ); ?>' src='<?php echo esc_url( $viewer_url ); ?>" + _href +"#zoom=<?php echo esc_html( $tnc_pvfw_default_zoom ); ?>&pagemode=<?php echo esc_html( $tnc_pvfw_default_pagemode ); ?>'></iframe>");
					} else {
						// do nothing.
					}
				});
			});
		</script>
		<?php
	}
	if ( 'auto-iframe' === $auto_add ) {
		add_action( 'wp_footer', 'tnc_pdf_autoiframe' );
	} elseif ( 'auto-link' === $auto_add ) {
		add_action( 'wp_footer', 'tnc_pdf_autolink' );
		add_action( 'wp_footer', 'themencode_autolink_target' );
	}

	/**
	 * Automatic Link Target
	 */
	function themencode_autolink_target() {
		$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );
		$autolink_setting         = $get_pvfw_global_settings['select-automatic-link-target'];

		$output  = '<script type="text/javascript">';
		$output .= 'jQuery(function($) {';
		$output .= "jQuery('a[href$=\".pdf\"]').attr('target', '" . esc_attr( $autolink_setting ) . "');";
		$output .= '});';
		$output .= '</script>';
		echo $output;
	}
}

if ( ! function_exists( 'tnc_pdf_iframe_responsive_fix' ) ) {
	/**
	 * IFrame Responsive Fix
	 *
	 * @return void
	 */
	function tnc_pdf_iframe_responsive_fix() {
		$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );
		if( isset( $get_pvfw_global_settings['general-iframe-responsive-fix'] )){
			$get_iframe_fix           = $get_pvfw_global_settings['general-iframe-responsive-fix'];
		} else {
			$get_iframe_fix           = 'no';
		}
		
		if( isset( $get_pvfw_global_settings['general-mobile-iframe-height'] )){
			$get_mobile_iframe_height = $get_pvfw_global_settings['general-mobile-iframe-height'];
		} else {
			$get_mobile_iframe_height = '500px';
		}
		
		$output = "<style type='text/css'>";
		if ( '1' === $get_iframe_fix ) {
			$output .= "
				iframe.pvfw-pdf-viewer-frame{
					border: 0px;
				}";
		} else {
			$output .= "
				iframe.pvfw-pdf-viewer-frame{
					max-width: 100%;
					border: 0px;
				}";
		}

		if( !empty( $get_pvfw_iframe_mobile_height ) ) {
			$output .= "
				@media screen and (max-width: 799px) {
					iframe.pvfw-pdf-viewer-frame{
						height: " . esc_attr( $get_pvfw_iframe_mobile_height ) . ";
					}
				}";
		}

		$output .= "</style>";

		echo $output;
	}

	add_action( 'wp_head', 'tnc_pdf_iframe_responsive_fix' );
}
