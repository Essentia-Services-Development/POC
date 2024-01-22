<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * DetmirruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class DetmirruParser extends LdShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//div/div/div/div/div//div/a[contains(@href, '/product/')]/@href");
    }

    public function parseOldPrice()
    {
        if (preg_match('/,&quot;old_price&quot;:{&quot;price&quot;:(.+?),&quot;/', $this->dom->saveHTML(), $matches))
            return $matches[1];
    }

}
