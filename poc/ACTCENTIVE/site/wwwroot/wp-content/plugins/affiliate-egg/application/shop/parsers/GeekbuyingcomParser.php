<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * GeekbuyingcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class GeekbuyingcomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        $path = array(
            ".//div[@class='img pruImage']/a/@href",
            ".//div[@class='name']/a/@href",
            ".//div[@class='width fix']//dl/dt/a/@href",
        );

        return $this->xpathArray($path);    }

    public function parseImg()
    {
        // wrong immg in ld data
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='price_box']//*[@id='regprice']");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();
        $names = $this->xpathArray(".//table[@class='jbEidtTable']//td[1]");
        $values = $this->xpathArray(".//table[@class='jbEidtTable']//td[2]", true);
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $value = strip_tags(str_replace('<br>', '; ', $values[$i]));
                $value = str_replace('&nbsp;', ' ', $value);
                $value = html_entity_decode($value);
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($value);
                $extra['features'][] = $feature;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//*[@id='nowBuyDiv']//*[@class='btn sold_btn']") == 'Sold Out')
            return false;
        else
            return parent::isInStock();
    }

}
