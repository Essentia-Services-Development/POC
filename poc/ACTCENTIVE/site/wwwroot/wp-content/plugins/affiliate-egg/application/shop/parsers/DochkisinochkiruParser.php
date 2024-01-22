<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * DochkisinochkiruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class DochkisinochkiruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[contains(@class,'items')]//span/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.dochkisinochki.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@id='detailText']/div/p");
    }

    public function parsePrice()
    {
        $price = $this->xpathScalar(".//*[@itemprop='offers']//meta[@itemprop='price']/@content");
        if (!$price)
            $price = $this->xpathScalar(".//*[@class='ProductPrice']");
        return $price;
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@id='ProductPrice']");
    }

    public function parseManufacturer()
    {
        return trim($this->xpathScalar(".//span[@itemprop='brand']/meta/@content"));
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//img[@class='cloudzoom']/@src");
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $names = $this->xpathArray(".//div[@id='paramsItem']//table//td[1]");
        $values = $this->xpathArray(".//div[@id='paramsItem']//table//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = str_replace(':', '', sanitize_text_field($names[$i]));
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[contains(@class,'smImg')]/a/@data-source");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
                $extra['images'][] = $res;
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//div[@class='rq_conteiner']//div[@class='rq_avatar_title']");
        $dates = $this->xpathArray(".//div[@class='rq_conteiner']//div[@class='rq_date']");
        $comments = $this->xpathArray(".//div[@class='rq_conteiner']//div[@class='rq_comment']");
        for ($i = 0; $i < count($comments); $i++)
        {
            if (!empty($comments[$i]))
            {
                $comment['name'] = sanitize_text_field($users[$i]);
                $datestr = explode(',', $dates[$i]);
                if (isset($datestr[1]))
                {
                    $date = explode('.', $datestr[1]);
                    if (count($date) == 2)
                        $comment['date'] = strtotime(trim($date[1]) . '/' . trim($date[0]) . '/' . date('Y'));
                    else
                        $comment['date'] = '';
                }

                $comment['comment'] = sanitize_text_field($comments[$i]);
                $extra['comments'][] = $comment;
            }
        }

        return $extra;
    }

    public function isInStock()
    {
        return $this->xpathScalar(".//div[@class='yelloyprice2 new']/span") == 'Нет в наличии' ? false : true;
    }

}
