<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

use Keywordrush\AffiliateEgg\TextHelper;

/**
 * GearbestcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class GearbestcomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';
    protected $user_agent = array('ia_archiver');

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//p[@class='all_proNam']//a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//p[@class='title']/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//a[contains(@class, 'gbGoodsItem_title')]/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//a[@class='js-titleLink']/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@class='goodsItem_title']/a/@href"), 0, $max);
        return $urls;
    }

    public function parseDescription()
    {
        $path = array(
            ".//p[@class='textDesc']",
            ".//div[@class='textDesc']//p[@class='textDescContent']",
            ".//*[@class='product_pz'][1]",
        );
        return $this->xpathScalar($path, true);
    }

    /*
    public function parsePrice()
    {
        if ($price = $this->xpathScalar(".//meta[@property='og:price:amount']/@content"))
        {
            $this->currency = $this->xpathScalar(".//meta[@property='og:price:currency']/@content");
        }

        if (!$price)
            $price = $this->xpathScalar(".//*[@class='my_shop_price new_shop_price']/@orgp");
        if (!$price)
            $price = $this->xpathScalar(".//*[@class='my_shop_price new_shop_price']/@data-orgp");
        if (!$price)
            $price = $this->xpathScalar(".//*[@id='unit_price']/@orgp");
        if (!$price)
            $price = $this->xpathScalar(".//*[@id='unit_price']/@data-orgp");
        if (!$price)
            $price = $this->xpathScalar(".//span[@name='money']/@orgp");
        if (!$price)
            $price = $this->xpathScalar(".//span[@name='money']/@data-orgp");
        if (!$price)
            $price = $this->xpathScalar(".//inout[@id='js_hidden_price']/@value");

        if (!$price)
            return parent::parsePrice();
        return $price;
    }
     * 
     */

    public function parseOldPrice()
    {
        if ($this->xpathScalar(".//meta[@property='og:price:currency']/@content") != 'USD')
            return;

        $paths = array(
            ".//del[@class='goodsIntro_shopPrice js-currency js-panelIntroShopPrice']",
        );

        return $this->xpathScalar($paths);        
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@class='from']/*[@class='brand-name']");
    }

    public function parseImg()
    {
        if ($img = $this->xpathScalar(".//img[contains(@class, 'goodsIntro_thumbnailImg')]/@data-normal-src"))
            return $img;
        return parent::parseImg();
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $features_list = $this->xpathArray(".//div[contains(@class,'product_pz_info')]//td", true);
        foreach ($features_list as $fl)
        {
            $parts = explode('<br>', $fl);
            foreach ($parts as $part)
            {
                if (!$part = trim($part))
                    continue;

                $name_value = explode(':', $part);
                if (count($name_value) != 2)
                    continue;

                $feature = array();
                $feature['name'] = sanitize_text_field(trim($name_value[0]));
                $feature['value'] = sanitize_text_field(trim($name_value[1]));
                $extra['features'][] = $feature;
            }
        }

        $extra['comments'] = array();
        $pros = $this->xpathArray("//div[@id='js-reviewWrap']//dl[position()=1]/dd");
        $cons = $this->xpathArray("//div[@id='js-reviewWrap']//dl[position()=2]/dd");
        $ratings = $this->xpathArray("//div[@id='js-reviewWrap']//*[@class='goodsReviews_item']//span[@data-rate]/@data-rate");
        $users = $this->xpathArray("//div[@id='js-reviewWrap']//*[@class='goodsReviews_item']//*[@class='goodsReviews_itemUserName']");

        for ($i = 0; $i < count($pros); $i++)
        {
            $comment = array();
            $comment['comment'] = \sanitize_text_field($pros[$i]);
            if (!empty($cons[$i]))
                $comment['comment'] .= ' ' . $cons[$i];
            if (!empty($ratings[$i]))
                $comment['rating'] = TextHelper::ratingPrepare($ratings[$i]);

            if (!empty($users[$i]))
                $comment['name'] = sanitize_text_field($users[$i]);
            $extra['comments'][] = $comment;
        }
        $extra['images'] = array_slice($this->xpathArray(".//img[contains(@class, 'goodsIntro_thumbnailImg')]/@data-normal-src"), 1);

        return $extra;
    }

}
