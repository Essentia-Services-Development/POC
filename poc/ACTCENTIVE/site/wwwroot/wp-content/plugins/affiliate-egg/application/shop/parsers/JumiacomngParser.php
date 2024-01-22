<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * JumiacomngParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
require_once dirname(__FILE__) . '/JumiacomegParser.php';

class JumiacomngParser extends JumiacomegParser {

    protected $charset = 'utf-8';
    protected $currency = 'NGN';

    public function parsePrice()
    {
        $price = $this->xpathScalar("(.//*[@class='price-box']//*/@data-price)[1]");
        if ($price)
            return $price;
        else
            return parent::parsePrice();
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='price-box']//*[@class='price -old']/*/@data-price");
    }

    public function parseImgLarge()
    {
        return $this->xpathScalar(".//*[@id='productImage']/@data-zoom");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $names = $this->xpathArray(".//*[@id='product-details']//*[@class='osh-col -head']");
        $values = $this->xpathArray(".//*[@id='product-details']//*[@class='osh-col ']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (empty($names[$i]))
                continue;
            if (!empty($values[$i]))
            {
                $value = sanitize_text_field($values[$i]);
                if (!$value)
                    continue;
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = $value;
                $extra['features'][] = $feature;
            }
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@id='ratingReviews']//*[@class='container']/span"));

        return $extra;
    }

}
