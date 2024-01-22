<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * YvesrocheruaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class YvesrocheruaParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='liste_produits']//h2/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.yves-rocher.ua' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@class='titre']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='scrollbar']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@id='price']/span[@class='prix']/text()");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@id='price']//span[@class='remise']");
    }

    public function parseManufacturer()
    {
        return 'Yves Rocher';
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//*[@property='og:image']/@content");
        $img = str_replace('/detail_product1/', '/zoom1/', $img);
        return $img;
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();

        $images = $this->xpathArray(".//td[contains(@class, 'thumbnails')]//li[position() > 1]//img/@src");
        $extra['images'] = array();
        foreach ($images as $img)
        {
            $extra['images'] = 'http://www.yves-rocher.ua' . preg_replace('#/view(\d)/#', '/zoom$1/', $img);
        }

        if ($this->xpathArray(".//*[@class='ratings']/span[@class='star star10']"))
        {
            $star0 = $this->xpathArray(".//*[@class='ratings']/span[@class='star star00']");
            $extra['rating'] = TextHelper::ratingPrepare(5 - count($star0));
        }

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
