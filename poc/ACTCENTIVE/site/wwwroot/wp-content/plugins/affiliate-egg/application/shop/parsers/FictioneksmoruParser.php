<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * FictioneksmoruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class FictioneksmoruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//ul[@class='b_products_list']//div[@class='product_name']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://fiction.eksmo.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//*[@class='b_detail__td_info']/h1"));
    }

    public function parseDescription()
    {
        return trim($this->xpathScalar(".//div[@class='b_detail__text']"));
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//span[@class='actions__price_num']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//td[@class='b_detail__td_info']//p/b");
    }

    public function parseManufacturer()
    {
        return trim($this->xpathScalar(".//h2[@class='b_detail__author']/a"));
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//td[@class='b_detail__td_img']//a/img/@src");
    }

    public function parseImgLarge()
    {
        return $this->xpathScalar(".//td[@class='b_detail__td_img']//a/@href");
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $names = $this->xpathArray(".//div[@class='b_detail__props']//span[@class='prop_name']");
        $values = $this->xpathArray(".//div[@class='b_detail__props']//*[self::span[@class='prop_value'] or self::a]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field(str_replace(":", "", $names[$i]));
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@class='b_detail__carousel']/ul/li/a/@href");
        foreach ($results as $i => $res)
        {
            if ($res)
            {
                if (!preg_match('/^http:\/\//', $res))
                    $res = 'http://fiction.eksmo.ru' . $res;
                $extra['images'][] = $res;
            }
        }
        $extra['comments'] = array();
        $users = $this->xpathArray(".//div[@class='b_comment']//div[@class='b_comment__head']/a");
        $dates = $this->xpathArray(".//div[@class='b_comment']//span[@class='comment__date']");
        $comments = $this->xpathArray(".//div[@class='b_comment']//div[@class='b_comment__text']");
        for ($i = 0; $i < count($comments); $i++)
        {
            if (!empty($comments[$i]))
            {
                $comment['name'] = sanitize_text_field($users[$i]);
                $date = explode('.', $dates[$i]);
                if (count($date) == 3)
                    $comment['date'] = strtotime($date[1] . '/' . $date[0] . '/' . $date[2]);
                else
                    $comment['date'] = '';

                $comment['comment'] = sanitize_text_field($comments[$i]);
                $extra['comments'][] = $comment;
            }
        }

        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
