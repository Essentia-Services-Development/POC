<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * SnapdealParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class SnapdealParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@class='productWrapper']//div[@class='product-title']/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//div[@class='offerCont_wrap']//a[@class='OffersContentBoxLink']/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//div[@id='products']//*[contains(@class,'product-desc-rating')]/a/@href"), 0, $max);
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//meta[@property='og:title']/@content");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@itemprop='description' and @class='detailssubbox']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@id='buyPriceBox']//s/span");
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {

        return $this->xpathScalar(".//img[@itemprop='image']/@src");
    }

    public function parseImgLarge()
    {
        return $this->xpathScalar(".//*[@id='bx-slider-left-image-panel']//img[@itemprop='image']/@bigsrc");
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@id='productSpecs']//table[@class='product-spec']//td[1]");
        $values = $this->xpathArray(".//*[@id='productSpecs']//table[@class='product-spec']//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && $names[$i] != '1' && $names[$i] != '2' && $names[$i] != '3' && $names[$i] != '4')
            {
                $feature['name'] = $names[$i];
                $feature['value'] = $values[$i];
                $extra['features'][] = $feature;
            }
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@class='pdp-e-i-ratings']/div/@ratings"));

        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
