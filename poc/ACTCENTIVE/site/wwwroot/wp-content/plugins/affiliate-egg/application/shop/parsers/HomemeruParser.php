<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * HomemeruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class HomemeruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@class='goods-block']//a[@class='gtm-product-link']/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//div[@class='listing-item-content']//a[@class='productLink']/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//div[@class='listing']//span/a/@href"), 0, $max);

        if (!$urls)
            $urls = array_slice($this->xpathArray(".//div[contains(@class, 'goods-list')]//a[contains(@class, 'gtm-product-link')]/@href"), 0, $max);

        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.homeme.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1/text()");
    }

    public function parseDescription()
    {
        $description = sanitize_text_field($this->xpathScalar(".//div[@class='good-params']/p"));
        if (!$description)
            $description = sanitize_text_field($this->xpathScalar(".//div[@class='card-add-blocks']"));
        if (!$description)
            $description = sanitize_text_field($this->xpathScalar(".//*[contains(@class,'c-discr2__text')]/p"));
        return $description;
    }

    public function parseOldPrice()
    {

        return $this->xpathScalar(".//span[contains(@class, 'oldprice')]");
    }

    public function parseManufacturer()
    {
        //return 'HomeMe';
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();

        $names = $this->xpathArray(".//dl[@class='c-characteristic__list']/dt");
        $values = $this->xpathArray(".//dl[@class='c-characteristic__list']/dd");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//a[@class='js-fullscreen-btn']/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            $extra['images'][] = $res;
        }
        return $extra;
    }

}
