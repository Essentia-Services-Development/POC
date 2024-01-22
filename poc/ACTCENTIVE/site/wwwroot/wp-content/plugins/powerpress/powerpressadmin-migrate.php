<?php


function powerpress_admin_verify_url($url)
{
	$wp_remote_options = array();
	$wp_remote_options['user-agent'] = 'Blubrry PowerPress/'.POWERPRESS_VERSION;
	$wp_remote_options['httpversion'] = '1.1';
	$response = wp_remote_head( $url, $wp_remote_options );
	
	for( $x = 0; $x < 5; $x++ )
	{
		// Redirect 1-5
		if( !is_wp_error( $response ) && ($response['response']['code'] == 301 || $response['response']['code'] == 302) )
		{
			$headers = wp_remote_retrieve_headers( $response );
			$response = wp_remote_head( $headers['location'], $wp_remote_options );
		}
		else
		{
			break;// Either we had an error or the response code is no longer a redirect
		}
	}

	if ( is_wp_error( $response ) )
	{
		return array('error'=>$response->get_error_message() );
	}
	
	if( isset($response['response']['code']) && ($response['response']['code'] < 200 || $response['response']['code'] > 203) )
	{
		return array('error'=>'Error, HTTP '.$response['response']['code'] );
	}
	
	return array('error'=>false);
}

function powerpress_admin_migrate_get_files($clean=false, $exclude_blubrry=true)
{
		global $wpdb;
		
		$return = array();
		//$return['feeds_required'] = 0;
		$query = "SELECT p.ID, p.post_title, p.post_date, pm.meta_id, pm.post_id, pm.meta_key, pm.meta_value ";
		$query .= "FROM {$wpdb->posts} AS p ";
		$query .= "INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id ";
		$query .= "WHERE (pm.meta_key = 'enclosure' OR pm.meta_key LIKE '\_%:enclosure') ";
		$query .= "AND p.post_type != 'revision' ";
		$query .= "GROUP BY pm.meta_id ";
		$query .= "ORDER BY p.post_date DESC ";
		
		$results_data = $wpdb->get_results($query, ARRAY_A);
		if( $results_data )
		{
			foreach( $results_data as $null => $row )
			{
				$meta_id = $row['meta_id'];
				$EpisodeData = powerpress_get_enclosure_data($row['post_id'], 'podcast', $row['meta_value'], false); // Get the enclosure data with no redirect added
				
				if( $exclude_blubrry && strstr($EpisodeData['url'], 'content.blubrry.com') )
					continue; // Skip media hosted on blubrry in this case

                if( $exclude_blubrry && strstr($EpisodeData['url'], 'ins.blubrry.com') )
                    continue; // Skip media hosted on blubrry in this case

                if( $exclude_blubrry && strstr($EpisodeData['url'], 'protected.blubrry.com') )
                    continue; // Skip media hosted on blubrry in this case

				if( !$clean )
					$return[$meta_id] = $row;
				if( !$exclude_blubrry )
					$return[$meta_id]['on_blubrry'] = ( preg_match('/(ins|protected|content)\.blubrry\.com/i',$EpisodeData['url']) == 1 );
				$return[$meta_id]['src_url'] = $EpisodeData['url'];
			}
		}
		return $return;
}


function powepress_admin_migrate_add_urls($urls)
{
    $Settings = get_option('powerpress_general');
    $creds = get_option('powerpress_creds');
    require_once(POWERPRESS_ABSPATH .'/powerpressadmin-auth.class.php');
    $auth = new PowerPressAuth();
	if( empty($Settings['blubrry_auth']) && !$creds )
	{
		powerpress_page_message_add_error( sprintf(__('You must have a blubrry Podcast Hosting account to continue.', 'powerpress')) .' '. '<a href="https://blubrry.com/services/podcast-hosting/" target="_blank">'. __('Learn More', 'powerpress') .'</a>', 'inline', false );
		return false;
	}
	
	$PostArgs = array('urls'=>$urls);
	
	$json_data = false;
	$api_url_array = powerpress_get_api_array();
    if (is_plugin_active('powerpress-hosting/powerpress-hosting.php')) {
        $website_detection_string = "?wp_blubrry_hosted=true";
    } else {
        $website_detection_string = "?wp_admin_url=" . urlencode(admin_url());
    }
    if ($creds) {
        $accessToken = powerpress_getAccessToken();
        $req_url = sprintf('/2/media/%s/migrate_add.json%s', urlencode($Settings['blubrry_program_keyword']), $website_detection_string);
        $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA')?'&'. POWERPRESS_BLUBRRY_API_QSA:'');
        $results = $auth->api($accessToken, $req_url, $PostArgs);
    } else {
        foreach ($api_url_array as $index => $api_url) {
            $req_url = sprintf('%s/media/%s/migrate_add.json%s', rtrim($api_url, '/'), urlencode($Settings['blubrry_program_keyword']), $website_detection_string);
            $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '&' . POWERPRESS_BLUBRRY_API_QSA : '');

            $json_data = powerpress_remote_fopen($req_url, $Settings['blubrry_auth'], $PostArgs);
            if (!$json_data && $api_url == 'https://api.blubrry.com/') { // Lets force cURL and see if that helps...
                $json_data = powerpress_remote_fopen($req_url, $Settings['blubrry_auth'], $PostArgs, 15, false, true);
            }
            if ($json_data != false)
                break;
        }
        $results = powerpress_json_decode($json_data);

        if (empty($results)) {
            $results = array();
            $results['error'] = __('Unknown error occurred decoding results from server.', 'powerpress');
        }
    }

    if( !empty($results['error']) )
    {
        $error = __('Blubrry Migrate Media Error', 'powerpress') .': '. $results['error'];
        powerpress_page_message_add_error($error);
        return false;
    } else if( empty($results) )
	{
		if( !empty($GLOBALS['g_powerpress_remote_errorno']) && $GLOBALS['g_powerpress_remote_errorno'] == 401 )
			$error =  __('Incorrect sign-in email address or password.', 'powerpress') .' '. __('Verify your account settings then try again.', 'powerpress');
		else if( !empty($GLOBALS['g_powerpress_remote_error']) )
			$error = $GLOBALS['g_powerpress_remote_error'];
		else
			$error = __('Authentication failed.', 'powerpress');
		powerpress_page_message_add_error($error);
		return false;
	}

    // unlikely to be necessary for most sites
    // i discovered in testing that this value needs cleared and won't automatically overwrite
    // in the event of someone doing another migration for a different program
    $prev_saved_val = get_option('blubrry_manage_media');
    if ($prev_saved_val) {
        delete_option('blubrry_manage_media');
    }

    if (!empty($results['publisher'])) {
        add_option('blubrry_manage_media', $results['publisher']);
    } else {
        add_option('blubrry_manage_media', 'https://publish.blubrry.com/');
    }

	return $results;
}


function powerpress_admin_migrate_get_status()
{
	$Settings = get_option('powerpress_general');
    $creds = get_option('powerpress_creds');
    require_once(POWERPRESS_ABSPATH .'/powerpressadmin-auth.class.php');
    $auth = new PowerPressAuth();
	if( empty($Settings['blubrry_auth']) && !$creds )
	{
		powerpress_page_message_add_error( sprintf(__('You must have a blubrry Podcast Hosting account to continue.', 'powerpress')), 'inline', false );
		return false;
	}
	
	
	$json_data = false;
	$api_url_array = powerpress_get_api_array();
    if ($creds) {
        $accessToken = powerpress_getAccessToken();
        $req_url = sprintf('/2/media/%s/migrate_status.json?status=summary&simple=true', urlencode($Settings['blubrry_program_keyword']));
        $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA')?'?'. POWERPRESS_BLUBRRY_API_QSA:'');
        $results = $auth->api($accessToken, $req_url);
    } else {
        foreach ($api_url_array as $index => $api_url) {
            $req_url = sprintf('%s/media/%s/migrate_status.json?status=summary&simple=true', rtrim($api_url, '/'), $Settings['blubrry_program_keyword']);
            $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '&' . POWERPRESS_BLUBRRY_API_QSA : '');
            $json_data = powerpress_remote_fopen($req_url, $Settings['blubrry_auth']);
            if ($json_data != false)
                break;
        }

        if (!$json_data) {
            $error = '';
            if (!empty($GLOBALS['g_powerpress_remote_errorno']) && $GLOBALS['g_powerpress_remote_errorno'] == 401)
                $error = __('Incorrect sign-in email address or password.', 'powerpress') . ' ' . __('Verify your account settings then try again.', 'powerpress');
            else if (!empty($GLOBALS['g_powerpress_remote_error']))
                $error = $GLOBALS['g_powerpress_remote_error'];
            else
                $error = __('Authentication failed.', 'powerpress');
            powerpress_page_message_add_error($error);
            return false;
        }
        //mail('cio', 'ok', $json_data);
        $results = powerpress_json_decode($json_data);

        if (empty($results)) {
            $results = array();
            $results['error'] = __('Unknown error occurred decoding results from server.', 'powerpress');
        }
    }
	
	if( !empty($results['error']) )
	{
		$error = __('Blubrry Migrate Media Error', 'powerpress') .': '. $results['error'];
		powerpress_page_message_add_error($error);
		return false;
	}
	
	return $results;
}


function powerpress_admin_migrate_get_migrated_by_status($status='migrated')
{
	$Settings = get_option('powerpress_general');
    $creds = get_option('powerpress_creds');
    require_once(POWERPRESS_ABSPATH .'/powerpressadmin-auth.class.php');
    $auth = new PowerPressAuth();
	if( empty($Settings['blubrry_auth']) && !$creds )
	{
		powerpress_page_message_add_error( sprintf(__('You must have a blubrry Podcast Hosting account to continue.', 'powerpress')), 'inline', false );
		return false;
	}
	
	
	$json_data = false;
	$api_url_array = powerpress_get_api_array();
    if ($creds) {
        $accessToken = powerpress_getAccessToken();
        $req_url = sprintf('/2/media/%s/migrate_status.json?status=%s&limit=10000&simple=true', urlencode($Settings['blubrry_program_keyword']), urlencode($status));
        $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA')?'?'. POWERPRESS_BLUBRRY_API_QSA:'');
        $results = $auth->api($accessToken, $req_url);
    } else {
        foreach ($api_url_array as $index => $api_url) {
            $req_url = sprintf('%s/media/%s/migrate_status.json?status=%s&limit=10000&simple=true', rtrim($api_url, '/'), $Settings['blubrry_program_keyword'], urlencode($status));
            $req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA') ? '&' . POWERPRESS_BLUBRRY_API_QSA : '');
            $json_data = powerpress_remote_fopen($req_url, $Settings['blubrry_auth']);
            if ($json_data != false)
                break;
        }

        if (!$json_data) {
            if (!empty($GLOBALS['g_powerpress_remote_errorno']) && $GLOBALS['g_powerpress_remote_errorno'] == 401)
                $error = __('Incorrect sign-in email address or password.', 'powerpress') . ' ' . __('Verify your account settings then try again.', 'powerpress');
            else if (!empty($GLOBALS['g_powerpress_remote_error']))
                $error = '<p>' . $GLOBALS['g_powerpress_remote_error'];
            else
                $error = __('Authentication failed.', 'powerpress');
            powerpress_page_message_add_error($error);
            return false;
        }

        $results = powerpress_json_decode($json_data);
        if (empty($results)) {
            $error = __('Unknown error occurred decoding results from server.', 'powerpress');
            powerpress_page_message_add_error($error);
            return false;
        }
    }
	if( !empty($results['error']) )
	{
		$error = __('Blubrry Migrate Media Error', 'powerpress') .': '. $results['error'];
		powerpress_page_message_add_error($error);
		return false;
	}
	
	return $results;
}


// Handle POST/GET page requests here
function powerpress_admin_migrate_request()
{
	if( !empty($_GET['migrate_step']) )
	{
		switch( $_GET['migrate_step'] )
		{
			case 1: {
				$GLOBALS['powerpress_migrate_stats'] = powerpress_admin_extension_counts();
			}; break;
			
		}
	}
	
	if( !empty($_POST['migrate_action']) )
	{
		check_admin_referer('powerpress-migrate-media');
		
		switch($_POST['migrate_action'])
		{
			case 'queue_episodes': {
				
				if( !empty($_POST['Migrate']) )
				{
					powerpress_admin_queue_files($_POST['Migrate']);
						
					// Else error message handled in function called above
				}
			}; break;
			case 'update_episodes': { // <input type="hidden" name="migrate_action" value="update_episodes" />
				
				$MigrateResultsPrevious = get_option('powerpress_migrate_results');
				$add_option = false;
				if( $MigrateResultsPrevious == false )
					$add_option = true;
				unset($MigrateResultsPrevious); // Free up the memory
				
				//$URLs = powerpress_admin_migrate_get_migrated_by_status('completed');
				$URLs = powerpress_admin_migrate_get_migrated_by_status('all');
				if( !empty($URLs) )
				{
					$URLs['updated_timestamp'] = current_time( 'timestamp' );
					
					if( $add_option )
						add_option('powerpress_migrate_results', $URLs, '', 'no'); // Make sure it is not auto loaded
					else 
						update_option('powerpress_migrate_results', $URLs);
					
					if( !empty($URLs['results']) )
					{
						$update_option = true;
						$CompletedResults = get_option('powerpress_migrate_completed');
						if( $CompletedResults == false )
							$update_option = false;
						if( empty($CompletedResults['completed_count']) )
							$CompletedResults['completed_count'] = 0;
						if( empty($CompletedResults['error_count']) )
							$CompletedResults['error_count'] = 0;
						if( empty($GLOBALS['g_powerpress_verify_failed_count']) )
							$GLOBALS['g_powerpress_verify_failed_count'] = 0;
						if( empty($GLOBALS['g_powerpress_already_migrated']) )
							$GLOBALS['g_powerpress_already_migrated'] = 0;
						if( empty($GLOBALS['g_powerpress_total_files_found']) )
							$GLOBALS['g_powerpress_total_files_found'] = 0;
						if( empty($GLOBALS['g_powerpress_update_errors']) )
							$GLOBALS['g_powerpress_update_errors'] = 0;
						$QueuedEpisodes = get_option('powerpress_migrate_queued'); // Array of key meta_id => URL value pairs
						
						$FoundCount = 0;
						if( !empty($QueuedEpisodes) )
						{
							foreach( $URLs['results'] as $index => $row )
							{
								if( $row['status'] != 'completed' ) // Not migrated
									continue;
								
								$source_url = $row['source_url'];
								$new_url = $row['new_url'];
								$found = array_keys($QueuedEpisodes, $source_url);
								
								if( empty($found) )
								{
									continue; // Nothing found here
								}
								
								$FoundCount++;
								$GLOBALS['g_powerpress_total_files_found']++;
								
								foreach( $found as $null => $meta_id )
								{
									// Get the post meta
									$meta_object = get_metadata_by_mid('post', $meta_id);
									if( !is_object($meta_object) )
										continue; // Weird
										
									$meta_data = $meta_object->meta_value;
									
									$parts = explode("\n", $meta_data, 2);
									$other_meta_data = false;
									if( count($parts) == 2 )
										list($current_url, $other_meta_data) = $parts;
									else
										$current_url = trim($meta_data);
									
									$current_url = trim($current_url);
									
									// We already migrated this one, or it was modified anyway
									if( $source_url != $current_url )
									{
										//echo "$source_url != $current_url ";
										$GLOBALS['g_powerpress_already_migrated']++;
										continue;
									}
									
									// Verify the URL:
									if( !empty($_POST['PowerPressVerifyURLs']) )
									{
										$verified= powerpress_admin_verify_url($new_url);
										if( !empty($verified['error']) )
										{
											// TODO: Handle the error here...
											$GLOBALS['g_powerpress_verify_failed_count']++;
											continue;
										}
									}
									
									$new_meta_data = $new_url;
									if( $other_meta_data )
										$new_meta_data .= "\n". $other_meta_data;
								
									// save the new URL
									if( update_metadata_by_mid( 'post', $meta_id, $new_meta_data) )
									{
										$CompletedResults['completed_count']++;
										$CompletedResults['results'][ $meta_id ] = $new_url;
									}
									else
									{
										$CompletedResults['error_count']++;
										$GLOBALS['g_powerpress_update_errors']++;
									}
								}
							}
							
							if( $CompletedResults['completed_count'] > 0 )
							{
								if( $update_option )
									update_option('powerpress_migrate_completed', $CompletedResults);
								else
									add_option('powerpress_migrate_completed', $CompletedResults, '', 'no'); // Make sure we are not preloading 
								powerpress_page_message_add_notice( sprintf(__('Episodes updated successfully.', 'powerpress')) );
								return;
							}
							
							powerpress_page_message_add_notice( sprintf(__('No Episodes updated. Please see results.', 'powerpress')) );
							return;
						}
					}
					else
					{
						powerpress_page_message_add_notice(  sprintf(__('No episodes updated.', 'powerpress')) );
					}
				}

                // delete the cron task that check for the migration status
                $timestamp = wp_next_scheduled( 'powerpress_admin_migration_hook' );
                wp_unschedule_event( $timestamp, 'powerpress_admin_migration_hook' );
			}; break;
		}
	}
	
	if( !empty($_GET['migrate_action']) )
	{
		check_admin_referer('powerpress-migrate-media');
		
		switch($_GET['migrate_action'])
		{
			case 'reset_migrate_media': {
				delete_option('powerpress_migrate_completed');
				delete_option('powerpress_migrate_queued');
				delete_option('powerpress_migrate_status');
				delete_option('powerpress_migrate_results');
				powerpress_page_message_add_notice(  sprintf(__('Media migration reset successfully.', 'powerpress')) );
			}; break;
		}
	}
}

function powerpress_admin_extension_counts()
{
	$files = powerpress_admin_migrate_get_files(true, false);
	$extensions = array(); // 'blubrry'=>0, 'mp3'=>0, 'm4a'=>0, 'mp4'=>0, 'm4v'=>0, '*'=>0 );
	foreach( $files as $meta_id => $row )
	{
		$extension = '*';
			
		$parts = pathinfo($row['src_url']);
		if (empty($parts['extension'])) {
		    continue;
        }
		if (strpos($parts['extension'], '?') !== false) {
		    $ext_query_string = explode('?', $parts['extension']);
		    $parts['extension'] = $ext_query_string[0];
        }
		if( preg_match('/(mp3|m4a|mp4|m4v|mov)/i', $parts['extension']) )
			$extension = strtolower($parts['extension']);
			
		if( !empty($row['on_blubrry']) )
			$extension = 'blubrry';
			
		if( empty($extensions[ $extension ]) )
			$extensions[ $extension ] = 0;
		
		$extensions[ $extension ]++;
	}
	return $extensions;
}

function powerpress_admin_queue_files($extensions=array() )
{
	$add_urls = '';
	$extensions_preg_match = '';
	foreach( $extensions as $extension => $null )
	{
		if( $extension == '*' )
		{
			$extensions_preg_match = '.*';
			break; // Lets just match everything
		}
		if( !empty($extensions_preg_match) )
			$extensions_preg_match .= '|';
		$extensions_preg_match .= $extension;
	}
	
	if( empty($extensions_preg_match) )
	{
		// No files specified, no error message needed
		return;
	}
	
	$files = powerpress_admin_migrate_get_files(true, true); // Keep the URLs clean, excude blubrry media URLs
	
	$QueuedFiles = array();
	$Update = false;
	$update_option = true;
	$PastResults = get_option('powerpress_migrate_queued');
	if( $PastResults == false )
		$update_option = false;
	if( is_array($PastResults) )
		$QueuedFiles = $PastResults;
	$AddedCount = 0;;
	$AlreadyAddedCount = 0;
	
	foreach( $files as $meta_id => $row )
	{
		$parts = pathinfo($row['src_url']);
        if (empty($parts['extension'])) {
            continue;
        }
        if (strpos($parts['extension'], '?') !== false) {
            $ext_query_string = explode('?', $parts['extension']);
            $parts['extension'] = $ext_query_string[0];
        }
		if( preg_match('/('.$extensions_preg_match.')/i', $parts['extension']) )
		{
			if( !empty($QueuedFiles[ $meta_id ]) && $QueuedFiles[ $meta_id ] == $row['src_url'] )
			{
				$AlreadyAddedCount++;
				continue; // Already queued
			}
			
			$QueuedFiles[ $meta_id ] = $row['src_url'];
			if( !empty($add_urls ) )
				$add_urls .= "\n";
			$add_urls .= $row['src_url'];
			$Update = true;
			$AddedCount++;
		}
	}
	
	if( $Update )
	{
		// Make API CALL to add files to queue here!
		$UpdateResults = powepress_admin_migrate_add_urls( $add_urls );
	
		if( empty($UpdateResults) )
			$Update = false;
	}
	
	if( $Update )
	{
		// IF the API call was successful, lets save the list locally
		if( $update_option )
			update_option('powerpress_migrate_queued', $QueuedFiles);
		else
			add_option('powerpress_migrate_queued', $QueuedFiles, '', 'no');
		powerpress_page_message_add_notice( sprintf(__('%d media files added to migration queue.', 'powerpress'), $AddedCount) );
	}
	
	if( $AlreadyAddedCount  > 0 )
	{
		powerpress_page_message_add_notice( sprintf(__('%d media files were already added to migration queue.', 'powerpress'), $AlreadyAddedCount) );
	}



    if( !wp_next_scheduled('powerpress_admin_migration_hook')) {
        add_action( 'powerpress_admin_migration_hook', 'powerpress_admin_migration_cron' );
        wp_schedule_event(time(), 'hourly', 'powerpress_admin_migration_hook');
    }
}


function powerpress_admin_migrate_step1()
{
	// Use check_admin_referer('powerpress-migrate-media');  when handling this post request
?>
<form enctype="multipart/form-data" method="post" action="<?php echo admin_url( 'admin.php?page=powerpress/powerpressadmin_migrate.php'); ?>">
<?php wp_nonce_field('powerpress-migrate-media'); ?>
<input type="hidden" name="action" value="powerpress-migrate-media" />
<input type="hidden" name="migrate_action" value="queue_episodes" />
<h2><?php echo __('Migrate Media to your Blubrry Podcast Media Hosting Account', 'powerpress'); ?></h2>

    <div id="powerpress_steps" class="pp-migrate-container">
        <div class="pp-migrate-container-heading">
            <h1><div class="powerpress-step-blue">1</div><?php echo __('Select Media to Migrate', 'powerpress'); ?></h1>
        </div>
        <div class="pp-migrate-content">

<ul>
<?php 
	if( count($GLOBALS['powerpress_migrate_stats']) == 0 )
	{
	?>
	<li>
	<?php echo __('No media found to migrate', 'powerpress'); ?>
	</li>
	<?php
	}
	$types = array('mp3', 'm4a', 'mp4', 'm4v', 'mov', '*', 'blubrry');
	foreach( $types as $null => $extension )
	{
		if( empty($GLOBALS['powerpress_migrate_stats'][$extension]) )
			continue;
		$count = $GLOBALS['powerpress_migrate_stats'][$extension];
		$checked = ' checked';
		switch( $extension )
		{
			case 'mp3': $label = __('mp3 audio files', 'powerpress'); break;
			case 'm4a': $label = __('m4a audio files', 'powerpress'); break;
			case 'mp4': $label = __('mp4 video files', 'powerpress'); break;
			case 'm4v': $label = __('m4v video files', 'powerpress'); break;
			case 'mov': $label = __('mov video files', 'powerpress'); break;
			case 'blubrry': $label = __('media hosted by Blubrry', 'powerpress'); break;
			default: $label = __('Other media formats', 'powerpress'); $checked = '';
		}
		
	?>
	<li>
	<?php if( $extension == 'blubrry' ) { ?>
        <h4><input type="checkbox" name="NULL[<?php echo $extension; ?>]" value="0" disabled /><?php echo $label; ?>
	<?php } else { ?>
        <h4><input type="checkbox" name="Migrate[<?php echo $extension; ?>]" value="1" <?php echo $checked; ?> />  <?php echo $label; ?>
	<?php } ?>
	<?php echo sprintf( __('(%d found)', 'powerpress'), $count); ?></h4>
	</li>
<?php } ?>
</ul>
<?php
	if( count($GLOBALS['powerpress_migrate_stats']) )
?><p class="submit"><button type="submit" class="pp_button" name="Submit"><span><?php echo __('Request Migration', 'powerpress'); ?></span></button></p><?php
?>
        </div></div>
</form>

<p style="margin-bottom: 40px;">&#8592;  <a href="<?php echo admin_url( 'admin.php?page=powerpress/powerpressadmin_migrate.php'); ?>"><?php echo __('Migrate Media', 'powerpress'); ?></a></p>
<?php
}

function powerpress_admin_migrate_find_in_results(&$results, $src_url)
{
	$found = false;
	foreach( $results as $index => $row )
	{
		if( $row['source_url'] == $src_url )
		{
			$found = $row;
			break;
		}
	}
	reset($results);
	
	return $found;
}

function powerpress_admin_migrate_step2($QueuedResults, $MigrateStatus, $CompletedResults)
{
	$update_episodes = false;
	$count = count($QueuedResults);
	$MigrateResults = get_option('powerpress_migrate_results');
	// allow a refresh every three minutes (was previously every thirty, but now we're allowing them to check details while migration is in progress, so we'll need to update more often)
	if( empty($MigrateResults['updated_timestamp']) || $MigrateResults['updated_timestamp']  < ( current_time( 'timestamp') - (60*3) ) )
	{
		$update_option = true;
		if( $MigrateResults == false )
			$update_option = false;
			
		$MigrateResults = powerpress_admin_migrate_get_migrated_by_status('all');
		$MigrateResults['updated_timestamp'] = current_time( 'timestamp');
		if( $update_option )
			update_option('powerpress_migrate_results', $MigrateResults );
		else
			add_option('powerpress_migrate_results', $MigrateResults, '', 'no');
	}
	
	$CompletedResults = get_option('powerpress_migrate_completed');
?>
<h2><?php echo __('Migrate Media to your Blubrry Podcast Media Hosting Account', 'powerpress'); ?></h2>


<div id="powerpress_single_step">
	<h3><?php echo __('List of requested media', 'powerpress'); ?></h3>
</div>
<!-- <p><?php echo sprintf( __('%d files requested', 'powerpress'), $count); ?></p> -->
<?php if( !empty($MigrateStatus['queued']) && false ) { ?><p><?php echo sprintf( __('%d files in queue', 'powerpress'), $MigrateStatus['queued']); ?></p><?php } ?>
<?php if( !empty($MigrateStatus['completed']) && false ) { ?><p><?php echo sprintf( __('%d migrated files available', 'powerpress'), $MigrateStatus['completed']); ?></p><?php } ?>
<?php if( !empty($MigrateStatus['skipped']) && false ) { ?><p><?php echo sprintf( __('%d skipped', 'powerpress'), $MigrateStatus['skipped']); ?></p><?php } ?>
<?php if( !empty($MigrateStatus['failed']) && false ) { ?><p><?php echo sprintf( __('%d failed', 'powerpress'), $MigrateStatus['failed']); ?></p><?php } ?>
<?php if( !empty($CompletedResults['completed_count']) && false ) { ?><p><?php echo sprintf( __('%d episodes updated', 'powerpress'), $CompletedResults['completed_count']); ?></p><?php } ?>
<style type="text/css">
table.powerpress-migration-table {
	min-width: 80%;
}
table.powerpress-migration-table th {
	text-align: left;
}
table.powerpress-migration-table td {
	padding-left: 6px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    max-width: 400px;
}
table.powerpress-migration-table tr:hover {
	background-color: #DDDDDD;
}
.powerpress-migrate-e,
.powerpress-migrate-s {
	width: 18%;
}
</style>
<table class="powerpress-migration-table">
 <tr>
	<th class="powerpress-migrate-f"><?php echo __('File', 'powerpress'); ?></th>
	<th class="powerpress-migrate-s"><?php echo __('Migration Status', 'powerpress'); ?></th>
	<th class="powerpress-migrate-e"><?php echo __('Episode Updated', 'powerpress'); ?></th>
 </tr>
<?php
	foreach( $QueuedResults as $meta_id => $url )
	{
		$status = __('Requested', 'powerpress');
		$updated = '-';
		$file = basename($url);
		
		if( !empty($CompletedResults['results'][$meta_id]) )
		{
			$found = array('status'=>'completed', 'new_url'=>$CompletedResults['results'][$meta_id]);
			$updated = __('Yes', 'powerpress');
		}
		else
		{
			$found = powerpress_admin_migrate_find_in_results($MigrateResults['results'], $url );
		}
		
		if( !empty($found['status']) )
		{
			switch($found['status'])
			{
				case 'completed': { 
					$status = __('Completed', 'powerpress');
					if( empty($CompletedResults['results'][$meta_id]) )
					{
						$updated = __('No', 'powerpress');
						$update_episodes = true;
					}
				}; break;
				case 'skipped': $status = __('Skipped', 'powerpress'); break;
				case 'error': $status = __('Error', 'powerpress'); break;
			}
		}
?>
 <tr>
	<td><?php echo htmlspecialchars($file); ?> &nbsp;</td>
	<td><?php echo $status; ?></td>
	<td><?php echo $updated; ?></td>
 </tr>
<?php
	}
?>
</table>


<?php
}

function powerpress_admin_migrate_step3($MigrateStatus, $CompletedResults)
{
	// $MigrateStatus['completed']
?>
<form enctype="multipart/form-data" method="post" action="<?php echo admin_url( 'admin.php?page=powerpress/powerpressadmin_migrate.php'); ?>">
<?php wp_nonce_field('powerpress-migrate-media'); ?>
<input type="hidden" name="action" value="powerpress-migrate-media" />
<input type="hidden" name="migrate_action" value="update_episodes" />
<h2><?php echo __('Migrate Media to your Blubrry Podcast Media Hosting Account', 'powerpress'); ?></h2>

<div id="powerpress_single_step">
	<h3><?php echo __('Step 3', 'powerpress'); ?> - <?php echo __('Update your Episodes', 'powerpress'); ?></h3>
</div>


<?php if( !empty($MigrateStatus['completed']) ) { ?><p><?php echo sprintf( __('%d migrated files available', 'powerpress'), $MigrateStatus['completed']); ?></p><?php } ?>
<?php if( !empty($CompletedResults['completed_count']) ) { ?><p><?php echo sprintf( __('%d episodes updated', 'powerpress'), $CompletedResults['completed_count']); ?></p><?php } ?>
<p><?php echo __('', 'powerpress'); ?></p>

<p style="margin: 30px 0;"><?php echo __('We recommend backing up your database before proceeding.', 'powerpress'); ?></p>


<p class="submit">
	<input type="submit" name="Submit" id="powerpress_save_button" class="button-primary button-blubrry" value="<?php echo __('Update Episodes', 'powerpress'); ?>" onclick="" />
	&nbsp;
	<input type="checkbox" name="PowerPressVerifyURLs" value="1" checked />
	<strong><?php echo __('Verify URLs', 'powerpress'); ?></strong>
		(<?php echo __('Does not change URL if invalid', 'powerpress'); ?>)</p>
</p>


</form>
<p style="margin-bottom: 40px;">&#8592;  <a href="<?php echo admin_url( 'admin.php?page=powerpress/powerpressadmin_migrate.php'); ?>"><?php echo __('Migrate Media', 'powerpress'); ?></a></p>
<?php
}


function powerpress_admin_migrate()
{
    add_thickbox();
	$General = powerpress_get_settings('powerpress_general');
	$files = powerpress_admin_migrate_get_files();

	// styles for create episode and PP settings buttons
    if (defined('WP_DEBUG')) {
        if (WP_DEBUG) {
            wp_enqueue_style('powerpress_onboarding_styles', plugin_dir_url(__FILE__) . 'css/onboarding.css', array(), POWERPRESS_VERSION);
        } else {
            wp_enqueue_style('powerpress_onboarding_styles', plugin_dir_url(__FILE__) . 'css/onboarding.min.css', array(), POWERPRESS_VERSION);
        }
    } else {
        wp_enqueue_style('powerpress_onboarding_styles', plugin_dir_url(__FILE__) . 'css/onboarding.min.css', array(), POWERPRESS_VERSION);
    }

	if( !empty($_REQUEST['migrate_step']) && $_REQUEST['migrate_step'] == 1 )
	{
		powerpress_admin_migrate_step1();
		return;
	}
	
	$Step = 0;
	$RequestedCount = 0;
	$BlubrryQueuedCount = 0;
	$MigratedCount = 0;
	$FailedCount = 0;
	$SkippedCount = 0;
	$QueuedResults = get_option('powerpress_migrate_queued');
	if( is_array($QueuedResults) )
	{
		$RequestedCount = count($QueuedResults);
		if( $RequestedCount  > 0 )
			$Step = 1;
	}

	$MigrateStatus = false;
	if( $Step >= 1 || !empty($_GET['refresh_migrate_status']) )
	{
		$MigrateStatus = get_option('powerpress_migrate_status');
		if( empty($MigrateStatus) || $MigrateStatus['updated_timestamp'] < current_time('timestamp')-(60*30) || !empty($_GET['refresh_migrate_status']) ) // Check every 30 minutes
		{
			$update_option = true;
			if( $MigrateStatus == false )
				$update_option = false;
			
			$MigrateStatus = powerpress_admin_migrate_get_status();
			if( is_array($MigrateStatus) )
			{
				$MigrateStatus['updated_timestamp'] = current_time( 'timestamp' );
				if( $update_option )
					update_option('powerpress_migrate_status', $MigrateStatus);
				else
					add_option('powerpress_migrate_status', $MigrateStatus, '', 'no' );
			}
		}
	}
	
	if( !empty($MigrateStatus['completed']) )
	{
		$Step = 3;
		$MigratedCount = $MigrateStatus['completed'];
	}
	if( !empty($MigrateStatus['failed']) )
	{
		$FailedCount = $MigrateStatus['failed'];
	}
	if( !empty($MigrateStatus['skipped']) )
	{
		$SkippedCount = $MigrateStatus['skipped'];
	}
	if( !empty($MigrateStatus['queued']) )
	{
		$BlubrryQueuedCount = $MigrateStatus['queued'];
	}

	$CompletedResults = get_option('powerpress_migrate_completed');
	
	if( !empty($_REQUEST['migrate_step']) && $_REQUEST['migrate_step'] == 2 && $Step > 0 )
	{
		powerpress_admin_migrate_step2($QueuedResults, $MigrateStatus, $CompletedResults);
		return;
	}

	if( !empty($_REQUEST['migrate_step']) && $_REQUEST['migrate_step'] == '3' && $Step == 3 )
	{
		powerpress_admin_migrate_step3($MigrateStatus, $CompletedResults);
		return;
	}

	// If we have powerpress credentials, check if the account has been verified
    $creds = get_option('powerpress_creds');
    powerpress_check_credentials($creds);
    wp_enqueue_script('powerpress-admin', powerpress_get_root_url() . 'js/admin.js', array(), POWERPRESS_VERSION );

	if( !empty($CompletedResults['completed_count']) )
	{
		$CompletedCount = $CompletedResults['completed_count'];
	}
	
?>
    <div id="pp-migrate-page">
<?php powerpress_page_message_print(); ?>
<?php
	if( !empty($GLOBALS['g_powerpress_verify_failed_count']) )
	{
		echo '<p>';
		echo sprintf(__('%d urls failed verification.', 'powerpress'), $GLOBALS['g_powerpress_verify_failed_count']);
		echo '</p>';
	}
						
	if( !empty($GLOBALS['g_powerpress_total_files_found']) )
	{
		echo '<p>';
		echo sprintf(__('%d migrated files found on this site.', 'powerpress'), $GLOBALS['g_powerpress_total_files_found']);
		echo '</p>';
	}
	
	if( !empty($GLOBALS['g_powerpress_already_migrated']) )
	{
		echo '<p>';
		echo sprintf(__('%d episodes already updated with new URLs.', 'powerpress'), $GLOBALS['g_powerpress_already_migrated']);
		echo '</p>';
	}
	
	if( !empty($GLOBALS['g_powerpress_update_errors']) )
	{
		echo '<p>';
		echo sprintf(__('%d update errors.', 'powerpress'), $GLOBALS['g_powerpress_update_errors']);
		echo '</p>';
	}
?>

        <h1 style="margin-bottom: 1em;"><b><?php echo __('MIGRATION', 'powerpress'); ?></b></h1>

    <?php

    if (empty($RequestedCount) && empty($BlubrryQueuedCount) && empty($MigratedCount) && empty($CompletedCount)) {

        $GLOBALS['powerpress_migrate_stats'] = powerpress_admin_extension_counts();

    $pp_nonce = powerpress_login_create_nonce();
    ?>

        <div id="pp-migration-marketing" class="pp-migrate-container">
            <div class="pp-migrate-content">
                <h4><b><?php echo __('Transfer media files to your Blubrry hosting account', 'powerpress'); ?></b></h4>
                <div style="margin-top: 1em;">
                    <img src="<?php echo powerpress_get_root_url(); ?>images/onboarding/hosting_icon.png" alt="" class="pp-migration-image" />
                    <h4 class="pp-migrate-subtitle"><?php echo __('This makes it easy to publish episodes right from your own site. Hosting customers enjoy integrated publishing features, such as stats with PowerPress.', 'powerpress'); ?>
                        <?php // If not hosting
                        if( empty($General['blubrry_hosting']) || $General['blubrry_hosting'] === 'false' ) { ?>
                            <br />
                            <a href="<?php echo add_query_arg( '_wpnonce', $pp_nonce, admin_url("admin.php?page=powerpressadmin_onboarding.php&step=blubrrySignup&onboarding_type=hosting")); ?>">
                                <?php echo __('Try Blubrry hosting FREE', 'powerpress'); ?>
                            </a>
                        <?php } ?>
                    </h4>
                </div>
            </div>
        </div>

        <div id="powerpress_steps" class="pp-migrate-container">
            <div class="pp-migrate-container-heading">
                <h1><div class="powerpress-step-blue">1</div><?php echo __('Migrate Media', 'powerpress'); ?></h1>
            </div>
            <div class="pp_flex-grid pp-migrate-content">
                <div class="powerpress-step pp_col" id="powerpreess_step_1a">
                    <?php
                    $file_count = 0;
                    foreach($GLOBALS['powerpress_migrate_stats'] as $ext => $count) {
                        $file_count += $count;
                    }?>
                    <h1>
                        <?php echo $file_count . ' ' . __('episode files found', 'powerpress'); ?>
                    </h1>

                    <div style="margin-top: 1em;">
                        <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;migrate_step=1"); ?>">
                            <button type="button" class="pp_button"><span><?php echo __('START MIGRATION', 'powerpress'); ?></span></button>
                        </a>
                    </div>

                </div>
                <div class="powerpress-step pp_col divider-left" id="powerpreess_step_1b">
                    <h2><b><?php echo __('What is this?', 'powerpress'); ?></b></h2>
                    <p><?php echo __('We locate the files we are able to transfer to your new account.', 'powerpress'); ?></p>
                    <br />
                    <p class="migrate-status-red"><?php echo __('Please complete migration before deleting the source of your files.', 'powerpress'); ?></p>
                </div>
            </div>
        </div>
    <?php }
    if(  !empty($BlubrryQueuedCount) && empty($CompletedCount)  ) { ?>

    <div id="pp-migration-marketing" class="pp-migrate-container">
        <div class="pp-migrate-content">
            <h4><b><?php echo __('Looking for private or premium hosting?', 'powerpress'); ?></b></h4>
            <div style="margin-top: 1em;">
                <img src="<?php echo powerpress_get_root_url(); ?>images/onboarding/pip_logo_rbg.png" alt="" class="pp-migration-image" />
                <h4 class="pp-migrate-subtitle"><?php echo __('Our private internal podcasting option makes it simple to offer secure and restricted content. Available for desktop and app streaming only.', 'powerpress'); ?>
                        <br />
                        <a href="https://blubrry.com/services/private-internal-podcasting/">
                            <?php echo __('Learn more here', 'powerpress'); ?>
                        </a>
                </h4>
            </div>
        </div>
    </div>

    <div id="powerpress_steps" class="pp-migrate-container">
        <div class="pp-migrate-container-heading">
            <h1><div class="powerpress-step-blue">2</div><?php echo __('Migration Status', 'powerpress'); ?></h1>
        </div>
        <div class="pp_flex-grid pp-migrate-content">
            <div class="powerpress-step pp_col" style="text-align: center;">
                <img alt="Step 2" src="<?php echo powerpress_get_root_url() . 'images/onboarding/migration_bird.png' ?>"/>
                <img class="loading" alt="Migrating media..." src="<?php echo powerpress_get_root_url() . 'images/onboarding/loading_bar.gif' ?>"/>
                <h4 class="migrate-status-yellow">
                    <?php echo __('Migrating...', 'powerpress'); ?>
                    </h4>
                <br />
                <h2 class="migrate-status-blue"><?php echo sprintf(__('%d files migrated', 'powerpress'), $MigratedCount); ?></h2>
                <div class="pp_flex-grid" style="justify-content: space-around">
                <?php if ($BlubrryQueuedCount) { ?><h4>
                         <?php echo sprintf(__('%d files in queue', 'powerpress'), $BlubrryQueuedCount); ?></h4> <?php } ?>
                <?php if ($FailedCount) { ?><h4 class="migrate-status-red">
                    <?php echo sprintf(__('%d files failed', 'powerpress'), $FailedCount); ?></h4><?php } ?>
                <?php if ($SkippedCount) { ?><h4 class="migrate-status-yellow">
                    <?php echo sprintf(__('%d files skipped', 'powerpress'), $SkippedCount); ?></h4><?php } ?>
                </div>

            </div>
            <div class="powerpress-step pp_col divider-left" id="powerpreess_step_1b">
                <h2><b><?php echo __('How long will this take?', 'powerpress'); ?></b></h2>
                <p><?php echo __('Times may vary, dependent on the number of files. We will notify you once the migration is complete. You can leave this page and check back later.', 'powerpress'); ?></p>
                <div class="pp_flex-grid" style="justify-content: space-around">
                <h4>
                    <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;refresh_migrate_status=1"); ?>">
                        <?php echo __('Refresh Page', 'powerpress'); ?>
                    </a>
                </h4>
                <h4>
                    <a href="<?php echo admin_url() . wp_nonce_url("admin.php?action=powerpress-jquery-migrate-queue", 'powerpress-jquery-migrate-queue'); ?>&migrate_step=2&KeepThis=true&TB_iframe=true&modal=false" class="thickbox">
                        <?php echo __('Migration Status Details', 'powerpress'); ?>
                    </a>
                </h4>
                </div>
                <br />
                <p class="migrate-status-red"><?php echo __('Remember, complete the migration before deleting the source of your files.', 'powerpress'); ?></p>
            </div>
        </div>
    </div>
        <style>
            #wpfooter {
                position: relative;
            }
        </style>
        <div id="pp-create-episode" class="pp-migrate-container">
            <div class="pp-migrate-content">
                <section id="one" class="pp_wrapper">
                    <div class="pp_inner">

                        <div class="pp_flex-grid">

                            <div class="pp_col">
                                <div style="border: none; margin-bottom: 0;padding-left: 8px;">
                                    <div>
                                        <div class="pp_button-container" style="margin-bottom: 1em;float: left;">
                                            <a href="<?php echo admin_url('post-new.php') ?>"><button type="button" class="pp_button"><span><?php echo __('Create a new episode', 'powerpress'); ?></span></button></a>
                                        </div>
                                        <div class="btn-caption-container">
                                            <p style="width: 100%; margin-bottom: 1ch; float: left;"><?php echo __('Release a new episode or blog post.', 'powerpress'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pp_col divider-left" style="padding-left: 2.5em;">
                                <div style="border: none; margin-bottom: 0;">
                                    <div>
                                        <div class="pp_button-container" style="margin-bottom: 1em;float: left;">
                                            <a href="<?php echo admin_url('admin.php?page=powerpressadmin_basic') ?>">
                                                <button type="button" class="pp_button"><span><?php echo __('Go to settings', 'powerpress'); ?></span></button>
                                            </a>
                                        </div>
                                        <div class="btn-caption-container">
                                            <p style="float: left;"><?php echo __('Continue with PowerPress options for your feed and website.', 'powerpress'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
            </div>
        </div>
        <?php
    }

    if( empty($BlubrryQueuedCount) && !empty($MigratedCount) && empty($CompletedCount) )
    {  ?>

        <div id="pp-migration-marketing" class="pp-migrate-container">
            <div class="pp-migrate-content">
                <h4><b><?php echo __('Advanced Statistics provide unparalleled insight into your show', 'powerpress'); ?></b></h4>
                <div style="margin-top: 1em;">
                    <img src="<?php echo powerpress_get_root_url(); ?>images/onboarding/blubrry_stats.png" alt="" class="pp-migration-image" />
                    <h4 class="pp-migrate-subtitle"><?php echo __('Discover your listener retention data! Find out how long your audience is listening to your episodes on a regular basis. Included in any hosting plan.', 'powerpress'); ?>
                        <br />
                        <a href="https://secure.blubrry.com/checkout/manage-subscriptions/">
                            <?php echo __('Upgrade to hosting here', 'powerpress'); ?>
                        </a>
                    </h4>
                </div>
            </div>
        </div>


        <div id="powerpress_steps" class="pp-migrate-container">
            <div class="pp-migrate-container-heading">
                <h1><div class="powerpress-step-blue">3</div><?php echo __('Almost Done', 'powerpress'); ?></h1>
            </div>
            <div class="pp_flex-grid pp-migrate-content">
                <div class="powerpress-step pp_col">
                    <h1><?php echo sprintf(__('%d files migrated', 'powerpress'), $MigratedCount); ?></h1>
                    <h4><a href="<?php echo admin_url() . wp_nonce_url("admin.php?action=powerpress-jquery-migrate-queue", 'powerpress-jquery-migrate-queue'); ?>&migrate_step=2&KeepThis=true&TB_iframe=true&modal=false" class="thickbox"><?php echo __('View Results', 'powerpress'); ?></a></h4>
                    <br />
                    <?php if ($FailedCount) { ?><h4 class="migrate-status-red">
                        <?php echo sprintf(__('%d files failed', 'powerpress'), $FailedCount); ?></h4><?php } ?>
                    <form enctype="multipart/form-data" method="post" action="<?php echo admin_url( 'admin.php?page=powerpress/powerpressadmin_migrate.php'); ?>">
                        <?php wp_nonce_field('powerpress-migrate-media'); ?>
                        <input type="hidden" name="action" value="powerpress-migrate-media" />
                        <input type="hidden" name="migrate_action" value="update_episodes" />
                        <input type="hidden" name="PowerPressVerifyURLs" value="1" />
                        <div class="pp_button-container" style="margin-top: 1em; float: left;">
                            <b><button type="submit" class="pp_button"><span><?php echo __('UPDATE EPISODES', 'powerpress'); ?></span></button></b>
                        </div>
                    </form>
                </div>
                <div class="powerpress-step pp_col divider-left" id="powerpreess_step_1b">
                    <h2><b><?php echo __('What is this?', 'powerpress'); ?></b></h2>
                    <p><?php echo __('This is your last chance to check all of your files have been migrated. If you don’t see any issues, you’re ready to update your episodes with your new Blubrry media.', 'powerpress'); ?></p>
                </div>

            </div>
        </div>
        <?php } ?>

    <?php if (!empty($CompletedCount) ) {
        $publisher_migration_url = get_option('blubrry_manage_media');
        if (!$publisher_migration_url) {
            $publisher_migration_url = 'https://publish.blubrry.com/';
        }
        ?>
        <div id="powerpress_steps" class="pp-migrate-container">
            <div class="pp-migrate-container-heading">
                <h1><div class="powerpress-step-blue">4</div><?php echo __('Migration Finished', 'powerpress'); ?></h1>
            </div>
            <div class="pp_flex-grid pp-migrate-content">
                <div class="powerpress-step pp_col">
                    <h1><?php echo sprintf(__('%d files migrated', 'powerpress'), $MigratedCount); ?></h1>
                    <h4>
                        <a href="<?php echo admin_url() . wp_nonce_url("admin.php?action=powerpress-jquery-migrate-queue", 'powerpress-jquery-migrate-queue'); ?>&migrate_step=2&KeepThis=true&TB_iframe=true&modal=false" class="thickbox">
                            <?php echo __('View Migrated List', 'powerpress'); ?>
                        </a>
                    </h4>
                    <?php if ($FailedCount) { ?><br /><h4 class="migrate-status-red">
                        <?php echo sprintf(__('%d files failed', 'powerpress'), $FailedCount); ?></h4><?php } ?>

                    <div class="pp_button-container" style="margin-top: 1em; float: left;">
                        <a href="<?php echo $publisher_migration_url; ?>" target="_blank">
                            <b><button type="button" class="pp_button"><span><?php echo __('GO TO MEDIA LIBRARY', 'powerpress'); ?></span></button></b>
                        </a>
                    </div>
                </div>
                <div class="powerpress-step pp_col divider-left" id="powerpreess_step_1b">
                    <h2><b><?php echo __('Files Missing?', 'powerpress'); ?></b></h2>
                    <p><?php echo __('This is your last chance to check all of your files have been migrated. If you don’t see any issues, you’re ready to update your episodes with your new Blubrry media.', 'powerpress'); ?></p>
                    <br />
                    <p class="migrate-status-red"><?php echo __('Remember, complete the migration before deleting the source of your files.', 'powerpress'); ?></p>
                    <h4>
                        <b><a href="<?php echo admin_url(); echo wp_nonce_url( "admin.php?page=powerpress/powerpressadmin_migrate.php&amp;migrate_action=reset_migrate_media&amp;action=powerpress-migrate-media", 'powerpress-migrate-media'); ?>" onclick="return confirm('<?php echo esc_js(__('Reset migration, are you sure?','powerpress')); ?>');"><?php echo __('RETRY MIGRATION', 'powerpress'); ?></a></b>
                    </h4>
                </div>

            </div>
        </div>
        <style>
            #wpfooter {
                position: relative;
            }
        </style>
        <div id="pp-create-episode" class="pp-migrate-container">
            <div class="pp-migrate-content">
                <section id="one" class="pp_wrapper">
                    <div class="pp_inner">

                        <div class="pp_flex-grid">

                            <div class="pp_col">
                                <div style="border: none; margin-bottom: 0;padding-left: 8px;">
                                    <div>
                                        <div class="pp_button-container" style="margin-bottom: 1em;float: left;">
                                            <a href="<?php echo admin_url('post-new.php') ?>"><button type="button" class="pp_button"><span><?php echo __('Create a new episode', 'powerpress'); ?></span></button></a>
                                        </div>
                                        <div class="btn-caption-container">
                                            <p style="width: 100%; margin-bottom: 1ch; float: left;"><?php echo __('Release a new episode or blog post.', 'powerpress'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pp_col divider-left" style="padding-left: 2.5em;">
                                <div style="border: none; margin-bottom: 0;">
                                    <div>
                                        <div class="pp_button-container" style="margin-bottom: 1em;float: left;">
                                            <a href="<?php echo admin_url('admin.php?page=powerpressadmin_basic') ?>">
                                                <button type="button" class="pp_button"><span><?php echo __('Go to settings', 'powerpress'); ?></span></button>
                                            </a>
                                        </div>
                                        <div class="btn-caption-container">
                                            <p style="float: left;"><?php echo __('Continue with PowerPress options for your feed and website.', 'powerpress'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
            </div>
        </div>
    <?php } ?>
	<div class="clear"></div>
    </div>
    <div class="clear"></div>
    </div>
<?php
}

/**
 * This function, called hourly by cron, will get the migration status and update the database accordingly.
 *
 * @return void
 */
function powerpress_admin_migration_cron() {
    // do logic to get correct counts and update wp options
    $MigrateStatus = get_option('powerpress_migrate_status');
    if( empty($MigrateStatus) || $MigrateStatus['updated_timestamp'] < current_time('timestamp')-(60*30) || !empty($_GET['refresh_migrate_status']) ) // Check every 30 minutes
    {
        $update_option = $MigrateStatus == false ? false : true;

        $MigrateStatus = powerpress_admin_migrate_get_status();
        if( is_array($MigrateStatus) )
        {
            $MigrateStatus['updated_timestamp'] = current_time( 'timestamp' );
            if( $update_option )
                update_option('powerpress_migrate_status', $MigrateStatus);
            else
                add_option('powerpress_migrate_status', $MigrateStatus, '', 'no' );
        }
    }
}
