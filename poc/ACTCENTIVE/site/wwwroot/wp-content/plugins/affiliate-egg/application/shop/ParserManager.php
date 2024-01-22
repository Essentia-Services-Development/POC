<?php

namespace Keywordrush\AffiliateEgg;

/**
 * ParserManager class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class ParserManager {

    const HTTP_ARG_ID_SEARCH = 'search';

    private $domain2id;
    private static $parsers = array();
    private static $instance;

    private function __construct()
    {
        
    }

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    public function getParserById($shop_id)
    {
        return $this->getParser($shop_id);
    }

    private function getParser($shop_id)
    {
        $shop = ShopManager::getInstance()->getItem($shop_id);
        $parser_name = ucfirst($shop_id) . 'Parser';
        if (!isset(self::$parsers[$parser_name]))
        {
            if ($shop->is_custom)
                $parser_file = $shop->file;
            else
                $parser_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'parsers' . DIRECTORY_SEPARATOR . $parser_name . '.php';

            if (is_file($parser_file))
                require_once($parser_file);
            else
                throw new \Exception("Parser for '{$parser_name}' not found.");

            if (class_exists(NS . $parser_name, false) === false)
                throw new \Exception("Parser for '{$parser_name}' not found.");

            $ns_parser_name = NS . $parser_name;
            $parser = new $ns_parser_name;
            if (!($parser instanceof NS . 'ShopParser'))
                throw new \Exception("The parser '{$parser}' must inherit from ShopParser.");

            self::$parsers[$parser_name] = $parser;
        }
        return self::$parsers[$parser_name];
    }

    public function parseProduct($url, \DomXPath $xpath = null, $html = '')
    {
        return $this->getParserByUrl($url, null, $xpath, $html)->parseProduct();
    }

    public function parseCatalog($url, $max, $http_arg_id = null, \DomXPath $xpath = null, $html = '')
    {
        $urls = $this->getParserByUrl($url, $http_arg_id, $xpath, $html)->parseCatalog($max);
        if (!$urls)
            $urls = array();
        $urls = array_unique($urls);
        $urls = array_slice($urls, 0, $max);
        $base = parse_url($url);
        $base_uri = $base['scheme'] . '://' . $base['host'];
        $urls = TextHelper::fixFullUrls($urls, $base_uri);
        return $urls;
    }

    public function parseSearchCatalog($shop_id, $keyword, $max)
    {
        $shop = ShopManager::getInstance()->getItem($shop_id);
        if (!$shop)
            throw new \Exception("Shop ID not found.");

        $charset = $this->getParser($shop->getId())->getCharset();
        if (strtolower($charset) != 'utf-8')
            $keyword = iconv('utf-8', $charset, $keyword);

        if (class_exists('\ContentEgg\application\admin\GeneralConfig'))
            $lang = \ContentEgg\application\admin\GeneralConfig::getInstance()->option('lang');
        else
            $lang = self::getDefaultLang();
        $url = $shop->getSearchUri();
        $url = str_replace('%KEYWORD%', rawurlencode($keyword), $url);
        $url = str_replace('%KEY-WORD%', urlencode(str_replace(' ', '-', $keyword)), $url);
        $url = str_replace('%KEY--WORD%', urlencode(str_replace(' ', '--', $keyword)), $url);
        $url = str_replace('%KEY+WORD%', urlencode(str_replace(' ', '+', $keyword)), $url);
        if (strstr($url, '%KEY++WORD%'))
            $url = str_replace('%2B', '+', str_replace('%KEY++WORD%', urlencode(str_replace(' ', '+', $keyword)), $url));

        $url = str_replace('%KEY+WORD%', urlencode(str_replace(' ', '+', $keyword)), $url);
        $url = str_replace('%KEY_WORD%', urlencode(str_replace(' ', '_', $keyword)), $url);
        $url = str_replace('%LANG%', $lang, $url);
        // buecher.de search
        $url = str_replace('%BUECHERDE%', base64_encode('query=' . urlencode($keyword) . '&results=15'), $url);
        // biougnach.ma
        $url = str_replace('%BIOOUGNACH%', base64_encode(urlencode(urlencode($keyword))), $url);
        
        return $this->parseCatalog($url, $max, self::HTTP_ARG_ID_SEARCH);
    }

    public function getParserByUrl($url, $http_arg_id = null, \DomXPath $xpath = null, $html = '')
    {
        $shop_id = $this->getShopIdByUrl($url);
        if (!$shop_id)
            throw new \Exception("No parser found for this url.");
        $parser = $this->getParserById($shop_id);
        $parser->setUrl($url, $http_arg_id);

        // EI compatibility
        if ($html)
            $parser->getDomStr($html);

        if ($xpath)
            $parser->xpath = $xpath;
        else
            $parser->loadXPath($parser->getUrl());

        return $parser;
    }

    public function getShopIdByUrl($url)
    {
        if (!$this->domain2id)
        {
            $shops = ShopManager::getInstance()->getItems();
            foreach ($shops as $id => $item)
            {
                $this->domain2id[$this->normaliseDomain($item->uri)] = $id;
            }
        }
        if (!$url = $this->normaliseDomain($url))
            return false;
        
        if (array_key_exists($url, $this->domain2id))
            return $this->domain2id[$url];
        else
            return false;
    }

    private function normaliseDomain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        $domain = preg_replace('/^www\./i', '', $domain);
        if (preg_match('/\.jd\.com$/i', $domain))
            return 'jd.com';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,7})$/i', $domain, $regs))
            return strtolower($regs['domain']);

        return false;
    }

    public static function getDefaultLang()
    {
        $locale = \get_locale();
        $lang = explode('_', $locale);
        return $lang[0];
    }

    public function isExporterExists($url)
    {
        if (!$shop_id = $this->getShopIdByUrl($url))
            return false;

        if ($this->getParserById($shop_id))
            return true;
        else
            return false;
    }

}
