<?php

namespace ContentEgg\application\admin;
defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\components\ModuleManager;

/**
 * ModuleSettingsContoller class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class ModuleSettingsContoller {

    const slug = 'content-egg-modules';
    
    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug(), __('Modules', 'content-egg') . ' &lsaquo; Content Egg', __('Modules', 'content-egg'), 'manage_options', self::slug, array($this, 'actionIndex'));
    }

    public function actionIndex()
    {
        \wp_enqueue_style('egg-bootstrap', \ContentEgg\PLUGIN_RES . '/bootstrap/css/egg-bootstrap.min.css', array(), Plugin::version());
        PluginAdmin::getInstance()->render('module_index', array('modules' => ModuleManager::getInstance()->getConfigurableModules()));
    }


}
