<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\admin\LicConfig;
use ContentEgg\application\models\AutoblogModel;

/**
 * Installer class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class Installer {

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {

        if (!empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'plugins.php')
        {
            \add_action('admin_init', array($this, 'requirements'), 0);
        }

        \add_action('admin_init', array($this, 'upgrade'));
        \add_action('admin_init', array($this, 'redirect_after_activation'));
    }

    static public function dbVesrion()
    {
        return Plugin::db_version;
    }

    public static function activate()
    {
        if (!\current_user_can('activate_plugins'))
            return;

        self::requirements();

        ModuleUpdateScheduler::addScheduleEvent();
        \add_option(Plugin::slug . '_do_activation_redirect', true);
        \add_option(Plugin::slug . '_first_activation_date', time());
        self::upgradeTables();
        if (AutoblogModel::isActiveAutoblogs())
            AutoblogScheduler::addScheduleEvent();        
    }

    public static function deactivate()
    {
        ModuleUpdateScheduler::clearScheduleEvent();
        AutoblogScheduler::clearScheduleEvent();
    }

    public static function requirements()
    {
        $php_min_version = '5.3';
        $extensions = array(
            'simplexml',
            'mbstring',
            'hash',
        );

        $errors = array();
        $name = get_file_data(\ContentEgg\PLUGIN_FILE, array('Plugin Name'), 'plugin');

        global $wp_version;
        if (version_compare(Plugin::wp_requires, $wp_version, '>'))
            $errors[] = sprintf('You are using Wordpress %s. <em>%s</em> requires at least <strong>Wordpress %s</strong>.', $wp_version, $name[0], Plugin::wp_requires);

        $php_current_version = phpversion();
        if (version_compare($php_min_version, $php_current_version, '>'))
            $errors[] = sprintf('PHP is installed on your server %s. <em>%s</em> requires at least <strong>PHP %s</strong>.', $php_current_version, $name[0], $php_min_version);

        foreach ($extensions as $extension)
        {
            if (!extension_loaded($extension))
                $errors[] = sprintf('Requires extension <strong>%s</strong>.', $extension);
        }
        if (!$errors)
            return;
        unset($_GET['activate']);
        \deactivate_plugins(\plugin_basename(\ContentEgg\PLUGIN_FILE));
        $e = sprintf('<div class="error"><p>%1$s</p><p><em>%2$s</em> ' . 'cannot be installed!' . '</p></div>', join('</p><p>', $errors), $name[0]);
        \wp_die(wp_kses_post($e));
    }

    public static function uninstall()
    {
        global $wpdb;
        if (!\current_user_can('activate_plugins'))
            return;

        \delete_option(Plugin::slug . '_db_version');
        if (Plugin::isEnvato())
            \delete_option(Plugin::slug . '_env_install');
        if (Plugin::isPro())
            \delete_option(LicConfig::getInstance()->option_name());
    }

    public static function upgrade()
    {
        $db_version = \get_option(Plugin::slug . '_db_version');

        if ((int) $db_version >= (int) self::dbVesrion())
            return;
        self::upgradeTables();

        if ($db_version < 33)
            self::upgrade_33();

        if ($db_version < 50)
            self::upgrade_v50();

        if ($db_version < 53)
            self::upgrade_v53();

        \update_option(Plugin::slug . '_db_version', self::dbVesrion());
    }

    private static function upgradeTables()
    {
        $models = array('AutoblogModel', 'PriceHistoryModel', 'PriceAlertModel', 'ProductModel');
        $sql = '';
        foreach ($models as $model)
        {
            $m = "\\ContentEgg\\application\\models\\" . $model;
            $sql .= $m::model()->getDump();
            $sql .= "\r\n";
        }
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }

    /**
     * v 2.9.0 & 3.6.3
     * reinit schedule event
     */
    private static function upgrade_33()
    {
        ModuleUpdateScheduler::clearScheduleEvent();
        ModuleUpdateScheduler::addScheduleEvent();
    }

    private static function upgrade_v50()
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'cegg_awin_product');
    }
    
    private static function upgrade_v53()
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'cegg_daisycon_product');
    }    

    public function redirect_after_activation()
    {
        if (\get_option(Plugin::slug . '_do_activation_redirect', false))
        {
            \delete_option(Plugin::slug . '_do_activation_redirect');
            \wp_safe_redirect(\get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::slug));
        }
    }

}
