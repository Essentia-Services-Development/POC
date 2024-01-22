<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\ModuleManager;

/**
 * ModuleTemplateManager class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ModuleTemplateManager extends TemplateManager {

    const TEMPLATE_DIR = 'templates';
    const CUSTOM_TEMPLATE_DIR = 'content-egg-templates';
    const TEMPLATE_PREFIX = 'data_';

    private $module_id;
    private static $instances = array();

    public static function getInstance($module_id)
    {
        if (!isset(self::$instances[$module_id]))
        {
            self::$instances[$module_id] = new self($module_id);
        }

        return self::$instances[$module_id];
    }

    private function __construct($module_id)
    {
        $this->module_id = $module_id;
    }

    public function getTempatePrefix()
    {
        return self::TEMPLATE_PREFIX;
    }

    public function getTempateDir()
    {
        $path_id = Module::getPathId($this->module_id);
        if (ModuleManager::isCustomModule($this->module_id))
        {
            $path = \WP_CONTENT_DIR . '/' . \ContentEgg\CUSTOM_MODULES_DIR . '/' . $path_id . '/' . self::TEMPLATE_DIR;
        } else
        {
            $path = \ContentEgg\PLUGIN_PATH . 'application/modules/' . $path_id . '/' . self::TEMPLATE_DIR;
        }

        return $path;
    }

    public function getCustomTempateDirs()
    {
        $path_id = Module::getPathId($this->module_id);
        $paths = array(
            'child-theme' => \get_stylesheet_directory() . '/' . self::CUSTOM_TEMPLATE_DIR . '/' . $path_id,
            'theme' => \get_template_directory() . '/' . self::CUSTOM_TEMPLATE_DIR . '/' . $path_id,
            'custom' => \WP_CONTENT_DIR . '/' . self::CUSTOM_TEMPLATE_DIR . '/' . $path_id,
        );
        
        $paths = \apply_filters('content_egg_module_template_dirs', $paths, $this->module_id, $path_id);

        return $paths;
    }

    public function getModuleId()
    {
        return $this->module_id;
    }

    public function getTemplatesList($short_mode = false)
    {
        $templates = parent::getTemplatesList($short_mode);
        $templates = \apply_filters('content_egg_module_templates', $templates, $this->getModuleId());

        return $templates;
    }

    public function render($view_name, array $_data = array())
    {
        if (!self::isCustomTemplate($view_name))
        {
            $this->enqueueProductsStyle();
        }

        return parent::render($view_name, $_data);
    }

    public function renderPartialModule($view_name, $module_ids = array(), $data = array())
    {
        if (!$module_ids)
        {
            $module_ids = array();
        }

        if (!is_array($module_ids))
        {
            $module_ids = array($module_ids);
        }

        $current_module_id = $this->module_id;
        if ($module_ids && !in_array($current_module_id, $module_ids))
        {
            return;
        }

        $view_path = $this->getTempateDir() . DIRECTORY_SEPARATOR . TextHelper::clear($view_name) . '.php';
        $this->renderPath($view_path, $data);
    }

}
