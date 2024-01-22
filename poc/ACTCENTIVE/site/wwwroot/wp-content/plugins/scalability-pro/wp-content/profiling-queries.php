<?php 
function sp_start_timer($query) {
    global $SPRO_GLOBALS;
    if(!isset($SPRO_GLOBALS['enable_slow_log']) || !$SPRO_GLOBALS['enable_slow_log']) {
        return $query;
    }
    global $start_time;
    $start_time = microtime(true);

    return $query;
}

function sp_save_long_queries( $posts = null, $query = null ) {
    global $SPRO_GLOBALS;
    if(!isset($SPRO_GLOBALS['enable_slow_log']) || !$SPRO_GLOBALS['enable_slow_log']) {
        return $posts;
    }

    if (array_key_exists('save_running', $SPRO_GLOBALS) && $SPRO_GLOBALS['save_running']) {
        return $posts;
    }
    $SPRO_GLOBALS['save_running'] = true;
    global $start_time, $wpdb;

    $end_time = microtime(true);
    $duration = $end_time - $start_time;

    // other than the slow query limit, we also have a query pattern
    $query_pattern_matched = false;
    $query_pattern = "";
    if (isset($SPRO_GLOBALS['query_pattern'])) {
        $query_pattern = $SPRO_GLOBALS['query_pattern'];
    }
    if (!empty($query_pattern)) {
        set_error_handler(function($errno, $errstr) {
            // Handle error silently, maybe log it if necessary.
        });
    
        $isValidPattern = @preg_match('/' . $query_pattern . '/', $wpdb->last_query);
    
        restore_error_handler(); // Restore previous error handler
    
        if ($isValidPattern === false) {
            // Log error or handle as you see fit.
        } elseif ($isValidPattern === 1) {
            $query_pattern_matched = true;
        }
    }
    if( (is_numeric($duration) && $duration > $SPRO_GLOBALS['slow_query_limit']) || $query_pattern_matched) {
        $table_name = $wpdb->prefix . 'spro_slow_query_log';

        $url = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $e = new \Exception;
//        $stacktrace = $e->getTraceAsString(); // this captures #0 and #1 as this function and the apply_filters function
        $stacktrace = $e->getTrace();

        // Remove the first two entries of the stacktrace
        array_shift($stacktrace);  // Removes #0
        array_shift($stacktrace);  // Removes #1

        // Convert stacktrace back into a string
        $stacktrace_str = "";
        foreach ($stacktrace as $i => $trace) {
            $stacktrace_str .= sprintf("#%s %s(%s): %s%s%s()\n",
                $i,
                isset($trace['file']) ? $trace['file'] : '',
                isset($trace['line']) ? $trace['line'] : '',
                isset($trace['class']) ? $trace['class'] : '',
                isset($trace['type']) ? $trace['type'] : '',
                $trace['function']
            );
        }
        try {
            if (!defined('SPRO_MAX_TRACE_CHARS')) {
                define('SPRO_MAX_TRACE_CHARS', 10000);
            }
            
            if (empty($wpdb->prefix)) { // means we are checking multisite stuff inside WP_Site_Query - in this case we must abort since we don't know the table prefix
                $SPRO_GLOBALS['save_running'] = false;
                return $posts;
            }
            // Collect the required data
            $table_name = $wpdb->prefix . 'spro_slow_query_log';
            $time = current_time('mysql');
            $url = $url;
            $sql_query = substr($wpdb->last_query, 0, SPRO_MAX_TRACE_CHARS);
            $stacktrace = substr($stacktrace_str, 0, SPRO_MAX_TRACE_CHARS);
            $duration = $duration;

            // Connect to the database
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            // Check connection
            if ($mysqli->connect_error) {
                error_log("SPRO Save Profile Connection failed: " . $mysqli->connect_error);
                $SPRO_GLOBALS['save_running'] = false;
                return $posts;
            }

            // Prepare the statement
            $stmt = $mysqli->prepare("INSERT INTO `$table_name` (`time`, `url`, `query`, `stacktrace`, `duration`) VALUES (?, ?, ?, ?, ?)");

            // Bind parameters ('s' for string, 'f' for float)
            $stmt->bind_param('ssssd', $time, $url, $sql_query, $stacktrace, $duration);

            // Execute the prepared statement
            if (!$stmt->execute()) {
                // Handle error here
                error_log("SPRO Save Profile failed: " . $stmt->error);
                $SPRO_GLOBALS['save_running'] = false;
                return $posts;
            }

            // Close the statement
            $stmt->close();

            // Close the connection
            $mysqli->close();

            /* We cannot use $wpdb here, otherwise we override things like $wpdb->last_result which is heavily used in product archives etc */
            /*
            $wpdb->insert(
                $table_name,
                array(
                    'time' => current_time( 'mysql' ),
                    'url' => $url,
                    'query' => substr($wpdb->last_query, 0, SPRO_MAX_TRACE_CHARS), // Ensure the query is not too long
                    'stacktrace' => substr($stacktrace_str, 0, SPRO_MAX_TRACE_CHARS), // Ensure the stacktrace is not too long
                    'duration' => $duration
                ),
                array('%s', '%s', '%s', '%s', '%f') // data format
            );
            */
        } catch(Exception $e) {
            // Insert failed, handle error here
        }
    }
    $SPRO_GLOBALS['save_running'] = false;
    return $posts;
}