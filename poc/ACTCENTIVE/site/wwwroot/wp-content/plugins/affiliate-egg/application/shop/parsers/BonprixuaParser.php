<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * BonprixuaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class BonprixuaParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='product-list']/div/a[1]/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.bonprix.ua' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1/span[@id='product-name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@id='product-info']//h2");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@id='product-price']//*[contains(@class, 'currentPrice')]");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@id='product-price']//*[contains(@class, 'oldPrice')]");
    }

    public function parseManufacturer()
    {
        $features = $this->parseFeatures();
        foreach ($features as $f)
        {
            if ($f['name'] == 'Марка')
                return $f['value'];
        }
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//*[@id='product-main-photo-img']/img/@src");
        if ($img)
            $img = 'http:' . $img;
        $img = str_replace('.jpg?h600', '.jpg', $img);
        return $img;
    }

    public function parseImgLarge()
    {
        //str_replace('.jpg?h600', '.jpg', $this->parseImg());
    }

    public function parseFeatures()
    {
        $features = array();
        $feature = array();
        $names = $this->xpathArray(".//*[@id='product-info']//table//td[1]");
        $values = $this->xpathArray(".//*[@id='product-info']//table//td[2]");
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field(str_replace(":", "", $names[$i]));
                $feature['value'] = sanitize_text_field($values[$i]);
                $features[] = $feature;
            }
        }

        return $features;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = $this->parseFeatures();
        $extra['images'] = array();
        $results = $this->xpathArray(".//*[@id='thumbnail-gallery']//a/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            $extra['images'][] = 'http:' . $res;
        }
        return $extra;
    }

    public function isInStock()
    {
        if ($this->parsePrice())
            return true;
        else
            return false;
    }

}
