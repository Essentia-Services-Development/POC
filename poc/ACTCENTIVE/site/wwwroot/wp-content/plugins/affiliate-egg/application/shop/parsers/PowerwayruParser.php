<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * PowerwayruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class PowerwayruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';

    public function parseCatalog($max)
    {

        $urls = $this->xpathArray(".//*[@id='product_list']/li//h3/a/@href");
        $urls = array_slice($urls, 0, $max);
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@id='short_description_content']/p");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//span[@itemprop='price']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@id='old_price_display']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@class='pord_man']//span[@itemprop='name']");
    }

    public function parseImg()
    {

        return $this->xpathScalar(".//*[@id='bigpic']/@src");
    }

    public function parseImgLarge()
    {
        $this->xpathScalar(".//*[@id='thumbs_list']//a/@href");
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['weight'] = $this->xpathScalar(".//*[@id='product_reference']/span");

        return $extra;
    }

    public function isInStock()
    {
        //if ($this->xpathScalar(".//*[@id='add_to_cart']"))
        return true;
    }

}
