<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * YvesrocherruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class YvesrocherruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $manufacturer = 'Yves Rocher';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//h3/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.yves-rocher.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//h1"));
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@class='have_mention']//p");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[contains(@class, 'inside ')]//del[@class='striped_price']");
    }

    public function parseManufacturer()
    {
        return $this->manufacturer;
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        return $img;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='aggregateRating']//*[@itemprop='ratingValue']"));
        return $extra;
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//*[@itemprop='availability']/@content") == 'out_of_stock')
            return false;
        else
            return true;
    }

}
