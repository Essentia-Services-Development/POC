<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ShopcluesParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ShopcluesParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='product_list']//div[@class='row']//a[not(@class='whishlist_ic')]/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@class='products_list']//h5/a/@href"), 0, $max);
        foreach ($urls as $key => $url)
        {
            if (!preg_match('/^https?:/', $url))
                $urls[$key] = 'http://www.shopclues.com' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return trim($this->xpathScalar(".//*[@id='product_description']"));
    }

    public function parsePrice()
    {
        $price = $this->xpathScalar(".//*[@class='f_price']");
        $price = str_replace('Rs.', '', $price);
        return $price;
    }

    public function parseOldPrice()
    {
        $price = $this->xpathScalar(".//*[@id='sec_list_price_']");
        if (!$price)
            $price = $this->xpathScalar(".//*[@id='sec_discounted_price_']");
        return $price;
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//img[@id='zoom_picture_gall']/@src");
        return str_replace('https:', 'http:', $img);
    }

    public function parseImgLarge()
    {
        return $this->xpathScalar(".//img[@id='zoom_picture_gall']/@data-zoom-image");
    }

    public function parseExtra()
    {
        $extra = array();


        $extra['features'] = array();
        $feature_names = $this->xpathArray(".//*[@id='specification']//td[@width][1]");
        $feature_values = $this->xpathArray(".//*[@id='specification']//td[@width][2]");
        for ($i = 0; $i < count($feature_names); $i++)
        {
            if (empty($feature_values[$i]))
                continue;
            $feature = array();
            $feature['name'] = trim($feature_names[$i], " \t\n\r:");
            $feature['value'] = trim($feature_values[$i], " \t\n\r:");
            $extra['features'][] = $feature;
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@class='prd_ratings']/span[1]"));

        return $extra;
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//div[@class='soldout_content']//p[@class='discontinued']") == 'Product Sold Out')
            return false;
        else
            return true;
    }

}
