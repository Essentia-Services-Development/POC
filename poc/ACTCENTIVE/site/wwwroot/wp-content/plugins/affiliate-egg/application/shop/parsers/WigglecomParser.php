<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * WigglecomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class WigglecomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//div[@id='search-results']//div[contains(@class,'js-result-list-item')]//a[@data-ga-action='Product Title']/@href"), 0, $max);
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='bem-pricing__list-price']/span[@class='bem-pricing__list-price--origin']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//div[@id='itemtop']//span[@itemprop='manufacturer']");
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//div[@id='tabDescription']//tr[@class='bem-table__row']/th");
        $values = $this->xpathArray(".//div[@id='tabDescription']//tr[@class='bem-table__row']/td");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = str_replace(":", "", $names[$i]);
                $feature['value'] = $values[$i];
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//ul[@id='gallery']/li//img/@src");

        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $res = str_replace('w=73&h=73', 'w=430&h=430', $res);
                $extra['images'][] = $res;
            }
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='ratingValue']"));

        return $extra;
    }

    public function isInStock()
    {
        return true;
        /*
          $res = $this->xpath->evaluate("boolean(.//div[@id='productAvailabilityMessage'][contains(@class,'out-of-stock')])");
          return ($res) ? false : true;
         * 
         */
    }

}
