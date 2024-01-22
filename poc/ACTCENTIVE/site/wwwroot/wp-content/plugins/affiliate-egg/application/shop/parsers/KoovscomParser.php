<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * KoovscomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link httpы://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class KoovscomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//li[@class='imageView']//a/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='pd-right-side']//span[@class='pd-price-striked']");
        
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();

        $names = $this->xpathArray(".//div[@class='info-care']//*[@class='text']");
        $values = $this->xpathArray(".//div[@class='info-care']//*[contains(@class, 'pd-col')]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = \sanitize_text_field(str_replace(":", "", $names[$i]));
                $feature['value'] = \sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        return $extra;
    }


}
