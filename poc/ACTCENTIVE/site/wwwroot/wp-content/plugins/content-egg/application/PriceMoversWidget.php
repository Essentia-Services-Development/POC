<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\CEWidget;
use ContentEgg\application\models\PriceHistoryModel;
use ContentEgg\application\components\WidgetTemplateManager;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\components\ContentProduct;

/**
 * PriceMoversWidget class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class PriceMoversWidget extends CEWidget {

    const shortcode = 'content-egg-price-movers';

    public function __construct()
    {
        parent::__construct();
        $this->addShortcode();
    }

    public function slug()
    {
        return 'cegg_price_movers';
    }

    public function description()
    {
        return __('Products with the biggest price drops.', 'content-egg');
    }

    protected function name()
    {
        return __('CE: Price Movers', 'content-egg');
    }

    public function classname()
    {
        return 'widget cegg_widget_products';
    }

    public function settings($force = false)
    {
        /*
        if (!$force && (empty($GLOBALS['pagenow']) || ($GLOBALS['pagenow'] != 'widgets.php' && $GLOBALS['pagenow'] != 'admin-ajax.php')))
            return array();
         * 
         */

        return
                array(
                    'title' => array(
                        'type' => 'text',
                        'default' => __('Price Drops', 'content-egg-tpl'),
                        'title' => __('Title', 'content-egg'),
                    ),
                    'limit' => array(
                        'type' => 'number',
                        'min' => 1,
                        'max' => 30,
                        'default' => 5,
                        'title' => __('Number of products to show', 'content-egg'),
                    ),
                    'drop_type' => array(
                        'type' => 'select',
                        'default' => 'absolute',
                        'title' => __('Drop type', 'content-egg'),
                        'options' => array(
                            'absolute' => __('Biggest absolute', 'content-egg'),
                            'relative' => __('Biggest relative', 'content-egg'),
                        //'recent' => __('Most recent', 'content-egg'),
                        )
                    ),
                    'direction' => array(
                        'type' => 'select',
                        'default' => 'drops',
                        'title' => __('Direction', 'content-egg'),
                        'options' => array(
                            'drops' => __('Price drops', 'content-egg'),
                            'increases' => __('Price increases', 'content-egg'),
                        )
                    ),
                    'last_update' => array(
                        'type' => 'select',
                        'default' => 7,
                        'title' => __('Last update', 'content-egg'),
                        'options' => array(
                            1 => __('1 day ago', 'content-egg'),
                            2 => sprintf(__('%d days ago', 'content-egg'), 2),
                            3 => sprintf(__('%d days ago', 'content-egg'), 3),
                            4 => sprintf(__('%d days ago', 'content-egg'), 4),
                            5 => sprintf(__('%d days ago', 'content-egg'), 5),
                            6 => sprintf(__('%d days ago', 'content-egg'), 6),
                            7 => sprintf(__('%d days ago', 'content-egg'), 7),
                            21 => sprintf(__('%d days ago', 'content-egg'), 21),
                            30 => sprintf(__('%d days ago', 'content-egg'), 30),
                        )
                    ),
                    'template' => array(
                        'type' => 'select',
                        'default' => 'wdgt_price_movers_grid',
                        'title' => __('Template', 'content-egg'),
                        'options' => WidgetTemplateManager::getInstance($this->slug())->getTemplatesList()
                    ),
        );
    }

    /**
     * Front-end display of widget.
     */
    public function widget($args, $instance)
    {
        $items = $this->getItems($instance);
        if (!$items)
            return;

        $this->beforeWidget($args, $instance);
        $tpl_manager = WidgetTemplateManager::getInstance($this->slug());
        if (!$tpl_manager->isTemplateExists($instance['template']))
            return;

        echo $tpl_manager->render($instance['template'], array('items' => $items, 'is_shortcode' => false, 'btn_text' => '')); // phpcs:ignore

        $this->afterWidget($args, $instance);
    }

    private function getItems(array $instance)
    {

        $cache_key = $this->getCacheKey($instance);
        $items = $this->getCache($cache_key);
        if ($items === null)
        {
            $products = PriceHistoryModel::model()->getPriceMovers($instance, true);
            $active_parsers = array_keys(ModuleManager::getInstance()->getAffiliateParsers(true, true));
            $items = array();
            $outofstock_product = GeneralConfig::getInstance()->option('outofstock_product');
            foreach ($products as $product)
            {
                if (!in_array($product['module_id'], $active_parsers))
                    continue;
                $item = ContentManager::getProductbyUniqueId($product['unique_id'], $product['module_id'], $product['post_id'], $instance);
                if (!$item)
                    continue;

                if ($outofstock_product == 'hide_product' && isset($item['stock_status']) && $item['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
                    continue;

                $product['discount_percent'] = ceil(100 - ($product['price'] * 100) / $product['price_old']);
                $product['discount_value'] = $product['price_old'] - $product['price'];
                $product['create_date'] = strtotime($product['create_date']);
                $product['price_old_date'] = strtotime($product['price_old_date']);
                $item['_price_movers'] = $product;
                $items[] = $item;

                if (count($items) >= $instance['limit'])
                    break;
            }
            $this->setCache($items, $cache_key, 3600);
        }
        return $items;
    }

    private function getCacheKey(array $instance)
    {
        $str = '';
        foreach ($instance as $k => $v)
        {
            if (!is_array($v))
                $str .= $k . $v;
        }
        return md5($str);
    }

    public function addShortcode()
    {
        \add_shortcode(self::shortcode, array($this, 'viewShortcode'));
    }

    public function viewShortcode($atts, $content = "")
    {
        $a = $this->prepareAttr($atts);

        $items = $this->getItems($a);
        if (!$items)
            return;

        $tpl_manager = WidgetTemplateManager::getInstance($this->slug());
        if (empty($a['template']) || !$tpl_manager->isTemplateExists($a['template']))
            return;

        return $tpl_manager->render($a['template'], array('items' => $items, 'is_shortcode' => true, 'cols' => $a['cols'], 'btn_text' => ''));
    }

    private function prepareAttr($atts)
    {
        $settings = $this->settings(true);

        $defaults = array();
        foreach ($settings as $name => $setting)
        {
            if (isset($setting['default']))
                $defaults[$name] = $setting['default'];
            else
                $defaults[$name] = '';
        }
        $defaults['template'] = 'wdgt_price_movers_list';
        $defaults['cols'] = 0;
        $defaults['currency'] = '';

        $a = \shortcode_atts($defaults, $atts);
        $a['limit'] = (int) $a['limit'];
        $a['cols'] = (int) $a['cols'];
        $a['last_update'] = (int) $a['last_update'];
        $a['template'] = \sanitize_text_field($a['template']);
        $a['currency'] = strtoupper(TextHelper::clear($a['currency']));

        return $a;
    }

}
