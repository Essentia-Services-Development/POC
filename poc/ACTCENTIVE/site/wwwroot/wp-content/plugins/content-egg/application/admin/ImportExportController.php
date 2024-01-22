<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\components\ModuleManager;

/**
 * ImportExportController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class ImportExportController {

    const slug = 'content-egg-import-export';

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Import/Export', 'content-egg') . ' &lsaquo; Content Egg', __('Import/Export', 'content-egg'), 'manage_options', self::slug, array($this, 'actionIndex'));
    }

    public function actionIndex()
    {
        $message = '';
        $notice = '';

        $all_options = array();
        // main settings
        $all_options[GeneralConfig::getInstance()->option_name()] = GeneralConfig::getInstance()->getOptionValues();
        // modules
        $all_options = array_merge($all_options, ModuleManager::getInstance()->getOptionsList());

        if (!empty($_POST['nonce']) && \wp_verify_nonce(sanitize_key($_POST['nonce']), basename(__FILE__)) && !empty($_POST['import_str']))
        {
            $import = json_decode(sanitize_text_field(wp_unslash($_POST['import_str'])), true);
            if ($import)
            {
                foreach ($import as $option => $values)
                {
                    if (!array_key_exists($option, $all_options))
                        continue;
                    $save = $all_options[$option];
                    foreach ($save as $k => $v)
                    {
                        if (isset($values[$k]))
                            $save[$k] = $values[$k];
                    }
                    \update_option($option, $save);
                    $all_options[$option] = $save;
                }
                $message = __('Options were saved.', 'content-egg') . ' <a href="?page=content-egg">' . __('Page of settings', 'content-egg') . '</a>';
            } else
                $notice = __('Invalid format.', 'content-egg');
        }
        PluginAdmin::getInstance()->render('import_export', array(
            'export_str' => json_encode($all_options),
            'notice' => $notice,
            'message' => $message,
            'nonce' => \wp_create_nonce(basename(__FILE__)),
        ));
    }

}
