<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * LacywearruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class LacywearruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';

    public function parseCatalog($max)
    {
        $urls = $this->xpathArray(".//*[@id='shop_outer']//*[@class='l_class']/a/@href");
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^http/', $url))
                $urls[$i] = 'https://www.lacywear.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@id='viewgoodsinfo']//h1");
    }

    public function parseDescription()
    {
        return trim($this->xpathScalar(".//div[@class='product_info']/div[@class='info']"));
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@id='update_price_section']//*[@class='active']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='old_price']/span[@class='line']");
    }

    public function parseManufacturer()
    {
        $features = $this->parseFeatures();
        if ($features && isset($features[0]) && $features[0]['name'] == 'Бренд')
            return $features[0]['value'];
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//*[@id='main_foto_block']//img/@src");
        $img = 'https:' . $img;
        return $img;
    }

    /**
     * Большая картинка товара.
     * @return String
     */
    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//*[@id='main_foto_block']//a/@href");
        $img = 'https:' . $img;
        return $img;
    }

    public function parseFeatures()
    {
        $features = array();
        $names = $this->xpathArray(".//div[@class='product_info']//table//tr/td[1]");
        $values = $this->xpathArray(".//div[@class='product_info']//table//tr/td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $name = sanitize_text_field($names[$i]);
                $name = trim(str_replace(':', '', $name));
                $feature['name'] = $name;
                $feature['value'] = sanitize_text_field($values[$i]);
                $features[] = $feature;
            }
        }
        return $features;
    }

    /**
     * Любые дополнительные данные по товару
     * @return array
     */
    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = $this->parseFeatures();

        $extra['images'] = array();
        $results = $this->xpathArray(".//*[@id='viewgoodsfoto_min_left']/a/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res && preg_match('/^\/\//', $res))
                $res = 'http:' . $res;
            $extra['images'][] = $res;
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
