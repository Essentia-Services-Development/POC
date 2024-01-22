<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\models\LogModel;

/**
 * LogController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class LogController {

    public function page_slug()
    {
        return Plugin::slug . '-logs';
    }

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));
        \add_action('admin_init', array($this, 'remove_http_referer'));
    }

    public function remove_http_referer()
    {
        global $pagenow;

        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == Plugin::slug . '-logs' && !empty($_GET['_wp_http_referer']))
        {
            \wp_redirect(\remove_query_arg(array('_wp_http_referer', '_wpnonce'), \wp_unslash($_SERVER['REQUEST_URI'])));
            exit;
        }
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Logs', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Logs', 'Error log'), 'manage_options', $this->page_slug(), array($this, 'actionIndex'));
    }

    public function actionIndex()
    {
        $table = new LogTable(LogModel::model());
        $table->prepare_items();
        PluginAdmin::getInstance()->render('log_index', array('table' => $table));
    }

}
