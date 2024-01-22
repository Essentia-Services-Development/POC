<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * LinkHandler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class LinkHandler {

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    public function getRedirectLink()
    {
        
    }

    public static function getRedirectPrefixProduct()
    {
        return TextHelper::clear(\apply_filters('affegg_product_redirect_prefix', 'affegg'));
    }

    public static function getRedirectPrefixCatalog()
    {
        return TextHelper::clear(\apply_filters('affegg_catalog_redirect_prefix', 'affeggc'));
    }

    public static function createAffUrl($item, $is_catalog = false, $allow_redirect = true)
    {
        if (empty($item['orig_url']) || empty($item['id']) || empty($item['egg_id']))
            return '';
        $url = $item['orig_url'];

        // profitshare fix. return if url already created
        if (strstr($url, '/l.profitshare.ro/'))
            return $url;

        $sub_id = '';
        if (GeneralConfig::getInstance()->option('set_ext_subid'))
        {
            $sub_id = TextHelper::subidClear(filter_input(INPUT_GET, 'affegg_subid'));
        }

        if ($allow_redirect && GeneralConfig::getInstance()->option('set_local_redirect'))
        {
            if ($is_catalog)
                $redirect = self::getRedirectPrefixCatalog();
            else
                $redirect = self::getRedirectPrefixProduct();

            return self::getLocalRedirectUrl($item['id'], $sub_id, $redirect);
        }
        if (empty($item['shop_id']))
        {
            $item['shop_id'] = ParserManager::getInstance()->getShopIdByUrl($url);
            // parser not found
            if (!$item['shop_id'])
                return $url;
        }

        $deeplink = DeeplinkConfig::getInstance()->option($item['shop_id']);
        $deeplink = self::getMultiDeeplink($deeplink, $url);

        if (!$deeplink)
        {
            return $url;
        } elseif (substr(trim($deeplink), 0, 7) == '[regex]')
        {
            // regex preg_replace
            return self::getRegexReplace($url, $deeplink);
        } elseif (substr(trim($deeplink), 0, 13) == '[profitshare]')
        {
            // ProfitShare link creator
            return self::getProfitshareLink($url, $deeplink);
        } elseif ($link = CustomDeeplink::getInstance()->getLink($url, $deeplink))
        {
            // custom deeplink
            return $link;
        } elseif (strstr($deeplink, '{{') && strstr($deeplink, '}}'))
        {
            // teplate deeplink
            return self::getUrlWithTemplate($url, $deeplink, $item);
        } elseif (!preg_match('/^https?:\/\//i', $deeplink))
        {
            // url with tail
            return self::getUrlWithTail($url, $deeplink);
        } else
        {
            // deeplink
            $priority = 0;
            $subid = '';
            if ($sub_id)
            {
                $subid = $sub_id;
                $priority = 1;
            } elseif ($item['egg_id'] && GeneralConfig::getInstance()->option('set_subid'))
            {
                $subid = 'affegg-' . $item['egg_id'];
            }
            if ($subid)
                $deeplink = Cpa::deeplinkSetSubid($deeplink, $subid, $priority);
            return $deeplink . urlencode($url);
        }
    }

    public static function getRegexReplace($url, $regex)
    {
        $regex = trim($regex);

        $parts = explode('][', $regex);
        if (count($parts) != 3)
            return $url;

        $pattern = $parts[1];
        $replacement = substr($parts[2], 0, -1);

        // null character allows a premature regex end and "/../e" injection
        if (strpos($pattern, chr(0)) !== false || !trim($pattern))
            return $url;

        if ($result = @preg_replace($pattern, $replacement, $url))
            return $result;
        else
            return $url;
    }

    private static function getLocalRedirectUrl($id, $affegg_subid, $redirect)
    {
        $sub_id = '';
        if (GeneralConfig::getInstance()->option('set_ext_subid') && $affegg_subid)
        {
            $delim = (get_option('permalink_structure')) ? "/?" : "&";
            $sub_id = $delim . "affegg_subid=" . $affegg_subid;
        }
        $path = (get_option('permalink_structure')) ? $redirect . "/" : "?" . $redirect . "=";
        $path .= $id . $sub_id;
        $url = get_site_url(get_current_blog_id(), $path);
        return $url;
    }

    public static function redirect()
    {
        global $wp;

        $id = null;
        $affegg_subid = TextHelper::subidClear(filter_input(INPUT_GET, 'affegg_subid'));
        $affegg_var = self::getRedirectPrefixProduct();
        $affeggc_var = self::getRedirectPrefixCatalog();
        if (!get_option('permalink_structure'))
        {
            $affegg = filter_input(INPUT_GET, $affegg_var);
            $affeggc = filter_input(INPUT_GET, $affeggc_var);
            if ($affeggc)
            {
                $id = (int) $affeggc;
                $redirect = $affeggc_var;
            } elseif ($affegg)
            {
                $id = (int) $affegg;
                $redirect = $affegg_var;
            }
        } elseif (preg_match("/(" . $affegg_var . "|" . $affeggc_var . ")\/(\d+)$/msi", $wp->request, $match))
        {
            $id = (int) $match[2];
            $redirect = $match[1];
        }
        if ($id)
        {
            if ($redirect == $affegg_var)
            {
                $item = ProductModel::model()->findByPk($id);
                $is_catalog = false;
            } elseif ($redirect == $affeggc_var)
            {
                $item = CatalogModel::model()->findByPk($id);
                $is_catalog = true;
            }
            if ($item)
            {
                $url = esc_url_raw(self::createAffUrl($item, $is_catalog, false));

                //cashback integration
                if (GeneralConfig::getInstance()->option('cashback_integration') == 'enabled' && class_exists('\CashbackTracker\application\Plugin'))
                {
                    $url = \CashbackTracker\application\components\DeeplinkGenerator::maybeAddTracking($url);
                }
                \wp_redirect($url, 301);
                exit;
            } else
            {
                \wp_redirect(\get_site_url(\get_current_blog_id()), 301);
                exit;
            }
        }
    }

    public static function getUrlWithTail($url, $tail)
    {
        // replace params in URL
        parse_str($tail, $vars);
        if (count($vars) == 1 && strstr($tail, '='))
            return \add_query_arg($vars, $url);

        $tail = preg_replace('/^[?&]/', '', $tail);
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query)
            $url .= '&';
        else
            $url .= '?';

        parse_str($tail, $tail_array);
        $url .= http_build_query($tail_array);
        return $url;
    }

    public static function getUrlWithTemplate($url, $template, $item = array())
    {
        $template = str_replace('{{url}}', $url, $template);
        $template = str_replace('{{url_encoded}}', urlencode($url), $template);

        if (!preg_match_all('/{{[a-zA-Z0-9_\.\,\(\)]+}}/', $template, $matches))
            return $template;
        $replace = array();
        foreach ($matches[0] as $pattern)
        {
            // extra data
            if (stristr($pattern, '{{EXTRA.'))
            {
                $pattern_parts = explode('.', $pattern);
                $var_name = rtrim($pattern_parts[1], '}');
                // lazada SKU fix
                if ($var_name == 'sku' && !isset($item['extra']['sku']) && isset($item['extra']['features']))
                {
                    foreach ($item['extra']['features'] as $f)
                    {
                        if ($f['name'] == 'SKU')
                        {
                            $item['extra']['sku'] = $f['value'];
                            break;
                        }
                    }
                }

                if (isset($item['extra'][$var_name]))
                    $replace[$pattern] = urlencode($item['extra'][$var_name]);
                else
                    $replace[$pattern] = '';
                continue;
            }
        }
        return str_ireplace(array_keys($replace), array_values($replace), $template);
    }

    public static function getProfitshareLink($url, $regex, $item = array())
    {
        $regex = trim($regex);
        $parts = explode('][', $regex);
        if (count($parts) != 3)
            return $url;

        $api_user = $parts[1];
        $api_key = rtrim($parts[2], ']');

        $api_url = 'http://api.profitshare.ro/affiliate-links/?';
        $query_string = '';

        $spider = curl_init();
        curl_setopt($spider, CURLOPT_HEADER, false);
        curl_setopt($spider, CURLOPT_URL, $api_url . $query_string);
        curl_setopt($spider, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($spider, CURLOPT_TIMEOUT, 30);
        curl_setopt($spider, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($spider, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($spider, CURLOPT_USERAGENT, 'Content Egg WP Plugin (http://www.keywordrush.com/en/contentegg)');

        $data = array();
        $name = 'CE:' . TextHelper::getHostName($url);
        if (!empty($item['title']))
            $name .= ' ' . $item['title'];
        $data[] = array(
            'name' => $name,
            'url' => $url
        );

        curl_setopt($spider, CURLOPT_POST, true);
        curl_setopt($spider, CURLOPT_POSTFIELDS, http_build_query($data));

        $profitshare_login = array('api_user' => $api_user, 'api_key' => $api_key,);
        $date = gmdate('D, d M Y H:i:s T', time());
        $signature_string = 'POSTaffiliate-links/?' . $query_string . '/' . $profitshare_login['api_user'] . $date;
        $auth = hash_hmac('sha1', $signature_string, $profitshare_login['api_key']);

        $extra_headers = array("Date: {$date}", "X-PS-Client: {$profitshare_login['api_user']}", "X-PS-Accept: json", "X-PS-Auth: {$auth}");

        curl_setopt($spider, CURLOPT_HTTPHEADER, $extra_headers);

        $output = curl_exec($spider);
        if (!$output)
            return $url;

        $result = json_decode($output, true);

        if (!$result)
            return $url;
        if (isset($result['result'][0]['ps_url']))
            return $result['result'][0]['ps_url'];
        else
            return $url;
    }

    public static function getMultiDeeplink($deeplink, $url)
    {
        if (!strstr($deeplink, ';') || strstr($deeplink, 'ad.doubleclick'))
            return $deeplink;

        $url_host = TextHelper::urlHost($url);
        $deeplink_array = str_getcsv($deeplink, ';');
        $default = '';
        foreach ($deeplink_array as $da)
        {
            $parts = explode(':', $da, 2);

            // default deeplink
            if (count($parts) == 1)
                $default = trim($da);
            elseif (count($parts) == 2)
            {
                if (!$default)
                    $default = trim($parts[1]);

                $host = $parts[0];
                $host = preg_replace('/^https?:\/\//', '', $host);
                $host = preg_replace('/^www\./', '', $host);

                if ($host == $url_host)
                    return trim($parts[1]);
            }
        }

        return $default;
    }

}
