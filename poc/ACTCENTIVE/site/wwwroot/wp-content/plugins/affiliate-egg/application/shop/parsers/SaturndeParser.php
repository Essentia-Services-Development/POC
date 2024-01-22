<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * SaturndeParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */

// Bot protection!

class SaturndeParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'EUR';
    //protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    //protected $user_agent = array('wget');
    //protected $user_agent = array('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:78.0) Gecko/20100101 Firefox/78.0');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        // redirect from search to product page
        if ($this->parseTitle() && $url = $this->xpathScalar(".//link[@rel='canonical']/@href"))
        {
            return array($url);
        }
        return $this->xpathArray(".//a[contains(@class, 'ProductListItem__StyledLink')]/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//span[contains(@class, 'StrikeThrough__StyledStrikePriceTypo')]");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//section[@id='features']", true);
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();
        $names = $this->xpathArray(".//section[@id='features']//td[1]");
        $values = $this->xpathArray(".//section[@id='features']//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (empty($values[$i]))
                continue;

            $name = trim(\sanitize_text_field($names[$i]), " :");
            $value = \sanitize_text_field($values[$i]);
            if (!$name || !$value)
                continue;
            $feature['name'] = $name;
            $feature['value'] = $value;
            $extra['features'][] = $feature;
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
