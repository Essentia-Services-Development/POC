<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EbaydeParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015  keywordrush.com
 */
require_once dirname(__FILE__) . '/EbaycomParser.php';

class EbaydeParser extends EbaycomParser {

    protected $currency = 'EUR';

}
