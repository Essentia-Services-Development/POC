<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * JumiacomegParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class JumiacomegParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'EGP';

    /**
     * Site scaping is permited IF the user-agent is clearly identify it as a bot and
     * the bot owner and is using less than 200 request per minute
     */
    protected function getUserAgent()
    {
        return 'Mozilla/5.0 (compatible; AEBot; +' . \get_home_url() . ')';
    }

    public function parseCatalog($max)
    {
        return $this->xpathArray(array(".//article[@class='prd _fb col c-prd']/a/@href", ".//a[@class='link']/@href"));
    }

    public function parseTitle()
    {
        if ($t = parent::parseTitle())
            return $t;
        else
            return $this->xpathScalar(".//title"); //for outofstock products
    }

    public function parsePrice()
    {
        if ($p = $this->xpathScalar(array("(.//*[@class='price-box']//*/@data-price)[1]", ".//span[@class='-b -ltr -tal -fs24']")))
        {
            $parts = explode(' - ', $p);
            return $parts[0];
        } else
            return parent::parsePrice();
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div/span[@data-price-old]",
            ".//*[@class='price-box']//*/@data-price)[2]",
            ".//*[@class='row card _no-g -fh -pas']//span[@data-price-old]",
        );

        $p = $this->xpathScalar($paths);
        if ($p)
        {
            $parts = explode(' - ', $p);

            return $parts[0];
        }
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();
        $names = $this->xpathArray(".//*[@id='product-details']//*[contains(@class, 'osh-row')]/div[1]");
        $values = $this->xpathArray(".//*[@id='product-details']//*[contains(@class, 'osh-row')]/div[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && !empty($names[$i]))
            {
                $value = \sanitize_text_field($values[$i]);
                if (!$value || $value == '-')
                    continue;
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = $value;
                $extra['features'][] = $feature;
            }
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//div[@class='reviews']//address[@class='author word-wrap']");
        $comments = $this->xpathArray(".//div[@class='reviews']//div[@class='title']");
        $comments2 = $this->xpathArray(".//div[@class='reviews']//div[@class='detail truncate-txt']/text()");
        $comments2 = array_values(array_filter($comments2));
        $ratings = $this->xpathArray(".//div[@class='reviews']//*[@class='stars']/@style");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment = array();
            $comment['comment'] = \sanitize_text_field($comments[$i]);
            if (!empty($comments2[$i]))
                $comment['comment'] .= '. ' . \sanitize_text_field($comments2[$i]);
            if (!empty($users[$i]))
                $comment['name'] = \sanitize_text_field($users[$i]);

            if (!empty($ratings[$i]))
            {
                $rating_parts = explode(':', $ratings[$i]);
                if (count($rating_parts) == 2)
                    $comment['rating'] = TextHelper::ratingPrepare((int) $rating_parts[1] / 20);
            }
            $extra['comments'][] = $comment;
        }

        return $extra;
    }

    public function isInStock()
    {
        if (!$this->parsePrice())
            return false;

        if ($this->xpathScalar(".//meta[@property='og:image']/@content") == 'https://eg.jumia.is/cms/7-20/dau-mau/Jumia-Logo_new.png')
            return false;

        if ($this->xpathScalar(".//meta[@property='og:image']/@content") == 'https://eg.jumia.is/cms/7-20/Jumia-Logo.png')
            return false;

        return parent::isInStock();
    }

    public function restPostGet($url, $fix_encoding = true)
    {
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
            'timeout' => 15,
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

        // fix! 403 for out of stock products
        if ($response_code != 200 && $response_code != 206 && $response_code != 403)
            throw new \Exception('Error in url request. HTTP response code: ' . $response_code, $response_code);

        $body = \wp_remote_retrieve_body($response);
        return $this->decodeCharset($body, $fix_encoding);
    }

}
