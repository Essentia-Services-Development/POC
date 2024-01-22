<?php

// The functions and hooks in this file help us with various tasks and are usually used by more than one module.

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Adds more cron schedule intervals
add_filter( 'cron_schedules', 'be_mu_add_schedule_intervals' );

/**
 * If a certain variable is added to the URL of an admin page and the user is a Super Admin, the page will start auto-refreshing.
 * This speeds up cron jobs for sites that do not have a lot of visitors (since WordPress cron jobs rely on visits to work).
 */
add_action( 'admin_head', 'be_mu_auto_refresh_admin_page' );

/**
 * Adds more cron schedule intervals - one for every 15 seconds and one for every 9 seconds
 * @param array $schedules
 * @return array
 */
function be_mu_add_schedule_intervals( $schedules ) {
    $schedules['be_mu_every_15_sec'] = array(
        'interval' => 15,
        'display' => 'Every 15 seconds (Beyond Multisite)',
    );
    $schedules['be_mu_every_9_sec'] = array(
        'interval' => 9,
        'display' => 'Every 9 seconds (Beyond Multisite)',
    );
    return $schedules;
}

/**
 * If a certain variable is added to the URL of an admin page and the user can manage the network, the page will start auto-refreshing.
 * This speeds up cron jobs for sites that do not have a lot of visitors (since WordPress cron jobs rely on visits to work).
 */
function be_mu_auto_refresh_admin_page() {
    if ( isset( $_GET['be-mu-auto-refresh'] ) && current_user_can( 'manage_network' ) ) {
        echo '<meta http-equiv="refresh" content="15">';
    }
}

/**
 * Checks if the provided variable contains only comma separated numbers
 * @param string $string
 * @return int
 */
function be_mu_is_comma_separated_numbers( $string ) {
    return preg_match( '/^\d+(?:,\d+)*$/', $string );
}

/**
 * Checks if the provided variable contains only digits and English letters
 * @param string $string
 * @return bool
 */
function be_mu_is_digits_and_letters_only( $string ) {
    if ( ! preg_match( '/[^A-Za-z0-9]/', $string ) ) {
        return true;
    }
    return false;
}

/**
 * Removes all spaces, tabs, new lines from a string
 * @param string $string
 * @return string
 */
function be_mu_strip_whitespace( $string ) {
    return preg_replace( '/\s+/', '', $string );
}

/**
 * Removes everything from a string except digits and English letters
 * @param string $string
 * @return string
 */
function be_mu_strip_all_but_digits_and_letters( $string ) {
    return preg_replace( '/[^A-Za-z0-9]/', '', $string );
}

/**
 * Converts unix time to time and date in correct format and timezone (chosen in the general settings in wordpress)
 * @param int $unixtime
 * @return string
 */
function be_mu_unixtime_to_wp_datetime( $unixtime ) {
    return get_date_from_gmt( date( 'Y-m-d H:i:s', $unixtime ), get_option( 'date_format' ) . " " . get_option( 'time_format' ) );
}

/**
 * Converts unix time to time in correct format and timezone (chosen in the general settings in wordpress)
 * @param int $unixtime
 * @return string
 */
function be_mu_unixtime_to_wp_time( $unixtime ) {
    return get_date_from_gmt( date( 'Y-m-d H:i:s', $unixtime ), get_option( 'time_format' ) );
}

/**
 * Converts unix time to date in correct format and timezone (chosen in the general settings in wordpress)
 * @param int $unixtime
 * @return string
 */
function be_mu_unixtime_to_wp_date( $unixtime ) {
    return get_date_from_gmt( date( 'Y-m-d H:i:s', $unixtime ), get_option( 'date_format' ) );
}

/**
 * I use this function to debug the plugin sometimes (to see the value of some variable), it is not used in the actual plugin
 * @param string $variable_name
 * @param mixed $variable_name
 */
function be_mu_log( $variable_name, $variable_data ) {
    $file_path = be_mu_plugin_dir_path() . 'be_mu_debug_log.txt';
    if ( ! file_exists( $file_path ) || filesize( $file_path ) < 10000000 ) {
        file_put_contents( $file_path, $variable_name . ':' . $variable_data . ";\r\n", FILE_APPEND | LOCK_EX );
    }
}

/**
 * Returns an url of an image in the images folder based on a given file name
 * @param string $filename
 * @param string
 */
function be_mu_img_url( $filename ) {
    return be_mu_plugin_dir_url() . 'images/' . $filename;
}

// Reloads the settings page via javascript and show a 'Done' message at the end
function be_mu_reload_settings_page( $show_module = 'none' ) {
    if ( $show_module != 'none' ) {
        $module_in_URL = '&module=' . $show_module;
    } else {
        $module_in_URL = '';
    }
    echo '<br /><br /><br /><br /><center><b>' . esc_html__( 'Loading...', 'beyond-multisite' ) . '</b></center>'
        . '<script>window.location.href="' . esc_js( esc_url( network_admin_url( 'admin.php?page=beyond-multisite' ) ) ) . $module_in_URL . '&done";</script>';
}

/**
 * Returns the id of the main site of the network (which is usually 1)
 * @return int
 */
function be_mu_get_main_site_id() {
    if ( function_exists( 'get_main_site_id' ) ) {
        return get_main_site_id();
    } else {
        global $current_site;
        return intval( $current_site->blog_id );
    }
}

/**
 * Gets from the database and returns an array of arrays of task data based on the task id, the page number of the results the user is viewing, the number of
 * rows that need to be shown on a page, the database table and the names of the database table columns.
 * There is also a $to_skip argument that if is "calculate" the amount of data points to skip will be calculated and otherwise we provide it.
 * The function is made to read multiple rows of strings with data points separated by a separator and to skip a certain amount based on the selected results page
 * It also deletes old task data from the database
 * @param string $task_id
 * @param mixed $to_skip
 * @param int $page_number
 * @param int $per_page
 * @param string $db_table_without_prefix
 * @param array $data_columns
 * @param string $separator
 * @return array
 */
function be_mu_get_task_data( $task_id, $to_skip, $page_number, $per_page, $db_table_without_prefix, $data_columns, $separator = ',' ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . $db_table_without_prefix;

    // The unix time before 24 hours
    $before_one_day = time() - ( 24 * 3600 );

    // We delete task data older than 24 hours (just to keep the database table small)
    $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $db_table . ' WHERE unix_time_added < %d', $before_one_day ) );

    // In this segment we generate a string with the column names that we will use in the select query below
    $columns_count = count( $data_columns );
    if ( $columns_count > 1 ) {
        $columns_string = '';
        foreach( $data_columns as $key => $column ) {
            if ( $key != ( $columns_count - 1 ) ) {
                $columns_string .= $column . ', ';
            } else {
                $columns_string .= $column;
            }
        }
    } else {
        $columns_string = $data_columns[0];
    }

    // We get data from the database about the selected task id
    $results_multi_array = $wpdb->get_results( $wpdb->prepare( 'SELECT ' . $columns_string . ' FROM ' . $db_table
        . ' WHERE task_id = %s ORDER BY row_id ASC', $task_id ), ARRAY_A );

    // If there is no data for this task id we return fasle
    if ( empty( $results_multi_array ) ) {
        return false;
    }

    // We calculate how many data points we need to skip to get to the selecetd page number
    if ( 'calculate' == $to_skip ) {
        $to_skip = ( $page_number - 1 ) * $per_page;
    }
    /**
     * We set two vars to 0, first one tells us how many data points we have skipped so far while going through the data for the current task id in the database
     * and the second one tells us how many data points we alredy added to the results array (we need to add no more than the $per_page var).
     */
    $skipped_so_far = $we_got_so_far = 0;

    // This is the array of arrays we will return that will contain the data, we add arrays for each data column
    $data_points_to_return = Array();
    for ( $i = 0; $i < $columns_count; $i++ ) {
        $data_points_to_return[ $data_columns[ $i ] ] = Array();
    }

    /**
     * We go through all the data from the database - it is separated in big chunks (each chunk is a string with data points separated by a separator).
     * There will be more than one chunk only if the network is huge and we have reached a limit for time or sites processed per request.
     */
    foreach( $results_multi_array as $results ) {

        // This is the array of the current chunk strings of data points for each data column
        $data_point_chunk_strings = Array();
        for ( $i = 0; $i < $columns_count; $i++ ) {
            $data_point_chunk_strings[ $i ] = $results[ $data_columns[ $i ] ];
        }

        // The array of counts of data points in strings (they are the same as the number of separators, so we count them) for each data column
        $data_points_in_current_chunk = Array();
        for ( $i = 0; $i < $columns_count; $i++ ) {
            $data_points_in_current_chunk[ $i ] = substr_count( $data_point_chunk_strings[ $i ], $separator );

            // Remove the last separator so explode works correctly later below
            $data_point_chunk_strings[ $i ] = substr_replace( $data_point_chunk_strings[ $i ], '', - strlen( $separator ) );
        }

        /**
         * If we need to skip more data points than there are in the current chunk plus the amount we skipped so far, we will skip this whole chunk
         * and we will add the skipped count to $skipped_so_far.
         */
        if ( $to_skip > ( $data_points_in_current_chunk[0] + $skipped_so_far ) ) {
            $skipped_so_far += $data_points_in_current_chunk[0];
            continue;

        /**
         * If we need to skip less data points than (or equal to) there are in the current chunk plus the amount we skipped so far,
         * then we will use data from the current chunk.
         */
        } else {

            // We have reached a chunk from which we will get data (we need $per_page amount of data points in total at most or less if we run out - last page)

            // If the amount of data points we got so far is less than the amount we need, we proceed with getting the data, otherwise we break the foreach
            if ( $we_got_so_far < $per_page) {

                // We make an array of arrays with all the data points in the current chunk for each data column
                $current_data_points_chunk = Array();
                for ( $i = 0; $i < $columns_count; $i++ ) {
                    $current_data_points_chunk[ $i ] = explode( $separator, $data_point_chunk_strings[ $i ] );
                }

                // We go through all the data points in the chunk for each data column
                for ( $j = 0; $j < count( $current_data_points_chunk[0] ); $j++ ) {

                    // An array with the current data point for each data column
                    $current_data_point = Array();
                    for ( $i = 0; $i < $columns_count; $i++ ) {
                        $current_data_point[ $i ] = $current_data_points_chunk[ $i ][ $j ];
                    }

                    // If we haven't skipped enough data points we skip one by one until we skip enough
                    if ( $skipped_so_far < $to_skip ) {
                        $skipped_so_far++;
                        continue;
                    } else {

                        // At this point we have skipped enough data points and it is time to get some data (to put some data points in the array we will return)

                        // If so far we haven't got enough data points in the array we will return, we add them one by one untill we do
                        if ( $we_got_so_far < $per_page ) {
                            for ( $i = 0; $i < $columns_count; $i++ ) {
                                $data_points_to_return[ $data_columns[ $i ] ][] = $current_data_point[ $i ];
                            }
                            $we_got_so_far++;

                        // If we have enough data points we break the foreach
                        } else {
                            break;
                        }
                    }
                }
            } else {
                break;
            }
        }
    }

    // We return the array with arrays with the data points for each data column
    return $data_points_to_return;
}

/**
 * Gets from the database and returns an array of arrays of task data based on the task id, the page number of the results the user is viewing, the number of
 * rows that need to be shown on a page, the database table, the names of the database table columns, and information for the "where" part of the query.
 * There is also a $to_skip argument that if is "calculate" the amount of data points to skip will be calculated and otherwise we provide it.
 * The function is made to read rows with one data point per row and with a condition for one table column.
 * @param string $task_id
 * @param mixed $to_skip
 * @param int $page_number
 * @param int $per_page
 * @param string $db_table_without_prefix
 * @param array $data_columns
 * @param string $where_column
 * @param string $where_type
 * @param mixed $where_value
 * @return array
 */
function be_mu_get_specific_task_data( $task_id, $to_skip, $page_number, $per_page, $db_table_without_prefix, $data_columns, $where_column, $where_type,
    $where_value ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . $db_table_without_prefix;

    // In this segment we generate a string with the column names that we will use in the select query below
    $columns_count = count( $data_columns );
    if ( $columns_count > 1 ) {
        $columns_string = '';
        foreach( $data_columns as $key => $column ) {
            if ( $key != ( $columns_count - 1 ) ) {
                $columns_string .= $column . ', ';
            } else {
                $columns_string .= $column;
            }
        }
    } else {
        $columns_string = $data_columns[0];
    }

    // We calculate how many data points we need to skip to get to the selecetd page number
    if ( 'calculate' == $to_skip ) {
        $to_skip = ( $page_number - 1 ) * $per_page;
    }

    // We get data from the database about the selected task id
    $results_multi_array = $wpdb->get_results( $wpdb->prepare( 'SELECT ' . $columns_string . ' FROM ' . $db_table
        . ' WHERE task_id = %s AND ' . $where_column . ' = ' . $where_type . ' ORDER BY row_id ASC LIMIT ' . intval( $to_skip ) . ',' . intval( $per_page ),
        $task_id, $where_value ), ARRAY_A );

    // We get the total count of the results
    $total_results_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $db_table
        . ' WHERE task_id = %s AND ' . $where_column . ' = ' . $where_type . ' ORDER BY row_id ASC',
        $task_id, $where_value ) );

    // If there is no data for this task id we return fasle
    if ( empty( $results_multi_array ) ) {
        return false;
    }

    // This is the array of arrays we will return that will contain the data, we add arrays for each data column
    $data_points_to_return = Array();
    for ( $i = 0; $i < $columns_count; $i++ ) {
        $data_points_to_return[ $data_columns[ $i ] ] = Array();
    }

    // We add the total count of the results to the array to return
    $data_points_to_return['total_results_count'] = $total_results_count;

    // We go through all the data from the database for each column name and add the data to array to return
    for ( $i = 0; $i < $columns_count; $i++ ) {
        foreach( $results_multi_array as $results ) {
            $data_points_to_return[ $data_columns[ $i ] ][] = $results[ $data_columns[ $i ] ];
        }
    }

    // We return the array with arrays with the data points for each data column
    return $data_points_to_return;
}

/**
 * Adds a new element in an array on the exact place we want (if possible).
 * We use this when adding a custom column or an action link on some places in the admin panel.
 * @param array $original_array
 * @param string $add_element_key
 * @param mixed $add_element_value
 * @param string $add_before_key
 * @return array
 */
function be_mu_add_element_to_array( $original_array, $add_element_key, $add_element_value, $add_before_key ) {

    // This variable shows if we were able to add the element where we wanted
    $is_added = 0;

    // This will be the new array, it will include our element placed where we want
    $new_array = array();

    // We go through all the current elements and we add our new element on the place we want
    foreach( $original_array as $key => $value ) {

        // We put the element before the key we want
        if ( $key == $add_before_key ) {
      	    $new_array[ $add_element_key ] = $add_element_value;

            // We were able to add the element where we wanted so no need to add it again later
            $is_added = 1;
        }

        // All the normal elements remain and are added to the new array we made
        $new_array[ $key ] = $value;
    }

    // If we failed to add the element earlier (because the key we tried to add it in front of is gone) we add it now to the end
    if ( 0 == $is_added ) {
        $new_array[ $add_element_key ] = $add_element_value;
    }

    // We return the new array we made
    return $new_array;
}

/**
 * Returns the email address of the network admin
 * @return mixed
 */
function be_mu_get_network_admin_email() {
    $email = get_site_option( 'admin_email' );

    //if the value in the newtork options is not valid we get it from the current user
    if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
        $current_user = wp_get_current_user();
        $email = $current_user->user_email;
    }
    return $email;
}

/**
 * Returns a UTC timezone offset string (for example: UTC+2) based on a offset number (for example: 2)
 * @param int $offset_number
 * @return string
 */
function be_mu_gmt_offset_to_utc_string( $offset_number ) {
    if ( $offset_number >= 0 ) {
        $offset_string = '+' . $offset_number;
    } else {
        $offset_string = (string) $offset_number;
    }
    $offset_string = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $offset_string );
    $offset_string = 'UTC' . $offset_string;
    return $offset_string;
}

/**
 * Returs the wordpress time zone setting
 * @return string
 */
function be_mu_get_wp_time_zone() {
    return be_mu_gmt_offset_to_utc_string( get_option( 'gmt_offset' ) );
}

// Shows a fading message saying "Done"
function be_mu_echo_fading_div_message() {
    echo '<div id="be-mu-message-id" class="be-mu-message be-mu-success">'
        . esc_html__( 'Done', 'beyond-multisite' )
        . '<script type="text/javascript"> '
        . 'jQuery( document ).ready( function() { setTimeout( beyondMultisiteStartFadingMessage, 3500 ); } ); '
        . '</script>'
        . '</div>';
}

/**
 * Outputs the header part of super admin pages of the plugin
 * @param string $page_name
 * @param string $status_post_request
 */
function be_mu_header_super_admin_page( $page_name, $status_post_request = '' ) {
    ?>
        <div class="be-mu-logo-div be-mu-white-box">
            <b>
                <img class="be-mu-logo-img" src="<?php echo esc_url( be_mu_img_url( 'beyond-multisite-logo.png' ) ); ?>" />
                <?php esc_html_e( 'Beyond Multisite', 'beyond-multisite' ); ?>
                &nbsp;&nbsp; <span>&raquo; <?php echo esc_html( $page_name ); ?></span>
            </b>
            <?php

                // Based on the given variable it will display a message if needed
                if ( '' != $status_post_request ) {
                    be_mu_handle_post_status( $status_post_request );
                }

            ?>
        </div>
    <?php
}

/**
 * Sends an email and returns true on success or false on failure
 * @param string $from_email
 * @param string $from_name
 * @param string $to_email
 * @param string $subject
 * @param string $message
 * @return bool
 */
function be_mu_send_email( $from_email, $from_name, $to_email, $subject, $message ) {
    require_once( ABSPATH . 'wp-includes/pluggable.php' );
                   
    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    if ( wp_mail( $to_email, $subject, $message, $headers ) ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Generates a random string. We can choose the length and also which characters to exclude.
 * @param int $length
 * @param string $exclude
 * @return string
 */
function be_mu_random_string( $length, $exclude = '' ) {

    // Available characters to choose from
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';

    // Exclude some characters (if any)
    $characters = str_replace( str_split( $exclude ), '', $characters );

    // The count of the availavle characters
    $available_character_length = strlen( $characters );

    // Start with an empty string
    $random_string = '';

    // Add a random character from the available ones
    // And do this $length times
    for ( $i = 0; $i < $length; $i++ ) {
        $random_string .= $characters[ rand( 0, $available_character_length - 1 ) ];
    }

    // Return the string we made
    return $random_string;
}

/**
 * If the user does not have a user token assigned, we assign a new random one and add it to the user global data.
 * @param int $user_id
 */
function be_mu_assign_user_token_if_not_exist( $user_id ) {
    if ( get_user_option( 'be-mu-user-token', $user_id ) === false ) {
        $user_token = be_mu_random_string( 25 );
        update_user_option( $user_id, 'be-mu-user-token', $user_token, true );
    }
}

/**
 * Replaces the first occurrence of a string inside a string with another string.
 * @param string $find
 * @param string $replace_with
 * @param string $in_string
 * @return string
 */
function be_mu_replace_first( $find, $replace_with, $in_string ) {
    $position = strpos( $in_string, $find );
    if ( false !== $position ) {
        return substr_replace( $in_string, $replace_with, $position, strlen( $find ) );
    }
}

/**
 * Returns the uplaod folder path (refreshing the cache just in case) of a site by its ID.
 * @param int $site_id
 * @return string
 */
function be_mu_get_site_upload_folder( $site_id ) {
    switch_to_blog( $site_id );
    $upload_folder_array = wp_upload_dir( null, true, true );
    restore_current_blog();
    return $upload_folder_array['basedir'];
}

/**
 * Returns the database name(s) that the multisite uses, made into a string to use in a mysql query inside an IN function. Supports the Multi-DB dropin.
 * @return string
 */
function be_mu_get_database_names_string_for_mysql_in() {

    if ( ! function_exists( 'get_dropins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $dropins = get_dropins();
    $database_names = Array();

    // Check if the Multi-DB dropin is present and get all database names if yes
    if ( is_array( $dropins ) && isset( $dropins['db.php']['Name'] ) && 'Multi-DB' == $dropins['db.php']['Name'] ) {
        global $db_servers;
        foreach ( $db_servers as $db_server ) {
            $database_names[] = "'" . $db_server[0]['name'] . "'";
        }

    // If the Multi-DB dropin is not present, we assume it is only one database and get it
    } else {
        global $wpdb;
        $database_names[] = "'" . $wpdb->dbname . "'";
    }

    // We return a comma-separated list of database names in single quotes
    return implode( ',', $database_names );
}

// Creates the database tables to store the log data for all modules that want to use it
function be_mu_logs_db_table() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // This is the query that will create the database table (if it does not exist)
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_logs ( '
        . 'row_id bigint( 20 ) NOT NULL AUTO_INCREMENT, '
        . 'type varchar( 50 ) DEFAULT NULL, '
        . 'task_id varchar( 10 ) DEFAULT NULL, '
        . 'log longtext DEFAULT NULL, '
        . 'unix_time_added int( 11 ) NOT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';

    // Execute the query
    dbDelta( $sql );
}

/**
 * Adds log data about some task to the database
 * @param string $type
 * @param string $task_id
 * @param string $log
 */
function be_mu_add_log_data( $type, $task_id, $log ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table name
    $db_table = $main_blog_prefix . 'be_mu_logs';

    // Insert the log data to the database
    $wpdb->insert(
    	$db_table,
    	array(
    		'type' => $type,
    		'task_id' => $task_id,
    		'log' => $log,
    		'unix_time_added' => time(),
    	),
    	array(
    		'%s',
    		'%s',
    		'%s',
    		'%d',
    	)
    );
}

/**
 * Gets log data about some task from the database
 * @param string $task_id
 * @return mixed
 */
function be_mu_get_log_data( $task_id ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table name
    $db_table = $main_blog_prefix . 'be_mu_logs';

    // We get the log data in an array
    $results_array = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $db_table . " WHERE task_id = %s", $task_id ), ARRAY_A );

    // If there was data found, we return the array
    if ( ! empty( $results_array ) ) {
        return $results_array;
    }

    // Nothing was found, we return false
    return false;
}

/**
 * Replaces only the last occurrence of a string inside another string
 * @param string $search
 * @param string $replace
 * @param string $subject
 * @return string
 */
function be_mu_str_last_replace( $search, $replace, $subject ) {
    $pos = strrpos( $subject, $search );
    if( false !== $pos ) {
        $subject = substr_replace( $subject, $replace, $pos, strlen( $search ) );
    }
    return $subject;
}

/**
 * An empty function to use for admin pages that will redirect to somewhere.
 * @return bool
 */
function be_mu_empty_function() {
    return true;
}

/**
 * Checks if the statement put together with the two parameters is true or false for the WordPress version.
 * @param string $operator
 * @param string $version
 * @return bool
 */
function be_mu_wordpress_version_is( $operator, $version ) {
    global $wp_version;
    return version_compare( $wp_version, $version, $operator );
}

/**
 * Checks if the the server has the cURL extension is enabled.
 * @return bool
 */
function be_mu_is_curl_enabled() {
    return function_exists( 'curl_version' );
}

/**
 * Returns the size of the provided string along with the unit at the end (B, KB, or MB)
 * @return bool
 */
function be_mu_get_string_size( $string ) {
    $bytes = mb_strlen( $string, '8bit' );
    if ( $bytes > 999999 ) {
        return round( $bytes / 1000000, 1 ) . ' MB';
    }
    if ( $bytes > 999 ) {
        return round( $bytes / 1000 ) . ' KB';
    }
    return intval( $bytes ) . ' B';
}

/**
 * Gets the IP address of the visitor (CloudFlare real IP is supported).
 * @return mixed
 */
function be_mu_get_visitor_ip() {
    $method = be_mu_get_setting( 'be-mu-ban-detect-ip-method', 'Auto' );
    return be_mu_get_visitor_ip_by_method( $method );
}

/**
 * Gets the IP address of the visitor by selected method.
 * @param string $method
 * @return mixed
 */
function be_mu_get_visitor_ip_by_method( $method ) {

    if ( 'Auto' === $method ) {
        if ( be_mu_is_good_server_ip_key( 'HTTP_CF_CONNECTING_IP' ) ) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if ( be_mu_is_good_server_ip_key( 'REMOTE_ADDR' ) ) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return false;
    } elseif ( 'HTTP_CF_CONNECTING_IP' === $method ) {
        if ( be_mu_is_good_server_ip_key( 'HTTP_CF_CONNECTING_IP' ) ) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        return false;
    } elseif ( 'REMOTE_ADDR' === $method ) {
        if ( be_mu_is_good_server_ip_key( 'REMOTE_ADDR' ) ) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return false;
    } elseif ( 'HTTP_X_FORWARDED_FOR' === $method ) {

        $trusted_proxies = Array();

        // We get the trusted proxies setting
        $trusted_proxies_setting = be_mu_get_setting( 'be-mu-ban-trusted-proxies', '' );
        if ( ! empty( $trusted_proxies_setting ) ) {
            $trusted_proxies = explode( ',', $trusted_proxies_setting );
            $trusted_proxies = array_map( 'trim', $trusted_proxies );

            // If this is not a trusted proxy, and the REMOTE_ADDR is valid, we will use the REMOTE_ADDR value as IP
            if ( be_mu_is_good_server_ip_key( 'REMOTE_ADDR' ) && ! in_array( $_SERVER['REMOTE_ADDR'], $trusted_proxies ) ) {
                return $_SERVER['REMOTE_ADDR'];
            }
        }

        // If we are here, the proxy is trusted (or REMOTE_ADDR is invalid). So we use HTTP_X_FORWARDED_FOR. It can be one or many IPs.
        if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

            /*
            * We will remove the trusted proxies from the list of IPs. Then the IP on the right is the IP we use.
            * This will work good even if it s only one IP and there is no ','
            */
            $ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $ips = array_map( 'trim', $ips );
            $ips = array_diff( $ips, $trusted_proxies );
            if ( empty( $ips ) ) {
                return false;
            }
            $ip = array_pop( $ips );
            if ( be_mu_is_valid_ip( $ip ) ) {
                return $ip;
            }
            return false;
        }
        return false;

    } elseif ( 'HTTP_CLIENT_IP' === $method ) {
        if ( be_mu_is_good_server_ip_key( 'HTTP_CLIENT_IP' ) ) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        return false;
    } elseif ( 'HTTP_X_REAL_IP' === $method ) {
        if ( be_mu_is_good_server_ip_key( 'HTTP_X_REAL_IP' ) ) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        return false;
    }
    return false;
}

/**
 * Checks if the provided array key exists in the $_SERVER array and is set and its value is a valid IP address.
 * @param string $key
 * @return bool
 */
function be_mu_is_good_server_ip_key( $key ) {
    if ( array_key_exists( $key, $_SERVER ) && isset( $_SERVER[ $key ] ) && be_mu_is_valid_ip( $_SERVER[ $key ] ) ) {
        return true;
    }
    return false;
}

/**
 * Checks if the provided IP address is valid. Supports IPv4 and IPv6.
 * @param string $ip
 * @return bool
 */
function be_mu_is_valid_ip( $ip ) {
    if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
        return false;
    }
    return true;
}

/**
 * Checks if the provided string is a comma-separated list of valid IP addresses. Supports IPv4 and IPv6.
 * @param string $string
 * @return bool
 */
function be_mu_is_comma_separated_ips( $string ) {
    if ( strpos( $string, ',' ) !== false ) {
        $ips = explode( ',', $string );
        $ips = array_map( 'trim', $ips );
        foreach ( $ips as $ip ) {
            if ( ! be_mu_is_valid_ip( $ip ) ) {
                return false;
            }
        }
        return true;
    }
    return be_mu_is_valid_ip( $string );
}

/**
 * Checks if a user exists based on a used id
 * @param int $user_id
 * @return bool
 */
function be_mu_user_exists( $user_id ) {
    if ( false === get_userdata( $user_id ) ) {
        return false;
    }
    return true;
}

/**
 * Gets all the blogs in which a given user has a given role and returns their IDs in an array.
 * @param int $user_id
 * @param string $role_name
 * @return array
 */
function be_mu_get_user_blogs_by_role( $user_id, $role_name ) {

    // We make the role slug from the role name. Since they are all just one word, we can simply turn them to lower case.
    $role_slug = strtolower( $role_name );

    // This array will hold the blog ids in which a given user has a given role
    $blog_ids = Array();

    // We get all blogs in which the user has any role. Second argument is true to get even spammed, deleted, and archived.
    $blogs = get_blogs_of_user( $user_id, true );

    // We go through all the blogs
    foreach ( $blogs as $blog_id => $blog ) {

        // If the chosen role setting is "Any role" we add the blog id to the array to return
        if ( 'Any role' === $role_name ) {
            $blog_ids[] = $blog_id;

        // If the chosen role setting is not "Any" we need to check the exact roles of the user in the blog
        } else {

            // Get the user object for the user for the current blog
            $user = new WP_User( $user_id, '', $blog_id );

            // If the required role is in the user roles for this blog, we add the blog id to the array to return
            if ( in_array( $role_slug, $user->roles ) ) {
                $blog_ids[] = $blog_id;
            }
        }
    }

    // We return the blog ids
    return $blog_ids;
}

/**
 * Gets the default email address from which emails are sent by WordPress
 * @return mixed
 */
function be_mu_get_wordpress_email() {
    $sitename = strtolower( $_SERVER['SERVER_NAME'] );
    if ( substr( $sitename, 0, 4 ) == 'www.' ) {
        $sitename = substr( $sitename, 4 );
    }
    $email = 'wordpress@' . $sitename;
    if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
        return $email;
    } else {
        return false;
    }
}

/**
 * Extracts the URLs from a string. It is a little different than the built in wp_extract_urls because it does allow semicolons in the URL and
 * also it does not decode html entities. This way when I want to strip an URL that contains &amp; it actually works properly.
 * @param string $content
 * @return array
 */
function be_mu_extract_unique_urls( $content ) {
    preg_match_all(
        "#([\"']?)("
            . "(?:([\w-]+:)?//?)"
            . "[^\s()<>]+"
            . "[.]"
            . "(?:"
                . "\([\w\d]+\)|"
                . "(?:"
                    . "[^`!()\[\]{}:'\".,<>«»“”‘’\s]|"
                    . "(?:[:]\d+)?/?"
                . ")+"
            . ")"
        . ")\\1#",
        $content,
        $urls
    );
    $urls = array_unique( $urls[2] );
    return array_values( $urls );
}

/**
 * Returns true if the string contains a URL pointing to an external site
 * @param string $content
 * @return bool
 */
function be_mu_contains_external_url( $content ) {

    // We extract all URLs from the string
    $urls = be_mu_extract_unique_urls( $content );

    // If there are any URLs
    if ( count( $urls ) > 0 ) {

        // The URL of the site we are currently in
        $current_site_url = get_site_url( get_current_blog_id() );

        // We parse the URL of the current site
        $parsed_current_url = parse_url( $current_site_url );

        // The host name of the current site
        $current_host = $parsed_current_url['host'];

        // We go through the URLs we found in the string
        foreach ( $urls as $url ) {

            // We parse the current URL in the loop
            $parsed_url = parse_url( $url );

            // If the host name of the current URL in the loop is different from the host name of the current site, we return true
            if ( $parsed_url['host'] != $current_host ) {
                return true;
            }
        }
    }

    // If we got to this point, the string does not contain external URLs, so we return false
    return false;
}

/**
 * Strips from a string URLs pointing to an external site
 * @param string $content
 * @return string
 */
function be_mu_strip_external_urls( $content ) {

    // We extract all URLs from the string
    $urls = be_mu_extract_unique_urls( $content );

    // If there are any URLs
    if ( count( $urls ) > 0 ) {

        // The URL of the site we are currently in
        $current_site_url = get_site_url( get_current_blog_id() );

        // We parse the URL of the current site
        $parsed_current_url = parse_url( $current_site_url );

        // The host name of the current site
        $current_host = $parsed_current_url['host'];

        // We go through the URLs we found in the string
        foreach ( $urls as $url ) {

            // We parse the current URL in the loop
            $parsed_url = parse_url( $url );

            // If the host name of the current URL in the loop is different from the host name of the current site, we replace the URL with an empty string
            if ( $parsed_url['host'] != $current_host ) {
                $content = str_replace( $url, '', $content );
            }
        }
    }

    // We return the string with stripped external URLs now (if there were any)
    return $content;
}

/**
 * Strips all characters from a string before a given substring
 * @param string $string
 * @param string $substring
 * @return string
 */
function be_mu_strip_before_substring( $string, $substring ) {
    $index = strpos( $string, $substring );
    if ( $index !== false ) {
        return substr( $string, $index );
    }
    return $string;
}

/**
 * Strips all characters from a string except digits and dots
 * @param string $content
 * @return string
 */
function be_mu_sanitize_version( $string ) {
    return preg_replace( "/[^0-9.]/", "", $string );
}

/**
 * Checks if the provided variable is a whole positive number. Allows a string number too!
 * @param mixed $number
 * @return bool
 */
function be_mu_is_whole_positive_number( $number ) {
    if ( is_numeric( $number ) && ! preg_match( '/[^0-9]/', $number ) && intval( $number ) == $number && $number > 0
        && substr( strval( $number ), 0, 1 ) !== '0' ) {
        return true;
    }
    return false;
}

/**
 * If the provided string is a http url, it returns a https version. And if it is https version, it returns http version.
 * @param string $url
 * @return string
 */
function be_mu_alternative_http_url( $url ) {
    if ( strpos( $url, "http:" ) === 0 ) {
        return substr_replace( $url, "https:", 0, strlen( "http:" ) );
    }
    if ( strpos( $url, "HTTP:" ) === 0 ) {
        return substr_replace( $url, "HTTPS:", 0, strlen( "HTTP:" ) );
    }
    if ( strpos( $url, "https:" ) === 0 ) {
        return substr_replace( $url, "http:", 0, strlen( "https:" ) );
    }
    if ( strpos( $url, "HTTPS:" ) === 0 ) {
        return substr_replace( $url, "HTTP:", 0, strlen( "HTTPS:" ) );
    }
    return $url;
}

/**
 * Checks if the string contains capital letters
 * @param string $string
 * @return bool
 */
function be_mu_contains_capital_letters( $string ) {
    if ( preg_match( '/[A-Z]/', $string ) === 1 ) {
        return true;
    }
    return false;
}

/**
 * Checks if the user has only the selected role (by slug) in any sites, and no other roles anywhere
 * @param int $user_id
 * @param string $role_slug
 * @return bool
 */
function be_mu_has_only_role( $user_id, $role_slug ) {
    global $wpdb;
    $sites = get_blogs_of_user( $user_id, true );
    if ( empty( $sites ) ) {
        return false;
    } else {
        foreach ( $sites as $site ) {
            $user_roles = get_user_meta( $user_id, $wpdb->get_blog_prefix( $site->userblog_id ) . 'capabilities', true );
            if ( ! is_array( $user_roles ) || ! array_key_exists( $role_slug, $user_roles ) || count( $user_roles ) > 1 ) {
                return false;
            }
        }
        return true;
    }
}

/**
 * Checks if the user has only the selected roles (by slug) in any sites, and no other roles anywhere
 * @param int $user_id
 * @param array $role_slugs_array
 * @return bool
 */
function be_mu_has_only_roles( $user_id, $role_slugs_array ) {
    global $wpdb;
    $sites = get_blogs_of_user( $user_id, true );
    if ( empty( $sites ) || ! is_array( $role_slugs_array ) || empty( $role_slugs_array ) ) {
        return false;
    } else {
        foreach ( $sites as $site ) {
            $user_roles = get_user_meta( $user_id, $wpdb->get_blog_prefix( $site->userblog_id ) . 'capabilities', true );
            if ( ! is_array( $user_roles ) || count( $user_roles ) > count( $role_slugs_array ) ) {
                return false;
            }
            foreach ( $user_roles as $user_role_slug => $value ) {
                if ( ! in_array( $user_role_slug, $role_slugs_array ) ) {
                    return false;
                }
            }
        }
        return true;
    }
}

/**
 * Strips all non-digit characters from a string
 * @param string $string
 * @return $string
 */
function be_mu_strip_non_digit( $string ) {
    return preg_replace( '/\D/', '', $string );
}
