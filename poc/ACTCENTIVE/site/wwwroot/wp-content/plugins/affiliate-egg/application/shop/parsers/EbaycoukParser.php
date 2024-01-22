<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EbaycoukParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016  keywordrush.com
 */
require_once dirname(__FILE__) . '/EbaycomParser.php';

class EbaycoukParser extends EbaycomParser {

    protected $currency = 'EUR';

}
