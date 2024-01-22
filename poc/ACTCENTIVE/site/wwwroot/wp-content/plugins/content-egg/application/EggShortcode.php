<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\components\ModuleTemplateManager;
use ContentEgg\application\components\Shortcoded;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\helpers\TemplateHelper;

/**
 * EggShortcode class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class EggShortcode
{

    const shortcode = 'content-egg';

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;
        return self::$instance;
    }

    private function __construct()
    {
        \add_shortcode(self::shortcode, array($this, 'viewData'));
        \add_filter('term_description', 'shortcode_unautop');
        \add_filter('term_description', 'do_shortcode');
    }

    private function prepareAttr($atts)
    {
        $allowed_atts = array(
            'module' => null,
            'limit' => 0,
            'offset' => 0,
            'next' => 0,
            'template' => '',
            'locale' => '',
            'title' => '',
            'post_id' => 0,
            'cols' => 0,
            'currency' => '',
            'groups' => '',
            'group' => '',
            'disable_features' => 0,
            'products' => '',
            'product' => '',
            'hide' => '',
            'btn_text' => '',
            'add_query_arg' => '',
            'sort' => '',
            'order' => '',
        );

        $allowed_atts = \apply_filters('cegg_module_shortcode_atts', $allowed_atts);
        $a = \shortcode_atts($allowed_atts, $atts);

        $a['next'] = (int) $a['next'];
        $a['limit'] = (int) $a['limit'];
        $a['offset'] = (int) $a['offset'];
        $a['module'] = TextHelper::clear($a['module']);
        $a['locale'] = TextHelper::clear($a['locale']);
        $a['title'] = \sanitize_text_field($a['title']);
        $a['post_id'] = (int) $a['post_id'];
        $a['cols'] = (int) $a['cols'];
        $a['disable_features'] = filter_var($a['disable_features'], FILTER_VALIDATE_BOOLEAN);
        $a['currency'] = strtoupper(TextHelper::clear($a['currency']));
        $a['groups'] = \sanitize_text_field($a['groups']);
        $a['group'] = \sanitize_text_field($a['group']);
        $a['hide'] = TemplateHelper::hideParamPrepare($a['hide']);
        $a['btn_text'] = \wp_strip_all_tags($a['btn_text'], true);
        $a['add_query_arg'] = \sanitize_text_field(\wp_strip_all_tags($a['add_query_arg'], true));
        if ($a['group'] && !$a['groups'])
            $a['groups'] = $a['group'];
        if ($a['groups'])
            $a['groups'] = TextHelper::getArrayFromCommaList($a['groups']);
        if ($a['product'] && !$a['products'])
            $a['products'] = $a['product'];
        if ($a['products'])
            $a['products'] = TextHelper::getArrayFromCommaList($a['products']);
        if ($a['add_query_arg'])
            parse_str($a['add_query_arg'], $a['add_query_arg']);
        $allowed_sort = array('price', 'discount', 'reverse');
        $allowed_order = array('asc', 'desc');
        $a['sort'] = strtolower($a['sort']);
        $a['order'] = strtolower($a['order']);
        if (!in_array($a['sort'], $allowed_sort))
            $a['sort'] = '';
        if (!in_array($a['order'], $allowed_order))
            $a['order'] = '';
        if ($a['sort'] == 'discount' && !$a['order'])
            $a['order'] = 'desc';

        if ($a['template'] && $a['module'])
        {
            $a['template'] = ModuleTemplateManager::getInstance($a['module'])->prepareShortcodeTempate($a['template']);
        }
        else
            $a['template'] = '';
        return $a;
    }

    public function viewData($atts, $content = "")
    {
        $a = $this->prepareAttr($atts);

        if (empty($a['module']))
            return;

        if (empty($a['post_id']))
        {
            global $post;
            $post_id = $post->ID;
        }
        else
            $post_id = $a['post_id'];

        $module_id = $a['module'];
        if (!ModuleManager::getInstance()->isModuleActive($module_id))
            return;

        Shortcoded::getInstance($post_id)->setShortcodedModule($module_id);
        return ModuleViewer::getInstance()->viewModuleData($module_id, $post_id, $a, $content);
    }

    public static function arraySortByColumn(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = array();
        foreach ($arr as $key => $row)
        {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);
    }
}
