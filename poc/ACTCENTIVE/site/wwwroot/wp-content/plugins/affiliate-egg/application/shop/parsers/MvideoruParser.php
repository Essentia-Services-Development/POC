<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MvideoruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class MvideoruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $user_agent = array('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:91.0) Gecko/20100101 Firefox/91.0');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language' => 'en-US,en;q=0.7,es-ES;q=0.3',
        'Cache-Control' => 'no-cache',
        'Cookie' => 'MVID_PDP_AVAILABILITY=true; SMSError=; authError=; _JHASH__=575; _HASH__=d1935b336934db2f79d98cddc5304fb5; MVID_TIMEZONE_OFFSET=3; MVID_CITY_ID=CityCZ_975; MVID_GUEST_ID=18142482988; JSESSIONID=XlcFh7zJC2ZXZc8ZMp0ZJL1cLFKFKNL6jj71ZChrBJ7sBHTQ510L!244953764; MVID_GET_LOCATION_BY_DADATA=DaData; MVID_REGION_ID=1; NEED_REQUIRE_APPLY_DISCOUNT=true; HINTS_FIO_COOKIE_NAME=2; PROMOLISTING_WITHOUT_STOCK_AB_TEST=2; searchType2=3; COMPARISON_INDICATOR=false; CACHE_INDICATOR=true; flacktory=no; bIPs=155255760; MVID_KLA…S/YiQhupAgBNQAPgROS3NGEPW00+4MLGD+jWv2vFOA5mvAVERwFBqgxODYTmR/XrvZeRutmdJv+ReWrwc0FZISErwLoJ9UDXw==; flocktory-uuid=2692e21e-71a7-4e5f-a2e8-0c4bbbd98772-8; fgsscgib-w-mvideo=9tduef5d3ad3d8750338ab9038c5bbcf6db963fa; fgsscgib-w-mvideo=9tduef5d3ad3d8750338ab9038c5bbcf6db963fa; _ym_isad=2; tmr_detect=0%7C1631286271667; SameSite=None; _dc_gtm_UA-1873769-1=1; ADRUM=s=1631286266790&r=https%3A%2F%2Fwww.mvideo.ru%2Fproducts%2Fnoutbuk-honor-magicbook-x-15-i5-8-512-gray-bbr-wah9-30056687%3F0; gssc218=; _gat_owox37=1',
    );

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//a[contains(@class,'product-tile-title-link')]/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'https://www.mvideo.ru' . $url;
        }
        return $urls;
    }

    public function parseDescription()
    {
        $d = $this->xpathScalar(".//*[@class='o-about-product']//*[@class='collapse-text-initial']", true);
        return str_replace('<br>', "\r\n", $d);
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='c-pdp-price__offers']//div[contains(@class, 'c-pdp-price__old')]");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $names = $this->xpathArray(".//table[@class='c-specification__table']//span[@class='c-specification__name-text']");
        $values = $this->xpathArray(".//table[@class='c-specification__table']//span[@class='c-specification__value']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = \sanitize_text_field(trim($names[$i], "?:"));
                $feature['value'] = \sanitize_text_field(trim($values[$i], "?:"));
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[contains(@class,'list-carousel')]//li[not(@class)]/a/@data-src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res && preg_match('/^\/\//', $res))
                $res = 'https:' . $res;
            if (!in_array($res, $extra['images']))
                $extra['images'][] = $res;
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//div[@class='product-review-area']//strong[@class='product-review-author-name']");
        $dates = $this->xpathArray(".//div[@class='product-review-area']//span[@class='product-review-date']");
        $comments = $this->xpathArray(".//div[@class='product-review-area']//div[@class='product-review-description']/p");
        for ($i = 0; $i < count($comments); $i++)
        {
            if (!empty($comments[$i]))
            {

                $comment['name'] = (isset($users[$i])) ? trim($users[$i], ', ') : '';
                $comment['date'] = '';
                if (isset($dates[$i]))
                {
                    $date = explode('.', $dates[$i]);
                    if (count($date) == 3)
                        $comment['date'] = strtotime(trim($date[1]) . '/' . trim($date[0]) . '/' . $date[2]);
                }

                $comment['comment'] = sanitize_text_field($comments[$i]);
                $extra['comments'][] = $comment;
            }
        }
        return $extra;
    }

}
