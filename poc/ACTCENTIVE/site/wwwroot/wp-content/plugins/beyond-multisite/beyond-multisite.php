<?php

/*
Plugin Name: Beyond Multisite
Description: Beyond Multisite helps super administrators to better control, protect, and clean their WordPress network.
Author: Nikolay Nikolov
Author URI: https://nikolaydev.com/
Plugin URI: https://codecanyon.net/item/beyond-multisite-utilities-for-wordpress-network-admins/19633352
Update URI: https://codecanyon.net/item/beyond-multisite-utilities-for-wordpress-network-admins/19633352
Text Domain: beyond-multisite
Domain Path: /languages
Version: 1.16.0
Network: True
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// A global constant for the current version of the plugin
define( "BEYOND_MULTISITE_VERSION", "1.16.0" );

// Include functions related to the plugin settings
require_once( 'includes/settings.php' );

// Include some helper functions usually used in more than one module or in the main plugin file
require_once( 'includes/helpers.php' );

// Include functions and hooks related to each module
require_once( 'includes/captcha.php' );
require_once( 'includes/insert-html.php' );
require_once( 'includes/ban-users.php' );
require_once( 'includes/pending-users.php' );
require_once( 'includes/activated-in.php' );
require_once( 'includes/cleanup.php' );
require_once( 'includes/improvements.php' );
require_once( 'includes/plugin-control.php' );
require_once( 'includes/email-users.php' );
require_once( 'includes/copy-maker.php' );

// Runs on plugin activation
register_activation_hook( be_mu_plugin_dir_path() . 'beyond-multisite.php', 'be_mu_activate_plugin' );

// Runs on plugin deactivation
register_deactivation_hook( be_mu_plugin_dir_path() . 'beyond-multisite.php', 'be_mu_deactivate_plugin' );

// Adds a network admin menu element
add_action( 'network_admin_menu', 'be_mu_add_beyond_multisite_menu' );

// Loads the plugin's translated strings
add_action( 'init', 'be_mu_load_plugin_textdomain' );

// Adds new settings and tables to the database after a new version of the plugin has been uploaded (if needed)
add_action( 'init', 'be_mu_update_database' );

// Returns the url of the plugin folder. Do not move this function to a file in another folder.
function be_mu_plugin_dir_url() {
    return plugin_dir_url( __FILE__ );
}

// Returns the path of the plugin folder. Do not move this function to a file in another folder.
function be_mu_plugin_dir_path() {
    return plugin_dir_path( __FILE__ );
}

// Loads the plugin's translated strings
function be_mu_load_plugin_textdomain() {
    load_plugin_textdomain( 'beyond-multisite', FALSE, be_mu_plugin_dir_path() . '/languages/' );
}

// Runs on plugin activation
function be_mu_activate_plugin() {

    // We call the functions that create different database tables. New function calls are added to other functions, not here!
    be_mu_create_captcha_db_table();
    be_mu_create_ban_db_tables();
    be_mu_activated_in_db_table();
    be_mu_clean_db_tables();

    if ( function_exists( 'be_mu_email_db_tables' ) ) {
        be_mu_email_db_tables();
    }
    if ( function_exists( 'be_mu_logs_db_table' ) ) {
        be_mu_logs_db_table();
    }

    /**
     * We create default settings if they do not exist. These settings are the one from the 1.0.0 version of the plugin. New ones are added in
     * other functions related to database udpating, not here!
     */
    be_mu_make_settings( Array(
        'be-mu-captcha-status-module' => 'off',
        'be-mu-captcha-login' => 'off',
        'be-mu-captcha-comment-logged-in' => 'off',
        'be-mu-captcha-comment-logged-out' => 'off',
        'be-mu-captcha-lost-password' => 'off',
        'be-mu-captcha-reset-password' => 'off',
        'be-mu-captcha-user-signup' => 'off',
        'be-mu-captcha-blog-signup-logged-out' => 'on',
        'be-mu-captcha-blog-signup-logged-in' => 'off',
        'be-mu-captcha-height' => '80',
        'be-mu-captcha-characters' => '3',
        'be-mu-captcha-character-set' => 'Numbers',
        'be-mu-insert-status-module' => 'off',
        'be-mu-insert-head' => '',
        'be-mu-insert-footer' => '',
        'be-mu-insert-exclude-include-footer' => 'on all sites except',
        'be-mu-insert-exclude-include-head' => 'on all sites except',
        'be-mu-insert-site-ids-head' => '',
        'be-mu-insert-site-ids-footer' => '',
        'be-mu-ban-status-module' => 'off',
        'be-mu-ban-period' => 'Permanent',
        'be-mu-ban-ip-column' => 'on',
        'be-mu-ban-status-column' => 'on',
        'be-mu-pending-status-module' => 'on',
        'be-mu-improve-status-module' => 'on',
        'be-mu-improve-user-id-column' => 'on',
        'be-mu-improve-user-sites-dash-action' => 'on',
        'be-mu-improve-site-id-column' => 'on',
        'be-mu-improve-hide-plugin-meta' => 'on',
        'be-mu-improve-network-plugin-admin-menus' => 'on',
        'be-mu-improve-network-theme-admin-menus' => 'on',
        'be-mu-improve-password-change-email' => 'off',
        'be-mu-activated-in-status-module' => 'on',
        'be-mu-clean-status-module' => 'on',
        'be-mu-clean-email-speed' => '240 per hour',
        'be-mu-clean-from-email' => be_mu_get_wordpress_email(),
        'be-mu-clean-from-name' => get_site_option( 'site_name' ),
        'be-mu-clean-subject' => 'Site deletion notification',
        'be-mu-clean-message' => '<p>Hello.</p><p>One or more sites, where you are an administrator, are scheduled for deletion in'
            .' [deletion_after_days] days. Here is a list of the sites:</p>[user_sites]'
            .'<p>If you wish to cancel the deletion you can do it by going to the admin dashboard of each site and clicking the '
            .'cancellation button in the big red deletion message. If you do not see the message, then the deletion has been cancelled.</p>'
            .'<p>Regards.<br />[network_site_url]</p>',
        'be-mu-plugin-status-module' => 'on',
        'be-mu-plugin-import-hide' => 'off',
    ) );

    // Schedules an event (if not already scheduled) to run once an hour to unban expired bans
    if ( be_mu_get_setting( 'be-mu-ban-status-module' ) == 'on' ) {
        be_mu_add_cron_unban();
    }

    // If the setting to disable new plugins is on, we update the list of plugins ever installed
    if ( be_mu_get_setting( 'be-mu-plugin-network-disable-new-plugins' ) == 'on' ) {
        be_mu_plugin_create_or_update_plugins_ever_installed_list();
    }
}

// Adds new settings and tables to the database after a new version of the plugin has been uploaded (if needed)
function be_mu_update_database() {

    // The most recent version of the plugin that has ever been activated in this installation
    $plugin_version_in_database = be_mu_get_setting( 'be-mu-plugin-version' );

    // If we do not have data in the database about the version, we will check the file, since sometimes database data is incorrect or corrupted
    if ( $plugin_version_in_database === false ) {
        $file_path = be_mu_plugin_dir_path() . 'db-version.php';
        if ( file_exists( $file_path ) && is_readable( $file_path ) ) {
            $file_data = file_get_contents( $file_path );
            if ( false !== $file_data ) {
                $plugin_version_in_database = str_replace( "<?php // ", "", $file_data );
            }
        }
    }

    /**
     * If there is no data for the version in the database, it is either 1.0.0 or 1.0.1, so we just set it to 1.0.0.
     * In either case we need to start updating from 1.1.0.
     */
    if ( $plugin_version_in_database === false ) {
        $plugin_version_in_database = '1.0.0';
    }

    // If the version in the database does not match the version in this current file, we need to update
    if ( $plugin_version_in_database != BEYOND_MULTISITE_VERSION ) {

        // These are all the versions that require database update
        $version_updates = Array( '1.1.0', '1.2.0', '1.3.0', '1.4.0', '1.5.0', '1.8.0', '1.9.0', '1.11.0', '1.13.0', '1.15.0', '1.16.0' );

        /**
         * For each version we compare the one in the database and the current one from the array, and if the one in the database is lower
         * we run the update function for the current one from the array. This way if the database version is for example 1.1.0 we will update first to 1.2.0
         * and then to 1.3.0 and so on until the current most recent version is reached.
         * Now to add a new update all I need to do is add the version in the array and add thr changes to the be_mu_update_to_version function. If a new
         * version is released that does not need a database update, I must NOT add it to the array. Then only the version will be updated in the database
         * and no other changes will be made.
         */
        foreach ( $version_updates as $version_update ) {
            if ( version_compare( $plugin_version_in_database, $version_update, '<' ) ) {
                be_mu_update_to_version( $version_update );
            }
        }

        // Changes the database version to the current one
        be_mu_set_or_make_setting( 'be-mu-plugin-version', BEYOND_MULTISITE_VERSION );

        // We store the database version in a file too, since sometimes the database data could be incorrect due to various problems
        $file_path = be_mu_plugin_dir_path() . 'db-version.php';
        if ( ! file_exists( $file_path ) || filesize( $file_path ) < 1000000 ) {
            @file_put_contents( $file_path, "<?php // " . be_mu_sanitize_version( BEYOND_MULTISITE_VERSION ), LOCK_EX );
        }
    }
}

/**
 * Makes the required changes to update the database data to a specific plugin version.
 * @param string $version
 */
function be_mu_update_to_version( $version ) {

    if ( '1.1.0' === $version ) {

        // Create the database tables for the email users module
        be_mu_email_db_tables();

        // Create the new settings for the 1.1.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-email-status-module' => 'on',
            'be-mu-email-speed' => '240 per hour',
            'be-mu-email-unsubscribe-feature' => 'on',
            'be-mu-email-unsubscribe-footer' => '<p>---<br />You can unsubscribe from these messages by clicking '
                . '<a href="[unsubscribe_url]" target="_blank">here</a>.</p>',
        ) );

    } elseif ( '1.2.0' === $version ) {

        // We get some settings for the insert module from the old version so we can migrate them correctly to the new version
        $old_insert_settings = be_mu_get_settings( Array ( 'be-mu-insert-exclude-include-head', 'be-mu-insert-site-ids-head',
            'be-mu-insert-exclude-include-footer', 'be-mu-insert-site-ids-footer' ) );

        // Hold some of the new settings, that depend on the old settings
        $new_insert_settings = Array();

        // Based on the old settings we create some of the new settings
        if ( 'on all sites except' === $old_insert_settings['be-mu-insert-exclude-include-head'] ) {
            if ( empty( $old_insert_settings['be-mu-insert-site-ids-head'] ) ) {
                $new_insert_settings['be-mu-insert-head-affect-sites-id-option'] = 'Any site ID';
            } else {
                $new_insert_settings['be-mu-insert-head-affect-sites-id-option'] = 'All except these site IDs:';
            }
        } else {
            $new_insert_settings['be-mu-insert-head-affect-sites-id-option'] = 'Only these site IDs:';
        }
        if ( 'on all sites except' === $old_insert_settings['be-mu-insert-exclude-include-footer'] ) {
            if ( empty( $old_insert_settings['be-mu-insert-site-ids-footer'] ) ) {
                $new_insert_settings['be-mu-insert-footer-affect-sites-id-option'] = 'Any site ID';
            } else {
                $new_insert_settings['be-mu-insert-footer-affect-sites-id-option'] = 'All except these site IDs:';
            }
        } else {
            $new_insert_settings['be-mu-insert-footer-affect-sites-id-option'] = 'Only these site IDs:';
        }

        // Create the new settings for the 1.2.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-insert-head-affect-sites-id-option' => $new_insert_settings['be-mu-insert-head-affect-sites-id-option'],
            'be-mu-insert-head-site-ids' => $old_insert_settings['be-mu-insert-site-ids-head'],
            'be-mu-insert-head-front-end-theme' => 'on',
            'be-mu-insert-head-front-end-login' => 'off',
            'be-mu-insert-head-back-end' => 'off',
            'be-mu-insert-footer-affect-sites-id-option' => $new_insert_settings['be-mu-insert-footer-affect-sites-id-option'],
            'be-mu-insert-footer-site-ids' => $old_insert_settings['be-mu-insert-site-ids-footer'],
            'be-mu-insert-footer-front-end-theme' => 'on',
            'be-mu-insert-footer-front-end-login' => 'off',
            'be-mu-insert-footer-back-end' => 'off',
            'be-mu-plugin-network-disable-new-plugins' => 'off',
            'be-mu-improve-wp-signup-css-class' => 'off',
            'be-mu-improve-drop-leftover-tables' => 'off',
            'be-mu-copy-status-module' => 'on',
            'be-mu-copy-store-logs' => 'Last 20',
        ) );

        // Creates the database tables to store the log data for all modules that want to use it
        be_mu_logs_db_table();

    } elseif ( '1.3.0' === $version ) {

        // We need these to connect to the database
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

        // We try to get the column country_code to see if it exists
        $results = $wpdb->query( "SHOW COLUMNS FROM " . $main_blog_prefix . "be_mu_user_ips LIKE 'country_code'" );

        // If it does not exist, we create the column. It will hold the country code of the IP address.
        if ( ! $results ) {
            $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_user_ips ADD country_code VARCHAR(50) AFTER ip" );
        }

        // We try to get the column country_code_ip to see if it exists
        $results = $wpdb->query( "SHOW COLUMNS FROM " . $main_blog_prefix . "be_mu_user_ips LIKE 'country_code_ip'" );

        /**
         * If it does not exist, we create the column. It will hold the IP address, of which we have the country code. So when the address in the column "ip"
         * is different, we will know to get a new countru code.
         */
        if ( ! $results ) {
            $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_user_ips ADD country_code_ip VARCHAR(50) AFTER country_code" );
        }

        // Create the new settings for the 1.3.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-ban-show-flags' => 'on',
        ) );

    } elseif ( '1.4.0' === $version ) {

        // Create the new settings for the 1.4.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-improve-show-noindex-status' => 'on',
            'be-mu-improve-user-sites-role-icon' => 'on',
        ) );

    } elseif ( '1.5.0' === $version ) {

        // Create the new settings for the 1.5.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-captcha-text-login' => 'off',
        ) );

    } elseif ( '1.8.0' === $version ) {

        // We increase the size of some fields in the database. Some we need now, some just in case.
        global $wpdb;
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
        $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_cleanup MODIFY task_id VARCHAR(100)" );
        $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_scheduled_site_deletions MODIFY task_id VARCHAR(100)" );
        $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_scheduled_site_deletions MODIFY deletion_type VARCHAR(200)" );
        $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_scheduled_site_deletions MODIFY status VARCHAR(200)" );
        $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_site_deletion_emails MODIFY task_id VARCHAR(100)" );
        $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_site_deletion_emails MODIFY site_delete_type VARCHAR(200)" );
        $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_site_deletion_emails MODIFY status VARCHAR(200)" );

    } elseif ( '1.9.0' === $version ) {

        // Create the new settings for the 1.9.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-copy-template-site-notify-error' => 'on',
            'be-mu-copy-template-site-email' => 'off',
            'be-mu-copy-template-site-title' => 'off',
            'be-mu-copy-template-site-users' => 'skip',
            'be-mu-copy-template-site-time' => '5',
            'be-mu-copy-template-site-id' => '',
            'be-mu-copy-template-site-skip-super' => 'off',
            'be-mu-copy-template-site-enable' => 'off',
            'be-mu-ban-detect-ip-method' => 'Auto',
            'be-mu-ban-trusted-proxies' => '',
        ) );

    } elseif ( '1.11.0' === $version ) {

        // Create the new settings for the 1.11.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-improve-delete-leftover-folder' => 'off',
        ) );
    } elseif ( '1.12.0' === $version ) {

        // Create the new settings for the 1.12.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-copy-template-no-posts' => 'off',
            'be-mu-copy-template-no-pages' => 'off',
            'be-mu-copy-template-no-categories' => 'off',
            'be-mu-copy-template-no-tags' => 'off',
            'be-mu-captcha-images-folder' => 'Uploads folder',
        ) );
    } elseif ( '1.13.0' === $version ) {

        // Create the new settings for the 1.13.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-copy-template-no-media' => 'off',
            'be-mu-copy-site-no-posts' => 'off',
            'be-mu-copy-site-no-pages' => 'off',
            'be-mu-copy-site-no-categories' => 'off',
            'be-mu-copy-site-no-tags' => 'off',
            'be-mu-copy-site-no-media' => 'off',
        ) );
    } elseif ( '1.15.0' === $version ) {

        // We increase the size of some fields in the database. Some we need now, some just in case.
        global $wpdb;
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
        $results = $wpdb->query( "SHOW COLUMNS FROM " . $main_blog_prefix . "be_mu_email_emails LIKE 'unix_time_last_working'" );
        if ( ! $results ) {
            $wpdb->query( "ALTER TABLE " . $main_blog_prefix . "be_mu_email_emails ADD unix_time_last_working int( 11 ) AFTER status" );
        }

    } elseif ( '1.16.0' === $version ) {

        // Create the new settings for the 1.16.0 version of the plugin
        be_mu_make_settings( Array(
            'be-mu-copy-site-email' => 'on',
            'be-mu-copy-site-title' => 'on',
        ) );

    }
}

// Runs on plugin deactivation
function be_mu_deactivate_plugin() {

    // Removes the scheduled event to unban expired bans
    be_mu_remove_cron_unban();
}

// Creates a network admin menu page and adds a style and a script file for that page
function be_mu_add_beyond_multisite_menu() {
	$beyond_multisite_page = add_menu_page(
        esc_html__( 'Beyond Multisite', 'beyond-multisite' ),
        esc_html__( 'Beyond Multisite', 'beyond-multisite' ),
        'manage_network',
        'beyond-multisite',
        'be_mu_super_admin_page',
        esc_url( be_mu_img_url( 'menu_icon.png' ) )
    );

    // We add the style for the page we just created
    add_action( 'load-' . $beyond_multisite_page, 'be_mu_add_beyond_multisite_style' );

    // We add the script for the page we just created
    add_action( 'load-' . $beyond_multisite_page, 'be_mu_add_beyond_multisite_script' );
}

// Adds the action needed to register the style for the beyond multisite page
function be_mu_add_beyond_multisite_style() {
    add_action( 'admin_enqueue_scripts', 'be_mu_register_beyond_multisite_style' );
}

// Adds the action needed to register the script for the beyond multisite page
function be_mu_add_beyond_multisite_script() {
    add_action( 'admin_enqueue_scripts', 'be_mu_register_beyond_multisite_script' );
}

// Registers a style file and enqueues it
function be_mu_register_beyond_multisite_style() {
    wp_register_style( 'be-mu-beyond-multisite-style', be_mu_plugin_dir_url() . 'styles/style.css', false, BEYOND_MULTISITE_VERSION );
    wp_enqueue_style( 'be-mu-beyond-multisite-style' );
}

// Registers and localizes the javascript file for the beyond multisite page
function be_mu_register_beyond_multisite_script() {

    // Register the script
    wp_register_script( 'be-mu-beyond-multisite-script', be_mu_plugin_dir_url() . 'scripts/beyond-multisite.js', array(), BEYOND_MULTISITE_VERSION, false );

    // This is the data we will send from the php to the javascript file
    $localize = array(
        'ajaxNonce' => wp_create_nonce( 'be_mu_ajax_nonce' ),
        'errorImage' => esc_js( __( 'Error: Could not create the image.', 'beyond-multisite' ) ),
        'errorResponse' => esc_js( __( 'Error: We got an empty response.', 'beyond-multisite' ) ),
        'errorEmail' => esc_js( __( 'Error: This is not a valid email.', 'beyond-multisite' ) ),
        'errorFromEmail' => esc_js( __( 'Error: This is not a valid email to send from.', 'beyond-multisite' ) ),
        'errorID' => esc_js( __( 'Error: This is not a valid ID.', 'beyond-multisite' ) ),
        'errorFailedSend' => esc_js( __( 'Error: The email was not sent.', 'beyond-multisite' ) ),
        'errorAccess' => esc_js( __( 'Error: You do not have sufficient permissions to make this request.', 'beyond-multisite' ) ),
        'errorInvalidNonce' => esc_js( __( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' ) ),
        'done' => esc_js( esc_html__( 'Done', 'beyond-multisite' ) ),
        'errorServerFail' => esc_js( __( 'Error: Unexpected server error. If you have WordPress debugging and logging enabled, '
            . 'you should be able to see more details about the error in the /wp-content/debug.log file.', 'beyond-multisite' ) ),
    );

    // We localize the script - we send php data to the javascript file
    wp_localize_script( 'be-mu-beyond-multisite-script', 'localizedBeyondMultisite', $localize );

    // Enqueued script with localized data
    wp_enqueue_script( 'be-mu-beyond-multisite-script', '', array(), false, true );
}

/**
 * Based on the given variable it will display a message if needed
 * @param string $status_post_request
 */
function be_mu_handle_post_status( $status_post_request ) {

    // Display a success message; the message will also fade out and disappear after a few seconds thanks to a small script
    if ( 'success' == $status_post_request ) {
        be_mu_echo_fading_div_message();
    }

    // Display a general error
    if ( 'general-error' == $status_post_request ) {
        echo '<div id="be-mu-message-id" class="be-mu-message be-mu-error">' . esc_html__( 'Error', 'beyond-multisite' ) . '</div>';
    }

    // Display an invalid data error
    if ( 'invalid-data-error' == $status_post_request ) {
        echo '<div id="be-mu-message-id" class="be-mu-message be-mu-error">' . esc_html__( 'Invalid data', 'beyond-multisite' ) . '</div>';
    }

    // Display an invalid nonce error
    if ( 'invalid-nonce-error' == $status_post_request ) {
        echo '<div id="be-mu-message-id" class="be-mu-message be-mu-error">' . esc_html__( 'Invalid security nonce', 'beyond-multisite' ) . '</div>';
    }
}

/**
 * Checks if any of the turn on/off buttons for the modules is clicked and then performs the action; also sets a variable by reference for the status
 * @param array $modules
 * @param string $status_post_request
 */
function be_mu_turn_on_off_modules( $modules, &$status_post_request ) {

    // We go through all the modules and check if any of the turn on or off buttons are clicked
    foreach ( $modules as $module ) {

        // Check if the turn on button for the current module is clicked
        if ( isset( $_POST['be-mu-turn-on-' . $module . '-module'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-' . $module . '-status-form-nonce-name'], 'be-mu-' . $module . '-status-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            // If we are turning on the ban module we also schedule an event (if not already scheduled) to run once an hour to unban expired bans
            if ( 'ban' == $module ) {
                be_mu_add_cron_unban();

            // If we are turning on the plugin control module and the setting to disable new plugins is on, we update the list of plugins ever installed
            } elseif ( 'plugin' == $module && be_mu_get_setting( 'be-mu-plugin-network-disable-new-plugins' ) == 'on' ) {
                be_mu_plugin_create_or_update_plugins_ever_installed_list();
            }

            if ( ! be_mu_set_or_make_setting( 'be-mu-' . $module . '-status-module', 'on' ) ) {
                $status_post_request = 'general-error';
                break;
            }
            $status_post_request = 'success';

            // If this is one of the modules that adds new menu elements in the admin panel menu, we reload so they are visible right now
            if ( 'ban' == $module || 'pending' == $module || 'clean' == $module || 'plugin' == $module || 'email' == $module || 'copy' == $module
                || 'improve' == $module || 'moderation' == $module ) {
                be_mu_reload_settings_page();
            }

            // No need to check for the other modules so break the foreach
            break;
        }

        // Check if the turn off button for the current module is clicked
        if ( isset( $_POST['be-mu-turn-off-' . $module . '-module'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-' . $module . '-status-form-nonce-name'], 'be-mu-' . $module . '-status-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            // If we are turning off the ban module we also remove the scheduled event that unbans expired bans
            if ( 'ban' == $module ) {
                be_mu_remove_cron_unban();
            }

            if ( ! be_mu_set_or_make_setting( 'be-mu-' . $module . '-status-module', 'off' ) ) {
                $status_post_request = 'general-error';
                break;
            }

            $status_post_request = 'success';

            // If this is one of the modules that adds new menu elements in the admin panel menu, we reload so they are visible right now
            if ( 'ban' == $module || 'pending' == $module || 'clean' == $module || 'plugin' == $module || 'email' == $module || 'copy' == $module
                || 'improve' == $module || 'moderation' == $module ) {
                be_mu_reload_settings_page();
            }

            // No need to check for the other modules so break the foreach
            break;
        }
    }
}

// Adds a network admin page for settings
function be_mu_super_admin_page() {

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'beyond-multisite' ) );
    }

    /*
     * This do - while(false) loop will always run just once, and it is just for our convenience
     * With it we can break at any point of the post request
     */
    do {

        // A variable for the status of the post request:
        $status_post_request = 'not-a-post-request';

        // An array with all the module short names
        $modules = Array( 'captcha', 'insert', 'ban', 'pending', 'improve', 'activated-in', 'clean', 'plugin', 'email', 'copy' );

        // Variable with the escaped url of the beyond multisite settings page
        $settings_url = esc_url( network_admin_url( 'admin.php?page=beyond-multisite' ) );

        // Here we check if any turn on/off button is clicked and turn on/off the selected module
        be_mu_turn_on_off_modules( $modules, $status_post_request );

        // The first array controls whether to display the show settings button, the second one whether to display the description tab
        $show_settings_display = $description_display = Array(
            'captcha' => '',
            'insert' => '',
            'ban' => '',
            'plugin' => '',
            'clean' => '',
            'improve' => '',
            'email' => '',
            'copy' => '',
        );

        // The array controls whether to display the hide settings button
        $hide_settings_display = Array(
            'captcha' => 'style="display:none;"',
            'insert' => 'style="display:none;"',
            'ban' => 'style="display:none;"',
            'plugin' => 'style="display:none;"',
            'clean' => 'style="display:none;"',
            'improve' => 'style="display:none;"',
            'email' => 'style="display:none;"',
            'copy' => 'style="display:none;"',
        );

        // The array controls whether to display the settings tab
        $settings_display = Array(
            'captcha' => 'be-mu-display-none',
            'insert' => 'be-mu-display-none',
            'ban' => 'be-mu-display-none',
            'plugin' => 'be-mu-display-none',
            'clean' => 'be-mu-display-none',
            'improve' => 'be-mu-display-none',
            'email' => 'be-mu-display-none',
            'copy' => 'be-mu-display-none',
        );

        // If the update settings button for the captcha module is clicked we update the settings
        if ( isset( $_POST['be-mu-update-captcha-settings'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-captcha-settings-form-nonce-name'], 'be-mu-captcha-settings-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            $status_post_request = 'success';
            $show_settings_display['captcha'] = 'style="display:none;"';
            $hide_settings_display['captcha'] = '';
            $description_display['captcha'] = 'be-mu-display-none';
            $settings_display['captcha'] = '';

            // If the drop down list options are not set, we stop and set the status to general error
            if ( ! isset( $_POST['be-mu-captcha-characters'] ) || ! isset( $_POST['be-mu-captcha-height'] )
                || ! isset( $_POST['be-mu-captcha-character-set'] ) ) {
                $status_post_request = 'general-error';
                break;
            }

            // Get the values for the drop down menus
            $captcha_characters = intval( $_POST['be-mu-captcha-characters'] );
            $captcha_character_set = $_POST['be-mu-captcha-character-set'];
            $captcha_images_folder = $_POST['be-mu-captcha-images-folder'];
            $captcha_height = intval( $_POST['be-mu-captcha-height'] );

            // If the data is not valid we stop and set the status to invalid data error
            if ( $captcha_characters < 3 || $captcha_characters > 5 || $captcha_height < 60 || $captcha_height > 120
                || ( 'Numbers' != $captcha_character_set && 'Letters' != $captcha_character_set && 'Numbers and letters' != $captcha_character_set )
                || ( 'Uploads folder' != $captcha_images_folder && 'Plugin folder' != $captcha_images_folder ) ) {
                $status_post_request = 'invalid-data-error';
                break;
            }

            // First we set all variables for the checkbox settings to off
            $captcha_login = $captcha_comment_logged_in = $captcha_comment_logged_out = $captcha_lost_password = $captcha_reset_password
                = $captcha_user_signup = $captcha_blog_signup_logged_out = $captcha_blog_signup_logged_in = $captcha_text_login = "off";

            // And now we set the ones that are checked to on
            if ( isset( $_POST['be-mu-captcha-login'] ) ) {
                $captcha_login = 'on';
            }
            if ( isset( $_POST['be-mu-captcha-comment-logged-out'] ) ) {
                $captcha_comment_logged_out = 'on';
            }
            if ( isset( $_POST['be-mu-captcha-comment-logged-in'] ) ) {
                $captcha_comment_logged_in = 'on';
            }
            if ( isset( $_POST['be-mu-captcha-lost-password'] ) ) {
                $captcha_lost_password = 'on';
            }
            if ( isset( $_POST['be-mu-captcha-reset-password'] ) ) {
                $captcha_reset_password = 'on';
            }
            if ( isset( $_POST['be-mu-captcha-user-signup'] ) ) {
                $captcha_user_signup = 'on';
            }
            if ( isset( $_POST['be-mu-captcha-blog-signup-logged-out'] ) ) {
                $captcha_blog_signup_logged_out = 'on';
            }
            if ( isset( $_POST['be-mu-captcha-blog-signup-logged-in'] ) ) {
                $captcha_blog_signup_logged_in = 'on';
            }
            if ( isset( $_POST['be-mu-captcha-text-login'] ) ) {
                $captcha_text_login = 'on';
            }

            // We update the settings with the chosen values
            if ( ! be_mu_set_or_make_settings( array(
                'be-mu-captcha-characters' => $captcha_characters,
                'be-mu-captcha-character-set' => $captcha_character_set,
                'be-mu-captcha-height' => $captcha_height,
                'be-mu-captcha-login' => $captcha_login,
                'be-mu-captcha-comment-logged-out' => $captcha_comment_logged_out,
                'be-mu-captcha-comment-logged-in' => $captcha_comment_logged_in,
                'be-mu-captcha-lost-password' => $captcha_lost_password,
                'be-mu-captcha-reset-password' => $captcha_reset_password,
                'be-mu-captcha-user-signup' => $captcha_user_signup,
                'be-mu-captcha-blog-signup-logged-out' => $captcha_blog_signup_logged_out,
                'be-mu-captcha-blog-signup-logged-in' => $captcha_blog_signup_logged_in,
                'be-mu-captcha-text-login' => $captcha_text_login,
                'be-mu-captcha-images-folder' => $captcha_images_folder,
            ) ) ) {
                $status_post_request = 'general-error';
                break;
            }
        }

        // If the update settings button for the insert HTML module is clicked we update the settings
        if ( isset( $_POST['be-mu-update-insert-settings'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-insert-settings-form-nonce-name'], 'be-mu-insert-settings-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            $status_post_request = 'success';
            $show_settings_display['insert'] = 'style="display:none;"';
            $hide_settings_display['insert'] = '';
            $description_display['insert'] = 'be-mu-display-none';
            $settings_display['insert'] = '';

            // If any of these two form elements are not set, we stop and set the status to general error. If they are set we put them in variables.
            if ( ! isset( $_POST['be-mu-insert-head-affect-sites-id-option'] ) || ! isset( $_POST['be-mu-insert-footer-affect-sites-id-option'] ) ) {
                $status_post_request = 'general-error';
                break;
            } else {
                $insert_affect_sites_footer = $_POST['be-mu-insert-footer-affect-sites-id-option'];
                $insert_affect_sites_head = $_POST['be-mu-insert-head-affect-sites-id-option'];
            }

            // If any of those elements are not set we set them to an empty string, otherwise we set them to the value that is sent for them from the form
            if ( ! isset( $_POST['be-mu-insert-head']) ) {
                $insert_head = '';
            } else {
                $insert_head = stripslashes_deep( $_POST['be-mu-insert-head'] );
            }

            if ( ! isset( $_POST['be-mu-insert-footer'] ) ) {
                $insert_footer = '';
            } else {
                $insert_footer = stripslashes_deep( $_POST['be-mu-insert-footer'] );
            }

            if ( ! isset( $_POST['be-mu-insert-footer-site-ids'] ) ) {
                $insert_site_ids_footer = '';
            } else {
                $insert_site_ids_footer = be_mu_strip_whitespace( $_POST['be-mu-insert-footer-site-ids'] );
            }

            if ( ! isset( $_POST['be-mu-insert-head-site-ids'] ) ) {
                $insert_site_ids_head = '';
            } else {
                $insert_site_ids_head = be_mu_strip_whitespace( $_POST['be-mu-insert-head-site-ids'] );
            }

            // We check if the values are valid for all except the fields that allow html code (nothing we can do there, we even allow scripts for them)
            if ( ( 'Any site ID' != $insert_affect_sites_footer && 'Only these site IDs:' != $insert_affect_sites_footer
                && 'All except these site IDs:' != $insert_affect_sites_footer )
                || ( 'Any site ID' != $insert_affect_sites_head && 'Only these site IDs:' != $insert_affect_sites_head
                && 'All except these site IDs:' != $insert_affect_sites_head )
                || ( ! be_mu_is_comma_separated_numbers( $insert_site_ids_footer ) && '' != $insert_site_ids_footer )
                || ( ! be_mu_is_comma_separated_numbers( $insert_site_ids_head ) && '' != $insert_site_ids_head ) ) {
                $status_post_request = 'invalid-data-error';
                break;
            }

            /*
             * We decode the contents of these variables, since we encoded it earlier with JavaScript, so when programming code is saved in these
             * fields, it is not blocked by various security measures.
             */
            $insert_head = rawurldecode( base64_decode( $insert_head ) );
            $insert_footer = rawurldecode( base64_decode( $insert_footer ) );

            // First we set all variables for the checkbox settings to off
            $insert_head_front_end_theme = $insert_footer_front_end_theme = $insert_head_front_end_login = $insert_footer_front_end_login
                = $insert_head_back_end = $insert_footer_back_end = "off";

            // And now we set the ones that are checked to on
            if ( isset( $_POST['be-mu-insert-head-front-end-theme'] ) ) {
                $insert_head_front_end_theme = 'on';
            }
            if ( isset( $_POST['be-mu-insert-footer-front-end-theme'] ) ) {
                $insert_footer_front_end_theme = 'on';
            }
            if ( isset( $_POST['be-mu-insert-head-front-end-login'] ) ) {
                $insert_head_front_end_login = 'on';
            }
            if ( isset( $_POST['be-mu-insert-footer-front-end-login'] ) ) {
                $insert_footer_front_end_login = 'on';
            }
            if ( isset( $_POST['be-mu-insert-head-back-end'] ) ) {
                $insert_head_back_end = 'on';
            }
            if ( isset( $_POST['be-mu-insert-footer-back-end'] ) ) {
                $insert_footer_back_end = 'on';
            }

            // We update the settings with the chosen values
            if ( ! be_mu_set_or_make_settings( array(
                'be-mu-insert-head' => $insert_head,
                'be-mu-insert-footer' => $insert_footer,
                'be-mu-insert-footer-affect-sites-id-option' => $insert_affect_sites_footer,
                'be-mu-insert-head-affect-sites-id-option' => $insert_affect_sites_head,
                'be-mu-insert-footer-site-ids' => $insert_site_ids_footer,
                'be-mu-insert-head-site-ids' => $insert_site_ids_head,
                'be-mu-insert-head-front-end-theme' => $insert_head_front_end_theme,
                'be-mu-insert-footer-front-end-theme' => $insert_footer_front_end_theme,
                'be-mu-insert-head-front-end-login' => $insert_head_front_end_login,
                'be-mu-insert-footer-front-end-login' => $insert_footer_front_end_login,
                'be-mu-insert-head-back-end' => $insert_head_back_end,
                'be-mu-insert-footer-back-end' => $insert_footer_back_end,
            ) ) ) {
                $status_post_request = 'general-error';
                break;
            }

        }

        // If the update settings button for the Ban Users module is clicked we update the settings
        if ( isset( $_POST['be-mu-update-ban-settings'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-ban-settings-form-nonce-name'], 'be-mu-ban-settings-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            $status_post_request = 'success';
            $show_settings_display['ban'] = 'style="display:none;"';
            $hide_settings_display['ban'] = '';
            $description_display['ban'] = 'be-mu-display-none';
            $settings_display['ban'] = '';

            // If the drop down list or radio options are not set, we stop and set the status to general error
            if ( ! isset( $_POST['be-mu-ban-detect-ip-method'] ) || ! isset( $_POST['be-mu-ban-trusted-proxies'] ) ) {
                $status_post_request = 'general-error';
                break;
            }

            // Get the values for the drop down menus and radio elements
            $ban_ip_method = $_POST['be-mu-ban-detect-ip-method'];
            $ban_trusted_proxies = $_POST['be-mu-ban-trusted-proxies'];

            // If the data is not valid we stop and set the status to invalid data error
            if ( ! in_array( $ban_ip_method, array( 'Auto', 'REMOTE_ADDR', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR',
                'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP' ) )
                || ( ! be_mu_is_comma_separated_ips( $ban_trusted_proxies ) && '' !== $ban_trusted_proxies ) ) {
                $status_post_request = 'invalid-data-error';
                break;
            }
                      
            // First we set all variables for the checkbox settings to off
            $ban_ip_column = $ban_status_column = $ban_show_flags = 'off';

            // And now we set the ones that are checked to on
            if ( isset( $_POST['be-mu-ban-ip-column'] ) ) {
                $ban_ip_column = 'on';
            }
            if ( isset( $_POST['be-mu-ban-status-column'] ) ) {
                $ban_status_column = 'on';
            }
            if ( isset( $_POST['be-mu-ban-show-flags'] ) ) {
                $ban_show_flags = 'on';
            }

            // We update the settings with the chosen values
            if ( ! be_mu_set_or_make_settings( array(
                'be-mu-ban-ip-column' => $ban_ip_column,
                'be-mu-ban-status-column' => $ban_status_column,
                'be-mu-ban-show-flags' => $ban_show_flags,
                'be-mu-ban-detect-ip-method' => $ban_ip_method,
                'be-mu-ban-trusted-proxies' => $ban_trusted_proxies,
            ) ) ) {
                $status_post_request = 'general-error';
                break;
            }

        }

        // If the update settings button for the Plugin Control module is clicked we update the settings
        if ( isset( $_POST['be-mu-update-plugin-settings'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-plugin-settings-form-nonce-name'], 'be-mu-plugin-settings-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            $status_post_request = 'success';
            $show_settings_display['plugin'] = 'style="display:none;"';
            $hide_settings_display['plugin'] = '';
            $description_display['plugin'] = 'be-mu-display-none';
            $settings_display['plugin'] = '';

            // First we set all variables for the checkbox settings to off
            $plugin_disable_new = 'off';

            // And now we set the ones that are checked to on
            if ( isset( $_POST['be-mu-plugin-network-disable-new-plugins'] ) ) {

                $plugin_disable_new = 'on';

                /*
                 * If we are changing the setting from off to on, we will also update the list with all plugins ever installed.
                 * If we do not do this, and a plugin is uploaded via FTP after this setting is turned on (without visiting the plugins page)
                 * then the new uploaded plugin will not be considered as a new one and will not be disabled.
                 */
                if ( be_mu_get_setting( 'be-mu-plugin-network-disable-new-plugins' ) !== 'on' ) {
                    be_mu_plugin_create_or_update_plugins_ever_installed_list();
                }
            }

            // We update the settings with the chosen values
            if ( ! be_mu_set_or_make_settings( array(
                'be-mu-plugin-network-disable-new-plugins' => $plugin_disable_new,
            ) ) ) {
                $status_post_request = 'general-error';
                break;
            }

        }

        // If the update settings button for the cleanup module is clicked we update the settings
        if ( isset( $_POST['be-mu-update-clean-settings'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-clean-settings-form-nonce-name'], 'be-mu-clean-settings-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            $status_post_request = 'success';
            $show_settings_display['clean'] = 'style="display:none;"';
            $hide_settings_display['clean'] = '';
            $description_display['clean'] = 'be-mu-display-none';
            $settings_display['clean'] = '';

            // If some of the settings are not set, we stop and set the status to general error
            if ( ! isset( $_POST['be-mu-clean-email-speed'] ) || ! isset( $_POST['be-mu-clean-from-email'] )
                || ! isset( $_POST['be-mu-clean-subject'] ) || ! isset( $_POST['be-mu-clean-message'] ) || ! isset( $_POST['be-mu-clean-test-email'] ) ) {
                $status_post_request = 'general-error';
                break;
            }

            // We get the values for the settings and put them in variables
            $clean_email_speed = $_POST['be-mu-clean-email-speed'];
            $clean_from_email = $_POST['be-mu-clean-from-email'];
            $clean_from_name = wp_filter_nohtml_kses( $_POST['be-mu-clean-from-name'] );
            $clean_from_name = wp_encode_emoji( $clean_from_name );
            $clean_subject = wp_filter_nohtml_kses( $_POST['be-mu-clean-subject'] );
            $clean_subject = wp_encode_emoji( $clean_subject );
            $clean_message = stripslashes( $_POST['be-mu-clean-message'] );
            $clean_message = wpautop( $clean_message );
            $clean_message = wp_encode_emoji( $clean_message );
            $clean_test_email = $_POST['be-mu-clean-test-email'];

            // If the data is not valid we stop and set the status to invalid data error
            if ( ! in_array( $clean_email_speed, array( '240 per hour', '480 per hour', '720 per hour', '960 per hour',
                    '1200 per hour', '1440 per hour', '1680 per hour', '1920 per hour', '2160 per hour',
                    '2400 per hour', '2640 per hour', '2880 per hour', '3120 per hour', '3360 per hour', '3600 per hour' ) )
                || ! filter_var( $clean_from_email, FILTER_VALIDATE_EMAIL )
                || ( ! filter_var( $clean_test_email, FILTER_VALIDATE_EMAIL ) && '' != $clean_test_email ) ) {
                $status_post_request = 'invalid-data-error';
                break;
            }

            // We update the settings with the chosen values
            if ( ! be_mu_set_or_make_settings( array(
                'be-mu-clean-email-speed' => $clean_email_speed,
                'be-mu-clean-from-email' => $clean_from_email,
                'be-mu-clean-from-name' => $clean_from_name,
                'be-mu-clean-subject' => $clean_subject,
                'be-mu-clean-message' => $clean_message,
                'be-mu-clean-test-email' => $clean_test_email,
            ) ) ) {
                $status_post_request = 'general-error';
                break;
            }
        }

        // If the update settings button for the improvements module is clicked we update the settings
        if ( isset( $_POST['be-mu-update-improve-settings'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-improve-settings-form-nonce-name'], 'be-mu-improve-settings-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            $status_post_request = 'success';
            $show_settings_display['improve'] = 'style="display:none;"';
            $hide_settings_display['improve'] = '';
            $description_display['improve'] = 'be-mu-display-none';
            $settings_display['improve'] = '';

            // First we set all variables for the checkbox settings to off
            $user_id_column = $site_id_column = $user_sites_dash_action = $hide_plugin_meta = $network_plugin_admin_menus
                = $network_theme_admin_menus = $change_password_email = $add_signup_css_class = $delete_leftover_tables = $show_noindex_icon
                = $user_sites_role_icon = $delete_leftover_folder = "off";

            // And now we set the ones that are checked to on
            if ( isset( $_POST['be-mu-improve-user-id-column'] ) ) {
                $user_id_column = 'on';
            }
            if ( isset( $_POST['be-mu-improve-user-sites-dash-action'] ) ) {
                $user_sites_dash_action = 'on';
            }
            if ( isset( $_POST['be-mu-improve-site-id-column'] ) ) {
                $site_id_column = 'on';
            }
            if ( isset( $_POST['be-mu-improve-hide-plugin-meta'] ) ) {
                $hide_plugin_meta = 'on';
            }
            if ( isset( $_POST['be-mu-improve-network-plugin-admin-menus'] ) ) {
                $network_plugin_admin_menus = 'on';
            }
            if ( isset( $_POST['be-mu-improve-network-theme-admin-menus'] ) ) {
                $network_theme_admin_menus = 'on';
            }
            if ( isset( $_POST['be-mu-improve-password-change-email'] ) ) {
                $change_password_email = 'on';
            }
            if ( isset( $_POST['be-mu-improve-wp-signup-css-class'] ) ) {
                $add_signup_css_class = 'on';
            }
            if ( isset( $_POST['be-mu-improve-drop-leftover-tables'] ) ) {
                $delete_leftover_tables = 'on';
            }
            if ( isset( $_POST['be-mu-improve-delete-leftover-folder'] ) ) {
                $delete_leftover_folder = 'on';
            }
            if ( isset( $_POST['be-mu-improve-show-noindex-status'] ) ) {
                $show_noindex_icon = 'on';
            }
            if ( isset( $_POST['be-mu-improve-user-sites-role-icon'] ) ) {
                $user_sites_role_icon = 'on';
            }

            // We update the settings with the chosen values
            if ( ! be_mu_set_or_make_settings( array(
                'be-mu-improve-user-id-column' => $user_id_column,
                'be-mu-improve-user-sites-dash-action' => $user_sites_dash_action,
                'be-mu-improve-site-id-column' => $site_id_column,
                'be-mu-improve-hide-plugin-meta' => $hide_plugin_meta,
                'be-mu-improve-network-plugin-admin-menus' => $network_plugin_admin_menus,
                'be-mu-improve-network-theme-admin-menus' => $network_theme_admin_menus,
                'be-mu-improve-password-change-email' => $change_password_email,
                'be-mu-improve-wp-signup-css-class' => $add_signup_css_class,
                'be-mu-improve-drop-leftover-tables' => $delete_leftover_tables,
                'be-mu-improve-delete-leftover-folder' => $delete_leftover_folder,
                'be-mu-improve-show-noindex-status' => $show_noindex_icon,
                'be-mu-improve-user-sites-role-icon' => $user_sites_role_icon,
            ) ) ) {
                $status_post_request = 'general-error';
                break;
            }

            // We need to reload the page in order for changes to the admin bar to take effect
            be_mu_reload_settings_page( 'improve' );
        }

        // If the update settings button for the email users module is clicked we update the settings
        if ( isset( $_POST['be-mu-update-email-settings'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-email-settings-form-nonce-name'], 'be-mu-email-settings-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            $status_post_request = 'success';
            $show_settings_display['email'] = 'style="display:none;"';
            $hide_settings_display['email'] = '';
            $description_display['email'] = 'be-mu-display-none';
            $settings_display['email'] = '';

            // First we set all variables for the checkbox settings to off
            $unsubscribe_feature = "off";

            // And now we set the ones that are checked to on
            if ( isset( $_POST['be-mu-email-unsubscribe-feature'] ) ) {
                $unsubscribe_feature = 'on';
            }

            // If some of the settings are not set, we stop and set the status to general error
            if ( ! isset( $_POST['be-mu-email-speed'] ) || ! isset( $_POST['be-mu-email-unsubscribe-footer'] ) ) {
                $status_post_request = 'general-error';
                break;
            }

            // We get the values for the settings and put them in variables
            $email_speed = $_POST['be-mu-email-speed'];
            $unsubscribe_footer = stripslashes( $_POST['be-mu-email-unsubscribe-footer'] );
            $unsubscribe_footer = wpautop( $unsubscribe_footer );
            $unsubscribe_footer = wp_encode_emoji( $unsubscribe_footer );

            // Fix automatically added http from editor
            if ( strpos( $unsubscribe_footer, "http://[unsubscribe_url]" ) !== false ) {
                $unsubscribe_footer = str_replace( "http://[unsubscribe_url]", "[unsubscribe_url]", $unsubscribe_footer );
            }
            if ( strpos( $unsubscribe_footer, "https://[unsubscribe_url]" ) !== false ) {
                $unsubscribe_footer = str_replace( "https://[unsubscribe_url]", "[unsubscribe_url]", $unsubscribe_footer );
            }

            // If the data is not valid we stop and set the status invalid data error
            if ( ! in_array( $email_speed, array( '240 per hour', '480 per hour', '720 per hour', '960 per hour',
                    '1200 per hour', '1440 per hour', '1680 per hour', '1920 per hour', '2160 per hour',
                    '2400 per hour', '2640 per hour', '2880 per hour', '3120 per hour', '3360 per hour', '3600 per hour' ) ) ) {
                $status_post_request = 'invalid-data-error';
                break;
            }

            // We update the settings with the chosen values
            if ( ! be_mu_set_or_make_settings( array(
                'be-mu-email-speed' => $email_speed,
                'be-mu-email-unsubscribe-feature' => $unsubscribe_feature,
                'be-mu-email-unsubscribe-footer' => $unsubscribe_footer,
            ) ) ) {
                $status_post_request = 'general-error';
                break;
            }
        }

        // If the update settings button for the copy maker module is clicked we update the settings
        if ( isset( $_POST['be-mu-update-copy-settings'] ) ) {

            // Check the nonce field for better security
            if ( ! wp_verify_nonce( $_POST['be-mu-copy-settings-form-nonce-name'], 'be-mu-copy-settings-form-nonce-action' ) ) {
                $status_post_request = 'invalid-nonce-error';
                break;
            }

            $status_post_request = 'success';
            $show_settings_display['copy'] = 'style="display:none;"';
            $hide_settings_display['copy'] = '';
            $description_display['copy'] = 'be-mu-display-none';
            $settings_display['copy'] = '';

            // First we set all variables for the checkbox settings to off
            $copy_template_site_enable = $copy_template_site_super = $copy_template_site_title = $copy_template_site_email = $copy_template_site_notify =
                $copy_template_no_posts = $copy_template_no_pages = $copy_template_no_categories = $copy_template_no_tags = $copy_template_no_media =
                $copy_site_no_posts = $copy_site_no_pages = $copy_site_no_categories = $copy_site_no_tags = $copy_site_no_media =
                $copy_site_title = $copy_site_email = "off";

            // And now we set the ones that are checked to on
            if ( isset( $_POST['be-mu-copy-template-site-enable'] ) ) {
                $copy_template_site_enable = 'on';
            }
            if ( isset( $_POST['be-mu-copy-template-site-skip-super'] ) ) {
                $copy_template_site_super = 'on';
            }
            if ( isset( $_POST['be-mu-copy-template-site-title'] ) ) {
                $copy_template_site_title = 'on';
            }
            if ( isset( $_POST['be-mu-copy-template-site-email'] ) ) {
                $copy_template_site_email = 'on';
            }
            if ( isset( $_POST['be-mu-copy-template-site-notify-error'] ) ) {
                $copy_template_site_notify = 'on';
            }
            if ( isset( $_POST['be-mu-copy-template-no-posts'] ) ) {
                $copy_template_no_posts = 'on';
            }
            if ( isset( $_POST['be-mu-copy-template-no-pages'] ) ) {
                $copy_template_no_pages = 'on';
            }
            if ( isset( $_POST['be-mu-copy-template-no-categories'] ) ) {
                $copy_template_no_categories = 'on';
            }
            if ( isset( $_POST['be-mu-copy-template-no-tags'] ) ) {
                $copy_template_no_tags = 'on';
            }
            if ( isset( $_POST['be-mu-copy-template-no-media'] ) ) {
                $copy_template_no_media = 'on';
            }
            if ( isset( $_POST['be-mu-copy-site-no-posts'] ) ) {
                $copy_site_no_posts = 'on';
            }
            if ( isset( $_POST['be-mu-copy-site-no-pages'] ) ) {
                $copy_site_no_pages = 'on';
            }
            if ( isset( $_POST['be-mu-copy-site-no-categories'] ) ) {
                $copy_site_no_categories = 'on';
            }
            if ( isset( $_POST['be-mu-copy-site-no-tags'] ) ) {
                $copy_site_no_tags = 'on';
            }
            if ( isset( $_POST['be-mu-copy-site-no-media'] ) ) {
                $copy_site_no_media = 'on';
            }
            if ( isset( $_POST['be-mu-copy-site-title'] ) ) {
                $copy_site_title = 'on';
            }
            if ( isset( $_POST['be-mu-copy-site-email'] ) ) {
                $copy_site_email = 'on';
            }

            if ( ! isset( $_POST['be-mu-copy-template-site-id'] ) ) {
                $copy_template_site_id = '';
            } else {
                $copy_template_site_id = be_mu_strip_whitespace( $_POST['be-mu-copy-template-site-id'] );
            }

            // If some of the settings are not set, we stop and set the status to general error
            if ( ! isset( $_POST['be-mu-copy-store-logs'] ) || ! isset( $_POST['be-mu-copy-template-site-time'] )
                || ! isset( $_POST['be-mu-copy-template-site-users'] ) ) {
                $status_post_request = 'general-error';
                break;
            }

            // We get the values for the settings and put them in variables
            $store_logs = $_POST['be-mu-copy-store-logs'];
            $copy_template_site_time = $_POST['be-mu-copy-template-site-time'];
            $copy_template_site_users = $_POST['be-mu-copy-template-site-users'];

            // If the data is not valid we stop and set the status invalid data error
            if ( ! in_array( $store_logs, array( 'Last 10', 'Last 20', 'Last 50', 'Last 100', 'Last 500', 'All' ) )
                || ( '' !== $copy_template_site_id && ! be_mu_is_whole_positive_number( $copy_template_site_id ) )
                || ! be_mu_is_whole_positive_number( $copy_template_site_time ) || $copy_template_site_time > 25
                || ! in_array( $copy_template_site_users, array( 'skip', 'add', 'replace' ) )
                || ( 'on' === $copy_template_site_enable && '' === $copy_template_site_id )
                || ( be_mu_is_whole_positive_number( $copy_template_site_id ) && is_main_site( $copy_template_site_id ) ) ) {
                $status_post_request = 'invalid-data-error';
                break;
            }

            // We update the settings with the chosen values
            if ( ! be_mu_set_or_make_settings( array(
                'be-mu-copy-store-logs' => $store_logs,
                'be-mu-copy-template-site-notify-error' => $copy_template_site_notify,
                'be-mu-copy-template-site-email' => $copy_template_site_email,
                'be-mu-copy-template-site-title' => $copy_template_site_title,
                'be-mu-copy-template-site-users' => $copy_template_site_users,
                'be-mu-copy-template-site-time' => $copy_template_site_time,
                'be-mu-copy-template-site-id' => $copy_template_site_id,
                'be-mu-copy-template-site-skip-super' => $copy_template_site_super,
                'be-mu-copy-template-site-enable' => $copy_template_site_enable,
                'be-mu-copy-template-no-posts' => $copy_template_no_posts,
                'be-mu-copy-template-no-pages' => $copy_template_no_pages,
                'be-mu-copy-template-no-categories' => $copy_template_no_categories,
                'be-mu-copy-template-no-tags' => $copy_template_no_tags,
                'be-mu-copy-template-no-media' => $copy_template_no_media,
                'be-mu-copy-site-no-posts' => $copy_site_no_posts,
                'be-mu-copy-site-no-pages' => $copy_site_no_pages,
                'be-mu-copy-site-no-categories' => $copy_site_no_categories,
                'be-mu-copy-site-no-tags' => $copy_site_no_tags,
                'be-mu-copy-site-no-media' => $copy_site_no_media,
                'be-mu-copy-site-email' => $copy_site_email,
                'be-mu-copy-site-title' => $copy_site_title,
            ) ) ) {
                $status_post_request = 'general-error';
                break;
            }

            // We delete the unwanted logs based on the new selected setting
            be_mu_copy_sites_delete_unwanted_logs();

        }

        // We have been redirected after enabling/disabling a module that changes the admin menus
        if ( isset( $_GET['done'] ) ) {
            $status_post_request = 'success';
        }

    } while( false );

    // If we have reloaded the page and need some settings to be visible, we do that
    if ( isset( $_GET['module'] ) && in_array( $_GET['module'], $modules ) ) {
        $module_name = $_GET['module'];
        $show_settings_display[ $module_name ] = 'style="display:none;"';
        $hide_settings_display[ $module_name ] = '';
        $description_display[ $module_name ] = 'be-mu-display-none';
        $settings_display[ $module_name ] = '';
    }

    // Based on the settings for the status of each module, we set some variables with a different css class, message and a button
    foreach ( $modules as $module ) {
        if ( be_mu_get_setting( 'be-mu-' . $module . '-status-module' ) != 'on' ) {
            $status_button_module[ $module ] = '<input class="button button-primary be-mu-status-button" name="be-mu-turn-on-' . esc_attr( $module ) . '-module"
                type="submit" value="' . esc_attr__( 'Turn On', 'beyond-multisite' ) . '" />';
            $status_class_module[ $module ] = 'class="be-mu-circle be-mu-circle-off" title="'
                . esc_attr__( 'This module is turned off.', 'beyond-multisite' ) . '"';
            $status_module_in_settings[ $module ] = "<div class='be-mu-module-is-off'>" . esc_html__( 'This module is turned off.', 'beyond-multisite' )
                . "</div>";
            $quick_start_step_one[ $module ] = "<li>" . esc_html__( 'Click on the "Turn On" button above', 'beyond-multisite' ) . "</li>";
        } else {
            $status_button_module[ $module ] = '<input class="button be-mu-status-button" name="be-mu-turn-off-' . esc_attr( $module ) . '-module"
                type="submit" value="' . esc_attr__( 'Turn Off', 'beyond-multisite' ) . '" />';
            $status_class_module[ $module ] = 'class="be-mu-circle be-mu-circle-on" title="'
                . esc_attr__( 'This module is turned on.', 'beyond-multisite' ) . '"';
            $status_module_in_settings[ $module ] = "";
            $quick_start_step_one[ $module ] = "";
        }
    }

    // Get the current captcha settings
    $current_captcha_characters = intval( be_mu_get_setting( 'be-mu-captcha-characters' ) );
    $current_captcha_character_set = be_mu_get_setting( 'be-mu-captcha-character-set' );
    $current_captcha_height = intval( be_mu_get_setting( 'be-mu-captcha-height' ) );

    // Decide which characters to exclude based on the settings
    if ( 'Numbers' == $current_captcha_character_set ) {
        $exclude = 'abcdefghijklmnopqrstuvwxyz';
    } elseif ( 'Letters' == $current_captcha_character_set ) {
        $exclude = '0olge312456789';
    } else {
        $exclude = '0olge3';
    }

    // Generate a randow answer text
    $answer = be_mu_random_string( $current_captcha_characters, $exclude );

    // Generate the preview captcha image
    be_mu_captcha_image( $current_captcha_height, $answer, 'preview' );

    // Set the url to the captcha preview image and the url to the loading gif
    $preview_captcha_url = be_mu_get_captcha_folder_url() . 'preview.png';
    $loading_gif_url = be_mu_img_url( 'loading.gif' );

    ?>

    <div class="wrap">
        <?php be_mu_header_super_admin_page( __( 'Modules and Settings', 'beyond-multisite' ), $status_post_request ); ?>
        <div class="be-mu-column-div-left">

            <div class="be-mu-white-box be-mu-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['plugin']; ?> ></div>
                    <?php esc_html_e( 'Plugin Control', 'beyond-multisite' ); ?>
                </b>
                <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                    <input id="be-mu-show-settings-plugin" <?php echo $show_settings_display['plugin']; ?>
                        class="button" onclick="beyondMultisiteShowSettings( 'plugin' )" type="button"
                        value="<?php esc_attr_e( 'Show Settings', 'beyond-multisite' ); ?>" />
                    <input id="be-mu-hide-settings-plugin" <?php echo $hide_settings_display['plugin']; ?>
                        class="button" onclick="beyondMultisiteHideSettings( 'plugin' )" type="button"
                        value="<?php esc_attr_e( 'Hide Settings', 'beyond-multisite' ); ?>" />
                    <?php echo $status_button_module['plugin']; ?>
                    <?php wp_nonce_field( 'be-mu-plugin-status-form-nonce-action', 'be-mu-plugin-status-form-nonce-name' ); ?>
                </form>
                <div id="be-mu-plugin-description-box" class="be-mu-description-box <?php echo $description_display['plugin']; ?>">
                    <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-plugin">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'plugin-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-plugin-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'plugin', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-plugin-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'plugin', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-plugin-main-features">
                                    <li><?php esc_html_e( 'Network disable plugins to hide them from site administrators', 'beyond-multisite' ); ?></li>
                                    <li><?php esc_html_e( 'Site enable plugins to allow access to only some sites', 'beyond-multisite' ); ?></li>
                                    <li><?php esc_html_e( 'Bulk activate/deactivate plugins on all or some sites in the network', 'beyond-multisite' ); ?></li>
                                </ul>
                                <ul id="be-mu-plugin-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['plugin']; ?>
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'To network disable or bulk activate/deactivate plugins, visit the %1$sPlugins%2$s page and '
                                                . 'use the links under a chosen plugin', 'beyond-multisite' ),
                                            '<a href="' . esc_url( network_admin_url( 'plugins.php' ) ) . '">',
                                            '</a>'
                                        );
                                        ?>
                                    </li>
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'To site enable plugins, on the %1$sSites%2$s page, click "Edit" to edit a site, '
                                                . 'and then click "Plugins"', 'beyond-multisite' ),
                                            '<a href="' . esc_url( network_admin_url( 'sites.php' ) ) . '">',
                                            '</a>'
                                        );
                                        ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="be-mu-plugin-settings-box" class="be-mu-settings-box <?php echo $settings_display['plugin']; ?>" >
                    <div class="be-mu-settings-table-div">
                        <div class="be-mu-settings-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-plugin">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'plugin-square.jpg' ) ); ?>" />
                            </div>
                            <form class="be-mu-plugin-settings-form be-mu-settings-form" method="post" action="<?php echo $settings_url; ?>">
                                <div class="be-mu-column-settings-full">
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-plugin-network-disable-new-plugins' ); ?>
                                            <label for="be-mu-plugin-network-disable-new-plugins">
                                                <?php
                                                    esc_html_e( 'Automatically network disable new plugins', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                        esc_html_e( 'Hint: When you add a new plugin, it will be network disabled by default. This will not '
                                                            . 'change the settings for currently installed plugins.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="be-mu-settings-bottom">
                                    <?php echo $status_module_in_settings['plugin']; ?>
                                    <div class="be-mu-right">
                                        <input class='button button-primary be-mu-right' name='be-mu-update-plugin-settings' type='submit'
                                            value='<?php esc_attr_e( 'Update Settings', 'beyond-multisite' ); ?>' />
                                        <span class='be-mu-help-link'>
                                            <a href="https://nikolaydev.com/beyond-multisite-documentation/plugin-control/" target="_blank">
                                                <?php esc_html_e( 'Help', 'beyond-multisite' ); ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <?php wp_nonce_field( 'be-mu-plugin-settings-form-nonce-action', 'be-mu-plugin-settings-form-nonce-name' ); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="be-mu-white-box be-mu-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['copy']; ?> ></div>
                    <?php esc_html_e( 'Copy Maker', 'beyond-multisite' ); ?>
                </b>
                <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                    <input id="be-mu-show-settings-copy" <?php echo $show_settings_display['copy']; ?>
                        class="button" onclick="beyondMultisiteShowSettings( 'copy' )" type="button"
                        value="<?php esc_attr_e( 'Show Settings', 'beyond-multisite' ); ?>" />
                    <input id="be-mu-hide-settings-copy" <?php echo $hide_settings_display['copy']; ?>
                        class="button" onclick="beyondMultisiteHideSettings( 'copy' )" type="button"
                        value="<?php esc_attr_e( 'Hide Settings', 'beyond-multisite' ); ?>" />
                    <?php echo $status_button_module['copy']; ?>
                    <?php wp_nonce_field( 'be-mu-copy-status-form-nonce-action', 'be-mu-copy-status-form-nonce-name' ); ?>
                </form>
                <div id="be-mu-copy-description-box" class="be-mu-description-box <?php echo $description_display['copy']; ?>">
                    <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-copy">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'copy-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-copy-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'copy', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-copy-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'copy', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-copy-main-features">
                                    <li><?php esc_html_e( 'Copy a site and paste it into another site', 'beyond-multisite' ); ?></li>
                                    <li><?php esc_html_e( 'Replace new sites with a copy of a template site upon their creation', 'beyond-multisite' ); ?></li>
                                </ul>
                                <ul id="be-mu-copy-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['copy']; ?>
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'Visit the %1$sSites%2$s page and click "Copy" under a chosen site', 'beyond-multisite' ),
                                            '<a href="' . esc_url( network_admin_url( 'sites.php' ) ) . '">',
                                            '</a>'
                                        );
                                        ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="be-mu-copy-settings-box" class="be-mu-settings-box <?php echo $settings_display['copy']; ?>" >
                    <div class="be-mu-settings-table-div">
                        <div class="be-mu-settings-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-copy">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'copy-square.jpg' ) ); ?>" />
                            </div>
                            <form class="be-mu-copy-settings-form be-mu-settings-form" method="post" action="<?php echo $settings_url; ?>">
                                <div class="be-mu-column-settings-full">
                                    <p>
                                        <label for="be-mu-copy-store-logs">
                                            <?php esc_html_e( 'How many logs to store:', 'beyond-multisite' ); ?>
                                        </label>
                                        <?php
                                        be_mu_setting_select(
                                            'be-mu-copy-store-logs',
                                            array( 'Last 10', 'Last 20', 'Last 50', 'Last 100', 'Last 500', 'All' ),
                                            array(
                                                sprintf( __( 'Last %d', 'beyond-multisite' ), 10 ),
                                                sprintf( __( 'Last %d', 'beyond-multisite' ), 20 ),
                                                sprintf( __( 'Last %d', 'beyond-multisite' ), 50 ),
                                                sprintf( __( 'Last %d', 'beyond-multisite' ), 100 ),
                                                sprintf( __( 'Last %d', 'beyond-multisite' ), 500 ),
                                                __( 'All', 'beyond-multisite' ),
                                            )
                                        );
                                        ?>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php
                                                esc_html_e( 'Hint: The logs contain a list of the taken actions during the process of copying a site. '
                                                    . 'Viewing the logs helps with debugging. They are stored in the database and could become big in size '
                                                    . 'if a lot of files were copied.', 'beyond-multisite' );
                                                ?>
                                            </span>
                                        </span>
                                    </p>
                                    <hr class="be-mu-settings-line">
                                    <p>
                                        <b class="be-mu-settings-section-title"><?php esc_html_e( 'Normal site copy settings', 'beyond-multisite' ); ?></b>
                                    </p>
                                    <p>
                                        <?php
                                        esc_html_e( 'The settings below affect the copy process that '
                                            . 'starts when you manually click the link to copy a site and the button "Copy Site".', 'beyond-multisite' );
                                        ?>
                                    </p>
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-site-title' ); ?>
                                            <label for="be-mu-copy-site-title">
                                                <?php
                                                esc_html_e( 'Copy the title of the site', 'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-site-email' ); ?>
                                            <label for="be-mu-copy-site-email">
                                                <?php
                                                esc_html_e( 'Copy the admin email address of the site', 'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                    </ul>
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-site-no-posts' ); ?>
                                            <label for="be-mu-copy-site-no-posts">
                                                <?php
                                                esc_html_e( 'Do not copy posts *', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: At the end of the process of copying the site, '
                                                        . 'all copied posts with post type "post" will be deleted from the newly created site. '
                                                        . 'This does not affect media files.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-site-no-pages' ); ?>
                                            <label for="be-mu-copy-site-no-pages">
                                                <?php
                                                esc_html_e( 'Do not copy pages *', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: At the end of the process of copying the site, '
                                                        . 'all copied posts with post type "page" will be deleted from the newly created site. '
                                                        . 'This does not affect media files.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-site-no-categories' ); ?>
                                            <label for="be-mu-copy-site-no-categories">
                                                <?php
                                                esc_html_e( 'Do not copy categories *', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: At the end of the process of copying the site, '
                                                        . 'all copied terms from the taxonomy "category" will be deleted from the newly created site.',
                                                        'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-site-no-tags' ); ?>
                                            <label for="be-mu-copy-site-no-tags">
                                                <?php
                                                esc_html_e( 'Do not copy tags *', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: At the end of the process of copying the site, '
                                                        . 'all copied terms from the taxonomy "post_tag" will be deleted from the newly created site.',
                                                        'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-site-no-media' ); ?>
                                            <label for="be-mu-copy-site-no-media">
                                                <?php
                                                esc_html_e( 'Do not copy media files **', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: The uploaded files are not copied, but the ones in the destination '
                                                        . 'site are still deleted. At the end of the process of copying the site, '
                                                        . 'all copied posts with post type "attachment" will be deleted from the newly created site.',
                                                        'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                    </ul>
                                    <hr class="be-mu-settings-line">
                                    <p>
                                        <b class="be-mu-settings-section-title"><?php esc_html_e( 'Template site copy settings', 'beyond-multisite' ); ?></b>
                                    </p>
                                    <p>
                                        <?php
                                        esc_html_e( 'You can choose a site to be automatically copied into new sites upon their creation. '
                                            . 'This does not change existing sites. The settings below are about sites copied upon creation.', 'beyond-multisite' );
                                        ?>
                                    </p>
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-site-enable' ); ?>
                                            <label for="be-mu-copy-template-site-enable">
                                                <?php esc_html_e( 'Automatically replace new sites with a copy of the default template site upon their creation',
                                                    'beyond-multisite' ); ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: This enables the feature', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-site-skip-super' ); ?>
                                            <label for="be-mu-copy-template-site-skip-super">
                                                <?php
                                                esc_html_e( 'Do not replace automatically sites created by a Super Administrator', 'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                    </ul>
                                    <p>
                                        <label for="be-mu-copy-template-site-id">
                                            <?php esc_html_e( 'Site ID of the default template site:', 'beyond-multisite' ); ?>
                                        </label>
                                        <?php be_mu_setting_input_text( 'be-mu-copy-template-site-id' ); ?>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php
                                                esc_html_e( 'Hint: Enter the site ID of the site that will be copied into new sites. '
                                                    . 'Cannot be the main site.', 'beyond-multisite' );
                                                ?>
                                            </span>
                                        </span>
                                        <br>
                                        <span id="be-mu-copy-enter-template-site-id" class="be-mu-display-none be-mu-red">
                                            <?php esc_html_e( 'Enter a site ID before updating the settings. Cannot be the main site.', 'beyond-multisite' ); ?>
                                        </span>
                                    </p>
                                    <p>
                                        <label for="be-mu-copy-template-site-time">
                                            <?php
                                            esc_html_e( 'Interrupt and postpone the rest of the copy process if it takes more than:', 'beyond-multisite' );
                                            ?>
                                        </label>
                                        <?php
                                        be_mu_setting_select(
                                            'be-mu-copy-template-site-time',
                                            array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25 ),
                                            array(
                                                sprintf( __( '%d second', 'beyond-multisite' ), 1 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 2 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 3 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 4 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 5 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 6 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 7 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 8 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 9 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 10 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 11 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 12 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 13 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 14 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 15 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 16 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 17 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 18 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 19 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 20 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 21 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 22 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 23 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 24 ),
                                                sprintf( __( '%d seconds', 'beyond-multisite' ), 25 ),
                                            )
                                        );
                                        ?>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php
                                                esc_html_e( 'Hint: Right after the new site is created (in the same request), the plugin '
                                                    . 'will start replacing it with a copy of the template site. Usually this is very fast, '
                                                    . 'but if the template site is bigger or the server is slower, it may require more '
                                                    . 'time than what would be comfortable for the user to wait (or also than what is '
                                                    . 'available in one request before being stopped by the server). In this case the copy '
                                                    . 'process will continue from where it left of when the new site is visited for the '
                                                    . 'first time. The visitor will see a message, and the site will be unusable until the '
                                                    . 'copying process is completed. You can choose the amount of time to spend copying in '
                                                    . 'the initial request, before deciding to interrupt the process and postpone it to be '
                                                    . 'finished on the first visit of the new site.', 'beyond-multisite' );
                                                ?>
                                            </span>
                                        </span>
                                    </p>
                                    <p>
                                        <?php
                                        be_mu_setting_radio(
                                            'be-mu-copy-template-site-users',
                                            array( 'skip', 'add', 'replace' ),
                                            array(
                                                __( 'Do not copy the users from the template site', 'beyond-multisite' ),
                                                __( 'Copy these users from the template site, that do not exist in the new site', 'beyond-multisite' ),
                                                __( 'Replace the existing users in the new site with all the users from the template site', 'beyond-multisite' ),
                                            )
                                        );
                                        ?>
                                    </p>
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-site-title' ); ?>
                                            <label for="be-mu-copy-template-site-title">
                                                <?php
                                                esc_html_e( 'Copy the title of the template site', 'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-site-email' ); ?>
                                            <label for="be-mu-copy-template-site-email">
                                                <?php
                                                esc_html_e( 'Copy the admin email address of the template site', 'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-site-notify-error' ); ?>
                                            <label for="be-mu-copy-template-site-notify-error">
                                                <?php
                                                esc_html_e( 'Notify the network administrator via email if there was an error '
                                                    . 'during the copy process', 'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                    </ul>
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-no-posts' ); ?>
                                            <label for="be-mu-copy-template-no-posts">
                                                <?php
                                                esc_html_e( 'Do not copy posts *', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: At the end of the process of copying the site, '
                                                        . 'all copied posts with post type "post" will be deleted from the newly created site. '
                                                        . 'This does not affect media files.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-no-pages' ); ?>
                                            <label for="be-mu-copy-template-no-pages">
                                                <?php
                                                esc_html_e( 'Do not copy pages *', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: At the end of the process of copying the site, '
                                                        . 'all copied posts with post type "page" will be deleted from the newly created site. '
                                                        . 'This does not affect media files.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-no-categories' ); ?>
                                            <label for="be-mu-copy-template-no-categories">
                                                <?php
                                                esc_html_e( 'Do not copy categories *', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: At the end of the process of copying the site, '
                                                        . 'all copied terms from the taxonomy "category" will be deleted from the newly created site.',
                                                        'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-no-tags' ); ?>
                                            <label for="be-mu-copy-template-no-tags">
                                                <?php
                                                esc_html_e( 'Do not copy tags *', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: At the end of the process of copying the site, '
                                                        . 'all copied terms from the taxonomy "post_tag" will be deleted from the newly created site.',
                                                        'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-copy-template-no-media' ); ?>
                                            <label for="be-mu-copy-template-no-media">
                                                <?php
                                                esc_html_e( 'Do not copy media files **', 'beyond-multisite' );
                                                ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: The uploaded files are not copied, but the ones in the destination site '
                                                        . 'are still deleted. At the end of the process of copying the site, '
                                                        . 'all copied posts with post type "attachment" will be deleted from the newly created site.',
                                                        'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                    </ul>
                                    <hr class="be-mu-settings-line">
                                    <p>
                                        <b class="be-mu-settings-section-title"><?php esc_html_e( 'WP-CLI Command', 'beyond-multisite' ); ?></b>
                                    </p>
                                    <p>
                                        <?php
                                        esc_html_e( 'You can also use the following command to copy a site:', 'beyond-multisite' );
                                        ?>
                                    </p>
                                    <p>
                                        <?php
                                        echo esc_html( 'be-mu-copy-site --from=<site-id> --to=<site-id> --for=<seconds>' );
                                        ?>
                                    </p>
                                    <p>
                                        <?php
                                        esc_html_e( 'The command has 3 required arguments: the site ID to copy from, the site ID to paste into, '
                                            . 'and the number of seconds to wait until it stops and leaves the process to be finished later '
                                            . 'when the site we paste into is visited for the first time.', 'beyond-multisite' );
                                        ?>
                                    </p>
                                    <p>
                                        <b>
                                            <?php
                                            esc_html_e( 'The rest of the settings will be used from the "Template site copy settings" above!',
                                                'beyond-multisite' );
                                            ?>
                                        </b>
                                    </p>
                                    <hr class="be-mu-settings-line">
                                    <ul>
                                        <li>
                                            <?php
                                            esc_html_e( '* Still copies them, but it deletes them at the end.', 'beyond-multisite' );
                                            ?>
                                        </li>
                                        <li>
                                            <?php
                                            esc_html_e( '** Actual files are not copied, attachment posts are still copied, but it '
                                                . 'deletes them at the end.', 'beyond-multisite' );
                                            ?>
                                        </li>
                                    </ul>
                                </div>
                                <div class="be-mu-settings-bottom">
                                    <?php echo $status_module_in_settings['copy']; ?>
                                    <div class="be-mu-right">
                                        <input class='button button-primary be-mu-right' name='be-mu-update-copy-settings' type='submit'
                                            value='<?php esc_attr_e( 'Update Settings', 'beyond-multisite' ); ?>' />
                                        <span class='be-mu-help-link'>
                                            <a href="https://nikolaydev.com/beyond-multisite-documentation/copy-maker/" target="_blank">
                                                <?php esc_html_e( 'Help', 'beyond-multisite' ); ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <?php wp_nonce_field( 'be-mu-copy-settings-form-nonce-action', 'be-mu-copy-settings-form-nonce-name' ); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="be-mu-white-box be-mu-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['insert']; ?> ></div>
                    <?php esc_html_e( 'Insert HTML', 'beyond-multisite' ); ?>
                </b>
                <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                    <input id="be-mu-show-settings-insert" <?php echo $show_settings_display['insert']; ?>
                        class="button" onclick="beyondMultisiteShowSettings( 'insert' )" type="button"
                        value="<?php esc_attr_e( 'Show Settings', 'beyond-multisite' ); ?>" />
                    <input id="be-mu-hide-settings-insert" <?php echo $hide_settings_display['insert']; ?>
                        class="button" onclick="beyondMultisiteHideSettings( 'insert' )" type="button"
                        value="<?php esc_attr_e( 'Hide Settings', 'beyond-multisite' ); ?>" />
                    <?php echo $status_button_module['insert']; ?>
                    <?php wp_nonce_field( 'be-mu-insert-status-form-nonce-action', 'be-mu-insert-status-form-nonce-name' ); ?>
                </form>
                <div id="be-mu-insert-description-box" class="be-mu-description-box <?php echo $description_display['insert']; ?>">
                    <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-insert">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'insert-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-insert-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'insert', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-insert-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'insert', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-insert-main-features">
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'Insert custom HTML code before the closing %1$s tag or the %2$s tag', 'beyond-multisite' ),
                                            '&lt;/head&gt;',
                                            '&lt;/body&gt;'
                                        );
                                        ?>
                                    </li>
                                    <li>
                                        <?php
                                        esc_html_e( 'Show the HTML code either on the front-end or the back-end of all or some sites', 'beyond-multisite' );
                                        ?>
                                    </li>
                                </ul>
                                <ul id="be-mu-insert-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['insert']; ?>
                                    <li><?php esc_html_e( 'Click on the "Show Settings" button above', 'beyond-multisite' ); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="be-mu-insert-settings-box" class="be-mu-settings-box <?php echo $settings_display['insert']; ?>" >
                    <div class="be-mu-settings-table-div">
                        <div class="be-mu-settings-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-insert">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'insert-square.jpg' ) ); ?>" />
                            </div>
                            <form class="be-mu-insert-settings-form be-mu-settings-form" method="post" action="<?php echo $settings_url; ?>">
                                <div class="be-mu-column-settings-full">
                                    <p>
                                        <label for="be-mu-insert-head">
                                            <?php printf( esc_html__( 'Insert HTML before %s:', 'beyond-multisite' ), '<b>&lt;/head&gt;</b>' ); ?>
                                        </label>
                                    </p>
                                    <p><?php be_mu_setting_textarea( 'be-mu-insert-head' ); ?></p>
                                    <p>
                                        <label for="be-mu-insert-head-affect-sites-id-option">
                                            <?php esc_html_e( 'Affect sites with:', 'beyond-multisite' ); ?>
                                        </label>
                                        <?php
                                        echo '<br />';
                                        be_mu_setting_select(
                                            'be-mu-insert-head-affect-sites-id-option',
                                            array( 'Any site ID', 'Only these site IDs:', 'All except these site IDs:' ),
                                            array(
                                                __( 'Any site ID (All sites)', 'beyond-multisite' ),
                                                __( 'Only these site IDs:', 'beyond-multisite' ),
                                                __( 'All except these site IDs:', 'beyond-multisite' ),
                                            )
                                        );
                                        echo '<br />';
                                        be_mu_setting_input_text( 'be-mu-insert-head-site-ids' );
                                        ?>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php esc_html_e( 'Accepts: Comma-separated numbers or an empty string.', 'beyond-multisite' ); ?>
                                            </span>
                                        </span>
                                    </p>
                                    <p>
                                        <?php esc_html_e( 'Affect page types:', 'beyond-multisite' ); ?>
                                    </p>
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-insert-head-front-end-theme' ); ?>
                                            <label for="be-mu-insert-head-front-end-theme">
                                                <?php esc_html_e( 'Front-end pages using the theme', 'beyond-multisite' ); ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: The code will be added to all public pages of the selected site(s) '
                                                        . 'that use the theme template. This includes the wp-signup.php page. The wp_head action hook is used.',
                                                        'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-insert-head-front-end-login' ); ?>
                                            <label for="be-mu-insert-head-front-end-login">
                                                <?php esc_html_e( 'Front-end login related pages', 'beyond-multisite' ); ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: The code will be added to all login related pages (login, lost password, reset password) '
                                                        . 'of the selected site(s). The login_head action hook is used.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-insert-head-back-end' ); ?>
                                            <label for="be-mu-insert-head-back-end">
                                                <?php esc_html_e( 'Back-end admin pages', 'beyond-multisite' ); ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: The code will be added to all admin pages '
                                                        . 'of the selected site(s). The admin_head action hook is used.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                    </ul>
                                    <hr class="be-mu-settings-line">
                                    <p>
                                        <label for="be-mu-insert-footer">
                                            <?php printf( esc_html__( 'Insert HTML before %s:', 'beyond-multisite' ), '<b>&lt;/body&gt;</b>' ); ?>
                                        </label>
                                    </p>
                                    <p><?php be_mu_setting_textarea( 'be-mu-insert-footer' ); ?></p>
                                    <p>
                                        <label for="be-mu-insert-footer-affect-sites-id-option">
                                            <?php esc_html_e( 'Affect sites with:', 'beyond-multisite' ); ?>
                                        </label>
                                        <?php
                                        echo '<br />';
                                        be_mu_setting_select(
                                            'be-mu-insert-footer-affect-sites-id-option',
                                            array( 'Any site ID', 'Only these site IDs:', 'All except these site IDs:' ),
                                            array(
                                                __( 'Any site ID (All sites)', 'beyond-multisite' ),
                                                __( 'Only these site IDs:', 'beyond-multisite' ),
                                                __( 'All except these site IDs:', 'beyond-multisite' ),
                                            )
                                        );
                                        echo '<br />';
                                        be_mu_setting_input_text( 'be-mu-insert-footer-site-ids' );
                                        ?>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php esc_html_e( 'Accepts: Comma-separated numbers or an empty string.', 'beyond-multisite' ); ?>
                                            </span>
                                        </span>
                                    </p>
                                    <p>
                                        <?php esc_html_e( 'Affect page types:', 'beyond-multisite' ); ?>
                                    </p>
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-insert-footer-front-end-theme' ); ?>
                                            <label for="be-mu-insert-footer-front-end-theme">
                                                <?php esc_html_e( 'Front-end pages using the theme', 'beyond-multisite' ); ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: The code will be added to all public pages of the selected site(s) '
                                                        . 'that use the theme template. This includes the wp-signup.php page. '
                                                        . 'The wp_footer action hook is used.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-insert-footer-front-end-login' ); ?>
                                            <label for="be-mu-insert-footer-front-end-login">
                                                <?php esc_html_e( 'Front-end login related pages', 'beyond-multisite' ); ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: The code will be added to all login related pages (login, lost password, reset password) '
                                                        . 'of the selected site(s). The login_footer action hook is used.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-insert-footer-back-end' ); ?>
                                            <label for="be-mu-insert-footer-back-end">
                                                <?php esc_html_e( 'Back-end admin pages', 'beyond-multisite' ); ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: The code will be added to all admin pages '
                                                        . 'of the selected site(s). The admin_print_footer_scripts action hook is used.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                    </ul>
                                    <p>&nbsp;</p>
                                </div>
                                <div class="be-mu-settings-bottom">
                                    <?php echo $status_module_in_settings['insert']; ?>
                                    <div class="be-mu-right">
                                        <input class='button button-primary be-mu-right' onclick='beyondMultisiteEncodeSubmitInsert()'
                                            name='be-mu-button-update-insert-settings' type='button'
                                            value='<?php esc_attr_e( 'Update Settings', 'beyond-multisite' ); ?>' />
                                        <span class='be-mu-help-link'>
                                            <a href="https://nikolaydev.com/beyond-multisite-documentation/insert-html/" target="_blank">
                                                <?php esc_html_e( 'Help', 'beyond-multisite' ); ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <?php wp_nonce_field( 'be-mu-insert-settings-form-nonce-action', 'be-mu-insert-settings-form-nonce-name' ); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="be-mu-white-box be-mu-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['email']; ?> ></div>
                    <?php esc_html_e( 'Email Users', 'beyond-multisite' ); ?>
                </b>
                <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                    <input id="be-mu-show-settings-email" <?php echo $show_settings_display['email']; ?>
                        class="button" onclick="beyondMultisiteShowSettings( 'email' )" type="button"
                        value="<?php esc_attr_e( 'Show Settings', 'beyond-multisite' ); ?>" />
                    <input id="be-mu-hide-settings-email" <?php echo $hide_settings_display['email']; ?>
                        class="button" onclick="beyondMultisiteHideSettings( 'email' )" type="button"
                        value="<?php esc_attr_e( 'Hide Settings', 'beyond-multisite' ); ?>" />
                    <?php echo $status_button_module['email']; ?>
                    <?php wp_nonce_field( 'be-mu-email-status-form-nonce-action', 'be-mu-email-status-form-nonce-name' ); ?>
                </form>
                <div id="be-mu-email-description-box" class="be-mu-description-box <?php echo $description_display['email']; ?>">
                    <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-email">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'email-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-email-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'email', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-email-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'email', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-email-main-features">
                                    <li><?php esc_html_e( 'Bulk send emails to all or some users in the network', 'beyond-multisite' ); ?></li>
                                    <li><?php esc_html_e( 'Target users by ID or role in all or some selected sites', 'beyond-multisite' ); ?></li>
                                </ul>
                                <ul id="be-mu-email-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['email']; ?>
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'Visit the %1$sEmail Users%2$s page', 'beyond-multisite' ),
                                            '<a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_email_users' ) ) . '">',
                                            '</a>'
                                        );
                                        ?>
                                   </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="be-mu-email-settings-box" class="be-mu-settings-box <?php echo $settings_display['email']; ?>" >
                    <div class="be-mu-settings-table-div">
                        <div class="be-mu-settings-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-email">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'email-square.jpg' ) ); ?>" />
                            </div>
                            <form class="be-mu-email-settings-form be-mu-settings-form" method="post" action="<?php echo $settings_url; ?>">
                                <div class="be-mu-column-settings-full">
                                    <p>
                                        <label for="be-mu-email-speed">
                                            <?php esc_html_e( 'Maximum email sending speed:', 'beyond-multisite' ); ?>
                                        </label>
                                        <?php
                                        be_mu_setting_select(
                                            'be-mu-email-speed',
                                            array( '240 per hour', '480 per hour', '720 per hour', '960 per hour', '1200 per hour', '1440 per hour',
                                                '1680 per hour', '1920 per hour', '2160 per hour', '2400 per hour', '2640 per hour', '2880 per hour',
                                                '3120 per hour', '3360 per hour', '3600 per hour' ),
                                            array(
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 240 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 480 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 720 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 960 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 1200 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 1440 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 1680 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 1920 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 2160 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 2400 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 2640 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 2880 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 3120 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 3360 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 3600 )
                                            )
                                        );
                                        ?>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php esc_html_e( 'Hint: Ask your hosting provider for your limit', 'beyond-multisite' ); ?>
                                            </span>
                                        </span>
                                    </p>
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-email-unsubscribe-feature' ); ?>
                                            <label for="be-mu-email-unsubscribe-feature">
                                                <?php esc_html_e( 'Allow users to unsubscribe and add the unsubscribe footer to emails', 'beyond-multisite' ); ?>
                                            </label>
                                            <span class="be-mu-tooltip">
                                                <span class="be-mu-info">i</span>
                                                <span class="be-mu-tooltip-text">
                                                    <?php
                                                    esc_html_e( 'Hint: If it is checked, the unsubscribe footer message below will be added at the '
                                                        . 'end of all email messages sent via this module. Users can unsubscribe via the link in '
                                                        . 'the footer or via the drop-down menu added to their profile settings page.', 'beyond-multisite' );
                                                    ?>
                                                </span>
                                            </span>
                                        </li>
                                    </ul>
                                    <p class="be-mu-mbot0">
                                        <label for="be-mu-email-unsubscribe-footer">
                                            <?php esc_html_e( 'Unsubscribe footer message:', 'beyond-multisite' ); ?>
                                        </label>
                                    </p>
                                    <?php be_mu_setting_wp_editor( 'be-mu-email-unsubscribe-footer', 150 ); ?>
                                    <p>
                                        <?php esc_html_e( 'Shortcodes for the unsubscribe footer message:', 'beyond-multisite' ); ?><br />
                                        [unsubscribe_url] -
                                        <?php esc_html_e( 'The URL of the page that unsubscribes the user automatically when visited.', 'beyond-multisite' ); ?>
                                        <br />
                                        [network_site_url] -
                                        <?php esc_html_e( 'The URL of the main network site.', 'beyond-multisite' ); ?><br />
                                    </p>
                                </div>
                                <div class="be-mu-settings-bottom">
                                    <?php echo $status_module_in_settings['email']; ?>
                                    <div class="be-mu-right">
                                        <input class='button button-primary be-mu-right' name='be-mu-update-email-settings' type='submit'
                                            value='<?php esc_attr_e( 'Update Settings', 'beyond-multisite' ); ?>' />
                                        <span class='be-mu-help-link'>
                                            <a href="https://nikolaydev.com/beyond-multisite-documentation/email-users/" target="_blank">
                                                <?php esc_html_e( 'Help', 'beyond-multisite' ); ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <?php wp_nonce_field( 'be-mu-email-settings-form-nonce-action', 'be-mu-email-settings-form-nonce-name' ); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="be-mu-white-box be-mu-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['captcha']; ?> ></div>
                    <?php esc_html_e( 'Captcha', 'beyond-multisite' ); ?>
                </b>
                <?php
                if ( extension_loaded( 'gd' ) ) {
                    ?>
                    <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                        <input id="be-mu-show-settings-captcha" <?php echo $show_settings_display['captcha']; ?>
                            class="button" onclick="beyondMultisiteShowSettings( 'captcha' )" type="button"
                            value="<?php esc_attr_e( 'Show Settings', 'beyond-multisite' ); ?>" />
                        <input id="be-mu-hide-settings-captcha" <?php echo $hide_settings_display['captcha']; ?>
                            class="button" onclick="beyondMultisiteHideSettings( 'captcha' )" type="button"
                            value="<?php esc_attr_e( 'Hide Settings', 'beyond-multisite' ); ?>" />
                        <?php echo $status_button_module['captcha']; ?>
                        <?php wp_nonce_field( 'be-mu-captcha-status-form-nonce-action', 'be-mu-captcha-status-form-nonce-name' ); ?>
                    </form>
                    <?php
                } else {
                    be_mu_set_or_make_setting( 'be-mu-captcha-status-module', 'off' );
                    ?>
                    <div class="be-mu-red be-mu-clear"><?php esc_html_e( 'Error: The GD PHP extension is not found on your server. '
                        . 'It is required for the Captcha module to work. Please contact your hosting '
                        . 'provider about this.', 'beyond-multisite' ); ?></div>
                    <?php
                }
                ?>
                <div id="be-mu-captcha-description-box" class="be-mu-description-box <?php echo $description_display['captcha']; ?>">
                    <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-captcha">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'captcha-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-captcha-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'captcha', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-captcha-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'captcha', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-captcha-main-features">
                                    <li>
                                        <?php
                                        esc_html_e( 'Protect the WordPress forms from spam bots with a customizable captcha', 'beyond-multisite' );
                                        ?>
                                    </li>
                                    <li>
                                        <?php
                                        esc_html_e( 'Choose the captcha size, character count, character set, and which forms to protect', 'beyond-multisite' );
                                        ?>
                                    </li>
                                </ul>
                                <ul id="be-mu-captcha-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['captcha']; ?>
                                    <li><?php esc_html_e( 'Click on the "Show Settings" button above', 'beyond-multisite' ); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="be-mu-captcha-settings-box" class="be-mu-settings-box <?php echo $settings_display['captcha']; ?>" >
                    <div class="be-mu-settings-table-div">
                        <div class="be-mu-settings-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-captcha">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'captcha-square.jpg' ) ); ?>" />
                            </div>
                            <form class="be-mu-captcha-settings-form be-mu-settings-form" method="post" action="<?php echo $settings_url; ?>">
                                <div class="be-mu-column-settings-left">
                                    <?php
                                    global $wpdb;
                                    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
                                    $db_table_captcha = $main_blog_prefix . 'be_mu_captcha';
                                    if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $db_table_captcha . "'" ) !== $db_table_captcha ) {
                                        ?>
                                        <p class="be-mu-red">
                                            <?php
                                            printf(
                                                esc_html__( '%sERROR:%s At least one database table is missing. '
                                                    . 'Please deactivate the plugin and activate it to trigger the database tables creation again.', 'beyond-multisite' ),
                                                    '<b>', '</b>'
                                            );
                                            ?>
                                        </p>
                                        <?php
                                    }
                                    ?>
                                    <p>
                                        <label for="be-mu-captcha-character-set">
                                            <?php esc_html_e( 'Character set:', 'beyond-multisite' ); ?>
                                        </label>
                                        <?php
                                        be_mu_setting_select(
                                            'be-mu-captcha-character-set',
                                            array( 'Numbers', 'Letters', 'Numbers and letters' ),
                                            array(
                                                __( 'Numbers', 'beyond-multisite' ),
                                                __( 'Letters', 'beyond-multisite' ),
                                                __( 'Numbers and letters', 'beyond-multisite' ),
                                            )
                                        );
                                        ?>
                                    </p>
                                    <p>
                                        <label for="be-mu-captcha-characters">
                                            <?php esc_html_e( 'Character count:', 'beyond-multisite' ); ?>
                                        </label>
                                        <?php be_mu_setting_select( 'be-mu-captcha-characters', array( 3, 4, 5 ) ); ?>
                                    </p>
                                    <p>
                                        <label for="be-mu-captcha-height">
                                            <?php esc_html_e( 'Captcha height:', 'beyond-multisite' ); ?>
                                        </label>
                                        <?php be_mu_setting_select( 'be-mu-captcha-height', array( 60, 70, 80, 90, 100, 110, 120 ) ); ?>
                                    </p>
                                    <p>
                                        <?php be_mu_setting_checkbox( 'be-mu-captcha-text-login' ); ?>
                                        <label for="be-mu-captcha-text-login">
                                            <?php esc_html_e( 'Use text captcha on the login form', 'beyond-multisite' ); ?>
                                        </label>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php
                                                esc_html_e( 'Hint: This is less secure but useful if you are getting a lot of brute force '
                                                    . 'requests to the login page and you want to avoid the extra server load of the image captcha generation.',
                                                    'beyond-multisite' );
                                                ?>
                                            </span>
                                        </span>
                                    </p>
                                    <p>
                                        <?php esc_html_e( 'Display captcha on:', 'beyond-multisite' ); ?>
                                    </p>
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-captcha-login' ); ?>
                                            <label for="be-mu-captcha-login">
                                                <?php esc_html_e( 'Login form', 'beyond-multisite' ); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-captcha-lost-password' ); ?>
                                            <label for="be-mu-captcha-lost-password">
                                                <?php esc_html_e( 'Lost password form', 'beyond-multisite' ); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-captcha-reset-password' ); ?>
                                            <label for="be-mu-captcha-reset-password">
                                                <?php esc_html_e( 'Reset password form', 'beyond-multisite' ); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-captcha-user-signup' ); ?>
                                            <label for="be-mu-captcha-user-signup">
                                                <?php esc_html_e( 'User signup form', 'beyond-multisite' ); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-captcha-blog-signup-logged-out' ); ?>
                                            <label for="be-mu-captcha-blog-signup-logged-out">
                                                <?php esc_html_e( 'Blog signup form (logged-out visitors)', 'beyond-multisite' ); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-captcha-blog-signup-logged-in' ); ?>
                                            <label for="be-mu-captcha-blog-signup-logged-in">
                                                <?php esc_html_e( 'Blog signup form (logged-in users)', 'beyond-multisite' ); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-captcha-comment-logged-out' ); ?>
                                            <label for="be-mu-captcha-comment-logged-out">
                                                <?php esc_html_e( 'Comment form (logged-out visitors)', 'beyond-multisite' ); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-captcha-comment-logged-in' ); ?>
                                            <label for="be-mu-captcha-comment-logged-in">
                                                <?php esc_html_e( 'Comment form (logged-in users)', 'beyond-multisite' ); ?>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                                <div class="be-mu-column-settings-right">
                                    <?php
                                    if ( extension_loaded( 'gd' ) ) {
                                        ?>
                                        <p>
                                            <?php esc_html_e( 'Captcha Preview:', 'beyond-multisite' ); ?><br />
                                            <div class="be-mu-contain-captcha-preview-image">
                                                <img id="be-mu-captcha-preview-image" src="<?php echo esc_url( $preview_captcha_url ); ?>"  />
                                            </div>
                                        </p>
                                        <p>
                                            <input class='button' onclick='beyondMultisiteUpdateCaptchaPreview()' type='button'
                                                value='<?php esc_attr_e( 'Update Preview', 'beyond-multisite' ); ?>' />
                                            &nbsp;<img id="be-mu-loading-captcha-preview" src="<?php echo esc_url( $loading_gif_url ); ?>" />
                                        </p>
                                        <?php
                                    } else {
                                        ?>
                                        <p>
                                            <?php esc_html_e( 'Captcha Preview:', 'beyond-multisite' ); ?><br />
                                            <span class="be-mu-red"><?php esc_html_e( 'Error: The GD PHP extension is not found on your server. '
                                                . 'It is required for the Captcha module to work. Please contact your hosting '
                                                . 'provider about this.', 'beyond-multisite' ); ?></span>
                                        </p>
                                        <?php
                                    }
                                    ?>
                                    <p>
                                        <i>
                                            <?php
                                            esc_html_e( 'Note: On some forms the captcha size will be limited to the available space.',
                                                'beyond-multisite' );
                                            ?>
                                        </i>
                                    </p>
                                    <p>
                                        <label for="be-mu-captcha-images-folder">
                                            <?php esc_html_e( 'Store images in the:', 'beyond-multisite' ); ?>
                                        </label>
                                        <?php
                                        be_mu_setting_select(
                                            'be-mu-captcha-images-folder',
                                            array( 'Uploads folder', 'Plugin folder' ),
                                            array(
                                                __( 'Uploads folder', 'beyond-multisite' ),
                                                __( 'Plugin folder', 'beyond-multisite' ),
                                            )
                                        );
                                        ?>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php
                                                esc_html_e( 'Hint: If there are any problmes with the captcha images in the uploads folder caused by a '
                                                    . 'CDN or a plugin, you can switch to using the plugin folder instead.', 'beyond-multisite' );
                                                ?>
                                            </span>
                                        </span>
                                    </p>
                                </div>
                                <div class="be-mu-settings-bottom">
                                    <?php echo $status_module_in_settings['captcha']; ?>
                                    <div class="be-mu-right">
                                        <input class='button button-primary be-mu-right' name='be-mu-update-captcha-settings' type='submit'
                                            value='<?php esc_attr_e( 'Update Settings', 'beyond-multisite' ); ?>' />
                                        <span class='be-mu-help-link'>
                                            <a href="https://nikolaydev.com/beyond-multisite-documentation/captcha/" target="_blank">
                                                <?php esc_html_e( 'Help', 'beyond-multisite' ); ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <?php wp_nonce_field( 'be-mu-captcha-settings-form-nonce-action', 'be-mu-captcha-settings-form-nonce-name' ); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="be-mu-column-div-right">

            <div class="be-mu-white-box be-mu-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['clean']; ?> ></div>
                    <?php esc_html_e( 'Cleanup', 'beyond-multisite' ); ?>
                </b>
                <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                    <input id="be-mu-show-settings-clean" <?php echo $show_settings_display['clean']; ?> class="button"
                        onclick="beyondMultisiteShowSettings( 'clean' )" type="button"
                        value="<?php esc_attr_e( 'Show Settings', 'beyond-multisite' ); ?>" />
                    <input id="be-mu-hide-settings-clean" <?php echo $hide_settings_display['clean']; ?> class="button"
                        onclick="beyondMultisiteHideSettings( 'clean' )" type="button"
                        value="<?php esc_attr_e( 'Hide Settings', 'beyond-multisite' ); ?>" />
                    <?php echo $status_button_module['clean']; ?>
                    <?php wp_nonce_field( 'be-mu-clean-status-form-nonce-action', 'be-mu-clean-status-form-nonce-name' ); ?>
                </form>
                <div id="be-mu-clean-description-box" class="be-mu-description-box <?php echo $description_display['clean']; ?>">
                    <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-clean">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'clean-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-clean-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'clean', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-clean-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'clean', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-clean-main-features">
                                    <li><?php esc_html_e( 'Bulk delete comments across the network by chosen criteria', 'beyond-multisite' ); ?></li>
                                    <li><?php esc_html_e( 'Bulk delete revisions across the network by age', 'beyond-multisite' ); ?></li>
                                    <li><?php esc_html_e( 'Bulk delete empty or old sites', 'beyond-multisite' ); ?></li>
                                    <li><?php esc_html_e( 'Bulk delete leftover database tables', 'beyond-multisite' ); ?></li>
                                    <li><?php esc_html_e( 'Bulk delete users without a role', 'beyond-multisite' ); ?></li>
                                </ul>
                                <ul id="be-mu-clean-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['clean']; ?>
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'Visit the %1$sNetwork Cleanup%2$s page', 'beyond-multisite' ),
                                            '<a href="' . esc_url( network_admin_url( 'index.php?page=be_mu_cleanup' ) ) . '">',
                                            '</a>'
                                        );
                                        ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="be-mu-clean-settings-box" class="be-mu-settings-box <?php echo $settings_display['clean']; ?>" >
                    <div class="be-mu-settings-table-div">
                        <div class="be-mu-settings-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-clean">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'clean-square.jpg' ) ); ?>" />
                            </div>
                            <form class="be-mu-clean-settings-form be-mu-settings-form" method="post" action="<?php echo $settings_url; ?>">
                                <div class="be-mu-column-settings-full">
                                    <p>
                                        <b class="be-mu-settings-section-title">
                                            <?php esc_html_e( 'Scheduled site deletion notification settings', 'beyond-multisite' ); ?>
                                        </b>
                                    </p>
                                    <p>
                                        <?php
                                        esc_html_e( 'When you schedule sites to be deleted, the administrators of those sites will be notified via email. '
                                            . 'The settings below are about those email notifications.', 'beyond-multisite' );
                                        ?>
                                    </p>
                                    <p>
                                        <label for="be-mu-clean-email-speed">
                                            <?php esc_html_e( 'Maximum email sending speed:', 'beyond-multisite' ); ?>
                                        </label><br />
                                        <?php
                                        be_mu_setting_select(
                                            'be-mu-clean-email-speed',
                                            array( '240 per hour', '480 per hour', '720 per hour', '960 per hour', '1200 per hour', '1440 per hour',
                                                '1680 per hour', '1920 per hour', '2160 per hour', '2400 per hour', '2640 per hour', '2880 per hour',
                                                '3120 per hour', '3360 per hour', '3600 per hour' ),
                                            array(
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 240 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 480 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 720 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 960 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 1200 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 1440 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 1680 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 1920 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 2160 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 2400 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 2640 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 2880 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 3120 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 3360 ),
                                                sprintf( __( '%d per hour', 'beyond-multisite' ), 3600 )
                                            )
                                        );
                                        ?>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php esc_html_e( 'Hint: Ask your hosting provider for your limit', 'beyond-multisite' ); ?>
                                            </span>
                                        </span>
                                    </p>
                                    <p>
                                        <label for="be-mu-clean-from-email">
                                            <?php esc_html_e( 'From email:', 'beyond-multisite' ); ?>
                                        </label><br />
                                        <?php be_mu_setting_input_text( 'be-mu-clean-from-email' ); ?>
                                    </p>
                                    <p>
                                        <label for="be-mu-clean-from-name">
                                            <?php esc_html_e( 'From name:', 'beyond-multisite' ); ?>
                                        </label><br />
                                        <?php be_mu_setting_input_text( 'be-mu-clean-from-name' ); ?>
                                    </p>
                                    <p>
                                        <label for="be-mu-clean-subject">
                                            <?php esc_html_e( 'Subject:', 'beyond-multisite' ); ?>
                                        </label><br />
                                        <?php be_mu_setting_input_text( 'be-mu-clean-subject' ); ?>
                                    </p>
                                    <p class="be-mu-mbot0">
                                        <label for="be-mu-clean-message">
                                            <?php esc_html_e( 'Message:', 'beyond-multisite' ); ?>
                                        </label>
                                    </p>
                                    <?php be_mu_setting_wp_editor( 'be-mu-clean-message' ); ?>
                                    <p>
                                        <?php esc_html_e( 'Shortcodes for the Message field:', 'beyond-multisite' ); ?><br />
                                        [user_sites] -
                                        <?php esc_html_e( 'A list of links to the sites where the user is an administrator.', 'beyond-multisite' ); ?><br />
                                        [deletion_after_days] -
                                        <?php esc_html_e( 'The number of days of waiting time before the deletion.', 'beyond-multisite' ); ?><br />
                                        [network_site_url] -
                                        <?php esc_html_e( 'The URL of the main network site.', 'beyond-multisite' ); ?><br />
                                    </p>
                                    <p>
                                        <label for="be-mu-clean-test-email"><?php esc_html_e( 'Send test email to:', 'beyond-multisite' ); ?></label>
                                        <?php be_mu_setting_input_text( 'be-mu-clean-test-email' ); ?><br />
                                        <i class="be-mu-hint">
                                            <?php
                                            esc_html_e( 'Hint: Send a test email to one of your emails to see how everything looks like.', 'beyond-multisite' );
                                            ?>
                                        </i>
                                    </p>
                                    <p>
                                        <input class='button' onclick='beyondMultisiteSendTestEmail("clean")' type='button'
                                            value='<?php esc_attr_e( 'Send Test Email', 'beyond-multisite' ); ?>' />
                                        <span id="be-mu-clean-test-email-done-span" class="be-mu-green"></span>
                                        <img id="be-mu-loading-clean-test-email" src="<?php echo esc_url( $loading_gif_url ); ?>" /><br />
                                        <i class="be-mu-hint">
                                            <?php
                                            esc_html_e( 'Hint: First udpate the settings if you have changed them.', 'beyond-multisite' );
                                            ?>
                                        </i>
                                    </p>
                                </div>
                                <div class="be-mu-settings-bottom">
                                    <?php echo $status_module_in_settings['clean']; ?>
                                    <div class="be-mu-right">
                                        <input class='button button-primary be-mu-right' name='be-mu-update-clean-settings' type='submit'
                                            value='<?php esc_attr_e( 'Update Settings', 'beyond-multisite' ); ?>' />
                                        <span class='be-mu-help-link'>
                                            <a href="https://nikolaydev.com/beyond-multisite-documentation/cleanup/" target="_blank">
                                                <?php esc_html_e( 'Help', 'beyond-multisite' ); ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <?php wp_nonce_field( 'be-mu-clean-settings-form-nonce-action', 'be-mu-clean-settings-form-nonce-name' ); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="be-mu-white-box be-mu-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['activated-in']; ?> ></div>
                    <?php esc_html_e( 'Activated in?', 'beyond-multisite' ); ?>
                </b>
                <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                    <?php echo $status_button_module['activated-in']; ?>
                    <?php wp_nonce_field( 'be-mu-activated-in-status-form-nonce-action', 'be-mu-activated-in-status-form-nonce-name' ); ?>
                </form>
                <div id="be-mu-activated-in-description-box" class="be-mu-description-box">
                    <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-activated-in">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'activated-in-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-activated-in-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'activated-in', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-activated-in-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'activated-in', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-activated-in-main-features">
                                    <li><?php esc_html_e( 'See a list of sites where a plugin or a theme is activated in', 'beyond-multisite' ); ?></li>
                                </ul>
                                <ul id="be-mu-activated-in-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['activated-in']; ?>
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'Visit the %1$sPlugins%2$s page or the %3$sThemes%2$s page and click "Activated in?" '
                                                . 'under a chosen plugin or theme',
                                                'beyond-multisite' ),
                                            '<a href="' . esc_url( network_admin_url( 'plugins.php' ) ) . '">',
                                            '</a>',
                                            '<a href="' . esc_url( network_admin_url( 'themes.php' ) ) . '">'
                                        );
                                        ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="be-mu-white-box be-mu-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['pending']; ?> ></div>
                    <?php esc_html_e( 'Pending Users', 'beyond-multisite' ); ?>
                </b>
                <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                    <?php echo $status_button_module['pending']; ?>
                    <?php wp_nonce_field( 'be-mu-pending-status-form-nonce-action', 'be-mu-pending-status-form-nonce-name' ); ?>
                </form>
                <div id="be-mu-pending-description-box" class="be-mu-description-box">
                    <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-pending">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'pending-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-pending-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'pending', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-pending-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'pending', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-pending-main-features">
                                    <li>
                                        <?php
                                        esc_html_e( 'Manage signups that are not yet activated: activate, resend email, or delete', 'beyond-multisite' );
                                        ?>
                                    </li>
                                    <li><?php esc_html_e( 'Search not yet activated signups by username or email', 'beyond-multisite' ); ?></li>
                                </ul>
                                <ul id="be-mu-pending-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['pending']; ?>
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'Visit the %1$sPending Users%2$s page', 'beyond-multisite' ),
                                            '<a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_pending_users' ) ) . '">',
                                            '</a>'
                                        );
                                        ?>
                                   </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="be-mu-white-box be-mu-module-box be-mu-every-second-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['ban']; ?> ></div>
                    <?php esc_html_e( 'Ban Users', 'beyond-multisite' ); ?>
                </b>
                <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                    <input id="be-mu-show-settings-ban" <?php echo $show_settings_display['ban']; ?> class="button"
                        onclick="beyondMultisiteShowSettings( 'ban' )" type="button"
                        value="<?php esc_attr_e( 'Show Settings', 'beyond-multisite' ); ?>" />
                    <input id="be-mu-hide-settings-ban" <?php echo $hide_settings_display['ban']; ?> class="button"
                        onclick="beyondMultisiteHideSettings( 'ban' )" type="button"
                        value="<?php esc_attr_e( 'Hide Settings', 'beyond-multisite' ); ?>" />
                    <?php echo $status_button_module['ban']; ?>
                    <?php wp_nonce_field( 'be-mu-ban-status-form-nonce-action', 'be-mu-ban-status-form-nonce-name' ); ?>
                </form>
                <div id="be-mu-ban-description-box" class="be-mu-description-box <?php echo $description_display['ban']; ?>">
                     <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-ban">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'ban-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-ban-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'ban', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-ban-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'ban', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-ban-main-features">
                                    <li><?php esc_html_e( 'Ban users and their IP address (denies login, signup, and commenting)', 'beyond-multisite' ); ?></li>
                                </ul>
                                <ul id="be-mu-ban-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['ban']; ?>
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'To ban users, visit the %1$sUsers%2$s page and click "Ban" under a chosen user (first the user has '
                                                . 'to login once to detect the IP)', 'beyond-multisite' ),
                                            '<a href="' . esc_url( network_admin_url( 'users.php' ) ) . '">',
                                            '</a>'
                                        );
                                        ?>
                                    </li>
                                    <li>
                                        <?php
                                        printf(
                                            esc_html__( 'To see all banned users, visit the %1$sBanned Users%2$s page', 'beyond-multisite' ),
                                            '<a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' ) ) . '">',
                                            '</a>'
                                        );
                                        ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="be-mu-ban-settings-box" class="be-mu-settings-box <?php echo $settings_display['ban']; ?>" >
                    <div class="be-mu-settings-table-div">
                        <div class="be-mu-settings-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-ban">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'ban-square.jpg' ) ); ?>" />
                            </div>
                            <form class="be-mu-ban-settings-form be-mu-settings-form" method="post" action="<?php echo $settings_url; ?>">
                                <div class="be-mu-column-settings-full">
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-ban-ip-column' ); ?>
                                            <label for="be-mu-ban-ip-column">
                                                <?php
                                                printf(
                                                    esc_html__( 'Add the %1$sLast IP%2$s column on the %3$sUsers%4$s page', 'beyond-multisite' ),
                                                    '<i>',
                                                    '</i>',
                                                    '<a href="' . esc_url( network_admin_url( 'users.php' ) ) . '">',
                                                    '</a>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-ban-status-column' ); ?>
                                            <label for="be-mu-ban-status-column">
                                                <?php
                                                printf(
                                                    esc_html__( 'Add the %1$sBan Status%2$s column on the %3$sUsers%4$s page', 'beyond-multisite' ),
                                                    '<i>',
                                                    '</i>',
                                                    '<a href="' . esc_url( network_admin_url( 'users.php' ) ) . '">',
                                                    '</a>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-ban-show-flags' ); ?>
                                            <label for="be-mu-ban-show-flags">
                                                <?php
                                                printf(
                                                    esc_html__( 'Show a country flag next to the IP address of users', 'beyond-multisite' ),
                                                    '<i>',
                                                    '</i>',
                                                    '<a href="' . esc_url( network_admin_url( 'users.php' ) ) . '">',
                                                    '</a>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                    </ul>
                                    <p>
                                        <b>
                                            <?php esc_html_e( 'IP address detection:', 'beyond-multisite' ); ?>
                                        </b>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php
                                                esc_html_e( 'Hint: In most cases it is best to choose REMOTE_ADDR, which is the only secure '
                                                    . 'option (not easy for the user to spoof the IP address). But if you are using CloudFlare '
                                                    . 'you should choose either Auto or HTTP_CF_CONNECTING_IP. Auto gives priority to '
                                                    . 'HTTP_CF_CONNECTING_IP. If there is a proxy server use '
                                                    . 'HTTP_X_FORWARDED_FOR and you may need to enter the trusted proxy IP addresses below in '
                                                    . 'order to get the correct value here. If the REMOTE_ADDR is valid and not a trusted proxy '
                                                    . 'we will use it even if you choose HTTP_X_FORWARDED_FOR.', 'beyond-multisite' );
                                                ?>
                                            </span>
                                        </span>
                                        <br />
                                        <?php
                                        $ip_auto = be_mu_get_visitor_ip_by_method( 'Auto' );
                                        $ip_remote_addr = be_mu_get_visitor_ip_by_method( 'REMOTE_ADDR' );
                                        $ip_cloudflare = be_mu_get_visitor_ip_by_method( 'HTTP_CF_CONNECTING_IP' );
                                        $ip_x_forwarded_for = be_mu_get_visitor_ip_by_method( 'HTTP_X_FORWARDED_FOR' );
                                        $ip_client_ip = be_mu_get_visitor_ip_by_method( 'HTTP_CLIENT_IP' );
                                        $ip_real_ip = be_mu_get_visitor_ip_by_method( 'HTTP_X_REAL_IP' );
                                        if ( $ip_auto === false ) {
                                            $ip_auto = __( '[Invalid or empty]', 'beyond-multisite' );
                                        }
                                        if ( $ip_remote_addr === false ) {
                                            $ip_remote_addr = __( '[Invalid or empty]', 'beyond-multisite' );
                                        }
                                        if ( $ip_cloudflare === false ) {
                                            $ip_cloudflare = __( '[Invalid or empty]', 'beyond-multisite' );
                                        }
                                        if ( $ip_x_forwarded_for === false ) {
                                            $ip_x_forwarded_for = __( '[Invalid or empty]', 'beyond-multisite' );
                                        }
                                        if ( $ip_client_ip === false ) {
                                            $ip_client_ip = __( '[Invalid or empty]', 'beyond-multisite' );
                                        }
                                        if ( $ip_real_ip === false ) {
                                            $ip_real_ip = __( '[Invalid or empty]', 'beyond-multisite' );
                                        }

                                        // The radio elements to choose the IP detection method
                                        be_mu_setting_radio(
                                            'be-mu-ban-detect-ip-method',
                                            array( 'Auto', 'REMOTE_ADDR', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP' ),
                                            array(
                                                sprintf( __( 'Auto', 'beyond-multisite' ) . ' ' . __( '(Your IP: %s)', 'beyond-multisite' ), $ip_auto ),
                                                sprintf( 'REMOTE_ADDR' . ' ' . __( '(Your IP: %s)', 'beyond-multisite' ), $ip_remote_addr ),
                                                sprintf( 'HTTP_CF_CONNECTING_IP' . ' ' . __( '(Your IP: %s)', 'beyond-multisite' ), $ip_cloudflare ),
                                                sprintf( 'HTTP_X_FORWARDED_FOR' . ' ' . __( '(Your IP: %s)', 'beyond-multisite' )
                                                    . __( ' - Enter trusted proxies below', 'beyond-multisite' ), $ip_x_forwarded_for ),
                                                sprintf( 'HTTP_CLIENT_IP' . ' ' . __( '(Your IP: %s)', 'beyond-multisite' ), $ip_client_ip ),
                                                sprintf( 'HTTP_X_REAL_IP' . ' ' . __( '(Your IP: %s)', 'beyond-multisite' ), $ip_real_ip )
                                            )
                                        );
                                        ?>
                                    </p>
                                    <p>
                                        <label for="be-mu-ban-trusted-proxies">
                                            <?php esc_html_e( 'Trusted proxies (comma-separated IPs):', 'beyond-multisite' ); ?>
                                        </label>
                                        <span class="be-mu-tooltip">
                                            <span class="be-mu-info">i</span>
                                            <span class="be-mu-tooltip-text">
                                                <?php
                                                esc_html_e( 'Hint: These will be removed from the HTTP_X_FORWARDED_FOR value so we can get '
                                                    . 'the correct IP address.', 'beyond-multisite' );
                                                ?>
                                            </span>
                                        </span>
                                        <br />
                                        <?php be_mu_setting_input_text( 'be-mu-ban-trusted-proxies' ); ?>
                                    </p>
                                </div>
                                <div class="be-mu-settings-bottom">
                                    <?php echo $status_module_in_settings['ban']; ?>
                                    <div class="be-mu-right">
                                        <input class='button button-primary be-mu-right' name='be-mu-update-ban-settings' type='submit'
                                            value='<?php esc_attr_e( 'Update Settings', 'beyond-multisite' ); ?>' />
                                        <span class='be-mu-help-link'>
                                            <a href="https://nikolaydev.com/beyond-multisite-documentation/ban-users/" target="_blank">
                                                <?php esc_html_e( 'Help', 'beyond-multisite' ); ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <?php wp_nonce_field( 'be-mu-ban-settings-form-nonce-action', 'be-mu-ban-settings-form-nonce-name' ); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="be-mu-white-box be-mu-module-box">
                <b class="be-mu-module-title">
                    <div <?php echo $status_class_module['improve']; ?> ></div>
                    <?php esc_html_e( 'Improvements', 'beyond-multisite' ); ?>
                </b>
                <form class="be-mu-module-status-form" method="post" action="<?php echo $settings_url; ?>">
                    <input id="be-mu-show-settings-improve" <?php echo $show_settings_display['improve']; ?> class="button"
                        onclick="beyondMultisiteShowSettings( 'improve' )" type="button"
                        value="<?php esc_attr_e( 'Show Settings', 'beyond-multisite' ); ?>" />
                    <input id="be-mu-hide-settings-improve" <?php echo $hide_settings_display['improve']; ?> class="button"
                        onclick="beyondMultisiteHideSettings( 'improve' )" type="button"
                        value="<?php esc_attr_e( 'Hide Settings', 'beyond-multisite' ); ?>" />
                    <?php echo $status_button_module['improve']; ?>
                    <?php wp_nonce_field( 'be-mu-improve-status-form-nonce-action', 'be-mu-improve-status-form-nonce-name' ); ?>
                </form>
                <div id="be-mu-improve-description-box" class="be-mu-description-box <?php echo $description_display['improve']; ?>">
                    <div class="be-mu-description-table-div">
                        <div class="be-mu-description-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-improve">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'improve-square.jpg' ) ); ?>" />
                            </div>
                            <div class="be-mu-module-menu-div-cell">
                                <b class="be-mu-module-tab-link be-mu-main-features" id="be-mu-improve-main-features-link"
                                    onclick="beyondMultisiteModuleMenu( 'improve', 'main-features' )">
                                    <?php esc_html_e( 'Main Features', 'beyond-multisite' ); ?>
                                </b>
                                <b class="be-mu-module-tab-link be-mu-how-to" id="be-mu-improve-how-to-link"
                                    onclick="beyondMultisiteModuleMenu( 'improve', 'how-to' )">
                                    <?php esc_html_e( 'Quick start', 'beyond-multisite' ); ?>
                                </b>
                                <ul id="be-mu-improve-main-features">
                                    <li>
                                        <?php
                                        esc_html_e( 'Various small improvements that save you some time or change some features', 'beyond-multisite' );
                                        ?>
                                    </li>
                                </ul>
                                <ul id="be-mu-improve-how-to" class="be-mu-module-how-to">
                                    <?php echo $quick_start_step_one['improve']; ?>
                                    <li><?php esc_html_e( 'Click on the "Show Settings" button above', 'beyond-multisite' ); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="be-mu-improve-settings-box" class="be-mu-settings-box <?php echo $settings_display['improve']; ?>" >
                    <div class="be-mu-settings-table-div">
                        <div class="be-mu-settings-row-div">
                            <div class="be-mu-module-icon-div-cell be-mu-module-icon-div-improve">
                                <img class="be-mu-module-icon" src="<?php echo esc_url( be_mu_img_url( 'improve-square.jpg' ) ); ?>" />
                            </div>
                            <form class="be-mu-improve-settings-form be-mu-settings-form" method="post" action="<?php echo $settings_url; ?>">
                                <div class="be-mu-column-settings-full">
                                    <ul>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-user-id-column' ); ?>
                                            <label for="be-mu-improve-user-id-column">
                                                <?php
                                                printf(
                                                    esc_html__( 'Add the %1$sID%2$s column on the %3$sUsers%4$s page', 'beyond-multisite' ),
                                                    '<i>',
                                                    '</i>',
                                                    '<a href="' . esc_url( network_admin_url( 'users.php' ) ) . '">',
                                                    '</a>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-user-sites-dash-action' ); ?>
                                            <label for="be-mu-improve-user-sites-dash-action">
                                                <?php
                                                printf(
                                                    esc_html__( 'Add the %1$sDashboard%2$s action link in the %1$sSites%2$s column on the %3$sUsers%4$s page',
                                                    'beyond-multisite' ),
                                                    '<i>',
                                                    '</i>',
                                                    '<a href="' . esc_url( network_admin_url( 'users.php' ) ) . '">',
                                                    '</a>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-site-id-column' ); ?>
                                            <label for="be-mu-improve-site-id-column">
                                                <?php
                                                printf(
                                                    esc_html__( 'Add the %1$sID%2$s column on the %3$sSites%4$s page', 'beyond-multisite' ),
                                                    '<i>',
                                                    '</i>',
                                                    '<a href="' . esc_url( network_admin_url( 'sites.php' ) ) . '">',
                                                    '</a>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-hide-plugin-meta' ); ?>
                                            <label for="be-mu-improve-hide-plugin-meta">
                                                <?php
                                                    esc_html_e( 'Hide plugin meta information (version, author, etc.) from site administrators',
                                                        'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-network-plugin-admin-menus' ); ?>
                                            <label for="be-mu-improve-network-plugin-admin-menus">
                                                <?php
                                                esc_html_e( 'Add quick links to network plugins menu items under the Plugins menu on all sites '
                                                    . '(visible only for Super Admins)', 'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-network-theme-admin-menus' ); ?>
                                            <label for="be-mu-improve-network-theme-admin-menus">
                                                <?php
                                                esc_html_e( 'Add quick links to network themes menu items under the Themes menu on all sites '
                                                    . '(visible only for Super Admins)', 'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-password-change-email' ); ?>
                                            <label for="be-mu-improve-password-change-email">
                                                <?php
                                                printf(
                                                    esc_html__( 'Disable the %1$sPassword Changed%2$s notification email that is sent to the '
                                                        . 'network admin when a user password is changed', 'beyond-multisite' ),
                                                    '<i>',
                                                    '</i>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-wp-signup-css-class' ); ?>
                                            <label for="be-mu-improve-wp-signup-css-class">
                                                <?php
                                                printf(
                                                    esc_html__( 'Add the %1$sbe-mu-wp-signup-class%2$s CSS class to the body tag on the wp-signup.php page',
                                                        'beyond-multisite' ),
                                                    '<i>',
                                                    '</i>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-drop-leftover-tables' ); ?>
                                            <label for="be-mu-improve-drop-leftover-tables">
                                                <?php
                                                printf(
                                                    esc_html__( 'Delete any leftover database tables when a site is permanently deleted '
                                                        . '(%sWarning! First try the same feature in the Cleanup module to see if there '
                                                        . 'are any speed issues on your server%s).', 'beyond-multisite' ),
                                                    '<i>','</i>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-delete-leftover-folder' ); ?>
                                            <label for="be-mu-improve-delete-leftover-folder">
                                                <?php
                                                esc_html_e( 'Delete the leftover empty uploads folder when a site is permanently deleted', 'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-show-noindex-status' ); ?>
                                            <label for="be-mu-improve-show-noindex-status">
                                                <?php
                                                esc_html_e( 'Show an icon in the admin bar and in "My Sites" for sites that discourage search engine indexing',
                                                    'beyond-multisite' );
                                                ?>
                                            </label>
                                        </li>
                                        <li>
                                            <?php be_mu_setting_checkbox( 'be-mu-improve-user-sites-role-icon' ); ?>
                                            <label for="be-mu-improve-user-sites-role-icon">
                                                <?php
                                                printf(
                                                    esc_html__( 'Add a role icon in the %1$sSites%2$s column on the %3$sUsers%4$s page',
                                                    'beyond-multisite' ),
                                                    '<i>',
                                                    '</i>',
                                                    '<a href="' . esc_url( network_admin_url( 'users.php' ) ) . '">',
                                                    '</a>'
                                                );
                                                ?>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                                <div class="be-mu-settings-bottom">
                                    <?php echo $status_module_in_settings['improve']; ?>
                                    <div class="be-mu-right">
                                        <input class='button button-primary be-mu-right' name='be-mu-update-improve-settings' type='submit'
                                            value='<?php esc_attr_e( 'Update Settings', 'beyond-multisite' ); ?>' />
                                        <span class='be-mu-help-link'>
                                            <a href="https://nikolaydev.com/beyond-multisite-documentation/improvements/" target="_blank">
                                                <?php esc_html_e( 'Help', 'beyond-multisite' ); ?>
                                            </a>
                                        </span>
                                    </div>      
                                </div>
                                <?php wp_nonce_field( 'be-mu-improve-settings-form-nonce-action', 'be-mu-improve-settings-form-nonce-name' ); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <?php

}
