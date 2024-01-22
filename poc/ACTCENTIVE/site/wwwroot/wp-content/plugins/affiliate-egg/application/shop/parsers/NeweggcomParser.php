<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * NeweggcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class NeweggcomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        return $this->xpathArray(array(".//*[@class='items-view is-grid']//a[@class='item-title']/@href", ".//div[@class='item-info']/a/@href"));
    }

    public function parseTitle()
    {
        if ($p = parent::parseTitle())
            return $p;

        return str_replace('- Newegg.com', '', $this->xpathScalar(".//title"));
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//ul[@class='price']//span[@class='price-was-data']");
    }

    public function parseImg()
    {
        if ($p = $this->xpathScalar(".//img[@class='product-view-img-original']/@src"))
            return $p;

        if ($img = $this->xpathScalar(".//meta[@property='og:image']/@content"))
            return $img;

        $img = $this->xpathScalar(".//a[@id='A2']//img/@src");
        if (!$img)
        {
            $img = $this->xpathScalar(".//*[@id='synopsis']//ul[@class='navThumbs']/li[position() = 1]//img/@src");
            $img = str_replace('/ProductImageCompressAll35/', '/ProductImage/', $img);
        }
        $img = str_replace('//images', 'https://images', $img);
        return $img;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@id='Specs']//dt",
                'value' => ".//div[@id='Specs']//dd",
            ),
            array(
                'name' => ".//div[@id='product-details']//div[@class='tab-pane']//th",
                'value' => ".//div[@id='product-details']//div[@class='tab-pane']//td",
            ),
        );
    }

    public function isInStock()
    {
        if (trim($this->xpathScalar(".//div[@class='flags-body has-icon-left fa-exclamation-triangle']/span")) == 'OUT OF STOCK')
            return false;
        else
            return true;
    }

}
