<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * GrouponusParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class GrouponusParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {

        $urls = $this->xpathArray(".//div[@id='pull-cards']//*[@class='cui-content']/parent::a/@href");
        if (!$urls)
            $urls = $this->xpathArray(".//figure//a[@ontouchstart]/@href");
        if (!$urls)
            $urls = $this->xpathArray(".//figure[contains(@class, 'card-ui cui-c-udc c-bdr-gray-clr')]//a/@href");
        if (!$urls)
            $urls = $this->xpathArray(".//div[contains(@class, 'cui-content')]/a/@href");

        $urls = array_unique($urls);
        $urls = array_slice($urls, 0, $max);
        return $urls;
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//section[@id='deal-pitch']", true);
    }

    public function parsePrice()
    {
        if ($p = parent::parsePrice())
                return $p;
        if ($price = (float) preg_replace('/[^0-9\.]/', '', $this->xpathScalar(".//meta[@itemprop='price']/@content")))
            return $price;
        if ($price = (float) preg_replace('/[^0-9\.]/', '', $this->xpathScalar(".//meta[@itemprop='lowprice']/@content")))
            return $price;
        if ($price = $this->xpathScalar(".//*[@class='breakout-option-price']"))
            return $price;

        return $this->xpathScalar(".//div[@id='deal-hero-price']/span");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(array(".//*[@class='breakout-pricing']//div[@class='breakout-option-value']", ".//div[@class='breakout-pricing-messages']//div[@class='breakout-option-value']"));
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

    public function parseImgLarge()
    {
        return $this->parseImg();
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['images'] = $this->xpathArray(".//ul[@class='gallery-thumbs']/li[@class='gallery-thumbnail']/img/@src");
        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[contains(@class, 'product-reviews-average-rating')]"));
        return $extra;
    }

    public function getCurrency()
    {
        $cur = $this->xpathScalar(".//meta[@itemprop='priceCurrency']/@content");
        if ($cur)
            return $cur;
        else
            return $this->currency;
    }

}
