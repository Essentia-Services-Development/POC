<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\components\LManager;

/**
 * PluginAdmin class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class PluginAdmin {

    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        if (!\is_admin())
            die('You are not authorized to perform the requested action.');

        \add_action('admin_menu', array($this, 'add_admin_menu'));
        \add_action('admin_enqueue_scripts', array($this, 'admin_load_scripts'));
        \add_filter('parent_file', array($this, 'highlight_admin_menu'));

        AdminNotice::getInstance()->adminInit();
        LManager::getInstance()->adminInit();
        
        if (Plugin::isFree() || (Plugin::isPro() && Plugin::isActivated()) || Plugin::isEnvato())
        {
            GeneralConfig::getInstance()->adminInit();
            WooConfig::getInstance()->adminInit();
            SyncConfig::getInstance()->adminInit();
            ParserConfig::getInstance()->adminInit();
            FrontendConfig::getInstance()->adminInit();
            DeeplinkConfig::getInstance()->adminInit();
            DropshippingConfig::getInstance()->adminInit();
            new ImportController;
            new AutoimportController;
            new ToolsController;
            new LogController;
            new ExtractorApi;
            new ImportApi;
            new StatMetabox;
            new DevController;
        }
        if (Plugin::isEnvato() && !Plugin::isActivated() && !\get_option(Plugin::slug . '_env_install'))
            EnvatoConfig::getInstance()->adminInit();
        elseif (Plugin::isPro())
            LicConfig::getInstance()->adminInit();
        if (Plugin::isPro() && Plugin::isActivated())
            new \ExternalImporter\application\Autoupdate(Plugin::version(), \plugin_basename(\ExternalImporter\PLUGIN_FILE), Plugin::getApiBase(), Plugin::slug);
    }

    function admin_load_scripts()
    {

        if ($GLOBALS['pagenow'] != 'admin.php' || empty($_GET['page']))
            return;

        $page_pats = explode('-', $_GET['page']);

        if (count($page_pats) < 2 || $page_pats[0] . '-' . $page_pats[1] != Plugin::slug())
            return;

        \wp_enqueue_script('external_importer_admin', \ExternalImporter\PLUGIN_RES . '/js/admin.js', array('jquery'));
        \wp_localize_script('external_importer_admin', 'externalimporterL10n', array(
            'are_you_shure' => __('Are you sure?', 'external-importer'),
        ));

        \wp_enqueue_style(Plugin::slug() . '-admin', \ExternalImporter\PLUGIN_RES . '/css/admin.css', array(), '_' . Plugin::version());
    }

    public function add_admin_menu()
    {
        $icon_svg = 'dashicons-download';
        \add_menu_page(Plugin::getName(), Plugin::getName(), 'publish_posts', Plugin::getSlug(), null, $icon_svg);
    }

    public static function render($view_name, $_data = null)
    {
        if (is_array($_data))
            extract($_data, EXTR_PREFIX_SAME, 'data');
        else
            $data = $_data;

        include \ExternalImporter\PLUGIN_PATH . 'application/admin/views/' . PluginAdmin::sanitize($view_name) . '.php';
    }

    /**
     * Highlight menu for hidden submenu item
     */
    function highlight_admin_menu($file)
    {
        global $plugin_page;

        // options.php - hidden submenu items        
        if ($file != 'options.php' || substr($plugin_page, 0, strlen(Plugin::slug())) !== Plugin::slug())
            return $file;

        if (strstr($plugin_page, Plugin::slug() . '-settings-'))
            $plugin_page = 'external-importer-settings';

        return $file;
    }

    static public function sanitize($str)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $str);
    }

}
