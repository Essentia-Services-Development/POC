<?php
if ( ! defined( 'THEMENCODE_PDF_VIEWER' ) ) {
	$scriptPath = dirname( __FILE__ );
	$path       = realpath( $scriptPath . '/./' );
	$filepath   = explode( 'wp-content', $path );
	define( 'WP_USE_THEMES', false );
	require $filepath[0] . '/wp-blog-header.php';
}

$_GET  = filter_input_array( INPUT_GET, FILTER_SANITIZE_STRING );
$_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

if ( isset( $_GET['file'] ) && ! empty( $_GET['file'] ) ) {
	$file        = esc_html( $_GET['file'] );
	$encode_file = base64_encode( $file );
} elseif ( isset( $_GET['tnc_pvfw'] ) && ! empty( $_GET['tnc_pvfw'] ) ) {
	$get_data    = esc_html( $_GET['tnc_pvfw'] );
	$decode_data = esc_html( base64_decode( $get_data ) );

	list($file_full, $lang_full)      = explode( '&', $decode_data );
	list($file_par, $file)            = explode( '=', $file_full );
	list($lang_par, $viewer_language) = explode( '=', $lang_full );

	$encode_file = base64_encode( $file );
} else {
	wp_redirect( site_url() );
}

$get_pvfw_global_settings = get_option( 'pvfw_csf_options' );
$pvfw_flip_audio 		= $get_pvfw_global_settings['appearance-disable-flip-sound'];


// retrieve options.
$find                   = $get_pvfw_global_settings['toolbar-find'];
$pagenav                = $get_pvfw_global_settings['toolbar-pagenav'];
$share                  = $get_pvfw_global_settings['toolbar-share'];
$zoom                   = $get_pvfw_global_settings['toolbar-zoom'];
$logo_image_url         = $get_pvfw_global_settings['general-logo']['url'];
$favicon_url            = $get_pvfw_global_settings['general-favicon']['url'];
$logo                   = $get_pvfw_global_settings['toolbar-logo'];
$print                  = $get_pvfw_global_settings['toolbar-print'];
$open                   = $get_pvfw_global_settings['toolbar-open'];
$download               = $get_pvfw_global_settings['toolbar-download'];
$fullscreen             = $get_pvfw_global_settings['toolbar-fullscreen'];
$current_view           = $get_pvfw_global_settings['toolbar-current-view'];
$rotate                 = $get_pvfw_global_settings['toolbar-rotate'];
$handtool               = $get_pvfw_global_settings['toolbar-handtool'];
$doc_prop               = $get_pvfw_global_settings['toolbar-doc-prop'];
$toggle_menu            = $get_pvfw_global_settings['toolbar-right-toggle'];
$toggle_left            = $get_pvfw_global_settings['toolbar-left-toggle'];
$scroll                 = $get_pvfw_global_settings['toolbar-scroll'];
$spread                 = $get_pvfw_global_settings['toolbar-spread'];
$viewer_language        = $get_pvfw_global_settings['toolbar-viewer-language'];
$tnc_pvfw_look          = $get_pvfw_global_settings['appearance-select-type'];
$tnc_pvfw_theme         = $get_pvfw_global_settings['appearance-select-theme'];
$tnc_primary_color      = $get_pvfw_global_settings['appearance-select-colors']['primary-color'];
$tnc_secondary_color    = $get_pvfw_global_settings['appearance-select-colors']['secondary-color'];
$tnc_text_color         = $get_pvfw_global_settings['appearance-select-colors']['text-color'];
$tnc_icon_color         = $get_pvfw_global_settings['appearance-select-icon'];
$analytics_id           = $get_pvfw_global_settings['general-analytics-id'];
$context_menu_setting   = $get_pvfw_global_settings['advanced-context-menu'];
$copying_setting        = $get_pvfw_global_settings['advanced-text-copying'];
$default_scroll_setting = $get_pvfw_global_settings['toolbar-default-scroll'];
$default_spread_setting = $get_pvfw_global_settings['toolbar-default-spread'];
$get_return_link_text   = $get_pvfw_global_settings['general-return-text'];

if ( isset( $default_scroll_setting ) && ! empty( $default_scroll_setting ) ) {
	$default_scroll = $default_scroll_setting;
} else {
	$default_scroll = '0';
}

if ( isset( $default_spread_setting ) && ! empty( $default_spread_setting ) ) {
	$default_spread = $default_spread_setting;
} else {
	$default_spread = '0';
}

switch ( $tnc_pvfw_look ) {
	case 'select-theme':
		$style_theme = $tnc_pvfw_theme . '.css';
		break;

	case 'custom-color':
		$style_theme = 'custom.php?primary=' . str_replace( '#', '', $tnc_primary_color ) . '&secondary=' . str_replace( '#', '', $tnc_secondary_color ) . '&text=' . str_replace( '#', '', $tnc_text_color ) . '&icon=' . $tnc_icon_color;
		break;

	default:
		$style_theme = 'aqua-white.css';
		break;
}


// display functions
function tnc_pvfw_display_share( $p_share ) {
	if ( $p_share == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_download( $p_download ) {
	if ( $p_download == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_print( $p_print ) {
	if ( $p_print == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_zoom( $p_zoom ) {
	if ( $p_zoom == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_fullscreen( $p_fullscreen ) {
	if ( $p_fullscreen == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_open( $p_open ) {
	if ( $p_open == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_logo( $p_logo ) {
	if ( $p_logo == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_pagenav( $p_pagenav ) {
	if ( $p_pagenav == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_find( $p_find ) {
	if ( $p_find == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_current_view( $p_current_view ) {
	if ( $p_current_view == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_rotate( $p_rotate ) {
	if ( $p_rotate == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_handtool( $p_handtool ) {
	if ( $p_handtool == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_doc_prop( $p_doc_prop ) {
	if ( $p_doc_prop == '0' ) {
		echo 'display: none';
	}
}
function tnc_pvfw_display_toggle_menu( $p_toggle_menu ) {
	if ( $p_toggle_menu == '0' ) {
		echo 'display: none';
	}
}

function tnc_pvfw_display_toggle_left( $p_toggle_left ) {
	if ( $p_toggle_left == '0' ) {
		echo 'display: none';
	}
}

function tnc_pvfw_display_scroll( $p_scroll ) {
	if ( $p_scroll == '0' ) {
		echo 'display: none';
	}
}

function tnc_pvfw_display_spread( $p_spread ) {
	if ( $p_spread == '0' ) {
		echo 'display: none';
	}
}

if ( function_exists( 'wfam_has_access' ) ) {
	$divide_file_url    = explode( 'uploads', $file );
	$get_requested_file = $divide_file_url[1];

	$file_requested = tnc_pvfw_generate_file_array( $get_requested_file );

	if ( ! wfam_has_access( $file_requested ) ) {
		?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="google" content="notranslate">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="icon" href="<?php echo esc_url( $favicon_url ); ?>">
  <title><?php echo esc_html_e( 'Permission Denied', $domain = 'pdf-viewer-for-wordpress' ); ?> - <?php bloginfo( 'name' ); ?></title>
  <meta property="og:image" content="<?php echo apply_filters( 'tnc_pvfw_facebook_share_thumb_url', plugins_url() . '/' . PVFW_PLUGIN_DIR . '/images/thumb.png' ); ?>">
  <meta name="twitter:card" content="summary_large_image">
  <style type="text/css">
	.pvfw-not-allowed{
	  margin: 100px auto;
	  text-align: center;
	  font-family: arial;
	}
	.pvfw-not-allowed h1{
	  font-size: 10em;
	  margin: 0;
	  color: #999;
	  text-shadow: 0px 0px 5px #eee;
	}
	.pvfw-not-allowed p{
	  font-size: 15px;
	}
	.pvfw-not-allowed a.tnc-go-home-btn{
	  padding: 15px 30px;
	  text-decoration: none;
	  display: inline-block;
	  border: 2px solid #999;
	  color: #333;
	  font-weight: bold;
	  margin-top: 30px;
	}

	@media only screen and (max-width: 600px){
	  .pvfw-not-allowed h1{
		font-size: 5em;
	  }
	}
  </style>

		<?php do_action( 'tnc_pvfw_not_allowed_head' ); ?>

</head>
<body>
  <div class='pvfw-not-allowed'>
	<h1><?php esc_html_e( 'SORRY', $domain = 'pdf-viewer-for-wordpress' ); ?></h1>
	<p><?php esc_html_e( 'You do not have permission to view this file, please contact us if you think this was by a mistake.', $domain = 'pdf-viewer-for-wordpress' ); ?></p>
	<a class='tnc-btn tnc-go-home-btn' target='_parent' href='<?php echo home_url(); ?>'><?php esc_html_e( 'Go To Homepage', $domain = 'pdf-viewer-for-wordpress' ); ?></a>
  </div>

  <?php do_action( 'tnc_pvfw_not_allowed_footer' ); ?>
</body>
</html>

		<?php
		die();
	}
}
?>
<!DOCTYPE html>
<!--
Copyright 2012 Mozilla Foundation

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

Adobe CMap resources are covered by their own copyright but the same license:

	Copyright 1990-2015 Adobe Systems Incorporated.

See https://github.com/adobe-type-tools/cmap-resources
-->
<html dir="ltr" mozdisallowselectionprint>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="google" content="notranslate">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="icon" href="<?php echo esc_url( $favicon_url ); ?>">
	<title><?php echo get_the_title(); ?> - <?php bloginfo( 'name' ); ?></title>

	<link rel="resource" type="application/l10n" href="<?php echo plugins_url() . '/' . esc_attr( TNC_PVFW_WEB_DIR ) . '/'; ?>locale/locale.properties" >
	<meta property="og:image" content="
	<?php
	if ( has_post_thumbnail( get_the_ID() ) ) {
		echo get_the_post_thumbnail_url( get_the_ID(), 'full' );
	} else {
		echo apply_filters( 'tnc_pvfw_facebook_share_thumb_url', plugins_url() . '/' . esc_attr( PVFW_PLUGIN_DIR ) . '/images/thumb.png' ); }
	?>
	">
	<meta name="twitter:card" content="summary_large_image">

	<?php wp_head(); ?>
	<style type="text/css">
		html{
			margin-top: 0px!important;
		}
	</style>
	<?php do_action( 'tnc_pvfw_head' ); ?>
</head>
<?php if ( ! empty( $analytics_id ) ) { ?>
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html( $analytics_id ); ?>"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', '<?php echo esc_html( $analytics_id ); ?>');
	</script>
<?php } ?>
<body class="loadingInProgress" tabindex="1"  
<?php
	if ( $context_menu_setting == '0' ) {
		echo 'oncontextmenu="return false"'; }
?>
>
<div id="outerContainer">

	<div id="sidebarContainer">
	<div id="toolbarSidebar">
		<div id="toolbarSidebarLeft">
		<div class="splitToolbarButton toggled">
			<button id="viewThumbnail" class="toolbarButton toggled" title="<?php echo esc_attr__( 'Show Thumbnails', 'pdf-viewer-for-wordpress' ); ?>" tabindex="2" data-l10n-id="thumbs">
				<span data-l10n-id="thumbs_label"> <?php echo esc_html__( 'Thumbnails', 'pdf-viewer-for-wordpress' ); ?> </span>
			</button>
			<button id="viewOutline" class="toolbarButton" title="<?php echo esc_attr__( 'Show Document Outline (double-click to expand/collapse all items)', 'pdf-viewer-for-wordpress' ); ?>" tabindex="3" data-l10n-id="document_outline">
				<span data-l10n-id="document_outline_label"><?php echo esc_html__( 'Document Outline', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
			<button id="viewAttachments" class="toolbarButton" title="<?php esc_attr_e( 'Show Attachments', 'pdf-viewer-for-wordpress' ); ?>" tabindex="4" data-l10n-id="attachments">
				<span data-l10n-id="attachments_label"><?php echo esc_html__( 'Attachments', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
			<button id="viewLayers" class="toolbarButton" title="<?php esc_attr_e( 'Show Layers (double-click to reset all layers to the default state)', 'pdf-viewer-for-wordpress' ); ?>" tabindex="5" data-l10n-id="layers">
				<span data-l10n-id="layers_label"><?php echo esc_html__( 'Layers', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
		</div>
		</div>
		<div id="toolbarSidebarRight">
		<div id="outlineOptionsContainer" class="hidden">
			<div class="verticalToolbarSeparator"></div>
			<button id="currentOutlineItem" class="toolbarButton" disabled="disabled" title="Find Current Outline Item" tabindex="6" data-l10n-id="current_outline_item">
			<span data-l10n-id="current_outline_item_label"><?php esc_html_e( 'Current Outline Item', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
		</div>
		</div> 
	</div>
	<div id="sidebarContent">
		<div id="thumbnailView">
		</div>
		<div id="outlineView" class="hidden">
		</div>
		<div id="attachmentsView" class="hidden">
		</div>
		<div id="layersView" class="hidden">
		</div>
	</div>
	<div id="sidebarResizer"></div>
	</div>  <!-- sidebarContainer -->

	<div id="mainContainer">
	<div style="<?php tnc_pvfw_display_find( $find ); ?>" class="findbar hidden doorHanger" id="findbar">
		<div id="findbarInputContainer">
		<input id="findInput" class="toolbarField" title="Find" placeholder="Find in document…" tabindex="91" data-l10n-id="find_input">
		<div class="splitToolbarButton">
			<button id="findPrevious" class="toolbarButton findPrevious" title="Find the previous occurrence of the phrase" tabindex="92" data-l10n-id="find_previous">
			<span data-l10n-id="find_previous_label"><?php esc_html_e( 'Previous', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
			<div class="splitToolbarButtonSeparator"></div>
			<button id="findNext" class="toolbarButton findNext" title="Find the next occurrence of the phrase" tabindex="93" data-l10n-id="find_next">
			<span data-l10n-id="find_next_label"><?php esc_html_e( 'Next', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
		</div>
		</div>

		<div id="findbarOptionsOneContainer">
		<input type="checkbox" id="findHighlightAll" class="toolbarField" tabindex="94">
		<label for="findHighlightAll" class="toolbarLabel" data-l10n-id="find_highlight"><?php esc_html_e( 'Highlight all', 'pdf-viewer-for-wordpress' ); ?></label>
		<input type="checkbox" id="findMatchCase" class="toolbarField" tabindex="95">
		<label for="findMatchCase" class="toolbarLabel" data-l10n-id="find_match_case_label"><?php esc_html_e( 'Match case', 'pdf-viewer-for-wordpress' ); ?></label>
		</div>

		<div id="findbarOptionsTwoContainer">
		<input type="checkbox" id="findEntireWord" class="toolbarField" tabindex="96">
		<label for="findEntireWord" class="toolbarLabel" data-l10n-id="find_entire_word_label"><?php esc_html_e( 'Whole words', 'pdf-viewer-for-wordpress' ); ?></label>
		<span id="findResultsCount" class="toolbarLabel hidden"></span>
		</div>

		<div id="findbarMessageContainer">
		<span id="findMsg" class="toolbarLabel"></span>
		</div>
	</div>  <!-- findbar -->

	<div id="secondaryToolbar" class="secondaryToolbar hidden doorHangerRight">
		<div id="secondaryToolbarButtonContainer">
		<button style="<?php tnc_pvfw_display_fullscreen( $fullscreen ); ?>" id="secondaryPresentationMode" class="secondaryToolbarButton presentationMode visibleLargeView" title="Switch to Presentation Mode" tabindex="51" data-l10n-id="presentation_mode">
			<span data-l10n-id="presentation_mode_label"><?php esc_html_e( 'Presentation Mode', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<button style="<?php tnc_pvfw_display_open( $open ); ?>" id="secondaryOpenFile" class="secondaryToolbarButton openFile visibleLargeView" title="Open File" tabindex="52" data-l10n-id="open_file">
			<span data-l10n-id="open_file_label"><?php esc_html_e( 'Open', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<button style="<?php tnc_pvfw_display_print( $print ); ?>" id="secondaryPrint" class="secondaryToolbarButton print visibleMediumView" title="Print" tabindex="53" data-l10n-id="print">
			<span data-l10n-id="print_label"><?php esc_html_e( 'Print', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<button style="<?php tnc_pvfw_display_download( $download ); ?>" id="secondaryDownload" class="secondaryToolbarButton download visibleMediumView" title="Download" tabindex="54" data-l10n-id="download">
			<span data-l10n-id="download_label"><?php esc_html_e( 'Download', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<a style="<?php tnc_pvfw_display_current_view( $current_view ); ?>" href="#" id="secondaryViewBookmark" class="secondaryToolbarButton bookmark visibleSmallView" title="Current view (copy or open in new window)" tabindex="55" data-l10n-id="bookmark">
			<span data-l10n-id="bookmark_label"><?php esc_html_e( 'Current View', 'pdf-viewer-for-wordpress' ); ?></span>
		</a>

		<div class="horizontalToolbarSeparator visibleLargeView"></div>

		<button style="<?php tnc_pvfw_display_pagenav( $pagenav ); ?>" id="firstPage" class="secondaryToolbarButton firstPage" title="Go to First Page" tabindex="56" data-l10n-id="first_page">
			<span data-l10n-id="first_page_label"><?php esc_html_e( 'Go to First Page', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>
		<button style="<?php tnc_pvfw_display_pagenav( $pagenav ); ?>" id="lastPage" class="secondaryToolbarButton lastPage" title="Go to Last Page" tabindex="57" data-l10n-id="last_page">
			<span data-l10n-id="last_page_label"><?php esc_html_e( 'Go to Last Page', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<div class="horizontalToolbarSeparator"></div>

		<button style="<?php tnc_pvfw_display_rotate( $rotate ); ?>" id="pageRotateCw" class="secondaryToolbarButton rotateCw" title="Rotate Clockwise" tabindex="58" data-l10n-id="page_rotate_cw">
			<span data-l10n-id="page_rotate_cw_label"><?php esc_html_e( 'Rotate Clockwise', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>
		<button style="<?php tnc_pvfw_display_rotate( $rotate ); ?>" id="pageRotateCcw" class="secondaryToolbarButton rotateCcw" title="Rotate Counterclockwise" tabindex="59" data-l10n-id="page_rotate_ccw">
			<span data-l10n-id="page_rotate_ccw_label"><?php esc_html_e( 'Rotate Counterclockwise', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<div class="horizontalToolbarSeparator"></div>

		<button style="<?php tnc_pvfw_display_handtool( $handtool ); ?>" id="cursorSelectTool" class="secondaryToolbarButton selectTool toggled" title="Enable Text Selection Tool" tabindex="60" data-l10n-id="cursor_text_select_tool">
			<span data-l10n-id="cursor_text_select_tool_label"><?php esc_html_e( 'Text Selection Tool', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<button style="<?php tnc_pvfw_display_handtool( $handtool ); ?>" id="cursorHandTool" class="secondaryToolbarButton handTool" title="Enable Hand Tool" tabindex="61" data-l10n-id="cursor_hand_tool">
			<span data-l10n-id="cursor_hand_tool_label"><?php esc_html_e( 'Hand Tool', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<div class="horizontalToolbarSeparator"></div>

		<button style="<?php tnc_pvfw_display_scroll( $scroll ); ?>" id="scrollVertical" class="secondaryToolbarButton scrollModeButtons scrollVertical toggled" title="Use Vertical Scrolling" tabindex="62" data-l10n-id="scroll_vertical">
			<span data-l10n-id="scroll_vertical_label"><?php esc_html_e( 'Vertical Scrolling', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>
		<button style="<?php tnc_pvfw_display_scroll( $scroll ); ?>" id="scrollHorizontal" class="secondaryToolbarButton scrollModeButtons scrollHorizontal" title="Use Horizontal Scrolling" tabindex="63" data-l10n-id="scroll_horizontal">
			<span data-l10n-id="scroll_horizontal_label"><?php esc_html_e( 'Horizontal Scrolling', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>
		<button style="<?php tnc_pvfw_display_scroll( $scroll ); ?>" id="scrollWrapped" class="secondaryToolbarButton scrollModeButtons scrollWrapped" title="Use Wrapped Scrolling" tabindex="64" data-l10n-id="scroll_wrapped">
			<span data-l10n-id="scroll_wrapped_label"><?php esc_html_e( 'Wrapped Scrolling', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<!-- $PVFW_FB: bookflip button -->
		<button style="<?php tnc_pvfw_display_scroll( $scroll ); ?>" id="bookFlip" class="secondaryToolbarButton scrollModeButtons bookFlip" title="Flip Book Style" tabindex="65" data-l10n-id="book_flip">
			<span data-l10n-id="book_flip_label"><?php esc_html_e( 'Book Flip', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<div class="horizontalToolbarSeparator scrollModeButtons"></div>

		<button style="<?php tnc_pvfw_display_spread( $spread ); ?>" id="spreadNone" class="secondaryToolbarButton spreadModeButtons spreadNone toggled" title="Do not join page spreads" tabindex="66" data-l10n-id="spread_none">
			<span data-l10n-id="spread_none_label"><?php esc_html_e( 'No Spreads', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>
		<button style="<?php tnc_pvfw_display_spread( $spread ); ?>" id="spreadOdd" class="secondaryToolbarButton spreadModeButtons spreadOdd" title="Join page spreads starting with odd-numbered pages" tabindex="67" data-l10n-id="spread_odd">
			<span data-l10n-id="spread_odd_label"><?php esc_html_e( 'Odd Spreads', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>
		<button style="<?php tnc_pvfw_display_spread( $spread ); ?>" id="spreadEven" class="secondaryToolbarButton spreadModeButtons spreadEven" title="Join page spreads starting with even-numbered pages" tabindex="68" data-l10n-id="spread_even">
			<span data-l10n-id="spread_even_label"><?php esc_html_e( 'Even Spreads', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>

		<div class="horizontalToolbarSeparator spreadModeButtons"></div>

		<button style="<?php tnc_pvfw_display_doc_prop( $doc_prop ); ?>" id="documentProperties" class="secondaryToolbarButton documentProperties" title="Document Properties…" tabindex="69" data-l10n-id="document_properties">

			<span data-l10n-id="document_properties_label"><?php esc_html_e( 'Document Properties…', 'pdf-viewer-for-wordpress' ); ?></span>
		</button>
		</div>
	</div>  <!-- secondaryToolbar -->

	<div class="toolbar">
		<div id="toolbarContainer">
		<div id="toolbarViewer">
			<div id="toolbarViewerLeft">
			<button style="<?php tnc_pvfw_display_toggle_left( $toggle_left ); ?>" id="sidebarToggle" class="toolbarButton" title="Toggle Sidebar" tabindex="11" data-l10n-id="toggle_sidebar">
				<span data-l10n-id="toggle_sidebar_label"><?php esc_html_e( 'Toggle Sidebar', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
			<div class="toolbarButtonSpacer"></div>
			<button style="<?php tnc_pvfw_display_find( $find ); ?>" id="viewFind" class="toolbarButton" title="Find in Document" tabindex="12" data-l10n-id="findbar">
				<span data-l10n-id="findbar_label"><?php esc_html_e( 'Find', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
			<div style="<?php tnc_pvfw_display_pagenav( $pagenav ); ?>" class="splitToolbarButton hiddenSmallView">
				<button class="toolbarButton pageUp" title="Previous Page" id="previous" tabindex="13" data-l10n-id="previous">
				<span data-l10n-id="previous_label"><?php esc_html_e( 'Previous', 'pdf-viewer-for-wordpress' ); ?></span>
				</button>
				<div class="splitToolbarButtonSeparator"></div>
				<button class="toolbarButton pageDown" title="Next Page" id="next" tabindex="14" data-l10n-id="next">
				<span data-l10n-id="next_label"><?php esc_html_e( 'Next', 'pdf-viewer-for-wordpress' ); ?></span>
				</button>
			</div>

			<input style="<?php tnc_pvfw_display_pagenav( $pagenav ); ?>" type="number" id="pageNumber" class="toolbarField pageNumber" title="Page" value="1" size="4" min="1" tabindex="15" data-l10n-id="page">
			<span style="<?php tnc_pvfw_display_pagenav( $pagenav ); ?>" id="numPages" class="toolbarLabel"></span>
			<span class="social_icon_d" id="open_slink" style="<?php tnc_pvfw_display_share( $share ); ?>"></span>

			<div class="tnc_social_share" id="tnc-share" style="display: none;">
				<?php
				function pagelink() {
					$pageURL = 'http';
					if ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on' ) {
						$pageURL .= 's';
					}
					$pageURL .= '://';
					if ( $_SERVER['SERVER_PORT'] != '80' ) {
						$pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
					} else {
						$pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
					}
					return esc_url( $pageURL );
				}
				$share_url = pagelink();
				?>
				<ul>
					<li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_html( $share_url ); ?>" target="_blank" class="tnc_fb"><?php esc_html_e( 'Facebook', 'pdf-viewer-for-wordpress' ); ?></a></li>
					<li><a href="https://twitter.com/intent/tweet?url=<?php echo esc_html( $share_url ); ?>&text=I Liked this pdf" target="_blank" class="tnc_tw"><?php esc_html_e( 'Twitter', 'pdf-viewer-for-wordpress' ); ?></a></li>
					<li><a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_html( $share_url ); ?>" target="_blank" class="tnc_lin"><?php esc_html_e( 'Linkedin', 'pdf-viewer-for-wordpress' ); ?></a></li>
					<li><a href="https://api.whatsapp.com/send?text=<?php echo esc_html( $share_url ); ?>" target="_blank" class="tnc_whatsapp"><?php esc_html_e( 'WhatsApp', 'pdf-viewer-for-wordpress' ); ?></a></li>
					<li><a href="#sendtofriend" rel="modal:open" class="tnc_email"><?php esc_html_e( 'Email', 'pdf-viewer-for-wordpress' ); ?></a></li>
				</ul>
			</div>
			</div>
			<div id="toolbarViewerRight">
			<div style="<?php tnc_pvfw_display_logo( $logo ); ?>" class="logo_block"><h3 class="logo_text"><a href="<?php bloginfo( 'url' ); ?>" title="<?php bloginfo( 'name' ); ?>"><img src="<?php echo $logo_image_url; ?>" class="tnc_logo_image" /></a></h3></div>

			<button style="<?php tnc_pvfw_display_fullscreen( $fullscreen ); ?>" id="presentationMode" class="toolbarButton presentationMode hiddenLargeView" title="Switch to Presentation Mode" tabindex="31" data-l10n-id="presentation_mode">
				<span data-l10n-id="presentation_mode_label"><?php esc_html_e( 'Presentation Mode', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>

			<button style="<?php tnc_pvfw_display_open( $open ); ?>" id="openFile" class="toolbarButton openFile hiddenLargeView" title="Open File" tabindex="32" data-l10n-id="open_file">
				<span data-l10n-id="open_file_label"><?php esc_html_e( 'Open', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>

			<button style="<?php tnc_pvfw_display_print( $print ); ?>" id="print" class="toolbarButton print hiddenMediumView" title="Print" tabindex="33" data-l10n-id="print">
				<span data-l10n-id="print_label"><?php esc_html_e( 'Print', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>

			<button style="<?php tnc_pvfw_display_download( $download ); ?>" id="download" class="toolbarButton download hiddenMediumView" title="Download" tabindex="34" data-l10n-id="download">
				<span data-l10n-id="download_label"><?php esc_html_e( 'Download', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
			<a style="<?php tnc_pvfw_display_current_view( $current_view ); ?>" href="#" id="viewBookmark" class="toolbarButton bookmark hiddenSmallView" title="Current view (copy or open in new window)" tabindex="35" data-l10n-id="bookmark">
				<span data-l10n-id="bookmark_label"><?php esc_html_e( 'Current View', 'pdf-viewer-for-wordpress' ); ?></span>
			</a>

			<div class="verticalToolbarSeparator hiddenSmallView"></div>

			<button style="<?php tnc_pvfw_display_toggle_menu( $toggle_menu ); ?>" id="secondaryToolbarToggle" class="toolbarButton" title="Tools" tabindex="36" data-l10n-id="tools">
				<span data-l10n-id="tools_label"><?php esc_html_e( 'Tools', 'pdf-viewer-for-wordpress' ); ?></span>
			</button>
			</div>
			<div style="<?php tnc_pvfw_display_zoom( $zoom ); ?>" id="toolbarViewerMiddle">
			<div class="splitToolbarButton">
				<button id="zoomOut" class="toolbarButton zoomOut" title="Zoom Out" tabindex="21" data-l10n-id="zoom_out">
				<span data-l10n-id="zoom_out_label"><?php esc_html_e( 'Zoom Out', 'pdf-viewer-for-wordpress' ); ?></span>
				</button>
				<div class="splitToolbarButtonSeparator"></div>
				<button id="zoomIn" class="toolbarButton zoomIn" title="Zoom In" tabindex="22" data-l10n-id="zoom_in">
				<span data-l10n-id="zoom_in_label"><?php esc_html_e( 'Zoom In', 'pdf-viewer-for-wordpress' ); ?></span>
				</button>
			</div>
			<span id="scaleSelectContainer" class="dropdownToolbarButton">
				<select id="scaleSelect" title="Zoom" tabindex="23" data-l10n-id="zoom">
				<option id="pageAutoOption" title="" value="auto" selected="selected" data-l10n-id="page_scale_auto"><?php esc_html_e( 'Automatic Zoom', 'pdf-viewer-for-wordpress' ); ?></option>
				<option id="pageActualOption" title="" value="page-actual" data-l10n-id="page_scale_actual"><?php esc_html_e( 'Actual Size', 'pdf-viewer-for-wordpress' ); ?></option>
				<option id="pageFitOption" title="" value="page-fit" data-l10n-id="page_scale_fit"><?php esc_html_e( 'Page Fit', 'pdf-viewer-for-wordpress' ); ?></option>
				<option id="pageWidthOption" title="" value="page-width" data-l10n-id="page_scale_width"><?php esc_html_e( 'Page Width', 'pdf-viewer-for-wordpress' ); ?></option>
				<option id="customScaleOption" title="" value="custom" disabled="disabled" hidden="true"></option>
				<option title="" value="0.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 50 }'><?php esc_html_e( '50%', 'pdf-viewer-for-wordpress' ); ?></option>
				<option title="" value="0.75" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 75 }'><?php esc_html_e( '75%', 'pdf-viewer-for-wordpress' ); ?></option>
				<option title="" value="1" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 100 }'><?php esc_html_e( '100%', 'pdf-viewer-for-wordpress' ); ?></option>
				<option title="" value="1.25" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 125 }'><?php esc_html_e( '125%', 'pdf-viewer-for-wordpress' ); ?></option>
				<option title="" value="1.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 150 }'><?php esc_html_e( '150%', 'pdf-viewer-for-wordpress' ); ?></option>
				<option title="" value="2" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 200 }'><?php esc_html_e( '200%', 'pdf-viewer-for-wordpress' ); ?></option>
				<option title="" value="3" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 300 }'><?php esc_html_e( '300%', 'pdf-viewer-for-wordpress' ); ?></option>
				<option title="" value="4" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 400 }'><?php esc_html_e( '400%', 'pdf-viewer-for-wordpress' ); ?></option>
				</select>
			</span>
			</div>
		</div>
		<div id="loadingBar">
			<div class="progress">
			<div class="glimmer">
			</div>
			</div>
		</div>
		</div>
	</div>

	<menu type="context" id="viewerContextMenu">
		<menuitem id="contextFirstPage" label="First Page"
				data-l10n-id="first_page"></menuitem>
		<menuitem id="contextLastPage" label="Last Page"
				data-l10n-id="last_page"></menuitem>
		<menuitem style="<?php tnc_pvfw_display_rotate( $rotate ); ?>" id="contextPageRotateCw" label="Rotate Clockwise"
				data-l10n-id="page_rotate_cw"></menuitem>
		<menuitem style="<?php tnc_pvfw_display_rotate( $rotate ); ?>" id="contextPageRotateCcw" label="Rotate Counter-Clockwise"
				data-l10n-id="page_rotate_ccw"></menuitem>
	</menu>

	<div id="viewerContainer" tabindex="0">
		<div id="viewer" class="pdfViewer"></div>
	</div>

	<div id="errorWrapper" hidden='true'>
		<div id="errorMessageLeft">
		<span id="errorMessage"></span>
		<button id="errorShowMore" data-l10n-id="error_more_info">
			<?php esc_html_e( 'More Information', 'pdf-viewer-for-wordpress' ); ?>
		</button>
		<button id="errorShowLess" data-l10n-id="error_less_info" hidden='true'>
			<?php esc_html_e( 'Less Information', 'pdf-viewer-for-wordpress' ); ?>
		</button>
		</div>
		<div id="errorMessageRight">
		<button id="errorClose" data-l10n-id="error_close">
			<?php esc_html_e( 'Close', 'pdf-viewer-for-wordpress' ); ?>
		</button>
		</div>
		<div class="clearBoth"></div>
		<textarea id="errorMoreInfo" hidden='true' readonly="readonly"></textarea>
	</div>
	</div> <!-- mainContainer -->

	<div id="overlayContainer" class="hidden">
	<div id="passwordOverlay" class="container hidden">
		<div class="dialog">
		<div class="row">
			<p id="passwordText" data-l10n-id="password_label"><?php esc_html_e( 'Enter the password to open this PDF file:', 'pdf-viewer-for-wordpress' ); ?></p>
		</div>
		<div class="row">
			<input type="password" id="password" class="toolbarField">
		</div>
		<div class="buttonRow">
			<button id="passwordCancel" class="overlayButton"><span data-l10n-id="password_cancel"><?php esc_html_e( 'Cancel', 'pdf-viewer-for-wordpress' ); ?></span></button>
			<button id="passwordSubmit" class="overlayButton"><span data-l10n-id="password_ok"><?php esc_html_e( 'OK', 'pdf-viewer-for-wordpress' ); ?></span></button>
		</div>
		</div>
	</div>
	<div id="documentPropertiesOverlay" class="container hidden">
		<div class="dialog">
		<div class="row">
			<span data-l10n-id="document_properties_file_name"><?php esc_html_e( 'File name:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="fileNameField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_file_size"><?php esc_html_e( 'File size:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="fileSizeField">-</p>
		</div>
		<div class="separator"></div>
		<div class="row">
			<span data-l10n-id="document_properties_title"><?php esc_html_e( 'Title:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="titleField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_author"><?php esc_html_e( 'Author:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="authorField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_subject"><?php esc_html_e( 'Subject:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="subjectField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_keywords"><?php esc_html_e( 'Keywords:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="keywordsField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_creation_date"><?php esc_html_e( 'Creation Date:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="creationDateField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_modification_date"><?php esc_html_e( 'Modification Date:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="modificationDateField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_creator"><?php esc_html_e( 'Creator:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="creatorField">-</p>
		</div>
		<div class="separator"></div>
		<div class="row">
			<span data-l10n-id="document_properties_producer"><?php esc_html_e( 'PDF Producer:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="producerField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_version"><?php esc_html_e( 'PDF Version:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="versionField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_page_count"><?php esc_html_e( 'Page Count:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="pageCountField">-</p>
		</div>
		<div class="row">
			<span data-l10n-id="document_properties_page_size"><?php esc_html_e( 'Page Size:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="pageSizeField">-</p>
		</div>
		<div class="separator"></div>
		<div class="row">
			<span data-l10n-id="document_properties_linearized"><?php esc_html_e( 'Fast Web View:', 'pdf-viewer-for-wordpress' ); ?></span> <p id="linearizedField">-</p>  
		</div>
		<div class="buttonRow">
			<button id="documentPropertiesClose" class="overlayButton"><span data-l10n-id="document_properties_close"><?php esc_html_e( 'Close', 'pdf-viewer-for-wordpress' ); ?></span></button>
		</div>
		</div>
	</div>
	<div id="printServiceOverlay" class="container hidden">
		<div class="dialog">
		<div class="row">
			<span data-l10n-id="print_progress_message"><?php esc_html_e( 'Preparing document for printing…', 'pdf-viewer-for-wordpress' ); ?></span>
		</div>
		<div class="row">
			<progress value="0" max="100"></progress>
			<span data-l10n-id="print_progress_percent" data-l10n-args='{ "progress": 0 }' class="relative-progress"><?php esc_html_e( '0%', 'pdf-viewer-for-wordpress' ); ?></span>
		</div>
		<div class="buttonRow">
			<button id="printCancel" class="overlayButton"><span data-l10n-id="print_progress_close"><?php esc_html_e( 'Cancel', 'pdf-viewer-for-wordpress' ); ?></span></button>
		</div>
		</div>
	</div>
	</div>  <!-- overlayContainer -->
	<?php if( $pvfw_flip_audio == "1" ){ ?>
		<audio id="audio" style="display:none;" src="<?php echo plugins_url() . '/' . esc_attr( PVFW_PLUGIN_DIR ) . '/web/pdf-turn/flip-blank.mp3'; ?>"></audio>
	<?php } else { ?>
		<audio id="audio" style="display:none;" src="<?php echo plugins_url() . '/' . esc_attr( PVFW_PLUGIN_DIR ) . '/web/pdf-turn/flip-audio-1.mp3'; ?>"></audio>
	<?php } ?>

</div> <!-- outerContainer -->
<div id="printContainer"></div>
<div id="sendtofriend" class="send-to-friend" style="display: none;">
<h3><?php esc_html_e( 'Share this file with friends', 'pdf-viewer-for-wordpress' ); ?></h3>
<form action="" method="POST" id="send-to-friend-form">
<?php esc_html_e( 'Your Name', 'pdf-viewer-for-wordpress' ); ?><br>
<input name="yourname" id="yourname" type="text" size="40" value=""><br>
<?php esc_html_e( 'Friends Name', 'pdf-viewer-for-wordpress' ); ?><br>
<input name="friendsname" type="text" size="40" value=""><br>
<?php esc_html_e( 'Your Email Address', 'pdf-viewer-for-wordpress' ); ?><br>
<input name="youremailaddress" type="email" size="40" value=""><br> 

<?php esc_html_e( 'Friends Email Address', 'pdf-viewer-for-wordpress' ); ?><br>
<input name="friendsemailaddress" type="email" size="40" value=""><br>

<?php esc_html_e( 'Email Subject', 'pdf-viewer-for-wordpress' ); ?><br>
<input name="email_subject" type="text" size="40" value=""><br>

<?php esc_html_e( 'Message', 'pdf-viewer-for-wordpress' ); ?><br>
<textarea name="message" id="message" cols="37" rows= "4">
Hi,
Please check out this pdf file: <?php echo $share_url; ?>

Thank You
</textarea>
<?php
$nonce = wp_create_nonce( 'tnc_mail_to_friend_nonce' );
?>
<br>
<input type="hidden" name="tnc_nonce" value="<?php echo $nonce; ?>" />
<input type="hidden" name="tnc_ajax" value="<?php echo admin_url( 'admin-ajax.php' ); ?>" />
<input type="submit" class="s-btn-style" id="send-to-friend-btn" value="Send Now" />
<input class="r-btn-style" type="reset" name="reset" value="Reset">
</form>

	<div id="email-result" class="email-result"></div>
</div>
	  <?php if ( ! empty( $analytics_id ) ) { ?>
	  <script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', '<?php echo esc_html( $analytics_id ); ?>', 'auto');
		ga('send', 'pageview');
	  </script>
	  <?php } ?>
	  <script type="text/javascript">
		document.onkeydown = function (e) {
			e = e || window.event;//Get event
			if (e.ctrlKey) {
				var c = e.which || e.keyCode;//Get key code
				switch (c) {
					case 83://Block Ctrl+S
					case 85://Block Ctrl+u
					<?php if ( $copying_setting == '0' ) { ?>
					  case 67://Block cmd+c
					<?php } ?>
					case 80://Block Ctrl+c
						e.preventDefault();     
						e.stopPropagation();
					break;
				}
			}
			if (e.metaKey) {
				var c = e.which || e.keyCode;//Get key code
				switch (c) {
					case 83://Block cmd+S
					case 85://Block cmd+u
					<?php if ( $copying_setting == '0' ) { ?>
					  case 67://Block cmd+c
					<?php } ?>
					case 80://Block cmd+c
						e.preventDefault();     
						e.stopPropagation();
					break;
				}
			}
		};
		   
		</script>
		<div class="tnc-pdf-back-to-btn">
		<?php 
			if( isset( $_SERVER['HTTP_REFERER'] ) ) {
				$return_link = $_SERVER['HTTP_REFERER'];
			} else {
				$return_link = home_url();
			}
		?>
		  <a href="<?php echo esc_html( $return_link ); ?>">
			<?php
			if ( ! empty( $get_return_link_text ) ) {
				$return_link_text = $get_return_link_text;
			} else {
				$return_link_text = 'Return to Site';
			}
			  echo esc_html__( $return_link_text, 'pdf-viewer-for-wordpress' );
			?>
		  </a>
		</div>
		<?php if ( $pagenav == '1' ) { ?>
		  <button style="<?php tnc_pvfw_display_pagenav( $pagenav ); ?>" class=" pvfw_page_prev" id="pvfw-previous-page" onclick="pvfw_prevpage()"><img src="<?php echo plugins_url() . '/' . TNC_PVFW_WEB_DIR . '/'; ?>schemes/light-icons/toolbarButton-pagePrev.svg"  alt=""></button>

		  <button style="<?php tnc_pvfw_display_pagenav( $pagenav ); ?>" class="pvfw_page_next" id="pvfw-next-page" onclick="pvfw_nextpage()"><img src="<?php echo plugins_url() . '/' . TNC_PVFW_WEB_DIR . '/'; ?>schemes/light-icons/toolbarButton-pageNext.svg"  alt=""></button>

		  <button style="<?php tnc_pvfw_display_pagenav( $pagenav ); ?> display: none;" class=" pvfw_page_prev" id="pvfw-flip-previous-page"><img src="<?php echo plugins_url() . '/' . TNC_PVFW_WEB_DIR . '/'; ?>schemes/light-icons/toolbarButton-pagePrev.svg"  alt=""></button>

		  <button style="<?php tnc_pvfw_display_pagenav( $pagenav ); ?> display: none;" class="pvfw_page_next" id="pvfw-flip-next-page"><img src="<?php echo plugins_url() . '/' . TNC_PVFW_WEB_DIR . '/'; ?>schemes/light-icons/toolbarButton-pageNext.svg"  alt=""></button>

		  <script>
		  jQuery("#pvfw-flip-next-page").on("click", function(e){
			var get_direction = jQuery("html").attr('dir');
			if( get_direction == "rtl" ){
			  jQuery("#viewer").turn("previous");
			} else {
			  jQuery("#viewer").turn("next");
			}
		  });

		  jQuery("#pvfw-flip-previous-page").on("click", function(e){
			var get_direction = jQuery("html").attr('dir');
			if( get_direction == "rtl" ){
			  jQuery("#viewer").turn("next");
			} else {
			  jQuery("#viewer").turn("previous");
			}
		  });
		  </script>
		<?php } ?>
		<script>
		  // display return button when not loaded inside iframe
		  function inIframe () {
			try {
				return window.self !== window.top;
			} catch (e) {
				return true;
			}
		  }
		
		  if ( inIframe() ) {
			jQuery( '.tnc-pdf-back-to-btn' ).hide();
		  }
		</script>
		<?php echo tnc_pvfw_site_registered_message(); ?>
	  <?php do_action( 'tnc_pvfw_footer' ); ?>
  </body>
</html>
