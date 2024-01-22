<?php

namespace Keywordrush\AffiliateEgg;

use Keywordrush\AffiliateEgg\TextHelper;

/**
 * ShopParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
abstract class ShopParser {

    const DEBUG_MODE = false;

    protected $charset = 'utf-8';
    protected $currency = 'USD';
    public $xpath;
    protected $dom;
    protected $url;
    protected $item = array();
    protected $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:104.0) Gecko/20100101 Firefox/104.0';
    protected $headers = array();
    protected $http_arg_sets = array();
    protected $http_arg_set_id;

    abstract public function parseCatalog($max);

    abstract public function parseTitle();

    abstract public function parsePrice();

    abstract public function parseImg();

    public function parseDescription()
    {
        return;
    }

    public function parseOldPrice()
    {
        return;
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();
        if($features = $this->parseFeatures())
            $extra['features'] = $features;
        
        return $extra;
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function isInStock()
    {
        return true;
    }

    public function __construct($url = null, $http_arg_set_id = '')
    {
        if ($url)
        {
            $this->setUrl($url, $http_arg_set_id);
            $this->loadXPath($this->url);
        }
    }

    protected function getUserAgent()
    {
        return $this->user_agent;
    }

    public function setUrl($url, $http_arg_set_id = null)
    {
        $this->url = $url;
        $this->http_arg_set_id = $http_arg_set_id;
        $this->xpath = null;
        $this->item = array();
    }

    public function getUrl()
    {
        return $this->url;
    }

    protected function preParseProduct()
    {
        
    }

    public function parseProduct()
    {
        $this->preParseProduct();

        $base = parse_url($this->getUrl());
        $base_uri = $base['scheme'] . '://' . $base['host'];

        $this->item['title'] = \wp_encode_emoji(\sanitize_text_field(html_entity_decode($this->parseTitle())));
        if (!$this->item['title'])
            throw new \Exception("Can not parse the item.");

        $this->item['description'] = \normalize_whitespace(\wp_encode_emoji(TextHelper::sanitizeHtml(html_entity_decode($this->parseDescription()))));
        $this->item['description'] = TextHelper::removeExtraBr($this->item['description']);
        $this->item['description'] = TextHelper::removeExtraBreaks($this->item['description']);

        $description_max_size = GeneralConfig::getInstance()->option('description_max_size');
        if ($description_max_size)
            $this->item['description'] = TextHelper::truncate($this->item['description'], $description_max_size);
        $this->item['price'] = TextHelper::parsePriceAmount($this->parsePrice());

        $img = $this->parseImg();
        if (is_array($img))
            $img = reset($img);
        $this->item['orig_img'] = TextHelper::fixFullUrl(strip_tags($img), $base_uri);
        if (!FormValidator::valid_url($this->item['orig_img']))
            $this->item['orig_img'] = null;
        $this->item['orig_img_large'] = TextHelper::fixFullUrl(strip_tags($this->parseImgLarge()), $base_uri);
        if (!FormValidator::valid_url($this->item['orig_img_large']))
            $this->item['orig_img_large'] = null;
        $this->item['img'] = $this->item['orig_img'];
        $this->item['old_price'] = TextHelper::parsePriceAmount($this->parseOldPrice());
        if ($this->item['old_price'] <= $this->item['price'])
            $this->item['old_price'] = 0;

        $manufacturer = $this->parseManufacturer();
        if (is_string($manufacturer))
            $this->item['manufacturer'] = \wp_encode_emoji(\sanitize_text_field(html_entity_decode($manufacturer)));
        else
            $this->item['manufacturer'] = '';
        $comments_max_count = GeneralConfig::getInstance()->option('comments_max_count');

        $this->item['orig_url'] = $this->getUrl();
        $this->item['extra']['domain'] = TemplateHelper::getHostName($this->item['orig_url']);

        if ($this->isInStock())
            $this->item['in_stock'] = ProductModel::IN_STOCK;
        else
            $this->item['in_stock'] = ProductModel::NOT_IN_STOCK;
        $this->item['currency'] = $this->getCurrency();

        $this->item['extra'] = $this->parseExtra($comments_max_count);
        if (empty($this->item['extra']['comments']))
            $this->item['extra']['comments'] = array();
        if ($comments_max_count > 0)
            $this->item['extra']['comments'] = array_slice($this->item['extra']['comments'], 0, $comments_max_count);
        elseif ($comments_max_count != 0)
            $this->item['extra']['comments'] = array();

        foreach ($this->item['extra']['comments'] as $i => $comment)
        {
            if (isset($comment['name']))
                $this->item['extra']['comments'][$i]['name'] = \wp_encode_emoji(\sanitize_text_field(html_entity_decode($comment['name'])));
            $this->item['extra']['comments'][$i]['comment'] = \wp_encode_emoji(\sanitize_text_field(html_entity_decode($comment['comment'])));
        }
        return $this->item;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function parseCurrency()
    {
        return '';
    }

    public function getCurrency()
    {
        if ($res = $this->parseCurrency())
            return $res;
        else
            return $this->currency;
    }

    public function loadXPathStr($html)
    {
        $this->xpath = new \DomXPath($this->getDomStr($html));
    }

    public function loadXPath($url)
    {
        $this->xpath = $this->getXPath($url);
    }

    public function getXPath($url)
    {
        return new \DomXPath($this->getDom($url));
    }

    public function getDomStr($html)
    {
        $dom = new \DomDocument();
        $dom->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        $this->xpath = null;
        $this->dom = null;
        if (!$dom->loadHTML($html))
            throw new \Exception('Can\'t load DOM Document.');
        $this->dom = $dom;
        return $this->dom;
    }

    public function getDom($url)
    {
        return $this->getDomStr($this->restPostGet($url));
    }

    public function requestGet($url, $fix_encoding = true)
    {
        return $this->restPostGet($url, $fix_encoding);
    }

    public function restPostGet($url, $fix_encoding = true)
    { 
        $url = \apply_filters('affegg_parse_product_url', $url);

        // Rectional between #! URL to _escaped_fragment_ URL
        $url = str_replace('#!', '?_escaped_fragment_=', $url);
        // custom cookies via admin config
        $headers = $this->headers;
        $shop_id = ParserManager::getInstance()->getShopIdByUrl($url);
        if ($shop_id && $cookie = CookiesConfig::getInstance()->option($shop_id))
        {
            $headers['Cookie'] = $cookie;
        }

        $user_agent = $this->getUserAgent();
        if (is_array($user_agent))
            $ua = $user_agent[array_rand($user_agent)];
        else
            $ua = $user_agent;
        $args = array(
            'method' => 'GET',
            'timeout' => 60,
            'redirection' => 5,
            'sslverify' => false,
            'user-agent' => $ua,
            'headers' => $headers,
            'body' => null,
            'cookies' => array()
        );

        // custom http request args "set" for parser (search mode)
        if ($this->http_arg_set_id && !empty($this->http_arg_sets[$this->http_arg_set_id]))
        {
            $custom_args = $this->http_arg_sets[$this->http_arg_set_id];
            foreach ($custom_args as $key => $value)
            {
                if (array_key_exists($key, $args))
                    $args[$key] = $value;
            }
        }

        $is_proxy = CurlProxy::initProxy($url);

        ScrapFactory::init();

        $url = \apply_filters('affegg_create_from_url', $url, $args);
        
        $response = \wp_remote_request($url, $args);
        if (\is_wp_error($response))
        {
            if ($is_proxy)
                CurlProxy::clearTransientData();
            $error_message = $response->get_error_message();
            throw new \Exception($error_message);
        }
        $response_code = (int) \wp_remote_retrieve_response_code($response);

        if (self::DEBUG_MODE)
        {
            $file_name = md5($url);
            $contents = 'URL: ' . $url . "\r\n";
            $contents .= 'Response Code: ' . $response_code . "\r\n";
            $contents .= "\r\n\r\n\r\n\r\n\r\n";
            self::seveDebugFile($file_name, $contents . \wp_remote_retrieve_body($response));
        }
        if ($response_code != 200 && $response_code != 206)
            throw new \Exception('Error in url request. HTTP response code: ' . $response_code, $response_code);

        $body = \wp_remote_retrieve_body($response);
        return $this->decodeCharset($body, $fix_encoding);
    }

    public function decodeCharset($str, $fix_encoding = true)
    {
        $encoding_hint = '';
        if ($fix_encoding)
            $encoding_hint = '<?xml encoding="UTF-8">';
        
        if (strtolower($this->charset) != 'utf-8')
        {
            if ($fix_encoding)
                $encoding_hint .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $str = $encoding_hint . $str;
            $result = iconv($this->charset, 'UTF-8//TRANSLIT//IGNORE', $str);

            
        } else
            $result = $encoding_hint . $str;

        //This will convert all non-ascii characters into an html named or numeric character entity. 
        return mb_convert_encoding($result, 'HTML-ENTITIES', 'UTF-8');
    }

    protected function xpathScalar($path, $return_child = false)
    {
        if (is_array($path))
            return $this->xpathScalarMulty($path, $return_child);

        $res = $this->xpath->query($path);
        if ($res && $res->length > 0)
        {
            if ($return_child)
            {
                foreach ($res as $tag)
                {
                    return $this->xpathReturnChild($tag);
                }
            }

            return trim(strip_tags($res->item(0)->nodeValue));
        } else
            return null;
    }

    protected function xpathScalarMulty(array $paths, $return_child = false)
    {
        foreach ($paths as $path)
        {
            if ($r = $this->xpathScalar($path, $return_child))
                return $r;
        }
        return $r;
    }

    protected function xpathArray($path, $return_child = false)
    {
        if (is_array($path))
            return $this->xpathArrayMulty($path, $return_child);

        $res = $this->xpath->query($path);
        $return = array();
        if ($res && $res->length > 0)
        {
            foreach ($res as $tag)
            {
                if ($return_child)
                    $return[] = $this->xpathReturnChild($tag);
                else
                    $return[] = trim(strip_tags($tag->nodeValue));
            }
        }
        return $return;
    }

    protected function xpathArrayMulty(array $paths, $return_child = false)
    {
        foreach ($paths as $path)
        {
            if ($r = $this->xpathArray($path, $return_child))
                return $r;
        }
        return $r;
    }

    private function xpathReturnChild($tag)
    {
        $innerHTML = '';

        // see http://fr.php.net/manual/en/class.domelement.php#86803
        $children = $tag->childNodes;
        foreach ($children as $child)
        {
            $tmp_doc = new \DOMDocument();
            $tmp_doc->appendChild($tmp_doc->importNode($child, true));
            $innerHTML .= $tmp_doc->saveHTML();
        }

        return trim($innerHTML);
    }

    public function getDefaultCurrency()
    {
        return $this->currency;
    }

    public function getRemoteJson($uri, array $headers = array())
    {
        $this->headers = array(
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-us,en;q=0.5',
            'Cache-Control' => 'no-cache',
        );
        
        $this->headers = array_merge($this->headers, $headers);        
        
        try
        {
            $result = $this->requestGet($uri);
        } catch (\Exception $e)
        {
            return false;
        }
        
        $this->headers = array();
        $result = str_replace('<?xml encoding="UTF-8">', '', $result);

        if (!$result = json_decode($result, true))
            return false;

        return $result;
    }
    
    public function getFeaturesXpath()
    {
        return array();
    }
    
    public function parseFeatures()
    {
        if (!$xpaths = $this->getFeaturesXpath())
            return array();

        if (isset($xpaths['name']) || isset($xpaths['name-value']))
            $xpaths = array($xpaths);

        foreach ($xpaths as $xpath)
        {
            $names = $values = array();

            if (isset($xpath['name-value']))
            {
                if (!$name_values = $this->xpathArray($xpath['name-value']))
                    continue;

                if (isset($xpath['separator']))
                    $separator = $xpath['separator'];
                else
                    $separator = ':';

                foreach ($name_values as $name_value)
                {
                    $parts = explode($separator, $name_value, 2);
                    if (count($parts) !== 2)
                        continue;

                    $names[] = $parts[0];
                    $values[] = $parts[1];
                }
            } elseif (isset($xpath['name']) && isset($xpath['value']))
            {
                $names = $this->xpathArray($xpath['name']);
                $values = $this->xpathArray($xpath['value']);
            }
            
            if (!$names || !$values || count($names) != count($values))
                continue;

            $features = array();
            for ($i = 0; $i < count($names); $i++)
            {
                $feature = array();
                $feature['name'] = ucfirst(\sanitize_text_field(trim($names[$i], " \r\n:-")));
                $feature['value'] = trim(\sanitize_text_field($values[$i]), " \r\n:-");
                if (in_array($feature['name'], array('Condition', 'Customer Reviews')))
                    continue;
                $features[] = $feature;
            }

            if ($features)
                return $features;
        }
        return array();
    }    


    /*
      public static function seveDebugFile($file_name, $contents)
      {
      $uploads = \wp_upload_dir();
      $save_dir = $uploads['basedir'] . '/' . 'ae-debug';
      $file_name .= '.html';
      $file_name = \wp_unique_filename($save_dir, $file_name);
      if (!file_put_contents($save_dir . '/' . $file_name, $contents))
      return false;
      }
     * 
     */
}
