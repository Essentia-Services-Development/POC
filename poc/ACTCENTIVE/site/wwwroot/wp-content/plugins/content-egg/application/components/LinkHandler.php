<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TextHelper;

/**
 * LinkHandler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class LinkHandler {

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Deeplink & more...
     */
    public static function createAffUrl($url, $deeplink, $item = array(), $subid = '')
    {
        // custom filter
        $filtered = \apply_filters('cegg_create_affiliate_link', $url, $deeplink);
        if ($filtered != $url)
        {
            return $url;
        }

        // profitshare fix. return if url already created
        if (!empty($item['url']) && strstr($item['url'], '/l.profitshare.ro/'))
        {
            return $item['url'];
        }
        // lomadee fix. return if url already created
        if (!empty($item['url']) && strstr($item['url'], '/redir.lomadee.com/') && !strstr($item['url'], 'https://redir.lomadee.com/v2/deeplink?url='))
        {
            return $item['url'];
        }
        // coupang fix. return if url already created
        if (!empty($item['url']) && (strstr($item['url'], 'https://coupa.ng/') || strstr($item['url'], 'https://link.coupang.com')))
        {
            return $item['url'];
        }

        $deeplink = self::getMultiDeeplink($deeplink, $url);

        if (!$deeplink)
        {
            $result = $url;
        } elseif (substr(trim($deeplink), 0, 7) == '[regex]')
        {
            // regex preg_replace
            $result = self::getRegexReplace($url, $deeplink);
        } elseif (substr(trim($deeplink), 0, 13) == '[profitshare]')
        {
            // ProfitShare link creator
            $result = self::getProfitshareLink($url, $deeplink, $item);
        } elseif (substr(trim($deeplink), 0, 9) == '[lomadee]')
        {
            // Lomadee link creator
            $result = self::getLomadeeLink($url, $deeplink, $item);
        } elseif (substr(trim($deeplink), 0, 13) == '[trovaprezzi]')
        {
            // Trovaprezzi link creator
            $result = self::getTrovaprezziLink($url, $deeplink, $item);
        } elseif (substr(trim($deeplink), 0, 9) == '[coupang]')
        {
            // Coupang link creator
            $result = self::getCoupangLink($url, $deeplink, $item);
        } elseif (strstr($deeplink, '{{') && strstr($deeplink, '}}'))
        {
            // template deeplink
            $result = self::getUrlTemplate($url, $deeplink, $item);
        } elseif (!preg_match('/^https?:\/\//i', $deeplink))
        {
            // url with tail
            $result = self::getUrlWithTail($url, $deeplink);
        } else
        {
            $result = $deeplink . urlencode($url);
        }
        if ($subid)
        {
            $result = self::getUrlWithTail($result, $subid);
        }

        return $result;
    }

    public static function getUrlWithTail($url, $tail)
    {
        // replace params in URL
        parse_str($tail, $vars);
        if (count($vars) == 1 && strstr($tail, '='))
        {
            return \add_query_arg($vars, $url);
        }

        $tail = preg_replace('/^[?&]/', '', $tail);

        $query = parse_url($url, PHP_URL_QUERY);
        if ($query)
        {
            $url .= '&';
        } else
        {
            $url .= '?';
        }

        parse_str($tail, $tail_array);
        $url .= http_build_query($tail_array);

        return $url;
    }

    public static function getUrlTemplate($url, $template, $item = array())
    {
        $template = str_replace('{{url}}', $url, $template);
        $template = str_replace('{{url_encoded}}', urlencode($url), $template);
        $template = str_replace('{{url_base64}}', base64_encode($url), $template);
        global $post;

        if ($item)
        {
            if (isset($item['post_id']))
            {
                $post_id = $item['post_id'];
            } elseif (!empty($post))
            {
                $post_id = $post->ID;
            } else
            {
                $post_id = 0;
            }
            $template = str_replace('{{post_id}}', urlencode($post_id), $template);

            if (!empty($item['unique_id']))
            {
                $template = str_replace('{{item_unique_id}}', urlencode($item['unique_id']), $template);
            }
        }

        if (!empty($post))
        {
            $author_id = $post->post_author;
            $user = \get_user_by('ID', $author_id);
            $author_login = $user ? $user->data->user_login : '';
            $template = str_replace('{{author_id}}', urlencode($author_id), $template);
            $template = str_replace('{{author_login}}', urlencode($author_login), $template);
        }

        return $template;
    }

    public static function getRegexReplace($url, $regex)
    {
        $regex = trim($regex);

        $parts = explode('][', $regex);
        if (count($parts) != 3)
        {
            return $url;
        }

        $pattern = $parts[1];
        //$replacement = rtrim($parts[2], ']');
        $replacement = substr($parts[2], 0, - 1);

        // null character allows a premature regex end and "/../e" injection
        if (strpos($pattern, chr(0)) !== false || !trim($pattern))
        {
            return $url;
        }

        if ($result = @preg_replace($pattern, $replacement, $url))
        {
            return $result;
        } else
        {
            return $url;
        }
    }

    public static function getProfitshareLink($url, $regex, $item = array())
    {
        $regex = trim($regex);
        $parts = explode('][', $regex);
        if (count($parts) != 3)
        {
            return $url;
        }

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
        curl_setopt($spider, CURLOPT_USERAGENT, 'Content Egg WP Plugin (https://www.keywordrush.com/contentegg)');

        $data = array();
        $name = 'CE:' . TextHelper::getHostName($url);
        if (!empty($item['title']))
        {
            $name .= ' ' . $item['title'];
        }
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

        $extra_headers = array(
            "Date: {$date}",
            "X-PS-Client: {$profitshare_login['api_user']}",
            "X-PS-Accept: json",
            "X-PS-Auth: {$auth}"
        );

        curl_setopt($spider, CURLOPT_HTTPHEADER, $extra_headers);

        $output = curl_exec($spider);
        if (!$output)
        {
            return $url;
        }

        $result = json_decode($output, true);

        if (!$result)
        {
            return $url;
        }
        if (isset($result['result'][0]['ps_url']))
        {
            return $result['result'][0]['ps_url'];
        } else
        {
            return $url;
        }
    }

    public static function getLomadeeLink($url, $regex, $item = array())
    {
        $regex = trim($regex);
        $parts = explode('][', $regex);
        if (count($parts) != 2)
        {
            return $url;
        }

        $sourceId = rtrim($parts[1], ']');
        $api_url = 'https://api.lomadee.com/v2/15071999399311f734bd1/deeplink/_create?sourceId=' . urlencode($sourceId) . '&url=' . urlencode($url);

        $response = \wp_remote_get($api_url);
        if (\is_wp_error($response))
        {
            return $url;
        }
        $response_code = (int) \wp_remote_retrieve_response_code($response);
        if ($response_code != 200)
        {
            return $url;
        }
        $output = \wp_remote_retrieve_body($response);
        $result = json_decode($output, true);
        if (!$result)
        {
            return $url;
        }
        if (isset($result['deeplinks'][0]['deeplink']))
        {
            return $result['deeplinks'][0]['deeplink'];
        } else
        {
            return $url;
        }
    }

    public static function getTrovaprezziLink($url, $regex, $item = array())
    {
        /**
         * Note: tracking links include a token in order to ensure that offers are updated as much as possible.
         * This token expires in 12 hours!  Therefore   you need to set your script to update your feed at least
         * once each 11 hours  , in order to guarantee the correct click tracking!
         */
        if (strstr($item['url'], 'splash?impression') && time() - $item['last_update'] < 111 * 3600)
        {
            return $item['url'];
        }

        $regex = trim($regex);
        $parts = explode('][', $regex);
        if (count($parts) != 2)
        {
            return $url;
        }

        /*
          $path = parse_url($url, PHP_URL_PATH);
          $path = trim($path, "/");
          $path = preg_replace('/\.aspx$/', '', $path);
          $path = explode('/', $path);
          $path = end($path);
          $path = explode('-', $path);
          $path = end($path);
          $keyword = $path;
         *
         */

        $keyword = $item['title'];
        $keyword = strtolower($keyword);
        $keyword = str_replace(' ', '_', $keyword);

        $partnerId = rtrim($parts[1], ']');
        $api_url = 'https://quickshop.shoppydoo.it/' . urlencode($partnerId) . '/' . urlencode($keyword) . '.aspx?format=json&sort=price';

        $response = \wp_remote_get($api_url);
        if (\is_wp_error($response))
        {
            return $url;
        }
        $response_code = (int) \wp_remote_retrieve_response_code($response);
        if ($response_code != 200)
        {
            return $url;
        }
        $output = \wp_remote_retrieve_body($response);
        $result = json_decode($output, true);
        if (!$result)
        {
            return $url;
        }
        if (isset($result['offers'][0]['url']))
        {
            return $result['offers'][0]['url'];
        } else
        {
            return $url;
        }
    }

    public static function getCoupangLink($url, $regex, $item = array())
    {
        $regex = trim($regex);
        $parts = explode('][', $regex);
        if (count($parts) != 3)
        {
            return $url;
        }

        $ACCESS_KEY = $parts[1];
        $SECRET_KEY = rtrim($parts[2], ']');

        //date_default_timezone_set("GMT+0");

        $datetime = date("ymd") . 'T' . date("His") . 'Z';
        $method = "POST";
        $path = "/v2/providers/affiliate_open_api/apis/openapi/v1/deeplink";
        $message = $datetime . $method . str_replace("?", "", $path);
        $algorithm = "HmacSHA256";

        $signature = hash_hmac('sha256', $message, $SECRET_KEY);

        $authorization = "CEA algorithm=HmacSHA256, access-key=" . $ACCESS_KEY . ", signed-date=" . $datetime . ", signature=" . $signature;

        $rurl = 'https://api-gateway.coupang.com' . $path;

        $strjson = '{"coupangUrls": ["' . $url . '"]}';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $rurl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type:  application/json;charset=UTF-8",
            "Authorization:" . $authorization
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $strjson);
        $output = curl_exec($curl);

        if (!$output)
        {
            return $url;
        }

        $result = json_decode($output, true);
        if (!$result)
        {
            return $url;
        }
        if (isset($result['data'][0]['shortenUrl']))
        {
            return $result['data'][0]['shortenUrl'];
        } else
        {
            return $url;
        }
    }

    public static function getMultiDeeplink($deeplink, $url)
    {
        if (!strstr($deeplink, ';') || strstr($deeplink, 'ad.doubleclick'))
        {
            return $deeplink;
        }

        $url_host = TextHelper::urlHost($url);
        $deeplink_array = str_getcsv($deeplink, ';');
        $default = '';
        foreach ($deeplink_array as $da)
        {
            $parts = explode(':', $da, 2);

            // default deeplink
            if (count($parts) == 1)
            {
                $default = trim($da);
            } elseif (count($parts) == 2)
            {
                if (!$default)
                {
                    $default = trim($parts[1]);
                }

                $host = $parts[0];
                $host = preg_replace('/^https?:\/\//', '', $host);
                $host = preg_replace('/^www\./', '', $host);

                if ($host == $url_host)
                {
                    return trim($parts[1]);
                }
            }
        }

        return $default;
    }

}
