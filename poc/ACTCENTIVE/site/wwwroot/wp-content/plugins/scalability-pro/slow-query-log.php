<?php
function maybe_update_spro_db() {
    $installed_ver = get_option('SPRO_DB_VERSION', false);
    if ($installed_ver != SPRO_DB_VERSION) {
        sp_create_table();
    }
}
add_action('init', 'maybe_update_spro_db');
function sp_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'spro_slow_query_log';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        url varchar(2080) NOT NULL,
        query longtext NOT NULL,
        stacktrace longtext NOT NULL,
        duration decimal(10,5) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // The table could not be created, handle error here
    } else {
        update_option('SPRO_DB_VERSION', SPRO_DB_VERSION, true);
    }
}
register_activation_hook( __FILE__, 'sp_create_table' );


/*backup code to perform profiling if no symlink available */
$real_wp_content_dir = realpath(WP_CONTENT_DIR);  // Get real path in case WP_CONTENT_DIR has symbolic links
$target_symlink = $real_wp_content_dir . '/db.php';

if (!is_link($target_symlink)) {
    $target_symlink = WP_CONTENT_DIR . '/db.php';
}
if (!is_link($target_symlink)) {
    add_filter('query', 'sp_start_timer', 1);
    add_filter('posts_results', 'sp_save_long_queries', 10, 2);
} else {
    $actual_target = @readlink($target_symlink); // Suppressed in case the link doesn't exist
    if ($actual_target !== $real_wp_content_dir . '/plugins/scalability-pro/wp-content/db.php') {
        add_filter('query', 'sp_start_timer', 1);
        add_filter('posts_results', 'sp_save_long_queries', 10, 2);
    }    
}
add_action('init', 'maybe_enable_slow_query_log');
function maybe_enable_slow_query_log() {
    global $SPRO_GLOBALS;

    $options = get_option('wpiperf_settings');
    if (!isset($options['enable_slow_log']) || !$options['enable_slow_log']) {
        return;
    }
    $limit = 5.0;
    if(isset($options['slow_query_limit'])) {
        $limit = doubleval($options['slow_query_limit']);
    }
    $query_pattern = "";
    if(isset($options['query_pattern'])) {
        $query_pattern = $options['query_pattern'];
    }
    $SPRO_GLOBALS['enable_slow_log'] = $options['enable_slow_log'];
    $SPRO_GLOBALS['slow_query_limit'] = $limit;
    $SPRO_GLOBALS['query_pattern'] = $query_pattern;
}

function spro_fetch_slow_queries() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'spro_slow_query_log';
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $per_page = 10;

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE ID < %d ORDER BY id DESC LIMIT %d", $offset, $per_page));

    // Check if there are results to send back.
    if (!empty($results)) {
        // Loop through the results and print each one.
        foreach ($results as $result) {
            // Print your table row here.
            echo '<tr><td><div class="slow_query_id spro_sq_td_container">' . $result->id . '</div></td><td><div class="spro_sq_td_container">' . $result->time . '</div></td><td><div class="spro_sq_td_container">' . $result->url . '</div></td><td><div class="spro_sq_td_container">' . $result->query . '</div></td><td><div class="spro_sq_td_container">' . $result->stacktrace . '</div></td><td><div class="spro_sq_td_container">' . $result->duration . '</div></td></tr>';
        }
    } else {
        // No more posts, let's print an end message.
        echo '<tr><td colspan="5">No more slow queries.</td></tr>';
    }

    wp_die(); // All ajax handlers die when finished
}
add_action('wp_ajax_spro_fetch_slow_queries', 'spro_fetch_slow_queries'); // Add AJAX handler for logged in users
add_action('wp_ajax_nopriv_spro_fetch_slow_queries', 'spro_fetch_slow_queries'); // Add AJAX handler for not logged in users

// slow-query-log.php

add_action('wp_ajax_spro_create_symlink', 'spro_create_symlink_handler');
add_action('wp_ajax_spro_delete_symlink', 'spro_delete_symlink_handler');

function spro_create_symlink_handler() {
    global $SPRO_GLOBALS;
    $source = plugin_dir_path(__FILE__) . 'wp-content/db.php';
    $destination = realpath(WP_CONTENT_DIR) . '/db.php';

    // Check if symlink already exists
    if (is_link($destination)) {
        wp_send_json_error('A symlink already exists.');
        return;
    }

    $success = @symlink($source, $destination);
    if ($success) {
        $save_globals = $SPRO_GLOBALS;
        if (array_key_exists('save_running', $save_globals)) {
            unset($save_globals['save_running']);
        }
        add_or_update_spro_globals($save_globals);
        wp_send_json_success('Symlink created successfully.');
    } else {
        wp_send_json_error('Failed to create symlink.');
    }
}

function spro_delete_symlink_handler() {
    $destination = realpath(WP_CONTENT_DIR) . '/db.php';
    
    // Check if our symlink exists
    if (!is_link($destination)) {
        $destination = WP_CONTENT_DIR . '/db.php';
    }
    
    if (is_link($destination) && readlink($destination) === plugin_dir_path(__FILE__) . 'wp-content/db.php') {
        $success = @unlink($destination);
        if (function_exists('opcache_reset')) {
            opcache_reset();
//            echo "OPcache has been reset.";
        } else {
//            echo "OPcache is not enabled.";
        }
        if ($success) {
            wp_send_json_success('Symlink deleted successfully.');
        } else {
            wp_send_json_error('Failed to delete symlink xxx.');
        }
    } else {
        wp_send_json_error('No symlink found or symlink not owned by our plugin.');
    }
}


function add_or_update_spro_globals($values) {
    $wp_config_path = ABSPATH . 'wp-config.php'; // Adjust this path as needed
    if (!is_writable($wp_config_path)) {
        $wp_config_path = ABSPATH . '../wp-config.php'; // Adjust this path as needed
        if (!is_writable($wp_config_path)) {
            return false;
        }
    }

    $contents = file_get_contents($wp_config_path);
    $spro_globals_line = "\$SPRO_GLOBALS = " . var_export($values, true) . ";";

    if (strpos($contents, '$SPRO_GLOBALS') !== false) {
        // Update existing $SPRO_GLOBALS
        $contents = preg_replace('/\$SPRO_GLOBALS = [^;]*;/', $spro_globals_line, $contents);
    } else {
        // Add $SPRO_GLOBALS before "/* That's all, stop editing! Happy publishing. */"
        $stop_editing_comment = "/* That's all, stop editing! Happy publishing. */";
        $contents = str_replace($stop_editing_comment, "$spro_globals_line\n\n$stop_editing_comment", $contents);
    }

    try {
        $result = @file_put_contents($wp_config_path, $contents);
        return $result;
    } catch (Exception $e) {
        // You could also log the exception message if needed
        error_log('An error occurred while updating wp-config.php: ' . $e->getMessage());
        return false;
    }
}

function remove_spro_globals() {
    $wp_config_path = ABSPATH . 'wp-config.php'; // Adjust this path as needed
    if (!is_writable($wp_config_path)) {
        return false;
    }

    $contents = file_get_contents($wp_config_path);

    if (strpos($contents, '$SPRO_GLOBALS') === false) {
        // $SPRO_GLOBALS not found
        return true;
    }

    // Remove existing $SPRO_GLOBALS
    $contents = preg_replace('/\$SPRO_GLOBALS = [^;]*;/', '', $contents);

    try {
        $result = @file_put_contents($wp_config_path, $contents);
        return $result;
    } catch (Exception $e) {
        // You could also log the exception message if needed
        error_log('An error occurred while updating wp-config.php: ' . $e->getMessage());
        return false;
    }
}
