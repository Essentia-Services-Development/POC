<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\AdminNotice;
use ExternalImporter\application\libs\pextractor\client\Session;

/**
 * ToolsController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class ToolsController {

    const slug = 'external-importer-tools';

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'actionHandler'));
    }

    public function actionHandler()
    {
        if (empty($GLOBALS['pagenow']) || $GLOBALS['pagenow'] != 'admin.php')
            return;

        if (empty($_GET['page']) || $_GET['page'] != 'external-importer-tools')
            return;

        if (!empty($_GET['action']) && $_GET['action'] == 'session_destroy')
            $this->actionSessionDestroy();

        die('You do not have permission to view this page.');
    }

    public function actionSessionDestroy()
    {
        if (!\current_user_can('administrator'))
            die('You do not have permission to view this page.');

        Session::clearSessionVariables();

        $redirect_url = \get_admin_url(\get_current_blog_id(), 'admin.php?page=external-importer-settings-parser');
        $redirect_url = AdminNotice::add2Url($redirect_url, 'session_cleared', 'info');
        \wp_redirect($redirect_url);
        exit;
    }

}
