<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EbayitParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018  keywordrush.com
 */
require_once dirname(__FILE__) . '/EbaycomParser.php';

class EbayitParser extends EbaycomParser {

    protected $currency = 'EUR';

}
