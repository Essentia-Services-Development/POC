<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EbayfrParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017  keywordrush.com
 */
require_once dirname(__FILE__) . '/EbaycomParser.php';

class EbayfrParser extends EbaycomParser {

    protected $currency = 'EUR';

}
