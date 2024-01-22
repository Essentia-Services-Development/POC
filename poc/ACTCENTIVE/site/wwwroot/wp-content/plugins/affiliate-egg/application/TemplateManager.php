<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * TemplateManager class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class TemplateManager {

    const TEMPLATE_DIR = 'templates';
    const CUSTOM_TEMPLATE_DIR = 'affegg-templates';
    const TEMPLATE_PREFIX_EGG = 'egg_';
    const TEMPLATE_PREFIX_EGG_WIDGET = 'wegg_';  //шаблоны для виджетов

    private static $templates_egg = null;
    private static $templates_widget = null;
    private static $instance = null;
    private $last_render_data;
    private static $product_style_enqueued = false;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        wp_register_style('affegg-bootstrap', PLUGIN_RES . '/bootstrap/css/bootstrap.min.css');
        wp_register_style('egg-bootstrap', PLUGIN_RES . '/bootstrap/css/egg-bootstrap.css');
        wp_register_style('affegg-bootstrap-glyphicons', PLUGIN_RES . '/bootstrap/css/bootstrap.glyphicons.css');
        wp_register_script('affegg-bootstrap', PLUGIN_RES . '/bootstrap/js/bootstrap.min.js', array('jquery'));
        wp_register_script('egg-bootstrap', PLUGIN_RES . '/bootstrap/js/bootstrap.min.js', array('jquery'));
    }

    public static function getTempateDir()
    {
        return PLUGIN_PATH . self::TEMPLATE_DIR;
    }

    public static function getCustomTempateDirs()
    {
        return array(
            \get_stylesheet_directory() . '/' . self::CUSTOM_TEMPLATE_DIR, //child theme		
            \get_template_directory() . '/' . self::CUSTOM_TEMPLATE_DIR, // theme
            \WP_CONTENT_DIR . '/' . self::CUSTOM_TEMPLATE_DIR,
        );
    }

    public function getTemplatesList($prefix)
    {
        $templates = array();
        foreach (self::getCustomTempateDirs() as $dir)
        {
            $templates = array_merge($templates, self::scanTemplates($dir, $prefix, true));
        }
        $templates = array_merge($templates, self::scanTemplates(self::getTempateDir(), $prefix, false));
        return $templates;
    }

    private static function scanTemplates($path, $prefix, $custom = false)
    {
        if ($custom && !is_dir($path))
            return array();

        $tpl_files = glob($path . '/' . $prefix . '*.php');
        if (!$tpl_files)
            return array();

        $templates = array();
        foreach ($tpl_files as $file)
        {
            $template_id = basename($file, '.php');
            if ($custom)
                $template_id = 'custom/' . $template_id;

            $data = get_file_data($file, array('name' => 'Name'));
            if ($data && !empty($data['name']))
                $templates[$template_id] = __(strip_tags($data['name']), 'affegg-tpl');
            else
                $templates[$template_id] = $template_id;
            if ($custom)
                $templates[$template_id] .= ' ' . __('[custom]', 'affegg');
        }
        return $templates;
    }

    public function getEggTemplatesList()
    {
        if (self::$templates_egg === null)
        {
            self::$templates_egg = self::getTemplatesList(self::TEMPLATE_PREFIX_EGG);
        }
        return self::$templates_egg;
    }

    public function getWidgetTemplatesList()
    {
        if (self::$templates_widget === null)
        {
            self::$templates_widget = self::getTemplatesList(self::TEMPLATE_PREFIX_EGG_WIDGET);
        }
        return self::$templates_widget;
    }

    public function render($view_name, $_data = null)
    {
        $file = $this->getViewPath($view_name);
        if (!$file)
            return '';

        $this->last_render_data = $_data;
        extract($_data, EXTR_PREFIX_SAME, 'data');

        ob_start();
        ob_implicit_flush(false);
        include $file;
        $res = ob_get_clean();

        if (GeneralConfig::getInstance()->option('noindex'))
            $res = '<noindex>' . $res . '</noindex>';
        return $res;
    }

    public function renderPartial($view_name, array $_data = array(), $block = false)
    {
        $file = PLUGIN_PATH . 'templates/';
        if ($block)
            $file .= 'blocks/';
        else
            $file .= $this->getTempatePrefix();
        $file .= $view_name . '.php';

        if (!$file)
            return '';
        $_data = array_merge($this->last_render_data, $_data);
        extract($_data, EXTR_PREFIX_SAME, 'data');
        include $file;
    }

    public function renderBlock($view_name, array $data = array())
    {
        $this->renderPartial($view_name, $data, true);
    }

    private function getViewPath($view_name)
    {
        $view_name = str_replace('.', '', $view_name);
        if (substr($view_name, 0, 7) == 'custom/')
        {
            $view_name = substr($view_name, 7);
            foreach (self::getCustomTempateDirs() as $custom_dir)
            {
                $tpl_path = $custom_dir;
                $file = $tpl_path . DIRECTORY_SEPARATOR . TextHelper::clear($view_name) . '.php';
                if (is_file($file) && is_readable($file))
                    return $file;
            }

            return false;
        } else
        {
            $tpl_path = self::getTempateDir();
            $file = $tpl_path . DIRECTORY_SEPARATOR . TextHelper::clear($view_name) . '.php';
            if (is_file($file) && is_readable($file))
                return $file;
            else
                return false;
        }
    }

    public function enqueueStyle()
    {
        if (self::$product_style_enqueued)
            return;

        \wp_enqueue_style('egg-bootstrap');
        \wp_enqueue_style('egg-products');

        if (!$background = \wp_strip_all_tags(GeneralConfig::getInstance()->option('button_color')))
            $background = '#dc3545';
        
        $border = TemplateHelper::adjustBrightness($background, -0.05);
        $background_hover = TemplateHelper::adjustBrightness($background, -0.15);
        $border_hover = TemplateHelper::adjustBrightness($background_hover, -0.05);

        $custom_css = '.egg-container .btn-danger {background-color: ' . $background . ' !important;border-color: ' . $border . ' !important;} .egg-container .btn-danger:hover,.egg-container .btn-danger:focus,.egg-container .btn-danger:active {background-color: ' . $background_hover . ' !important;border-color: ' . $border_hover . ' !important;}';

        \wp_add_inline_style('egg-products', $custom_css);
        self::$product_style_enqueued = true;
    }

}
