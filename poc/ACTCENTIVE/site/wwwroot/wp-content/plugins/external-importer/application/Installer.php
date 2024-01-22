<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\admin\LicConfig;
use ExternalImporter\application\SyncScheduler;
use ExternalImporter\application\GalleryScheduler;
use ExternalImporter\application\AutoimportSheduler;

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
        \add_option(Plugin::slug . '_do_activation_redirect', true);
        \add_option(Plugin::slug . '_first_activation_date', time());
        self::upgradeTables();

        SyncScheduler::maybeAddScheduleEvent();
        AutoimportSheduler::maybeAddScheduleEvent();
    }

    public static function deactivate()
    {
        SyncScheduler::clearScheduleEvent();
        GalleryScheduler::clearScheduleEvent();
        AutoimportSheduler::clearScheduleEvent();
    }

    public static function requirements()
    {
        $php_min_version = '5.6';
        $extensions = array(
            'simplexml',
            'mbstring',
        );
        $plugins = array(
                /*
                  'woocommerce/woocommerce.php' => array(
                  'name' => 'Woocommerce',
                  'version' => '3.6.5',
                  ),
                 * 
                 */
        );

        $errors = array();

        global $wp_version;
        if (version_compare(Plugin::wp_requires, $wp_version, '>'))
            $errors[] = sprintf('You are using Wordpress %s. <em>%s</em> requires at least <strong>Wordpress %s</strong>.', $wp_version, Plugin::name, Plugin::wp_requires);

        $php_current_version = phpversion();
        if (version_compare($php_min_version, $php_current_version, '>'))
            $errors[] = sprintf('PHP is installed on your server %s. <em>%s</em> requires at least <strong>PHP %s</strong>.', $php_current_version, Plugin::name, $php_min_version);

        foreach ($extensions as $extension)
        {
            if (!extension_loaded($extension))
                $errors[] = sprintf('Requires PHP extension <strong>%s</strong>.', $extension);
        }
        foreach ($plugins as $plugin_id => $plugin)
        {
            if (!\is_plugin_active($plugin_id) || \version_compare($plugin['version'], self::getPluginVersion($plugin_id), '>'))
                $errors[] = sprintf('<em>%s</em> requires <strong>%s %s+</strong> to be installed and active.', Plugin::name, $plugin['name'], $plugin['version']);
        }

        if (!$errors)
            return;
        unset($_GET['activate']);
        \deactivate_plugins(\plugin_basename(\ExternalImporter\PLUGIN_FILE));
        $e = sprintf('<div class="error"><p>%1$s</p><p><em>%2$s</em> ' . 'cannot be installed!' . '</p></div>', join('</p><p>', $errors), Plugin::name);
        \wp_die($e);
    }

    public static function uninstall()
    {
        global $wpdb;
        if (!current_user_can('activate_plugins'))
            return;

        \delete_option(Plugin::slug . '_db_version');
        if (Plugin::isEnvato())
            \delete_option(Plugin::slug . '_env_install');
        if (Plugin::isPro())
            \delete_option(LicConfig::getInstance()->option_name());
    }

    public static function upgrade()
    {
        $db_version = get_option(Plugin::slug . '_db_version');
        if ($db_version >= self::dbVesrion())
            return;
        self::upgradeTables();
        \update_option(Plugin::slug . '_db_version', self::dbVesrion());
    }

    private static function upgradeTables()
    {
        $models = array('TaskModel', 'QueryModel', 'LogModel', 'AutoimportModel', 'AutoimportItemModel');
        $sql = '';
        foreach ($models as $model)
        {
            $m = "\\ExternalImporter\\application\\models\\" . $model;
            $sql .= $m::model()->getDump();
            $sql .= "\r\n";
        }
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function redirect_after_activation()
    {
        if (\get_option(Plugin::slug . '_do_activation_redirect', false))
        {
            \delete_option(Plugin::slug . '_do_activation_redirect');
            \wp_redirect(\get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::slug()));
        }
    }

    public static function getPluginVersion($plugin_file)
    {
        $data = \get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
        if (isset($data['Version']))
            return $data['Version'];
        else
            return 0;
    }

}
