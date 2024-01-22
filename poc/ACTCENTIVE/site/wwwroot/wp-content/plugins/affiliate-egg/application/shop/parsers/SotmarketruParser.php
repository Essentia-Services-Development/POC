<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * SotmarketruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class SotmarketruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@itemprop='itemListElement']//a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.sotmarket.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//h1[@itemprop='name']"));
    }

    public function parseDescription()
    {
        $descr = $this->xpathScalar(".//div[@class='b-text']/p[@itemprop='description']");
        if (!$descr)
            $descr = $this->xpathScalar(".//div[@class='b-card-info-cell']/p[@itemprop='description']");
        return $descr;
    }

    public function parsePrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//meta[@itemprop='price']/@content"));
    }

    public function parseOldPrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//p[@class='b-goods-price-title']/del"));
    }

    public function parseManufacturer()
    {
        return trim($this->xpathScalar(".//div[@class='b-goods-specifications-cell']/span[normalize-space(text())='Производитель']/../parent::li/div[2]"));
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//div[@id='stm-gallery']/ul/li[1]/img/@src");
        $img = str_replace('/65x65/', '/standart/', $img);
        return $img;
    }

    public function parseImgLarge()
    {
        $img = '';
        if ($this->item['orig_img'])
            $img = str_replace('/standart/', '/1200x1200/', $this->item['orig_img']);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $names = $this->xpathArray(".//li[@class='b-goods-specifications-row g-clearfix']/div[1]");
        $values = $this->xpathArray(".//li[@class='b-goods-specifications-row g-clearfix']/div[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@id='stm-gallery']/ul/li/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
                $extra['images'][] = str_replace('/65x65/', '/standart/', $res);
        }

        $extra['comments'] = array();

        $users = $this->xpathArray(".//div[contains(@class,'b-responds-author')]");
        $dates = $this->xpathArray(".//div[contains(@class,'b-responds-meta')]");
        $comments = $this->xpathArray(".//div[contains(@class,'b-responds-text mod_comment')]/span");

        for ($i = 0; $i < count($comments); $i++)
        {
            if (!empty($comments[$i]))
            {
                $comment['name'] = sanitize_text_field($users[$i]);
                preg_match("/(\d{1,2})\s([а-яa-z]+)\s(\d{4})/u", $dates[$i], $match);
                if (count($match) == 4)
                    $comment['date'] = strtotime($this->_ru_month_to_num($match[2]) . "/" . $match[1] . "/" . $match[3]);
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
        $val = $this->xpathScalar(".//table[@class='b-goods-payment']//*[contains(@class,'js-card-subscribe-title')]");
        if (strstr($val, 'Нет в наличии'))
            return false;
        else
            return true;
        //

        return true;
        /*
         * It does not work?..
          $res = $this->xpathScalar(".//p[@class='b-goods-price-availability']");
          if ($res && $res == 'есть на складе')
          return true;
          else
          return false;
         * 
         */
    }

    private function _ru_month_to_num($month)
    {
        $matches = array('января' => 1, 'февраля' => 2, 'марта' => 3, 'апреля' => 4, 'мая' => 5, 'июня' => 6, 'июля' => 7, 'августа' => 8, 'сентября' => 9, 'октября' => 10, 'ноября' => 11, 'декабря' => 12);
        if (isset($matches[$month]))
            return $matches[$month];
        else
            return '';
    }

}
