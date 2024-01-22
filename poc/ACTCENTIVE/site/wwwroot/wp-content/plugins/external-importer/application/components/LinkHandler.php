<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\TextHelper;

/**
 * LinkHandler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class LinkHandler
{

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    public static function createAffUrl($url, $deeplink, $item = array(), $subid = '')
    {
        $filtered = \apply_filters('ei_create_affiliate_link', $url, $deeplink);
        if ($filtered != $url)
            return $url;

        // profitshare fix. return if url already created
        if (!empty($item['url']) && strstr($item['url'], '/l.profitshare.ro/'))
            return $item['url'];
        // lomadee fix. return if url already created
        if (!empty($item['url']) && strstr($item['url'], '/redir.lomadee.com/') && !strstr($item['url'], 'https://redir.lomadee.com/v2/deeplink?url='))
            return $item['url'];

        if (!$deeplink)
        {
            $result = $url;
        }
        elseif (substr(trim($deeplink), 0, 7) == '[regex]')
        {
            // regex preg_replace
            $result = self::getRegexReplace($url, $deeplink);
        }
        elseif (substr(trim($deeplink), 0, 13) == '[profitshare]')
        {
            // ProfitShare link creator
            $result = self::getProfitshareLink($url, $deeplink, $item);
        }
        elseif (substr(trim($deeplink), 0, 9) == '[lomadee]')
        {
            // Lomadee link creator
            $result = self::getLomadeeLink($url, $deeplink, $item);
        }
        elseif (substr(trim($deeplink), 0, 13) == '[trovaprezzi]')
        {
            //  Trovaprezzi link creator
            $result = self::getTrovaprezziLink($url, $deeplink, $item);
        }
        elseif (strstr($deeplink, '{{') && strstr($deeplink, '}}'))
        {
            // template deeplink
            $result = self::getUrlTemplate($url, $deeplink, $item);
        }
        elseif (!preg_match('/^https?:\/\//i', $deeplink))
        {
            // url with tail
            $result = self::getUrlWithTail($url, $deeplink);
        }
        else
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

    public static function getUrlTemplate($url, $template, $item = array())
    {
        $template = str_replace('{{url}}', $url, $template);
        $template = str_replace('{{url_encoded}}', urlencode($url), $template);
        $template = str_replace('{{url_slug_encoded}}', urlencode(trim(parse_url($url, PHP_URL_PATH), '/')), $template);

        global $post;

        if ($item)
        {
            if (isset($item['post_id']))
                $post_id = $item['post_id'];
            elseif (!empty($post))
                $post_id = $post->ID;
            else
                $post_id = 0;
            $template = str_replace('{{post_id}}', urlencode($post_id), $template);

            if (!empty($item['unique_id']))
                $template = str_replace('{{item_unique_id}}', urlencode($item['unique_id']), $template);
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
            return $url;

        $pattern = $parts[1];
        $replacement = substr($parts[2], 0, -1);

        // null character allows a premature regex end and "/../e" injection
        if (strpos($pattern, 0) !== false || !trim($pattern))
            return $url;

        if ($result = @preg_replace($pattern, $replacement, $url))
            return $result;
        else
            return $url;
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
        curl_setopt($spider, CURLOPT_USERAGENT, 'Content Egg WP Plugin (https://www.keywordrush.com/contentegg)');

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

    public static function getLomadeeLink($url, $regex, $item = array())
    {
        $regex = trim($regex);
        $parts = explode('][', $regex);
        if (count($parts) != 2)
            return $url;

        $sourceId = rtrim($parts[1], ']');
        $api_url = 'https://api.lomadee.com/v2/15071999399311f734bd1/deeplink/_create?sourceId=' . urlencode($sourceId) . '&url=' . urlencode($url);

        $response = \wp_remote_get($api_url);
        if (\is_wp_error($response))
            return $url;
        $response_code = (int) \wp_remote_retrieve_response_code($response);
        if ($response_code != 200)
            return $url;
        $output = \wp_remote_retrieve_body($response);
        $result = json_decode($output, true);
        if (!$result)
            return $url;
        if (isset($result['deeplinks'][0]['deeplink']))
            return $result['deeplinks'][0]['deeplink'];
        else
            return $url;
    }

    public static function getTrovaprezziLink($url, $regex, $item = array())
    {
        /**
         * Note: tracking links include a token in order to ensure that offers are updated as much as possible. 
         * This token expires in 12 hours!  Therefore   you need to set your script to update your feed at least 
         * once each 11 hours  , in order to guarantee the correct click tracking!  
         */
        if (time() - $item['last_update'] < 11 * 3600)
            return $item['url'];

        $regex = trim($regex);
        $parts = explode('][', $regex);
        if (count($parts) != 2)
            return $url;

        $path = parse_url($url, PHP_URL_PATH);
        $path = trim($path, "/");
        $path = preg_replace('/\.aspx$/', '', $path);

        $partnerId = rtrim($parts[1], ']');
        $api_url = 'https://quickshop.shoppydoo.it/' . urlencode($partnerId) . '/' . urlencode($path) . '.aspx?format=json';

        $response = \wp_remote_get($api_url);
        if (\is_wp_error($response))
            return $url;
        $response_code = (int) \wp_remote_retrieve_response_code($response);
        if ($response_code != 200)
            return $url;
        $output = \wp_remote_retrieve_body($response);
        $result = json_decode($output, true);
        if (!$result)
            return $url;
        if (isset($result['offers'][0]['url']))
            return $result['offers'][0]['url'];
        else
            return $url;
    }
}
