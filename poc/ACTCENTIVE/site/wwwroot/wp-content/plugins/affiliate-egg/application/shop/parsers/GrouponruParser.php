<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * GrouponruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class GrouponruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[@class='deal-offer__link']/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@class='do_title_text']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@id='offer']//div[@class='description']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(array(".//div[@class='info']//div[@class='price min']//strong", ".//*[@class='info']//strong[@class='js-price_label']"));
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='pdeal-info']//*[@class='pdeal-price']/b");
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['discount'] = preg_replace('/[^0-9]/', '', $this->xpathScalar(".//*[@id='offer']//div[@class='info']//td[2]"));
        $extra['bought'] = (int) $this->xpathScalar(".//*[@id='offer']//div[@class='info']//*[@class='bought']/strong");
        $extra['address'] = $this->xpathScalar(".//*[contains(@class, 'location')]//div[@class='address']");
        $extra['metro'] = $this->xpathScalar(".//*[contains(@class, 'location')]//*[contains(@class, 'metro')]");

        $extra['valid_to'] = $this->xpathScalar(".//*[@id='offer']//div[@class='holder']//*[@class='valid-to']/strong");

        $title = $this->xpathScalar(".//title");

        $parts = explode(' - ', $title);
        $extra['title_short'] = '';
        $extra['city'] = '';
        if (count($parts) > 1)
        {
            $extra['title_short'] = $parts[0];
            $extra['city'] = trim(str_replace('Групон', '', $parts[1]));
        }

        $extra['images'] = $this->xpathArray(".//*[@id='offer']//div[@class='slideshow']//a/@href");

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
