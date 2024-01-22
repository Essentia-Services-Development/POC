<?php 
function convert_on_to_off_toolbar_items( $value ){
	if ( $value == 'on' ){
		$output = '0';
	} else {
		$output = '1';
	}
	return $output;
}
// Get previous settings
$sh_opt_name                    = "auto_add_link";
$ss_opt_name                    = "hide_share";
$print_opt_name                 = "hide_print";
$download_opt_name              = "hide_download";
$open_opt_name                  = "hide_open";
$zoom_opt_name                  = "hide_zoom";
$fullscreen_opt_name            = "hide_fullscreen";
$logo_image_opt_name            = "logo_image";
$logo_display_opt_name          = "hide_logo";
$find_opt_name                  = "hide_find";
$pagenav_opt_name               = "hide_pagenav";
$current_view_opt_name          = "hide_current_view";
$rotate_opt_name                = "hide_rotate";
$handtool_opt_name              = "hide_handtool";
$doc_prop_opt_name              = "hide_doc_prop";
$toggle_menu_opt_name           = "hide_toggle_menu";
$toggle_left_opt_name           = "hide_toggle_left"; // added in 7.5
$link_opt_name                  = "tnc_link_target";
$tnc_pdf_custom_css             = "pdf_viewer_custom_css";
$auto_iframe_width_name         = "auto_iframe_width";
$auto_iframe_height_name        = "auto_iframe_height";
$analytics_opt_name             = "analytics_id";
$fs_text_opt_name               = "fullscreen_text";
$vpi_opt_name                   = "tnc_pdf_viewer_page_id";
$vpi_sc_opt_name                = "tnc_pdf_viewer_sc_page_id";
$iframe_fix_opt_name            = "iframe_responsive_fix"; // Added in 5.4
$lang_opt_name                  = "tnc_pdf_lang"; // Added in 6.1
$scroll_opt_name                = "hide_scroll"; // Added in 8.1
$spread_opt_name                = "hide_spread"; // Added in 8.1
$disable_context_menu_opt_name  = "tnc_pvfw_disable_context_menu"; // Added in 8.3
$disable_copying_opt_name       = "tnc_pvfw_disable_copying_shortcut"; // Added in 8.3
$return_text_opt_name           = "tnc_pvfw_return_link_text"; // added in 8.5
$default_scroll_opt_name        = "tnc_pvfw_default_scroll";
$default_spread_opt_name        = "tnc_pvfw_default_spread";
$favicon_opt_name               = "tnc_pvfw_favicon"; // added in v9.0.3

$auto_add                       = get_option($sh_opt_name);
$show_social                    = get_option($ss_opt_name);
$show_print                     = get_option($print_opt_name);
$show_download                  = get_option($download_opt_name);
$show_open                      = get_option($open_opt_name);
$show_zoom                      = get_option($zoom_opt_name);
$show_full                      = get_option($fullscreen_opt_name);
$show_logo_image                = get_option($logo_image_opt_name);
$show_logo                      = get_option($logo_display_opt_name);
$show_find                      = get_option($find_opt_name);
$show_pagenav                   = get_option($pagenav_opt_name);
$show_current_view              = get_option($current_view_opt_name);
$show_rotate                    = get_option($rotate_opt_name);
$show_handtool                  = get_option($handtool_opt_name);
$show_doc_prop                  = get_option($doc_prop_opt_name);
$show_toggle_menu               = get_option($toggle_menu_opt_name);
$show_toggle_left               = get_option($toggle_left_opt_name);
$show_scroll                    = get_option($scroll_opt_name);
$show_spread                    = get_option($spread_opt_name);
$iframe_fix                     = get_option($iframe_fix_opt_name);
$custom_css                     = get_option($tnc_pdf_custom_css);
$link_target                    = get_option($link_opt_name);
$auto_iframe_width              = get_option($auto_iframe_width_name);
$auto_iframe_height             = get_option($auto_iframe_height_name);
$analytics_id                   = get_option($analytics_opt_name);
$fullscreen_text                = get_option($fs_text_opt_name);
$vpi_value                      = get_option($vpi_opt_name);
$vpi_sc_value                   = get_option($vpi_sc_opt_name);
$lang_value                     = get_option($lang_opt_name);
$disable_context_menu_value     = get_option($disable_context_menu_opt_name);
$disable_copying_value          = get_option($disable_copying_opt_name);
$return_text                    = get_option($return_text_opt_name);
$default_scroll_setting         = get_option($default_scroll_opt_name);
$default_spread_setting         = get_option($default_spread_opt_name);
$show_favicon                   = get_option($favicon_opt_name);

// customize page
$tnc_pvfw_look_on         = "pvfw_look_style";
$tnc_pvfw_theme_on        = "pvfw_active_theme";
$tnc_primary_color_on     = "pvfw_primary_color";
$tnc_secondary_color_on   = "pvfw_secondary_color";
$tnc_text_color_on        = "pvfw_text_color";
$tnc_icon_color_on        = "pvfw_icon_color";

$tnc_pvfw_look        = get_option($tnc_pvfw_look_on);
$tnc_pvfw_theme       = get_option($tnc_pvfw_theme_on);
$tnc_primary_color    = get_option($tnc_primary_color_on);
$tnc_secondary_color  = get_option($tnc_secondary_color_on);
$tnc_text_color       = get_option($tnc_text_color_on);
$tnc_icon_color       = get_option($tnc_icon_color_on);

if( $auto_add == 'auto_iframe' ){
	$auto_add = 'auto-iframe';
} elseif ( $auto_add == 'auto_link' ){
	$auto_add = 'auto-link';
}

$context_menu_option = ( $disable_context_menu_value == 'enable' ) ? '1' : '0';
$copying_menu_option = ( $disable_copying_value == 'enable' ) ? '1' : '0';


$build_settings = array(
	'select-automatic-display'       	=> $auto_add,
	'select-automatic-link-target'   	=> $link_target,
	'select-automatic-iframe-width'  	=> $auto_iframe_width,
	'select-automatic-iframe-height' 	=> $auto_iframe_height,
	'general-fullscreen-text' 			=> $fullscreen_text,
	'general-return-text'				=> $return_text,
	'general-analytics-id'				=> $analytics_id,
	'general-iframe-responsive-fix' 	=> convert_on_to_off_toolbar_items( $iframe_fix ),
	'appearance-select-type'			=> $tnc_pvfw_look,
	'appearance-select-theme'			=> $tnc_pvfw_theme,
	'appearance-select-colors'			=> array(
		'primary-color'					=> $tnc_primary_color,
		'secondary-color' 				=> $tnc_secondary_color,
		'text-color'					=> $tnc_text_color,	
	),
	'appearance-select-icon'			=> $tnc_icon_color,
	'toolbar-default-scroll'			=> $default_scroll_setting,
	'toolbar-default-spread'			=> $default_spread_setting,
	'toolbar-viewer-language'			=> $lang_value,
	'toolbar-share'						=> convert_on_to_off_toolbar_items( $show_social ),
	'toolbar-print'						=> convert_on_to_off_toolbar_items( $show_print ),
	'toolbar-download'					=> convert_on_to_off_toolbar_items( $show_download ),
	'toolbar-open'						=> convert_on_to_off_toolbar_items( $show_open ),
	'toolbar-zoom'						=> convert_on_to_off_toolbar_items( $show_zoom ),
	'toolbar-fullscreen'				=> convert_on_to_off_toolbar_items( $show_full ),
	'toolbar-logo'						=> convert_on_to_off_toolbar_items( $show_logo ),
	'toolbar-find'						=> convert_on_to_off_toolbar_items( $show_find ),
	'toolbar-pagenav'					=> convert_on_to_off_toolbar_items( $show_pagenav ),
	'toolbar-current-view'				=> convert_on_to_off_toolbar_items( $show_current_view ),
	'toolbar-rotate'					=> convert_on_to_off_toolbar_items( $show_rotate ),
	'toolbar-handtool'					=> convert_on_to_off_toolbar_items( $show_handtool ),
	'toolbar-doc-prop'					=> convert_on_to_off_toolbar_items( $show_doc_prop ),
	'toolbar-left-toggle'				=> convert_on_to_off_toolbar_items( $show_toggle_left ),
	'toolbar-right-toggle'				=> convert_on_to_off_toolbar_items( $show_toggle_menu ),
	'toolbar-scroll'					=> convert_on_to_off_toolbar_items( $show_scroll ),
	'toolbar-spread'					=> convert_on_to_off_toolbar_items( $show_spread ),
	'advanced-pdf-viewer-page'			=> $vpi_value,
	'advanced-pdf-viewer-sc-page'		=> $vpi_sc_value,
	'advanced-context-menu'				=> $context_menu_option,
	'advanced-text-copying'				=> $copying_menu_option,
	'custom-css'						=> $custom_css,
);
$importable_data = json_encode($build_settings);

?>
<div class="wrap">
	<div id="poststuff">
	    <div id="post-body">

		    <div class="tnc-upload-container">
		    	<h1><?php echo esc_html_e( "Migrate Old Plugin settings to new panel.", $domain = 'pdf-viewer-for-wordpress' ); ?></h1>
		    	<p><?php echo esc_html_e( "This page is only useful if you have upgraded to TNC FlipBook - PDF Viewer for WordPress version 10 or newer from an old version, We have changed the setting panel completely on version 10 & you can copy the followng code and import on our new panel to have your previous data imported to new panel.", $domain = 'pdf-viewer-for-wordpress' ); ?></p>
		    	<p><?php echo esc_html_e( "It imports almost every settings except the image settings for logo and favicon. You need to set that again on the new panel.", $domain = 'pdf-viewer-for-wordpress' ); ?></p>

		    	<textarea name="" id="" cols="30" rows="10" class="tnc-migrate-settings-content" style="width: 100%;"><?php echo $importable_data; ?></textarea>

				<h3><?php echo esc_html_e( "How to import?", $domain = 'pdf-viewer-for-wordpress' ); ?></h3>
		    	<p><?php echo esc_html_e( "1. Copy the code from the textarea above", $domain = 'pdf-viewer-for-wordpress' ); ?></p>
		    	<p><?php echo esc_html_e( "2. Navigate to TNC FlipBook > Global Settings > Export/Import Menu", $domain = 'pdf-viewer-for-wordpress' ); ?></p>
		    	<p><?php echo esc_html_e( "3. Paste the code in the textarea above import button and click on import", $domain = 'pdf-viewer-for-wordpress' ); ?></p>
		    	<p><?php echo esc_html_e( "4. That's it, your previous settings should now be implemented on the new panel.", $domain = 'pdf-viewer-for-wordpress' ); ?></p>
			</div>
		</div>
	</div>
</div>