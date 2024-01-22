<?php

/**
 * Plugin Name: Import WP PRO
 * Plugin URI: https://www.importwp.com
 * Description: Import WP PRO extends Import WP to work with custom post types, custom taxonomes, custom fields, download remote xml and csv files, and schedule imports.
 * Author: James Collings <james@jclabs.co.uk>
 * Version: 2.9.5 
 * Author URI: https://www.importwp.com
 * Network: True
 */

$iwp_base_path = dirname(__FILE__);

if (!defined('IWP_PRO_VERSION')) {
    define('IWP_PRO_VERSION', '2.9.5');
}

if (!defined('IWP_MINIMUM_PHP_VERSION')) {
    define('IWP_MINIMUM_PHP_VERSION', '5.4');
}

if (!defined('IWP_POST_TYPE')) {
    define('IWP_POST_TYPE', 'iwp-importer');
}

if (!defined('EWP_POST_TYPE')) {
    define('EWP_POST_TYPE', 'iwp-exporter');
}

if (!defined('IWP_PRO_MIN_CORE_VERSION')) {
    define('IWP_PRO_MIN_CORE_VERSION', '2.6.0');
}

if (!defined('IWP_API_TOKEN')) {
    define('IWP_API_TOKEN', get_site_option('iwp_api_token', false));
}

if (version_compare(PHP_VERSION, IWP_MINIMUM_PHP_VERSION, '>=')) {
    require_once $iwp_base_path . '/class/autoload.php';
    require_once $iwp_base_path . '/setup-iwp-pro.php';

    if(!class_exists('PucFactory')){
        require $iwp_base_path .'/class-update-checker.php';
    }

    //Updater
    $wpfepp_checker = PucFactory::buildUpdateChecker(
        'https://wpsoul.net/serverupdate/?action=get_metadata&slug=importwp-pro',
        __FILE__,
        'importwp-pro',
        '24'
    );

}
