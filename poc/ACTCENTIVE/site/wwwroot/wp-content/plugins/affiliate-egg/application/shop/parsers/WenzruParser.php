<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * WenzruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class WenzruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//div[@class='overviewList']/div/a/@href"), 0, $max);
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//div[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//span[@itemprop='description']");
    }

    public function parsePrice()
    {
        return (float) preg_replace('/[^0-9.]/', '', $this->xpathScalar(".//meta[@itemprop='price']/@content"));
    }

    public function parseOldPrice()
    {
        return (float) preg_replace('/[^0-9,]/', '', $this->xpathScalar(".//span[@class='price crossedOut']"));
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//div[@itemprop='brand']");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//img[@itemprop='image']/@src");
        if (!preg_match('/^https?:\/\//', $img))
            $img = 'http://www.wenz.ru' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['features'] = array();

        $names = $this->xpathArray(".//table[@class='productAttributeTable']//td[1]/span");
        $values = $this->xpathArray(".//table[@class='productAttributeTable']//td[2]/span");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && trim($names[$i]) != "Бренд")
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();

        $results = $this->xpathArray(".//div[@id='thumbNailList']//a/@data-image");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                preg_match("/largeimage.+\'(.+)\'/", $res, $match);
                if (isset($match[1]))
                    $res = $match[1];
                if (!preg_match('/^http:\/\//', $res))
                    $res = 'http://www.wenz.ru' . $res;
                $extra['images'][] = $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//div[@class='status'][contains(., 'Продан')])");
        return ($res) ? false : true;
    }

}
