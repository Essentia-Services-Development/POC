<?php

namespace Keywordrush\AffiliateEgg;

/**
 * ShopManager class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class ShopManager {

    const CUSTOM_PARSER_DIR = 'affegg-parsers';

    private $items = array();
    private static $instance;

    private function __construct()
    {
        $config = $this->getParsers();
        foreach ($config as $id => $s)
        {
            $item = new Shop($id);
            foreach ($s as $key => $value)
            {
                if (property_exists($item, $key))
                    $item->$key = $value;
            }
            $this->items[$id] = $item;
        }
    }

    private function getParsers()
    {
        $parsers = require (dirname(__FILE__) . '/config.php');
        return array_merge($parsers, $this->getCustomParsers());
    }

    private function getCustomParsers()
    {
        $parsers = array();
        foreach (self::getCustomParsersDirs() as $dir)
        {
            $parsers = array_merge($parsers, $this->scanCustomParsers($dir));
        }
        return $parsers;
    }

    private function scanCustomParsers($path)
    {
        if (!is_dir($path))
            return array();
        $files = glob($path . '/' . '*Parser.php');
        $parsers = array();
        foreach ($files as $file)
        {
            $id = strtolower(basename($file, 'Parser.php'));
            $data = get_file_data($file, array('name' => 'Name', 'uri' => 'URI', 'cpa' => 'CPA', 'ico' => 'Icon', 'search_uri' => 'Search URI'));
            if (empty($data['uri']) || !FormValidator::valid_url($data['uri']))
                continue;

            $host = TextHelper::getHostName($data['uri']);
            if (empty($data['name']))
                $data['name'] = ucfirst($host);
            if (empty($data['ico']))
                $data['ico'] = 'http://www.google.com/s2/favicons?domain=' . $host;
            if (!empty($data['search_uri']) && !FormValidator::valid_url($data['search_uri']))
                $data['search_uri'] = '';

            if ($data['cpa'])
            {
                $data['cpa'] = array_map('trim', explode(',', $data['cpa']));
                $data['cpa'] = array_uintersect($data['cpa'], Cpa::getCpaIds(), 'strcasecmp');
            }
            $data['file'] = $file;
            $data['is_custom'] = true;
            $parsers[$id] = $data;
        }
        return $parsers;
    }

    public static function getCustomParsersDirs()
    {
        return array(
            \get_stylesheet_directory() . '/' . self::CUSTOM_PARSER_DIR, //child theme 
            \get_template_directory() . '/' . self::CUSTOM_PARSER_DIR, // theme
            \WP_CONTENT_DIR . '/' . self::CUSTOM_PARSER_DIR,
        );
    }

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getItem($id)
    {
        if (isset($this->items[$id]))
            return $this->items[$id];
        else
            null;
    }

    public function getActiveItems($sort = false, $sorted_custom = false, $sorted_deprecated = false)
    {
        $res = array();
        $custom = array();
        $deprecated = array();
        foreach ($this->items as $item)
        {
            if (!$item->isActive())
                continue;

            if ($sorted_custom && $item->isCustom())
                $custom[] = $item;
            elseif ($sorted_deprecated && $item->isDeprecated())
                $deprecated[] = $item;
            else
                $res[] = $item;
        }
        if ($sort)
        {
            sort($res);
            sort($custom);
            sort($deprecated);
        }

        $res = array_merge($custom, $res, $deprecated);
        return $res;
    }

    public function getItemList()
    {
        $result = array();
        foreach ($this->items as $id => $item)
        {
            $result[$id] = $item->name;
        }
        return $result;
    }

    public function getItemIdList()
    {
        return array_keys($this->items);
    }

    public function getSearchableItems($sort = false, $active_only = false)
    {
        $res = array();
        foreach ($this->items as $item)
        {
            if ($active_only && !$item->isActive())
                continue;

            if ($item->isSearchUriExists())
                $res[] = $item;
        }
        if ($sort)
            sort($res);
        return $res;
    }

    public function getSearchableItemsList($sort = false, $active_only = false, $pretty = false)
    {
        $items = $this->getSearchableItems($sort, $active_only);
        $list = array();
        foreach ($items as $item)
        {
            if ($pretty)
                $name = $item->getName() . ' <a href="' . $item->uri . '" target="_blank"><img src="' . $item->ico . '"/></a>';
            else
                $name = $item->getName();

            $list[$item->getId()] = $name;
        }
        return $list;
    }

    public function getShopName($id)
    {
        $item = $this->getItem($id);
        if (!$item)
            return '';
        return $item->name;
    }

    public function getShopUri($id)
    {
        $item = $this->getItem($id);
        if (!$item)
            return '';
        return $item->uri;
    }

    public function getSortedListByCurrency($ignor_custom = false)
    {
        $result = array();
        foreach ($this->getActiveItems(true) as $shop)
        {
            if ($ignor_custom && $shop->isCustom())
                continue;
            $currency = $shop->getDefaultCurrency();
            if (!isset($result[$currency]))
                $result[$currency] = array();
            $result[$currency][] = $shop;
        }
        ksort($result);
        return $result;
    }

}
