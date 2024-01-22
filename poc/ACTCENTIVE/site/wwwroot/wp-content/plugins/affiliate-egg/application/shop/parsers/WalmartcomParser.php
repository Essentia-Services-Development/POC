<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * WalmartcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class WalmartcomParser extends MicrodataShopParser {

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
        $urls = array_slice($this->xpathArray(".//*[@id='tile-container']//a[1]/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@class='search-result-listview-items']//a[@itemprop='url']/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@id='searchProductResult']//a[@itemprop='url']/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//a[contains(@class, 'product-title-link')]/@href"), 0, $max);
        return $urls;
    }

    public function parseTitle()
    {
        $title = $this->xpathScalar(".//h1[@itemprop='name']");
        if (!$title)
            $title = $this->xpathScalar(".//h2[@itemprop='name']");
        return $title;
    }

    public function parseDescription()
    {
        $description = $this->xpathScalar(".//div[@class='about-desc']");
        if (!$description)
            $description = $this->xpathScalar(".//div[@itemprop='description']");
        if (!$description)
            $description = $this->xpathScalar(".//div[@class='product-short-description']");
        return $description;
    }

    public function parsePrice()
    {
        $price = $this->xpathScalar("(.//*[contains(@class, 'Price-enhanced')]//*[@class='Price-group']/@aria-label)[1]");
        if (!$price)
            $price = $this->xpathScalar(".//*[@itemprop='price']/@content");
        if (!$price)
            $price = $this->xpathScalar(".//*[@itemprop='price']");
        return $price;
    }

    public function parseOldPrice()
    {
        $price = $this->xpathScalar(".//*[@class='price-old display-inline']//*[@class='price-group']/@aria-label");
        return $price;
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//span[@itemprop='brand']");
    }

    public function parseImg()
    {
        return $this->xpathScalar(array(".//img[@class='hover-zoom-hero-image']/@src", ".//img[@itemprop='image']/@src"));
    }

    public function parseImgLarge()
    {
        if ($img = $this->xpathScalar(".//img[@class='hover-zoom-hero-image']/@src"))
            return str_replace('?odnHeight=450&odnWidth=450&odnBg=FFFFFF', '', $img);

        if ($img = $this->xpathScalar(".//meta[@property='og:image']/@content"))
            return str_replace('?odnHeight=450&amp;odnWidth=450&amp;odnBg=FFFFFF', '', $img);
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//div[@class='product-specifications']//td[1]");
        $values = $this->xpathArray(".//div[@class='product-specifications']//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (empty($values[$i]))
                continue;
            $feature['name'] = sanitize_text_field($names[$i]);
            $feature['value'] = sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }

        return $extra;
    }

    /*
    public function isInStock()
    {
        if ($this->parsePrice())
            return true;
        else
            return false;
    }
     * 
     */

}
