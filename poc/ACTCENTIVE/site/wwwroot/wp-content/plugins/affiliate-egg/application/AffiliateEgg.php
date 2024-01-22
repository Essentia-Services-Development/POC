<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AffiliateEgg class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AffiliateEgg {

    const db_version = 52;
    const version = '10.7.1';
    const wp_requires = '4.2.0';
    const slug = 'affiliate-egg';
    const short_slug = 'affegg';
    const name = 'Affiliate Egg';
    const api_base = 'https://www.keywordrush.com/api/v1';
    const api_base2 = '';
    const product_id = 300;
    const supportUri = 'https://www.keywordrush.com/contact';
    const panelUri = 'https://www.keywordrush.com/panel';
    const website = 'https://www.keywordrush.com';

    private static $instance = null;
    private static $is_pro = null;
    private static $is_envato = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        $this->loadTextdomain();
        if (GeneralConfig::getInstance()->option('set_local_redirect'))
            \add_action('template_redirect', array(NS . 'LinkHandler', 'redirect'));

        \add_action('admin_init', array($this, 'upgrade'));
        if (self::isFree() || (self::isPro() && self::isActivated()) || self::isEnvato())
        {
            CurrencyHelper::getInstance(GeneralConfig::getInstance()->option('lang'));
            PriceAlert::getInstance()->init();
            \add_action('init', array(NS . 'Shortcode', 'getInstance'));
            \add_action('affeggcron', array(NS . 'Scheduler', 'run'));
            if (GeneralConfig::getInstance()->option('set_featured_img'))
                new FeaturedImage;
            if (GeneralConfig::getInstance()->option('save_custom_fields'))
                new CustomFields;

            \add_action('widgets_init', function() {
                \register_widget(NS . 'AffiliateEgg_Widget');
            });
        }

        if (!empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'plugins.php')
        {
            \add_action('admin_notices', array($this, 'requirements'), 0);
        }
    }

    static public function version()
    {
        return self::version;
    }

    static public function slug()
    {
        return self::slug;
    }

    public static function getApiBase()
    {
        return self::api_base;
    }

    public static function activate()
    {
        if (!\current_user_can('activate_plugins'))
            return;

        self::requirements();

        if (!\wp_next_scheduled('affeggcron'))
        {
            \wp_schedule_event(time(), 'hourly', 'affeggcron');
        }
        \add_option('affegg_do_activation_redirect', true);
        \add_option('affegg_first_activation_date', time());
        self::upgrade_tables();
    }

    public static function deactivate()
    {
        \wp_clear_scheduled_hook('affeggcron');
    }

    public static function isFree()
    {
        return !self::isPro();
    }

    public static function isPro()
    {
        if (self::$is_pro === null)
        {
            self::$is_pro = true;
        }
        return self::$is_pro;
    }

    public static function isEnvato()
    {
        if (self::$is_envato === null)
        {
            if (isset($_SERVER['KEYWORDRUSH_DEVELOPMENT']) && $_SERVER['KEYWORDRUSH_DEVELOPMENT'] == '16203273895503427')
                self::$is_envato = false;
            elseif (file_exists(PLUGIN_PATH . 'application/admin/EnvatoConfig.php') || \get_option(AffiliateEgg::slug . '_env_install'))
                self::$is_envato = true;
            else
                self::$is_envato = false;
        }
        return self::$is_envato;
    }

    public static function getSlug()
    {
        return self::slug;
    }

    public static function getShortSlug()
    {
        return self::short_slug;
    }

    public static function getName()
    {
        return self::name;
    }

    public static function getWebsite()
    {
        return self::website;
    }

    public static function isActivated()
    {
        if (self::isPro() && LicConfig::getInstance()->option('license_key'))
            return true;
        else
            return false;
    }

    public static function isInactiveEnvato()
    {
        if (self::isEnvato() && !self::isActivated())
            return true;
        else
            return false;
    }

    public static function requirements()
    {
        $php_min_version = '5.3';
        $extensions = array(
            'simplexml',
            'mbstring',
        );

        $errors = array();
        $name = get_file_data(PLUGIN_FILE, array('Plugin Name'), 'plugin');

        global $wp_version;
        if (version_compare(self::wp_requires, $wp_version, '>'))
            $errors[] = sprintf('You are using Wordpress %s. <em>%s</em> requires at least <strong>Wordpress %s</strong>.', $wp_version, $name[0], self::wp_requires);
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
        deactivate_plugins(plugin_basename(PLUGIN_FILE));
        $e = sprintf('<div class="error"><p>%1$s</p><p><em>%2$s</em> ' . 'cannot be installed!' . '</p></div>', join('</p><p>', $errors), $name[0]);
        wp_die($e);
    }

    private static function upgrade_tables()
    {
        $models = array('EggModel', 'CatalogModel', 'ProductModel', 'AutoblogModel', 'AutoblogItemModel', 'PriceHistoryModel', 'PriceAlertModel');
        $sql = '';
        foreach ($models as $model)
        {
            $m = NS . $model;
            $sql .= $m::model()->getDump();
            $sql .= "\r\n";
        }
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        update_option('affegg_db_version', self::db_version);
    }

    public static function uninstall()
    {
        \delete_option('affegg_db_version');
        \delete_option(LicConfig::getInstance()->option_name());
    }

    public static function upgrade()
    {
        $affegg_db_version = get_option('affegg_db_version');
        if ($affegg_db_version >= self::db_version)
            return;
        self::upgrade_tables();
        if ($affegg_db_version < 37)
        {
            self::upgrade_v37();
        }
    }

    /**
     * v. 2.6.3
     * fix img_file abspath
     */
    private static function upgrade_v37()
    {
        $upload_dir = wp_upload_dir();
        $wpdb = ProductModel::model()->getDb();
        $sql = $wpdb->prepare("UPDATE " . ProductModel::model()->tableName() . " SET img_file = REPLACE(img_file, %s, '') WHERE img_file != ''", trailingslashit($upload_dir['basedir']));
        ProductModel::model()->getDb()->query($sql);
    }

    public static function apiRequest($params = array())
    {
        $api_urls = array(self::api_base);
        if (self::api_base2)
            $api_urls[] = self::api_base2;
        
        foreach ($api_urls as $api_url)
        {
            $response = \wp_remote_post($api_url, $params);
            if (\is_wp_error($response))
                continue; // try alternative api uri

            $response_code = (int) \wp_remote_retrieve_response_code($response);
            if ($response_code == 200)
                return $response;
            else
                return false;
        }
        return false;
    }

    private function loadTextdomain()
    {
        // plugin admin
        \load_plugin_textdomain('affegg', false, dirname(\plugin_basename(\Keywordrush\AffiliateEgg\PLUGIN_FILE)) . '/languages/');

        // frontend templates
        $lang = GeneralConfig::getInstance()->option('lang');
        $mo_files = array(
            \trailingslashit(WP_LANG_DIR) . 'plugins/affegg-tpl-' . $lang . '.mo', // wp lang dir
        );
        if (defined('LOCO_LANG_DIR'))
            $mo_files[] = \trailingslashit(LOCO_LANG_DIR) . 'plugins/affegg-tpl-' . $lang . '.mo'; // loco lang dir
        $mo_files[] = \Keywordrush\AffiliateEgg\PLUGIN_PATH . 'languages/tpl/affegg-tpl-' . strtoupper($lang) . '.mo'; // plugin lang dir
        foreach ($mo_files as $mo_file)
        {
            if (file_exists($mo_file) && is_readable($mo_file))
            {
                if (\load_textdomain('affegg-tpl', $mo_file))
                    return;
            }
        }
    }

    public static function getPluginDomain()
    {
        return 'https://www.keywordrush.com/';
    }

    public static function pluginSiteUrl()
    {
        return self::getPluginDomain() . 'affiliateegg?utm_source=affegg&utm_medium=referral&utm_campaign=plugin';
    }

    public static function pluginDocsUrl()
    {
        return 'https://ae-docs.keywordrush.com';
    }

}

function prn($var, $depth = 10, $highlight = true)
{
    echo CVarDumper::dumpAsString($var, $depth, $highlight);
    echo '<br />';
}

function prnx($var, $depth = 10, $highlight = true)
{
    echo CVarDumper::dumpAsString($var, $depth, $highlight);
    die('Exit');
}
