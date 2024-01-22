<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EbayinParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015  keywordrush.com
 */
require_once dirname(__FILE__) . '/EbaycomParser.php';

class EbayinParser extends EbaycomParser {

    protected $currency = 'INR';

}
