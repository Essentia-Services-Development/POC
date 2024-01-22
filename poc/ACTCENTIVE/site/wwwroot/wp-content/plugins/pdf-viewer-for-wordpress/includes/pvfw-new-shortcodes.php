<?php
/**
 * Shortcodes
 *
 * Register new shortcodes
 *
 * @since 10.0
 *
 * @package pdf-viewer-for-wordpress
 */

if ( ! function_exists( 'tnc_pvfw_embed_shortcode' ) ) {

	function tnc_pvfw_embed_shortcode( $atts ) {
		extract(
			shortcode_atts(
				array(
					'width'        => '100%',
					'height'       => '800',
					'viewer_id'    => '',
					'iframe_title' => '',
					'page'         => '',
					'zoom'         => '',
					'pagemode'         => '',
				),
				$atts,
				'tnc_pdf_embed_shortcode'
			)
		);

		$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );
		$get_pvfw_single_settings = get_post_meta( $viewer_id, 'tnc_pvfw_pdf_viewer_fields', true );

		$toolbar_use_global = $get_pvfw_single_settings['toolbar-elements-use-global-settings'];

		if ( $toolbar_use_global == '0' ) {
			$fullscreen = ( $get_pvfw_single_settings['fullscreen'] == '1' ) ? 'on' : 'off';
		} else {
			$fullscreen = ( $get_pvfw_global_settings['toolbar-fullscreen'] == '1' ) ? 'on' : 'off';
		}

		$get_fullscreen_text = $get_pvfw_global_settings['general-fullscreen-text'];

		if ( ! empty( $get_fullscreen_text ) ) {
			$fullscreen_text = $get_fullscreen_text;
		} else {
			$fullscreen_text = esc_html__( 'Fullscreen Mode', 'pdf-viewer-for-wordpress' );
		}

		$output  = '';

		if( ! empty ( $zoom ) || ! empty( $pagemode ) || ! empty( $page ) ){
			$value_exists = true;
			$auto_viewer = "?auto_viewer=true";
		} else {
			$value_exists = false;
			$auto_viewer = "";
		}

		if ( $fullscreen == 'on' ) {
			if( $value_exists ){
				$output .= '<a class="fullscreen-mode" href="' . get_permalink( $viewer_id ) . $auto_viewer . '#page=' . $page . '&zoom=' . $zoom . '&pagemode=' . $pagemode . '" target="_blank">' . $fullscreen_text . '</a><br>';
			} else {
				$output .= '<a class="fullscreen-mode" href="' . get_permalink( $viewer_id ) .'" target="_blank">' . $fullscreen_text . '</a><br>';
			}
		}
		if( $value_exists ){
			$output .= '<iframe class="pvfw-pdf-viewer-frame" width="' . $width . '" height="' . $height . '" src="' . get_permalink( $viewer_id ) . $auto_viewer . '#page=' . $page . '&zoom=' . $zoom . '&pagemode=' . $pagemode . '" title="' . $iframe_title . '"></iframe>';
		} else {
			$output .= '<iframe class="pvfw-pdf-viewer-frame" width="' . $width . '" height="' . $height . '" src="' . get_permalink( $viewer_id ) .'" title="' . $iframe_title . '"></iframe>';
		}
		if( tnc_pvfw_site_registered_status( false ) ){
			return $output;
		} else {
			return tnc_pvfw_site_registered_message();
		}
	}

	add_shortcode( 'pvfw-embed', 'tnc_pvfw_embed_shortcode' );
}

if ( ! function_exists( 'tnc_pvfw_link_shortcode' ) ) {

	// New Link Shortcode.
	function tnc_pvfw_link_shortcode( $atts ) {
		extract(
			shortcode_atts(
				array(
					'text'      => 'Open PDF',
					'target'    => '_blank',
					'viewer_id' => '',
					'class'     => 'tnc-pdf',
					'page'      => '',
					'zoom'      => '',
					'pagemode'  => '',
				),
				$atts,
				'tnc_pdf_new_link_shortcode'
			)
		);

		$output  = '';

		if( ! empty ( $zoom ) || ! empty( $pagemode ) || ! empty( $page ) ){
			$value_exists = true;
			$auto_viewer = "?auto_viewer=true";
		} else {
			$value_exists = false;
			$auto_viewer = "";
		}

		if( $value_exists ){
			$output .= '<a href="' . get_permalink( $viewer_id ) . $auto_viewer . '#page=' . $page . '&zoom=' . $zoom . '&pagemode=' . $pagemode . '" class="' . $class . '" target="' . $target . '">' . $text . '</a>';
		} else {
			$output .= '<a href="' . get_permalink( $viewer_id ) . '" class="' . $class . '" target="' . $target . '">' . $text . '</a>';
		}
		if( tnc_pvfw_site_registered_status( false ) ){
			return $output;
		} else {
			return tnc_pvfw_site_registered_message();
		}
	}

	add_shortcode( 'pvfw-link', 'tnc_pvfw_link_shortcode' );
}

if ( ! function_exists( 'tnc_pvfw_image_link_shortcode' ) ) {

	function tnc_pvfw_image_link_shortcode( $atts ) {
		extract(
			shortcode_atts(
				array(
					'viewer_id'		=> '',
					'alt_text'		=> 'Link in a PDF',
					'class'			=> 'tnc_pdf_image_link',
					'target'		=> '_blank',
					'page'			=> '',
					'zoom'			=> '',
					'pagemode'		=> '',
					'img_url'		=> '',
					'width'			=> '100%',
					'height'		=> 'auto',
					'alignment'		=> 'inherit'
				),
				$atts,
				'tnc_pdf_new_image_link_shortcode'
			)
		);

		$output  = '';

		if( ! empty ( $zoom ) || ! empty( $pagemode ) || ! empty( $page ) ){
			$value_exists = true;
			$auto_viewer = "?auto_viewer=true";
		} else {
			$value_exists = false;
			$auto_viewer = "";
		}

		if( filter_var($img_url, FILTER_VALIDATE_URL) == false ) {
			$img_url = wp_get_attachment_image_url($img_url, 'full');
		}

		if( $value_exists ){
			$output .= '<a href="' . get_permalink( $viewer_id ) . $auto_viewer . '#page=' . $page . '&zoom=' . $zoom . '&pagemode=' . $pagemode . '" target="' . $target . '" style="text-align:' . $alignment . '; display: block">
			<img src="' . $img_url . '" class="' . $class . '" alt="' . $alt_text . '" style="width:' . $width . '; height:' . $height . '"> 
		</a>';
		} else {
			$output .= '<a href="' . get_permalink( $viewer_id ) . '" target="' . $target . '" style="text-align:' . $alignment . '; display: block">
			<img src="' . $img_url . '" class="' . $class . '" alt="' . $alt_text . '" style="width:' . $width . '; height:' . $height . '"> 
		</a>';
		}

		if( tnc_pvfw_site_registered_status( false ) ){
			return $output;
		} else {
			return tnc_pvfw_site_registered_message();
		}
	}

	add_shortcode( 'pvfw-image-link', 'tnc_pvfw_image_link_shortcode' );
}