<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EtsyParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class EtsyParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        $urls = $this->xpathArray(".//a[contains(@class, 'listing-link')]/@href");
        return $urls;
    }

    public function parseDescription()
    {
        $paths = array(
            ".//p[@class='wt-text-body-01 wt-break-word']",
        );

        return $this->xpathScalar($paths, true);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//div[@data-buy-box-region='price']//p[@class='wt-text-title-03 wt-mr-xs-1']/span[2]",
            ".//div[@class='wt-mb-xs-3']//p[@class='wt-text-title-03 wt-mr-xs-2']",
            ".//*[@class='text-largest strong override-listing-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='wt-text-strikethrough wt-mr-xs-1']",
            ".//div[@class='wt-mb-xs-3']//p[contains(@class, 'wt-text-strikethrough')]",
        );

        return $this->xpathScalar($paths);
    }

    /**
     * @return String
     */
    public function parseImgLarge()
    {
        return str_replace('/il_570xN.', '/il_fullxfull.', $this->parseImg());
    }

    public function getCurrency()
    {
        if ($this->parsePrice())
        {
            if (preg_match('/"locale_currency_code":"([A-Z]+?)"/', $this->dom->saveHtml(), $matches))
                return $matches[1];
        }
    }

}
