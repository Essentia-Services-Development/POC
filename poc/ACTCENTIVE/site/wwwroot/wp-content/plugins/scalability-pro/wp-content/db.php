<?php
/**
 * Plugin Name: Scalability Pro Database Dropin
 * Description: Database drop-in for Scalability Pro, the performance toolset for WordPress
 * Version:     1.0
 * Plugin URI:  https://superspeedyplugins.com/
 * Author:      Dave Hilditch
 * Author URI:  https://superspeedyplugins.com/
 *
 * *********************************************************************
 *
 * Ensure this file is symlinked to your wp-content directory to provide
 * improved database query information in Scalability Pro's output.
 *
 * @see https://superspeedyplugins.com
 *
 * *********************************************************************
 *
 * @package scalability-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DB_USER' ) ) {
	return;
}
require_once 'profiling-queries.php';

class SPRO_WPDB_Extension extends wpdb {
    
    public function query($query) {
        global $SPRO_GLOBALS;
        global $start_time;
        $start_time = microtime(true);
        $result = parent::query($query);
        sp_save_long_queries();
        unset ($start_time);
        return $result;
    }
}
$wpdb = new SPRO_WPDB_Extension(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

