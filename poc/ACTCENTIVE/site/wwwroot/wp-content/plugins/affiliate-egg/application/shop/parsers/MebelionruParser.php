<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MebelionruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class MebelionruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='innerdiv' or @id='filtercontainer']//div[@class='bm-photo']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.mebelion.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        $title = $this->xpathScalar(".//h1[@itemprop='name']");
        $title = preg_replace('/\(.+?\)/', '', $title);
        return trim($title);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@id='bc-description']/p");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@class='new-price']//span[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='old-price']//span[@class='js-price-old']");
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//meta[@itemprop='image']/@content");
        if (!$img)
            $img = $this->xpathScalar(".//*[@id='imagesBlock']/img/@src");
        if (!preg_match('/^https?:/', $img))
            $img = 'https:' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $feature = array();
        $names = $this->xpathArray(".//table[contains(@class,'bcp-full')]//td[@class='bpp-name' or contains(@class, 'bpp-name-level2')]");
        $values = $this->xpathArray(".//table[contains(@class,'bcp-full')]//td[contains(@class, 'bpp-value')]");
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field(str_replace(":", "", $names[$i]));
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }


        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@class='bcpt-list-wrapper']//div[contains(@class,'item')]/@data-imgbig");
        foreach ($results as $i => $res)
        {
            $extra['images'][] = $res;
        }
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
