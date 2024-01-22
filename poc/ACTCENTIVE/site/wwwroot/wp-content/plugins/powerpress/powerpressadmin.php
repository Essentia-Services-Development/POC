<?php

if( !function_exists('add_action') )
	die("access denied.");

add_action ('wp_loaded', 'redirect_to_settings');

function redirect_to_settings() {
    $General = powerpress_get_settings('powerpress_general');
    $hasChannels = isset($General['channels']) && $General['channels'] == 1;
    $hasCats = isset($General['cat_casting']) && $General['cat_casting'] == 1;
    $hasTax = isset($General['taxonomy_podcasting']) && $General['taxonomy_podcasting'] == 1;
    $hasPT = isset($General['posttype_podcasting']) && $General['posttype_podcasting'] == 1;

    if (!$hasChannels && !$hasCats && !$hasTax && !$hasPT && isset($_GET['page']) && $_GET['page'] == 'powerpress/powerpressadmin_live_item.php') {
        wp_redirect(admin_url() . "admin.php?page=powerpressadmin_basic&tab=live-item-tab");
    }
}

function powerpress_esc_html($escape)
{
	if( is_array($escape) )
	{
		foreach( $escape as $index => $value ) {
			$escape[ $index ] = powerpress_esc_html($value);
		}
	}
	return esc_html($escape);
}

function powerpress_login_create_nonce() {
    return wp_create_nonce( 'powerpress-link-blubrry' );
}

/**
 * Returns array of languages supported by RevAI
 * @return array
 */
function powerpress_revai_languages(){
    $languages = array();

    $languages['ar'] = 'Arabic';
    $languages['bg'] = 'Bulgarian';
    $languages['ca'] = 'Catalan';
    $languages['hr'] = 'Croatian';
    $languages['cs'] = 'Czech';
    $languages['da'] = 'Danish';
    $languages['nl'] = 'Dutch';
    $languages['en'] = 'English';
    $languages['fa'] = 'Farsi';
    $languages['fi'] = 'Finnish';
    $languages['fr'] = 'French';
    $languages['de'] = 'German';
    $languages['el'] = 'Greek';
    $languages['he'] = 'Hebrew';
    $languages['hi'] = 'Hindi';
    $languages['hu'] = 'Hungarian';
    $languages['id'] = 'Indonesian';
    $languages['it'] = 'Italian';
    $languages['ja'] = 'Japanese';
    $languages['ko'] = 'Korean';
    $languages['lt'] = 'Lithuanian';
    $languages['lv'] = 'Latvian';
    $languages['ms'] = 'Malay';
    $languages['cmn'] = 'Mandarin';
    $languages['no'] = 'Norwegian';
    $languages['pl'] = 'Polish';
    $languages['pt'] = 'Portuguese';
    $languages['ro'] = 'Romanian';
    $languages['ru'] = 'Russian';
    $languages['sk'] = 'Slovak';
    $languages['sl'] = 'Slovenian';
    $languages['es'] = 'Spanish';
    $languages['sv'] = 'Swedish';
    $languages['ta'] = 'Tamil';
    $languages['te'] = 'Telugu';
    $languages['tr'] = 'Turkish';

    return $languages;
}

function powerpress_languages()
{
    // List copied from PodPress:
    $langs = array();
    $langs['af'] = __('Afrikaans', 'powerpress');
    $langs['sq'] = __('Albanian', 'powerpress');
    $langs['ar'] = __('Arabic', 'powerpress');
    $langs['ar-sa'] = __('Arabic (Saudi Arabia)', 'powerpress');
    $langs['ar-eg'] = __('Arabic (Egypt)', 'powerpress');
    $langs['ar-dz'] = __('Arabic (Algeria)', 'powerpress');
    $langs['ar-tn'] = __('Arabic (Tunisia)', 'powerpress');
    $langs['ar-ye'] = __('Arabic (Yemen)', 'powerpress');
    $langs['ar-jo'] = __('Arabic (Jordan)', 'powerpress');
    $langs['ar-kw'] = __('Arabic (Kuwait)', 'powerpress');
    $langs['ar-bh'] = __('Arabic (Bahrain)', 'powerpress');
    $langs['eu'] = __('Basque', 'powerpress');
    $langs['be'] = __('Belarusian', 'powerpress');
    $langs['bg'] = __('Bulgarian', 'powerpress');
    $langs['ca'] = __('Catalan', 'powerpress');
    $langs['zh-cn'] = __('Chinese (Simplified)', 'powerpress');
    $langs['zh-tw'] = __('Chinese (Traditional)', 'powerpress');
    $langs['hr'] = __('Croatian', 'powerpress');
    $langs['cs'] = __('Czech', 'powerpress');
    $langs['cr'] = __('Cree', 'powerpress');
    $langs['da'] = __('Danish', 'powerpress');
    $langs['nl'] = __('Dutch', 'powerpress');
    $langs['nl-be'] = __('Dutch (Belgium)', 'powerpress');
    $langs['nl-nl'] = __('Dutch (Netherlands)', 'powerpress');
    $langs['en'] = __('English', 'powerpress');
    $langs['en-au'] = __('English (Australia)', 'powerpress');
    $langs['en-bz'] = __('English (Belize)', 'powerpress');
    $langs['en-ca'] = __('English (Canada)', 'powerpress');
    $langs['en-ie'] = __('English (Ireland)', 'powerpress');
    $langs['en-jm'] = __('English (Jamaica)', 'powerpress');
    $langs['en-nz'] = __('English (New Zealand)', 'powerpress');
    $langs['en-ph'] = __('English (Phillipines)', 'powerpress');
    $langs['en-za'] = __('English (South Africa)', 'powerpress');
    $langs['en-tt'] = __('English (Trinidad)', 'powerpress');
    $langs['en-gb'] = __('English (United Kingdom)', 'powerpress');
    $langs['en-us'] = __('English (United States)', 'powerpress');
    $langs['en-zw'] = __('English (Zimbabwe)', 'powerpress');
    $langs['et'] = __('Estonian', 'powerpress');
    $langs['fo'] = __('Faeroese', 'powerpress');
    $langs['fi'] = __('Finnish', 'powerpress');
    $langs['fr'] = __('French', 'powerpress');
    $langs['fr-be'] = __('French (Belgium)', 'powerpress');
    $langs['fr-ca'] = __('French (Canada)', 'powerpress');
    $langs['fr-fr'] = __('French (France)', 'powerpress');
    $langs['fr-lu'] = __('French (Luxembourg)', 'powerpress');
    $langs['fr-mc'] = __('French (Monaco)', 'powerpress');
    $langs['fr-ch'] = __('French (Switzerland)', 'powerpress');
    $langs['gl'] = __('Galician', 'powerpress');
    $langs['gd'] = __('Gaelic', 'powerpress');
    $langs['de'] = __('German', 'powerpress');
    $langs['de-at'] = __('German (Austria)', 'powerpress');
    $langs['de-de'] = __('German (Germany)', 'powerpress');
    $langs['de-li'] = __('German (Liechtenstein)', 'powerpress');
    $langs['de-lu'] = __('German (Luxembourg)', 'powerpress');
    $langs['de-ch'] = __('German (Switzerland)', 'powerpress');
    $langs['el'] = __('Greek', 'powerpress');
    $langs['haw'] = __('Hawaiian', 'powerpress');
    $langs['he_IL'] = __('Hebrew', 'powerpress');
    $langs['hu'] = __('Hungarian', 'powerpress');
    $langs['is'] = __('Icelandic', 'powerpress');
    $langs['in'] = __('Indonesian', 'powerpress');
    $langs['ga'] = __('Irish', 'powerpress');
    $langs['it'] = __('Italian', 'powerpress');
    $langs['hi'] = __('Hindi', 'powerpress');
    $langs['it-it'] = __('Italian (Italy)', 'powerpress');
    $langs['it-ch'] = __('Italian (Switzerland)', 'powerpress');
    $langs['ja'] = __('Japanese', 'powerpress');
    $langs['ko'] = __('Korean', 'powerpress');
    $langs['mk'] = __('Macedonian', 'powerpress');
    $langs['no'] = __('Norwegian', 'powerpress');
    $langs['pa'] = __('Punjabi', 'powerpress');
    $langs['pl'] = __('Polish', 'powerpress');
    $langs['pt'] = __('Portuguese', 'powerpress');
    $langs['pt-br'] = __('Portuguese (Brazil)', 'powerpress');
    $langs['pt-pt'] = __('Portuguese (Portugal)', 'powerpress');
    $langs['ro'] = __('Romanian', 'powerpress');
    $langs['ro-mo'] = __('Romanian (Moldova)', 'powerpress');
    $langs['ro-ro'] = __('Romanian (Romania)', 'powerpress');
    $langs['ru'] = __('Russian', 'powerpress');
    $langs['ru-mo'] = __('Russian (Moldova)', 'powerpress');
    $langs['ru-ru'] = __('Russian (Russia)', 'powerpress');
    $langs['sr'] = __('Serbian', 'powerpress');
    $langs['sk'] = __('Slovak', 'powerpress');
    $langs['sl'] = __('Slovenian', 'powerpress');
    $langs['es'] = __('Spanish', 'powerpress');
    $langs['es-ar'] = __('Spanish (Argentina)', 'powerpress');
    $langs['es-bo'] = __('Spanish (Bolivia)', 'powerpress');
    $langs['es-cl'] = __('Spanish (Chile)', 'powerpress');
    $langs['es-co'] = __('Spanish (Colombia)', 'powerpress');
    $langs['es-cr'] = __('Spanish (Costa Rica)', 'powerpress');
    $langs['es-do'] = __('Spanish (Dominican Republic)', 'powerpress');
    $langs['es-ec'] = __('Spanish (Ecuador)', 'powerpress');
    $langs['es-sv'] = __('Spanish (El Salvador)', 'powerpress');
    $langs['es-gt'] = __('Spanish (Guatemala)', 'powerpress');
    $langs['es-hn'] = __('Spanish (Honduras)', 'powerpress');
    $langs['es-mx'] = __('Spanish (Mexico)', 'powerpress');
    $langs['es-ni'] = __('Spanish (Nicaragua)', 'powerpress');
    $langs['es-pa'] = __('Spanish (Panama)', 'powerpress');
    $langs['es-py'] = __('Spanish (Paraguay)', 'powerpress');
    $langs['es-pe'] = __('Spanish (Peru)', 'powerpress');
    $langs['es-pr'] = __('Spanish (Puerto Rico)', 'powerpress');
    $langs['es-es'] = __('Spanish (Spain)', 'powerpress');
    $langs['es-uy'] = __('Spanish (Uruguay)', 'powerpress');
    $langs['es-ve'] = __('Spanish (Venezuela)', 'powerpress');
    $langs['sv'] = __('Swedish', 'powerpress');
    $langs['sv-fi'] = __('Swedish (Finland)', 'powerpress');
    $langs['sv-se'] = __('Swedish (Sweden)', 'powerpress');
    $langs['sw'] = __('Swahili', 'powerpress');
    $langs['ta'] = __('Tamil', 'powerpress');
    $langs['th'] = __('Thai', 'powerpress');
    $langs['bo'] = __('Tibetan', 'powerpress');
    $langs['tr'] = __('Turkish', 'powerpress');
    $langs['uk'] = __('Ukranian', 'powerpress');
    $langs['ve'] = __('Venda', 'powerpress');
    $langs['vi'] = __('Vietnamese', 'powerpress');
    $langs['zu'] = __('Zulu', 'powerpress');
    $langs['fa'] = __('Persian', 'powerpress');
    $langs['fa-af'] = __('Persian (Afghanistan)', 'powerpress');

    return $langs;
}
	
function powerpress_page_message_add_error($msg, $classes='inline', $escape=true)
{
	global $g_powerpress_page_message;
	if( $escape )
		$g_powerpress_page_message .= '<div class="error powerpress-error '.$classes.'">'. esc_html($msg) . '</div>';
	else
		$g_powerpress_page_message .= '<div class="error powerpress-error '.$classes.'">'. ($msg) . '</div>';
}

function powerpress_page_message_add_notice($msg, $classes='inline', $escape=true)
{
	global $g_powerpress_page_message;
	// Always pre-pend, since jQuery will re-order with first as last.
	if( $escape )
		$g_powerpress_page_message = '<div class="updated fade powerpress-notice '.$classes.'">'. esc_html($msg) . '</div>' . $g_powerpress_page_message;
	else
		$g_powerpress_page_message = '<div class="updated fade powerpress-notice '.$classes.'">'. ($msg) . '</div>' . $g_powerpress_page_message;
}


function powerpress_getAccessToken()
{
    // Look at the creds and use the latest access token, if its not the latest refresh it...
    $creds = get_option('powerpress_creds', array());
    if( !empty($creds['access_token']) && !empty($creds['access_expires']) && $creds['access_expires'] > time() ) { // If access token did not expire
        return $creds['access_token'];
    }

    if( !empty($creds['refresh_token']) && !empty($creds['client_id']) && !empty($creds['client_secret']) ) {

        // Create new access token with refresh token here...
        $auth = new PowerPressAuth();
        $resultTokens = $auth->getAccessTokenFromRefreshToken($creds['refresh_token'], $creds['client_id'], $creds['client_secret']);

        if( !empty($resultTokens['access_token']) && !empty($resultTokens['expires_in']) ) {
            powerpress_save_settings( array('access_token'=>$resultTokens['access_token'], 'access_expires'=>( time() + $resultTokens['expires_in'] - 10 ) ), 'powerpress_creds');

            return $resultTokens['access_token'];
        } else {
            //if their refresh token is expired, sign them out so they can re-authenticate
            delete_option('powerpress_creds');
            powerpress_page_message_add_error(__('Your account has been logged out due to inactivity with Blubrry services.', 'powerpress'));
            powerpress_page_message_print();

        }
    }

    // If we failed to get credentials, return false
    return false;
}

function powerpress_page_message_print()
{
	global $g_powerpress_page_message;
	if( $g_powerpress_page_message )
		echo $g_powerpress_page_message;
	$g_powerpress_page_message = '';
}

function powerpress_admin_activate()
{
	$Settings = get_option('powerpress_general', array());
	if( empty($Settings) )
	{
		// If no settings exist, see if either PodPress or Podcasting plugins are enabled and import those settings...
		if( defined('PODPRESS_VERSION') )
		{
			powerpress_admin_import_podpress_settings();
		}
		else if( isset($GLOBALS['podcasting_player_id']) || defined('PODCASTING_VERSION') )
		{
			powerpress_admin_import_podcasting_settings();
		}
	}
}

function powerpress_admin_init()
{
	global $wp_rewrite;
	add_thickbox(); // we use the thckbox for some settings
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core'); // Now including the library at Google

	// Powerpress page
	if( isset($_GET['page']) && strstr($_GET['page'], 'powerpress' ) !== false )
	{
		wp_enqueue_script('jquery-ui-tabs');
        // If we have powerpress credentials, check if the account has been verified
        $creds = get_option('powerpress_creds');
        powerpress_check_credentials($creds);
		//wp_enqueue_script('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/jquery-ui.min.js');
		if( preg_match('/powerpressadmin_(mobile|audio|video)player/', $_GET['page']) )
		{
			wp_enqueue_style( 'wp-color-picker' );
		}
		
		if( preg_match('/powerpressadmin_migrate/', $_GET['page']) )
		{
			wp_enqueue_script('media-upload'); // For the readjustment of the thickbox only
		}
	}

	if( function_exists('powerpress_admin_jquery_init') )
		powerpress_admin_jquery_init();
	
	if( !current_user_can(POWERPRESS_CAPABILITY_MANAGE_OPTIONS) )
	{
		powerpress_page_message_add_error( __('You do not have sufficient permission to manage options.', 'powerpress') );
		return;
	}

	// Check for other podcasting plugin
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
		powerpress_page_message_add_error( __('Another podcasting plugin has been detected, PowerPress is currently disabled.', 'powerpress') );
	
	global $wp_version;
	$VersionDiff = version_compare($wp_version, 3.6);
	if( $VersionDiff < 0 )
		powerpress_page_message_add_error( __('Blubrry PowerPress requires Wordpress version 3.6 or greater.', 'powerpress') );
	
	// Check for incompatible plugins:
	if( isset($GLOBALS['objWPOSFLV']) && is_object($GLOBALS['objWPOSFLV']) )
		powerpress_page_message_add_error( __('The WP OS FLV plugin is not compatible with Blubrry PowerPress.', 'powerpress') );
	
	// Security step, we must be in a powerpress/* page...
	if( isset($_GET['page']) && ( strstr($_GET['page'], 'powerpress/' ) !== false || strstr($_GET['page'], 'powerpressadmin_' ) !== false ) )
	{
        // Save settings here
		if( isset($_POST[ 'Feed' ]) || isset($_POST[ 'General' ])  )
		{
			check_admin_referer('powerpress-edit');

			// Save the posted value in the database
			$Feed = (isset($_POST['Feed'])?$_POST['Feed']:false);
			$General = (isset($_POST['General'])?$_POST['General']:false);
			$FeedSlug = (isset($_POST['feed_slug'])?esc_attr($_POST['feed_slug']):false);
			$Category = (isset($_POST['cat'])?intval($_POST['cat']):false);
			$term_taxonomy_id = (isset($_POST['ttid'])?intval($_POST['ttid']):false);
			$podcast_post_type = (isset($_POST['podcast_post_type'])?esc_attr($_POST['podcast_post_type']):false);
            $acceptable_extensions = ['jpg', 'jpeg', 'png'];

			// New iTunes image
			if( !empty($_POST['itunes_image_checkbox']) )
			{
				$temp = $_FILES['itunes_image_file']['tmp_name'];

                //Make sure the file extension is alright
                $name = $_FILES['itunes_image_file']['name'];
                $ext = substr($name, strrpos($name, '.') + 1);


                $UploadArray = wp_upload_dir();
                $upload_url = $UploadArray['baseurl'];
                if (empty($name) && !empty($Feed['itunes_image']) && strpos($Feed['itunes_image'], $upload_url) === false) {
                    // no uploaded file--URL pasted into field
                    $Feed['itunes_image'] = @media_sideload_image($Feed['itunes_image'], null, null, 'src');
                }
                if (!empty($_FILES['itunes_image_file']['tmp_name'])) {
                    if (!in_array(strtolower($ext), $acceptable_extensions)) {
                        unset($Feed['itunes_image']);
                        powerpress_page_message_add_error(__('Apple Podcasts image has an invalid file type: ' . $ext, 'powerpress'));
                    } else {
                        // Check the image...
                        if (file_exists($temp)) {
                            $ImageData = @getimagesize($temp);

                            $rgb = true; // We assume it is RGB
                            if (defined('POWERPRESS_IMAGICK') && POWERPRESS_IMAGICK) {
                                if ($ImageData[2] == IMAGETYPE_PNG && extension_loaded('imagick')) {
                                    $image = new Imagick($temp);
                                    if ($image->getImageColorspace() != imagick::COLORSPACE_RGB) {
                                        $rgb = false;
                                    }
                                }
                            }

                            if (empty($ImageData['channels']))
                                $ImageData['channels'] = 3; // Assume it's ok if we cannot detect it.

                            if ($ImageData) {
                                if ($rgb && ($ImageData[2] == IMAGETYPE_JPEG || $ImageData[2] == IMAGETYPE_PNG) && $ImageData[0] == $ImageData[1] && $ImageData[0] >= 1400 && $ImageData[0] <= 3000 && $ImageData['channels'] == 3) // Just check that it is an image, the correct image type and that the image is square
                                {
                                    $upload_result = wp_handle_upload($_FILES['itunes_image_file'], array('action' => $_POST['action'], 'test_form' => false));
                                    if (is_array($upload_result) && isset($upload_result['error'])) {
                                        unset($Feed['itunes_image']);
                                        powerpress_page_message_add_error(__('Error saving image', 'powerpress') . ':   ' . $upload_result['error']);
                                    } elseif (is_array($upload_result) && isset($upload_result['url'])) {
                                        $Feed['itunes_image'] = $upload_result['url'];
                                    } else {
                                        unset($Feed['itunes_image']);
                                        powerpress_page_message_add_error(__('Error saving image', 'powerpress'));
                                    }
                                } else if ($ImageData['channels'] != 3 || $rgb == false) {
                                    unset($Feed['itunes_image']);
                                    powerpress_page_message_add_error(__('Invalid image', 'powerpress') . ':	' . htmlspecialchars($_FILES['itunes_image_file']['name']) . ' - ' . __('Image must be in RGB color space (CMYK is not supported).', 'powerpress'));
                                } else if ($ImageData[0] != $ImageData[1]) {
                                    unset($Feed['itunes_image']);
                                    powerpress_page_message_add_error(__('Invalid image', 'powerpress') . ':	' . htmlspecialchars($_FILES['itunes_image_file']['name']) . ' - ' . __('Image must be square, 1400 x 1400 is the required minimum size.', 'powerpress'));
                                } else if ($ImageData[0] != $ImageData[1] || $ImageData[0] < 1400) {
                                    unset($Feed['itunes_image']);
                                    powerpress_page_message_add_error(__('Invalid image', 'powerpress') . ':	' . htmlspecialchars($_FILES['itunes_image_file']['name']) . ' - ' . __('Image is too small, 1400 x 1400 is the required minimum size.', 'powerpress'));
                                } else if ($ImageData[0] != $ImageData[1] || $ImageData[0] > 3000) {
                                    unset($Feed['itunes_image']);
                                    powerpress_page_message_add_error(__('Invalid image', 'powerpress') . ':	' . htmlspecialchars($_FILES['itunes_image_file']['name']) . ' - ' . __('Image is too large, 3000 x 3000 is the maximum size allowed.', 'powerpress'));
                                } else {
                                    unset($Feed['itunes_image']);
                                    powerpress_page_message_add_error(__('Invalid image', 'powerpress') . ':	' . htmlspecialchars($_FILES['itunes_image_file']['name']));
                                }
                            } else {
                                unset($Feed['itunes_image']);
                                powerpress_page_message_add_error(__('Invalid image', 'powerpress') . ':	' . htmlspecialchars($_FILES['itunes_image_file']['name']));
                            }
                        }
                    }
                }
			}
			
			// New mp3 coverart image
			if( !empty($_POST['coverart_image_checkbox']) )
			{
				$temp = $_FILES['coverart_image_file']['tmp_name'];

                //Make sure the file extension is alright
                $name = $_FILES['coverart_image_file']['name'];
                $ext = substr($name, strrpos($name, '.') + 1);

                if (!in_array(strtolower($ext), $acceptable_extensions)) {
                    powerpress_page_message_add_error(__('Coverart image has an invalid file type: ' . $ext, 'powerpress') );
                } else {
                    if (@getimagesize($temp))  // Just check that it is an image, we may add more to this later
                    {
                        $upload_result = wp_handle_upload($_FILES['coverart_image_file'], array('action' => $_POST['action'], 'test_form' => false));
                        if (is_array($upload_result) && isset($upload_result['error'])) {
                            powerpress_page_message_add_error(__('Error saving Coverart image', 'powerpress') . ':	' . $upload_result['error']);
                        } elseif (is_array($upload_result) && isset($upload_result['url'])) {
                            $General['tag_coverart'] = $upload_result['url'];
                        } else {
                            powerpress_page_message_add_error(__('Error saving Coverart image', 'powerpress'));
                        }
                    } else {
                        powerpress_page_message_add_error(__('Invalid Coverat image', 'powerpress') . ': ' . htmlspecialchars($_FILES['coverart_image_file']['name']));
                    }
                }
			}
			
			// New poster image
			if( !empty($_POST['poster_image_checkbox']) )
			{
				$temp = $_FILES['poster_image_file']['tmp_name'];

                //Make sure the file extension is alright
                $name = $_FILES['poster_image_file']['name'];
                $ext = substr($name, strrpos($name, '.') + 1);

                if (!in_array(strtolower($ext), $acceptable_extensions)) {
                    powerpress_page_message_add_error(__('Poster image has an invalid file type: ' . $ext, 'powerpress') );
                } else {
                    if (@getimagesize($temp))  // Just check that it is an image, we may add more to this later
                    {
                        $upload_result = wp_handle_upload($_FILES['poster_image_file'], array('action' => $_POST['action'], 'test_form' => false));
                        if (is_array($upload_result) && isset($upload_result['error'])) {
                            powerpress_page_message_add_error(__('Error saving Poster image', 'powerpress') . ':	' . $upload_result['error']);
                        } elseif (is_array($upload_result) && isset($upload_result['url'])) {
                            $General['poster_image'] = $upload_result['url'];
                        } else {
                            powerpress_page_message_add_error(__('Error saving Poster image', 'powerpress'));
                        }
                    } else {
                        powerpress_page_message_add_error(__('Invalid poster image', 'powerpress') . ': ' . htmlspecialchars($_FILES['poster_image_file']['name']));
                    }
                }
			}
			
			
			// New audio play icon image
			if( !empty($_POST['audio_custom_play_button_checkbox']) )
			{
				$temp = $_FILES['audio_custom_play_button_file']['tmp_name'];

                //Make sure the file extension is alright
                $name = $_FILES['audio_custom_play_button_file']['name'];
                $ext = substr($name, strrpos($name, '.') + 1);

                if (!in_array(strtolower($ext), $acceptable_extensions)) {
                    powerpress_page_message_add_error(__('Custom audio play button image has an invalid file type: ' . $ext, 'powerpress') );
                } else {
                    if (@getimagesize($temp))  // Just check that it is an image, we may add more to this later
                    {
                        $upload_result = wp_handle_upload($_FILES['audio_custom_play_button_file'], array('action' => $_POST['action'], 'test_form' => false));
                        if (is_array($upload_result) && isset($upload_result['error'])) {
                            powerpress_page_message_add_error(__('Error saving Play image', 'powerpress') . ':	' . $upload_result['error']);
                        } elseif (is_array($upload_result) && isset($upload_result['url'])) {
                            $General['audio_custom_play_button'] = $upload_result['url'];
                        } else {
                            powerpress_page_message_add_error(__('Error saving Play image', 'powerpress'));
                        }
                    } else {
                        powerpress_page_message_add_error(__('Invalid play icon image', 'powerpress') . ': ' . htmlspecialchars($_FILES['audio_custom_play_button_file']['name']));
                    }
                }
			}
			
			// New video play icon image powerpress-select-player
			if( !empty($_POST['video_custom_play_button_checkbox']) )
			{
				$temp = $_FILES['video_custom_play_button_file']['tmp_name'];

                //Make sure the file extension is alright
                $name = $_FILES['video_custom_play_button_file']['name'];
                $ext = substr($name, strrpos($name, '.') + 1);

                if (!in_array(strtolower($ext), $acceptable_extensions)) {
                    powerpress_page_message_add_error(__('Custom video play button image has an invalid file type: ' . $ext, 'powerpress') );
                } else {
                    $imageInfo = @getimagesize($temp);
                    if ($imageInfo && $imageInfo[0] == $imageInfo[1] && $imageInfo[0] == 60)  // Just check that it is an image, we may add more to this later
                    {
                        $upload_result = wp_handle_upload($_FILES['video_custom_play_button_file'], array('action' => $_POST['action'], 'test_form' => false));
                        if (is_array($upload_result) && isset($upload_result['error'])) {
                            powerpress_page_message_add_error(__('Error saving Video Play icon image', 'powerpress') . ':	' . $upload_result['error']);
                        } elseif (is_array($upload_result) && isset($upload_result['url'])) {
                            $General['video_custom_play_button'] = $upload_result['url'];
                        } else {
                            powerpress_page_message_add_error(__('Error saving Video Play icon image', 'powerpress'));
                        }
                    } else if ($imageInfo) {
                        powerpress_page_message_add_error(__('Invalid play icon image size', 'powerpress') . ': ' . htmlspecialchars($_FILES['video_custom_play_button_file']['name']));
                    } else {
                        powerpress_page_message_add_error(__('Invalid play icon image', 'powerpress') . ': ' . htmlspecialchars($_FILES['video_custom_play_button_file']['name']));
                    }
                }
			}
			
			if( isset($_POST['UpdateDisablePlayer']) )
			{
				$player_feed_slug = $_POST['UpdateDisablePlayer'];
				$General['disable_player'] = array();
				$GeneralPrev = get_option('powerpress_general', array());
				if( isset($GeneralPrev['disable_player']) )
					$General['disable_player'] = $GeneralPrev['disable_player'];
				if( isset($_POST['DisablePlayerFor']) && !empty($_POST['DisablePlayerFor']) )
					$General['disable_player'][ $player_feed_slug ] = 1;
				else
					unset($General['disable_player'][ $player_feed_slug ]);
			}
			
			// Check to see if we need to update the feed title
			if( $FeedSlug && !$podcast_post_type )
			{
				$GeneralSettingsTemp = powerpress_get_settings('powerpress_general', false);
				if( !isset($GeneralSettingsTemp['custom_feeds'][$FeedSlug]) || $GeneralSettingsTemp['custom_feeds'][$FeedSlug] != $Feed['title'] )
				{
					if( !$General )
						$General = array();
					if( !empty($GeneralSettingsTemp['custom_feeds']) )
						$General['custom_feeds'] = $GeneralSettingsTemp['custom_feeds'];
					else
						$General['custom_feeds'] = array();
					$General['custom_feeds'][$FeedSlug] = $Feed['title'];
				}
			}
			
			// Update the settings in the database:
			if( $General )
			{
				if( !empty($_POST['action']) && $_POST['action'] == 'powerpress-save-settings' )
				{
					if( !isset($General['display_player_excerpt']) ) // If we are modifying appearance settings but this option was not checked...
						$General['display_player_excerpt'] = 0; // Set it to zero.
					
					//if( !isset($General['display_player_disable_mobile']) )
					//	$General['display_player_disable_mobile'] = 0;
					
					$General['disable_dashboard_stats'] = 0;
					if( !empty($_POST['DisableStatsInDashboard'] ) )
						$General['disable_dashboard_stats'] = 1;
					if( !isset($General['disable_dashboard_news'] ) )
						$General['disable_dashboard_news'] = 0;
					if( !isset($General['allow_feed_comments'] ) )
						$General['allow_feed_comments'] = 0;
						
					if( !isset($General['feed_links']) )
						$General['feed_links'] = 0;
                    if( !isset($General['suppress_unused_item_tags']) )
                        $General['suppress_unused_item_tags'] = 0;

                    if( !isset($General['skip_to_episode_settings']) )
                        $General['skip_to_episode_settings'] = 0;

					// Advanced Features
					if( !isset($General['player_options'] ) )
						$General['player_options'] = 0;
					if( !isset($General['cat_casting'] ) )
						$General['cat_casting'] = 0;
					if( !isset($General['channels'] ) )
						$General['channels'] = 0;
					if( !isset($General['taxonomy_podcasting'] ) )
						$General['taxonomy_podcasting'] = 0;
					if( !isset($General['posttype_podcasting'] ) )
						$General['posttype_podcasting'] = 0;
					if( !isset($General['playlist_player'] ) )
						$General['playlist_player'] = 0;
					if(!isset($General['powerpress_network']))
					    $General['powerpress_network'] = 0;
                    if(!isset($General['powerpress_accept_json']))
                        $General['powerpress_accept_json'] = 0;
                    if(!isset($General['pp_show_block_errors']))
                        $General['pp_show_block_errors'] = 0;

                    // Media Presentation Settings
					$PlayerSettings = array();
					if( !empty($_POST['PlayerSettings']) )
						$PlayerSettings = $_POST['PlayerSettings'];	
					if( empty($PlayerSettings['display_pinw']) )
						$PlayerSettings['display_pinw'] = 0;
					if( empty($PlayerSettings['display_media_player']) )
						$PlayerSettings['display_media_player'] = 0;
					if( empty($PlayerSettings['display_pinw']) ) $PlayerSettings['display_pinw'] = 0;
					if( empty($PlayerSettings['display_media_player']) ) $PlayerSettings['display_media_player'] = 0;
					
					$General['player_function'] = abs( $PlayerSettings['display_pinw'] - $PlayerSettings['display_media_player'] );
					$General['podcast_link'] = 0;
					if( !empty($PlayerSettings['display_download']) )
					{
						$General['podcast_link'] = 1;
						if( !empty($PlayerSettings['display_download_size']) )
						{
							$General['podcast_link'] = 2;
							if( !empty($PlayerSettings['display_download_duration']) )
								$General['podcast_link'] = 3;
						}
					}
					
					if( !isset($General['podcast_embed'] ) )
						$General['podcast_embed'] = 0;
					if( !isset($General['podcast_embed_in_feed'] ) )
						$General['podcast_embed_in_feed'] = 0;
					if( !isset($General['m4a'] ) )
						$General['m4a'] = '';
					if( !isset($General['new_window_nofactor'] ) )
						$General['new_window_nofactor'] = '';
						
					if( !isset($General['subscribe_links'] ) )
						$General['subscribe_links'] = false;	
					if( !isset($General['subscribe_feature_email'] ) )
						$General['subscribe_feature_email'] = false;		
				}
				else if( !empty($_POST['action']) && $_POST['action'] == 'powerpress-save-defaults' )
				{
					if( !isset($General['display_player_excerpt']) ) // If we are modifying appearance settings but this option was not checked...
						$General['display_player_excerpt'] = 0; // Set it to zero.
					$General['disable_dashboard_stats'] = 0;
					if( !empty($_POST['DisableStatsInDashboard'] ) )
						$General['disable_dashboard_stats'] = 1;
					
					// Advanced Mode options
					if( !isset($General['cat_casting'] ) )
						$General['cat_casting'] = 0;
					if( !isset($General['channels'] ) )
						$General['channels'] = 0;
					if( !isset($General['taxonomy_podcasting'] ) )
						$General['taxonomy_podcasting'] = 0;
					if( !isset($General['posttype_podcasting'] ) )
						$General['posttype_podcasting'] = 0;
                    if( !isset($General['playlist_player'] ) )
                        $General['playlist_player'] = 0;
                    if(!isset($General['powerpress_network']))
                        $General['powerpress_network'] = 0;
                    if(!isset($General['powerpress_accept_json']))
                        $General['powerpress_accept_json'] = 0;
                    if(!isset($General['pp_show_block_errors']))
                        $General['pp_show_block_errors'] = 0;
				}

				// seo settings
				if( !empty($_POST['action']) && $_POST['action'] == 'powerpress-save-settings' )
				{
					//$PowerPressSearch = $_POST['PowerPressSearch'];
					$PowerPressSearchToggle = $_POST['PowerPressSearchToggle'];
					if( empty($PowerPressSearchToggle['seo_feed_title']) )
						$General['seo_feed_title'] = 0;
				}

				// tags settings
				if( !empty($_POST['action']) && $_POST['action'] == 'powerpress-save-settings' )
				{
					if( !isset($General['write_tags']) ) // If we are modifying appearance settings but this option was not checked...
						$General['write_tags'] = 0; // Set it to zero.
						
					$TagValues = $_POST['TagValues'];
					$GeneralPosted = $_POST['General'];
					
					if( !empty($_POST['PowerPressTrackNumber']) ) {
						update_option('powerpress_track_number',  $_POST['PowerPressTrackNumber']);
					}
					// Set all the tag values...
					foreach( $GeneralPosted as $key => $value )
					{
						if( substr($key, 0, 4) == 'tag_' )
						{
							// Special case, we are uploading new coverart image
							if( !empty($_POST['coverart_image_checkbox']) && $key == 'tag_coverart' )
								continue;
								
							// Specail case, the track is saved in a separate column in the database.
							if( $key == 'tag_track' )
								continue; 
							
							if( !empty($value) )
								$General[$key] = $TagValues[$key];
							else
								$General[$key] = '';
						}
					}
					
					if( !empty($General['tag_coverart']) ) // $TagValues['tag_coverart'] != '' )
					{
						$GeneralSettingsTemp = powerpress_get_settings('powerpress_general', false);
						if( !empty($GeneralSettingsTemp['blubrry_hosting']) && $GeneralSettingsTemp['blubrry_hosting'] !== 'false' )
						{
                            require_once(POWERPRESS_ABSPATH .'/powerpressadmin-auth.class.php');
                            $auth = new PowerPressAuth();
							$json_data = false;
                            $api_url_array = powerpress_get_api_array();
                            $creds = get_option('powerpress_creds');
							if ($creds) {
                                $accessToken = powerpress_getAccessToken();
                                $req_url = sprintf('/2/media/%s/coverart.json?url=%s', $GeneralSettingsTemp['blubrry_program_keyword'], urlencode($TagValues['tag_coverart']));
                                $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA')?'?'. POWERPRESS_BLUBRRY_API_QSA:'');
                                $results = $auth->api($accessToken, $req_url, false, 'DELETE');
                            } else {
                                foreach ($api_url_array as $index => $api_url) {
                                    $req_url = sprintf('%s/media/%s/coverart.json?url=%s', rtrim($api_url, '/'), $GeneralSettingsTemp['blubrry_program_keyword'], urlencode($TagValues['tag_coverart']));
                                    $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '&' . POWERPRESS_BLUBRRY_API_QSA : '');
                                    $json_data = powerpress_remote_fopen($req_url, $GeneralSettingsTemp['blubrry_auth']);
                                    if (!$json_data && $api_url == 'https://api.blubrry.com/') { // Lets force cURL and see if that helps...
                                        $json_data = powerpress_remote_fopen($req_url, $GeneralSettingsTemp['blubrry_auth'], array(), 15, false, true);
                                    }
                                    if ($json_data != false)
                                        break;
                                }
                                // Lets try to cache the image onto Blubrry's Server...
                                $results = powerpress_json_decode($json_data);
                            }
								
							if( is_array($results) && !isset($results['error']) )
							{
								// Good!
								powerpress_page_message_add_notice( __('Coverart image updated successfully.', 'powerpress') );
							}
							else if( isset($results['error']) )
							{
								$error = __('Blubrry Hosting Error (updating coverart)', 'powerpress') .': '. $results['error'];
								powerpress_page_message_add_error($error);
							}
							else
							{
								$error = __('An error occurred updating the coverart with your Blubrry Services Account.', 'powerpress');
								powerpress_page_message_add_error($error);
							}
						}
						else
						{
							powerpress_page_message_add_error( __('Coverart Image was not uploaded to your Blubrry Services Account. It will NOT be added to your mp3s.', 'powerpress') );
						}
					}
				}
				
				if( !empty($_POST['action']) && $_POST['action'] == 'powerpress-save-videocommon' )
				{
					if( !isset($General['poster_play_image'] ) )
						$General['poster_play_image'] = 0;
					if( !isset($General['poster_image_audio'] ) )
						$General['poster_image_audio'] = 0;
				}
				
				// Wordpress adds slashes to everything, but since we're storing everything serialized, lets remove them...
				$General = powerpress_stripslashes($General);
				powerpress_save_settings($General);
			}
			
			if( $Feed )
			{
                $personNames = [];
                $personRoles = [];
                $personURLs = [];
                $linkURLs = [];
                $count = 1;
                while (true) {
                    if (!isset($_POST["role-$count-name"]))
                        break;

                    $name = $_POST["role-$count-name"];

                    if ($name != "") {
                        $personNames[] = $name;
                        $personRoles[] = $_POST["role-$count-role"];
                        $personURLs[] = $_POST["role-$count-personurl"];
                        $linkURLs[] = $_POST["role-$count-linkurl"];
                    }
                    $count += 1;
                }

                if (count($personNames) > 0) {
                    $Feed['person_names'] = $personNames;
                    $Feed['person_roles'] = $personRoles;
                    $Feed['person_urls'] = $personURLs;
                    $Feed['link_urls'] = $linkURLs;
                } else {
                    $Feed['person_names'] = [''];
                    $Feed['person_roles'] = [''];
                    $Feed['person_urls'] = [''];
                    $Feed['link_urls'] = [''];
                }

                // Experimental Features - Logic from experimental.php in pub to save shit
                $Feed['value_error'] = 'no';

                $valueErrorMsg = '';
                $valueError = false;
                $morePeople = true;
                $lightning = [];
                $splits = [];
                $pubKeys = [];
                $customKeys = [];
                $customValues = [];
                $count = 1;
                $validPeople = 0;
                while ($morePeople) {
                    if (isset($_POST["person-$count-pubkey"]))
                        $pubKey = $_POST["person-$count-pubkey"];
                    else
                        $pubKey = false;

                    if ($pubKey === false) {
                        $morePeople = false;
                    } else {
                        if ($pubKey != "" || $_POST["person-$count-split"] != "") {
                            $pubKeys[] = $pubKey;
                            $splits[] = $_POST["person-$count-split"];
                            $lightning[] = $_POST["person-$count-lightning"];
                            $customKeys[] = $_POST["person-$count-customkey"];
                            $customValues[] = $_POST["person-$count-customvalue"];
                            $validPeople += 1;
                        }
                    }
                    $count += 1;
                }

                foreach ($pubKeys as $pubKey) {
                    if ($pubKey == "") {
                        $Feed['value_error'] = 'yes';
                        $valueError = true;
                        $valueErrorMsg .= " PubKey is required for all recipients.";
                    }
                }

                $sum = 0;

                if (count($pubKeys) == 1)
                    $splits[0] = 100;

                foreach ($splits as $split) {
                    $sum += $split;
                }

                if (!$valueError) {
                    $Feed['value_error'] = "no";
                    $Feed['value_pubkey'] = $pubKeys;
                }

                $Feed['value_lightning'] = $lightning;
                $Feed['value_custom_key'] = $customKeys;
                $Feed['value_custom_value'] = $customValues;
                $Feed['value_split'] = $splits;

                if ($sum != 100 && $validPeople > 0) {
                    $Feed['value_error'] = "yes";
                    $valueErrorMsg .= " Split values should add up to 100.";
                }

                $Feed['value_error_message'] = $valueErrorMsg;

                $feed_url = "";
                if ($Category) {
                    if( !empty($General['cat_casting_podcast_feeds']) )
                        $feed_url = get_category_feed_link($Category, 'podcast');
                    else // Use the old link
                        $feed_url = get_category_feed_link($Category);
                } else if ($term_taxonomy_id) {
                    global $wpdb;
                    $term_info = $wpdb->get_results("SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = $term_taxonomy_id",  ARRAY_A);
                    $taxonomy_type = $term_info[0]['taxonomy'];
                    $feed_url = get_term_feed_link($term_taxonomy_id, $taxonomy_type, 'rss2');
                } else if ($podcast_post_type) {
                    $feed_url = get_post_type_archive_feed_link($podcast_post_type, $FeedSlug);
                } else // otherwise treat as a podcast channel
                {
                    $feed_url = get_feed_link($FeedSlug);
                }

                require_once('uuid5.class.php');
                if (!UUID::is_valid($Feed['live_item']['guid']) || $Feed['live_item']['status'] == 'Pending') {
                    $Feed['live_item']['guid'] = UUID::v5('ead4c236-bf58-58c6-a2c6-a6b28d128cb6', $Feed['live_item']['title'] . ' ' .rand(0, 1000000));
                    $Feed['live_item']['podping_status'] = -1;
                }

                $litEerror = false;
                update_option('lit_error', false);
                update_option('lit_error_msg', '');

                if ($Feed['live_item']['enabled'] == '1') {
                    if ($Feed['live_item']['start_date_time'] == "" || $Feed['live_item']['end_date_time'] == "" || $Feed['live_item']['title'] == "" || $Feed['live_item']['stream_link'] == "" || $Feed['live_item']['fallback_link'] == "") {
                        update_option('lit_error', true);
                        update_option('lit_error_msg', "Please fill out all required fields before enabling your live item.");
                        $litEerror = true;
                    }

                    // Ensure end date is after start date
                    $startDate = explode('T', $Feed['live_item']['start_date_time']);
                    $endDate = explode('T', $Feed['live_item']['end_date_time']);

                    $startUnix = strtotime($startDate[0] . ' ' . $startDate[1] . ' ' . $Feed['live_item']['timezone']);
                    $endUnix = strtotime($endDate[0] . ' ' . $endDate[1] . ' ' . $Feed['live_item']['timezone']);

                    if ($endUnix <= $startUnix) {
                        update_option('lit_error', true);
                        update_option('lit_error_msg', "Please choose an ending date and time which is after your starting date.");
                        $litEerror = true;
                    }

                    if ($startUnix <= time() && $Feed['live_item']['status'] == "Pending") {
                        update_option('lit_error', true);
                        update_option('lit_error_msg', 'Please choose a starting date and time in the future.');
                        $litEerror = true;
                    }

                    if ($litEerror) {
                        $Feed['live_item']['enabled'] = '0';
                    } else {
                        $Feed['live_item']['podping_status'] = intval($Feed['live_item']['podping_status']);
                        $reason = false;

                        if ($Feed['live_item']['status'] == 'Ended')
                            $reason = 'liveEnd';
                        elseif ($Feed['live_item']['status'] == 'Live')
                            $reason = 'live';

                        $creds = get_option('powerpress_creds');
                        $GeneralSettingsTemp = powerpress_get_settings('powerpress_general', false);
                        $pending = !$reason && $Feed['live_item']['podping_status'] != 0;
                        $live = $reason == 'live' && $Feed['live_item']['podping_status'] != 1;
                        $ended = $reason == 'liveEnd' && $Feed['live_item']['podping_status'] != 2;

                        if (($pending || $live || $ended) && (!empty($creds) || !empty($GeneralSettingsTemp['blubrry_auth']))) {
                            if (!(defined('POWERPRESS_DISABLE_PODPING') && POWERPRESS_DISABLE_PODPING)) {
                                require_once('powerpresspodping.class.php');
                                $Podping = new PowerPressPodping();
                                try {
                                    // because auth key is the same across the whole site and each feed url is being passed, it doesn't matter what program keyword we use to call the api
                                    $Podping->publish($feed_url, $GeneralSettingsTemp['blubrry_program_keyword'], $reason);
                                } catch (Exception $e) {
                                    if (defined('WP_DEBUG') && WP_DEBUG) {
                                        powerpress_add_error($e->getMessage());
                                    }
                                }

                                if ($reason == 'live')
                                    $Feed['live_item']['podping_status'] = 1;
                                else if ($reason == 'liveEnd')
                                    $Feed['live_item']['podping_status'] = 2;
                                else
                                    $Feed['live_item']['podping_status'] = 0;
                            }
                        }
                    }
                }

                if (isset($Feed['update_frequency'])) {
                    $updateFrequency = $Feed['update_frequency'];
                    $Feed['frequency'] = $updateFrequency == 1 ? 'Daily' : ($updateFrequency == 2 ? 'Weekly' : 'Monthly');

                    if ($updateFrequency == 2) { # Weekly
                        $selectedDayList = [];
                        for ($count=0; $count<7; $count++) {
                            $isChecked = isset($Feed['freq-day-'.$count]);

                            if ($isChecked)
                                $selectedDayList[] = $count;
                        }

                        $Feed['update_frequency_week'] = implode(',', $selectedDayList);
                    }
                }

                $blockAgree = $Feed['block'] ?? false;

                if (!isset($Feed['block_all']))
                    $Feed['block_all'] = 0;

                if ($blockAgree)
                    $Feed['block_list'] = implode(';', $Feed['block_list'] ?? []);

                $remoteItems = $Feed['remoteItems'] ?? [];

                if (!empty($remoteItems)) {
                    $newRemoteItems = [];
                    $podrollItems = $remoteItems['podroll'] ?? [];
                    $feedItems = $remoteItems['feed'] ?? [];

                    foreach ($podrollItems as $podcastGuid => $itemGuids) {
                        $newRemoteItems[] = array(
                            'feed_guid' => $podcastGuid,
                            'item_guid' => '',
                            'item_title' => $itemGuids[0],
                            'item_link' => $itemGuids['link'],
                            'podroll' => 1
                        );
                    }

                    foreach ($feedItems as $podcastGuid => $itemGuids) {
                        $previousItems = array();

                        foreach ($itemGuids as $itemGuid => $itemTitle) {
                            if (!in_array($itemGuid, $previousItems) && $itemGuid != 'link') {
                                $newRemoteItems[] = array(
                                    'feed_guid' => $podcastGuid,
                                    'item_guid' => $itemGuid != 'none' ? $itemGuid : '',
                                    'item_title' => $itemTitle,
                                    'item_link' => $itemGuids['link'],
                                    'podroll' => 0
                                );

                                $previousItems[] = $itemGuid;
                            }
                        }
                    }

                    $Feed['remote_items'] = $newRemoteItems;
                } else {
                    $Feed['remote_items'] = [];
                }
                unset($Feed['remoteItems']);

				if( !isset($_POST['ProtectContent']) && isset($Feed['premium']) )
					$Feed['premium'] = false;
				if( !isset($Feed['enhance_itunes_summary']) )
					$Feed['enhance_itunes_summary'] = false;
				if( !isset($Feed['itunes_author_post']) )
					$Feed['itunes_author_post'] = false;
                if( !isset($Feed['pp_enable_feed_lock']) )
                    $Feed['pp_enable_feed_lock'] = false;
                if( !isset($Feed['guid_override_check']) )
                    $Feed['guid_override_check'] = false;
					
				if( !isset($Feed['itunes_block']) )
					$Feed['itunes_block'] = false;
				if( !isset($Feed['itunes_complete']) )
					$Feed['itunes_complete'] = false;
				if( !isset($Feed['maximize_feed']) )
					$Feed['maximize_feed'] = false;
                if( !isset($Feed['unlock_podcast']) )
                    $Feed['unlock_podcast'] = false;
				if( !isset($Feed['donate_link']) )
					$Feed['donate_link'] = false;
				if( !isset($Feed['episode_itunes_image']) )
					$Feed['episode_itunes_image'] = false;


				$Feed = powerpress_stripslashes($Feed);
				if( $Category )
				{
					powerpress_save_settings($Feed, 'powerpress_cat_feed_'.$Category);
				}
				else if ( $term_taxonomy_id )
				{
					powerpress_save_settings($Feed, 'powerpress_taxonomy_'.$term_taxonomy_id);
				}
				else if( $podcast_post_type )
				{
					$PostTypeSettings = array();
					$PostTypeSettings[ $FeedSlug ] = $Feed;
					powerpress_save_settings($PostTypeSettings, 'powerpress_posttype_'.$podcast_post_type);
					powerpress_rebuild_posttype_podcasting();
				}
				else // otherwise treat as a podcast channel
				{
					if( $FeedSlug == false && get_option('powerpress_feed_podcast') ) // If the settings were moved to the podcast channels feature...
						powerpress_save_settings($Feed, 'powerpress_feed_podcast' ); // save a copy here if that is the case.
					
					powerpress_save_settings($Feed, 'powerpress_feed'.($FeedSlug?'_'.$FeedSlug:'') );
				}
			}
			
			if( isset($_POST['EpisodeBoxBGColor']) )
			{
				$GeneralSettingsTemp = get_option('powerpress_general', array());
				$SaveEpisdoeBoxBGColor['episode_box_background_color'] = array();
				if( isset($GeneralSettingsTemp['episode_box_background_color']) )
					$SaveEpisdoeBoxBGColor['episode_box_background_color'] = $GeneralSettingsTemp['episode_box_background_color']; //  copy previous settings
				
				foreach( $_POST['EpisodeBoxBGColor'] as $feed_slug_temp => $background_color ) {
					break;
				}
				$SaveEpisdoeBoxBGColor['episode_box_background_color'][ $feed_slug_temp ] = $background_color;
				powerpress_save_settings($SaveEpisdoeBoxBGColor);
			}

			if (isset($_POST['blubrry-login'])) {
                check_admin_referer('powerpress-edit');
                $pp_nonce = powerpress_login_create_nonce();
                $tab_string = isset($_POST['tab']) ? "&tab={$_POST['tab']}" : "";
                $sidenav_tab_string = isset($_POST['sidenav-tab']) ? "&sidenav-tab={$_POST['sidenav-tab']}" : "";
                header("Location: " . add_query_arg( '_wpnonce', $pp_nonce, admin_url("admin.php?page=powerpressadmin_onboarding.php&step=blubrrySignin&from=powerpressadmin_basic{$tab_string}{$sidenav_tab_string}")));
            }

			// Anytime settings are saved lets flush the rewrite rules
			$wp_rewrite->flush_rules();
			// Settings saved successfully
			if( !empty($_POST['action']) )
			{
				switch( $_POST['action'] )
				{
					case 'powerpress-save-settings':
					case 'powerpress-save-defaults': {
						powerpress_page_message_add_notice( __('Blubrry PowerPress settings saved.', 'powerpress') );
					}; break;
					case 'powerpress-save-channel': {
						powerpress_page_message_add_notice( __('Blubrry PowerPress Channel settings saved.', 'powerpress') );
                        $_GET['action'] = 'powerpress-editfeed';
                        $_GET['feed_slug'] = $FeedSlug;
					}; break;
					case 'powerpress-save-category': {
						powerpress_page_message_add_notice( __('Blubrry PowerPress Category Podcasting  settings saved.', 'powerpress') );
                        $_GET['action'] = 'powerpress-editcategoryfeed';
                        $_GET['cat'] = $Category;
					}; break;
					case 'powerpress-save-ttid': {
						powerpress_page_message_add_notice( __('Blubrry PowerPress Taxonomy Podcasting settings saved.', 'powerpress') );
                        $_GET['action'] = 'powerpress-edittaxonomyfeed';
                        $term_ID = intval( isset($_POST['term'])? $_POST['term'] : $_GET['term'] );
                        $_GET['term'] = $term_ID;
                        $_GET['ttid'] = $term_taxonomy_id;
					}; break;
					case 'powerpress-save-post_type': {
						powerpress_page_message_add_notice( __('Blubrry PowerPress Post Type Podcasting settings saved.', 'powerpress') );
                        $_GET['action'] = 'powerpress-editposttypefeed';
                        $_GET['feed_slug'] = $FeedSlug;
                        $_GET['podcast_post_type'] = $podcast_post_type;
					}; break;
					default: {
						powerpress_page_message_add_notice( __('Blubrry PowerPress settings saved.', 'powerpress') );
					}; break;
				}
			}
		}
		
		// Handle POST actions...
		if( isset($_POST['action'] ) )
		{
			switch($_POST['action'])
			{
				case 'powerpress-addfeed': {
					check_admin_referer('powerpress-add-feed');
					
					$Settings = get_option('powerpress_general', array());
					$key = sanitize_title($_POST['feed_slug']);
					$value = $_POST['feed_name'];
					$value = powerpress_stripslashes($value);
					
					/*
					if( isset($Settings['custom_feeds'][ $key ]) && empty($_POST['overwrite']) )
					{
						powerpress_page_message_add_error( sprintf(__('Feed slug "%s" already exists.'), $key) );
					} else */
					if( $key == '' )
					{
						powerpress_page_message_add_error( sprintf(__('Feed slug "%s" is not valid.', 'powerpress'), esc_html($_POST['feed_slug']) ) );
					}
					else if( in_array($key, $wp_rewrite->feeds)  && !isset($Settings['custom_feeds'][ $key ]) ) // If it is a system feed or feed created by something else
					{
						powerpress_page_message_add_error( sprintf(__('Feed slug "%s" is not available.', 'powerpress'), esc_html($key) ) );
					}
					else
					{
						$Settings['custom_feeds'][ $key ] = $value;
						powerpress_save_settings($Settings);
						
						add_feed($key, 'powerpress_do_podcast_feed'); // Before we flush the rewrite rules we need to add the new custom feed...
						$wp_rewrite->flush_rules();
						
						powerpress_page_message_add_notice( sprintf(__('Podcast Feed "%s" added, please configure your new feed now.', 'powerpress'), esc_html($value) ) );
						$_GET['action'] = 'powerpress-editfeed';
						$_GET['feed_slug'] = $key;
					}
				}; break;
				case 'powerpress-addtaxonomyfeed': {
					if( !empty($_POST['cancel']) )
						unset($_POST['taxonomy']);
					
					if( empty($_POST['add_podcasting']) )
						break; // We do not handle this situation
				}
				case 'powerpress-addcategoryfeed': {
				
					check_admin_referer('powerpress-add-taxonomy-feed');
					
					
					
				
					$taxonomy_type = ( isset($_POST['taxonomy'])? $_POST['taxonomy'] : $_GET['taxonomy'] );
					$term_ID = intval( isset($_POST['term'])? $_POST['term'] : $_GET['term'] );
					
					
					$term_object = get_term( $term_ID, $taxonomy_type, OBJECT, 'edit');
					
					if( empty($term_ID) )
					{
						if( $taxonomy_type == 'category' )
							powerpress_page_message_add_error( __('You must select a category to continue.', 'powerpress') );
						else
							powerpress_page_message_add_error( __('You must select a term to continue.', 'powerpress') );
					}
					else if( $term_object == false )
					{
						powerpress_page_message_add_error( __('Error obtaining term information.', 'powerpress') );
					}
					else if( $taxonomy_type == 'category' )
					{
						$Settings = get_option('powerpress_general', array());
						if( empty($Settings['custom_cat_feeds']) )
							$Settings['custom_cat_feeds'] = array();
						
						if( !in_array($term_ID, $Settings['custom_cat_feeds']) )
						{
							$Settings['custom_cat_feeds'][] = $term_ID;
							powerpress_save_settings($Settings);
						}
					
						powerpress_page_message_add_notice( __('Please configure your category podcast feed now.', 'powerpress') );
						
						$_GET['action'] = 'powerpress-editcategoryfeed';
						$_GET['cat'] = $term_ID;
					}
					else
					{
						
			
						//$term_info = term_exists($term_ID, $taxonomy_type);
						$tt_id = $term_object->term_taxonomy_id;
						
						if( !$tt_id )
						{
						
						}
						else
						{
							$Settings = get_option('powerpress_taxonomy_podcasting', array());
			
							if( !isset($Settings[ $tt_id ])  )
							{
								$Settings[ $tt_id ] = true;
								powerpress_save_settings($Settings, 'powerpress_taxonomy_podcasting'); // add the feed to the taxonomy podcasting list
							}
						
							powerpress_page_message_add_notice( __('Please configure your taxonomy podcast now.', 'powerpress') );
							
							$_GET['action'] = 'powerpress-edittaxonomyfeed';
							$_GET['term'] = $term_ID;
							$_GET['ttid'] = $tt_id;
						}
					}
				}; break;
				case 'powerpress-addposttypefeed': {
					
					
					check_admin_referer('powerpress-add-posttype-feed');
					//die('ok 2');
					
					$Settings = get_option('powerpress_general', array());
					$feed_slug = sanitize_title($_POST['feed_slug']);
					$post_type = $_POST['podcast_post_type'];
					$post_type = powerpress_stripslashes($post_type);
					$feed_title = $_POST['feed_title'];
					$feed_title = powerpress_stripslashes($feed_title);
					
					
					
					/*
					if( isset($Settings['custom_feeds'][ $key ]) && empty($_POST['overwrite']) )
					{
						powerpress_page_message_add_error( sprintf(__('Feed slug "%s" already exists.'), $key) );
					} else */
					if( empty($feed_slug) )
					{
						powerpress_page_message_add_error( sprintf(__('Feed slug "%s" is not valid.', 'powerpress'), esc_html($_POST['feed_slug']) ) );
					}
					else if( empty($post_type) )
					{
						powerpress_page_message_add_error( __('Post Type is invalid.', 'powerpress') );
					}
					// TODO:
					//else if( in_array($feed_slug, $wp_rewrite->feeds)  && !isset($Settings['custom_feeds'][ $key ]) ) // If it is a system feed or feed created by something else
					//{
					//	powerpress_page_message_add_error( sprintf(__('Feed slug "%s" is not available.', 'powerpress'), $key) );
					//}
					else
					{
						$ExistingSettings = powerpress_get_settings('powerpress_posttype_'. $post_type);
						if( !empty($ExistingSettings[ $feed_slug ]) )
						{
							powerpress_page_message_add_error( sprintf(__('Feed slug "%s" already exists.', 'powerpress'), $_POST['feed_slug']) );
						}
						else
						{
							$NewSettings = array();
							$NewSettings[ $feed_slug ]['title'] = $feed_title;
							powerpress_save_settings($NewSettings, 'powerpress_posttype_'. $post_type);
							
							
							add_feed($feed_slug, 'powerpress_do_podcast_feed'); // Before we flush the rewrite rules we need to add the new custom feed...
							$wp_rewrite->flush_rules();
							
							powerpress_page_message_add_notice( sprintf(__('Podcast "%s" added, please configure your new podcast.', 'powerpress'), $feed_title) );
							$_GET['action'] = 'powerpress-editposttypefeed';
							$_GET['feed_slug'] = $feed_slug;
							$_GET['podcast_post_type'] = $post_type;
						}
					}
				}; break;
				case 'powerpress-ping-sites': {
					check_admin_referer('powerpress-ping-sites');
					
					require_once( POWERPRESS_ABSPATH . '/powerpressadmin-ping-sites.php');
					powerpressadmin_ping_sites_process();
					
					$_GET['action'] = 'powerpress-ping-sites';
				}; break;
				case 'powerpress-find-replace': {
					check_admin_referer('powerpress-find-replace');
					
					require_once( POWERPRESS_ABSPATH . '/powerpressadmin-find-replace.php');
					powerpressadmin_find_replace_process();
					
					$_GET['action'] = 'powerpress-find-replace';
				}; break;
				case 'powerpress-importpodpress': {
					check_admin_referer('powerpress-import-podpress');
					
					require_once( POWERPRESS_ABSPATH . '/powerpressadmin-podpress.php');
					powerpressadmin_podpress_do_import();
					
					$_GET['action'] = 'powerpress-podpress-epiosdes';
				}; break;
				case 'powerpress-importmt': {
					check_admin_referer('powerpress-import-mt');
					
					require_once( POWERPRESS_ABSPATH . '/powerpressadmin-mt.php');
					powerpressadmin_mt_do_import();
					
					$_GET['action'] = 'powerpress-mt-epiosdes';
				}; break;
				case 'deletepodpressdata': {
					check_admin_referer('powerpress-delete-podpress-data');
					
					require_once( POWERPRESS_ABSPATH . '/powerpressadmin-podpress.php');
					powerpressadmin_podpress_delete_data();
					
				}; break;
				case 'powerpress-category-settings': {
					// Save here!
					check_admin_referer('powerpress-category-settings');
					
					if( isset($_POST['cat_casting_podcast_feeds']) && isset($_POST['cat_casting_strict']) )
					{
						$Save = array('cat_casting_podcast_feeds'=>$_POST['cat_casting_podcast_feeds'], 'cat_casting_strict'=>$_POST['cat_casting_strict']);
						powerpress_save_settings($Save);
						powerpress_page_message_add_notice( __('Settings saved successfully.', 'powerpress') );
					}
					
				}; break;
			}
		}
		
		// Handle GET actions...
		if( isset($_GET['action'] ) )
		{
			switch( $_GET['action'] )
			{
                case 'powerpress-sync-progad': {
                    function buildRedirect($Redirects) {
                        $redirect_result = '';
                        for( $x = 3; $x >= 0; $x-- )
                        {
                            $key = sprintf('redirect%d', $x);
                            if( !empty($Redirects[ $key ]) )
                            {
                                if( preg_match('/^https?:\/\/(.*)$/', trim($Redirects[ $key ]) , $matches ) == 0 )
                                    continue;

                                $RedirectClean = $matches[1];
                                if( substr($RedirectClean, -1, 1) != '/' ) // Rediercts need to end with a slash /.
                                    $RedirectClean .= '/';

                                if( !empty($RedirectClean) )
                                {
                                    if( strpos($RedirectClean, '/') == 0 ) // Not a valid redirect URL
                                        continue;

                                    if( !strstr($redirect_result, $RedirectClean) ) // If the redirect is not already added...
                                        $redirect_result = $RedirectClean . $redirect_result;
                                }
                            }
                        }
                        return 'https://' . $redirect_result;
                    }
                    // grab the redirect url prefixes for each feed slug and make an array
                    $General = powerpress_get_settings('powerpress_general');
                    // append general redirects to each other (starting with redirect1)
                    $Redirects = array('redirect0'=>'', 'redirect1'=>'', 'redirect2'=>'', 'redirect3'=>'');
                    if( !empty($General['redirect1']) )
                        $Redirects['redirect1'] = $General['redirect1'];
                    if( !empty($General['redirect2']) )
                        $Redirects['redirect2'] = $General['redirect2'];
                    if( !empty($General['redirect3']) )
                        $Redirects['redirect3'] = $General['redirect3'];

                    // add to redirect array with key 'enclosure'
                    $main_redirect = buildRedirect($Redirects);
                    $redirect_array = array('enclosure' => $main_redirect);

                    // then append custom feed redirects to beginning of main feed redirect with _slug:enclosure for each custom feed
                    // channels
                    if (!empty($General['custom_feeds'])) {
                        foreach ($General['custom_feeds'] as $slug => $title) {
                            $Feed = get_option('powerpress_feed_' . $slug, array());
                            if (!empty($Feed['redirect'])) {
                                $Redirects['redirect0'] = $Feed['redirect'];
                                $redirect_array += array('_' . $slug . ':enclosure' => buildRedirect($Redirects));
                                $Redirects['redirect0'] = '';
                            } else {
                                $redirect_array += array('_' . $slug . ':enclosure' => $main_redirect);
                                // default stats redirect
                            }
                        }
                    }

                    // categories
                    if (!empty($General['custom_cat_feeds'])) {
                        foreach ($General['custom_cat_feeds'] as $idx => $id) {
                            $category = get_category($id);
                            // $category['slug']
                            $Feed = get_option('powerpress_cat_feed_' . $id, array());
                            if (!empty($Feed['redirect'])) {
                                $Redirects['redirect0'] = $Feed['redirect'];
                                $redirect_array += array('_' . $category->slug . ':enclosure' => buildRedirect($Redirects));
                                $Redirects['redirect0'] = '';
                            } else {
                                $redirect_array += array('_' . $category->slug . ':enclosure' => $main_redirect);
                                // default stats redirect
                            }
                        }
                    }

                    // taxonomies
                    $PowerPressTaxonomies = get_option('powerpress_taxonomy_podcasting', array());
                    if (!empty($PowerPressTaxonomies)) {
                        foreach ($PowerPressTaxonomies as $tt_id => $null) {

                            $taxonomy_type = '';
                            $term_ID = '';

                            global $wpdb;
                            $term_info = $wpdb->get_results("SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = $tt_id", ARRAY_A);
                            if (!empty($term_info[0]['term_id'])) {
                                $term_ID = $term_info[0]['term_id'];
                                $taxonomy_type = $term_info[0]['taxonomy'];
                            } else {
                                continue; // we didn't find this taxonomy relationship
                            }

                            $Feed = powerpress_get_settings('powerpress_taxonomy_' . $tt_id);
                            $term_object = get_term( $term_ID, $taxonomy_type, OBJECT, 'edit');
                            if (!empty($Feed['redirect'])) {
                                $Redirects['redirect0'] = $Feed['redirect'];
                                $redirect_array += array('_' . $term_object->slug . ':enclosure' => buildRedirect($Redirects));
                                $Redirects['redirect0'] = '';
                            } else {
                                $redirect_array += array('_' . $term_object->slug . ':enclosure' => $main_redirect);
                                // default stats redirect
                            }
                        }
                    }

                    // post types
                    $post_types = powerpress_admin_get_post_types(false);
                    if (!empty($post_types)) {
                        foreach ($post_types as $null => $post_type) {
                            $PostTypeSettingsArray = get_option('powerpress_posttype_' . $post_type, array());
                            if (empty($PostTypeSettingsArray))
                                continue;

                            foreach ($PostTypeSettingsArray as $feed_slug => $Feed) {
                                if (!empty($Feed['redirect'])) {
                                    $Redirects['redirect0'] = $Feed['redirect'];
                                    $redirect_array += array('_' . $feed_slug . ':enclosure' => buildRedirect($Redirects));
                                    $Redirects['redirect0'] = '';
                                } else {
                                    $redirect_array += array('_' . $feed_slug . ':enclosure' => $main_redirect);
                                    // default stats redirect
                                }
                            }
                        }
                    }

                    // figure out which shows we are enabling/disabling
                    $progad_error = '';
                    $progad_enable_urls = array();
                    $progad_disable_urls = array();
                    $auth = new PowerPressAuth();
                    $accessToken = powerpress_getAccessToken();
                    $req_url = sprintf('/2/media/prog_ad_status.json?cache=' . md5(rand(0, 999) . time()));
                    $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '?' . POWERPRESS_BLUBRRY_API_QSA : '');
                    $req_url .= (defined('POWERPRESS_PUBLISH_PROTECTED') ? '&protected=true' : '');
                    $progad_enabled_shows = $auth->api($accessToken, $req_url, array(), false, 60 * 30);
                    if (!$progad_enabled_shows) {
                        $progad_error = $auth->getLastError();
                    }
                    $past_shows_with_progad = get_option('pp_programmatic_enabled_shows');
                    if (!empty($past_shows_with_progad) && !empty($progad_enabled_shows['programs'])) {
                        $shows_to_enable = array_diff($progad_enabled_shows['programs'], $past_shows_with_progad);
                        $shows_to_disable = array_diff($past_shows_with_progad, $progad_enabled_shows['programs']);
                    } elseif (!empty($past_shows_with_progad) && empty($progad_enabled_shows['programs'])) {
                        $shows_to_disable = $past_shows_with_progad;
                    } elseif (!empty($progad_enabled_shows['programs']) && empty($past_shows_with_progad)) {
                        $shows_to_enable = $progad_enabled_shows['programs'];
                    }
                    update_option('pp_programmatic_enabled_shows', $progad_enabled_shows['programs']);

                    // use the API to get associated URLs for all URLs in any program whose ads were just enabled
                    if (!empty($shows_to_enable)) {
                        foreach ($shows_to_enable as $idx => $keyword) {
                            $req_url = sprintf('/2/media/' . $keyword . '/prog_ad_urls.json?cache=' . md5(rand(0, 999) . time()));
                            if (defined('POWERPRESS_PROGRAMMATIC_FIX')) {
                                $req_url .= '&pp_first_release_fix=true';
                            }
                            $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '?' . POWERPRESS_BLUBRRY_API_QSA : '');
                            $req_url .= (defined('POWERPRESS_PUBLISH_PROTECTED') ? '&protected=true' : '');
                            $result_prog = $auth->api($accessToken, $req_url, array(), false, 60 * 30);
                            if (isset($result_prog['urls']) && is_array($result_prog['urls'])) {
                                foreach ($result_prog['urls'] as $i => $url_pair) {
                                    // add the redirect to the key before adding this pair
                                    $progad_enable_urls += $url_pair;
                                }
                            } elseif (isset($result_prog['message']) && $result_prog['message'] == 'no media') {
                                // no error--continue
                            }
                            else {
                                $progad_error = $auth->getLastError();
                            }
                        }
                    }

                    // use the API to get associated URLs for all URLs in any program whose ads were just disabled
                    if (!empty($shows_to_disable)) {
                        foreach ($shows_to_disable as $idx => $keyword) {
                            $req_url = sprintf('/2/media/' . $keyword . '/prog_ad_urls.json?disable=true&cache=' . md5(rand(0, 999) . time()));
                            $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '?' . POWERPRESS_BLUBRRY_API_QSA : '');
                            $req_url .= (defined('POWERPRESS_PUBLISH_PROTECTED') ? '&protected=true' : '');
                            $result_prog = $auth->api($accessToken, $req_url, array(), false, 60 * 30);
                            $progad_error = $auth->getLastError();
                            if (isset($result_prog['urls']) && is_array($result_prog['urls'])) {
                                foreach ($result_prog['urls'] as $i => $url_pair) {
                                    // add the redirect to the key before adding this pair
                                    $progad_disable_urls += $url_pair;
                                }
                            } elseif (isset($result_prog['message']) && $result_prog['message'] == 'no media') {
                                // no error--continue
                            }
                            else {
                                $progad_error = $auth->getLastError();
                            }
                        }
                    }

                    // query the wordpress database to match up the URLs that we need to update
                    global $wpdb;
                    $query = "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE \"%enclosure\"";
                    $results_data = $wpdb->get_results($query, ARRAY_A);
                    foreach ($results_data as $idx => $data) {
                        $meta_parts = explode("\n", $data['meta_value']);

                        if (strpos($meta_parts[0], 'ins.blubrry.com')) {
                            $parts_array = explode('ins.blubrry.com', $meta_parts[0]);
                        } else if (strpos($meta_parts[0], 'content3.blubrry.biz')) {
                            $parts_array = explode('content3.blubrry.biz', $meta_parts[0]);
                        } else if (strpos($meta_parts[0], 'mc.blubrry.com')) {
                            $parts_array = explode('mc.blubrry.com', $meta_parts[0]);
                        } elseif (strpos($meta_parts[0], 'content.blubrry.com')) {
                            $parts_array = explode('content.blubrry.com', $meta_parts[0]);
                        } else {
                            // not Blubrry hosted
                            continue;
                        }
                        $url_without_prefix = $parts_array[1];
                        $parts_drop_qs = explode('?', $url_without_prefix);
                        if (!empty($progad_enable_urls) && array_key_exists($parts_drop_qs[0], $progad_enable_urls)) {
                            // now, if they have a redirect for the feed that this url is in, we need to replace the https://media.blubrry.com/{keyword}/ with those redirects
                            $progad_url_with_pp_redirect = preg_replace('#https://media.blubrry.com/(.*)/#U', $redirect_array[$data['meta_key']], $progad_enable_urls[$parts_drop_qs[0]]);
                            // replace the url in the meta_parts array, implode it back together, and update the program meta
                            $meta_parts[0] = $progad_url_with_pp_redirect;
                            $new_meta_value = implode("\n", $meta_parts);
                            update_post_meta($data['post_id'], $data['meta_key'], $new_meta_value);
                        } else if (!empty($progad_disable_urls) && array_key_exists($parts_drop_qs[0], $progad_disable_urls)) {
                            $hosting_url_with_pp_redirect = preg_replace('#http(s?)://#U', $redirect_array[$data['meta_key']], $progad_disable_urls[$parts_drop_qs[0]]);
                            // replace the url in the meta_parts array, implode it back together, and update the program meta
                            $meta_parts[0] = $hosting_url_with_pp_redirect;
                            $new_meta_value = implode("\n", $meta_parts);
                            update_post_meta($data['post_id'], $data['meta_key'], $new_meta_value);
                        }
                    }
                    if ($progad_error) {
                        powerpress_add_error("Error syncing Programmatic Advertising Settings: " . $progad_error);
                    } else {
                        powerpress_page_message_add_notice("Successfully synced Programmatic Advertising Settings from Blubrry.", 'inline', false);
                    }
                }; break;
				case 'powerpress-enable-categorypodcasting': {
					check_admin_referer('powerpress-enable-categorypodcasting');
					
					$Settings = get_option('powerpress_general', array());
					$Settings['cat_casting'] = 1;
					powerpress_save_settings($Settings);
					
					wp_redirect('edit-tags.php?taxonomy=category&message=3');
					exit;
					
				}; break;
				case 'powerpress-addcategoryfeed': {
					check_admin_referer('powerpress-add-taxonomy-feed');
					$cat_ID = intval($_GET['cat']);
					
					$Settings = get_option('powerpress_general', array());
					$category = get_category($cat_ID);
					if( $category == false )
					{
						powerpress_page_message_add_error( __('Error obtaining category information.', 'powerpress') );
					}
					else
					{
						if( empty($Settings['custom_cat_feeds']) || !is_array($Settings['custom_cat_feeds']) )
							$Settings['custom_cat_feeds'] = array();
						
						if( !in_array($cat_ID, $Settings['custom_cat_feeds']) )
						{
							$Settings['custom_cat_feeds'][] = $cat_ID;
							powerpress_save_settings($Settings);
						}
					
						powerpress_page_message_add_notice( __('Please configure your category podcast feed now.', 'powerpress') );
						
						$_GET['action'] = 'powerpress-editcategoryfeed';
						$_GET['cat'] = $cat_ID;
					}
				}; break;
				case 'powerpress-delete-feed': {
					$delete_slug = $_GET['feed_slug'];
					$force_deletion = !empty($_GET['force']);
					check_admin_referer('powerpress-delete-feed-'.$delete_slug);
					
					$Episodes = powerpress_admin_episodes_per_feed($delete_slug);
					
					if( false && $delete_slug == 'podcast' && $force_deletion == false ) // Feature disabled, you can now delete podcast specific settings
					{
						powerpress_page_message_add_error( __('Cannot delete default podcast feed.', 'powerpress') );
					}
					else if( $delete_slug != 'podcast' && $Episodes > 0 && $force_deletion == false )
					{
						powerpress_page_message_add_error( sprintf(__('Cannot delete feed. Feed contains %d episode(s).', 'powerpress'), $Episodes) );
					}
					else
					{
						$Settings = get_option('powerpress_general', array());
						unset($Settings['custom_feeds'][ $delete_slug ]);
						powerpress_save_settings($Settings); // Delete the feed from the general settings
						delete_option('powerpress_feed_'.$delete_slug); // Delete the actual feed settings
						
						// Now we need to update the rewrite cso the cached rules are up to date
						if ( in_array($delete_slug, $wp_rewrite->feeds))
						{
							$index = array_search($delete_slug, $wp_rewrite->feeds);
							if( $index !== false )
								unset($wp_rewrite->feeds[$index]); // Remove the old feed
						}
					
						// Remove feed function hook
						$hook = 'do_feed_' . $delete_slug;
						remove_action($hook, $hook, 10, 1); // This may not be necessary
						$wp_rewrite->flush_rules(); // This is definitely necessary
						
						powerpress_page_message_add_notice( __('Feed deleted successfully.', 'powerpress') );
					}
				}; break;
				case 'powerpress-delete-category-feed': {
					$cat_ID = intval($_GET['cat']);
					check_admin_referer('powerpress-delete-category-feed-'.$cat_ID);
					
					$Settings = get_option('powerpress_general', array());
					$key = array_search($cat_ID, $Settings['custom_cat_feeds']);
					if( $key !== false )
					{
						unset( $Settings['custom_cat_feeds'][$key] );
						powerpress_save_settings($Settings); // Delete the feed from the general settings
					}
					delete_option('powerpress_cat_feed_'.$cat_ID); // Delete the actual feed settings
					
					powerpress_page_message_add_notice( __('Removed podcast settings for category feed successfully.', 'powerpress') );
				}; break;
				case 'powerpress-delete-taxonomy-feed': {
					$tt_ID = intval($_GET['ttid']);
					check_admin_referer('powerpress-delete-taxonomy-feed-'.$tt_ID);
					
					$Settings = get_option('powerpress_taxonomy_podcasting', array());
					if( !empty($Settings[ $tt_ID ]) )
					{
						unset( $Settings[ $tt_ID ] );
						powerpress_save_settings($Settings, 'powerpress_taxonomy_podcasting'); // Delete the feed from the general settings
					}
					delete_option('powerpress_taxonomy_'.$tt_ID); // Delete the actual feed settings
					
					powerpress_page_message_add_notice( __('Removed podcast settings for term successfully.', 'powerpress') );
				}; break;
				case 'powerpress-delete-posttype-feed': {
				
					// check admin referer prevents xss
					$feed_slug = esc_attr($_GET['feed_slug']);
					$post_type = esc_attr($_GET['podcast_post_type']);
					check_admin_referer('powerpress-delete-posttype-feed-'.$post_type .'_'.$feed_slug);
			
					$Settings = get_option('powerpress_posttype_'.$post_type, array());
					if( !empty($Settings[ $feed_slug ]) )
					{
						unset( $Settings[ $feed_slug ] );
						update_option('powerpress_posttype_'.$post_type,  $Settings);
						//powerpress_save_settings($Settings, 'powerpress_posttype_'.$post_type); // Delete the feed from the general settings
					}
							
					powerpress_page_message_add_notice( __('Removed podcast settings for post type successfully.', 'powerpress') );
				}; break;
				case 'powerpress-podpress-settings': {
					check_admin_referer('powerpress-podpress-settings');
					
					// Import settings here..
					if( powerpress_admin_import_podpress_settings() )
						powerpress_page_message_add_notice( __('Podpress settings imported successfully.', 'powerpress') );
					else
						powerpress_page_message_add_error( __('No Podpress settings found.', 'powerpress') );
					
				}; break;
				case 'powerpress-podcasting-settings': {
					check_admin_referer('powerpress-podcasting-settings');
					
					// Import settings here..
					if( powerpress_admin_import_podcasting_settings() )
						powerpress_page_message_add_notice( __('Settings imported from the plugin "Podcasting" successfully.', 'powerpress') );
					else
						powerpress_page_message_add_error( __('No settings found for the plugin "Podcasting".', 'powerpress') );
					
				}; break;
				case 'powerpress-add-caps': {
					check_admin_referer('powerpress-add-caps');
					
					$users = array('administrator','editor', 'author'); // , 'contributor', 'subscriber');
					foreach( $users as $null => $user )
					{
						$role = get_role($user);
						if( !empty($role) )
						{
							if( !$role->has_cap('edit_podcast') )
								$role->add_cap('edit_podcast');
							if( $user == 'administrator' && !$role->has_cap('view_podcast_stats') )
								$role->add_cap('view_podcast_stats');
						}
					}
					
					$General = array('use_caps'=>true);
					powerpress_save_settings($General);
					powerpress_page_message_add_notice( __('PowerPress Roles and Capabilities added to WordPress Blog.', 'powerpress') );
					
				}; break;
				case 'powerpress-remove-caps': {
					check_admin_referer('powerpress-remove-caps');
					
					$users = array('administrator','editor', 'author', 'contributor', 'subscriber');
					foreach( $users as $null => $user )
					{
						$role = get_role($user);
						if( !empty($role) )
						{
							if( $role->has_cap('edit_podcast') )
								$role->remove_cap('edit_podcast');
							if( $role->has_cap('view_podcast_stats') )
								$role->remove_cap('view_podcast_stats');
						}
					}
					$General = array('use_caps'=>false);
					powerpress_save_settings($General);
					powerpress_page_message_add_notice( __('PowerPress Roles and Capabilities removed from WordPress Blog', 'powerpress') );
					
				}; break;
				case 'powerpress-add-feed-caps': {
					check_admin_referer('powerpress-add-feed-caps');
					
					$ps_role = get_role('premium_subscriber');
					if( empty($ps_role) )
					{
						add_role('premium_subscriber', __('Premium Subscriber', 'powerpress'));
						$ps_role = get_role('premium_subscriber');
						$ps_role->add_cap('read');
						$ps_role->add_cap('premium_content');
					}
					
					$users = array('administrator','editor', 'author'); // , 'contributor', 'subscriber');
					foreach( $users as $null => $user )
					{
						$role = get_role($user);
						if( !empty($role) )
						{
							if( !$role->has_cap('premium_content') )
								$role->add_cap('premium_content');
						}
					}
					
					$General = array('premium_caps'=>true);
					powerpress_save_settings($General);
					powerpress_page_message_add_notice( __('Podcast Password Protection Capabilities for Custom Channel Feeds added successfully.', 'powerpress') );
					
				}; break;
				case 'powerpress-remove-feed-caps': {
					check_admin_referer('powerpress-remove-feed-caps');
					
					$users = array('administrator','editor', 'author', 'contributor', 'subscriber', 'premium_subscriber', 'powerpress');
					foreach( $users as $null => $user )
					{
						$role = get_role($user);
						if( !empty($role) )
						{
							if( $role->has_cap('premium_content') )
								$role->remove_cap('premium_content');
						}
					}
					
					remove_role('premium_subscriber');
					
					$General = array('premium_caps'=>false);
					powerpress_save_settings($General);
					powerpress_page_message_add_notice( __('Podcast Password Protection Capabilities for Custom Channel Feeds removed successfully.', 'powerpress') );
					
				}; break;
				case 'powerpress-clear-update_plugins': {
					check_admin_referer('powerpress-clear-update_plugins');
					
					delete_option('update_plugins'); // OLD method
					delete_option('_site_transient_update_plugins'); // New method
					powerpress_page_message_add_notice( sprintf( __('Plugins Update Cache cleared successfully. You may now to go the %s page to see the latest plugin versions.', 'powerpress'), '<a href="'. admin_url() .'plugins.php" title="'.  __('Manage Plugins', 'powerpress') .'">'.  __('Manage Plugins', 'powerpress') .'</a>'), 'inline', false );
					
				}; break;
				case 'powerpress-ios11-fields': {
					check_admin_referer('powerpress-ios11-fields');
					
					$General = array('ios11_fields'=> (!empty($_REQUEST['variation'])? $_REQUEST['variation'] : '0') );
					powerpress_save_settings($General);
					powerpress_page_message_add_notice( 'iOS 11 program level fields changed.' );
				}; break;
			}
		}
		
		if( isset($_REQUEST['action']) )
		{
			switch( $_REQUEST['action'] )
			{
				case 'powerpress-migrate-media': {
					
					require_once( POWERPRESS_ABSPATH . '/powerpressadmin-migrate.php');
					powerpress_admin_migrate_request();
				
				}; break;
			}
		}

        // check for ongoing migration, if one exisits, call the function to display
        $migrationInprogress = get_option('powerpress_migrate_queued', array());
        $migrationComplete = get_option('powerpress_migrate_completed', array());
        $migrationStatus = get_option('powerpress_migrate_status', array());
        $migrationResults = get_option('powerpress_migrate_results', array());
        $oneMonthAgo = strtotime('-1 month', current_time('timestamp'));

        // disable the cron task if:
        // - have have the wp-option 'powerpress_migrate_completed', they have completed all three migration steps
        // - the migration is over a month old
        // - they do not have the migration complete option but have the results option (older migrations)
        if (!empty($migrationComplete) || (!empty($migrationStatus) && $migrationStatus['updated_timestamp'] < $oneMonthAgo) || (empty($migrationComplete) && !empty($migrationResults))) {
            // unregister cron task if it still exists
            $timestamp = wp_next_scheduled( 'powerpress_admin_migration_hook' );
            wp_unschedule_event( $timestamp, 'powerpress_admin_migration_hook' );
        } elseif (!empty($migrationInprogress) && $_GET['page'] != 'powerpress/powerpressadmin_migrate.php') {
            // handles in progress, and update episode directive
            powerpress_admin_migration_notice();
        }
    }
	
	// Handle edit from category page
	if( isset($_POST['from_categories']) )
	{
		wp_redirect('edit-tags.php?taxonomy=category&message=3');
		exit;
	}
	
	add_filter( 'plugin_row_meta', 'powerpress_plugin_row_meta', 10, 2);
	
	// Hnadle player settings
	require_once( POWERPRESS_ABSPATH .'/powerpressadmin-player.php');
	powerpress_admin_players_init();

	// Handle notices
	require_once( POWERPRESS_ABSPATH .'/powerpressadmin-notifications.php');
}

function delete_post_refresh_player($postId) {
    $post_status = get_post_status($postId);
    $post_meta = get_post_meta($postId);

    if ($post_status == 'publish') {
        foreach ($post_meta as $key => $value) {
            if (preg_match('/enclosure/i', $key) && count($value) > 0) {
                $EnclosureData = explode("\n", $value[0], 4);
                $mediaURL = $EnclosureData[0];
                $GeneralSettingsTemp = powerpress_get_settings('powerpress_general', false);
                if( !empty($mediaURL) && !empty($GeneralSettingsTemp['blubrry_hosting']) && $GeneralSettingsTemp['blubrry_hosting'] !== 'false' ) {
                    require_once(POWERPRESS_ABSPATH . '/powerpressadmin-auth.class.php');
                    $auth = new PowerPressAuth();
                    $json_data = false;
                    $creds = get_option('powerpress_creds');
                    if ($creds) {
                        $post_array = array('media_url' => urlencode($mediaURL));
                        $accessToken = powerpress_getAccessToken();
                        $req_url = sprintf('/2/episode/%s/delete/', $GeneralSettingsTemp['blubrry_program_keyword']);
                        $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '?' . POWERPRESS_BLUBRRY_API_QSA : '');
                        $results = $auth->api($accessToken, $req_url, $post_array, false, 60 * 30);
                    }
                }
            }
        }
    }
}

add_action('admin_init', 'powerpress_admin_init');
add_action('wp_trash_post', 'delete_post_refresh_player');

function powerpress_admin_notices()
{
	$errors = get_option('powerpress_errors');
	if( !empty($errors) )
	{
		if( !delete_option('powerpress_errors') ) {
			// If for some reason we cannot delete this record, maybe we can at least update it with a blank value...
			update_option('powerpress_errors', '');
		}
		
		// Clear the SG cachepress plugin:
		if (function_exists('sg_cachepress_purge_cache')) { sg_cachepress_purge_cache(); }
		
		foreach( $errors as $null => $error )
		{
?>
<div class="updated"><p style="line-height: 125%;"><strong><?php echo $error; ?></strong></p></div>
<?php
		}
	}
}

add_action( 'admin_notices', 'powerpress_admin_notices' );

function powerpress_save_settings($SettingsNew=false, $field = 'powerpress_general' )
{

	if(  $field == 'powerpress_taxonomy_podcasting' || $field == 'powerpress_itunes_featured' ) { // No merging settings for these fields...
		update_option($field,  $SettingsNew);
		return;
	}
	// Save general settings
	if( $SettingsNew )
	{
		
		$Settings = get_option($field);
		if( !is_array($Settings) )
			$Settings = array();
		foreach( $SettingsNew as $key => $value ) {
			$Settings[$key] = $value;
		}
		if( $field == 'powerpress_general' && !isset($Settings['timestamp']) )
			$Settings['timestamp'] = time();
			
		// Special case fields, if they are empty, we can delete them., this will keep the Settings array uncluttered
		if( isset($Settings['feed_links']) && $Settings['feed_links'] == 0 ) // If set to default value, no need to save it in the database
			unset($Settings['feed_links']);
        if( isset($Settings['suppress_unused_item_tags']) && $Settings['suppress_unused_item_tags'] == 0 ) // If set to default value, no need to save it in the database
            unset($Settings['suppress_unused_item_tags']);

		// We can unset settings that are set to their defaults to save database size...
		if( $field == 'powerpress_general' )
		{
		    if( isset($SettingsNew['new_episode_box_flag'])) {
                /* Switch the settings over to the actual field name (to fix FCGI mode problem with older versions of PHP.
                if (isset($SettingsNew['ebititle'])) {
                    if ($SettingsNew['ebititle'] == 'false') {
                        $Settings['new_episode_box_itunes_title'] = 2;
                    } else {
                        $Settings['new_episode_box_itunes_title'] = 1;
                        $SettingsNew['new_episode_box_itunes_title'] = 1;
                    }
                    unset($Settings['ebititle']);
                }

                if (isset($SettingsNew['ebinst'])) {
                    if ($SettingsNew['ebinst'] == 'false') {
                        $Settings['new_episode_box_itunes_nst'] = 2;
                    } else {
                        $Settings['new_episode_box_itunes_nst'] = 1;
                        $SettingsNew['new_episode_box_itunes_nst'] = 1;
                    }
                    unset($Settings['ebinst']);
                }*/

                if (!isset($SettingsNew['new_episode_box_embed']))
                    $Settings['new_episode_box_embed'] = 2;
                if (!isset($SettingsNew['new_embed_replace_player']))
                    $Settings['new_embed_replace_player'] = 2;
                if (!isset($SettingsNew['new_episode_box_no_player']))
                    $Settings['new_episode_box_no_player'] = 2;
                if (!isset($SettingsNew['new_episode_box_no_links']))
                    $Settings['new_episode_box_no_links'] = 2;
                if (!isset($SettingsNew['new_episode_box_no_player_and_links']))
                    $Settings['new_episode_box_no_player_and_links'] = 2;
                if (!isset($SettingsNew['new_episode_box_cover_image']))
                    $Settings['new_episode_box_cover_image'] = 2;
                if (!isset($SettingsNew['new_episode_box_player_size']))
                    $Settings['new_episode_box_player_size'] = 2;
                if (!isset($SettingsNew['new_episode_box_subtitle']))
                    $Settings['new_episode_box_subtitle'] = 2;
                if (!isset($SettingsNew['new_episode_box_summary']))
                    $Settings['new_episode_box_summary'] = 2;
                if (!isset($SettingsNew['new_episode_box_author']))
                    $Settings['new_episode_box_author'] = 2;
                if (!isset($SettingsNew['new_episode_box_explicit']))
                    $Settings['new_episode_box_explicit'] = 2;
                if (!isset($SettingsNew['new_episode_box_pci']))
                    $Settings['new_episode_box_pci'] = 2;
                if (!isset($SettingsNew['new_episode_box_block']))
                    $Settings['new_episode_box_block'] = 2;
                if (!isset($SettingsNew['new_episode_box_itunes_image']))
                    $Settings['new_episode_box_itunes_image'] = 2;
                if (!isset($SettingsNew['new_episode_box_order']))
                    $Settings['new_episode_box_order'] = 2;
                if (!isset($SettingsNew['new_episode_box_itunes_title']))
                    $Settings['new_episode_box_itunes_title'] = 2;
                if (!isset($SettingsNew['new_episode_box_itunes_nst']))
                    $Settings['new_episode_box_itunes_nst'] = 2;
                if (!isset($SettingsNew['new_episode_box_gp_explicit']))
                    $Settings['new_episode_box_gp_explicit'] = 2;
                if (!isset($SettingsNew['new_episode_box_feature_in_itunes']))
                    $Settings['new_episode_box_feature_in_itunes'] = 2;
            } elseif(isset($SettingsNew['pp-gen-settings-tabs'])) {
                if (!isset($SettingsNew['skip_to_episode_settings']) || empty($SettingsNew['skip_to_episode_settings']))
                    unset($Settings['skip_to_episode_settings']);
                if (!isset($SettingsNew['display_player_excerpt']) || empty($SettingsNew['display_player_excerpt']))
                    unset($Settings['display_player_excerpt']);
                if (!isset($SettingsNew['hide_player_more']) || empty($SettingsNew['hide_player_more']))
                    unset($Settings['hide_player_more']);
                if (!isset($SettingsNew['podcast_embed']) || empty($SettingsNew['podcast_embed']))
                    unset($Settings['podcast_embed']);
                if (!isset($SettingsNew['subscribe_links']) || empty($SettingsNew['subscribe_links']))
                    unset($Settings['subscribe_links']);
                if (!isset($SettingsNew['new_window_no_factor']) || empty($SettingsNew['new_window_no_factor']))
                    unset($Settings['new_window_no_factor']);
            } elseif( isset($SettingsNew['powerpress_bplayer_settings'])) {
		        unset($Settings['powerpress_bplayer_settings']);
                if (!isset($SettingsNew['new_episode_box_itunes_image']) || empty($SettingsNew['new_episode_box_itunes_image']))
                    $Settings['new_episode_box_itunes_image'] = 2;
                if (isset($SettingsNew['bp_episode_image']) && empty($SettingsNew['bp_episode_image']))
                    unset($Settings['bp_episode_image']);
            }


			if( isset($Settings['videojs_css_class']) && empty($Settings['videojs_css_class']) )
				unset($Settings['videojs_css_class']);
			if( isset($Settings['cat_casting']) && empty($Settings['cat_casting']) )
                unset($Settings['cat_casting']);
			if( isset($Settings['posttype_podcasting']) && empty($Settings['posttype_podcasting']) )
				unset($Settings['posttype_podcasting']);
			if( isset($Settings['taxonomy_podcasting']) && empty($Settings['taxonomy_podcasting']) )
				unset($Settings['taxonomy_podcasting']);
			if( isset($Settings['playlist_player']) && empty($Settings['playlist_player']) )
				unset($Settings['playlist_player']);	
			if( isset($Settings['seo_feed_title']) && empty($Settings['seo_feed_title']) )
				unset($Settings['seo_feed_title']);
			if( isset($Settings['subscribe_feature_email']) && empty($Settings['subscribe_feature_email']) )
				unset($Settings['subscribe_feature_email']);
			if( isset($Settings['poster_image_video']) && empty($Settings['poster_image_video']) )
				unset($Settings['poster_image_video']);
			if( isset($Settings['poster_image_audio']) && empty($Settings['poster_image_audio']) )
				unset($Settings['poster_image_audio']);
			if( isset($Settings['itunes_image_audio']) && empty($Settings['itunes_image_audio']) )
				unset($Settings['itunes_image_audio']);
            if( isset($Settings['network_mode']) && empty($Settings['network_mode']) )
				unset($Settings['network_mode']);
            if( isset($Settings['use_caps']) && empty($Settings['use_caps']) )
				unset($Settings['use_caps']);
		}
		else // Feed or player settings...
		{
			if( isset($Settings['itunes_block'] ) && $Settings['itunes_block'] == 0 )
				unset($Settings['itunes_block']);
			if( isset($Settings['itunes_complete'] ) && $Settings['itunes_complete'] == 0 )
				unset($Settings['itunes_complete']);
			if( isset($Settings['maximize_feed'] ) && $Settings['maximize_feed'] == 0 )
				unset($Settings['maximize_feed']);
            if( isset($Settings['unlock_podcast'] ) && $Settings['unlock_podcast'] == 0 )
                unset($Settings['unlock_podcast']);
			if( isset($Settings['donate_link'] ) && $Settings['donate_link'] == 0 )
				unset($Settings['donate_link']);
			if( empty($Settings['donate_url']) )
				unset($Settings['donate_url']);
			if( empty($Settings['donate_label']) )
				unset($Settings['donate_label']);
			if( isset($Settings['allow_feed_comments'] ) && $Settings['allow_feed_comments'] == 0 )
				unset($Settings['allow_feed_comments']);	
			if( empty($Settings['episode_itunes_image']) )
				unset($Settings['episode_itunes_image']);
		}

		update_option($field,  $Settings);
	}
}

function powerpress_get_settings($field, $for_editing=true)
{
	$Settings = get_option($field, array());
	if( $for_editing )
		$Settings = powerpress_htmlspecialchars($Settings);
	return $Settings;
}

function powerpress_htmlspecialchars($data)
{
	if( !$data )
		return $data;
	if( is_array($data) )
	{
		foreach( $data as $key => $value )
		{
			if( $key == 'itunes_summary' )
				continue; // Skip this one as we escape it in the form.
			if( is_array($value) )
				$data[$key] = powerpress_htmlspecialchars($value);
			else
				$data[$key] = htmlspecialchars($value);
		}
		reset($data);
	}
	return $data;
}

function powerpress_stripslashes($data)
{
	if( !$data )
		return $data;
	
	if( !is_array($data) )
		return stripslashes($data);
	
	foreach( $data as $key => $value )
	{
		if( is_array($value) )
			$data[$key] = powerpress_stripslashes($value);
		else
			$data[$key] = stripslashes($value);
	}
	reset($data);
	return $data;
}

function powerpress_admin_get_post_types($capability_type = 'post')
{
	if( !function_exists('get_post_types') || !function_exists('get_post_type_object') )
		return array($capability_type);
		
	$return = array();
	$post_types = get_post_types();
	foreach( $post_types as $index => $post_type )
	{
		if( $post_type == 'redirect_rule' || $post_type == 'attachment' || $post_type == 'nav_menu_item' || $post_type == 'revision' || $post_type == 'action' )
			continue;
		if( $capability_type !== false )
		{
			$object = get_post_type_object($post_type);
			if( $object && $object->capability_type == $capability_type )
				$return[] = $post_type;
		}
		else
		{
			$return[] = $post_type;
		}
	}
	return $return;
}

/* Rebuild powerpress_posttype_podcasting field*/
function powerpress_rebuild_posttype_podcasting()
{
	// Loop through all the posttype podcasting settings, save them into a field
	// array( feed-slugs => array('posttype1'=>'post type 1 title', 'posttype2'=>post type 2 title', ...) );
	$post_types = get_post_types();
	$FeedSlugPostTypeArray = array();
	foreach( $post_types as $index => $post_type )
	{
		$PostTypeSettingsArray = get_option('powerpress_posttype_'. $post_type, array() );
		if( empty($PostTypeSettingsArray ))
			continue;
		
		foreach( $PostTypeSettingsArray as $feed_slug => $PostTypeSettings )
		{
			$FeedSlugPostTypeArray[ $feed_slug ][ $post_type ] = ( empty($PostTypeSettings['title'])? $feed_slug : $PostTypeSettings['title'] );
		}
	}
	update_option('powerpress_posttype-podcasting', $FeedSlugPostTypeArray);
}

function powerpress_admin_menu()
{
	$Powerpress = get_option('powerpress_general', array());
	
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
	{
		// CRAP
	}
	else if( empty($Powerpress['use_caps']) || current_user_can('edit_podcast') )
	{ // Otherwise we're using a version of wordpress that is not supported.
		
		require_once( POWERPRESS_ABSPATH .'/views/episode-box.php');
		$FeedSlugPostTypesArray = array();
		if( !empty($Powerpress['posttype_podcasting']) )
		{
			$FeedSlugPostTypesArray = get_option('powerpress_posttype-podcasting');
				if( empty($FeedSlugPostTypesArray) )
					$FeedSlugPostTypesArray = array();
		}
		
		if( !defined('POWERPRESS_POST_TYPES') )
		{
			$page_types = array('page'); // Only apply to default pages
			if( empty($Powerpress['posttype_podcasting']) )
				$page_types = powerpress_admin_get_post_types('page'); // Get pages by capability type
			
			foreach( $page_types as $null => $page_type )
			{
				if( empty($FeedSlugPostTypesArray[ 'podcast' ][ $page_type ]) )
					add_meta_box('powerpress-podcast', __('Podcast Episode', 'powerpress'), 'powerpress_meta_box', $page_type, 'normal');
			}
			
			$post_types = array('post'); // Only apply to default posts if post type podcasting is disabled
			if( empty($Powerpress['posttype_podcasting']) )
				$post_types = powerpress_admin_get_post_types('post'); // Get pages by capability type
		}
		else
		{
			$post_type_string = str_replace(' ', '',POWERPRESS_POST_TYPES); // Get all the spaces out
			$post_types = explode(',', $post_type_string);
		}
		
		if( !empty($Powerpress['posttype_podcasting']) )
		{
			add_meta_box('powerpress-podcast', __('Podcast Episode (default)', 'powerpress'), 'powerpress_meta_box', 'post', 'normal'); // Default podcast box for post type 'post'
			
			$FeedSlugPostTypesArray = get_option('powerpress_posttype-podcasting');
			if( empty($FeedSlugPostTypesArray) )
				$FeedSlugPostTypesArray = array();

			foreach( $FeedSlugPostTypesArray as $feed_slug => $FeedSlugPostTypes )
			{
				foreach( $FeedSlugPostTypes as $post_type => $type_title )
				{
					if ( $feed_slug != 'podcast' || $post_type != 'post' ) // No the default podcast feed
					{
						$feed_title = $type_title;
						if( empty($feed_title) )
							$feed_title = $feed_slug;
							//echo (" $feed_slug ");
						add_meta_box('powerpress-'.$feed_slug,  __('Podcast Episode', 'powerpress') .': '.$feed_title, 'powerpress_meta_box', $post_type, 'normal');
					}
				}
			}
		}
		
		if( isset($Powerpress['custom_feeds']) )
		{
			$FeedDefaultPodcast = get_option('powerpress_feed_podcast', array());
			
			foreach( $post_types as $null => $post_type )
			{
				// Make sure this post type can edit the default podcast channel...
				if( !empty($FeedDefaultPodcast['custom_post_type']) && $FeedDefaultPodcast['custom_post_type'] != $post_type )
					continue;
					
				if( empty($FeedSlugPostTypesArray[ 'podcast' ][ $post_type ]) )
					add_meta_box('powerpress-podcast', __('Podcast Episode (default)', 'powerpress'), 'powerpress_meta_box', $post_type, 'normal');
			}
			
			foreach( $Powerpress['custom_feeds'] as $feed_slug => $feed_title )
			{
				if( $feed_slug == 'podcast' )
					continue;
				
				$FeedCustom = get_option('powerpress_feed_'.$feed_slug, array());
				$feed_slug = esc_attr($feed_slug);
				
						
				reset($post_types);
				foreach( $post_types as $null => $post_type )
				{
					// Make sure this post type can edit the default podcast channel...
					if( !empty($FeedCustom['custom_post_type']) && $FeedCustom['custom_post_type'] != $post_type )
						continue;
					
					if( empty($FeedSlugPostTypesArray[ $feed_slug ][ $post_type ]) )
						add_meta_box('powerpress-'.$feed_slug, __('Podcast Episode for Custom Channel', 'powerpress') .': '. esc_attr($feed_title), 'powerpress_meta_box', $post_type, 'normal');
				}
			}
			reset($Powerpress['custom_feeds']);
		}
		else // This handles all podcast post types and default  'post'. if post type podcasting enabled. 
		{
			reset($post_types);
			foreach( $post_types as $null => $post_type )
			{
				if( empty($FeedSlugPostTypesArray[ 'podcast' ][ $post_type ]) )
					add_meta_box('powerpress-podcast', __('Podcast Episode', 'powerpress'), 'powerpress_meta_box', $post_type, 'normal');
			}
		}
		
		// For custom compatibility type set:
		if( isset($Powerpress['custom_feeds']) && defined('POWERPRESS_CUSTOM_CAPABILITY_TYPE') )
		{
			$post_types = powerpress_admin_get_post_types( POWERPRESS_CUSTOM_CAPABILITY_TYPE );
			if( !empty($post_types) )
			{
				foreach( $Powerpress['custom_feeds'] as $feed_slug => $feed_title )
				{
					if( $feed_slug == 'podcast' )
						continue;
					
					$FeedCustom = get_option('powerpress_feed_'.$feed_slug, array());
							
					reset($post_types);
					foreach( $post_types as $null => $post_type )
					{
						if( !empty($FeedCustom['custom_post_type']) && $FeedCustom['custom_post_type'] != $post_type )
							continue;
						
						if( empty($FeedSlugPostTypesArray[ $feed_slug ][ $post_type ]) )
							add_meta_box('powerpress-'.$feed_slug, __('Podcast Episode for Custom Channel', 'powerpress') .': '.$feed_title, 'powerpress_meta_box', $post_type, 'normal');
					}
				}
				reset($Powerpress['custom_feeds']);
			}
		}
	}
	
	if( current_user_can(POWERPRESS_CAPABILITY_MANAGE_OPTIONS) ) {
        $Powerpress = powerpress_default_settings($Powerpress, 'basic');

        if (isset($_GET['page']) && strstr($_GET['page'], 'powerpress') !== false && isset($_POST['General'])) {
            $ToBeSaved = $_POST['General'];

            if (isset($ToBeSaved['channels']))
                $Powerpress['channels'] = $ToBeSaved['channels'];
            if (isset($ToBeSaved['cat_casting']))
                $Powerpress['cat_casting'] = $ToBeSaved['cat_casting'];
            if (isset($ToBeSaved['taxonomy_podcasting']))
                $Powerpress['taxonomy_podcasting'] = $ToBeSaved['taxonomy_podcasting'];
            if (isset($ToBeSaved['posttype_podcasting']))
                $Powerpress['posttype_podcasting'] = $ToBeSaved['posttype_podcasting'];
            if (isset($ToBeSaved['podpress_stats']))
                $Powerpress['podpress_stats'] = $ToBeSaved['podpress_stats'];
            if (isset($ToBeSaved['blubrry_hosting']))
                $Powerpress['blubrry_hosting'] = $ToBeSaved['blubrry_hosting'];
        }
        $Settings = get_option('powerpress_general', array());
        if (!$Settings || (isset($Settings['pp_onboarding_incomplete']) && $Settings['pp_onboarding_incomplete'] == 1) && (isset($Settings['timestamp']) && $Settings['timestamp'] > 1576972800)) {
            $parent_slug = 'powerpressadmin_onboarding.php';
            $parent_slug = apply_filters('powerpress_submenu_parent_slug', $parent_slug);
            add_menu_page(__('PowerPress', 'powerpress'), __('PowerPress', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpressadmin_onboarding.php', 'powerpress_admin_page_onboarding', powerpress_get_root_url() . 'images/PowerPress_white.png');
            add_submenu_page($parent_slug, __('Get Started', 'powerpress'), __('Get Started', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpressadmin_onboarding.php', 'powerpress_admin_page_onboarding');
            add_submenu_page($parent_slug, __('PowerPress Settings', 'powerpress'), __('Settings', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpressadmin_basic', 'powerpress_admin_page_basic');
        } else {
            $parent_slug = 'powerpressadmin_basic';
            $parent_slug = apply_filters('powerpress_submenu_parent_slug', $parent_slug);
            add_menu_page(__('PowerPress', 'powerpress'), __('PowerPress', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpressadmin_basic', 'powerpress_admin_page_basic', powerpress_get_root_url() . 'images/PowerPress_white.png');
            add_submenu_page($parent_slug, __('PowerPress Settings', 'powerpress'), __('Settings', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpressadmin_basic', 'powerpress_admin_page_basic');
            add_submenu_page($parent_slug, __('Get Started', 'powerpress'), __('Get Started', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpressadmin_onboarding.php', 'powerpress_admin_page_onboarding');

        }
        add_options_page(__('PowerPress', 'powerpress'), __('PowerPress', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpressadmin_basic', 'powerpress_admin_page_basic');

        add_submenu_page($parent_slug, __('Live Item Tag.', 'powerpress'), __('Live Item', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_live_item.php', 'powerpress_admin_page_live_item');

        add_submenu_page($parent_slug, __('Import podcast feed from SoundCloud, LibSyn, PodBean or other podcast service.', 'powerpress'), __('Import Podcast', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_import_feed.php', 'powerpress_admin_page_import_feed');
        add_submenu_page($parent_slug, __('Migrate media files to Blubrry Podcast Media Hosting with only a few clicks.', 'powerpress'), __('Migrate Media', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_migrate.php', 'powerpress_admin_page_migrate');

        add_submenu_page($parent_slug, __('PowerPress Audio Player Options', 'powerpress'), __('Audio Player', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_player.php', 'powerpress_admin_page_players');
        add_submenu_page($parent_slug, __('PowerPress Video Player Options', 'powerpress'), __('Video Player', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_videoplayer.php', 'powerpress_admin_page_videoplayers');
        if (!empty($Powerpress['channels']))
            add_submenu_page($parent_slug, __('PowerPress Custom Podcast Channels', 'powerpress'), __('Podcast Channels', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_customfeeds.php', 'powerpress_admin_page_customfeeds');
        if (!empty($Powerpress['cat_casting']))
            add_submenu_page($parent_slug, __('PowerPress Category Podcasting', 'powerpress'), __('Category Podcasting', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_categoryfeeds.php', 'powerpress_admin_page_categoryfeeds');
        if (defined('POWERPRESS_TAXONOMY_PODCASTING') || !empty($Powerpress['taxonomy_podcasting']))
            add_submenu_page($parent_slug, __('PowerPress Taxonomy Podcasting', 'powerpress'), __('Taxonomy Podcasting', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_taxonomyfeeds.php', 'powerpress_admin_page_taxonomyfeeds');
        if (defined('POWERPRESS_POSTTYPE_PODCASTING') || !empty($Powerpress['posttype_podcasting']))
            add_submenu_page($parent_slug, __('PowerPress Post Type Podcasting', 'powerpress'), __('Post Type Podcasting', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_posttypefeeds.php', 'powerpress_admin_page_posttypefeeds');
        if (!empty($Powerpress['podpress_stats']))
            add_submenu_page($parent_slug, __('PodPress Stats', 'powerpress'), __('PodPress Stats', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_podpress-stats.php', 'powerpress_admin_page_podpress_stats');
        if (!empty($Powerpress['powerpress_network'])) {
            add_submenu_page( $parent_slug,  __('PowerPress Network', 'powerpress'), __('PowerPress Network', 'powerpress'), 'manage_options', 'network-plugin', 'network_plugin' );
        }
        //if( !empty($Powerpress['blubrry_hosting']) &&  $Powerpress['blubrry_hosting'] !== 'false' )

        add_submenu_page($parent_slug, __('PowerPress Tools', 'powerpress'), __('Tools', 'powerpress'), POWERPRESS_CAPABILITY_EDIT_PAGES, 'powerpress/powerpressadmin_tools.php', 'powerpress_admin_page_tools');
    }
}


add_action('admin_menu', 'powerpress_admin_menu');


function network_plugin() {
    if (isset($GLOBALS['ppn_object'])) {
        $GLOBALS['ppn_object']->display_plugin();
    }
}

// Save episode information
function powerpress_edit_post($post_ID, $post)
{
	if ( !current_user_can('edit_post', $post_ID) )
		return $post_ID;
		
	$GeneralSettings = get_option('powerpress_general', array());

	if( isset($GeneralSettings['auto_enclose']) && $GeneralSettings['auto_enclose'] )
	{
		powerpress_do_enclose($post->post_content, $post_ID, ($GeneralSettings['auto_enclose']==2) );
	}

	// If the field limit is exceeded, WordPress won't send an error so we need to, as this prevents publishing
	if( count($_POST, COUNT_RECURSIVE) > (ini_get('max_input_vars') -100 ) ) {
        // we want to display the warning message
        $error = "PowerPress Warning: you may be exceeding your fields limit, a server setting that limits how many fields your pages can contain. Your current limit is ";
        $error .= ini_get('max_input_vars') . " <a href='https://blubrry.com/support/powerpress-documentation/warning-messages-explained/'>Learn more</a>";
        powerpress_add_error($error);
    }

    $Episodes = ( isset($_POST['Powerpress'])? $_POST['Powerpress'] : false);
	if( $Episodes )
	{
	    $success_array = array();
		foreach( $Episodes as $feed_slug => $Powerpress )
		{
		    $error = '';
			$field = 'enclosure';
			if( $feed_slug != 'podcast' )
				$field = '_'.$feed_slug.':enclosure';
			
			if( !empty($Powerpress['remove_podcast']) )
			{
				delete_post_meta( $post_ID, $field);
				
				if( $feed_slug == 'podcast' ) // Clean up the old data
					delete_post_meta( $post_ID, 'itunes:duration');
			}
			else if( !empty($Powerpress['change_podcast']) || !empty($Powerpress['new_podcast']) )
			{
				// No URL specified, then it's not really a podcast--display a warning
                // check some basic episode data and save if it's there
				if( empty($Powerpress['url']) && ( !empty($Powerpress['itunes_image']) || !empty($Powerpress['episode_title']) || !empty($Powerpress['feed_title']) || !empty($Powerpress['summary']) || !empty($Powerpress['subtitle'])) ) {
				    $Powerpress['url'] = '';
				    $error = __('WARNING: This post contains podcast data but no podcast sound file. ', 'powerpress');
				    if ($feed_slug != 'podcast') {
				        $error .= __('Episode published in feed ', 'powerpress') . $feed_slug . ' ';
                    } else {
				        $error .= __('This episode ', 'powerpress');
                    }
				    $error .= __('will not be included in any podcast feed.', 'powerpress');
				    powerpress_add_error($error);

                } else if( empty($Powerpress['url']) && empty($Powerpress['itunes_image']) && empty($Powerpress['episode_title']) && empty($Powerpress['feed_title']) && empty($Powerpress['summary']) && empty($Powerpress['subtitle']) ) {
				    continue;
                }

                    // Initialize the important variables:
                $MediaURL = $Powerpress['url'];
				if( !empty($GeneralSettings['default_url']) && strpos($MediaURL, 'http://') !== 0 && strpos($MediaURL, 'https://') !== 0 && empty($Powerpress['hosting']) ) // If the url entered does not start with a http:// or https://
				{
					if( !empty($MediaURL) )
						$MediaURL = rtrim($GeneralSettings['default_url'], '/') .'/'. ltrim($MediaURL, '/');
				}


				
				$FileSize = '';
				$ContentType = '';
				$Duration = false;
				if( $Powerpress['set_duration'] == 0 )
					$Duration = ''; // allow the duration to be detected

				// Get the content type based on the file extension, first we have to remove query string if it exists
				$UrlParts = parse_url($Powerpress['url']);
				if( $UrlParts['path'] )
				{
					// using functions that already exist in WordPress when possible:
					$ContentType = powerpress_get_contenttype($UrlParts['path']);
				}

				if( !$ContentType && !empty($Powerpress['url']) )
				{
					$error = __('Error', 'powerpress') ." [{$Powerpress['url']}]: " .__('Unable to determine content type of media (e.g. audio/mpeg). Verify file extension is correct and try again.', 'powerpress');
					powerpress_add_error($error);
				}

				//Set the duration specified by the user
				if( $Powerpress['set_duration'] == 1 ) // specify duration
				{
					$Duration = sprintf('%02d:%02d:%02d', $Powerpress['duration_hh'], $Powerpress['duration_mm'], $Powerpress['duration_ss'] );
				}
				
				//Set the file size specified by the user
				if( $Powerpress['set_size'] == 1 ) // specify file size
				{
					$FileSize = $Powerpress['size'];
				}

				if( $Powerpress['set_size'] == 0 || $Powerpress['set_duration'] == 0 )
				{
					if( !empty($Powerpress['hosting']) && !empty($Powerpress['url']) )
					{
						if( $Powerpress['set_size'] == 0 || $Powerpress['set_duration'] == 0 )
						{
							$MediaInfo = powerpress_get_media_info($Powerpress['url'], $Powerpress['program_keyword']);
							if( !isset($MediaInfo['error']) )
							{
								if( $Powerpress['set_size'] == 0 )
									$FileSize = $MediaInfo['length'];
								if( $Powerpress['set_duration'] == 0 && !empty($MediaInfo['duration']) )
									$Duration = powerpress_readable_duration($MediaInfo['duration'], true);
							}
							else
							{
							    // Even when this error is thrown, we still detect filesize and duration fine as far as i can tell
                                // Leaving this in for a few updates in case it causes some unforeseen issues
                                // Will likely want to delete this block for good August 2023
								//$error = __('Error', 'powerpress') ." ({$Powerpress['url']}): {$MediaInfo['error']}";
								//powerpress_add_error($error);
							}
						}
					}
					else
					{
                        if (!empty($Powerpress['url']) ) {
                            $media_hostname = $UrlParts['host'];
                            if (in_array($media_hostname, array('0.0.0.0', '127.0.0.1', 'localhost', '[::]', '0x7f000001/', '0xc0a80014/')) || filter_var($media_hostname, FILTER_VALIDATE_IP) || !preg_match('/^[a-zA-Z.\-\d]+$/i', $media_hostname)) {
                                // they have already seen the invalid url message on verify--no media check!
                            } else {
                                if (empty($Powerpress['set_duration']))
                                    $MediaInfo = powerpress_get_media_info_local($MediaURL, $ContentType, 0, '');
                                else
                                    $MediaInfo = powerpress_get_media_info_local($MediaURL, $ContentType, 0, $Duration);

                                if (isset($MediaInfo['error'])) {
                                    $error = __('Error', 'powerpress') . " (<a href=\"$MediaURL\" target=\"_blank\">{$MediaURL}</a>): {$MediaInfo['error']}";
                                    powerpress_add_error($error);
                                } else if (empty($MediaInfo['length'])) {
                                    $error = __('Error', 'powerpress') . " (<a href=\"$MediaURL\" target=\"_blank\">{$MediaURL}</a>): " . __('Unable to obtain size of media.', 'powerpress');
                                    powerpress_add_error($error);
                                } else {
                                    // Detect the duration
                                    if (empty($Powerpress['set_duration']) && !empty($MediaInfo['duration']))
                                        $Duration = powerpress_readable_duration($MediaInfo['duration'], true); // Fix so it looks better when viewed for editing

                                    // Detect the file size
                                    if (empty($Powerpress['set_size']) && $MediaInfo['length'] > 0)
                                        $FileSize = $MediaInfo['length'];
                                }
                            }
                        }
					}
				}

				// If we made if this far, we have the content type and file size...
                if (!empty($MediaURL)) {
                    $EnclosureData = $MediaURL . "\n" . $FileSize . "\n" . $ContentType;
                } else {
                    $EnclosureData = "no\n0\n0";
                }

                $ToSerialize = array();

                $valueError = false;
                $valueErrorMsg = "";
                $lightning = [];
                $splits = [];
                $pubKeys = [];
                $customKeys = [];
                $customValues = [];
                $count = 1;
                $morePeople = true;
                $validPeople  = 0;
                while ($morePeople) {
                    if (isset($Powerpress["ep-person-$count-pubkey"]))
                        $pubKey = $Powerpress["ep-person-$count-pubkey"];
                    else
                        $pubKey = false;

                    if ($pubKey === false) {
                        $morePeople = false;
                    } else {
                        if ($pubKey != "" || $Powerpress["ep-person-$count-split"] != "") {
                            $pubKeys[] = $pubKey;
                            $splits[] = $Powerpress["ep-person-$count-split"];
                            $lightning[] = $Powerpress["ep-person-$count-lightning"];
                            $customKeys[] = $Powerpress["ep-person-$count-customkey"];
                            $customValues[] = $Powerpress["ep-person-$count-customvalue"];
                            $validPeople += 1;
                        }
                    }
                    $count += 1;
                }

                foreach ($pubKeys as $pubKey) {
                    if ($pubKey == "") {
                        $valueError = true;
                        $valueErrorMsg .= " PubKey is required for all recipients.";
                    }
                }

                $sum = count($splits) > 0 ? 0 : 100;

                if (count($pubKeys) == 1)
                    $splits[0] = 100;

                foreach ($splits as $split) {
                    $sum += $split;
                }

                if (!$valueError) {
                    $ToSerialize['value_pubkey'] = $pubKeys;
                }

                $ToSerialize['value_lightning'] = $lightning;
                $ToSerialize['value_custom_key'] = $customKeys;
                $ToSerialize['value_custom_value'] = $customValues;
                $ToSerialize['value_split'] = $splits;

                if ($sum != 100 && $validPeople > 0) {
                    $valueError = true;
                    $valueErrorMsg .= " Split values should add up to 100.";
                }

                if ($valueError) {
                    powerpress_add_error(__($valueErrorMsg, 'powerpress'));
                }

				if( !empty($Powerpress['hosting']) )
					$ToSerialize['hosting'] = 1;

                $personNames = [];
                $personRoles = [];
                $personURLs = [];
                $linkURLs = [];
                $count = 1;
                while (true) {
                    if (!isset($Powerpress["role-$count-name"]))
                        break;

                    $name = $Powerpress["role-$count-name"];

                    if ($name != "") {
                        $personNames[] = $name;
                        $personRoles[] = $Powerpress["role-$count-role"];
                        $personURLs[] = $Powerpress["role-$count-personurl"];
                        $linkURLs[] = $Powerpress["role-$count-linkurl"];
                    }
                    $count += 1;
                }

                if (count($personNames) > 0) {
                    $ToSerialize['person_names'] = $personNames;
                    $ToSerialize['person_roles'] = $personRoles;
                    $ToSerialize['person_urls'] = $personURLs;
                    $ToSerialize['link_urls'] = $linkURLs;
                } else {
                    $ToSerialize['person_names'] = [''];
                    $ToSerialize['person_roles'] = [''];
                    $ToSerialize['person_urls'] = [''];
                    $ToSerialize['link_urls'] = [''];
                }

                $soundbiteStarts = [];
                $soundbiteDurations = [];
                $soundbiteTitles = [];
                $count = 1;
                while (true) {
                    if (!isset($Powerpress["soundbite-$count-start"]))
                        break;

                    $start = $Powerpress["soundbite-$count-start"];

                    if ($start != "" && $Powerpress["soundbite-$count-duration"] != "" && $Powerpress["soundbite-$count-duration"] != "00:00:00") {
                        $startParsed = explode(':', $start);
                        $startSecs = intval($startParsed[0]) * 60 * 60 + intval($startParsed[1]) * 60 + intval($startParsed[2]);

                        $durParsed = explode(':', $Powerpress["soundbite-$count-duration"]);
                        $durSecs = intval($durParsed[0]) * 60 * 60 + intval($durParsed[1]) * 60 + intval($durParsed[2]);

                        $soundbiteStarts[] = $startSecs;
                        $soundbiteDurations[] = $durSecs;
                        $soundbiteTitles[] = $Powerpress["soundbite-$count-title"];
                    }
                    $count += 1;
                }

                if (count($soundbiteStarts) > 0) {
                    $ToSerialize['soundbite_starts'] = $soundbiteStarts;
                    $ToSerialize['soundbite_durations'] = $soundbiteDurations;
                    $ToSerialize['soundbite_titles'] = $soundbiteTitles;
                } else {
                    $ToSerialize['soundbite_starts'] = [''];
                    $ToSerialize['soundbite_durations'] = [''];
                    $ToSerialize['soundbite_titles'] = [''];
                }

                if (!empty($Powerpress['location'])) {
                    $ToSerialize['location'] = $Powerpress['location'];

                    if (!empty($Powerpress['pci_geo']))
                        $ToSerialize['pci_geo'] = $Powerpress['pci_geo'];

                    if (!empty($Powerpress['pci_osm']))
                        $ToSerialize['pci_osm'] = $Powerpress['pci_osm'];
                }

                if (!empty($Powerpress['social_interact_uri'])) {
                    $ToSerialize['social_interact_uri'] = $Powerpress['social_interact_uri'];
                    $ToSerialize['social_interact_protocol'] = $Powerpress['social_interact_protocol'];

                    if (!empty($Powerpress['social_interact_accountid']))
                        $ToSerialize['social_interact_accountid'] = $Powerpress['social_interact_accountid'];
                }

                if (!empty($Powerpress['copyright'])) {
                    $ToSerialize['copyright'] = $Powerpress['copyright'];
                }
					
				// iTunes duration
				if( $Duration && ltrim($Duration, '0:') != '' ) // If all the zeroz and : are trimmed from the front and you're left with an empty value then don't save it.
					$ToSerialize['duration'] = $Duration; // regular expression '/^(\d{1,2}\:)?\d{1,2}\:\d\d$/i' (examples: 1:23, 12:34, 1:23:45, 12:34:56)

                if (isset($Powerpress['set_duration'])) {
                    $ToSerialize['set_duration'] = $Powerpress['set_duration'];
                }
                if (isset($Powerpress['set_size'])) {
                    $ToSerialize['set_size'] = $Powerpress['set_size'];
                }
                // iTunes Subtitle
				if( isset($Powerpress['subtitle']) && trim($Powerpress['subtitle']) != '' ) 
					$ToSerialize['subtitle'] = stripslashes($Powerpress['subtitle']);
				// iTunes Summary
				if( isset($Powerpress['summary']) && trim($Powerpress['summary']) != '' ) 
					$ToSerialize['summary'] = stripslashes($Powerpress['summary']);
				// Google Play Description
				if( isset($Powerpress['gp_desc']) && trim($Powerpress['gp_desc']) != '' ) 
					$ToSerialize['gp_desc'] = stripslashes($Powerpress['gp_desc']);
				// iTunes keywords (Deprecated by Apple)
				if( isset($Powerpress['keywords']) && trim($Powerpress['keywords']) != '' ) 
					$ToSerialize['keywords'] = stripslashes($Powerpress['keywords']);
				// iTunes Author
				if( isset($Powerpress['author']) && trim($Powerpress['author']) != '' ) 
					$ToSerialize['author'] = stripslashes($Powerpress['author']);
				// iTunes Explicit
				if( isset($Powerpress['explicit']) && trim($Powerpress['explicit']) != '' ) 
					$ToSerialize['explicit'] = stripslashes($Powerpress['explicit']);
				// Google Play Explicit
				if( isset($Powerpress['gp_explicit']) && trim($Powerpress['gp_explicit']) == '1' )
					$ToSerialize['gp_explicit'] = stripslashes($Powerpress['gp_explicit']);
				// iTunes CC
				if( isset($Powerpress['cc']) && trim($Powerpress['cc']) != '' ) 
					$ToSerialize['cc'] = stripslashes($Powerpress['cc']);
				// iTunes Episode image
				if( isset($Powerpress['itunes_image']) && trim($Powerpress['itunes_image']) != '' ) 
					$ToSerialize['itunes_image'] = stripslashes($Powerpress['itunes_image']);

				if( isset($Powerpress['episode_title']) && trim($Powerpress['episode_title']) != '' ) 
					$ToSerialize['episode_title'] = stripslashes($Powerpress['episode_title']);
				if( isset($Powerpress['episode_no']) && trim($Powerpress['episode_no']) != '' ) {
				    if (intval($Powerpress['episode_no']) < 0) {
                        $Powerpress['episode_no'] = -1 * intval($Powerpress['episode_no']);
                    }
                    $ToSerialize['episode_no'] = stripslashes(floor($Powerpress['episode_no']));
                }
				if( isset($Powerpress['season']) && trim($Powerpress['season']) != '' ) {
                    if (intval($Powerpress['season']) < 0) {
                        $Powerpress['season'] = -1 * intval($Powerpress['season']);
                    }
                    $ToSerialize['season'] = stripslashes($Powerpress['season']);
                }
				if( isset($Powerpress['episode_type']) && trim($Powerpress['episode_type']) != '' ) 
					$ToSerialize['episode_type'] = stripslashes($Powerpress['episode_type']);
                else
                    $ToSerialize['episode_type'] = 'full';
			
				// order
				if( isset($Powerpress['order']) && trim($Powerpress['order']) != '' ) 
					$ToSerialize['order'] = stripslashes($Powerpress['order']);
				// always
				if( isset($Powerpress['always']) && trim($Powerpress['always']) != '' ) 
					$ToSerialize['always'] = stripslashes($Powerpress['always']);
				// iTunes Block
				if( isset($Powerpress['block']) && $Powerpress['block'] == '1' ) 
					$ToSerialize['block'] = 1;
				// Google Play Block
				if( isset($Powerpress['gp_block']) && $Powerpress['gp_block'] == '1' ) 
					$ToSerialize['gp_block'] = 1;
				// Player Embed
				if( isset($Powerpress['embed']) && trim($Powerpress['embed']) != '' )
					$ToSerialize['embed'] = stripslashes($Powerpress['embed']); // we have to strip slahes if they are present befure we serialize the data
				if( isset($Powerpress['image']) && trim($Powerpress['image']) != '' )
					$ToSerialize['image'] = stripslashes($Powerpress['image']);
				if( isset($Powerpress['no_player']) && $Powerpress['no_player'] )
					$ToSerialize['no_player'] = 1;
				if( isset($Powerpress['no_links']) && $Powerpress['no_links'] )
					$ToSerialize['no_links'] = 1;
				if( isset($Powerpress['ishd']) && $Powerpress['ishd'] )
					$ToSerialize['ishd'] = 1;
                if( isset($Powerpress['program_keyword']) && !empty($Powerpress['program_keyword']) )
                    $ToSerialize['program_keyword'] = $Powerpress['program_keyword'];
				if( isset($Powerpress['width']) && trim($Powerpress['width']) )
					$ToSerialize['width'] =stripslashes( trim($Powerpress['width']));
				if( isset($Powerpress['height']) && trim($Powerpress['height']) )
					$ToSerialize['height'] = stripslashes(trim($Powerpress['height']));
				if( !empty($Powerpress['feed_title']) && trim($Powerpress['feed_title']) )
					$ToSerialize['feed_title'] = stripslashes(trim($Powerpress['feed_title']));
				if( !empty($Powerpress['category']) )
					$ToSerialize['category'] = stripslashes($Powerpress['category']);
                if( isset($Powerpress['transcript']['upload']) && $Powerpress['transcript']['upload'] ) {
                    $ToSerialize['pci_transcript'] = 1;
                    if (isset($Powerpress['pci_transcript_url']) && trim($Powerpress['pci_transcript_url']) != '') {
                        if (strpos($Powerpress['pci_transcript_url'], 'http') !== 0) {
                            powerpress_add_error(__('Transcript Error: Transcript should be a link, starting with http.', 'powerpress'));
                        } else {
                            $ToSerialize['pci_transcript_url'] = stripslashes($Powerpress['pci_transcript_url']);
                        }
                    }
                    if (isset($Powerpress['pci_transcript_language']) && trim($Powerpress['pci_transcript_language']) != '') {
                        $ToSerialize['pci_transcript_language'] = stripslashes($Powerpress['pci_transcript_language']);
                    }
                }
                if( isset($Powerpress['transcript']['generate']) && $Powerpress['transcript']['generate'] ) {
                    $ToSerialize['pci_transcript'] = 1;
                    if (isset($Powerpress['pci_transcript_language']) && trim($Powerpress['pci_transcript_language']) != '') {
                        $ToSerialize['pci_transcript_language'] = stripslashes($Powerpress['pci_transcript_language']);
                    }
                }

                if( isset($Powerpress['pci_chapters']) && $Powerpress['pci_chapters'] ) {
                    $ToSerialize['pci_chapters'] = 1;
                    $jsonChapters = array(
                        'version' => '1.2.0',
                        'chapters' => []
                    );

                    if (isset($Powerpress['pci_chapters_url']) && trim($Powerpress['pci_chapters_url']) != '') {
                        $ToSerialize['pci_chapters_url'] = stripslashes($Powerpress['pci_chapters_url']);
                    } else {
                        $uploadPath  = wp_upload_dir()['basedir'] . '/' . $feed_slug . '/' . $_POST['post_ID'];
                        $uploadURL = wp_upload_dir()['baseurl'] . '/' . $feed_slug . '/' . $_POST['post_ID'];
                        if (!is_dir($uploadPath)) {
                            wp_mkdir_p($uploadPath);
                            wp_mkdir_p($uploadPath . "/images");
                        }

                        $json = json_encode($jsonChapters);

                        $fname = "temp_chapters.json";
                        $tempFile = tempnam(sys_get_temp_dir(), $fname);
                        $file = fopen($tempFile, 'w');
                        fwrite($file, $json);
                        fclose($file);

                        file_put_contents($uploadPath . "/chapters.json", file_get_contents($tempFile));
                        unlink($tempFile);

                        $chapterURL = $uploadURL . "/chapters.json";
                        $ToSerialize['pci_chapters_url'] = $chapterURL;
                    }

                    if (isset($Powerpress['pci_chapters_manual']) && $Powerpress['pci_chapters_manual']) {
                        $ToSerialize['pci_chapters_manual'] = 1;

                        $chapterStarts = $_POST[$feed_slug."-starts"];
                        $chapterTitles = $_POST[$feed_slug."-titles"];
                        $chapterURLs = $_POST[$feed_slug."-urls"];
                        $chapterIms = $_FILES[$feed_slug."-images"];
                        $existingIms = $_POST[$feed_slug."-existingIms"];
                        $removeIms = $_POST[$feed_slug."-removeExisting"];

                        $numChapters = count($chapterStarts);
                        $tempChapters = array();

                        for ($i = 0; $i < $numChapters; $i++) {
                            if ($chapterStarts[$i] == "" || $chapterTitles[$i] == "") {
                                continue;
                            }

                            $startParsed = explode(':', $chapterStarts[$i]);
                            $title = $chapterTitles[$i];
                            $url = $chapterURLs[$i];
                            $fileName = $chapterIms['name'][$i];
                            $filePath = $chapterIms['tmp_name'][$i];
                            $existingIm = $existingIms[$i];
                            $removeIm = $removeIms[$i];

                            $startSecs = intval($startParsed[0]) * 60 * 60 + intval($startParsed[1]) * 60 + intval($startParsed[2]);
                            $tempChapters["$startSecs"] = array(
                                'title' => $title,
                                'url' => $url,
                                'img' => array(
                                    'existing_file' => $existingIm,
                                    'remove_existing' => $removeIm == '1',
                                    'new_file_name' => $fileName,
                                    'new_file_path' => $filePath
                                )
                            );
                        }

                        ksort($tempChapters);

                        $uploadPath  = wp_upload_dir()['basedir'] . '/' . $feed_slug . '/' . $_POST['post_ID'];
                        $uploadURL = wp_upload_dir()['baseurl'] . '/' . $feed_slug . '/' . $_POST['post_ID'];
                        if (!is_dir($uploadPath)) {
                            wp_mkdir_p($uploadPath);
                            wp_mkdir_p($uploadPath . "/images");
                        }

                        foreach ($tempChapters as $startTime => $chapterInfo) {
                            $chapter = array(
                                'startTime' => intval($startTime),
                                'title' => $chapterInfo['title'],
                            );

                            if (!empty($chapterInfo['url'])) {
                                $chapter['url'] = $chapterInfo['url'];
                            }

                            $img = $chapterInfo['img'];

                            if ($img['existing_file'] != '' && $img['new_file_name'] == '' && !$img['remove_existing']) {
                                $chapter['img'] = $img['existing_file'];
                            } elseif ( $img['new_file_name'] != '') {
                                if ($img['existing_file'] != '')
                                    unlink($uploadPath . "/" . $img['existing_file']);

                                file_put_contents($uploadPath . "/images/" . $img['new_file_name'], file_get_contents($img['new_file_path']));
                                $chapter['img'] = $uploadURL . "/images/" . $img['new_file_name'];
                            } elseif ($img['existing_file'] != '' && $img['remove_existing']) {
                                if (preg_match($uploadPath, $img['existing_file']))
                                    unlink($uploadPath . "/images/" . $img['existing_file']);
                            }

                            $jsonChapters['chapters'][] = $chapter;
                        }

                        $json = json_encode($jsonChapters);

                        $fname = "temp_chapters.json";
                        $tempFile = tempnam(sys_get_temp_dir(), $fname);
                        $file = fopen($tempFile, 'w');
                        fwrite($file, $json);
                        fclose($file);

                        file_put_contents($uploadPath . "/chapters.json", file_get_contents($tempFile));
                        unlink($tempFile);

                        $chapterURL = $uploadURL . "/chapters.json";

                        $ToSerialize['pci_chapters_url'] = $chapterURL;
                    }
                }

                if( isset($Powerpress['pci_soundbites']) && $Powerpress['pci_soundbites'] )
                    $ToSerialize['pci_soundbites'] = 1;
					
				if( isset($Powerpress['no_player_and_links']) && $Powerpress['no_player_and_links'] )
				{
					$ToSerialize['no_player'] = 1;
					$ToSerialize['no_links'] = 1;
				}

				if (isset($Powerpress['podcast_id'])) {
				    $ToSerialize['podcast_id'] = $Powerpress['podcast_id'];
                }
				
				// WebM Support:
				if( !empty($Powerpress['webm_src']) )
				{
					$WebMSrc = $Powerpress['webm_src'];
					if( !empty($GeneralSettings['default_url']) && strpos($WebMSrc, 'http://') !== 0 ) // && $Powerpress['hosting'] != 1 ) // If the url entered does not start with a http://
					{
						$WebMSrc = rtrim($GeneralSettings['default_url'], '/') .'/'. ltrim($WebMSrc, '/');
					}
					$ToSerialize['webm_src'] = $WebMSrc;
					
					$MediaInfo = powerpress_get_media_info_local($WebMSrc, 'video/webm', 0, '');
					if( isset($MediaInfo['error']) )
					{
						$error = __('Error', 'powerpress') ." ({$WebMSrc}): {$MediaInfo['error']}";
						powerpress_add_error($error);
					}
					else if( empty($MediaInfo['length']) )
					{
						$error = __('Error', 'powerpress') ." ({$WebMSrc}): ". __('Unable to obtain size of media.', 'powerpress');
						powerpress_add_error($error);
					}
					else
					{
						$ToSerialize['webm_length'] = $MediaInfo['length'];
					}
				}
				
				if( $Powerpress['set_duration'] == -1 )
					unset($ToSerialize['duration']);
				if( count($ToSerialize) > 0 ) // Lets add the serialized data
					$EnclosureData .= "\n".serialize( $ToSerialize );

				if( !empty($Powerpress['new_podcast']) )
				{
					add_post_meta($post_ID, $field, $EnclosureData, true);
				}
				else
				{
					update_post_meta($post_ID, $field, $EnclosureData);
				}
				
				if( !empty($ToSerialize['category']) )
				{
					$Categories = wp_get_post_categories($post_ID);
					if( !in_array($ToSerialize['category'], $Categories) )
					{
						$AddCategories = array($ToSerialize['category']);
						wp_set_post_categories($post_ID, $AddCategories, true);
					}
				}

                if (!empty($error)) {
                    $success_array[$feed_slug] = $error;
                } else {
                    $success_array[$feed_slug] = true;
                }
            }
		} // Loop through posted episodes...
		
		// Check for PowerpressFeature for each channel...
		if( isset($_POST['PowerpressFeature']) )
		{
			$FeatureEpisodes = powerpress_get_settings('powerpress_itunes_featured');
			if( empty($FeatureEpisodes) && !is_array($FeatureEpisodes) )
				$FeatureEpisodes = array();
			
			$PowerpressFeature = $_POST['PowerpressFeature'];
			foreach( $PowerpressFeature as $feed_slug => $set_featured )
			{
				if( !empty($set_featured) )
					$FeatureEpisodes[ $feed_slug ] = $post_ID;
				else
					unset($FeatureEpisodes[ $feed_slug ]);
			}
			
			powerpress_save_settings( $FeatureEpisodes, 'powerpress_itunes_featured');
		}

        require_once(POWERPRESS_ABSPATH .'/powerpress-metamarks.php');
        powerpress_metabox_save($post_ID);
	}
    // Anytime the post is marked published, private or scheduled for the future we need to make sure we're making the media available for hosting
    if( $post->post_status == 'publish' || $post->post_status == 'private' || $post->post_status == 'future' )
    {
        if( !empty($GeneralSettings['blubrry_hosting']) &&  $GeneralSettings['blubrry_hosting'] !== 'false' )
            powerpress_process_hosting($post_ID, $post->post_title); // Call anytime blog post is in the published state
    }
    //WebSub and podping implementation
    if($post->post_status == 'publish') {
        $feedUrls = array(); //feed urls that have been updated by this post and that the hub should be notified about.
        $postType = get_post_type($post_ID);

        if (!empty($GeneralSettings['cat_casting'])) {
            foreach (wp_get_post_categories($post_ID) as $id) {
                if (!empty($GeneralSettings['cat_casting_podcast_feeds'])) {
                    array_push($feedUrls, get_category_feed_link($id, 'podcast'));
                } else {
                    array_push($feedUrls, get_category_feed_link($id));
                }
            }
        }
        foreach (get_post_meta($post_ID) as $key => $value) {
            if ($key === 'enclosure') {
                //main feed updated
                array_push($feedUrls, get_feed_link('podcast'));
            }
            else if (!empty($GeneralSettings['channels']) && $postType == 'post' && preg_match("/_(\w{1,}):enclosure/i", $key, $matches) == 1) {
                array_push($feedUrls, get_feed_link($matches[1]));
            }
        }
        if (!empty($GeneralSettings['posttype_podcasting'])) {
            if ($postType != "page" || $postType != "post") {
                $PostTypeSettings = get_option('powerpress_posttype_' . $postType);
                if (!empty($PostTypeSettings)) {
                    foreach ($PostTypeSettings as $feed_slug => $setting) {
                        array_push($feedUrls, get_post_type_archive_feed_link($postType, $feed_slug));
                    }
                }
            }
        }
        if (!(defined('POWERPRESS_DISABLE_WEBSUB') && POWERPRESS_DISABLE_WEBSUB )) {
            require_once( 'class.powerpresswebsub.php' );
            $Websub = new PowerPressWebSub();
            foreach ($feedUrls as $url) {
                try {
                    $Websub->publish($url);
                } catch (Exception $e) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        powerpress_add_error($e->getMessage());
                    }
                }
            }
        }

        // podping uses the Blubrry API, so an account needs to be linked
        $creds = get_option('powerpress_creds');
        if (!empty($MediaURL) && (!empty($creds) || !empty($GeneralSettings['blubrry_auth']))) {
            if (!(defined('POWERPRESS_DISABLE_PODPING') && POWERPRESS_DISABLE_PODPING )) {
                require_once( 'powerpresspodping.class.php' );
                $Podping = new PowerPressPodping();
                foreach ($feedUrls as $url) {
                    try {
                        // because auth key is the same across the whole site and each feed url is being passed, it doesn't matter what program keyword we use to call the api
                        $Podping->publish($url, $GeneralSettings['blubrry_program_keyword']);
                    } catch (Exception $e) {
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            powerpress_add_error($e->getMessage());
                        }
                    }
                }
            }

        }
    }

    // cookies to either reload the block on success, or display an error on error
    if (!empty($success_array) && (!isset($GeneralSettings['pp_show_block_errors']) || $GeneralSettings['pp_show_block_errors'])) {
        // if successful, success = true. otherwise, success = a string that contains the error
        foreach($success_array as $feed_slug => $success) {
            if ($success === true) {
                setcookie('pp_event_' . $feed_slug . '_' . $post_ID . '_success', "success", 0, '/');
            } else {
                setcookie( 'pp_event_' . $feed_slug . '_' . $post_ID . '_add_notice',  $success, 0, '/' );
            }
        }
    }

    // And we're done!
	return $post_ID;
}

add_action('save_post', 'powerpress_edit_post', 10, 2);

if( defined('POWERPRESS_DO_ENCLOSE_FIX') )
{
	function powerpress_insert_post_data($data, $postarr)
	{
		// If we added or modified a podcast episode, then we need to re-add/remove the embedded hidden link...
		if( isset($_POST['Powerpress']['podcast']) && $postarr['post_type'] == 'post' )
		{
			// First, remove the previous comment if one exists in the post body.
			$data['post_content'] = preg_replace('/\<!--.*added by PowerPress.*-->/im', '', $data['post_content']);
			
			$Powerpress = $_POST['Powerpress']['podcast'];
			if( !empty($Powerpress['remove_podcast']) )
			{
				// Do nothing
			}
			else if( !empty($Powerpress['change_podcast']) || !empty($Powerpress['new_podcast']) )
			{
				$MediaURL = $Powerpress['url'];
				if( strpos($MediaURL, 'http://') !== 0 && strpos($MediaURL, 'https://') !== 0 && empty($Powerpress['hosting']) ) // If the url entered does not start with a http:// or https://
				{
					// Only glitch here is if the media url had an error, and if that's the case then there are other issues the user needs to worry about.
					$GeneralSettings = get_option('powerpress_general');
					if( $GeneralSettings && isset($GeneralSettings['default_url']) )
						$MediaURL = rtrim($GeneralSettings['default_url'], '/') .'/'. ltrim($MediaURL, '/');
				}
					
				$data['post_content'] .= "<!-- DO NOT DELETE href=\"$MediaURL\" added by PowerPress to fix WordPress 2.8+ bug -->";
			}
			else
			{
				$EncloseData = powerpress_get_enclosure_data($postarr['ID']);
				if( $EncloseData && $EncloseData['url'] )
					$data['post_content'] .= "<!-- DO NOT DELETE href=\"{$EncloseData['url']}\" added by PowerPress to fix WordPress 2.8+ bug -->";
			}
		}
		
		return $data;
	}
	add_filter('wp_insert_post_data', 'powerpress_insert_post_data',1,2);
}

// Do the iTunes pinging here...
function powerpress_publish_post($post_id)
{
	// Delete scheduled _encloseme requests...
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_encloseme' ");
	
	$GeneralSettings = get_option('powerpress_general');
	if( isset($GeneralSettings['auto_enclose']) && $GeneralSettings['auto_enclose'] )
	{
		$post = get_post($post_id);
		powerpress_do_enclose($post->post_content, $post_id, ($GeneralSettings['auto_enclose']==2) );
	}
}

add_action('publish_post', 'powerpress_publish_post');

// Admin page, html meta header
function powerpress_admin_head()
{
	global $parent_file, $hook_suffix;
	$page_name = '';
	if ( isset($parent_file) && !empty($parent_file) )
	{
		$page_name = substr($parent_file, 0, -4);
		$page_name = preg_replace('/(\?.*)$/', '', $page_name); // Hack required for WP 3.0
		$page_name = str_replace(array('.php', '-new', '-add'), '', $page_name); // Hack required for WP 3.0
	}
	else
	{
		$page_name = str_replace(array('.php', '-new', '-add'), '', $hook_suffix);
	}
	
	// Powerpress page
	if( isset($_GET['page']) && strstr($_GET['page'], 'powerpress' ) !== false )
	{
		powerpress_wp_print_styles();
		echo "<script type=\"text/javascript\" src=\"". powerpress_get_root_url() ."player.min.js\"></script>\n";
?>
<script type="text/javascript"><!--
function powerpress_show_field(id, show) {
	if( document.getElementById(id).nodeName == "SPAN" )
	 document.getElementById(id).style.display = (show?"inline":"none");
 else
	 document.getElementById(id).style.display = (show?"block":"none");
}
function powerpress_new_feed_url_prompt() {
	var Msg = '<?php echo __('WARNING: Changes made here are permanent. If the New Feed URL entered is incorrect, you will lose subscribers and will no longer be able to update your listing in the iTunes Store.\n\nDO NOT MODIFY THIS SETTING UNLESS YOU ABSOLUTELY KNOW WHAT YOU ARE DOING.\n\nAre you sure you want to continue?', 'powerpress'); ?>';
	if( confirm(Msg) ) {
		powerpress_show_field('new_feed_url_step_1', false);
		powerpress_show_field('new_feed_url_step_2', true);
	}
	return false;
}


function powerpress_create_subscribe_page()
{
	// This function is only called once!
	jQuery.ajax( {
		type: 'POST',
		url: '<?php echo admin_url(); ?>admin-ajax.php', 
		data: { action: 'powerpress_create_subscribe_page', nonce: '<?php echo wp_create_nonce('powerpress-create-subscribe-page'); ?>' },
		timeout: (30 * 1000),
		success: function(response) {
			
			response = response.trim();
			<?php
			if( defined('POWERPRESS_AJAX_DEBUG') )
				echo "\t\t\t\talert(response);\n";
			?>
			// This logic will parse beyond warning messages generated by the server that we don't know about
			
			var foundAt = response.indexOf('PAGE-OK');
			if( foundAt > 0 )
			{
				response = response.substring( foundAt );
			}
			
			var Parts = response.split("\n", 5);
			
			if( Parts[0] == 'PAGE-OK' )
			{
				jQuery('#subscribe_page_link_id').append('<option value="' + Parts[1] + '" selected>' + Parts[3] + '</option>');
				jQuery('#subscribe_page_link_or').hide();
				jQuery('#powerpress_create_subscribe_page').hide();
			}
			else if( Parts[0] == 'PAGE-ERROR' )
			{
				alert( Parts[1] );
			}
			else
			{
				alert(  '<?php echo __('Unknown error occurred creating subscribe page.', 'powerpress'); ?>' );
			}
		},
		error: function(objAJAXRequest, strError) {
			
			alert(  '<?php echo __('Unknown ajax error occurred creating subscribe page.', 'powerpress'); ?>' );
			
			var errorMsg = "HTTP " +objAJAXRequest.statusText;
			if ( objAJAXRequest.responseText ) {
				errorMsg += ', '+ objAJAXRequest.responseText.replace( /<.[^<>]*?>/g, '' );
			}
		}
	});
}

/* Save tab position */
jQuery(document).ready(function($) {
	
	if( jQuery("#powerpress_settings_page").length > 0 )
	{
        <?php if (!empty($_POST['tab'])) { ?>
        document.getElementById("<?php echo esc_js($_POST['tab']); ?>").click();
        <?php } elseif (!empty($_GET['tab'])) { ?>
        document.getElementById("<?php echo esc_js($_GET['tab']); ?>").click();
        <?php }
        if (!empty($_POST['sidenav-tab'])) { ?>
        document.getElementById("<?php echo esc_js($_POST['sidenav-tab']); ?>").click();
        <?php }  elseif (!empty($_GET['sidenav-tab'])) { ?>
        document.getElementById("<?php echo esc_js($_GET['sidenav-tab']); ?>").click();
        <?php }?>
        jQuery('form').submit(function() {
            let selectedTemp = jQuery('.tablinks.active:first');
            jQuery('#save_tab_pos').val(selectedTemp.attr('id'));
            let selectedSide = jQuery('.pp-tabcontent.active .pp-sidenav-tablinks.active:first');
            if (selectedSide) {
                jQuery("#save_sidenav_pos").val(selectedSide.attr('id'));
            }
        });
	}
	
	jQuery('#powerpress_create_subscribe_page').click( function(e) {
		e.preventDefault();
		powerpress_create_subscribe_page();
		return false;
	});
	jQuery('#subscribe_page_link_id').change( function(e) {
		if( jQuery('#subscribe_page_link_id').val().length > 0 )
			jQuery('#subscribe_page_link_or').css('display', 'none');
		else
			jQuery('#subscribe_page_link_or').css('display', 'inline-block');
	});
	
	jQuery('.powerpress-parental-rating-tip').click( function(event) {
		event.preventDefault();
		jQuery('.powerpress-parental-rating-tip-p').css('display', 'none');
		jQuery('#'+this.id +'_p').css('display', 'block');
	});
	jQuery('.activate-player').click( function(event) {
		event.preventDefault();
		var PlayerName = this.id.replace(/(activate_)(.*)$/, "$2");
		if( !PlayerName )
			return;

		if(typeof jQuery.prop === 'function') {
            jQuery('.player-type-input').prop('disabled', true);
			jQuery('#player_'+PlayerName).prop('disabled', false);
            jQuery('#player_'+PlayerName).removeAttr('disabled');
		} else {
            jQuery('.player-type-input').attr('disabled', true);
			jQuery('#player_'+PlayerName).removeAttr('disabled');
		}
		jQuery(this).closest("form").submit();
	});
	jQuery('.goto-artwork-tab').click( function(event) {
		event.preventDefault();
		// TODO:
		
	});
});


//-->
</script>
        <?php
        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (WP_DEBUG) {
                wp_register_style('powerpress-admin-style', powerpress_get_root_url() . 'css/admin.css', array(), POWERPRESS_VERSION);
                wp_register_style('powerpress-grid', powerpress_get_root_url() . 'css/bootstrap-grid.css', array(), POWERPRESS_VERSION);
            } else {
                wp_register_style('powerpress-admin-style', powerpress_get_root_url() . 'css/admin.min.css', array(), POWERPRESS_VERSION);
                wp_register_style('powerpress-grid', powerpress_get_root_url() . 'css/bootstrap-grid.min.css', array(), POWERPRESS_VERSION);
            }
        } else {
            wp_register_style('powerpress-admin-style', powerpress_get_root_url() . 'css/admin.min.css', array(), POWERPRESS_VERSION);
            wp_register_style('powerpress-grid', powerpress_get_root_url() . 'css/bootstrap-grid.min.css', array(), POWERPRESS_VERSION);
        }
        wp_enqueue_style( 'powerpress-admin-style' );
        wp_enqueue_style( 'powerpress-grid' );
    }
	else if( $page_name == 'edit' || $page_name == 'edit-pages' ) // || $page_name == '' ) // we don't know the page, we better include our CSS just in case
	{
?>
<style type="text/css">
.powerpress_podcast_box {
	
}
.powerpress_podcast_box label {
	width: 120px;
	font-weight: bold;
	font-size: 110%;
	display: inline;
	position: absolute;
	top: 0;
	left: 0;
}
.powerpress_podcast_box .powerpress_row {
	margin-top: 10px;
	margin-bottom: 10px;
	position: relative;
}
.powerpress_podcast_box .powerpress_row_content {
	margin-left: 120px;
}
.powerpress_podcast_box  .error,
.powerpress_podcast_box  .warning,
.powerpress_podcast_box  .success {
	margin-top: 10px;
	margin-bottom: 10px;
	padding: 5px;
	font-size: 12px;
	border-width: 1px;
	border-style: solid;
	font-weight: bold;
	text-align: center;
	-moz-border-radius: 3px;
	-khtml-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	position: relative;
}
.powerpress_podcast_box  .warning {
	color: #8a6d3b;
	border-color: #faebcc;
	background-color: #fcf8e3;
}
.powerpress_podcast_box  .error {
	color: #a94442;
	border-color: #ebccd1;
	background-color: #f2dede;
}
.powerpress_podcast_box  .success {
	color: #3c763d;
	border-color: #d6e9c6;
	background-color: #dff0d8;
}
.powerpress_podcast_box  .success a.close {
	position: absolute;
	top: 2px;
	right: 2px;
	text-align: right;
	color: #993366;
	text-decoration: none;
}
.powerpress_podcast_box  .updated {
	margin-top: 10px;
	margin-bottom: 10px;
	padding: 5px;
	font-size: 12px;
	border-width: 1px;
	border-style: solid;
	font-weight: bold;
	text-align: center;
}

</style>
<script language="javascript"><!--

g_powerpress_last_selected_channel = '';

function powerpress_check_url(url, DestDiv)
{
	if( powerpress_check_url.arguments.length > 1 )
		DestDiv = powerpress_check_url.arguments[1];

	let Div = jQuery( '#'+DestDiv );
    Div.addClass("pp-error");
    Div.removeClass("updated");
    // allow query strings from trusted hosts
    var validChars = ':0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/-_.:';
    if (url.includes('traffic.libsyn.com') || url.includes('cdn.simplecast.com') || url.includes('buzzsprout.com') || url.includes('audioboom.com')) {
        validChars = ':0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/-_.?=&:';

    }
    // art19 urls have commas and %s
    if (url.includes('art19.com')) {
        validChars = ':0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/-_.?=&,%:';
    }
    // for blubrry's programmatic ads, we include %s to encode the feed url in the query string
    if (url.includes('mc.blubrry.com')) {
        validChars = ':0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/-_.?=&%;+:';
    }
    let FeedSlug = DestDiv.replace("powerpress_warning_", "");

	for( var x = 0; x < url.length; x++ )
	{
		if( validChars.indexOf( url.charAt(x) ) == -1 )
		{
            Div.html('<?php echo esc_js( __('Media URL contains characters that may cause problems for some clients. For maximum compatibility, only use letters, numbers, dash - and underscore _ characters only.', 'powerpress')); ?>');
            Div.css('display', 'block');
            jQuery('#powerpress_fail_'+FeedSlug).css("display", 'inline-block');
            jQuery( '#powerpress_url_show_'+FeedSlug ).css('background-color', '#FFF3CD');
            jQuery( '#powerpress_url_show_'+FeedSlug ).css('background-color');
			return false;
		}
	
		if( x == 5 )
			validChars = validChars.substring(1); // remove the colon, should no longer appear in URLs
	}

	Div.css('display', 'none');
	return true;
}


function powerpress_get_media_info(FeedSlug)
{
	if( jQuery('#powerpress_check_'+FeedSlug).css("display") != "none" )
		return; // Another process is already running

    jQuery('#powerpress_fail_'+FeedSlug).css("display", 'none');
	jQuery( '#powerpress_success_'+FeedSlug ).css('display', 'none');
	//jQuery( '#powerpress_warning_'+FeedSlug ).text('');
	jQuery( '#powerpress_warning_'+FeedSlug ).css('display', 'none');
	jQuery( '#powerpress_warning_'+FeedSlug ).addClass("pp-error");
	jQuery( '#powerpress_warning_'+FeedSlug ).removeClass("updated");
	
	var Value = jQuery('#powerpress_url_display_'+FeedSlug).val();
	var Hosting = jQuery('#powerpress_hosting_'+FeedSlug).val();
	var program_keyword = jQuery('#powerpress_program_keyword_'+FeedSlug).val();
	if( Value )
	{
		if( powerpress_check_url(Value, 'powerpress_warning_'+FeedSlug ) )
		{
			jQuery('#powerpress_check_'+FeedSlug).css("display", 'inline-block');
			jQuery.ajax( {
				type: 'POST',
				url: '<?php echo admin_url(); ?>admin-ajax.php',
				data: { action: 'powerpress_media_info', media_url : Value, feed_slug : encodeURIComponent(FeedSlug), hosting: Hosting, program_keyword: program_keyword, nonce: '<?php echo wp_create_nonce('powerpress-media-info'); ?>' },
				timeout: (30 * 1000),
				success: function(response) {

					response = response.trim();
					// This logic will parse beyond warning messages generated by the server that we don't know about
					var foundAt = response.indexOf('VERIFY-OK');
					if( foundAt > 0 )
					{
						response = response.substring( foundAt );
					}
					console.log(response);
					var Parts = response.split("\n", 5);

					var FinishFeedSlug = Parts[1];
					
					jQuery('#powerpress_check_'+FeedSlug).css("display", 'none');
					
					if( FeedSlug == FinishFeedSlug && Parts[0] == 'VERIFY-OK' )
					{
						// only set file size if auto detect is selected
                        var sizeChecked = jQuery('#powerpress_set_size_0_'+FeedSlug).attr('checked');
                        if(typeof jQuery.prop === 'function') {
                            sizeChecked = jQuery('#powerpress_set_size_0_'+FeedSlug).prop('checked');
                        }
						if (sizeChecked && Parts[2])
						{

                            jQuery('#powerpress_size_'+FeedSlug).val( Parts[2] );
                        }

                        // only set duration if auto detect is selected
                        var durationChecked = jQuery('#powerpress_set_duration_0_'+FeedSlug).attr('checked');
                        if(typeof jQuery.prop === 'function') {
                            durationChecked = jQuery('#powerpress_set_duration_0_'+FeedSlug).prop('checked');
                        }
						if( durationChecked && Parts[3] )
						{
							
							var Duration = Parts[3].split(':');
							jQuery('#powerpress_duration_hh_'+FeedSlug).val( Duration[0] );
							jQuery('#powerpress_duration_mm_'+FeedSlug).val( Duration[1] );
							jQuery('#powerpress_duration_ss_'+FeedSlug).val( Duration[2] );
						}
						else if( durationChecked )
						{
							jQuery('#powerpress_duration_hh_'+FeedSlug).val( '' );
							jQuery('#powerpress_duration_mm_'+FeedSlug).val( '' );
							jQuery('#powerpress_duration_ss_'+FeedSlug).val( '' );
						}

						if (Parts[4]) {
                            self.parent.document.getElementById('powerpress_url_' + FeedSlug).value = Parts[4];
                        }
						
						if( Parts.length > 5 && Parts[5] != '' )
						{
							jQuery( '#powerpress_warning_'+FeedSlug ).html( Parts[5] );
							jQuery( '#powerpress_warning_'+FeedSlug ).css('display', 'block');
							jQuery( '#powerpress_warning_'+FeedSlug ).addClass("updated");
							jQuery( '#powerpress_warning_'+FeedSlug ).removeClass("error");
						<?php
						if( defined('POWERPRESS_AJAX_DEBUG') )
							echo "\t\t\t\tjQuery( '#powerpress_warning_'+FeedSlug ).append( '<br/>Complete Response: '+ response);\n";
						?>
						}
						else
						{
							jQuery( '#powerpress_success_'+FeedSlug ).css('display', 'inline-block');
                            jQuery( '#powerpress_url_show_'+FeedSlug ).css('background-color', '#c1f2b8');
							// setTimeout( function() { jQuery( '#powerpress_success_'+FeedSlug ).fadeOut(1000); }, 10000 );
							<?php
						if( defined('POWERPRESS_AJAX_DEBUG') )
							echo "\t\t\t\tjQuery( '#powerpress_warning_'+FeedSlug ).append( '<br/>Complete Response: '+ response);\n";
						?>
						}
					}
					else
					{
						var Parts = response.split("\n", 5);
						if( Parts.length > 5 )
						{
							var server_error = response.replace(/\n/g, "<br \/>");
							jQuery( '#powerpress_warning_'+FeedSlug ).html( '<div style="text-align: left;">Server Error:</div><div style="text-align: left; font-weight: normal;">' + server_error +'<\/div>' );
						}
						else if( Parts[1] )
							jQuery( '#powerpress_warning_'+FeedSlug ).html( Parts[1] );
						else
							jQuery( '#powerpress_warning_'+FeedSlug ).text( '<?php echo __('Unknown error occurred while checking Media URL. You can still publish this episode, but will need to enter filesize and duration manually.', 'powerpress'); ?>' );

						<?php
						if( defined('POWERPRESS_AJAX_DEBUG') )
							echo "\t\t\t\tjQuery( '#powerpress_warning_'+FeedSlug ).append( '<br/>Complete Response: '+ response);\n";
						?>
						jQuery( '#powerpress_warning_'+FeedSlug ).css('display', 'block');
                        jQuery('#powerpress_fail_'+FeedSlug).css("display", 'inline-block');
                        jQuery( '#powerpress_url_show_'+FeedSlug ).css('background-color', '#eba2a2');
					}
				},
				error: function(objAJAXRequest, textStatus, errorThrown) {
					
					var errorCode = objAJAXRequest.status;
					var errorMsg = objAJAXRequest.statusText;
					var responseClean = '';
					if ( objAJAXRequest.responseText ) {
						responseClean = objAJAXRequest.responseText.replace( /<.[^<>]*?>/g, '' );
					}
					
					jQuery('#powerpress_check_'+FeedSlug).css("display", 'none');
                    jQuery( '#powerpress_warning_'+FeedSlug ).css('display', 'block');
                    jQuery('#powerpress_fail_'+FeedSlug).css("display", 'inline-block');
                    jQuery( '#powerpress_url_show_'+FeedSlug ).css('background-color', '#eba2a2');

					if( textStatus == 'timeout' ) {
						jQuery( '#powerpress_warning_'+FeedSlug ).text( '<?php echo __('Operation timed out.', 'powerpress'); ?>' );
					}
					else if( textStatus == 'error' ) {
						jQuery( '#powerpress_warning_'+FeedSlug ).html( errorCode +' - '+ errorThrown +'<br />');
					}
					else if( textStatus == 'abort' ) {
						jQuery( '#powerpress_warning_'+FeedSlug ).text( '<?php echo __('Operation aborted.', 'powerpress'); ?>' );
					}
					else if( textStatus == 'parsererror' ) {
						jQuery( '#powerpress_warning_'+FeedSlug ).text( '<?php echo __('Parse error occurred.', 'powerpress'); ?>' );
					}
					else if( textStatus != null ) {
						jQuery( '#powerpress_warning_'+FeedSlug ).text( '<?php echo __('AJAX Error', 'powerpress') .': '; ?>'+textStatus );
					}
					else if( errorMsg ) {
						jQuery( '#powerpress_warning_'+FeedSlug ).text( +errorMsg );
					}
					else {
						jQuery( '#powerpress_warning_'+FeedSlug ).text( '<?php echo __('AJAX Error', 'powerpress') .': '. __('Unknown', 'powerpress'); ?>' );
					}
					
					if( textStatus != 'error' && errorThrown ) { // If we have an error thrown, lets append it to the error message
						jQuery('#powerpress_warning_'+FeedSlug).append('<br/>'+errorThrown);
					}

					<?php
					if( defined('POWERPRESS_AJAX_DEBUG') ) {
						echo "\t\t\tif( objAJAXRequest.responseText ) {\n";
						echo "\t\t\t\tjQuery('#powerpress_warning_'+FeedSlug).text( jQuery('#powerpress_warning_'+FeedSlug).text() +' - Response: '+ objAJAXRequest.responseText);\n";
						echo "\t\t\t}\n";
					}
					?>
				}
			});
		}
	}
}

function powerpress_update_for_video(media_url, FeedSlug)
{
	if (media_url.search(/\.(mp4|m4v|ogg|ogv|webm)$/) > -1)
	{
		jQuery('#powerpress_ishd_'+ FeedSlug +'_span').css('display','inline');
	}
	else
	{
		jQuery('#powerpress_ishd_'+ FeedSlug +'_span').css('display','none');
		jQuery('#powerpress_ishd_'+ FeedSlug +'_span').removeAttr('checked');
		if(typeof jQuery.removeProp === 'function') {
			jQuery('#powerpress_ishd_'+ FeedSlug +'_span').removeProp('checked');
		}
	}
	
		
	if (media_url.search(/\.(mp4|m4v)$/) > -1)
	{
		jQuery('#powerpress_webm_'+ FeedSlug ).css('display', 'block');
	}
	else
	{
		jQuery('#powerpress_webm_'+ FeedSlug ).css('display', 'none');
	}
}

function powerpress_remove_hosting(FeedSlug)
{
	if( confirm('<?php echo __('Are you sure you want to remove this media file?', 'powerpress'); ?>') )
	{
		jQuery( '#powerpress_url_display_'+FeedSlug ).attr("readOnly", false);
		jQuery( '#powerpress_url_'+FeedSlug ).val('');
		jQuery( '#powerpress_hosting_'+FeedSlug ).val(0);
		jQuery( '#powerpress_hosting_note_'+FeedSlug ).css('display', 'none');
		powerpress_update_for_video('', FeedSlug);
	}
}

var pp_upload_image_button_funct = false;

jQuery(document).ready(function($) {
	
	jQuery('.powerpress-url').change(function() {
	
		var FeedSlug = this.id.replace(/(powerpress_url_)(.*)$/, "$2");
		if( !FeedSlug )
			return;
		
		var media_url = jQuery(this).val();
		powerpress_check_url(media_url,'powerpress_warning_'+FeedSlug)
		powerpress_update_for_video(media_url, FeedSlug);
	});
	
	jQuery('.powerpress-image-browser').click(function(e) {
		e.preventDefault();
		g_powerpress_last_selected_channel = this.id.replace(/(powerpress_image_browser_)(.*)$/, "$2");
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true&amp;post_id=0', false);

		if( pp_upload_image_button_funct == false )
			pp_upload_image_button_funct = window.send_to_editor;
		
		window.send_to_editor = function(html)
		{
			url = jQuery('img', html).attr('src');
			if (url === undefined) {
				url = jQuery(html).attr('src');
			}
			jQuery('#powerpress_image_'+g_powerpress_last_selected_channel).val( url );
			g_powerpress_last_selected_channel = '';
			tb_remove();
			window.send_to_editor = pp_upload_image_button_funct;
			pp_upload_image_button_funct = false;
		}
		return false;
	});
	jQuery('.powerpress-itunes-image-browser').click(function(e) {
		e.preventDefault();
		g_powerpress_last_selected_channel = this.id.replace(/(powerpress_itunes_image_browser_)(.*)$/, "$2");
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true&amp;post_id=0', false);

		if( pp_upload_image_button_funct == false )
			pp_upload_image_button_funct = window.send_to_editor;
		
		window.send_to_editor = function(html)
		{
			url = jQuery('img', html).attr('src');
			if (url === undefined) {
				url = jQuery(html).attr('src');
			}
			jQuery('#powerpress_itunes_image_'+g_powerpress_last_selected_channel).val( url );
			g_powerpress_last_selected_channel = '';
			tb_remove();
			window.send_to_editor = pp_upload_image_button_funct;
			pp_upload_image_button_funct = false;
		}
		return false;
	});
	jQuery('#insert-media-button').click( function(e) {
		if( pp_upload_image_button_funct != false )
		{
			window.send_to_editor = pp_upload_image_button_funct;
			pp_upload_image_button_funct = false;
		}
	});
	jQuery('.powerpress-embed').change( function() {
		// if there is a value in the embed box, but there is no value in the url box, then we need to display a warning...
		var FeedSlug = this.id.replace(/(powerpress_embed_)(.*)$/, "$2");
		if( !FeedSlug )
			return;
		
		var MediaURL = jQuery('#powerpress_url_'+FeedSlug).val();
		if( !MediaURL )
		{
			jQuery('#powerpress_warning_'+FeedSlug ).text( '<?php echo __('You must enter a Media URL in order to save Media Embed.', 'powerpress'); ?>');
			jQuery('#powerpress_warning_'+FeedSlug ).css('display', 'block');
			jQuery('#powerpress_url_'+FeedSlug).focus();
		}
	});
});

function powerpress_send_to_poster_image(url)
{
	if( g_powerpress_last_selected_channel )
	{
		jQuery('#powerpress_image_'+g_powerpress_last_selected_channel).val( url );
		g_powerpress_last_selected_channel = '';
	}
	tb_remove();
}
//-->
</script>
<?php
	}
	else if( $page_name == 'index' )
	{
		// Print this line for debugging when looking for other pages to include header data for
		//echo "<!-- WP Page Name: $page_name; Hook Suffix: $hook_suffix -->\n";

        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (WP_DEBUG) {
                wp_register_style('powerpress-dashboard', powerpress_get_root_url() . 'css/dashboard.css', array(), POWERPRESS_VERSION);
            } else {
                wp_register_style('powerpress-dashboard', powerpress_get_root_url() . 'css/dashboard.min.css', array(), POWERPRESS_VERSION);
            }
        } else {
            wp_register_style('powerpress-dashboard', powerpress_get_root_url() . 'css/dashboard.min.css', array(), POWERPRESS_VERSION);
        }
        wp_enqueue_style( 'powerpress-dashboard' );
	}
}

add_action('admin_head', 'powerpress_admin_head');

function powerpress_check_account_verified_popup($no_signout_link = false) {
    $link_action_url = admin_url('admin.php?action=powerpress-jquery-account-verify');
    $link_action = 'powerpress-jquery-account-verify';
    $url = wp_nonce_url($link_action_url, $link_action);
    $url = str_replace("&amp;", "&", $url);
    if ($no_signout_link) {
        $no_signout = 'true';
    } else {
        $no_signout = 'false';
    }
    $url = $url . '&no_signout_link=' . $no_signout;
    echo "<input type='hidden' id='verify-account-url' value='" . $url . "'/>";
}

function powerpress_check_credentials($creds) {
    if (isset($creds['client_id'])) {
        if (!isset($creds['account_verified']) || !$creds['account_verified']) {
            powerpress_check_account_verified_popup($_GET['page'] == 'powerpressadmin_onboarding.php');
        }
    }
}

function powerpress_media_info_ajax()
{
    // Check for nonce security
    if (!isset($_POST['nonce'])) {
        exit;
    }
    if ( ! wp_verify_nonce( $_POST['nonce'], 'powerpress-media-info' ) ) {
        exit;
    }
    $feed_slug = htmlspecialchars($_POST['feed_slug']);
	$hosting = $_POST['hosting'];
	$size = 0;
	$duration = '';
	$GeneralSettings = get_option('powerpress_general');
	if (empty($GeneralSettings['blubrry_hosting'])) {
        $hosting = 0;
    }
	if (!$hosting && preg_match('#^(.+)://#', $_POST['media_url'])) {
        $media_url = filter_var($_POST['media_url'], FILTER_VALIDATE_URL);
    } else {
        $media_url = $_POST['media_url'];
    }
	$program_keyword = false;
	if( !empty($_POST['program_keyword']) ) {
		$program_keyword = $_POST['program_keyword'];
	} else if( !empty($GeneralSettings['blubrry_program_keyword']) ) {
		$program_keyword = $GeneralSettings['blubrry_program_keyword'];
	}

    if (!empty($program_keyword) && !preg_match('/[A-Za-z0-9\_]+/', $program_keyword)) {
        exit;
    }
	if( strpos($media_url, 'http://') !== 0 && strpos($media_url, 'https://') !== 0 && $hosting != 1 ) // If the url entered does not start with a http:// or https://
	{
		$media_url = rtrim($GeneralSettings['default_url'], '/') .'/'. $media_url;
	}
	// for media creek urls, just verify the content.blubrry.com version
    $orig_url = false;
    if (strpos($media_url, 'mc.blubrry.com') !== false) {
        $orig_url = $media_url;
        //remove query string
        $media_url_array = explode('?', $media_url);
        $media_url = $media_url_array[0];
        // replace media host
        $media_url = str_replace('mc.blubrry.com', 'content.blubrry.com', $media_url);
    }

    $ContentType = false;
	$UrlParts = parse_url($media_url);
	if( !empty($UrlParts['path']) )
	{
		// using functions that already exist in WordPress when possible:
		$ContentType = powerpress_get_contenttype($UrlParts['path'], false);
	}

    if (!$hosting) {
        $media_hostname = $UrlParts['host'];
        if (in_array($media_hostname, array('0.0.0.0', '127.0.0.1', 'localhost', '[::]', '0x7f000001/', '0xc0a80014/')) || filter_var($media_hostname, FILTER_VALIDATE_IP) || !preg_match('/^[a-zA-Z.\-\d]+$/i', $media_hostname)) {
            $error = __('Invalid url. Please ensure that your url is formatted correctly, e.g https://example.com/filename.mp3. You can still publish this episode, but will need to enter filesize and duration manually.', 'powerpress');
            echo "$feed_slug\n";
            echo $error;
            exit;
        }
    } else {
        // hosting flag means unpublished--use filename only!
        if (substr($media_url, 0, 4) == 'http') {
            $file_array = explode('/', $media_url);
            $file = $file_array[count($file_array) - 1];
            // remove query string
            $file_array = explode('?', $file);
            $media_url = $file_array[0];
        }

    }
	if( !$ContentType )
	{
		$error = __('Unable to determine content type of media (e.g. audio/mpeg). Verify file extension is correct and try again.', 'powerpress');
		echo "$feed_slug\n";
		echo $error;
		exit;
	}
	
	// Get media info here...
	if( $hosting )
		$MediaInfo = powerpress_get_media_info($media_url, $program_keyword );
	else
		$MediaInfo = powerpress_get_media_info_local($media_url, '', 0, '', true);

    //If the file is unpublished, check to make sure the user has hosting space
    if( strpos($media_url, 'http://') !== 0 && strpos($media_url, 'https://') !== 0) {
        if (isset($MediaInfo['space_remaining']) && !$MediaInfo['space_remaining']) {
            if (empty($MediaInfo['error'])) {
                $MediaInfo['error'] = __('Not enough storage space remaining to publish this file.', 'powerpress');
            } else {
                $MediaInfo['error'] .= ' ' . __('Not enough storage space remaining to publish this file.', 'powerpress');
            }
        }
    }

    echo "$feed_slug\n";
    if( $MediaInfo['error'] ) {
        echo $MediaInfo['error'];
        if( preg_match('/^https?\:\/\//i', $media_url) )
            echo '<br />'. sprintf('Test: %s', "<a href=\"" . esc_attr($media_url) . "\" target=\"_blank\">" . htmlspecialchars($media_url) . "</a>");
    }
    echo "\n";

	if( !isset($MediaInfo['error']) )
	{
        //substr('', 'abc');
        echo "VERIFY-OK\n";
        echo "$feed_slug\n"; // swap positions
        if (!empty($MediaInfo['length'])) {
            echo "{$MediaInfo['length']}\n";
        } else {
            echo "\n";
        }
        if (!empty($MediaInfo['duration'])) {
            echo powerpress_readable_duration($MediaInfo['duration'], true) . "\n";
        } else {
            echo "\n";
        }
        echo $orig_url ? htmlspecialchars($orig_url) : htmlspecialchars($MediaInfo['enclosure_url']) . "\n";
        if (isset($MediaInfo['warnings']))
            echo $MediaInfo['warnings'];

        echo "\n"; // make sure this line is ended
        exit;
	}

	exit;
}
 
add_action('wp_ajax_powerpress_media_info', 'powerpress_media_info_ajax');

function powerpress_metamarks_addrow_ajax()
{
    // Check for nonce security
    if (!isset($_POST['nonce'])) {
        exit;
    }
    if ( ! wp_verify_nonce( $_POST['nonce'], 'powerpress-metamarks-addrow' ) ) {
        exit;
    }
	require_once(POWERPRESS_ABSPATH .'/views/episode-box.php');
	powerpress_metamarks_addrow();
}
add_action('wp_ajax_powerpress_metamarks_addrow', 'powerpress_metamarks_addrow_ajax');

function powerpress_dashboard_dismiss_ajax()
{
    // Check for nonce security
    if (!isset($_POST['nonce'])) {
        exit;
    }
    if ( ! wp_verify_nonce( $_POST['nonce'], 'powerpress-dashboard-dismiss' ) ) {
        exit;
    }
	require_once(POWERPRESS_ABSPATH .'/powerpressadmin-dashboard.php');
	powerpress_dashboard_dismiss();
}
add_action('wp_ajax_powerpress_dashboard_dismiss', 'powerpress_dashboard_dismiss_ajax');


function powerpress_create_subscribe_page()
{
    // Check for nonce security
    if (!isset($_POST['nonce'])) {
        exit;
    }
    if ( ! wp_verify_nonce( $_POST['nonce'], 'powerpress-create-subscribe-page' ) ) {
        exit;
    }
	$ajax = ( defined('DOING_AJAX') && DOING_AJAX ); // Now we can use this function without an ajax call! :)
	
	$template_url = 'http://plugins.svn.wordpress.org/powerpress/assets/subscribe_template/';
	$languages = array();
	$language = get_option( 'WPLANG' );
	if( !empty($language) ) {
		switch( $language )
		{
			// Template translated for the following languages
			case 'en_AU':
			case 'en_CA':
			case 'en_GB':
			case 'en_NZ':
			case 'en_ZA': {
				$languages[] = $language; // Placed at the top of array
			}; break;
		}
	}
	$languages[] = 'en_US'; // fallback to the en_US version

	$template_content = false;
	foreach( $languages as $index => $lang )
	{
		$template_content = powerpress_remote_fopen( $template_url . $lang . '.txt' );
		if( empty($template_content) ) { // Lets force cURL and see if that helps...
			$template_content = powerpress_remote_fopen($template_url . $lang . '.txt', false, array(), 15, false, true);
		}
		if( !empty($template_content) ) // We found a translation!
			break;
	}
	
	if( empty($template_content) )
	{
		if( $ajax )
		{
			echo "PAGE-ERROR\n";
			echo __('Error occurred downloading subscribe page template.', 'powerpress');
			exit;
		}
		return false;
	}

	// delete heading stuff from template
    $template_array = explode('[powerpress_subscribe]', $template_content);
	$template_content = '[powerpress_subscribe]' . $template_array[1];
	
	// Create page here...
	global $user_ID;
	$page['post_type']    = 'page';
	$page['post_content'] = $template_content;
	$page['post_parent']  = 0;
	$page['post_author']  = $user_ID;
	$page['post_status']  = 'publish';
	$page['post_title']   = __('Subscribe to Podcast', 'powerpress');
	
	$pageid = wp_insert_post ($page);
	if ($pageid == 0)
	{
		if( $ajax ) {
			echo "PAGE-ERROR\n";
			echo __('Error occurred creating subscribe page.', 'powerpress');
			exit;
		}
		return false;
	}

	// Save to settings...
	$Save = array('subscribe_page_link_id'=>$pageid );
	powerpress_save_settings($Save, 'powerpress_feed_podcast');
	
	// send back the page URL and Page ID
	if( $ajax ) {
		echo "PAGE-OK\n";
		echo "$pageid\n";
		echo get_page_link($pageid). "\n";
		echo $page['post_title']. "\n";
		exit;
	}
	return true;
}
add_action('wp_ajax_powerpress_create_subscribe_page', 'powerpress_create_subscribe_page');

function powerpress_cat_row_actions($actions, $object)
{
	$General = get_option('powerpress_general');
	
	
	// New 3.0+ tag in taxonomy check
	if( !empty($General['tag_casting']) && !empty($object->taxonomy) && $object->taxonomy == 'tag' )
	{
		// TODO:
	}
	
	// Otherwise from here on in, we're working with a category or nothing at all.
	if( empty($General['cat_casting']) )
		return $actions;
	
	// 3.0 category in taxonomy check
	if( !empty($object->taxonomy) && $object->taxonomy != 'category' )
		return $actions;
		
	$cat_id = (isset($object->term_id)?$object->term_id : $object->cat_ID);
	
	if( empty($cat_id) )
		return $actions;
	
	if( isset($General['custom_cat_feeds']) && is_array($General['custom_cat_feeds']) && in_array($cat_id, $General['custom_cat_feeds']) )
	{
		$edit_link = admin_url('admin.php?page=powerpress/powerpressadmin_categoryfeeds.php&amp;from_categories=1&amp;action=powerpress-editcategoryfeed&amp;cat=') . $cat_id;
		$actions['powerpress'] = '<a href="' . $edit_link . '" title="'. __('Edit Blubrry PowerPress Podcast Settings', 'powerpress') .'">' . str_replace(' ', '&nbsp;', __('Podcast Settings', 'powerpress')) . '</a>';
	}
	else
	{
		$edit_link = admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_categoryfeeds.php&amp;from_categories=1&amp;action=powerpress-addcategoryfeed&amp;taxonomy=category&amp;cat=".$cat_id, 'powerpress-add-taxonomy-feed');
		$actions['powerpress'] = '<a href="' . $edit_link . '" title="'. __('Add Blubrry PowerPress Podcasting Settings', 'powerpress') .'">' . str_replace(' ', '&nbsp;', __('Add Podcasting', 'powerpress')) . '</a>';
	}
	return $actions;
}

add_filter('cat_row_actions', 'powerpress_cat_row_actions', 1,2);
add_filter('tag_row_actions', 'powerpress_cat_row_actions', 1,2);

// Handles category and all other taxonomy terms
function powerpress_delete_term($term_id, $tt_id, $taxonomy)
{
	if( $taxonomy == 'category' )
	{
		$Settings = get_option('powerpress_general');
		if( isset($Settings['custom_cat_feeds']) )
		{
			$key = array_search($term_id, $Settings['custom_cat_feeds']);
			if( $key !== false )
			{
				unset( $Settings['custom_cat_feeds'][$key] );
				powerpress_save_settings($Settings); // Delete the feed from the general settings
			}
		}
		delete_option('powerpress_cat_feed_'.$term_id); // Delete the actual feed settings
	}
	else // All other taxonomies handled here
	{
		$Settings = get_option('powerpress_taxonomy_podcasting');
		
		if( isset($Settings[ $tt_id ])  )
		{
			unset( $Settings[ $tt_id ] );
			powerpress_save_settings($Settings); // Delete the feed from the general settings
		}
		delete_option('powerpress_taxonomy_'.$tt_id); // Delete the actual feed settings
	}
}

add_action('delete_term', 'powerpress_delete_term', 10, 3);


function powerpress_edit_category_form($cat)
{
	if( empty($cat) || !isset( $cat->cat_ID ) )
	{
?>
<div>
<?php
		$General = get_option('powerpress_general');
		if( !isset($General['cat_casting']) || $General['cat_casting'] == 0 )
		{
			$enable_link = admin_url() . wp_nonce_url('edit-tags.php?taxonomy=category&action=powerpress-enable-categorypodcasting', 'powerpress-enable-categorypodcasting');
?>
	<h2><?php echo __('PowerPress Category Podcasting'); ?></h2>
	<p><a href="<?php echo $enable_link; ?>" title="<?php echo __('Enable Category Podcasting', 'powerpress'); ?>"><?php echo __('Enable Category Podcasting', 'powerpress'); ?></a> <?php echo __('if you would like to add specific podcasting settings to your blog categories.', 'powerpress'); ?></p>
<?php
		}
		else
		{
?>
	<h2><?php echo __('PowerPress Category Podcasting', 'powerpress'); ?></h2>
	<p><?php echo __('PowerPress Category Podcasting is enabled. Select \'Add Podcasting\' to add podcasting settings. Select <u>Podcast Settings</u> to edit existing podcast settings.', 'powerpress'); ?></p>
<?php
		}
?>
</div>
<?php
	}
}
add_action('category_edit_form', 'powerpress_edit_category_form');

// Admin page, header
function powerpress_admin_page_header($page=false, $nonce_field = 'powerpress-edit', $page_type='')
{
	if( !$page )
		$page = 'powerpressadmin_basic';
?>
<div class="wrap" id="powerpress_settings">
<?php
	if( $nonce_field )
	{
?>
<form enctype="multipart/form-data" method="post" action="<?php echo admin_url( 'admin.php?page='. urlencode($page) ) ?>">
<?php
		wp_nonce_field($nonce_field);
	}
	if( !empty($page_type) )
		echo '<input type="hidden" name="page_type" value="'. $page_type .'" />';
			
	powerpress_page_message_print();
}

// Admin page, footer
function powerpress_admin_page_footer($SaveButton=true, $form=true)
{
	if( $SaveButton ) { ?>
<h2 style="margin-bottom: 0;"><b><?php echo __('Looking for Support?','powerpress'); ?></b></h2>
<p class="submit" style="margin-top: 0; margin-left: 2em;">
    <a title="<?php echo esc_attr(__('Blubrry Services Info', 'powerpress')); ?>"
       href="https://blubrry.com/services/podcast-hosting/"
       target="_blank"><?php echo __('Learn more about Blubrry Support Options', 'powerpress'); ?></a>
</p>
<p class="submit">
<strong><i><?php echo powerpress_review_message(); ?></i></strong>
</p>
<?php } ?>
<p style="font-size: 85%; text-align: center; padding-bottom: 35px;">
	<a href="https://blubrry.com/services/powerpress-plugin/" title="Blubrry PowerPress" target="_blank"><?php echo __('Blubrry PowerPress', 'powerpress'); ?></a> <?php echo POWERPRESS_VERSION; ?> &#8212;
	<a href="https://blubrry.com/services/podcast-hosting/" target="_blank" title="<?php echo __('Blubrry PowerPress and related Resources', 'powerpress'); ?>"><?php echo __('Resources', 'powerpress'); ?></a> |
	<a href="https://blubrry.com/support/" target="_blank" title="<?php echo __('Blubrry Support', 'powerpress'); ?>"><?php echo __('Support', 'powerpress'); ?></a>
</p>
<?php if( $form ) { ?>
</form><?php } ?>
</div>
<?php 
}

// Admin page, footer
function powerpress_settings_save_button($blue = false, $float_left = false)
{
    if ($float_left) {
        $float_string = '';
    } else {
        $float_string = ' pp-save-button-container-right';
    }
    if ($blue) {
        $class = "powerpress_save_button_other";
    } else {
        $class = "powerpress_save_button";
    }?>
    <div class="pp-save-button-container<?php echo $float_string; ?>">
        <input type="submit" name="Submit" class="<?php echo $class; ?>" value="<?php echo __('Save Changes', 'powerpress') ?>" />
    </div>
    <?php
}

// Admin page, advanced mode: basic settings
function powerpress_admin_page_basic()
{
	powerpress_admin_page_header();
	require_once( POWERPRESS_ABSPATH .'/powerpressadmin-basic.php');
	require_once( POWERPRESS_ABSPATH .'/powerpressadmin-editfeed.php');
	powerpress_admin_basic();
	powerpress_admin_page_footer(true);
}

// Admin page, advanced mode: basic settings
function powerpress_admin_page_players()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_player.php');
	require_once( POWERPRESS_ABSPATH.'/powerpressadmin-player-page.php');
	powerpress_admin_players('audio');
	powerpress_admin_page_footer(true);
}

function powerpress_admin_page_videoplayers()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_videoplayer.php');
	require_once( POWERPRESS_ABSPATH.'/powerpressadmin-player-page.php');
	powerpress_admin_players('video');
	powerpress_admin_page_footer(true);
}

function powerpress_admin_page_mobileplayers()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_mobileplayer.php');
	require_once( POWERPRESS_ABSPATH.'/powerpressadmin-player-page.php');
	powerpress_admin_players('mobile');
    powerpress_settings_save_button(true);
	powerpress_admin_page_footer(true);
}

// Admin page, advanced mode: feed settings
function powerpress_admin_page_podpress_stats()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_podpress-stats.php');
	require_once( POWERPRESS_ABSPATH .'/powerpressadmin-podpress-stats.php');
	powerpress_admin_podpress_stats();
	powerpress_admin_page_footer(false);
}

// Migrate
function powerpress_admin_page_migrate()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_migrate.php');
	require_once( POWERPRESS_ABSPATH .'/powerpressadmin-migrate.php');
	powerpress_admin_migrate();
	powerpress_admin_page_footer(false);
}

function powerpress_admin_page_import_feed()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_import_feed.php');
	require_once( POWERPRESS_ABSPATH .'/powerpressadmin-import-feed.php');
	powerpress_admin_import_feed();
	powerpress_admin_page_footer(false);
}

function marketing_footer($button = false) {
    // only display if not signed in
    $creds = get_option('powerpress_creds');
    if (!$creds) {
        $pp_nonce = powerpress_login_create_nonce();
        echo '<div class="powerpress-blubrry-hosting-marketing">';
        echo '<img class="hosting-marketing-blubrry-logo" src="' . powerpress_get_root_url() . 'images/Blubrry2017Logo-LOWERCASE.svg" alt="Blubrry Podcasting" />';
        echo '<div class="hosting-marketing-text-container" ';
        if ($button) {
            echo 'style="width: 55%">';
        } else {
            echo 'style="width: 70%">';
        }
        echo '<h2 class="hosting-marketing-title">Publish everywhere from this website</h2>';
        echo '<h4 class="hosting-marketing-text">You want podcast hosting that\'s easy to use with full control. ';
        if ($button) {
            echo 'Try Blubrry hosting ';
        } else {
            echo '<a class="hosting-marketing-text" href="' . add_query_arg( '_wpnonce', $pp_nonce, admin_url("admin.php?page={$_GET['page']}&step=blubrrySignup&onboarding_type=stats")) . '" target="_blank">Try Blubrry hosting</a> ';
        }
        echo 'for free and enjoy additional publishing features with PowerPress.</h4></div>';
        if ($button) {
            echo '<a class="hosting-marketing-link-button" href="' . add_query_arg( '_wpnonce', $pp_nonce, admin_url("admin.php?page={$_GET['page']}&step=blubrrySignup&onboarding_type=stats")) . '" target="_blank">';
            echo '<button class="hosting-marketing-button">TRY BLUBRRY HOSTING FOR FREE</button>';
            echo '</a>';
        }
        echo '</div>';
    }
}

function powerpress_admin_page_onboarding() {
    powerpress_admin_page_header('powerpress/powerpressadmin_onboarding.php', false);
    require_once( POWERPRESS_ABSPATH .'/class.powerpress_onboarding.php');
    $onboardinClass = new PowerpressOnboarding();
    $onboardinClass->router($_GET);
    powerpress_admin_page_footer(false, false);
}


// Admin page, advanced mode: custom feeds
function powerpress_admin_page_customfeeds()
{
	$Action = (!empty($_GET['action'])? $_GET['action'] : false);

    if (!$Action) {
        $Action = (!empty($_POST['action'])? $_POST['action'] : false);
    }

    switch( $Action )
	{
		case 'powerpress-editfeed' : {
			powerpress_admin_page_header('powerpress/powerpressadmin_customfeeds.php');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-editfeed.php');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-basic.php');
			$feed_slug = esc_attr($_GET['feed_slug']);
			powerpress_admin_editfeed('channel', $feed_slug);
			powerpress_admin_page_footer();
		}; break;
		default: {
			powerpress_admin_page_header('powerpress/powerpressadmin_customfeeds.php', 'powerpress-add-feed');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-customfeeds.php');
			powerpress_admin_customfeeds();
			powerpress_admin_page_footer(false);
		};
	}
}

function powerpress_admin_page_live_item()
{
    $Action = (!empty($_GET['action'])? $_GET['action'] : false);
    switch( $Action )
    {
        case 'powerpress-editfeed' : {
            powerpress_admin_page_header('powerpress/powerpressadmin_customfeeds.php');
            require_once( POWERPRESS_ABSPATH .'/powerpressadmin-editfeed.php');
            require_once( POWERPRESS_ABSPATH .'/powerpressadmin-basic.php');
            $feed_slug = esc_attr($_GET['feed_slug']);
            powerpress_admin_editfeed('channel', $feed_slug);
            powerpress_admin_page_footer();
        }; break;
        default: {
            powerpress_admin_page_header('powerpress/powerpressadmin_live_item.php', 'powerpress-live-item');
            require_once( POWERPRESS_ABSPATH .'/powerpressadmin-live-item.php');
            powerpress_admin_live_item();
            powerpress_admin_page_footer(false);
        };
    }
}

// Category feeds
function powerpress_admin_page_categoryfeeds()
{
	$Action = (!empty($_GET['action'])? $_GET['action'] : false);
	switch( $Action )
	{
		case 'powerpress-editcategoryfeed' : {
			powerpress_admin_page_header('powerpress/powerpressadmin_categoryfeeds.php');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-editfeed.php');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-basic.php');
			powerpress_admin_editfeed('category', intval($_GET['cat']) );
			powerpress_admin_page_footer();
		}; break;
		default: {
			powerpress_admin_page_header('powerpress/powerpressadmin_categoryfeeds.php', 'powerpress-add-categoryfeed');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-categoryfeeds.php');
			powerpress_admin_categoryfeeds();
			powerpress_admin_page_footer(false);
		};
	}
}

// Taxonomy Feeds
function powerpress_admin_page_taxonomyfeeds()
{
	$Action = (!empty($_GET['action'])? $_GET['action'] : false);
	switch( $Action )
	{
		case 'powerpress-edittaxonomyfeed' : {
			if( !empty($_GET['ttid']) )
			{
				powerpress_admin_page_header('powerpress/powerpressadmin_taxonomyfeeds.php');
				require_once( POWERPRESS_ABSPATH .'/powerpressadmin-editfeed.php');
				require_once( POWERPRESS_ABSPATH .'/powerpressadmin-basic.php');
				powerpress_admin_editfeed('ttid', intval($_GET['ttid']));
				powerpress_admin_page_footer();
			}
		}; break;
		default: {
			powerpress_admin_page_header('powerpress/powerpressadmin_taxonomyfeeds.php', 'powerpress-add-taxonomyfeed');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-taxonomyfeeds.php');
			powerpress_admin_taxonomyfeeds();
			powerpress_admin_page_footer(false);
		};
	}
}

// Custom Post Type Feeds
function powerpress_admin_page_posttypefeeds()
{
	
	$Action = (!empty($_GET['action'])? $_GET['action'] : false);
	switch( $Action )
	{
		case 'powerpress-editposttypefeed' : {
			if( !empty($_GET['podcast_post_type']) && !empty($_GET['feed_slug']) ) {
				
				powerpress_admin_page_header('powerpress/powerpressadmin_posttypefeeds.php');
				require_once( POWERPRESS_ABSPATH .'/powerpressadmin-editfeed.php');
				require_once( POWERPRESS_ABSPATH .'/powerpressadmin-basic.php');
				$post_type = esc_attr( $_GET['podcast_post_type'] );
				$feed_slug = esc_attr( $_GET['feed_slug'] );
				powerpress_admin_editfeed('post_type', $post_type, $feed_slug);
				powerpress_admin_page_footer();
				
			}
		} break; 
		default: {
			powerpress_admin_page_header('powerpress/powerpressadmin_posttypefeeds.php', 'powerpress-add-posttypefeed');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-posttypefeeds.php');
			powerpress_admin_posttypefeeds();
			powerpress_admin_page_footer(false);
		};
	}
}

// Admin page, advanced mode: tools
function powerpress_admin_page_tools()
{
	$Action = (!empty($_GET['action'])? $_GET['action'] : false);
	if ($Action == 'powerpress-network-mode-off') {
        $GeneralSettings = get_option('powerpress_general');
        $GeneralSettings['network_mode'] = 0;
        powerpress_save_settings($GeneralSettings);
        $Action = '';
    } elseif ($Action == 'powerpress-network-mode-on') {
        $GeneralSettings = get_option('powerpress_general');
	    $GeneralSettings['network_mode'] = 1;
        powerpress_save_settings($GeneralSettings);
        $Action = '';
    }
	switch( $Action )
	{
		case 'powerpress-podpress-epiosdes' : {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', 'powerpress-import-podpress');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-podpress.php');
			powerpress_admin_podpress();
			powerpress_admin_page_footer(false);
		}; break;
		case 'powerpress-mt-epiosdes': {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', 'powerpress-import-mt');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-mt.php');
			powerpress_admin_mt();
			powerpress_admin_page_footer(false);
		}; break;
		case 'powerpress-ping-sites': {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', 'powerpress-ping-sites');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-ping-sites.php');
			powerpress_admin_ping_sites();
			powerpress_admin_page_footer(false);
		}; break;
		case 'powerpress-find-replace': {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', 'powerpress-find-replace');
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-find-replace.php');
			powerpress_admin_find_replace();
			powerpress_admin_page_footer(false);
		}; break;
		case 'powerpress-diagnostics': {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', false);
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-diagnostics.php');
			powerpressadmin_diagnostics();
			powerpress_admin_page_footer(false, false);
		}; break;
		default: {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', false);
			require_once( POWERPRESS_ABSPATH .'/powerpressadmin-tools.php');
			powerpress_admin_tools();
			powerpress_admin_page_footer(false, false);
		};
	}
}

function powerpress_podpress_episodes_exist()
{
	global $wpdb;
	$query = "SELECT post_id ";
	$query .= "FROM {$wpdb->postmeta} ";
	$query .= "WHERE meta_key LIKE '%podPressMedia' ";
	$query .= "LIMIT 0, 1";
	$results = $wpdb->get_results($query, ARRAY_A);
	if( count($results) )
		return true;
	return false;
}

function powerpress_podpress_stats_exist()
{
	global $wpdb;
	// First, see if the table exists...
	$query = "SHOW TABLES LIKE '{$wpdb->prefix}podpress_statcounts'";
	$wpdb->hide_errors();
	$results = $wpdb->get_results($query, ARRAY_A);
	$wpdb->show_errors();
	if( count($results) == 0 )
		return false;
	
	// Now see if a record exists...
	$query = "SELECT `media` ";
	$query .= "FROM {$wpdb->prefix}podpress_statcounts ";
	$query .= "LIMIT 1";
	$results = $wpdb->get_results($query, ARRAY_A);
	if( count($results) )
		return true;
	return false;
}

/*
// Helper functions:
*/
function powerpress_remote_fopen($url, $basic_auth = false, $post_args = array(), $timeout = 15, $custom_request = false, $force_curl=false )
{
	unset($GLOBALS['g_powerpress_remote_error']);
	unset($GLOBALS['g_powerpress_remote_errorno']);
	
	if( ($force_curl || (defined('POWERPRESS_CURL') && POWERPRESS_CURL) ) && function_exists( 'curl_init' ) )
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		
		if ( version_compare( PHP_VERSION, '5.3.0') < 0 )
		{
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // Follow location redirection
			curl_setopt($curl, CURLOPT_MAXREDIRS, 12); // Location redirection limit
		}
		else if ( !ini_get('open_basedir') )
		{
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // Follow location redirection
			curl_setopt($curl, CURLOPT_MAXREDIRS, 12); // Location redirection limit
		}
		else
		{
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($curl, CURLOPT_MAXREDIRS, 0 );
		}

		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2 ); // Connect time out
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); // The maximum number of seconds to execute.
		curl_setopt($curl, CURLOPT_USERAGENT, 'Blubrry PowerPress/'.POWERPRESS_VERSION);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		if( preg_match('/^https:\/\//i', $url) != 0 )
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2 );
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt($curl, CURLOPT_CAINFO, ABSPATH . WPINC . '/certificates/ca-bundle.crt');
		}
		// HTTP Authentication
		if( $basic_auth )
		{
			curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$basic_auth) );
		}
		// HTTP Post:
		if( count($post_args) > 0 )
		{
			$post_query = '';
			foreach( $post_args as $name => $value )
			{
				if( $post_query != '' )
					$post_query .= '&';
				$post_query .= $name;
				$post_query .= '=';
				$post_query .= urlencode($value);
			}
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_query);
		}
		else if( $custom_request )
		{
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $custom_request);
		}
		
		$content = curl_exec($curl);
		$error = curl_errno($curl);
		$error_msg = curl_error($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		
		curl_close($curl);
		if( $error )
		{
			$GLOBALS['g_powerpress_remote_error'] = $error_msg;
			$GLOBALS['g_powerpress_remote_errorno'] = $http_code;
			//echo 'error: '.$content;
			
			$decoded = json_decode($content);
			if( !empty($decoded) )
				return $content; // We can still return the error from the server at least
			return false;
		}
		else if( $http_code > 399 )
		{
			//echo '40x';
			$GLOBALS['g_powerpress_remote_error'] = "HTTP $http_code";
			$GLOBALS['g_powerpress_remote_errorno'] = $http_code;
			switch( $http_code )
			{
				case 400: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Bad Request", 'powerpress'); break;
				case 401: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Unauthorized (Check that your username and password are correct)", 'powerpress'); break;
				case 402: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Payment Required", 'powerpress'); break;
				case 403: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Forbidden", 'powerpress'); break;
				case 404: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Not Found", 'powerpress'); break;
			}
			
			$decoded = json_decode($content);
			if( !empty($decoded) )
				return $content; // We can still return the error from the server at least
			return false;
		}
		return $content;
	}
	
	if( $force_curl )
		return false; // Do not continue, we wanted to use cURL
	
	$options = array();
	$options['timeout'] = $timeout;
	$options['user-agent'] = 'Blubrry PowerPress/'.POWERPRESS_VERSION;
	if( $basic_auth )
		$options['headers']['Authorization'] = 'Basic '.$basic_auth;
	
	if( count($post_args) > 0 )
	{
		$options['body'] = $post_args;
		$response = wp_remote_post( $url, $options );
	}
	else if($custom_request) {
	    $options['method'] = $custom_request;
	    $response = wp_remote_request($url,$options);
    }
	else
	{
		$response = wp_remote_get( $url, $options );
	}
	
	if ( is_wp_error( $response ) )
	{
		$GLOBALS['g_powerpress_remote_errorno'] = $response->get_error_code();
		$GLOBALS['g_powerpress_remote_error'] = $response->get_error_message();
		return false;
	}
	
	if( isset($response['response']['code']) && $response['response']['code'] > 399 )
	{
		$GLOBALS['g_powerpress_remote_error'] = "HTTP ".$response['response']['code'];
		$GLOBALS['g_powerpress_remote_errorno'] = $response['response']['code'];
		switch( $response['response']['code'] )
		{
			case 400: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Bad Request", 'powerpress'); break;
			case 401: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Unauthorized (Check that your username and password are correct)", 'powerpress'); break;
			case 402: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Payment Required", 'powerpress'); break;
			case 403: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Forbidden", 'powerpress'); break;
			case 404: $GLOBALS['g_powerpress_remote_error'] .= ' '. __("Not Found", 'powerpress'); break;
			default: $GLOBALS['g_powerpress_remote_error'] .= ' '.$response['response']['message'];
		}
	}

	return $response['body'];
}

// Process any episodes for the specified post that have been marked for hosting and that do not have full URLs...
function powerpress_process_hosting($post_ID, $post_title)
{
	$errors = array();
	$Settings = get_option('powerpress_general');
    $creds = get_option('powerpress_creds');
    require_once(POWERPRESS_ABSPATH .'/powerpressadmin-auth.class.php');
    $auth = new PowerPressAuth();

    $CustomFeeds = array();
	if( !empty($Settings['custom_feeds']) && is_array($Settings['custom_feeds']) )
		$CustomFeeds = $Settings['custom_feeds'];
	if( !isset($CustomFeeds['podcast']) )
		$CustomFeeds['podcast'] = 'podcast';
		
	
	if( !empty($Settings['posttype_podcasting']) )
	{
		$FeedSlugPostTypesArray = get_option('powerpress_posttype-podcasting');
		foreach( $FeedSlugPostTypesArray as $feed_slug => $null )
		{
			if( empty($CustomFeeds[$feed_slug]) )
				$CustomFeeds[$feed_slug] = $feed_slug;
		}
	}

    $api_url_array = powerpress_get_api_array();
	foreach( $CustomFeeds as $feed_slug => $null )
	{
		$field = 'enclosure';
		if( $feed_slug != 'podcast' )
			$field = '_'.$feed_slug.':enclosure';
		$EnclosureData = get_post_meta($post_ID, $field, true);
		$post_guid = get_the_guid($post_ID);
		$post_time = get_post_time('U', false, $post_ID);

		if( $EnclosureData )
		{
			/*
			// Old Logic, replaced with below $MetaParts so no notices appear
			list($EnclosureURL, $EnclosureSize, $EnclosureType, $Serialized) = explode("\n", $EnclosureData, 4);
			$EnclosureURL = trim($EnclosureURL);
			$EnclosureType = trim($EnclosureType);
			$EnclosureSize = trim($EnclosureSize);
			$EpisodeData = unserialize($Serialized);
			*/
			$MetaParts = explode("\n", $EnclosureData, 4);
			$EnclosureURL = '';
			if( count($MetaParts) > 0 )
				$EnclosureURL = trim($MetaParts[0]);
			
			$EnclosureSize = '';
			if( count($MetaParts) > 1 )
				$EnclosureSize = trim($MetaParts[1]);
			$EnclosureType = '';
			if( count($MetaParts) > 2 )
				$EnclosureType = trim($MetaParts[2]);
				
			$EpisodeData = false;
			if( count($MetaParts) > 3 )
				$EpisodeData = unserialize($MetaParts[3]);
				
			if( $EnclosureType == '' )
			{
				$error = __('Blubrry Hosting Error (publish)', 'powerpress') .': '. __('Error occurred obtaining enclosure content type.', 'powerpress');
				powerpress_add_error($error);
			}

            $episode_art = $Settings['itunes_image'] ?? '';

			if( $EpisodeData && (!empty($EpisodeData['hosting']) || !empty($_POST['Powerpress'][$feed_slug]['transcript']['edit'])))
			{
                $episode_art = $EpisodeData['image'] ?? $episode_art;
				$program_keyword = (!empty($EpisodeData['program_keyword']) ? $EpisodeData['program_keyword'] : $Settings['blubrry_program_keyword'] );
				$error = false;
				if (!empty($EpisodeData['hosting'])) {
                    // First we need to get media information...
                    // If we are working with an Mp3, we can write id3 tags and get the info returned...
                    if (($EnclosureType == 'audio/mpg' || $EnclosureType == 'audio/mpeg') && !empty($Settings['write_tags'])) {
                        $results = powerpress_write_tags($EnclosureURL, $post_title, $program_keyword);
                        // if error, try inputting just the file name in case it's unpublished
                        if ((strpos($EnclosureURL, 'ins.blubrry.com') || strpos($EnclosureURL, 'content.blubrry.com') || strpos($EnclosureURL, 'protected.blubrry.com') || strpos($EnclosureURL, 'mc.blubrry.com')) && (isset($results['error']) || !is_array($results) || !$results)) {
                            $EnclosureUrLArray = explode('/', $EnclosureURL);
                            $EnclosureURL = end($EnclosureUrLArray);
                            $EnclosureUrlQSArray = explode('?', $EnclosureURL);
                            $EnclosureURL = $EnclosureUrlQSArray[0];
                            $results = powerpress_write_tags($EnclosureURL, $post_title, $program_keyword);
                        }
                    } else {
                        // order of logic below is reversed due to changes in powerpress_get_media_info to properly verify media in drafts
                        // first, try as if the file is unpublished
                        $EnclosureFullURL = $EnclosureURL;
                        $EnclosureUrLArray = explode('/', $EnclosureURL);
                        $EnclosureURL = end($EnclosureUrLArray);
                        $EnclosureUrlQSArray = explode('?', $EnclosureURL);
                        $EnclosureURL = $EnclosureUrlQSArray[0];
                        $results = powerpress_get_media_info($EnclosureURL, $program_keyword);

                        // if error, try the full URL
                        if ((isset($results['error']) || !is_array($results))) {
                            $EnclosureURL = $EnclosureFullURL;
                            $results = powerpress_get_media_info($EnclosureURL, $program_keyword);
                        }
                    }

                    if (is_array($results) && !isset($results['error'])) {
                        if (isset($results['duration']) && $results['duration'])
                            $EpisodeData['duration'] = $results['duration'];
                        if (isset($results['content-type']) && $results['content-type'])
                            $EnclosureType = $results['content-type'];
                        if (isset($results['length']) && $results['length'])
                            $EnclosureSize = $results['length'];
                    } else if (isset($results['error'])) {
                        $error = __('Blubrry Hosting Error (media info)', 'powerpress') . ': ' . $results['error'];
                        powerpress_add_error($error);
                    } else {
                        $error = sprintf(__('Blubrry Hosting Error (media info): An error occurred publishing media %s.', 'powerpress'), $EnclosureURL);
                        $error .= ' ';
                        $rand_id = rand(100, 2000);
                        $error .= '<a href="#" onclick="document.getElementById(\'powerpress_error_' . $rand_id . '\');this.style.display=\'none\';return false;">' . __('Display Error', 'powerpress') . '</a>';
                        powerpress_add_error($error);
                    }

                    if ($error == false) {
                        $post_vars = array('podcast_guid' => $post_guid, 'episode_art' => $episode_art, 'podcast_post_date' => $post_time, 'podcast_title' => $post_title, 'podcast_subtitle' => isset($EpisodeData['subtitle']) ? $EpisodeData['subtitle'] : '');
                        // Extend the max execution time here
                        @set_time_limit(60 * 20); // give it 20 minutes just in case
                        $json_data = false;
                        if ($creds) {
                            $accessToken = powerpress_getAccessToken();
                            $req_url = sprintf('/2/media/%s/%s?format=json&publish=true&cache=' . md5(rand(0, 999) . time()), urlencode($program_keyword), urlencode($EnclosureURL));
                            $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '?' . POWERPRESS_BLUBRRY_API_QSA : '');
                            $req_url .= (defined('POWERPRESS_PUBLISH_PROTECTED') ? '&protected=true' : '');
                            $results = $auth->api($accessToken, $req_url, $post_vars, false, 60 * 30);
                        } else {
                            foreach ($api_url_array as $index => $api_url) {
                                $req_url = sprintf('%s/media/%s/%s?format=json&publish=true&cache=' . md5(rand(0, 999) . time()), rtrim($api_url, '/'), urlencode($program_keyword), urlencode($EnclosureURL));
                                $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '&' . POWERPRESS_BLUBRRY_API_QSA : '');
                                $req_url .= (defined('POWERPRESS_PUBLISH_PROTECTED') ? '&protected=true' : '');
                                $json_data = powerpress_remote_fopen($req_url, $Settings['blubrry_auth'], $post_vars, 60 * 30); // give this up to 30 minutes, though 3 seocnds to 20 seconds is all one should need.
                                if (!$json_data && $api_url == 'https://api.blubrry.com/') { // Lets force cURL and see if that helps...
                                    $json_data = powerpress_remote_fopen($req_url, $Settings['blubrry_auth'], $post_vars, 60 * 30, false, true);
                                }
                                if ($json_data != false)
                                    break;
                            }

                            $results = powerpress_json_decode($json_data);
                        }

                        if (is_array($results) && !isset($results['error'])) {
                            $EnclosureURL = $results['media_url'];
                            if (strtolower(substr($results['media_url'], 0, 4)) != 'http') {
                                $error = __('Blubrry Hosting Error (publish): Please re-upload media file and re-publish post', 'powerpress');
                                powerpress_add_error($error);
                            }
                            unset($EpisodeData['hosting']); // we need to remove the flag since we're now using the correct FULL url

                            // save podcast id
                            if (!empty($results['podcast_id'])) {
                                $EpisodeData['podcast_id'] = $results['podcast_id'];
                            }

                            $EnclosureData = $EnclosureURL . "\n" . $EnclosureSize . "\n" . $EnclosureType . "\n" . serialize($EpisodeData);
                            update_post_meta($post_ID, $field, $EnclosureData);
                        } else if (isset($results['error'])) {
                            $error = __('Blubrry Hosting Error (publish)', 'powerpress') . ': ' . $results['error'];
                            powerpress_add_error($error);
                        } else {
                            $rand_id = rand(100, 2000);
                            $error = __('Blubrry Hosting Error (publish)', 'powerpress') . ': ' . sprintf(__('An error occurred publishing media \'%s\'.', 'powerpress'), $EnclosureURL);
                            $error .= ' <a href="#" onclick="document.getElementById(\'powerpress_error_' . $rand_id . '\').style.display=\'block\';this.style.display=\'none\';return false;">' . __('Display Error', 'powerpress') . '</a>';
                            $error .= '<div id="powerpress_error_' . $rand_id . '" style="display: none;">';
                            if (!empty($json_data))
                                $error .= $json_data;
                            else if (!empty($GLOBALS['g_powerpress_remote_error']))
                                $error .= htmlspecialchars($GLOBALS['g_powerpress_remote_error']);
                            else
                                $error .= __('Unknown error occurred.', 'powerpress');
                            $error .= '</div>';
                            powerpress_add_error($error);
                        }
                    }
                }
                // new transcript call here
                // only for hosting customers and Blubrry hosted media
                // transcript url is already saved; hosting call will replace it with a hosted version
                if (!empty($_POST['Powerpress'][$feed_slug]['transcript']['edit'])) {
                    $blubrry_hosted_media = true;
                    if (strpos($EnclosureURL, 'http://') !== false || strpos($EnclosureURL, 'https://') !== false) {
                        $blubrry_hosted_media = false;
                        if (strpos($EnclosureURL, 'content.blubrry.com') !== false) {
                            $blubrry_hosted_media = true;
                        }
                        if (strpos($EnclosureURL, 'ins.blubrry.com') !== false) {
                            $blubrry_hosted_media = true;
                        }
                        if (strpos($EnclosureURL, 'mc.blubrry.com') !== false) {
                            $blubrry_hosted_media = true;
                        }
                    }
                    if (!empty($Settings['blubrry_hosting']) && $blubrry_hosted_media) {
                        $podcastId = false;
                        if (!empty($results['podcast_id'])) {
                            $EpisodeData['podcast_id'] = $results['podcast_id'];
                            $podcastId = $results['podcast_id'];
                        } else {
                            // try using one saved in the postmeta
                            $postmeta = get_post_meta($post_ID, $field, true);
                            if (!empty($postmeta['podcast_id'])) {
                                $podcastId = $postmeta['podcast_id'];
                            }
                        }

                        if ($podcastId) {
                            $podcastSearchAnd = "&podcast_id=" . intval($podcastId);
                        } else {
                            // pass in a media_url if we don't have a podcast id
                            $podcastSearchAnd = "&media_url=" . urlencode($EnclosureURL);
                        }
                        if( !empty($EpisodeData['podcast_id']) ) {
                            $purgeUrl = 'https://player.blubrry.com/?podcast_id='. intval($EpisodeData['podcast_id']);
                            $purgeUrl .= '&media_url='. urlencode($EnclosureURL);
                            if (!empty($Settings['player']) && $Settings['player'] == 'blubrrymodern') {
                                $purgeUrl .= '&modern=1';
                            }
                        } else {
                            $purgeUrl = 'https://player.blubrry.com/?media_url='. urlencode($EnclosureURL);
                            if (!empty($Settings['player']) && $Settings['player'] == 'blubrrymodern') {
                                $purgeUrl .= '&modern=1';
                            }
                            if( !empty($EpisodeData['id']) ) {
                                // Get permalink URL
                                $permalink = get_permalink( $EpisodeData['id'] );
                                if( !empty($permalink) )
                                    $purgeUrl.= '&podcast_link='. urlencode($permalink);
                            }
                            if( !empty($EpisodeData['itunes_image']) ) {
                                if(isset($Settings['bp_episode_image']) && $Settings['bp_episode_image'] != false)
                                    $purgeUrl.= '&artwork_url='. urlencode($EpisodeData['itunes_image']);
                            }
                        }
                        $podcastSearchAnd .= "&purge_url=" . urlencode($purgeUrl);
                        $EnclosureUrLArray = explode('/', $EnclosureURL);
                        $EnclosureURLQS = end($EnclosureUrLArray);
                        $EnclosureUrlQSArray = explode('?', $EnclosureURLQS);
                        $EnclosureFilename = $EnclosureUrlQSArray[0];
                        if ((!empty($_POST['Powerpress'][$feed_slug]['transcript']['generate']) || (!empty($_POST['Powerpress'][$feed_slug]['transcript']['upload']) && !empty($_POST['Powerpress'][$feed_slug]['pci_transcript_url'])))) {
                            if (!empty($_POST['Powerpress'][$feed_slug]['pci_transcript_url']) && !empty($_POST['Powerpress'][$feed_slug]['transcript']['upload']))
                                $podcastSearchAnd .= '&transcript_url=' . urlencode($_POST['Powerpress'][$feed_slug]['pci_transcript_url']);

                            if (!empty($_POST['Powerpress'][$feed_slug]['pci_transcript_language'])) {
                                $podcastSearchAnd .= '&language=' . $_POST['Powerpress'][$feed_slug]['pci_transcript_language'];
                            }
                            if ($creds) {
                                $accessToken = powerpress_getAccessToken();
                                $req_url = sprintf('/2/media/%s/%s?format=json&transcript=true%s&cache=' . md5(rand(0, 999) . time()), urlencode($program_keyword), urlencode($EnclosureFilename), $podcastSearchAnd);
                                $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '?' . POWERPRESS_BLUBRRY_API_QSA : '');
                                $req_url .= (defined('POWERPRESS_PUBLISH_PROTECTED') ? '&protected=true' : '');
                                $transcriptResults = $auth->api($accessToken, $req_url, false, false, 60 * 30);

                            } else {
                                foreach ($api_url_array as $index => $api_url) {
                                    $req_url = sprintf('/media/%s/%s?format=json&transcript=true%s&cache=' . md5(rand(0, 999) . time()), urlencode($program_keyword), urlencode($EnclosureFilename), $podcastSearchAnd);
                                    $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '&' . POWERPRESS_BLUBRRY_API_QSA : '');
                                    $req_url .= (defined('POWERPRESS_PUBLISH_PROTECTED') ? '&protected=true' : '');
                                    $json_data = powerpress_remote_fopen(rtrim($api_url, '/') . $req_url, $Settings['blubrry_auth'], array(), 60 * 30); // give this up to 30 minutes, though 3 seocnds to 20 seconds is all one should need.
                                    if (!$json_data && $api_url == 'https://api.blubrry.com/') { // Let's force cURL and see if that helps...
                                        $json_data = powerpress_remote_fopen(rtrim($api_url, '/') . $req_url, $Settings['blubrry_auth'], array(), 60 * 30, false, true);
                                    }
                                    if ($json_data != false)
                                        break;
                                }
                                $transcriptResults = powerpress_json_decode($json_data);
                            }

                            // Take the temp transcription file and add to the feed
                            if (!empty($transcriptResults['temp_transcription_file'])) {
                                $EpisodeData["pci_transcript_url"] = $transcriptResults['temp_transcription_file'];
                                $EpisodeData["pci_transcript"] = 1;
                                $EnclosureData = $EnclosureURL . "\n" . $EnclosureSize . "\n" . $EnclosureType . "\n" . serialize($EpisodeData);
                                update_post_meta($post_ID, $field, $EnclosureData);
                            }

                            // Check for transcription error
                            if (!empty($transcriptResults['insufficient_transcription_storage'])) {
                                $error = 'Your episode was published without a transcript because you have reached your transcription limit. 
                                Limits are calculated based on transcripts generated for your total media published/replaced for the month.';

                                // Once free trial is over, we will add:
                                // To increase your transcription limit, upgrade to a hosting plan with more storage. <a href="">Change Plan</a>

                                $error = __($error, 'powerpress');
                                powerpress_add_error($error);
                            }

                            if (isset($transcriptResults['error'])) {
                                powerpress_add_error(__('Error generating transcript: ', 'powerpress') . $transcriptResults['error']);
                            } else if (empty($transcriptResults) || !is_array($transcriptResults) || empty($transcriptResults['temp_transcription_file'])) {
                                powerpress_add_error(__('Error generating transcript', 'powerpress'));
                            }

                            // check for notices from api call
                            if (!empty($transcriptResults['message'])) {
                                powerpress_add_error($transcriptResults['message']);
                            }

                        }
                    }
                }
            }
			if (empty($program_keyword)) {
                $program_keyword = (!empty($EpisodeData['program_keyword']) ? $EpisodeData['program_keyword'] : $Settings['blubrry_program_keyword'] );
            }
            // Push the title to Blubrry
            $post_array = array('title' => $post_title, 'media_url' => $EnclosureURL);
            if (!empty($podcastId)) {
                $post_array = array('title' => $post_title, 'podcast_id' => $podcastId, 'media_url' => $EnclosureURL);
            }
            $post_array['podcast_post_date'] = $post_time;
            $post_array['episode_art'] = $episode_art;
            if ($creds) {
                $accessToken = powerpress_getAccessToken();
                $req_url = sprintf('/2/episode/%s/update-title/?format=json&cache=' . md5(rand(0, 999) . time()), urlencode($program_keyword));
                $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '?' . POWERPRESS_BLUBRRY_API_QSA : '');
                $req_url .= (defined('POWERPRESS_PUBLISH_PROTECTED') ? '&protected=true' : '');
                $title_results = $auth->api($accessToken, $req_url, $post_array, false, 60 * 30);

            } else {
                foreach ($api_url_array as $index => $api_url) {
                    $req_url = sprintf('/episode/%s/update-title/?format=json&cache=' . md5(rand(0, 999) . time()), urlencode($program_keyword));
                    $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '&' . POWERPRESS_BLUBRRY_API_QSA : '');
                    $req_url .= (defined('POWERPRESS_PUBLISH_PROTECTED') ? '&protected=true' : '');
                    $json_data = powerpress_remote_fopen(rtrim($api_url, '/') . $req_url, $Settings['blubrry_auth'], $post_array, 60 * 30); // give this up to 30 minutes, though 3 seocnds to 20 seconds is all one should need.
                    if (!$json_data && $api_url == 'https://api.blubrry.com/') { // Let's force cURL and see if that helps...
                        $json_data = powerpress_remote_fopen(rtrim($api_url, '/') . $req_url, $Settings['blubrry_auth'], $post_array, 60 * 30, false, true);
                    }
                    if ($json_data != false)
                        break;
                }
                $title_results = powerpress_json_decode($json_data);
            }
		}
	}
}

function powerpress_json_decode($value)
{
	return json_decode($value, true);
}

// Import podpress settings
function powerpress_admin_import_podpress_settings()
{
	// First pull in the Podpress settings
	$PodpressData = get_option('podPress_config');
	if( !$PodpressData )
		return false;
	
	$General = get_option('powerpress_general');
	if( !$General)
		$General = array();
	$General['process_podpress'] = 1;
	$General['display_player'] = 1;
	$General['player_function'] = 1;
	$General['podcast_link'] = 1;
	// Lets try to copy settings from podpress
	$General['default_url'] = $PodpressData['mediaWebPath'];
	if( substr($General['default_url'], 0, -1) != '/' )
		$General['default_url'] .= '/'; // Add the trailing slash, donno it's not there...
	
	// Insert the blubrry redirect
	if( isset($PodpressData['statBluBrryProgramKeyword']) && strlen($PodpressData['statBluBrryProgramKeyword']) > 2 )
	{
		$General['redirect1'] = 'https://media.blubrry.com/'.$PodpressData['statBluBrryProgramKeyword'].'/';
	}
	
	// Insert the Podtrac redirect
	if( $PodpressData['enable3rdPartyStats'] == 'PodTrac' )
	{
		if( $General['redirect1'] )
			$General['redirect2'] = 'http://www.podtrac.com/pts/redirect.mp3/';
		else
			$General['redirect1'] = 'http://www.podtrac.com/pts/redirect.mp3/';
	}
	
	if( $PodpressData['contentDownload'] == 'enabled' )
		$General['podcast_link'] = 1;
	else
		$General['podcast_link'] = 0;
	
	if( $PodpressData['contentPlayer'] == 'both' )
		$General['player_function'] = 1;
	else if( $PodpressData['contentPlayer'] == 'inline' )
		$General['player_function'] = 2;
	else if( $PodpressData['contentPlayer'] == 'popup' )
		$General['player_function'] = 3;
	else
		$General['player_function'] = 0;
		
	if( $PodpressData['contentPlayer'] == 'start' )
		$General['display_player'] = 2;
	else
		$General['display_player'] = 1;
	
	// save these imported general settings
	powerpress_save_settings($General, 'powerpress_general');

	$FeedSettings = get_option('powerpress_feed');
	
	if( !$FeedSettings ) // If no feed settings, lets set defaults or copy from podpress.
		$FeedSettings = array();
		
	$FeedSettings['apply_to'] = 1; // Default, apply to all the rss2 feeds
	
	$FeedSettings['itunes_image'] = $PodpressData['iTunes']['image'];
	if( strstr($FeedSettings['itunes_image'], 'powered_by_podpress') )
		$FeedSettings['itunes_image'] = ''; // We're not using podpress anymore
	
	$FeedSettings['itunes_summary'] = $PodpressData['iTunes']['summary'];
	$FeedSettings['itunes_talent_name'] = $PodpressData['iTunes']['author'];
	$FeedSettings['itunes_subtitle'] = $PodpressData['iTunes']['subtitle'];
	$FeedSettings['copyright'] = $PodpressData['rss_copyright'];

    // Categories are tricky...
	$iTunesCategories = powerpress_itunes_categories(true);
	for( $x = 0; $x < 3; $x++ )
	{	
		if( isset($PodpressData['iTunes']['category'][$x]) )
		{
			$CatDesc = str_replace(':', ' > ', $PodpressData['iTunes']['category'][$x]);
			$CatKey = array_search($CatDesc, $iTunesCategories);
			if( $CatKey )
				$FeedSettings['itunes_cat_'.($x+1)] = $CatKey;
		}
	}
	
	if( $PodpressData['iTunes']['explicit'] == 'No' )
		$FeedSettings['itunes_explicit'] = 0;
	else if( $PodpressData['iTunes']['explicit'] == 'Yes' )
		$FeedSettings['itunes_explicit'] = 1;
	else if( $PodpressData['iTunes']['explicit'] == 'Clean' )
		$FeedSettings['itunes_explicit'] = 2;
		
	if( !empty($PodpressData['iTunes']['FeedID']) )
		$FeedSettings['itunes_url'] = 'http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id='. $PodpressData['iTunes']['FeedID'];

	// Lastly, lets try to get the RSS image from the database
	$RSSImage = get_option('rss_image');
	if( $RSSImage )
		$FeedSettings['itunes_image'] = $RSSImage;
	if( strstr($FeedSettings['itunes_image'], 'powered_by_podpress') )
		$FeedSettings['itunes_image'] = ''; // We're not using podpress anymore
	$AdminEmail = get_option('admin_email');
	if( $AdminEmail )
		$FeedSettings['email'] = $AdminEmail;
		
	// save these imported feed settings
	powerpress_save_settings($FeedSettings, 'powerpress_feed');
	return true;
}

// Import plugin Podcasting settings
function powerpress_admin_import_podcasting_settings()
{
	$Changes = false;
	
	$General = get_option('powerpress_general');
	if( !$General)
	{
		$General = array();
		$Changes = true;
		$General['process_podpress'] = 0;
		$General['display_player'] = 1;
		$General['player_function'] = 1;
		$General['podcast_link'] = 1;
	}

	$pod_player_location = get_option('pod_player_location');
	if( $pod_player_location == 'top' ) // display player below posts is default in PowerPress
	{
		$General['display_player'] = 2; // display above posts
		$Changes = true;
	}
	
	$pod_audio_width = get_option('pod_audio_width');
	if( is_int( (int)$pod_audio_width) && $pod_audio_width > 100 ) // audio player width
	{
		$General['player_width_audio'] = $pod_audio_width;
		$Changes = true;
	}
	
	$pod_player_width = get_option('pod_player_width');
	if( is_int( (int)$pod_player_width) && $pod_player_width > 100 ) // video player width
	{
		$General['player_width'] = $pod_player_width;
		$Changes = true;
	}
	
	$pod_player_height = get_option('pod_player_height');
	if( is_int( (int)$pod_player_height) && $pod_player_height > 100 ) // video player width
	{
		$General['player_height'] = $pod_player_height;
		$Changes = true;
	}
	
	if( $Changes == true )
	{
		// save these imported general settings
		powerpress_save_settings($General, 'powerpress_general');
	}
	
	$FeedChanges = false;
	// Feed settings:
	$FeedSettings = get_option('powerpress_feed');
	
	if( !$FeedSettings ) // If no feed settings, lets set defaults or copy from podpress.
	{
		$FeedSettings = array();
		$FeedChanges = true;
	}
	
	$pod_itunes_summary = get_option('pod_itunes_summary');
	if( $pod_itunes_summary )
	{
		$FeedSettings['itunes_summary'] = stripslashes($pod_itunes_summary);
		$FeedChanges = true;
	}
	
	$pod_itunes_image = get_option('pod_itunes_image');
	if( $pod_itunes_image ) 
	{
		$FeedSettings['itunes_image'] = $pod_itunes_image;
		$FeedChanges = true;
	}
	
	$iTunesCategories = powerpress_itunes_categories(true);
	for( $x = 1; $x <= 3; $x++ )
	{
		$pod_itunes_cat = get_option('pod_itunes_cat'.$x);
		$find = str_replace('&amp;', '&', $pod_itunes_cat);
		$CatDesc = str_replace('||', ' > ', $find);
		$CatKey = array_search($CatDesc, $iTunesCategories);
		if( $CatKey )
		{
			$FeedSettings['itunes_cat_'.$x] = $CatKey;
			$FeedChanges = true;
		}
	}
	
	$pod_itunes_ownername = get_option('pod_itunes_ownername');
	if( $pod_itunes_ownername ) 
	{
		$FeedSettings['itunes_talent_name'] = stripslashes($pod_itunes_ownername);
		$FeedChanges = true;
	}
	
	$pod_itunes_owneremail = get_option('pod_itunes_owneremail');
	if( $pod_itunes_owneremail ) 
	{
		$FeedSettings['email'] = $pod_itunes_owneremail;
		$FeedChanges = true;
	}
	
	$rss_language = get_option('rss_language');
	if( $rss_language ) 
	{
		$FeedSettings['rss_language'] = $rss_language;
		$FeedChanges = true;
	}
	
	$pod_tagline = get_option('pod_tagline');
	if( $pod_tagline ) 
	{
		$FeedSettings['itunes_subtitle'] = stripslashes($pod_tagline);
		$FeedChanges = true;
	}
	
	$pod_itunes_explicit = get_option('pod_itunes_explicit');
	if( $pod_itunes_explicit == 'yes'  ) 
	{
		$FeedSettings['itunes_explicit'] = 1;
		$FeedChanges = true;
	}
	else if( $pod_itunes_explicit == 'clean'  ) 
	{
		$FeedSettings['itunes_explicit'] = 2;
		$FeedChanges = true;
	}
	
	if( $FeedChanges )
	{
		// save these imported feed settings
		powerpress_save_settings($FeedSettings, 'powerpress_feed');
	}
	
	return ($Changes||$FeedChanges);
}

function powerpress_admin_episodes_not_hosted() {
    // no need to specify feed slug--this is for use during onboarding, before additional feeds are set up
    $field = 'enclosure';
    global $wpdb;
    if ($results = $wpdb->get_results("SELECT COUNT(pm.post_id) AS episodes_total FROM $wpdb->posts AS p INNER JOIN $wpdb->postmeta AS pm ON pm.post_id = p.ID WHERE pm.meta_key = '$field' AND p.post_status <> 'auto-draft' AND p.post_status <> 'trash' AND p.post_status <> 'inherit' AND pm.meta_value NOT LIKE '%content.blubrry.com%' ", ARRAY_A)) {
        if (count($results)) {
            foreach ($results as $key => $row) {
                if ($row['episodes_total'])
                    return $row['episodes_total'];
            }
        }
    }
    return 0;
}

function powerpress_admin_episodes_per_feed($feed_slug, $post_type='post')
{
	$field = 'enclosure';
	if( $feed_slug != 'podcast' )
		$field = '_'. $feed_slug .':enclosure';
	global $wpdb;
	if ( $results = $wpdb->get_results("SELECT COUNT(pm.post_id) AS episodes_total FROM $wpdb->posts AS p INNER JOIN $wpdb->postmeta AS pm ON pm.post_id = p.ID WHERE pm.meta_key = '$field' AND p.post_status <> 'auto-draft' AND p.post_status <> 'trash' AND p.post_status <> 'inherit' ", ARRAY_A) ) {
        if( count($results) )
		{
			foreach( $results as $key => $row ) {
				break;
			}
			if( $row['episodes_total'] )
				return $row['episodes_total'];
		}
	}
	return 0;
}

// Set the default settings basedon the section user is in.
function powerpress_default_settings($Settings, $Section='basic')
{
	// Set the default settings if the setting does not exist...
	switch($Section)
	{
		case 'basic': {
			// Nothing needs to be pre-set in the basic settings area
			
			if( !isset($Settings['player_options'] ) )
			{
				$Settings['player_options'] = 0;
				if( isset($Settings['player']) && $Settings['player'] != '' && $Settings['player'] != 'default' )
					$Settings['player_options'] = 1;
			}
			
			if( !isset($Settings['cat_casting'] ) )
			{
				$Settings['cat_casting'] = 0;
				//if( isset($Settings['custom_cat_feeds']) && count($Settings['custom_cat_feeds']) > 0 )
				//	$Settings['cat_casting'] = 1;
			}
			
			if( !isset($Settings['channels'] ) )
				$Settings['channels'] = 0;
			if( isset($Settings['custom_feeds']) && count($Settings['custom_feeds']) > 0 ) // They can't delete this until they remove all the channels
				$Settings['channels'] = 1;
					
		}; break;
		case 'editfeed': {
			if( !isset($Settings['apply_to']) )
				$Settings['apply_to'] = 1; // Make sure settings are applied to all feeds by default
			//if( !isset($Settings['enhance_itunes_summary']) )
			//	$Settings['enhance_itunes_summary'] = 1;
		}; // Let this fall through to the custom feed settings
		case 'editfeed_custom': {
			if( !isset($Settings['enhance_itunes_summary']) )
				$Settings['enhance_itunes_summary'] = 0;
		}; break;
		case 'appearance': {
			if( !isset($Settings['display_player']) )
				$Settings['display_player'] = 1;
			if( !isset($Settings['player_function']) )
				$Settings['player_function'] = 1;
			if( !isset($Settings['podcast_link']) )
				$Settings['podcast_link'] = 1;
			if( !isset($Settings['display_player_excerpt']) )
					$Settings['display_player_excerpt'] = 0;
			//if( !isset($Settings['display_player_disable_mobile']) )
			//		$Settings['display_player_disable_mobile'] = 0;
			
			// Play in page obsolete, switching here:
			if( $Settings['player_function'] == 5 )
				$Settings['player_function'] = 1;
			else if( $Settings['player_function'] == 4 )
				$Settings['player_function'] = 2;
			if(!isset($Settings['subscribe_widget_shape'])) {
                $Settings['subscribe_widget_shape'] = 'squared';
            }
			if(!isset($Settings['subscribe_no_important_styling'])) {
			    $Settings['subscribe_no_important_styling'] = 'include';
            }
		}; break;
	}
	
	return $Settings;
}

function powerpress_write_tags($file, $post_title, $program_keyword = false)
{
	// Use the Blubrry API to write ID3 tags. to the media...
	
	$Settings = get_option('powerpress_general');
    $creds = get_option('powerpress_creds');
    require_once(POWERPRESS_ABSPATH .'/powerpressadmin-auth.class.php');
    $auth = new PowerPressAuth();
	if( empty($program_keyword) && !empty($Settings['blubrry_program_keyword']) ) {
		$program_keyword = $Settings['blubrry_program_keyword'];
	}

	$PostArgs = array();
	$Fields = array('title','artist','album','genre','year','track','composer','copyright','url');
	foreach( $Fields as $null => $field )
	{
		if( !empty($Settings[ 'tag_'.$field ]) )
		{
			if( $field == 'track' )
			{
				$TrackNumber = get_option('powerpress_track_number');
				if( empty($TrackNumber) )
					$TrackNumber = 1;
				$PostArgs[ $field ] = $TrackNumber;
				update_option('powerpress_track_number', ($TrackNumber+1) );
			}
			else
			{
				$PostArgs[ $field ] = $Settings[ 'tag_'.$field ];
			}
		}
		else
		{
			switch($field)
			{
				case 'title': {
					$PostArgs['title'] = $post_title;
				}; break;
				case 'album': {
					$PostArgs['album'] = get_bloginfo('name');
				}; break;
				case 'genre': {
					$PostArgs['genre'] = 'Podcast';
				}; break;
				case 'year': {
					$PostArgs['year'] = date('Y');
				}; break;
				case 'artist':
				case 'composer': {
					if( !empty($Settings['itunes_talent_name']) )
						$PostArgs[ $field ] = $Settings['itunes_talent_name'];
				}; break;
				case 'copyright': {
					if( !empty($Settings['itunes_talent_name']) )
						$PostArgs['copyright'] = '(c) '.$Settings['itunes_talent_name'];
				}; break;
				case 'url': {
					$PostArgs['url'] = get_bloginfo('url');
				}; break;
			}
		}
	}
							
	// Get meta info via API
    $Results = false;
	$content = false;
	$api_url_array = powerpress_get_api_array();
    if ($creds) {
        $accessToken = powerpress_getAccessToken();
        $req_url = sprintf('/2/media/%s/%s?format=json&id3=true&cache=' . md5( rand(0, 999) . time() ) , urlencode($program_keyword), urlencode($file));
        $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA')?'?'. POWERPRESS_BLUBRRY_API_QSA:'');
        $Results = $auth->api($accessToken, $req_url, $PostArgs);
        //$Results['error'] = print_r($Results, true);
    } else {
        foreach ($api_url_array as $index => $api_url) {
            $req_url = sprintf('%s/media/%s/%s?format=json&id3=true&cache=' . md5( rand(0, 999) . time() ), rtrim($api_url, '/'), urlencode($program_keyword), urlencode($file));
            $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '&' . POWERPRESS_BLUBRRY_API_QSA : '');
            $content = powerpress_remote_fopen($req_url, $Settings['blubrry_auth'], $PostArgs);
            if (!$content && $api_url == 'https://api.blubrry.com/') { // Lets force cURL and see if that helps...
                $content = powerpress_remote_fopen($req_url, $Settings['blubrry_auth'], $PostArgs, 15, false, true);
            }
            if ($content != false)
                break;
        }

        if ($content) {
            $Results = powerpress_json_decode($content);
        }
    }
    if ($Results && is_array($Results))
        return $Results;

	
	return array('error'=>__('Error occurred writing MP3 ID3 Tags.', 'powerpress') );
}

function powerpress_get_media_info($file, $program_Keyword = false)
{
    require_once(POWERPRESS_ABSPATH .'/powerpressadmin-auth.class.php');
    $auth = new PowerPressAuth();
	$Settings = get_option('powerpress_general');
    $creds = get_option('powerpress_creds');
	if( empty($program_Keyword) && !empty($Settings['blubrry_program_keyword']) ) {
		$program_Keyword = $Settings['blubrry_program_keyword'];
	}
    // for media creek urls, just verify the content.blubrry.com version
	if (strpos($file, 'mc.blubrry.com') !== false) {
        //remove query string
        $file_array = explode('?', $file);
        $file = $file_array[0];
        // replace media host
        $file = str_replace('mc.blubrry.com', 'content.blubrry.com', $file);
    }
	$content = false;
    $Results = array();
    $full_url = true;
    $api_url_array = powerpress_get_api_array();
    if (substr($file, 0, 4) != 'http') {
        // filename only, remove query string
        $file_array = explode('?', $file);
        $file = $file_array[0];
        $full_url = false;
    }
    if ($creds) {
        $accessToken = powerpress_getAccessToken();
        $req_url = sprintf('/2/media/%s/%s?format=json&info=true', urlencode($program_Keyword), urlencode($file));
        $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA')?'?'. POWERPRESS_BLUBRRY_API_QSA:'');
        $Results = $auth->api($accessToken, $req_url, false);
    } else {
        foreach ($api_url_array as $index => $api_url) {
            $req_url = sprintf('%s/media/%s/%s?format=json&info=true', rtrim($api_url, '/'), urlencode($program_Keyword), urlencode($file));
            $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '&' . POWERPRESS_BLUBRRY_API_QSA : '');
            $content = powerpress_remote_fopen($req_url, $Settings['blubrry_auth']);
            if (!$content && $api_url == 'https://api.blubrry.com/') { // Lets force cURL and see if that helps...
                $content = powerpress_remote_fopen($req_url, $Settings['blubrry_auth'], array(), 15, false, true);
            }

            if ($content != false)
                break;
        }

        if ($content) {
            $Results = powerpress_json_decode($content);
        }
    }

    if ($Results && is_array($Results) && empty($Results['error']))
        return $Results;

	return array('error'=>__('Error occurred obtaining media information. ' . print_r($Results, true), 'powerpress') );
}

// Call this function when there is no enclosure currently detected for the post but users set the option to auto-add first media file linked within post option is checked.
function powerpress_do_enclose( $content, $post_ID, $use_last_media_link = false )
{
	$ltrs = '\w';
	$gunk = '/#~:.?+=&%@!\-';
	$punc = '.:?\-';
	$any = $ltrs . $gunk . $punc;

	preg_match_all( "{\b http : [$any] +? (?= [$punc] * [^$any] | $)}x", $content, $post_links_temp );
	
	if( $use_last_media_link )
		$post_links_temp[0] = array_reverse($post_links_temp[0]);
	
	$enclosure = false;
	foreach ( (array) $post_links_temp[0] as $link_test ) {
		$test = parse_url( $link_test );
		// Wordpress also acecpts query strings, which doesn't matter to us what's more important is taht the request ends with a file extension.
		// get the file extension at the end of the request:
		if( preg_match('/\.([a-z0-9]{2,7})$/i', $link_test, $matches) )
		{
			// see if the file extension is one of the supported media types...
			$content_type = powerpress_get_contenttype('test.'.$matches[1], false); // we want to strictly use the content types known for media, so pass false for second argument
			if( $content_type )
			{
				$enclosure = $link_test;
				$MediaInfo = powerpress_get_media_info_local($link_test, $content_type);
				if( !isset($MediaInfo['error']) && !empty($MediaInfo['length']) )
				{
					// Insert enclosure here:
					$EnclosureData = $link_test . "\n" . $MediaInfo['length'] . "\n". $content_type;
					if( !empty($MediaInfo['duration']) )
						$EnclosureData .= "\n".serialize( array('duration'=>$MediaInfo['duration']) );
					add_post_meta($post_ID, 'enclosure', $EnclosureData, true);
					break; // We don't wnat to insert anymore enclosures, this was it!
				}
			}
		}
	}
}

function powerpress_get_episode_count($feed_slug, $post_type = 'post')
{
	global $wpdb;
	$custom_field = 'enclosure';
	if( $feed_slug != 'podcast' )
		$custom_field = '_'. $feed_slug .':enclosure';
		
	$query = "SELECT COUNT( * ) AS num_posts FROM {$wpdb->posts} ";
	$query .= "INNER JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
	$query .= "WHERE {$wpdb->postmeta}.meta_key = '". $custom_field ."' AND post_type = %s AND post_status = 'publish' ";
	
	$results = $wpdb->get_results( $wpdb->prepare( $query, $post_type ), ARRAY_A );
	if( !empty($results[0]['num_posts']) )
	{
		return $results[0]['num_posts'];
	}
	return 0;
}

function powerpress_get_media_info_local($media_file, $content_type='', $file_size=0, $duration='', $return_warnings=false)
{
	$error_msg = '';
	$warning_msg = '';
	if( $content_type == '' )
		$content_type = powerpress_get_contenttype($media_file);
		
	if( isset($GLOBALS['objWPOSFLV']) && is_object($GLOBALS['objWPOSFLV']) )
		return array('error'=>__('The WP OS FLV plugin is not compatible with Blubrry PowerPress.', 'powerpress') );

	$get_duration_info = ( ($content_type == 'audio/mpeg' || $content_type == 'audio/x-m4a' || $content_type == 'video/x-m4v' || $content_type == 'video/mp4' || $content_type == 'audio/ogg' ) && $duration === '');
	// Lets use the mp3info class:
	require_once( POWERPRESS_ABSPATH .'/mp3info.class.php');
	$Mp3Info = new Mp3Info();

	if( $get_duration_info )
	{
		if( preg_match('/video/i', $content_type) )
		{
			if( defined('POWERPRESS_DOWNLOAD_BYTE_LIMIT_VIDEO') )
				$Mp3Info->SetDownloadBytesLimit(POWERPRESS_DOWNLOAD_BYTE_LIMIT_VIDEO);
		}
		else
		{
			if( defined('POWERPRESS_DOWNLOAD_BYTE_LIMIT') )
				$Mp3Info->SetDownloadBytesLimit(POWERPRESS_DOWNLOAD_BYTE_LIMIT);
		}
	}

	$Mp3Data = $Mp3Info->GetMp3Info($media_file, !$get_duration_info);
	if( $Mp3Data )
	{
		if( $Mp3Info->GetRedirectCount() > 5 )
		{
			// Add a warning that the redirect count exceeded 5, which may prevent some podcatchers from downloading the media.
			$warning = sprintf( __('Warning, the Media URL %s contains %d redirects.', 'powerpress'), $media_file, $Mp3Info->GetRedirectCount() );
			$warning .=	' [<a href="https://blubrry.com/support/powerpress-documentation/warning-messages-explained/" title="'. __('PowerPress Warnings Explained', 'powerpress') .'" target="_blank">'. __('PowerPress Warnings Explained') .'</a>]';
			if( $return_warnings )
				$warning_msg .= $warning;
			else
				powerpress_add_error( $warning );
		}

		if( $file_size == 0 )
			$file_size = $Mp3Info->GetContentLength();

		if( $get_duration_info )
		{
			$playtime_string = ( !empty($Mp3Data['playtime_string']) ? $Mp3Data['playtime_string'] : '');
			$duration = powerpress_readable_duration($playtime_string, true); // Fix so it looks better when viewed for editing
		}

		$GeneralSettings = get_option('powerpress_general');
		if( empty($GeneralSettings['hide_warnings']) && count( $Mp3Info->GetWarnings() ) > 0 )
		{
			$Warnings = $Mp3Info->GetWarnings();
			foreach( $Warnings as $null => $warning )
			{
				$warning = sprintf( __('Warning, Media URL %s', 'powerpress'), $media_file) .': '. $warning  .' [<a href="https://blubrry.com/support/powerpress-documentation/warning-messages-explained/" target="_blank">'. __('PowerPress Warnings Explained', 'powerpress') .'</a>]';
				if( $return_warnings )
					$warning_msg .= $warning;
				else
					powerpress_add_error( $warning );
			}
		}
	}
	else
	{
		if( $Mp3Info->GetError() != '' )
			return array('error'=>$Mp3Info->GetError() );
		else
			return array('error'=>__('Error occurred obtaining media information.', 'powerpress') );
	}

	if( $file_size == 0 )
		return array('error'=>__('Error occurred obtaining media file size.', 'powerpress') );

	if( $return_warnings && $warning_msg != '' )
		return array('content-type'=>$content_type, 'length'=>$file_size, 'duration'=>$duration, 'warnings'=>$warning_msg, 'enclosure_url' => $media_file);
	return array('content-type'=>$content_type, 'length'=>$file_size, 'duration'=>$duration, 'enclosure_url' => $media_file);
}

function powerpress_add_error($error)
{
	$Errors = get_option('powerpress_errors');
	if( !is_array($Errors) )
		$Errors = array();
	$Errors[] = $error;
	update_option('powerpress_errors',  $Errors);
}
	
function powerpress_print_options($options,$selected=null, $return=false)
{
	reset($options);
	if( $return )
	{
		$html = '';
		foreach( $options as $key=> $value )
		{
			$html .= '<option value="'. esc_attr($key) .'"'. ( ($selected !== null && strcmp($selected, $key) == 0 )?' selected':'') .'>';
			$html .= htmlspecialchars($value);
			$html .= "</option>\n";
		}
		
		return $html;
	}
	foreach( $options as $key=> $value )
	{
		echo '<option value="'. esc_attr($key) .'"'. ( ($selected !== null && strcmp($selected, $key) == 0 )?' selected':'') .'>';
		echo htmlspecialchars($value);
		echo "</option>\n";
	}
}

/*
Help Link
2.0 beta
*/
function powerpress_help_link($link, $title = false )
{
	if( $title == '' )
		$title = __('Learn More', 'powerpress');
	
	return ' [<a href="'. $link .'" title="'. htmlspecialchars($title) .'" target="_blank">'. htmlspecialchars($title) .'</a>] ';
}

$g_SupportUploads = null;
function powerpressadmin_support_uploads()
{
	global $g_SupportUploads;
	if( $g_SupportUploads != null )
		return $g_SupportUploads;
	
	$g_SupportUploads = false;
	$UploadArray = wp_upload_dir();
	if( false === $UploadArray['error'] )
	{
		$upload_path =  $UploadArray['basedir'].'/powerpress/';
		
		if( !file_exists($upload_path) )
			$g_SupportUploads = @wp_mkdir_p( rtrim($upload_path, '/') );
		else
			$g_SupportUploads = true;
	}	
	return $g_SupportUploads;
}

function powerpressadmin_new($span = false, $additional_style='')
{
    $style = 'color: #CC0000; font-weight: bold; background-color: white; padding: 2px; border-radius: 4px;';
	if( !empty($additional_style) )
		$style .= $additional_style;
	if ($span) {
        return '<span style="'.$style.'">'. __('New!', 'powerpress') .'</span>';
    }
	return '<sup style="'.$style.'">'. __('New!', 'powerpress') .'</sup>';
}

function powerpressadmin_updated($updated_message)
{
	return '<div style="margin: 5px;"><sup style="color: #CC0000; font-weight: bold; font-size: 85%;">'. $updated_message .'</sup></div>';
}

function powerpressadmin_notice($updated_message)
{
	return '<sup style="color: #CC0000; font-weight: bold; font-size: 105%;">'. htmlspecialchars($updated_message) .'</sup>';
}

function powerpressadmin_community_news($items=4, $pp_settings=false)
{
	require_once( POWERPRESS_ABSPATH. '/powerpress-player.php'); // Include, if not included already
	$rss_items = powerpress_get_news(POWERPRESS_FEED_NEWS, $items);
	echo '<div class="powerpress-news-dashboard">';	
	echo '<ul>';

	if ( !$rss_items )
	{
		echo '<li>'. __('Error occurred retrieving news.' , 'powerpress') .'</li>';
	}
	else
	{
		$first_item = true;
		foreach( $rss_items as $null=> $item)
		{
            preg_match_all('/<img.*?src="(.*?)"/', $item->get_content(), $matches);
			if (!isset($matches[1][0])) {
			    $img_url = powerpress_get_root_url() . 'images/pts_cover.jpg';
            } else {
                $img_url = $matches[1][0];
            }
            $enclosure = $item->get_enclosure();
			echo '<li class="pp-news-item">';
			echo "<img class='pp-news-image' src=\"$img_url\" alt=\"No image\" /><div class='pp-news-details-container'>";
			echo '<a class="rsswidget" href="'.esc_url( $item->get_permalink(), $protocolls=null, 'display' ).'" target="_blank">'. esc_html( $item->get_title() ) .'</a>';
			echo ' <div class="rss-date">'. $item->get_date('F j, Y') .'</div>';
			echo '<div class="rssSummary">'. esc_html( powerpress_feed_text_limit( strip_tags( $item->get_description() ), 150 ) );
            echo '<a href="' . esc_url($item->get_permalink(), $protocolls = null, 'display') . '" target="_blank">' . __('Read more', 'powerpress') . '</a>';
			echo '</div>';
			if( $enclosure && !empty($enclosure->link) )
			{
				$poster_image = '';
				$poster_tag = $item->get_item_tags('http://www.rawvoice.com/rawvoiceRssModule/', 'poster');
				if( $poster_tag && !empty($poster_tag[0]['attribs']['']['url']) )
					$poster_image = $item->sanitize($poster_tag[0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_TEXT);
				
				$embed = '';
				$embed_tag = $item->get_item_tags('http://www.rawvoice.com/rawvoiceRssModule/', 'embed');
				if( $embed_tag && !empty($embed_tag[0]['data']) )
					$embed = $embed_tag[0]['data'];
				
				
				// Only show an episode with the latest item
				if( $first_item && $embed )
				{
					if( preg_match('/width="(\d{1,4})"/i', $embed, $matches ) && count($matches) > 1 )
					{
						$max_width = $matches[1];
						$embed = preg_replace('/width="/i', 'style="max-width: '.$max_width.'px;" width="', $embed );
					}
					$embed = preg_replace('/width="(\d{1,4})"/i', 'width="100%"', $embed );
					
					echo '<div class="powerpressNewsPlayer">';
					echo $embed;
					echo '</div>';
				}
				else if( $first_item )
				{
					$EpisodeData = array();
					$EpisodeData['type'] = $enclosure->type;
					$EpisodeData['duration'] = $enclosure->duration;
					$EpisodeData['poster'] = $poster_image;
					$EpisodeData['width'] = '100%';
					$EpisodeData['custom_play_button'] = powerpress_get_root_url() . 'play_audio.png';
					$ext = powerpressplayer_get_extension($enclosure->link);
					switch($ext)
					{
						case 'mp4':
						case 'm4v':
						case 'webm': {
							echo '<div class="powerpressNewsPlayer powerpressadmin-mejs-video">';
								echo powerpressplayer_build_mediaelementvideo($enclosure->link, $EpisodeData);
							echo '</div>';
						}; break;
						case 'mp3':
						case 'm4a': {
							echo '<div class="powerpressNewsPlayer">';
								echo powerpressplayer_build_mediaelementaudio($enclosure->link, $EpisodeData);
							echo '</div>';
						}; break;
					}
				}
				
					echo '<div style="clear: both;"></div>';
			}
			echo '</div>';
			echo '</li>';
			//$first_item = false;
		}
	}						

	echo '</ul>';
	echo '<br class="clear"/>';
	echo '<div style="text-align:center;"><b>';
	echo  __('MORE ARTICLES AT', 'powerpress');
	echo '&nbsp;';
	echo '<em><a href="https://blubrry.com/podcast-insider/">'. __('PODCAST INSIDER', 'powerpress') .'</a></em>';

	echo '</b></div>';
	echo '</div>';
}

function powerpressadmin_community_highlighted($items=8)
{
	require_once( POWERPRESS_ABSPATH. '/powerpress-player.php'); // Include, if not included already
	$rss_items = powerpress_get_news(POWERPRESS_FEED_HIGHLIGHTED, $items);
	echo '<div class="powerpress-highlighted-dashboard">';	
	echo '<ul>';

	if ( !$rss_items )
	{
		echo '<li>'. __('Error occurred retrieving highlighted items.' , 'powerpress') .'</li>';
	}
	else
	{
		foreach( $rss_items as $null=> $item)
		{
			echo '<li>';
			echo '<a class="rsswidget" href="'.esc_url( $item->get_permalink(), $protocolls=null, 'display' ).'" target="_blank">'. esc_html( $item->get_title() ) .'</a>';
			//echo ' <span class="rss-date">'. $item->get_date('F j, Y') .'</span>';
			echo '<div class="rssSummary">'. esc_html( powerpress_feed_text_limit( strip_tags( $item->get_description() ), 150 ) ).'</div>';
			echo '</li>';
		}
	}						

	echo '</ul>';
	echo '</div>';
}

function powerpress_admin_plugin_action_links( $links, $file )
{
	if( preg_match('/powerpress\.php$/', $file)  )
		array_push( $links, '<a href="'. admin_url("admin.php?page=powerpressadmin_basic")  .'">'. __('Settings', 'powerpress') .'</a>');
	return $links;
}
add_filter( 'plugin_action_links', 'powerpress_admin_plugin_action_links', 10, 2 );

function powerpress_plugin_row_meta( $links, $file ) {
	
	if ( strpos( $file, 'powerpress.php' ) !== false ) {
	
		$new_links = array();
		$new_links[] = powerpress_get_documentation_link();
		//$new_links[] = '<a href="https://blubrry.com/support/powerpress-documentation/powerpress-documentation/" target="_blank">' . __( 'Support', 'powerpress' ) . '</a>';
		$new_links[] = powerpress_get_review_link();
		
		
		$links = array_merge( $links, $new_links );
	}
	
	return $links;
}

function powerpress_admin_get_page()
{
	if( !empty($_REQUEST['page']) )
		return $_REQUEST['page'];
	return 'powerpressadmin_basic';
}

function powerpress_review_message($type=0)
{
	if( $type == 1 )
		return sprintf(__('If you appreciate PowerPress and the features provided, we would greatly appreciate it if you could <a href="%s" target="_blank">leave a review on WordPress.org</a>.', 'powerpress'), 'https://wordpress.org/support/plugin/powerpress/reviews/?rate=5#new-post');
	
	return sprintf(__('Fan of PowerPress? Please show your appreciation by <a href="%s" target="_blank">leaving a review</a>.', 'powerpress'), 'https://wordpress.org/support/plugin/powerpress/reviews/?rate=5#new-post');
}

function powerpress_get_review_link()
{
	return '<a href="https://wordpress.org/support/view/plugin-reviews/powerpress?rate=5#postform" target="_blank">' . __( 'Write a review', 'powerpress' ) . '</a>';
}

function powerpress_get_documentation_link()
{
	return '<a href="https://blubrry.com/support/powerpress-documentation/powerpress-documentation/" target="_blank">' . __( 'Documentation', 'powerpress' ) . '</a>';
}



require_once( POWERPRESS_ABSPATH .'/powerpressadmin-jquery.php');
// Only include the dashboard when appropriate.
require_once( POWERPRESS_ABSPATH .'/powerpressadmin-dashboard.php');

if( defined('WP_LOAD_IMPORTERS') ) {
	require_once( POWERPRESS_ABSPATH .'/powerpressadmin-rss-import.php');
}


