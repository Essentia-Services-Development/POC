<?php
/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */
 
/**
 * custom option and settings
 */

if (!function_exists('wpi_check_license')) {

    function wpintense_settings_validate_and_save( $input ) {
        // Delete the transient when the options are saved
        $plugins = array('scalability-pro', 'super-speedy-search', 'super-speedy-filters', 'price-comparison-pro', 'external-images', 'auto-infinite-scroll');

        //delete all the transients created in the other function
        foreach($plugins as $plugin) {
            delete_transient('wpi_l_' . $plugin);
        }
    
        // Sanitize and validate the input as needed before saving
        $new_input = array_map('sanitize_text_field', $input);
    
        return $new_input;
    }
    function wpintense_settings_init() {
        // Register a new setting for "wpintense" page.
        register_setting( 'wpintense', 'wpintense_options', 'wpintense_settings_validate_and_save' );
    
        // Register a new section in the "wpintense" page.
        add_settings_section(
            'wpintense_section_license',
            __( 'Settings', 'wpintense' ), 'wpintense_section_license_callback',
            'wpintense'
        );
    
        // Register a new field in the "wpintense_section_license" section, inside the "wpintense" page.
        add_settings_field(
            'wpintense_field_licensekey', // As of WP 4.6 this value is used only internally.
                                    // Use $args' label_for to populate the id inside the callback.
                __( 'License Key', 'wpintense' ),
            'wpintense_field_licensekey_cb',
            'wpintense',
            'wpintense_section_license',
            array(
                'label_for'         => 'wpintense_field_licensekey',
                'class'             => 'wpintense_row',
                'wpintense_custom_data' => 'custom',
            )
        );
    }
    
    add_action( 'admin_init', 'wpintense_settings_init' );

    function wpintense_section_license_callback( $args ) {
        global $superspeedy_plugin_versions;
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo __( '<a href="#wpintense_field_licensekey">Enter your license key below to enable plugin updates.</a>', 'wpintense' ); ?></p>
        <?php 
        $wpilicenses = array();
        $wpilicenses[] = array('slug' => 'scalability-pro', 'name' => 'Scalability Pro', 'license' => wpi_check_license('scalability-pro'), 'function' => 'wpi_getIndexes', 'discord' => 'https://discord.gg/f8BZxBPkMg');
        $wpilicenses[] = array('slug' => 'super-speedy-filters', 'name' => 'Super Speedy Filters', 'license' => wpi_check_license('super-speedy-filters'), 'function' => 'fww_update_db_check', 'discord' => 'https://discord.gg/wFmJaNCeyY');
        $wpilicenses[] = array('slug' => 'super-speedy-search', 'name' => 'Super Speedy Search', 'license' => wpi_check_license('super-speedy-search'), 'function' => 'sss_update_db_check', 'discord' => 'https://discord.gg/acmgV8SaAd');
        $wpilicenses[] = array('slug' => 'external-images', 'name' => 'External Images', 'license' => wpi_check_license('external-images'), 'function' => 'external_images_scripts', 'discord' => 'https://discord.gg/m3jN2pkeFT');
        $wpilicenses[] = array('slug' => 'auto-infinite-scroll', 'name' => 'Auto Infinite Scroll', 'license' => wpi_check_license('auto-infinite-scroll'), 'function' => 'ais_active', 'discord' => 'https://discord.gg/eJFa36gfXG');
        $wpilicenses[] = array('slug' => 'price-comparison-pro', 'name' => 'Price Comparison Pro', 'license' => wpi_check_license('price-comparison-pro'), 'function' => 'pcp_rel2abs', 'discord' => 'https://discord.gg/WkHvqDqFcN');

        $site_url = get_site_url();
        $site_url = str_replace('localhost', 'lh', $site_url); // localhost gets banned by GridPane
        $utm_source = parse_url($site_url, PHP_URL_HOST);

        $action = 'puc_check_for_updates';

        // Get the nonce
        $nonce = wp_create_nonce($action);

        // Construct the URL
        $update_plugin_url = admin_url('plugins.php') . "?puc_check_for_updates=1&_wpnonce=" . $nonce . "&puc_slug=";


        echo '<table class="superspeedy-plugins">';
        echo '<thead><tr><th>Plugin Name</th><th>Installation Status</th><th>License Status</th><th>Changes</th><th>Docs</th><th>Support</th></tr></thead>';
        echo '<tbody>';
        
        foreach($wpilicenses as $wpilicense) {
            echo '<tr>';
            echo '<td>' . $wpilicense['name'] . '</td>';
            
            if ($wpilicense['license']['error'] == 'not installed') {
                echo '<td>Not installed</td>';
                echo '<td>Not checked</td>';
            } else {
                $active = function_exists($wpilicense['function']) ? "Activated" : "Deactivated";
                echo "<td>Installed / $active</td>";
                echo '<td>' . $wpilicense['license']['error'] . '</td>';
            }
        
            if (isset($superspeedy_plugin_versions[$wpilicense['slug']])) {
                $changes = get_changes_since_version($wpilicense['slug'], $superspeedy_plugin_versions[$wpilicense['slug']]);
                if (!$changes) {
                    echo '<td>Unable to check for updates - please check your server allows remote URL requests</td>';
                } else {
                    echo '<td>' . $changes["count"] . ' changes since your last update<br /><a href="https://www.superspeedyplugins.com/support/changelogs/' . $wpilicense['slug'] . '/?utm_source=' . urlencode($utm_source) . '&utm_content=plugin" target="_blank">View Change log</a>';
                    if ($changes["count"] > 0) {
                        echo ' | <a href="' . $update_plugin_url . $wpilicense['slug'] . '">Update Plugin</a>';
                    }
                    echo '</td>';
                }
            } else {
                $changes = get_changes_since_days($wpilicense['slug'], 90);
                echo '<td>' . $changes["count"] . ' changes in the last 90 days<br /><a href="https://www.superspeedyplugins.com/support/changelogs/' . $wpilicense['slug'] . '/?utm_source=' . urlencode($utm_source) . '&utm_content=plugin" target="_blank">View Change log</a></td>';
            }
            echo '<td><a href="https://www.superspeedyplugins.com/kb/' . $wpilicense['slug'] . '/?utm_source=' . urlencode($utm_source) . '&utm_content=plugin" target="_blank">Knowledge Base</a><br /><a href="https://www.superspeedyplugins.com/tag/' . $wpilicense['slug'] . '/?utm_source=' . urlencode($utm_source) . '&utm_content=plugin">Plugin Articles</a></td>';
            echo '<td><a href="https://www.superspeedyplugins.com/question/category/' . $wpilicense['slug'] . '/?utm_source=' . urlencode($utm_source) . '&utm_content=plugin">Public Q&A</a><br /><a href="' . $wpilicense['discord'] . '" target="_blank">Live Support</a></td>';
        
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '<p><strong>Please note:</strong> Our plugins must be activated in order to update them. If you wish to update them without activating them, you must download the latest zip from <a href="https://www.superspeedyplugins.com/my-account/downloads/?utm_source=' . urlencode($utm_source) . '&utm_content=plugin">your account</a> and upload the new zip file manually.</p>';
        ?>
        <style> 
            table.superspeedy-plugins { background-color:white; border:1px solid #ccc; border-radius:4px; border-collapse: collapse;}
            table.superspeedy-plugins thead th { text-align: left; padding:4px 5px; background-color:#444; color: #f1f1f1; } 
            table.superspeedy-plugins tbody td { padding: 4px 5px; } 
            table.superspeedy-plugins tr td {border-top: 1px solid #ccc; }
        </style>
        <?php
    }

    function wpintense_field_licensekey_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'wpintense_options' );
        $site_url = get_site_url();
        $site_url = str_replace('localhost', 'lh', $site_url); // localhost gets banned by GridPane

        $utm_source = parse_url($site_url, PHP_URL_HOST);

        ?>
        <input type="password" id="<?php echo esc_attr( $args['label_for'] ); ?>"
                data-custom="<?php echo esc_attr( $args['wpintense_custom_data'] ); ?>"
                name="wpintense_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                value="<?php esc_html_e($options[ $args['label_for'] ]);?>">
        <p class="description">
            Your license key enables updates for your plugins. You can find your license key in your <a href="https://www.superspeedyplugins.com/my-account/?utm_source=<?php echo urlencode($utm_source)?>&utm_content=plugin"> account page</a>.
        </p>
        <p>You can renew your license and lock in 25%-off whatever you paid for your first year. <a href="https://www.superspeedyplugins.com/my-account/renewals/?utm_source=<?php echo urlencode($utm_source)?>&utm_content=plugin">Renew your license here</a>.</p>
        <p>You can also upgrade your license to allow more websites and only pay the difference. <a href="https://www.superspeedyplugins.com/my-account/upgrade-account/?utm_source=<?php echo urlencode($utm_source)?>&utm_content=plugin">Upgrade your license here</a>.</p>

        <?php
    }
    
    /**
     * Add the top level menu page.
     */
    function wpintense_options_page() {
        $wpilicenses = array();
        $wpilicenses[] = array('slug' => 'scalability-pro', 'name' => 'Scalability Pro', 'license' => wpi_check_license('scalability-pro'), 'function' => 'wpi_getIndexes', 'discord' => 'https://discord.gg/f8BZxBPkMg');
        $wpilicenses[] = array('slug' => 'super-speedy-filters', 'name' => 'Super Speedy Filters', 'license' => wpi_check_license('super-speedy-filters'), 'function' => 'fww_update_db_check', 'discord' => 'https://discord.gg/wFmJaNCeyY');
        $wpilicenses[] = array('slug' => 'super-speedy-search', 'name' => 'Super Speedy Search', 'license' => wpi_check_license('super-speedy-search'), 'function' => 'sss_update_db_check', 'discord' => 'https://discord.gg/acmgV8SaAd');
        $wpilicenses[] = array('slug' => 'external-images', 'name' => 'External Images', 'license' => wpi_check_license('external-images'), 'function' => 'external_images_scripts', 'discord' => 'https://discord.gg/m3jN2pkeFT');
        $wpilicenses[] = array('slug' => 'auto-infinite-scroll', 'name' => 'Auto Infinite Scroll', 'license' => wpi_check_license('auto-infinite-scroll'), 'function' => 'ais_active', 'discord' => 'https://discord.gg/eJFa36gfXG');
        $wpilicenses[] = array('slug' => 'price-comparison-pro', 'name' => 'Price Comparison Pro', 'license' => wpi_check_license('price-comparison-pro'), 'function' => 'pcp_rel2abs', 'discord' => 'https://discord.gg/WkHvqDqFcN');

        global $superspeedy_plugin_versions;
        $total_changes = 0;
        // Iterate over licenses array and count total changes
        foreach ($wpilicenses as $wpilicense) {
            if (isset($superspeedy_plugin_versions[$wpilicense['slug']])) { // checks if the plugin is active
                $changes = get_changes_since_version($wpilicense['slug'], $superspeedy_plugin_versions[$wpilicense['slug']]);
                if ($changes) {
                    $total_changes += $changes['count'];
                }
            }
        }
        // Generate menu title, add bubble if there are updates
        $menu_title = 'Super Speedy ' . ($total_changes ? ' <span class="update-plugins count-' . $total_changes . '"><span class="plugin-count">' . $total_changes . '</span></span>' : '');

        add_menu_page(
            'Super Speedy',
            $menu_title,
            'manage_options',
            'wpintense',
            'wpintense_options_page_html',
            'https://www.superspeedyplugins.com/assets/favicon2-e1691072779325.png' // Add the URL of your icon here
        );
    }
    
    
    
    /**
     * Register our wpintense_options_page to the admin_menu action hook.
     */
    add_action( 'admin_menu', 'wpintense_options_page' );
    
    
    /**
     * Top level menu callback function
     */
    function wpintense_options_page_html() {
        // check user capabilities
    
        // add error/update messages
    
        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( 'wpintense_messages', 'wpintense_message', __( 'Settings Saved', 'wpintense' ), 'updated' );
        }
    
        // show error/update messages
        settings_errors( 'wpintense_messages' );
        ?>
        <div class="wrap">
            <div style="padding:10px;"><img src="https://www.superspeedyplugins.com/assets/super-speedy-plugins-fire.png"></div>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting "wpintense"
                settings_fields( 'wpintense' );
                // output setting sections and their fields
                // (sections are registered for "wpintense", each field is registered to a specific section)
                do_settings_sections( 'wpintense' );
                // output save settings button
                submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
        <?php
    }
    function wpi_check_license($slug) {
        $options = get_option('wpintense_options');
        $installed = false;
        if (is_dir(WP_PLUGIN_DIR . '/' . $slug)) {
            $installed = true;
        }
        if (!$installed) {
            return array('error' => 'not installed', 'message' => 'This plugin is not installed');
        }
        if (!isset($options['wpintense_field_licensekey'])) {
            return array('error' => 'invalid', 'message' =>'Enter a valid license key to enable updates for Super Speedy plugins.');
        }
        if (get_transient('wpi_l_' . $slug)) {
            return get_transient('wpi_l_' . $slug);
        }
        
        $homeurl = get_home_url();
        $parsedurl = parse_url($homeurl);
        if (isset($parsedurl['host']) && !empty($parsedurl['host']) && isset($options['wpintense_field_licensekey']) && !empty($options['wpintense_field_licensekey']) && !empty($slug)) {
            $keycheckurl = 'https://www.superspeedyplugins.com/wpiapi/check_product_key/' . $options['wpintense_field_licensekey'] . '/' . $slug . '/' . $parsedurl['host'] . '/?v=1';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_URL, $keycheckurl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_HEADER, FALSE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($curl, CURLOPT_TIMEOUT,60);

            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Accept: application/json", 
            "Cache-Control: no-cache"
            ));
            $keycheckdata = curl_exec($curl);
            curl_close($curl);

            //echo "<pre>" . print_r($keycheckdata, true) . "</pre>";

            $api_resp = json_decode($keycheckdata);
    //        echo "<pre>" . print_r($api_resp, true) . "</pre>";

    //        $api_resp = array('activated' => true, 'error' => 'No message');
            if (isset($api_resp->activated) && (bool) $api_resp->activated == true) {
                $data = array('error' => 'valid', 'message' => 'Plugin installed with valid key');
                set_transient('wpi_l_' . $slug, $data, WEEK_IN_SECONDS);
                return $data;
            }
            if (isset($api_resp) && $api_resp->error == 'invalid') {
                $data = array('error' => 'invalid', 'message' => 'Enter a valid license key to enable updates for Super Speedy plugins.');
                set_transient('wpi_l_' . $slug, $data, WEEK_IN_SECONDS);
                return $data;
            }
            if (isset($api_resp) && $api_resp->error == 'expired') {
                $data = array('error' => 'expired', 'message' => 'License expired - please purchase a license extension to enable updates.');
                set_transient('wpi_l_' . $slug, $data, WEEK_IN_SECONDS);
                return $data;
            }
            if (isset($api_resp) && $api_resp->error == 'exceeded') {
                $data = array('error' => 'exceeded', 'message' => 'Website limit exceeded - please purchase additional licenses or deactivate a previous website license to enable updates.');
                set_transient('wpi_l_' . $slug, $data, WEEK_IN_SECONDS);
                return $data;
            }
        } else {
            return array('error' => 'invalid', 'message' => 'Host URL cannot be identified.');
        }

//        return array('error' => $api_resp->error, 'message' => 'Enter a valid license key to enable updates for WP Intense plugins.');
        $data = array('error' => $api_resp->error, 'message' => $api_resp->error);
        set_transient('wpi_l_' . $slug, $data, HOUR_IN_SECONDS);

        return array('error' => $api_resp->error, 'message' => $api_resp->error);
    }


}

if (!function_exists('wpi_add_license_to_remote_update_request')) {
    function wpi_add_license_to_remote_update_request($update) {
        // Check if the download_url is set
        if (isset($update->download_url)) {
            $options = get_option( 'wpintense_options' );
            if (isset($options['wpintense_field_licensekey'])) {
                // Your license key
                $license_key = $options['wpintense_field_licensekey'];

                // Your domain
                $domain = 'domain_not_found';
                $homeurl = get_home_url();
                $parsedurl = parse_url($homeurl);
                if (isset($parsedurl['host']) && !empty($parsedurl['host'])) {
                    $domain = $parsedurl['host'];
                }
                if ($domain == 'localhost') {
                    $domain = 'lh';
                }
                // Append these parameters to the download_url
                $update->download_url = add_query_arg(array(
                    'license_key' => $license_key,
                    'domain'      => $domain
                ), $update->download_url);
            } else {
                $update->upgrade_notice = 'Enter a valid license key to enable updates for Super Speedy plugins.';
            }
        }
        return $update;
    }
}
if (!function_exists('wpi_add_license_to_remote_http_request')) {
    function wpi_add_license_to_remote_http_request($query_params) {
        $options = get_option( 'wpintense_options' );

        if (isset($options['wpintense_field_licensekey'])) {
            $query_params['license_key'] = $options['wpintense_field_licensekey'];
        } else {
            $query_params['license_key'] = 'no_license_key';
        }
        $homeurl = get_home_url();
        $parsedurl = parse_url($homeurl);
        if (isset($parsedurl['host']) && !empty($parsedurl['host'])) {
            $query_params['domain'] = $parsedurl['host'];
        } else {
            $query_params['domain'] = 'no_host';
        }
        if ($query_params['domain'] == 'localhost') {
            $query_params['domain'] = 'lh';
        }
        return $query_params;
    }
}
if (!function_exists('wpi_add_license_to_remote_get')) {
    function wpi_add_license_to_remote_get($query_params) {
        $options = get_option( 'wpintense_options' );

        if (isset($options['wpintense_field_licensekey'])) {
            $query_params['license_key'] = $options['wpintense_field_licensekey'];
        } else {
            $query_params['license_key'] = 'no_license_key';
        }
        $homeurl = get_home_url();
        $parsedurl = parse_url($homeurl);
        if (isset($parsedurl['host']) && !empty($parsedurl['host'])) {
            $query_params['domain'] = $parsedurl['host'];
        } else {
            $query_params['domain'] = 'no_host';
        }
        if ($query_params['domain'] == 'localhost') {
            $query_params['domain'] = 'lh';
        }
        return $query_params;
    }
}
if (!function_exists('get_changes_since_version')) {
    function get_changes_since_version($plugin, $version) {
        // Check if data is already in the cache
        $transient_key = 'wpi_changes_' . $plugin . '_' . $version;
        if (($changes = get_transient($transient_key)) !== false) {
            return $changes;
        }
        // Open the changelog file
        $response = ssp_remote_get('https://www.superspeedyplugins.com/assets/plugins/' . $plugin . '/readme.txt?v=' . $version);

        // Check if there was an error in retrieving the content
        if (is_wp_error($response)) {
            return false; // failed to check for updates
        }

        $file_content = wp_remote_retrieve_body($response);
        $lines = array_map('trim', explode("\n", $file_content));

        //todo: need to configure cloudflare & nginx so .txt files are not cached if they have a version number        
        // Initialize variables
        $changes = [];
        $count = 0;
        $record = false;

        // Skip the early lines to only look at the changelog
        $changelog_position = array_search("== Changelog ==", $lines);
        if ($changelog_position !== false) {
            $lines = array_slice($lines, $changelog_position + 1);
        }
        // Read the file line by line
        foreach ($lines as $line) {
            // Trim the line to remove leading/trailing whitespace
            $line = trim($line);

            // Check if the line is a version line
            if (strpos($line, '=') === 0 && strpos($line, '==') !== 0) {
                // Extract the version number from the line
                preg_match("/\d+\.\d+/", $line, $matches);
                $line_version = $matches[0] ?? null;

                try {
                    // Try to cast versions to floats and perform comparison
                    $line_version_float = (float)$line_version;
                    $version_float = (float)$version;
                
                    // If any of the versions couldn't be converted to a float, exception will be thrown and this line won't be executed
                    if ($line_version_float <= $version_float) {
                        break;
                    }
                } catch (Exception $e) {
                    // An error occurred while converting the versions to floats, ignore and continue
                    continue;
                }
                
                // Start recording changes if we've passed the version in the changelog
                $record = true;
            }
            
            // Record the change
            if ($record && strpos($line, '*') === 0) {
                $changes[] = $line;
                $count++;
            }
        }

        // Prepare result
        $result = ["count" => $count, "changes" => $changes];
        // Store data in cache, keep it for 8 hours
        set_transient($transient_key, $result, 8 * HOUR_IN_SECONDS);
        return $result;
    }
}
if (!function_exists('get_changes_since_days')) {
    function get_changes_since_days($plugin, $days) {
            // Check if data is already in the cache
        $transient_key = 'wpi_changes_days_' . $plugin . '_' . $days;
        if (($changes = get_transient($transient_key)) !== false) {
            return $changes;
        }

        $response = ssp_remote_get('https://www.superspeedyplugins.com/assets/plugins/' . $plugin . '/readme.txt');

        // Check if there was an error in retrieving the content
        if (is_wp_error($response)) {
            return false; // failed to check for updates
        }

        $file_content = wp_remote_retrieve_body($response);
        $lines = array_map('trim', explode("\n", $file_content));
        
        // Initialize variables
        $changes = [];
        $count = 0;
        $record = false;

        // Calculate the date X days ago
        $date_x_days_ago = new DateTime();
        $date_x_days_ago->sub(new DateInterval("P{$days}D"));

        // Skip the early lines to only look at the changelog
        $changelog_position = array_search("== Changelog ==", $lines);
        if ($changelog_position !== false) {
            $lines = array_slice($lines, $changelog_position + 1);
        }
        // Read the file line by line
        foreach ($lines as $line) {
            // Trim the line to remove leading/trailing whitespace
            $line = trim($line);
            

            // Check if the line is a version line
            if (strpos($line, '=') === 0 && strpos($line, '==') !== 0) {
                // Extract the date from the line
                $date_start = strpos($line, '(') + 1;
                $date_length = strpos($line, ')') - $date_start;
                $line_date_str = substr($line, $date_start, $date_length);
                $line_date = DateTime::createFromFormat('dS F Y', $line_date_str);

                // If we've reached a date that is X days ago or more, stop recording changes
                if ($line_date < $date_x_days_ago) {
                    break;
                }

                // Start recording changes if we've passed the date in the changelog
                $record = true;
            }
            
            // Record the change
            if ($record && strpos($line, '*') === 0) {
                $changes[] = $line;
                $count++;
            }
        }

        // Prepare result
        $result = ["count" => $count, "changes" => $changes];

        // Store data in cache, keep it for 24 hours
        set_transient($transient_key, $result, 24 * HOUR_IN_SECONDS);

        return $result;

    }
}
if (!function_exists('ssp_remote_get')) {
    function ssp_remote_get($url, $args = array()) {
        // Create a unique transient key based on the URL
        $transient_key = 'ssp_' . md5($url);
    
        // Try to get the content from the transient
        $cached_content = get_transient($transient_key);
    
        if ($cached_content !== false) {
            return $cached_content;
        }
    
        // If not in transient, fetch the content
        $response = wp_remote_get($url, $args);
    
        // Cache the response, whether it's a WP_Error or not
        set_transient($transient_key, $response, 2 * HOUR_IN_SECONDS);
    
        return $response;
    }
    
}