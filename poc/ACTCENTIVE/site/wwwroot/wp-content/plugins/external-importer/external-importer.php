<?php

namespace ExternalImporter;

/*
  Plugin Name: External Importer
  Plugin URI: https://www.keywordrush.com/externalimporter
  Description: Extract and import products into your affiliate website.
  Version: 1.9.12
  Author: keywordrush.com
  Author URI: https://www.keywordrush.com
  Text Domain: external-importer
 */

/*
 * Copyright (c)  www.keywordrush.com  (email: support@keywordrush.com)
 */

defined('\ABSPATH') || die('No direct script access allowed!');

define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');
define(NS . 'PLUGIN_PATH', \plugin_dir_path(__FILE__));
define(NS . 'PLUGIN_FILE', __FILE__);
define(NS . 'PLUGIN_RES', \plugins_url('res', __FILE__));

require_once PLUGIN_PATH . 'loader.php';

\add_action('plugins_loaded', array('\ExternalImporter\application\Plugin', 'getInstance'));
if (\is_admin())
{
    \register_activation_hook(__FILE__, array(\ExternalImporter\application\Installer::getInstance(), 'activate'));
    \register_deactivation_hook(__FILE__, array(\ExternalImporter\application\Installer::getInstance(), 'deactivate'));
    \register_uninstall_hook(__FILE__, array('\ExternalImporter\application\Installer', 'uninstall'));
    \add_action('init', array('\ExternalImporter\application\admin\PluginAdmin', 'getInstance'));
}