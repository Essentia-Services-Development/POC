<?php

/**
* @package ZephyrProjectManager
*
* Plugin Name:  Zephyr Project Manager
* Description:  A modern project manager for WordPress to keep track of all your projects from within WordPress.
* Plugin URI:   https://zephyr-one.com
* Version:      3.3.96
* Author:       Dylan James
* License:      GPLv2 or later
* Text Domain:  zephyr-project-manager
* Domain Path: /languages
*/

if (!defined('ABSPATH')) die;

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

global $wpdb;
global $zpm_settings;
global $zpmMessages;

use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\Api\Emails;
use ZephyrProjectManager\Base\Activate;
use ZephyrProjectManager\Base\Deactivate;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\Init;
use ZephyrProjectManager\Core\Controllers\MessageController;

define('ZPM_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('ZPM_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('ZPM_PLUGIN', plugin_basename( __FILE__ ));
define('ZPM_PROJECTS_TABLE', $wpdb->prefix . 'zpm_projects');
define('ZPM_TASKS_TABLE', $wpdb->prefix . 'zpm_tasks');
define('ZPM_MESSAGES_TABLE', $wpdb->prefix . 'zpm_messages');
define('ZPM_CATEGORY_TABLE', $wpdb->prefix . 'zpm_categories');
define('ZPM_ACTIVITY_TABLE', $wpdb->prefix . 'zpm_activity');
define('ZEPHYR_PRO_LINK', 'https://zephyr-one.com/purchase-pro/');
define('ZPM_REQUIRED_PRO_VERSION', '3.3.0' );

require_once(ZPM_PLUGIN_PATH . 'includes/functions.php');

global $zpmSettings;
$zpmMessages = new MessageController();
$zpmSettings = Utillities::general_settings();

function activate_project_manager_plugin($networkwide) {
    if (is_multisite() && $networkwide) {
        global $wpdb;
        $blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ((array) $blogIDs as $blog_id) {
            switch_to_blog( $blog_id );
            Activate::activate();

            if (function_exists('zephyr_project_manager_activate_pro')) {
                zephyr_project_manager_activate_pro();
            }

            restore_current_blog();
        }
    } else {
        Activate::activate();

        if (function_exists('zephyr_project_manager_activate_pro')) {
            zephyr_project_manager_activate_pro();
        }
    }
}

register_activation_hook( __FILE__, 'activate_project_manager_plugin' );

function deactivate_project_manager_plugin() {
    Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_project_manager_plugin' );

if (class_exists('ZephyrProjectManager\\Init')) {
    Utillities::install_missing_columns();
    Init::register_services();
    zpm_add_scheduled_events();
    // $currentVersion = Zephyr::getPluginVersion();
    // $databaseVersion = get_option( 'zpm_database_version', 1 );
    // if (version_compare($currentVersion, $databaseVersion, '>')) {
    //     Activate::activate();
    // }
}

function zpm_plugin_loaded() {
    global $zpm_settings;

    $version = zpm_get_version();
    $db_version = get_option( 'zpm_db_version', '0' );

    if (version_compare($db_version, $version, '<')) {
        Activate::installTables();
        update_option( 'zpm_db_version', $version );
    }

    $locale = apply_filters('plugin_locale', get_locale(), 'zephyr-project-manager');
    $language = substr($locale, 0, 2);

    if ($language == 'en') {
        $locale = 'en_EN';
    }


    // var_dump($loaded);
    Utillities::check_save_general_settings();
    $tasks = new Tasks();
    $zpm_settings = Utillities::general_settings();
    $pluginTextdomainLoaded = load_plugin_textdomain('zephyr-project-manager', FALSE, basename(dirname(__FILE__)) . '/languages/');
    // $theme = load_plugin_textdomain('zephyr-project-manager', FALSE, 'languages/zephyr-project-manager-fr_FR.mo');

    // if ($zpm_settings['label_type'] == 'scrum') {
    //     $loaded = load_textdomain('zephyr-project-manager', dirname( __FILE__ ) . "/languages/zephyr-project-manager-scrum-{$locale}.mo");
    // }
}

function zpm_admin_init() {
    if (isset($_POST['zpm_save_general_settings'])) {
        check_admin_referer('zpm_save_general_settings');
        $ics = isset($_POST['zpm-settings-ics-sync-enabled']);

        if ($ics) {
            Tasks::syncIcs();
        } else {
            Tasks::unsyncIcs();
        }
    }
}

add_action('plugins_loaded', 'zpm_plugin_loaded');
add_action('admin_init', 'zpm_admin_init');
add_filter('admin_body_class', 'zpm_body_classes');

if (!zpm_is_required_pro_version()) {
    add_action('admin_notices', 'zpm_incompatible_pro_version');
}

function zpm_incompatible_pro_version() {
    ?>
        <div class="notice notice-error">
            <p><?php echo sprintf(__('Zephyr Project Manager PRO version %s or higher is required. To continue using all the Pro features and avoid any incompatibility, please update it by going to Plugins > Zephyr Project Manager Pro and clicking the Update button.', 'zephyr-project-manager'), ZPM_REQUIRED_PRO_VERSION) ?></p>
        </div>
    <?php
}

function zpm_body_classes( $classes ) {
    if (isZephyrPage()) {
        return "$classes zephyr-project-manager";
    }
    return $classes;
}

function zpm_logged_in_redirect() {
    $redirectTo = isset($_GET['redirect_to']) ? filter_var($_GET['redirect_to'], FILTER_SANITIZE_URL) : '';

    if (!empty($redirectTo) && strpos($redirectTo, 'zephyrprojectmanager') !== false) {
        if (is_user_logged_in()) {
            $redirected = wp_safe_redirect($redirectTo);

            if ($redirected) {
                die;
            }
        }
    }
}

add_action('admin_menu', 'zpm_logged_in_redirect');