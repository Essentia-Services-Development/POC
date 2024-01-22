<?php

namespace Keywordrush\AffiliateEgg;

/*
  Plugin Name: Affiliate Egg Pro
  Plugin URI: http://www.keywordrush.com/en/affiliateegg
  Description: Parse, add, update products from affiliate shops. Let you to earn money from affiliate networks.
  Version: 10.7.1
  Author: keywordrush.com
  Author URI: http://www.keywordrush.com
  Text Domain: affegg
 */

/*
 * Copyright (c)  www.keywordrush.com  (email: support@keywordrush.com)
 */

defined('\ABSPATH') || die('No direct script access allowed!');

define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');
define(NS . 'PLUGIN_PATH', plugin_dir_path(__FILE__));
define(NS . 'PLUGIN_FILE', __FILE__);
define(NS . 'PLUGIN_RES', plugins_url('res', __FILE__));

require_once PLUGIN_PATH . 'application/MapAutoLoader.php';
require_once PLUGIN_PATH . 'application/AffiliateEgg.php';
require_once PLUGIN_PATH . 'application/Widget.php';
new MapAutoLoader;

\add_action('plugins_loaded', array(NS . 'AffiliateEgg', 'getInstance'));
if (\is_admin())
{
    require_once PLUGIN_PATH . 'application/admin/AffiliateEggAdmin.php';
    \register_activation_hook(__FILE__, array(AffiliateEgg::getInstance(), 'activate'));
    \register_deactivation_hook(__FILE__, array(AffiliateEgg::getInstance(), 'deactivate'));
    \register_uninstall_hook(__FILE__, array(NS . 'AffiliateEgg', 'uninstall'));
    \add_action('plugins_loaded', array(NS . 'AffiliateEggAdmin', 'getInstance'));
}
