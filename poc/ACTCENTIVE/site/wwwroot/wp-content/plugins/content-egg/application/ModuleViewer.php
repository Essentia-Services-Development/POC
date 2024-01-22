<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\components\ModuleTemplateManager;
use ContentEgg\application\components\Shortcoded;
use ContentEgg\application\helpers\ArrayHelper;
use ContentEgg\application\components\BlockTemplateManager;
use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\helpers\TemplateHelper;

/**
 * ModuleViewer class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ModuleViewer
{
    private static $instance = null;
    private $module_data_pointer = array();
    private $block_data_pointer = array();
    private $data = array();

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
    }

    public function init()
    {
        // priority = 12 because do_shortcode() is registered as a default filter on 'the_content' with a priority of 11. 
        \add_filter('the_content', array($this, 'viewData'), 12);
    }

    public function setData($module_id, $post_id, array $data)
    {
        if (!isset($this->data[$post_id]))
            $this->data[$post_id] = array();
        $this->data[$post_id][$module_id] = $data;
    }

    public function getData($module_id, $post_id, $params = array())
    {
        if (isset($this->data[$post_id]) && isset($this->data[$post_id][$module_id]))
            return $this->data[$post_id][$module_id];
        else
        {
            $data = ContentManager::getViewData($module_id, $post_id, $params);
            $outofstock_product = GeneralConfig::getInstance()->option('outofstock_product');
            if ($outofstock_product == 'hide_product')
            {
                foreach ($data as $key => $d)
                {
                    if (isset($d['stock_status']) && $d['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
                    {
                        unset($data[$key]);
                    }
                }
            }

            return $data;
        }
    }

    public function viewData($content)
    {
        global $post;
        if ($post)
            $post_id = $post->ID;
        else
            $post_id = -1;

        $top_modules_priorities = array();
        $bottom_modules_priorities = array();
        foreach (ModuleManager::getInstance()->getModules(true) as $module_id => $module)
        {
            $embed_at = $module->config('embed_at');
            if ($embed_at != 'post_bottom' && $embed_at != 'post_top')
                continue;
            if (Shortcoded::getInstance($post_id)->isShortcoded($module->getId()))
                continue;

            $priority = (int) $module->config('priority');
            if ($embed_at == 'post_top')
                $top_modules_priorities[$module_id] = $priority;
            elseif ($embed_at == 'post_bottom')
                $bottom_modules_priorities[$module_id] = $priority;
        }

        // sort by priority, keep module_id order
        $top_modules_priorities = ArrayHelper::asortStable($top_modules_priorities);
        $bottom_modules_priorities = ArrayHelper::asortStable($bottom_modules_priorities);

        // reverse for corret gluing order
        $top_modules_priorities = array_reverse($top_modules_priorities, true);
        foreach ($top_modules_priorities as $module_id => $p)
        {
            $content = $this->viewModuleData($module_id, $post_id, array()) . $content;
        }
        foreach ($bottom_modules_priorities as $module_id => $p)
        {
            $content = $content . $this->viewModuleData($module_id, $post_id, array());
        }

        return $content;
    }

    public function viewModuleData($module_id, $post_id = null, $params = array(), $content = '')
    {
        if (!$post_id)
        {
            global $post;
            $post_id = $post->ID;
        }

        $data = $this->getData($module_id, $post_id, $params);
        if (!$data)
            return '';

        //groups
        if (!empty($params['groups']))
        {
            foreach ($data as $key => $d)
            {
                if (!$d['group'] || !in_array($d['group'], $params['groups']))
                    unset($data[$key]);
            }
        }

        // product IDs
        if (!empty($params['products']))
        {
            foreach ($data as $key => $d)
            {
                if (!in_array($d['unique_id'], $params['products']))
                    unset($data[$key]);
            }
        }

        // hide fields
        if (!empty($params['hide']))
        {
            foreach ($data as $key => $d)
            {
                foreach ($params['hide'] as $hide)
                {
                    if (isset($d[$hide]))
                    {
                        if ($hide == 'title')
                            $data[$key]['_alt'] = $data[$key][$hide];
                        $data[$key][$hide] = '';
                    }
                }
            }
        }

        // sort
        if (!empty($params['sort']))
        {
            if ($params['sort'] == 'reverse')
                $data = array_reverse($data);
            elseif ($params['sort'] == 'price' || $params['sort'] == 'discount')
                $data = TemplateHelper::sortByPrice($data, $params['order'], $params['sort']);
        }

        $module = ModuleManager::factory($module_id);
        $keyword = \get_post_meta($post_id, ContentManager::META_PREFIX_KEYWORD . $module->getId(), true);

        if (!isset($this->module_data_pointer[$post_id]))
            $this->module_data_pointer[$post_id] = array();

        // next param
        if (!empty($params['next']))
        {
            if (!isset($this->module_data_pointer[$post_id][$module_id]))
                $this->module_data_pointer[$post_id][$module_id] = 0;

            $data = array_splice($data, $this->module_data_pointer[$post_id][$module_id], $params['next']);
            if (count($data) < $params['next'])
                $params['next'] = count($data);

            $this->module_data_pointer[$post_id][$module_id] += $params['next'];
        }
        elseif (!empty($params['limit']))
        {
            if (!isset($params['offset']))
                $params['offset'] = 0;

            $data = array_splice($data, $params['offset'], $params['limit']);
            $this->module_data_pointer[$post_id][$module_id] = $params['offset'] + $params['limit'];
        }
        if (!$data)
            return;

        // template
        $tpl_manager = ModuleTemplateManager::getInstance($module_id);
        if (!empty($params['template']) && $tpl_manager->isTemplateExists($params['template']))
            $template = $params['template'];
        else
            $template = $module->config('template');

        if (!empty($params['title']))
            $title = $params['title'];
        else
            $title = $module->config('tpl_title');

        if (!empty($params['cols']))
            $cols = $params['cols'];
        else
            $cols = 0;

        if (isset($params['disable_features']))
            $disable_features = $params['disable_features'];
        else
            $disable_features = 0;

        if (isset($params['btn_text']))
            $btn_text = $params['btn_text'];
        else
            $btn_text = '';

        return $tpl_manager->render($template, array('items' => $data, 'title' => $title, 'keyword' => $keyword, 'post_id' => $post_id, 'module_id' => $module_id, 'cols' => $cols, 'disable_features' => $disable_features, 'btn_text' => $btn_text, 'atts' => $params, 'content' => $content));
    }

    public function viewBlockData(array $module_ids, $post_id = null, $params = array(), $content = '')
    {
        if (!$post_id)
        {
            global $post;
            $post_id = $post->ID;
        }

        // Get modules data
        $data = array();
        foreach ($module_ids as $module_id)
        {
            $module_data = $this->getData($module_id, $post_id, $params);

            //groups filter
            if (!empty($params['groups']))
            {
                foreach ($module_data as $key => $d)
                {
                    if (!$d['group'] || !in_array($d['group'], $params['groups']))
                        unset($module_data[$key]);
                }
            }

            // product IDs filter
            if (!empty($params['products']))
            {
                foreach ($module_data as $key => $d)
                {
                    if (!in_array($d['unique_id'], $params['products']))
                        unset($module_data[$key]);
                }
            }

            // hide fields
            if (!empty($params['hide']))
            {
                foreach ($module_data as $key => $d)
                {
                    foreach ($params['hide'] as $hide)
                    {
                        if (isset($d[$hide]))
                            $module_data[$key][$hide] = '';
                    }
                }
            }

            if ($module_data)
                $data[$module_id] = $module_data;

            // shortcoded!
            if (!isset($params['shortcoded']) || (bool) $params['shortcoded'])
                Shortcoded::getInstance($post_id)->setShortcodedModule($module_id);
        }

        if (!$data)
            return;

        // template
        $tpl_manager = BlockTemplateManager::getInstance();
        if (empty($params['template']) || !$tpl_manager->isTemplateExists($params['template']))
            return;
        $template = $params['template'];

        // next, limit, offset
        if (!isset($this->block_data_pointer[$post_id]))
            $this->block_data_pointer[$post_id] = array();

        if (!empty($params['next']))
        {
            if (!isset($this->block_data_pointer[$post_id][$template]))
                $this->block_data_pointer[$post_id][$template] = 0;

            $data = $this->spliceBlockData($data, $this->block_data_pointer[$post_id][$template], $params['next'], $params['order'], $params['sort']);
            $count = $this->countBlockData($data);
            if ($count < $params['next'])
                $params['next'] = $count;
            $this->block_data_pointer[$post_id][$template] += $params['next'];
        }
        elseif (!empty($params['limit']))
        {
            if (!isset($params['offset']))
                $params['offset'] = 0;

            $data = $this->spliceBlockData($data, $params['offset'], $params['limit'], $params['order'], $params['sort']);
            $this->block_data_pointer[$post_id][$module_id] = $params['offset'] + $params['limit'];
        }
        elseif (!empty($params['order']) || !empty($params['sort']))
            $this->spliceBlockData($data, 0, 999999, $params['order'], $params['sort']);

        if (!$data)
            return;

        if (!empty($params['title']))
            $title = $params['title'];
        else
            $title = '';

        if (!empty($params['cols']))
            $cols = $params['cols'];
        else
            $cols = 0;

        return $tpl_manager->render($params['template'], array('data' => $data, 'post_id' => $post_id, 'params' => $params, 'title' => $title, 'cols' => $cols, 'sort' => $params['sort'], 'order' => $params['order'], 'groups' => $params['groups'], 'btn_text' => $params['btn_text'], 'atts' => $params, 'content' => $content));
    }

    private function spliceBlockData($data, $offset, $length, $order = null, $sort = null)
    {
        if ($order || $sort)
        {
            if (!$sort)
                $sort = 'price';

            if (!$order)
                $order = 'ask';

            if ($sort == 'price' || $sort == 'discount')
                return $this->spliceBlockDataSorted($data, $offset, $length, $order, $sort);
        }

        $results = array();
        $count = 0;
        $results_count = 0;
        foreach ($data as $module_id => $module_data)
        {
            $results[$module_id] = array();
            foreach ($module_data as $key => $data)
            {
                if ($count < $offset)
                {
                    $count++;
                    continue;
                }

                $results[$module_id][$key] = $data;
                $count++;
                $results_count++;

                if ($results_count >= $length)
                    return $results;
            }
        }
        return $results;
    }

    private function spliceBlockDataSorted($data, $offset, $length, $order = 'ask', $sort = 'price')
    {
        $all_items = TemplateHelper::sortAllByPrice($data, $order, $sort);
        $all_items = array_splice($all_items, $offset, $length);

        $results = array();
        foreach ($all_items as $item)
        {
            if (!isset($results[$item['module_id']]))
                $results[$item['module_id']] = array();

            $results[$item['module_id']][$item['unique_id']] = $item;
        }

        return $results;
    }

    private function countBlockData($data)
    {
        $count = 0;
        foreach ($data as $module_id => $module_data)
        {
            $count += count($module_data);
        }
        return $count;
    }
}
