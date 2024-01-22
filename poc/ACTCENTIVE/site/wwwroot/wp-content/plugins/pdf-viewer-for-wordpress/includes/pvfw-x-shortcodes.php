<?php
/**
 * Shortcodes for Oxygen Builder
 */

function tnc_x_pdf_shortcode( $atts ) {

	$shortcode_viewer_url = plugins_url()."/".TNC_PVFW_WEB_DIR."/pdf-viewer-x.php";
	$sc_page_one          = parse_url( $shortcode_viewer_url, PHP_URL_QUERY );
	if ( $sc_page_one ) {
		$shortcode_viewer_url_par = '&tnc_pvfw=';
	} else {
		$shortcode_viewer_url_par = '?tnc_pvfw=';
	}
	extract(
		shortcode_atts(
			array(
				'file'           => '',
				'width'          => '550',
				'height'         => '800',
				'download'       => 'true',
				'print'          => 'true',
				'fullscreen'     => 'true',
				'share'          => 'true',
				'zoom'           => 'true',
				'open'           => 'true',
				'logo'           => 'true',
				'pagenav'        => 'true',
				'find'           => 'true',
				'language'       => 'en-US',
				'page'           => '', // Added in 3.0
				'default_zoom'   => 'auto', // Added in 3.0 // Fixed in 3.6
				'pagemode'       => 'none', // Added in 5.0 // added default value none in 8.2
				'current_view'   => 'true', // Added in 7.0
				'rotate'         => 'true', // Added in 7.0
				'handtool'       => 'true', // Added in 7.0
				'doc_prop'       => 'true', // Added in 7.0
				'toggle_menu'    => 'true', // Added in 7.0
				'toggle_left'    => 'true', // Added in 8.0
				'scroll'         => 'true', // added in 8.0
				'spread'         => 'true', // Added in 8.0
				'default_scroll' => '0', // added in 8.5
				'default_spread' => '0', // Added in 8.5
				'iframe_title'   => '', // Added in 8.2
			),
			$atts,
			'tnc_x_pdf_embed_shortcode'
		)
	);

	$get_pvfw_global_settings = get_option( 'pvfw_csf_options' ); 

	$get_fullscreen_text = $get_pvfw_global_settings['general-fullscreen-text'];
	if ( ! empty( $get_fullscreen_text ) ) {
		$fullscreen_text = $get_pvfw_global_settings['general-fullscreen-text'];
	} else {
		$fullscreen_text = esc_html__( 'Fullscreen Mode', 'pdf-viewer-for-wordpress' );
	}

	switch ( $download ) {
		case 'true':
		case '1':
			$s_download = '1';
			break;

		case 'false':
		case '0':
			$s_download = '0';
			break;
	}

	switch ( $print ) {
		case 'true':
		case '1':
			$s_print = '1';
			break;

		case 'false':
		case '0':
			$s_print = '0';
			break;
	}

	switch ( $fullscreen ) {
		case 'true':
		case '1':
			$s_fullscreen = '1';
			break;

		case 'false':
		case '0':
			$s_fullscreen = '0';
			break;
	}

	switch ( $zoom ) {
		case 'true':
		case '1':
			$s_zoom = '1';
			break;

		case 'false':
		case '0':
			$s_zoom = '0';
			break;
	}

	switch ( $share ) {
		case 'true':
		case '1':
			$s_share = '1';
			break;

		case 'false':
		case '0':
			$s_share = '0';
			break;
	}

	switch ( $open ) {
		case 'true':
		case '1':
			$s_open = '1';
			break;

		case 'false':
		case '0':
			$s_open = '0';
			break;
	}

	switch ( $logo ) {
		case 'true':
		case '1':
			$s_logo = '1';
			break;

		case 'false':
		case '0':
			$s_logo = '0';
			break;
	}

	switch ( $pagenav ) {
		case 'true':
		case '1':
			$s_pagenav = '1';
			break;

		case 'false':
		case '0':
			$s_pagenav = '0';
			break;
	}

	switch ( $find ) {
		case 'true':
		case '1':
			$s_find = '1';
			break;

		case 'false':
		case '0':
			$s_find = '0';
			break;
	}

	switch ( $current_view ) {
		case 'true':
		case '1':
			$s_current_view = '1';
			break;

		case 'false':
		case '0':
			$s_current_view = '0';
			break;
	}

	switch ( $rotate ) {
		case 'true':
		case '1':
			$s_rotate = '1';
			break;

		case 'false':
		case '0':
			$s_rotate = '0';
			break;
	}

	switch ( $handtool ) {
		case 'true':
		case '1':
			$s_handtool = '1';
			break;

		case 'false':
		case '0':
			$s_handtool = '0';
			break;
	}

	switch ( $doc_prop ) {
		case 'true':
		case '1':
			$s_doc_prop = '1';
			break;

		case 'false':
		case '0':
			$s_doc_prop = '0';
			break;
	}

	switch ( $toggle_menu ) {
		case 'true':
		case '1':
			$s_toggle_menu = '1';
			break;

		case 'false':
		case '0':
			$s_toggle_menu = '0';
			break;
	}

	switch ( $toggle_left ) {
		case 'true':
		case '1':
			$s_toggle_left = '1';
			break;

		case 'false':
		case '0':
			$s_toggle_left = '0';
			break;
	}

	switch ( $scroll ) {
		case 'true':
		case '1':
			$s_scroll = '1';
			break;

		case 'false':
		case '0':
			$s_scroll = '0';
			break;
	}

	switch ( $spread ) {
		case 'true':
		case '1':
			$s_spread = '1';
			break;

		case 'false':
		case '0':
			$s_spread = '0';
			break;
	}
	$generated_url  = 'file=' . $file . '&settings=' . $s_download . $s_print . $s_zoom . $s_fullscreen . $s_share . $s_open . $s_logo . $s_pagenav . $s_find . $s_current_view . $s_rotate . $s_handtool . $s_doc_prop . $s_toggle_menu . $s_toggle_left . $s_scroll . $s_spread . $default_scroll . $default_spread . '&lang=' . $language;
	$additional_url = '#page=' . $page . '&zoom=' . $default_zoom . '&pagemode=' . $pagemode;
	$encoded_url    = base64_encode( $generated_url );
	$final_url      = $shortcode_viewer_url . $shortcode_viewer_url_par . $encoded_url . $additional_url;
	$output  = '';

	if ( $fullscreen == 'true' ) {
		$output .= '<a class="fullscreen-mode" href="' . $final_url . '" target="_blank">' . $fullscreen_text . '</a><br>';
	}
	$output .= '<iframe class="pvfw-pdf-viewer-frame" width="' . $width . '" height="' . $height . '" src="' . $final_url . '" title="' . $iframe_title . '"></iframe>';

	if( tnc_pvfw_site_registered_status( false ) ){
		return $output;
	} else {
		return tnc_pvfw_site_registered_message();
	}
}

add_shortcode( 'tnc-x-pdf-viewer-embed', 'tnc_x_pdf_shortcode' );

// Link Shortcode.
function tnc_x_pdf_link_shortcode( $atts ) {
	$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );

	$shortcode_viewer_url = plugins_url()."/".TNC_PVFW_WEB_DIR."/pdf-viewer-x.php";
	$sc_page_one          = parse_url( $shortcode_viewer_url, PHP_URL_QUERY );
	if ( $sc_page_one ) {
		$shortcode_viewer_url_par = '&tnc_pvfw=';
	} else {
		$shortcode_viewer_url_par = '?tnc_pvfw=';
	}
	extract(
		shortcode_atts(
			array(
				'file'           => '',
				'text'           => 'Open PDF',
				'target'         => '_blank',
				'download'       => 'true',
				'print'          => 'true',
				'fullscreen'     => 'true',
				'share'          => 'true',
				'zoom'           => 'true',
				'open'           => 'true',
				'class'          => 'tnc_pdf',
				'logo'           => 'true',
				'pagenav'        => 'true',
				'find'           => 'true',
				'language'       => 'en-US',
				'page'           => '', // Added in 3.0
				'default_zoom'   => 'auto', // Added in 3.0
				'pagemode'       => 'none', // Added in 5.0 // added default value none in 8.2
				'current_view'   => 'true', // Added in 7.0
				'rotate'         => 'true', // Added in 7.0
				'handtool'       => 'true', // Added in 7.0
				'doc_prop'       => 'true', // Added in 7.0
				'toggle_menu'    => 'true', // Added in 7.0
				'toggle_left'    => 'true', // Added in 8.0
				'scroll'         => 'true', // added in 8.0
				'spread'         => 'true', // Added in 8.0
				'default_scroll' => '0', // added in 8.5
				'default_spread' => '0', // Added in 8.5
			),
			$atts,
			'tnc_x_pdf_link_shortcode'
		)
	);

	switch ( $download ) {
		case 'true':
		case '1':
			$s_download = '1';
			break;

		case 'false':
		case '0':
			$s_download = '0';
			break;
	}

	switch ( $print ) {
		case 'true':
		case '1':
			$s_print = '1';
			break;

		case 'false':
		case '0':
			$s_print = '0';
			break;
	}

	switch ( $fullscreen ) {
		case 'true':
		case '1':
			$s_fullscreen = '1';
			break;

		case 'false':
		case '0':
			$s_fullscreen = '0';
			break;
	}

	switch ( $zoom ) {
		case 'true':
		case '1':
			$s_zoom = '1';
			break;

		case 'false':
		case '0':
			$s_zoom = '0';
			break;
	}

	switch ( $share ) {
		case 'true':
		case '1':
			$s_share = '1';
			break;

		case 'false':
		case '0':
			$s_share = '0';
			break;
	}

	switch ( $open ) {
		case 'true':
		case '1':
			$s_open = '1';
			break;

		case 'false':
		case '0':
			$s_open = '0';
			break;
	}

	switch ( $logo ) {
		case 'true':
		case '1':
			$s_logo = '1';
			break;

		case 'false':
		case '0':
			$s_logo = '0';
			break;
	}

	switch ( $pagenav ) {
		case 'true':
		case '1':
			$s_pagenav = '1';
			break;

		case 'false':
		case '0':
			$s_pagenav = '0';
			break;
	}

	switch ( $find ) {
		case 'true':
		case '1':
			$s_find = '1';
			break;

		case 'false':
		case '0':
			$s_find = '0';
			break;
	}

	switch ( $current_view ) {
		case 'true':
		case '1':
			$s_current_view = '1';
			break;

		case 'false':
		case '0':
			$s_current_view = '0';
			break;
	}

	switch ( $rotate ) {
		case 'true':
		case '1':
			$s_rotate = '1';
			break;

		case 'false':
		case '0':
			$s_rotate = '0';
			break;
	}

	switch ( $handtool ) {
		case 'true':
		case '1':
			$s_handtool = '1';
			break;

		case 'false':
		case '0':
			$s_handtool = '0';
			break;
	}

	switch ( $doc_prop ) {
		case 'true':
		case '1':
			$s_doc_prop = '1';
			break;

		case 'false':
		case '0':
			$s_doc_prop = '0';
			break;
	}

	switch ( $toggle_menu ) {
		case 'true':
		case '1':
			$s_toggle_menu = '1';
			break;

		case 'false':
		case '0':
			$s_toggle_menu = '0';
			break;
	}

	switch ( $toggle_left ) {
		case 'true':
		case '1':
			$s_toggle_left = '1';
			break;

		case 'false':
		case '0':
			$s_toggle_left = '0';
			break;
	}

	switch ( $scroll ) {
		case 'true':
		case '1':
			$s_scroll = '1';
			break;

		case 'false':
		case '0':
			$s_scroll = '0';
			break;
	}

	switch ( $spread ) {
		case 'true':
		case '1':
			$s_spread = '1';
			break;

		case 'false':
		case '0':
			$s_spread = '0';
			break;
	}

	$generated_url  = 'file=' . $file . '&settings=' . $s_download . $s_print . $s_zoom . $s_fullscreen . $s_share . $s_open . $s_logo . $s_pagenav . $s_find . $s_current_view . $s_rotate . $s_handtool . $s_doc_prop . $s_toggle_menu . $s_toggle_left . $s_scroll . $s_spread . $default_scroll . $default_spread . '&lang=' . $language;
	$additional_url = '#page=' . $page . '&zoom=' . $default_zoom . '&pagemode=' . $pagemode;
	$encoded_url    = base64_encode( $generated_url );
	$final_url      = $shortcode_viewer_url . $shortcode_viewer_url_par . $encoded_url . $additional_url;

	$output  = '';
	$output .= '<a href="' . $final_url . '" class="' . $class . '" target="' . $target . '">' . $text . '</a>';
	if( tnc_pvfw_site_registered_status( false ) ){
		return $output;
	} else {
		return tnc_pvfw_site_registered_message();
	}
}
add_shortcode( 'tnc-x-pdf-viewer-link', 'tnc_x_pdf_link_shortcode' );