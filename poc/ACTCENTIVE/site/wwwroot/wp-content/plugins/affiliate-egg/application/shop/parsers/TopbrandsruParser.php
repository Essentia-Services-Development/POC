<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * TopbrandsruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class TopbrandsruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@id='products']/div/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'https://www.topbrands.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@class='card-main-header']//p/a");
    }

    public function parseDescription()
    {
        return '';
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='start-price']/s");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@class='card-main-header']//h2");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        if ($img && !preg_match('/^https?:\/\//', $img))
            $img = 'http:' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {

        $extra = array();

        $extra['features'] = array();

        $names_values = $this->xpathArray(".//*[@class='card-description']//*[contains(@class, 'desc-item')]");
        $feature = array();
        for ($i = 0; $i < count($names_values); $i++)
        {
            $parts = explode(':', $names_values[$i]);
            if (count($parts) !== 2)
                continue;

            $parts[0] = trim($parts[0]);
            $parts[1] = trim($parts[1]);

            if ($parts[0] && $parts[1])
            {
                $feature['name'] = sanitize_text_field($parts[0]);
                $feature['value'] = sanitize_text_field($parts[1]);
                $extra['features'][] = $feature;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//td[@class='button_buy']/a[contains(.,'ТОВАР ПРОДАН')])");
        return ($res) ? false : true;
    }

}
