<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * WedstylesuParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class WedstylesuParser extends ShopParser {

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//td[@class='eshop_list_item_row']//a[@class='name']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.wedstyle.su/' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//td[@class='itemD_detail']//div[@class='item_name']/h1");
    }

    public function parseDescription()
    {

        $res = $this->xpathArray(".//td[@class='itemD_detail']//div[@class='description']/node()[position()>1]");
        return implode(' ', $res);
    }

    public function parsePrice()
    {
        return (float) preg_replace('/[^0-9]/', '', $this->xpathScalar(".//div[@class='itemD_price']/span[@class='price_price']"));
    }

    public function parseOldPrice()
    {
        return (float) preg_replace('/[^0-9]/', '', $this->xpathScalar(".//div[@class='itemD_price_orig']/span[@class='price_original']"));
    }

    public function parseManufacturer()
    {
        $manuf = $this->xpathScalar(".//td[@class='itemD_detail']//div[@class='item_name']/following::b[1]");
        return preg_replace("/[^\w]/", "", $manuf);
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@class='small_picture_wrapper']//img/@src");
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//div[@class='sticker_wrap']/a/@onclick");
        if ($img && preg_match('/http:[^\s]+\.jpg/msi', $img, $match))
            $img = $match[0];
        else
            $img = '';
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['features'] = array();
        $res = $this->xpathScalar(".//td[@class='itemD_detail']//div[@class='sku']");
        $expl = explode(":", $res, 2);
        if (count($expl) == 2)
        {
            $feature['name'] = sanitize_text_field($expl[0]);
            $feature['value'] = sanitize_text_field($expl[1]);
            $extra['features'][] = $feature;
        }
        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@class='small_picture_dop']/img/@src");
        foreach ($results as $i => $res)
        {
            if ($res && !preg_match('/^http:/', $res))
                $res = 'http://www.wedstyle.su/' . $res;
            $extra['images'][] = $res;
        }
        return $extra;
    }

    public function isInStock()
    {
        if (isset($this->item['price']) && $this->item['price'])
            return true;
        else
            false;
    }

}
