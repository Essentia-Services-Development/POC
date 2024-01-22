<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * PriceministercomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class MaterielnetParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'EUR';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='nomProduit']/../../../a/@href");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@id='ProdSectionDesc']//td[@nowrap]");
        $values = $this->xpathArray(".//*[@id='ProdSectionDesc']//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!trim($names[$i]))
                continue;
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        return $extra;
    }

}
