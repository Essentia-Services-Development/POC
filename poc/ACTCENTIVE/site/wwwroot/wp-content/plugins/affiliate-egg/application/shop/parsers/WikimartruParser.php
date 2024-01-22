<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * WikimartruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class WikimartruParser extends ShopParser {

    protected $charset = 'UTF-8';

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//div[@itemprop='itemListElement']//a[@itemprop='url']/@href"), 0, $max);
    }

    public function parseTitle()
    {
        return sanitize_text_field($this->xpathScalar(".//div[@class='container']//h1[@itemprop='name']"));
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='container']//div[@itemprop='description']");
    }

    public function parsePrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//div[@class='container']//div[contains(@class,'price ')]"));
    }

    public function parseOldPrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//div[@class='container']//div[@class='old-price']"));
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//span[@itemprop='brand']/meta/@content");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@class='container']//ul[@data-wa-name='photo_gallery']/li/@data-preview-image-url");
    }

    public function parseImgLarge()
    {
        return $this->xpathScalar(".//div[@class='container']//ul[@data-wa-name='photo_gallery']/li/@data-original-image-url");
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//div[@data-wa-name='all-characteristics']//div[contains(@class,'column-5-of-20')]/span/text()");
        $values = $this->xpathArray(".//div[@data-wa-name='all-characteristics']//div[contains(@class,'column-2-of-20')]");
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
        $results = $this->xpathArray(".//div[@class='container']//ul[@data-wa-name='photo_gallery']/li/@data-preview-image-url");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $extra['images'][] = $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//div[@class='container']//h1[@itemprop='name'][contains(.,'(нет в наличии)')])");
        return ($res) ? false : true;
    }

}
