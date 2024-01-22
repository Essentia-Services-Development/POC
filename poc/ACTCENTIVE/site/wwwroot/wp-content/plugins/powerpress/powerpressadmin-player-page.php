<?php
// PowerPress Player settings page

require_once( POWERPRESS_ABSPATH. '/powerpress-player.php'); // Include, if not included already

function powerpressplayer_mediaelement_info($full_info = true)
{
?>
	<p>
		<?php echo __('MediaElement.js is an open source HTML5 audio and video player that supports both audio (mp3, m4a and oga) and video (mp4, m4v, webm, ogv and flv) media files. It includes all the necessary features for playback including a play/pause button, scroll-able position bar, elapsed time, total time, mute button and volume control.', 'powerpress'); ?>
	</p>
	<?php
	if( $full_info )
	{ 
	?>
	<p>
		<?php echo __('MediaElement.js is the default player in Blubrry PowerPress because it is HTML and CSS based, meets accessibility standards including WebVTT, and will play in any browser using either HTML5, Flash or Silverlight for playback.', 'powerpress'); ?>
	</p>
<?php
	}
}

function powerpressplayer_videojs_info()
{
	$plugin_link = '';
	
	if( !function_exists('add_videojs_header') && file_exists( WP_PLUGIN_DIR . '/' . 'videojs-html5-video-player-for-wordpress' ) ) // plugin downloaded but not activated...
	{
		$plugin_file = 'videojs-html5-video-player-for-wordpress' . '/' . 'video-js.php';
		$plugin_link = '<a href="' . esc_url(wp_nonce_url(admin_url('plugins.php?plugin_status=active&action=activate&plugin=' . $plugin_file ), 'activate-plugin_' . $plugin_file)) .
										'"title="' . esc_attr__('Activate Plugin') . '"">' . __('VideoJS - HTML5 Video Player for WordPress plugin', 'powerpress') . '</a>';
	
	
	} else {
		$plugin_link = '<a href="'. esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . 'videojs-html5-video-player-for-wordpress' .
									'&TB_iframe=true&width=600&height=550' ) ) .'" class="thickbox" title="' .
									esc_attr__('Install Plugin') . '">'. __('VideoJS - HTML5 Video Player for WordPress plugin', 'powerpress') . '</a>';
	}
?>
	<p style="margin-bottom: 20px;">
		<?php echo __('VideoJS is a HTML5 JavaScript and CSS video player with fallback to Flash. ', 'powerpress'); ?>
	</p>
	
	<?php if( $plugin_link ) { ?>
	<div <?php echo ( function_exists('add_videojs_header') ?'':' styleX="background-color: #FFFFE0; border: 1px solid #E6DB55; padding: 8px 12px; line-height: 29px; font-weight: bold; font-size: 14px; display:inline;"'); ?>><p>
		<?php echo sprintf(__('The %s must be installed and activated in order to enable this feature.', 'powerpress'), $plugin_link ); ?>
	</p></div>
	<?php } ?>
<?php
}

function powerpress_admin_players($type='audio')
{
    if (defined('WP_DEBUG')) {
        if (WP_DEBUG) {
            wp_register_style('powerpress_settings_style',  powerpress_get_root_url() . 'css/settings.css', array(), POWERPRESS_VERSION);
        } else {
            wp_register_style('powerpress_settings_style',  powerpress_get_root_url() . 'css/settings.min.css', array(), POWERPRESS_VERSION);
        }
    } else {
        wp_register_style('powerpress_settings_style',  powerpress_get_root_url() . 'css/settings.min.css', array(), POWERPRESS_VERSION);
    }
    wp_enqueue_style("powerpress_settings_style");

    // colorpicker library
    wp_register_script('powerpress_colorpicker_new_js', 'https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js');
    wp_enqueue_script('powerpress_colorpicker_new_js');
    wp_register_style('powerpress_colorpicker_new_css', 'https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/monolith.min.css');
    wp_enqueue_style('powerpress_colorpicker_new_css');

	$General = powerpress_get_settings('powerpress_general');
	
	$select_player = true;
	if( isset($_REQUEST['ep']) )
	{
		$select_player = false;
	}
	
	if( isset($_GET['sp']) )
	{
		$select_player = true;
	}
	else if( $type == 'video' )
	{
		if( empty($General['video_player']) ) {
			$select_player = true;
		} else {
			switch( $General['video_player'] ) {
				case 'mediaelement-video':
				case 'videojs-html5-video-player-for-wordpress':
				case 'html5video': break;
				default: {
					$select_player = true;
				};
			}
		}
	}
	else
	{
		if( empty($General['player']) )
		{
			$select_player = true;
		}
		else
		{
			switch( $General['player'] )
			{
                case 'blubrrymodern':
				case 'blubrryaudio':
				case 'mediaelement-audio':
				case 'html5audio': break;
				default: {
					$select_player = true;
				};
			}
		}
	}
	
	if( empty($General['player']) )
		$General['player'] = 'mediaelement-audio';
	
	if( empty($General['player']) )
		$General['video_player'] = 'mediaelement-video';
	
	if( empty($General['audio_custom_play_button']) )
		$General['audio_custom_play_button'] = '';
	
	
		
	$Audio = array();
	$Audio['html5audio'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/html5.mp3';
	$Audio['mediaelement-audio'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/MediaElement_audio.mp3';
	$Audio['blubrryaudio'] = ''; // Set hardcoded by ID
		
	
	$Video = array();
	$Video['html5video'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/html5.mp4';
	$Video['videojs-html5-video-player-for-wordpress'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/videojs.mp4';
	$Video['mediaelement-video'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/MediaElement_video.mp4';
		
		wp_enqueue_style( 'wp-color-picker' );
    	wp_enqueue_script( 'wp-color-picker');

	if( $type == 'video' && function_exists('add_videojs_header') )
			add_videojs_header();
?>
<link rel="stylesheet" href="<?php echo powerpress_get_root_url(); ?>3rdparty/colorpicker/css/colorpicker.css" type="text/css" />
<script type="text/javascript" src="<?php echo powerpress_get_root_url(); ?>3rdparty/colorpicker/js/colorpicker.js"></script>
<script type="text/javascript" src="<?php echo powerpress_get_root_url(); ?>player.min.js"></script>
<script type="text/javascript">

function rgb2hex(rgb) {
 
 rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
 function hex(x) {
  hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
  return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
 }
 
 if( rgb )
	return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
 return '';
}

function UpdatePlayerPreview(name, value)
{
	if( typeof(generator) != "undefined" ) // Update the Maxi player...
	{
		generator.updateParam(name, value);
		generator.updatePlayer();
	}
	
	if( typeof(update_audio_player) != "undefined" ) // Update the 1 px out player...
		update_audio_player();
}

jQuery(document).ready(function($) {
    if(document.getElementById('modern_player_iframe_div') !== null){
        generateModernPlayerHash();
    } else if(document.getElementById('player_iframe_div') !== null){
        generatePlayerHash();
    }
    <?php $modernPlayerSettings = powerpress_get_settings('powerpress_bplayer'); ?>
// Simple example, see optional options for more configuration.
    if (jQuery('.new-color-field-button').length > 0) {
        var pickr = Pickr.create({
            el: '.new-color-field-button',
            theme: 'monolith',
            useAsButton: true,
            default: '<?php echo !empty($modernPlayerSettings['backgroundcolor']) ? $modernPlayerSettings['backgroundcolor'] : "#ffffff"; ?>',
            defaultRepresentation: 'HEX',
            components: {

                // Main components
                preview: true,
                opacity: true,
                hue: true,

                // Input / output Options
                interaction: {
                    input: true
                }
            }
        });

        pickr.on('show', (color, source, instance) => {
            console.log(color.toHEXA().toString());
        });

        pickr.on('change', (color, source, instance) => {
            console.log(color.toHEXA().toString());
            jQuery('#backgroundcolorbutton').attr('style', 'background-color: ' + color.toHEXA().toString());
            jQuery('#backgroundcolor').attr('value', color.toHEXA().toString());
        });

        pickr.on('save', (color, source, instance) => {
            console.log(color.toHEXA());
        });
        console.log(pickr.setColor('<?php echo !empty($modernPlayerSettings['backgroundcolor']) ? $modernPlayerSettings['backgroundcolor'] : "#ffffff"; ?>'));
        //console.log(pickr.getColor().toHEXA().toString());
    }

    function generateModernPlayerHash(){
        var textColor = jQuery("input[name='ModernPlayer[textcolor]']:checked").val();
        var backgroundColor = jQuery('input[name="ModernPlayer[backgroundcolor]"]').val();
        if(backgroundColor.charAt(0) === '#'){
            backgroundColor = backgroundColor.substring(1);
        }
        var addition = '&modern_preview=1#textcolor-' + textColor + '&backgroundcolor-' + backgroundColor;

        var episodeId = 80155910;
        if(location.hostname === 'blubrry.local'){
            var domain = 'http://player.blubrry.local';
            episodeId = 12559710;
        } else {
            domain = 'https://player.blubrry.com';

        }

        document.getElementById('modern_player_iframe_div').innerHTML = '<iframe src="' + domain + '?id=' + episodeId + addition + '" id="modernplayeriframe" scrolling="yes" width="100%" height="<?php echo !empty($modernPlayerSettings['transcriptspacing']) ? $modernPlayerSettings['transcriptspacing'] : '230px'; ?>" frameborder="0"></iframe>';
    }

    function generatePlayerHash(){
        var time = jQuery("input.time-form").val();
        if(time==='') {
            time = '0';
        }
        var darkorlightval = jQuery("input[name='BBPlayer[playerstyle]']:checked").val();
        var color1=jQuery('input[name="BBPlayer[showbg]"]').val();
        var color2=jQuery('input[name="BBPlayer[downloadbgcolor]"]').val();
        var color3=jQuery('input[name="BBPlayer[subscribebg]"]').val();
        var color4=jQuery('input[name="BBPlayer[bgshare]"]').val();
        var fontcolor1 = jQuery('input[name="BBPlayer[showtext]"]').val();
        var fontcolor2 = jQuery('input[name="BBPlayer[downloadcolortext]"]').val();
        var fontcolor3 = jQuery('input[name="BBPlayer[textsubscribe]"]').val();
        var fontcolor4 = jQuery('input[name="BBPlayer[textshare]"]').val();
        var addition = '#time-'+time+"&darkOrLight-"+darkorlightval+"&shownotes-"+fontcolor1.substring(1)+"&shownotesBackground-"+color1.substring(1)+
            "&download-"+fontcolor2.substring(1)+"&downloadBackground-"+color2.substring(1)+"&subscribe-"+fontcolor3.substring(1)+"&subscribeBackground-"+color3.substring(1)+
            "&share-"+fontcolor4.substring(1)+"&shareBackground-"+color4.substring(1);

            document.getElementById('player_iframe_div').innerHTML = '<iframe src="https://player.blubrry.com?podcast_id=12559710' + addition + '" id="playeriframe" scrolling="no" width="100%" height="138px" frameborder="0"></iframe>';

    }

    function restoreDefaultColors(){
        document.getElementById('player_iframe_div').innerHTML ='<iframe src="https://player.blubrry.com?podcast_id=12559710" id="playeriframe" scrolling="no" width="100%" height="138px" frameborder="0"></iframe>';

        jQuery('input[name="BBPlayer[downloadbgcolor]"]').wpColorPicker('color',"#003366");
        jQuery('input[name="BBPlayer[downloadcolortext]"]').wpColorPicker('color',"#ffffff");
        jQuery('input[name="BBPlayer[subscribebg]"]').wpColorPicker('color',"#fb8c00");
        jQuery('input[name="BBPlayer[textsubscribe]"]').wpColorPicker('color',"#ffffff");
        jQuery('input[name="BBPlayer[bgshare]"]').wpColorPicker('color', "#1976d2");
        jQuery('input[name="BBPlayer[textshare]"]').wpColorPicker('color',"#ffffff");
        jQuery('input[name="BBPlayer[showbg]"]').wpColorPicker('color',"#444444");
        jQuery('input[name="BBPlayer[showtext]"]').wpColorPicker('color',"#ffffff");

        jQuery("input:radio[name='BBPlayer[playerstyle]'][value='light']").prop('checked', true);
    }

    function restoreModernDefaultColors(){
        var addition = '&modern_preview=1#textcolor-Dark&backgroundcolor-ffffff';

        if(location.hostname === 'blubrry.local'){
            var domain = 'http://player.blubrry.local';
        } else {
            domain = 'https://player.blubrry.com';
        }
        document.getElementById('modern_player_iframe_div').innerHTML ='<iframe src="' + domain + '?podcast_id=12559710' + addition + '" id="modernplayeriframe" scrolling="yes" width="100%" height="230px" frameborder="0"></iframe>';

        jQuery("input:radio[name='ModernPlayer[textcolor]'][value='Dark']").prop('checked', true);
        //jQuery('input[name="ModernPlayer[backgroundcolor]"]').wpColorPicker('color',"#ffffff");
        pickr.setColor("#ffffff");
    }

        jQuery('.color-field').wpColorPicker({
//            change: function(){
//            }
        });

    const preview_btn = document.getElementById("previewButton");
    const restore_btn = document.getElementById("restoreDefaultsButton");

    const preview_btn_modern = document.getElementById("previewButtonModern");
    const restore_btn_modern = document.getElementById("restoreDefaultsButtonModern");

    if(preview_btn_modern){
        document.getElementById("previewButtonModern").addEventListener("click", generateModernPlayerHash);
    }

    if(restore_btn_modern){
        document.getElementById("restoreDefaultsButtonModern").addEventListener("click", restoreModernDefaultColors);
    }

    if (preview_btn) {
        document.getElementById("previewButton").addEventListener("click", generatePlayerHash);
    }
    if (restore_btn) {
        document.getElementById("restoreDefaultsButton").addEventListener("click", restoreDefaultColors);
    }

    jQuery('.color_preview').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).css({ 'background-color' : '#' + hex });
			jQuery(el).ColorPickerHide();
			var Id = jQuery(el).attr('id');
			Id = Id.replace(/_prev/, '');
			jQuery('#'+ Id  ).val( '#' + hex );
			UpdatePlayerPreview(Id, '#'+hex );
		},
		onBeforeShow: function () {
			jQuery(this).ColorPickerSetColor( rgb2hex( jQuery(this).css("background-color") ) );
		}
	})
	.bind('keyup', function(){
		jQuery(this).ColorPickerSetColor( rgb2hex( jQuery(this).css("background-color") ) );
	});
	
	jQuery('.color_field').bind('change', function () {
		var Id = jQuery(this).attr('id');
		jQuery('#'+ Id + '_prev'  ).css( { 'background-color' : jQuery(this).val() } );
		if( typeof(update_audio_player) != "undefined" ) // Update the 1 px out player...
			update_audio_player();
	});
	
	jQuery('.other_field').bind('change', function () {
		if( typeof(update_audio_player) != "undefined" ) // Update the 1 px out player...
			update_audio_player();
	});

});
//-->
</script>


<!-- special page styling goes here -->
<style type="text/css">
div.color_control input { display: inline; float: left; }
div.color_control div.color_picker { display: inline; float: left; margin-top: 3px; }
#player_preview { margin-bottom: 0px; height: 50px; margin-top: 8px;}
input#colorpicker-value-input {
	width: 60px;
	height: 16px;
	padding: 0;
	margin: 0;
	font-size: 12px;
	border-spacing: 0;
	border-width: 0;
}
table.html5formats {
	width: 600px;
	margin: 0;
	padding: 0;
}
table.html5formats tr {
	margin: 0;
	padding: 0;
}
table.html5formats tr th {
	font-weight: bold;
	border-bottom: 1px solid #000000;
	margin: 0;
	padding: 0 5px;
	width: 25%;
}
table.html5formats tr td {
	
	border-right: 1px solid #000000;
	border-bottom: 1px solid #000000;
	margin: 0;
	padding: 0 10px;
}
table.html5formats tr > td:first-child {
	border-left: 1px solid #000000;
}
</style>
<?php

    // If we have powerpress credentials, check if the account has been verified
    $GeneralSettings = get_option('powerpress_general');
    if (!empty($GeneralSettings['blubrry_program_keyword'])) {
        $program_keyword = $GeneralSettings['blubrry_program_keyword'];
    } else {
        $program_keyword = '';
    }
    $creds = get_option('powerpress_creds');
    powerpress_check_credentials($creds);
    wp_enqueue_script('powerpress-admin', powerpress_get_root_url() . 'js/admin.js', array(), POWERPRESS_VERSION );

	// mainly 2 pages, first page selects a player, second configures the player, if there are optiosn to configure for that player. If the user is on the second page,
	// a link should be provided to select a different player.
	if( $select_player )
	{
?>
<input type="hidden" name="action" value="powerpress-select-player" />
<h2 style="margin-bottom: 0;"><?php echo __('PowerPress Podcast Player', 'powerpress'); ?></h2>
<p style="margin-top: 0;"><?php echo __('Select the media player you would like to use.', 'powerpress'); ?></p>

<?php
		if( $type == 'video' ) // Video player
		{
			if( empty($General['video_player']) )
				$General['video_player'] = '';
?>
<input type="hidden" name="ep" value="1" />
<div class="pp-sidenav-tab active" style="display: block;width: auto;border-radius: 8px;">
<div class="player-options video-player-options">
	<div>
		<div class="pp-settings-subsection">
            <div class="video-player-text">
            <?php if( $General['video_player'] == 'mediaelement-video' ) { ?>
                <input type="hidden" name="VideoPlayer[video_player]" id="player_mediaelement_video" value="mediaelement-video" class="player-type-input" />
            <?php } else { ?>
                <input type="hidden" name="VideoPlayer[video_player]" id="player_mediaelement_video" value="mediaelement-video" class="player-type-input" disabled />

            <?php } ?>
            <h3><?php echo __('MediaElement.js Media Player (default)', 'powerpress'); ?></h3>
            <div>
                <?php powerpressplayer_mediaelement_info(); ?>
            </div>
            </div>
            <div class="video-player-sample">
            <?php if( $General['video_player'] == 'mediaelement-video' ) { ?>
                <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_videoplayer.php&amp;ep=1"); ?>" id="activate_mediaelement_video" class="activate-player activated"><?php echo __('Customize', 'powerpress'); ?></a>
            <?php } else { ?>
                <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_videoplayer.php&amp;ep=1"); ?>" id="activate_mediaelement_video" class="activate-player"><?php echo __('Activate and Customize Now', 'powerpress'); ?></a>
            <?php } ?>
			<div>
				<div class="powerpressadmin-mejs-video">
                <?php echo powerpressplayer_build_mediaelementvideo( $Video['mediaelement-video'] ); ?>
				</div>
			</div>
            </div>
		</div>

        <div class="pp-settings-subsection">
            <div class="video-player-text html5" style="">
                <?php if( $General['video_player'] == 'html5video' ) { ?>
                    <input type="hidden" name="VideoPlayer[video_player]" id="player_html5video" value="html5video" class="player-type-input" />
                <?php } else { ?>
                    <input type="hidden" name="VideoPlayer[video_player]" id="player_html5video" value="html5video" class="player-type-input" disabled />
                <?php } ?>
                <h3><?php echo __('HTML5 Video Player', 'powerpress'); ?>  </h3>
                <p><?php echo __('HTML5 Video is an element introduced in the latest HTML specification (HTML5) for the purpose of playing videos.', 'powerpress'); ?></p>

            </div>
            <div class="video-player-sample" style="min-width: 400px;">
                <?php if( $General['video_player'] == 'html5video' ) { ?>
                    <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_videoplayer.php&amp;ep=1"); ?>" id="activate_html5video" class="activate-player activated"><?php echo __('Customize', 'powerpress'); ?></a>
                <?php } else { ?>
                    <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_videoplayer.php&amp;ep=1"); ?>" id="activate_html5video" class="activate-player"><?php echo __('Activate and Configure Now', 'powerpress'); ?></a>
                <?php } ?>
                <div style="float: right;"><?php echo powerpressplayer_build_html5video($Video['html5video']); ?></div>
            </div>
        </div>
        <div class="pp-settings-subsection-no-border">
            <!-- videojs-html5-video-player-for-wordpress -->
            <div class="video-player-text">
                <?php if( $General['video_player'] == 'videojs-html5-video-player-for-wordpress' ) { ?>
                    <input type="hidden" name="VideoPlayer[video_player]" id="player_videojs_html5_video_player_for_wordpress" value="videojs-html5-video-player-for-wordpress" class="player-type-input" />
                <?php } else { ?>
                    <input type="hidden" name="VideoPlayer[video_player]" id="player_videojs_html5_video_player_for_wordpress" value="videojs-html5-video-player-for-wordpress" class="player-type-input" disabled />
                <?php } ?>
                <h3><?php echo __('VideoJS', 'powerpress'); ?></h3>
                <?php powerpressplayer_videojs_info(); ?>
            </div>
            <div class="video-player-sample">
                <?php if ( function_exists('add_videojs_header') ) { ?>
                    <?php if( $General['video_player'] == 'videojs-html5-video-player-for-wordpress' ) { ?>
                        <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_videoplayer.php&amp;ep=1"); ?>" id="activate_videojs_html5_video_player_for_wordpress" class="activate-player activated"><?php echo __('Customize', 'powerpress'); ?></a>
                    <?php } else { ?>
                        <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_videoplayer.php&amp;ep=1"); ?>" id="activate_videojs_html5_video_player_for_wordpress" class="activate-player"><?php echo __('Activate and Configure Now', 'powerpress'); ?></a>
                    <?php } ?>
                <?php } ?>
                <p>
            <?php
            if ( function_exists('add_videojs_header') ) {
                echo powerpressplayer_build_videojs( $Video['videojs-html5-video-player-for-wordpress'] );
            }
            ?>
                </p>
            </div>
        </div>
	</div>

</div>
</div>
<?php
		}
		else // audio player
		{
?>
<input type="hidden" name="ep" value="1" />
<div class="pp-sidenav-tab active" style="display: block;width: auto;border-radius: 8px;">
<div class="player-options">
	<div>
        <div class="pp-settings-subsection">
            <?php if( $General['player'] == 'blubrrymodern' ) { ?>
                <input type="hidden" name="Player[player]" id="player_blubrrymodern" value="blubrrymodern" class="player-type-input" />
                <h3><?php echo __('Blubrry Player (Modern)', 'powerpress'); ?>
                <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_player.php&ep=1'); ?>" id="activate_blubrrymodern" class="activate-player activated" style="float: right;"><?php echo __('Customize', 'powerpress'); ?></a></h3>
		    <?php } else { ?>
                <input type="hidden" name="Player[player]" id="player_blubrrymodern" value="blubrrymodern" class="player-type-input" disabled />
                <h3><?php echo __('Blubrry Player (Modern)', 'powerpress'); ?>
                <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_player.php&ep=1'); ?>" id="activate_blubrrymodern" class="activate-player" style="float: right;"><?php echo __('Activate and Customize', 'powerpress'); ?></a></h3>
            <?php } ?>
            <div style="width: 90%;">
			<?php print_modern_player_demo(); ?>
            </div>
            <?php if( $General['player'] == 'blubrrymodern' && !empty($program_keyword)) { ?>
                <script>
                    jQuery(document).ready(function() {
                        jQuery(window).keydown(function(event){
                            if(event.keyCode == 13) {
                                event.preventDefault();
                                return false;
                            }
                        });
                    });

                    function copyText(textElem) {
                        textElem.select();
                        document.execCommand("copy");
                    }

                    function getiFrameSrcAndHeight(epInput, scroll) {
                        epInput.value = parseInt(epInput.value);

                        if (epInput.value > 50) {
                            epInput.value = 50;
                        }

                        let calcPlayerHeight = 230 + 40 * (epInput.value - 1) + 5 * epInput.value;
                        let newPlayerHeight = epInput.value >= 5 && scroll ? 530 : calcPlayerHeight;
                        let newPlayerURL = "<?php echo "https://player.blubrry.com/" . $program_keyword;?>/playlist/?episodes=" + epInput.value;

                        return [newPlayerURL, newPlayerHeight];
                    }

                    function updatePlaylistPlayeriFrame() {
                        let scroll = document.getElementById('scrollCheck').checked;
                        let newiFrameInfo = getiFrameSrcAndHeight(document.getElementById('num-episodes'), scroll);

                        let url = newiFrameInfo[0];
                        let height = newiFrameInfo[1];

                        if (scroll) {
                            url += '&scroll=1'
                        }

                        let newPlayeriFrame = '<iframe src="' + url + '" title="Blubrry Podcast Player" scrolling="no" width="100%" height="' + height  + '" frameborder="0"></iframe>';
                        document.getElementById('link-to-embed').value = newPlayeriFrame;
                        document.getElementById('player').height = height;
                        document.getElementById('player').src = url;
                    }
                </script>
                <div class="player-customize-container" style=" margin-top: 15px;">
                    <div class="customize-box-section" style="width: 100%;">
                        <h3><?php echo __('Latest Episode Player', 'powerpress'); ?></h3>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px;">
                            <h4 style="font-weight: bold; width: 10%;">Embed Code</h4>
                            <div style="width: 90%;">
                                <?php
                                $playerUrlLatest = "https://player.blubrry.com" . "/$program_keyword/latest/";
                                $playerHeightLatest = "230px;";
                                ?>
                                <input type="text" onclick="copyText(this)"
                                       id="link-to-embed-latest"
                                       class="form-control"
                                       title="Copy to clipboard"
                                       value='<iframe src="<?php echo $playerUrlLatest; ?>" title="Blubrry Podcast Player" scrolling="no" width="100%" height="<?php echo $playerHeightLatest; ?>" frameborder="0"></iframe>'
                                       readonly/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="player-customize-container" style=" margin-top: 50px;">
                    <div class="customize-box-section" style="width: 100%;">
                        <h3><?php echo __('Player with Playlist', 'powerpress'); ?></h3>
                        <p>
                            Set the number of episodes you want to feature in your playlist. Copy and paste the embed code to place your playlist on your website.
                        </p>
                        <p>
                            <span style="font-weight: bold;">NOTE:</span> The below settings do not save. Once they are set the iframe automatically updates for you to copy and paste into your post.
                        </p>
                        <div style="display: flex; align-items: center; margin-top: 25px;">
                            <h4 style="font-weight: bold; margin-right: 15px;">Do you want a scroll bar?</h4>
                            <input type="checkbox" value="" id="scrollCheck" onchange="updatePlaylistPlayeriFrame()">
                            <label for="scrollCheck" style="color: black;">
                                <strong>Yes</strong>
                            </label>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px;">
                            <div style="display: flex; align-items: center; flex-direction: row; width: 40%;">
                                <h4 style="font-weight: bold; margin-right: 15px;">Number of Episodes</h4>
                                <input onchange="updatePlaylistPlayeriFrame()" class="form-control" style="width: 60px; margin-right: 15px;" id="num-episodes" name="num-episodes" type="number" value="5">
                                <p style="margin: 0; color: #9F6E00 !important; font-weight: bold;">Max 50</p>
                            </div>
                            <div style="display: flex; align-items: center; flex-direction: row; width: 60%;">
                                <h4 style="font-weight: bold; width: 10%;">Embed Code</h4>
                                <div style="width: 90%;">
                                    <?php
                                    $playerUrl = "https://player.blubrry.com" . "/$program_keyword/playlist/?episodes=5";
                                    $playerHeight = "415;";
                                    ?>
                                    <input type="text" onclick="copyText(this)"
                                           id="link-to-embed"
                                           class="form-control"
                                           title="Copy to clipboard"
                                           value='<iframe src="<?php echo $playerUrl; ?>" title="Blubrry Podcast Player" scrolling="no" width="100%" height="<?php echo $playerHeight; ?>" frameborder="0"></iframe>'
                                           readonly/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
		</div>

        <?php if( $General['player'] != 'blubrrymodern' ) { ?>
		<div class="pp-settings-subsection">
            <?php if( $General['player'] == 'blubrryaudio' ) { ?>
                <input type="hidden" name="Player[player]" id="player_blubrryaudio" value="blubrryaudio" class="player-type-input" />
                <h3> <?php echo __('Blubrry Player (Legacy)', 'powerpress'); ?>
                <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_player.php&ep=1'); ?>" id="activate_blubrryaudio" class="activate-player activated" style="float: right;"><?php echo __('Customize', 'powerpress'); ?></a></h3>
            <?php } else { ?>
                <input type="hidden" name="Player[player]" id="player_blubrryaudio" value="blubrryaudio" class="player-type-input" disabled />
                <h3> <?php echo __('Blubrry Player (Legacy)', 'powerpress'); ?>
                <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_player.php&ep=1'); ?>" id="activate_blubrryaudio" class="activate-player" style="float: right;"><?php echo __('Activate and Customize', 'powerpress'); ?></a></h3>
            <?php } ?>

            <div style="width: 90%;">
			<?php print_blubrry_player_demo(); ?>
            </div>
		</div>
        <?php } ?>

		<div class="pp-settings-subsection">
            <?php if( $General['player'] == 'mediaelement-audio' ) { ?>
                <input type="hidden" name="Player[player]" id="player_mediaelement_audio" value="mediaelement-audio" class="player-type-input" />
                <h3><?php echo __('MediaElement.js Media Player (default)', 'powerpress'); ?>
                <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_player.php&ep=1'); ?>" id="activate_mediaelement_audio" class="activate-player activated" style="float: right;"><?php echo __('Customize', 'powerpress'); ?></a></h3>
            <?php } else { ?>
                <input type="hidden" name="Player[player]" id="player_mediaelement_audio" value="mediaelement-audio" class="player-type-input" disabled />
                <h3><?php echo __('MediaElement.js Media Player (default)', 'powerpress'); ?>
                <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_player.php&ep=1'); ?>" id="activate_mediaelement_audio" class="activate-player" style="float: right;"><?php echo __('Activate', 'powerpress'); ?></a></h3>
            <?php } ?>
            <?php powerpressplayer_mediaelement_info(); ?>
			<p>
            <div style="width: 90%;">
            <?php echo powerpressplayer_build_mediaelementaudio( $Audio['mediaelement-audio'] ); ?>
            </div>
			</p>
		</div>
		
		<div class="pp-settings-subsection-no-border">
		    <?php if( $General['player'] == 'html5audio' ) { ?>
                <input type="hidden" name="Player[player]" id="player_html5audio" value="html5audio" class="player-type-input" />
                <h3><?php echo __('HTML5 Audio Player', 'powerpress'); ?>
                <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_player.php&ep=1'); ?>" id="activate_html5audio" class="activate-player activated" style="float: right;"><?php echo __('Customize', 'powerpress'); ?></a></h3>
            <?php } else { ?>
                <input type="hidden" name="Player[player]" id="player_html5audio" value="html5audio" class="player-type-input" disabled />
                <h3><?php echo __('HTML5 Audio Player', 'powerpress'); ?>
                <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_player.php&ep=1'); ?>" id="activate_html5audio" class="activate-player" style="float: right;"><?php echo __('Activate', 'powerpress'); ?></a></h3>
            <?php } ?>
            <p>
                <?php echo __('HTML5 audio is an element introduced in the latest HTML specification (HTML5) for the purpose of playing audio.', 'powerpress'); ?>
            </p>
			<p>
            <div style="width: 90%;">
			<?php echo powerpressplayer_build_html5audio($Audio['html5audio']); ?>
            </div>
			</p>
		</div>
	</div>

</div>
</div>
<?php
		}
?>
<?php
	}
	else
	{
?>
<input type="hidden" name="ep" value="1" />
<h2><?php echo __('Configure Player', 'powerpress'); ?></h2>
<?php if( $type == 'audio' ) { ?>
<p style="margin-bottom: 20px;"><strong>&#8592;  <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_player.php&amp;sp=1"); ?>"><?php echo __('Select a different audio player', 'powerpress'); ?></a></strong></p>
<?php } else if( $type == 'video' ) { ?>
<p style="margin-bottom: 20px;"><strong>&#8592;  <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_videoplayer.php&amp;sp=1"); ?>"><?php echo __('Select a different video player', 'powerpress'); ?></a></strong></p>
<?php } else { ?>
<p style="margin-bottom: 20px;"><strong>&#8592;  <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_mobileplayer.php&amp;sp=1"); ?>"><?php echo __('Select a different mobile player', 'powerpress'); ?></a></strong></p>
<?php } ?>
        <div class="player-options pp-sidenav-tab active" style="display: block;width: auto;border-radius: 8px;">
     <?php
	 // Start adding logic here to display options based on the player selected...
	 if($type == 'audio'){
		if(empty($General['player'])){
            $General['player'] = '';
        }

		switch ($General['player']){
            case 'blubrrymodern': {
                if(empty($modernPlayerSettings['textcolor'])){
                    $modernPlayerSettings['textcolor'] = 'Dark';
                }
                if(empty($modernPlayerSettings['backgroundcolor'])){
                    $modernPlayerSettings['backgroundcolor'] = '#ffffff';
                }
                if(empty($modernPlayerSettings['transcriptspacing'])){
                    $modernPlayerSettings['transcriptspacing'] = '230px';
                }
                ?>

                <div id="tab_play">
                    <div class="pp-settings-subsection" style="padding-top: 0;">
                        <h3><b> <?php echo __('Blubrry Player (Modern) Settings', 'powerpress'); ?> </b></h3>

                        <p style="margin-bottom: 2em;"><?php echo __('The Blubrry Audio Player is only available to Blubrry Hosting Customers.', 'powerpress'); ?></p>

                        <div id="modern_player_iframe_div" style="width: 90%;"></div>
                    </div>
                    <div class="pp-settings-subsection-no-border">
                        <div>
                            <h3 style="margin-bottom: 1em;"><?php echo __('Customize Player', 'powerpress'); ?><a href="#" id="previewButtonModern" style="float: right; text-decoration: none;"><?php echo __('Preview Changes', 'powerpress'); ?></a></h3>
                            <h3 style="font-weight: normal; margin-bottom: 1em;">Don't forget to <a target="_blank" href="https://webaim.org/resources/contrastchecker/">check the contrast</a> of your color scheme to ensure it is accessible for all users.</h3>
                        </div>
                        <div class="player-customize-container" style="">
                            <div class="customize-box-section">
                                <h3><?php echo __('Player Contents', 'powerpress'); ?></h3>

                                <div style="display: flex; justify-content: space-between;">
                                    <h4 style="font-weight: bold;">Text Color</h4>
                                    <div style="margin-top: 1em; margin-bottom: 1.33em; width: 60%;">
                                        <input type="radio" name="ModernPlayer[textcolor]" id="selectlight" value="Light" <?php echo ($modernPlayerSettings['textcolor'] == 'Light') ? 'checked = true' : ''; ?>>
                                        <label for="selectlight" style="font-size:14px; margin-right: 40px;">Light</label>

                                        <input type="radio" name="ModernPlayer[textcolor]" id="selectdark" value="Dark" <?php echo ($modernPlayerSettings['textcolor'] == 'Dark') ? 'checked = true' : ''; ?> >
                                        <label for="selectdark" style="font-size:14px;">Dark (default)</label>
                                    </div>
                                </div>


                                <div class="color_control" style="display: flex; justify-content: space-between;">
                                    <h4 style="font-weight: bold;">Background Color</h4>
                                    <div style="display: block; padding-top: 0.75em; width: 60%;">
                                        <input type="text" style="" id="backgroundcolor" name="ModernPlayer[backgroundcolor]"
                                            class="new-color-field"
                                            value="<?php echo esc_attr($modernPlayerSettings['backgroundcolor']); ?>"
                                            maxlength="20"
                                            aria-label="Background Color"
                                        />
                                        <button type="button" id="backgroundcolorbutton" class="new-color-field-button" style="background-color: <?php echo esc_attr($modernPlayerSettings['backgroundcolor']); ?>"></button>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between;">
                                    <h4 style="font-weight: bold;">Player Spacing</h4>
                                    <div style="margin-top: 1em; margin-bottom: 1.33em; width: 60%; display: flex; flex-direction: column;">
                                        <div>
                                            <input type="radio" name="ModernPlayer[transcriptspacing]" id="selectnormal" value="230px" <?php echo ($modernPlayerSettings['transcriptspacing'] == '230px') ? 'checked = true' : ''; ?>>
                                            <label for="selectnormal" style="font-size:14px; margin-right: 40px;">Leave space for transcripts</label>
                                        </div>
                                        <div>
                                            <input type="radio" name="ModernPlayer[transcriptspacing]" id="selectless" value="185px" <?php echo ($modernPlayerSettings['transcriptspacing'] == '185px') ? 'checked = true' : ''; ?>>
                                            <label for="selectless" style="font-size:14px;">Remove spacing under player</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="customize-box-section">
                                <h3 style="margin-bottom: 1em;"><?php echo __('Episode Image', 'powerpress'); ?></h3>

                                <p style="display: block; margin-bottom: 1em;">
                                    <?php echo __('Select a specific category to each episode for statistics tracking and subscription links.', 'powerpress'); ?>
                                </p>

                                <p style="display: block; margin-bottom: 1em;"><input name="General[new_episode_box_itunes_image]" type="hidden" value="0"/><input
                                            name="General[new_episode_box_itunes_image]" type="checkbox"
                                            value="1" <?php echo((empty($General['new_episode_box_itunes_image']) || $General['new_episode_box_itunes_image'] == 1) ? 'checked' : ''); ?> /> <?php echo __('Display field for entering iTunes episode image ', 'powerpress'); ?>
                                </p>
                                <p style="display: block; margin-bottom: 1em;"><input name="General[bp_episode_image]" type="hidden" value="0"/><input
                                            name="General[bp_episode_image]" type="checkbox"
                                            value="1" <?php echo(!empty($General['bp_episode_image']) ? 'checked' : ''); ?> /> <?php echo __('Use iTunes episode image with player', 'powerpress'); ?>
                                </p>
                                <input type="hidden" name="General[powerpress_bplayer_settings]" value="1" />
                            </div>
                        </div>
                        <?php if (!empty($program_keyword)) { ?>
                        <script>
                            jQuery(document).ready(function() {
                                jQuery(window).keydown(function(event){
                                    if(event.keyCode == 13) {
                                        event.preventDefault();
                                        return false;
                                    }
                                });
                            });

                        </script>
                        <div class="player-customize-container" style=" margin-top: 50px;">
                            <div class="customize-box-section" style="width: 100%;">
                                <h3><?php echo __('Latest Episode Player', 'powerpress'); ?></h3>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px;">
                                    <h4 style="font-weight: bold; width: 10%;">Embed Code</h4>
                                    <div style="width: 90%;">
                                        <?php
                                        $playerUrlLatest = "https://player.blubrry.com" . "/$program_keyword/latest/";
                                        $playerHeightLatest = "230px;";
                                        ?>
                                        <input type="text" onclick="copyText(this)"
                                               id="link-to-embed-latest"
                                               class="form-control"
                                               title="Copy to clipboard"
                                               value='<iframe src="<?php echo $playerUrlLatest; ?>" title="Blubrry Podcast Player" scrolling="no" width="100%" height="<?php echo $playerHeightLatest; ?>" frameborder="0"></iframe>'
                                               readonly/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="player-customize-container" style=" margin-top: 50px;">
                            <div class="customize-box-section" style="width: 100%;">
                                <h3><?php echo __('Player with Playlist', 'powerpress'); ?></h3>
                                <p>
                                    Set the number of episodes you want to feature in your playlist. Copy and paste the embed code to place your playlist on your website.
                                </p>
                                <p>
                                    <span style="font-weight: bold;">NOTE:</span> The below settings do not save. Once they are set the iframe automatically updates for you to copy and paste into your post.
                                </p>
                                <div style="display: flex; align-items: center; margin-top: 25px;">
                                    <h4 style="font-weight: bold; margin-right: 15px;">Do you want a scroll bar?</h4>
                                    <input type="checkbox" value="" id="scrollCheck" onchange="updatePlaylistPlayeriFrame()">
                                    <label for="scrollCheck" style="color: black;">
                                        <strong>Yes</strong>
                                    </label>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px;">
                                    <div style="display: flex; align-items: center; flex-direction: row; width: 40%;">
                                        <h4 style="font-weight: bold; margin-right: 15px;">Number of Episodes</h4>
                                        <input onchange="updatePlaylistPlayeriFrame()" class="form-control" style="width: 60px; margin-right: 15px;" id="num-episodes" name="num-episodes" type="number" value="5">
                                        <p style="margin: 0; color: #9F6E00 !important; font-weight: bold;">Max 50</p>
                                    </div>
                                    <div style="display: flex; align-items: center; flex-direction: row; width: 60%;">
                                        <h4 style="font-weight: bold; width: 10%;">Embed Code</h4>
                                        <div style="width: 90%;">
                                            <?php
                                            $playerUrl = "https://player.blubrry.com" . "/$program_keyword/playlist/?episodes=5";
                                            $playerHeight = "415;";
                                            ?>
                                            <input type="text" onclick="copyText(this)"
                                                   id="link-to-embed"
                                                   class="form-control"
                                                   title="Copy to clipboard"
                                                   value='<iframe src="<?php echo $playerUrl; ?>" title="Blubrry Podcast Player" scrolling="no" width="100%" height="<?php echo $playerHeight; ?>" frameborder="0"></iframe>'
                                                   readonly/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="player-customize-container" style=" margin-top: 50px;">
                            <div class="customize-box-section" style="width: 100%;">
                                <h3 style="margin-bottom: 25px;"><?php echo __('Playlist Player Preview', 'powerpress'); ?></h3>
                                <iframe id="player" src="<?php echo $playerUrl; ?>" title="Blubrry Podcast Player" scrolling="no" width="100%" height="<?php echo $playerHeight; ?>" frameborder="0"></iframe>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                    <div style="margin-left: 2em;">
                    <?php powerpress_settings_save_button(true, true); ?>
                    <h3 style="display: inline-block; vertical-align: top; margin-top: 1ch; margin-left: 1em;"> <a href="#" id="restoreDefaultsButtonModern" style="text-decoration: none;"><?php echo __('Restore Defaults', 'powerpress'); ?></a></h3>
                    </div>
                    <script>
                        function copyText(textElem) {
                            textElem.select();
                            document.execCommand("copy");
                        }

                        function getiFrameSrcAndHeight(epInput, scroll) {
                            epInput.value = parseInt(epInput.value);

                            if (epInput.value > 50) {
                                epInput.value = 50;
                            }

                            let calcPlayerHeight = 230 + 40 * (epInput.value - 1) + 5 * epInput.value;
                            let newPlayerHeight = epInput.value >= 5 && scroll ? 530 : calcPlayerHeight;
                            let newPlayerURL = "<?php echo "https://player.blubrry.com/" . $program_keyword;?>/playlist/?episodes=" + epInput.value;

                            return [newPlayerURL, newPlayerHeight];
                        }

                        function updatePlaylistPlayeriFrame() {
                            let scroll = document.getElementById('scrollCheck').checked;
                            let newiFrameInfo = getiFrameSrcAndHeight(document.getElementById('num-episodes'), scroll);

                            let url = newiFrameInfo[0];
                            let height = newiFrameInfo[1];

                            if (scroll) {
                                url += '&scroll=1'
                            }

                            let newPlayeriFrame = '<iframe src="' + url + '" title="Blubrry Podcast Player" scrolling="no" width="100%" height="' + height  + '" frameborder="0"></iframe>';
                            document.getElementById('link-to-embed').value = newPlayeriFrame;
                            document.getElementById('player').height = height;
                            document.getElementById('player').src = url;
                        }
                    </script>
                </div>
                <input type="hidden" name="action" value="powerpress_bplayer"/>

            <?php } break;

            case 'html5audio': {
				$SupportUploads = powerpressadmin_support_uploads();
                ?>

                <p><?php echo __('Configure HTML5 Audio Player', 'powerpress'); ?></p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <?php echo __('Preview of Player', 'powerpress'); ?>
                        </th>
                        <td>
                            <p>
                <?php
                            echo powerpressplayer_build_html5audio( $Audio['html5audio'] );
                ?>
                            </p>
                        </td>
                    </tr>


                    <tr>
                    <th scope="row">
                    <?php echo __('Play Icon', 'powerpress'); ?></th>
                    <td>

                    <input type="text" id="audio_custom_play_button" name="General[audio_custom_play_button]" style="width: 60%;" value="<?php echo esc_attr($General['audio_custom_play_button']); ?>" maxlength="255" />
                    <a href="#" onclick="javascript: window.open( document.getElementById('audio_custom_play_button').value ); return false;"><?php echo __('preview', 'powerpress'); ?></a>

                    <p><?php echo __('Place the URL to the play icon above.', 'powerpress'); ?> <?php echo __('Example', 'powerpress'); ?>: http://example.com/images/audio_play_icon.jpg<br /><br />
                    <?php echo __('Leave blank to use default play icon image.', 'powerpress'); ?></p>

                    <?php if( $SupportUploads ) { ?>
                    <p><input name="audio_custom_play_button_checkbox" type="checkbox" onchange="powerpress_show_field('audio_custom_play_button_upload', this.checked)" value="1" /> <?php echo __('Upload new image', 'powerpress'); ?> </p>
                    <div style="display:none" id="audio_custom_play_button_upload">
                        <label for="audio_custom_play_button_file"><?php echo __('Choose file', 'powerpress'); ?>:</label><input type="file" name="audio_custom_play_button_file"  />
                    </div>
                    <?php } ?>
                    </td>
                    </tr>
                </table>

            <?php } break;

            case 'blubrryaudio' : {   //TODO
                $BBplayerSettings = powerpress_get_settings('powerpress_bplayer');
                if (empty($BBplayerSettings)) {
                    $BBplayerSettings = array(
                        'showbg' => '#444444',
                        'showtext' => '#ffffff',
                        'downloadbgcolor' => '#003366',
                        'downloadcolortext' => '#ffffff',
                        'subscribebg' => '#fb8c00',
                        'textsubscribe' => '#ffffff',
                        'bgshare' => '#1976d2',
                        'textshare' => '#ffffff',
                        'playerstyle' => 'light'
                    );
                }
                ?>

                <div id="tab_play" class="powerpress_tab bbplayer_settings" style="padding-left: 3%; padding-right: 3%">
                    <h2 style="font-size: 2em;"> <?php echo __('Blubrry Player', 'powerpress'); ?> </h2>
                    <p>
                        <?php echo __('Note: The Blubrry Audio Player is only available to Blubrry Hosting Customers.', 'powerpress'); ?>
                    </p>
                    <p>
                        <?php echo __('Shownotes and Download options are not displayed initially.', 'powerpress'); ?>
                    </p>
                    <div id="player_iframe_div"
                         style="border: 1px solid #000000; height: 138px; box-shadow: inset 0 0 10px black, 0 0 6px black; margin: 20px 0;">
                        <?php  //print_blubrry_player_demo(); ?>
                    </div>
                    <br>

                    <table class="form-table">
                        <tr>
                            <td>
                                <a href="#" id="previewButton"
                                   style="font-weight: bold; color: #1976d2; font-size: 12px"><?php echo __('Preview Changes', 'powerpress'); ?></a>

                            </td>
                            <td>
                                <a href="#" id="restoreDefaultsButton"
                                   style="font-weight: bold; color: #1976d2; font-size: 12px"><?php echo __('Restore Default Colors', 'powerpress'); ?></a>

                            </td>
                        </tr>
                        <h3 style="font-size: 2em; margin-bottom: 5px; color: #23282d; font-weight: 500"><?php echo __('Player Customization Settings', 'powerpress'); ?></h3>

                        <tr valign="top" style="margin-bottom: -15px;">
                            <th scope="row">
                                <h3>   <?php echo __('Buttons', 'powerpress'); ?> </h3>
                            </th>

                            <th scope="row">
                                <h3>  <?php echo __('Background Color', 'powerpress'); ?> </h3>
                            </th>

                            <th scope="row">
                                <h3>   <?php echo __('Font Color', 'powerpress'); ?> </h3>
                            </th>

                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php echo __('Shownotes/Embed Button', 'powerpress'); ?>
                            </th>
                            <td>

                                <div class="color_control">
                                    <input type="text" style="width: 100px;" id="shownotesbg" name="BBPlayer[showbg]"
                                           class="color-field"
                                           value="<?php echo esc_attr($BBplayerSettings['showbg']); ?>"
                                           maxlength="20"/>
                                </div>
                            </td>
                            <td>
                                <div class="color_control">
                                    <input type="text" style="width: 100px;" id="showtext"
                                           name="BBPlayer[showtext]" class="color-field"
                                           value="<?php echo esc_attr($BBplayerSettings['showtext']); ?>"
                                           maxlength="20"/>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php echo __('Download Button', 'powerpress'); ?>
                            </th>
                            <td>
                                <div class="color_control">
                                    <input type="text" style="width: 100px;" id="downloadbgcolor"
                                           name="BBPlayer[downloadbgcolor]"
                                           class="color-field"
                                           value="<?php echo esc_attr($BBplayerSettings['downloadbgcolor']); ?>"
                                           maxlength="20"/>
                                </div>
                            </td>
                            <td>
                                <div class="color_control">
                                    <input type="text" style="width: 100px;" id="downloadcolortext"
                                           name="BBPlayer[downloadcolortext]"
                                           class="color-field"
                                           value="<?php echo esc_attr($BBplayerSettings['downloadcolortext']); ?>"
                                           maxlength="20"/>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php echo __('Subscribe Button', 'powerpress'); ?>
                            </th>
                            <td>
                                <div class="color_control">
                                    <input type="text" style="width: 100px;" id="subscribebg" name="BBPlayer[subscribebg]"
                                           class="color-field"
                                           value="<?php echo esc_attr($BBplayerSettings['subscribebg']); ?>"
                                           maxlength="20"/>
                                </div>
                            </td>
                            <td>
                                <div class="color_control">
                                    <input type="text" style="width: 100px;" id="textsubscribe"
                                           name="BBPlayer[textsubscribe]" class="color-field"
                                           value="<?php echo esc_attr($BBplayerSettings['textsubscribe']); ?>"
                                           maxlength="20"/>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php echo __('Share Button', 'powerpress'); ?>
                            </th>
                            <td>
                                <div class="color_control">
                                    <input type="text" style="width: 100px;" id="bgshare" name="BBPlayer[bgshare]"
                                           class="color-field"
                                           value="<?php echo esc_attr($BBplayerSettings['bgshare']); ?>"
                                           maxlength="20"/>
                                </div>
                            </td>
                            <td>
                                <div class="color_control">
                                    <input type="text" style="width: 100px;" id="textshare" name="BBPlayer[textshare]"
                                           class="color-field"
                                           value="<?php echo esc_attr($BBplayerSettings['textshare']); ?>"
                                           maxlength="20"/>
                                </div>
                            </td>

                        </tr>

                        <tr valign="top">
                            <br>

                            <th scope="row">
                                <h3><?php echo __('Player Style', 'powerpress'); ?> </h3>
                            </th>
                            <td>
                                <input type="radio" name="BBPlayer[playerstyle]" id="selectlight"
                                       value="light" <?php echo ($BBplayerSettings['playerstyle'] == 'light') ? 'checked = true' : ''; ?>>
                                <label for="selectlight" style="font-size:14px;">Light (default)</label>
                            </td>
                            <td>
                                <input type="radio" name="BBPlayer[playerstyle]" id="selectdark"
                                       value="dark" <?php echo ($BBplayerSettings['playerstyle'] == 'dark') ? 'checked = true' : ''; ?> >
                                <label for="selectdark" style="font-size:14px;">Dark</label>
                            </td>
                        </tr>

                        <!-- <p><input name="General[itunes_image_audio]" type="hidden" value="0"/><input name="General[itunes_image_audio]" type="checkbox" value="1" <?php echo(!empty($General['itunes_image_audio']) ? 'checked' : ''); ?> /> <?php echo __('Use episode iTunes image if set', 'powerpress'); ?> </p> -->
                    </table>
                    <h3><?php echo __('Episode Image', 'powerpress'); ?></h3>

                    <p><input name="General[new_episode_box_itunes_image]" type="hidden" value="0"/><input
                                name="General[new_episode_box_itunes_image]" type="checkbox"
                                value="1" <?php echo((empty($General['new_episode_box_itunes_image']) || $General['new_episode_box_itunes_image'] == 1) ? 'checked' : ''); ?> /> <?php echo __('Display field for entering iTunes episode image ', 'powerpress'); ?>
                    </p>
                    <p><input name="General[bp_episode_image]" type="hidden" value="0"/><input
                                name="General[bp_episode_image]" type="checkbox"
                                value="1" <?php echo(!empty($General['bp_episode_image']) ? 'checked' : ''); ?> /> <?php echo __('Use iTunes episode image with player', 'powerpress'); ?>
                    </p>
                    <input type="hidden" name="General[powerpress_bplayer_settings]" value="1" />
                </div>
                <input type="hidden" name="action" value="powerpress_bplayer"/>
            <?php } break;

		    case 'mediaelement-audio': {
				$SupportUploads = powerpressadmin_support_uploads();

				if(!isset($General['audio_player_max_width'])){
                    $General['audio_player_max_width'] = '';
                } ?>
                <p><?php echo __('Configure MediaElement.js Audio Player', 'powerpress'); ?></p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <?php echo __('Preview of Player', 'powerpress'); ?>
                        </th>
                        <td><p>
                        <?php
                        // TODO
                            echo powerpressplayer_build_mediaelementaudio($Audio['mediaelement-audio']);
                        ?>
                            </p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <?php echo __('Max Width', 'powerpress'); ?>
                        </th>
                        <td valign="top">
                                <input type="text" style="width: 50px;" id="audio_player_max_width" name="General[audio_player_max_width]" class="player-width" value="<?php echo esc_attr($General['audio_player_max_width']); ?>" maxlength="4" />
                            <?php echo __('Width of Audio mp3 player (leave blank for max width)', 'powerpress'); ?>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            &nbsp;
                        </th>
                        <td>
                            <p><?php echo __('MediaElement.js Player has no additional settings at this time.', 'powerpress'); ?></p>
                        </td>
                    </tr>
                </table>

            <?php } break;

			// TODO:
			default: {
				if(empty($General['player_width_audio'])){
                    $General['player_width_audio'] = '';
                }
                ?>

                <h2><?php echo __('General Settings', 'powerpress'); ?></h2>
                <table class="form-table">
                    <tr valign="top">
                    <th scope="row">
                        <?php echo __('Width', 'powerpress'); ?>
                    </th>
                    <td valign="top">
                            <input type="text" style="width: 50px;" id="player_width" name="General[player_width_audio]" class="player-width" value="<?php echo esc_attr($General['player_width_audio']); ?>" maxlength="4" />
                        <?php echo __('Width of Audio mp3 player (leave blank for 320 default)', 'powerpress'); ?>
                    </td>
                </tr>
            </table>
            <?php } break;
		}
	 }
	 else if( $type == 'video' )
	 {
			$player_to_configure = (!empty($General['video_player'])?$General['video_player']:'');
			switch( $player_to_configure )
			{
				case 'html5':
				case 'html5video': {
				
					echo '<p>'. __('Configure HTML5 Video Player', 'powerpress') . '</p>'; 
					?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php echo __('Preview of Player', 'powerpress'); ?> 
		</th>
		<td>
		<?php
			if( $type == 'mobile' )
			{
				echo '<p>' . __('Audio:', 'powerpress') .' ';
				echo powerpressplayer_build_html5audio( $Audio['html5audio'] );
				echo '</p>';
			}
		?>
			<p>
<?php
				if( $type == 'mobile' )
					echo  __('Video:', 'powerpress') .' ';
				echo powerpressplayer_build_html5video( $Video['html5video'] );
?>
			</p>
		</td>
	</tr>
</table>

					<?php
				}; break;
				case 'videojs-html5-video-player-for-wordpress': {
					?>
					<p><?php echo __('Configure VideoJS', 'powerpress'); ?></p>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php echo __('Preview of Player', 'powerpress'); ?> 
		</th>
		<td>
			<p>
<?php
				echo powerpressplayer_build_videojs( $Video['videojs-html5-video-player-for-wordpress'] );
?>
			</p>
		</td>
	</tr>
</table>
<h3><?php echo __('VideoJS Settings', 'powerpress'); ?></h3>
<table class="form-table">
<tr valign="top">
<th scope="row">
<?php echo __('VideoJS CSS Class', 'powerpress'); ?>
</th>
<td>
<p>
<input type="text" name="General[videojs_css_class]" style="width: 150px;" value="<?php echo ( empty($General['videojs_css_class']) ?'':esc_attr($General['videojs_css_class']) ); ?>" /> 
<?php echo __('Apply specific CSS styling to your Video JS player.', 'powerpress'); ?>
</p>
</td>
</tr>
</table>
					<?php
				}; break;
				case 'mejs': // $player_to_configure
				case 'mediaelement-video':
				default: {
					?>
					<p><?php echo __('Configure MediaElement.js Player', 'powerpress'); ?></p>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php echo __('Preview of Player', 'powerpress'); ?> 
		</th>
		<td>
			<p>
			<?php
			if( $type == 'mobile' )
			{
				echo '<p>' . __('Audio:', 'powerpress') .' ';
				echo powerpressplayer_build_mediaelementaudio( $Audio['mediaelement-audio'] );
				echo '</p>';
			}
					?>
			</p>
			<div style="max-width: 70%;">
				<div class="powerpressadmin-mejs-video">
<?php
				if( $type == 'mobile' )
					echo  __('Video:', 'powerpress') .' ';
				echo powerpressplayer_build_mediaelementvideo( $Video['mediaelement-video'] );
?>
				</div>
			</div>
		</td>
	</tr>
</table>

					<?php
				}; break;
			}
			
			if( !isset($General['poster_play_image']) )
				$General['poster_play_image'] = 1;
			if( !isset($General['poster_image_audio']) )
				$General['poster_image_audio'] = 0;
			if( !isset($General['player_width']) )
				$General['player_width'] = '';
			if( !isset($General['player_height']) )
				$General['player_height'] = '';
			if( !isset($General['poster_image']) )
				$General['poster_image'] = '';
			if( !isset($General['video_player_max_width']) )
				$General['video_player_max_width'] = '';
			if( !isset($General['video_player_max_height']) )
				$General['video_player_max_height'] = '';
			
			if( !isset($General['video_custom_play_button']) )
				$General['video_custom_play_button'] = '';

?>
<!-- Global Video Player settings (Appy to all video players -->
<input type="hidden" name="action" value="powerpress-save-videocommon" />
<h3><?php echo __('Common Video Settings', 'powerpress'); ?></h3>

<p><?php echo __('The following video settings apply to the video player above as well as to classic video &lt;embed&gt; formats such as Microsoft Windows Media (.wmv), QuickTime (.mov) and RealPlayer.', 'powerpress'); ?></p>
<table class="form-table">
<?php
	if( $player_to_configure == 'mediaelement-video' || $player_to_configure == 'mejs' ) 
	{
?>
<tr valign="top">
<th scope="row">
<?php echo __('Player Width', 'powerpress'); ?>
</th>
<td>
<input type="text" name="General[player_width]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9%]/g, '');" value="<?php echo esc_attr($General['player_width']); ?>" maxlength="4" />
<?php echo __('Width of player (leave blank for default width)', 'powerpress'); ?>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __('Player Height', 'powerpress'); ?>
</th>
<td>
<input type="text" name="General[player_height]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9%]/g, '');" value="<?php echo esc_attr($General['player_height']); ?>" maxlength="4" />
<?php echo __('Height of player (leave blank for default height)', 'powerpress'); ?>
</td>
</tr>
<?php
	}
	else
	{
?>
<tr valign="top">
<th scope="row">
<?php echo __('Player Width', 'powerpress'); ?>
</th>
<td>
<input type="text" name="General[player_width]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9%]/g, '');" value="<?php echo esc_attr($General['player_width']); ?>" maxlength="4" />
<?php echo __('Width of player (leave blank for 400 default)', 'powerpress'); ?>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __('Player Height', 'powerpress'); ?>
</th>
<td>
<input type="text" name="General[player_height]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9%]/g, '');" value="<?php echo esc_attr($General['player_height']); ?>" maxlength="4" />
<?php echo __('Height of player (leave blank for 225 default)', 'powerpress'); ?>
</td>
</tr>
<?php
	}
		$SupportUploads = powerpressadmin_support_uploads();
		
?>
<tr>
<th scope="row">
<?php echo __('Default Poster Image', 'powerpress'); ?></th>
<td>

<input type="text" id="poster_image" name="General[poster_image]" style="width: 60%;" value="<?php echo esc_attr($General['poster_image']); ?>" maxlength="255" />
<a href="#" onclick="javascript: window.open( document.getElementById('poster_image').value ); return false;"><?php echo __('preview', 'powerpress'); ?></a>

<p><?php echo __('Place the URL to the poster image above.', 'powerpress'); ?> <?php echo __('Example', 'powerpress'); ?>: http://example.com/images/poster.jpg<br /><br />
<?php echo __('Image should be at minimum the same width/height as the player above. Leave blank to use default black background image.', 'powerpress'); ?></p>

<?php if( $SupportUploads ) { ?>
<p><input name="poster_image_checkbox" type="checkbox" onchange="powerpress_show_field('poster_image_upload', this.checked)" value="1" /> <?php echo __('Upload new image', 'powerpress'); ?> </p>
<div style="display:none" id="poster_image_upload">
	<label for="poster_image_file"><?php echo __('Choose file', 'powerpress'); ?>:</label><input type="file" name="poster_image_file"  />
</div>
<?php } ?>
<?php
		if( in_array($General['video_player'], array('html5video') ) )
		{
?>
<p><input name="General[poster_play_image]" type="checkbox" value="1" <?php echo ((!isset($General['poster_play_image']) || $General['poster_play_image'])?'checked':''); ?> /> <?php echo __('Include play icon over poster image when applicable', 'powerpress'); ?> </p>
	<?php if( $type == 'video'  ) { ?>
<p><input name="General[poster_image_audio]" type="checkbox" value="1" <?php echo ($General['poster_image_audio']?'checked':''); ?> /> <?php echo __('Use poster image, player width and height above for audio (Flow Player only)', 'powerpress'); ?> </p>
	<?php } ?>
<?php } ?>
</td>
</tr>

<?php
		// Play icon, only applicable to HTML5/FlowPlayerClassic
		if( in_array($General['video_player'], array('html5video') ) )
		{
?>
<tr>
<th scope="row">
<?php echo __('Video Play Icon', 'powerpress'); ?></th>
<td>

<input type="text" id="video_custom_play_button" name="General[video_custom_play_button]" style="width: 60%;" value="<?php echo esc_attr($General['video_custom_play_button']); ?>" maxlength="255" />
<a href="#" onclick="javascript: window.open( document.getElementById('video_custom_play_button').value ); return false;"><?php echo __('preview', 'powerpress'); ?></a>

<p><?php echo __('Place the URL to the play icon above.', 'powerpress'); ?> <?php echo __('Example', 'powerpress'); ?>: http://example.com/images/video_play_icon.jpg<br /><br />
<?php echo __('Image should 60 pixels by 60 pixels. Leave blank to use default play icon image.', 'powerpress'); ?></p>

<?php if( $SupportUploads ) { ?>
<p><input name="video_custom_play_button_checkbox" type="checkbox" onchange="powerpress_show_field('video_custom_play_button_upload', this.checked)" value="1" /> <?php echo __('Upload new image', 'powerpress'); ?> </p>
<div style="display:none" id="video_custom_play_button_upload">
	<label for="video_custom_play_button_file"><?php echo __('Choose file', 'powerpress'); ?>:</label><input type="file" name="video_custom_play_button_file"  />
</div>
<?php } ?>
</td>
</tr>
<?php
		}
?>
</table>
<?php
	 }
?>

<?php

        ?>
        </div>
            <?php
        if ($General['player'] != 'blubrrymodern' || $type == 'video' ) {
            powerpress_settings_save_button(true);
        }
	}
}

function print_blubrry_player_demo()
{
?>
		<p>
			<?php echo __('The Blubrry Audio Player is only available to Blubrry Hosting Customers.', 'powerpress'); ?>
		</p>
        <p style="font-weight: bold;">
            <?php echo __('The Legacy player will be discontinued on December 31, 2023.', 'powerpress'); ?>
        </p>
			<div style="border: 1px solid #000000; height: 138px; box-shadow: inset 0 0 10px black, 0 0 6px black; margin: 20px 0;">
			<?php
			echo powerpressplayer_build_blubrryaudio_by_id(12559710); // Special episode where we talk about the new player
			?></div>

<?php
}

function print_modern_player_demo(){ ?>
    <p><?php echo __('The Modern Blubrry Player is only available to Blubrry Hosting Customers.', 'powerpress'); ?></p>

    <div style="margin: 25px 0;">
        <?php echo powerpressplayer_build_blubrryaudio_by_id(80155910, true); // Special episode where we talk about the new player ?>
    </div>

<?php } ?>

<?php // eof ?>