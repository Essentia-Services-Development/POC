<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * TigerfitnesscomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class TigerfitnesscomParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[@class='productitem--image-link']/@href");
    }

}
