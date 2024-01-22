<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * BabaduruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class BabaduruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@class='qcontent']//a[@class='bproduct__info']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.babadu.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        $title = '';
        $brand = $this->parseManufacturer();
        $title = trim($this->xpathScalar(".//h1[@itemprop='name']"));
        $title = str_replace($brand, '', $title);
        $title = preg_replace("/\(.+?\)/msi", '', $title);
        $title = preg_replace("/\s+/msi", ' ', $title);
        return trim($title);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@id='tab_descr']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//meta[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='product__price_old']/*[@class='js-price2']/@price");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//p[@itemtype='http://schema.org/Brand']//meta[@itemprop='name']/@content");
    }

    public function parseImg()
    {
        $ret = $this->xpathScalar(".//meta[@itemprop='image']/@content");
        if ($ret)
        {
            if (!preg_match('/^https?/', $ret))
                $ret = 'https:' . $ret;
        }
        return $ret;
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();

        /*
          $extra['features'] = array();

          $names = $this->xpathArray(".//div[@class='specifications__descr']//strong");
          $values = $this->xpathArray(".//div[@class='about_catalog']//div[@class='r']");
          $feature = array();
          for ($i = 0; $i < count($names); $i++)
          {
          if (!empty($values[$i]) && $names[$i] != 'Бренды')
          {
          $feature['name'] = sanitize_text_field($names[$i]);
          $feature['value'] = sanitize_text_field($values[$i]);
          $extra['features'][] = $feature;
          }
          }
         * 
         */

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@class='product__images']//a[@itemtype='http://schema.org/ImageObject']/@data-zoom-image");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                if (!preg_match('/^https?:/', $res))
                    $res = 'https:' . $res;
                $extra['images'][] = $res;
            }
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
