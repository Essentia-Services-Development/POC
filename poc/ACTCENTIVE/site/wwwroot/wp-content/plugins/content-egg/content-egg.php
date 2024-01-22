<?php

namespace ContentEgg;

/*
  Plugin Name: Content Egg Pro
  Plugin URI: https://www.keywordrush.com/contentegg
  Description: All in one solution for creating affiliate websites.
  Version: 10.10.0
  Author: keywordrush.com
  Author URI: https://www.keywordrush.com
  Text Domain: content-egg
  Domain Path: /languages
 */

/*
 * Copyright (c)  www.keywordrush.com  (email: support@keywordrush.com)
 */

defined('\ABSPATH') || die('No direct script access allowed!');

define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');
define(NS . 'PLUGIN_PATH', \plugin_dir_path(__FILE__));
define(NS . 'PLUGIN_FILE', __FILE__);
define(NS . 'PLUGIN_RES', \plugins_url('res', __FILE__));
define(NS . 'CUSTOM_MODULES_DIR', 'content-egg-modules');

require_once PLUGIN_PATH . 'loader.php';

\add_action('plugins_loaded', array('\ContentEgg\application\Plugin', 'getInstance'));
if (\is_admin())
{
    \register_activation_hook(__FILE__, array(\ContentEgg\application\Installer::getInstance(), 'activate'));
    \register_deactivation_hook(__FILE__, array(\ContentEgg\application\Installer::getInstance(), 'deactivate'));
    \register_uninstall_hook(__FILE__, array('\ContentEgg\application\Installer', 'uninstall'));
    \add_action('init', array('\ContentEgg\application\admin\PluginAdmin', 'getInstance'));
}