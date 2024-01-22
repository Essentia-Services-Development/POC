<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * CoupangcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class CoupangcomParser extends ShopParser {

    protected $charset = 'UTF-8';
    protected $currency = 'KRW';
    protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'Accept-Encoding' => 'identity',
    );

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@id='productList']//a/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h2[@class='prod-buy-header__title']");
    }

    public function parseDescription()
    {
        
    }

    public function parsePrice()
    {
        $xpath = array(
            ".//div[@class='prod-coupon-price prod-major-price']//strong",
        );
        if ($p = (float) str_replace(',', '', $this->xpathScalar($xpath)))
            return $p;

        $xpath = array(
            ".//*[@class='prod-price']//*[@class='total-price']",
        );
        return $this->xpathScalar($xpath);
    }

    public function parseOldPrice()
    {
        $xpath = array(
            ".//div[@class='prod-price']//span[@class='origin-price']",
        );
        return $this->xpathScalar($xpath);
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//div[@class='prod-price-onetime']//div[@class='oos-label']") == '일시품절')
            return false;
        else
            return true;
    }

}
