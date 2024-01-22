<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * Shortcode class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class Shortcode {

    const shortcode = 'affegg';

    private static $instance = null;
    private $deeplink;
    private $egg_products = array();
    private $eggs = array();
    private $egg_product_pointer = array();
    private static $egg_counter = 0;
    private $ajax_eggs_params = array();
    private $geo_params = array(
        'country_code' => null,
        'country_name' => null,
        'region' => null,
        'region_name' => null,
        'city' => null,
        'continent_code' => null,
    );

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        if (LManager::isNulled())
            return;
        
        if (GeneralConfig::getInstance()->option('ajax_eggs'))
        {
            \add_action('wp_ajax_render_egg', array($this, 'printAjaxEgg'));
            \add_action('wp_ajax_nopriv_render_egg', array($this, 'printAjaxEgg'));
            \add_shortcode(self::shortcode, array($this, 'displayAjaxContainer'));
        } else
        {
            \add_shortcode(self::shortcode, array($this, 'renderEgg'));
        }

        \add_filter('term_description', 'shortcode_unautop');
        \add_filter('term_description', 'do_shortcode');
        \add_action('wp_enqueue_scripts', array($this, 'registerScripts'));
    }

    public function registerScripts()
    {
        \wp_register_style('egg-bootstrap', PLUGIN_RES . '/bootstrap/css/egg-bootstrap.css');
        \wp_register_script('bootstrap', PLUGIN_RES . '/bootstrap/js/bootstrap.min.js', array('jquery'), null, false);
        \wp_register_style('egg-products', \plugins_url('templates/style/products.css', PLUGIN_FILE), null, '' . AffiliateEgg::version());
        \wp_register_script('raphaeljs', PLUGIN_RES . '/js/morris.js/raphael.min.js', array('jquery'));
        \wp_register_script('morrisjs', PLUGIN_RES . '/js/morris.js/morris.min.js', array('raphaeljs'));
        \wp_register_style('morrisjs', PLUGIN_RES . '/js/morris.js/morris.min.css');
    }

    public function displayAjaxContainer($atts, $content = "")
    {
        $a = $this->prepareAttr($atts);
        if ($a['next'] || ($a['ajax'] !== null && !(bool) $a['ajax']))
        {
            return $this->renderEgg($atts);
        }

        if (empty($a['id']))
            return;
        $this->ajax_eggs_params[self::$egg_counter] = $a;
        \wp_enqueue_script("jquery");
        \add_action('wp_footer', array($this, 'eggJavascript'), 100);

        // include template
        $egg = $this->getEggById($a['id']);
        if (!$egg)
            return;
        TemplateManager::getInstance()->render($egg['template'], array('items' => array(), 'egg' => null, 'egg_counter' => 0, 'see_more_uri' => '', 'see_more_ga_label' => 0));

        $res = '<div id="affegg-container-' . self::$egg_counter . '"></div>';
        self::$egg_counter++;
        return $res;
    }

    private function prepareAttr($atts)
    {
        $all_attr = array(
            'id' => null,
            'limit' => 0,
            'offset' => 0,
            'next' => 0,
            'random' => 0,
            'template' => '',
            'order' => 'asc',
            'see_more' => null,
            'ajax' => null,
        );

        $all_attr = array_merge($all_attr, $this->geo_params);
        $a = shortcode_atts($all_attr, $atts);

        $order_allowed = array('rand', 'title', 'price', 'manufacturer', 'in_stock', 'create_date', 'last_update', 'last_in_stock', 'shop_id');

        $a['id'] = (int) $a['id'];
        $a['next'] = (int) $a['next'];
        $a['limit'] = (int) $a['limit'];
        $a['offset'] = (int) $a['offset'];
        $a['random'] = (int) $a['random'];

        // geo
        foreach ($this->getGeoParams() as $p)
        {
            $a[$p] = TextHelper::commaListArray($a[$p]);
        }

        if (substr($a['template'], 0, 7) == 'custom/')
            $a['template'] = 'custom/' . TextHelper::clear(substr($a['template'], 7));
        else
            $a['template'] = TextHelper::clear($a['template']);

        $a['order_asc_desc'] = 'asc';

        if ($a['order'])
        {
            $a['order'] = strtolower($a['order']);
            $o_split = explode(' ', $a['order']);
            $a['order'] = trim($o_split[0]);
            if (isset($o_split[1]))
            {
                $o_split[1] = trim($o_split[1]);
                if ($o_split[1] == 'desc')
                    $a['order_asc_desc'] = $o_split[1];
            }
            if (!in_array($a['order'], $order_allowed))
                $a['order'] = '';
        }

        return $a;
    }

    public function eggJavascript()
    {
        $params = array(
            'action' => 'render_egg',
        );

        echo
        '<script type="text/javascript" >
            var $j = jQuery.noConflict();
            $j(document).ready(function() { ';
        foreach ($this->ajax_eggs_params as $egg_num => $egg_params)
        {
            $params['nonce'] = wp_create_nonce('affegg-egg-nonce' . $egg_params['id']);

            if (!$egg_params['limit'])
                unset($egg_params['limit']);
            if (!$egg_params['offset'])
                unset($egg_params['offset']);
            if (!$egg_params['next'])
                unset($egg_params['next']);
            if (!$egg_params['random'])
                unset($egg_params['random']);
            if (!$egg_params['template'])
                unset($egg_params['template']);
            if (!$egg_params['order'])
                unset($egg_params['order']);
            if (!$egg_params['see_more'])
                unset($egg_params['see_more']);
            if ($egg_params['order_asc_desc'] == 'asc')
                unset($egg_params['order_asc_desc']);

            foreach ($this->getGeoParams() as $p)
            {
                if (!$egg_params[$p])
                    unset($egg_params[$p]);
            }

            unset($egg_params['ajax']);
            $params2['affegg'] = $egg_params;
            echo '$j("#affegg-container-' . $egg_num . '").load("' . admin_url('admin-ajax.php') . '?' . http_build_query($params) . '&' . http_build_query($params2) . '");';
        }
        echo
        '});
        </script>';
    }

    public function printAjaxEgg()
    {
        if (empty($_GET['affegg']) || !is_array($_GET['affegg']))
            die();
        if (isset($_GET['affegg']['template']))
            unset($_GET['affegg']['template']); // @todo: allow template param?
        $a = $this->prepareAttr($_GET['affegg']);

        if (!\apply_filters('affegg_dont_check_storefront_nonce', false))
            check_ajax_referer('affegg-egg-nonce' . $a['id'], 'nonce');

        echo $this->renderEgg($a);
        die();
    }

    public function renderEgg($atts, $content = "")
    {
        $a = $this->prepareAttr($atts);

        // geo
        if (!$this->checkGeo($a))
            return '';

        if (empty($a['id']))
            return;
        else
            $id = $a['id'];

        $egg = $this->getEggById($id);
        if (!$egg)
            return '';

        if (!isset($this->egg_products[$id]))
            $this->egg_products[$id] = ProductModel::model()->getEggProducts($id, $egg['prod_limit']);

        $items = $this->egg_products[$id];
        if (!$items)
            return '';

        if ($a['order'] && $a['order'] == 'rand')
        {
            shuffle($items);
        } elseif ($a['order'])
        {
            if ($a['order_asc_desc'] == 'desc')
                $sort_dir = SORT_DESC;
            else
                $sort_dir = SORT_ASC;
            self::arraySortByColumn($items, $a['order'], $sort_dir);
        }

        if ($a['next'])
        {
            if (!isset($this->egg_product_pointer[$id]))
                $this->egg_product_pointer[$id] = 0;
            $items = array_splice($items, $this->egg_product_pointer[$id], $a['next']);
            if (count($items) < $a['next'])
                $a['next'] = count($items);
            $this->egg_product_pointer[$id] += $a['next'];
        } elseif ($a['limit'])
        {
            $items = array_splice($items, $a['offset'], $a['limit']);
            $this->egg_product_pointer[$id] = $a['offset'] + $a['limit'];
        } elseif ($a['random'])
        {
            shuffle($items);
            $items = array_splice($items, 0, $a['random']);
        }

        if (!$items)
            return '';

        $items = self::prepareItems($items, $egg);

        if (!$a['template'])
            $a['template'] = $egg['template'];

        if ($a['see_more'] !== null && $a['see_more'])
            $see_more_uri = $this->getSeeMoreUri($egg['id']);
        elseif ($a['see_more'] !== null && !$a['see_more'])
            $see_more_uri = '';
        elseif (!isset($this->egg_product_pointer[$id]) || $this->egg_product_pointer[$id] == count($this->egg_products[$id]))
            $see_more_uri = $this->getSeeMoreUri($egg['id']);
        else
            $see_more_uri = '';

        $ga_label = 'affegg-' . $egg['id'];
        $see_more_ga_label = ' onclick="ga(\'send\', \'event\', \'Affiliate Egg Plugin\', \'Click See More\', \'' . esc_js($ga_label) . '\');"';
        $res = TemplateManager::getInstance()->render($a['template'], array('items' => $items, 'egg' => $egg, 'egg_counter' => self::$egg_counter, 'see_more_uri' => $see_more_uri, 'see_more_ga_label' => $see_more_ga_label));

        self::$egg_counter++;
        return $res;
    }

    public function getEggById($id)
    {
        if (!isset($this->eggs[$id]))
        {
            $egg = EggModel::model()->findByPk($id);
            if ($egg)
            {
                $this->eggs[$id] = $egg;
            } else
            {
                $this->eggs[$id] = array();
                $this->egg_products[$id] = array();
            }
        }
        return $this->eggs[$id];
    }

    public static function prepareItems(array $items, $egg)
    {
        $results = array();
        foreach ($items as $key => $item)
        {
            $item = self::prepareItem($item, $key, $egg);
            if ($item)
                $results[] = $item;
        }
        return $results;
    }

    public static function prepareItem($item, $index = null, $egg = null)
    {
        if (!$item['title'] && (int) $item['prod_type'] !== ProductModel::PROD_TYPE_IMG)
        {
            return null;
        }
        $item['price'] = (float) $item['price'];
        $item['old_price'] = (float) $item['old_price'];
        $item['price_raw'] = $item['price'];
        $item['old_price_raw'] = $item['old_price'];
        $item['currency_code'] = $item['currency'];

        if ($item['old_price'])
            $item['discount_perc'] = floor(((float) $item['old_price'] - (float) $item['price']) / (float) $item['old_price'] * 100);
        else
            $item['discount_perc'] = 0;

        if ($item['price'])
            $item['price'] = CurrencyHelper::getInstance()->numberFormat($item['price'], $item['currency_code']);
        if ($item['old_price'])
            $item['old_price'] = CurrencyHelper::getInstance()->numberFormat($item['old_price'], $item['currency_code']);
        if ($item['currency'])
            $item['currency'] = TemplateHelper::currencyTyping($item['currency']);
        else
            $item['currency'] = TemplateHelper::currencyTyping('RUB');

        /*
          if ($item['price'])
          $item['price'] = TemplateHelper::formatPriceCurrency($item['price'], $item['currency_code']);
          if ($item['old_price'])
          $item['old_price'] = TemplateHelper::formatPriceCurrency($item['old_price'], $item['currency_code']);
         * 
         */

        if ((int) $item['prod_type'] == ProductModel::PROD_TYPE_IMG)
        {
            $item['url'] = $item['orig_url'];
        } else
        {
            $item['url'] = LinkHandler::createAffUrl($item);
            
            //cashback integration
            if (GeneralConfig::getInstance()->option('cashback_integration') == 'enabled' && class_exists('\CashbackTracker\application\Plugin'))
            {
                $item['url'] = \CashbackTracker\application\components\DeeplinkGenerator::maybeAddTracking($item['url']);
            }
        }
        $item['url'] = esc_url($item['url']);

        // domain
        if (!empty($item['extra']['domain']))
            $item['domain'] = $item['extra']['domain'];
        else
            $item['domain'] = TemplateHelper::getHostName($item['orig_url']);

        // ga events
        $item['ga_event'] = '';
        if ($index && $egg && isset($egg['id']) && GeneralConfig::getInstance()->option('ga_events'))
        {
            $ga_label = 'affegg-' . $egg['id'];
            $item['ga_event'] = ' onclick="ga(\'send\', \'event\', \'Affiliate Egg Plugin\', \'Click #' . $index . '\', \'' . esc_js($ga_label) . '\', ' . $index . ');"';
        }
        return $item;
    }

    private function getSeeMoreUri($egg_id)
    {
        $see_more = GeneralConfig::getInstance()->option('see_more_link');
        if (!$see_more)
            return '';

        $catalogs = CatalogModel::model()->findAll(array('select' => 'orig_url,id,egg_id,shop_id', 'where' => array('egg_id = %d', array($egg_id))));
        if (!$catalogs)
            return '';

        $catalog = array();
        $result_uri = '';
        if ($see_more == 'single' && count($catalogs) == 1)
            $catalog = $catalogs[0];
        elseif ($see_more == 'first')
            $catalog = $catalogs[0];
        elseif ($see_more == 'last')
            $catalog = $catalogs[count($catalogs) - 1];
        else
            return '';
        return LinkHandler::createAffUrl($catalog, true);
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

    private function getGeoParams()
    {
        return array_keys($this->geo_params);
    }

    /**
     * Visitor IP checker. It uses GeoIP Detection plugin
     * @link: https://wordpress.org/plugins/geoip-detect/
     */
    private function checkGeo($a)
    {
        // geo params exists?
        $geo_params = array();
        foreach ($this->getGeoParams() as $p)
        {
            if (!empty($a[$p]))
            {
                $geo_params[] = $p;
            }
        }

        if (!$geo_params)
            return true;

        // plugin is not installed. it's impossible to check ip 
        if (!function_exists('geoip_detect_get_info_from_current_ip'))
            return false;

        // geo data
        $geo = geoip_detect_get_info_from_current_ip();
        if (!$geo || !is_object($geo))
            return false;

        // check
        foreach ($geo_params as $p)
        {
            // null option allowed
            if (in_array('null', $a[$p]) && !$geo->$p)
                continue;

            // negation
            if (in_array('!' . $geo->$p, $a[$p]))
                return false;

            $positive_list = self::getPositiveList($a[$p]);
            if ($positive_list && !in_array($geo->$p, $positive_list))
                return false;
        }

        return true;
    }

    private static function getPositiveList($list)
    {
        $res = array();
        foreach ($list as $l)
        {
            if ($l[0] == '!')
                continue;
            $res[] = $l;
        }
        return $res;
    }

}
