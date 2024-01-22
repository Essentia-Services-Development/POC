<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * Homeshop18comParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class Homeshop18comParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//table[@class='flexGrid']/div[@class='product-h1']/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@id='searchResultsDiv']//*[@class='product_title']/a/@href"), 0, $max);

        foreach ($urls as $key => $url)
        {
            if (!preg_match('/^http:/', $url))
                $urls[$key] = 'http://www.homeshop18.com' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@id='product-info']//span[@itemprop='name']");
    }

    public function parseDescription()
    {
        if ($key_features = $this->xpathArray(".//*[@id='keyfeaturesBoxDiv']/ul/li"))
            return join('. ', $key_features);

        if ($key_features = $this->xpathArray(".//table[@class='specs_map']//td[@class='specs_value']//li"))
            return join(' ', $key_features);
    }

    public function parsePrice()
    {
        $price = $this->xpathScalar(".//*[@itemprop='price']");
        if (!$price)
            $price = $this->xpathScalar(".//meta[@property='og:price:amount']/@content");
        return $price;
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@id='mrpPrice']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//div[@class='product-logotype']/a/@title");
    }

    public function parseImg()
    {
        return 'http:' . $this->xpathScalar(".//*[@id='pbilimage1tag']/@src");
    }

    public function parseImgLarge()
    {
        return str_replace('-medium_', '-large_', $this->parseImg());
    }

    public function parseExtra()
    {
        $extra = array();


        $extra['features'] = array();
        $feature_names = $this->xpathArray(".//*[@id='productSpecificationDetailsAreaPDP']//td[@class='specs_key']/span");
        $feature_values = $this->xpathArray(".//*[@id='productSpecificationDetailsAreaPDP']//td[@class='specs_value']/span");
        for ($i = 0; $i < count($feature_names); $i++)
        {
            if (empty($feature_values[$i]))
                continue;
            $feature = array();
            $feature['name'] = sanitize_text_field(trim($feature_names[$i]));
            $feature['value'] = sanitize_text_field(trim($feature_values[$i]));
            $extra['features'][] = $feature;
        }
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
