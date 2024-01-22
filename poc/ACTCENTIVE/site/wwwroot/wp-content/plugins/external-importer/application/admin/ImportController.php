<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\PluginAdmin;
use ExternalImporter\application\Plugin;
use ExternalImporter\application\helpers\WooHelper;

/**
 * ImportController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class ImportController {

    private $app_params = array();

    public function page_slug()
    {
        return Plugin::slug . '';
    }

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'addAdminMenu'));
        \add_action('admin_notices', array($this, 'wooNotice'));
    }

    public function addAdminMenu()
    {
        \remove_submenu_page(Plugin::getSlug(), Plugin::getSlug());
        \add_submenu_page(Plugin::getSlug(), __('Product Import', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Product Import', 'external-importer'), 'publish_posts', $this->page_slug(), array($this, 'actionImport'), 0);
    }

    public function actionImport()
    {
        $this->angularInit();
        PluginAdmin::getInstance()->render('import');
    }

    private function angularInit()
    {
        // Angular core
        \wp_enqueue_script('angularjs', \ExternalImporter\PLUGIN_RES . '/app/vendor/angular.min.js', array('jquery'), null, false);
        \wp_enqueue_script('angularjs-sanitize', \ExternalImporter\PLUGIN_RES . '/app/vendor/angular-sanitize.js', array('angularjs'), null, false);
        \wp_enqueue_script('angularjs-animate', \ExternalImporter\PLUGIN_RES . '/app/vendor/angular-animate.js', array('angularjs'), null, false);

        // EI angular application
        \wp_enqueue_script('angular-ui-bootstrap', \ExternalImporter\PLUGIN_RES . '/app/vendor/angular-ui-bootstrap/ui-bootstrap-tpls-0.13.3.min.js', array('angularjs'), null, false);
        \wp_register_script('ei-import-app', \ExternalImporter\PLUGIN_RES . '/app/app.js', array('angularjs'), null, false);

        \wp_enqueue_script('ei-import-app');
        \wp_enqueue_script('ei-product-service', \ExternalImporter\PLUGIN_RES . '/app/ExtractorService.js', array('ei-import-app'), null, false);

        // Bootstrap
        \wp_enqueue_style('egg-bootstrap', \ExternalImporter\PLUGIN_RES . '/bootstrap/css/egg-bootstrap.css');
        \wp_enqueue_script('bootstrap', \ExternalImporter\PLUGIN_RES . '/bootstrap/js/bootstrap.min.js', array('jquery'), null, false);

        // Application params
        $this->addAppParam('nonce', \wp_create_nonce('ei-import'));

        \wp_localize_script('ei-import-app', 'ei_params', $this->getAppParams());
    }

    private function addAppParam($param, $value)
    {
        $this->app_params[$param] = $value;
    }

    private function getAppParams()
    {
        return $this->app_params;
    }

    public function wooNotice()
    {
        if ($GLOBALS['pagenow'] != 'admin.php' || empty($_GET['page']) || !strstr($_GET['page'], 'external-importer'))
            return;

        if (WooHelper::isWooInstalled())
            return;

        $uri = 'https://wordpress.org/plugins/woocommerce/';
        echo '<div class="notice notice-error settings-error"><p><strong>';
        echo sprintf(__('Please install <a target="_blank" href="%s">WooCommerce plugin</a> so you can import products.', 'external-importer'), $uri);
        echo '</strong></p></div>';
    }

}
